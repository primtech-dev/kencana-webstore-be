<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class BranchScopeTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const ORDER_PERMS = ['orders.view', 'orders.manage', 'orders.cancel', 'orders.ship', 'orders.delete'];

    public function test_branch_admin_cannot_access_orders_of_other_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $variant = $this->makeVariant();
        $this->setInventory($b, $variant, 10, 2);
        $orderB = $this->makeOrder($b, [[$variant, 2]]);

        $admin = $this->makeAdmin($a, self::ORDER_PERMS);

        $this->actingAs($admin)->get(route('admin.orders.show', $orderB->id))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.orders.print', $orderB->id))->assertNotFound();
        $this->actingAs($admin)->post(route('admin.orders.updateStatus', $orderB->id), ['status' => 'cancelled'])->assertNotFound();
        $this->actingAs($admin)->delete(route('admin.orders.destroy', $orderB->id))->assertNotFound();

        $this->assertSame('pending', $orderB->fresh()->status);
        $this->assertNull(Order::find($orderB->id)->deleted_at);
    }

    public function test_orders_datatable_is_limited_to_own_branch_and_pusat_sees_all(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $variant = $this->makeVariant();
        $orderA = $this->makeOrder($a, [[$variant, 1]]);
        $orderB = $this->makeOrder($b, [[$variant, 1]]);

        $branchAdmin = $this->makeAdmin($a, self::ORDER_PERMS, 'branch_role');
        $pusat = $this->makeAdmin(null, self::ORDER_PERMS, 'pusat_role');

        $own = $this->actingAs($branchAdmin)->ajax()->get(route('admin.orders.index', ['branch_id' => $b->id]))->assertOk();
        $this->assertSame([$orderA->order_no], collect($own->json('data'))->pluck('order_no')->all());

        $all = $this->actingAs($pusat)->ajax()->get(route('admin.orders.index'))->assertOk();
        $this->assertEqualsCanonicalizing([$orderA->order_no, $orderB->order_no], collect($all->json('data'))->pluck('order_no')->all());

        $filtered = $this->actingAs($pusat)->ajax()->get(route('admin.orders.index', ['branch_id' => $b->id]))->assertOk();
        $this->assertSame([$orderB->order_no], collect($filtered->json('data'))->pluck('order_no')->all());
    }

    public function test_orders_search_by_customer_name_works(): void
    {
        $a = $this->makeBranch('A');
        $order = $this->makeOrder($a, [[$this->makeVariant(), 1]]);
        $pusat = $this->makeAdmin(null, self::ORDER_PERMS);

        $cols = ['DT_RowIndex', 'order_no', 'customer', 'branch', 'total_amount', 'status', 'created_at', 'action'];
        $get = fn (string $q) => $this->actingAs($pusat)->ajax()->get(route('admin.orders.index', $this->dtParams($cols, $q)))->assertOk();

        $hit = $get('budi');
        $this->assertSame([$order->order_no], collect($hit->json('data'))->pluck('order_no')->all());

        // multi-kata, seperti kotak pencarian DataTables sungguhan
        $this->assertSame([$order->order_no], collect($get('budi santoso')->json('data'))->pluck('order_no')->all());
        $this->assertSame([$order->order_no], collect($get(strtolower($order->order_no))->json('data'))->pluck('order_no')->all());

        $miss = $get('tidak ada');
        $this->assertCount(0, $miss->json('data'));
    }

    public function test_dashboard_works_for_superadmin_without_branch_and_for_branch_admin(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v = $this->makeVariant();
        $this->makeOrder($a, [[$v, 1]]);
        $this->makeOrder($b, [[$v, 1]]);

        $this->actingAs($this->makeSuperAdmin())->get('/')->assertOk()->assertViewHas('ordersToday', 2);
        $this->actingAs($this->makeAdmin($a, []))->get('/')->assertOk()->assertViewHas('ordersToday', 1);
    }

    public function test_branch_admin_user_management_is_scoped_and_cannot_escalate(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $perms = ['users.view', 'users.create', 'users.update', 'users.delete'];
        $admin = $this->makeAdmin($a, $perms);

        $other = User::factory()->create(['branch_id' => $b->id]);
        $super = $this->makeSuperAdmin();

        // user cabang lain & superadmin tidak terlihat
        $this->actingAs($admin)->get(route('users.edit', $other->id))->assertNotFound();
        $this->actingAs($admin)->get(route('users.edit', $super->id))->assertNotFound();
        $this->actingAs($admin)->put(route('users.update', $super->id), ['name' => 'x', 'email' => 'x@x.com', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertNotFound();
        $this->actingAs($admin)->delete(route('users.destroy', $other->id))->assertNotFound();

        $list = $this->actingAs($admin)->ajax()->get(route('users.index'))->assertOk();
        $this->assertSame([$admin->email], collect($list->json('data'))->pluck('email')->all());
        $this->flushHeaders();

        // tidak boleh assign role superadmin
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Baru', 'email' => 'baru@example.com',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
            'roles' => ['superadmin'],
        ])->assertSessionHasErrors('roles.0');
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);

        // branch & flag superadmin dipaksa
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Kasir', 'email' => 'kasir@example.com',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
            'branch_id' => $b->id, 'is_superadmin' => 1, 'roles' => ['kasir'],
        ])->assertRedirect(route('users.index'));

        $created = User::where('email', 'kasir@example.com')->firstOrFail();
        $this->assertSame($a->id, $created->branch_id);
        $this->assertFalse($created->is_superadmin);
        $this->assertTrue($created->hasRole('kasir'));
    }

    public function test_superadmin_can_create_pusat_user_and_last_superadmin_is_protected(): void
    {
        $super = $this->makeSuperAdmin();

        $this->actingAs($super)->post(route('users.store'), [
            'name' => 'Pusat', 'email' => 'pusat@example.com',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ])->assertRedirect(route('users.index'));
        $this->assertNull(User::where('email', 'pusat@example.com')->first()->branch_id);

        // tidak boleh menghapus / menonaktifkan diri sendiri atau superadmin terakhir
        $this->actingAs($super)->delete(route('users.destroy', $super->id))->assertSessionHas('error');
        $this->assertNotNull(User::find($super->id));
    }

    public function test_only_superadmin_can_manage_roles(): void
    {
        $a = $this->makeBranch('A');
        $admin = $this->makeAdmin($a, ['roles.view', 'roles.create', 'roles.update', 'roles.delete']);

        $this->actingAs($admin)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($this->makeSuperAdmin())->get(route('roles.index'))->assertOk();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
    }
}

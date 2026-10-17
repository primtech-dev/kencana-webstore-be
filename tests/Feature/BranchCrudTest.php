<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class BranchCrudTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['branches.view', 'branches.create', 'branches.update', 'branches.delete'];

    public function test_create_normalizes_code_and_validates_input(): void
    {
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('branches.store'), ['code' => ' knc-jkt1 ', 'name' => 'Jakarta 1', 'latitude' => -6.2, 'longitude' => 106.8, 'phone' => '021-555 1234'])
            ->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', ['code' => 'KNC-JKT1']);

        $this->actingAs($admin)->post(route('branches.store'), ['code' => 'knc-jkt1', 'name' => 'Dup'])->assertSessionHasErrors('code');
        $this->actingAs($admin)->post(route('branches.store'), ['code' => 'X1', 'name' => 'Bad', 'latitude' => 120, 'phone' => 'abc'])->assertSessionHasErrors(['latitude', 'phone']);
    }

    public function test_manager_can_be_set_from_branch_users_only(): void
    {
        $branch = $this->makeBranch('A');
        $other = $this->makeBranch('B');
        $admin = $this->makeAdmin(null, self::PERMS);
        $manager = $this->makeAdmin($branch, [], 'm1');
        $foreign = $this->makeAdmin($other, [], 'm2');

        $payload = ['code' => 'A', 'name' => 'Cabang A', 'is_active' => 1];

        $this->actingAs($admin)->put(route('branches.update', $branch->id), $payload + ['manager_user_id' => $manager->id])->assertSessionHasNoErrors();
        $this->assertSame($manager->id, $branch->fresh()->manager_user_id);

        $this->actingAs($admin)->put(route('branches.update', $branch->id), $payload + ['manager_user_id' => $foreign->id])->assertSessionHasErrors('manager_user_id');
    }

    public function test_delete_is_blocked_when_branch_has_dependencies_and_code_can_be_reused(): void
    {
        $admin = $this->makeAdmin(null, self::PERMS);
        $keep = $this->makeBranch('KEEP');
        $branch = $this->makeBranch('DEL');
        $variant = $this->makeVariant();
        $this->makeOrder($branch, [[$variant, 1]]);

        $this->actingAs($admin)->delete(route('branches.destroy', $branch->id))->assertSessionHas('error');
        $this->assertNull(Branch::find($branch->id)->deleted_at);

        $empty = $this->makeBranch('EMPTY');
        $this->actingAs($admin)->delete(route('branches.destroy', $empty->id))->assertSessionHas('success');
        $this->assertSoftDeleted('branches', ['id' => $empty->id]);

        // kode bekas cabang terhapus boleh dipakai lagi
        $this->actingAs($admin)->post(route('branches.store'), ['code' => 'EMPTY', 'name' => 'Baru'])->assertRedirect(route('branches.index'));
    }

    public function test_cannot_delete_or_deactivate_last_active_branch(): void
    {
        $admin = $this->makeAdmin(null, self::PERMS);
        $only = $this->makeBranch('ONLY');

        $this->actingAs($admin)->delete(route('branches.destroy', $only->id))->assertSessionHas('error');
        $this->actingAs($admin)->put(route('branches.update', $only->id), ['code' => 'ONLY', 'name' => 'x', 'is_active' => 0])->assertSessionHas('error');
        $this->assertTrue($only->fresh()->is_active);
    }

    public function test_branch_admin_manages_only_own_branch(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $admin = $this->makeAdmin($a, self::PERMS);

        $this->actingAs($admin)->get(route('branches.edit', $b->id))->assertNotFound();
        $this->actingAs($admin)->get(route('branches.show', $b->id))->assertNotFound();
        $this->actingAs($admin)->get(route('branches.create'))->assertForbidden();
        $this->actingAs($admin)->delete(route('branches.destroy', $a->id))->assertForbidden();

        // boleh ubah data biasa, tapi kode & status aktif diabaikan
        $this->actingAs($admin)->put(route('branches.update', $a->id), ['code' => 'HACK', 'name' => 'Nama Baru', 'is_active' => 0])->assertSessionHasNoErrors();
        $fresh = $a->fresh();
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertSame('A', $fresh->code);
        $this->assertTrue($fresh->is_active);

        $this->actingAs($admin)->get(route('branches.show', $a->id))->assertOk();
        $this->actingAs($admin)->get(route('branches.edit', $a->id))->assertOk();
    }
}

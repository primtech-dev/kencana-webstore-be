<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes, BelongsToBranch;

    protected $table = 'orders';

    protected $casts = [
        'tax_breakdown' => 'array',
        'discount_breakdown' => 'array',
        'applied_promotions' => 'array',
        'meta' => 'array',
        'is_refunded' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'placed_at' => 'datetime',
    ];

    protected $fillable = [
        'public_id',
        'order_no',
        'customer_id',
        'branch_id',
        'status',
        'currency',
        'address_id',
        'delivery_method',
        'placed_at',
        'subtotal_before_discount',
        'discount_total',
        'subtotal_after_discount',
        'shipping_cost',
        'other_charges',
        'tax_total',
        'total_amount',
        'payment_method_id',
        'payment_status',
        'tax_breakdown',
        'discount_breakdown',
        'applied_promotions',
        'notes',
        'cancelled_at',
        'is_refunded',
        'meta',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->public_id)) {
                $order->public_id = (string) Str::uuid();
            }
            if (empty($order->order_no)) {
                $order->order_no = 'ORD' . strtoupper(Str::random(8));
            }
        });
    }

    // Relations
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id'); // pastikan model Branch ada
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function address()
    {
        return $this->belongsTo(\App\Models\Address::class, 'address_id')->withTrashed();
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->latest('created_at')->latest('id');
    }
}

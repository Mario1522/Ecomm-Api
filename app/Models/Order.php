<?php

namespace App\Models;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;

class Order extends Model
{
    //fillable
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_country',
        'shipping_phone',
        'subtotal',
        'tax',
        'shipping_cost',
        'payment_method',
        'payment_status',
        'order_number',
        'notes',
        'transaction_id',
        'paid_at'
    ];

    //casts
    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    // Define relationships
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }
    public function canBeCancelled(){
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::PAID
        ]);
    }
    public function markAsPaid($transactionId){
        $this->update([
            'status' => OrderStatus::PAID,
            'payment_status' => PaymentStatus::COMPLETED,
            'transaction_id' => $transactionId,
            'paid_at' => now()
        ]);
    }

    public function markAsFaild(){
        $this->update([
            'payment_status' => PaymentStatus::FAILD,
        ]);
    }

    public function canBePaid(){
        return in_array($this->payment_status, [
            PaymentStatus::PENDING,
            PaymentStatus::FAILD
        ]);
    }

        public static function generateOrderNumber()
    {
        $year = date('Y');


        $randomNumber = strtoupper(substr(uniqid(), -6));
        return "ORD-{$year}-{$randomNumber}"; // e.g., ORD-2025-ABC123
    }

}

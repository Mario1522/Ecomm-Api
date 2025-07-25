<?php

namespace App\Models;


use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //fillable
    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity'
    ];
    // Define relationships
    public function order(){
        return $this->belongsTo(Order::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}

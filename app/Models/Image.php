<?php

namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'image',
        'product_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}

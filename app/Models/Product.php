<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Image;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'stock',
        'description',
        'category_id'
    ];


    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function images(){
        return $this->hasMany(Image::class);
    }
}

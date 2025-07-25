<?php

namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //fillable
    protected $fillable = ['name', 'slug', 'parent_id'];
    //relationships
    public function parent(){
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children(){
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function products(){
        return $this->hasMany(Product::class);
    }
}

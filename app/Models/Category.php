<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['kelkoo_category_id', 'name', 'slug','meta_title','meta_description','keyword'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}


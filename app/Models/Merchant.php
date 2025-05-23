<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = ['kelkoo_merchant_id', 'name','image','meta_title','meta_description','keyword','url','slug'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['kelkoo_brand_id', 'name', 'slug'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

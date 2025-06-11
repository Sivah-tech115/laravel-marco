<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBrandMerchant extends Model
{
    protected $table = 'category_brand_merchant'; // explicitly define the table name

    protected $fillable = [
        'category_id',
        'brand_id',
        'merchant_id',
    ];
}

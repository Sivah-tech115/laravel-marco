<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryBrandMerchant extends Model
{
    protected $fillable = [
        'query',
        'brand_id',
        'merchant_id',
    ];
}

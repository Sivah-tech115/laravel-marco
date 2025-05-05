<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'offer_id',
        'title',
        'slug',
        'description',
        'price',
        'price_without_rebate',
        'rebate_percentage',
        'delivery_cost',
        'total_price',
        'currency',
        'availability_status',
        'time_to_deliver',
        'ean',
        'image_url',
        'zoom_image_url',
        'offer_url',
        'go_url',
        'estimated_cpc',
        'estimated_mobile_cpc',
        'category_id',
        'brand_id',
        'merchant_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}


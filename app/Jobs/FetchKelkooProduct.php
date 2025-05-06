<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;

class FetchKelkooProduct implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $offers = $this->data['offers'] ?? [];

        foreach ($offers as $offer) {
            
            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $offer['brand']['id'] ?? null],
                ['name' => $offer['brand']['name'] ?? '', 'slug' => Str::slug($offer['brand']['name'] ?? '')]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $offer['category']['id'] ?? null],
                ['name' => $offer['category']['name'] ?? '', 'slug' => Str::slug($offer['category']['name'] ?? '')]

            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $offer['merchant']['id'] ?? null],
                ['name' => $offer['merchant']['name'] ?? '', 'image' => $offer['merchant']['logoUrl'] ?? '']

            );

            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $offer['title'],
                    'slug' => Str::slug($offer['title']),
                    'description' => $offer['description'] ?? null,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => $offer['code']['ean'] ?? null,
                    'image_url' => $offer['images'][0]['url'] ?? null,
                    'zoom_image_url' => $offer['images'][0]['zoomUrl'] ?? null,
                    'offer_url' => $offer['offerUrl']['landingUrl'] ?? null,
                    'go_url' => $offer['goUrl'] ?? null,
                    'estimated_cpc' => $offer['estimatedCpc'] ?? null,
                    'estimated_mobile_cpc' => $offer['estimatedMobileCpc'] ?? null,
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }
    }
}

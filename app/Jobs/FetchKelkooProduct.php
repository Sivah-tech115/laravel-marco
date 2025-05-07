<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Traits\GeneratesShortTitle;

class FetchKelkooProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GeneratesShortTitle;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        $offers = $this->data['offers'] ?? [];

        foreach ($offers as $offer) {
            $brandName = data_get($offer, 'brand.name', '');
            $categoryName = data_get($offer, 'category.name', '');
            $merchantName = data_get($offer, 'merchant.name', '');

            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => data_get($offer, 'brand.id')],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => data_get($offer, 'category.id')],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => data_get($offer, 'merchant.id')],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );

            $productTitle = $offer['title'] ?? '';
            $productDescription = data_get($offer, 'description', null);
            $shortTitle = $this->generateShortTitle($productTitle, $brandName);

            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $productTitle,
                    'slug' => Str::slug($productTitle),
                    'description' => $productDescription,
                    'meta_title' => $shortTitle,
                    'meta_description' => $productDescription ? Str::words($productDescription, 30, '...') : null,
                    'keyword' => $shortTitle,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => data_get($offer, 'code.ean'),
                    'image_url' => data_get($offer, 'images.0.url'),
                    'zoom_image_url' => data_get($offer, 'images.0.zoomUrl'),
                    'offer_url' => data_get($offer, 'offerUrl.landingUrl'),
                    'go_url' => data_get($offer, 'goUrl'),
                    'estimated_cpc' => data_get($offer, 'estimatedCpc'),
                    'estimated_mobile_cpc' => data_get($offer, 'estimatedMobileCpc'),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }
    }
}

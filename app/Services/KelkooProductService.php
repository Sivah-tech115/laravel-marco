<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Support\Str;

class KelkooProductService
{
    public function saveOffer(array $offer): void
    {
        $kelkooBrandId = data_get($offer, 'brand.id');
        $kelkooCategoryId = data_get($offer, 'category.id');
        $kelkooMerchantId = data_get($offer, 'merchant.id');

        $brand = null;
        $category = null;
        $merchant = null;

        if ($kelkooBrandId) {
            $brandName = data_get($offer, 'brand.name', '');
            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $kelkooBrandId],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );
        }

        if ($kelkooCategoryId) {
            $categoryName = data_get($offer, 'category.name', '');
            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $kelkooCategoryId],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );
        }

        if ($kelkooMerchantId) {
            $merchantName = data_get($offer, 'merchant.name', '');
            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $kelkooMerchantId],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );
        }

        $productTitle = $offer['title'] ?? '';
        $productDescription = data_get($offer, 'description', null);
        $shortTitle = Str::limit($productTitle, 40); // Or use your generateShortTitle()

        Product::updateOrCreate(
            ['offer_id' => $offer['offerId']],
            [
                'title' => $productTitle,
                'slug' => Str::slug($productTitle),
                'description' => $productDescription,
                'meta_title' => $productTitle,
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
                'brand_id' => $brand?->id,
                'category_id' => $category?->id,
                'merchant_id' => $merchant?->id,
            ]
        );
    }
}

?>

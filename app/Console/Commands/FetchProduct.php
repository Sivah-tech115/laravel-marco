<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Shortcode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\GeneratesShortTitle;

class FetchProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

     use GeneratesShortTitle;


    public function handle()
    {
        $country = 'it';  // Change the country as needed
        $page = 1;
        $shortcode = Shortcode::first();
        if (!$shortcode || !$shortcode->api_key) {
            $this->error('Token not found');
            return;
        }
        $token = $shortcode->api_key;

        do {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";
            $params = [
                'country' => $country,
                'additionalFields' => 'merchantLogoUrl,description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                'pageSize' => 100,
                'page' => $page,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            // Check if response is valid
            if (!$response->successful()) {
                $this->error('Failed to fetch data from Kelkoo API');
                Log::error('Kelkoo API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'page' => $page,
                ]);
                return;
            }
            $data = $response->json();
            if (!$response->successful() || isset($data['error'])) {
                $this->error('Kelkoo API error: ' . ($data['error'] ?? 'Unknown error'));
                Log::error('Kelkoo API failed', [
                    'status' => $response->status(),
                    'body' => $data,
                    'page' => $page,
                ]);
                return;
            }
            // Loop through offers and insert or update products
            foreach ($data['offers'] ?? [] as $offer) {


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
                        'slug' => Str::slug($merchantName),
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
            // Increment the page and check if there are more pages to fetch
            $page++;
            $hasNext = $data['meta']['offers']['nextPage'] ?? false;

            // Prevent API rate limit
            sleep(1);
        } while ($hasNext);

        $this->info('Import completed.');
    }
}

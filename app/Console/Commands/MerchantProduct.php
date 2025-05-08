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

class MerchantProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merchant-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to fetch and store merchant product data from Kelkoo API';

    /**
     * Execute the console command.
     */
    use GeneratesShortTitle;

    public function handle()
    {
        $country = 'it';  // Change the country as needed
        $page = 1;
        $shortcode = Shortcode::first();

        // Check if API key exists
        if (!$shortcode || !$shortcode->api_key) {
            $this->error('Token not found');
            return;
        }

        $token = $shortcode->api_key;

        // Fetch all merchants
        $merchants = Merchant::all();

        // Loop through each merchant and make API requests
        foreach ($merchants as $merchant) {


            do {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";
                $params = [
                    'country' => $country,
                    'additionalFields' => 'merchantLogoUrl,description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                    'filterBy' => 'merchantId:' . $merchant->kelkoo_merchant_id,  // Corrected syntax here
                    'pageSize' => 100,
                    'page' => $page,
                ];
                Log::info('params', $params);

                // Make the API request
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($url, $params);

                // Log the page and merchant ID for debugging
                Log::info('Fetching data', [
                    'merchant_id' => $merchant->kelkoo_merchant_id,
                    'page' => $page,
                    'status' => $response->status(),
                ]);

                // Check if response is valid
                if (!$response->successful()) {
                    $this->error('Failed to fetch data from Kelkoo API');
                    Log::error('Kelkoo API failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'merchant_id' => $merchant->kelkoo_merchant_id,
                        'page' => $page,
                    ]);
                    return; // Terminate the script if there's an error
                }

                $data = $response->json();

                // Handle error response from the API
                if (isset($data['error'])) {
                    $errorMessage = $data['error'];

                    // Check for page size related error
                    if (str_contains($errorMessage, 'The specified page and pageSize parameters are invalid')) {
                        $this->error('API error: ' . $errorMessage);
                        Log::error('Kelkoo API error: Page size limit exceeded', [
                            'merchant_id' => $merchant->kelkoo_merchant_id,
                            'error' => $errorMessage,
                            'page' => $page,
                        ]);
                        break; // Stop processing further pages for this merchant
                    } else {
                        $this->error('Kelkoo API error: ' . $errorMessage);
                        Log::error('Kelkoo API error', [
                            'merchant_id' => $merchant->kelkoo_merchant_id,
                            'error' => $errorMessage,
                        ]);
                        break; // Break the loop on any other error
                    }
                }

                // Loop through offers and insert or update products
                foreach ($data['offers'] ?? [] as $offer) {

                    $merchantId = $offer['merchant']['id'] ?? null;
                    $brandId = $offer['brand']['id'] ?? null;
                    $categoryId = $offer['category']['id'] ?? null;
                    $brandName = data_get($offer, 'brand.name', '');
                    $categoryName = data_get($offer, 'category.name', '');
                    $merchantName = data_get($offer, 'merchant.name', '');

                    // Insert or update brand
                    $brand = Brand::updateOrCreate(
                        ['kelkoo_brand_id' => $brandId],
                        [
                            'name' => $brandName,
                            'slug' => Str::slug($brandName),
                            'meta_title' => $brandName,
                            'keyword' => $brandName,
                        ]
                    );

                    // Insert or update category
                    $category = Category::updateOrCreate(
                        ['kelkoo_category_id' => $categoryId],
                        [
                            'name' => $categoryName,
                            'slug' => Str::slug($categoryName),
                            'meta_title' => $categoryName,
                            'keyword' => $categoryName,
                        ]
                    );

                    // Insert or update merchant (avoid overwriting the merchant variable here)
                    $merchantEntity = Merchant::updateOrCreate(
                        ['kelkoo_merchant_id' => $merchantId ],
                        [
                            'name' => $merchantName,
                            'slug' => Str::slug($merchantName),
                            'image' => data_get($offer, 'merchant.logoUrl', ''),
                            'meta_title' => $merchantName,
                            'keyword' => $merchantName,
                        ]
                    );

                    // Generate short title and description for the product
                    $productTitle = $offer['title'] ?? '';
                    $productDescription = data_get($offer, 'description', null);
                    $shortTitle = $this->generateShortTitle($productTitle, $brandName);

                    // Insert or update product
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
                            'merchant_id' => $merchantEntity->id,
                        ]
                    );
                }

                // Check if there are more pages to fetch
                $page++;
                $hasNext = $data['meta']['offers']['nextPage'] ?? false;

                // Prevent API rate limit
                sleep(1);
            } while ($hasNext);

            $this->info("Import completed for Merchant: {$merchant->name}");
        }

        $this->info('Import process for all merchants completed.');
    }
}

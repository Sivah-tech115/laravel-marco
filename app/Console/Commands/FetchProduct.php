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
                'page' => 6,
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
                // Get or create brand, category, and merchant
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
            // Increment the page and check if there are more pages to fetch
            $page++;
            $hasNext = $data['meta']['offers']['nextPage'] ?? false;

            // Prevent API rate limit
            sleep(1);
        } while ($hasNext);

        $this->info('Import completed.');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Shortcode;

class ProductexistCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:productexist-check';

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
        // $country = 'it';
        $shortcode = Shortcode::first();

        if (!$shortcode || !$shortcode->api_key) {
            $this->error('Kelkoo API token not found.');
            return;
        }

        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

        $products = Product::all();

        foreach ($products as $product) {
            $offerId = $product->offer_id;

            $params = [
                'country' => $country,
                'filterBy' => 'offerId:' . $offerId,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                $this->error("Failed to fetch offerId: {$offerId}, HTTP {$response->status()}");
                continue;
            }

            $data = $response->json();

            if (empty($data['offers']) || !collect($data['offers'])->firstWhere('offerId', $offerId)) {
                $this->info("Deleting product with offer_id {$offerId} (no longer exists in Kelkoo)");
                $product->delete();
            } else {
                $this->info("Product with offer_id {$offerId} exists on Kelkoo.");
            }
        }

        $this->info('Product existence check complete.');
    }
}

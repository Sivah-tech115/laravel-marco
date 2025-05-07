<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Shortcode;

class ImportKelkooProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-kelkoo-merchant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports merchant from Kelkoo API into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $country = 'it';  // Change the country as needed
        $shortcode = Shortcode::first();
        if (!$shortcode || !$shortcode->api_key) {
            $this->error('Token not found');
            return;
        }
        $token = $shortcode->api_key;

        $url = "https://api.kelkoogroup.net/publisher/shopping/v2/feeds/merchants";

        $params = [
            'country' => $country,
            'format' => 'json',
            'offerMatch' => 'any',
            'merchantMatch' => 'any'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept-encoding' => 'gzip'
        ])->get($url, $params);

        if (!$response->successful()) {
            $this->error('Failed to fetch data from Kelkoo API. Status: ' . $response->status());
            $this->info('Import  ' . $response);
            return;
        }

        $data = $response->json();

        foreach ($data as $offer) {
            $merchantName = data_get($offer, 'merchant.name', '');

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => data_get($offer, 'merchant.id')],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );
        }

        $this->info('Import completed successfully. Total merchants imported: ' . count($data));
    }
}

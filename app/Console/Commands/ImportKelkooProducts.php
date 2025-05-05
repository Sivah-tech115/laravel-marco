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
            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $offer['id']],
                [
                    'name' => $offer['name'] ?? '',
                    'image' => $offer['logoUrl'] ?? '',
                    'url' => $offer['url'] ?? ''
                ]

            );
        }

        $this->info('Import completed successfully. Total merchants imported: ' . count($data));
    }
}

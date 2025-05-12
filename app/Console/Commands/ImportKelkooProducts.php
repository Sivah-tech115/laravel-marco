<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Merchant;
use App\Models\Shortcode;
use Illuminate\Support\Str;

class ImportKelkooProducts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:import-kelkoo-merchant';

    /**
     * The console command description.
     */
    protected $description = 'Imports merchants from Kelkoo API into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $country = 'it';  // Set country code (e.g., 'it' for Italy)
        $shortcode = Shortcode::first();

        if (!$shortcode || !$shortcode->api_key) {
            $this->error('❌ API token not found in the shortcode table.');
            return;
        }

        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $url = "https://api.kelkoogroup.net/publisher/shopping/v2/feeds/merchants";

        $params = [
            'country' => $country,
            'format' => 'json',
            'offerMatch' => 'any',
            'merchantMatch' => 'any'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept-Encoding' => 'gzip'
        ])->get($url, $params);

        if (!$response->successful()) {
            $this->error('❌ Failed to fetch data from Kelkoo API. HTTP Status: ' . $response->status());
            return;
        }

        $merchants = $response->json();

        if (!is_array($merchants)) {
            $this->error('❌ Unexpected response format from Kelkoo API.');
            return;
        }

        $importedCount = 0;

        foreach ($merchants as $item) {
            $merchantId = $item['id'] ?? null;
            $merchantName = $item['name'] ?? null;
            $logoUrl = $item['logoUrl'] ?? null;
            $Url = $item['url'] ?? null;
            


            Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $merchantId],
                [
                    'name' => $merchantName,
                    'image' => $logoUrl ?? '',
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                    'url' => $Url,
                'slug' => Str::slug(str_replace('.', '-', $merchantName))
                ]
            );

            $importedCount++;
        }

        $this->info("✅ Import completed successfully. Total merchants imported or updated: {$importedCount}");
    }
}

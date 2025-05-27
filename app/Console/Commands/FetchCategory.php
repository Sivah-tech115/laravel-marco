<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Shortcode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FetchCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and import categories from Kelkoo API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Step 1: Load token and country
        $shortcode = Shortcode::first();

        if (!$shortcode || !$shortcode->api_key) {
            $this->error('❌ API token not found in the shortcode table.');
            return;
        }

        $token = $shortcode->api_key;
        $country = $shortcode->countryName ?? 'it';

        // Step 2: Fetch category list from Kelkoo API
        $url = "https://api.kelkoogroup.net/publisher/shopping/v2/feeds/category-list";

        $params = [
            'country' => $country,
            'format'  => 'json'
        ];

        $response = Http::withHeaders([
            'Authorization'    => 'Bearer ' . $token,
            'Accept-Encoding'  => 'gzip',
        ])->get($url, $params);

        if (!$response->successful()) {
            $this->error('❌ Failed to fetch data from Kelkoo API. HTTP Status: ' . $response->status());
            return;
        }

        $categories = $response->json();

        if (!is_array($categories)) {
            $this->error('❌ Unexpected response format from Kelkoo API.');
            return;
        }

        // Step 3: Save categories to DB
        $importedCount = 0;

        foreach ($categories as $item) {
            $categoryId   = $item['id'] ?? null;
            $categoryName = $item['name'] ?? null;

            if (!$categoryId || !$categoryName) {
                continue;
            }

            Category::updateOrCreate(
                ['kelkoo_category_id' => $categoryId],
                [
                    'name'        => $categoryName,
                    'slug'        => Str::slug($categoryName),
                    'meta_title'  => $categoryName,
                    'keyword'     => $categoryName,
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]
            );

            $importedCount++;
        }

        // Step 4: Done
        $this->info("✅ Import completed successfully. Total categories imported or updated: {$importedCount}");
    }
}

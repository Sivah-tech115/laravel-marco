<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class MigrateTables extends Command
{
    protected $signature = 'app:migrate-tables';
    protected $description = 'Migrate offers with pagination';

    public function handle()
    {
        $offset = 0;
        $batchSize = 50;
        $totalProcessed = 0;

        do {
            // Get 50 offers
            $offers = DB::table('wp_posts as p')
                ->join('wp_postmeta as pm', 'p.ID', '=', 'pm.post_id')
                ->where('p.post_type', 'offers')
                ->where('p.comment_count', '0')
                ->where('p.post_status', 'publish')
                ->where('pm.meta_key', 'offers_ids')
                ->whereNotNull('pm.meta_value')
                ->where('pm.meta_value', '!=', '')
                ->orderBy('p.ID', 'asc')
                ->offset($offset)
                ->limit($batchSize)
                ->select('p.*', 'pm.meta_value as offers_ids')
                ->get();

            // If no more offers, break
            if ($offers->isEmpty()) {
                break;
            }

            // Get all meta data for these offers
            $postIds = $offers->pluck('ID');
            $allMetas = DB::table('wp_postmeta')
                ->whereIn('post_id', $postIds)
                ->get()
                ->groupBy('post_id');

            // Process each offer
            foreach ($offers as $offer) {
                $postmeta = $allMetas[$offer->ID] ?? collect();
                
                // Convert meta to array
                $meta = [];
                foreach ($postmeta as $m) {
                    $meta[$m->meta_key] = $m->meta_value;
                }

                // Get category and merchant IDs
                $categoryId = null;
                $merchantId = null;
                
                if (!empty($meta['cusotm_img_url'])) {
                    $urlParts = parse_url($meta['cusotm_img_url']);
                    if (!empty($urlParts['query'])) {
                        parse_str($urlParts['query'], $queryParams);
                        if (!empty($queryParams['categoryId'])) {
                            $categoryId = DB::table('categories')
                                ->where('kelkoo_category_id', $queryParams['categoryId'])
                                ->value('id');
                        }
                        if (!empty($queryParams['merchantId'])) {
                            $merchantId = DB::table('merchants')
                                ->where('kelkoo_merchant_id', $queryParams['merchantId'])
                                ->value('id');
                        }
                    }
                }
                
                    // Skip if category ID is missing
                    if (empty($categoryId)) {
                        \Log::info("Skipped offer ID {$offer->ID} due to missing category ID.");
                        continue;
                    }
                    
                    // Skip if merchant ID is missing
                    if (empty($merchantId)) {
                        \Log::info("Skipped offer ID {$offer->ID} due to missing merchant ID.");
                        continue;
                    }

                // Create/update product
                Product::updateOrCreate(
                    ['offer_id' => $offer->offers_ids],
                    [
                        'title' => $offer->post_title,
                        'description' => $offer->post_content,
                        'slug' => $offer->post_name,
                        'keyword' => $offer->post_title,
                        'meta_title' => $offer->post_title,
                        'meta_description' => $offer->post_content,
                        'country' => $meta['offers_country'] ?? null,
                        'ean' => $meta['offers_ean_code'] ?? null,
                        'image_url' => $meta['cusotm_img_url'] ?? null,
                        'price' => $meta['price'] ?? null,
                        'go_url' => $meta['custom_go_url'] ?? null,
                        'availability_status' => 'in_stock',
                        'currency' => $meta['price_cuurency'] ?? null,
                        'offer_id' => $offer->offers_ids,
                        'merchant_id' => $merchantId,
                        'category_id' => $categoryId,
                    ]
                );

                // Mark as processed
                DB::table('wp_posts')
                    ->where('ID', $offer->ID)
                    ->update(['comment_count' => 1]);

                $totalProcessed++;
            }

            $this->info("Processed batch: " . ($offset + $batchSize) . " | Total: $totalProcessed");
               \Log::info("Processed batch: " . ($offset + $batchSize) . " | Total: $totalProcessed");

            
            // Move to next batch
            $offset += $batchSize;

        } while (true);

        $this->info("Migration completed! Total processed: $totalProcessed");
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class MigrateTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate one offer post from wp_posts and wp_postmeta into products table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Step 1: Fetch WordPress offer post and its metadata
        $offers = DB::table('wp_posts')
            ->join('wp_postmeta', 'wp_posts.ID', '=', 'wp_postmeta.post_id')
            ->select('wp_posts.*', 'wp_postmeta.meta_key', 'wp_postmeta.meta_value')
            ->where('wp_posts.post_type', 'offers')
            ->where('wp_posts.ID',374 ) // change or remove for multiple
            ->get();

        // Step 2: Organize post data and metadata
        $postData = [];
        $metaData = [];

        foreach ($offers as $row) {
            if (empty($postData)) {
                $postData = [
                    'post_id'      => $row->ID,
                    'title'        => $row->post_title,
                    'description'  => $row->post_content,
                    'slug'         => $row->post_name,
                    'status'       => $row->post_status,
                    'created_at'   => $row->post_date,
                    'updated_at'   => $row->post_modified,
                    'url'          => $row->guid,
                ];
            }

            $metaData[$row->meta_key] = $row->meta_value;
        }

        // Step 3: Merge post and metadata
        $productData = array_merge($postData, $metaData);

        $categoryExternalId = null;

        if (!empty($productData['cusotm_img_url'])) {
            $imgUrl = $productData['cusotm_img_url'];
            $urlParts = parse_url($imgUrl);

            if (!empty($urlParts['query'])) {
                parse_str($urlParts['query'], $queryParams);
                $categoryExternalId = $queryParams['categoryId'] ?? null;
            }
        }

        $categoryId = null;

        if ($categoryExternalId) {
            $categoryId = DB::table('categories')
                ->where('kelkoo_category_id', $categoryExternalId)
                ->value('id');
        }

        $productData['category_id'] = $categoryId;

        // Step 7: Insert into the products table
        // Product::create($productData);

        // Step 8: Output the result
        $this->info('Offer imported to products table:');
        $this->line(json_encode($productData, JSON_PRETTY_PRINT));
    }
}

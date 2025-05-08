<?php

namespace App\Services;

use Carbon\Carbon;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Support\Facades\Route;

class SitemapService
{
    public function generateSitemap()
    {
        $sitemapPath = public_path('sitemap.xml');

        // Start with crawling the site to get base URLs
        $sitemap = SitemapGenerator::create(config('app.url'))
            ->configureCrawler(function ($crawler) {
                $crawler->setMaximumDepth(3); // Control the crawling depth
            })
            ->getSitemap(); // Get the basic sitemap generated

        // Fetch dynamic content (e.g., Posts, Pages, or other models)
        $models = [
            Merchant::class, // Add other models you want to include
            Brand::class, // Example for pages
            Product::class, // Example for products
            Category::class, // Example for products
        ];

        foreach ($models as $model) {
            $items = $model::all(); // Fetch all records from the model

            foreach ($items as $item) {
                $url = route('admin.merchant', ['slug' => $item->slug]); // Dynamically generate the URL based on model data

                $sitemap->add(
                    Url::create($url)
                        ->setLastModificationDate($item->updated_at) // Set last modified date
                        ->setPriority(0.8) // Optional: Set priority dynamically if needed
                );
            }
        }

        // Write the sitemap to the file
        $sitemap->writeToFile($sitemapPath);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for all key models.';

    public function handle()
    {
        $this->info("📦 Generating sitemaps...");
    
        $models = [
            'products' => [Product::class, 'offers.product', 'slug'],
            'categories' => [Category::class, 'category.offers', 'slug'],
            'brands' => [Brand::class, 'brands.offers', 'slug'],
            'merchants' => [Merchant::class, 'merchant.offers', 'slug'], // update route if needed
        ];
    
        $sitemapIndex = SitemapIndex::create();
        $storagePath = public_path('sitemaps');
    
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0775, true);
        }
    
        foreach ($models as $key => [$modelClass, $routeName, $slugField]) {
            $this->info("🔄 Checking {$key}...");
    
            // Check if sitemap files exist and were modified today
            $existingFiles = glob($storagePath . "/sitemap-{$key}-*.xml");
            $skipGeneration = false;
    
            foreach ($existingFiles as $file) {
                if (date('Y-m-d', filemtime($file)) === date('Y-m-d')) {
                    $skipGeneration = true;
                    break;
                }
            }
    
            if ($skipGeneration) {
                $this->info("⏩ Skipping {$key} sitemap generation — files already generated today.");
                // Add existing sitemap files to index
                foreach ($existingFiles as $file) {
                    $filename = basename($file);
                    $sitemapIndex->add("sitemaps/{$filename}");
                }
                continue;
            }
    
            $this->info("🔄 Generating sitemap for {$key}...");
    
            $chunkCount = 0;
    
            $modelClass::whereNotNull($slugField)->chunk(10000, function ($items) use (&$chunkCount, $key, $routeName, $slugField, $storagePath, $sitemapIndex) {
                $chunkCount++;
                $sitemapFileName = "sitemap-{$key}-{$chunkCount}.xml";
                $sitemapFilePath = $storagePath . '/' . $sitemapFileName;
    
                $sitemap = Sitemap::create();
    
                foreach ($items as $item) {
                    try {
                        $url = route($routeName, [$slugField => $item->{$slugField}]);
    
                        $sitemap->add(
                            Url::create($url)
                                ->setLastModificationDate($item->updated_at ?? now())
                                ->setPriority(0.8)
                        );
                    } catch (\Throwable $e) {
                        $this->warn("❌ Skipping {$key} ID {$item->id} - " . $e->getMessage());
                        continue;
                    }
                }
    
                $sitemap->writeToFile($sitemapFilePath);
                $sitemapIndex->add("sitemaps/{$sitemapFileName}");
            });
    
            $this->info("✅ Sitemap for {$key} generated with {$chunkCount} file(s).");
        }
    
        $sitemapIndex->writeToFile(public_path('sitemap.xml'));
    
        $this->info("🎉 All sitemaps generated successfully.");
    }
    
}

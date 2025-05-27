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
    
        // Base crawl
        $sitemap = SitemapGenerator::create(config('app.url'))
            ->configureCrawler(function ($crawler) {
                $crawler->setMaximumDepth(3);
            })
            ->getSitemap();
    
        // Define models and their route or URL structure
        $models = [
            'merchants' => Merchant::class,
            'brands' => Brand::class,
            'products' => Product::class,
            'categories' => Category::class,
        ];
    
        foreach ($models as $type => $model) {
            $items = $model::all();
    
            foreach ($items as $item) {
                // Decide URL based on model type
                switch ($type) {
                    case 'merchants':
                        $url = route('merchant.offers', ['name' => $item->slug]);
                        break;
    
                    case 'brands':
                        $url = route('brands.offers', ['name' => $item->slug]);
                        break;
    
                    case 'products':
                        $url = route('offers.product', ['name' => $item->slug]);
                        break;
    
                    case 'categories':
                        $url = route('category.offers', ['name' => $item->slug]);
                        break;
    
                    default:
                        continue 2; // skip unknown type
                }
    
                $sitemap->add(
                    Url::create($url)
                        ->setLastModificationDate($item->updated_at ?? now())
                        ->setPriority(0.8)
                );
            }
        }
    
        // Save sitemap
        $sitemap->writeToFile($sitemapPath);
    }
    
}

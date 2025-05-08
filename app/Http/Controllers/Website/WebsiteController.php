<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shortcode;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Jobs\FetchKelkooProduct;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $query = $request->input('query');
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;
        $cacheKey = "kelkoo_offers_page_{$page}_{$country}_query_" . md5($request->input('query'));

        // Cache response for 1 hour
        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $query) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";
            $params = [
                'country' => $country,
                'additionalFields' => 'merchantLogoUrl,description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                'pageSize' => 12,
                'page' => $page,
            ];

            if ($query) {
                $params['query'] = $query;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });


        $offers = $data['offers'] ?? [];
        FetchKelkooProduct::dispatch($data);

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.index', [
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages,
        ]);
    }


    public function brands(Request $request)
    {
        $brands = Brand::orderBy('name')->paginate(20); // Optional: Adjust page size
        return view('website.brands', [
            'brands' => $brands
        ]);
    }

    public function brandProducts(Request $request, $name)
    {
        $brand = Brand::where('slug', $name)->firstOrFail();
        $kelkooBrandId = $brand->kelkoo_brand_id;
        $kelkooBrandName = $brand->name;
        $kelkooBrandslugName = $brand->slug;
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $cacheKey = "kelkoo_offers_brand_{$kelkooBrandId}_page_{$page}_country_{$country}";

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $kelkooBrandId) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

            $params = [
                'country' => $country,
                'pageSize' => 12,
                'page' => $page,
                'additionalFields' => 'description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                'filterBy' => 'brandId:' . $kelkooBrandId,  // Corrected syntax here
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        $offers = $data['offers'] ?? [];
        FetchKelkooProduct::dispatch($data);

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.brandsproducts', [
            'kelkooBrandName' => $kelkooBrandName,
            'brandSlug' => $kelkooBrandslugName,
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages - 1,
        ]);
    }


    public function categoryProducts(Request $request, $name)
    {
        $category = Category::where('slug', $name)->firstOrFail();
        $kelkooCatId = $category->kelkoo_category_id;
        $kelkooCategoryName = $category->name;
        $kelkooCatslugName = $category->slug;
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $cacheKey = "kelkoo_offers_category_{$kelkooCatId}_page_{$page}_country_{$country}";

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $kelkooCatId) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

            $params = [
                'country' => $country,
                'pageSize' => 12,
                'page' => $page,
                'additionalFields' => 'description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                'filterBy' => 'categoryId:' . $kelkooCatId,  // Corrected syntax here
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        $offers = $data['offers'] ?? [];

        FetchKelkooProduct::dispatch($data);

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.categoryproducts', [
            'kelkooCategoryName' => $kelkooCategoryName,
            'kelkooCatslugName' => $kelkooCatslugName,
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages - 1,
        ]);
    }



    public function merchantProducts(Request $request, $name)
    {
        $merchant = Merchant::where('slug', $name)->firstOrFail();
        $kelkoomerchantId = $merchant->kelkoo_merchant_id;
        $kelkooMerchantName = $merchant->name;
        $kelkooMerchantslug = $merchant->slug;
        $kelkooMerchantmetatitle = $merchant->meta_title;
        $kelkooMerchantkeyword = $merchant->keyword;
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $cacheKey = "kelkoo_offers_merchant_{$kelkoomerchantId}_page_{$page}_country_{$country}";

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $kelkoomerchantId) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

            $params = [
                'country' => $country,
                'pageSize' => 12,
                'page' => $page,
                'additionalFields' => 'description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
                'filterBy' => 'merchantId:' . $kelkoomerchantId,  // Corrected syntax here
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        $offers = $data['offers'] ?? [];

        FetchKelkooProduct::dispatch($data);

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.merchantsproducts', [
            'kelkooMerchantName' => $kelkooMerchantName,
            'kelkooMerchantslug' => $kelkooMerchantslug,
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages - 1,
        ]);
    }


    public function SingleBrandProduct(Request $request, $name)
    {
        $product = Product::where('slug', $name)->firstOrFail();
        $producteanId = $product->ean;
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        $cacheKey = "kelkoo_single_offers_product_{$producteanId}_page_{$page}_country_{$country}";

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $producteanId) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

            $params = [
                'country' => $country,
                'pageSize' => 12,
                'page' => $page,
                'additionalFields' => 'description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl',
                'filterBy' => 'codeEan:' . $producteanId,  // Corrected syntax here
                'sortDirection' => 'asc',
                'sortBy' => 'price'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        $offers = $data['offers'] ?? [];
        // FetchKelkooProduct::dispatch($country, $page, $request->input('query'), $token);

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        // Extract all prices
        $prices = array_column($offers, 'price');

        $minPrice = min($prices);
        $maxPrice = max($prices);


        // dd($offers,$page, $hasNextPage, $totalPages );

        return view('website.product', [
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'product' => $product
        ]);
    }


    public function Merchants(Request $request)
    {
        $brands = Merchant::paginate(20); // Optional: Adjust page size
        return view('website.merchants', [
            'brands' => $brands
        ]);
    }
}

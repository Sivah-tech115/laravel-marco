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
use App\Traits\GeneratesShortTitle;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class WebsiteController extends Controller
{
    use GeneratesShortTitle;

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

        // FetchKelkooProduct::dispatch($data);

        foreach ($offers as $offer) {

            $kelkooBrandId = data_get($offer, 'brand.id');
            $kelkooCategoryId = data_get($offer, 'category.id');
            $kelkooMerchantId = data_get($offer, 'merchant.id');

            $brandName = data_get($offer, 'brand.name', '');
            $categoryName = data_get($offer, 'category.name', '');
            $merchantName = data_get($offer, 'merchant.name', '');

            // // Skip the entire offer if any required ID is missing
            // if (is_null($kelkooBrandId) || is_null($kelkooCategoryId) || is_null($kelkooMerchantId)) {
            //     continue;
            // }


            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $kelkooBrandId],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $kelkooCategoryId],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $kelkooMerchantId],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );

            $productTitle = $offer['title'] ?? '';
            $productDescription = data_get($offer, 'description', null);
            $shortTitle = $this->generateShortTitle($productTitle, $brandName);


            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $productTitle,
                    'slug' => Str::slug($productTitle),
                    'description' => $productDescription,
                    'meta_title' => $productTitle,
                    'meta_description' => $productDescription ? Str::words($productDescription, 30, '...') : null,
                    'keyword' => $shortTitle,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => data_get($offer, 'code.ean'),
                    'image_url' => data_get($offer, 'images.0.url'),
                    'zoom_image_url' => data_get($offer, 'images.0.zoomUrl'),
                    'offer_url' => data_get($offer, 'offerUrl.landingUrl'),
                    'go_url' => data_get($offer, 'goUrl'),
                    'estimated_cpc' => data_get($offer, 'estimatedCpc'),
                    'estimated_mobile_cpc' => data_get($offer, 'estimatedMobileCpc'),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }



        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.index', [
            'products' => $offers,
            'page' => $page,
            'hasNextPage' => $hasNextPage,
            'totalPages' => $totalPages - 1,
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
        // FetchKelkooProduct::dispatch($data);

        foreach ($offers as $offer) {

            $kelkooBrandId = data_get($offer, 'brand.id');
            $kelkooCategoryId = data_get($offer, 'category.id');
            $kelkooMerchantId = data_get($offer, 'merchant.id');

            $brandName = data_get($offer, 'brand.name', '');
            $categoryName = data_get($offer, 'category.name', '');
            $merchantName = data_get($offer, 'merchant.name', '');

            // // Skip the entire offer if any required ID is missing
            // if (is_null($kelkooBrandId) || is_null($kelkooCategoryId) || is_null($kelkooMerchantId)) {
            //     continue;
            // }


            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $kelkooBrandId],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $kelkooCategoryId],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $kelkooMerchantId],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );

            $productTitle = $offer['title'] ?? '';
            $productDescription = data_get($offer, 'description', null);
            $shortTitle = $this->generateShortTitle($productTitle, $brandName);


            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $productTitle,
                    'slug' => Str::slug($productTitle),
                    'description' => $productDescription,
                    'meta_title' => $productTitle,
                    'meta_description' => $productDescription ? Str::words($productDescription, 30, '...') : null,
                    'keyword' => $shortTitle,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => data_get($offer, 'code.ean'),
                    'image_url' => data_get($offer, 'images.0.url'),
                    'zoom_image_url' => data_get($offer, 'images.0.zoomUrl'),
                    'offer_url' => data_get($offer, 'offerUrl.landingUrl'),
                    'go_url' => data_get($offer, 'goUrl'),
                    'estimated_cpc' => data_get($offer, 'estimatedCpc'),
                    'estimated_mobile_cpc' => data_get($offer, 'estimatedMobileCpc'),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }

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
        $selectedBrand = $request->query('brand');
        $selectedMerchant = $request->query('merchant');

        $kelkooBrandIdsel = null;
        $kelkooMerchantIdsel = null;

        if ($selectedBrand) {
            $kelkooBrand = Brand::where('slug', $selectedBrand)->first();
            $kelkooBrandIdsel = $kelkooBrand?->kelkoo_brand_id;
        }

        if ($selectedMerchant) {
            $kelkooMerchant = Merchant::where('slug', $selectedMerchant)->first();
            $kelkooMerchantIdsel = $kelkooMerchant?->kelkoo_merchant_id;
        }

        $category = Category::where('slug', $name)->firstOrFail();
        $kelkooCatId = $category->kelkoo_category_id;
        $kelkooCategoryName = $category->name;
        $kelkooCatslugName = $category->slug;
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);
        $categoryId = $category->id;

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }
        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        // Caching key logic
        $cacheKey = "kelkoo_offers_category_{$kelkooCatId}_page_{$page}_country_{$country}";
        if ($kelkooBrandIdsel) {
            $cacheKey .= "_brand_{$kelkooBrandIdsel}";
        }
        if ($kelkooMerchantIdsel) {
            $cacheKey .= "_merchant_{$kelkooMerchantIdsel}";
        }

        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $kelkooCatId, $kelkooBrandIdsel, $kelkooMerchantIdsel) {
            // $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";

            // $params = [
            //     'country' => $country,
            //     'pageSize' => 12,
            //     'page' => $page,
            //     'additionalFields' => 'description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
            //     'filterBy' => 'categoryId:' . $kelkooCatId,  // Corrected syntax here
            // ];

            if ($kelkooBrandIdsel && $kelkooMerchantIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=brandId:{$kelkooBrandIdsel}&filterBy=merchantId:{$kelkooMerchantIdsel}&pageSize=12&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($kelkooBrandIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=brandId:{$kelkooBrandIdsel}&pageSize=12&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($kelkooMerchantIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=merchantId:{$kelkooMerchantIdsel}&pageSize=12&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } else {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&pageSize=12&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        $offers = $data['offers'] ?? [];

        // FetchKelkooProduct::dispatch($data);

        foreach ($offers as $offer) {

            $kelkooBrandId = data_get($offer, 'brand.id');
            $kelkooCategoryId = data_get($offer, 'category.id');
            $kelkooMerchantId = data_get($offer, 'merchant.id');

            $brandName = data_get($offer, 'brand.name', '');
            $categoryName = data_get($offer, 'category.name', '');
            $merchantName = data_get($offer, 'merchant.name', '');

            // Skip the entire offer if any required ID is missing
            // if (is_null($kelkooBrandId) || is_null($kelkooCategoryId) || is_null($kelkooMerchantId)) {
            //     continue;
            // }


            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $kelkooBrandId],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $kelkooCategoryId],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $kelkooMerchantId],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );

            $productTitle = $offer['title'] ?? '';
            $productDescription = data_get($offer, 'description', null);
            $shortTitle = $this->generateShortTitle($productTitle, $brandName);


            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $productTitle,
                    'slug' => Str::slug($productTitle),
                    'description' => $productDescription,
                    'meta_title' => $productTitle,
                    'meta_description' => $productDescription ? Str::words($productDescription, 30, '...') : null,
                    'keyword' => $shortTitle,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => data_get($offer, 'code.ean'),
                    'image_url' => data_get($offer, 'images.0.url'),
                    'zoom_image_url' => data_get($offer, 'images.0.zoomUrl'),
                    'offer_url' => data_get($offer, 'offerUrl.landingUrl'),
                    'go_url' => data_get($offer, 'goUrl'),
                    'estimated_cpc' => data_get($offer, 'estimatedCpc'),
                    'estimated_mobile_cpc' => data_get($offer, 'estimatedMobileCpc'),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }

        $brands = DB::table('products')
            ->select('brand_id')
            ->where('category_id', $categoryId)
            ->whereNotNull('brand_id')
            ->distinct()
            ->limit(100)
            ->pluck('brand_id');

        $brandList = DB::table('brands')
            ->whereIn('id', $brands)
            ->get();

        $merchants = DB::table('products')
            ->select('merchant_id')
            ->where('category_id', $categoryId)
            ->whereNotNull('merchant_id')
            ->distinct()
            ->limit(100)
            ->pluck('merchant_id');

        $merchantList = DB::table('merchants')
            ->whereIn('id', $merchants)
            ->get();

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        // dd($offers,$page, $hasNextPage, $totalPages );

        return view('website.categoryproducts', [
            'brandList' => $brandList,
            'merchantList' => $merchantList,
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

        // FetchKelkooProduct::dispatch($data);

        foreach ($offers as $offer) {

            $kelkooBrandId = data_get($offer, 'brand.id');
            $kelkooCategoryId = data_get($offer, 'category.id');
            $kelkooMerchantId = data_get($offer, 'merchant.id');

            $brandName = data_get($offer, 'brand.name', '');
            $categoryName = data_get($offer, 'category.name', '');
            $merchantName = data_get($offer, 'merchant.name', '');

            // Skip the entire offer if any required ID is missing
            // if (is_null($kelkooBrandId) || is_null($kelkooCategoryId) || is_null($kelkooMerchantId)) {
            //     continue;
            // }


            $brand = Brand::updateOrCreate(
                ['kelkoo_brand_id' => $kelkooBrandId],
                [
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'meta_title' => $brandName,
                    'keyword' => $brandName,
                ]
            );

            $category = Category::updateOrCreate(
                ['kelkoo_category_id' => $kelkooCategoryId],
                [
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'meta_title' => $categoryName,
                    'keyword' => $categoryName,
                ]
            );

            $merchant = Merchant::updateOrCreate(
                ['kelkoo_merchant_id' => $kelkooMerchantId],
                [
                    'name' => $merchantName,
                    'image' => data_get($offer, 'merchant.logoUrl', ''),
                    'meta_title' => $merchantName,
                    'keyword' => $merchantName,
                ]
            );

            $productTitle = $offer['title'] ?? '';
            $productDescription = data_get($offer, 'description', null);
            $shortTitle = $this->generateShortTitle($productTitle, $brandName);


            Product::updateOrCreate(
                ['offer_id' => $offer['offerId']],
                [
                    'title' => $productTitle,
                    'slug' => Str::slug($productTitle),
                    'description' => $productDescription,
                    'meta_title' => $productTitle,
                    'meta_description' => $productDescription ? Str::words($productDescription, 30, '...') : null,
                    'keyword' => $shortTitle,
                    'price' => $offer['price'],
                    'price_without_rebate' => $offer['priceWithoutRebate'] ?? null,
                    'rebate_percentage' => $offer['rebatePercentage'] ?? null,
                    'delivery_cost' => $offer['deliveryCost'] ?? null,
                    'total_price' => $offer['totalPrice'],
                    'currency' => $offer['currency'],
                    'availability_status' => $offer['availabilityStatus'] ?? null,
                    'time_to_deliver' => $offer['timeToDeliver'] ?? null,
                    'ean' => data_get($offer, 'code.ean'),
                    'image_url' => data_get($offer, 'images.0.url'),
                    'zoom_image_url' => data_get($offer, 'images.0.zoomUrl'),
                    'offer_url' => data_get($offer, 'offerUrl.landingUrl'),
                    'go_url' => data_get($offer, 'goUrl'),
                    'estimated_cpc' => data_get($offer, 'estimatedCpc'),
                    'estimated_mobile_cpc' => data_get($offer, 'estimatedMobileCpc'),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'merchant_id' => $merchant->id,
                ]
            );
        }

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
        try {
            $mainProduct = Product::where('slug', $name)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $query = str_replace('-', ' ', $name);
            return redirect()->route('home', ['query' => $query]);
        }
        $producteanId = $mainProduct->ean;

        $productsToUpdate = Product::where('ean', 'LIKE', '%' . $producteanId . '%')
            ->whereNull('barcode_img')
            ->orWhere('barcode_img', '')
            ->get();

        if ($productsToUpdate->isNotEmpty()) {
            // $token = env('EAN_SEARCH_API_KEY');
            $token = '05939f5296400025856649d3526cd919c2e2dc96';
            $width = 400; // high resolution
            $height = 150;
            $apiurl = "https://api.ean-search.org/api?token={$token}&op=barcode-image&ean={$producteanId}&width={$width}&height={$height}";
            $response = Http::get($apiurl);

            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                $barcodeBase64 = (string) $xml->product->barcode;

                foreach ($productsToUpdate as $product) {
                    $product->barcode_img = $barcodeBase64;
                    $product->save();
                }
                $mainProduct->refresh();
            }
        }

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
            'product' => $mainProduct
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

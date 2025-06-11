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
use App\Services\KelkooProductService;
use App\Models\CategoryBrandMerchant;
use App\Models\QueryBrandMerchant;
use Carbon\Carbon;

class WebsiteController extends Controller
{
    use GeneratesShortTitle;

    protected $kelkooService;

    public function __construct(KelkooProductService $kelkooService)
    {
        $this->kelkooService = $kelkooService;
    }

    public function index(Request $request)
    {
        $shortcode = Shortcode::first();
        $page = $request->input('page', 1);

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }

        $query = $request->input('query');
        $brandquery = $request->input('brand');

        $kelkooBrandIdsel = null; // Initialize the variable

        if ($brandquery) {
            $kelkooBrand = Brand::where('slug', $brandquery)->first();
            $kelkooBrandIdsel = $kelkooBrand?->kelkoo_brand_id;
        }

        // dd($kelkooBrandIdsel);

        $token = $shortcode->api_key;
        $country = $shortcode->countryName;
        $cacheKey = "kelkoo_offers_page_{$page}_{$country}_query_" . md5($request->input('query'));

        if ($kelkooBrandIdsel) {
            $cacheKey .= "_brand_{$kelkooBrandIdsel}";
        }

        // Cache response for 1 hour
        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $query, $kelkooBrandIdsel) {
            $pageSize = 50;

            if ($kelkooBrandIdsel && $query) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&query={$query}&filterBy=brandId:{$kelkooBrandIdsel}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($kelkooBrandIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=brandId:{$kelkooBrandIdsel}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($query) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&query={$query}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } else {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            }


            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);

            // dd($response);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }


            return $response->json();
        });

        $offers = $data['offers'] ?? [];

        if ($query) {
            // Extract brands and merchants from offers
            $brands = collect($offers)->map(function ($offer) {
                return [
                    'id' => data_get($offer, 'brand.id'),
                    'name' => data_get($offer, 'brand.name'),
                ];
            })->unique('id')->filter(fn($b) => $b['id'])->values();

            $merchants = collect($offers)->map(function ($offer) {
                return [
                    'id' => data_get($offer, 'merchant.id'),
                    'name' => data_get($offer, 'merchant.name'),
                ];
            })->unique('id')->filter(fn($m) => $m['id'])->values();

            foreach ($brands as $brand) {
                QueryBrandMerchant::updateOrCreate([
                    'query' => $query,
                    'brand_id' => $brand['id'],
                    'merchant_id' => null,
                ]);
            }

            foreach ($merchants as $merchant) {
                QueryBrandMerchant::updateOrCreate([
                    'query' => $query,
                    'brand_id' => null,
                    'merchant_id' => $merchant['id'],
                ]);
            }

            foreach ($brands as $brand) {
                foreach ($merchants as $merchant) {
                    QueryBrandMerchant::updateOrCreate([
                        'query' => $query,
                        'brand_id' => $brand['id'],
                        'merchant_id' => $merchant['id'],
                    ]);
                }
            }

            $brandList = Brand::whereIn(
                'kelkoo_brand_id',
                QueryBrandMerchant::where('query', $query)
                    ->whereNotNull('brand_id')
                    ->pluck('brand_id')
            )->get();

            $merchantList = Merchant::whereIn(
                'kelkoo_merchant_id',
                QueryBrandMerchant::where('query', $query)
                    ->whereNotNull('merchant_id')
                    ->pluck('merchant_id')
            )->get();
        }



        foreach ($offers as $offer) {
            $this->kelkooService->saveOffer($offer);
        }

        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        return view('website.index', [
            'query' => $query,  // Pass the query to the view
            'brandList' => $query ? $brandList : null,  // Pass brandList only if query is present
            'merchantList' => $query ? $merchantList : null,  // Pass merchantList only if query is present
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
            $this->kelkooService->saveOffer($offer);
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

            $pageSize = 50;
            // $params = [
            //     'country' => $country,
            //     'pageSize' => 12,
            //     'page' => $page,
            //     'additionalFields' => 'description,codeEan,brandId,brandName,offerUrlLandingUrl,merchantName,categoryName',
            //     'filterBy' => 'categoryId:' . $kelkooCatId,  // Corrected syntax here
            // ];

            if ($kelkooBrandIdsel && $kelkooMerchantIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=brandId:{$kelkooBrandIdsel}&filterBy=merchantId:{$kelkooMerchantIdsel}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($kelkooBrandIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=brandId:{$kelkooBrandIdsel}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } elseif ($kelkooMerchantIdsel) {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&filterBy=merchantId:{$kelkooMerchantIdsel}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
            } else {
                $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country={$country}&filterBy=categoryId:{$kelkooCatId}&pageSize={$pageSize}&page={$page}&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl";
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


        // // Check if there's any entry for today
        // $hasTodayData = CategoryBrandMerchant::where('category_id', $categoryId)
        //     ->whereDate('created_at', Carbon::today())
        //     ->exists();

        // // If no entries were created today, delete old ones
        // if (!$hasTodayData) {
        //     CategoryBrandMerchant::where('category_id', $categoryId)->delete();
        // }

        // Extract brands and merchants from offers
        $brands = collect($offers)->map(function ($offer) {
            return [
                'id' => data_get($offer, 'brand.id'),
                'name' => data_get($offer, 'brand.name'),
            ];
        })->unique('id')->filter(fn($b) => $b['id'])->values();

        $merchants = collect($offers)->map(function ($offer) {
            return [
                'id' => data_get($offer, 'merchant.id'),
                'name' => data_get($offer, 'merchant.name'),
            ];
        })->unique('id')->filter(fn($m) => $m['id'])->values();

        foreach ($brands as $brand) {
            CategoryBrandMerchant::updateOrCreate([
                'category_id' => $category->id,
                'brand_id' => $brand['id'],
                'merchant_id' => null,
            ]);
        }

        foreach ($merchants as $merchant) {
            CategoryBrandMerchant::updateOrCreate([
                'category_id' => $category->id,
                'brand_id' => null,
                'merchant_id' => $merchant['id'],
            ]);
        }

        foreach ($brands as $brand) {
            foreach ($merchants as $merchant) {
                CategoryBrandMerchant::updateOrCreate([
                    'category_id' => $category->id,
                    'brand_id' => $brand['id'],
                    'merchant_id' => $merchant['id'],
                ]);
            }
        }

        $brandList = Brand::whereIn(
            'kelkoo_brand_id',
            CategoryBrandMerchant::where('category_id', $category->id)
                ->whereNotNull('brand_id')
                ->pluck('brand_id')
        )->get();

        $merchantList = Merchant::whereIn(
            'kelkoo_merchant_id',
            CategoryBrandMerchant::where('category_id', $category->id)
                ->whereNotNull('merchant_id')
                ->pluck('merchant_id')
        )->get();


        // FetchKelkooProduct::dispatch($data);

        foreach ($offers as $offer) {
            $this->kelkooService->saveOffer($offer);
        }


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
            $this->kelkooService->saveOffer($offer);
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
            // Fetch the product using the offer_id from the request
            $mainProduct = Product::where('offer_id', $request->offer_id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            // If the product is not found, redirect to the home page with a search query
            $query = str_replace('-', ' ', $name);
            return redirect()->route('home', ['query' => $query]);
        }

        $producteanId = $mainProduct->ean;

        // If EAN exists, attempt to fetch barcode
        if ($producteanId) {
            $this->fetchBarcodeImage($producteanId);
        }

        // Retrieve the shortcode settings
        $shortcode = Shortcode::first();

        if (!$shortcode || !$shortcode->api_key) {
            abort(403, 'API token not found.');
        }

        $token = $shortcode->api_key;
        $country = $shortcode->countryName;

        // Pagination handling
        $page = $request->input('page', 1);

        // Cache key based on product offer ID, page number, and country
        $cacheKey = "kelkoo_single_offers_product_{$request->offer_id}_page_{$page}_country_{$country}";

        // Fetch the offers using cache
        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($token, $country, $page, $request) {
            $url = "https://api.kelkoogroup.net/publisher/shopping/v2/search/offers";
            $params = [
                'country' => $country,
                'filterBy' => 'offerId:' . $request->offer_id,  // Correctly filter by offer ID
                'additionalFields' => 'description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl',

            ];

            // Fetch the data from the Kelkoo API with Authorization header
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url, $params);

            // Check for API response success
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from Kelkoo API.');
            }

            return $response->json();
        });

        // Extract offers from the response data
        $offers = $data['offers'] ?? [];
        $hasNextPage = $data['meta']['offers']['nextPage'] ?? false;
        $totalPages = $data['meta']['offers']['totalPages'] ?? 1;

        // Extract prices for further calculations
        $prices = array_column($offers, 'price');
        $minPrice = min($prices);
        $maxPrice = max($prices);

        // Return the view with the product details and offers


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

    /**
     * Fetch barcode image using the EAN.
     *
     * @param string $ean
     */
    private function fetchBarcodeImage($ean)
    {
        // Retrieve products with matching EAN
        $productsToUpdate = Product::where('ean', 'LIKE', '%' . $ean . '%')
            ->whereNull('barcode_img')
            ->orWhere('barcode_img', '')
            ->get();

        // Only proceed if there are products to update
        if ($productsToUpdate->isNotEmpty()) {
            $token = '05939f5296400025856649d3526cd919c2e2dc96';  // Replace with a secure key if needed
            $width = 400; // Set image width
            $height = 150; // Set image height

            // API URL to fetch barcode image
            $apiurl = "https://api.ean-search.org/api?token={$token}&op=barcode-image&ean={$ean}&width={$width}&height={$height}";
            $response = Http::get($apiurl);

            // Check if the API response is successful
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                $barcodeBase64 = (string) $xml->product->barcode;

                // Update all products with the barcode image
                foreach ($productsToUpdate as $product) {
                    $product->barcode_img = $barcodeBase64;
                    $product->save();
                }
            }
        }
    }



    public function showOfferPage(Request $request, $name)
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

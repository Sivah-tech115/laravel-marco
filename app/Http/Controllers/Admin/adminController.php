<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Shortcode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class adminController extends Controller
{
    public function index(Request $request)
    {
        // return view('Admin.Dashboard');
        $data = Merchant::get(); // get the first (and only) row

        return view('Admin.merchant.index', compact('data'));
    }

    public function merchant(Request $request)
    {
        $data = Merchant::get(); // get the first (and only) row
        return view('Admin.merchant.index', compact('data'));
    }

    public function merchantProduct($id)
    {
        $merchant = Merchant::with('products')->findOrFail($id);

        $products = $merchant->products;

        $csvHeader = [
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'brand',
            'google_product_category',
            'fb_product_category',
            'quantity_to_sell_on_facebook',
            'sale_price',
            'sale_price_effective_date',
            'item_group_id',
            'gender',
            'color',
            'size',
            'age_group',
            'material',
            'pattern',
            'shipping',
            'shipping_weight',
            'gtin',
            'video[0].url',
            'video[0].tag[0]',
            'product_tags[0]',
            'product_tags[1]',
            'style[0]'
        ];
        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product->id,
                $product->title ?? '',
                $product->description ?? '',
                $product->availability_status ?? 'in stock',
                $product->condition ?? 'new',
                $product->price ?? '',
                url('/offers/' . $product->slug),
                $product->image_url ?? '',
                $product->brand_name ?? '',
                $product->google_product_category ?? '',
                $product->fb_product_category ?? '',
                $product->quantity_to_sell_on_facebook ?? '',
                $product->sale_price ?? '',
                $product->sale_price_effective_date ?? '',
                $product->offer_id ?? '',
                $product->gender ?? '',
                $product->color ?? '',
                $product->size ?? '',
                $product->age_group ?? '',
                $product->material ?? '',
                $product->pattern ?? '',
                $product->shipping ?? '',
                $product->shipping_weight ?? '',
                $product->ean ?? '',
                $product->videos[0]['url'] ?? '',
                $product->videos[0]['tag'][0] ?? '',
                $product->product_tags[0] ?? '',
                $product->product_tags[1] ?? '',
                $product->style[0] ?? ''
            ];
        }

        $filename = $merchant->name . '_' . $merchant->kelkoo_merchant_id . '.csv';

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $csvHeader);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }


    public function showFacebookFeedLink()
    {
        $products = Product::get();

        $directory = storage_path('app/public/feeds');
        $filePath = 'public/feeds/feed.csv';
        $publicPath = Storage::url('feeds/feed.csv');

        // Ensure the directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true); // recursive = true
        }

        // Delete old file if it exists
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link'
        ];

        foreach ($products as $product) {
            $csvData[] = [
                $product->id,
                $product->title,
                strip_tags($product->description ?? ''),
                'in stock',
                'new',
                number_format($product->price, 2),
                url('/product/' . $product->slug),
                $product->image_url
            ];
        }

        // Write CSV file
        $handle = fopen(storage_path('app/public/feeds/feed.csv'), 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $feedUrl = asset($publicPath);

        return view('Admin.feed', compact('feedUrl'));
    }



    public function AllOffers()
{
    $products = Product::get(); // Show 10 per page
    return view('Admin.products.index', compact('products'));
}


}

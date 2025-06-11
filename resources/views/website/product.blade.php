@extends('website.layouts.main')
@section('title', $product->meta_title ?? 'Offers | Negozi Shop')
@section('meta_title', $product->meta_title ?? 'Offers | Negozi Shop')
@section('meta_description', $product->meta_description ?? 'Offers | Negozi Shop')
@section('meta_keywords', $product->meta_title ?? 'Offers | Negozi Shop')

@section('content')
<?php
// dd($products[0]);
?>
<section class="singlepro_sec">
    <div class="container">
        <div class="pro_colm">
            @isset($products[0]['images'][0]['url'])
            <img src="{{$products[0]['images'][0]['url']}}" alt="">
            @endisset
        </div>
        <div class="pro_colm">
            <h3 class="pro_title">
                {{ $products[0]['title'] ?? 'Product Title' }}
                @isset($products[0]['code']['ean'])
                ({{ $products[0]['code']['ean'] }})
                @endisset
            </h3>
            <h4 class="pro_price">
                @isset($minPrice)
                {{ $minPrice }}
                @endisset
                @isset($products[0]['currency'])
                {{ $products[0]['currency'] }}
                @endisset
                -
                @isset($maxPrice)
                {{ $maxPrice }}
                @endisset
                @isset($products[0]['currency'])
                {{ $products[0]['currency'] }}
                @endisset
            </h4>

            <div class="pro_meta">
                @if(isset($products[0]['category']['id']) && isset($products[0]['category']['name']))
                <a href="{{ route('category.offers', [Str::slug($products[0]['category']['name'])]) }}" class="pro_cat">
                    Category: {{ $products[0]['category']['name'] }}
                </a>
                @endisset

                @if(isset($products[0]['brand']['id']) && isset($products[0]['brand']['name']))
                <a href="{{ route('brands.offers', [Str::slug($products[0]['brand']['name'])]) }}" class="pro_brand">
                    Brand: {{ $products[0]['brand']['name'] }}
                </a>
                @endisset
            </div>

            @isset($product['barcode_img'])
            <div class="pro_barcode">
                <img
                    src="data:image/png;base64,{{ $product['barcode_img'] }}"
                    alt="Barcode Image"
                    style="width: 200px; height: auto;" />
            </div>
            @endisset

            @isset($products[0]['description'])
            <p class="pro_description">{{ $products[0]['description'] }}</p>
            @endisset
        </div>
    </div>
</section>

<section class="offer_sec">
    <div class="container">
        <div class="hero_txt">
            <span>Products</span>
            <h2>All Offers</h2>
        </div>
        <ul class="offer_grid">
            @forelse($products as $product)
            <li>
                <a href="{{ $product['goUrl'] }}" class="pro_title">
                    {{ $product['title'] ?? 'Product Title' }}
                </a>
                <div class="pro_meta">
                    <a href="{{ $product['goUrl'] }}" class="pro_price">
                        Price:
                        @if($product['price'] == $product['priceWithoutRebate'])
                        {{ $product['price'] }} {{$product['currency']}}
                        @else
                        <span class="regular_price">
                            {{ $product['priceWithoutRebate'] }} {{$product['currency']}}
                        </span>
                        <span class="sale_price">
                            {{ $product['price'] }} {{$product['currency']}}
                        </span>
                        @endif
                    </a>
                    <a href="{{ $product['goUrl'] }}" class="pro_delivery">
                        @isset($product['deliveryCost'])
                        Delivery: {{ $product['deliveryCost'] > 0 ? $product['deliveryCost'] . ' ' . $product['currency'] : 'Free Delivery' }}
                        @endisset
                    </a>
                </div>

                <span class="pro_time">
                    @isset($product['timeToDeliver'])
                    Time: {{ $product['timeToDeliver'] }}
                    @else
                    Time: N/A
                    @endisset
                </span>

                @if(!empty($product['merchant']['logoUrl']))
                <a href="{{ $product['goUrl'] }}" class="pro_merchant">
                    <img src="{{ $product['merchant']['logoUrl'] }}" alt="{{ $product['merchant']['name'] ?? 'Merchant' }}">
                </a>
                @else
                <a href="{{ $product['goUrl'] }}" class="pro_merchant">
                    {{ $product['merchant']['name'] ?? 'Merchant' }}
                </a>
                @endif

                <a href="{{ $product['goUrl'] }}" class="btn secondary_btn">See More</a>
            </li>
            @empty
            <p>No products found.</p>
            @endforelse
        </ul>
    </div>
</section>
@endsection
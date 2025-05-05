@extends('website.layouts.main')
@section('title', 'Offers | Negozi Shop')
@section('content')
<section class="singlepro_sec">
    <div class="container">
        <div class="pro_colm">
            <img src="{{ asset('website/assets/images/pasta-maker.webp') }}" alt="">
        </div>
        <div class="pro_colm">
            <h3 class="pro_title">{{$products[0]['title']}} ({{$products[0]['code']['ean']}})</h3>
            <h4 class="pro_price">{{$minPrice}} {{$products[0]['currency']}} - {{ $maxPrice }} {{$products[0]['currency']}}</h4>

            <div class="pro_meta">
                <a href="{{ route('category.offers', [Str::slug($products[0]['category']['name'])]) }}" class="pro_cat">
                    Category: {{ $products[0]['category']['name'] }}
                </a>
                <a href="{{ route('brands.offers', [Str::slug($products[0]['brand']['name'])]) }}" class="pro_brand">
                    Brands: {{$products[0]['brand']['name']}}
                </a>
            </div>

            <p class="pro_descripton">{{$products[0]['description']}}</p>
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
                <a href="{{$product['goUrl']}}" class="pro_title">{{$product['title']}}</a>
                <div class="pro_meta">
                    <a href="{{$product['goUrl']}}" class="pro_price">
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
                    <a href="{{$product['goUrl']}}" class="pro_delivery">
                        Delivery: {{ $product['deliveryCost'] > 0 ? $product['deliveryCost'] . ' ' . $product['currency'] : 'Free Delivery' }}

                    </a>

                </div>
                <span class="pro_time">Time: {{ $product['timeToDeliver'] ?? 'N/A' }}</span>
                <a href="{{$product['goUrl']}}" class="pro_merchant">
                    <img src="{{ $product['merchant']['logoUrl'] ?? '' }}" alt="{{ $product['merchant']['name'] }}">

                </a>
                <a href="{{$product['goUrl']}}" class="btn secondary_btn">See More</a>
            </li>
            @empty
            <p>No products found.</p>
            @endforelse
        </ul>

    </div>
</section>
@endsection
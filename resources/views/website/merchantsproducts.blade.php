@extends('website.layouts.main')
@section('content')
@section('title', $kelkooMerchantName ?? 'Merchants | Negozi Shop')
@section('meta_title', $kelkooMerchantName ?? 'Merchants | Negozi Shop')
@section('meta_keywords', $kelkooMerchantName ?? 'Merchants | Negozi Shop')

<section class="herobanner_sec overlay">
    <div class="container">
        <h1>Merchant</h1>
    </div>
</section>
<section class="productgrid_sec">
    <div class="container">
        <div class="hero_txt">
            <!-- <span>Products</span> -->
            <h2>All Offers for :- {{$kelkooMerchantName}}</h2>
        </div>
        <div class="product_list">
            <ul class="pro_grid">
                @forelse($products as $product)
                <li>
                    <div class="progrid_inner">
                        <div class="pro_img">
                            @if (!empty($product['rebatePercentage']) && $product['rebatePercentage'] > 0)
                            <span class="pro_badge">{{ $product['rebatePercentage'] }}% off</span>
                            @endif

                            @if($product['images'][0]['url'] ?? false)
                            <a href="{{ $product['goUrl'] }}">
                                <img src="{{ $product['images'][0]['url'] }}" alt="{{ $product['title'] }}">
                            </a>
                            @endif

                        </div>
                        <div class="pro_meta">
                            @if (!empty($product['category']['name']))
                            <a href="{{ $product['goUrl'] }}" class="pro_cat">Category: {{ $product['category']['name'] }}</a>
                            @endif

                            @if (!empty($product['brand']['name']))
                            <a href="{{ $product['goUrl'] }}" class="pro_cat">Brands: {{ $product['brand']['name'] }}</a>
                            @endif

                            @if (!empty($product['merchant']['name']))
                            <a href="{{ $product['goUrl'] }}" class="pro_cat">{{ $product['merchant']['name'] }}</a>
                            @endif
                        </div>
                        <h3 class="pro_title"><a href="#">{{ $product['title'] }}</a></h3>
                    </div>
                    <div class="pro_btns">
                        <a href="{{ $product['goUrl'] }}" class="btn">See More</a>
                        <!-- <a href="#" class="btn">Learn More</a> -->
                    </div>
                </li>

                @empty
                <p>No Offers found.</p>
                @endforelse


            </ul>
            @if($totalPages > 1 || $hasNextPage)
            <div class="pagination">
    {{-- Previous --}}
    @if ($page > 1)
        <a href="{{ route('merchant.offers', ['slug' => $kelkooMerchantslug, 'page' => $page - 1, 'query' => request()->query('query')]) }}" class="step prev">
            <i class="fa-solid fa-angle-left"></i>
        </a>
    @else
        <span class="step prev disabled"><i class="fa-solid fa-angle-left"></i></span>
    @endif

    @php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
    @endphp

    {{-- First Page --}}
    @if ($startPage > 1)
        <a href="{{ route('merchant.offers', ['slug' => $kelkooMerchantslug, 'page' => 1, 'query' => request()->query('query')]) }}" class="step">1</a>
        @if ($startPage > 2)
            <span class="step">...</span>
        @endif
    @endif

    {{-- Numbered Pages --}}
    @for ($i = $startPage; $i <= $endPage; $i++)
        <a href="{{ route('merchant.offers', ['slug' => $kelkooMerchantslug, 'page' => $i, 'query' => request()->query('query')]) }}" class="step {{ $page == $i ? 'active' : '' }}">{{ $i }}</a>
    @endfor

    {{-- Last Page --}}
    @if ($endPage < $totalPages)
        @if ($endPage < $totalPages - 1)
            <span class="step">...</span>
        @endif
        <a href="{{ route('merchant.offers', ['slug' => $kelkooMerchantslug, 'page' => $totalPages, 'query' => request()->query('query')]) }}" class="step">{{ $totalPages }}</a>
    @endif

    {{-- Next --}}
    @if ($hasNextPage && $page < $totalPages)
        <a href="{{ route('merchant.offers', ['slug' => $kelkooMerchantslug, 'page' => $page + 1, 'query' => request()->query('query')]) }}" class="step next">
            <i class="fa-solid fa-angle-right"></i>
        </a>
    @else
        <span class="step next disabled"><i class="fa-solid fa-angle-right"></i></span>
    @endif
</div>
@endif


        </div>
    </div>
</section>
@endsection
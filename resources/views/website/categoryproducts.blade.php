@extends('website.layouts.main')
@section('content')
@section('title', $kelkooCategoryName ?? 'Category | Negozi Shop')
@section('meta_title', $kelkooCategoryName ?? 'Category | Negozi Shop')
@section('meta_keywords', $kelkooCategoryName ?? 'Category | Negozi Shop')

<?php

// dd($brandList);
?>
<section class="herobanner_sec overlay">
    <div class="container">
        <h1>Category</h1>
    </div>
</section>


<section class="productgrid_sec">
    <div class="container with_sidebar">
        <div class="hero_txt">
            <!-- <span>Products</span> -->
            <h2>All Offers for :- {{$kelkooCategoryName}}</h2>
        </div>
        {{-- Sidebar --}}
        <div class="filter_sidebar">
            <form id="brandFilterForm" method="GET" action="{{ route('category.offers', ['slug' => $kelkooCatslugName]) }}" class="{{ count($brandList) > 30 ? 'long-brand-list' : '' }}">
                {{-- Filter by Brand --}}
                <div class="filter_cards">
                    <h5>Brands</h5>
                    <div class="filter_box">
                        @foreach($brandList as $brand)
                        @php
                        $isChecked = request()->query('brand') === $brand->slug;
                        @endphp
                        <div class="form-check">
                            <input type="radio" class="form-check-input"
                                name="brand" value="{{ $brand->slug }}"
                                id="brand_{{ $brand->id }}"
                                {{ $isChecked ? 'checked' : '' }}
                                onchange="this.form.submit();">
                            <label class="form-check-label" for="brand_{{ $brand->id }}">{{ $brand->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Filter by Merchant --}}
                <div class="filter_cards">
                    <h5>Merchants</h5>
                    <div class="filter_box">
                        @foreach($merchantList as $merchant)
                        @php
                        $isChecked = request()->query('merchant') === $merchant->slug;
                        @endphp
                        <div class="form-check">
                            <input type="radio" class="form-check-input"
                                name="merchant" value="{{ $merchant->slug }}"
                                id="merchant_{{ $merchant->id }}"
                                {{ $isChecked ? 'checked' : '' }}
                                onchange="this.form.submit();">
                            <label class="form-check-label" for="merchant_{{ $merchant->id }}">{{ $merchant->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
            <div class="filter_footer">
                <a href="{{ route('category.offers', ['slug' => $kelkooCatslugName]) }}" class="btn secondary_btn">Clear Filters</a>
            </div>
        </div>

        <div class="product_list">
            <ul class="pro_grid">
                @forelse($products as $product)
                <li>
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
                    <div class="pro_btns">
                        <a href="{{ $product['goUrl'] }}" class="btn">See More</a>
                        <a href="{{ route('offers.product', [Str::slug($product['title'])]) }}" class="btn" target="_blank" rel="noopener noreferrer">Learn More</a>

                    </div>
                </li>

                @empty
                <p>No Offers found.</p>
                @endforelse


            </ul>
            @php
            $query = request()->except('page'); // get all query params except 'page'
            @endphp


            @if($totalPages > 1 || $hasNextPage)
            <div class="pagination">
                {{-- Previous --}}
                @if($page > 1)
                @php
                $queryString = http_build_query(array_merge($query, ['page' => $page - 1]));
                @endphp
                <a href="{{ url()->current() . '?' . $queryString }}" class="step prev">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
                @else
                <span class="step prev disabled"><i class="fa-solid fa-angle-left"></i></span>
                @endif

                {{-- Numbered pages --}}
                @php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                @endphp

                @for($i = $startPage; $i <= $endPage; $i++)
                    @php
                    $queryString=http_build_query(array_merge($query, ['page'=> $i]));
                    @endphp
                    <a href="{{ url()->current() . '?' . $queryString }}" class="step {{ $page == $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor

                    {{-- Next --}}
                    @if($hasNextPage && $page < $totalPages)
                        @php
                        $queryString=http_build_query(array_merge($query, ['page'=> $page + 1]));
                        @endphp
                        <a href="{{ url()->current() . '?' . $queryString }}" class="step next">
                            <i class="fa-solid fa-angle-right"></i>
                        </a>
                        @else
                        <span class="step next disabled"><i class="fa-solid fa-angle-right"></i></span>
                        @endif
            </div>

            @endisset

        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const brandCheckboxes = document.querySelectorAll('.brand-checkbox');
        brandCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                document.getElementById('brandFilterForm').submit();
            });
        });
    });
</script>

@endsection
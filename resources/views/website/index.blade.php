@extends('website.layouts.main')
@section('content')
@section('title', 'Shop | Negozi Shop')

<section class="herobanner_sec overlay">
    <div class="container">
        <h1>Shop</h1>
    </div>
</section>
<section class="search_section">
    <div class="container">
        <form action="{{ route('home') }}" method="GET" class="search_form">
            <div class="form_group">
                <input type="text" id="search" name="query" value="{{request()->query('query')}}" placeholder="Enter your search term" class="form_control" required>
            </div>
            <button type="submit" class="btn">Search</button>
        </form>
    </div>
</section>
<section class="productgrid_sec">
    <div class="container {{ request()->query('query') ? 'with_sidebar' : '' }}">
        <div class="hero_txt">
            <span>Products</span>
            <h2>All Offers</h2>
        </div>

        <?php
        $query = request()->query('query');
        if ($query) {
        ?>
            <div class="filter_sidebar">
                <form id="brandFilterForm" method="GET" action="{{ route('home') }}" class="{{ count($brandList) > 30 ? 'long-brand-list' : '' }}">

                    {{-- Include query in the form --}}
                    <input type="hidden" name="query" value="{{ request()->query('query') }}">

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

                    <!-- {{-- Filter by Merchant --}}
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
                </div> -->

                </form>
                <div class="filter_footer">
                    <a href="{{ route('home', ['query' => request()->query('query')]) }}" class="btn secondary_btn">Clear Filters</a>
                </div>
            </div>
        <?php
        }
        ?>

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
                        <h3 class="pro_title"><a href="{{ $product['goUrl'] }}">{{ $product['title'] }}</a></h3>
                    </div>
                    <div class="pro_btns">
                        <a href="{{ $product['goUrl'] }}" class="btn">See More</a>
                        <form action="{{ route('offers.product', ['slug' => Str::slug($product['title'])]) }}" method="POST" target="_blank">
                            @csrf
                            <!-- Hidden field for the offer_id -->
                            <input type="hidden" name="offer_id" value="{{ $product['offerId'] }}">

                            <button type="submit" class="btn" rel="noopener noreferrer">Learn More</button>
                        </form>

                    </div>
                </li>

                @empty
                <p>No products found.</p>
                @endforelse


            </ul>
            <div class="pagination">
                {{-- Previous --}}
                @if ($page > 1)
                <a href="{{ route('home', ['brand' => request()->query('brand'), 'query' => request()->query('query'), 'page' => $page - 1]) }}" class="step prev">
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
                <a href="{{ route('home', ['brand' => request()->query('brand'), 'query' => request()->query('query'), 'page' => 1]) }}" class="step">1</a>
                @if ($startPage > 2)
                <span class="step">...</span>
                @endif
                @endif

                {{-- Numbered Pages --}}
                @for ($i = $startPage; $i <= $endPage; $i++)
                    <a href="{{ route('home', ['brand' => request()->query('brand'), 'query' => request()->query('query'), 'page' => $i]) }}" class="step {{ $page == $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor

                    {{-- Last Page --}}
                    @if ($endPage < $totalPages)
                        @if ($endPage < $totalPages - 1)
                        <span class="step">...</span>
                        @endif
                        <a href="{{ route('home', ['brand' => request()->query('brand'), 'query' => request()->query('query'), 'page' => $totalPages]) }}" class="step">{{ $totalPages }}</a>
                        @endif

                        {{-- Next --}}
                        @if ($hasNextPage && $page < $totalPages)
                            <a href="{{ route('home', ['brand' => request()->query('brand'), 'query' => request()->query('query'), 'page' => $page + 1]) }}" class="step next">
                            <i class="fa-solid fa-angle-right"></i>
                            </a>
                            @else
                            <span class="step next disabled"><i class="fa-solid fa-angle-right"></i></span>
                            @endif
            </div>




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
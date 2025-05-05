@extends('website.layouts.main')
@section('title', 'Brands | Negozi Shop')


@section('content')


<section class="herobanner_sec overlay">
    <div class="container">
        <h1>Brands</h1>
    </div>
</section>
<section class="brands_sec">
    <div class="container">
        <div class="brands_list">
            <ul class="brand_grid">
                @foreach($brands as $brand)
                <li>
                    <a href="{{ route('brands.offers', ['name' => $brand->slug]) }}">
                        {{ $brand->name }}
                    </a>
                </li>
                @endforeach
            </ul>

            @php
            $currentPage = $brands->currentPage();
            $lastPage = $brands->lastPage();
            $start = max($currentPage - 2, 1);
            $end = min($start + 4, $lastPage);
            if ($end - $start < 4) {
                $start=max($end - 4, 1);
                }
                @endphp

                <div class="pagination">
                {{-- Previous Page Link --}}
                @if ($brands->onFirstPage())
                <span class="step prev disabled"><i class="fa-solid fa-angle-left"></i></span>
                @else
                <a href="{{ $brands->previousPageUrl() }}" class="step prev"><i class="fa-solid fa-angle-left"></i></a>
                @endif

                {{-- Page Number Links --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page==$currentPage)
                    <span class="step active">{{ $page }}</span>
                    @else
                    <a href="{{ $brands->url($page) }}" class="step">{{ $page }}</a>
                    @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($brands->hasMorePages())
                    <a href="{{ $brands->nextPageUrl() }}" class="step next"><i class="fa-solid fa-angle-right"></i></a>
                    @else
                    <span class="step next disabled"><i class="fa-solid fa-angle-right"></i></span>
                    @endif
        </div>


    </div>
    </div>
</section>


@endsection
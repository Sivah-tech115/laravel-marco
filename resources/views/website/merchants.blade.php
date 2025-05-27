@extends('website.layouts.main')
@section('title', 'Merchants | Negozi Shop')
@section('keywords', 'Merchants | Negozi Shop')

@section('content')

<section class="herobanner_sec overlay">
    <div class="container">
        <h1>Merchants</h1>
    </div>
</section>
<section class="merchants_sec">
    <div class="container">
        <div class="merchants_list">
            <ul class="merchants_grid">
                @foreach($brands as $brand)
                <li>
                    <a href="{{ route('merchant.offers', ['slug' => $brand->slug]) }}">
                        <img src="{{ $brand->image ?: asset('assets/images/noimage.jpg') }}" alt="{{ $brand->name }}">

                        {{ $brand->name }}
                    </a>
                </li>


                @endforeach
            </ul>

            <div class="pagination">
                {{-- Previous Page --}}
                @if ($brands->onFirstPage())
                <span class="step prev disabled"><i class="fa-solid fa-angle-left"></i></span>
                @else
                <a href="{{ $brands->previousPageUrl() }}" class="step prev">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
                @endif

                {{-- First 3 pages --}}
                @for ($i = 1; $i <= min(3, $brands->lastPage()); $i++)
                    <a href="{{ $brands->url($i) }}" class="step {{ $brands->currentPage() == $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor

                    {{-- Ellipsis if current page is far from the end --}}
                    @if ($brands->lastPage() > 4)
                    @if ($brands->currentPage() < $brands->lastPage() - 2)
                        <span class="step">...</span>
                        @endif

                        {{-- Last page --}}
                        <a href="{{ $brands->url($brands->lastPage()) }}" class="step {{ $brands->currentPage() == $brands->lastPage() ? 'active' : '' }}">
                            {{ $brands->lastPage() }}
                        </a>
                        @endif

                        {{-- Next Page --}}
                        @if ($brands->hasMorePages())
                        <a href="{{ $brands->nextPageUrl() }}" class="step next">
                            <i class="fa-solid fa-angle-right"></i>
                        </a>
                        @else
                        <span class="step next disabled"><i class="fa-solid fa-angle-right"></i></span>
                        @endif
            </div>



        </div>
    </div>
</section>
@endsection
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
                    <a href="{{ route('merchant.offers', ['name' => $brand->name]) }}">
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

                {{-- Page Numbers (Max 5 around current page) --}}
                @php
                $start = max($brands->currentPage() - 1, 1);
                $end = min($brands->currentPage() + 1, $brands->lastPage());
                @endphp

                @for ($i = $start; $i <= $end; $i++)
                    <a href="{{ $brands->url($i) }}" class="step {{ $brands->currentPage() == $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor

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
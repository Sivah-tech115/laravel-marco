<!DOCTYPE html>
<html>

<head>
    <title>@yield('title', 'Negozi Shop')</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @if (
    request()->is('merchant/offers/*') ||
    request()->is('category/offers/*') ||
    request()->is('brands/offers/*') ||
    request()->is('offers/*')
    )
    <meta name="title" content="@yield('meta_title', 'Negozi Shop')" />
    <meta name="description" content="@yield('meta_description', 'Negozi Shop')" />
    <meta name="keywords" content="@yield('meta_keywords', 'Negozi Shop')" />

    @else
    <meta name="title" content="{{ setting('meta_title', 'Default title') }}" />
    <meta name="description" content="{{ setting('meta_description', 'Default description') }}" />
    <meta name="keywords" content="{{ setting('meta_keywords', 'Default keywords') }}" />

    @endif

    <link rel="shortcut icon" href="{{ asset('website/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('website/assets/css/style.css') }}">
    {!! App\Models\SeoSetting::first()->header_scripts ?? '' !!}

</head>

<body>


    <div class="wrapper">
        @include('website.layouts.header')
        @yield('content')
        @include('website.layouts.footer')

    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('website/assets/js/script.css') }}"></script>
    <link rel="stylesheet" href="{{ asset('website/assets/css/style.css') }}">
    <script>
        $('.mobile_menu').on('click', function() {
            $('.nav_menu').toggleClass('active');
        });
    </script>
    {!! App\Models\SeoSetting::first()->footer_scripts ?? '' !!}
</body>

</html>
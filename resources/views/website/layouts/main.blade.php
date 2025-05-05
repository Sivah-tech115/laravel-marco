<!DOCTYPE html>
<html>

<head>
    <title>@yield('title', 'Negozi Shop')</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <link rel="shortcut icon" href="{{ asset('website/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('website/assets/css/style.css') }}">
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

</body>

</html>
<!doctype html>
<html lang="en">

<head>
    <title>Negozi shop</title>
    <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 11]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description"
        content="Dasho Bootstrap admin template made using bootstrap 5 and it has huge amount of ready made feature, UI components, pages which completely fulfills any dashboard needs." />
    <meta name="keywords"
        content="admin templates, bootstrap admin templates, bootstrap 5, dashboard, dashboard templets, sass admin templets, html admin templates, responsive, bootstrap admin templates free download,premium bootstrap admin templates, Elite Able, Dasho bootstrap admin template">
    <meta name="author" content="Phoenixcoded" />

    <!-- Favicon icon -->
    <link rel="shortcut icon" href="{{ asset('website/assets/images/favicon.png') }}" type="image/x-icon">
     <!-- fontawesome icon -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <!-- material icon -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/material/css/materialdesignicons.min.css') }}">
    <!-- animation css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/animation/css/animate.min.css') }}">
    <!-- prism css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/prism/css/prism.min.css') }}">
    <!-- notification css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/notification/css/notification.min.css') }}">
    <!-- template css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layouts/layout-4.css') }}">

    <!-- <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/fonts/simple-line-icons/css/simple-line-icons.css') }}"> -->





    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

    <!-- <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script> -->


    <!-- <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script> -->
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- Bootstrap JS -->
    <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script> -->



    @yield('styles') <!-- Here styles will be injected -->
    <style>
        .header-logo .logo {
            height: 40px;
        }

        .header-logo .logo-thumb {
            height: 45px;
        }

        body.layout-4 .pcoded-navbar .navbar-content {
            box-shadow: 0 0 20px 0 #0004;
        }

        .card {
            box-shadow: 0 0 5px #0001;
            border-radius: 3px;
        }

        .backgroundblur,
        .navbar-brand.header-logo {
            backdrop-filter: blur(10px);
        }

        .pcoded-header .dropdown .notification .noti-head {
            background: linear-gradient(-45deg, #0b416d, #0A3354);
            border-radius: 4px;
        }

        @media(min-width:767px) {
            .pcoded-header a>i {
                font-size: 18px;
            }

            .pcoded-header .dropdown .dropdown-toggle:after {
                left: 16px;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body class="layout-4">
    <div class="wrapper">


        @include('Admin.layouts.header')

        @include('Admin.layouts.sidebar')

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <!-- <section class="main-page-handle">
      <h3 class="font-24">Dashboard</h3>
      <p class="font-14">Hello, {{ auth()->user()->name }}. Welcome to Dashboard</p>
    </section> -->
                            @yield('content')



                            @include('Admin.layouts.footer')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Required Js -->
    <script src="{{ asset('assets/js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script>
    <!-- <script src="{{ asset('assets/js/menu-setting.js') }}"></script> -->
    {{--
    <!-- am chart js -->
    <script src="{{ asset('assets/plugins/chart-am4/js/core.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart-am4/js/charts.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart-am4/js/animated.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart-am4/js/maps.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart-am4/js/worldLow.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart-am4/js/continentsLow.js') }}"></script>

    <!-- Float Chart js -->
    <script src="{{ asset('assets/plugins/flot/js/jquery.flot.js') }}"></script>
    <script src="{{ asset('assets/plugins/flot/js/jquery.flot.categories.js') }}"></script>
    <script src="{{ asset('assets/plugins/flot/js/curvedLines.js') }}"></script>
    <script src="{{ asset('assets/plugins/flot/js/jquery.flot.tooltip.min.js') }}"></script>

    <!-- peity chart js -->
    <script src="{{ asset('assets/plugins/chart-peity/js/jquery.peity.min.js') }}"></script>

    <!-- Rating Js -->
    <script src="{{ asset('assets/plugins/ratting/js/jquery.barrating.min.js') }}"></script>

    <!-- custom-chart js -->
    <script src="{{ asset('assets/js/pages/chart.js') }}"></script>



    <script src="{{ asset('assets/js/analytics.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboard-analytics.js') }}"></script>

    <script>
        $('.layout-type > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('.layout-type > a').removeClass('active');
            $(this).addClass('active');
            $('head').append('<link rel="stylesheet" class="layout-css" href="">');
            if (temp == "menu-dark") {
                $('.pcoded-navbar').removeClassPrefix('menu-');
                $('.pcoded-navbar').removeClass('navbar-dark');
            }
            if (temp == "menu-light") {
                $('.pcoded-navbar').removeClassPrefix('menu-');
                $('.pcoded-navbar').removeClass('navbar-dark');
                $('.pcoded-navbar').addClass(temp);
            }
            if (temp == "reset") {
                location.reload();
            }
            if (temp == "dark") {
                $('.pcoded-navbar').removeClassPrefix('menu-');
                $('.pcoded-navbar').addClass('navbar-dark');
                $('.layout-css').attr("href", "{{asset('assets/css/layouts/dark.css') }} ");
            } else {
                $('.layout-css').attr("href", "");
            }
        });
        // Background images
        $('.bg-images > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('body').removeAttr('style');
            $('.bg-images > a').removeClass('active');
            $('body').css({
                'background-image': temp,
                'background-size': 'cover'
            });
        });
        // Background pattern
        $('.bg-images.pattern > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('body').removeAttr('style');
            $('.bg-images.pattern > a').removeClass('active');
            $('body').css({
                'background-image': temp,
                'background-size': 'auto'
            });
        });
        // Background Color
        $('.laybg-color > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('body').removeAttr('style');
            $('.laybg-color > a').removeClass('active');
            $(this).addClass('active');
            $('body').css('background', temp);
        });
        // Active Color
        $('.active-color > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('.active-color > a').removeClass('active');
            $(this).addClass('active');
            if (temp == "active-default") {
                $('.pcoded-navbar').removeClassPrefix('active-');
            } else {
                $('.pcoded-navbar').removeClassPrefix('active-');
                $('.pcoded-navbar').addClass(temp);
            }
        });
        // Caption Hide
        $('#caption-hide').change(function() {
            if ($(this).is(":checked")) {
                $('.pcoded-navbar').addClass('caption-hide');
            } else {
                $('.pcoded-navbar').removeClass('caption-hide');
            }
        });
        // title Color
        $('.title-color > a').on('click', function() {
            var temp = $(this).attr('data-value');
            $('.title-color > a').removeClass('active');
            $(this).addClass('active');
            if (temp == "title-default") {
                $('.pcoded-navbar').removeClassPrefix('title-');
            } else {
                $('.pcoded-navbar').removeClassPrefix('title-');
                $('.pcoded-navbar').addClass(temp);
            }
        });
        // rtl layouts
        $('#theme-rtl').change(function() {
            $('head').append('<link rel="stylesheet" class="rtl-css" href="">');
            if ($(this).is(":checked")) {
                $('.rtl-css').attr("href", "{{asset('assets/css/layouts/rtl.css') }} ");
            } else {
                $('.rtl-css').attr("href", "");
            }
        });
        // Menu Icon Color
        $('#icon-colored').change(function() {
            if ($(this).is(":checked")) {
                $('.pcoded-navbar').addClass('icon-colored');
            } else {
                $('.pcoded-navbar').removeClass('icon-colored');
            }
        });
        // Box layouts
        $('#box-layouts').change(function() {
            if ($(this).is(":checked")) {
                $('body').addClass('container');
                $('body').addClass('box-layout');
            } else {
                $('body').removeClass('container');
                $('body').removeClass('box-layout');
            }
        });
        // Menu Dropdown icon
        function drpicon(temp) {
            if (temp == "style1") {
                $('.pcoded-navbar').removeClassPrefix('drp-icon-');
            } else {
                $('.pcoded-navbar').removeClassPrefix('drp-icon-');
                $('.pcoded-navbar').addClass('drp-icon-' + temp);
            }
        }
        // Menu subitem icon
        function menuitemicon(temp) {
            if (temp == "style1") {
                $('.pcoded-navbar').removeClassPrefix('menu-item-icon-');
            } else {
                $('.pcoded-navbar').removeClassPrefix('menu-item-icon-');
                $('.pcoded-navbar').addClass('menu-item-icon-' + temp);
            }
        }
        $.fn.removeClassPrefix = function(prefix) {
            this.each(function(i, it) {
                var classes = it.className.split(" ").map(function(item) {
                    return item.indexOf(prefix) === 0 ? "" : item;
                });
                it.className = classes.join(" ");
            });
            return this;
        };
    </script>

    --}}

    <script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
    @yield('scripts') <!-- All JS goes here -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

</body>

</html>
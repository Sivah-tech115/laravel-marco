<header>
    <nav class="container">
        <div class="logo"><a href="{{ route('home') }}"><img src="{{ asset('website/assets/images/negozishop-logo.png') }}" alt="Negozi Shop Logo"></a></div>
        <ul class="nav_menu">
            <li class="menu_item">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('website/assets/images/negozishop-logo.png') }}" alt="Negozi Shop Logo">
                </a>
            </li>
            <li class="menu_item"><a href="{{ route('home') }}">Shop</a></li>
            <li class="menu_item"><a href="{{ route('brands') }}">Brands</a></li>
            <li class="menu_item"><a href="{{ route('merchants') }}">Merchants</a></li>
            <li class="menu_item"><a href="{{ route('login') }}">Login</a></li>
        </ul>
        <span class="mobile_menu">
            <i class="fa-solid fa-bars open_menu"></i>
            <i class="fa-solid fa-xmark close_menu"></i>
        </span>
    </nav>
</header>
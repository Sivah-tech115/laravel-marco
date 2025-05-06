    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <nav class="pcoded-navbar  menu-light icon-colored brand-info">
        <div class="navbar-wrapper">
            <div class="navbar-brand header-logo">
                <a href="{{route('home')}}" class="b-brand">
                    <img src="{{asset('website/assets/images/negozishop-logo.png')}}" alt="" class="logo images">
                    <img src="{{asset('website/assets/images/negozishop-logo.png')}}" alt="" class="logo-thumb images">
                </a>
                <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            </div>
            <div class="navbar-content scroll-div">
                <ul class="nav pcoded-inner-navbar">
                    <!-- <li class="nav-item ">
                        <a href="{{route('admin')}}" class="nav-link">
                            <span class="pcoded-micon">
                                <i class="feather icon-home"></i>
                            </span>
                            <span class="pcoded-mtext">Dashboard</span>
                        </a>
                    </li> -->
                    <li class="nav-item ">
                        <a href="{{route('admin.merchant')}}" class="nav-link">
                            <span class="pcoded-micon">
                                <i class="feather icon-user"></i>
                            </span>
                            <span class="pcoded-mtext">Merchants</span>
                        </a>
                    </li>
                    <li data-username="Settings" class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-search"></i></span>
                            <span class="pcoded-mtext">Kelkoo Search</span></a>
                        <ul class="pcoded-submenu">
                            <!-- <li class=""><a href="{{url('/kelkoo-search')}}" class="">Kelkoo Search</a></li> -->
                            <li class=""><a href="{{url('admin/api-key')}}" class="">Api Key</a></li>
                            <li class=""><a href="{{url('admin/country-list')}}" class="">Country List</a></li>
                            <!-- <li class=""><a href="#!" class="">User Management</a></li> -->
                        </ul>
                    </li>
                    <li class="nav-item ">
                        <a href="{{route('admin.showFacebookFeedLink')}}" class="nav-link">
                            <span class="pcoded-micon">
                                <i class="feather icon-user"></i>
                            </span>
                            <span class="pcoded-mtext">Feeds</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
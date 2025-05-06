    <!-- [ Header ] start -->
    <header class="navbar pcoded-header navbar-expand-lg navbar-light">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse1" href="#!"><span></span></a>
            <a href="#" class="b-brand">
                <img src="{{asset('assets/images/Layer-22.png')}}" alt="" class="logo images">
                <img src="{{asset('assets/images/Layer-22.png')}}" alt="" class="logo-thumb images">
            </a>
        </div>
        <a class="mobile-menu" id="mobile-header" href="#!">
            <i class="feather icon-more-horizontal"></i>
        </a>
        <div class="collapse navbar-collapse backgroundblur dashboard_custom_header justify-content-between">
            <div class="page_title_bar with_bread">
                <h5>@yield('breadcrumbtitle', 'Dashboard')</h5>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><span> @yield('breadcrumbtitle2', 'Dashboard')</span></li>
                </ul>
            </div>
            <ul class="navbar-nav ms-auto d-block">
                {{-- <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown"><i
                                class="icon feather icon-bell"></i></a>
                        <div class="dropdown-menu dropdown-menu-end notification shadow-lg">
                            <div class="noti-head">
                                <h6 class="d-inline-block m-b-0">Notifications</h6>
                                <div class="float-end">
                                    <a href="#!" class="m-r-10">mark as read</a>
                                    <a href="#!">clear all</a>
                                </div>
                            </div>
                            <ul class="noti-body">
                                <li class="n-title">
                                    <p class="m-b-0">NEW</p>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-1.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>John Doe</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>5 min</span></p>
                                            <p>New ticket Added</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="n-title">
                                    <p class="m-b-0">EARLIER</p>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-2.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>Joseph William</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>10 min</span></p>
                                            <p>Prchace New Theme and make payment</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-3.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>Sara Soudein</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>12 min</span></p>
                                            <p>currently login</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-1.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>Joseph William</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>30 min</span></p>
                                            <p>Prchace New Theme and make payment</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-3.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>Sara Soudein</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>1 hour</span></p>
                                            <p>currently login</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="notification">
                                    <div class="d-flex">
                                        <img class="img-radius" src="assets/images/user/avatar-1.jpg"
                                            alt="Generic placeholder image">
                                        <div class="flex-grow-1">
                                            <p><strong>Joseph William</strong><span class="n-time text-muted"><i
                                                        class="icon feather icon-clock m-r-10"></i>2 hour</span></p>
                                            <p>Prchace New Theme and make payment</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="noti-footer">
                                <a href="#!">show all</a>
                            </div>
                        </div>
                    </div>
                </li> --}}
                {{-- <li><a href="#!" class="displayChatbox"><i class="icon feather icon-mail"></i></a></li> --}}
                <li>
                    <div class="dropdown drp-user">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="icon feather icon-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end profile-notification">
                            <div class="pro-head">
                                @if(auth()->check())
                                <img src="{{ auth()->user()->image ? asset('storage/' . auth()->user()->image) : asset('assets/images/user/avatar-1.jpg') }}"
                                    class="img-radius" alt="User-Profile-Image">
                                @endif
                                <span>{{ Auth::user()->name }}</span>

                            </div>
                            <ul class="pro-body">
                                <li><a href="{{ route('admin.profile') }}" class="dropdown-item"><i class="feather icon-user"></i> Profile</a>
                                </li>
                                <!-- <li><a href="#!" class="dropdown-item"><i class="feather icon-settings"></i>
                                        Settings</a></li> -->

                                <li> <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                  document.getElementById('logout-form').submit();">
                                        <i class="feather icon-log-out"></i>
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                                {{-- <li><a href="auth-signin.html" class="dropdown-item"><i class="feather icon-lock"></i>
                                        Lock Screen</a></li> --}}
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </header>
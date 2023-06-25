<style>
    .li-bottom {
        height: 43px;
        padding: 10px;
        text-align: left;
    }

    .side-card {
        height: auto;
        padding: 0;
    }

    /*.sidebar-left .navigation-left .nav-item .nav-item-hold {
        display: block;
        width: 100%;
        padding: 20px 0;
        color: #ffff !important;
        background: #a92219;
    }

    .layout-sidebar-large .sidebar-left .navigation-left .nav-item .nav-item-hold .nav-text {
        font-size: 15px;
        display: block;
        font-weight: bold;
        text-align: left;
        padding-left: 10px;
    }
    .layout-sidebar-large .sidebar-left .navigation-left .nav-item {
        color: #b90006;
    }
    .layout-sidebar-large .sidebar-left .navigation-left .nav-item:hover {
         color: #421112;
    }

    .i-Arrow-Down {
        float: right;
        padding-right: 20px;
        font-size: 21px;
        font-weight: bold;
    }*/


    nav {
        position: relative;
        width: 230px !important;
    }

    nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    nav ul li .active {
        color: #fff !important;
        background-color: #b90006;
    }

    nav ul li a {
        display: block;
        background: #000;
        padding: 10px 15px;
        color: #ccc;
        font-size: 18px;
        font-weight: bold;
        text-decoration: none;
        -webkit-transition: 0.2s linear;
        -moz-transition: 0.2s linear;
        -ms-transition: 0.2s linear;
        -o-transition: 0.2s linear;
        transition: 0.2s linear;
    }

    nav ul li a:hover {
        background: #b90006 !important;
        color: #fff;
    }

    nav ul li a .fa {
        width: 16px;
        text-align: center;
        margin-right: 5px;
        float: right;
    }

    nav ul ul {
        background-color: #ebebeb;
    }

    nav ul li ul li a {
        background: #000;
        border-left: 4px solid transparent;
        padding: 10px 20px;
    }

    nav ul li ul li a:hover {
        background: #ebebeb;
        border-left: 4px solid red;
    }
</style>
<div class="side-content-wrap">
    <div class="sidebar-left open rtl-ps-none">
        <nav class='animated bounceInDown'>
            <ul>
                <li class="nav-item p-1">
                    <a href="{{url('/')}}">
                        <img src="{{asset('public/assets/images/logo.png')}}" alt="">
                    </a>
                </li>
                <hr class="m-2" color="#fff">
                <li><a class="{{(request()->segment(1) == 'dashboard') ? 'active' : '' }}"
                       href="{{route('dashboard')}}"> Dashboard</a></li>
                <li><a class="{{request()->is('orders/*') ? 'active' : '' }}" href="{{route('order-list')}}">Orders</a>
                </li>
                <li><a class="{{ request()->is('menu/*') ? 'active' : '' }}" href="{{route('menu-type')}}">Menu</a></li>
                <li><a class="{{ request()->is('setting/*') ? 'active' : '' }}" href="{{route('tax-settings')}}">Settings</a>
                </li>
                <li><a style="background-color: #454545!important;color: #ccc;">Promotions</a>
                    <ul>
                        {{--                                        <li class="tree"><a class="{{ request()->is('promotions/send-notification') ? 'active' : '' }}" href="{{route('send-notification')}}">Email</a></li>--}}
                        <li class="tree"><a class="{{ request()->is('promotions/bonus') ? 'active' : '' }}"
                                            href="{{route('bonus')}}">Bonus</a></li>
                    </ul>
                </li>
                <li><a style="background-color: #454545!important; color: #ccc;">Financial</a>
                    <ul>
                        <li class="tree"><a class="{{ request()->is('financial/transaction') ? 'active' : '' }}"
                                            href="{{route('transaction')}}">Transactions</a></li>
                        {{--<li class="tree"><a class="{{ request()->is('financial/statements') ? 'active' : '' }}"
                                            href="{{route('statements')}}">Statements</a></li>--}}
                    </ul>
                </li>
                <li><a class="{{ request()->is('users/*') ? 'active' : '' }}" href="{{route('customers')}}">Users</a>
                <li>
                    <a style="background-color: #454545!important; color: #ccc;">Cards</a>
                    <ul>
                        {{--<li class="tree"><a class="{{ request()->is('promotions/send-notification') ? 'active' : '' }}" href="{{route('send-notification')}}">Email</a></li>--}}
                        <li class="tree">
                            <a class="{{ request()->is('card/user-cards') ? 'active' : '' }}"
                                            href="{{route('user_cards')}}">User Cards</a>
                        </li>
                        <li class="tree">
                            <a class="{{ request()->is('card/gift-cards') ? 'active' : '' }}"
                               href="{{route('gift_cards')}}">Gift Cards</a>
                        </li>
                    </ul>
                </li>
                <li><a class="{{ request()->is('settings/timings') ? 'active' : '' }}" href="{{route('res_timings')}}">Timings</a>
                </li>
                {{--                                <li class='sub-menu'><a>Help<div class='fa fa-caret-down right'></div></a>--}}
                {{--                                    <ul>--}}
                {{--                                        <li><a class="{{ request()->is('settings/*') ? 'active' : '' }}" href='#message'>Global Settings</a></li>--}}
                {{--                                        <li><a href='#settings'>Submit a Ticket</a></li>--}}
                {{--                                        <li><a href='#settings'>Network Status</a></li>--}}
                {{--                                    </ul>--}}
                {{--                                </li>--}}
                <li><a href="#" onclick="adminLogout()">Logout</a></li>
            </ul>
        </nav>

        {{--
                <ul class="navigation-left">
                     <li class="nav-item p-2">
                         <a href="{{url('/')}}">
                             <img src="{{asset('public/assets/images/logo.png')}}" alt="">
                         </a>
                     </li>
                    <li class="nav-item {{ request()->is('users/*') ? 'active' : '' }}">
                        <a class="nav-item-hold"
                           aria-controls="collapseExample_1">
                            <span class="nav-text">Dashboard<b><i class="i-Arrow-Down text-right"></i></b></span>
                        </a>
                        <div class="collapse show" id="collapseExample_1">
                            <div class="col-md-12 side-card">
                                <ul class="p-0">
                                    <a href="{{route('dashboard')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Bar-Chart"></i>
                                            <span class="item-name">Dashboard</span>
                                        </li>
                                    </a>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item {{ request()->is('orders/*') ? 'active' : '' }}">
                        <a class="nav-item-hold"
                           aria-controls="collapseExample_1">
                            <span class="nav-text">Order(s)</span>
                        </a>
                        <div class="collapse show" id="collapseExample_1">
                            <div class="col-md-12 side-card">
                                <ul class="p-0">
                                    <a class="{{ Route::currentRouteName()=='orders' ? 'open' : '' }}"
                                       href="{{route('order-list')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Align-Right"></i>
                                            <span class="item-name">Orders</span>
                                        </li>
                                    </a>
                                    <a class="{{ Route::currentRouteName()=='orders' ? 'open' : '' }}"
                                       href="{{route('transaction')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Align-Right"></i>
                                            <span class="item-name">Transactions</span>
                                        </li>
                                    </a>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item {{ request()->is('orders/*') ? 'active' : '' }}">
                        <a class="nav-item-hold"
                           aria-controls="collapseExample_1">
                            <span class="nav-text">Rewards/Bonus</span>
                        </a>
                        <div class="collapse show" id="collapseExample_1">
                            <div class="col-md-12 side-card">
                                <ul class="p-0">
                                    <a href="{{route('rewards-items')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Dollar-Sign"></i>
                                            <span class="item-name">Reward / Birthday Items</span>
                                        </li>
                                    </a>
                                    <a href="{{route('bonus')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Gift-Box"></i>
                                            <span class="item-name">Bonus</span>
                                        </li>
                                    </a>
                                    <a class="" href="{{url('/send-notification')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Align-Right"></i>
                                            <span class="item-name">Send Notification</span>
                                        </li>
                                    </a>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item {{ request()->is('users/*') ? 'active' : '' }}">
                        <a class="nav-item-hold"
                           aria-controls="collapseExample_1">
                            <span class="nav-text">User(s) <b><b><i class="i-Arrow-Down text-right"></i></b><</span>
                        </a>
                        <div class="collapse show" id="collapseExample_1">
                            <div class="col-md-12 side-card">
                                <ul class="p-0">
                                    <a class="{{ Route::currentRouteName()=='customers' ? 'open' : '' }}"
                                       href="{{route('customers')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Checked-User"></i>
                                            <span class="item-name">Customers</span>
                                        </li>
                                    </a>
                                    @if(Auth::guard('admin')->user()->type ==1)
                                        <a class="{{ Route::currentRouteName()=='managers' ? 'open' : '' }}"
                                           href="{{route('managers')}}">
                                            <li class="nav-item li-bottom">
                                                <i class="nav-icon i-Checked-User"></i>
                                                <span class="item-name">Managers</span>
                                            </li>
                                        </a>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item {{ (request()->is('menu') || request()->is('menu/*')) ? 'active' : '' }}"
                        data-item="menu">
                        data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false"
                        <a class="nav-item-hold"
                           aria-controls="collapseExample">
                            <span class="nav-text">Menu <b><b><i class="i-Arrow-Down text-right"></i></b><</span>
                        </a>
                        <div class="collapse show" id="collapseExample">
                            <div class="col-md-12 side-card">
                                <ul class="p-0">
                                    <a href="{{route('dashboard')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Bar-Chart"></i>
                                            <span class="item-name">Dashboard</span>
                                    </li>
                                    </a>
                                    <a href="{{route('menu-type')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Newspaper"></i>
                                            <span class="item-name">Menu</span>
                                    </li>
                                    </a>
                                    <a href="{{route('side-menu-categories')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Receipt-4"></i>
                                            <span class="item-name">Categories</span>
                                    </li>
                                    </a>
                                    <a href="{{route('side-menu-list')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Car-Items"></i>
                                            <span class="item-name">Items</span>
                                    </li>
                                    </a>
                                    <a href="{{route('items-availability')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Car-Items"></i>
                                            <span class="item-name">Items Availability</span>
                                        </li>
                                    </a>
                                    <a href="{{route('modifier-group')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Receipt-3"></i>
                                            <span class="item-name">Modifier Group</span>
                                    </li>
                                    </a>
                                    <a href="{{route('complete-meals')}}">
                                    <li class="nav-item li-bottom">
                                            <i class="nav-icon i-File-Clipboard-File--Text"></i>
                                            <span class="item-name">Complete Meals</span>
                                    </li>
                                    </a>
                                    <a class="{{ Route::currentRouteName()=='favorite-name' ? 'open' : '' }}"
                                       href="{{route('favorite-name')}}">
                                        <li class="nav-item li-bottom">
                                            <i class="nav-icon i-Heart"></i>
                                            <span class="item-name">Favorite Label</span>
                                        </li>
                                    </a>
                                </ul>
                            </div>
                        </div>
                    </li>
                    @if(Auth::guard('admin')->user()->type ==1)
                        <li class="nav-item {{ request()->is('setting/*') ? 'active' : '' }}">
                            <a class="nav-item-hold"
                               aria-controls="collapseExample_1">
                                <span class="nav-text">Settings</span>
                            </a>
                            <div class="collapse show" id="collapseExample_1">
                                <div class="col-md-12 side-card">
                                    <ul class="p-0">
                                        <a href="{{route('tax-settings')}}">
                                            <li class="nav-item li-bottom">
                                                <i class="nav-icon i-Dollar-Sign"></i>
                                                <span class="item-name">Global Settings</span>
                                            </li>
                                        </a>
                                        <a href="{{route('preparation-time')}}">
                                            <li class="nav-item li-bottom">
                                                <i class="nav-icon i-Clock"></i>
                                                <span class="item-name">Time settings</span>
                                            </li>
                                        </a>
                                        <a class="{{ Route::currentRouteName()=='setting-restaurants' ? 'open' : '' }}"
                                           href="{{route('setting-restaurants')}}">
                                        <li class="nav-item li-bottom">
                                                <i class="nav-icon i-Shop-2"></i>
                                                <span class="item-name">Locations</span>
                                        </li>
                                        </a>

                                        <li class="nav-item li-bottom">
                                            <a class="{{ Route::currentRouteName()=='notifications' ? 'open' : '' }}"
                                               href="{{route('notifications')}}">
                                                <i class="nav-icon i-Bell"></i>
                                                <span class="item-name">Mange Notifications</span>
                                            </a>
                                        </li>
                                        <li class="nav-item li-bottom">
                                            <a class="{{ Route::currentRouteName()=='pagination-limit' ? 'open' : '' }}"
                                               href="{{route('pagination-limit')}}">
                                                <i class="nav-icon i-Data-Settings"></i>
                                                <span class="item-name">Pagination Limit</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if(Auth::guard('admin')->user()->type ==1)
                        <li class="nav-item {{ request()->is('delivery/*') ? 'active' : '' }}">
                            <a class="nav-item-hold"
                               aria-controls="collapseExample_1">
                                <span class="nav-text">Delivery</span>
                            </a>
                            <div class="collapse show" id="collapseExample_1">
                                <div class="col-md-12 side-card">
                                    <ul class="p-0">
                                        <li class="nav-item li-bottom">
                                            <a class="{{ Route::currentRouteName()=='delivery-create' ? 'open' : '' }}"
                                               href="{{route('delivery-create')}}">
                                                <i class="nav-icon i-Shop-2"></i>
                                                <span class="item-name">Locations</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
        --}}

    </div>
    <div class="sidebar-left-secondary rtl-ps-none" data-perfect-scrollbar data-suppress-scroll-x="true">
        <ul class="childNav" data-parent="menu">
            <li class="nav-item">
                <a href="{{route('menu-type')}}">
                    <i class="nav-icon i-Bag-Items"></i>
                    <span class="item-name">Main Menu</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('side-menu-list')}}">
                    <i class="nav-icon i-Car-Items"></i>
                    <span class="item-name">Side Menu</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('side-menu-categories')}}">
                    <i class="nav-icon i-Receipt-4"></i>
                    <span class="item-name">Side Menu Categories</span>
                </a>
            </li>
        </ul>
        <ul class="childNav" data-parent="setting">
            {{--<li class="nav-item">
                <a class="{{ Route::currentRouteName()=='setting-timing' ? 'open' : '' }}"
                   href="{{route('setting-timing')}}">
                    <i class="nav-icon i-Clock"></i>
                    <span class="item-name">Timing</span>
                </a>
            </li>--}}
            <li class="nav-item">
                <a class="{{ Route::currentRouteName()=='setting-restaurants' ? 'open' : '' }}"
                   href="{{route('setting-restaurants')}}">
                    <i class="nav-icon i-Shop-2"></i>
                    <span class="item-name">Restaurants</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-overlay"></div>
</div>
@yield('page-js')
<script>
    function adminLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to logout.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Logout!'
        }).then(function (result) {
            if (result.value
            ) {
                axios.get(baseUrl + 'logout').then(function (response) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 200);
                }).catch(function (error) {
                });
            }
        })
    }
</script>


<header class="header shop walmart-header">
    <!-- Walmart Topbar -->
    <div class="topbar walmart-topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="list-main">
                            @php
                                $settings=DB::table('settings')->get();
                            @endphp
                            <li><i class="fas fa-phone"></i> @foreach($settings as $data) {{$data->phone}} @endforeach</li>
                            <li><i class="fas fa-envelope"></i> @foreach($settings as $data) {{$data->email}} @endforeach</li>
                        </ul>
                    </div>
                    <!--/ End Top Left -->
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Right -->
                    <div class="right-content">
                        <ul class="list-main">
                            <li><i class="fas fa-map-marker-alt"></i> <a href="{{route('order.track')}}">Track Order</a></li>
                            @auth 
                                @if(Auth::user()->role=='admin')
                                    <li><i class="fas fa-tachometer-alt"></i> <a href="{{route('admin')}}" target="_blank">Dashboard</a></li>
                                @else 
                                    <li><i class="fas fa-user-circle"></i> <a href="{{route('user')}}" target="_blank">My Account</a></li>
                                @endif
                                <li><i class="fas fa-sign-out-alt"></i> <a href="{{route('user.logout')}}">Logout</a></li>
                            @else
                                <li><i class="fas fa-sign-in-alt"></i> <a href="{{route('login.form')}}">Sign In</a></li>
                                <li><i class="fas fa-user-plus"></i> <a href="{{route('register.form')}}">Register</a></li>
                            @endauth
                        </ul>
                    </div>
                    <!-- End Top Right -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Walmart Topbar -->
    <div class="middle-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-12">
                    <!-- Walmart Logo -->
                    <div class="logo walmart-logo">
                        @php
                            $settings=DB::table('settings')->get();
                        @endphp                    
                        <a href="{{route('home')}}" class="walmart-brand">
                            <img src="@foreach($settings as $data) {{$data->logo}} @endforeach" alt="logo" class="logo-img">
                            <span class="brand-text">Walmart</span>
                        </a>
                    </div>
                    <!--/ End Logo -->
                    <div class="mobile-nav"></div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <!-- Walmart Search Bar -->
                    <div class="walmart-search-container">
                        <form method="POST" action="{{route('product.search')}}" class="walmart-search-form">
                            @csrf
                            <div class="search-input-group">
                                <select class="category-select">
                                    <option value="">All Departments</option>
                                    @foreach(Helper::getAllCategory() as $cat)
                                        <option value="{{$cat->id}}">{{$cat->title}}</option>
                                    @endforeach
                                </select>
                                <input name="search" placeholder="Search everything at Walmart..." type="search" class="search-input">
                                <button class="search-btn" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-12">
                    <div class="right-bar walmart-actions">
                        <!-- Deposit Request Button -->
                        @auth
                        <div class="sinlge-bar">
                            <a href="{{route('deposit.request')}}" class="walmart-btn walmart-btn-success walmart-btn-sm deposit-btn" title="Add Money">
                                <i class="fas fa-plus-circle"></i>
                                <span class="deposit-text">Add Money</span>
                            </a>
                        </div>
                        @endauth
                        <!-- Wishlist -->
                        <div class="sinlge-bar shopping walmart-wishlist">
                            @php 
                                $total_prod=0;
                                $total_amount=0;
                            @endphp
                           @if(session('wishlist'))
                                @foreach(session('wishlist') as $wishlist_items)
                                    @php
                                        $total_prod+=$wishlist_items['quantity'];
                                        $total_amount+=$wishlist_items['amount'];
                                    @endphp
                                @endforeach
                           @endif
                            <a href="{{route('wishlist')}}" class="walmart-icon-btn" title="My Wishlist">
                                <i class="fas fa-heart"></i> 
                                <span class="walmart-badge">{{Helper::wishlistCount()}}</span>
                                <span class="icon-label d-none d-lg-inline">Wishlist</span>
                            </a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>{{count(Helper::getAllProductFromWishlist())}} Items</span>
                                        <a href="{{route('wishlist')}}">View Wishlist</a>
                                    </div>
                                    <ul class="shopping-list">
                                        {{-- {{Helper::getAllProductFromCart()}} --}}
                                            @foreach(Helper::getAllProductFromWishlist() as $data)
                                                    @php
                                                        $photo=explode(',',$data->product['photo']);
                                                    @endphp
                                                    <li>
                                                        <a href="{{route('wishlist-delete',$data->id)}}" class="remove" title="Remove this item"><i class="fas fa-times"></i></a>
                                                        <a class="cart-img" href="#"><img src="{{$photo[0]}}" alt="{{$photo[0]}}"></a>
                                                        <h4><a href="{{route('product-detail',$data->product['slug'])}}" target="_blank">{{$data->product['title']}}</a></h4>
                                                        <p class="quantity">{{$data->quantity}} x - <span class="amount">${{number_format($data->price,2)}}</span></p>
                                                    </li>
                                            @endforeach
                                    </ul>
                                    <div class="bottom">
                                        <div class="total">
                                            <span>Total</span>
                                            <span class="total-amount">${{number_format(Helper::totalWishlistPrice(),2)}}</span>
                                        </div>
                                        <a href="{{route('cart')}}" class="btn animate">Cart</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
                        </div>
                        {{-- <div class="sinlge-bar">
                            <a href="{{route('wishlist')}}" class="single-icon"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
                        </div> --}}
                        <div class="sinlge-bar shopping walmart-cart">
                            <a href="{{route('cart')}}" class="walmart-icon-btn" title="My Cart">
                                <i class="fas fa-shopping-cart"></i> 
                                <span class="walmart-badge">{{Helper::cartCount()}}</span>
                                <span class="icon-label d-none d-lg-inline">Cart</span>
                            </a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>{{count(Helper::getAllProductFromCart())}} Items</span>
                                        <a href="{{route('cart')}}">View Cart</a>
                                    </div>
                                    <ul class="shopping-list">
                                        {{-- {{Helper::getAllProductFromCart()}} --}}
                                            @foreach(Helper::getAllProductFromCart() as $data)
                                                    @php
                                                        $photo=explode(',',$data->product['photo']);
                                                    @endphp
                                                    <li>
                                                        <a href="{{route('cart-delete',$data->id)}}" class="remove" title="Remove this item"><i class="fas fa-times"></i></a>
                                                        <a class="cart-img" href="#"><img src="{{$photo[0]}}" alt="{{$photo[0]}}"></a>
                                                        <h4><a href="{{route('product-detail',$data->product['slug'])}}" target="_blank">{{$data->product['title']}}</a></h4>
                                                        <p class="quantity">{{$data->quantity}} x - <span class="amount">${{number_format($data->price,2)}}</span></p>
                                                    </li>
                                            @endforeach
                                    </ul>
                                    <div class="bottom">
                                        <div class="total">
                                            <span>Total</span>
                                            <span class="total-amount">${{number_format(Helper::totalCartPrice(),2)}}</span>
                                        </div>
                                        <a href="{{route('checkout')}}" class="btn animate">Checkout</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Walmart Navigation -->
    <div class="header-inner walmart-nav">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="walmart-menu-area">
                        <!-- Mobile Menu Toggle -->
                        <button class="mobile-menu-toggle d-lg-none">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <!-- Main Menu -->
                        <nav class="walmart-mainmenu">
                            <ul class="walmart-nav-list">
                                <li class="{{Request::path()=='home' ? 'active' : ''}}">
                                    <a href="{{route('home')}}">
                                        <i class="fas fa-home"></i>
                                        <span>Home</span>
                                    </a>
                                </li>
                                <li class="@if(Request::path()=='product-grids'||Request::path()=='product-lists') active @endif">
                                    <a href="{{route('product-grids')}}">
                                        <i class="fas fa-th-large"></i>
                                        <span>All Products</span>
                                        <span class="walmart-badge-new">New</span>
                                    </a>
                                </li>
                                
                                <!-- Categories Dropdown -->
                                @php
                                    $categories = Helper::getAllCategory();
                                @endphp
                                @if($categories && count($categories) > 0)
                                <li class="walmart-dropdown">
                                    <a href="javascript:void(0);" class="dropdown-toggle">
                                        <i class="fas fa-list"></i>
                                        <span>Categories</span>
                                        <i class="fas fa-angle-down dropdown-arrow"></i>
                                    </a>
                                    <ul class="walmart-dropdown-menu">
                                        @foreach($categories as $category)
                                            @if($category->child_cat && count($category->child_cat) > 0)
                                                <li class="walmart-dropdown-submenu">
                                                    <a href="{{route('product-cat', $category->slug)}}">
                                                        {{$category->title}}
                                                        <i class="fas fa-angle-right submenu-arrow"></i>
                                                    </a>
                                                    <ul class="walmart-submenu">
                                                        @foreach($category->child_cat as $subcategory)
                                                            <li>
                                                                <a href="{{route('product-sub-cat', [$category->slug, $subcategory->slug])}}">
                                                                    {{$subcategory->title}}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{route('product-cat', $category->slug)}}">
                                                        {{$category->title}}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                                @endif
                                
                                <li class="@if(str_contains(Request::path(), 'lucky-wheel')) active @endif">
                                    <a href="{{route('lucky-wheel.index')}}">
                                        <i class="fas fa-gift"></i>
                                        <span>Lucky Wheel</span>
                                        @auth
                                            @if(Helper::getUserRemainingSpins() > 0)
                                                <span class="walmart-badge-hot">{{Helper::getUserRemainingSpins()}}</span>
                                            @endif
                                        @endauth
                                        <span class="walmart-badge-hot">Hot</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <!--/ End Main Menu -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ End Walmart Navigation -->
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay"></div>
</header>
@extends('frontend.layouts.master')
@section('title','Wallmart88 ||HOME PAGE')
@section('main-content')

<!-- Hero Slider Area - Walmart Style -->
@if(count($banners)>0)
<section class="walmart-hero-slider">
    <div id="walmartSlider" class="carousel slide" data-ride="carousel" data-interval="5000">
        <ol class="carousel-indicators">
            @foreach($banners as $key=>$banner)
            <li data-target="#walmartSlider" data-slide-to="{{$key}}" class="{{(($key==0)? 'active' : '')}}"></li>
            @endforeach
        </ol>
        
        <div class="carousel-inner">
            @foreach($banners as $key=>$banner)
            <div class="carousel-item {{(($key==0)? 'active' : '')}}">
                <div class="hero-slide" style="background-image: url('{{$banner->photo}}');">
                    <div class="hero-overlay"></div>
                    <div class="container">
                        <div class="row align-items-center min-vh-60">
                            <div class="col-lg-6">
                                <div class="hero-content">
                                    <h1 class="hero-title">{{$banner->title}}</h1>
                                    <p class="hero-description">{!! html_entity_decode($banner->description) !!}</p>
                                    <div class="hero-actions">
                                        <a href="{{route('product-grids')}}" class="walmart-btn walmart-btn-primary walmart-btn-lg">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            Shop Now
                                        </a>
                                        <a href="{{route('product-grids')}}" class="walmart-btn walmart-btn-outline-white walmart-btn-lg ml-3">
                                            Learn More
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <a class="carousel-control-prev" href="#walmartSlider" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#walmartSlider" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</section>
@endif

<!-- Category Showcase - Walmart Style -->
<section class="walmart-category-showcase py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Discover our wide range of products across different categories</p>
            </div>
        </div>
        <div class="row">
            @php
            $category_lists=DB::table('categories')->where('status','active')->limit(3)->get();
            @endphp
            @if($category_lists)
                @foreach($category_lists as $cat)
                    @if($cat->is_parent==1)
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="walmart-category-card">
                            <div class="category-image">
                                @if($cat->photo)
                                    <img src="{{$cat->photo}}" alt="{{$cat->title}}" class="img-fluid">
                                @else
                                    <img src="https://via.placeholder.com/400x300/0071ce/ffffff?text={{urlencode($cat->title)}}" alt="{{$cat->title}}" class="img-fluid">
                                @endif
                                <div class="category-overlay">
                                    <div class="category-content">
                                        <h3 class="category-title">{{$cat->title}}</h3>
                                        <a href="{{route('product-cat',$cat->slug)}}" class="walmart-btn walmart-btn-white">
                                            Shop Now
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</section>

<!-- Trending Products - Walmart Style -->
<section class="walmart-trending-products py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Trending Products</h2>
                <p class="section-subtitle">Discover what's popular right now</p>
            </div>
        </div>
        
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="walmart-filter-tabs text-center">
                    @php
                        $categories=DB::table('categories')->where('status','active')->where('is_parent',1)->get();
                    @endphp
                    @if($categories)
                    <button class="walmart-filter-btn active" data-filter="*">
                        All Products
                    </button>
                    @foreach($categories as $key=>$cat)
                    <button class="walmart-filter-btn" data-filter=".{{$cat->id}}">
                        {{$cat->title}}
                    </button>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="row walmart-products-grid" id="productsGrid">
            @if($product_lists)
                @foreach($product_lists as $key=>$product)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4 product-item {{$product->cat_id}}">
                    <div class="walmart-product-card">
                        <div class="product-image-wrapper">
                            <a href="{{route('product-detail',$product->slug)}}">
                                @php
                                    $photo=explode(',',$product->photo);
                                @endphp
                                <img class="product-image" src="{{$photo[0]}}" alt="{{$product->title}}">
                                
                                <!-- Product Badges -->
                                @if($product->stock<=0)
                                    <span class="product-badge out-of-stock">Out of Stock</span>
                                @elseif($product->condition=='new')
                                    <span class="product-badge new">New</span>
                                @elseif($product->condition=='hot')
                                    <span class="product-badge hot">Hot</span>
                                @elseif($product->discount > 0)
                                    <span class="product-badge discount">{{$product->discount}}% Off</span>
                                @endif
                            </a>
                            
                            <!-- Product Actions -->
                            <div class="product-actions">
                                <button class="action-btn" data-toggle="modal" data-target="#{{$product->id}}" title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{route('add-to-wishlist',$product->slug)}}" class="action-btn" title="Add to Wishlist">
                                    <i class="fas fa-heart"></i>
                                </a>
                                <a href="{{route('add-to-cart',$product->slug)}}" class="action-btn" title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <h4 class="product-title">
                                <a href="{{route('product-detail',$product->slug)}}">{{Str::limit($product->title, 50)}}</a>
                            </h4>
                            
                            <div class="product-price">
                                @php
                                    $after_discount=($product->price-($product->price*$product->discount)/100);
                                @endphp
                                <span class="current-price">${{number_format($after_discount,2)}}</span>
                                @if($product->discount > 0)
                                <span class="original-price">${{number_format($product->price,2)}}</span>
                                @endif
                            </div>
                            
                            @if($product->commission && $product->commission > 0)
                            <div class="commission-info">
                                <i class="fas fa-percentage"></i>
                                {{$product->commission}}% Commission
                            </div>
                            @endif
                            
                            <div class="product-actions-bottom">
                                <a href="{{route('add-to-cart',$product->slug)}}" class="walmart-btn walmart-btn-primary walmart-btn-sm w-100">
                                    <i class="fas fa-cart-plus mr-2"></i>
                                    Add to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        
        <!-- View All Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{route('product-grids')}}" class="walmart-btn walmart-btn-outline-primary walmart-btn-lg">
                    View All Products
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- Featured Products Banner - Walmart Style -->
@if($featured && count($featured) > 0)
<section class="walmart-featured-banner py-5">
    <div class="container">
        <div class="row">
            @foreach($featured as $data)
            <div class="col-lg-6 col-md-6 col-12 mb-4">
                <div class="walmart-featured-card">
                    @php
                        $photo=explode(',',$data->photo);
                    @endphp
                    <div class="featured-image" style="background-image: url('{{$photo[0]}}');">
                        <div class="featured-overlay"></div>
                        <div class="featured-content">
                            <span class="featured-category">{{$data->cat_info['title'] ?? 'Featured'}}</span>
                            <h3 class="featured-title">{{$data->title}}</h3>
                            @if($data->discount > 0)
                            <div class="featured-discount">
                                Up to <span class="discount-percent">{{$data->discount}}%</span> Off
                            </div>
                            @endif
                            <a href="{{route('product-detail',$data->slug)}}" class="walmart-btn walmart-btn-white walmart-btn-lg">
                                Shop Now
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Hot Items Carousel - Walmart Style -->
<section class="walmart-hot-items py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">🔥 Hot Items</h2>
                <p class="section-subtitle">Don't miss out on these popular products</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="walmart-hot-carousel owl-carousel">
                    @foreach($product_lists as $product)
                        @if($product->condition=='hot')
                        <div class="walmart-hot-item">
                            <div class="hot-product-card">
                                <div class="hot-product-image">
                                    <a href="{{route('product-detail',$product->slug)}}">
                                        @php
                                            $photo=explode(',',$product->photo);
                                        @endphp
                                        <img src="{{$photo[0]}}" alt="{{$product->title}}" class="img-fluid">
                                        <span class="hot-badge">🔥 HOT</span>
                                    </a>
                                    
                                    <div class="hot-product-actions">
                                        <button class="hot-action-btn" data-toggle="modal" data-target="#{{$product->id}}" title="Quick View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{route('add-to-wishlist',$product->slug)}}" class="hot-action-btn" title="Add to Wishlist">
                                            <i class="fas fa-heart"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="hot-product-info">
                                    <h4 class="hot-product-title">
                                        <a href="{{route('product-detail',$product->slug)}}">{{Str::limit($product->title, 40)}}</a>
                                    </h4>
                                    
                                    <div class="hot-product-price">
                                        @php
                                            $after_discount=($product->price-($product->price*$product->discount)/100);
                                        @endphp
                                        <span class="hot-current-price">${{number_format($after_discount,2)}}</span>
                                        @if($product->discount > 0)
                                        <span class="hot-original-price">${{number_format($product->price,2)}}</span>
                                        @endif
                                    </div>
                                    
                                    @if($product->commission && $product->commission > 0)
                                    <div class="hot-commission">
                                        <i class="fas fa-percentage"></i>
                                        {{$product->commission}}% Commission
                                    </div>
                                    @endif
                                    
                                    <a href="{{route('add-to-cart',$product->slug)}}" class="walmart-btn walmart-btn-primary walmart-btn-sm w-100 mt-2">
                                        <i class="fas fa-cart-plus mr-2"></i>
                                        Add to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Start Lucky Wheel Banner -->
<section class="lucky-wheel-banner section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="lucky-wheel-promo">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="promo-content">
                                <h2 class="promo-title">
                                    <i class="fas fa-gift"></i> 
                                    Lucky Wheel
                                </h2>
                                <p class="promo-description">
                                    Join the lucky wheel to win attractive prizes! 
                                    Every day you have the chance to spin and win valuable gifts.
                                </p>
                                <div class="promo-features">
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Free to play</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Attractive prizes</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Daily chances to win</span>
                                    </div>
                                </div>
                                <div class="promo-actions">
                                    <a href="{{ route('lucky-wheel.index') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sync-alt"></i> Spin Now
                                    </a>
                                    @auth
                                    <a href="{{ route('lucky-wheel.history') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-history"></i> History
                                    </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="promo-image text-center">
                                <div class="wheel-preview">
                                    <div class="wheel-circle">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="floating-prizes">
                                        <div class="prize prize-1">🎁</div>
                                        <div class="prize prize-2">💎</div>
                                        <div class="prize prize-3">🏆</div>
                                        <div class="prize prize-4">💰</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Lucky Wheel Banner -->

<!-- Start Shop Home List  -->
<section class="shop-home-list section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="row">
                    <div class="col-12">
                        <div class="shop-section-title">
                            <h1>Latest Items</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @php
                        $product_lists=DB::table('products')->where('status','active')->orderBy('id','DESC')->limit(6)->get();
                    @endphp
                    @foreach($product_lists as $product)
                        <div class="col-md-4">
                            <!-- Start Single List  -->
                            <div class="single-list">
                                <div class="row">
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="list-image overlay">
                                        @php
                                            $photo=explode(',',$product->photo);
                                            // dd($photo);
                                        @endphp
                                        <img src="{{$photo[0]}}" alt="{{$photo[0]}}">
                                        <a href="{{route('add-to-cart',$product->slug)}}" class="buy"><i class="fa fa-shopping-bag"></i></a>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 no-padding">
                                    <div class="content">
                                        <h4 class="title"><a href="#">{{$product->title}}</a></h4>
                                        <p class="price with-discount">${{number_format($product->discount,2)}}</p>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <!-- End Single List  -->
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Shop Home List  -->

<!-- Start Shop Blog  -->
<section class="shop-blog section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>From Our Blog</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @if($posts)
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Blog  -->
                        <div class="shop-single-blog">
                            <img src="{{$post->photo}}" alt="{{$post->photo}}">
                            <div class="content">
                                <p class="date">{{$post->created_at->format('d M , Y. D')}}</p>
                                <a href="{{route('blog.detail',$post->slug)}}" class="title">{{$post->title}}</a>
                                <a href="{{route('blog.detail',$post->slug)}}" class="more-btn">Continue Reading</a>
                            </div>
                        </div>
                        <!-- End Single Blog  -->
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</section>
<!-- End Shop Blog  -->

<!-- Start Shop Services Area -->
<section class="shop-services section home">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-12">
                <!-- Start Single Service -->
                <div class="single-service">
                    <i class="ti-rocket"></i>
                    <h4>Free shiping</h4>
                    <p>Orders over $100</p>
                </div>
                <!-- End Single Service -->
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <!-- Start Single Service -->
                <div class="single-service">
                    <i class="ti-reload"></i>
                    <h4>Free Return</h4>
                    <p>Within 30 days returns</p>
                </div>
                <!-- End Single Service -->
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <!-- Start Single Service -->
                <div class="single-service">
                    <i class="ti-lock"></i>
                    <h4>Sucure Payment</h4>
                    <p>100% secure payment</p>
                </div>
                <!-- End Single Service -->
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <!-- Start Single Service -->
                <div class="single-service">
                    <i class="ti-tag"></i>
                    <h4>Best Peice</h4>
                    <p>Guaranteed price</p>
                </div>
                <!-- End Single Service -->
            </div>
        </div>
    </div>
</section>
<!-- End Shop Services Area -->

@include('frontend.layouts.newsletter')

<!-- Modal -->
@if($product_lists)
    @foreach($product_lists as $key=>$product)
        <div class="modal fade" id="{{$product->id}}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span class="ti-close" aria-hidden="true"></span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row no-gutters">
                                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                    <!-- Product Slider -->
                                        <div class="product-gallery">
                                            <div class="quickview-slider-active">
                                                @php
                                                    $photo=explode(',',$product->photo);
                                                // dd($photo);
                                                @endphp
                                                @foreach($photo as $data)
                                                    <div class="single-slider">
                                                        <img src="{{$data}}" alt="{{$data}}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    <!-- End Product slider -->
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                    <div class="quickview-content">
                                        <h2>{{$product->title}}</h2>
                                        <div class="quickview-ratting-review">
                                            <div class="quickview-ratting-wrap">
                                                <div class="quickview-ratting">
                                                    {{-- <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="fa fa-star"></i> --}}
                                                    @php
                                                        $rate=DB::table('product_reviews')->where('product_id',$product->id)->avg('rate');
                                                        $rate_count=DB::table('product_reviews')->where('product_id',$product->id)->count();
                                                    @endphp
                                                    @for($i=1; $i<=5; $i++)
                                                        @if($rate>=$i)
                                                            <i class="yellow fa fa-star"></i>
                                                        @else
                                                        <i class="fa fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <a href="#"> ({{$rate_count}} customer review)</a>
                                            </div>
                                            <div class="quickview-stock">
                                                @if($product->stock >0)
                                                <span><i class="fa fa-check-circle-o"></i> {{$product->stock}} in stock</span>
                                                @else
                                                <span><i class="fa fa-times-circle-o text-danger"></i> {{$product->stock}} out stock</span>
                                                @endif
                                            </div>
                                        </div>
                                        @php
                                            $after_discount=($product->price-($product->price*$product->discount)/100);
                                        @endphp
                                        <h3><small><del class="text-muted">${{number_format($product->price,2)}}</del></small>    ${{number_format($after_discount,2)}}  </h3>
                                        <div class="quickview-peragraph">
                                            <p>{!! html_entity_decode($product->summary) !!}</p>
                                        </div>
                                        @if($product->size)
                                            <div class="size">
                                                <div class="row">
                                                    <div class="col-lg-6 col-12">
                                                        <h5 class="title">Size</h5>
                                                        <select>
                                                            @php
                                                            $sizes=explode(',',$product->size);
                                                            // dd($sizes);
                                                            @endphp
                                                            @foreach($sizes as $size)
                                                                <option>{{$size}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    {{-- <div class="col-lg-6 col-12">
                                                        <h5 class="title">Color</h5>
                                                        <select>
                                                            <option selected="selected">orange</option>
                                                            <option>purple</option>
                                                            <option>black</option>
                                                            <option>pink</option>
                                                        </select>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        @endif
                                        <form action="{{route('single-add-to-cart')}}" method="POST" class="mt-4">
                                            @csrf
                                            <div class="quantity">
                                                <!-- Input Order -->
                                                <div class="input-group">
                                                    <div class="button minus">
                                                        <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                                                            <i class="ti-minus"></i>
                                                        </button>
                                                    </div>
													<input type="hidden" name="slug" value="{{$product->slug}}">
                                                    <input type="text" name="quant[1]" class="input-number"  data-min="1" data-max="1000" value="1">
                                                    <div class="button plus">
                                                        <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                                                            <i class="ti-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <!--/ End Input Order -->
                                            </div>
                                            <div class="add-to-cart">
                                                <button type="submit" class="btn">Add to cart</button>
                                                <a href="{{route('add-to-wishlist',$product->slug)}}" class="btn min"><i class="ti-heart"></i></a>
                                            </div>
                                        </form>
                                        <div class="default-social">
                                        <!-- ShareThis BEGIN --><div class="sharethis-inline-share-buttons"></div><!-- ShareThis END -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    @endforeach
@endif
<!-- Modal end -->
@endsection

@push('styles')
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=5f2e5abf393162001291e431&product=inline-share-buttons' async='async'></script>
    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=5f2e5abf393162001291e431&product=inline-share-buttons' async='async'></script>
    <style>
        /* Banner Sliding */
        #Gslider .carousel-inner {
        background: #000000;
        color:black;
        }

        #Gslider .carousel-inner{
        height: 550px;
        }
        #Gslider .carousel-inner img{
            width: 100% !important;
            opacity: .8;
        }

        #Gslider .carousel-inner .carousel-caption {
        bottom: 60%;
        }

        #Gslider .carousel-inner .carousel-caption h1 {
        font-size: 50px;
        font-weight: bold;
        line-height: 100%;
        color: #F7941D;
        }

        #Gslider .carousel-inner .carousel-caption p {
        font-size: 18px;
        color: black;
        margin: 28px 0 28px 0;
        }

        #Gslider .carousel-indicators {
        bottom: 70px;
        }

        /* Lucky Wheel Banner Styles */
        .lucky-wheel-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .lucky-wheel-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .lucky-wheel-promo {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }

        .promo-title {
            color: #333;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .promo-title i {
            color: #f39c12;
            margin-right: 10px;
        }

        .promo-description {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .promo-features {
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #555;
        }

        .feature-item i {
            color: #27ae60;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .promo-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .promo-actions .btn {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .promo-actions .btn-primary {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            border: none;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .promo-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }

        .wheel-preview {
            position: relative;
            display: inline-block;
        }

        .wheel-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(45deg, #f39c12, #e67e22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.3);
            animation: wheelRotate 10s linear infinite;
            position: relative;
            z-index: 2;
        }

        .floating-prizes {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
        }

        .prize {
            position: absolute;
            font-size: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        .prize-1 {
            top: 10%;
            left: 50%;
            animation-delay: 0s;
        }

        .prize-2 {
            top: 50%;
            right: 10%;
            animation-delay: 0.5s;
        }

        .prize-3 {
            bottom: 10%;
            left: 50%;
            animation-delay: 1s;
        }

        .prize-4 {
            top: 50%;
            left: 10%;
            animation-delay: 1.5s;
        }

        @keyframes wheelRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .lucky-wheel-banner {
                padding: 40px 0;
            }
            
            .lucky-wheel-promo {
                padding: 30px 20px;
            }
            
            .promo-title {
                font-size: 2rem;
                text-align: center;
            }
            
            .wheel-circle {
                width: 150px;
                height: 150px;
                font-size: 3rem;
            }
            
            .floating-prizes {
                width: 250px;
                height: 250px;
            }
            
            .promo-actions {
                justify-content: center;
            }
        }
    </style>
@endpush

@push('styles')
<style>
/* Walmart Frontend Home Styles */

/* Hero Slider */
.walmart-hero-slider {
  position: relative;
  overflow: hidden;
}

.hero-slide {
  height: 60vh;
  min-height: 500px;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
  display: flex;
  align-items: center;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 113, 206, 0.8) 0%, rgba(0, 76, 145, 0.6) 100%);
}

.hero-content {
  position: relative;
  z-index: 2;
  color: white;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero-description {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.95;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.min-vh-60 {
  min-height: 60vh;
}

/* Category Showcase */
.walmart-category-showcase {
  background: #f8f9fa;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #0071ce;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 0;
}

.walmart-category-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.walmart-category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.category-image {
  position: relative;
  overflow: hidden;
}

.category-image img {
  width: 100%;
  height: 250px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.walmart-category-card:hover .category-image img {
  transform: scale(1.05);
}

.category-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(0, 113, 206, 0.8) 0%, rgba(0, 76, 145, 0.6) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.walmart-category-card:hover .category-overlay {
  opacity: 1;
}

.category-content {
  text-align: center;
  color: white;
}

.category-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

/* Trending Products */
.walmart-trending-products {
  background: #f8f9fa;
}

.walmart-filter-tabs {
  margin-bottom: 2rem;
}

.walmart-filter-btn {
  background: white;
  border: 2px solid #e9ecef;
  color: #495057;
  padding: 0.75rem 1.5rem;
  margin: 0 0.5rem 0.5rem 0;
  border-radius: 25px;
  font-weight: 500;
  transition: all 0.3s ease;
  cursor: pointer;
}

.walmart-filter-btn:hover,
.walmart-filter-btn.active {
  background: #0071ce;
  border-color: #0071ce;
  color: white;
  transform: translateY(-2px);
}

.walmart-product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  height: 100%;
}

.walmart-product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-image-wrapper {
  position: relative;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.walmart-product-card:hover .product-image {
  transform: scale(1.05);
}

.product-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.product-badge.new {
  background: #28a745;
  color: white;
}

.product-badge.hot {
  background: #dc3545;
  color: white;
}

.product-badge.discount {
  background: #ffc220;
  color: #000;
}

.product-badge.out-of-stock {
  background: #6c757d;
  color: white;
}

.product-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.walmart-product-card:hover .product-actions {
  opacity: 1;
}

.action-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  color: #495057;
  text-decoration: none;
}

.action-btn:hover {
  background: #0071ce;
  color: white;
  transform: scale(1.1);
  text-decoration: none;
}

.product-info {
  padding: 1rem;
}

.product-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

.product-title a {
  color: #333;
  text-decoration: none;
}

.product-title a:hover {
  color: #0071ce;
  text-decoration: none;
}

.product-price {
  margin-bottom: 0.75rem;
}

.current-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0071ce;
}

.original-price {
  font-size: 1rem;
  color: #999;
  text-decoration: line-through;
  margin-left: 0.5rem;
}

.commission-info {
  background: #e8f5e8;
  color: #28a745;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: inline-block;
}

/* Featured Banner */
.walmart-featured-banner {
  background: white;
}

.walmart-featured-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.walmart-featured-card:hover {
  transform: translateY(-5px);
}

.featured-image {
  height: 300px;
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.featured-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 113, 206, 0.8) 0%, rgba(0, 76, 145, 0.6) 100%);
}

.featured-content {
  position: relative;
  z-index: 2;
  text-align: center;
  color: white;
  padding: 2rem;
}

.featured-category {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1rem;
  display: inline-block;
}

.featured-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.featured-discount {
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
}

.discount-percent {
  font-size: 2rem;
  font-weight: 700;
  color: #ffc220;
}

/* Hot Items */
.walmart-hot-items {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  color: white;
}

.walmart-hot-items .section-title,
.walmart-hot-items .section-subtitle {
  color: white;
}

.hot-product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
  margin: 0 10px;
}

.hot-product-card:hover {
  transform: translateY(-5px);
}

.hot-product-image {
  position: relative;
  overflow: hidden;
}

.hot-product-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.hot-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #ff4757;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
}

.hot-product-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.hot-product-card:hover .hot-product-actions {
  opacity: 1;
}

.hot-action-btn {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  background: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  color: #495057;
  text-decoration: none;
}

.hot-action-btn:hover {
  background: #ff4757;
  color: white;
  text-decoration: none;
}

.hot-product-info {
  padding: 1rem;
}

.hot-product-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #333;
}

.hot-product-title a {
  color: #333;
  text-decoration: none;
}

.hot-product-title a:hover {
  color: #ff4757;
  text-decoration: none;
}

.hot-product-price {
  margin-bottom: 0.5rem;
}

.hot-current-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #ff4757;
}

.hot-original-price {
  font-size: 1rem;
  color: #999;
  text-decoration: line-through;
  margin-left: 0.5rem;
}

.hot-commission {
  background: #e8f5e8;
  color: #28a745;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: inline-block;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-description {
    font-size: 1.1rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .walmart-filter-btn {
    margin: 0 0.25rem 0.5rem 0;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
  
  .product-image {
    height: 180px;
  }
  
  .hot-product-image img {
    height: 180px;
  }
  
  .featured-image {
    height: 250px;
  }
  
  .featured-content {
    padding: 1.5rem;
  }
  
  .featured-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .hero-slide {
    height: 50vh;
    min-height: 400px;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-description {
    font-size: 1rem;
  }
  
  .section-title {
    font-size: 1.75rem;
  }
  
  .category-image img {
    height: 200px;
  }
  
  .product-image {
    height: 160px;
  }
  
  .hot-product-image img {
    height: 160px;
  }
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
$(document).ready(function(){
    // Walmart Filter Buttons
    $('.walmart-filter-btn').click(function(){
        var filterValue = $(this).attr('data-filter');
        
        // Remove active class from all buttons
        $('.walmart-filter-btn').removeClass('active');
        // Add active class to clicked button
        $(this).addClass('active');
        
        // Filter products
        if(filterValue == '*') {
            $('.product-item').show();
        } else {
            $('.product-item').hide();
            $('.product-item' + filterValue).show();
        }
    });
    
    // Initialize Owl Carousel for Hot Items
    $('.walmart-hot-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        navText: [
            '<i class="fas fa-chevron-left"></i>',
            '<i class="fas fa-chevron-right"></i>'
        ],
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 2
            },
            768: {
                items: 3
            },
            992: {
                items: 4
            }
        }
    });
    
    // Hero Slider Auto-play
    $('#walmartSlider').carousel({
        interval: 5000,
        pause: 'hover'
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Add to cart with animation
    $('.walmart-btn[href*="add-to-cart"]').click(function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalText = $btn.html();
        
        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Adding...');
        $btn.prop('disabled', true);
        
        // Simulate adding to cart (replace with actual AJAX call)
        setTimeout(function() {
            // Redirect to add to cart
            window.location.href = $btn.attr('href');
        }, 500);
    });
    
    // Wishlist animation
    $('a[href*="add-to-wishlist"]').click(function(e) {
        var $btn = $(this);
        var $icon = $btn.find('i');
        
        // Add animation class
        $icon.addClass('fa-beat');
        
        // Remove animation after 1 second
        setTimeout(function() {
            $icon.removeClass('fa-beat');
        }, 1000);
    });
    
    // Product card hover effects
    $('.walmart-product-card, .hot-product-card').hover(
        function() {
            $(this).find('.product-actions, .hot-product-actions').addClass('show');
        },
        function() {
            $(this).find('.product-actions, .hot-product-actions').removeClass('show');
        }
    );
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});

// Legacy Isotope support (for backward compatibility)
/*==================================================================
[ Isotope ]*/
var $topeContainer = $('.isotope-grid');
var $filter = $('.filter-tope-group');

// filter items on button click
$filter.each(function () {
    $filter.on('click', 'button', function () {
        var filterValue = $(this).attr('data-filter');
        $topeContainer.isotope({filter: filterValue});
    });
});

// init Isotope
$(window).on('load', function () {
    var $grid = $topeContainer.each(function () {
        $(this).isotope({
            itemSelector: '.isotope-item',
            layoutMode: 'fitRows',
            percentPosition: true,
            animationEngine : 'best-available',
            masonry: {
                columnWidth: '.isotope-item'
            }
        });
    });
});

var isotopeButton = $('.filter-tope-group button');

$(isotopeButton).each(function(){
    $(this).on('click', function(){
        for(var i=0; i<isotopeButton.length; i++) {
            $(isotopeButton[i]).removeClass('how-active1');
        }
        $(this).addClass('how-active1');
    });
});
    </script>
    <script>
         function cancelFullScreen(el) {
            var requestMethod = el.cancelFullScreen||el.webkitCancelFullScreen||el.mozCancelFullScreen||el.exitFullscreen;
            if (requestMethod) { // cancel full screen.
                requestMethod.call(el);
            } else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
                var wscript = new ActiveXObject("WScript.Shell");
                if (wscript !== null) {
                    wscript.SendKeys("{F11}");
                }
            }
        }

        function requestFullScreen(el) {
            // Supports most browsers and their versions.
            var requestMethod = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen || el.msRequestFullscreen;

            if (requestMethod) { // Native full screen.
                requestMethod.call(el);
            } else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
                var wscript = new ActiveXObject("WScript.Shell");
                if (wscript !== null) {
                    wscript.SendKeys("{F11}");
                }
            }
            return false
        }
    </script>

@endpush

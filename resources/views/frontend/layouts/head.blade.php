<!-- Meta Tag -->
@yield('meta')
<!-- Title Tag  -->
<title>@yield('title')</title>
<!-- Favicon -->
<link rel="icon" type="image/png" href="images/favicon.png">
<!-- Web Font -->
<link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

<!-- StyleSheet -->
<link rel="manifest" href="/manifest.json">
<!-- Bootstrap -->
<link rel="stylesheet" href="{{asset('frontend/css/bootstrap.css')}}">
<!-- Magnific Popup -->
<link rel="stylesheet" href="{{asset('frontend/css/magnific-popup.min.css')}}">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{asset('frontend/css/font-awesome.css')}}">
<!-- Font Awesome 6 CDN for better icon support -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Fancybox -->
<link rel="stylesheet" href="{{asset('frontend/css/jquery.fancybox.min.css')}}">
<!-- Themify Icons -->
<link rel="stylesheet" href="{{asset('frontend/css/themify-icons.css')}}">
<!-- Nice Select CSS -->
<link rel="stylesheet" href="{{asset('frontend/css/niceselect.css')}}">
<!-- Animate CSS -->
<link rel="stylesheet" href="{{asset('frontend/css/animate.css')}}">
<!-- Flex Slider CSS -->
<link rel="stylesheet" href="{{asset('frontend/css/flex-slider.min.css')}}">
<!-- Owl Carousel -->
<link rel="stylesheet" href="{{asset('frontend/css/owl-carousel.css')}}">
<!-- Slicknav -->
<link rel="stylesheet" href="{{asset('frontend/css/slicknav.min.css')}}">
<!-- Jquery Ui -->
<link rel="stylesheet" href="{{asset('frontend/css/jquery-ui.css')}}">

<!-- Walmart StyleSheet -->
<link rel="stylesheet" href="{{asset('frontend/css/reset.css')}}">
<link rel="stylesheet" href="{{asset('frontend/css/style.css')}}">
<link rel="stylesheet" href="{{asset('frontend/css/responsive.css')}}">

<!-- Walmart Theme CSS -->
<link rel="stylesheet" href="{{asset('frontend/css/walmart-theme.css')}}">
<link rel="stylesheet" href="{{asset('frontend/css/walmart-components.css')}}">
<link rel="stylesheet" href="{{asset('frontend/css/walmart-responsive.css')}}">

<!-- Walmart Theme JavaScript -->
<script src="{{asset('frontend/js/walmart-theme.js')}}" defer></script>
<script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=5f2e5abf393162001291e431&product=inline-share-buttons' async='async'></script>
<style>
    /* Multilevel dropdown */
    .dropdown-submenu {
    position: relative;
    }

    .dropdown-submenu>a:after {
    content: "\f0da";
    float: right;
    border: none;
    font-family: 'FontAwesome';
    }

    .dropdown-submenu>.dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: 0px;
    margin-left: 0px;
    }

    /* Deposit Button Styles */
    .header.shop .right-bar .sinlge-bar .deposit-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white !important;
        padding: 8px 15px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .header.shop .right-bar .sinlge-bar .deposit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .header.shop .right-bar .sinlge-bar .deposit-btn:hover::before {
        left: 100%;
    }

    .header.shop .right-bar .sinlge-bar .deposit-btn:hover {
        background: linear-gradient(135deg, #218838, #1ea085);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        color: white !important;
        text-decoration: none;
    }

    .header.shop .right-bar .sinlge-bar .deposit-btn i {
        font-size: 16px;
    }

    .header.shop .right-bar .sinlge-bar .deposit-btn .deposit-text {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .header.shop .right-bar .sinlge-bar .deposit-btn {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .header.shop .right-bar .sinlge-bar .deposit-btn .deposit-text {
            font-size: 12px;
        }
    }

    @media (max-width: 768px) {
        .header.shop .right-bar .sinlge-bar .deposit-btn {
            padding: 6px 10px;
            font-size: 12px;
        }
        
        .header.shop .right-bar .sinlge-bar .deposit-btn .deposit-text {
            display: none;
        }
        
        .header.shop .right-bar .sinlge-bar .deposit-btn i {
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .header.shop .right-bar .sinlge-bar .deposit-btn {
            padding: 5px 8px;
            border-radius: 20px;
        }
    }

    /*
</style>
@stack('styles')

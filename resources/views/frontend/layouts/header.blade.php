<style>
/* Walmart Search Bar CSS */
.walmart-search-container {
    padding: 10px 0;
    width: 100%;
}

/* Topbar Mobile Enhancements */
.walmart-topbar {
    background: linear-gradient(135deg, #0071ce 0%, #004c91 100%);
    position: relative;
}

.walmart-topbar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
    z-index: 0;
}

.walmart-topbar > .container {
    position: relative;
    z-index: 1;
}

@media (max-width: 767px) {
    .walmart-topbar {
        padding: 12px 0;
    }
    
    .top-left {
        display: none; /* Hide top left on mobile to give more space */
    }
    
    .right-content {
        width: 100%;
    }
}

.walmart-search-form {
    width: 100%;
}

.walmart-search-wrapper {
    display: flex;
    align-items: stretch;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 50px;
    max-width: 100%;
}

.walmart-search-wrapper:hover {
    border-color: #0071ce;
    box-shadow: 0 2px 8px rgba(0, 113, 206, 0.15);
}

.walmart-search-wrapper:focus-within,
.walmart-search-wrapper.focused {
    border-color: #0071ce;
    box-shadow: 0 2px 12px rgba(0, 113, 206, 0.2);
}

/* Category Dropdown */
.search-category-dropdown {
    position: relative;
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    min-width: 160px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.category-select {
    width: 100%;
    height: 100%;
    padding: 0 30px 0 20px;
    border: none;
    background: transparent;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    outline: none;
    font-weight: 500;
}

.category-select:focus {
    outline: none;
}

.dropdown-arrow {
    position: absolute;
    right: 15px;
    color: #666;
    font-size: 12px;
    pointer-events: none;
    transition: transform 0.3s ease;
}

.search-category-dropdown:hover .dropdown-arrow {
    color: #0071ce;
}

/* Search Input */
.search-input-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    min-width: 0;
    position: relative;
}

.walmart-search-input {
    width: 100%;
    height: 100%;
    padding: 0 20px;
    border: none;
    background: transparent;
    font-size: 16px;
    color: #333;
    outline: none;
    font-weight: 400;
    position: relative;
    z-index: 1;
    box-sizing: border-box;
}



.walmart-search-input::placeholder {
    color: #999;
    font-weight: 400;
}

.walmart-search-input:focus::placeholder {
    color: #ccc;
}

/* Search Button */
.walmart-search-btn {
    background: #0071ce;
    border: none;
    padding: 0 30px;
    height: 100%;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 0 50px 50px 0;
}

.walmart-search-btn:hover {
    background: #004c91;
}

.walmart-search-btn:active {
    transform: scale(0.98);
}

.walmart-search-btn i {
    font-size: 16px;
}

.btn-text {
    font-size: 14px;
    font-weight: 600;
}

/* Wallet Balance CSS */
.wallet-balance-display {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    padding: 10px 14px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    font-size: 14px;
    white-space: nowrap;
    color: #495057;
    vertical-align: middle;
    height: 40px;
    box-sizing: border-box;
}

.wallet-balance-display i {
    margin-right: 8px;
    color: #28a745;
    font-size: 16px;
}

.balance-text {
    color: #28a745;
    font-weight: 700;
    font-size: 15px;
}

/* Responsive Design */
@media (max-width: 991px) {
    .walmart-search-wrapper {
        height: 45px;
        border-radius: 45px;
    }
    
    .walmart-search-input {
        font-size: 15px;
        padding: 0 15px;
    }
    
    .walmart-search-btn {
        padding: 0 25px;
        min-width: 100px;
        border-radius: 0 45px 45px 0;
    }
    
    .btn-text {
        display: none;
    }
    
    .wallet-balance-display {
        font-size: 13px;
        padding: 8px 12px;
        height: 36px;
    }
    
    .balance-text {
        font-size: 14px;
    }
    
    .wallet-balance-display i {
        font-size: 14px;
        margin-right: 6px;
    }
}

@media (max-width: 767px) {
    .walmart-search-container {
        padding: 8px 0;
    }
    
    .walmart-search-wrapper {
        height: 42px;
        border-radius: 42px;
        border-width: 1px;
    }
    
    .walmart-search-input {
        font-size: 14px;
        padding: 0 12px;
    }
    
    .walmart-search-input::placeholder {
        font-size: 13px;
    }
    
    .walmart-search-btn {
        padding: 0 20px;
        min-width: 80px;
        border-radius: 0 42px 42px 0;
    }
    
    .walmart-search-btn i {
        font-size: 14px;
    }
    
    .wallet-balance-display {
        font-size: 12px;
        padding: 6px 10px;
        height: 32px;
    }
    
    .balance-text {
        font-size: 13px;
    }
    
    .wallet-balance-display i {
        font-size: 13px;
        margin-right: 5px;
    }
}

@media (max-width: 480px) {
    .walmart-search-wrapper {
        height: 40px;
        border-radius: 40px;
    }
    
    .walmart-search-input {
        font-size: 13px;
        padding: 0 10px;
    }
    
    .walmart-search-input::placeholder {
        font-size: 12px;
    }
    
    .walmart-search-btn {
        padding: 0 15px;
        min-width: 60px;
        border-radius: 0 40px 40px 0;
    }
    
    .walmart-search-btn i {
        font-size: 13px;
    }
}

/* Top Right Mobile Fix */
@media (max-width: 991px) {
    .right-content {
        text-align: right !important;
    }
    
    .right-content .list-main {
        justify-content: flex-end !important;
        flex-direction: row !important;
    }
    
    /* Improve tablet font sizes */
    .right-content .list-main li a {
        font-size: 18px !important;
        padding: 12px 16px !important;
        font-weight: 600;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
    }
    
    .right-content .list-main li a:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }
    
    .right-content .list-main li i {
        font-size: 18px !important;
        margin-right: 10px;
    }
}

@media (max-width: 767px) {
    .topbar {
        padding: 12px 0 !important;
    }
    
    .right-content .list-main {
        flex-wrap: wrap;
        gap: 0.8rem;
        justify-content: center !important;
    }
    
    .right-content .list-main li {
        margin: 0 0.2rem;
        flex: 1;
        min-width: fit-content;
    }
    
    .right-content .list-main li a {
        font-size: 20px !important;
        padding: 14px 18px !important;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-weight: 800;
        white-space: nowrap;
        min-height: 52px;
        box-sizing: border-box;
        border: 2px solid rgba(255, 255, 255, 0.4);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        filter: brightness(1.1);
    }
    
    .right-content .list-main li a:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.7);
        color: #ffffff !important;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.9);
        filter: brightness(1.2);
    }
    
    .right-content .list-main li i {
        font-size: 20px !important;
        margin-right: 12px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.8)) brightness(1.1);
        color: #ffffff !important;
    }
}

@media (max-width: 576px) {
    .right-content .list-main {
        justify-content: center !important;
        gap: 0.6rem;
    }
    
    .right-content .list-main li {
        flex: 0 0 auto;
        margin: 0 0.1rem;
    }
    
    .right-content .list-main li a {
        font-size: 22px !important;
        padding: 16px 18px !important;
        min-height: 56px;
        border-radius: 25px;
        font-weight: 900;
        background: rgba(255, 255, 255, 0.3);
        border: 2px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        color: #ffffff !important;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.9);
        filter: brightness(1.15);
    }
    
    .right-content .list-main li a:hover {
        background: rgba(255, 255, 255, 0.45);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        color: #ffffff !important;
        text-shadow: 0 3px 8px rgba(0, 0, 0, 1);
        filter: brightness(1.3);
    }
    
    .right-content .list-main li i {
        font-size: 22px !important;
        margin-right: 10px;
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.9)) brightness(1.15);
        color: #ffffff !important;
    }
}

@media (max-width: 480px) {
    .right-content .list-main {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.8rem;
        align-items: center;
        justify-content: center !important;
        padding: 8px 0;
    }
    
    .right-content .list-main li {
        margin: 0;
        flex: 0 0 auto;
        min-width: 120px;
    }
    
    .right-content .list-main li a {
        font-size: 24px !important;
        padding: 18px 22px !important;
        text-align: center;
        justify-content: center;
        min-height: 60px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.35);
        border: 3px solid rgba(255, 255, 255, 0.6);
        font-weight: 900;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        text-shadow: 0 3px 8px rgba(0, 0, 0, 1);
        color: #ffffff !important;
        filter: brightness(1.2) contrast(1.1);
    }
    
    .right-content .list-main li a:hover {
        background: rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.8);
        color: #ffffff !important;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 1);
        filter: brightness(1.35) contrast(1.2);
    }
    
    .right-content .list-main li i {
        font-size: 24px !important;
        margin-right: 12px;
        filter: drop-shadow(0 3px 8px rgba(0, 0, 0, 1)) brightness(1.2);
        color: #ffffff !important;
    }
    
    /* Special styling for very small screens */
    @media (max-width: 380px) {
        .right-content .list-main li {
            min-width: 100px;
        }
        
        .right-content .list-main li a {
            font-size: 22px !important;
            padding: 16px 18px !important;
            min-height: 56px;
            color: #ffffff !important;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.9);
            filter: brightness(1.15) contrast(1.05);
        }
        
        .right-content .list-main li i {
            font-size: 22px !important;
            margin-right: 10px;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.9)) brightness(1.15);
            color: #ffffff !important;
        }
    }
}

/* Notification Bell CSS */
.notification-wrapper {
    position: relative;
    display: inline-block;
}

.notification-btn {
    position: relative;
    color: #666 !important;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.notification-btn:hover {
    background: #f8f9fa;
    color: #0071ce !important;
}

.notification-btn i {
    font-size: 18px;
}

.notification-count {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #dc3545 !important;
    color: white !important;
    border-radius: 50%;
    font-size: 10px;
    min-width: 16px;
    height: 16px;
    line-height: 16px;
    text-align: center;
    font-weight: bold;
    padding: 0;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
    max-height: 400px;
    overflow: hidden;
}

.notification-dropdown.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.notification-header h6 {
    margin: 0;
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.mark-all-read {
    color: #0071ce;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
}

.mark-all-read:hover {
    text-decoration: underline;
    color: #004c91;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
    padding: 0;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s ease;
    position: relative;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #fff3cd;
    border-left: 3px solid #ffc107;
}

.notification-item.unread:before {
    content: '';
    position: absolute;
    left: 8px;
    top: 20px;
    width: 8px;
    height: 8px;
    background: #dc3545;
    border-radius: 50%;
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #666;
}

.notification-icon.warning {
    background: #fff3cd;
    color: #856404;
}

.notification-icon.success {
    background: #d4edda;
    color: #155724;
}

.notification-icon.info {
    background: #d1ecf1;
    color: #0c5460;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin: 0 0 4px 0;
    line-height: 1.4;
    word-wrap: break-word;
}

.notification-time {
    font-size: 11px;
    color: #666;
    margin: 0;
}

.notification-loading,
.notification-empty {
    padding: 30px 20px;
    text-align: center;
    color: #666;
    font-size: 14px;
}

.notification-empty i {
    font-size: 24px;
    margin-bottom: 8px;
    display: block;
    color: #ccc;
}

.notification-footer {
    padding: 12px 20px;
    border-top: 1px solid #eee;
    text-align: center;
    background: #f8f9fa;
}

.view-all-notifications {
    color: #0071ce;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

.view-all-notifications:hover {
    text-decoration: underline;
    color: #004c91;
}

/* Responsive for notification dropdown */
@media (max-width: 767px) {
    .notification-dropdown {
        width: 320px;
        right: -50px;
    }
}

@media (max-width: 480px) {
    .notification-dropdown {
        width: 280px;
        right: -80px;
    }
    
    .notification-item {
        padding: 12px 15px;
    }
    
    .notification-title {
        font-size: 13px;
    }
    
    .notification-time {
        font-size: 10px;
    }
    
    .notification-header {
        padding: 12px 15px;
    }
    
    .notification-footer {
        padding: 10px 15px;
    }
}
</style>

<header class="header shop walmart-header">
    <!-- Walmart Topbar -->
    <div class="topbar walmart-topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="list-main">
                            
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
                            <div class="walmart-search-wrapper">
                                <div class="search-input-wrapper">
                                    <input name="search" type="search" class="walmart-search-input" autocomplete="off">
                                </div>
                                <button class="walmart-search-btn" type="submit">
                                    <i class="fas fa-search"></i>
                                    <span class="btn-text">Search</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-12">
                    <div class="right-bar walmart-actions">
                        <!-- Wallet Balance -->
                        @auth
                        <div class="sinlge-bar wallet-balance-bar">
                            <div class="wallet-balance-display">
                                <i class="fas fa-wallet"></i>
                                <span class="balance-text">${{number_format(Auth::user()->wallet_balance ?? 0, 2)}}</span>
                            </div>
                        </div>
                        @endauth
                        
                        <!-- Deposit Request Button -->
                        @auth
                        <div class="sinlge-bar">
                            <a href="{{route('deposit.request')}}" class="walmart-btn walmart-btn-success walmart-btn-sm deposit-btn" title="Add Money">
                                <i class="fas fa-plus-circle"></i>
                                <span class="deposit-text">Add Money</span>
                            </a>
                        </div>
                        @endauth
                        
                        <!-- Notifications Bell -->
                        @auth
                        <div class="sinlge-bar notification-bar">
                            <div class="notification-wrapper">
                                <a href="#" class="walmart-icon-btn notification-btn" id="notificationBell" title="Notifications">
                                    <i class="fas fa-bell"></i>
                                    <span class="walmart-badge notification-count" id="notificationCount" style="display: none;">0</span>
                                    <span class="icon-label d-none d-lg-inline">Alerts</span>
                                </a>
                                
                                <!-- Notification Dropdown -->
                                <div class="notification-dropdown" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h6>Notifications</h6>
                                        <a href="#" id="markAllRead" class="mark-all-read">Mark all read</a>
                                    </div>
                                    <div class="notification-list" id="notificationList">
                                        <div class="notification-loading">
                                            <i class="fas fa-spinner fa-spin"></i> Loading notifications...
                                        </div>
                                    </div>
                                    <div class="notification-footer">
                                        <a href="{{route('user')}}#notifications" class="view-all-notifications">View All Notifications</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- VIP Badge (English, next to notification bell) --}}
                        @php
                            $rawVipName = optional(Auth::user())->vip_level_name
                                ?? optional(optional(Auth::user())->currentVipPlan)->name
                                ?? 'FREE';
                            $name = trim($rawVipName);
                            $vipNameMap = [
                                'FREE' => 'FREE',
                                'VIP BẠC' => 'VIP Silver',
                                'BẠC' => 'Silver',
                                'VIP BAC' => 'VIP Silver',
                                'BAC' => 'Silver',
                                'VIP BẠCH KIM' => 'VIP Platinum',
                                'BẠCH KIM' => 'Platinum',
                                'VIP BACH KIM' => 'VIP Platinum',
                                'BACH KIM' => 'Platinum',
                                'VIP KIM CƯƠNG' => 'VIP Diamond',
                                'KIM CƯƠNG' => 'Diamond',
                                'VIP LEGEND' => 'VIP Legend',
                                'LEGEND' => 'Legend',
                            ];
                            $displayVip = $vipNameMap[strtoupper($name)] ?? $name;
                        @endphp
                        <div class="sinlge-bar vip-badge-bar" style="margin-left:8px;">
                            <div class="wallet-balance-display" style="height:36px;">
                                <i class="fas fa-crown" style="color:#f0ad4e;"></i>
                                <span class="balance-text" style="color:#f0ad4e;">
                                    VIP: {{ $displayVip ?: 'FREE' }}
                                </span>
                            </div>
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

<!-- Notification JavaScript -->
@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllRead');
    
    let isDropdownOpen = false;
    let notifications = [];
    let pollingInterval = null;

    // Toggle notification dropdown
    notificationBell.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-wrapper')) {
            closeDropdown();
        }
    });

    // Prevent dropdown from closing when clicking inside
    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Mark all as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        markAllAsRead();
    });

    function toggleDropdown() {
        if (isDropdownOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    function openDropdown() {
        notificationDropdown.classList.add('show');
        isDropdownOpen = true;
        loadNotifications();
    }

    function closeDropdown() {
        notificationDropdown.classList.remove('show');
        isDropdownOpen = false;
    }

    // Load notifications from database
    function loadNotifications() {
        showLoading();
        
        fetch('/user/notifications/get', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notifications = data.notifications || [];
                updateNotificationCount(data.unread_count || 0);
                renderNotifications(notifications);
            } else {
                showError('Failed to load notifications');
            }
        })
        .catch(error => {
            console.error('Notification fetch error:', error);
            showError('Error loading notifications');
        });
    }

    function showLoading() {
        notificationList.innerHTML = `
            <div class="notification-loading">
                <i class="fas fa-spinner fa-spin"></i> Loading notifications...
            </div>
        `;
    }

    function showError(message) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-exclamation-triangle"></i>
                ${message}
            </div>
        `;
    }

    function renderNotifications(notifications) {
        if (!notifications || notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    No notifications yet
                </div>
            `;
            return;
        }

        const notificationHtml = notifications.map(notification => {
            const isUnread = notification.read_at === null;
            const iconClass = getIconClass(notification.data);
            const timeAgo = formatTimeAgo(notification.created_at);
            
            return `
                <div class="notification-item ${isUnread ? 'unread' : ''}" 
                     onclick="handleNotificationClick('${notification.id}', '${notification.data.actionURL || '#'}')">
                    <div class="notification-icon ${iconClass}">
                        <i class="${notification.data.fas || 'fas fa-bell'}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.data.title || 'Notification'}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                </div>
            `;
        }).join('');

        notificationList.innerHTML = notificationHtml;
    }

    function getIconClass(data) {
        if (data.fas && data.fas.includes('exclamation')) return 'warning';
        if (data.fas && data.fas.includes('check')) return 'success';
        if (data.fas && data.fas.includes('info')) return 'info';
        return '';
    }

    function formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
        if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + 'd ago';
        return date.toLocaleDateString();
    }

    function updateNotificationCount(count) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.style.display = 'inline-block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    // Handle notification click
    window.handleNotificationClick = function(notificationId, actionURL) {
        markAsRead(notificationId);
        
        if (actionURL && actionURL !== '#') {
            window.open(actionURL, '_blank');
        }
    };

    function markAsRead(notificationId) {
        fetch('/user/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to mark as read
                const notificationItem = document.querySelector(`[onclick*="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                }
                // Refresh count
                loadNotificationCount();
            }
        })
        .catch(error => {
            console.error('Mark as read error:', error);
        });
    }

    function markAllAsRead() {
        fetch('/user/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread class from all items
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                updateNotificationCount(0);
            }
        })
        .catch(error => {
            console.error('Mark all as read error:', error);
        });
    }

    // Load notification count only
    function loadNotificationCount() {
        fetch('/user/notifications/count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount(data.count);
            }
        })
        .catch(error => {
            console.error('Count fetch error:', error);
        });
    }

    // Real-time updates with polling (every 30 seconds)
    function startPolling() {
        loadNotificationCount(); // Initial load
        
        pollingInterval = setInterval(function() {
            if (!isDropdownOpen) {
                loadNotificationCount();
            } else {
                loadNotifications(); // Full reload if dropdown is open
            }
        }, 30000); // Poll every 30 seconds
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // Start polling when page loads
    startPolling();

    // Stop polling when page unloads
    window.addEventListener('beforeunload', stopPolling);

    // Optional: Use Page Visibility API to pause/resume polling
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });
});
</script>
@endauth
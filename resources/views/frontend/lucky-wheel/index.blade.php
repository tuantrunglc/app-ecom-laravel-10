@extends('frontend.layouts.master')

@section('title', 'Lucky Wheel')

@section('main-content')
<div class="lucky-wheel-page">
    <!-- Background Animation -->
    <div class="background-animation">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
    </div>

    <!-- Sound Toggle Button -->
    <button class="sound-toggle" id="soundToggle" title="Bật/Tắt âm thanh">
        <i class="fas fa-volume-up"></i>
    </button>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <!-- Header Section -->
                <div class="wheel-header text-center mb-5">
                    <div class="header-content">
                        <h1 class="wheel-title">
                            <i class="fas fa-star-of-life spinning-icon"></i>
                            LUCKY WHEEL
                            <i class="fas fa-star-of-life spinning-icon"></i>
                        </h1>
                        <p class="wheel-subtitle">Spin to win attractive prizes!</p>
                        <div class="title-decoration">
                            <div class="decoration-line"></div>
                            <i class="fas fa-gem decoration-gem"></i>
                            <div class="decoration-line"></div>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                @auth
                <div class="user-info-card mb-4">
                    <div class="user-info-content">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details">
                            <h5 class="user-name">Hello {{ Auth::user()->name }}!</h5>
                            <div class="spin-info">
                                <span class="spin-count">{{ $userSpinsToday }}</span> / {{ $settings['max_spins_per_day'] }} spins today
                            </div>
                            @if($canSpin)
                                <div class="remaining-spins">
                                    <i class="fas fa-fire"></i> {{ $settings['max_spins_per_day'] - $userSpinsToday }} spins left!
                                </div>
                            @else
                                <div class="no-spins">
                                    <i class="fas fa-clock"></i> No spins left today!
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endauth

                @guest
                @if($settings['require_login'])
                <div class="login-required-card mb-4">
                    <div class="login-content">
                        <i class="fas fa-lock login-icon"></i>
                        <h5>Login to join the wheel!</h5>
                        <a href="{{ route('login.form') }}" class="btn btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login now
                        </a>
                    </div>
                </div>
                @endif
                @endguest

                <!-- Main Wheel Section -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="wheel-section">
                            <div class="wheel-container">
                                <!-- Particles Effect -->
                                <div class="particles-container" id="particles"></div>
                                
                                <!-- Wheel Glow Effect -->
                                <div class="wheel-glow"></div>
                                
                                <!-- Wheel Wrapper -->
                                <div class="wheel-wrapper">
                                    <canvas id="wheelCanvas" width="450" height="450"></canvas>
                                    <div class="wheel-pointer">
                                        <div class="pointer-inner"></div>
                                    </div>
                                    <div class="wheel-center">
                                        <div class="center-circle">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Spin Button -->
                                <div class="spin-button-container">
                                    <button id="spinBtn" class="spin-button {{ !$canSpin ? 'disabled' : '' }}" 
                                            {{ !$canSpin ? 'disabled' : '' }}>
                                        <div class="button-content">
                                            <i class="fas fa-sync-alt spin-icon"></i>
                                            <span id="spinBtnText">{{ $canSpin ? 'SPIN NOW!' : 'NO SPINS LEFT' }}</span>
                                        </div>
                                        <div class="button-glow"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Prizes List -->
                        <div class="prizes-section">
                            <div class="prizes-header">
                                <h5><i class="fas fa-trophy"></i> Prize List</h5>
                            </div>
                            <div class="prizes-container">
                                @foreach($prizes as $prize)
                                <div class="prize-item" data-prize-id="{{ $prize->id }}">
                                    <div class="prize-content">
                                        <div class="prize-icon-wrapper">
                                            @if($prize->image)
                                            <img src="{{ asset('storage/' . $prize->image) }}" alt="{{ $prize->name }}" 
                                                 class="prize-image">
                                            @else
                                            <div class="prize-icon">
                                                <i class="fas fa-gift"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="prize-details">
                                            <h6 class="prize-name">{{ $prize->name }}</h6>
                                            @if($prize->description)
                                            <p class="prize-description">{{ $prize->description }}</p>
                                            @endif
                                            <div class="prize-probability">
                                                <i class="fas fa-percentage"></i> {{ $prize->probability }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- History Link -->
                            @auth
                            <div class="history-section">
                                <a href="{{ route('lucky-wheel.history') }}" class="history-button">
                                    <i class="fas fa-history"></i> Spin History
                                </a>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content result-modal">
            <div class="modal-header result-modal-header">
                <h4 class="modal-title w-100 text-center" id="resultModalTitle">Spin Result</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body result-modal-body" id="resultModalBody">
                <!-- Result content will be inserted here -->
            </div>
            <div class="modal-footer result-modal-footer">
                <button type="button" class="btn btn-result-close" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                @auth
                <a href="{{ route('lucky-wheel.history') }}" class="btn btn-result-history">
                    <i class="fas fa-history"></i> View History
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
        </div>
        <h4>Processing...</h4>
        <p>Please wait a moment</p>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Page Background */
.lucky-wheel-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow-x: hidden;
    padding: 2rem 0;
}

/* Background Animation */
.background-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
}

.shape {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.shape-1 { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
.shape-2 { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: 2s; }
.shape-3 { width: 40px; height: 40px; top: 80%; left: 20%; animation-delay: 4s; }
.shape-4 { width: 100px; height: 100px; top: 10%; left: 70%; animation-delay: 1s; }
.shape-5 { width: 50px; height: 50px; top: 40%; left: 90%; animation-delay: 3s; }

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
    50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
}

/* Header Section */
.wheel-header {
    position: relative;
    z-index: 2;
    margin-bottom: 3rem;
}

.header-content {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.wheel-title {
    font-size: 3rem;
    font-weight: 900;
    background: linear-gradient(45deg, #FFD700, #FFA500, #FF6347);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.spinning-icon {
    animation: spin-slow 3s linear infinite;
    margin: 0 1rem;
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.wheel-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1.5rem;
}

.title-decoration {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.decoration-line {
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #FFD700, transparent);
}

.decoration-gem {
    color: #FFD700;
    font-size: 1.5rem;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* User Info Card */
.user-info-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
    backdrop-filter: blur(10px);
}

.user-info-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    font-size: 3rem;
    color: #667eea;
}

.user-name {
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.spin-info {
    color: #666;
    margin-bottom: 0.5rem;
}

.spin-count {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-weight: bold;
}

.remaining-spins {
    color: #28a745;
    font-weight: 600;
}

.no-spins {
    color: #dc3545;
    font-weight: 600;
}

/* Login Required Card */
.login-required-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
}

.login-icon {
    font-size: 3rem;
    color: #ffc107;
    margin-bottom: 1rem;
}

.btn-login {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Wheel Section */
.wheel-section {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 2;
}

.wheel-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.particles-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.wheel-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255, 215, 0, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    animation: glow-pulse 3s ease-in-out infinite;
    z-index: 1;
}

@keyframes glow-pulse {
    0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
}

.wheel-wrapper {
    position: relative;
    display: inline-block;
    z-index: 3;
}

#wheelCanvas {
    border-radius: 50%;
    box-shadow: 
        0 0 30px rgba(255, 215, 0, 0.5),
        0 0 60px rgba(255, 215, 0, 0.3),
        0 10px 30px rgba(0,0,0,0.3);
    transition: transform 4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border: 5px solid rgba(255, 255, 255, 0.8);
}

.wheel-pointer {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}

.pointer-inner {
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 40px solid #ff4757;
    filter: drop-shadow(0 3px 6px rgba(0,0,0,0.3));
    position: relative;
}

.pointer-inner::after {
    content: '';
    position: absolute;
    top: -35px;
    left: -15px;
    width: 30px;
    height: 30px;
    background: #ff4757;
    border-radius: 50%;
    border: 3px solid white;
}

.wheel-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 5;
}

.center-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    font-size: 1.5rem;
    color: white;
    animation: center-pulse 2s ease-in-out infinite;
}

@keyframes center-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Spin Button */
.spin-button-container {
    position: relative;
    z-index: 4;
}

.spin-button {
    position: relative;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    border: none;
    border-radius: 50px;
    padding: 1rem 3rem;
    font-size: 1.2rem;
    font-weight: 900;
    color: white;
    text-transform: uppercase;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.4);
}

.spin-button:not(.disabled):hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(255, 107, 107, 0.6);
}

.spin-button:not(.disabled):active {
    transform: translateY(-1px);
}

.spin-button.disabled {
    background: #6c757d;
    cursor: not-allowed;
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.button-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.spin-icon {
    transition: transform 0.3s ease;
}

.spin-button:not(.disabled):hover .spin-icon {
    transform: rotate(180deg);
}

.button-glow {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.spin-button:not(.disabled):hover .button-glow {
    left: 100%;
}

/* Prizes Section */
.prizes-section {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 2;
    height: fit-content;
}

.prizes-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.prizes-header h5 {
    color: #333;
    font-weight: 700;
    font-size: 1.3rem;
}

.prizes-header i {
    color: #FFD700;
    margin-right: 0.5rem;
}

.prizes-container {
    max-height: 450px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.prizes-container::-webkit-scrollbar {
    width: 6px;
}

.prizes-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.prizes-container::-webkit-scrollbar-thumb {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border-radius: 10px;
}

.prize-item {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.prize-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border-left-color: #667eea;
}

.prize-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.prize-icon-wrapper {
    flex-shrink: 0;
}

.prize-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #f0f0f0;
}

.prize-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #667eea, #764ba2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.prize-details {
    flex-grow: 1;
}

.prize-name {
    color: #333;
    font-weight: 600;
    margin-bottom: 0.3rem;
    font-size: 1rem;
}

.prize-description {
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.prize-probability {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 0.2rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* History Section */
.history-section {
    margin-top: 1.5rem;
    text-align: center;
    padding-top: 1rem;
    border-top: 2px solid #f0f0f0;
}

.history-button {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.history-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

/* Spinning Animation */
.spinning {
    pointer-events: none;
}

.spinning #wheelCanvas {
    animation: wheel-spin 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

@keyframes wheel-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(var(--spin-angle, 1440deg)); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .wheel-title {
        font-size: 2rem;
    }
    
    .wheel-section {
        padding: 1rem;
    }
    
    #wheelCanvas {
        width: 350px;
        height: 350px;
    }
    
    .spin-button {
        padding: 0.8rem 2rem;
        font-size: 1rem;
    }
    
    .user-info-content {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .wheel-title {
        font-size: 1.5rem;
    }
    
    #wheelCanvas {
        width: 300px;
        height: 300px;
    }
    
    .header-content {
        padding: 1rem;
    }
    
    .decoration-line {
        width: 50px;
    }
}

/* Particle Effect */
.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #FFD700;
    border-radius: 50%;
    pointer-events: none;
    animation: particle-float 3s ease-out forwards;
}

@keyframes particle-float {
    0% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    100% {
        opacity: 0;
        transform: translateY(-100px) scale(0);
    }
}

/* Winner Highlight Effect */
.prize-item.winner {
    animation: winner-glow 2s ease-in-out infinite;
    border-left-color: #FFD700 !important;
}

@keyframes winner-glow {
    0%, 100% {
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.3);
    }
    50% {
        box-shadow: 0 5px 30px rgba(255, 215, 0, 0.6);
    }
}

/* Enhanced Modal Styling */
.result-modal {
    border-radius: 20px;
    border: none;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.result-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1.5rem;
    position: relative;
}

.result-modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="stars" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.3)"/></pattern></defs><rect width="100" height="100" fill="url(%23stars)"/></svg>');
    opacity: 0.3;
}

.result-modal-header .modal-title {
    position: relative;
    z-index: 2;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.result-modal-header .close {
    position: relative;
    z-index: 2;
    opacity: 0.8;
    font-size: 2rem;
    font-weight: 300;
    text-shadow: none;
}

.result-modal-header .close:hover {
    opacity: 1;
}

.result-modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 200px;
}

.result-modal-footer {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    padding: 1.5rem;
    justify-content: center;
    gap: 1rem;
}

.btn-result-close {
    background: linear-gradient(45deg, #6c757d, #5a6268);
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-result-close:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    color: white;
}

.btn-result-history {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-result-history:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-content {
    text-align: center;
    color: white;
}

.loading-spinner {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
}

.spinner-ring {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top: 4px solid #FFD700;
    border-radius: 50%;
    animation: spin-ring 1s linear infinite;
}

.spinner-ring:nth-child(2) {
    width: 60px;
    height: 60px;
    top: 10px;
    left: 10px;
    border-top-color: #ff6b6b;
    animation-duration: 1.5s;
    animation-direction: reverse;
}

.spinner-ring:nth-child(3) {
    width: 40px;
    height: 40px;
    top: 20px;
    left: 20px;
    border-top-color: #4ECDC4;
    animation-duration: 2s;
}

@keyframes spin-ring {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-content h4 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.loading-content p {
    font-size: 1rem;
    opacity: 0.8;
}

/* Sound Toggle Button */
.sound-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.sound-toggle:hover {
    transform: scale(1.1);
    background: white;
}

.sound-toggle.muted {
    color: #dc3545;
}

/* Confetti Animation */
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #FFD700;
    animation: confetti-fall 3s linear forwards;
    z-index: 9999;
}

.confetti:nth-child(odd) {
    background: #ff6b6b;
    animation-duration: 3.5s;
}

.confetti:nth-child(3n) {
    background: #4ECDC4;
    animation-duration: 2.5s;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    const spinBtn = document.getElementById('spinBtn');
    const spinBtnText = document.getElementById('spinBtnText');
    const particlesContainer = document.getElementById('particles');
    
    // Prizes data from server
    const prizes = @json($prizes);
    const settings = @json($settings);
    const canSpin = @json($canSpin);
    
    let isSpinning = false;
    let currentRotation = 0;
    let animationId;
    
    // Enhanced colors for wheel segments with gradients
    const colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
        '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F',
        '#FF8A80', '#82B1FF', '#B9F6CA', '#FFCC02'
    ];
    
    // Particle system
    class Particle {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.vx = (Math.random() - 0.5) * 4;
            this.vy = (Math.random() - 0.5) * 4;
            this.life = 1;
            this.decay = Math.random() * 0.02 + 0.01;
            this.size = Math.random() * 3 + 1;
            this.color = colors[Math.floor(Math.random() * colors.length)];
        }
        
        update() {
            this.x += this.vx;
            this.y += this.vy;
            this.life -= this.decay;
            this.size *= 0.99;
        }
        
        draw(ctx) {
            ctx.save();
            ctx.globalAlpha = this.life;
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
        
        isDead() {
            return this.life <= 0;
        }
    }
    
    let particles = [];
    
    // Create particles
    function createParticles(x, y, count = 10) {
        for (let i = 0; i < count; i++) {
            particles.push(new Particle(x, y));
        }
    }
    
    // Update and draw particles
    function updateParticles() {
        const particleCanvas = document.createElement('canvas');
        particleCanvas.width = canvas.width;
        particleCanvas.height = canvas.height;
        const particleCtx = particleCanvas.getContext('2d');
        
        particles = particles.filter(particle => {
            particle.update();
            particle.draw(particleCtx);
            return !particle.isDead();
        });
        
        // Clear previous particles
        particlesContainer.innerHTML = '';
        if (particles.length > 0) {
            particlesContainer.appendChild(particleCanvas);
        }
        
        if (particles.length > 0) {
            requestAnimationFrame(updateParticles);
        }
    }
    
    // Enhanced wheel drawing with gradients and shadows
    function drawWheel() {
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 200;
        
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (prizes.length === 0) {
            // Draw empty wheel with gradient
            const gradient = ctx.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius);
            gradient.addColorStop(0, '#f8f9fa');
            gradient.addColorStop(1, '#e9ecef');
            
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
            ctx.fillStyle = gradient;
            ctx.fill();
            ctx.strokeStyle = '#dee2e6';
            ctx.lineWidth = 3;
            ctx.stroke();
            
            ctx.fillStyle = '#6c757d';
            ctx.font = 'bold 18px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Chưa có phần thưởng', centerX, centerY);
            return;
        }
        
        const anglePerPrize = (2 * Math.PI) / prizes.length;
        
        // Draw outer ring
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 10, 0, 2 * Math.PI);
        ctx.fillStyle = '#FFD700';
        ctx.fill();
        
        prizes.forEach((prize, index) => {
            const startAngle = index * anglePerPrize;
            const endAngle = startAngle + anglePerPrize;
            
            // Create gradient for each segment
            const gradient = ctx.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius);
            const baseColor = colors[index % colors.length];
            gradient.addColorStop(0, lightenColor(baseColor, 20));
            gradient.addColorStop(1, baseColor);
            
            // Draw segment with shadow
            ctx.save();
            ctx.shadowColor = 'rgba(0,0,0,0.3)';
            ctx.shadowBlur = 5;
            ctx.shadowOffsetX = 2;
            ctx.shadowOffsetY = 2;
            
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();
            
            ctx.restore();
            
            // Draw segment border
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.closePath();
            ctx.strokeStyle = 'rgba(255,255,255,0.8)';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            // Draw text with better styling
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(startAngle + anglePerPrize / 2);
            ctx.textAlign = 'center';
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 14px Arial';
            ctx.shadowColor = 'rgba(0,0,0,0.8)';
            ctx.shadowBlur = 3;
            ctx.shadowOffsetX = 1;
            ctx.shadowOffsetY = 1;
            
            // Smart text positioning and wrapping
            const text = prize.name;
            const maxWidth = radius * 0.6;
            const textRadius = radius * 0.75;
            
            if (text.length > 12) {
                const words = text.split(' ');
                if (words.length > 1) {
                    const mid = Math.ceil(words.length / 2);
                    const line1 = words.slice(0, mid).join(' ');
                    const line2 = words.slice(mid).join(' ');
                    ctx.fillText(line1, textRadius, -8);
                    ctx.fillText(line2, textRadius, 8);
                } else {
                    // Single long word - truncate with ellipsis
                    const truncated = text.length > 15 ? text.substring(0, 12) + '...' : text;
                    ctx.fillText(truncated, textRadius, 0);
                }
            } else {
                ctx.fillText(text, textRadius, 0);
            }
            
            ctx.restore();
        });
        
        // Draw decorative elements
        drawWheelDecorations(centerX, centerY, radius);
    }
    
    // Draw wheel decorations
    function drawWheelDecorations(centerX, centerY, radius) {
        // Draw inner decorative ring
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius - 20, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255,255,255,0.3)';
        ctx.lineWidth = 1;
        ctx.stroke();
        
        // Draw outer decorative ring
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 5, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255,215,0,0.8)';
        ctx.lineWidth = 2;
        ctx.stroke();
    }
    
    // Helper function to lighten colors
    function lightenColor(color, percent) {
        const num = parseInt(color.replace("#",""), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;
        return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + 
                     (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255))
                     .toString(16).slice(1);
    }
    
    // Enhanced spin animation
    function spinWheel() {
        if (isSpinning || !canSpin) return;
        
        isSpinning = true;
        spinBtn.classList.add('disabled');
        spinBtn.classList.add('spinning');
        spinBtnText.textContent = 'ĐANG QUAY...';
        
        // Show loading overlay
        showLoadingOverlay();
        
        // Add spinning class to container
        document.querySelector('.wheel-container').classList.add('spinning');
        
        // Create initial particles
        createParticles(canvas.width / 2, canvas.height / 2, 20);
        updateParticles();
        
        // Play spin sound effect (if enabled)
        playSpinSound();
        
        // Make AJAX request to spin
        $.ajax({
            url: '{{ route("lucky-wheel.spin") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Calculate winning angle with better precision
                    let winningIndex = 0;
                    if (response.prize) {
                        winningIndex = prizes.findIndex(p => p.id === response.prize.id);
                        if (winningIndex === -1) winningIndex = 0;
                    }
                    
                    const anglePerPrize = 360 / prizes.length;
                    const winningAngle = (winningIndex * anglePerPrize) + (anglePerPrize / 2);
                    
                    // More rotations for dramatic effect
                    const baseSpins = 5 + Math.random() * 3; // 5-8 full rotations
                    const finalAngle = (360 - winningAngle) + (360 * baseSpins);
                    
                    // Set CSS custom property for animation
                    canvas.style.setProperty('--spin-angle', finalAngle + 'deg');
                    canvas.style.transform = `rotate(${currentRotation + finalAngle}deg)`;
                    currentRotation += finalAngle;
                    
                    // Highlight winning prize
                    if (response.prize) {
                        highlightWinningPrize(response.prize.id);
                    }
                    
                    // Hide loading overlay after a short delay
                    setTimeout(() => {
                        hideLoadingOverlay();
                    }, 2000);
                    
                    // Show result after animation with delay for suspense
                    setTimeout(() => {
                        // Create celebration particles
                        createParticles(canvas.width / 2, canvas.height / 2, 50);
                        updateParticles();
                        
                        // Create confetti for winners
                        if (response.is_winner) {
                            createConfetti();
                            playWinSound();
                        }
                        
                        showResult(response);
                        isSpinning = false;
                        
                        // Remove spinning classes
                        spinBtn.classList.remove('spinning');
                        document.querySelector('.wheel-container').classList.remove('spinning');
                        
                        // Update button state
                        if (response.remaining_spins && response.remaining_spins > 0) {
                            spinBtn.classList.remove('disabled');
                            spinBtnText.textContent = 'QUAY NGAY!';
                        } else {
                            spinBtnText.textContent = 'HẾT LƯỢT QUAY';
                        }
                        
                        // Update spin count display
                        updateSpinCount(response.remaining_spins);
                        
                    }, 4500); // Increased delay for better suspense
                } else {
                    hideLoadingOverlay();
                    resetSpinButton();
                    showErrorMessage(response.message || 'Có lỗi xảy ra, vui lòng thử lại!');
                }
            },
            error: function() {
                hideLoadingOverlay();
                resetSpinButton();
                showErrorMessage('Có lỗi xảy ra, vui lòng thử lại!');
            }
        });
    }
    
    // Loading overlay functions
    function showLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    function hideLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
    
    // Confetti effect
    function createConfetti() {
        const colors = ['#FFD700', '#ff6b6b', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7'];
        const confettiCount = 100;
        
        for (let i = 0; i < confettiCount; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                document.body.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => {
                    if (confetti.parentNode) {
                        confetti.parentNode.removeChild(confetti);
                    }
                }, 4000);
            }, i * 50);
        }
    }
    
    // Sound effects (placeholder functions - you can implement actual sounds)
    function playSpinSound() {
        // Implement spin sound effect here
        // Example: new Audio('/sounds/spin.mp3').play();
    }
    
    function playWinSound() {
        // Implement win sound effect here
        // Example: new Audio('/sounds/win.mp3').play();
    }
    
    // Reset spin button state
    function resetSpinButton() {
        isSpinning = false;
        spinBtn.classList.remove('disabled', 'spinning');
        document.querySelector('.wheel-container').classList.remove('spinning');
        spinBtnText.textContent = 'QUAY NGAY!';
    }
    
    // Show error message with better styling
    function showErrorMessage(message) {
        // Create a custom toast notification
        const toast = document.createElement('div');
        toast.className = 'error-toast';
        toast.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Highlight winning prize
    function highlightWinningPrize(prizeId) {
        const prizeItems = document.querySelectorAll('.prize-item');
        prizeItems.forEach(item => {
            item.classList.remove('winner');
            if (item.dataset.prizeId == prizeId) {
                item.classList.add('winner');
            }
        });
    }
    
    // Update spin count display
    function updateSpinCount(remainingSpins) {
        const spinCountElement = document.querySelector('.spin-count');
        const remainingElement = document.querySelector('.remaining-spins');
        const noSpinsElement = document.querySelector('.no-spins');
        
        if (spinCountElement) {
            const currentCount = parseInt(spinCountElement.textContent);
            spinCountElement.textContent = currentCount + 1;
        }
        
        if (remainingSpins > 0) {
            if (remainingElement) {
                remainingElement.innerHTML = `<i class="fas fa-fire"></i> Còn ${remainingSpins} lượt quay!`;
            }
            if (noSpinsElement) {
                noSpinsElement.style.display = 'none';
            }
        } else {
            if (remainingElement) {
                remainingElement.style.display = 'none';
            }
            if (noSpinsElement) {
                noSpinsElement.style.display = 'block';
            }
        }
    }
    
    // Enhanced result modal
    function showResult(response) {
        const modal = $('#resultModal');
        const title = $('#resultModalTitle');
        const body = $('#resultModalBody');
        
        if (response.is_winner && response.prize) {
            title.html('🎉 CHÚC MỪNG! 🎉');
            body.html(`
                <div class="result-winner">
                    <div class="winner-animation">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="winner-prize-name">${response.prize.name}</h3>
                    ${response.prize.description ? `<p class="winner-description">${response.prize.description}</p>` : ''}
                    ${response.admin_set ? '<div class="special-badge">🌟 Phần thưởng đặc biệt 🌟</div>' : ''}
                    <div class="winner-message">
                        <p><strong>${response.message}</strong></p>
                    </div>
                    <div class="celebration-text">
                        🎊 Bạn đã trúng thưởng! 🎊
                    </div>
                </div>
            `);
        } else {
            title.html('😊 CẢM ƠN BẠN ĐÃ THAM GIA!');
            body.html(`
                <div class="result-no-win">
                    <div class="no-win-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>${response.message}</h4>
                    <p class="encouragement">Đừng bỏ lỡ cơ hội trong những lần quay tiếp theo nhé!</p>
                    <div class="next-chance">
                        <i class="fas fa-star"></i> Chúc bạn may mắn lần sau! <i class="fas fa-star"></i>
                    </div>
                </div>
            `);
        }
        
        modal.modal('show');
        
        // Add celebration effect for winners
        if (response.is_winner) {
            setTimeout(() => {
                createParticles(window.innerWidth / 2, window.innerHeight / 2, 100);
                updateParticles();
            }, 500);
        }
    }
    
    // Sound management
    let soundEnabled = localStorage.getItem('wheelSoundEnabled') !== 'false';
    const soundToggle = document.getElementById('soundToggle');
    
    function updateSoundToggle() {
        const icon = soundToggle.querySelector('i');
        if (soundEnabled) {
            icon.className = 'fas fa-volume-up';
            soundToggle.classList.remove('muted');
            soundToggle.title = 'Tắt âm thanh';
        } else {
            icon.className = 'fas fa-volume-mute';
            soundToggle.classList.add('muted');
            soundToggle.title = 'Bật âm thanh';
        }
    }
    
    // Event listeners
    spinBtn.addEventListener('click', spinWheel);
    
    soundToggle.addEventListener('click', function() {
        soundEnabled = !soundEnabled;
        localStorage.setItem('wheelSoundEnabled', soundEnabled);
        updateSoundToggle();
    });
    
    // Add keyboard support
    document.addEventListener('keydown', function(e) {
        if (e.code === 'Space' && canSpin && !isSpinning) {
            e.preventDefault();
            spinWheel();
        }
        
        // Toggle sound with 'S' key
        if (e.code === 'KeyS' && !e.ctrlKey && !e.altKey) {
            e.preventDefault();
            soundToggle.click();
        }
    });
    
    // Add hover effects to prize items
    document.querySelectorAll('.prize-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.classList.contains('winner')) {
                this.style.transform = 'translateX(0) scale(1)';
            }
        });
    });
    
    // Initial setup
    updateSoundToggle();
    
    // Initial draw with animation
    setTimeout(() => {
        drawWheel();
        // Add entrance animation
        canvas.style.opacity = '0';
        canvas.style.transform = 'scale(0.8)';
        setTimeout(() => {
            canvas.style.transition = 'all 0.8s ease';
            canvas.style.opacity = '1';
            canvas.style.transform = 'scale(1)';
        }, 100);
    }, 200);
    
    // Redraw wheel on window resize
    window.addEventListener('resize', function() {
        setTimeout(drawWheel, 100);
    });
    
    // Add smooth scrolling to history button
    document.querySelectorAll('a[href*="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add page visibility API to pause animations when tab is not active
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Pause animations when tab is hidden
            canvas.style.animationPlayState = 'paused';
        } else {
            // Resume animations when tab is visible
            canvas.style.animationPlayState = 'running';
        }
    });
});

// Add CSS for toast notifications and enhanced modal
const additionalStyles = `
<style>
.error-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(45deg, #ff4757, #ff3742);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    font-weight: 600;
}

.error-toast.show {
    transform: translateX(0);
}

.result-winner {
    text-align: center;
    padding: 1rem;
}

.winner-animation {
    font-size: 4rem;
    color: #FFD700;
    margin-bottom: 1rem;
    animation: winner-bounce 1s ease-in-out infinite;
}

@keyframes winner-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.winner-prize-name {
    color: #28a745;
    font-weight: bold;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.winner-description {
    color: #666;
    margin-bottom: 1rem;
    font-style: italic;
}

.special-badge {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 1rem;
    font-weight: bold;
    box-shadow: 0 3px 10px rgba(255, 215, 0, 0.3);
}

.winner-message {
    background: rgba(40, 167, 69, 0.1);
    border: 2px solid #28a745;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.celebration-text {
    font-size: 1.2rem;
    font-weight: bold;
    color: #ff6b6b;
    animation: celebration-glow 2s ease-in-out infinite;
}

@keyframes celebration-glow {
    0%, 100% { text-shadow: 0 0 5px rgba(255, 107, 107, 0.5); }
    50% { text-shadow: 0 0 20px rgba(255, 107, 107, 0.8); }
}

.result-no-win {
    text-align: center;
    padding: 1rem;
}

.no-win-icon {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 1rem;
    animation: heart-beat 2s ease-in-out infinite;
}

@keyframes heart-beat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.encouragement {
    color: #666;
    font-style: italic;
    margin-bottom: 1rem;
}

.next-chance {
    color: #667eea;
    font-weight: bold;
    font-size: 1.1rem;
}

/* Enhanced modal styling */
.modal-content {
    border-radius: 15px;
    border: none;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    text-align: center;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    border: none;
    justify-content: center;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);
</script>
@endpush
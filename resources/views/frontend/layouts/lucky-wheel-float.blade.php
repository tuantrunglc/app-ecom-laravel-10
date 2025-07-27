<!-- Lucky Wheel Floating Button -->
@if(Helper::isLuckyWheelEnabled())
<div class="lucky-wheel-float">
    <a href="{{ route('lucky-wheel.index') }}" class="float-btn" title="Vòng Quay May Mắn">
        <i class="fas fa-gift"></i>
        <span class="float-text">Quay Ngay!</span>
        @auth
            @if(Helper::getUserRemainingSpins() > 0)
                <span class="float-badge">{{ Helper::getUserRemainingSpins() }}</span>
            @endif
        @endauth
    </a>
</div>
@endif

<style>
.lucky-wheel-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.float-btn {
    display: flex;
    align-items: center;
    background: linear-gradient(45deg, #f39c12, #e67e22);
    color: white;
    padding: 15px 20px;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(243, 156, 18, 0.4);
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
    position: relative;
}

.float-btn:hover {
    color: white;
    text-decoration: none;
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(243, 156, 18, 0.5);
}

.float-btn i {
    font-size: 1.2rem;
    margin-right: 8px;
}

.float-text {
    font-weight: 600;
    font-size: 0.9rem;
}

@keyframes pulse {
    0% {
        box-shadow: 0 4px 20px rgba(243, 156, 18, 0.4);
    }
    50% {
        box-shadow: 0 4px 20px rgba(243, 156, 18, 0.6), 0 0 0 10px rgba(243, 156, 18, 0.1);
    }
    100% {
        box-shadow: 0 4px 20px rgba(243, 156, 18, 0.4);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .lucky-wheel-float {
        bottom: 20px;
        right: 20px;
    }
    
    .float-btn {
        padding: 12px 16px;
    }
    
    .float-text {
        display: none;
    }
    
    .float-btn i {
        margin-right: 0;
        font-size: 1.5rem;
    }
}

.float-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}
</style>
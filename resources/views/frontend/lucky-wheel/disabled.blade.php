@extends('frontend.layouts.master')

@section('title', 'Vòng Quay Tạm Dừng')

@section('main-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-pause-circle fa-5x text-warning"></i>
                    </div>
                    
                    <h2 class="text-warning mb-3">Vòng Quay Tạm Dừng</h2>
                    
                    <p class="text-muted mb-4">
                        Vòng quay may mắn hiện đang tạm dừng hoạt động để bảo trì và cập nhật.
                        <br>
                        Vui lòng quay lại sau để tham gia những phần thưởng hấp dẫn!
                    </p>
                    
                    <div class="mb-4">
                        <a href="{{ route('home') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home"></i> Về Trang Chủ
                        </a>
                        
                        <button onclick="location.reload()" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt"></i> Thử Lại
                        </button>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Thông báo:</strong> Hãy theo dõi fanpage của chúng tôi để cập nhật thông tin mới nhất về các chương trình khuyến mãi!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    border-radius: 15px;
}

.fa-pause-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
@endpush
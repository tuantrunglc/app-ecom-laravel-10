@extends('user.layouts.master')

@section('main-content')
<!-- Notifications -->
@include('user.layouts.notification')

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">My Reviews</h1>
    <p class="text-muted mb-0">Manage your product reviews and ratings</p>
  </div>
  <div class="d-none d-md-block">
    <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
      <i class="fas fa-shopping-cart mr-2"></i>
      Shop More Products
    </a>
  </div>
</div>

@php
    $total_reviews = count($reviews);
    $active_reviews = $reviews->where('status', 'active')->count();
    $pending_reviews = $reviews->where('status', 'inactive')->count();
    $avg_rating = $reviews->avg('rate');
@endphp

<!-- Review Stats -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card primary">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$total_reviews}}</h3>
          <p>Total Reviews</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-star"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card success">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$active_reviews}}</h3>
          <p>Published</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card warning">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$pending_reviews}}</h3>
          <p>Pending</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card info">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{number_format($avg_rating, 1)}}</h3>
          <p>Avg Rating</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-chart-line"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reviews List -->
<div class="walmart-card">
  <div class="card-header">
    <h4 class="card-title">Review History</h4>
    <div class="d-flex align-items-center">
      <select class="walmart-select" id="statusFilter" style="width: auto; min-width: 120px;">
        <option value="">All Status</option>
        <option value="active">Published</option>
        <option value="inactive">Pending</option>
      </select>
    </div>
  </div>
  <div class="card-body p-0">
    @if(count($reviews)>0)
      <div class="reviews-list">
        @foreach($reviews as $review)
        <div class="review-item" data-status="{{$review->status}}">
          <div class="review-content">
            <div class="row">
              <div class="col-md-2 text-center">
                @if($review->product && $review->product->photo)
                  <img src="{{$review->product->photo}}" alt="{{$review->product->title}}" class="product-thumb">
                @else
                  <div class="product-thumb-placeholder">
                    <i class="fas fa-image"></i>
                  </div>
                @endif
              </div>
              <div class="col-md-7">
                <div class="review-details">
                  <h6 class="product-title mb-2">
                    <a href="#" class="text-walmart-blue">{{$review->product->title ?? 'Product Not Found'}}</a>
                  </h6>
                  <div class="rating-stars mb-2">
                    @for($i=1; $i<=5; $i++)
                      @if($review->rate >= $i)
                        <i class="fas fa-star text-warning"></i>
                      @else
                        <i class="far fa-star text-warning"></i>
                      @endif
                    @endfor
                    <span class="rating-text ml-2">{{$review->rate}}/5</span>
                  </div>
                  <p class="review-text mb-2">{{Str::limit($review->review, 150)}}</p>
                  <div class="review-meta">
                    <small class="text-muted">
                      <i class="fas fa-calendar mr-1"></i>
                      {{$review->created_at->format('M d, Y')}}
                    </small>
                    <small class="text-muted ml-3">
                      <i class="fas fa-user mr-1"></i>
                      By {{$review->user_info['name'] ?? 'Unknown'}}
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 text-right">
                <div class="review-status mb-3">
                  @if($review->status=='active')
                    <span class="status-badge delivered">Published</span>
                  @else
                    <span class="status-badge process">Pending</span>
                  @endif
                </div>
                <div class="review-actions">
                  <a href="{{route('user.productreview.edit',$review->id)}}" 
                     class="walmart-btn walmart-btn-warning walmart-btn-icon mr-2" 
                     data-toggle="tooltip" title="Edit Review">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" action="{{route('user.productreview.delete',[$review->id])}}" class="d-inline">
                    @csrf
                    @method('delete')
                    <button type="button" 
                            class="walmart-btn walmart-btn-danger walmart-btn-icon dltBtn" 
                            data-id="{{$review->id}}" 
                            data-toggle="tooltip" 
                            title="Delete Review">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      
      <!-- Pagination -->
      <div class="p-3 border-top">
        {{$reviews->links()}}
      </div>
    @else
      <div class="text-center py-5">
        <div class="mb-3">
          <i class="fas fa-star fa-4x text-muted"></i>
        </div>
        <h5 class="text-muted mb-3">No Reviews Yet</h5>
        <p class="text-muted mb-4">You haven't written any product reviews yet. Start shopping and share your experience!</p>
        <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
          <i class="fas fa-shopping-cart mr-2"></i>
          Start Shopping
        </a>
      </div>
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
.reviews-list {
  padding: 0;
}

.review-item {
  border-bottom: 1px solid var(--border-light);
  transition: background-color 0.2s ease;
}

.review-item:hover {
  background-color: var(--gray-50);
}

.review-item:last-child {
  border-bottom: none;
}

.review-content {
  padding: 1.5rem;
}

.product-thumb {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--border-light);
}

.product-thumb-placeholder {
  width: 80px;
  height: 80px;
  background: var(--gray-100);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray-400);
  font-size: 1.5rem;
}

.product-title {
  font-weight: var(--font-semibold);
  margin-bottom: 0.5rem;
}

.product-title a {
  text-decoration: none;
}

.product-title a:hover {
  text-decoration: underline;
}

.rating-stars {
  display: flex;
  align-items: center;
}

.rating-stars i {
  margin-right: 2px;
  font-size: 0.9rem;
}

.rating-text {
  font-weight: var(--font-medium);
  color: var(--gray-600);
  font-size: var(--text-sm);
}

.review-text {
  color: var(--gray-700);
  line-height: 1.5;
  margin-bottom: 0.75rem;
}

.review-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.review-status {
  display: flex;
  justify-content: flex-end;
}

.review-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.fa-4x {
  font-size: 4em;
}

.text-warning {
  color: #ffc107 !important;
}

.ml-2 {
  margin-left: 0.5rem !important;
}

.ml-3 {
  margin-left: 1rem !important;
}

.mb-2 {
  margin-bottom: 0.5rem !important;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

.mr-1 {
  margin-right: 0.25rem !important;
}

.mr-2 {
  margin-right: 0.5rem !important;
}

.p-0 {
  padding: 0 !important;
}

.p-3 {
  padding: 1rem !important;
}

.py-5 {
  padding-top: 3rem !important;
  padding-bottom: 3rem !important;
}

.border-top {
  border-top: 1px solid var(--border-light) !important;
}

.text-right {
  text-align: right !important;
}

.text-center {
  text-align: center !important;
}

/* Status filter styling */
#statusFilter {
  padding: 0.5rem 0.75rem;
  font-size: var(--text-sm);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .review-content {
    padding: 1rem;
  }
  
  .review-item .row {
    flex-direction: column;
  }
  
  .review-item .col-md-2,
  .review-item .col-md-7,
  .review-item .col-md-3 {
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
  }
  
  .review-item .col-md-2 {
    text-align: left !important;
  }
  
  .review-item .col-md-3 {
    text-align: left !important;
  }
  
  .review-status {
    justify-content: flex-start;
    margin-bottom: 1rem;
  }
  
  .review-actions {
    justify-content: flex-start;
  }
  
  .product-thumb,
  .product-thumb-placeholder {
    width: 60px;
    height: 60px;
  }
  
  .review-meta {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .stats-card h3 {
    font-size: 1.5rem;
  }
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
$(document).ready(function(){
  // CSRF Token setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  
  // Status filter functionality
  $('#statusFilter').change(function(){
    var status = $(this).val().toLowerCase();
    var items = $('.review-item');
    
    if(status === '') {
      items.show();
    } else {
      items.each(function(){
        var itemStatus = $(this).data('status');
        if(itemStatus === status) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  });
  
  // Delete confirmation with SweetAlert
  $('.dltBtn').click(function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var dataID = $(this).data('id');
    
    swal({
      title: "Delete Review?",
      text: "Are you sure you want to delete this review? This action cannot be undone!",
      icon: "warning",
      buttons: {
        cancel: {
          text: "Cancel",
          value: null,
          visible: true,
          className: "btn-secondary",
          closeModal: true,
        },
        confirm: {
          text: "Yes, Delete",
          value: true,
          visible: true,
          className: "btn-danger",
          closeModal: true
        }
      },
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        form.submit();
      }
    });
  });
  
  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip();
  
  // Stats cards click functionality
  $('.stats-card').click(function(){
    var cardType = '';
    if($(this).hasClass('success')) {
      cardType = 'active';
    } else if($(this).hasClass('warning')) {
      cardType = 'inactive';
    }
    
    if(cardType) {
      $('#statusFilter').val(cardType).trigger('change');
    }
  });
  
  // Add cursor pointer to clickable stats cards
  $('.stats-card.success, .stats-card.warning').css('cursor', 'pointer');
});
</script>
@endpush

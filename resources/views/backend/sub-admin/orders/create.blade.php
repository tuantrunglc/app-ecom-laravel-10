@extends('backend.layouts.master')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>
   <div class="card-header py-3">
     <h6 class="m-0 font-weight-bold text-primary float-left">Tạo Đơn Hàng Mới</h6>
     <div class="float-right">
       <a class="btn btn-primary" href="{{route('sub-admin.orders')}}" role="button">Xem Tất Cả Đơn Hàng</a>
     </div>
   </div>
   <div class="card-body">
     <form method="post" action="{{route('sub-admin.orders.store')}}">
       {{csrf_field()}}
       
       <!-- User Search Section -->
       <div class="form-group">
         <label for="user_search" class="col-form-label">Tìm Kiếm User <span class="text-danger">*</span></label>
         <div class="input-group">
           <input id="user_search" class="form-control" name="user_search" type="text" placeholder="Nhập ID hoặc email của user thuộc quyền quản lý...">
           <div class="input-group-append">
             <button class="btn btn-outline-secondary" type="button" id="search_user_btn">Tìm Kiếm</button>
           </div>
         </div>
         <small class="form-text text-muted">Chỉ có thể tìm kiếm users thuộc quyền quản lý của bạn</small>
       </div>

       <!-- User Info Display -->
       <div id="user_info" style="display: none;">
         <div class="card border-success mb-3">
           <div class="card-header bg-success text-white">
             <h6 class="mb-0">Thông Tin User</h6>
           </div>
           <div class="card-body">
             <div class="row">
               <div class="col-md-6">
                 <p><strong>ID:</strong> <span id="user_id_display"></span></p>
                 <p><strong>Tên:</strong> <span id="user_name_display"></span></p>
                 <p><strong>Email:</strong> <span id="user_email_display"></span></p>
               </div>
               <div class="col-md-6">
                 <p><strong>Trạng thái:</strong> <span id="user_status_display"></span></p>
                 <p><strong>Số dư ví:</strong> <span id="user_wallet_display"></span></p>
               </div>
             </div>
           </div>
         </div>
       </div>

       <!-- Hidden user ID field -->
       <input type="hidden" id="user_id" name="user_id" value="">

       <!-- Order Details Form -->
       <div id="order_form" style="display: none;">
         <div class="card border-info">
           <div class="card-header bg-info text-white">
             <h6 class="mb-0">Chi Tiết Đơn Hàng</h6>
           </div>
           <div class="card-body">

             <!-- Shipping Information -->
             <div class="row">
               <div class="col-md-6">
                 <div class="form-group">
                   <label for="first_name" class="col-form-label">Họ <span class="text-danger">*</span></label>
                   <input id="first_name" class="form-control @error('first_name') is-invalid @enderror" name="first_name" type="text" placeholder="Nhập họ" value="{{ old('first_name') }}">
                   @error('first_name')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
               <div class="col-md-6">
                 <div class="form-group">
                   <label for="last_name" class="col-form-label">Tên <span class="text-danger">*</span></label>
                   <input id="last_name" class="form-control" name="last_name" type="text" placeholder="Nhập tên">
                   @error('last_name')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
             </div>

             <div class="form-group">
               <label for="email" class="col-form-label">Email <span class="text-danger">*</span></label>
               <input id="order_email" class="form-control" name="email" type="email" placeholder="Nhập email">
               @error('email')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group">
               <label for="phone" class="col-form-label">Số điện thoại <span class="text-danger">*</span></label>
               <input id="phone" class="form-control" name="phone" type="text" placeholder="Nhập số điện thoại">
               @error('phone')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group">
               <label for="country" class="col-form-label">Quốc gia <span class="text-danger">*</span></label>
               <input id="country" class="form-control" name="country" type="text" placeholder="Nhập quốc gia">
               @error('country')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group">
               <label for="address1" class="col-form-label">Địa chỉ 1 <span class="text-danger">*</span></label>
               <textarea class="form-control" id="address1" name="address1" placeholder="Nhập địa chỉ chính"></textarea>
               @error('address1')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group">
               <label for="address2" class="col-form-label">Địa chỉ 2</label>
               <textarea class="form-control" id="address2" name="address2" placeholder="Nhập địa chỉ phụ (tùy chọn)"></textarea>
               @error('address2')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group">
               <label for="post_code" class="col-form-label">Mã bưu chính</label>
               <input id="post_code" class="form-control" name="post_code" type="text" placeholder="Nhập mã bưu chính">
               @error('post_code')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <!-- Order Info -->
             <hr>
             <h6 class="text-info">Thông Tin Đơn Hàng</h6>

             <div class="row">
               <div class="col-md-6">
                 <div class="form-group">
                   <label for="shipping" class="col-form-label">Phương thức vận chuyển <span class="text-danger">*</span></label>
                   <select name="shipping" class="form-control">
                     <option value="">--Chọn phương thức vận chuyển--</option>
                     @foreach(App\Models\Shipping::where('status','active')->get() as $shipping)
                     <option value="{{$shipping->id}}">{{$shipping->type}} ({{$shipping->price}} VND)</option>
                     @endforeach
                   </select>
                   @error('shipping')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
               <div class="col-md-6">
                 <div class="form-group">
                   <label for="payment_method" class="col-form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                   <select name="payment_method" class="form-control">
                     <option value="">--Chọn phương thức thanh toán--</option>
                     <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                     <option value="paypal">PayPal</option>
                   </select>
                   @error('payment_method')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
             </div>

             <div class="row">
               <div class="col-md-4">
                 <div class="form-group">
                   <label for="quantity" class="col-form-label">Số lượng <span class="text-danger">*</span></label>
                   <input id="quantity" class="form-control" name="quantity" type="number" min="1" placeholder="Nhập số lượng">
                   @error('quantity')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
               <div class="col-md-4">
                 <div class="form-group">
                   <label for="sub_total" class="col-form-label">Tổng phụ <span class="text-danger">*</span></label>
                   <input id="sub_total" class="form-control" name="sub_total" type="number" step="0.01" min="0" placeholder="Nhập tổng phụ">
                   @error('sub_total')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
               <div class="col-md-4">
                 <div class="form-group">
                   <label for="total_amount" class="col-form-label">Tổng cộng <span class="text-danger">*</span></label>
                   <input id="total_amount" class="form-control" name="total_amount" type="number" step="0.01" min="0" placeholder="Nhập tổng cộng">
                   @error('total_amount')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
             </div>

             <div class="form-group">
               <label for="status" class="col-form-label">Trạng thái <span class="text-danger">*</span></label>
               <select name="status" class="form-control">
                 <option value="">--Chọn trạng thái--</option>
                 <option value="new">Mới</option>
                 <option value="process">Đang xử lý</option>
                 <option value="delivered">Đã giao</option>
                 <option value="cancel">Đã hủy</option>
               </select>
               @error('status')
               <span class="text-danger">{{$message}}</span>
               @enderror
             </div>

             <div class="form-group mb-3">
               <button type="reset" class="btn btn-warning">Reset</button>
               <button class="btn btn-success" type="submit">Tạo Đơn Hàng</button>
             </div>
           </div>
         </div>
       </div>
     </form>
   </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>

<script>
$(document).ready(function() {
    // Search user functionality
    $('#search_user_btn').click(function() {
        var search = $('#user_search').val();
        
        if (!search) {
            alert('Vui lòng nhập ID hoặc email để tìm kiếm');
            return;
        }
        
        $.ajax({
            url: '{{route("sub-admin.orders.search-user")}}',
            type: 'POST',
            data: {
                search: search,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Display user info
                    $('#user_id_display').text(response.user.id);
                    $('#user_name_display').text(response.user.name);
                    $('#user_email_display').text(response.user.email);
                    $('#user_status_display').text(response.user.status);
                    $('#user_wallet_display').text(response.user.wallet_balance + ' VND');
                    
                    // Set hidden field
                    $('#user_id').val(response.user.id);
                    
                    // Pre-fill form
                    $('#order_email').val(response.user.email);
                    $('#first_name').val(response.user.name.split(' ')[0] || '');
                    $('#last_name').val(response.user.name.split(' ').slice(1).join(' ') || '');
                    
                    // Show sections
                    $('#user_info').show();
                    $('#order_form').show();
                } else {
                    alert(response.message);
                    $('#user_info').hide();
                    $('#order_form').hide();
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tìm kiếm user');
            }
        });
    });
    
    // Auto calculate total when sub_total or shipping changes
    $('#sub_total, select[name="shipping"]').change(function() {
        var subTotal = parseFloat($('#sub_total').val()) || 0;
        var shippingCost = 0;
        
        var selectedShipping = $('select[name="shipping"] option:selected').text();
        if (selectedShipping) {
            var match = selectedShipping.match(/\((\d+(?:\.\d+)?)\s*VND\)/);
            if (match) {
                shippingCost = parseFloat(match[1]);
            }
        }
        
        var total = subTotal + shippingCost;
        $('#total_amount').val(total.toFixed(2));
    });
});
</script>
@endpush
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
       <a class="btn btn-primary" href="{{route('order.index')}}" role="button">Xem Tất Cả Đơn Hàng</a>
     </div>
   </div>
   <div class="card-body">
     <form method="post" action="{{route('order.store')}}">
       {{csrf_field()}}
       
       <!-- User Search Section -->
       <div class="form-group">
         <label for="user_search" class="col-form-label">Tìm Kiếm User <span class="text-danger">*</span></label>
         <div class="input-group">
           <input id="user_search" class="form-control" name="user_search" type="text" placeholder="Nhập ID hoặc email của user...">
           <div class="input-group-append">
             <button class="btn btn-outline-secondary" type="button" id="search_user_btn">Tìm Kiếm</button>
           </div>
         </div>
         <small class="form-text text-muted">Nhập ID hoặc email của user để tìm kiếm</small>
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

       <!-- Hidden User ID -->
       <input type="hidden" id="selected_user_id" name="user_id" value="">

       <!-- Order Information -->
       <div id="order_form" style="display: none;">
         <h5 class="mb-3">Thông Tin Đơn Hàng</h5>
         
         <div class="form-group">
           <label for="first_name" class="col-form-label">Họ <span class="text-danger">*</span></label>
           <input id="first_name" class="form-control" name="first_name" type="text" placeholder="Nhập họ..." value="{{old('first_name')}}" required>
           @error('first_name')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="last_name" class="col-form-label">Tên <span class="text-danger">*</span></label>
           <input id="last_name" class="form-control" name="last_name" type="text" placeholder="Nhập tên..." value="{{old('last_name')}}" required>
           @error('last_name')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="email" class="col-form-label">Email <span class="text-danger">*</span></label>
           <input id="email" class="form-control" name="email" type="email" placeholder="Nhập email..." value="{{old('email')}}" required>
           @error('email')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="phone" class="col-form-label">Số Điện Thoại <span class="text-danger">*</span></label>
           <input id="phone" class="form-control" name="phone" type="text" placeholder="Nhập số điện thoại..." value="{{old('phone')}}" required>
           @error('phone')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="country" class="col-form-label">Quốc Gia <span class="text-danger">*</span></label>
           <input id="country" class="form-control" name="country" type="text" placeholder="Nhập quốc gia..." value="{{old('country')}}" required>
           @error('country')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="address1" class="col-form-label">Địa Chỉ 1 <span class="text-danger">*</span></label>
           <input id="address1" class="form-control" name="address1" type="text" placeholder="Nhập địa chỉ..." value="{{old('address1')}}" required>
           @error('address1')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="address2" class="col-form-label">Địa Chỉ 2</label>
           <input id="address2" class="form-control" name="address2" type="text" placeholder="Nhập địa chỉ 2..." value="{{old('address2')}}">
           @error('address2')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="post_code" class="col-form-label">Mã Bưu Điện</label>
           <input id="post_code" class="form-control" name="post_code" type="text" placeholder="Nhập mã bưu điện..." value="{{old('post_code')}}">
           @error('post_code')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="shipping" class="col-form-label">Phương Thức Vận Chuyển <span class="text-danger">*</span></label>
           <select name="shipping" class="form-control" required>
               <option value="">--Chọn phương thức vận chuyển--</option>
               @php
                   $shippings = DB::table('shippings')->where('status','active')->get();
               @endphp
               @foreach($shippings as $shipping)
                   <option value="{{$shipping->id}}" {{(old('shipping')==$shipping->id) ? 'selected' : ''}}>{{$shipping->type}} - ${{$shipping->price}}</option>
               @endforeach
           </select>
           @error('shipping')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="payment_method" class="col-form-label">Phương Thức Thanh Toán <span class="text-danger">*</span></label>
           <select name="payment_method" class="form-control" required>
               <option value="">--Chọn phương thức thanh toán--</option>
               <option value="cod" {{(old('payment_method')=='cod') ? 'selected' : ''}}>Thanh toán khi nhận hàng (COD)</option>
               <option value="paypal" {{(old('payment_method')=='paypal') ? 'selected' : ''}}>PayPal</option>
           </select>
           @error('payment_method')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="status" class="col-form-label">Trạng Thái <span class="text-danger">*</span></label>
           <select name="status" class="form-control" required>
               <option value="">--Chọn trạng thái--</option>
               <option value="new" {{(old('status')=='new') ? 'selected' : ''}}>Mới</option>
               <option value="process" {{(old('status')=='process') ? 'selected' : ''}}>Đang xử lý</option>
               <option value="delivered" {{(old('status')=='delivered') ? 'selected' : ''}}>Đã giao</option>
               <option value="cancel" {{(old('status')=='cancel') ? 'selected' : ''}}>Đã hủy</option>
           </select>
           @error('status')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="sub_total" class="col-form-label">Tổng Phụ <span class="text-danger">*</span></label>
           <input id="sub_total" class="form-control" name="sub_total" type="number" step="0.01" placeholder="Nhập tổng phụ..." value="{{old('sub_total')}}" required>
           @error('sub_total')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group">
           <label for="quantity" class="col-form-label">Số Lượng <span class="text-danger">*</span></label>
           <input id="quantity" class="form-control" name="quantity" type="number" placeholder="Nhập số lượng..." value="{{old('quantity')}}" required>
           @error('quantity')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>



         <div class="form-group">
           <label for="total_amount" class="col-form-label">Tổng Tiền <span class="text-danger">*</span></label>
           <input id="total_amount" class="form-control" name="total_amount" type="number" step="0.01" placeholder="Nhập tổng tiền..." value="{{old('total_amount')}}" required>
           @error('total_amount')
           <span class="text-danger">{{$message}}</span>
           @enderror
         </div>

         <div class="form-group mb-3">
           <button type="reset" class="btn btn-warning">Đặt Lại</button>
           <button class="btn btn-success" type="submit">Tạo Đơn Hàng</button>
         </div>
       </div>
     </form>
   </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
<style>
    .user-search-result {
        border: 1px solid #28a745;
        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
        background-color: #f8f9fa;
    }
    .user-not-found {
        border: 1px solid #dc3545;
        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // User search functionality
        $('#search_user_btn').click(function() {
            var searchTerm = $('#user_search').val().trim();
            
            if (searchTerm === '') {
                alert('Vui lòng nhập ID hoặc email của user');
                return;
            }

            // Show loading
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Đang tìm...');
            $(this).prop('disabled', true);

            $.ajax({
                url: '{{ route("order.search-user") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    search: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        // Display user info
                        $('#user_id_display').text(response.user.id);
                        $('#user_name_display').text(response.user.name);
                        $('#user_email_display').text(response.user.email);
                        $('#user_status_display').html(
                            response.user.status === 'active' 
                                ? '<span class="badge badge-success">Hoạt động</span>' 
                                : '<span class="badge badge-danger">Không hoạt động</span>'
                        );
                        $('#user_wallet_display').text('$' + parseFloat(response.user.wallet_balance || 0).toFixed(2));
                        
                        // Set hidden user ID
                        $('#selected_user_id').val(response.user.id);
                        
                        // Auto-fill form fields
                        $('#first_name').val(response.user.name.split(' ')[0] || '');
                        $('#last_name').val(response.user.name.split(' ').slice(1).join(' ') || '');
                        $('#email').val(response.user.email);
                        
                        // Show user info and order form
                        $('#user_info').show();
                        $('#order_form').show();
                        
                        // Scroll to user info
                        $('html, body').animate({
                            scrollTop: $("#user_info").offset().top - 100
                        }, 500);
                    } else {
                        alert('Không tìm thấy user với thông tin đã nhập');
                        $('#user_info').hide();
                        $('#order_form').hide();
                        $('#selected_user_id').val('');
                    }
                },
                error: function(xhr) {
                    alert('Có lỗi xảy ra khi tìm kiếm user');
                    console.log(xhr.responseText);
                },
                complete: function() {
                    $('#search_user_btn').html('Tìm Kiếm');
                    $('#search_user_btn').prop('disabled', false);
                }
            });
        });

        // Allow search on Enter key
        $('#user_search').keypress(function(e) {
            if (e.which == 13) {
                $('#search_user_btn').click();
            }
        });

        // Auto calculate total amount (you can manually adjust this)
        $('#sub_total').on('input', function() {
            var subTotal = parseFloat($('#sub_total').val()) || 0;
            // You can add shipping cost calculation here if needed
            $('#total_amount').val(subTotal.toFixed(2));
        });
    });
</script>
@endpush
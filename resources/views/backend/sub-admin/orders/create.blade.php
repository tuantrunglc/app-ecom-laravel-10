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

             <!-- Hidden fields for shipping information - will be auto-filled from selected user -->
             <input type="hidden" id="first_name" name="first_name" value="">
             <input type="hidden" id="last_name" name="last_name" value="">
             <input type="hidden" id="order_email" name="email" value="">
             <input type="hidden" id="phone" name="phone" value="N/A">
             <input type="hidden" id="country" name="country" value="N/A">
             <input type="hidden" id="address1" name="address1" value="N/A">
             <input type="hidden" id="address2" name="address2" value="">
             <input type="hidden" id="post_code" name="post_code" value="">

             <!-- Product Selection -->
             <hr>
             <h6 class="text-info">Chọn Sản Phẩm</h6>
             
             <div class="form-group">
               <label for="product_search" class="col-form-label">Tìm kiếm sản phẩm <span class="text-danger">*</span></label>
               <div class="input-group">
                 <input id="product_search" class="form-control" type="text" placeholder="Nhập tên sản phẩm để tìm kiếm...">
                 <div class="input-group-append">
                   <button class="btn btn-outline-secondary" type="button" id="search_product_btn">Tìm Kiếm</button>
                 </div>
               </div>
             </div>

             <!-- Product Search Results -->
             <div id="product_results" style="display: none;">
               <div class="card border-primary mb-3">
                 <div class="card-header bg-primary text-white">
                   <h6 class="mb-0">Kết Quả Tìm Kiếm</h6>
                 </div>
                 <div class="card-body" id="product_list">
                   <!-- Products will be loaded here -->
                 </div>
               </div>
             </div>

             <!-- Selected Products -->
             <div id="selected_products_section" style="display: none;">
               <div class="card border-success mb-3">
                 <div class="card-header bg-success text-white">
                   <h6 class="mb-0">Sản Phẩm Đã Chọn</h6>
                 </div>
                 <div class="card-body">
                   <div class="table-responsive">
                     <table class="table table-bordered" id="selected_products_table">
                       <thead>
                         <tr>
                           <th>Sản phẩm</th>
                           <th>Giá</th>
                           <th>Số lượng</th>
                           <th>Thành tiền</th>
                           <th>Hành động</th>
                         </tr>
                       </thead>
                       <tbody id="selected_products_body">
                         <!-- Selected products will be shown here -->
                       </tbody>
                     </table>
                   </div>
                 </div>
               </div>
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
                     <option value="{{$shipping->id}}">{{$shipping->type}} - ${{$shipping->price}}</option>
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
                     <option value="wallet" selected>Thanh toán qua ví</option>
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
                   <label for="total_quantity" class="col-form-label">Tổng số lượng</label>
                   <input id="total_quantity" class="form-control" name="quantity" type="number" readonly>
                 </div>
               </div>
               <div class="col-md-4">
                 <div class="form-group">
                   <label for="sub_total" class="col-form-label">Tổng phụ</label>
                   <input id="sub_total" class="form-control" name="sub_total" type="number" step="0.01" readonly>
                   @error('sub_total')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
               <div class="col-md-4">
                 <div class="form-group">
                   <label for="total_amount" class="col-form-label">Tổng cộng</label>
                   <input id="total_amount" class="form-control" name="total_amount" type="number" step="0.01" readonly>
                   @error('total_amount')
                   <span class="text-danger">{{$message}}</span>
                   @enderror
                 </div>
               </div>
             </div>

             <!-- Hidden field for products data -->
             <input type="hidden" id="products_data" name="products_data" value="">

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
    var selectedProducts = [];
    
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
                    $('#user_wallet_display').text('$' + parseFloat(response.user.wallet_balance || 0).toFixed(2));
                    
                    // Set hidden field
                    $('#user_id').val(response.user.id);
                    
                    // Auto-fill hidden form fields
                    $('#order_email').val(response.user.email);
                    $('#first_name').val(response.user.name.split(' ')[0] || '');
                    $('#last_name').val(response.user.name.split(' ').slice(1).join(' ') || '');
                    $('#phone').val('N/A');
                    $('#country').val('N/A');
                    $('#address1').val('N/A');
                    $('#address2').val('');
                    $('#post_code').val('');
                    
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
    
    // Search product functionality
    $('#search_product_btn').click(function() {
        var search = $('#product_search').val();
        
        if (!search) {
            alert('Vui lòng nhập tên sản phẩm để tìm kiếm');
            return;
        }
        
        $.ajax({
            url: '{{route("sub-admin.orders.search-product")}}',
            type: 'POST',
            data: {
                search: search,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.products.length > 0) {
                    var html = '';
                    response.products.forEach(function(product) {
                        var price = parseFloat(product.price);
                        var discount = parseFloat(product.discount) || 0;
                        var finalPrice = price - (price * discount / 100);
                        
                        html += '<div class="product-item border p-3 mb-2">';
                        html += '<div class="row">';
                        html += '<div class="col-md-2">';
                        if (product.photo) {
                            html += '<img src="' + product.photo + '" class="img-thumbnail" style="width: 60px; height: 60px;">';
                        } else {
                            html += '<div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">No Image</div>';
                        }
                        html += '</div>';
                        html += '<div class="col-md-6">';
                        html += '<h6>' + product.title + '</h6>';
                        html += '<p class="text-muted mb-1">Giá: $' + price.toFixed(2) + '</p>';
                        if (discount > 0) {
                            html += '<p class="text-success mb-1">Giảm giá: ' + discount + '%</p>';
                            html += '<p class="text-primary mb-1">Giá sau giảm: $' + finalPrice.toFixed(2) + '</p>';
                        }
                        html += '<p class="text-info mb-0">Tồn kho: ' + product.stock + '</p>';
                        html += '</div>';
                        html += '<div class="col-md-4">';
                        html += '<div class="input-group mb-2">';
                        html += '<input type="number" class="form-control product-quantity" min="1" max="' + product.stock + '" value="1" data-product-id="' + product.id + '">';
                        html += '<div class="input-group-append">';
                        html += '<button class="btn btn-success add-product-btn" type="button" data-product-id="' + product.id + '" data-product-title="' + product.title + '" data-product-price="' + finalPrice + '" data-product-stock="' + product.stock + '">Thêm</button>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                    
                    $('#product_list').html(html);
                    $('#product_results').show();
                } else {
                    $('#product_list').html('<p class="text-center">Không tìm thấy sản phẩm nào</p>');
                    $('#product_results').show();
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tìm kiếm sản phẩm');
            }
        });
    });
    
    // Add product to selected list
    $(document).on('click', '.add-product-btn', function() {
        var productId = $(this).data('product-id');
        var productTitle = $(this).data('product-title');
        var productPrice = parseFloat($(this).data('product-price'));
        var productStock = parseInt($(this).data('product-stock'));
        var quantity = parseInt($('.product-quantity[data-product-id="' + productId + '"]').val());
        
        if (quantity <= 0 || quantity > productStock) {
            alert('Số lượng không hợp lệ');
            return;
        }
        
        // Check if product already selected
        var existingIndex = selectedProducts.findIndex(p => p.id == productId);
        if (existingIndex >= 0) {
            selectedProducts[existingIndex].quantity += quantity;
        } else {
            selectedProducts.push({
                id: productId,
                title: productTitle,
                price: productPrice,
                quantity: quantity,
                stock: productStock
            });
        }
        
        updateSelectedProductsDisplay();
        calculateTotals();
    });
    
    // Remove product from selected list
    $(document).on('click', '.remove-product-btn', function() {
        var productId = $(this).data('product-id');
        selectedProducts = selectedProducts.filter(p => p.id != productId);
        updateSelectedProductsDisplay();
        calculateTotals();
    });
    
    // Update quantity in selected products
    $(document).on('change', '.selected-product-quantity', function() {
        var productId = $(this).data('product-id');
        var newQuantity = parseInt($(this).val());
        var product = selectedProducts.find(p => p.id == productId);
        
        if (product && newQuantity > 0 && newQuantity <= product.stock) {
            product.quantity = newQuantity;
            updateSelectedProductsDisplay();
            calculateTotals();
        } else {
            alert('Số lượng không hợp lệ');
            $(this).val(product.quantity);
        }
    });
    
    function updateSelectedProductsDisplay() {
        if (selectedProducts.length === 0) {
            $('#selected_products_section').hide();
            return;
        }
        
        var html = '';
        selectedProducts.forEach(function(product) {
            var total = product.price * product.quantity;
            html += '<tr>';
            html += '<td>' + product.title + '</td>';
            html += '<td>$' + product.price.toFixed(2) + '</td>';
            html += '<td><input type="number" class="form-control selected-product-quantity" min="1" max="' + product.stock + '" value="' + product.quantity + '" data-product-id="' + product.id + '"></td>';
            html += '<td>$' + total.toFixed(2) + '</td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-product-btn" data-product-id="' + product.id + '">Xóa</button></td>';
            html += '</tr>';
        });
        
        $('#selected_products_body').html(html);
        $('#selected_products_section').show();
        
        // Update hidden field
        $('#products_data').val(JSON.stringify(selectedProducts));
    }
    
    function calculateTotals() {
        var totalQuantity = 0;
        var subTotal = 0;
        
        selectedProducts.forEach(function(product) {
            totalQuantity += product.quantity;
            subTotal += product.price * product.quantity;
        });
        
        $('#total_quantity').val(totalQuantity);
        $('#sub_total').val(subTotal.toFixed(2));
        
        // Calculate total with shipping
        var shippingCost = 0;
        var selectedShipping = $('select[name="shipping"] option:selected').text();
        if (selectedShipping) {
            var match = selectedShipping.match(/\$(\d+(?:\.\d+)?)/);
            if (match) {
                shippingCost = parseFloat(match[1]);
            }
        }
        
        var total = subTotal + shippingCost;
        $('#total_amount').val(total.toFixed(2));
    }
    
    // Auto calculate total when shipping changes
    $('select[name="shipping"]').change(function() {
        calculateTotals();
    });
    
    // Form validation before submit
    $('form').submit(function(e) {
        if (selectedProducts.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một sản phẩm');
            return false;
        }
    });
});
</script>
@endpush
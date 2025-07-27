<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin')}}">
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fas fa-laugh-wink"></i>
      </div>
      <div class="sidebar-brand-text mx-3">Quản Trị</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
      <a class="nav-link" href="{{route('admin')}}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Bảng Điều Khiển</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Banner
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <!-- Nav Item - Charts -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('file-manager')}}">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Quản Lý Media</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-image"></i>
        <span>Banner</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Tùy Chọn Banner:</h6>
          <a class="collapse-item" href="{{route('banner.index')}}">Danh Sách Banner</a>
          <a class="collapse-item" href="{{route('banner.create')}}">Thêm Banner</a>
        </div>
      </div>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
            Cửa Hàng
        </div>

    <!-- Categories -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#categoryCollapse" aria-expanded="true" aria-controls="categoryCollapse">
          <i class="fas fa-sitemap"></i>
          <span>Danh Mục</span>
        </a>
        <div id="categoryCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Danh Mục:</h6>
            <a class="collapse-item" href="{{route('category.index')}}">Danh Sách Danh Mục</a>
            <a class="collapse-item" href="{{route('category.create')}}">Thêm Danh Mục</a>
          </div>
        </div>
    </li>

     {{-- Brands --}}
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#brandCollapse" aria-expanded="true" aria-controls="brandCollapse">
          <i class="fas fa-table"></i>
          <span>Thương Hiệu</span>
        </a>
        <div id="brandCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Thương Hiệu:</h6>
            <a class="collapse-item" href="{{route('brand.index')}}">Danh Sách Thương Hiệu</a>
            <a class="collapse-item" href="{{route('brand.create')}}">Thêm Thương Hiệu</a>
          </div>
        </div>
    </li>



    {{-- Products --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#productCollapse" aria-expanded="true" aria-controls="productCollapse">
          <i class="fas fa-cubes"></i>
          <span>Sản Phẩm</span>
        </a>
        <div id="productCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Sản Phẩm:</h6>
            <a class="collapse-item" href="{{route('product.index')}}">Danh Sách Sản Phẩm</a>
            <a class="collapse-item" href="{{route('product.create')}}">Thêm Sản Phẩm</a>
          </div>
        </div>
    </li>

  
    {{-- Shipping --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#shippingCollapse" aria-expanded="true" aria-controls="shippingCollapse">
          <i class="fas fa-truck"></i>
          <span>Vận Chuyển</span>
        </a>
        <div id="shippingCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Vận Chuyển:</h6>
            <a class="collapse-item" href="{{route('shipping.index')}}">Danh Sách Vận Chuyển</a>
            <a class="collapse-item" href="{{route('shipping.create')}}">Thêm Vận Chuyển</a>
          </div>
        </div>
    </li>

    <!--Orders -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('order.index')}}">
            <i class="fas fa-hammer fa-chart-area"></i>
            <span>Đơn Hàng</span>
        </a>
    </li>

    <!-- Reviews -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('review.index')}}">
            <i class="fas fa-comments"></i>
            <span>Đánh Giá</span></a>
    </li>
    

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
      Bài Viết
    </div>

    <!-- Posts -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCollapse" aria-expanded="true" aria-controls="postCollapse">
        <i class="fas fa-fw fa-folder"></i>
        <span>Bài Viết</span>
      </a>
      <div id="postCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Tùy Chọn Bài Viết:</h6>
          <a class="collapse-item" href="{{route('post.index')}}">Danh Sách Bài Viết</a>
          <a class="collapse-item" href="{{route('post.create')}}">Thêm Bài Viết</a>
        </div>
      </div>
    </li>

     <!-- Category -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCategoryCollapse" aria-expanded="true" aria-controls="postCategoryCollapse">
          <i class="fas fa-sitemap fa-folder"></i>
          <span>Danh Mục Bài Viết</span>
        </a>
        <div id="postCategoryCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Danh Mục:</h6>
            <a class="collapse-item" href="{{route('post-category.index')}}">Danh Sách Danh Mục</a>
            <a class="collapse-item" href="{{route('post-category.create')}}">Thêm Danh Mục</a>
          </div>
        </div>
      </li>

      <!-- Tags -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#tagCollapse" aria-expanded="true" aria-controls="tagCollapse">
            <i class="fas fa-tags fa-folder"></i>
            <span>Thẻ Tag</span>
        </a>
        <div id="tagCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Thẻ Tag:</h6>
            <a class="collapse-item" href="{{route('post-tag.index')}}">Danh Sách Thẻ Tag</a>
            <a class="collapse-item" href="{{route('post-tag.create')}}">Thêm Thẻ Tag</a>
            </div>
        </div>
    </li>

      <!-- Comments -->
      <li class="nav-item">
        <a class="nav-link" href="{{route('comment.index')}}">
            <i class="fas fa-comments fa-chart-area"></i>
            <span>Bình Luận</span>
        </a>
      </li>


    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
     <!-- Heading -->
    <div class="sidebar-heading">
        Cài Đặt Chung
    </div>
    <li class="nav-item">
      <a class="nav-link" href="{{route('coupon.index')}}">
          <i class="fas fa-table"></i>
          <span>Mã Giảm Giá</span></a>
    </li>

    <!-- Lucky Wheel -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#luckyWheelCollapse" aria-expanded="true" aria-controls="luckyWheelCollapse">
            <i class="fas fa-gift"></i>
            <span>Vòng Quay May Mắn</span>
        </a>
        <div id="luckyWheelCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản Lý Vòng Quay:</h6>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.index')}}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.prizes')}}">
                    <i class="fas fa-trophy"></i> Phần Thưởng
                </a>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.settings')}}">
                    <i class="fas fa-cog"></i> Cài Đặt
                </a>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.set-result')}}">
                    <i class="fas fa-magic"></i> Đặt Kết Quả
                </a>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.spins')}}">
                    <i class="fas fa-history"></i> Lịch Sử Quay
                </a>
                <a class="collapse-item" href="{{route('admin.lucky-wheel.statistics')}}">
                    <i class="fas fa-chart-bar"></i> Thống Kê
                </a>
            </div>
        </div>
    </li>
     <!-- Users -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('users.index')}}">
            <i class="fas fa-users"></i>
            <span>Người Dùng</span></a>
    </li>
     <!-- General settings -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('settings')}}">
            <i class="fas fa-cog"></i>
            <span>Cài Đặt</span></a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
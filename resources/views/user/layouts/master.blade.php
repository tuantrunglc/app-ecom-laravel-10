<!DOCTYPE html>
<html lang="en">

@include('user.layouts.head')

<body id="page-top">

  <!-- Walmart Wrapper -->
  <div class="walmart-wrapper">

    <!-- Walmart Sidebar -->
    @include('user.layouts.sidebar')
    <!-- End of Sidebar -->
    
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Main Content Area -->
    <div class="walmart-main">

      <!-- Walmart Header -->
      @include('user.layouts.header')
      <!-- End of Header -->

      <!-- Page Content -->
      <div class="container-fluid py-4">
        @yield('main-content')
      </div>
      <!-- End of Page Content -->

      <!-- Footer -->
      @include('user.layouts.footer')
      <!-- End of Footer -->

    </div>
    <!-- End of Main Content Area -->

  </div>
  <!-- End of Walmart Wrapper -->

  <!-- Bootstrap core JavaScript-->
  <script src="{{asset('backend/vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('backend/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Core plugin JavaScript-->
  <script src="{{asset('backend/vendor/jquery-easing/jquery.easing.min.js')}}"></script>

  <!-- Custom scripts for all pages-->
  <script src="{{asset('backend/js/sb-admin-2.min.js')}}"></script>
  
  <!-- Axios for API calls (needed by dashboard charts) -->
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  
  <!-- Walmart Theme JavaScript -->
  <script src="{{asset('js/walmart-theme.js')}}"></script>
  
  <script>
  // Fallback: đảm bảo toggle luôn hoạt động
  (function($){
    $(function(){
      // Hủy bind cũ theo namespace rồi bind lại
      $(document)
        .off('click.userSidebar', '#sidebarToggleTop, .sidebar-toggle')
        .on('click.userSidebar', '#sidebarToggleTop, .sidebar-toggle', function(e){
          e.preventDefault();
          $('.walmart-sidebar').toggleClass('show');       // Hiện/ẩn sidebar
          $('.sidebar-backdrop').toggleClass('show');      // Hiện/ẩn nền tối
          $('body').toggleClass('sidebar-open');           // Khóa scroll body
        });
    });
  })(jQuery);
  </script>


  @stack('scripts')

</body>

</html>

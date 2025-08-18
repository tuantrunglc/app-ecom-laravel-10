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
  
  <!-- Walmart Theme JavaScript -->
  <script src="{{asset('js/walmart-theme.js')}}"></script>
  
  <!-- Mobile Menu Debug Script -->
  <script>
  $(document).ready(function() {
    console.log('Document ready - checking mobile menu setup');
    console.log('Sidebar toggle button exists:', $('#sidebarToggleTop').length > 0);
    console.log('Sidebar exists:', $('.walmart-sidebar').length > 0);
    console.log('Backdrop exists:', $('.sidebar-backdrop').length > 0);
    
    // Test click handler with actual toggle functionality
    $('#sidebarToggleTop, .sidebar-toggle').on('click', function(e) {
      e.preventDefault();
      console.log('Toggle button clicked - manual handler');
      
      const sidebar = $('.walmart-sidebar');
      const backdrop = $('.sidebar-backdrop');
      const body = $('body');
      
      // Toggle classes
      sidebar.toggleClass('show');
      backdrop.toggleClass('show');
      body.toggleClass('sidebar-open');
      
      console.log('Sidebar has show class:', sidebar.hasClass('show'));
      console.log('Backdrop has show class:', backdrop.hasClass('show'));
    });
    
    // Backdrop click to close
    $('.sidebar-backdrop').on('click', function() {
      console.log('Backdrop clicked');
      $('.walmart-sidebar').removeClass('show');
      $('.sidebar-backdrop').removeClass('show');
      $('body').removeClass('sidebar-open');
    });
  });
  </script>

  @stack('scripts')

</body>

</html>

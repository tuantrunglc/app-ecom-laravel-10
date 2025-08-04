<!DOCTYPE html>
<html lang="en">

@include('user.layouts.head')

<body id="page-top">

  <!-- Walmart Wrapper -->
  <div class="walmart-wrapper">

    <!-- Walmart Sidebar -->
    @include('user.layouts.sidebar')
    <!-- End of Sidebar -->

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

  <!-- Walmart Theme JavaScript -->
  <script src="{{asset('js/walmart-theme.js')}}"></script>
  
  <!-- Bootstrap core JavaScript-->
  <script src="{{asset('backend/vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('backend/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Core plugin JavaScript-->
  <script src="{{asset('backend/vendor/jquery-easing/jquery.easing.min.js')}}"></script>

  <!-- Custom scripts for all pages-->
  <script src="{{asset('backend/js/sb-admin-2.min.js')}}"></script>

  @stack('scripts')

</body>

</html>

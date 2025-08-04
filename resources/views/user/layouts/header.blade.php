<header class="walmart-header">
  <div class="walmart-topbar">
    
    <!-- Left Section -->
    <div class="topbar-left">
      <!-- Sidebar Toggle (Mobile) -->
      <button id="sidebarToggleTop" class="sidebar-toggle d-lg-none">
        <i class="fas fa-bars"></i>
      </button>
      
      <!-- Logo/Brand (Mobile) -->
      <div class="d-lg-none">
        <a href="{{route('user')}}" class="text-walmart-blue font-weight-bold">
          <i class="fas fa-store mr-2"></i>E-SHOP
        </a>
      </div>
    </div>

    <!-- Center Section - Search -->
    <div class="topbar-search d-none d-md-block">
      <form class="search-container">
        <input type="text" class="search-input" placeholder="Search orders, products..." aria-label="Search">
        <button class="search-btn" type="submit">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>

    <!-- Right Section -->
    <div class="topbar-right">
      
      <!-- Home Link -->
      <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-secondary walmart-btn-sm mr-3 d-none d-sm-inline-flex" data-toggle="tooltip" title="Visit Store">
        <i class="fas fa-home mr-2"></i>
        <span class="d-none d-lg-inline">Store</span>
      </a>

      <!-- User Menu -->
      <div class="user-menu walmart-dropdown">
        <a href="#" class="user-dropdown" data-toggle="dropdown">
          @if(Auth()->user()->photo)
            <img class="user-avatar" src="{{Auth()->user()->photo}}" alt="User Avatar">
          @else
            <img class="user-avatar" src="{{asset('backend/img/avatar.png')}}" alt="User Avatar">
          @endif
          <span class="user-name d-none d-lg-inline">{{Auth()->user()->name}}</span>
          <i class="fas fa-chevron-down ml-2 d-none d-lg-inline"></i>
        </a>
        
        <!-- User Dropdown Menu -->
        <div class="walmart-dropdown-menu">
          <a class="walmart-dropdown-item" href="{{route('user-profile')}}">
            <i class="fas fa-user mr-2"></i>
            My Profile
          </a>
          <a class="walmart-dropdown-item" href="{{route('user.change.password.form')}}">
            <i class="fas fa-key mr-2"></i>
            Change Password
          </a>
          <div class="walmart-dropdown-divider"></div>
          <a class="walmart-dropdown-item" href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt mr-2"></i>
            Logout
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
    </div>
    
  </div>
  
  <!-- Mobile Search (Hidden by default) -->
  <div class="mobile-search d-md-none" style="display: none;">
    <form class="search-container p-3">
      <input type="text" class="search-input" placeholder="Search orders, products..." aria-label="Search">
      <button class="search-btn" type="submit">
        <i class="fas fa-search"></i>
      </button>
    </form>
  </div>
  
</header>

<!-- Mobile Search Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile search toggle
  const searchToggle = document.createElement('button');
  searchToggle.className = 'walmart-btn walmart-btn-secondary walmart-btn-sm d-md-none';
  searchToggle.innerHTML = '<i class="fas fa-search"></i>';
  searchToggle.style.marginRight = '0.5rem';
  
  const topbarRight = document.querySelector('.topbar-right');
  topbarRight.insertBefore(searchToggle, topbarRight.firstChild);
  
  const mobileSearch = document.querySelector('.mobile-search');
  
  searchToggle.addEventListener('click', function() {
    if (mobileSearch.style.display === 'none' || !mobileSearch.style.display) {
      mobileSearch.style.display = 'block';
      searchToggle.innerHTML = '<i class="fas fa-times"></i>';
    } else {
      mobileSearch.style.display = 'none';
      searchToggle.innerHTML = '<i class="fas fa-search"></i>';
    }
  });
});
</script>
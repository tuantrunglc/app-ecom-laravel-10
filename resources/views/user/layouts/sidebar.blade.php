<aside class="walmart-sidebar" id="walmartSidebar">
  
  <!-- Sidebar Brand -->
  <div class="walmart-sidebar-brand">
    <div class="walmart-sidebar-brand-icon">
      <i class="fas fa-store"></i>
    </div>
    <div class="sidebar-brand-text">
      My Account
    </div>
  </div>

  <!-- Sidebar Navigation -->
  <nav class="sidebar-nav">
    <ul class="nav-list">
      
      <!-- Dashboard -->
      <li class="nav-item {{ request()->routeIs('user') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user')}}">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Shopping Section -->
      <div class="sidebar-heading">Shopping</div>
      
      <!-- Orders -->
      <li class="nav-item {{ request()->routeIs('user.order.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user.order.index')}}">
          <i class="fas fa-shopping-bag"></i>
          <span>My Orders</span>
        </a>
      </li>

      <!-- Reviews -->
      <li class="nav-item {{ request()->routeIs('user.productreview.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user.productreview.index')}}">
          <i class="fas fa-star"></i>
          <span>Reviews</span>
        </a>
      </li>

      <!-- Wallet -->
      <li class="nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('wallet.index')}}">
          <i class="fas fa-wallet"></i>
          <span>My Wallet</span>
        </a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Support Section -->
      <div class="sidebar-heading">Support</div>
      
      <!-- Chat Support -->
      <li class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('chat.index')}}">
          <i class="fas fa-comment-dots"></i>
          <span>Chat Support</span>
        </a>
      </li>

      <!-- Comments -->
      <li class="nav-item {{ request()->routeIs('user.post-comment.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user.post-comment.index')}}">
          <i class="fas fa-comments"></i>
          <span>My Comments</span>
        </a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Account Section -->
      <div class="sidebar-heading">Account</div>
      
      <!-- Profile -->
      <li class="nav-item {{ request()->routeIs('user-profile') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user-profile')}}">
          <i class="fas fa-user-circle"></i>
          <span>My Profile</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer p-3 mt-auto">
    <div class="text-center">
      <small class="text-muted">Walmart User Panel</small>
    </div>
  </div>

</aside>



<style>
.sidebar-footer {
  border-top: 1px solid var(--border-light);
  margin-top: auto;
}

.walmart-sidebar {
  display: flex;
  flex-direction: column;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
}

/* Active state styling */
.nav-item.active .nav-link {
  background: var(--walmart-blue);
  color: var(--white);
  font-weight: var(--font-semibold);
}

.nav-item.active .nav-link i {
  color: var(--white);
}

/* Hover effects */
.nav-link:hover {
  background: rgba(0, 113, 206, 0.1);
  color: var(--walmart-blue);
}

.nav-link:hover i {
  color: var(--walmart-blue);
}

/* Mobile responsive */
@media (max-width: 991.98px) {
  .walmart-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1050;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  }
  
  .walmart-sidebar.show {
    transform: translateX(0);
  }
  
  .sidebar-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }
  
  .sidebar-backdrop.show {
    opacity: 1;
    visibility: visible;
  }
}
</style>
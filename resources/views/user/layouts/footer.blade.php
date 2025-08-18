
<!-- Walmart Footer -->
<footer class="walmart-footer bg-white border-top mt-auto">
  <div class="container-fluid py-3">
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="copyright text-muted">
          <small>
            &copy; {{date('Y')}} Walmart. All rights reserved. 
            <span class="d-none d-md-inline">
              | Built with <i class="fas fa-heart text-danger"></i> by 
              <a href="https://github.com/Prajwal100" target="_blank" class="text-walmart-blue">Prajwal R.</a>
            </span>
          </small>
        </div>
      </div>
      <div class="col-md-6 text-md-right">
        <div class="footer-links">
          <a href="{{route('home')}}" class="text-muted mr-3" target="_blank">
            <small><i class="fas fa-home mr-1"></i>Store</small>
          </a>
          <a href="#" class="text-muted mr-3">
            <small><i class="fas fa-question-circle mr-1"></i>Help</small>
          </a>
          <a href="#" class="text-muted">
            <small><i class="fas fa-shield-alt mr-1"></i>Privacy</small>
          </a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" style="display: none;">
  <i class="fas fa-chevron-up"></i>
</button>

<!-- Page level plugins -->
<script src="{{asset('backend/vendor/chart.js/Chart.min.js')}}"></script>

@stack('scripts')

<script>
// Alert auto-hide
setTimeout(function(){
  $('.alert').slideUp();
}, 4000);

// Scroll to top functionality
document.addEventListener('DOMContentLoaded', function() {
  const scrollToTopBtn = document.getElementById('scrollToTop');
  
  // Show/hide scroll to top button
  window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
      scrollToTopBtn.style.display = 'flex';
    } else {
      scrollToTopBtn.style.display = 'none';
    }
  });
  
  // Scroll to top when clicked
  scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
});
</script>

<style>
.walmart-footer {
  margin-top: auto;
  border-top: 1px solid var(--border-light);
}

.footer-links a {
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: var(--walmart-blue) !important;
  text-decoration: none;
}

.scroll-to-top {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 45px;
  height: 45px;
  background: var(--walmart-blue);
  color: var(--white);
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0, 113, 206, 0.3);
  transition: all 0.3s ease;
  z-index: 1000;
}

.scroll-to-top:hover {
  background: var(--walmart-dark-blue);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 113, 206, 0.4);
}

@media (max-width: 575.98px) {
  .scroll-to-top {
    bottom: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
  }
  
  .footer-links {
    margin-top: 0.5rem;
  }
}
</style>

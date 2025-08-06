/* ===== WALMART THEME JAVASCRIPT ===== */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initWalmartTheme();
    });

    function initWalmartTheme() {
        // Initialize mobile menu
        initMobileMenu();
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize dropdowns
        initDropdowns();
        
        // Initialize alerts
        initAlerts();
        
        // Initialize form enhancements
        initFormEnhancements();
        
        // Initialize table enhancements
        initTableEnhancements();
        
        // Initialize sidebar
        initSidebar();
    }

    // Mobile Menu Functionality
    function initMobileMenu() {
        // Toggle mobile menu using existing sidebar toggle
        $(document).on('click', '#sidebarToggleTop, .sidebar-toggle', function() {
            $('.walmart-sidebar').toggleClass('show');
            $('.sidebar-backdrop').toggleClass('show');
            $('body').toggleClass('sidebar-open');
        });

        // Close mobile menu when clicking backdrop
        $(document).on('click', '.sidebar-backdrop', function() {
            $('.walmart-sidebar').removeClass('show');
            $('.sidebar-backdrop').removeClass('show');
            $('body').removeClass('sidebar-open');
        });

        // Close mobile menu when clicking a nav link on mobile
        $(document).on('click', '.sidebar-nav .nav-link', function() {
            if ($(window).width() <= 991) {
                $('.walmart-sidebar').removeClass('show');
                $('.sidebar-backdrop').removeClass('show');
                $('body').removeClass('sidebar-open');
            }
        });

        // Handle window resize
        $(window).resize(function() {
            if ($(window).width() > 991) {
                $('.walmart-sidebar').removeClass('show');
                $('.sidebar-backdrop').removeClass('show');
                $('body').removeClass('sidebar-open');
            }
        });
    }

    // Initialize Tooltips
    function initTooltips() {
        if (typeof $().tooltip === 'function') {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    // Initialize Dropdowns
    function initDropdowns() {
        // Handle user dropdown toggle
        $(document).on('click', '.user-dropdown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $dropdown = $(this).closest('.user-menu');
            const $menu = $dropdown.find('.walmart-dropdown-menu');
            
            // Close other dropdowns
            $('.walmart-dropdown-menu').not($menu).removeClass('show');
            
            // Toggle current dropdown
            $menu.toggleClass('show');
        });

        // Handle generic dropdown toggles
        $(document).on('click', '.walmart-dropdown-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $dropdown = $(this).closest('.walmart-dropdown');
            const $menu = $dropdown.find('.walmart-dropdown-menu');
            
            // Close other dropdowns
            $('.walmart-dropdown-menu').not($menu).removeClass('show');
            
            // Toggle current dropdown
            $menu.toggleClass('show');
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.walmart-dropdown, .user-menu').length) {
                $('.walmart-dropdown-menu').removeClass('show');
            }
        });

        // Close dropdown when pressing Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.walmart-dropdown-menu').removeClass('show');
            }
        });
    }

    // Initialize Alerts
    function initAlerts() {
        // Auto-dismiss alerts after 5 seconds
        $('.walmart-alert').each(function() {
            const $alert = $(this);
            if ($alert.data('auto-dismiss') !== false) {
                setTimeout(function() {
                    $alert.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });

        // Close alert when clicking close button
        $(document).on('click', '.walmart-alert .close', function() {
            $(this).closest('.walmart-alert').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    // Form Enhancements
    function initFormEnhancements() {
        // Add focus classes to form groups
        $('.walmart-input, .walmart-select, .walmart-textarea').on('focus', function() {
            $(this).closest('.walmart-form-group').addClass('focused');
        }).on('blur', function() {
            $(this).closest('.walmart-form-group').removeClass('focused');
        });

        // Form validation styling
        $('form').on('submit', function() {
            const $form = $(this);
            const $requiredFields = $form.find('[required]');
            
            $requiredFields.each(function() {
                const $field = $(this);
                const $group = $field.closest('.walmart-form-group');
                
                if (!$field.val()) {
                    $group.addClass('has-error');
                } else {
                    $group.removeClass('has-error');
                }
            });
        });

        // Clear validation errors on input
        $('.walmart-input, .walmart-select, .walmart-textarea').on('input change', function() {
            $(this).closest('.walmart-form-group').removeClass('has-error');
        });
    }

    // Table Enhancements
    function initTableEnhancements() {
        // Add hover effects to table rows
        $('.walmart-table tbody tr').hover(
            function() {
                $(this).addClass('hover');
            },
            function() {
                $(this).removeClass('hover');
            }
        );

        // Make tables responsive on mobile
        if ($(window).width() <= 767) {
            $('.walmart-table').wrap('<div class="walmart-table-responsive"></div>');
        }
    }

    // Sidebar Enhancements
    function initSidebar() {
        // Set active nav item based on current URL
        const currentPath = window.location.pathname;
        $('.sidebar-nav .nav-link').each(function() {
            const $link = $(this);
            const href = $link.attr('href');
            
            if (href && currentPath.includes(href)) {
                $link.closest('.nav-item').addClass('active');
            }
        });

        // Smooth scrolling for sidebar links
        $('.sidebar-nav .nav-link').on('click', function(e) {
            const href = $(this).attr('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = $(href);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 300);
                }
            }
        });
    }

    // Utility Functions
    window.WalmartTheme = {
        // Show loading spinner
        showLoading: function(element) {
            const $element = $(element);
            const originalText = $element.html();
            $element.data('original-text', originalText);
            $element.html('<span class="walmart-spinner"></span> Loading...');
            $element.prop('disabled', true);
        },

        // Hide loading spinner
        hideLoading: function(element) {
            const $element = $(element);
            const originalText = $element.data('original-text');
            if (originalText) {
                $element.html(originalText);
            }
            $element.prop('disabled', false);
        },

        // Show alert
        showAlert: function(message, type = 'info', container = 'body') {
            const alertHtml = `
                <div class="walmart-alert walmart-alert-${type}" role="alert">
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ${message}
                </div>
            `;
            
            $(container).prepend(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $(container).find('.walmart-alert').first().fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Confirm dialog
        confirm: function(message, callback) {
            if (confirm(message)) {
                if (typeof callback === 'function') {
                    callback();
                }
                return true;
            }
            return false;
        },

        // Format currency
        formatCurrency: function(amount, currency = '$') {
            return currency + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        // Format date
        formatDate: function(date, format = 'MM/DD/YYYY') {
            const d = new Date(date);
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const year = d.getFullYear();
            
            switch (format) {
                case 'MM/DD/YYYY':
                    return `${month}/${day}/${year}`;
                case 'DD/MM/YYYY':
                    return `${day}/${month}/${year}`;
                case 'YYYY-MM-DD':
                    return `${year}-${month}-${day}`;
                default:
                    return d.toLocaleDateString();
            }
        },

        // Animate number counting
        animateNumber: function(element, start, end, duration = 1000) {
            const $element = $(element);
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(function() {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                $element.text(Math.floor(current));
            }, 16);
        }
    };

    // Initialize theme when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWalmartTheme);
    } else {
        initWalmartTheme();
    }

})(jQuery);
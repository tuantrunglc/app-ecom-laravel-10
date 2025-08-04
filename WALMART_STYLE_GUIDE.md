# Hướng Dẫn Chỉnh Sửa Giao Diện User Theo Phong Cách Walmart

## 📋 Mục Lục
1. [Tổng Quan Phong Cách Walmart](#tổng-quan-phong-cách-walmart)
2. [Phân Tích Cấu Trúc Hiện Tại](#phân-tích-cấu-trúc-hiện-tại)
3. [Màu Sắc và Typography](#màu-sắc-và-typography)
4. [Layout và Components](#layout-và-components)
5. [Kế Hoạch Thực Hiện](#kế-hoạch-thực-hiện)
6. [Files Cần Chỉnh Sửa](#files-cần-chỉnh-sửa)
7. [CSS Framework và Assets](#css-framework-và-assets)

---

## 🎨 Tổng Quan Phong Cách Walmart

### Đặc Điểm Chính của Walmart Design System:

#### **1. Màu Sắc Chủ Đạo**
- **Primary Blue**: `#0071ce` (Walmart Blue)
- **Secondary Blue**: `#004c91` (Dark Blue)
- **Accent Yellow**: `#ffc220` (Walmart Yellow)
- **Background**: `#ffffff` (White)
- **Text Primary**: `#000000` (Black)
- **Text Secondary**: `#5a5a5a` (Gray)
- **Border**: `#e6e6e6` (Light Gray)

#### **2. Typography**
- **Font Family**: "Bogle", "Helvetica Neue", Arial, sans-serif
- **Fallback**: System fonts (Segoe UI, Roboto, sans-serif)
- **Font Weights**: 300 (Light), 400 (Regular), 500 (Medium), 700 (Bold)

#### **3. Design Principles**
- **Clean & Minimal**: Giao diện sạch sẽ, không rối mắt
- **User-Friendly**: Dễ sử dụng, navigation rõ ràng
- **Mobile-First**: Responsive design ưu tiên mobile
- **Accessibility**: Tuân thủ WCAG guidelines
- **Trust & Security**: Thiết kế tạo cảm giác tin cậy

---

## 🔍 Phân Tích Cấu Trúc Hiện Tại

### **User Dashboard Structure:**
```
resources/views/user/
├── layouts/
│   ├── master.blade.php      # Main layout
│   ├── head.blade.php        # Head section
│   ├── header.blade.php      # Top navigation
│   ├── sidebar.blade.php     # Side navigation
│   └── footer.blade.php      # Footer
├── index.blade.php           # Dashboard home
├── setting.blade.php         # User settings
├── order/                    # Order management
├── review/                   # Reviews
└── wallet/                   # Wallet features
```

### **Current CSS Framework:**
- **Backend CSS**: SB Admin 2 (Bootstrap-based)
- **Frontend CSS**: Custom CSS với Bootstrap
- **Icons**: Font Awesome
- **Fonts**: Nunito (Google Fonts)

---

## 🎨 Màu Sắc và Typography

### **Walmart Color Palette**
```css
:root {
  /* Primary Colors */
  --walmart-blue: #0071ce;
  --walmart-dark-blue: #004c91;
  --walmart-yellow: #ffc220;
  --walmart-orange: #ff6900;
  
  /* Neutral Colors */
  --white: #ffffff;
  --black: #000000;
  --gray-100: #f8f9fa;
  --gray-200: #e9ecef;
  --gray-300: #dee2e6;
  --gray-400: #ced4da;
  --gray-500: #adb5bd;
  --gray-600: #6c757d;
  --gray-700: #495057;
  --gray-800: #343a40;
  --gray-900: #212529;
  
  /* Semantic Colors */
  --success: #28a745;
  --info: #17a2b8;
  --warning: #ffc107;
  --danger: #dc3545;
  
  /* Background Colors */
  --bg-primary: #ffffff;
  --bg-secondary: #f8f9fa;
  --bg-light: #f5f5f5;
  
  /* Border Colors */
  --border-light: #e6e6e6;
  --border-medium: #d1d5db;
  --border-dark: #9ca3af;
}
```

### **Typography System**
```css
/* Font Imports */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
  /* Font Families */
  --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-secondary: 'Helvetica Neue', Arial, sans-serif;
  
  /* Font Sizes */
  --text-xs: 0.75rem;    /* 12px */
  --text-sm: 0.875rem;   /* 14px */
  --text-base: 1rem;     /* 16px */
  --text-lg: 1.125rem;   /* 18px */
  --text-xl: 1.25rem;    /* 20px */
  --text-2xl: 1.5rem;    /* 24px */
  --text-3xl: 1.875rem;  /* 30px */
  --text-4xl: 2.25rem;   /* 36px */
  
  /* Font Weights */
  --font-light: 300;
  --font-normal: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold: 700;
  
  /* Line Heights */
  --leading-tight: 1.25;
  --leading-normal: 1.5;
  --leading-relaxed: 1.75;
}
```

---

## 🏗️ Layout và Components

### **1. Header Navigation (Walmart Style)**
```html
<!-- Walmart-inspired header -->
<nav class="walmart-header">
  <div class="container-fluid">
    <div class="header-top">
      <!-- Logo và Search -->
      <div class="header-main">
        <div class="logo-section">
          <img src="logo.png" alt="E-SHOP" class="logo">
        </div>
        <div class="search-section">
          <div class="search-container">
            <input type="text" placeholder="Search everything at E-SHOP">
            <button class="search-btn">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
        <div class="user-actions">
          <div class="user-menu">
            <i class="fas fa-user"></i>
            <span>Account</span>
          </div>
          <div class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">0</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Navigation Menu -->
    <div class="header-nav">
      <ul class="nav-menu">
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Orders</a></li>
        <li><a href="#">Reviews</a></li>
        <li><a href="#">Wallet</a></li>
        <li><a href="#">Settings</a></li>
      </ul>
    </div>
  </div>
</nav>
```

### **2. Sidebar Navigation**
```html
<!-- Walmart-style sidebar -->
<div class="walmart-sidebar">
  <div class="sidebar-header">
    <h3>My Account</h3>
  </div>
  
  <nav class="sidebar-nav">
    <ul class="nav-list">
      <li class="nav-item active">
        <a href="#" class="nav-link">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-shopping-bag"></i>
          <span>My Orders</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-star"></i>
          <span>Reviews</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-wallet"></i>
          <span>Wallet</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </nav>
</div>
```

### **3. Card Components**
```html
<!-- Walmart-style cards -->
<div class="walmart-card">
  <div class="card-header">
    <h4 class="card-title">Recent Orders</h4>
    <a href="#" class="view-all">View All</a>
  </div>
  <div class="card-body">
    <div class="order-item">
      <div class="order-info">
        <h5>Order #12345</h5>
        <p class="order-date">Placed on Aug 15, 2024</p>
      </div>
      <div class="order-status">
        <span class="status delivered">Delivered</span>
      </div>
    </div>
  </div>
</div>
```

---

## 📋 Kế Hoạch Thực Hiện

### **Phase 1: Chuẩn Bị (1-2 ngày)**
1. **Backup files hiện tại**
2. **Tạo custom CSS file cho Walmart theme**
3. **Chuẩn bị assets (fonts, icons, images)**
4. **Setup color variables**

### **Phase 2: Layout Chính (2-3 ngày)**
1. **Chỉnh sửa master.blade.php**
2. **Redesign header.blade.php**
3. **Redesign sidebar.blade.php**
4. **Update footer.blade.php**

### **Phase 3: Components (2-3 ngày)**
1. **Tạo Walmart-style cards**
2. **Redesign forms và buttons**
3. **Update tables và lists**
4. **Responsive adjustments**

### **Phase 4: Pages (3-4 ngày)**
1. **Dashboard page (index.blade.php)**
2. **Orders pages**
3. **Settings page**
4. **Review pages**
5. **Wallet pages**

### **Phase 5: Testing & Polish (1-2 ngày)**
1. **Cross-browser testing**
2. **Mobile responsiveness**
3. **Performance optimization**
4. **Final adjustments**

---

## 📁 Files Cần Chỉnh Sửa

### **1. Layout Files**
```
resources/views/user/layouts/
├── master.blade.php          # ✏️ Major changes
├── head.blade.php           # ✏️ Add new CSS/fonts
├── header.blade.php         # ✏️ Complete redesign
├── sidebar.blade.php        # ✏️ Complete redesign
└── footer.blade.php         # ✏️ Minor updates
```

### **2. Page Files**
```
resources/views/user/
├── index.blade.php          # ✏️ Dashboard redesign
├── setting.blade.php        # ✏️ Settings page
├── order/                   # ✏️ All order pages
├── review/                  # ✏️ Review pages
└── wallet/                  # ✏️ Wallet pages
```

### **3. CSS Files**
```
public/css/
├── walmart-theme.css        # 🆕 New file
├── walmart-components.css   # 🆕 New file
└── walmart-responsive.css   # 🆕 New file
```

### **4. JavaScript Files**
```
public/js/
├── walmart-theme.js         # 🆕 New file
└── walmart-interactions.js  # 🆕 New file
```

---

## 🎨 CSS Framework và Assets

### **1. Custom CSS Structure**
```css
/* walmart-theme.css */

/* ===== RESET & BASE ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-primary);
  font-size: var(--text-base);
  line-height: var(--leading-normal);
  color: var(--gray-800);
  background-color: var(--bg-secondary);
}

/* ===== LAYOUT ===== */
.walmart-wrapper {
  display: flex;
  min-height: 100vh;
}

.walmart-sidebar {
  width: 250px;
  background: var(--white);
  border-right: 1px solid var(--border-light);
  position: fixed;
  height: 100vh;
  overflow-y: auto;
}

.walmart-main {
  flex: 1;
  margin-left: 250px;
  background: var(--bg-secondary);
}

.walmart-header {
  background: var(--white);
  border-bottom: 1px solid var(--border-light);
  padding: 1rem 2rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

/* ===== COMPONENTS ===== */
.walmart-card {
  background: var(--white);
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.walmart-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: var(--font-medium);
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.walmart-btn-primary {
  background: var(--walmart-blue);
  color: var(--white);
}

.walmart-btn-primary:hover {
  background: var(--walmart-dark-blue);
  color: var(--white);
}

.walmart-btn-secondary {
  background: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--border-light);
}

.walmart-btn-secondary:hover {
  background: var(--gray-200);
}
```

### **2. Component Styles**
```css
/* walmart-components.css */

/* ===== NAVIGATION ===== */
.sidebar-nav .nav-list {
  list-style: none;
  padding: 1rem 0;
}

.sidebar-nav .nav-item {
  margin-bottom: 0.25rem;
}

.sidebar-nav .nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--gray-700);
  text-decoration: none;
  transition: all 0.2s ease;
}

.sidebar-nav .nav-link:hover,
.sidebar-nav .nav-item.active .nav-link {
  background: var(--walmart-blue);
  color: var(--white);
}

.sidebar-nav .nav-link i {
  width: 20px;
  margin-right: 0.75rem;
  text-align: center;
}

/* ===== CARDS ===== */
.card-header {
  display: flex;
  justify-content: between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-light);
}

.card-title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--gray-800);
}

.card-body {
  padding: 1.5rem;
}

/* ===== FORMS ===== */
.walmart-form-group {
  margin-bottom: 1.5rem;
}

.walmart-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: var(--font-medium);
  color: var(--gray-700);
}

.walmart-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: 6px;
  font-size: var(--text-base);
  transition: border-color 0.2s ease;
}

.walmart-input:focus {
  outline: none;
  border-color: var(--walmart-blue);
  box-shadow: 0 0 0 3px rgba(0, 113, 206, 0.1);
}

/* ===== TABLES ===== */
.walmart-table {
  width: 100%;
  border-collapse: collapse;
  background: var(--white);
}

.walmart-table th,
.walmart-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-light);
}

.walmart-table th {
  background: var(--gray-50);
  font-weight: var(--font-semibold);
  color: var(--gray-700);
}

/* ===== STATUS BADGES ===== */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
}

.status-delivered {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}
```

### **3. Responsive Design**
```css
/* walmart-responsive.css */

/* ===== MOBILE FIRST ===== */
@media (max-width: 768px) {
  .walmart-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .walmart-sidebar.active {
    transform: translateX(0);
  }
  
  .walmart-main {
    margin-left: 0;
  }
  
  .walmart-header {
    padding: 1rem;
  }
  
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}

/* ===== TABLET ===== */
@media (min-width: 769px) and (max-width: 1024px) {
  .walmart-sidebar {
    width: 200px;
  }
  
  .walmart-main {
    margin-left: 200px;
  }
}

/* ===== DESKTOP ===== */
@media (min-width: 1025px) {
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
  }
}
```

---

## 🚀 Bước Tiếp Theo

### **Để Bắt Đầu Implementation:**

1. **Xác nhận thiết kế**: Review và approve design concept
2. **Backup dự án**: Tạo backup trước khi bắt đầu
3. **Tạo branch mới**: `git checkout -b walmart-theme`
4. **Bắt đầu với Phase 1**: Tạo CSS files và setup variables

### **Cần Hỗ Trợ Thêm:**
- Có cần tôi tạo mockup/wireframe chi tiết không?
- Có muốn tôi bắt đầu implement ngay không?
- Có cần customize thêm màu sắc hay components nào không?

---

## 📞 Liên Hệ & Hỗ Trợ

Nếu bạn cần hỗ trợ thêm trong quá trình implementation, hãy cho tôi biết:
- File nào cần chỉnh sửa trước
- Component nào cần ưu tiên
- Có vấn đề gì cần giải quyết

**Chúc bạn thành công với dự án! 🎉**
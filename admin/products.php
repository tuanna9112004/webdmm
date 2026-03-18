<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();
$pageTitle = 'Quản lý sản phẩm';

// --- XỬ LÝ BỘ LỌC TỪ REQUEST ---
$search_keyword = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_category = $_GET['category'] ?? '';

// Nếu hàm get_products của bạn đã hỗ trợ nhận các tham số lọc, bạn có thể truyền vào.
// Tạm thời mình giữ nguyên cấu trúc gọi hàm hiện tại của bạn:
$products = get_products(null, false); 

// require_once __DIR__ . '/../includes/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   MODERN ADMIN DASHBOARD STYLESHEET
   ========================================================================== */
:root {
    --admin-bg: #f3f4f6;
    --admin-card: #ffffff;
    --admin-text-main: #111827;
    --admin-text-muted: #6b7280;
    --admin-border: #e5e7eb;
    --admin-primary: #4f46e5;
    --admin-primary-hover: #4338ca;
    --admin-danger: #ef4444;
    --admin-danger-bg: #fef2f2;
    --admin-danger-border: #fecaca;
    --admin-success: #10b981;
    --admin-success-bg: #ecfdf5;
    --admin-warning: #f59e0b;
    --admin-radius: 12px;
    --admin-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --admin-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --sidebar-width: 260px;
}

body {
    background-color: var(--admin-bg);
    color: var(--admin-text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
}

/* ==========================================
   BỐ CỤC CHÍNH (LAYOUT CỘT)
   ========================================== */
.admin-wrapper {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

/* SIDEBAR */
.admin-sidebar {
    width: var(--sidebar-width);
    background: var(--admin-card);
    border-right: 1px solid var(--admin-border);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-header {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-header h2 {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    color: var(--admin-primary);
    letter-spacing: -0.5px;
}

.sidebar-menu {
    list-style: none;
    padding: 0 16px;
    margin: 0;
    flex-grow: 1;
}

.sidebar-menu li {
    margin-bottom: 4px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: var(--admin-text-muted);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.sidebar-menu a:hover {
    background-color: #f9fafb;
    color: var(--admin-text-main);
}

.sidebar-menu a.active {
    background-color: #eef2ff;
    color: var(--admin-primary);
    font-weight: 600;
}

.sidebar-menu a.text-danger {
    color: var(--admin-danger);
    margin-top: auto;
}
.sidebar-menu a.text-danger:hover {
    background-color: var(--admin-danger-bg);
}

/* MAIN CONTENT */
.admin-main {
    flex-grow: 1;
    padding: 32px;
    max-width: calc(100% - var(--sidebar-width));
    overflow-x: hidden;
}

.admin-container {
    background: var(--admin-card);
    border-radius: var(--admin-radius);
    box-shadow: var(--admin-shadow);
    padding: 24px;
    margin-bottom: 40px;
    border: 1px solid var(--admin-border);
}

.admin-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}

.admin-header-title h1 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--admin-text-main);
}

.admin-header-title p {
    color: var(--admin-text-muted);
    font-size: 14px;
    margin: 0;
}

.btn-primary-action {
    background-color: var(--admin-primary);
    color: #fff;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    box-shadow: var(--admin-shadow-sm);
}
.btn-primary-action:hover {
    background-color: var(--admin-primary-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

/* ==========================================
   BỘ LỌC THÔNG MINH
   ========================================== */
.admin-filters {
    background: #f9fafb;
    border: 1px solid var(--admin-border);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: var(--admin-text-main);
}

.filter-control {
    padding: 10px 14px;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    outline: none;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.filter-control:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.filter-actions {
    display: flex;
    gap: 12px;
}

.filter-actions .btn {
    padding: 10px 20px;
    height: 42px;
    font-size: 14px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-search { background: var(--admin-text-main); color: #fff; }
.btn-search:hover { background: #374151; }
.btn-reset { background: #e5e7eb; color: var(--admin-text-main); text-decoration: none; }
.btn-reset:hover { background: #d1d5db; }

/* ==========================================
   BẢNG DỮ LIỆU
   ========================================== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
    border: 1px solid var(--admin-border);
    background: #fff;
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: left;
    min-width: 1100px;
}

.admin-table th {
    background-color: #f9fafb;
    color: var(--admin-text-muted);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 16px 14px;
    border-bottom: 1px solid var(--admin-border);
    white-space: nowrap;
    letter-spacing: 0.5px;
}

.admin-table td {
    padding: 16px 14px;
    vertical-align: middle;
    border-bottom: 1px solid var(--admin-border);
    font-size: 14px;
}

.admin-table tr:hover td { background-color: #f9fafb; }
.admin-table tr:last-child td { border-bottom: none; }

.table-thumb {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--admin-border);
    flex-shrink: 0;
}

.cell-product-info { line-height: 1.5; }
.cell-product-name { font-weight: 600; display: block; margin-bottom: 2px; color: var(--admin-text-main); }
.cell-product-code { font-size: 12px; color: var(--admin-text-muted); }

/* Các loại nút Link Nhập */
.link-source {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
    white-space: nowrap;
}
.link-source:hover { transform: translateY(-1px); }

/* Nút Copy */
.btn-copy {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    color: #4b5563;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    font-family: 'Inter', sans-serif;
}
.btn-copy:hover {
    background: #e5e7eb;
}
.btn-copy.copied {
    background: var(--admin-success-bg);
    color: var(--admin-success);
    border-color: #a7f3d0;
}

/* Nút Zalo, FB, Phone, Web */
.link-zalo { color: #0068ff; background: #e5f0ff; }
.link-zalo:hover { background: #d0e4ff; }
.link-fb { color: #1877f2; background: #e7f0fd; }
.link-fb:hover { background: #d4e4fc; }
.link-phone { color: #059669; background: #d1fae5; }
.link-phone:hover { background: #bbf7d0; }
.link-web { color: #4b5563; background: #f3f4f6; }
.link-web:hover { background: #e5e7eb; }

/* Badges */
.status-badge {
    display: inline-flex;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    white-space: nowrap;
    align-items: center;
    gap: 4px;
}
.status-badge::before {
    content: '';
    display: block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.status-active { background: var(--admin-success-bg); color: var(--admin-success); }
.status-active::before { background-color: var(--admin-success); }
.status-inactive { background: #f3f4f6; color: var(--admin-text-muted); }
.status-inactive::before { background-color: var(--admin-text-muted); }

.badge-category {
    background: #eef2ff;
    color: var(--admin-primary);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.table-actions { 
    display: flex; 
    gap: 8px; 
    flex-wrap: nowrap; 
}
.table-actions .btn { 
    padding: 8px 12px; 
    font-size: 13px; 
    border-radius: 6px; 
    white-space: nowrap; 
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid transparent;
}
.table-actions .btn-light { background-color: #fff; color: var(--admin-text-main); border-color: var(--admin-border); }
.table-actions .btn-light:hover { background-color: #f9fafb; border-color: #d1d5db; }
.table-actions .btn-danger { background-color: #fff; color: var(--admin-danger); border-color: var(--admin-danger-border); }
.table-actions .btn-danger:hover { background-color: var(--admin-danger-bg); }

/* Responsive */
@media (max-width: 1024px) {
    .admin-main { padding: 20px; }
}

@media (max-width: 768px) {
    .admin-wrapper { flex-direction: column; }
    .admin-sidebar { width: 100%; height: auto; position: relative; border-right: none; border-bottom: 1px solid var(--admin-border); }
    .sidebar-menu { display: flex; overflow-x: auto; padding: 12px; gap: 8px; }
    .sidebar-menu li { margin: 0; white-space: nowrap; }
    .sidebar-menu a.text-danger { margin-top: 0; }
    
    .admin-main { max-width: 100%; padding: 16px; }
    .admin-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .filter-group { min-width: 100%; }
    .filter-actions { width: 100%; flex-direction: column; }
    .filter-actions .btn { width: 100%; }
}
</style>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--admin-primary)"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
            <h2>Quản Lý Dương Một Mí Shop</h2>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_URL ?>/admin/products.php" class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Sản phẩm
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/categories.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Danh mục
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/product_types.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Loại sản phẩm
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/product_conditions.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Tình trạng
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/styles.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    Phong cách
                </a>
            </li>
            <li style="margin-top: 24px;">
                <a href="<?= BASE_URL ?>/admin/logout.php" class="text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Đăng xuất
                </a>
            </li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-container">
            
            <div class="admin-header">
                <div class="admin-header-title">
                    <h1>Quản lý sản phẩm</h1>
                    <p>Xin chào, <?= e($_SESSION['admin_name'] ?? 'Admin') ?> 👋 Quản lý kho hàng của bạn tại đây.</p>
                </div>
                <div>
                    <a class="btn-primary-action" href="<?= BASE_URL ?>/admin/product_form.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Thêm Sản Phẩm Mới
                    </a>
                </div>
            </div>

            <div class="admin-filters">
                <form action="" method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="search">Tìm kiếm</label>
                        <input type="text" name="search" id="search" class="filter-control" placeholder="Tên hoặc mã SP..." value="<?= e($search_keyword) ?>">
                    </div>

                    <div class="filter-group">
                        <label for="category">Danh mục</label>
                        <select name="category" id="category" class="filter-control">
                            <option value="">-- Tất cả danh mục --</option>
                            <option value="1" <?= $filter_category == '1' ? 'selected' : '' ?>>Áo thun</option>
                            <option value="2" <?= $filter_category == '2' ? 'selected' : '' ?>>Quần Jean</option>
                            <option value="3" <?= $filter_category == '3' ? 'selected' : '' ?>>Phụ kiện</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Trạng thái hiển thị</label>
                        <select name="status" id="status" class="filter-control">
                            <option value="">-- Tất cả --</option>
                            <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Đang hiện</option>
                            <option value="inactive" <?= $filter_status == 'inactive' ? 'selected' : '' ?>>Đã ẩn</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-search">Tìm kiếm</button>
                        <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-reset">Xóa bộ lọc</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Tình trạng</th>
                            <th>Giá bán</th>
                            <th>Giá nhập</th>
                            <th>Ghi chú</th>
                            <th>Kho</th>
                            <th>Nguồn nhập</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 60px 20px; color: var(--admin-text-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; color: #9ca3af;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <br>
                                Không tìm thấy sản phẩm nào phù hợp.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td style="color: var(--admin-text-muted); font-weight: 500; font-family: monospace; font-size: 13px;">
                                #<?= sprintf('%04d', (int)$product['id']) ?>
                            </td>
                            
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img class="table-thumb" src="<?= e(resolve_media_url($product['thumbnail'])) ?>" alt="Thumb">
                                    <div class="cell-product-info">
                                        <span class="cell-product-name"><?= e($product['product_name']) ?></span>
                                        <span class="cell-product-code"><?= e($product['product_code']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div style="margin-bottom: 4px;">
                                    <span class="badge-category"><?= e($product['category_name'] ?: 'Chưa phân loại') ?></span>
                                </div>
                                <div style="color: var(--admin-text-muted); font-size: 13px;">
                                    <?= e($product['product_type_name'] ?: '-') ?> • <?= e($product['gender'] ?: '-') ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($product['condition_names'])): ?>
                                    <span style="background: #f3f4f6; color: #4b5563; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                        <?= e($product['condition_names']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--admin-text-muted);">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($product['sale_price'])): ?>
                                    <div style="font-weight: 700; color: var(--admin-danger);"><?= format_price($product['sale_price']) ?></div>
                                    <div style="font-size: 12px; color: var(--admin-text-muted); text-decoration: line-through; margin-top: 2px;"><?= format_price($product['original_price']) ?></div>
                                <?php else: ?>
                                    <div style="font-weight: 600; color: var(--admin-text-main);"><?= format_price($product['original_price']) ?></div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($product['purchase_price'] !== null && $product['purchase_price'] !== '' && (float)$product['purchase_price'] > 0): ?>
                                    <div style="font-weight: 600; color: var(--admin-success);"><?= format_price($product['purchase_price']) ?></div>
                                <?php else: ?>
                                    <span style="color: var(--admin-text-muted);">-</span>
                                <?php endif; ?>
                            </td>

                            <td style="max-width: 200px;">
                                <?php if (!empty(trim((string)($product['note'] ?? '')))): ?>
                                    <div style="white-space: normal; line-height: 1.5; color: var(--admin-text-muted); font-size: 13px;">
                                        <?= nl2br(e($product['note'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--admin-text-muted);">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php $qty = (int)$product['quantity']; ?>
                                <strong style="color: <?= $qty > 0 ? 'inherit' : 'var(--admin-danger)' ?>;">
                                    <?= $qty ?>
                                </strong>
                            </td>

                            <td>
                                <?php 
                                $sourceData = trim($product['import_link'] ?? ''); 
                                
                                if (empty($sourceData)) {
                                    echo '<span style="color: var(--admin-text-muted);">-</span>';
                                } else {
                                    $lowerData = strtolower($sourceData);
                                    $isPhone = preg_match('/^[0-9\+\-\s\.]+$/', $sourceData) && strlen(preg_replace('/[^0-9]/', '', $sourceData)) >= 8;
                                    
                                    echo '<div style="display:flex; gap:8px; flex-direction:column; align-items:flex-start;">';
                                    
                                    if ($isPhone) {
                                        $cleanPhone = preg_replace('/[^0-9\+]/', '', $sourceData);
                                        echo '<a class="link-source link-phone" href="tel:' . e($cleanPhone) . '">📞 ' . e($sourceData) . '</a>';
                                        
                                        echo '<div style="display: flex; gap: 6px;">';
                                        echo '<a class="link-source link-zalo" target="_blank" href="https://zalo.me/' . e($cleanPhone) . '">💬 Zalo</a>';
                                        echo '<button type="button" class="btn-copy" onclick="copyText(this, \'' . e($cleanPhone) . '\')">📋 Copy</button>';
                                        echo '</div>';
                                    } else {
                                        $hrefUrl = (strpos($sourceData, 'http') !== 0) ? 'https://' . $sourceData : $sourceData;
                                        
                                        echo '<div style="display: flex; gap: 6px;">';
                                        if (strpos($lowerData, 'zalo.me') !== false) {
                                            echo '<a class="link-source link-zalo" target="_blank" href="' . e($sourceData) . '">💬 Chat Zalo</a>';
                                        } elseif (strpos($lowerData, 'facebook.com') !== false || strpos($lowerData, 'fb.com') !== false) {
                                            echo '<a class="link-source link-fb" target="_blank" href="' . e($sourceData) . '">📘 Facebook</a>';
                                        } else {
                                            $displayUrl = (strlen($sourceData) > 18) ? substr($sourceData, 0, 15) . '...' : $sourceData;
                                            echo '<a class="link-source link-web" target="_blank" href="' . e($hrefUrl) . '">🔗 ' . e($displayUrl) . '</a>';
                                        }
                                        echo '<button type="button" class="btn-copy" onclick="copyText(this, \'' . e($sourceData) . '\')">📋 Copy</button>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </td>

                            <td>
                                <span class="status-badge <?= !empty($product['is_active']) ? 'status-active' : 'status-inactive' ?>">
                                    <?= !empty($product['is_active']) ? 'Đang hiện' : 'Đã ẩn' ?>
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_form.php?id=<?= (int)$product['id'] ?>">Sửa</a>
                                    <a class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');" href="<?= BASE_URL ?>/admin/product_delete.php?id=<?= (int)$product['id'] ?>">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
// Hàm thực hiện copy số/link
function copyText(button, textToCopy) {
    if (navigator.clipboard && window.isSecureContext) {
        // Sử dụng API Clipboard hiện đại
        navigator.clipboard.writeText(textToCopy).then(() => {
            showCopiedState(button);
        }).catch(err => {
            console.error('Lỗi khi copy: ', err);
            fallbackCopyTextToClipboard(textToCopy, button);
        });
    } else {
        // Fallback cho trình duyệt cũ hoặc không có HTTPS
        fallbackCopyTextToClipboard(textToCopy, button);
    }
}

// Chuyển đổi trạng thái nút sau khi copy thành công
function showCopiedState(button) {
    const originalText = button.innerHTML;
    button.innerHTML = "✔ Đã copy!";
    button.classList.add("copied");
    
    // Tự động trả về nút cũ sau 2 giây
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove("copied");
    }, 2000);
}

// Phương pháp dự phòng nếu không dùng được Clipboard API
function fallbackCopyTextToClipboard(text, button) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed"; // Tránh bị cuộn màn hình
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        if(successful) {
            showCopiedState(button);
        } else {
            alert('Không thể copy, vui lòng thao tác tay!');
        }
    } catch (err) {
        alert('Trình duyệt không hỗ trợ copy tự động!');
    }
    document.body.removeChild(textArea);
}
</script>


<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);

    if ($name !== '') {
        insert_lookup_item('product_conditions', $name, $sort);
    }

    redirect('/admin/product_conditions.php');
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    try {
        db()->prepare('DELETE FROM product_conditions WHERE id = ?')->execute([$deleteId]);
        redirect('/admin/product_conditions.php');
    } catch (Throwable $e) {
        $error = 'Không thể xóa tình trạng này vì đang có sản phẩm sử dụng nó.';
    }
}

$pageTitle = 'Tình trạng sản phẩm';

$stmt = db()->query('SELECT id, name, slug, sort_order, created_at FROM product_conditions ORDER BY sort_order ASC, id ASC');
$conditions = $stmt->fetchAll();

// require_once __DIR__ . '/../includes/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   MODERN ADMIN DASHBOARD STYLESHEET (Đồng bộ)
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
   BỐ CỤC CHÍNH
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

.admin-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.admin-header h1 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    color: var(--admin-text-main);
}

/* MENU PHỤ (Nav Tabs) */
.admin-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-nav .btn {
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.admin-nav .btn-light {
    background-color: #fff;
    color: var(--admin-text-muted);
    border-color: var(--admin-border);
}
.admin-nav .btn-light:hover {
    background-color: #f9fafb;
    color: var(--admin-text-main);
}
.admin-nav .btn-back {
    background-color: var(--admin-text-main);
    color: #fff;
}
.admin-nav .btn-back:hover {
    background-color: #374151;
}

/* ==========================================
   ALERTS (THÔNG BÁO)
   ========================================== */
.alert {
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.alert.error {
    background-color: var(--admin-danger-bg);
    color: #b91c1c;
    border: 1px solid var(--admin-danger-border);
}

/* ==========================================
   GRID 2 CỘT (FORM & BẢNG)
   ========================================== */
.category-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 992px) {
    .category-layout {
        grid-template-columns: 340px 1fr;
        align-items: start;
    }
}

.admin-card {
    background: var(--admin-card);
    border-radius: var(--admin-radius);
    box-shadow: var(--admin-shadow-sm);
    border: 1px solid var(--admin-border);
    padding: 24px;
}

.admin-card h3 {
    font-size: 16px;
    font-weight: 700;
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--admin-border);
    color: var(--admin-text-main);
}

/* ==========================================
   FORM STYLES
   ========================================== */
.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--admin-text-main);
    margin-bottom: 8px;
}

.required-mark {
    color: var(--admin-danger);
}

.form-control {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    font-size: 14px;
    color: var(--admin-text-main);
    outline: none;
    transition: all 0.2s ease;
    background-color: #fff;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.btn-submit {
    width: 100%;
    background-color: var(--admin-primary);
    color: #fff;
    padding: 12px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
    font-family: 'Inter', sans-serif;
}
.btn-submit:hover {
    background-color: var(--admin-primary-hover);
    transform: translateY(-1px);
}

/* ==========================================
   TABLE STYLES
   ========================================== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: left;
    min-width: 600px;
}

.admin-table th {
    background-color: #f9fafb;
    color: var(--admin-text-muted);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 14px 16px;
    border-bottom: 1px solid var(--admin-border);
    white-space: nowrap;
    letter-spacing: 0.5px;
}

.admin-table td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--admin-border);
    font-size: 14px;
    color: var(--admin-text-main);
}

.admin-table tr:hover td {
    background-color: #f9fafb;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.btn-delete {
    background-color: #fff;
    color: var(--admin-danger);
    border: 1px solid var(--admin-danger-border);
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-delete:hover {
    background-color: var(--admin-danger-bg);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-wrapper { flex-direction: column; }
    .admin-sidebar { width: 100%; height: auto; position: relative; border-right: none; border-bottom: 1px solid var(--admin-border); }
    .sidebar-menu { display: flex; overflow-x: auto; padding: 12px; gap: 8px; }
    .sidebar-menu li { margin: 0; white-space: nowrap; }
    .sidebar-menu a.text-danger { margin-top: 0; }
    
    .admin-main { max-width: 100%; padding: 16px; }
    .admin-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .admin-nav { width: 100%; }
    .admin-nav .btn { flex: 1; text-align: center; justify-content: center; }
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
                <a href="<?= BASE_URL ?>/admin/products.php">
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
                <a href="<?= BASE_URL ?>/admin/product_conditions.php" class="active">
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
        <div class="admin-header">
            <h1>Tình trạng sản phẩm</h1>
            
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="category-layout">
            <div class="admin-card">
                <form method="post" action="">
                    <h3>Thêm tình trạng</h3>

                    <div class="form-group">
                        <label for="name">Tên tình trạng <span class="required-mark">*</span></label>
                        <input id="name" type="text" name="name" class="form-control" required placeholder="VD: Mới về, Bán chạy, Sale...">
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Thứ tự hiển thị <span style="font-weight: 400; color: var(--admin-text-muted);">(Tùy chọn)</span></label>
                        <input id="sort_order" type="number" name="sort_order" class="form-control" value="0" placeholder="0">
                    </div>

                    <button class="btn-submit" type="submit">Lưu tình trạng</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Danh sách tình trạng</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tên tình trạng</th>
                                <th>Slug</th>
                                <th style="text-align: center;">Thứ tự</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($conditions)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px 20px; color: var(--admin-text-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; color: #9ca3af;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <br>Chưa có tình trạng nào. Hãy thêm mới ở form bên cạnh!
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($conditions as $condition): ?>
                            <tr>
                                <td><strong style="font-weight: 600;"><?= e($condition['name']) ?></strong></td>
                                <td style="color: var(--admin-text-muted); font-size: 13px; font-family: monospace;"><?= e($condition['slug'] ?? '') ?></td>
                                <td style="text-align: center;">
                                    <span style="background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #4b5563;">
                                        <?= (int)$condition['sort_order'] ?>
                                    </span>
                                </td>
                                <td style="font-size: 13px; color: var(--admin-text-muted);"><?= e(date('d/m/Y', strtotime($condition['created_at'] ?? 'now'))) ?></td>
                                <td>
                                    <a class="btn-delete" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa tình trạng này?');" 
                                       href="<?= BASE_URL ?>/admin/product_conditions.php?delete=<?= (int)$condition['id'] ?>">
                                       Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
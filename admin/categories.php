<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    if ($name !== '') {
        insert_lookup_item('categories', $name, $sort);
    }
    redirect('/admin/categories.php');
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$deleteId]);
        redirect('/admin/categories.php');
    } catch (Throwable $e) {
        $error = 'Không thể xóa danh mục này vì đang có loại sản phẩm hoặc sản phẩm liên kết.';
    }
}

$pageTitle = 'Quản lý Danh mục';
$categories = get_categories();
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================================
   CSS DÀNH RIÊNG CHO TRANG ADMIN (Đồng bộ thiết kế)
   ========================================================================== */
.admin-wrapper {
    padding-top: 20px;
    padding-bottom: 60px;
}

.admin-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--line-light, #e5e7eb);
}

.admin-header h1 {
    font-size: 24px;
    color: var(--text-main);
    margin: 0;
}

.admin-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-nav .btn {
    padding: 8px 14px;
    font-size: 13px;
    min-height: unset;
}

.admin-two-col {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 992px) {
    .admin-two-col {
        grid-template-columns: 350px 1fr;
        align-items: start;
    }
}

.card-box {
    background: var(--bg-white, #fff);
    border-radius: var(--radius-lg, 12px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--line-light, #e5e7eb);
    padding: 24px;
}

.card-box h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--line-light, #e5e7eb);
    color: var(--text-main);
}

/* Style Form */
.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin-bottom: 8px;
}

.required-mark {
    color: var(--danger-color, #ef4444);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--line-strong, #d1d5db);
    border-radius: var(--radius-md, 8px);
    font-size: 14px;
    color: var(--text-main);
    outline: none;
    transition: border-color 0.2s;
    background-color: var(--bg-white, #fff);
}

.form-control:focus {
    border-color: var(--primary-color, #000);
}

/* Bảng hiển thị dữ liệu */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    min-width: 500px;
}

.admin-table th {
    background-color: #f8fafc;
    color: var(--text-muted, #6b7280);
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    padding: 12px 16px;
    border-bottom: 2px solid var(--line-light, #e5e7eb);
    white-space: nowrap;
}

.admin-table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--line-light, #e5e7eb);
    font-size: 14px;
    color: var(--text-main);
}

.admin-table tr:hover td {
    background-color: #f8fafc;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

/* Alerts */
.alert {
    padding: 14px 16px;
    border-radius: var(--radius-md, 8px);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
}

.alert.error {
    background-color: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}

.alert.info {
    background-color: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.btn-danger {
    background-color: #fef2f2;
    color: var(--danger-color, #ef4444);
    border: 1px solid #fca5a5;
}
.btn-danger:hover {
    background-color: var(--danger-color, #ef4444);
    color: #fff;
    border-color: var(--danger-color, #ef4444);
}

@media (max-width: 768px) {
    .admin-nav { width: 100%; }
    .admin-nav .btn { flex: 1; text-align: center; }
    .card-box { padding: 16px; }
}
</style>

<div class="container admin-wrapper">
    <div class="admin-header">
        <h1>Danh mục sản phẩm</h1>
        <div class="admin-nav">
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_types.php">Loại sản phẩm</a>
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_conditions.php">Tình trạng</a>
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/styles.php">Phong cách</a>
            <a class="btn" style="background-color: #333;" href="<?= BASE_URL ?>/admin/products.php">← Quay lại kho SP</a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert error">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="alert info">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        Hệ thống đang dùng cấu trúc phân loại 2 tầng. <strong>Ví dụ:</strong> "Áo" là Danh mục chính, còn "Áo Khoác", "Áo Polo" sẽ là Loại sản phẩm thuộc danh mục "Áo".
    </div>

    <div class="admin-two-col">
        <div class="card-box">
            <form method="post" action="">
                <h3>Thêm danh mục chính</h3>
                
                <div class="form-group">
                    <label>Tên danh mục <span class="required-mark">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Áo, Quần, Giày...">
                </div>

                <div class="form-group">
                    <label>Thứ tự hiển thị (Tùy chọn)</label>
                    <input type="number" name="sort_order" class="form-control" value="0" placeholder="0">
                </div>

                <button class="btn" type="submit" style="width: 100%; margin-top: 8px;">Lưu danh mục</button>
            </form>
        </div>

        <div class="card-box">
            <h3>Danh sách danh mục</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tên danh mục</th>
                            <th>Đường dẫn (Slug)</th>
                            <th style="text-align: center;">Thứ tự</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có danh mục nào. Hãy thêm mới ở form bên cạnh!
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td style="color: var(--text-muted); font-size: 13px;"><?= e($cat['slug'] ?? '') ?></td>
                            <td style="text-align: center;"><?= (int)$cat['sort_order'] ?></td>
                            <td>
                                <a class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; min-height: unset;" 
                                   onclick="return confirm('Bạn có chắc chắn xóa danh mục này? Hành động này có thể bị chặn nếu đang có Loại sản phẩm chứa bên trong.');" 
                                   href="<?= BASE_URL ?>/admin/categories.php?delete=<?= (int)$cat['id'] ?>">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
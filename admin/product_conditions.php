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

require_once __DIR__ . '/../includes/header.php';
?>

<style>
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
    color: var(--text-main, #111827);
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
    border-radius: 12px;
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
    color: var(--text-main, #111827);
}

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
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--line-strong, #d1d5db);
    border-radius: 8px;
    font-size: 14px;
    color: var(--text-main, #111827);
    outline: none;
    transition: border-color 0.2s;
    background-color: #fff;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: #111827;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    min-width: 620px;
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
    color: var(--text-main, #111827);
}

.admin-table tr:hover td {
    background-color: #f8fafc;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.alert {
    padding: 14px 16px;
    border-radius: 8px;
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

.btn-danger {
    background-color: #fef2f2;
    color: #ef4444;
    border: 1px solid #fca5a5;
}

.btn-danger:hover {
    background-color: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

.text-muted {
    color: #6b7280;
    font-size: 13px;
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .admin-nav {
        width: 100%;
    }

    .admin-nav .btn {
        flex: 1;
        text-align: center;
    }

    .card-box {
        padding: 16px;
    }
}
</style>

<div class="container admin-wrapper">
    <div class="admin-header">
        <h1>Tình trạng sản phẩm</h1>
        <div class="admin-nav">
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/categories.php">Danh mục</a>
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_types.php">Loại sản phẩm</a>
            <a class="btn btn-light" style="background-color:#111827;color:#fff;" href="<?= BASE_URL ?>/admin/product_conditions.php">Tình trạng</a>
            <a class="btn" style="background-color:#333;" href="<?= BASE_URL ?>/admin/products.php">← Quay lại kho SP</a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="admin-two-col">
        <div class="card-box">
            <form method="post" action="">
                <h3>Thêm tình trạng</h3>

                <div class="form-group">
                    <label for="name">Tên tình trạng <span class="required-mark">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control"
                        required
                        placeholder="VD: Mới về, Bán chạy, Sale..."
                    >
                </div>

                <div class="form-group">
                    <label for="sort_order">Thứ tự hiển thị</label>
                    <input
                        id="sort_order"
                        type="number"
                        name="sort_order"
                        class="form-control"
                        value="0"
                        placeholder="0"
                    >
                </div>

                <button class="btn" type="submit" style="width:100%;margin-top:8px;">Lưu tình trạng</button>
            </form>
        </div>

        <div class="card-box">
            <h3>Danh sách tình trạng</h3>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên tình trạng</th>
                            <th>Slug</th>
                            <th class="text-center">Thứ tự</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($conditions)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:30px;color:#6b7280;">
                                Chưa có tình trạng nào. Hãy thêm mới ở form bên trái.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($conditions as $condition): ?>
                        <tr>
                            <td><?= (int)$condition['id'] ?></td>
                            <td><strong><?= e($condition['name']) ?></strong></td>
                            <td class="text-muted"><?= e($condition['slug'] ?? '') ?></td>
                            <td class="text-center"><?= (int)$condition['sort_order'] ?></td>
                            <td><?= e($condition['created_at'] ?? '') ?></td>
                            <td>
                                <a
                                    class="btn btn-danger"
                                    style="padding:6px 12px;font-size:12px;min-height:unset;"
                                    href="<?= BASE_URL ?>/admin/product_conditions.php?delete=<?= (int)$condition['id'] ?>"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa tình trạng này?');"
                                >
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
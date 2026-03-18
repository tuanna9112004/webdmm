<?php
require_once __DIR__ . '/../includes/functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['full_name'];
        redirect('/admin/products.php');
    }
    $error = 'Sai tài khoản hoặc mật khẩu.';
}
$pageTitle = 'Đăng nhập quản trị';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================================
   CSS DÀNH RIÊNG CHO TRANG ĐĂNG NHẬP ADMIN
   ========================================================================== */
.auth-wrapper {
    min-height: calc(100vh - 400px); /* Đảm bảo căn giữa theo chiều dọc màn hình */
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 15px;
}

.admin-auth-box {
    background: var(--bg-white, #fff);
    width: 100%;
    max-width: 400px;
    padding: 40px;
    border-radius: var(--radius-lg, 12px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--line-light, #e5e7eb);
}

.admin-auth-box h1 {
    font-size: 24px;
    font-weight: 800;
    text-align: center;
    margin-bottom: 8px;
    color: var(--text-main);
}

.auth-subtitle {
    text-align: center;
    color: var(--text-muted);
    font-size: 14px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--line-strong, #d1d5db);
    border-radius: var(--radius-md, 8px);
    font-size: 15px;
    color: var(--text-main);
    outline: none;
    transition: all 0.2s;
    background-color: #f9fafb;
}

.form-control:focus {
    border-color: var(--primary-color, #000);
    background-color: #fff;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05);
}

.btn-login {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: 700;
    margin-top: 10px;
    border-radius: 8px;
}

/* Alert báo lỗi */
.alert.error {
    background-color: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.auth-footer-hint {
    margin-top: 25px;
    text-align: center;
    padding-top: 20px;
    border-top: 1px dashed var(--line-light);
    font-size: 13px;
    color: var(--text-muted);
}

.auth-footer-hint strong {
    color: var(--text-main);
}
</style>

<div class="auth-wrapper">
    <div class="admin-auth-box">
        <h1>Hệ thống Quản trị</h1>
        <p class="auth-subtitle">Vui lòng đăng nhập để quản lý cửa hàng</p>

        <?php if ($error): ?>
            <div class="alert error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">Tên tài khoản</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="Nhập tài khoản admin..." 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="••••••••" 
                    required
                >
            </div>

            <button class="btn btn-login" type="submit">
                Đăng nhập ngay
            </button>
        </form>

        <div class="auth-footer-hint">
            <!-- Tài khoản mặc định: <strong>admin</strong> / <strong>admin123</strong> -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
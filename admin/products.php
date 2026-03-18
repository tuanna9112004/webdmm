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

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================================
   LUXURY ADMIN DASHBOARD STYLESHEET
   ========================================================================== */
:root {
    --admin-bg: #f8fafc;
    --admin-card: #ffffff;
    --admin-text-main: #0f172a;
    --admin-text-muted: #64748b;
    --admin-border: #e2e8f0;
    --admin-primary: #1e293b;
    --admin-danger: #ef4444;
    --admin-danger-bg: #fef2f2;
    --admin-danger-border: #fca5a5;
    --admin-success: #059669;
    --admin-success-bg: #d1fae5;
    --admin-radius: 16px;
    --admin-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
}

body {
    background-color: var(--admin-bg);
    color: var(--admin-text-main);
}

/* Mở rộng tối đa khung container */
.container-fluid {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
}

.admin-container {
    background: var(--admin-card);
    border-radius: var(--admin-radius);
    box-shadow: var(--admin-shadow);
    padding: 24px;
    margin-bottom: 40px;
    border: 1px solid rgba(0,0,0,0.02);
}

.admin-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--admin-border);
}

.admin-header-title h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.admin-header-title p {
    color: var(--admin-text-muted);
    font-size: 14px;
    font-weight: 500;
}

.admin-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-nav .btn {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.admin-nav .btn-light {
    background-color: #f1f5f9;
    color: var(--admin-text-main);
    border: 1px solid transparent;
}
.admin-nav .btn-light:hover {
    background-color: #e2e8f0;
    transform: translateY(-1px);
}

.admin-nav .btn-danger {
    background-color: var(--admin-danger-bg);
    color: var(--admin-danger);
    border: 1px solid var(--admin-danger-border);
}
.admin-nav .btn-danger:hover {
    background-color: var(--admin-danger);
    color: #fff;
}

/* ==========================================
   BỘ LỌC THÔNG MINH
   ========================================== */
.admin-filters {
    background: #f8fafc;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
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
    gap: 8px;
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
}

.filter-control:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
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
}

.btn-search { background: var(--admin-primary); color: #fff; }
.btn-search:hover { background: #0f172a; transform: translateY(-1px); }
.btn-reset { background: #e2e8f0; color: var(--admin-text-main); text-decoration: none; display: inline-flex; align-items: center; }
.btn-reset:hover { background: #cbd5e1; }

/* ==========================================
   BẢNG DỮ LIỆU
   ========================================== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 12px;
    border: 1px solid var(--admin-border);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.admin-table th {
    background-color: #f8fafc;
    color: var(--admin-text-muted);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 14px 10px;
    border-bottom: 1px solid var(--admin-border);
    white-space: nowrap;
}

.admin-table td {
    padding: 14px 10px;
    vertical-align: middle;
    border-bottom: 1px solid var(--admin-border);
    font-size: 13px;
}

.admin-table tr:hover td { background-color: #fcfcfd; }
.admin-table tr:last-child td { border-bottom: none; }

.table-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--admin-border);
    flex-shrink: 0;
}

.cell-product-info { line-height: 1.4; }
.cell-product-name { font-weight: 600; display: block; margin-bottom: 4px; font-size: 13px; }
.cell-product-code { font-size: 11px; color: var(--admin-text-muted); background: #f1f5f9; padding: 2px 6px; border-radius: 4px; display: inline-block; }

/* Các loại nút Link Nhập */
.link-source {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    font-size: 11px;
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
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.btn-copy:hover {
    background: #e2e8f0;
}
.btn-copy.copied {
    background: var(--admin-success-bg);
    color: var(--admin-success);
    border-color: #a7f3d0;
}

/* Nút Zalo, FB, Phone, Web */
.link-zalo { color: #0068ff; background: #e5f0ff; border-color: #b3d4ff; }
.link-zalo:hover { box-shadow: 0 2px 6px rgba(0, 104, 255, 0.15); }
.link-fb { color: #1877f2; background: #e7f0fd; border-color: #b9d3fa; }
.link-fb:hover { box-shadow: 0 2px 6px rgba(24, 119, 242, 0.15); }
.link-phone { color: #059669; background: #d1fae5; border-color: #a7f3d0; }
.link-phone:hover { box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15); }
.link-web { color: #475569; background: #f1f5f9; border-color: #cbd5e1; }
.link-web:hover { box-shadow: 0 2px 6px rgba(71, 85, 105, 0.15); }

.status-badge {
    display: inline-flex;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 50px;
    white-space: nowrap;
}
.status-active { background: var(--admin-success-bg); color: var(--admin-success); border: 1px solid #a7f3d0; }
.status-inactive { background: #f1f5f9; color: var(--admin-text-muted); border: 1px solid #e2e8f0; }

.table-actions { 
    display: flex; 
    gap: 6px; 
    flex-wrap: wrap; 
}
.table-actions .btn { padding: 6px 12px; font-size: 12px; min-height: unset; box-shadow: none; border-radius: 6px; white-space: nowrap; }

@media (max-width: 1024px) {
    .admin-table { min-width: 900px; }
}

@media (max-width: 768px) {
    .admin-container { padding: 15px; margin-top: 10px; }
    .admin-header { flex-direction: column; align-items: flex-start; }
    .admin-nav { width: 100%; justify-content: space-between; }
    .admin-nav .btn { flex: 1; justify-content: center; }
    .filter-group { min-width: 100%; }
    .filter-actions { width: 100%; }
    .filter-actions .btn { flex: 1; }
}
</style>

<div class="container-fluid">
    <div class="admin-container">
        
        <div class="admin-header">
            <div class="admin-header-title">
                <h1>Quản lý sản phẩm</h1>
                <p>Xin chào, <?= e($_SESSION['admin_name'] ?? 'Admin') ?> 👋</p>
            </div>
            <div class="admin-nav">
                <a class="btn btn-light" href="<?= BASE_URL ?>/admin/categories.php">Danh mục</a>
                <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_types.php">Loại</a>
                <a class="btn btn-light" href="<?= BASE_URL ?>/admin/product_conditions.php">Tình trạng</a>
                <a class="btn btn-light" href="<?= BASE_URL ?>/admin/styles.php">Phong cách</a>
                <a class="btn" style="background: var(--admin-primary); color: #fff;" href="<?= BASE_URL ?>/admin/product_form.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Thêm SP
                </a>
                <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/logout.php">Đăng xuất</a>
            </div>
        </div>

        <div class="admin-filters">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" name="search" id="search" class="filter-control" placeholder="Tên hoặc mã sản phẩm..." value="<?= e($search_keyword) ?>">
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
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="filter-control">
                        <option value="">-- Tất cả --</option>
                        <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Đang hiện</option>
                        <option value="inactive" <?= $filter_status == 'inactive' ? 'selected' : '' ?>>Đã ẩn</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-search">Tìm kiếm</button>
                    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-reset">Xóa lọc</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            Không tìm thấy sản phẩm nào phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($products as $product): ?>
                    <tr>
                        <td style="color: var(--admin-text-muted); font-weight: 500;">
                            #<?= sprintf('%04d', (int)$product['id']) ?>
                        </td>
                        
                        <td style="display: flex; align-items: center; gap: 10px; border-bottom: none;">
                            <img class="table-thumb" src="<?= e(resolve_media_url($product['thumbnail'])) ?>" alt="Thumb">
                            <div class="cell-product-info">
                                <span class="cell-product-name"><?= e($product['product_name']) ?></span>
                                <span class="cell-product-code">Mã: <?= e($product['product_code']) ?></span>
                            </div>
                        </td>

                        <td>
                            <div style="margin-bottom: 2px;">
                                <strong>DM:</strong> <?= e($product['category_name'] ?: '-') ?>
                            </div>
                            <div style="color: var(--admin-text-muted);">
                                <?= e($product['product_type_name'] ?: '-') ?> • <?= e($product['gender'] ?: '-') ?>
                            </div>
                        </td>

                        <td>
                            <?php if (!empty($product['condition_names'])): ?>
                                <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                                    <?= e($product['condition_names']) ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!empty($product['sale_price'])): ?>
                                <div style="font-weight: 700; color: var(--admin-danger);"><?= format_price($product['sale_price']) ?></div>
                                <div style="font-size: 11px; color: var(--admin-text-muted); text-decoration: line-through;"><?= format_price($product['original_price']) ?></div>
                            <?php else: ?>
                                <div style="font-weight: 600;"><?= format_price($product['original_price']) ?></div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($product['purchase_price'] !== null && $product['purchase_price'] !== '' && (float)$product['purchase_price'] > 0): ?>
                                <div style="font-weight: 600; color: #0f766e;"><?= format_price($product['purchase_price']) ?></div>
                            <?php else: ?>
                                <span style="color: var(--admin-text-muted);">-</span>
                            <?php endif; ?>
                        </td>

                        <td style="max-width: 220px;">
                            <?php if (!empty(trim((string)($product['note'] ?? '')))): ?>
                                <div style="white-space: normal; line-height: 1.5;">
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
                                
                                echo '<div style="display:flex; gap:6px; flex-direction:column; align-items:flex-start;">';
                                
                                if ($isPhone) {
                                    $cleanPhone = preg_replace('/[^0-9\+]/', '', $sourceData);
                                    echo '<a class="link-source link-phone" href="tel:' . e($cleanPhone) . '">📞 ' . e($sourceData) . '</a>';
                                    
                                    echo '<div style="display: flex; gap: 4px;">';
                                    echo '<a class="link-source link-zalo" target="_blank" href="https://zalo.me/' . e($cleanPhone) . '">💬 Add Zalo</a>';
                                    echo '<button type="button" class="btn-copy" onclick="copyText(this, \'' . e($cleanPhone) . '\')">📋 Copy số</button>';
                                    echo '</div>';
                                } else {
                                    $hrefUrl = (strpos($sourceData, 'http') !== 0) ? 'https://' . $sourceData : $sourceData;
                                    
                                    echo '<div style="display: flex; gap: 4px;">';
                                    if (strpos($lowerData, 'zalo.me') !== false) {
                                        echo '<a class="link-source link-zalo" target="_blank" href="' . e($sourceData) . '">💬 Chat Zalo</a>';
                                    } elseif (strpos($lowerData, 'facebook.com') !== false || strpos($lowerData, 'fb.com') !== false) {
                                        echo '<a class="link-source link-fb" target="_blank" href="' . e($sourceData) . '">📘 Facebook</a>';
                                    } else {
                                        $displayUrl = (strlen($sourceData) > 18) ? substr($sourceData, 0, 15) . '...' : $sourceData;
                                        echo '<a class="link-source link-web" target="_blank" href="' . e($hrefUrl) . '">🔗 ' . e($displayUrl) . '</a>';
                                    }
                                    echo '<button type="button" class="btn-copy" onclick="copyText(this, \'' . e($sourceData) . '\')">📋 Copy link</button>';
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
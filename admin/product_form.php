<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();

// ==========================================================================
// API AJAX: TỰ ĐỘNG SINH MÃ SẢN PHẨM DỰA TRÊN DANH MỤC
// ==========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'get_next_code') {
    header('Content-Type: application/json');
    $catId = (int)($_GET['category_id'] ?? 0);
    $newCode = '';
    
    if ($catId > 0) {
        $stmt = db()->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$catId]);
        $catName = $stmt->fetchColumn();
        
        if ($catName) {
            // Hàm chuyển tiếng Việt có dấu thành không dấu
            $unaccent = static function($str) {
                $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
                $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
                $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
                $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
                $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
                $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
                $str = preg_replace("/(đ)/", 'd', $str);
                $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
                $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
                $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
                $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
                $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
                $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
                $str = preg_replace("/(Đ)/", 'D', $str);
                return strtoupper(trim($str));
            };
            
            $cleanName = $unaccent($catName);
            // Bỏ các ký tự đặc biệt, chỉ giữ chữ và số
            $cleanName = preg_replace('/[^A-Z0-9 ]/', '', $cleanName);
            $words = array_values(array_filter(explode(' ', $cleanName)));
            
            $prefix = '';
            if (count($words) === 1) {
                $prefix = substr($words[0], 0, 2); // vd: "ÁO" -> "AO"
            } else {
                foreach ($words as $word) {
                    $prefix .= substr($word, 0, 1); // vd: "ÁO KHOÁC" -> "AK"
                }
            }
            
            if (strlen($prefix) > 4) {
                $prefix = substr($prefix, 0, 4); // Giới hạn tiền tố tối đa 4 ký tự
            }
            if (empty($prefix)) {
                $prefix = 'SP';
            }
            
            // Tìm mã SP cao nhất có cùng prefix hiện tại trong DB
            $stmt = db()->prepare("SELECT product_code FROM products WHERE product_code LIKE ? ORDER BY LENGTH(product_code) DESC, product_code DESC LIMIT 1");
            $stmt->execute([$prefix . '\_%']);
            $latestCode = $stmt->fetchColumn();
            
            if ($latestCode) {
                $parts = explode('_', $latestCode);
                $num = (int)end($parts);
                $newCode = $prefix . '_' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newCode = $prefix . '_001';
            }
        }
    }
    
    echo json_encode(['code' => $newCode]);
    exit;
}
// ==========================================================================

$formatPriceInput = static function ($value): string {
    if ($value === null || $value === '') {
        return '';
    }

    $number = (float)$value;
    if ($number <= 0) {
        return '0';
    }

    return number_format($number, 0, ',', '.');
};

$normalizePriceInput = static function ($value): float {
    $clean = preg_replace('/[^\d]/', '', (string)$value);
    return $clean === '' ? 0 : (float)$clean;
};

$moveImageToFront = static function (array $items, ?string $target): array {
    $items = array_values(array_filter($items, static fn($value) => $value !== null && $value !== ''));

    if ($target === null || $target === '') {
        return $items;
    }

    $index = array_search($target, $items, true);
    if ($index === false) {
        return $items;
    }

    unset($items[$index]);
    array_unshift($items, $target);

    return array_values($items);
};

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$product = [
    'product_name' => '',
    'product_code' => next_product_code_preview(),
    'category_id' => '',
    'product_type_id' => '',
    'style_id' => '',
    'gender' => 'Nam',
    'original_price' => '',
    'sale_price' => '',
    'purchase_price' => '',
    'note' => '',
    'material' => '',
    'size' => '',
    'information' => '',
    'short_description' => '',
    'quantity' => '0',
    'color' => '',
    'import_link' => '',
    'thumbnail' => '',
    'is_active' => 1,
];

$images = [];
$selectedConditions = [];
$errors = [];
$stagedUploadPaths = [];
$stagedUploadPathsJson = '[]';

if ($isEdit) {
    $existing = get_product($id);
    if ($existing) {
        $product = $existing;
        $images = get_product_images($id);
        $selectedConditions = get_product_condition_ids($id);
    } else {
        $errors[] = 'Không tìm thấy sản phẩm cần sửa.';
        $isEdit = false;
        $id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $genderOptions = product_gender_options();
    $postedGender = trim($_POST['gender'] ?? 'Nam');

    $data = [
        'product_name' => trim($_POST['product_name'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'product_type_id' => (int)($_POST['product_type_id'] ?? 0),
        'style_id' => ($_POST['style_id'] ?? '') !== '' ? (int)$_POST['style_id'] : null,
        'gender' => in_array($postedGender, $genderOptions, true) ? $postedGender : 'Nam',
        'original_price' => $normalizePriceInput($_POST['original_price'] ?? 0),
        'sale_price' => trim($_POST['sale_price'] ?? '') !== '' ? $normalizePriceInput($_POST['sale_price'] ?? 0) : null,
        'purchase_price' => trim($_POST['purchase_price'] ?? '') !== '' ? $normalizePriceInput($_POST['purchase_price'] ?? 0) : null,
        'note' => trim($_POST['note'] ?? ''),
        'material' => trim($_POST['material'] ?? ''),
        'size' => trim($_POST['size'] ?? ''),
        'information' => trim($_POST['information'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'quantity' => (int)($_POST['quantity'] ?? 0),
        'color' => trim($_POST['color'] ?? ''),
        'import_link' => trim($_POST['import_link'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    $selectedConditions = array_values(
        array_unique(
            array_filter(
                array_map('intval', $_POST['condition_ids'] ?? []),
                fn($value) => $value > 0
            )
        )
    );

    $removedImageIds = array_values(
        array_unique(
            array_filter(
                array_map('intval', $_POST['remove_image_ids'] ?? []),
                fn($value) => $value > 0
            )
        )
    );

    $primaryImageInput = trim($_POST['primary_image'] ?? '');

    if ($data['product_name'] === '') {
        $errors[] = 'Vui lòng nhập tên sản phẩm.';
    }

    if ($data['category_id'] <= 0) {
        $errors[] = 'Vui lòng chọn danh mục.';
    }

    if ($data['product_type_id'] <= 0) {
        $errors[] = 'Vui lòng chọn loại sản phẩm.';
    }

    if (
        $data['category_id'] > 0 &&
        $data['product_type_id'] > 0 &&
        !product_type_exists_for_category($data['product_type_id'], $data['category_id'])
    ) {
        $errors[] = 'Loại sản phẩm không thuộc danh mục đã chọn.';
    }

    if ($data['original_price'] <= 0) {
        $errors[] = 'Giá gốc phải lớn hơn 0.';
    }

    if ($data['sale_price'] !== null && $data['sale_price'] > 0 && $data['sale_price'] > $data['original_price']) {
        $errors[] = 'Giá sale không được lớn hơn giá gốc.';
    }

    $stagedUploadPaths = normalize_posted_uploaded_paths($_POST['uploaded_gallery_paths'] ?? []);
    $directUploadedImages = handle_multiple_image_uploads($_FILES['gallery_files'] ?? null, [
        'destination' => 'uploads',
        'optimize' => true,
        'max_width' => 1400,
        'jpeg_quality' => 82,
        'webp_quality' => 80,
    ]);

    if (!empty($directUploadedImages)) {
        $stagedUploadPaths = array_values(array_unique(array_merge($stagedUploadPaths, $directUploadedImages)));
    }

    if (!$isEdit && empty($stagedUploadPaths)) {
        $errors[] = 'Khi thêm mới, bạn phải upload ít nhất 1 ảnh sản phẩm.';
    }

    if ($isEdit) {
        $currentExistingIds = array_map(
            'intval',
            array_column(get_product_images($id), 'id')
        );

        $remainingExistingIds = array_values(array_diff($currentExistingIds, $removedImageIds));

        if (empty($remainingExistingIds) && empty($stagedUploadPaths)) {
            $errors[] = 'Bạn phải giữ lại hoặc thêm ít nhất 1 ảnh sản phẩm.';
        }
    }

    if (empty($errors)) {
        $uploadedImages = finalize_temp_uploaded_images($stagedUploadPaths);

        if ($isEdit) {
            $existingImages = get_product_images($id);
            $existingImageMap = [];

            foreach ($existingImages as $imageRow) {
                $existingImageMap[(int)$imageRow['id']] = $imageRow['image_url'];
            }

            if (!empty($removedImageIds)) {
                $placeholders = implode(',', array_fill(0, count($removedImageIds), '?'));
                $params = array_merge([$id], $removedImageIds);
                $stmt = db()->prepare("DELETE FROM product_images WHERE product_id = ? AND id IN ($placeholders)");
                $stmt->execute($params);
            }

            $existingImages = get_product_images($id);
            $existingImageMap = [];

            foreach ($existingImages as $imageRow) {
                $existingImageMap[(int)$imageRow['id']] = $imageRow['image_url'];
            }

            $galleryImages = array_values(array_merge(array_values($existingImageMap), $uploadedImages));

            $primaryTarget = null;

            if (strpos($primaryImageInput, 'existing:') === 0) {
                $primaryId = (int)substr($primaryImageInput, 9);
                $primaryTarget = $existingImageMap[$primaryId] ?? null;
            } elseif (strpos($primaryImageInput, 'new:') === 0) {
                $newIndex = (int)substr($primaryImageInput, 4);
                $primaryTarget = $uploadedImages[$newIndex] ?? null;
            }

            $galleryImages = $moveImageToFront($galleryImages, $primaryTarget);
            $thumbnail = $galleryImages[0] ?? null;

            $stmt = db()->prepare('
                UPDATE products SET 
                    product_name = ?, category_id = ?, product_type_id = ?, style_id = ?, gender = ?,
                    original_price = ?, sale_price = ?, purchase_price = ?, note = ?, material = ?, size = ?, information = ?, short_description = ?,
                    quantity = ?, color = ?, import_link = ?, thumbnail = ?, is_active = ?
                WHERE id = ?
            ');

            $stmt->execute([
                $data['product_name'],
                $data['category_id'],
                $data['product_type_id'],
                $data['style_id'],
                $data['gender'],
                $data['original_price'],
                $data['sale_price'],
                $data['purchase_price'],
                $data['note'],
                $data['material'],
                $data['size'],
                $data['information'],
                $data['short_description'],
                $data['quantity'],
                $data['color'],
                $data['import_link'],
                $thumbnail,
                $data['is_active'],
                $id
            ]);

            replace_product_gallery($id, $galleryImages);
            sync_product_conditions($id, $selectedConditions);
        } else {
            // Lấy mã sản phẩm từ Form (đã sinh ra dựa trên chọn danh mục)
            $productCode = trim($_POST['product_code'] ?? '');
            if (empty($productCode)) {
                $productCode = generate_unique_product_code(); // Backup
            }

            // XỬ LÝ TRÙNG MÃ SẢN PHẨM KHI ĐĂNG TỪ NHIỀU TAB CÙNG LÚC
            $finalProductCode = $productCode;
            $attempt = 0;
            while(true) {
                $stmtCheck = db()->prepare("SELECT id FROM products WHERE product_code = ?");
                $stmtCheck->execute([$finalProductCode]);
                if (!$stmtCheck->fetchColumn()) {
                    break; // Không bị trùng, thoát vòng lặp
                }
                
                // Trùng mã -> tăng số đuôi lên
                $attempt++;
                $parts = explode('_', $productCode);
                $num = (int)end($parts);
                array_pop($parts);
                $prefix = implode('_', $parts);
                
                if (empty($prefix)) {
                    $prefix = $productCode;
                    $num = 0;
                }
                
                $finalProductCode = $prefix . '_' . str_pad($num + $attempt, 3, '0', STR_PAD_LEFT);
            }
            $productCode = $finalProductCode;

            $galleryImages = $uploadedImages;
            $primaryTarget = null;

            if (strpos($primaryImageInput, 'new:') === 0) {
                $newIndex = (int)substr($primaryImageInput, 4);
                $primaryTarget = $uploadedImages[$newIndex] ?? null;
            }

            $galleryImages = $moveImageToFront($galleryImages, $primaryTarget);
            $thumbnail = $galleryImages[0] ?? null;

            $stmt = db()->prepare('
                INSERT INTO products (
                    product_name, product_code, category_id, product_type_id, style_id, gender,
                    original_price, sale_price, purchase_price, note, material, size, information, short_description,
                    quantity, color, import_link, thumbnail, is_active
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $data['product_name'],
                $productCode, 
                $data['category_id'],
                $data['product_type_id'],
                $data['style_id'],
                $data['gender'],
                $data['original_price'],
                $data['sale_price'],
                $data['purchase_price'],
                $data['note'],
                $data['material'],
                $data['size'],
                $data['information'],
                $data['short_description'],
                $data['quantity'],
                $data['color'],
                $data['import_link'],
                $thumbnail,
                $data['is_active'],
            ]);

            $newId = (int)db()->lastInsertId();

            replace_product_gallery($newId, $galleryImages);
            sync_product_conditions($newId, $selectedConditions);
        }

        redirect('/admin/products.php');
    }

    $product = array_merge($product, $data, [
        'product_code' => trim($_POST['product_code'] ?? ($isEdit ? ($product['product_code'] ?? '') : next_product_code_preview())),
        'thumbnail' => $product['thumbnail'] ?? '',
    ]);

    if ($isEdit) {
        $images = get_product_images($id);
    }
}

$pageTitle = $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
$categories = get_categories();
$styles = get_styles();
$productTypes = get_product_types();
$productConditions = get_product_conditions();

$stagedUploadPathsJson = json_encode($stagedUploadPaths, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($stagedUploadPathsJson === false) {
    $stagedUploadPathsJson = '[]';
}

$currentPrimaryImage = trim($_POST['primary_image'] ?? '');

if ($currentPrimaryImage === '' && !empty($images)) {
    $currentPrimaryImage = 'existing:' . (int)$images[0]['id'];
}

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

/* SIDEBAR */
.admin-wrapper { display: flex; min-height: 100vh; width: 100%; }
.admin-sidebar { width: var(--sidebar-width); background: var(--admin-card); border-right: 1px solid var(--admin-border); flex-shrink: 0; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
.sidebar-header { padding: 24px; display: flex; align-items: center; gap: 12px; }
.sidebar-header h2 { font-size: 22px; font-weight: 700; margin: 0; color: var(--admin-primary); letter-spacing: -0.5px; }
.sidebar-menu { list-style: none; padding: 0 16px; margin: 0; flex-grow: 1; }
.sidebar-menu li { margin-bottom: 4px; }
.sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--admin-text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 8px; transition: all 0.2s ease; }
.sidebar-menu a:hover { background-color: #f9fafb; color: var(--admin-text-main); }
.sidebar-menu a.active { background-color: #eef2ff; color: var(--admin-primary); font-weight: 600; }
.sidebar-menu a.text-danger { color: var(--admin-danger); margin-top: auto; }
.sidebar-menu a.text-danger:hover { background-color: var(--admin-danger-bg); }

/* MAIN CONTENT */
.admin-main { flex-grow: 1; padding: 32px; max-width: calc(100% - var(--sidebar-width)); overflow-x: hidden; }

/* CSS FORM */
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--admin-border); gap: 12px; flex-wrap: wrap; }
.admin-header h1 { font-size: 24px; color: var(--admin-text-main); margin: 0; font-weight: 700; }
.admin-header .btn-light { background: #fff; border: 1px solid var(--admin-border); padding: 8px 16px; font-size: 14px; font-weight: 500; border-radius: 8px; color: var(--admin-text-main); text-decoration: none; transition: all 0.2s; }
.admin-header .btn-light:hover { background: #f9fafb; }

.card-box { background: var(--admin-card); border-radius: var(--admin-radius); box-shadow: var(--admin-shadow-sm); border: 1px solid var(--admin-border); padding: 30px; }
.form-grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
.col-full { grid-column: 1 / -1; }
@media (min-width: 992px) { .form-grid { grid-template-columns: 1fr 1fr; } }

.form-group label { display: block; font-size: 14px; font-weight: 600; color: var(--admin-text-main); margin-bottom: 8px; }
.form-group .hint { display: block; font-size: 13px; color: var(--admin-text-muted); margin-top: 6px; font-weight: 400; line-height: 1.5; }
.required-mark { color: var(--admin-danger); margin-left: 2px; }

.form-control { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid var(--admin-border); border-radius: 8px; font-size: 14px; color: var(--admin-text-main); background-color: #fff; font-family: 'Inter', sans-serif; outline: none; transition: all 0.2s ease; min-height: 46px; }
.form-control:focus { border-color: var(--admin-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.form-control[readonly] { background-color: #f9fafb; cursor: not-allowed; color: var(--admin-text-muted); }
textarea.form-control { resize: vertical; min-height: 100px; }

select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; background-size: 16px 16px; padding-right: 40px; -webkit-appearance: none; -moz-appearance: none; appearance: none;
}

.checkbox-list { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px; }
.checkbox-chip { display: inline-flex; align-items: center; gap: 8px; background: #f9fafb; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: 1px solid var(--admin-border); transition: all 0.2s; color: var(--admin-text-main); }
.checkbox-chip input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--admin-primary); cursor: pointer; flex-shrink: 0; margin: 0; }
.checkbox-chip:hover { border-color: #d1d5db; background: #f3f4f6; }
.checkbox-chip:has(input:checked) { background: #eef2ff; border-color: #a5b4fc; color: var(--admin-primary); }

.checkbox-inline { display: inline-flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 600; color: var(--admin-text-main); cursor: pointer; padding: 14px 20px; background: #f9fafb; border: 1px solid var(--admin-border); border-radius: 8px; transition: all 0.2s; }
.checkbox-inline:hover { background: #f3f4f6; }
.checkbox-inline input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--admin-primary); flex-shrink: 0; margin: 0; }
.checkbox-inline:has(input:checked) { background: #eef2ff; border-color: #a5b4fc; }

.upload-container { background: #fff; border: 1px solid var(--admin-border); border-radius: 12px; padding: 24px; box-shadow: var(--admin-shadow-sm); }
.upload-title { font-size: 16px !important; font-weight: 700 !important; margin-bottom: 16px !important; color: var(--admin-text-main) !important; }
.upload-dropzone { position: relative; border: 2px dashed #cbd5e1; border-radius: 12px; background: #f9fafb; padding: 40px 20px; text-align: center; transition: all 0.2s ease; cursor: pointer; }
.upload-dropzone:hover { border-color: var(--admin-primary); background: #eef2ff; }
.upload-dropzone input[type="file"] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
.upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 12px; pointer-events: none; }
.upload-placeholder svg { color: var(--admin-text-muted); width: 40px; height: 40px; }
.upload-placeholder .text-main { font-size: 15px; font-weight: 600; color: var(--admin-text-main); }
.upload-placeholder .text-sub { font-size: 13px; color: var(--admin-text-muted); }
.upload-status { margin-top: 12px; font-size: 14px; font-weight: 500; min-height: 20px; }

.existing-gallery { margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--admin-border); }
.preview-gallery { margin-top: 0; padding-top: 0; border-top: none; }
.existing-gallery h3 { font-size: 16px; font-weight: 600; color: var(--admin-text-main); margin-bottom: 16px; }
.existing-gallery-grid { display: flex; flex-wrap: wrap; gap: 16px; }
.existing-gallery-item { width: 160px; position: relative; border: 1px solid var(--admin-border); border-radius: 10px; overflow: hidden; background: #fff; transition: all 0.2s ease; }
.existing-gallery-item:hover { border-color: #cbd5e1; box-shadow: var(--admin-shadow-sm); }
.existing-gallery-item img { width: 100%; height: 160px; object-fit: cover; display: block; border-bottom: 1px solid var(--admin-border); }
.existing-gallery-meta { padding: 12px; background: #f9fafb; display: flex; flex-direction: column; align-items: stretch; gap: 8px; }

.thumb-badge { background: var(--admin-primary); color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; align-self: flex-start; margin-bottom: 4px; }
.mini-option { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: var(--admin-text-main); font-weight: 500; margin: 0; }
.mini-option input[type="radio"], .mini-option input[type="checkbox"] { accent-color: var(--admin-primary); flex-shrink: 0; width: 16px; height: 16px; margin: 0; }
.mini-option.danger { color: var(--admin-danger); }
.mini-option.danger input[type="checkbox"] { accent-color: var(--admin-danger); }
.preview-file-name { font-size: 12px; color: var(--admin-text-muted); line-height: 1.4; word-break: break-word; text-align: left; margin-bottom: 4px; }
.existing-gallery-item.is-removing { opacity: 0.5; border-color: var(--admin-danger-border); background: var(--admin-danger-bg); }
.existing-gallery-item.is-removing img { filter: grayscale(80%); }

.alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; font-weight: 500; line-height: 1.5; display: flex; align-items: center; gap: 10px; }
.alert.error { background-color: var(--admin-danger-bg); color: #b91c1c; border: 1px solid var(--admin-danger-border); }

.form-actions { display: flex; gap: 16px; margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--admin-border); }
.form-actions .btn-big { padding: 12px 24px; font-size: 15px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; border: none; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; }
.btn-primary { background: var(--admin-primary); color: #fff; }
.btn-primary:hover { background: var(--admin-primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-light { background: #fff; border: 1px solid var(--admin-border); color: var(--admin-text-main); }
.btn-light:hover { background: #f9fafb; border-color: #d1d5db; }

@media (max-width: 768px) { 
    .admin-wrapper { flex-direction: column; }
    .admin-sidebar { width: 100%; height: auto; position: relative; border-right: none; border-bottom: 1px solid var(--admin-border); }
    .sidebar-menu { display: flex; overflow-x: auto; padding: 12px; gap: 8px; }
    .sidebar-menu li { margin: 0; white-space: nowrap; }
    .sidebar-menu a.text-danger { margin-top: 0; }
    .admin-main { max-width: 100%; padding: 16px; }
    .form-actions { flex-direction: column; } 
    .form-actions .btn-big { width: 100%; } 
    .existing-gallery-item { width: 100%; } 
    .existing-gallery-item img { height: 200px; }
}
</style>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--admin-primary)"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
            <h2>Luxury Admin</h2>
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
        <div class="admin-header">
            <h1><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></h1>
            <a class="btn btn-light" href="<?= BASE_URL ?>/admin/products.php">← Hủy & Quay lại</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div><?= e(implode('<br>', $errors)) ?></div>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="card-box" id="productForm">
            <div class="form-grid">

                <div class="form-group col-full">
                    <label for="product_name">Tên sản phẩm <span class="required-mark">*</span></label>
                    <input id="product_name" type="text" name="product_name" class="form-control" value="<?= e($product['product_name']) ?>" required placeholder="VD: Áo Thun Nam Có Cổ">
                </div>

                <div class="form-group">
                    <label>Mã sản phẩm</label>
                    <input type="text" id="product_code" name="product_code" class="form-control" value="<?= e($product['product_code']) ?>" readonly>
                    <span class="hint">Hệ thống sẽ tự động tạo mã SP dựa theo danh mục bạn chọn.</span>
                </div>

                <div class="form-group">
                    <label for="categorySelect">Danh mục <span class="required-mark">*</span></label>
                    <select name="category_id" id="categorySelect" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="productTypeSelect">Loại sản phẩm <span class="required-mark">*</span></label>
                    <select name="product_type_id" id="productTypeSelect" class="form-control" required>
                        <option value="">-- Chọn loại sản phẩm --</option>
                        <?php foreach ($productTypes as $type): ?>
                            <option value="<?= (int)$type['id'] ?>" data-category-id="<?= (int)$type['category_id'] ?>" <?= (int)$product['product_type_id'] === (int)$type['id'] ? 'selected' : '' ?>>
                                <?= e($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="hint">Chỉ hiển thị các loại thuộc danh mục đã chọn.</span>
                </div>

                <div class="form-group">
                    <label for="style_id">Phong cách</label>
                    <select name="style_id" id="style_id" class="form-control">
                        <option value="">-- Chọn phong cách --</option>
                        <?php foreach ($styles as $style): ?>
                            <option value="<?= (int)$style['id'] ?>" <?= (int)$product['style_id'] === (int)$style['id'] ? 'selected' : '' ?>>
                                <?= e($style['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Giới tính <span class="required-mark">*</span></label>
                    <select name="gender" id="gender" class="form-control" required>
                        <?php foreach (product_gender_options() as $gender): ?>
                            <option value="<?= e($gender) ?>" <?= $product['gender'] === $gender ? 'selected' : '' ?>>
                                <?= e($gender) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="original_price">Giá gốc (VNĐ) <span style="font-weight: 400; color: var(--admin-text-muted);">(đây là giá gạch)</span> <span class="required-mark">*</span></label>
                    <input id="original_price" type="text" name="original_price" class="form-control money-input" inputmode="numeric" autocomplete="off" value="<?= e($formatPriceInput($product['original_price'])) ?>" required>
                </div>

                <div class="form-group">
                    <label for="sale_price">Giá khuyến mãi (VNĐ) <span style="font-weight: 400; color: var(--admin-text-muted);">(đây là giá bán)</span></label>
                    <input id="sale_price" type="text" name="sale_price" class="form-control money-input" inputmode="numeric" autocomplete="off" value="<?= e($formatPriceInput($product['sale_price'])) ?>">
                    <span class="hint">Để trống nếu không sale.</span>
                </div>

                <div class="form-group">
                    <label for="purchase_price">Giá nhập từ kho (VNĐ)</label>
                    <input id="purchase_price" type="text" name="purchase_price" class="form-control money-input" inputmode="numeric" autocomplete="off" value="<?= e($formatPriceInput($product['purchase_price'] ?? '')) ?>">
                    <span class="hint">Trường ghi nhớ nội bộ, chỉ dùng trong giao diện quản lý.</span>
                </div>

                <div class="form-group">
                    <label for="note">Ghi chú nội bộ</label>
                    <input id="note" type="text" name="note" class="form-control" value="<?= e($product['note'] ?? '') ?>" placeholder="VD: Hàng dễ bán, form nhỏ hơn bình thường, nhập từ mối A...">
                    <span class="hint">Chỉ dùng để ghi nhớ trong trang quản lý, không hiển thị ra ngoài shop.</span>
                </div>

                <div class="form-group">
                    <label for="material">Chất liệu</label>
                    <input id="material" type="text" name="material" class="form-control" value="<?= e($product['material']) ?>" placeholder="VD: Cotton, Kaki...">
                </div>

                <div class="form-group">
                    <label for="size">Kích thước (Size)</label>
                    <input id="size" type="text" name="size" class="form-control" placeholder="VD: S, M, L, XL" value="<?= e($product['size']) ?>">
                </div>

                <div class="form-group">
                    <label for="color">Màu sắc</label>
                    <input id="color" type="text" name="color" class="form-control" placeholder="VD: Đen, Trắng, Xám" value="<?= e($product['color']) ?>">
                </div>

                <div class="form-group">
                    <label for="quantity">Số lượng trong kho</label>
                    <input id="quantity" type="number" name="quantity" class="form-control" min="0" value="<?= e((string)$product['quantity']) ?>">
                </div>

                <div class="form-group col-full">
                    <label for="import_link">Link Zalo nhập hàng / Nguồn / SĐT</label>
                    <input id="import_link" type="text" name="import_link" class="form-control" value="<?= e($product['import_link']) ?>" placeholder="Nhập link (https://...) hoặc số điện thoại nguồn hàng">
                    <span class="hint">Bạn có thể điền link Zalo, link nguồn hàng hoặc số điện thoại đều được.</span>
                </div>

                <div class="form-group col-full">
                    <label>Tình trạng sản phẩm</label>
                    <div class="checkbox-list">
                        <?php if (empty($productConditions)): ?>
                            <span class="hint">Chưa có tình trạng nào. Hãy thêm tại mục Quản lý tình trạng.</span>
                        <?php else: ?>
                            <?php foreach ($productConditions as $condition): ?>
                                <label class="checkbox-chip">
                                    <input type="checkbox" name="condition_ids[]" value="<?= (int)$condition['id'] ?>" <?= in_array((int)$condition['id'], $selectedConditions, true) ? 'checked' : '' ?>>
                                    <span><?= e($condition['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group col-full">
                    <label for="short_description">Mô tả ngắn</label>
                    <textarea id="short_description" name="short_description" class="form-control" rows="3" placeholder="Đoạn văn ngắn gọn giới thiệu điểm nổi bật của SP..."><?= e($product['short_description']) ?></textarea>
                </div>

                <div class="form-group col-full">
                    <label for="information">Thông tin chi tiết</label>
                    <textarea id="information" name="information" class="form-control" rows="6" placeholder="Mô tả chi tiết về sản phẩm, hướng dẫn bảo quản, nguồn gốc..."><?= e($product['information']) ?></textarea>
                </div>

                <div class="form-group col-full">
                    <label>Trạng thái hiển thị</label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="is_active" value="1" <?= !empty($product['is_active']) ? 'checked' : '' ?>>
                        Hiển thị sản phẩm này trên gian hàng website
                    </label>
                </div>

                <div class="form-group col-full upload-container">
                    <label class="upload-title" for="galleryFiles">Thư viện ảnh sản phẩm <span class="required-mark">*</span></label>

                    <div class="upload-dropzone">
                        <input id="galleryFiles" type="file" name="gallery_files[]" accept="image/png,image/jpeg,image/webp" multiple <?= $isEdit ? '' : 'required' ?>>
                        <div class="upload-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span class="text-main">Nhấn để chọn ảnh hoặc kéo thả vào đây</span>
                            <span class="text-sub">Hệ thống sẽ nén nhẹ và tải nền ngay lập tức. Tối đa 50 ảnh/lần.</span>
                        </div>
                    </div>

                    <input type="hidden" name="uploaded_gallery_paths" id="uploadedGalleryPaths" value='<?= e($stagedUploadPathsJson) ?>'>
                    <div id="uploadStatus" class="upload-status"></div>

                    <div class="existing-gallery preview-gallery" id="newPreviewBlock" style="display:none;">
                        <h3>Ảnh mới vừa tải lên</h3>
                        <div class="existing-gallery-grid" id="newPreviewGrid"></div>
                    </div>
                </div>

                <?php if (!empty($images)): ?>
                    <div class="col-full existing-gallery">
                        <h3>Quản lý ảnh hiện tại</h3>
                        <div class="existing-gallery-grid">
                            <?php foreach ($images as $index => $image): ?>
                                <?php $existingId = (int)$image['id']; ?>
                                <div class="existing-gallery-item" data-existing-id="<?= $existingId ?>">
                                    <img src="<?= e(resolve_media_url($image['image_url'])) ?>" alt="Ảnh SP">

                                    <div class="existing-gallery-meta">
                                        <?php if ($index === 0): ?>
                                            <span class="thumb-badge">Đang là ảnh chính</span>
                                        <?php endif; ?>

                                        <label class="mini-option">
                                            <input type="radio" name="primary_image" value="existing:<?= $existingId ?>" class="primary-radio" data-existing-id="<?= $existingId ?>" <?= $currentPrimaryImage === 'existing:' . $existingId ? 'checked' : '' ?>>
                                            <span>Chọn làm ảnh chính</span>
                                        </label>

                                        <label class="mini-option danger">
                                            <input type="checkbox" name="remove_image_ids[]" value="<?= $existingId ?>" class="remove-image-checkbox" data-existing-id="<?= $existingId ?>">
                                            <span>Xóa ảnh</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="form-actions">
                <button class="btn btn-primary btn-big" type="submit" id="submitBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Lưu thông tin sản phẩm
                </button>

                <a class="btn btn-light btn-big" href="<?= BASE_URL ?>/admin/products.php">Hủy thao tác</a>
            </div>
        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('categorySelect');
    const typeSelect = document.getElementById('productTypeSelect');
    const galleryInput = document.getElementById('galleryFiles');
    const uploadedGalleryPathsInput = document.getElementById('uploadedGalleryPaths');
    const uploadStatus = document.getElementById('uploadStatus');
    const form = document.getElementById('productForm') || document.querySelector('form[enctype="multipart/form-data"]');
    const submitBtn = document.getElementById('submitBtn') || (form ? form.querySelector('button[type="submit"]') : null);
    const newPreviewBlock = document.getElementById('newPreviewBlock');
    const newPreviewGrid = document.getElementById('newPreviewGrid');
    const tempUploadEndpoint = <?= json_encode(BASE_URL . '/admin/upload_temp_images.php') ?>;
    const baseUrl = <?= json_encode(BASE_URL) ?>;
    const isEditMode = <?= $isEdit ? 'true' : 'false' ?>;

    let compressedFilesCache = [];
    let isCompressing = false;
    let isUploading = false;
    let originalSubmitHtml = submitBtn ? submitBtn.innerHTML : '';
    let lastSelectionKey = '';
    let previewObjectUrls = [];
    let stagedUploads = [];

    // TỰ ĐỘNG LẤY MÃ SẢN PHẨM MỚI KHI THAY ĐỔI DANH MỤC
    if (categorySelect && !isEditMode) {
        categorySelect.addEventListener('change', function () {
            const catId = this.value;
            const codeInput = document.getElementById('product_code');
            
            if (!catId) {
                return;
            }

            codeInput.value = 'Đang tự tạo mã...';
            
            fetch(`?action=get_next_code&category_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.code) {
                        codeInput.value = data.code;
                    } else {
                        codeInput.value = '';
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi lấy mã SP:', err);
                    codeInput.value = '';
                });
        });
    }

    function setUploadStatus(message, type = '') {
        if (!uploadStatus) return;
        uploadStatus.textContent = message || '';
        uploadStatus.style.color = type === 'error' ? '#dc2626' : (type === 'success' ? '#10b981' : '#4f46e5');
    }

    function setSubmitBusyState() {
        if (!submitBtn) return;

        if (isCompressing) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang chuẩn bị ảnh...';
            return;
        }

        if (isUploading) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang tải ảnh lên...';
            return;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalSubmitHtml;
    }

    function resolveMediaUrl(value) {
        if (!value) return '';
        if (/^(https?:)?\/\//i.test(value) || value.startsWith('/')) {
            return value;
        }

        const cleanBase = (baseUrl || '').replace(/\/$/, '');
        const cleanValue = String(value).replace(/^\/+/, '');
        return cleanBase ? `${cleanBase}/${cleanValue}` : `/${cleanValue}`;
    }

    function syncHiddenUploadedPaths() {
        if (!uploadedGalleryPathsInput) return;
        uploadedGalleryPathsInput.value = JSON.stringify(stagedUploads.map(item => item.path));
        updateValidationState(); 
    }

    function updateValidationState() {
        if (!isEditMode && galleryInput) {
            if (stagedUploads.length > 0) {
                galleryInput.removeAttribute('required'); 
            } else {
                galleryInput.setAttribute('required', 'required'); 
            }
        }
    }

    function syncTypeOptions() {
        if (!categorySelect || !typeSelect) return;

        const categoryId = categorySelect.value;
        let hasVisibleSelected = false;

        Array.from(typeSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const optionCategoryId = option.dataset.categoryId || '';
            const shouldShow = !!categoryId && optionCategoryId === categoryId;

            option.hidden = !shouldShow;

            if (!shouldShow && option.selected) {
                option.selected = false;
            }

            if (shouldShow && option.selected) {
                hasVisibleSelected = true;
            }
        });

        if (!hasVisibleSelected) {
            typeSelect.value = '';
        }
    }

    function formatMoney(value) {
        const digits = String(value).replace(/\D/g, '');
        if (!digits) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 KB';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let size = bytes;

        while (size >= 1024 && i < units.length - 1) {
            size /= 1024;
            i++;
        }

        return `${size.toFixed(size >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
    }

    function getFilesKey(files) {
        return files.map(file => `${file.name}__${file.size}__${file.lastModified}`).join('||');
    }

    function getSelectedPrimaryValue() {
        const checked = document.querySelector('input[name="primary_image"]:checked:not(:disabled)');
        return checked ? checked.value : '';
    }

    function ensureAnyPrimarySelected() {
        const checked = document.querySelector('input[name="primary_image"]:checked:not(:disabled)');
        if (checked) return;

        const firstAvailable = document.querySelector('input[name="primary_image"]:not(:disabled)');
        if (firstAvailable) {
            firstAvailable.checked = true;
        }
    }

    function syncExistingImageControls() {
        const removeCheckboxes = document.querySelectorAll('.remove-image-checkbox');

        removeCheckboxes.forEach((checkbox) => {
            const id = checkbox.getAttribute('data-existing-id');
            const radio = document.querySelector('.primary-radio[data-existing-id="' + id + '"]');
            const card = checkbox.closest('.existing-gallery-item');

            if (radio) {
                radio.disabled = checkbox.checked;

                if (checkbox.checked && radio.checked) {
                    radio.checked = false;
                }
            }

            if (card) {
                card.classList.toggle('is-removing', checkbox.checked);
            }
        });

        ensureAnyPrimarySelected();
    }

    function clearPreviewUrls() {
        previewObjectUrls.forEach((url) => URL.revokeObjectURL(url));
        previewObjectUrls = [];
    }

    function clearNewPreview() {
        clearPreviewUrls();

        if (newPreviewGrid) {
            newPreviewGrid.innerHTML = '';
        }

        if (newPreviewBlock) {
            newPreviewBlock.style.display = 'none';
        }
    }

    function renderPreviewItems(items, preserveUrls = false) {
        if (!newPreviewBlock || !newPreviewGrid) return;

        if (!preserveUrls) {
            clearPreviewUrls();
        }
        newPreviewGrid.innerHTML = '';

        const displayItems = Array.isArray(items) ? items : [];
        if (!displayItems.length) {
            newPreviewBlock.style.display = 'none';
            ensureAnyPrimarySelected();
            return;
        }

        newPreviewBlock.style.display = 'block';

        const selectedPrimary = getSelectedPrimaryValue();
        let hasChecked = !!document.querySelector('input[name="primary_image"]:checked:not(:disabled)');

        displayItems.forEach((item, index) => {
            const value = `new:${index}`;
            const previewSrc = item.previewSrc || item.url || '';

            const card = document.createElement('div');
            card.className = 'existing-gallery-item';

            const img = document.createElement('img');
            img.src = previewSrc;
            img.alt = item.name || `Ảnh ${index + 1}`;

            const meta = document.createElement('div');
            meta.className = 'existing-gallery-meta';

            const name = document.createElement('div');
            name.className = 'preview-file-name';
            name.textContent = item.label || `${item.name || `Ảnh ${index + 1}`} • ${formatBytes(item.size || 0)}`;

            const primaryLabel = document.createElement('label');
            primaryLabel.className = 'mini-option';

            const primaryRadio = document.createElement('input');
            primaryRadio.type = 'radio';
            primaryRadio.name = 'primary_image';
            primaryRadio.value = value;
            primaryRadio.className = 'primary-radio';

            let shouldCheck = false;
            if (selectedPrimary) {
                shouldCheck = selectedPrimary === value;
            } else if (!hasChecked && index === 0) {
                shouldCheck = true;
            }

            if (shouldCheck) {
                primaryRadio.checked = true;
                hasChecked = true;
            }

            const primaryText = document.createElement('span');
            primaryText.textContent = 'Chọn làm ảnh chính';

            primaryLabel.appendChild(primaryRadio);
            primaryLabel.appendChild(primaryText);

            meta.appendChild(name);
            meta.appendChild(primaryLabel);
            card.appendChild(img);
            card.appendChild(meta);

            newPreviewGrid.appendChild(card);
        });

        ensureAnyPrimarySelected();
    }

    function renderNewPreviewFromFiles(files) {
        clearPreviewUrls();

        const items = Array.from(files || []).map((file) => {
            const objectUrl = URL.createObjectURL(file);
            previewObjectUrls.push(objectUrl);

            return {
                previewSrc: objectUrl,
                name: file.name,
                size: file.size || 0,
                label: `${file.name} • ${formatBytes(file.size || 0)}`
            };
        });

        renderPreviewItems(items, true);
    }

    function renderNewPreviewFromUploads(items) {
        const previewItems = Array.from(items || []).map((item) => ({
            previewSrc: item.url,
            name: item.name || 'Ảnh mới',
            size: item.size || 0,
            label: `${item.name || 'Ảnh mới'}${item.size ? ` • ${formatBytes(item.size)}` : ''}`
        }));

        renderPreviewItems(previewItems);
    }

    function loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();

            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };

            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Không thể đọc ảnh'));
            };

            img.src = url;
        });
    }

    async function compressImage(file, options = {}) {
        const {
            maxWidth = 1280,
            quality = 0.8
        } = options;

        if (!file || !file.type || !file.type.startsWith('image/')) {
            return file;
        }

        if ((file.size || 0) < 350 * 1024) {
            return file;
        }

        let srcWidth = 0;
        let srcHeight = 0;
        let drawSource = null;

        try {
            if ('createImageBitmap' in window) {
                const bitmap = await createImageBitmap(file);
                srcWidth = bitmap.width;
                srcHeight = bitmap.height;
                drawSource = bitmap;
            } else {
                const img = await loadImageFromFile(file);
                srcWidth = img.naturalWidth || img.width;
                srcHeight = img.naturalHeight || img.height;
                drawSource = img;
            }
        } catch (error) {
            console.error(error);
            return file;
        }

        let targetWidth = srcWidth;
        let targetHeight = srcHeight;

        if (srcWidth > maxWidth) {
            targetWidth = maxWidth;
            targetHeight = Math.round((srcHeight / srcWidth) * targetWidth);
        }

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;

        const ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) {
            if (drawSource && typeof drawSource.close === 'function') {
                drawSource.close();
            }
            return file;
        }

        ctx.drawImage(drawSource, 0, 0, targetWidth, targetHeight);

        if (drawSource && typeof drawSource.close === 'function') {
            drawSource.close();
        }

        const outputType = file.type === 'image/png' ? 'image/webp' : 'image/jpeg';
        const blob = await new Promise((resolve) => {
            canvas.toBlob((result) => resolve(result || null), outputType, quality);
        });

        if (!(blob instanceof Blob)) {
            return file;
        }

        if ((blob.size || 0) >= (file.size || 0)) {
            return file;
        }

        const ext = outputType === 'image/webp' ? 'webp' : 'jpg';
        const cleanName = file.name.replace(/\.[^.]+$/, '');

        return new File([blob], `${cleanName}.${ext}`, {
            type: outputType,
            lastModified: Date.now()
        });
    }

    async function compressSelectedFiles(files) {
        const originalTotal = files.reduce((sum, file) => sum + (file.size || 0), 0);
        const compressedFiles = new Array(files.length);
        const concurrency = Math.min(3, Math.max(1, files.length));
        let cursor = 0;

        async function worker() {
            while (cursor < files.length) {
                const index = cursor++;
                setUploadStatus(`Đang chuẩn bị ảnh ${index + 1}/${files.length}...`);
                compressedFiles[index] = await compressImage(files[index], {
                    maxWidth: 1280,
                    quality: 0.8
                });
            }
        }

        await Promise.all(Array.from({ length: concurrency }, worker));
        const compressedTotal = compressedFiles.reduce((sum, file) => sum + (file.size || 0), 0);

        return {
            files: compressedFiles,
            originalTotal,
            compressedTotal
        };
    }

    async function uploadPreparedFiles(files) {
        const formData = new FormData();
        files.forEach((file) => {
            formData.append('gallery_files[]', file, file.name);
        });

        const response = await fetch(tempUploadEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const payload = await response.json().catch(() => null);
        if (!response.ok || !payload || !payload.success) {
            throw new Error(payload && payload.message ? payload.message : 'Không thể tải ảnh lên máy chủ.');
        }

        return Array.isArray(payload.files) ? payload.files : [];
    }

    async function prepareImagesNow(fileList) {
        if (!galleryInput) return;

        const files = Array.from(fileList || []);
        compressedFilesCache = [];

        stagedUploads = [];
        syncHiddenUploadedPaths();
        clearNewPreview();
        setUploadStatus('');

        if (!files.length) {
            return;
        }

        if (files.length > 50) {
            galleryInput.value = '';
            setUploadStatus('Chỉ nên chọn tối đa 50 ảnh mỗi lần.', 'error');
            return;
        }

        const selectionKey = getFilesKey(files);
        lastSelectionKey = selectionKey;
        renderNewPreviewFromFiles(files);

        try {
            isCompressing = true;
            setSubmitBusyState();

            const result = await compressSelectedFiles(files);
            if (selectionKey !== lastSelectionKey) {
                return;
            }

            compressedFilesCache = result.files;
            isCompressing = false;
            isUploading = true;
            setSubmitBusyState();
            setUploadStatus(`Đang tải ${files.length} ảnh lên máy chủ...`);

            const uploaded = await uploadPreparedFiles(result.files);
            if (selectionKey !== lastSelectionKey) {
                return;
            }

            stagedUploads = uploaded.map((item) => ({
                path: item.path,
                url: resolveMediaUrl(item.path),
                name: item.name || (item.path ? item.path.split('/').pop() : 'Ảnh mới'),
                size: item.size || 0
            }));

            syncHiddenUploadedPaths();
            renderNewPreviewFromUploads(stagedUploads);
            galleryInput.value = ''; 
            compressedFilesCache = [];

            setUploadStatus(
                `Đã chuẩn bị ${stagedUploads.length} ảnh: ${formatBytes(result.originalTotal)} → ${formatBytes(result.compressedTotal)}. Khi bấm Lưu sẽ cực nhanh.`,
                'success'
            );
        } catch (error) {
            console.error(error);
            stagedUploads = [];
            syncHiddenUploadedPaths();

            if (window.DataTransfer) {
                const dt = new DataTransfer();
                compressedFilesCache.forEach(file => dt.items.add(file));
                galleryInput.files = dt.files;
                renderNewPreviewFromFiles(Array.from(galleryInput.files || []));
            }

            setUploadStatus('Tải nền không thành công. Bạn vẫn có thể bấm Lưu để upload theo cách cũ.', 'error');
        } finally {
            isCompressing = false;
            isUploading = false;
            setSubmitBusyState();
        }
    }

    // ==========================================================================
    // [CẬP NHẬT] LUÔN LUÔN THÊM 3 SỐ 0 KHI NGƯỜI DÙNG THAY ĐỔI DỮ LIỆU
    // Dùng sự kiện 'change' để tránh lỗi thêm 3 số 0 liên tục khi click ra/vào
    // ==========================================================================
    const moneyInputs = document.querySelectorAll('.money-input');
    moneyInputs.forEach((input) => {
        // Vẫn định dạng dấu chấm như bình thường khi đang gõ
        input.addEventListener('input', function () {
            this.value = formatMoney(this.value);
        });
        
        // Khi thay đổi xong (nhấn Tab hoặc click ra ngoài), luôn luôn tự nhân 1000
        input.addEventListener('change', function () {
            let rawValue = String(this.value).replace(/\D/g, ''); 
            if (rawValue !== '') {
                let num = parseInt(rawValue, 10);
                if (num > 0) {
                    num = num * 1000;
                    this.value = formatMoney(String(num));
                }
            }
        });
        
        input.value = formatMoney(input.value);
    });

    if (categorySelect) {
        categorySelect.addEventListener('change', syncTypeOptions);
        syncTypeOptions();
    }

    document.querySelectorAll('.remove-image-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', syncExistingImageControls);
    });

    syncExistingImageControls();

    if (uploadedGalleryPathsInput) {
        try {
            const initialPaths = JSON.parse(uploadedGalleryPathsInput.value || '[]');
            stagedUploads = Array.isArray(initialPaths)
                ? initialPaths.map((path) => ({
                    path,
                    url: resolveMediaUrl(path),
                    name: String(path).split('/').pop() || 'Ảnh mới',
                    size: 0
                }))
                : [];
        } catch (error) {
            stagedUploads = [];
        }

        syncHiddenUploadedPaths();
        if (stagedUploads.length) {
            renderNewPreviewFromUploads(stagedUploads);
            setUploadStatus('Đã khôi phục ảnh mới đã chọn trước đó.', 'success');
        }
    }

    if (galleryInput) {
        galleryInput.addEventListener('change', async function () {
            await prepareImagesNow(this.files);
        });
    }

    if (form && galleryInput) {
        form.addEventListener('submit', function (e) {
            if (isCompressing || isUploading) {
                e.preventDefault();
                setUploadStatus('Ảnh vẫn đang được xử lý. Đợi xong rồi bấm Lưu.', 'error');
                return;
            }

            const hiddenPaths = (() => {
                try {
                    const parsed = JSON.parse((uploadedGalleryPathsInput && uploadedGalleryPathsInput.value) || '[]');
                    return Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    return [];
                }
            })();

            const files = Array.from(galleryInput.files || []);
            if (!hiddenPaths.length && files.length && window.DataTransfer && compressedFilesCache.length) {
                const dt = new DataTransfer();
                compressedFilesCache.forEach(file => dt.items.add(file));
                galleryInput.files = dt.files;
            }

            if (hiddenPaths.length) {
                galleryInput.disabled = true;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Đang lưu sản phẩm...';
            }
        });
    }
});
</script>
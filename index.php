<?php
require_once __DIR__ . '/includes/functions.php';

function render_product_cards(array $products): string
{
    ob_start();
    foreach ($products as $index => $product):
        $productUrl = BASE_URL . '/product.php?id=' . (int)$product['id'];
        $imageUrl   = e(resolve_media_url($product['thumbnail']));
        $imageAlt   = e($product['product_name']);
        $isPriority = $index < 4;
        ?>
        <article class="product-card-pro">
            <a class="product-image-wrap" href="<?= $productUrl ?>">
                <img
                    src="<?= $imageUrl ?>"
                    alt="<?= $imageAlt ?>"
                    loading="<?= $isPriority ? 'eager' : 'lazy' ?>"
                    fetchpriority="<?= $isPriority ? 'high' : 'auto' ?>"
                    decoding="async"
                    width="600"
                    height="750"
                >
                <span class="product-badge"><?= e($product['category_name'] ?: 'Chưa phân loại') ?></span>
            </a>

            <div class="product-card-content">
                <div class="product-top">
                    <div class="product-category">
                        <?= e($product['category_name'] ?: 'Danh mục') ?>
                        •
                        <?= e($product['product_type_name'] ?: 'Loại đang cập nhật') ?>
                        •
                        <?= e($product['gender'] ?: 'Unisex') ?>
                    </div>

                    <h3 class="product-title">
                        <a href="<?= $productUrl ?>">
                            <?= e($product['product_name']) ?>
                        </a>
                    </h3>

                    <div class="product-code">Mã SP: <?= e($product['product_code']) ?></div>
                </div>

                <div class="product-bottom">
                    <div class="price-stack">
                        <?php if (!empty($product['sale_price'])): ?>
                            <div class="price-old"><?= format_price($product['original_price']) ?></div>
                            <div class="price"><?= format_price($product['sale_price']) ?></div>
                        <?php else: ?>
                            <div class="price"><?= format_price($product['original_price']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <a class="btn btn-light" href="<?= $productUrl ?>">Xem chi tiết</a>
                        <a class="btn btn-zalo" target="_blank" href="<?= e(ZALO_LINK) ?>">Mua qua Zalo</a>
                    </div>
                </div>
            </div>
        </article>
    <?php endforeach;

    if (empty($products)): ?>
        <div class="empty-state">
            <h3>Chưa có sản phẩm nào</h3>
            <p>Hiện chưa có sản phẩm phù hợp với bộ lọc bạn chọn. Vui lòng thử lại.</p>
        </div>
    <?php endif;

    return ob_get_clean();
}

$priceRange = trim($_GET['price_range'] ?? '');

$priceMin = null;
$priceMax = null;

switch ($priceRange) {
    case 'under_200':
        $priceMin = null;
        $priceMax = 200000;
        break;
    case '200_500':
        $priceMin = 200000;
        $priceMax = 500000;
        break;
    case '500_1000':
        $priceMin = 500000;
        $priceMax = 1000000;
        break;
    case 'over_1000':
        $priceMin = 1000000;
        $priceMax = null;
        break;
}

$filters = [
    'category_id' => (isset($_GET['category']) && $_GET['category'] !== '') ? (int)$_GET['category'] : null,
    'type_id'     => (isset($_GET['type']) && $_GET['type'] !== '') ? (int)$_GET['type'] : null,
    'gender'      => (isset($_GET['gender']) && $_GET['gender'] !== '') ? trim($_GET['gender']) : null,
    'price_min'   => $priceMin,
    'price_max'   => $priceMax,
    'q'           => trim($_GET['q'] ?? ''),
];

$products = get_products($filters);
$categories = get_categories();
$productTypes = get_product_types();

$visibleProductTypes = array_filter($productTypes, function ($type) use ($filters) {
    if (empty($filters['category_id'])) {
        return true;
    }
    return (int)$type['category_id'] === (int)$filters['category_id'];
});

$productTypesForJs = array_map(function ($type) {
    return [
        'id' => (int)$type['id'],
        'name' => $type['name'],
        'category_id' => (int)$type['category_id'],
    ];
}, $productTypes);

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'count' => count($products),
        'html'  => render_product_cards($products),
    ]);
    exit;
}

$brandLogoUrl    = resolve_media_url('img/logo.jpg');
$heroBannerUrl   = resolve_media_url('img/logoduongmotmi.jpg');
$tiktokIconUrl   = resolve_media_url('img/tt.png');
$facebookIconUrl = resolve_media_url('img/fb.png');
$instagramIconUrl = resolve_media_url('img/ig.png');
$zaloIconUrl     = resolve_media_url('img/zl.png');
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

// CỜ HIỂN THỊ POPUP: Chỉ hiện khi người dùng KHÔNG sử dụng bất kỳ filter nào (Trang chủ thuần)
$isFiltering = (!empty($filters['category_id']) || !empty($filters['type_id']) || !empty($filters['gender']) || !empty($filters['price_max']) || !empty($filters['price_min']) || !empty($filters['q']));
$showPopup = !$isFiltering;
?>

<style>
/* =========================================================
   CSS RIÊNG CHO TRANG INDEX TỐI ƯU GỌN NHẸ (LUXURY UPDATE)
   ========================================================= */
:root {
    --primary-color: #111;
    --primary-hover: #333;
    --danger-color: #e53935;
    --text-main: #111;
    --text-muted: #666;
    --radius-pill: 50px;
    --radius-md: 12px;
    --z-index-modal: 9999;
}

.intro-open-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--primary-color);
    color: #ffffff;
    border: none;
    padding: 14px 24px;
    border-radius: var(--radius-pill);
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    z-index: 100;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    animation: pulseFloat 2s infinite;
}

.intro-open-btn:hover {
    transform: scale(1.05) translateY(-5px);
    background: var(--primary-hover);
    animation: none;
}

@keyframes pulseFloat {
    0% { transform: translateY(0); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    50% { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(0,0,0,0.3); }
    100% { transform: translateY(0); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
}

body.popup-open {
    overflow: hidden;
}

.store-intro-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;    
    justify-content: center; 
    z-index: var(--z-index-modal);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}

.store-intro-overlay.show {
    opacity: 1;
    visibility: visible;
}

.store-intro-modal {
    position: relative;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    width: 950px;
    max-width: 92%;
    max-height: 85vh; 
    border-radius: 30px;
    overflow-y: auto; 
    transform: scale(0.9) translateY(30px);
    transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.5);
}

.store-intro-overlay.show .store-intro-modal {
    transform: scale(1) translateY(0);
}

.popup-close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.05);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    font-size: 24px;
    font-weight: 300;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s;
    color: var(--text-main);
}

.popup-close-btn:hover {
    background: var(--danger-color);
    color: #fff;
    transform: rotate(90deg);
}

.hero-brand-layout-home {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: transparent;
}

.hero-brand-content {
    padding: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hero-brand-content h1 {
    font-size: 38px;
    line-height: 1.1;
    margin-bottom: 20px;
    font-weight: 900;
    letter-spacing: -1px;
    background: linear-gradient(45deg, #111, #555);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-brand-content p {
    font-size: 17px;
    color: var(--text-muted);
    margin-bottom: 30px;
    line-height: 1.6;
}

.hero-feature-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 40px;
}

.hero-feature-tags span {
    background: rgba(0,0,0,0.04);
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hero-socials-home {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.social-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255,255,255,0.8);
    padding: 15px;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s;
    box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    text-decoration: none;
}

.social-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    border-color: rgba(0,0,0,0.1);
    background: #ffffff;
}

.social-icon img {
    border-radius: 8px;
}

.social-text {
    display: flex;
    flex-direction: column;
}

.social-text strong {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
}

.social-text span {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
}

.hero-brand-banner {
    height: 100%;
    position: relative;
    overflow: hidden;
}

.hero-brand-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0 30px 30px 0;
}

/* Ẩn Filter button Desktop */
.mobile-filter-toggle {
    display: none;
    width: 100%;
    margin-bottom: 15px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(0,0,0,0.05);
    color: var(--text-main);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    font-weight: 700;
    border-radius: var(--radius-md);
    padding: 12px;
}

.mobile-filter-toggle svg {
    transition: transform 0.3s ease;
}

.mobile-filter-toggle.is-open .chevron {
    transform: rotate(180deg);
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* =========================================================
   UI/UX SẢN PHẨM (GRID, CARD, HOVER EFFECTS)
   ========================================================= */
.product-grid-pro {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Desktop: 4 items per row */
    gap: 30px;
    padding: 20px 0;
    transition: opacity 0.3s ease;
}

.product-grid-pro.is-loading {
    opacity: 0.5;
    pointer-events: none;
}

.product-card-pro {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
}

.product-card-pro:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    border-color: rgba(0,0,0,0.1);
}

.product-image-wrap {
    position: relative;
    overflow: hidden;
    aspect-ratio: 4/5;
    display: block;
}

.product-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.product-card-pro:hover .product-image-wrap img {
    transform: scale(1.08);
}

.product-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-main);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    z-index: 2;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-card-content {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
}

.product-top {
    margin-bottom: 20px;
}

.product-category {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 8px;
    font-weight: 500;
}

.product-title {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.4;
    margin: 0 0 8px 0;
}

.product-title a {
    color: var(--text-main);
    text-decoration: none;
    transition: color 0.3s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-title a:hover {
    color: var(--primary-hover);
}

.product-code {
    font-size: 12px;
    color: #999;
    background: #f5f5f5;
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
}

.price-stack {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.price {
    font-size: 20px;
    font-weight: 800;
    color: var(--danger-color);
}

.price-old {
    font-size: 14px;
    text-decoration: line-through;
    color: #aaa;
    font-weight: 500;
}

.card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.card-actions .btn {
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-light {
    background: #f5f5f5;
    color: var(--text-main);
}

.btn-light:hover {
    background: #e0e0e0;
}

.btn-zalo {
    background: #0068ff;
    color: #fff;
}

.btn-zalo:hover {
    background: #0056d6;
    box-shadow: 0 4px 15px rgba(0,104,255,0.3);
}

/* =========================================================
   RESPONSIVE: MOBILE LÊN ĐẾN 4 SẢN PHẨM / HÀNG
   ========================================================= */
@media screen and (max-width: 768px) {
    /* Popup Mobile */
    .store-intro-modal {
        width: 92% !important;
        max-height: 85vh !important;
        padding: 0; 
        border-radius: 24px;
    }

    .hero-brand-layout-home {
        display: flex !important;
        flex-direction: column-reverse; 
        background: transparent;
    }

    .hero-brand-content {
        padding: 25px 20px !important;
    }

    .hero-brand-content h1 {
        font-size: 26px !important;
    }

    .hero-brand-content p {
        font-size: 14px;
        margin-bottom: 20px;
    }

    .hero-feature-tags {
        margin-bottom: 25px;
    }

    .hero-feature-tags span {
        font-size: 11px;
        padding: 6px 10px;
        border-radius: 8px;
    }

    .hero-socials-home {
        grid-template-columns: 1fr; 
        gap: 12px;
    }

    .social-card {
        padding: 12px;
        border-radius: 12px;
    }

    .hero-brand-banner img {
        height: 200px; 
        border-radius: 24px 24px 0 0;
    }
    
    .popup-close-btn {
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        font-size: 20px;
        border: none;
    }

    /* Logic bật tắt Filter Mobile */
    .mobile-filter-toggle {
        display: flex;
        border-radius: 12px;
        font-size: 14px;
    }

    .filter-panel {
        display: none; 
        margin-bottom: 20px;
        padding: 20px;
        border-radius: 20px;
    }

    .filter-panel.show-on-mobile {
        display: block;
        animation: slideDown 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .filter-grid {
        grid-template-columns: 1fr; 
        gap: 15px;
    }
    
    .filter-field label {
        margin-bottom: 6px;
        font-size: 11px;
    }
    
    .filter-actions {
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
    
    .filter-actions .btn {
        width: 100%;
        border-radius: 10px;
    }

    /* Ép 4 SẢN PHẨM 1 HÀNG TRÊN MOBILE */
    .product-grid-pro {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 6px; /* Khoảng cách siêu nhỏ để vừa 4 sản phẩm */
        padding: 10px 0;
    }

    .product-card-pro {
        border-radius: 10px; /* Bo góc nhỏ gọn hơn */
    }

    .product-card-pro:hover {
        transform: translateY(-4px); /* Giảm hiệu ứng bay để tránh lẹm màn hình */
    }

    .product-card-content {
        padding: 8px; /* Giảm padding thẻ tối đa */
    }

    .product-badge {
        font-size: 8px; /* Thu nhỏ chữ badge */
        padding: 3px 6px;
        top: 6px;
        left: 6px;
        border-radius: 12px;
    }

    .product-top {
        margin-bottom: 6px;
    }

    .product-category {
        font-size: 8px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-title {
        font-size: 11px; /* Font size siêu nhỏ gọn */
        line-height: 1.3;
        margin-bottom: 4px;
    }

    .product-code {
        font-size: 8px;
        padding: 2px 4px;
        border-radius: 4px;
    }

    .price-stack {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        margin-bottom: 8px;
    }

    .price {
        font-size: 12px;
    }

    .price-old {
        font-size: 9px;
    }

    .card-actions {
        display: flex;
        flex-direction: column; /* Xếp dọc 2 nút bấm */
        gap: 4px;
    }

    .card-actions .btn {
        font-size: 9px;
        padding: 6px 2px;
        border-radius: 6px;
        white-space: nowrap;
    }
}
</style>

<?php if ($showPopup): ?>
<div class="store-intro-overlay" id="storeIntroPopup" aria-hidden="true">
    <div class="store-intro-modal" role="dialog" aria-modal="true" aria-labelledby="storeIntroTitle">
        <button class="popup-close-btn" type="button" data-close-popup>&times;</button>

        <section class="hero-pro hero-pro-upgraded">
            <div class="hero-brand-layout hero-brand-layout-home">
                <div class="hero-brand-content">
                    <div class="hero-socials hero-socials-home">
                        <a class="social-card tiktok" href="https://www.tiktok.com/@duongmotmi2004?_r=1&_t=ZS-94ljEoOsGHP" target="_blank" rel="noopener noreferrer">
                            <div class="social-icon icon-image">
                                <img src="<?= e($tiktokIconUrl) ?>" alt="TikTok" width="24" height="24" loading="lazy" decoding="async">
                            </div>
                            <div class="social-text">
                                <strong>TikTok</strong>
                                <span>@duongmotmi2004</span>
                            </div>
                        </a>

                        <a class="social-card facebook" href="https://www.facebook.com/share/18LTswFoe7/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer">
                            <div class="social-icon icon-image">
                                <img src="<?= e($facebookIconUrl) ?>" alt="Facebook" width="24" height="24" loading="lazy" decoding="async">
                            </div>
                            <div class="social-text">
                                <strong>Facebook</strong>
                                <span>Liên hệ mua hàng</span>
                            </div>
                        </a>

                        <a class="social-card instagram" href="https://www.instagram.com/giuong_tung/" target="_blank" rel="noopener noreferrer">
                            <div class="social-icon icon-image">
                                <img src="<?= e($instagramIconUrl) ?>" alt="Instagram" width="24" height="24" loading="lazy" decoding="async">
                            </div>
                            <div class="social-text">
                                <strong>Instagram</strong>
                                <span>@giuong_tung</span>
                            </div>
                        </a>

                        <a class="social-card zalo" href="<?= e(ZALO_LINK) ?>" target="_blank" rel="noopener noreferrer">
                            <div class="social-icon icon-image">
                                <img src="<?= e($zaloIconUrl) ?>" alt="Zalo" width="24" height="24" loading="lazy" decoding="async">
                            </div>
                            <div class="social-text">
                                <strong>Zalo</strong>
                                <span>Liên hệ mua hàng</span>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="hero-brand-banner">
                    <img
                        src="<?= e($heroBannerUrl) ?>"
                        alt="Duong Mot Mi SHOP"
                        width="900"
                        height="900"
                        loading="eager"
                        decoding="async"
                    >
                </div>
            </div>
        </section>
    </div>
</div>
<?php endif; ?>

<button class="intro-open-btn" type="button" id="introOpenBtn">Giới thiệu shop</button>

<section class="shop-filter-wrap">
    <div class="category-pills">
        <a class="pill category-filter <?= !$filters['category_id'] ? 'active' : '' ?>"
           href="<?= BASE_URL ?>/index.php"
           data-category="">
            Tất cả
        </a>

        <?php foreach ($categories as $cat): ?>
            <a class="pill category-filter <?= $filters['category_id'] === (int)$cat['id'] ? 'active' : '' ?>"
               href="<?= BASE_URL ?>/index.php?category=<?= (int)$cat['id'] ?>"
               data-category="<?= (int)$cat['id'] ?>">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <button type="button" class="btn btn-outline mobile-filter-toggle" id="mobileFilterToggle">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
        </svg>
        Tùy chỉnh bộ lọc
        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <form id="filterForm" class="filter-panel" method="get" action="<?= BASE_URL ?>/index.php">
        <input type="hidden" name="category" id="categoryInput" value="<?= $filters['category_id'] ?? '' ?>">

        <div class="filter-grid">
            <div class="filter-field filter-field-search">
                <label for="q">Tìm kiếm</label>
                <input
                    id="q"
                    type="text"
                    name="q"
                    value="<?= e($filters['q']) ?>"
                    placeholder="Tên sản phẩm, mã SP, loại..."
                >
            </div>

            <div class="filter-field">
                <label for="type">Loại</label>
                <select name="type" id="type">
                    <option value="">Tất cả loại</option>
                    <?php foreach ($visibleProductTypes as $type): ?>
                        <option value="<?= (int)$type['id'] ?>" <?= $filters['type_id'] === (int)$type['id'] ? 'selected' : '' ?>>
                            <?= e($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="gender">Giới tính</label>
                <select name="gender" id="gender">
                    <option value="">Tất cả</option>
                    <option value="Nam" <?= $filters['gender'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= $filters['gender'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    <option value="Unisex" <?= $filters['gender'] === 'Unisex' ? 'selected' : '' ?>>Unisex</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="price_range">Khoảng giá</label>
                <select name="price_range" id="price_range">
                    <option value="">Tất cả mức giá</option>
                    <option value="under_200" <?= $priceRange === 'under_200' ? 'selected' : '' ?>>Dưới 200K</option>
                    <option value="200_500" <?= $priceRange === '200_500' ? 'selected' : '' ?>>200K - 500K</option>
                    <option value="500_1000" <?= $priceRange === '500_1000' ? 'selected' : '' ?>>500K - 1 Triệu</option>
                    <option value="over_1000" <?= $priceRange === 'over_1000' ? 'selected' : '' ?>>Hơn 1 Triệu</option>
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn" type="submit">Lọc sản phẩm</button>
            <button class="btn btn-light" type="button" id="resetFilter">Xóa bộ lọc</button>
        </div>
    </form>
</section>

<section class="section-head" id="product-list">
    <div class="section-note" id="productCount"><?= count($products) ?> sản phẩm</div>
</section>

<div class="product-grid-pro" id="productGrid">
    <?= render_product_cards($products) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const productGrid = document.getElementById('productGrid');
    const productCount = document.getElementById('productCount');
    const categoryInput = document.getElementById('categoryInput');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const resetFilterBtn = document.getElementById('resetFilter');
    const searchInput = document.getElementById('q');
    const typeSelect = document.getElementById('type');
    const genderSelect = document.getElementById('gender');
    const priceRangeSelect = document.getElementById('price_range');
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    const baseUrl = '<?= BASE_URL ?>/index.php';
    const allProductTypes = <?= json_encode($productTypesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    let typingTimer = null;
    let activeController = null;
    let requestId = 0;
    let lastQuery = '';

    if (mobileFilterToggle) {
        mobileFilterToggle.addEventListener('click', function() {
            filterForm.classList.toggle('show-on-mobile');
            this.classList.toggle('is-open');
        });
    }

    function updateActiveCategory(categoryId) {
        categoryFilters.forEach(link => {
            const value = link.dataset.category || '';
            link.classList.toggle('active', value === (categoryId || ''));
        });
    }

    function renderTypeOptions(categoryId, selectedType = '') {
        const normalizedCategory = categoryId ? String(categoryId) : '';
        const normalizedSelectedType = selectedType ? String(selectedType) : '';

        const filteredTypes = !normalizedCategory
            ? allProductTypes
            : allProductTypes.filter(type => String(type.category_id) === normalizedCategory);

        typeSelect.innerHTML = '<option value="">Tất cả loại</option>';
        let hasSelectedType = false;

        filteredTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = String(type.id);
            option.textContent = type.name;

            if (String(type.id) === normalizedSelectedType) {
                option.selected = true;
                hasSelectedType = true;
            }
            typeSelect.appendChild(option);
        });

        if (normalizedSelectedType && !hasSelectedType) {
            typeSelect.value = '';
        }
    }

    function buildQueryFromForm() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            const normalizedValue = String(value).trim();
            if (normalizedValue !== '') {
                params.set(key, normalizedValue);
            }
        }
        return params;
    }

    function setLoadingState(isLoading) {
        productGrid.classList.toggle('is-loading', isLoading);
        productGrid.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    }

    async function loadProducts(force = false) {
        const params = buildQueryFromForm();
        const queryString = params.toString();

        if (!force && queryString === lastQuery) {
            return;
        }

        const ajaxParams = new URLSearchParams(queryString);
        ajaxParams.set('ajax', '1');

        const browserUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        const currentRequestId = ++requestId;

        setLoadingState(true);

        try {
            const response = await fetch(`${baseUrl}?${ajaxParams.toString()}`, {
                signal: activeController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Lỗi fetch');

            const data = await response.json();

            if (currentRequestId !== requestId) return;

            productGrid.classList.add('is-swapping');

            requestAnimationFrame(() => {
                productGrid.innerHTML = data.html;
                productCount.textContent = `${data.count} sản phẩm`;
                history.replaceState(null, '', browserUrl);
                updateActiveCategory(categoryInput.value);
                lastQuery = queryString;

                requestAnimationFrame(() => {
                    productGrid.classList.remove('is-swapping');
                });
            });
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error(error);
        } finally {
            if (currentRequestId === requestId) {
                setLoadingState(false);
            }
        }
    }

    // LOGIC MỚI: KHI ĐỔI DANH MỤC, RESET TẤT CẢ FILTER KHÁC
    categoryFilters.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            
            // Lấy ID danh mục mới
            const newCategoryId = this.dataset.category || '';
            categoryInput.value = newCategoryId;
            
            // XÓA TRỐNG TOÀN BỘ CÁC TRƯỜNG LỌC KHÁC
            if (searchInput) searchInput.value = '';
            if (genderSelect) genderSelect.value = '';
            if (priceRangeSelect) priceRangeSelect.value = '';
            
            // Render lại options Loại theo danh mục mới và để trống (không chọn loại nào)
            renderTypeOptions(newCategoryId, '');
            
            updateActiveCategory(newCategoryId);
            
            // Load sản phẩm với force=true
            loadProducts(true);
            
            if (window.innerWidth <= 768) {
                document.getElementById('product-list').scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadProducts(true);
        if (window.innerWidth <= 768) {
             filterForm.classList.remove('show-on-mobile');
             mobileFilterToggle.classList.remove('is-open');
        }
    });

    filterForm.querySelectorAll('select').forEach(field => {
        field.addEventListener('change', function () {
            loadProducts(true);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                loadProducts(true);
            }, 300);
        });
    }

    resetFilterBtn.addEventListener('click', function () {
        categoryInput.value = '';
        if (searchInput) searchInput.value = '';
        if (typeSelect) typeSelect.value = '';
        if (genderSelect) genderSelect.value = '';
        if (priceRangeSelect) priceRangeSelect.value = '';

        updateActiveCategory('');
        renderTypeOptions('', '');
        loadProducts(true);
    });

    renderTypeOptions(categoryInput.value, '<?= (int)($filters['type_id'] ?? 0) ?>');
    updateActiveCategory(categoryInput.value);
    lastQuery = buildQueryFromForm().toString();

    // POPUP LOGIC
    const popup = document.getElementById('storeIntroPopup');
    const openPopupBtn = document.getElementById('introOpenBtn');
    const closePopupBtns = document.querySelectorAll('[data-close-popup]');

    function openPopup() {
        if (!popup) return;
        popup.classList.add('show');
        document.body.classList.add('popup-open');
    }

    function closePopup() {
        if (!popup) return;
        popup.classList.remove('show');
        document.body.classList.remove('popup-open');
    }

    if (openPopupBtn) {
        openPopupBtn.addEventListener('click', openPopup);
    }

    closePopupBtns.forEach(btn => {
        btn.addEventListener('click', closePopup);
    });

    if (popup) {
        popup.addEventListener('click', function (e) {
            if (e.target === popup) {
                closePopup();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });

    // Chỉ hiện tự động nếu PHP cờ $showPopup = true
    <?php if ($showPopup): ?>
        const schedulePopup = () => {
            setTimeout(() => {
                if (!document.hidden) {
                    openPopup();
                }
            }, 800);
        };
        if ('requestIdleCallback' in window) {
            requestIdleCallback(schedulePopup, { timeout: 1500 });
        } else {
            window.addEventListener('load', schedulePopup, { once: true });
        }
    <?php endif; ?>
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product($id);

if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm.');
}

$images = get_product_images($id);
$pageTitle = $product['product_name'];
require_once __DIR__ . '/includes/header.php';

$displayPrice = !empty($product['sale_price']) ? $product['sale_price'] : $product['original_price'];
$zaloText = rawurlencode(
    'Tôi muốn mua sản phẩm: ' . $product['product_name'] .
    ' - Mã: ' . $product['product_code'] .
    ' - Giá: ' . format_price($displayPrice)
);

$baseBuyLink = !empty($product['import_link']) ? $product['import_link'] : ZALO_LINK;
$buyLink = $baseBuyLink . (strpos($baseBuyLink, '?') !== false ? '&' : '?') . 'text=' . $zaloText;

$galleryImages = [];
if (!empty($product['thumbnail'])) {
    $galleryImages[] = resolve_media_url($product['thumbnail']);
}

foreach ($images as $img) {
    if (!empty($img['image_url'])) {
        $resolved = resolve_media_url($img['image_url']);
        if (!in_array($resolved, $galleryImages, true)) {
            $galleryImages[] = $resolved;
        }
    }
}

if (empty($galleryImages)) {
    $galleryImages[] = 'https://placehold.co/800x900?text=No+Image';
}
?>

<style>
/* ==========================================================================
   CSS CHO TRANG CHI TIẾT SẢN PHẨM (LUXURY GLASSMORPHISM)
   ========================================================================== */
.breadcrumbs {
    padding: 20px 0;
    font-size: 14px;
    color: var(--text-muted, #8e8e9f);
    margin-bottom: 30px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.breadcrumbs a {
    color: var(--primary-color, #111);
    font-weight: 600;
    transition: 0.2s;
}
.breadcrumbs a:hover {
    color: var(--danger-color, #ff3366);
    text-decoration: none;
}

.detail-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
    margin-bottom: 80px;
}

@media (min-width: 992px) {
    .detail-layout {
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: start;
    }
}

/* Khối Hình Ảnh */
.detail-image-wrap {
    position: relative;
    z-index: 10;
}

@media (min-width: 992px) {
    .detail-image-wrap {
        position: sticky;
        top: 100px;
    }
}

.detail-image-shell {
    background: rgba(255,255,255,0.8);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.8);
    margin-bottom: 20px;
    position: relative;
    cursor: zoom-in;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.detail-main-image {
    width: 100%;
    height: auto;
    max-height: 80vh;
    object-fit: contain;
    transition: transform 0.5s ease, opacity 0.2s ease;
    drop-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.detail-image-shell:hover .detail-main-image {
    transform: scale(1.05);
}

.thumb-grid {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 10px;
    scrollbar-width: thin;
}
.thumb-grid::-webkit-scrollbar {
    height: 4px;
}
.thumb-grid::-webkit-scrollbar-thumb {
    background: var(--primary-color, #111);
    border-radius: 4px;
}

.detail-thumb {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.5;
    transition: all 0.3s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.detail-thumb:hover,
.detail-thumb.active {
    opacity: 1;
    border-color: var(--primary-color, #111);
    transform: translateY(-2px);
}

/* Khối Thông Tin */
.detail-box-pro {
    display: flex;
    flex-direction: column;
    padding: 30px;
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.6);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
}

.meta-row-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.meta-pill {
    font-size: 13px;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 8px;
    background: rgba(0,0,0,0.05);
    color: var(--text-main, #1a1a24);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.meta-pill-accent {
    background: rgba(255, 51, 102, 0.1);
    color: var(--danger-color, #ff3366);
}

.detail-box-pro h1 {
    font-size: 32px;
    line-height: 1.2;
    margin-bottom: 12px;
    color: var(--text-main, #1a1a24);
    font-weight: 800;
    letter-spacing: -0.5px;
}

.detail-code {
    font-size: 15px;
    color: var(--text-muted, #8e8e9f);
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.detail-code::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: var(--primary-color, #111);
    border-radius: 50%;
}

.detail-price-stack {
    display: flex;
    align-items: baseline;
    gap: 20px;
    margin-bottom: 30px;
    padding: 25px;
    background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.4));
    border-radius: 16px;
    border-left: 5px solid var(--danger-color, #ff3366);
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.detail-price-stack .price-sale {
    font-size: 38px;
    font-weight: 900;
    color: var(--danger-color, #ff3366);
    letter-spacing: -1px;
}

.detail-price-stack .price-original {
    font-size: 20px;
    color: var(--text-muted, #8e8e9f);
    text-decoration: line-through;
    font-weight: 500;
}

.lead-text {
    font-size: 17px;
    line-height: 1.7;
    color: #4b5563;
    margin-bottom: 35px;
}

/* Lưới Thông số */
.spec-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.spec-item {
    background: rgba(255,255,255,0.8);
    padding: 16px 20px;
    border: 1px solid rgba(0,0,0,0.04);
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.spec-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.spec-label {
    font-size: 12px;
    color: var(--text-muted, #8e8e9f);
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.spec-item strong {
    font-size: 16px;
    color: var(--text-main, #1a1a24);
    font-weight: 700;
}

.full-width-spec {
    grid-column: 1 / -1;
}

/* Nút bấm mua hàng (Hiệu ứng Shine) */
.detail-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 10px;
    margin-bottom: 40px;
}

.btn-big {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 16px 28px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.5px;
    border-radius: 14px;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
    text-align: center;
    border: none;
    cursor: pointer;
}

.btn-big::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -60%;
    width: 20%;
    height: 200%;
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(45deg);
    transition: all 0.5s ease;
}

.btn-big:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.btn-big:hover::after {
    left: 120%;
}

.btn-zalo {
    background: linear-gradient(135deg, #0068ff 0%, #0052cc 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(0, 104, 255, 0.3);
}

.btn-zalo:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 104, 255, 0.4);
}

.btn-light {
    background-color: rgba(255, 255, 255, 0.9);
    color: var(--primary-color, #111);
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
}

.btn-light:hover {
    background-color: #ffffff;
    border-color: var(--primary-color, #111);
}

/* Chi tiết mô tả */
.description-box {
    border-top: 1px solid rgba(0,0,0,0.1);
    padding-top: 40px;
}

.description-box h3 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 25px;
    color: var(--text-main);
    display: inline-block;
    position: relative;
}

.description-box h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -8px;
    width: 40%;
    height: 3px;
    background: var(--primary-color, #111);
    border-radius: 2px;
}

.description-box p {
    font-size: 17px;
    line-height: 1.8;
    color: #4b5563;
}

@media (max-width: 768px) {
    .detail-box-pro { border-radius: 20px; padding: 20px; }
    .detail-box-pro h1 { font-size: 24px; }
    .detail-price-stack { padding: 20px; gap: 12px; border-radius: 12px; }
    .detail-price-stack .price-sale { font-size: 28px; }
    .detail-price-stack .price-original { font-size: 16px; }
    .spec-grid { gap: 12px; }
    .spec-item { padding: 12px; border-radius: 12px; }
}
</style>

<div class="container">
    <div class="breadcrumbs">
        <a href="<?= BASE_URL ?>/index.php">Trang chủ</a> / <?= e($product['product_name']) ?>
    </div>

    <section class="detail-layout">
        <div class="detail-image-wrap">
            <div class="detail-image-shell">
                <div class="detail-slider" id="detailSlider">
                    <img
                        id="mainDetailImage"
                        class="detail-main-image"
                        src="<?= e($galleryImages[0]) ?>"
                        alt="<?= e($product['product_name']) ?>"
                        loading="eager"
                        decoding="async"
                    >
                </div>
            </div>

            <?php if (count($galleryImages) > 1): ?>
                <div class="thumb-grid" id="thumbGrid">
                    <?php foreach ($galleryImages as $index => $imgUrl): ?>
                        <img
                            class="detail-thumb <?= $index === 0 ? 'active' : '' ?>"
                            src="<?= e($imgUrl) ?>"
                            data-index="<?= $index ?>"
                            alt="Ảnh phụ <?= e($product['product_name']) ?>"
                            loading="lazy"
                        >
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-box detail-box-pro">
            <div class="meta-row-wrap">
                <span class="meta-pill">Danh mục: <?= e($product['category_name'] ?: 'Chưa phân loại') ?></span>
                <?php if (!empty($product['product_type_name'])): ?>
                    <span class="meta-pill meta-pill-light">Loại: <?= e($product['product_type_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($product['condition_names'])): ?>
                    <?php foreach (explode(', ', $product['condition_names']) as $conditionName): ?>
                        <span class="meta-pill meta-pill-accent"><?= e($conditionName) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h1><?= e($product['product_name']) ?></h1>
            <div class="detail-code">Mã SP: <?= e($product['product_code']) ?></div>

            <div class="detail-price-stack">
                <?php if (!empty($product['sale_price'])): ?>
                    <div class="price-sale"><?= format_price($product['sale_price']) ?></div>
                    <div class="price-original"><?= format_price($product['original_price']) ?></div>
                <?php else: ?>
                    <div class="price-sale"><?= format_price($product['original_price']) ?></div>
                <?php endif; ?>
            </div>

            <p class="lead-text">
                <?= nl2br(e($product['short_description'] ?: 'Chưa có mô tả ngắn.')) ?>
            </p>

            <div class="spec-grid">
                <div class="spec-item">
                    <span class="spec-label">Giới tính</span>
                    <strong><?= e($product['gender'] ?: 'Đang cập nhật') ?></strong>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Chất liệu</span>
                    <strong><?= e($product['material'] ?: 'Đang cập nhật') ?></strong>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Size</span>
                    <strong><?= e($product['size'] ?: 'Đang cập nhật') ?></strong>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Phong cách</span>
                    <strong><?= e($product['style_name'] ?: 'Đang cập nhật') ?></strong>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Màu sắc</span>
                    <strong><?= e($product['color'] ?: 'Đang cập nhật') ?></strong>
                </div>
            </div>

            <div class="detail-actions">
                <a class="btn-big btn-zalo" target="_blank" href="<?= $buyLink ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    Mua ngay qua Zalo
                </a>
                <a class="btn-light btn-big" href="<?= BASE_URL ?>/index.php">Quay lại gian hàng</a>
            </div>

            <div class="description-box">
                <h3>Mô tả chi tiết</h3>
                <p><?= nl2br(e($product['information'] ?: 'Chưa có thông tin chi tiết.')) ?></p>
            </div>
        </div>
    </section>
</div>

<?php if (count($galleryImages) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const images = <?= json_encode(array_values($galleryImages), JSON_UNESCAPED_SLASHES) ?>;
    const mainImage = document.getElementById('mainDetailImage');
    const thumbs = document.querySelectorAll('.detail-thumb');
    const imageShell = document.querySelector('.detail-image-shell');

    let currentIndex = 0;
    let sliderInterval = null;
    const intervalTime = 4000;

    function showImage(index) {
        currentIndex = index;
        
        mainImage.style.opacity = '0.5';
        setTimeout(() => {
            mainImage.src = images[index];
            mainImage.style.opacity = '1';
        }, 200);

        thumbs.forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
            if(i === index) {
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        });
    }

    function startAutoSlide() {
        if (!sliderInterval) {
            sliderInterval = setInterval(() => {
                const nextIndex = (currentIndex + 1) % images.length;
                showImage(nextIndex);
            }, intervalTime);
        }
    }

    function stopAutoSlide() {
        clearInterval(sliderInterval);
        sliderInterval = null;
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    thumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', function () {
            if(currentIndex !== index) {
                showImage(index);
                resetAutoSlide();
            }
        });
    });

    imageShell.addEventListener('mouseenter', stopAutoSlide);
    imageShell.addEventListener('mouseleave', startAutoSlide);
    imageShell.addEventListener('touchstart', stopAutoSlide, {passive: true});
    imageShell.addEventListener('touchend', startAutoSlide, {passive: true});

    startAutoSlide();
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
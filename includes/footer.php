</main>

<style>
/* ==========================================================================
   CSS CHO FOOTER LUXURY GLASSMORPHISM
   ========================================================================== */
.site-footer {
    position: relative;
    background: rgba(255, 255, 255, 0.5); /* Nền kính mờ */
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-top: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.03);
    padding: 70px 0 20px;
    margin-top: 80px;
    color: var(--text-main, #1a1a24);
    overflow: hidden;
}

/* Hiệu ứng ánh sáng tinh tế chạy ngang viền trên của footer */
.site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 1), transparent);
    animation: footerShine 6s infinite linear;
}

@keyframes footerShine {
    0% { left: -100%; }
    20% { left: 200%; }
    100% { left: 200%; }
}

.footer-top {
    display: grid;
    /* Tự động chia cột: Trên PC 4 cột, trên Tablet 2 cột, trên Mobile 1 cột */
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 40px;
    margin-bottom: 50px;
    position: relative;
    z-index: 2;
}

.footer-col h4 {
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 24px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--primary-color, #111);
    position: relative;
    display: inline-block;
}

.footer-col h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -6px;
    width: 20px;
    height: 3px;
    background: var(--danger-color, #ff3366);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.footer-col:hover h4::after {
    width: 100%;
}

.footer-col p {
    color: var(--text-muted, #4b5563);
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 20px;
}

.footer-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-col ul li {
    margin-bottom: 14px;
}

.footer-col ul li a {
    color: var(--text-muted, #6b7280);
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: inline-flex;
    align-items: center;
    position: relative;
}

.footer-col ul li a::before {
    content: '→';
    position: absolute;
    left: -15px;
    opacity: 0;
    color: var(--danger-color, #ff3366);
    transition: all 0.3s ease;
}

.footer-col ul li a:hover {
    color: var(--primary-color, #111);
    transform: translateX(15px);
}

.footer-col ul li a:hover::before {
    opacity: 1;
    left: -20px;
}

/* Các icon mạng xã hội Luxury */
.footer-socials {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.footer-socials a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px; /* Bo góc hiện đại thay vì tròn xoe */
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0,0,0,0.05);
    color: var(--text-main);
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.footer-socials a:hover {
    background: var(--primary-color, #111);
    color: #fff;
    transform: translateY(-6px) scale(1.05);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    border-color: var(--primary-color, #111);
}

.footer-socials a svg {
    transition: transform 0.3s ease;
}

.footer-socials a:hover svg {
    transform: scale(1.1);
}

.footer-bottom {
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    padding-top: 30px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-muted, #8e8e9f);
    position: relative;
    z-index: 2;
}

/* Tối ưu khoảng cách trên Mobile */
@media (max-width: 768px) {
    .site-footer {
        padding: 50px 0 20px;
        margin-top: 40px;
        /* Phải cộng thêm khoảng trống cho Tab Bar Mobile (nếu có) để nội dung footer không bị che mất */
        padding-bottom: calc(20px + env(safe-area-inset-bottom) + 60px); 
        border-radius: 30px 30px 0 0; /* Bo cong nhẹ góc trên ở mobile */
    }
    .footer-top { gap: 35px; }
    
    .footer-col h4 {
        margin-bottom: 15px;
    }
    
    .footer-socials a {
        width: 40px;
        height: 40px;
    }
}
</style>

<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            
            <div class="footer-col">
                <h4>Duong Mot Mi SHOP</h4>
                <p>Khám phá phong cách thời trang trẻ trung, hiện đại. Chúng tôi cam kết mang đến cho bạn những sản phẩm chất lượng với dịch vụ chốt đơn nhanh chóng, tận tâm.</p>
                <div class="footer-socials">
                    <a href="https://www.facebook.com/share/18LTswFoe7/?mibextid=wwXIfr" target="_blank" title="Facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="https://www.instagram.com/giuong_tung/" target="_blank" title="Instagram">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="<?= e(ZALO_LINK ?? '#') ?>" target="_blank" title="Zalo">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Thông tin liên hệ</h4>
                <ul style="color: var(--text-muted); font-size: 15px;">
                    <li style="margin-bottom: 14px;"><strong style="color: var(--text-main);">Hotline/Zalo:</strong> 0961.691.107</li>
                    <li style="margin-bottom: 14px;"><strong style="color: var(--text-main);">Giờ làm việc:</strong> 08:00 - 22:00 (T2 - CN)</li>
                    <li><strong style="color: var(--text-main);">Địa chỉ:</strong> Sớm cập nhật</li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Duong Mot Mi SHOP. Tự hào thiết kế và vận hành.</p>
        </div>
    </div>
</footer>

<script>
// Thêm sự kiện cho nút "Giới thiệu shop" ở Footer để nó mở cái Popup Intro mà bạn đang dùng ở trang chủ
document.addEventListener('DOMContentLoaded', function() {
    const footerIntroBtn = document.getElementById('footerIntroBtn');
    const storeIntroPopup = document.getElementById('storeIntroPopup');
    
    if(footerIntroBtn && storeIntroPopup) {
        footerIntroBtn.addEventListener('click', function(e) {
            e.preventDefault();
            storeIntroPopup.classList.add('show');
            document.body.classList.add('popup-open');
        });
    }
});
</script>

</body>
</html>
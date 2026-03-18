<?php
require_once __DIR__ . '/functions.php';

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isHome = (
    strpos($currentUri, 'index.php') !== false ||
    $currentUri === '/' ||
    $currentUri === '/shop/'
);
$isAdminPage = strpos($currentUri, '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Shop quần áo Cao cấp'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">

    <style>
        /* ==========================================================================
           LUXURY & MINIMALIST STYLESHEET
           ========================================================================== */
        
        :root {
            --lux-dark: #121212;
            --lux-light: #ffffff;
            --lux-gold: #cda55d;
            --lux-gold-hover: #e3be75;
            --lux-muted: #888888;
            --lux-border: rgba(0, 0, 0, 0.08);
            --transition-smooth: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--lux-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #fafafa;
        }

        /* PREMIUM GLASSMORPHISM HEADER */
        .site-header {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: saturate(180%) blur(25px);
            -webkit-backdrop-filter: saturate(180%) blur(25px);
            box-shadow: 0 4px 40px rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid var(--lux-border);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 18px 0;
            transition: var(--transition-smooth);
        }

        .header-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* LUXURY LOGO */
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 22px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--lux-gold); /* Chuyển sang màu vàng luxury mặc định */
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .logo-mark-img {
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: var(--transition-smooth);
            border: 1px solid var(--lux-border);
        }

        .logo:hover {
            color: var(--lux-gold-hover);
            text-shadow: 0 4px 15px rgba(205, 165, 93, 0.3);
        }

        .logo:hover .logo-mark-img {
            transform: scale(1.03) translateY(-2px);
            box-shadow: 0 8px 20px rgba(205, 165, 93, 0.2);
            border-color: var(--lux-gold);
        }

        /* ELEGANT MENU */
        .site-header .menu {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .site-header .menu a {
            font-size: 13px;
            font-weight: 500;
            color: var(--lux-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            text-decoration: none;
            transition: var(--transition-smooth);
            padding: 5px 0;
        }

        .site-header .menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--lux-gold);
            transition: width 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .site-header .menu a:hover {
            color: var(--lux-dark);
        }

        .site-header .menu a:hover::after {
            width: 100%;
        }

        /* Nút menu mobile */
        .mobile-menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--lux-dark);
            cursor: pointer;
            padding: 10px;
            transition: var(--transition-smooth);
        }
        
        .mobile-menu-toggle:hover {
            color: var(--lux-gold);
        }

        .tab-bar-mobile {
            display: none !important;
        }

        /* FLOATING MUSIC TOGGLE - PREMIUM TACTILE FEEL */
        .floating-music-toggle {
            position: fixed;
            right: 30px;
            bottom: 100px; /* Nâng lề dưới lên 100px để chừa chỗ cho các nút khác */
            z-index: 999;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(205, 165, 93, 0.3);
            border-radius: 50px;
            padding: 14px 24px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--lux-dark);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: var(--transition-smooth);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .floating-music-toggle:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 45px rgba(205, 165, 93, 0.15);
            border-color: var(--lux-gold);
            color: var(--lux-gold);
        }

        .floating-music-toggle:active {
            transform: translateY(1px) scale(0.98);
        }

        .floating-music-toggle svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            transition: var(--transition-smooth);
            stroke-width: 1.5;
        }

        .floating-music-toggle .music-icon-off {
            display: none;
        }

        /* Trạng thái đang phát nhạc - Đảo màu sang trọng */
        .floating-music-toggle.is-playing {
            background: var(--lux-dark);
            color: var(--lux-light);
            border-color: var(--lux-dark);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .floating-music-toggle.is-playing:hover {
            background: #222;
            color: var(--lux-gold);
        }

        .floating-music-toggle.is-playing .music-icon-on {
            display: none;
        }

        /* Animation sóng âm thanh lịch */
        .music-waves {
            display: none;
            align-items: center;
            gap: 4px;
            height: 16px;
        }
        
        .floating-music-toggle.is-playing .music-waves {
            display: flex;
        }
        
        .floating-music-toggle.is-playing .music-icon-off {
            display: none; 
        }

        .wave {
            width: 2px;
            height: 100%;
            background-color: var(--lux-gold);
            border-radius: 2px;
            animation: elegant-bounce 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        .wave:nth-child(1) { animation-delay: 0.0s; }
        .wave:nth-child(2) { animation-delay: -0.3s; }
        .wave:nth-child(3) { animation-delay: -0.6s; }
        .wave:nth-child(4) { animation-delay: -0.9s; }

        @keyframes elegant-bounce {
            0%, 100% { transform: scaleY(0.2); opacity: 0.5; }
            50% { transform: scaleY(1); opacity: 1; }
        }

        .floating-music-toggle .music-toggle-text {
            line-height: 1;
            white-space: nowrap;
        }

        /* MOBILE OPTIMIZATION */
        @media (max-width: 768px) {
            .site-header {
                padding: 12px 20px;
            }

            .header-wrap {
                justify-content: space-between !important;
                position: relative;
            }

            .logo span {
                font-size: 16px;
            }
            
            .logo-mark-img {
                width: 36px;
                height: 36px;
            }

            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .site-header .menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: rgba(255,255,255,0.98);
                backdrop-filter: blur(30px);
                -webkit-backdrop-filter: blur(30px);
                flex-direction: column;
                padding: 10px 0 30px 0;
                box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
                border-top: 1px solid var(--lux-border);
                gap: 0;
            }

            .site-header .menu a {
                padding: 18px 25px;
                display: block;
                width: 100%;
                text-align: center;
                font-size: 14px;
                letter-spacing: 2px;
            }
            
            .site-header .menu a::after {
                display: none;
            }

            .site-header .menu.is-open {
                display: flex;
                animation: fadeSlideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            body {
                padding-bottom: 0 !important;
            }

            .floating-music-toggle {
                right: 20px;
                bottom: 90px; /* Nâng lề dưới lên 90px trên điện thoại */
                padding: 0;
                border-radius: 50%;
                width: 54px;
                height: 54px;
                justify-content: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }

            .floating-music-toggle .music-toggle-text {
                display: none;
            }
        }

        @keyframes fadeSlideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<?php if (!$isAdminPage): ?>
    <audio id="siteBgMusic" preload="auto" autoplay loop playsinline style="display:none;">
        <source src="/abc.mp3" type="audio/mpeg">
    </audio>

    <button
        class="floating-music-toggle"
        type="button"
        data-music-toggle
        aria-pressed="false"
        aria-label="Bật hoặc tắt nhạc nền"
        title="Bật / Tắt nhạc nền"
    >
        <svg class="music-icon-on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
        </svg>

        <svg class="music-icon-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <line x1="23" y1="9" x2="17" y2="15"></line>
            <line x1="17" y1="9" x2="23" y2="15"></line>
        </svg>

        <div class="music-waves" aria-hidden="true">
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
        </div>

        <span class="music-toggle-text">Âm thanh</span>
    </button>
<?php endif; ?>

<header class="site-header">
    <div class="container header-wrap">
        <a class="logo" href="<?= BASE_URL ?>/index.php">
            <img
                class="logo-mark-img"
                src="<?= e(resolve_media_url('img/logo.jpg')) ?>"
                alt="Logo Duong Mot Mi SHOP"
                width="46"
                height="46"
                loading="eager"
                decoding="async"
            >
            <span>Duong Mot Mi</span>
        </a>

        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Mở menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <nav class="menu" id="headerMenu">
            <a href="<?= BASE_URL ?>/index.php">Trang chủ</a>
            <a href="<?= BASE_URL ?>/index.php#product-list">Xem Sản Phẩm</a>
            <a target="_blank" rel="noopener noreferrer" href="<?= e(ZALO_LINK) ?>">Liên hệ</a>
        </nav>
    </div>
</header>

<main class="container main-content">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const headerMenu = document.getElementById('headerMenu');

    if (mobileMenuToggle && headerMenu) {
        mobileMenuToggle.addEventListener('click', function () {
            headerMenu.classList.toggle('is-open');
        });

        document.addEventListener('click', function (event) {
            if (!headerMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                headerMenu.classList.remove('is-open');
            }
        });
    }

    const audio = document.getElementById('siteBgMusic');
    if (!audio) return;

    const KEY_ENABLED = 'shop_bg_music_enabled_v3';
    const KEY_TIME = 'shop_bg_music_time_v3';
    let isAutoplayResolved = false; 

    if (localStorage.getItem(KEY_ENABLED) === null) {
        localStorage.setItem(KEY_ENABLED, '1');
    }

    function isEnabled() {
        return localStorage.getItem(KEY_ENABLED) === '1';
    }

    function setEnabled(value) {
        localStorage.setItem(KEY_ENABLED, value ? '1' : '0');
        updateMusicButtons();
    }

    function saveCurrentTime() {
        try {
            if (Number.isFinite(audio.currentTime)) {
                localStorage.setItem(KEY_TIME, String(audio.currentTime));
            }
        } catch (error) {}
    }

    function restoreCurrentTime() {
        const saved = parseFloat(localStorage.getItem(KEY_TIME) || '0');
        if (Number.isFinite(saved) && saved > 0) {
            try {
                audio.currentTime = saved;
            } catch (error) {}
        }
    }

    function updateMusicButtons() {
        const buttons = document.querySelectorAll('[data-music-toggle]');
        const isPlaying = !audio.paused && !audio.ended;

        buttons.forEach((button) => {
            const textNode = button.querySelector('.music-toggle-text');

            if (textNode) {
                textNode.textContent = isPlaying ? 'Tắt âm' : 'Âm thanh';
            }

            button.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
            button.classList.toggle('is-playing', isPlaying);
        });
    }

    async function tryPlayMusic() {
        if (!isEnabled()) {
            updateMusicButtons();
            return false;
        }

        try {
            audio.volume = 0; 
            await audio.play();
            isAutoplayResolved = true;
            
            // Hiệu ứng Fade-in âm thanh cho mượt mà
            let vol = 0;
            const fadeAudio = setInterval(() => {
                if (vol < 0.8) {
                    vol += 0.05;
                    audio.volume = vol;
                } else {
                    clearInterval(fadeAudio);
                }
            }, 100);

            updateMusicButtons();
            return true;
        } catch (error) {
            updateMusicButtons();
            return false;
        }
    }

    function pauseMusic() {
        // Hiệu ứng Fade-out âm thanh trước khi tắt
        let vol = audio.volume;
        const fadeAudio = setInterval(() => {
            if (vol > 0.05) {
                vol -= 0.05;
                audio.volume = vol;
            } else {
                clearInterval(fadeAudio);
                audio.pause();
                saveCurrentTime();
                updateMusicButtons();
            }
        }, 50);
    }

    function bindMusicButtons() {
        const buttons = document.querySelectorAll('[data-music-toggle]');

        buttons.forEach((button) => {
            if (button.dataset.musicBound === '1') return;
            button.dataset.musicBound = '1';

            button.addEventListener('click', async function () {
                if (!audio.paused) {
                    setEnabled(false);
                    pauseMusic();
                    return;
                }

                setEnabled(true);
                restoreCurrentTime();
                await tryPlayMusic();
            });
        });

        updateMusicButtons();
    }

    async function bootstrapAutoplay() {
        if (!isEnabled()) {
            updateMusicButtons();
            return;
        }
        restoreCurrentTime();
        await tryPlayMusic();
    }

    bindMusicButtons();
    bootstrapAutoplay();

    window.addEventListener('load', bootstrapAutoplay);
    window.addEventListener('pageshow', function () {
        bindMusicButtons();
        bootstrapAutoplay();
    });

    const forcePlayOnInteraction = async function () {
        if (isAutoplayResolved) return; 
        if (isEnabled() && audio.paused) {
            restoreCurrentTime();
            try {
                audio.volume = 0;
                await audio.play();
                isAutoplayResolved = true; 
                
                let vol = 0;
                const fadeAudio = setInterval(() => {
                    if (vol < 0.8) {
                        vol += 0.05;
                        audio.volume = vol;
                    } else {
                        clearInterval(fadeAudio);
                    }
                }, 100);

                updateMusicButtons();
            } catch (err) {}
        }
    };

    const interactionEvents = ['click', 'touchstart', 'keydown', 'scroll', 'pointerdown', 'mousemove'];
    interactionEvents.forEach(evt => {
        document.addEventListener(evt, forcePlayOnInteraction, { capture: true, passive: true });
    });

    audio.addEventListener('play', updateMusicButtons);
    audio.addEventListener('pause', updateMusicButtons);
    audio.addEventListener('timeupdate', saveCurrentTime);

    audio.addEventListener('ended', function () {
        localStorage.setItem(KEY_TIME, '0');
        updateMusicButtons();
    });

    window.addEventListener('beforeunload', saveCurrentTime);
    window.addEventListener('pagehide', saveCurrentTime);
});
</script>
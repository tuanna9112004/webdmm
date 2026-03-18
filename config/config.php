<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'clothing_shop');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('BASE_URL', ''); // đổi theo tên thư mục trong htdocs, ví dụ '' nếu nằm trực tiếp trong htdocs
define('ZALO_LINK', 'https://zalo.me/0961691107'); // đổi thành link Zalo của bạn

date_default_timezone_set('Asia/Ho_Chi_Minh');

<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    db()->prepare('DELETE FROM product_condition_maps WHERE product_id = ?')->execute([$id]);
    db()->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$id]);
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
}

redirect('/admin/products.php');

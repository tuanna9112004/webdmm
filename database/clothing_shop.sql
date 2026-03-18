CREATE DATABASE IF NOT EXISTS clothing_shop
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE clothing_shop;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS product_condition_maps;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS product_conditions;
DROP TABLE IF EXISTS product_types;
DROP TABLE IF EXISTS styles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) DEFAULT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE styles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) DEFAULT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) DEFAULT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_types_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) DEFAULT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    product_code VARCHAR(100) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    product_type_id INT NOT NULL,
    style_id INT DEFAULT NULL,
    gender ENUM('Nam', 'Nữ', 'Unisex') NOT NULL DEFAULT 'Unisex',
    original_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(12,2) DEFAULT NULL,
    purchase_price DECIMAL(12,2) DEFAULT NULL,
    note VARCHAR(500) DEFAULT NULL,
    material VARCHAR(255) DEFAULT NULL,
    size VARCHAR(100) DEFAULT NULL,
    information TEXT DEFAULT NULL,
    short_description VARCHAR(500) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 0,
    color VARCHAR(255) DEFAULT NULL,
    import_link VARCHAR(500) DEFAULT NULL,
    thumbnail VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_products_type FOREIGN KEY (product_type_id) REFERENCES product_types(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_products_style FOREIGN KEY (style_id) REFERENCES styles(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_condition_maps (
    product_id INT NOT NULL,
    condition_id INT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, condition_id),
    CONSTRAINT fk_pcm_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pcm_condition FOREIGN KEY (condition_id) REFERENCES product_conditions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (username, password_hash, full_name) VALUES
('admin', '$2y$12$1AeDhkdQKKeecIf0VFefaOa10Hh9msDIC08u.Ba8W5XrhKwodwtu.', 'Quản trị viên');

INSERT INTO categories (name, slug, sort_order) VALUES
('Áo', 'ao', 1),
('Quần', 'quan', 2),
('Giày', 'giay', 3),
('Túi Sách', 'tui-sach', 4);

INSERT INTO styles (name, slug, sort_order) VALUES
('Basic', 'basic', 1),
('Streetwear', 'streetwear', 2),
('Công sở', 'cong-so', 3),
('Hàn Quốc', 'han-quoc', 4);

INSERT INTO product_types (category_id, name, slug, sort_order) VALUES
(1, 'Áo Khoác', 'ao-khoac', 1),
(1, 'Áo Polo', 'ao-polo', 2),
(1, 'Sơ Mi', 'so-mi', 3),
(1, 'Áo Thun', 'ao-thun', 4),
(2, 'Quần Jean', 'quan-jean', 1),
(2, 'Quần Tây', 'quan-tay', 2),
(2, 'Quần Short', 'quan-short', 3),
(2, 'Chân Váy', 'chan-vay', 4),
(3, 'Giày Sneaker', 'giay-sneaker', 1),
(3, 'Giày Lười', 'giay-luoi', 2),
(3, 'Sandal', 'sandal', 3),
(4, 'Túi Đeo Chéo', 'tui-deo-cheo', 1),
(4, 'Túi Tote', 'tui-tote', 2),
(4, 'Balo', 'balo', 3);

INSERT INTO product_conditions (name, slug, sort_order) VALUES
('Mới về', 'moi-ve', 1),
('Bán chạy', 'ban-chay', 2),
('Sale', 'sale', 3);

INSERT INTO products (
    product_name, product_code, category_id, product_type_id, style_id, gender,
    original_price, sale_price, purchase_price, note, material, size, information, short_description,
    quantity, color, import_link, thumbnail, is_active
) VALUES
('Áo khoác nữ form rộng', 'SP00001', 1, 1, 4, 'Nữ',
 450000, 329000, 240000, 'Form rộng, hàng dễ bán', 'Kaki mềm', 'S,M,L',
 'Áo khoác nữ form rộng phù hợp thời tiết se lạnh, dễ phối cùng quần jean và chân váy.',
 'Áo khoác nữ trẻ trung, dễ phối đồ.',
 25, 'Đen, Be', 'https://zalo.me/your_zalo_id', 'uploads/img_69b9810f84d6d4.07617753.png', 1),
('Quần jean ống suông', 'SP00002', 2, 5, 1, 'Nữ',
 320000, 259000, 175000, 'Mẫu bán ổn, nên giữ đủ size M-L', 'Jean co giãn', 'M,L,XL',
 'Quần jean ống suông thiết kế basic, phù hợp mặc đi học, đi làm và dạo phố.',
 'Quần jean basic, tôn dáng.',
 18, 'Xanh nhạt, Xanh đậm', 'https://zalo.me/your_zalo_id', 'uploads/img_69b983ef812ec6.84328365.png', 1),
('Túi tote dạo phố', 'SP00003', 4, 13, 2, 'Unisex',
 550000, 449000, 290000, 'Có thể upsell combo phụ kiện', 'Canvas', 'One size',
 'Túi tote thời trang gọn nhẹ, phù hợp đi chơi và phối cùng nhiều phong cách thường ngày.',
 'Túi tote cá tính, tiện dụng.',
 12, 'Đen, Xám', 'https://zalo.me/your_zalo_id', 'uploads/img_69b9810f84d6d4.07617753.png', 1);

INSERT INTO product_images (product_id, image_url, sort_order) VALUES
(1, 'uploads/img_69b9810f84d6d4.07617753.png', 1),
(1, 'uploads/img_69b983ef812ec6.84328365.png', 2),
(2, 'uploads/img_69b983ef812ec6.84328365.png', 1),
(2, 'uploads/img_69b9810f84d6d4.07617753.png', 2),
(3, 'uploads/img_69b9810f84d6d4.07617753.png', 1),
(3, 'uploads/img_69b983ef812ec6.84328365.png', 2);

INSERT INTO product_condition_maps (product_id, condition_id, sort_order) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 3, 1),
(3, 2, 1);

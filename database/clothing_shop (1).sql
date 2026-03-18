-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 18, 2026 lúc 04:49 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `clothing_shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$12$1AeDhkdQKKeecIf0VFefaOa10Hh9msDIC08u.Ba8W5XrhKwodwtu.', 'Quản trị viên', '2026-03-18 15:28:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, 'Áo', 'ao', 1, '2026-03-18 15:28:34'),
(2, 'Quần', 'quan', 2, '2026-03-18 15:28:34'),
(3, 'Giày', 'giay', 3, '2026-03-18 15:28:34'),
(4, 'Túi Sách', 'tui-sach', 4, '2026-03-18 15:28:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_type_id` int(11) NOT NULL,
  `style_id` int(11) DEFAULT NULL,
  `gender` enum('Nam','Nữ','Unisex') NOT NULL DEFAULT 'Unisex',
  `original_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `information` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `color` varchar(255) DEFAULT NULL,
  `import_link` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_conditions`
--

CREATE TABLE `product_conditions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_conditions`
--

INSERT INTO `product_conditions` (`id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, 'Mới về', 'moi-ve', 1, '2026-03-18 15:28:34'),
(2, 'Bán chạy', 'ban-chay', 2, '2026-03-18 15:28:34'),
(3, 'Sale', 'sale', 3, '2026-03-18 15:28:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_condition_maps`
--

CREATE TABLE `product_condition_maps` (
  `product_id` int(11) NOT NULL,
  `condition_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_types`
--

CREATE TABLE `product_types` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_types`
--

INSERT INTO `product_types` (`id`, `category_id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, 1, 'Áo Khoác', 'ao-khoac', 1, '2026-03-18 15:28:34'),
(2, 1, 'Áo Polo', 'ao-polo', 2, '2026-03-18 15:28:34'),
(3, 1, 'Sơ Mi', 'so-mi', 3, '2026-03-18 15:28:34'),
(4, 1, 'Áo Thun', 'ao-thun', 4, '2026-03-18 15:28:34'),
(5, 2, 'Quần Jean', 'quan-jean', 1, '2026-03-18 15:28:34'),
(6, 2, 'Quần Tây', 'quan-tay', 2, '2026-03-18 15:28:34'),
(7, 2, 'Quần Short', 'quan-short', 3, '2026-03-18 15:28:34'),
(8, 2, 'Chân Váy', 'chan-vay', 4, '2026-03-18 15:28:34'),
(9, 3, 'Giày Sneaker', 'giay-sneaker', 1, '2026-03-18 15:28:34'),
(10, 3, 'Giày Lười', 'giay-luoi', 2, '2026-03-18 15:28:34'),
(11, 3, 'Sandal', 'sandal', 3, '2026-03-18 15:28:34'),
(12, 4, 'Túi Đeo Chéo', 'tui-deo-cheo', 1, '2026-03-18 15:28:34'),
(13, 4, 'Túi Tote', 'tui-tote', 2, '2026-03-18 15:28:34'),
(14, 4, 'Balo', 'balo', 3, '2026-03-18 15:28:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `styles`
--

CREATE TABLE `styles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `styles`
--

INSERT INTO `styles` (`id`, `name`, `slug`, `sort_order`, `created_at`) VALUES
(1, 'Basic', 'basic', 1, '2026-03-18 15:28:34'),
(2, 'Streetwear', 'streetwear', 2, '2026-03-18 15:28:34'),
(3, 'Công sở', 'cong-so', 3, '2026-03-18 15:28:34'),
(4, 'Hàn Quốc', 'han-quoc', 4, '2026-03-18 15:28:34');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `fk_products_category` (`category_id`),
  ADD KEY `fk_products_type` (`product_type_id`),
  ADD KEY `fk_products_style` (`style_id`);

--
-- Chỉ mục cho bảng `product_conditions`
--
ALTER TABLE `product_conditions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `product_condition_maps`
--
ALTER TABLE `product_condition_maps`
  ADD PRIMARY KEY (`product_id`,`condition_id`),
  ADD KEY `fk_pcm_condition` (`condition_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_images_product` (`product_id`);

--
-- Chỉ mục cho bảng `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_product_types_category` (`category_id`);

--
-- Chỉ mục cho bảng `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `product_conditions`
--
ALTER TABLE `product_conditions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `product_types`
--
ALTER TABLE `product_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `styles`
--
ALTER TABLE `styles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_style` FOREIGN KEY (`style_id`) REFERENCES `styles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_type` FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_condition_maps`
--
ALTER TABLE `product_condition_maps`
  ADD CONSTRAINT `fk_pcm_condition` FOREIGN KEY (`condition_id`) REFERENCES `product_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pcm_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `fk_product_types_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

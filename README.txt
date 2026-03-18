PROJECT: Clothing Shop XAMPP Pro

1. Chép thư mục project vào htdocs của XAMPP.
2. Import lại file database/clothing_shop.sql vào phpMyAdmin (schema đã thay đổi).
3. Sửa config/config.php:
   - DB_HOST, DB_NAME, DB_USER, DB_PASS
   - BASE_URL
   - ZALO_LINK

Ví dụ BASE_URL:
- Nếu project ở C:\xampp\htdocs\clothing-shop-xampp => /clothing-shop-xampp
- Nếu project nằm trực tiếp trong htdocs => để rỗng ''

Tài khoản admin mặc định:
- username: admin
- password: admin123

Các thay đổi đã làm theo yêu cầu:
- Khi thêm sản phẩm, mã sản phẩm tự sinh.
- Bỏ ô nhập Ảnh đại diện bằng URL.
- Ảnh sản phẩm luôn upload từ máy.
- Một sản phẩm có thể upload nhiều ảnh.
- Ảnh đầu tiên được chọn sẽ là ảnh đại diện.
- Có bảng tình trạng sản phẩm riêng và có thể thêm mới trong admin.
- Form sản phẩm cho phép tích chọn nhiều tình trạng.
- Thêm giới tính: Nam / Nữ / Unisex.
- Thêm bảng loại sản phẩm riêng.
- Loại sản phẩm mặc định gồm: Áo, Quần, Giầy, Túi sách.
- Khi nhập sản phẩm bắt buộc phải chọn 1 loại sản phẩm.
- Danh sách sản phẩm admin hiển thị thêm loại, giới tính, tình trạng, trạng thái hiển thị.

Trang quản trị mới:
- /admin/product_types.php
- /admin/product_conditions.php

Database mới nhất gồm:
- admins
- categories
- styles
- product_types (nay là loại con thuộc từng danh mục)
- product_conditions
- products
- product_images
- product_condition_maps


Cập nhật cấu trúc:
- Danh mục chính: Áo, Quần, Giày, Túi Sách.
- Mỗi danh mục có nhiều loại con riêng. Ví dụ: Áo -> Áo Khoác, Áo Polo, Sơ Mi...
- Khi thêm sản phẩm, phải chọn Danh mục trước, sau đó mới chọn Loại thuộc danh mục đó.

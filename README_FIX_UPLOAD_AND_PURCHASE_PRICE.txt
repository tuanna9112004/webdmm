ĐÃ FIX:
1) Tăng tốc upload ảnh ở admin thêm/sửa sản phẩm.
2) Thêm trường purchase_price (giá nhập từ kho) cho products.
3) Hiển thị giá nhập trong admin/products.php.

NGUYÊN NHÂN CHẬM TRƯỚC ĐÓ:
- Ảnh bị nén ở trình duyệt rồi upload tạm.
- Sau đó server lại nén tiếp ảnh tạm một lần nữa.
=> Thành ra bị xử lý lặp, nên thêm ảnh chậm.

ĐÃ SỬA:
- Luồng upload tạm chỉ tối ưu lại khi file vẫn còn quá lớn.
- Giảm mức xử lý lặp ở server cho ảnh đã được nén từ trình duyệt.
- Bổ sung trường giá nhập trong form thêm/sửa và danh sách quản lý.

FILE DB:
- database/clothing_shop.sql              -> full database mới
- database/migration_add_purchase_price.sql -> chỉ thêm cột cho DB cũ đang chạy

NẾU BẠN ĐANG CÓ WEBSITE CHẠY RỒI:
1) Replace toàn bộ source code bằng bản mới.
2) Chạy file: database/migration_add_purchase_price.sql
3) Xong là dùng được ngay.

NẾU BẠN MUỐN IMPORT MỚI HOÀN TOÀN:
- Import file: database/clothing_shop.sql


Cập nhật thêm:
3) Thêm trường note (ghi chú nội bộ) cho products.

File mới:
- database/migration_add_product_note.sql -> chỉ thêm cột ghi chú cho DB cũ đang chạy

Cách nâng cấp nếu đang dùng DB cũ:
3) Chạy tiếp file: database/migration_add_product_note.sql

Trường note:
- dùng để ghi nhớ nội bộ trong trang quản lý
- xuất hiện ở form thêm/sửa sản phẩm
- hiển thị ở danh sách quản lý sản phẩm
- không hiển thị ra giao diện khách hàng

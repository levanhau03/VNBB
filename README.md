# Giới thiệu
VNBB 1.0 là một sản phẩm forum nhẹ.
Dựa trên phiên bản git chính thức đã biến mất, phiên bản này khắc phục các vấn đề tương thích giữa php7.4 và php8.0; sử dụng utf8mb4 để hỗ trợ biểu tượng cảm xúc; jQuery được cập nhật lên 3.5.1 bootstrap được cập nhật lên 4.5.0. Xóa một số plugin và cập nhật chủ đề mặc định. Đã sửa một số lỗi nhỏ.

## Tôi đã làm gì

### Sửa chữa
- Sửa lỗi tương thích php7.4
- Khắc phục sự cố tương thích php8.2
- Sửa lỗi không thể gỡ cài đặt plugin
- Đã khắc phục sự cố không thể mở được trang plugin
### Thay mới
- 💄Cập nhật chủ đề mặc định
- 💥Áp dụng **utf8mb4**, hỗ trợ emoji
- jQuery được cập nhật lên 3.5.1
- 💥Bootstrap được cập nhật lên 4.5.0
- Một số css và js đã được thay đổi thành min để cải thiện tốc độ trang
### Thêm một số plugin
- Đăng nhập/trả lời có thể xem bài viết
- Thành viên mới nhất
- Bộ sưu tập bài mới
- Trình chỉnh sửa TinyMCE
- Phong cách đơn giản
- Hệ thống nhắn tin
- Sắp xếp trả lời

## Screenshot
![image](https://raw.githubusercontent.com/levanhau03/VNBB/f7761a1f2c5ed5868c58b4c71441874177191961/screenshot.png)

## 使用
Vui lòng sử dụng **utf8mb4** (utf8mb4_general_ci) cho cơ sở dữ liệu. Sau khi cài đặt hoàn tất, vui lòng xóa thư mục install.
Các plugin và chủ đề được tải trực tiếp lên thư mục **plugin** và **theme**

### Rewrite
Bật giả tĩnh trong cài đặt nền và thêm quy tắc giả tĩnh tương ứng.

<details>
<summary>Apache giả tĩnh:</summary>

```
<IfModule mod_rewrite.c>
RewriteEngine on

# Apache 2.4
RewriteCond %{REQUEST_FILENAME} !-d 
RewriteCond %{REQUEST_FILENAME} !-f 
RewriteRule ^(.*?)([^/]*)$ $1index.php?$2 [QSA,PT,L]

# Apache other
#RewriteRule ^(.*?)([^/]*)\.htm(.*)$ $1/index.php?$2.htm$3 [L]
</IfModule>
```
</details>

<details>
<summary>Nginx giả tĩnh:</summary>

```
location ~* \.(htm)$ {

    rewrite "^(.*)/(.+?).htm(.*?)$" $1/index.php?$2.htm$3 last;

}
```
</details>


## Tải xuống plugin

Kho lưu trữ plugin tạm thời:[Trung tâm plugin](https://github.com/jiix/plugins)

## Người đóng góp
Người sáng lập: Mr.Hau

## Enjoy!

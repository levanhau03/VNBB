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
- 💄默认主题更新
- 💥采用**utf8mb4**，支持emoji
- jQuery更新到 3.5.1
- 💥bootstrap更新到4.5.0
- 部分css、js改用min版，提高页面速度
- 移除IE hock
- 移除插件中心链接
- UMEditor 百度编辑器更新简约主题
### Thêm một số plugin
- Đăng nhập/trả lời có thể xem bài viết
- Thành viên mới nhất
- Bộ sưu tập bài mới
- Trình chỉnh sửa TinyMCE
- Phong cách đơn giản
- Hệ thống nhắn tin
- Sắp xếp trả lời

## Screenshot
![image](https://raw.githubusercontent.com/jiix/xiunobbs/master/screenshot.png)

## 使用
使用请下载发布版，集成较少插件。数据库请采用**utf8mb4**（utf8mb4_general_ci），安装完成后，请删除install目录。
插件和主题，直接上传到**plugin**目录中，后台插件中心开启。

### Rewrite
后台设置开启伪静态，添加对应的伪静态规则。

<details>
<summary>Apache伪静态:</summary>

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
<summary>Nginx伪静态:</summary>

```
location ~* \.(htm)$ {

    rewrite "^(.*)/(.+?).htm(.*?)$" $1/index.php?$2.htm$3 last;

}
```
</details>

<details>
<summary>Caddy伪静态（Caddyfile演示）：</summary>

```
www.yourdomain.com {

# Set this path to your site's directory.
root * /var/www

file_server

# Or serve a PHP site through php-fpm:
php_fastcgi localhost:9000
}

```
</details>


## 插件下载

临时插件仓库：[插件主题中心](https://github.com/jiix/plugins)

## 下一步

- [x] 增加插件仓库，添加常用插件。
- [x] 对php8进行适配。
- [x] 将部分设置选项（比如开启伪静态设置）集成到后台，方便管理员使用。
- [ ] 整理修复部分插件
- [ ] 添加简约风、acg风格、绿色小清新风格主题。
- [ ] 重启社区计划

## 贡献者
创始人：axiuno

感谢：cnteacher@discuz、Discuz!、Team Artery、剑心@wooyun、右键森林、吴兆焕、杨永全、郑城、大象、燃烧的冰、⭐Star本项目的您。

## Enjoy!

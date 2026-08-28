=== HKSlider ===
Contributors: hungkin
Tags: gallery, slider, image slider, lightbox, zoom, flatsome, ux builder
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tạo slide ảnh gallery kéo thả trực quan dưới dạng Flatsome UX Builder Element, tích hợp hover zoom và lightbox cao cấp.

== Description ==

HKSlider là plugin kéo thả ảnh trực quan cho Flatsome UX Builder. Tạo gallery ảnh sản phẩm chất lượng cao với:

= Tính năng nổi bật: =
* **UX Builder Element**: Kéo thả element HKSlider vào trang, thêm HKImage cho từng slide trực tiếp.
* **Syncing Slider (Slider đồng bộ)**: Kết hợp trượt mượt mà hai chiều giữa ảnh chính và dải thumbnail.
* **Tùy chỉnh Dải Thumbnail**: Chỉnh kích thước ô thumbnail (Width/Height) và khoảng cách (Gap) linh hoạt.
* **Vị trí Thumbnail linh hoạt**: Hiển thị dải thumbnail ở Phía dưới, Bên trái hoặc Bên phải của ảnh chính.
* **Hover Zoom**: Phóng to ảnh khi rê chuột bằng Vanilla JS mượt mà.
* **Lightbox**: Popup xem ảnh toàn màn hình, điều hướng bằng bàn phím.
* **Chọn kích thước ảnh**: Mỗi slide có thể dùng kích thước ảnh riêng (Original, Thumbnail, Medium, Large...).
* **Tối ưu hóa hiệu năng**: Vanilla JS thuần, không phụ thuộc jQuery ở frontend.
* **Responsive**: Layout thumbnail tự chuyển đổi dọc/ngang theo màn hình.

== Installation ==

1. Tải file zip và vào **Plugins -> Add New -> Upload Plugin** trong trang quản trị WordPress.
2. Kích hoạt plugin.
3. Mở trang/bài viết với **UX Builder**, tìm element **HKSlider** trong danh sách và kéo thả vào trang.
4. Thêm các **HKImage** con bên trong HKSlider, chọn ảnh cho từng slide.

== Changelog ==

= 1.0.7 =
* Thêm tùy chọn chỉnh Chiều rộng (Width), Chiều cao (Height) và Khoảng cách (Gap) cho dải Thumbnail trong UX Builder.
* Tối ưu Live Preview trong trình biên tập UX Builder (Tự động re-init slider khi thêm slide hoặc đổi cài đặt).
* Fix lỗi hai nút mũi tên điều hướng (Previous/Next) bị quay cùng chiều.
* Bổ sung các Shortcode Alias: [hkslide], [hkslider], [hkimage], [hk_slide].

= 1.0.6 =
* Đổi tên plugin thành HKSlider, element con thành HKImage, tác giả thành hungkin.

= 1.0.5 =
* Thêm tùy chọn kích thước ảnh (Image Size) per-slide trong UX Builder.

= 1.0.4 =
* Đơn giản hóa plugin: bỏ phần Admin CPT, chỉ giữ UX Builder kéo thả. Cải thiện CSS glassmorphism.

= 1.0.3 =
* Tích hợp Container Element kéo thả trong UX Builder (woogallery_slider + woogallery_slide).

= 1.0.0 =
* Bản phát hành đầu tiên.

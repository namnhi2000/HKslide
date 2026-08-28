<?php
/**
 * Tích hợp phần tử tùy chỉnh (Custom Element) cho Flatsome UX Builder
 * Hỗ trợ kéo thả trực quan dạng Container và Slide Item gốc giống Slider Flatsome (Không cần Admin page)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooGallery_Slider_UX_Builder {

	/**
	 * Khởi tạo hook đăng ký element của UX Builder
	 */
	public function init() {
		add_action( 'ux_builder_setup', array( $this, 'register_ux_builder_elements' ) );
	}

	/**
	 * Đăng ký các element của WooGallery Slider vào Flatsome UX Builder
	 */
	public function register_ux_builder_elements() {
		// Đảm bảo hàm add_ux_builder_shortcode của Flatsome tồn tại
		if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
			return;
		}

		// Icon SVG tùy biến đại diện cho WooGallery Slider (đẹp hơn dashicon thông thường)
		$gallery_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#6c7a89" width="36" height="36" style="margin-bottom:4px;"><path d="M22 16V4c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2zm-11-4l2.03 2.71L16 11l4 5H8l3-4zM2 6v14c0 1.1.9 2 2 2h14v-2H4V6H2z"/></svg>';
		
		$slide_item_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a2b4c6" width="36" height="36" style="margin-bottom:4px;"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z"/></svg>';

		// 1. Đăng ký Element Cha (Container) - [woogallery_slider]
		add_ux_builder_shortcode( 'woogallery_slider', array(
			'name'      => __( 'HKSlider', 'woogallery-slider' ),
			'category'  => __( 'Content', 'woogallery-slider' ),
			'icon'      => $gallery_icon_svg,
			'info'      => __( 'HKSlider - Slide ảnh kiểu WooCommerce (Kéo thả slide con)', 'woogallery-slider' ),
			'type'      => 'container',
			'allow'     => array( 'woogallery_slide' ),
			'options'   => array(
				'thumb_position' => array(
					'type'    => 'select',
					'heading' => __( 'Vị trí Thumbnail', 'woogallery-slider' ),
					'default' => 'bottom',
					'options' => array(
						'bottom' => __( 'Phía dưới ảnh (Bottom)', 'woogallery-slider' ),
						'left'   => __( 'Bên trái ảnh (Left)', 'woogallery-slider' ),
						'right'  => __( 'Bên phải ảnh (Right)', 'woogallery-slider' ),
					),
				),
				'thumb_width' => array(
					'type'    => 'slider',
					'heading' => __( 'Chiều rộng Thumbnail (px)', 'woogallery-slider' ),
					'default' => 80,
					'min'     => 40,
					'max'     => 200,
					'step'    => 5,
				),
				'thumb_height' => array(
					'type'    => 'slider',
					'heading' => __( 'Chiều cao Thumbnail (px)', 'woogallery-slider' ),
					'default' => 80,
					'min'     => 40,
					'max'     => 200,
					'step'    => 5,
				),
				'thumb_gap' => array(
					'type'    => 'slider',
					'heading' => __( 'Khoảng cách Thumbnail (px)', 'woogallery-slider' ),
					'default' => 12,
					'min'     => 0,
					'max'     => 50,
					'step'    => 2,
				),
				'hover_zoom' => array(
					'type'    => 'checkbox',
					'heading' => __( 'Bật Hover Zoom', 'woogallery-slider' ),
					'default' => 'true',
				),
				'lightbox' => array(
					'type'    => 'checkbox',
					'heading' => __( 'Bật Lightbox', 'woogallery-slider' ),
					'default' => 'true',
				),
				'autoplay' => array(
					'type'    => 'checkbox',
					'heading' => __( 'Tự động trượt (Autoplay)', 'woogallery-slider' ),
					'default' => 'false',
				),
				'autoplay_speed' => array(
					'type'    => 'slider',
					'heading' => __( 'Thời gian chờ (ms)', 'woogallery-slider' ),
					'default' => 3000,
					'min'     => 1000,
					'max'     => 10000,
					'step'    => 500,
				),
			),
		) );

		// 2. Đăng ký Element Con (Slide Item) - [woogallery_slide]
		add_ux_builder_shortcode( 'woogallery_slide', array(
			'name'      => __( 'HKImage', 'woogallery-slider' ),
			'category'  => __( 'Content', 'woogallery-slider' ),
			'icon'      => $slide_item_icon_svg,
			'info'      => __( 'Hình ảnh con trong HKSlider', 'woogallery-slider' ),
			'wrap'      => false,
			'options'   => array(
				'id' => array(
					'type'    => 'image',
					'heading' => __( 'Chọn hình ảnh', 'woogallery-slider' ),
					'default' => '',
				),
				'image_size' => array(
					'type'    => 'select',
					'heading' => __( 'Kích thước ảnh hiển thị', 'woogallery-slider' ),
					'default' => 'large',
					'options' => array(
						'full'        => __( 'Original (Gốc)', 'woogallery-slider' ),
						'thumbnail'   => __( 'Thumbnail (150x150)', 'woogallery-slider' ),
						'medium'      => __( 'Medium (300x300)', 'woogallery-slider' ),
						'medium_large'=> __( 'Medium Large (768)', 'woogallery-slider' ),
						'large'       => __( 'Large (1024x1024)', 'woogallery-slider' ),
					),
				),
			),
		) );
	}
}

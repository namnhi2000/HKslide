<?php
/**
 * Lớp điều phối vòng đời chính của plugin (Đơn giản hóa: Bỏ phần Admin)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooGallery_Slider {

	/**
	 * Khởi tạo và load các thành phần phụ thuộc của plugin
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load các file thành viên
	 */
	private function load_dependencies() {
		require_once WOOGALLERY_SLIDER_PATH . 'includes/class-woogallery-slider-shortcode.php';
		require_once WOOGALLERY_SLIDER_PATH . 'includes/class-woogallery-slider-ux-builder.php';
	}

	/**
	 * Đăng ký tất cả các filter và action với WordPress
	 */
	public function run() {
		// Khởi tạo Frontend (Shortcode)
		$shortcode = new WooGallery_Slider_Shortcode();
		add_shortcode( 'woogallery', array( $shortcode, 'render_shortcode_cpt' ) ); // Giữ tương thích ngược shortcode cũ
		add_shortcode( 'woogallery_slider', array( $shortcode, 'render_shortcode_parent' ) );
		add_shortcode( 'woogallery_slide', array( $shortcode, 'render_shortcode_child' ) );
		
		add_action( 'wp_enqueue_scripts', array( $shortcode, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $shortcode, 'enqueue_scripts' ) );

		// Tích hợp UX Builder (Flatsome)
		$ux_builder = new WooGallery_Slider_UX_Builder();
		$ux_builder->init();
	}
}

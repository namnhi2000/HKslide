<?php
/**
 * Plugin Name:       HKSlider
 * Plugin URI:        https://github.com/hungkin/hkslider
 * Description:       Tạo slide ảnh gallery kéo thả trực quan dưới dạng Flatsome UX Builder Element, tích hợp hover zoom và lightbox cao cấp.
 * Version:           1.0.7
 * Author:            hungkin
 * Author URI:        https://github.com/hungkin
 * License:           GPL2
 * Text Domain:       woogallery-slider
 * Domain Path:       /languages
 */

// Ngăn cản truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Khai báo các hằng số của tệp tin
define( 'WOOGALLERY_SLIDER_VERSION', '1.0.7' );
define( 'WOOGALLERY_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOGALLERY_SLIDER_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOGALLERY_SLIDER_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Kích hoạt/Hủy kích hoạt plugin (Không cần flush rewrite rules vì đã bỏ phần Admin CPT)
 */
function activate_woogallery_slider() {}
register_activation_hook( __FILE__, 'activate_woogallery_slider' );

function deactivate_woogallery_slider() {}
register_deactivation_hook( __FILE__, 'deactivate_woogallery_slider' );

// Require lớp khởi tạo chính của plugin
require_once WOOGALLERY_SLIDER_PATH . 'includes/class-woogallery-slider.php';

/**
 * Khởi chạy plugin
 */
function run_woogallery_slider() {
	$plugin = new WooGallery_Slider();
	$plugin->run();
}
run_woogallery_slider();

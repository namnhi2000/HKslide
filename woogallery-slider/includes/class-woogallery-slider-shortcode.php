<?php
/**
 * Xử lý hiển thị Shortcode ngoài Frontend (Đã sửa lỗi logic checkbox & đơn giản hóa)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooGallery_Slider_Shortcode {

	// Bộ nhớ tạm chứa ID ảnh được thu gom khi phân tích shortcode con
	public static $collecting_slides = array();
	public static $is_collecting = false;
	
	// Bộ đếm tạo ID slider duy nhất trên trang
	private static $slider_counter = 0;

	/**
	 * Khởi tạo và đăng ký các shortcodes của plugin
	 */
	public function __construct() {
		// Shortcode cũ (chỉ định ID từ post type) - Giữ để không lỗi nếu người dùng đã dùng
		add_shortcode( 'woogallery', array( $this, 'render_shortcode_cpt' ) );

		// Đăng ký cặp shortcode chính và các Alias (hkslide, hkslider, hkimage...)
		$parent_shortcodes = array( 'woogallery_slider', 'hkslider', 'hkslide', 'hk_slider' );
		foreach ( $parent_shortcodes as $tag ) {
			add_shortcode( $tag, array( $this, 'render_shortcode_parent' ) );
		}

		$child_shortcodes = array( 'woogallery_slide', 'hkimage', 'hk_image', 'hk_slide' );
		foreach ( $child_shortcodes as $tag ) {
			add_shortcode( $tag, array( $this, 'render_shortcode_child' ) );
		}
	}

	/**
	 * Đăng ký Stylesheet ngoài Frontend
	 */
	public function enqueue_styles() {
		// Enqueue thư viện Splide từ file Local
		wp_enqueue_style( 'splide-css', WOOGALLERY_SLIDER_URL . 'public/css/splide.min.css', array(), '4.1.4', 'all' );
		
		// Enqueue file CSS tùy chỉnh của plugin
		wp_enqueue_style( 'woogallery-slider-public', WOOGALLERY_SLIDER_URL . 'public/css/woogallery-slider-public.css', array( 'splide-css' ), WOOGALLERY_SLIDER_VERSION, 'all' );
	}

	/**
	 * Đăng ký Script ngoài Frontend
	 */
	public function enqueue_scripts() {
		// Enqueue thư viện Splide từ file Local
		wp_enqueue_script( 'splide-js', WOOGALLERY_SLIDER_URL . 'public/js/splide.min.js', array(), '4.1.4', true );
		
		// Enqueue file JS tùy chỉnh của plugin
		wp_enqueue_script( 'woogallery-slider-public', WOOGALLERY_SLIDER_URL . 'public/js/woogallery-slider-public.js', array( 'splide-js' ), WOOGALLERY_SLIDER_VERSION, true );
	}

	/**
	 * 1. Render Shortcode cũ [woogallery id="XX"] (Tương thích ngược)
	 */
	public function render_shortcode_cpt( $atts ) {
		$args = shortcode_atts( array(
			'id' => 0,
		), $atts, 'woogallery' );

		$slider_id = intval( $args['id'] );

		if ( ! $slider_id ) {
			return '<!-- WooGallery Slider: Invalid Slider ID -->';
		}

		$image_ids_str = get_post_meta( $slider_id, '_woogallery_image_ids', true );
		if ( empty( $image_ids_str ) ) {
			return '<!-- WooGallery Slider: No images selected -->';
		}

		$image_ids = explode( ',', $image_ids_str );

		// Lấy các cấu hình thiết lập của Slider
		$thumb_position = get_post_meta( $slider_id, '_woogallery_thumb_position', true );
		$thumb_position = ! empty( $thumb_position ) ? $thumb_position : 'bottom';
		
		$hover_zoom_meta = get_post_meta( $slider_id, '_woogallery_hover_zoom', true );
		$hover_zoom = ( 'no' !== $hover_zoom_meta );

		$lightbox_meta = get_post_meta( $slider_id, '_woogallery_lightbox', true );
		$lightbox = ( 'no' !== $lightbox_meta );

		$autoplay_meta = get_post_meta( $slider_id, '_woogallery_autoplay', true );
		$autoplay = ( 'yes' === $autoplay_meta );

		$autoplay_speed = get_post_meta( $slider_id, '_woogallery_autoplay_speed', true );
		$autoplay_speed = ! empty($autoplay_speed) ? intval( $autoplay_speed ) : 3000;

		$options = array(
			'thumbPosition' => $thumb_position,
			'hoverZoom'     => $hover_zoom,
			'lightbox'      => $lightbox,
			'autoplay'      => $autoplay,
			'autoplaySpeed' => $autoplay_speed
		);

		self::$slider_counter++;
		$unique_dom_id = 'woogallery-cpt-' . self::$slider_counter;

		return $this->generate_gallery_html( $unique_dom_id, $image_ids, $options );
	}

	/**
	 * 2. Render Shortcode element cha [woogallery_slider]
	 */
	public function render_shortcode_parent( $atts, $content = null ) {
		$args = shortcode_atts( array(
			'thumb_position' => 'bottom',
			'thumb_width'    => '80',
			'thumb_height'   => '80',
			'thumb_gap'      => '12',
			'hover_zoom'     => 'true',
			'lightbox'       => 'true',
			'autoplay'       => 'false',
			'autoplay_speed' => '3000',
		), $atts, 'woogallery_slider' );

		// Bật cơ chế thu gom dữ liệu slide con
		self::$collecting_slides = array();
		self::$is_collecting = true;

		// Thực thi shortcode con (các slide con sẽ tự thêm ID ảnh vào mảng tĩnh $collecting_slides)
		do_shortcode( $content );

		// Vô hiệu hóa thu gom
		self::$is_collecting = false;

		$image_ids = self::$collecting_slides;
		
		if ( empty( $image_ids ) ) {
			// Hiển thị khung chờ đẹp trong UX Builder editor khi chưa có ảnh
			if ( is_admin() || ( function_exists( 'ux_builder_is_active' ) && ux_builder_is_active() ) || isset( $_GET['ux_builder'] ) ) {
				return '<div style="padding: 40px; border: 2px dashed #cfd8dc; text-align: center; border-radius: 12px; background: #fdfdfd; font-family: sans-serif;"><p style="font-weight: 700; font-size: 15px; color:#5c6bc0; margin: 0 0 5px 0;">HKSlider Container</p><p style="margin: 0; font-size: 13px; color:#78909c;">Nhấp nút "+" bên trong hàng đợi để thêm "HKImage" mới và chọn tải ảnh lên trực tiếp.</p></div>';
			}
			return '<!-- WooGallery Slider: No images inside container -->';
		}

		// Sửa lỗi phán đoán checkbox của Flatsome trả về "" hoặc "false" hoặc "0" khi bỏ tick
		$hover_zoom_val = ( ! empty( $args['hover_zoom'] ) && 'false' !== $args['hover_zoom'] && '0' !== $args['hover_zoom'] );
		$lightbox_val   = ( ! empty( $args['lightbox'] ) && 'false' !== $args['lightbox'] && '0' !== $args['lightbox'] );
		$autoplay_val   = ( ! empty( $args['autoplay'] ) && 'false' !== $args['autoplay'] && '0' !== $args['autoplay'] );

		$options = array(
			'thumbPosition' => sanitize_text_field( $args['thumb_position'] ),
			'thumbWidth'    => intval( $args['thumb_width'] ) ? intval( $args['thumb_width'] ) : 80,
			'thumbHeight'   => intval( $args['thumb_height'] ) ? intval( $args['thumb_height'] ) : 80,
			'thumbGap'      => isset( $args['thumb_gap'] ) ? intval( $args['thumb_gap'] ) : 12,
			'hoverZoom'     => $hover_zoom_val,
			'lightbox'      => $lightbox_val,
			'autoplay'      => $autoplay_val,
			'autoplaySpeed' => intval( $args['autoplay_speed'] ) ? intval( $args['autoplay_speed'] ) : 3000
		);

		self::$slider_counter++;
		$unique_dom_id = 'woogallery-ux-' . self::$slider_counter;

		return $this->generate_gallery_html( $unique_dom_id, $image_ids, $options );
	}

	/**
	 * 3. Xử lý Shortcode con [woogallery_slide id="XX" image_size="large"]
	 */
	public function render_shortcode_child( $atts ) {
		// Chỉ thu gom khi shortcode cha đang chạy
		if ( ! self::$is_collecting ) {
			return '';
		}

		$args = shortcode_atts( array(
			'id'         => '',
			'image_size' => 'large', // Mặc định là large
		), $atts, 'woogallery_slide' );

		$img_id = intval( $args['id'] );

		if ( $img_id ) {
			// Lưu cả id và image_size vào mảng thu gom
			self::$collecting_slides[] = array(
				'id'         => $img_id,
				'image_size' => sanitize_text_field( $args['image_size'] ),
			);
		}

		return ''; // Không trả về HTML gì để tránh vỡ giao diện trong luồng con
	}

	/**
	 * Xây dựng mã HTML hoàn chỉnh cho Gallery Slider
	 * $image_ids có thể là mảng số (ư [int, int]) hoặc mảng object [{id, image_size}]
	 */
	private function generate_gallery_html( $slider_dom_id, $image_ids, $options ) {
		$thumb_position = $options['thumbPosition'];
		$hover_zoom     = $options['hoverZoom'];

		$thumb_w   = ! empty( $options['thumbWidth'] ) ? intval( $options['thumbWidth'] ) : 80;
		$thumb_h   = ! empty( $options['thumbHeight'] ) ? intval( $options['thumbHeight'] ) : 80;
		$thumb_gap = isset( $options['thumbGap'] ) ? intval( $options['thumbGap'] ) : 12;

		$inline_style = sprintf( '--woogallery-thumb-w: %dpx; --woogallery-thumb-h: %dpx; --woogallery-thumb-gap: %dpx;', $thumb_w, $thumb_h, $thumb_gap );
		
		// Encode các option cấu hình sang JSON
		$js_options_str = wp_json_encode( $options );

		ob_start();
		?>
		<div class="woogallery-container woogallery-layout-<?php echo esc_attr( $thumb_position ); ?>" 
			 id="woogallery-container-<?php echo esc_attr( $slider_dom_id ); ?>"
			 style="<?php echo esc_attr( $inline_style ); ?>"
			 data-settings='<?php echo esc_attr( $js_options_str ); ?>'>
			
			<!-- Khu vực Slider chính -->
			<div class="woogallery-main-wrapper">
				<?php if ( $options['lightbox'] ) : ?>
					<button class="woogallery-expand-btn" aria-label="<?php esc_attr_e( 'Xem ảnh phóng to', 'woogallery-slider' ); ?>" title="<?php esc_attr_e( 'Xem ảnh phóng to', 'woogallery-slider' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="15 3 21 3 21 9"></polyline>
							<polyline points="9 21 3 21 3 15"></polyline>
							<line x1="21" y1="3" x2="14" y2="10"></line>
							<line x1="3" y1="21" x2="10" y2="14"></line>
						</svg>
					</button>
				<?php endif; ?>
				<div id="woogallery-main-<?php echo esc_attr( $slider_dom_id ); ?>" class="splide woogallery-main-slider">
					<div class="splide__track">
						<ul class="splide__list">
							<?php foreach ( $image_ids as $slide_data ) :
								// Hỗ trợ cả 2 format: [int] và [{id, image_size}]
								if ( is_array( $slide_data ) ) {
									$img_id     = intval( $slide_data['id'] );
									$img_size   = ! empty( $slide_data['image_size'] ) ? $slide_data['image_size'] : 'large';
								} else {
									$img_id   = intval( $slide_data );
									$img_size = 'large';
								}

								$full_url_src    = wp_get_attachment_image_src( $img_id, 'full' );
								$display_url_src = wp_get_attachment_image_src( $img_id, $img_size );
								if ( ! $display_url_src ) {
									continue;
								}
								$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
								?>
								<li class="splide__slide">
									<div class="woogallery-slide-inner <?php echo $hover_zoom ? 'woogallery-zoomable' : ''; ?>" 
										 data-src="<?php echo esc_url( $full_url_src[0] ); ?>">
										<img src="<?php echo esc_url( $display_url_src[0] ); ?>" 
											 alt="<?php echo esc_attr( $alt ); ?>"
											 class="woogallery-main-img" />
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Khu vực Slider Thumbnail -->
			<div class="woogallery-thumbs-wrapper">
				<div id="woogallery-thumbs-<?php echo esc_attr( $slider_dom_id ); ?>" class="splide woogallery-thumb-slider">
					<div class="splide__track">
						<ul class="splide__list">
							<?php foreach ( $image_ids as $slide_data ) :
								$img_id = is_array( $slide_data ) ? intval( $slide_data['id'] ) : intval( $slide_data );
								$thumb_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
								if ( ! $thumb_src ) {
									continue;
								}
								$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
								?>
								<li class="splide__slide">
									<div class="woogallery-thumb-inner">
										<img src="<?php echo esc_url( $thumb_src[0] ); ?>" 
											 alt="<?php echo esc_attr( $alt ); ?>" />
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}

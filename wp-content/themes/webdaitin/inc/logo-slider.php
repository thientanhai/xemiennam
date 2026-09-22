<?php
/**
 * Module: Logo Slider (Swiper Center Mode + ACF Options Pro)
 * Description: Quản lý và hiển thị nhiều danh sách Logo (Báo chí, Khách hàng, Đối tác...) qua Shortcode.
 * Author: Antigravity AI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * 1. ĐĂNG KÝ ACF OPTIONS PAGE (Yêu cầu ACF Pro)
 */
add_action( 'acf/init', 'webdaitin_register_logo_options_page' );
function webdaitin_register_logo_options_page() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title'  => 'Quản lý Logo List',
			'menu_title'  => 'Quản lý Logo List',
			'menu_slug'   => 'logo-list-settings',
			'capability'  => 'manage_options',
			'redirect'    => false,
			'icon_url'    => 'dashicons-images-alt2',
			'position'    => 30,
		) );
	}
}

/**
 * 2. TỰ ĐỘNG KHAI BÁO ACF FIELD GROUP BẰNG PHP
 * (Không cần thao tác bấm tạo Field trong Admin)
 */
add_action( 'acf/init', 'webdaitin_register_logo_field_group' );
function webdaitin_register_logo_field_group() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		acf_add_local_field_group( array(
			'key'                   => 'group_logo_lists_settings',
			'title'                 => 'Cấu hình Danh sách Logo',
			'fields'                => array(
				array(
					'key'          => 'field_logo_lists',
					'label'        => 'Danh sách các Bộ Logo',
					'name'         => 'logo_lists',
					'type'         => 'repeater',
					'instructions' => 'Tạo và quản lý các nhóm logo (ví dụ: Báo chí, Khách hàng, Đối tác...)',
					'button_label' => '+ Thêm Bộ Logo Mới',
					'layout'       => 'block',
					'sub_fields'   => array(
						array(
							'key'           => 'field_list_id',
							'label'         => 'Mã ID Danh sách (Dùng trong Shortcode)',
							'name'          => 'list_id',
							'type'          => 'text',
							'instructions'  => 'Nhập ID không dấu, viết liền hoặc dùng gạch nối (ví dụ: bao-chi, khach-hang, doi-tac)',
							'required'      => 1,
							'wrapper'       => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_list_title',
							'label'         => 'Tên/Tiêu đề Danh sách',
							'name'          => 'list_title',
							'type'          => 'text',
							'instructions'  => 'Ví dụ: Báo chí nói về chúng tôi, Khách hàng tiêu biểu',
							'wrapper'       => array( 'width' => '40' ),
						),
						array(
							'key'           => 'field_slides_per_view',
							'label'         => 'Số Logo hiển thị (Desktop)',
							'name'          => 'slides_per_view',
							'type'          => 'number',
							'default_value' => 5,
							'min'           => 1,
							'max'           => 10,
							'wrapper'       => array( 'width' => '15' ),
						),
						array(
							'key'           => 'field_slides_per_view_mobile',
							'label'         => 'Số Logo hiển thị (Mobile)',
							'name'          => 'slides_per_view_mobile',
							'type'          => 'number',
							'default_value' => 2,
							'min'           => 1,
							'max'           => 5,
							'wrapper'       => array( 'width' => '15' ),
						),
						array(
							'key'           => 'field_autoplay',
							'label'         => 'Tự động chạy (Autoplay)',
							'name'          => 'autoplay',
							'type'          => 'true_false',
							'default_value' => 1,
							'ui'            => 1,
							'wrapper'       => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_logo_items',
							'label'         => 'Danh sách Logo trong bộ này',
							'name'          => 'items',
							'type'          => 'repeater',
							'button_label'  => '+ Thêm Logo',
							'layout'        => 'table',
							'sub_fields'    => array(
								array(
									'key'           => 'field_item_logo_img',
									'label'         => 'Ảnh Logo',
									'name'          => 'logo_img',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'thumbnail',
									'required'      => 1,
								),
								array(
									'key'   => 'field_item_logo_title',
									'label' => 'Tên Logo / Alt',
									'name'  => 'logo_title',
									'type'  => 'text',
								),
								array(
									'key'   => 'field_item_logo_link',
									'label' => 'Đường dẫn (Link)',
									'name'  => 'logo_link',
									'type'  => 'url',
								),
								array(
									'key'           => 'field_item_logo_target',
									'label'         => 'Mở Tab',
									'name'          => 'logo_target',
									'type'          => 'select',
									'choices'       => array(
										'_self'  => 'Trang hiện tại (_self)',
										'_blank' => 'Tab mới (_blank)',
									),
									'default_value' => '_blank',
								),
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'logo-list-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		) );
	}
}

/**
 * 3. NẠP ASSETS (SWIPER JS/CSS + CUSTOM CSS & JS)
 */
add_action( 'wp_enqueue_scripts', 'webdaitin_logo_slider_enqueue_assets' );
function webdaitin_logo_slider_enqueue_assets() {
	// Nạp Swiper v11 từ CDN
	wp_register_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
	wp_register_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
}

/**
 * 4. SHORTCODE: [logo_slider id="bao-chi"]
 */
add_shortcode( 'logo_slider', 'webdaitin_logo_slider_shortcode' );
function webdaitin_logo_slider_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'id'            => '',        // Mã ID bộ logo (ví dụ: bao-chi, khach-hang)
		'items'         => '',        // Ghi đè số lượng item desktop (nếu truyền)
		'items_mobile'  => '',        // Ghi đè số lượng item mobile (nếu truyền)
		'space_between' => 20,        // Khoảng cách giữa các card (px)
		'autoplay'      => '',        // 1 hoặc 0 (tùy chọn)
		'loop'          => 'true',    // true / false
		'center'        => 'true',    // centeredSlides true/false
	), $atts, 'logo_slider' );

	$list_id = trim( $atts['id'] );
	if ( empty( $list_id ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p style="color:red;">[Logo Slider Error]: Vui lòng nhập tham số <code>id</code>. Ví dụ: <code>[logo_slider id="bao-chi"]</code></p>';
		}
		return '';
	}

	// Lấy dữ liệu từ ACF Options Page
	$logo_lists = function_exists( 'get_field' ) ? get_field( 'logo_lists', 'option' ) : array();
	$target_list = null;

	if ( is_array( $logo_lists ) ) {
		foreach ( $logo_lists as $list ) {
			if ( isset( $list['list_id'] ) && trim( $list['list_id'] ) === $list_id ) {
				$target_list = $list;
				break;
			}
		}
	}

	if ( ! $target_list || empty( $target_list['items'] ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p style="color:red;">[Logo Slider Error]: Không tìm thấy bộ logo có ID <code>' . esc_html( $list_id ) . '</code> hoặc bộ logo chưa có dữ liệu trong Admin > Quản lý Logo List.</p>';
		}
		return '';
	}

	// Nạp Swiper assets
	wp_enqueue_style( 'swiper-css' );
	wp_enqueue_script( 'swiper-js' );

	// Cấu hình các tham số hiển thị
	$desktop_items = ! empty( $atts['items'] ) ? intval( $atts['items'] ) : ( ! empty( $target_list['slides_per_view'] ) ? intval( $target_list['slides_per_view'] ) : 5 );
	$mobile_items  = ! empty( $atts['items_mobile'] ) ? intval( $atts['items_mobile'] ) : ( ! empty( $target_list['slides_per_view_mobile'] ) ? intval( $target_list['slides_per_view_mobile'] ) : 2 );
	$space_between = intval( $atts['space_between'] );
	$is_autoplay   = ( $atts['autoplay'] !== '' ) ? ( $atts['autoplay'] === '1' || $atts['autoplay'] === 'true' ) : ( ! empty( $target_list['autoplay'] ) );
	$is_center     = ( $atts['center'] === 'true' );
	$is_loop       = ( $atts['loop'] === 'true' );

	$unique_id = 'logo-swiper-' . wp_generate_password( 8, false, false );

	ob_start();
	?>
	<div class="webdaitin-logo-slider-wrapper">
		<!--<?php if ( ! empty( $target_list['list_title'] ) ) : ?>
			<h3 class="logo-slider-title"><?php echo esc_html( $target_list['list_title'] ); ?></h3>
		<?php endif; ?>-->

		<div id="<?php echo esc_attr( $unique_id ); ?>" class="swiper webdaitin-logo-swiper">
			<div class="swiper-wrapper">
				<?php foreach ( $target_list['items'] as $item ) :
					$img_url  = isset( $item['logo_img']['url'] ) ? $item['logo_img']['url'] : ( is_string( $item['logo_img'] ) ? $item['logo_img'] : '' );
					$img_alt  = ! empty( $item['logo_title'] ) ? $item['logo_title'] : ( isset( $item['logo_img']['alt'] ) ? $item['logo_img']['alt'] : '' );
					$link     = ! empty( $item['logo_link'] ) ? $item['logo_link'] : '';
					$target   = ! empty( $item['logo_target'] ) ? $item['logo_target'] : '_blank';
					if ( empty( $img_url ) ) continue;
					?>
					<div class="swiper-slide">
						<div class="logo-card">
							<?php if ( $link ) : ?>
								<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" title="<?php echo esc_attr( $img_alt ); ?>">
									<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" />
								</a>
							<?php else : ?>
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" />
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Navigation controls -->
			<div class="swiper-pagination"></div>
			<div class="swiper-button-next"></div>
			<div class="swiper-button-prev"></div>
		</div>
	</div>

	<style>
		.webdaitin-logo-slider-wrapper {
			position: relative;
			padding: 0px 0;
			margin: 0px 0;
		}
		.webdaitin-logo-slider-wrapper .logo-slider-title {
			text-align: center;
			font-size: 22px;
			font-weight: 700;
			margin-bottom: 25px;
			color: #333;
		}
		.webdaitin-logo-swiper {
			padding: 20px 10px 45px 10px !important;
		}
		.webdaitin-logo-swiper .swiper-slide {
			display: flex;
			justify-content: center;
			align-items: center;
			transition: transform 0.3s ease, opacity 0.3s ease;
			height: auto;
		}
		/* Style Card Logo có box-shadow */
		.webdaitin-logo-swiper .logo-card {
			background: #ffffff;
			border-radius: 12px;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
			padding: 18px 22px;
			width: 100%;
			height: 110px;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1);
			border: 1px solid rgba(0, 0, 0, 0.04);
		}
		.webdaitin-logo-swiper .logo-card img {
			max-height: 75px;
			max-width: 100%;
			object-fit: contain;
			filter: grayscale(20%);
			transition: all 0.3s ease;
		}
		/* Hover Effect */
		.webdaitin-logo-swiper .logo-card:hover {
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
			transform: translateY(-4px);
			border-color: rgba(0, 0, 0, 0.08);
		}
		.webdaitin-logo-swiper .logo-card:hover img {
			filter: grayscale(0%);
			transform: scale(1.05);
		}
		/* Swiper Center Mode Highlight */
		.webdaitin-logo-swiper .swiper-slide-active .logo-card {
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16);
			border-color: #0073aa;
			transform: scale(1.04);
		}
		.webdaitin-logo-swiper .swiper-slide-active .logo-card img {
			filter: grayscale(0%);
		}
		/* Navigation buttons & Pagination */
		.webdaitin-logo-swiper .swiper-button-next,
		.webdaitin-logo-swiper .swiper-button-prev {
			color: #0073aa;
			width: 38px;
			height: 38px;
			background: #ffffff;
			border-radius: 50%;
			box-shadow: 0 2px 10px rgba(0,0,0,0.12);
		}
		.webdaitin-logo-swiper .swiper-button-next:after,
		.webdaitin-logo-swiper .swiper-button-prev:after {
			font-size: 16px;
			font-weight: bold;
		}
		.webdaitin-logo-swiper .swiper-pagination-bullet-active {
			background: #0073aa;
			width: 20px;
			border-radius: 6px;
		}
	</style>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			if (typeof Swiper !== 'undefined') {
				new Swiper('#<?php echo esc_js( $unique_id ); ?>', {
					slidesPerView: <?php echo intval( $mobile_items ); ?>,
					spaceBetween: <?php echo intval( $space_between ); ?>,
					centeredSlides: <?php echo $is_center ? 'true' : 'false'; ?>,
					loop: <?php echo $is_loop ? 'true' : 'false'; ?>,
					<?php if ( $is_autoplay ) : ?>
					autoplay: {
						delay: 2500,
						disableOnInteraction: false,
						pauseOnMouseEnter: true,
					},
					<?php endif; ?>
					pagination: {
						el: '#<?php echo esc_js( $unique_id ); ?> .swiper-pagination',
						clickable: true,
					},
					navigation: {
						nextEl: '#<?php echo esc_js( $unique_id ); ?> .swiper-button-next',
						prevEl: '#<?php echo esc_js( $unique_id ); ?> .swiper-button-prev',
					},
					breakpoints: {
						576: {
							slidesPerView: <?php echo max( 2, intval( $mobile_items ) ); ?>,
							spaceBetween: <?php echo intval( $space_between ); ?>,
						},
						768: {
							slidesPerView: <?php echo max( 3, intval( $desktop_items ) - 2 ); ?>,
							spaceBetween: <?php echo intval( $space_between ); ?>,
						},
						1024: {
							slidesPerView: <?php echo intval( $desktop_items ); ?>,
							spaceBetween: <?php echo intval( $space_between ); ?>,
						}
					}
				});
			}
		});
	</script>
	<?php
	return ob_get_clean();
}

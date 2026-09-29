<?php
// Add custom Theme Functions here

add_filter( 'use_block_editor_for_post_type', '__return_false' );

add_action( 'wp_enqueue_scripts', 'webdaitin_enqueue_custom_css', 20 );
function webdaitin_enqueue_custom_css() {
    wp_enqueue_style(
        'webdaitin-custom',
        get_stylesheet_directory_uri() . '/css/custom.css',
        array( 'flatsome-style' ),
        file_exists( get_stylesheet_directory() . '/css/custom.css' ) ? filemtime( get_stylesheet_directory() . '/css/custom.css' ) : null
    );
}

add_action( 'wp_enqueue_scripts', 'webdaitin_enqueue_category_archive_assets' );
function webdaitin_enqueue_category_archive_assets() {
    if ( ! is_category() ) {
        return;
    }

    $category_css = get_stylesheet_directory() . '/css/category-page.css';
    $category_js  = get_stylesheet_directory() . '/js/category-page.js';

    wp_enqueue_style(
        'webdaitin-category-archive',
        get_stylesheet_directory_uri() . '/css/category-page.css',
        array( 'webdaitin-custom' ),
        file_exists( $category_css ) ? filemtime( $category_css ) : null
    );

    wp_enqueue_script(
        'webdaitin-category-archive',
        get_stylesheet_directory_uri() . '/js/category-page.js',
        array(),
        file_exists( $category_js ) ? filemtime( $category_js ) : null,
        true
    );
}



// SEO 
/**
 * 1. ĐIỀU CHỈNH THẺ CANONICAL CỦA CÁC TRANG PHÂN TRANG (PAGINATION) VỀ TRANG CHÍNH TẮC
 * Áp dụng đồng thời cho cả Rank Math SEO và Yoast SEO
 */

// Xử lý Canonical đối với Rank Math SEO
add_filter( 'rank_math/frontend/canonical', 'flatsome_custom_seo_canonical' );

// Xử lý Canonical đối với Yoast SEO
add_filter( 'wpseo_canonical', 'flatsome_custom_seo_canonical' );

function flatsome_custom_seo_canonical( $canonical ) {
    // Chỉ can thiệp nếu là trang phân trang (ví dụ: /page/4/)
    if ( is_paged() ) {
        // Sử dụng Regex loại bỏ phần "page/x/" hoặc "page/x" ở cuối đường dẫn
        $canonical = preg_replace( '/page\/\d+\/?$/', '', $canonical );
    }
    return $canonical;
}


/**
 * 2. TỰ ĐỘNG TẠO THỂ LINK REL="NEXT" VÀ REL="PREV" CHUẨN XÁC TRONG THẺ <HEAD>
 * Nhận diện cấu trúc phân trang trên các trang lưu trữ, danh mục và tin tức
 */
add_action( 'wp_head', 'flatsome_add_pagination_prev_next_tags', 2 );
function flatsome_add_pagination_prev_next_tags() {
    // Kiểm tra nếu là trang lưu trữ, danh mục, thẻ tag hoặc trang chủ tin tức
    if ( is_archive() || is_home() || is_category() || is_tag() ) {
        global $wp_query;
        
        // Xác định số trang hiện tại và tổng số trang
        $paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
        $max_pages = $wp_query->max_num_pages;

        // Nếu chuỗi phân trang có từ 2 trang trở lên
        if ( $max_pages > 1 ) {
            
            // --- Xử lý thẻ link rel="prev" (Trang trước) ---
            if ( $paged > 1 ) {
                $prev_page = $paged - 1;
                // Lấy link trang trước đó
                $prev_url = get_pagenum_link( $prev_page );
                // Nếu trang trước đó là trang đầu tiên (page 1), xóa bỏ phần /page/1/ dư thừa để tránh trùng lặp cấu trúc
                if ( $prev_page == 1 ) {
                    $prev_url = preg_replace( '/page\/1\/?$/', '', $prev_url );
                }
                echo '<link rel="prev" href="' . esc_url( $prev_url ) . '" />' . "\n";
            }

            // --- Xử lý thẻ link rel="next" (Trang tiếp theo) ---
            if ( $paged < $max_pages ) {
                $next_url = get_pagenum_link( $paged + 1 );
                echo '<link rel="next" href="' . esc_url( $next_url ) . '" />' . "\n";
            }
        }
    }
}


//Schema



// Ẩn tiêu đề bài viết hiện tại trong Rank Math Breadcrumb (Chỉ áp dụng cho Post, giữ nguyên cho Page)
add_filter( 'rank_math/frontend/breadcrumb/items', function( $crumbs, $class ) {
    // Kiểm tra nếu đang ở trang chi tiết của Bài viết (Post)
    if ( is_singular( 'post' ) ) {
        // Xóa phần tử cuối cùng (tức là tiêu đề bài viết)
        array_pop( $crumbs );
    }
    return $crumbs;
}, 10, 2);



/*========================================================================
 * HIỂN THỊ BÀI VIẾT LIÊN QUAN TRONG BÀI VIẾT SINGLE (PURE HTML - KHÔNG DÙNG SHORTCODE FLATSOME)
 *========================================================================*/
add_shortcode( 'bai_viet_lien_quan_cat', 'flatsome_related_posts_by_category' );
function flatsome_related_posts_by_category() {
	if ( ! is_single() ) {
		return '';
	}

	global $post;
	if ( ! $post ) {
		return '';
	}

	$categories = get_the_category( $post->ID );
	if ( ! $categories ) {
		return '';
	}

	$category_ids = wp_list_pluck( $categories, 'term_id' );
	if ( empty( $category_ids ) ) {
		return '';
	}

	$query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'category__in'        => $category_ids,
		'post__not_in'        => array( $post->ID ),
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	) );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="related-posts-section" style="background:#fff;padding:30px;border-radius:4px;box-shadow:0 0 15px rgba(0,0,0,.05);margin-top:20px;margin-bottom:30px;">
		<div class="related-posts-title" style="display:flex;align-items:center;justify-content:center;gap:20px;margin-bottom:30px;">
			<span class="line" style="flex:1;height:1px;background:#ddd;"></span>
			<p style="margin:0;white-space:nowrap;font-weight:700;letter-spacing:.5px;font-size:1.25em;">BÀI VIẾT LIÊN QUAN</p>
			<span class="line" style="flex:1;height:1px;background:#ddd;"></span>
		</div>
		<div class="row row-small related-posts-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$p_cats = get_the_category(); ?>
			<div class="col small-6 large-3">
				<div class="related-post-item" style="margin-bottom:20px;">
					<a href="<?php the_permalink(); ?>" class="related-post-thumb" style="display:block;overflow:hidden;border-radius:4px;margin-bottom:12px;">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium', array( 'style' => 'width:100%;height:150px;object-fit:cover;display:block;' ) ); } ?>
					</a>
					<div class="related-post-meta" style="font-size:12px;color:#999;margin-bottom:8px;display:flex;gap:15px;flex-wrap:wrap;">
						<?php if ( ! empty( $p_cats ) ) : ?>
							<span class="related-post-cat"><i class="icon-folder"></i> <?php echo esc_html( $p_cats[0]->name ); ?></span>
						<?php endif; ?>
						<span class="related-post-date"><i class="icon-calendar"></i> <?php echo esc_html( get_the_date( 'd-m-Y' ) ); ?></span>
					</div>
					<p class="related-post-title" style="font-size:15px;font-weight:700;line-height:1.4;margin:0 0 8px;">
						<a href="<?php the_permalink(); ?>" style="color:#222;"><?php the_title(); ?></a>
					</p>
					<div class="related-post-excerpt" style="font-size:13px;color:#777;line-height:1.5;">
						<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
					</div>
				</div>
			</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Thêm ô upload ẢNH cho Category (Danh mục bài viết) trong wp-admin
 * KHÔNG cần plugin ACF - dùng Media Library có sẵn của WordPress.
 * Lưu vào term meta: category_thumbnail_id
 *
 * ==> Copy toàn bộ đoạn code này vào cuối file functions.php của child theme.
 */
 
// 1. Enqueue script Media Uploader ở màn hình quản lý Category
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		wp_enqueue_media();
	}
} );
 
// 2. Thêm ô upload ảnh khi TẠO MỚI category
add_action( 'category_add_form_fields', function () {
	?>
	<div class="form-field term-thumbnail-wrap">
		<label><?php esc_html_e( 'Ảnh danh mục', 'flatsome-child' ); ?></label>
		<div class="category-thumbnail-preview" style="margin-bottom:10px;">
			<img src="" style="max-width:150px;height:auto;display:none;" class="category-thumbnail-img" />
		</div>
		<input type="hidden" name="category_thumbnail_id" class="category-thumbnail-id" value="">
		<button type="button" class="button button-secondary upload-category-thumbnail"><?php esc_html_e( 'Chọn ảnh', 'flatsome-child' ); ?></button>
		<button type="button" class="button button-link-delete remove-category-thumbnail" style="display:none;"><?php esc_html_e( 'Xóa ảnh', 'flatsome-child' ); ?></button>
		<p class="description"><?php esc_html_e( 'Ảnh hiển thị cho ô danh mục con ở ngoài trang chủ.', 'flatsome-child' ); ?></p>
	</div>
	<?php
} );
 
// 3. Thêm ô upload ảnh khi SỬA category
add_action( 'category_edit_form_fields', function ( $term ) {
	$thumbnail_id  = get_term_meta( $term->term_id, 'category_thumbnail_id', true );
	$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
	?>
	<tr class="form-field term-thumbnail-wrap">
		<th scope="row"><label><?php esc_html_e( 'Ảnh danh mục', 'flatsome-child' ); ?></label></th>
		<td>
			<div class="category-thumbnail-preview" style="margin-bottom:10px;">
				<img src="<?php echo esc_url( $thumbnail_url ); ?>" style="max-width:150px;height:auto;<?php echo $thumbnail_url ? '' : 'display:none;'; ?>" class="category-thumbnail-img" />
			</div>
			<input type="hidden" name="category_thumbnail_id" class="category-thumbnail-id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
			<button type="button" class="button button-secondary upload-category-thumbnail"><?php esc_html_e( 'Chọn ảnh', 'flatsome-child' ); ?></button>
			<button type="button" class="button button-link-delete remove-category-thumbnail" style="<?php echo $thumbnail_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Xóa ảnh', 'flatsome-child' ); ?></button>
			<p class="description"><?php esc_html_e( 'Ảnh hiển thị cho ô danh mục con ở ngoài trang chủ.', 'flatsome-child' ); ?></p>
		</td>
	</tr>
	<?php
} );
 
// 4. Lưu dữ liệu khi tạo mới
add_action( 'create_category', function ( $term_id ) {
	if ( isset( $_POST['category_thumbnail_id'] ) ) {
		update_term_meta( $term_id, 'category_thumbnail_id', absint( $_POST['category_thumbnail_id'] ) );
	}
} );
 
// 5. Lưu dữ liệu khi sửa
add_action( 'edited_category', function ( $term_id ) {
	if ( isset( $_POST['category_thumbnail_id'] ) ) {
		update_term_meta( $term_id, 'category_thumbnail_id', absint( $_POST['category_thumbnail_id'] ) );
	}
} );
 
// 6. JS xử lý nút "Chọn ảnh" / "Xóa ảnh" (Media Uploader)
add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-category' !== $screen->id ) {
		return;
	}
	?>
	<script>
	jQuery(function ($) {
		var frame;
		$(document).on('click', '.upload-category-thumbnail', function (e) {
			e.preventDefault();
			var wrap = $(this).closest('.term-thumbnail-wrap');
 
			if (frame) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: 'Chọn ảnh danh mục',
				button: { text: 'Dùng ảnh này' },
				multiple: false
			});
			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				wrap.find('.category-thumbnail-id').val(attachment.id);
				wrap.find('.category-thumbnail-img').attr('src', attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url).show();
				wrap.find('.remove-category-thumbnail').show();
			});
			frame.open();
		});
 
		$(document).on('click', '.remove-category-thumbnail', function (e) {
			e.preventDefault();
			var wrap = $(this).closest('.term-thumbnail-wrap');
			wrap.find('.category-thumbnail-id').val('');
			wrap.find('.category-thumbnail-img').hide();
			$(this).hide();
		});
	});
	</script>
	<?php
} );






/**
 * WIDGET "CÁC DÒNG XE"
 * Copy toàn bộ vào cuối functions.php của child theme
 */

/* =========================================================
 * 1. CUSTOM POST TYPE: Dòng xe
 * =========================================================*/
add_action( 'init', function () {
	register_post_type( 'xe_dong_xe', array(
		'labels'       => array(
			'name'          => 'Dòng xe',
			'singular_name' => 'Dòng xe',
			'add_new_item'  => 'Thêm dòng xe mới',
			'edit_item'     => 'Sửa dòng xe',
			'menu_name'     => 'Dòng xe',
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-car',
		'supports'     => array( 'title', 'page-attributes' ),
	) );

	register_taxonomy( 'loai_xe', 'xe_dong_xe', array(
		'labels'            => array( 'name' => 'Loại xe', 'singular_name' => 'Loại xe' ),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_menu'      => true,
	) );
} );

/* =========================================================
 * 2. Enqueue Media Uploader + Flickity
 * =========================================================*/
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post_type, $taxonomy;
	if ( 'xe_dong_xe' === $post_type || 'loai_xe' === $taxonomy ) {
		wp_enqueue_media();
	}
} );

// Đảm bảo Flickity chắc chắn được nạp trên frontend (không giả định Flatsome đã có sẵn)
add_action( 'wp_enqueue_scripts', function () {
	if ( ! wp_script_is( 'flickity', 'registered' ) ) {
		wp_register_script( 'flickity', 'https://cdnjs.cloudflare.com/ajax/libs/flickity/2.3.0/flickity.pkgd.min.js', array( 'jquery' ), '2.3.0', true );
	}
	if ( ! wp_style_is( 'flickity', 'registered' ) ) {
		wp_register_style( 'flickity', 'https://cdnjs.cloudflare.com/ajax/libs/flickity/2.3.0/flickity.min.css', array(), '2.3.0' );
	}
	wp_enqueue_script( 'flickity' );
	wp_enqueue_style( 'flickity' );
} );

/* =========================================================
 * 3. Metabox thông tin xe — nhiều ảnh nội/ngoại thất
 * =========================================================*/
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'xe_dong_xe_details', 'Thông tin dòng xe', 'flatsome_child_render_xe_metabox', 'xe_dong_xe', 'normal', 'high' );
} );

function flatsome_child_render_xe_metabox( $post ) {
	wp_nonce_field( 'xe_dong_xe_save', 'xe_dong_xe_nonce' );
	$f      = function ( $key ) use ( $post ) { return get_post_meta( $post->ID, $key, true ); };
	$ext_ids = json_decode( $f( 'xe_imgs_exterior' ), true ) ?: array();
	$int_ids = json_decode( $f( 'xe_imgs_interior' ), true ) ?: array();

	$render_img_list = function( $ids, $label, $field_name ) {
		$thumbs = '';
		foreach ( $ids as $id ) {
			$url     = wp_get_attachment_image_url( $id, 'thumbnail' );
			$thumbs .= '<span class="xe-thumb-item" data-id="' . esc_attr( $id ) . '">
				<img src="' . esc_url( $url ) . '">
				<a class="xe-remove-thumb" title="Xóa">✕</a>
			</span>';
		}
		echo '<div class="xe-field xe-multi-img-box" data-field="' . esc_attr( $field_name ) . '">
			<label>' . esc_html( $label ) . '</label>
			<div class="xe-thumbs-wrap">' . $thumbs . '</div>
			<input type="hidden" name="' . esc_attr( $field_name ) . '" class="xe-imgs-ids" value="' . esc_attr( wp_json_encode( $ids ) ) . '">
			<button type="button" class="button xe-upload-multi-btn">+ Thêm ảnh</button>
		</div>';
	};
	?>
	<style>
		.xe-field{margin-bottom:16px;} .xe-field label{display:block;font-weight:600;margin-bottom:6px;}
		.xe-field input[type=text],.xe-field input[type=url]{width:100%;max-width:420px;}
		.xe-field-row{display:flex;gap:30px;flex-wrap:wrap;}
		.xe-thumbs-wrap{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;min-height:20px;}
		.xe-thumb-item{position:relative;display:inline-block;}
		.xe-thumb-item img{width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;display:block;}
		.xe-remove-thumb{position:absolute;top:-6px;right:-6px;background:#e00;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;line-height:18px;text-align:center;cursor:pointer;text-decoration:none;}
	</style>
	<div class="xe-field-row">
		<?php
		$render_img_list( $ext_ids, 'Ảnh Ngoại thất (nhiều ảnh)', 'xe_imgs_exterior' );
		$render_img_list( $int_ids, 'Ảnh Nội thất (nhiều ảnh)', 'xe_imgs_interior' );
		?>
	</div>
	<div class="xe-field">
		<label>Số chỗ (VD: "4 chỗ")</label>
		<input type="text" name="xe_seats" value="<?php echo esc_attr( $f( 'xe_seats' ) ); ?>">
	</div>
	<div class="xe-field">
		<label>Tag đặc điểm (cách nhau bởi dấu phẩy, VD: Sân bay, Đi tỉnh, Tự lái)</label>
		<input type="text" name="xe_tags" value="<?php echo esc_attr( $f( 'xe_tags' ) ); ?>">
	</div>
	<div class="xe-field-row">
		<div class="xe-field"><label>Động cơ</label><input type="text" name="xe_engine" value="<?php echo esc_attr( $f( 'xe_engine' ) ); ?>"></div>
		<div class="xe-field"><label>Nhiên liệu</label><input type="text" name="xe_fuel" value="<?php echo esc_attr( $f( 'xe_fuel' ) ); ?>"></div>
	</div>
	<div class="xe-field-row">
		<div class="xe-field"><label>Điều hòa</label><input type="text" name="xe_ac" value="<?php echo esc_attr( $f( 'xe_ac' ) ); ?>"></div>
		<div class="xe-field"><label>Hành lý</label><input type="text" name="xe_luggage" value="<?php echo esc_attr( $f( 'xe_luggage' ) ); ?>"></div>
	</div>
	<div class="xe-field-row">
		<div class="xe-field"><label>Hộp số</label><input type="text" name="xe_transmission" value="<?php echo esc_attr( $f( 'xe_transmission' ) ); ?>"></div>
		<div class="xe-field"><label>Đời xe</label><input type="text" name="xe_year" value="<?php echo esc_attr( $f( 'xe_year' ) ); ?>"></div>
	</div>
	<div class="xe-field">
		<label>Link nút "Yêu cầu báo giá"</label>
		<input type="url" name="xe_quote_link" value="<?php echo esc_attr( $f( 'xe_quote_link' ) ); ?>" placeholder="https://...">
	</div>
	<script>
	jQuery(function ($) {
		$(document).on('click', '.xe-upload-multi-btn', function (e) {
			e.preventDefault();
			var box = $(this).closest('.xe-multi-img-box');
			var frame = wp.media({ title: 'Chọn ảnh', button: { text: 'Thêm ảnh đã chọn' }, multiple: true });
			frame.on('select', function () {
				var ids = JSON.parse(box.find('.xe-imgs-ids').val() || '[]');
				frame.state().get('selection').each(function (att) {
					var a = att.toJSON();
					if (ids.indexOf(a.id) === -1) {
						ids.push(a.id);
						var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
						box.find('.xe-thumbs-wrap').append('<span class="xe-thumb-item" data-id="' + a.id + '"><img src="' + url + '"><a class="xe-remove-thumb" title="Xóa">✕</a></span>');
					}
				});
				box.find('.xe-imgs-ids').val(JSON.stringify(ids));
			});
			frame.open();
		});
		$(document).on('click', '.xe-remove-thumb', function (e) {
			e.preventDefault();
			var item = $(this).closest('.xe-thumb-item');
			var box  = item.closest('.xe-multi-img-box');
			var rmId = parseInt(item.data('id'));
			var ids  = JSON.parse(box.find('.xe-imgs-ids').val() || '[]');
			box.find('.xe-imgs-ids').val(JSON.stringify(ids.filter(function(i){ return i !== rmId; })));
			item.remove();
		});
	});
	</script>
	<?php
}

add_action( 'save_post_xe_dong_xe', function ( $post_id ) {
	if ( ! isset( $_POST['xe_dong_xe_nonce'] ) || ! wp_verify_nonce( $_POST['xe_dong_xe_nonce'], 'xe_dong_xe_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	foreach ( array( 'xe_imgs_exterior', 'xe_imgs_interior' ) as $img_field ) {
		if ( isset( $_POST[ $img_field ] ) ) {
			$ids = json_decode( wp_unslash( $_POST[ $img_field ] ), true );
			if ( is_array( $ids ) ) {
				update_post_meta( $post_id, $img_field, wp_json_encode( array_map( 'absint', $ids ) ) );
			}
		}
	}
	foreach ( array( 'xe_seats', 'xe_tags', 'xe_engine', 'xe_fuel', 'xe_ac', 'xe_luggage', 'xe_transmission', 'xe_year', 'xe_quote_link' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
} );

/* =========================================================
 * 4. Icon cho "Loại xe"
 * =========================================================*/
add_action( 'loai_xe_add_form_fields', function () { ?>
	<div class="form-field">
		<label>Icon loại xe</label>
		<div><img src="" class="loai-xe-icon-preview" style="max-width:60px;display:none;margin-bottom:8px;"></div>
		<input type="hidden" name="loai_xe_icon_id" class="loai-xe-icon-id" value="">
		<button type="button" class="button loai-xe-upload-icon">Chọn icon</button>
	</div>
<?php } );

add_action( 'loai_xe_edit_form_fields', function ( $term ) {
	$icon_id  = get_term_meta( $term->term_id, 'loai_xe_icon_id', true );
	$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
	?>
	<tr class="form-field">
		<th><label>Icon loại xe</label></th>
		<td>
			<div><img src="<?php echo esc_url( $icon_url ); ?>" class="loai-xe-icon-preview" style="max-width:60px;<?php echo $icon_url ? '' : 'display:none;'; ?>margin-bottom:8px;"></div>
			<input type="hidden" name="loai_xe_icon_id" class="loai-xe-icon-id" value="<?php echo esc_attr( $icon_id ); ?>">
			<button type="button" class="button loai-xe-upload-icon">Chọn icon</button>
		</td>
	</tr>
<?php } );

foreach ( array( 'create_loai_xe', 'edited_loai_xe' ) as $_hook ) {
	add_action( $_hook, function ( $term_id ) {
		if ( isset( $_POST['loai_xe_icon_id'] ) ) update_term_meta( $term_id, 'loai_xe_icon_id', absint( $_POST['loai_xe_icon_id'] ) );
	} );
}

add_action( 'admin_footer', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-loai_xe' !== $screen->id ) return;
	?>
	<script>
	jQuery(function ($) {
		$(document).on('click', '.loai-xe-upload-icon', function (e) {
			e.preventDefault();
			var wrap = $(this).closest('.form-field, td');
			var frame = wp.media({ title: 'Chọn icon', button: { text: 'Dùng ảnh này' }, multiple: false });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				wrap.find('.loai-xe-icon-preview').attr('src', url).show();
				wrap.find('.loai-xe-icon-id').val(att.id);
			});
			frame.open();
		});
	});
	</script>
	<?php
} );

/* =========================================================
 * 5. Helper: build slider HTML dùng Flickity
 * =========================================================*/
function xe_build_slider( $ids, $slider_id ) {
	if ( empty( $ids ) ) return '';

	$uid   = 'xe-slider-' . esc_attr( $slider_id );
	$cells = '';
	foreach ( $ids as $att_id ) {
		$img_url = wp_get_attachment_image_url( $att_id, 'large' );
		if ( ! $img_url ) continue;
		$cells .= '<div class="xe-flickity-cell"><img src="' . esc_url( $img_url ) . '" alt="" loading="lazy"></div>';
	}

	if ( ! $cells ) return '';

	// Không dùng data-flickity (auto-init) nữa — JS sẽ tự khởi tạo bằng new Flickity(...)
	// sau khi DOM sẵn sàng, tránh việc slider chưa init đã hiện hết ảnh.
	return '<div class="xe-flickity" id="' . esc_attr( $uid ) . '">'
		. $cells
		. '</div>';
}


/* =========================================================
 * 6. SHORTCODE [dong_xe_showcase]
 * =========================================================*/
add_shortcode( 'dong_xe_showcase', function ( $atts ) {
	$atts = shortcode_atts( array(
		'title' => 'CÁC DÒNG XE TẠI XE MIỀN NAM',
		'loai'  => '', // slug của Loại xe (taxonomy loai_xe), để trống = hiện tất cả
	), $atts );

	$flatsome_xe_filter_slug = sanitize_title( $atts['loai'] );

	$terms = get_terms( array( 'taxonomy' => 'loai_xe', 'hide_empty' => true ) );

	$flatsome_xe_query_args = array(
		'post_type'      => 'xe_dong_xe',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	// Nếu có attribute loai=slug thì chỉ lấy đúng loại xe đó
	if ( $flatsome_xe_filter_slug ) {
		$flatsome_xe_query_args['tax_query'] = array(
			array(
				'taxonomy' => 'loai_xe',
				'field'    => 'slug',
				'terms'    => $flatsome_xe_filter_slug,
			),
		);
	}

	$posts = get_posts( $flatsome_xe_query_args );

	if ( empty( $posts ) ) return '';

	$default_icon = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13l1.5-4.5A2 2 0 0 1 6.4 7h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v4a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H8v1a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-4z"/><circle cx="7.5" cy="17.5" r="1.2"/><circle cx="16.5" cy="17.5" r="1.2"/></svg>';

	// Pre-render tất cả sliders một lần (PHP), lưu vào mảng
	$sliders_html = array(); // key: post_id => array( 'ext' => html, 'int' => html )
	foreach ( $posts as $p ) {
		$ext_ids = json_decode( get_post_meta( $p->ID, 'xe_imgs_exterior', true ), true ) ?: array();
		$int_ids = json_decode( get_post_meta( $p->ID, 'xe_imgs_interior', true ), true ) ?: array();
		$sliders_html[ $p->ID ] = array(
			'ext' => xe_build_slider( $ext_ids, $p->ID . '-ext' ),
			'int' => xe_build_slider( $int_ids, $p->ID . '-int' ),
		);
	}

	ob_start();
	?>
	<div class="xe-showcase">

		<div class="xe-showcase__title">
			<span class="line"></span>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<span class="line"></span>
		</div>

		<?php if ( ! $flatsome_xe_filter_slug && ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
		<div class="xe-showcase__tabs">
			<button type="button" class="xe-tab-btn active" data-term="__all__">TẤT CẢ</button>
			<?php foreach ( $terms as $term ) : ?>
				<button type="button" class="xe-tab-btn" data-term="<?php echo esc_attr( $term->slug ); ?>">
					<?php echo esc_html( mb_strtoupper( $term->name, 'UTF-8' ) ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="xe-showcase__body">

			<!-- ===== SIDEBAR DANH SÁCH XE ===== -->
			<div class="xe-showcase__sidebar">
				<div class="xe-sidebar-label">LOẠI XE</div>
				<?php foreach ( $posts as $i => $p ) :
					$terms_of_post = get_the_terms( $p->ID, 'loai_xe' );
					$term_slug     = ( $terms_of_post && ! is_wp_error( $terms_of_post ) ) ? $terms_of_post[0]->slug : '';
					$icon_id       = ( $terms_of_post && ! is_wp_error( $terms_of_post ) ) ? get_term_meta( $terms_of_post[0]->term_id, 'loai_xe_icon_id', true ) : '';
					$icon_url      = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
				?>
					<div class="xe-item <?php echo 0 === $i ? 'active' : ''; ?>"
						data-post="<?php echo esc_attr( $p->ID ); ?>"
						data-term="<?php echo esc_attr( $term_slug ); ?>"
						data-title="<?php echo esc_attr( $p->post_title ); ?>"
						data-seats="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_seats', true ) ); ?>"
						data-tags="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_tags', true ) ); ?>"
						data-engine="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_engine', true ) ); ?>"
						data-fuel="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_fuel', true ) ); ?>"
						data-ac="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_ac', true ) ); ?>"
						data-luggage="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_luggage', true ) ); ?>"
						data-transmission="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_transmission', true ) ); ?>"
						data-year="<?php echo esc_attr( get_post_meta( $p->ID, 'xe_year', true ) ); ?>"
						data-quote="<?php echo esc_url( get_post_meta( $p->ID, 'xe_quote_link', true ) ?: '#' ); ?>">
						<span class="xe-item__icon"><?php echo $icon_url ? '<img src="' . esc_url( $icon_url ) . '" alt="">' : $default_icon; ?></span>
						<span class="xe-item__text">
							<strong><?php echo esc_html( $p->post_title ); ?></strong>
							<small><?php echo esc_html( get_post_meta( $p->ID, 'xe_seats', true ) ); ?></small>
						</span>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- ===== DETAIL PANEL ===== -->
			<div class="xe-showcase__detail">

				<div class="xe-detail-head">
					<span class="xe-detail-icon"><?php echo $default_icon; ?></span>
					<div>
						<h3 class="xe-detail-title"></h3>
						<div class="xe-detail-tags"></div>
					</div>
				</div>

				<!-- Toggle nội/ngoại thất -->
				<div class="xe-toggle-row">
					<button type="button" class="xe-toggle-btn active" data-show="ext">
						<?php echo $default_icon; ?> Ngoại thất
					</button>
					<button type="button" class="xe-toggle-btn" data-show="int">
						🛋 Nội thất
					</button>
				</div>

				<!-- Flatsome sliders pre-rendered — tất cả slider render sẵn, JS chỉ show/hide -->
				<div class="xe-sliders-wrap">
					<?php foreach ( $posts as $p ) :
						$has_ext = ! empty( $sliders_html[ $p->ID ]['ext'] );
						$has_int = ! empty( $sliders_html[ $p->ID ]['int'] );
						if ( ! $has_ext && ! $has_int ) continue;
					?>
					<div class="xe-slider-group" data-post="<?php echo esc_attr( $p->ID ); ?>" style="display:none;">
						<?php if ( $has_ext ) : ?>
							<div class="xe-slider-panel" data-panel="ext">
								<?php echo $sliders_html[ $p->ID ]['ext']; ?>
							</div>
						<?php endif; ?>
						<?php if ( $has_int ) : ?>
							<div class="xe-slider-panel" data-panel="int" style="display:none;">
								<?php echo $sliders_html[ $p->ID ]['int']; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>

				<div class="xe-detail-specs-title">THÔNG SỐ KỸ THUẬT</div>
				<div class="xe-detail-specs">
					<div class="xe-spec"><span>⚙ Động cơ</span><strong class="v-engine"></strong></div>
					<div class="xe-spec"><span>⛽ Nhiên liệu</span><strong class="v-fuel"></strong></div>
					<div class="xe-spec"><span>❄ Điều hòa</span><strong class="v-ac"></strong></div>
					<div class="xe-spec"><span>🧳 Hành lý</span><strong class="v-luggage"></strong></div>
					<div class="xe-spec"><span>⚙ Hộp số</span><strong class="v-transmission"></strong></div>
					<div class="xe-spec"><span>📅 Đời xe</span><strong class="v-year"></strong></div>
				</div>

				<a href="#" target="_blank" class="xe-quote-btn">Yêu cầu báo giá ↗</a>
			</div><!-- /.xe-showcase__detail -->
		</div><!-- /.xe-showcase__body -->
	</div><!-- /.xe-showcase -->

	<style>
	.xe-showcase{background:#fff;border-radius:6px;padding:30px;box-shadow:0 0 15px rgba(0,0,0,.05);margin-bottom:30px;}
	.xe-showcase__title{display:flex;align-items:center;gap:20px;margin-bottom:25px;}
	.xe-showcase__title h2{margin:0;text-align:center;font-weight:800;color:var(--primary-color,#1573ba);white-space:nowrap;}
	.xe-showcase__title .line{flex:1;height:1px;background:#ddd;}
	/* Tabs */
	.xe-showcase__tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
	.xe-tab-btn{background:#f2f2f2;border:none;padding:9px 15px;border-radius:5px;font-weight:700;font-size:13px;cursor:pointer;color:#333;transition:.2s;}
	.xe-tab-btn.active,.xe-tab-btn:hover{background:var(--primary-color,#1573ba);color:#fff;}
	/* Body */
	.xe-showcase__body{display:flex;border:1px solid #eee;border-radius:6px;overflow:hidden;}
	/* Sidebar */
	.xe-showcase__sidebar{width:260px;flex:0 0 260px;border-right:1px solid #eee;background:#fafafa;max-height:620px;overflow-y:auto;}
	.xe-sidebar-label{font-size:11px;font-weight:700;color:#aaa;padding:12px 14px;letter-spacing:.5px;}
	.xe-item{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-left:3px solid transparent;transition:.15s;}
	.xe-item:hover{background:#eef4fa;}
	.xe-item.active{background:#eaf2fb;border-left-color:var(--primary-color,#1573ba);}
	.xe-item.xe-hidden{display:none;}
	.xe-item__icon{width:32px;height:32px;flex:0 0 32px;display:flex;align-items:center;justify-content:center;background:#fff;border-radius:5px;color:var(--primary-color,#1573ba);}
	.xe-item__icon img{max-width:20px;max-height:20px;}
	.xe-item__text{display:flex;flex-direction:column;font-size:13px;line-height:1.4;}
	.xe-item__text small{color:#999;font-size:11px;}
	/* Detail */
	.xe-showcase__detail{flex:1;padding:20px;min-width:0;overflow:hidden;}
	.xe-detail-head{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;}
	.xe-detail-icon{color:var(--primary-color,#1573ba);flex:0 0 auto;margin-top:3px;}
	.xe-detail-title{margin:0 0 5px;font-size:18px;font-weight:800;}
	.xe-detail-tags span{display:inline-block;background:#f2f2f2;border-radius:20px;padding:3px 10px;font-size:12px;margin:2px 4px 2px 0;}
	/* Toggle buttons */
	.xe-toggle-row{display:flex;gap:10px;margin-bottom:12px;}
	.xe-toggle-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid #ddd;background:#f7f7f7;border-radius:5px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;color:#555;transition:.2s;}
	.xe-toggle-btn svg{width:16px;height:16px;}
	.xe-toggle-btn.active{background:#e4eefb;border-color:#bcd6f2;color:var(--primary-color,#1573ba);}
	/* Sliders wrap */
	.xe-sliders-wrap{margin-bottom:16px;}
	/* Flickity slider */
	.xe-flickity{border-radius:8px;overflow:hidden;background:#f0f0f0;}
	.xe-flickity-cell{width:100%;}
	.xe-flickity-cell img{width:100%;height:260px;object-fit:cover;display:block;}
	/* Trước khi Flickity init xong, chỉ hiện ảnh đầu tiên - tránh hiện hết tất cả ảnh dạng list */
	.xe-flickity:not(.flickity-enabled) .xe-flickity-cell:not(:first-child){display:none;}
	.xe-flickity .flickity-prev-next-button{width:34px;height:34px;background:rgba(0,0,0,.5);}
	.xe-flickity .flickity-prev-next-button:hover{background:rgba(0,0,0,.75);}
	.xe-flickity .flickity-prev-next-button .arrow{fill:#fff;}
	.xe-flickity .flickity-page-dots{bottom:8px;}
	.xe-flickity .flickity-page-dots .dot{width:8px;height:8px;margin:0 4px;background:#fff;opacity:.6;}
	.xe-flickity .flickity-page-dots .dot.is-selected{opacity:1;background:var(--primary-color,#1573ba);}
	/* Specs */
	.xe-detail-specs-title{font-size:11px;font-weight:700;color:#aaa;letter-spacing:.5px;margin-bottom:8px;text-transform:uppercase;}
	.xe-detail-specs{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;}
	.xe-spec{background:#f8f8f8;border-radius:5px;padding:10px 12px;}
	.xe-spec span{display:block;font-size:11px;color:#999;margin-bottom:3px;}
	.xe-spec strong{font-size:13px;}
	.xe-quote-btn{display:block;text-align:center;border:1px solid #ddd;border-radius:5px;padding:12px;font-weight:700;text-decoration:none;color:#333;transition:.2s;}
	.xe-quote-btn:hover{background:var(--primary-color,#1573ba);border-color:var(--primary-color,#1573ba);color:#fff;}
	@media(max-width:768px){
		.xe-showcase__body{flex-direction:column;}
		.xe-showcase__sidebar{width:100%;flex:none;max-height:240px;}
		.xe-detail-specs{grid-template-columns:1fr;}
		.xe-slider-panel .slide-bg-image{height:180px !important;}
	}
	</style>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var roots = document.querySelectorAll('.xe-showcase');
		var root  = roots[roots.length - 1];
		if (!root) return;

		var items   = root.querySelectorAll('.xe-item');
		var tabs    = root.querySelectorAll('.xe-tab-btn');
		var toggles = root.querySelectorAll('.xe-toggle-btn');
		var activeType = 'ext'; // ngoại thất mặc định

		// ---- Hiển thị slider nhóm đúng xe + đúng loại (nội/ngoại) ----
		function showSlider(postId, type) {
			// Ẩn tất cả groups
			root.querySelectorAll('.xe-slider-group').forEach(function(g){
				g.style.display = 'none';
				g.querySelectorAll('.xe-slider-panel').forEach(function(p){ p.style.display = 'none'; });
			});
			// Hiện group đúng xe
			var group = root.querySelector('.xe-slider-group[data-post="' + postId + '"]');
			if (!group) return;
			group.style.display = 'block';
			// Hiện panel đúng loại, fallback sang ext nếu không có int
			var panel = group.querySelector('.xe-slider-panel[data-panel="' + type + '"]');
			if (!panel) panel = group.querySelector('.xe-slider-panel[data-panel="ext"]');
			if (panel) {
				panel.style.display = 'block';
				// Init hoặc resize Flickity sau khi panel visible
				var flkEl = panel.querySelector('.xe-flickity');
				if (flkEl && window.Flickity) {
					var flk = Flickity.data(flkEl);
					if (flk) {
						flk.resize();
					} else {
						new Flickity(flkEl, {
							wrapAround: true,
							imagesLoaded: true,
							pageDots: true,
							prevNextButtons: true,
							adaptiveHeight: false
						});
					}
				}
			}
		}

		// ---- Tab lọc loại xe ----
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				tabs.forEach(function (t) { t.classList.remove('active'); });
				tab.classList.add('active');
				var term = tab.dataset.term;
				var visible = [];
				items.forEach(function (item) {
					var match = (term === '__all__' || item.dataset.term === term);
					item.classList.toggle('xe-hidden', !match);
					if (match) visible.push(item);
				});
				if (visible.length) renderItem(visible[0]);
			});
		});

		// ---- Toggle nội/ngoại thất ----
		toggles.forEach(function (btn) {
			btn.addEventListener('click', function () {
				activeType = btn.dataset.show;
				toggles.forEach(function (b) { b.classList.toggle('active', b.dataset.show === activeType); });
				var activeItem = root.querySelector('.xe-item.active');
				if (activeItem) showSlider(activeItem.dataset.post, activeType);
			});
		});

		// ---- Render item ----
		function renderItem(el) {
			items.forEach(function (i) { i.classList.remove('active'); });
			el.classList.add('active');

			root.querySelector('.xe-detail-title').textContent = el.dataset.title || '';

			var tagsWrap = root.querySelector('.xe-detail-tags');
			tagsWrap.innerHTML = '';
			(el.dataset.tags || '').split(',').forEach(function (t) {
				t = t.trim();
				if (!t) return;
				var span = document.createElement('span');
				span.textContent = t;
				tagsWrap.appendChild(span);
			});

			root.querySelector('.v-engine').textContent       = el.dataset.engine || '';
			root.querySelector('.v-fuel').textContent         = el.dataset.fuel || '';
			root.querySelector('.v-ac').textContent           = el.dataset.ac || '';
			root.querySelector('.v-luggage').textContent      = el.dataset.luggage || '';
			root.querySelector('.v-transmission').textContent = el.dataset.transmission || '';
			root.querySelector('.v-year').textContent         = el.dataset.year || '';
			root.querySelector('.xe-quote-btn').href          = el.dataset.quote || '#';

			showSlider(el.dataset.post, activeType);
		}

		items.forEach(function (el) {
			el.addEventListener('click', function () { renderItem(el); });
		});

		if (items.length) renderItem(items[0]);
	});
	</script>
	<?php
	return ob_get_clean();
} );

/* =========================================================
 * 7. Checkbox "Hiển thị dòng xe" trên màn hình Category
 * =========================================================*/
add_action( 'category_edit_form_fields', function ( $term ) {
	$checked = get_term_meta( $term->term_id, 'show_vehicle_widget', true );
	?>
	<tr class="form-field">
		<th><label>Hiển thị "Các dòng xe"</label></th>
		<td>
			<label>
				<input type="checkbox" name="show_vehicle_widget" value="1" <?php checked( $checked, '1' ); ?>>
				Hiện widget "Các dòng xe" ở trang danh mục này
			</label>
		</td>
	</tr>
	<?php
} );
add_action( 'edited_category', function ( $term_id ) {
	update_term_meta( $term_id, 'show_vehicle_widget', isset( $_POST['show_vehicle_widget'] ) ? '1' : '' );
} );


/**
 * ACF Field Group: Các mục bài viết (chọn tay) cho page template
 * "Trang chọn bài theo mục".
 * Cần plugin Advanced Custom Fields PRO (vì dùng Repeater + Relationship field).
 */

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_page_sections',
		'title'    => 'Các mục bài viết (Page: Chọn bài theo mục)',
		'fields'   => array(
			array(
				'key'          => 'field_page_sections_repeater',
				'label'        => 'Các mục bài viết',
				'name'         => 'page_sections',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Thêm mục mới',
				'min'          => 0,
				'sub_fields'   => array(
					array(
						'key'         => 'field_section_title',
						'label'       => 'Tiêu đề mục',
						'name'        => 'section_title',
						'type'        => 'text',
						'required'    => 1,
						'placeholder' => 'VD: Tin khuyến mãi, Kinh nghiệm thuê xe...',
					),
					array(
						'key'           => 'field_section_posts',
						'label'         => 'Chọn bài viết',
						'name'          => 'section_posts',
						'type'          => 'relationship',
						'instructions'  => 'Chọn các bài viết muốn hiển thị trong mục này (kéo thả để sắp xếp thứ tự).',
						'required'      => 1,
						'post_type'     => array( 'post' ),
						'filters'       => array( 'search', 'taxonomy' ),
						'elements'      => array( 'featured_image' ),
						'return_format' => 'id',
						'min'           => 1,
						'max'           => 12,
					),
					array(
						'key'          => 'field_section_link_url',
						'label'        => 'Link "Xem tất cả" (tuỳ chọn)',
						'name'         => 'section_link_url',
						'type'         => 'url',
						'instructions' => 'Để trống nếu không cần nút "Xem tất cả" cho mục này.',
						'placeholder'  => 'https://...',
					),
					array(
						'key'               => 'field_section_link_text',
						'label'             => 'Chữ trên nút "Xem tất cả"',
						'name'              => 'section_link_text',
						'type'              => 'text',
						'default_value'     => 'Xem tất cả',
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_section_link_url',
									'operator' => '!=empty',
								),
							),
						),
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'page-chon-bai-theo-muc.php',
				),
			),
		),
	) );

} );


/**
 * Metabox "Tiêu đề hiển thị (tùy chỉnh)" cho bài viết.
 * Chỉ dùng để hiển thị ở lưới bài viết trang category cha
 * (template-parts/posts/category-posts-grid.php) — KHÔNG đổi
 * tiêu đề thật, không ảnh hưởng permalink/SEO.
 */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'flatsome_display_title_box',
		'Tiêu đề hiển thị (tùy chỉnh)',
		'flatsome_child_render_display_title_metabox',
		'post',
		'side',
		'default'
	);
} );

function flatsome_child_render_display_title_metabox( $post ) {
	wp_nonce_field( 'flatsome_display_title_save', 'flatsome_display_title_nonce' );
	$value = get_post_meta( $post->ID, 'flatsome_display_title', true );
	?>
	<p style="margin-top:0;color:#666;font-size:12px;">
		Dùng khi bài viết được hiển thị dạng lưới ở trang danh mục cha (VD: "THUÊ XE 16 CHỖ").
		Để trống sẽ tự dùng tiêu đề bài viết. Không ảnh hưởng link hay SEO.
	</p>
	<input
		type="text"
		style="width:100%;"
		name="flatsome_display_title"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="VD: THUÊ XE 16 CHỖ"
	>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['flatsome_display_title_nonce'] ) || ! wp_verify_nonce( $_POST['flatsome_display_title_nonce'], 'flatsome_display_title_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['flatsome_display_title'] ) ) {
		update_post_meta( $post_id, 'flatsome_display_title', sanitize_text_field( wp_unslash( $_POST['flatsome_display_title'] ) ) );
	}
} );


/**
 * ACF Field Group cho Page Template "Trang danh sách bài viết theo danh mục"
 * (page-danh-sach-bai-viet.php).
 */

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_page_danh_sach_bai_viet',
		'title'    => 'Cấu hình bài viết & danh mục (Page: Danh sách bài viết theo danh mục)',
		'fields'   => array(
			array(
				'key'           => 'field_ds_page_banner_image',
				'label'         => 'Ảnh Banner đầu trang (tuỳ chọn)',
				'name'          => 'ds_page_banner_image',
				'type'          => 'image',
				'instructions'  => 'Tải ảnh banner hiển thị fullwidth chiều cao 500px ở đầu trang. Để trống sẽ dùng banner mặc định.',
				'return_format' => 'url',
				'preview_size'  => 'medium',
			),
			array(
				'key'          => 'field_ds_bai_viet_repeater',
				'label'        => 'Danh sách bài viết hiển thị (Khối chính)',
				'name'         => 'ds_bai_viet_repeater',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Thêm bài viết',
				'sub_fields'   => array(
					array(
						'key'           => 'field_ds_repeater_bai_viet',
						'label'         => 'Bài viết',
						'name'          => 'bai_viet',
						'type'          => 'post_object',
						'post_type'     => array( 'post' ),
						'return_format' => 'id',
						'required'      => 1,
					),
					array(
						'key'         => 'field_ds_repeater_tieu_de_hien_thi',
						'label'       => 'Tiêu đề hiển thị tùy chỉnh (để trống sẽ lấy tiêu đề gốc)',
						'name'        => 'tieu_de_hien_thi',
						'type'        => 'text',
						'placeholder' => 'VD: Dịch vụ thuê xe 35 chỗ (để trống sẽ hiển thị tiêu đề gốc)',
					),
				),
			),
			array(
				'key'           => 'field_ds_chon_danh_muc',
				'label'         => 'Chọn danh mục bài viết (dùng cho bài liên quan)',
				'name'          => 'ds_chon_danh_muc',
				'type'          => 'taxonomy',
				'instructions'  => 'Chọn danh mục để hệ thống tự động hiển thị các "Bài viết liên quan" phía dưới trang (và dùng lấy bài cho khối chính nếu không chọn thủ công bài viết ở trên).',
				'required'      => 0,
				'taxonomy'      => 'category',
				'field_type'    => 'select',
				'return_format' => 'id',
				'allow_null'    => 1,
			),
			array(
				'key'           => 'field_ds_so_bai_moi_trang',
				'label'         => 'Số bài viết mỗi trang',
				'name'          => 'ds_so_bai_moi_trang',
				'type'          => 'number',
				'default_value' => 9,
				'min'           => 1,
			),
			array(
				'key'           => 'field_ds_hien_showcase_xe',
				'label'         => 'Hiện widget "Các dòng xe" bên dưới',
				'name'          => 'ds_hien_showcase_xe',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'page-danh-sach-bai-viet.php',
				),
			),
		),
	) );

} );


/**
 * ACF Field Group: Chọn bài viết & Tiêu đề hiển thị cho trang Chuyên mục (Category)
 */
add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_category_custom_posts',
		'title'    => 'Cấu hình bài viết hiển thị (Chuyên mục)',
		'fields'   => array(
			array(
				'key'          => 'field_cat_posts_repeater',
				'label'        => 'Danh sách bài viết hiển thị (Chọn thủ công)',
				'name'         => 'cat_posts_repeater',
				'type'         => 'repeater',
				'instructions' => 'Chọn các bài viết muốn hiển thị ở lưới chính trang chuyên mục này (kéo thả để sắp xếp thứ tự). Nếu để trống, hệ thống sẽ tự động hiển thị tất cả bài viết thuộc chuyên mục.',
				'layout'       => 'table',
				'button_label' => 'Thêm bài viết',
				'sub_fields'   => array(
					array(
						'key'           => 'field_cat_repeater_post',
						'label'         => 'Bài viết',
						'name'          => 'post_item',
						'type'          => 'post_object',
						'post_type'     => array( 'post' ),
						'return_format' => 'id',
						'required'      => 1,
					),
					array(
						'key'         => 'field_cat_repeater_custom_title',
						'label'       => 'Tiêu đề hiển thị tùy chỉnh',
						'name'        => 'custom_title',
						'type'        => 'text',
						'placeholder' => 'VD: Thuê xe 16 chỗ Đà Lạt (để trống sẽ lấy tiêu đề gốc)',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'taxonomy',
					'operator' => '==',
					'value'    => 'category',
				),
			),
		),
	) );

} );

/**
 * Tích hợp Module Logo Slider (Swiper Center Mode + ACF Options Pro)
 */
require_once get_stylesheet_directory() . '/inc/logo-slider.php';

/**
 * Tự động bọc <table> trong bài viết bằng <div class="table-responsive"> để tự động Responsive trên Mobile
 */
add_filter( 'the_content', 'webdaitin_auto_responsive_tables', 20 );
function webdaitin_auto_responsive_tables( $content ) {
	if ( is_admin() || empty( $content ) || strpos( $content, '<table' ) === false ) {
		return $content;
	}

	// Nếu thẻ table chưa nằm trong wrapper div.table-responsive thì tự động bọc lại
	return preg_replace_callback( '/(?<!<div class="table-responsive">)(<table[\s\S]*?<\/table>)/i', function( $matches ) {
		return '<div class="table-responsive">' . $matches[0] . '</div>';
	}, $content );
}

add_filter( 'rank_math/frontend/breadcrumb/html', function ( $html, $crumbs, $class ) {
    // Tìm thẻ span cuối cùng đại diện cho trang hiện tại
    if ( preg_match('/<span class="last">(.*?)<\/span>/i', $html, $matches) ) {
        $current_title = $matches[1];
        
        // Lấy URL chuẩn của trang hiện tại
        global $wp;
        $current_url = home_url( add_query_arg( array(), $wp->request ) ) . '/';

        $search_text = '<span class="last">' . $current_title . '</span>';
        $replace_text = '<a href="' . esc_url($current_url) . '">' . $current_title . '</a>';
        
        $html = str_replace( $search_text, $replace_text, $html );
    }
    return $html;
}, 99, 3);

/**
 * Filter comment form reply title tag from h3 to p for SEO optimization
 */
add_filter( 'comment_form_defaults', function( $defaults ) {
	$defaults['title_reply_before'] = '<p id="reply-title" class="comment-reply-title h3-style" style="font-size:20px;font-weight:700;margin-bottom:15px;">';
	$defaults['title_reply_after']  = '</p>';
	return $defaults;
} );

/**
 * Filter Contact Form 7 form elements to convert any h1-h6 tags to p tags for SEO optimization
 */
add_filter( 'wpcf7_form_elements', function( $content ) {
	if ( empty( $content ) ) return $content;
	$content = preg_replace( '/<h[1-6]([^>]*)>/i', '<p$1>', $content );
	$content = preg_replace( '/<\/h[1-6]>/i', '</p>', $content );
	return $content;
} );


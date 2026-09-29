<?php
/**
 * Template Name: Trang danh sách bài viết theo danh mục
 * Template Post Type: page
 *
 * Page template độc lập (không phụ thuộc trang category):
 * - Đầu trang: Breadcrumb (Rank Math) + Banner fullwidth chiều cao 500px.
 * - Cột trái: lưới bài viết (chọn bài + tuỳ chỉnh tiêu đề hiển thị ở ACF Repeater,
 *   hoặc lấy tự động theo danh mục). Nếu tiêu đề tùy chỉnh để trống, tự động hiển thị tiêu đề gốc.
 * - Cột phải: sidebar "Báo giá thuê xe" (Contact Form 7 có sẵn).
 * - Bên dưới: widget "Các dòng xe" (nếu bật) + "Bài viết liên quan" theo danh mục.
 *
 * @package Flatsome\Templates
 */

get_header();

$flatsome_ds_banner_url = get_field( 'ds_page_banner_image' ); // URL ảnh banner từ ACF
$flatsome_ds_cat_id     = get_field( 'ds_chon_danh_muc' );     // ID danh mục (nếu có)
$flatsome_ds_per_page   = get_field( 'ds_so_bai_moi_trang' ) ?: 9;
$flatsome_ds_showcase   = get_field( 'ds_hien_showcase_xe' );

$flatsome_ds_cat_ids = array();
if ( $flatsome_ds_cat_id ) {
	$flatsome_ds_cat_ids[] = (int) $flatsome_ds_cat_id;
	$flatsome_ds_child_cats = get_categories( array(
		'parent'     => $flatsome_ds_cat_id,
		'hide_empty' => false,
	) );
	$flatsome_ds_cat_ids = array_merge( $flatsome_ds_cat_ids, wp_list_pluck( $flatsome_ds_child_cats, 'term_id' ) );
}

$flatsome_ds_paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

// Kiểm tra xem có cấu hình Repeater chọn bài viết không
$flatsome_has_repeater_posts = have_rows( 'ds_bai_viet_repeater' );

// Thu thập thông tin các bài chọn tay nếu dùng Repeater
$flatsome_custom_posts = array();
$flatsome_selected_post_ids = array();

if ( $flatsome_has_repeater_posts ) {
	while ( have_rows( 'ds_bai_viet_repeater' ) ) : the_row();
		$p_obj = get_sub_field( 'bai_viet' );
		if ( ! $p_obj ) continue;
		$p_id = is_object( $p_obj ) ? $p_obj->ID : (int) $p_obj;
		$custom_title = get_sub_field( 'tieu_de_hien_thi' );
		
		$flatsome_custom_posts[] = array(
			'id'           => $p_id,
			'custom_title' => $custom_title,
		);
		$flatsome_selected_post_ids[] = $p_id;
	endwhile;
}

$flatsome_ds_query = null;

if ( ! empty( $flatsome_selected_post_ids ) ) {
	// Lấy theo danh sách bài viết trong Repeater (phân trang + giữ đúng thứ tự)
	$flatsome_ds_query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => $flatsome_ds_per_page,
		'paged'               => $flatsome_ds_paged,
		'post__in'            => $flatsome_selected_post_ids,
		'orderby'             => 'post__in',
		'ignore_sticky_posts' => true,
	) );
} elseif ( ! empty( $flatsome_ds_cat_ids ) ) {
	// Tự động lấy theo danh mục nếu không chọn tay bài viết ở Repeater
	$flatsome_ds_query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => $flatsome_ds_per_page,
		'paged'               => $flatsome_ds_paged,
		'category__in'        => $flatsome_ds_cat_ids,
		'ignore_sticky_posts' => true,
	) );
}

// Map custom title từ repeater cho từng post_id
$flatsome_custom_title_map = array();
foreach ( $flatsome_custom_posts as $item ) {
	$flatsome_custom_title_map[ $item['id'] ] = $item['custom_title'];
}

// Truy vấn bài viết liên quan (nếu có chọn danh mục)
$flatsome_ds_related_query = null;
if ( ! empty( $flatsome_ds_cat_ids ) ) {
	$flatsome_ds_related_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'category__in'        => $flatsome_ds_cat_ids,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);
	if ( ! empty( $flatsome_selected_post_ids ) ) {
		$flatsome_ds_related_args['post__not_in'] = $flatsome_selected_post_ids;
	}
	$flatsome_ds_related_query = new WP_Query( $flatsome_ds_related_args );
}
?>

<?php do_action( 'flatsome_before_content' ); ?>

<!-- 1. Breadcrumb sử dụng Rank Math -->
<div class="container ds-page-breadcrumb">
	<?php
	if ( shortcode_exists( 'rank_math_breadcrumb' ) ) {
		echo do_shortcode( '[rank_math_breadcrumb]' );
	} elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		rank_math_the_breadcrumbs();
	}
	?>
</div>

<!-- 2. Banner fullwidth chiều cao 500px -->
<?php if ( $flatsome_ds_banner_url ) : ?>
	<div class="ds-page-banner" style="background-image: url('<?php echo esc_url( $flatsome_ds_banner_url ); ?>');"></div>
<?php else : ?>
	<div class="ds-page-banner-default">
		<?php echo do_shortcode( '[section bg="9888" bg_size="original" bg_pos="53% 47%" height="500px"][/section]' ); ?>
	</div>
<?php endif; ?>

<div class="container ds-page-wrap">
	<div class="row row-large">

		<div class="large-9 col">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<?php if ( get_the_content() ) : ?>
					<div class="ds-page-intro"><?php the_content(); ?></div>
				<?php endif; ?>
			<?php endwhile; endif; ?>

			<?php if ( $flatsome_ds_query && $flatsome_ds_query->have_posts() ) : ?>

				<div class="cat-posts-grid row row-small">
					<?php while ( $flatsome_ds_query->have_posts() ) : $flatsome_ds_query->the_post();
						$current_pid = get_the_ID();
						
						// Ưu tiên 1: Tiêu đề tùy chỉnh riêng trong ACF Repeater trang này
						$repeater_title = isset( $flatsome_custom_title_map[ $current_pid ] ) ? $flatsome_custom_title_map[ $current_pid ] : '';
						
						// Ưu tiên 2: Tiêu đề tùy chỉnh trong trang sửa bài viết (meta 'flatsome_display_title')
						$meta_title = get_post_meta( $current_pid, 'flatsome_display_title', true );
						
						// Nếu cả 2 đều trống -> Dùng tiêu đề bài viết gốc
						if ( ! empty( $repeater_title ) ) {
							$flatsome_title = $repeater_title;
						} elseif ( ! empty( $meta_title ) ) {
							$flatsome_title = $meta_title;
						} else {
							$flatsome_title = get_the_title();
						}
						
						$flatsome_bg_url = has_post_thumbnail() ? get_the_post_thumbnail_url( $current_pid, 'medium_large' ) : '';
					?>
					<div class="col small-6 large-4">
						<a href="<?php the_permalink(); ?>" class="cat-post-item">
							<div class="cat-post-item__bg"<?php if ( $flatsome_bg_url ) : ?> style="background-image:url('<?php echo esc_url( $flatsome_bg_url ); ?>');"<?php endif; ?>></div>
							<div class="cat-post-item__overlay"></div>
							<span class="cat-post-item__title"><?php echo esc_html( $flatsome_title ); ?></span>
						</a>
					</div>
					<?php endwhile; ?>
				</div>

				<?php if ( $flatsome_ds_query->max_num_pages > 1 ) :
					$flatsome_big = 999999999;
				?>
				<div class="cat-posts-pagination">
					<?php
					echo paginate_links( array(
						'base'      => str_replace( $flatsome_big, '%#%', esc_url( get_pagenum_link( $flatsome_big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, $flatsome_ds_paged ),
						'total'     => $flatsome_ds_query->max_num_pages,
						'prev_text' => '‹',
						'next_text' => '›',
						'type'      => 'plain',
					) );
					?>
				</div>
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'Chưa có bài viết nào được cấu hình. Vào sửa trang này và thêm bài viết/chọn danh mục ở phần cấu hình phía dưới.', 'flatsome-child' ); ?></p>

			<?php endif; ?>

		</div>

		<div class="post-sidebar large-3 col">
			<?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
			<?php get_sidebar(); ?>
			<?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
		</div>

	</div>

	<?php if ( $flatsome_ds_showcase ) : ?>
	<div class="row row-large">
		<div class="large-12 col">
			<?php echo do_shortcode( '[dong_xe_showcase]' ); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $flatsome_ds_related_query && $flatsome_ds_related_query->have_posts() ) : ?>
	<div class="row row-large">
		<div class="large-12 col">
			<div class="related-posts-section">
				<div class="related-posts-title">
					<span class="line"></span>
					<p class="related-posts-heading">BÀI VIẾT LIÊN QUAN</p>
					<span class="line"></span>
				</div>
				<div class="row row-small related-posts-grid">
					<?php while ( $flatsome_ds_related_query->have_posts() ) : $flatsome_ds_related_query->the_post();
						$flatsome_post_cats = get_the_category(); ?>
					<div class="col small-6 large-3">
						<div class="related-post-item">
							<a href="<?php the_permalink(); ?>" class="related-post-thumb">
								<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium' ); } ?>
							</a>
							<div class="related-post-meta">
								<?php if ( ! empty( $flatsome_post_cats ) ) : ?>
									<span class="related-post-cat"><i class="icon-folder"></i> <?php echo esc_html( $flatsome_post_cats[0]->name ); ?></span>
								<?php endif; ?>
								<span class="related-post-date"><i class="icon-calendar"></i> <?php echo esc_html( get_the_date( 'd-m-Y' ) ); ?></span>
							</div>
							<p class="related-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
							<div class="related-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></div>
						</div>
					</div>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

</div>

<?php do_action( 'flatsome_after_content' ); ?>

<style>
.ds-page-breadcrumb{padding-top:15px;padding-bottom:15px;}
.ds-page-banner{width:100%;height:500px;background-size:cover;background-position:center;background-repeat:no-repeat;margin-bottom:30px;}
.ds-page-banner-default{margin-bottom:30px;}
.ds-page-wrap{padding-top:10px;padding-bottom:10px;}
.ds-page-intro{margin-bottom:30px;}
.cat-posts-grid{margin-bottom:10px;}
.cat-post-item{position:relative;display:block;height:190px;border-radius:4px;overflow:hidden;margin-bottom:20px;text-decoration:none;}
.cat-post-item__bg{position:absolute;top:0;left:0;right:0;bottom:0;background-color:#ccc;background-size:cover;background-position:center;transition:transform .35s ease;}
.cat-post-item:hover .cat-post-item__bg{transform:scale(1.06);}
.cat-post-item__overlay{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.45);}
.cat-post-item__title{position:absolute;left:0;right:0;bottom:0;top:0;display:flex;align-items:center;justify-content:center;text-align:center;color:#fff;font-weight:700;font-size:20px;text-transform:uppercase;letter-spacing:.5px;padding:10px;line-height:1.3;}
.cat-posts-pagination{text-align:center;margin:20px 0 10px;}
.cat-posts-pagination .page-numbers{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 6px;margin:0 4px;border-radius:4px;background:#f2f2f2;color:#333;font-weight:700;text-decoration:none;}
.cat-posts-pagination .page-numbers.current{background:var(--primary-color,#1573ba);color:#fff;}
.cat-posts-pagination .page-numbers:hover{background:var(--primary-color,#1573ba);color:#fff;}

.related-posts-section{background:#fff;padding:30px;border-radius:4px;box-shadow:0 0 15px rgba(0,0,0,.05);margin-top:20px;margin-bottom:30px;}
.related-posts-title{display:flex;align-items:center;justify-content:center;gap:20px;margin-bottom:30px;}
.related-posts-title h3, .related-posts-title p, .related-posts-heading{margin:0;white-space:nowrap;font-weight:700;letter-spacing:.5px;font-size:1.25em;}
.related-posts-title .line{flex:1;height:1px;background:#ddd;}
.related-post-item{margin-bottom:20px;}
.related-post-thumb{display:block;overflow:hidden;border-radius:4px;margin-bottom:12px;}
.related-post-thumb img{width:100%;height:150px;object-fit:cover;display:block;transition:transform .35s ease;}
.related-post-thumb:hover img{transform:scale(1.06);}
.related-post-meta{font-size:12px;color:#999;margin-bottom:8px;display:flex;gap:15px;flex-wrap:wrap;}
.related-post-title{font-size:15px;font-weight:700;line-height:1.4;margin:0 0 8px;}
.related-post-title a{color:#222;}
.related-post-title a:hover{color:var(--primary-color,#1573ba);}
.related-post-excerpt{font-size:13px;color:#777;line-height:1.5;}

@media (max-width: 549px) {
	.ds-page-banner{height:300px;}
	.cat-post-item{height:150px;}
	.cat-post-item__title{font-size:16px;}
}
</style>

<?php get_footer(); ?>

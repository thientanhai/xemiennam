<?php
/**
 * Posts layout right sidebar.
 * Child theme override of the Flatsome blog layout.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

// 1. Breadcrumb Rank Math (trên cùng trang category)
if ( is_category() ) : ?>
	<div class="container category-top-breadcrumb">
		<?php
		if ( shortcode_exists( 'rank_math_breadcrumb' ) ) {
			echo do_shortcode( '[rank_math_breadcrumb]' );
		} elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
			rank_math_the_breadcrumbs();
		}
		?>
	</div>
	<style>.category-top-breadcrumb{padding-top:15px;padding-bottom:15px;}</style>

	<?php
	// 2. Banner shortcode Flatsome 500px fullwidth
	$flatsome_cat_banner = '[section bg="9888" bg_size="original" bg_pos="53% 47%" height="500px"][/section]';
	echo '<div class="category-top-banner">' . do_shortcode( $flatsome_cat_banner ) . '</div>';
	echo '<style>.category-top-banner{margin-bottom:30px;}</style>';
endif;
?>

<?php do_action( 'flatsome_before_blog' ); ?>

<?php if ( ! is_single() && flatsome_option( 'blog_featured' ) === 'top' ) { get_template_part( 'template-parts/posts/featured-posts' ); } ?>

<?php if ( is_category() ) :
	$flatsome_current_cat = get_queried_object();
	$flatsome_cat_id      = $flatsome_current_cat->term_id;

	// 1. Kiểm tra danh sách bài viết chọn thủ công trong ACF Term Meta chuyên mục
	$flatsome_cat_repeater_posts = get_field( 'cat_posts_repeater', 'category_' . $flatsome_cat_id );

	$flatsome_selected_post_ids = array();
	$flatsome_custom_title_map  = array();

	if ( ! empty( $flatsome_cat_repeater_posts ) && is_array( $flatsome_cat_repeater_posts ) ) {
		foreach ( $flatsome_cat_repeater_posts as $row ) {
			$p_obj = isset( $row['post_item'] ) ? $row['post_item'] : null;
			if ( ! $p_obj ) continue;
			$p_id = is_object( $p_obj ) ? $p_obj->ID : (int) $p_obj;
			$c_title = isset( $row['custom_title'] ) ? trim( $row['custom_title'] ) : '';

			$flatsome_selected_post_ids[]        = $p_id;
			$flatsome_custom_title_map[ $p_id ] = $c_title;
		}
	}

	// Lấy danh mục con
	$flatsome_child_cats = get_categories( array(
		'parent'     => $flatsome_cat_id,
		'hide_empty' => false,
	) );
	$flatsome_cat_ids   = wp_list_pluck( $flatsome_child_cats, 'term_id' );
	$flatsome_cat_ids[] = $flatsome_cat_id;

	// Phân trang
	$flatsome_paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

	$flatsome_grid_query = null;

	if ( ! empty( $flatsome_selected_post_ids ) ) {
		// Nếu chọn tay bài viết -> Lấy đúng danh sách bài chọn tay theo thứ tự
		$flatsome_grid_query = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => 9,
			'paged'               => $flatsome_paged,
			'post__in'            => $flatsome_selected_post_ids,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
		) );
	} else {
		// Nếu không chọn tay -> Tự động lấy tất cả bài viết thuộc category
		$flatsome_grid_query = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => 9,
			'paged'               => $flatsome_paged,
			'category__in'        => $flatsome_cat_ids,
			'ignore_sticky_posts' => true,
		) );
	}
?>

	<?php // Hàng chính: Cột trái Lưới bài viết (large-9) + Cột phải Sidebar Báo giá (large-3) ?>
	<div class="row row-large <?php if ( flatsome_option( 'blog_layout_divider' ) ) { echo 'row-divided '; } ?>">
		<div class="large-9 col">

			<?php if ( $flatsome_grid_query->have_posts() ) : ?>

				<div class="cat-posts-grid row row-small">
					<?php while ( $flatsome_grid_query->have_posts() ) : $flatsome_grid_query->the_post();
						$current_pid         = get_the_ID();
						$repeater_cat_title  = isset( $flatsome_custom_title_map[ $current_pid ] ) ? $flatsome_custom_title_map[ $current_pid ] : '';
						$flatsome_meta_title = get_post_meta( $current_pid, 'flatsome_display_title', true );
						
						if ( ! empty( $repeater_cat_title ) ) {
							$flatsome_title = $repeater_cat_title;
						} elseif ( ! empty( $flatsome_meta_title ) ) {
							$flatsome_title = $flatsome_meta_title;
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

				<?php if ( $flatsome_grid_query->max_num_pages > 1 ) :
					$flatsome_big = 999999999;
				?>
				<div class="cat-posts-pagination">
					<?php
					echo paginate_links( array(
						'base'      => str_replace( $flatsome_big, '%#%', esc_url( get_pagenum_link( $flatsome_big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, $flatsome_paged ),
						'total'     => $flatsome_grid_query->max_num_pages,
						'prev_text' => '‹',
						'next_text' => '›',
						'type'      => 'plain',
					) );
					?>
				</div>
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'Chưa có bài viết nào trong danh mục này.', 'flatsome-child' ); ?></p>

			<?php endif; ?>

		</div>

		<div class="post-sidebar large-3 col">
			<?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
			<?php get_sidebar(); ?>
			<?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
		</div>
	</div>

	<?php // Widget dòng xe: row riêng full width large-12 ?>
	<?php if ( get_term_meta( $flatsome_cat_id, 'show_vehicle_widget', true ) ) : ?>
	<div class="row row-large">
		<div class="large-12 col">
			<?php echo do_shortcode( '[dong_xe_showcase]' ); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php
	// Bài viết liên quan (4 bài mới nhất)
	$flatsome_related_query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'category__in'        => $flatsome_cat_ids,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	) );
	?>

	<?php if ( $flatsome_related_query->have_posts() ) : ?>
	<div class="row row-large">
		<div class="large-12 col">
			<div class="related-posts-section">
				<div class="related-posts-title">
					<span class="line"></span>
					<h3>BÀI VIẾT LIÊN QUAN</h3>
					<span class="line"></span>
				</div>
				<div class="row row-small related-posts-grid">
					<?php while ( $flatsome_related_query->have_posts() ) : $flatsome_related_query->the_post();
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
							<h4 class="related-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
							<div class="related-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></div>
						</div>
					</div>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<style>
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
	.related-posts-title h3{margin:0;white-space:nowrap;font-weight:700;letter-spacing:.5px;}
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
		.cat-post-item{height:150px;}
		.cat-post-item__title{font-size:16px;}
	}
	</style>

<?php else : ?>

	<?php // Trang thường (Single post, tag, author, etc.): layout chuẩn có sidebar bên phải ?>
	<div class="row row-large <?php if ( flatsome_option( 'blog_layout_divider' ) ) { echo 'row-divided '; } ?>">
		<div class="large-9 col">
			<?php if ( ! is_single() && flatsome_option( 'blog_featured' ) === 'content' ) { get_template_part( 'template-parts/posts/featured-posts' ); } ?>
			<?php
				if ( is_single() ) {
					get_template_part( 'template-parts/posts/single' );
					comments_template();
				} elseif ( flatsome_option( 'blog_style_archive' ) && ( is_archive() || is_search() ) ) {
					get_template_part( 'template-parts/posts/archive', flatsome_option( 'blog_style_archive' ) );
				} else {
					get_template_part( 'template-parts/posts/archive', flatsome_option( 'blog_style' ) );
				}
			?>
		</div>
		<div class="post-sidebar large-3 col">
			<?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
			<?php get_sidebar(); ?>
			<?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
		</div>
	</div>

	<?php
	// Bài viết liên quan cho trang Single Post - hiển thị 1 row riêng fullwidth ngoài sidebar
	if ( is_single() && get_post_type() === 'post' ) :
		$flatsome_single_cat_ids = wp_get_post_categories( get_the_ID() );
		if ( ! empty( $flatsome_single_cat_ids ) ) :
			$flatsome_single_related_query = new WP_Query( array(
				'post_type'           => 'post',
				'posts_per_page'      => 4,
				'category__in'        => $flatsome_single_cat_ids,
				'post__not_in'        => array( get_the_ID() ),
				'ignore_sticky_posts' => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
			) );

			if ( $flatsome_single_related_query->have_posts() ) : ?>
			<div class="row row-large">
				<div class="large-12 col">
					<div class="related-posts-section">
						<div class="related-posts-title">
							<span class="line"></span>
							<h3>BÀI VIẾT LIÊN QUAN</h3>
							<span class="line"></span>
						</div>
						<div class="row row-small related-posts-grid">
							<?php while ( $flatsome_single_related_query->have_posts() ) : $flatsome_single_related_query->the_post();
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
									<h4 class="related-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
									<div class="related-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></div>
								</div>
							</div>
							<?php endwhile; wp_reset_postdata(); ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif;
		endif;
	endif;
	?>

	<style>
	.related-posts-section{background:#fff;padding:30px;border-radius:4px;box-shadow:0 0 15px rgba(0,0,0,.05);margin-top:20px;margin-bottom:30px;}
	.related-posts-title{display:flex;align-items:center;justify-content:center;gap:20px;margin-bottom:30px;}
	.related-posts-title h3{margin:0;white-space:nowrap;font-weight:700;letter-spacing:.5px;}
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
	</style>

<?php endif; ?>

<?php do_action( 'flatsome_after_blog' ); ?>
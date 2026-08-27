<?php
/**
 * Custom Category Template for Category 166 (Hợp Phát Style Layout)
 *
 * @package WebDaiTin
 */

$queried_term        = get_queried_object();
$current_category_id = ( $queried_term && ! is_wp_error( $queried_term ) && isset( $queried_term->term_id ) ) ? (int) $queried_term->term_id : 0;
$current_category    = $current_category_id ? get_term( $current_category_id, 'category' ) : null;
$root_category_id    = 166;

// Subcategories (child of 166) or other categories show default category template
if ( $current_category_id !== $root_category_id ) {
	require get_template_directory() . '/category-default.php';
	return;
}

/**
 * Helper function to render a category section (1 Featured post left + 4 Latest posts right)
 */
if ( ! function_exists( 'webdaitin_render_hopphat_section' ) ) {
	function webdaitin_render_hopphat_section( $term_object, $posts_query = null, $show_more = true ) {
		if ( null === $posts_query ) {
			$posts_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 5,
					'cat'                 => $term_object->term_id,
					'ignore_sticky_posts' => true,
				)
			);
		}

		if ( ! $posts_query->have_posts() ) {
			return;
		}

		$all_posts     = $posts_query->posts;
		$featured_post = array_shift( $all_posts );
		$latest_posts  = array_slice( $all_posts, 0, 4 );

		$term_link = get_term_link( $term_object );
		if ( is_wp_error( $term_link ) ) {
			$term_link = home_url( '/' );
		}
		?>
		<section class="webdaitin-hopphat-section">
			<div class="webdaitin-section-header">
				<h2 class="webdaitin-section-title">
					<a href="<?php echo esc_url( $term_link ); ?>">
						<?php echo esc_html( mb_strtoupper( $term_object->name, 'UTF-8' ) ); ?>
					</a>
				</h2>
				<?php if ( $show_more ) : ?>
					<a class="webdaitin-section-more" href="<?php echo esc_url( $term_link ); ?>">Xem thêm &gt;&gt;&gt;</a>
				<?php endif; ?>
			</div>

			<div class="webdaitin-feature-layout" data-category-archive>
				<!-- Left Column: Featured Post -->
				<div class="webdaitin-feature-column webdaitin-feature-column--main">
					<?php if ( $featured_post ) : ?>
						<?php
						$featured_id       = $featured_post->ID;
						$featured_terms    = get_the_category( $featured_id );
						$featured_category = ! empty( $featured_terms ) ? $featured_terms[0] : $term_object;
						$featured_image    = get_the_post_thumbnail_url( $featured_id, 'large' );
						?>
						<article class="webdaitin-card webdaitin-featured-card">
							<a class="webdaitin-card-image-link" href="<?php echo esc_url( get_permalink( $featured_id ) ); ?>">
								<?php if ( $featured_image ) : ?>
									<img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( get_the_title( $featured_id ) ); ?>" loading="lazy" />
								<?php else : ?>
									<div class="webdaitin-img-placeholder"></div>
								<?php endif; ?>
							</a>
							<div class="webdaitin-card-body webdaitin-featured-body">
								<h3 class="webdaitin-featured-title">
									<a href="<?php echo esc_url( get_permalink( $featured_id ) ); ?>">
										<?php echo esc_html( get_the_title( $featured_id ) ); ?>
									</a>
								</h3>
								<p class="webdaitin-post-excerpt">
									<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $featured_id ) ), 35, '...' ) ); ?>
								</p>
							</div>
						</article>
					<?php endif; ?>
				</div>

				<!-- Right Column: 4 Latest Posts -->
				<div class="webdaitin-feature-column webdaitin-feature-column--aside">
					<div class="webdaitin-latest-list">
						<?php foreach ( $latest_posts as $post_item ) : ?>
							<?php
							$post_id    = $post_item->ID;
							$post_image = get_the_post_thumbnail_url( $post_id, 'medium' );
							$post_terms = get_the_category( $post_id );
							$post_term  = ! empty( $post_terms ) ? $post_terms[0] : $term_object;
							?>
							<article class="webdaitin-latest-item webdaitin-card">
								<a class="webdaitin-latest-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
									<?php if ( $post_image ) : ?>
										<img src="<?php echo esc_url( $post_image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" />
									<?php else : ?>
										<div class="webdaitin-img-placeholder"></div>
									<?php endif; ?>
								</a>
								<div class="webdaitin-latest-content">
									<h4 class="webdaitin-latest-title">
										<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
											<?php echo esc_html( get_the_title( $post_id ) ); ?>
										</a>
									</h4>
									<div class="webdaitin-post-meta webdaitin-post-meta--compact">
										<span class="meta-date"><i class="meta-icon">📅</i> <?php echo esc_html( get_the_date( 'd-m-Y', $post_id ) ); ?></span>
										<?php if ( $post_term ) : ?>
											<span class="meta-cat"><i class="meta-icon">📁</i> <?php echo esc_html( $post_term->name ); ?></span>
										<?php endif; ?>
									</div>
									<p class="webdaitin-post-excerpt webdaitin-post-excerpt--compact">
										<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ), 18, '...' ) ); ?>
									</p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}
}

get_header();

$ancestor_ids   = $current_category_id ? get_ancestors( $current_category_id, 'category' ) : array();
$breadcrumb_ids = array_reverse( $ancestor_ids );
$root_term      = get_term( $root_category_id, 'category' );

// Get Category Image URL for Parent Category 166
$category_image_url = '';

// 1. Try theme's category_thumbnail_id (used in child-categories.php)
$cat_thumb_id = get_term_meta( $root_category_id, 'category_thumbnail_id', true );
if ( $cat_thumb_id ) {
	$category_image_url = wp_get_attachment_image_url( (int) $cat_thumb_id, 'full' );
}

// 2. Try standard thumbnail_id / image
if ( empty( $category_image_url ) ) {
	$thumb_id = get_term_meta( $root_category_id, 'thumbnail_id', true );
	if ( ! $thumb_id ) {
		$thumb_id = get_term_meta( $root_category_id, 'image', true );
	}
	if ( $thumb_id ) {
		if ( is_numeric( $thumb_id ) ) {
			$category_image_url = wp_get_attachment_image_url( (int) $thumb_id, 'full' );
		} elseif ( is_string( $thumb_id ) && strpos( $thumb_id, 'http' ) !== false ) {
			$category_image_url = $thumb_id;
		}
	}
}

// 3. Try ACF fields
if ( empty( $category_image_url ) && function_exists( 'get_field' ) ) {
	$acf_img = get_field( 'category_image', 'category_' . $root_category_id );
	if ( ! $acf_img ) {
		$acf_img = get_field( 'image', 'category_' . $root_category_id );
	}
	if ( is_array( $acf_img ) && isset( $acf_img['url'] ) ) {
		$category_image_url = $acf_img['url'];
	} elseif ( is_string( $acf_img ) ) {
		$category_image_url = $acf_img;
	}
}

// 4. Try Taxonomy Images plugin
if ( empty( $category_image_url ) && function_exists( 'z_taxonomy_image_url' ) ) {
	$category_image_url = z_taxonomy_image_url( $root_category_id );
}

// 5. Fallback to latest post thumbnail in category 166 if no category image set
if ( empty( $category_image_url ) ) {
	$hero_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'cat'            => $root_category_id,
		)
	);
	if ( $hero_query->have_posts() ) {
		$category_image_url = get_the_post_thumbnail_url( $hero_query->posts[0]->ID, 'full' );
	}
	wp_reset_postdata();
}

// Fetch subcategories under root category 166
$tab_terms = get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'parent'     => $root_category_id,
		'orderby'    => 'term_order',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $tab_terms ) ) {
	$tab_terms = array();
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
?>

<main id="primary" class="site-main category-archive-page webdaitin-hopphat-theme">

	<!-- Breadcrumbs (Above Banner) -->
	<div class="webdaitin-category-shell">
		<div class="webdaitin-category-breadcrumbs" aria-label="Breadcrumb">
			<?php echo do_shortcode( '[rank_math_breadcrumb]' ); ?>
		</div>
	</div>

	<!-- Parent Category Hero Banner (Fullwidth) -->
	<div class="webdaitin-category-hero">
		<?php if ( ! empty( $category_image_url ) ) : ?>
			<div class="webdaitin-category-hero__media">
				<img src="<?php echo esc_url( $category_image_url ); ?>" alt="<?php echo esc_attr( $root_term ? $root_term->name : '' ); ?>" loading="lazy" />
			</div>
		<?php else : ?>
			<div class="webdaitin-category-hero__fallback"></div>
		<?php endif; ?>
		<div class="webdaitin-category-hero__overlay">
			<div class="webdaitin-category-hero__content webdaitin-category-shell">
				<div class="row">
					<div class="col large-12 small-12 medium-12">
							<h1 class="webdaitin-category-title"><?php echo esc_html( $root_term ? mb_strtoupper( $root_term->name, 'UTF-8' ) : 'TIN TỨC' ); ?></h1>
				<?php if ( $root_term && ! empty( $root_term->description ) ) : ?>
					<div class="webdaitin-category-summary">
						<?php echo wp_kses_post( wpautop( $root_term->description ) ); ?>
					</div>
				<?php endif; ?>
					</div>
				</div>
				
			</div>
		</div>
	</div>

	<div class="webdaitin-category-shell">

		<!-- Subcategory Navigation Tabs -->
		<?php if ( ! empty( $tab_terms ) ) : ?>
			<?php $root_link = get_term_link( $root_category_id, 'category' ); ?>
			<div class="webdaitin-category-tabs" role="tablist" aria-label="Danh mục tin tức">
				<?php if ( ! is_wp_error( $root_link ) ) : ?>
					<a
						class="webdaitin-category-tab <?php echo ( $current_category_id === $root_category_id ) ? 'is-active' : ''; ?>"
						href="<?php echo esc_url( $root_link ); ?>"
					>
						TẤT CẢ
					</a>
				<?php endif; ?>
				<?php foreach ( $tab_terms as $tab_term ) : ?>
					<?php $tab_link = get_term_link( $tab_term ); ?>
					<?php if ( is_wp_error( $tab_link ) ) { continue; } ?>
					<a
						class="webdaitin-category-tab <?php echo ( $current_category_id === (int) $tab_term->term_id ) ? 'is-active' : ''; ?>"
						href="<?php echo esc_url( $tab_link ); ?>"
					>
						<?php echo esc_html( mb_strtoupper( $tab_term->name, 'UTF-8' ) ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Main Content Area -->
		<div class="webdaitin-sections-wrapper">

			<?php
			// Display ALL Subcategories as Sections for Root Category 166
			if ( ! empty( $tab_terms ) ) :
				$sections_rendered = 0;
				foreach ( $tab_terms as $subcat_term ) {
					$sub_query = new WP_Query(
						array(
							'post_type'           => 'post',
							'post_status'         => 'publish',
							'posts_per_page'      => 5,
							'cat'                 => $subcat_term->term_id,
							'ignore_sticky_posts' => true,
						)
					);

					if ( $sub_query->have_posts() ) {
						webdaitin_render_hopphat_section( $subcat_term, $sub_query, true );
						$sections_rendered++;
					}
				}

				// If subcategories had no posts, fallback to Category 166 direct posts
				if ( 0 === $sections_rendered ) :
					$main_query = new WP_Query(
						array(
							'post_type'           => 'post',
							'post_status'         => 'publish',
							'posts_per_page'      => 15,
							'paged'               => $paged,
							'cat'                 => $root_category_id,
							'ignore_sticky_posts' => true,
						)
					);
					if ( $main_query->have_posts() ) :
						webdaitin_render_hopphat_section( $current_category, $main_query, false );

						// Remaining grid posts if > 5 posts
						if ( count( $main_query->posts ) > 5 ) :
							$grid_posts = array_slice( $main_query->posts, 5 );
							?>
							<div class="webdaitin-grid-section">
								<div class="webdaitin-post-grid">
									<?php foreach ( $grid_posts as $grid_item ) : ?>
										<?php
										$g_id    = $grid_item->ID;
										$g_image = get_the_post_thumbnail_url( $g_id, 'medium' );
										?>
										<article class="webdaitin-card webdaitin-grid-card">
											<a class="webdaitin-grid-thumb" href="<?php echo esc_url( get_permalink( $g_id ) ); ?>">
												<?php if ( $g_image ) : ?>
													<img src="<?php echo esc_url( $g_image ); ?>" alt="<?php echo esc_attr( get_the_title( $g_id ) ); ?>" loading="lazy" />
												<?php endif; ?>
											</a>
											<div class="webdaitin-grid-body">
												<h4 class="webdaitin-grid-title">
													<a href="<?php echo esc_url( get_permalink( $g_id ) ); ?>"><?php echo esc_html( get_the_title( $g_id ) ); ?></a>
												</h4>
												<div class="webdaitin-post-meta webdaitin-post-meta--compact">
													<span><?php echo esc_html( get_the_date( 'd-m-Y', $g_id ) ); ?></span>
												</div>
												<p class="webdaitin-post-excerpt webdaitin-post-excerpt--compact">
													<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $g_id ) ), 18, '...' ) ); ?>
												</p>
											</div>
										</article>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $main_query->max_num_pages > 1 ) : ?>
							<nav class="webdaitin-pagination" aria-label="Pagination">
								<?php
								echo paginate_links(
									array(
										'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
										'format'    => '?paged=%#%',
										'current'   => $paged,
										'total'     => $main_query->max_num_pages,
										'prev_text' => '‹',
										'next_text' => '›',
										'type'      => 'list',
									)
								);
								?>
							</nav>
						<?php endif; ?>
					<?php else : ?>
						<section class="webdaitin-empty-state webdaitin-card">
							<h2>Chưa có bài viết</h2>
							<p>Danh mục này hiện chưa có nội dung. Vui lòng quay lại sau.</p>
						</section>
					<?php endif; ?>
				<?php endif; ?>

			<?php else : ?>
				<section class="webdaitin-empty-state webdaitin-card">
					<h2>Chưa có bài viết</h2>
					<p>Danh mục này hiện chưa có nội dung. Vui lòng quay lại sau.</p>
				</section>
			<?php endif; ?>

		</div><!-- .webdaitin-sections-wrapper -->

	</div><!-- .webdaitin-category-shell -->
</main>

<?php
wp_reset_postdata();
get_footer();
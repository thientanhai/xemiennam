<?php
/**
 * Single post content template in child theme.
 * Ensures title, metadata, featured image, and content are always displayed reliably.
 *
 * @package WebDaiTin
 */

if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article-inner <?php flatsome_blog_article_classes(); ?>">
		<header class="entry-header single-header">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-image flex-col text-center" style="margin-bottom:20px;">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

			<div class="single-breadcrumnb">
				<?php if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs(); ?>

			</div>

			<h1 class="entry-title single-title" style="font-weight:700;font-size:28px;margin-bottom:15px;line-height:1.3;">
				<?php the_title(); ?>
			</h1>

			<div class="entry-meta uppercase is-xsmall" style="font-size:13px;color:#888;margin-bottom:25px;display:flex;gap:15px;flex-wrap:wrap;">
				<span class="posted-on"><i class="icon-calendar"></i> <?php echo esc_html( get_the_date( 'd-m-Y' ) ); ?></span>
				<span class="byline"><i class="icon-user"></i> <a href="/doan-minh-tai/">Đoàn Minh Tài</span></a>
				<?php if ( has_category() ) : ?>
					<span class="cat-links"><i class="icon-folder"></i> <?php the_category( ', ' ); ?></span>
				<?php endif; ?>
			</div>
		</header>

		<div class="entry-content single-page">
			<?php the_content(); ?>
		</div>

		<?php if ( get_theme_mod( 'blog_share', 1 ) ) : ?>
			<div class="blog-share text-center">
				<div class="is-divider medium" style="margin:25px auto 15px; width:30px; height:2px; background:#ccc;"></div>
				<?php echo do_shortcode( '[share]' ); ?>
			</div>
		<?php endif; ?>

		<?php
		if ( get_theme_mod( 'blog_author_box', 1 ) ) :
			$author_id          = get_the_author_meta( 'ID' );
			$author_name        = get_the_author_meta( 'display_name' );
			$author_description = get_the_author_meta( 'description' );
			$author_url         = get_author_posts_url( $author_id );
			$author_avatar      = get_avatar( $author_id, 110 );
			?>
			<div class="entry-author author-box">
				<div class="author-box-flex">
					<?php if ( $author_avatar ) : ?>
						<div class="author-box-avatar">
							<a href="<?php echo esc_url( $author_url ); ?>">
								<?php echo $author_avatar; ?>
							</a>
						</div>
					<?php endif; ?>
					<div class="author-box-content">
						<h5 class="author-name">
							<a href="<?php echo esc_url( $author_url ); ?>">
								<?php echo esc_html( $author_name ); ?>
							</a>
						</h5>
						<?php if ( $author_description ) : ?>
							<div class="author-desc">
								<?php echo wp_kses_post( wpautop( $author_description ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</article>

<?php endwhile; endif; ?>

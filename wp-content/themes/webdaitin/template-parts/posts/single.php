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

			<h1 class="entry-title single-title" style="font-weight:700;font-size:28px;margin-bottom:15px;line-height:1.3;">
				<?php the_title(); ?>
			</h1>

			<div class="entry-meta uppercase is-xsmall" style="font-size:13px;color:#888;margin-bottom:25px;display:flex;gap:15px;flex-wrap:wrap;">
				<span class="posted-on"><i class="icon-calendar"></i> <?php echo esc_html( get_the_date( 'd-m-Y' ) ); ?></span>
				<span class="byline"><i class="icon-user"></i> <?php the_author(); ?></span>
				<?php if ( has_category() ) : ?>
					<span class="cat-links"><i class="icon-folder"></i> <?php the_category( ', ' ); ?></span>
				<?php endif; ?>
			</div>
		</header>

		<div class="entry-content single-page">
			<?php the_content(); ?>
		</div>
	</div>
</article>

<?php endwhile; endif; ?>

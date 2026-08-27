<?php
/**
 * Default category archive template for non-166 categories.
 * Copy of the Flatsome blog archive entry point so this child theme can be customized independently.
 */

get_header();
?>

<div id="content" class="blog-wrapper blog-archive page-wrapper">
	<?php get_template_part( 'template-parts/posts/layout', get_theme_mod( 'blog_layout', 'right-sidebar' ) ); ?>
</div>

<?php get_footer(); ?>

<?php
/**
 * Default category archive template for non-166 categories.
 * Copy of the Flatsome blog archive entry point so this child theme can be customized independently.
 */

get_header();
?>

<div id="content" class="blog-wrapper blog-archive page-wrapper">
	<?php require get_template_directory() . '/template-parts/posts/layout.php'; ?>
</div>

<?php get_footer(); ?>

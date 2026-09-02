<?php
/**
 * Category Template Router
 *
 * @package WebDaiTin
 */

$queried_term        = get_queried_object();
$current_category_id = ( $queried_term && ! is_wp_error( $queried_term ) && isset( $queried_term->term_id ) ) ? (int) $queried_term->term_id : 0;
$legacy_root_id      = 166;

// 1. Root Category 166 -> Keep custom section layout (category-166.php)
if ( $current_category_id === $legacy_root_id ) {
	require __DIR__ . '/category-166.php';
	return;
}

// Check if current category is a subcategory (descendant) of 166
$ancestor_ids     = $current_category_id ? get_ancestors( $current_category_id, 'category' ) : array();
$is_subcat_of_166 = in_array( $legacy_root_id, $ancestor_ids, true );

// 2. Subcategories of Category 166 -> Default Flatsome parent layout
if ( $is_subcat_of_166 ) {
	require __DIR__ . '/category-default.php';
	return;
}

// 3. Other categories (e.g. thue-xe-theo-cho) -> Custom child theme right sidebar layout
get_header();
get_template_part( 'template-parts/posts/layout', 'right-sidebar' );
get_footer();


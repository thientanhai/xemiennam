<?php
$queried_term        = get_queried_object();
$current_category_id = ( $queried_term && ! is_wp_error( $queried_term ) && isset( $queried_term->term_id ) ) ? (int) $queried_term->term_id : 0;
$legacy_root_id      = 166;

// Only root Category 166 uses the custom section layout
if ( $current_category_id === $legacy_root_id ) {
	require __DIR__ . '/category-166.php';
	return;
}

// Subcategories & other categories show the default layout
require __DIR__ . '/category-default.php';

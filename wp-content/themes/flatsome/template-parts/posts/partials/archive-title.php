<?php
/**
 * Post archive title.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

?>
<header class="archive-page-header">
	<div class="row">
		<div class="large-12 text-left col">
			<?php
			if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
				rank_math_the_breadcrumbs();
			}
			?>
			<h1 class="page-title is-large uppercase">
				<?php

				if ( is_category() ) :

					single_cat_title();

				elseif ( is_tag() ) :

					single_tag_title();

				elseif ( is_search() ) :

					echo get_search_query();

				elseif ( is_author() ) :

					the_post();
					echo get_the_author();
					rewind_posts();

				elseif ( is_day() ) :

					echo get_the_date();

				elseif ( is_month() ) :

					echo get_the_date( 'F Y' );

				elseif ( is_year() ) :

					echo get_the_date( 'Y' );

				elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :

					_e( 'Asides', 'flatsome' );

				elseif ( is_tax( 'post_format', 'post-format-image' ) ) :

					_e( 'Images', 'flatsome' );

				elseif ( is_tax( 'post_format', 'post-format-video' ) ) :

					_e( 'Videos', 'flatsome' );

				elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :

					_e( 'Quotes', 'flatsome' );

				elseif ( is_tax( 'post_format', 'post-format-link' ) ) :

					_e( 'Links', 'flatsome' );

				endif;

				?>
			</h1>

			<?php
			if ( is_category() ) :

				$category_description = category_description();

				if ( ! empty( $category_description ) ) :
					echo apply_filters(
						'category_archive_meta',
						'<div class="taxonomy-description">' . $category_description . '</div>'
					);
				endif;

			elseif ( is_tag() ) :

				$tag_description = tag_description();

				if ( ! empty( $tag_description ) ) :
					echo apply_filters(
						'tag_archive_meta',
						'<div class="taxonomy-description">' . $tag_description . '</div>'
					);
				endif;

			endif;
			?>
		</div>
	</div>
</header>
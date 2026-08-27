<?php
/**
 * Template Name: Trang chọn bài theo mục
 * Template Post Type: page
 *
 * Trang tuỳ chỉnh: mỗi "mục" là 1 khối bài viết được chọn tay
 * qua ACF Relationship field (field group: page_sections).
 *
 * @package Flatsome\Templates
 */

get_header();
?>

<?php do_action( 'flatsome_before_content' ); ?>

<div class="container muc-page-wrap">
	<div class="row">
		<div class="large-12 col">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<?php if ( get_the_content() ) : ?>
					<div class="muc-page-intro">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>

			<?php endwhile; endif; ?>

			<?php if ( have_rows( 'page_sections' ) ) : ?>

				<?php while ( have_rows( 'page_sections' ) ) : the_row();

					$flatsome_section_title = get_sub_field( 'section_title' );
					$flatsome_section_posts = get_sub_field( 'section_posts' ); // mảng post ID
					$flatsome_section_link  = get_sub_field( 'section_link_url' );
					$flatsome_section_link_text = get_sub_field( 'section_link_text' ) ?: 'Xem tất cả';

					if ( empty( $flatsome_section_posts ) ) {
						continue;
					}
				?>

				<div class="muc-section">

					<div class="muc-section__head">
						<div class="muc-section__title-wrap">
							<span class="line"></span>
							<h2 class="muc-section__title"><?php echo esc_html( $flatsome_section_title ); ?></h2>
							<span class="line"></span>
						</div>
						<?php if ( $flatsome_section_link ) : ?>
							<a href="<?php echo esc_url( $flatsome_section_link ); ?>" class="muc-section__viewall">
								<?php echo esc_html( $flatsome_section_link_text ); ?> <i class="icon-angle-right"></i>
							</a>
						<?php endif; ?>
					</div>

					<div class="row row-small muc-posts-grid">
						<?php foreach ( $flatsome_section_posts as $flatsome_post_id ) :
							$flatsome_post_cats = get_the_category( $flatsome_post_id );
						?>
						<div class="col small-6 large-3">
							<div class="muc-post-item">
								<a href="<?php echo esc_url( get_permalink( $flatsome_post_id ) ); ?>" class="muc-post-thumb">
									<?php echo get_the_post_thumbnail( $flatsome_post_id, 'medium' ); ?>
								</a>
								<div class="muc-post-meta">
									<?php if ( ! empty( $flatsome_post_cats ) ) : ?>
										<span class="muc-post-cat"><i class="icon-folder"></i> <?php echo esc_html( $flatsome_post_cats[0]->name ); ?></span>
									<?php endif; ?>
									<span class="muc-post-date"><i class="icon-calendar"></i> <?php echo esc_html( get_the_date( 'd-m-Y', $flatsome_post_id ) ); ?></span>
								</div>
								<h4 class="muc-post-title">
									<a href="<?php echo esc_url( get_permalink( $flatsome_post_id ) ); ?>"><?php echo esc_html( get_the_title( $flatsome_post_id ) ); ?></a>
								</h4>
								<div class="muc-post-excerpt">
									<?php echo esc_html( wp_trim_words( get_the_excerpt( $flatsome_post_id ), 20 ) ); ?>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>

				</div><!-- /.muc-section -->

				<?php endwhile; ?>

			<?php else : ?>

				<p><?php esc_html_e( 'Chưa có mục bài viết nào được cấu hình. Vào sửa trang này và thêm mục ở phần "Các mục bài viết" phía dưới.', 'flatsome-child' ); ?></p>

			<?php endif; ?>

		</div>
	</div>
</div>

<?php do_action( 'flatsome_after_content' ); ?>

<style>
.muc-page-wrap{padding-top:30px;padding-bottom:10px;}
.muc-page-intro{margin-bottom:30px;}
.muc-section{background:#fff;padding:30px;border-radius:4px;box-shadow:0 0 15px rgba(0,0,0,.05);margin-bottom:30px;}
.muc-section__head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:25px;flex-wrap:wrap;}
.muc-section__title-wrap{display:flex;align-items:center;gap:20px;flex:1;min-width:220px;}
.muc-section__title{margin:0;white-space:nowrap;font-weight:700;letter-spacing:.5px;}
.muc-section__title-wrap .line{flex:1;height:1px;background:#ddd;}
.muc-section__viewall{white-space:nowrap;font-weight:600;font-size:13px;color:var(--primary-color,#1573ba);}
.muc-section__viewall:hover{opacity:.8;}
.muc-post-item{margin-bottom:20px;}
.muc-post-thumb{display:block;overflow:hidden;border-radius:4px;margin-bottom:12px;}
.muc-post-thumb img{width:100%;height:150px;object-fit:cover;display:block;transition:transform .35s ease;}
.muc-post-thumb:hover img{transform:scale(1.06);}
.muc-post-meta{font-size:12px;color:#999;margin-bottom:8px;display:flex;gap:15px;flex-wrap:wrap;}
.muc-post-title{font-size:15px;font-weight:700;line-height:1.4;margin:0 0 8px;}
.muc-post-title a{color:#222;}
.muc-post-title a:hover{color:var(--primary-color,#1573ba);}
.muc-post-excerpt{font-size:13px;color:#777;line-height:1.5;}
</style>

<?php get_footer(); ?>
<?php
/**
 * Template part: Lưới danh mục con (child categories grid)
 * Hiển thị khi đang ở trang category cha, có danh mục con.
 * Nhấn vào 1 ô sẽ chuyển sang trang category con và hiển thị list bài viết bình thường.
 */

$flatsome_current_cat = get_queried_object();
$flatsome_child_cats  = get_categories( array(
	'parent'     => $flatsome_current_cat->term_id,
	'hide_empty' => false,
) );

// Ảnh mặc định nếu category con chưa gán ảnh riêng
$flatsome_default_cat_img = get_template_directory_uri() . '/assets/images/default-category.jpg';
?>

<div class="child-cat-grid row row-small">
	<?php foreach ( $flatsome_child_cats as $flatsome_child ) : ?>
		<?php
		// Lấy ảnh danh mục con đã upload ở màn hình Sửa danh mục (Categories) trong wp-admin
		$flatsome_thumb_id = get_term_meta( $flatsome_child->term_id, 'category_thumbnail_id', true );
		$flatsome_img_url  = $flatsome_thumb_id ? wp_get_attachment_image_url( $flatsome_thumb_id, 'medium_large' ) : '';
		if ( ! $flatsome_img_url ) {
			$flatsome_img_url = $flatsome_default_cat_img;
		}
		?>
		<div class="col small-6 large-4">
			<a href="<?php echo esc_url( get_category_link( $flatsome_child->term_id ) ); ?>" class="child-cat-item">
				<div class="child-cat-item__bg" style="background-image:url('<?php echo esc_url( $flatsome_img_url ); ?>');"></div>
				<div class="child-cat-item__overlay"></div>
				<span class="child-cat-item__title"><?php echo esc_html( $flatsome_child->name ); ?></span>
			</a>
		</div>
	<?php endforeach; ?>
</div>



<style>
.child-cat-grid {
	margin-bottom: 40px;
}
.child-cat-item {
	position: relative;
	display: block;
	height: 190px;
	border-radius: 4px;
	overflow: hidden;
	margin-bottom: 20px;
	text-decoration: none;
}
.child-cat-item__bg {
	position: absolute;
	top: 0; left: 0; right: 0; bottom: 0;
	background-size: cover;
	background-position: center;
	transition: transform .35s ease;
}
.child-cat-item:hover .child-cat-item__bg {
	transform: scale(1.06);
}
.child-cat-item__overlay {
	position: absolute;
	top: 0; left: 0; right: 0; bottom: 0;
	background: rgba(0,0,0,0.45);
}
.child-cat-item__title {
	position: absolute;
	left: 0; right: 0; bottom: 0; top: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	text-align: center;
	color: #fff;
	font-weight: 700;
	font-size: 20px;
	text-transform: uppercase;
	letter-spacing: .5px;
	padding: 10px;
	line-height: 1.3;
}
@media (max-width: 549px) {
	.child-cat-item {
		height: 150px;
	}
	.child-cat-item__title {
		font-size: 16px;
	}
}
</style>
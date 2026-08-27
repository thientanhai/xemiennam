<?php
/**
 * Template part: Sidebar "Báo giá thuê xe" — dùng Contact Form 7 có sẵn.
 * Thay cho get_sidebar() mặc định ở trang category cha.
 */
?>
<div class="quote-sidebar-box">
	<div class="quote-sidebar-box__head">BÁO GIÁ THUÊ XE</div>
	<div class="quote-sidebar-box__body">
		<?php echo do_shortcode( '[contact-form-7 id="cedbca4" title="Liên hệ - Đặt xe"]' ); ?>
	</div>
</div>

<style>
.quote-sidebar-box{background:#fff;border-radius:6px;overflow:hidden;box-shadow:0 0 15px rgba(0,0,0,.08);margin-bottom:30px;}
.quote-sidebar-box__head{background:var(--primary-color,#1573ba);color:#fff;text-align:center;font-weight:800;letter-spacing:.5px;padding:16px;font-size:15px;}
.quote-sidebar-box__body{padding:20px;}
.quote-sidebar-box__body .wpcf7 form p{margin-bottom:14px;}
.quote-sidebar-box__body .wpcf7-submit{width:100%;background:var(--primary-color,#1573ba)!important;color:#fff!important;border:none;padding:12px;border-radius:5px;font-weight:700;cursor:pointer;}
.quote-sidebar-box__body .wpcf7-submit:hover{opacity:.9;}
</style>

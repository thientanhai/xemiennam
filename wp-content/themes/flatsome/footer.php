<?php
/**
 * The template for displaying the footer.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

global $flatsome_opt;
?>

</main>
<?php echo do_shortcode('[lightbox id="form-bao-gia" width="600px" padding="0"][contact-form-7 id="cedbca4" title="Liên hệ - Đặt xe"][/lightbox]'); ?>
<footer id="footer" class="footer-wrapper">

	<?php do_action('flatsome_footer'); ?>

</footer>

</div>

<?php wp_footer(); ?>

</body>
</html>

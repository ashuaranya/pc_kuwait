<?php
/**
 * Add to Quote button template
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 1.5.3
 * @author  YITH
 *
 * @var $product_id integer
 * @var $wpnonce string
 */

?>

<a href="#" class="<?php echo esc_attr( $class ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-wp_nonce="<?php echo esc_attr( $wpnonce ); ?>">
	<?php echo wp_kses_post( $label ); ?>
</a>
<img src="<?php echo esc_url( ywraq_get_ajax_default_loader() ); ?>" class="ajax-loading" alt="loading" width="16" height="16" style="visibility:hidden" />

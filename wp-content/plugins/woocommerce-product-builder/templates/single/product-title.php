<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $product;
?>
<div class="woopb-product-title">
    <a href="<?php echo esc_url( $product->get_permalink() ) ?>">
<!--		--><?php //the_title() ?>
		<?php echo esc_html($product->get_name()) ?>
    </a>
</div>

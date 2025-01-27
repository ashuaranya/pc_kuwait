<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	$ctw = apply_filters( 'accept_external_type', false );
	if ( ! $ctw ) {
		return;
	}
}

echo wc_get_stock_html( $product );
if ( $product->is_in_stock() ) : ?>

    <form class="cart" action="#<?php //echo esc_url( @add_query_arg( array() ) ) ?>" method="post">
		<?php
		wp_nonce_field( '_woopb_add_to_cart', '_nonce' );
		do_action( 'woocommerce_product_builder_quantity_field', $product, $post_id );
		?>
        <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
        <button type="submit" name="woopb-add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
                class="button alt"><?php echo esc_html__( 'Select', 'woocommerce-product-builder' ) ?></button>

    </form>

<?php endif;
//single_add_to_cart_button
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$message_success = $settings->get_message_success();
$back_url        = get_the_permalink();

global $post;
$post_id = is_woopb_shortcode() ? $id : $post->ID;
?>

<div class="woocommerce-product-builder">
    <form method="POST" action="<?php echo apply_filters( 'woopb_redirect_link_after_add_to_cart', wc_get_cart_url() ) ?>" class="woopb-form">
		<?php wp_nonce_field( '_woopb_add_to_woocommerce', '_nonce' ) ?>
        <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
        <h2><?php esc_html_e( 'Your chosen list', 'woocommerce-product-builder' ); ?></h2>
		<?php
		if ( is_array( $products ) && count( $products ) ) {
		?>
        <table class="woocommerce-product-builder-table">
            <thead>
            <tr>
                <th width="55%"><?php esc_html_e( 'Product', 'woocommerce-product-builder' ) ?></th>
                <th width="15%"><?php esc_html_e( 'Price', 'woocommerce-product-builder' ) ?></th>
                <th width="15%"><?php esc_html_e( 'Total', 'woocommerce-product-builder' ) ?></th>
                <th width="15%"></th>
            </tr>
            </thead>
            <tbody>
			<?php
			$index = 1;
			$total = $final_total = 0;
			foreach ( $products as $step_id => $items ) {
				foreach ( $items as $product_id => $detail ) {
					$product       = wc_get_product( $product_id );
					$attr          = $product->is_type( 'variation' ) ? ' (' . implode( ', ', $detail['attributes'] ) . ') ' : '';
					$product_title = $product->get_title() . $attr;
					$prd_des       = $product->get_short_description();
					if ( ! empty( get_the_post_thumbnail( $product_id ) ) ) {
						$prd_thumbnail = get_the_post_thumbnail( $product_id, 'thumbnail' );
					} else {
						$prd_thumbnail = wc_placeholder_img();
					}
					$product_price = wc_get_price_to_display( $product );
					?>
                    <tr>
                        <td class="woopb-preview-product-col">
							<?php echo $prd_thumbnail; ?>
                            <a target="_blank" href="<?php echo get_permalink( $product_id ); ?>" class="vi-chosen_title">
								<?php echo esc_html( $product_title ); ?>
                                x
								<?php echo esc_html( $detail['quantity'] ) ?>
                            </a>
                        </td>
                        <td><?php echo $product->get_price_html() ?></td>

                        <td class="woopb-total">
							<?php echo wc_price( ( $product_price * $detail['quantity'] ) ); ?>
                        </td>
                        <td>
							<?php do_action( 'link_external_button', $product_id ) ?>
							<?php
							$param = get_post_meta( $post_id, 'woopb-param', true );
							if ( ! isset( $param['require_product'] ) || ! $param['require_product'] ) {
								$arg_remove = array( 'stepp' => $step_id, 'product_id' => $product_id, 'post_id' => $post_id );
								?>
                                <a class="woopb-step-product-added-remove"
                                   href="<?php echo wp_nonce_url( add_query_arg( $arg_remove ), '_woopb_remove_product_step', '_nonce' ) ?>"></a>
							<?php } ?>
                        </td>
                    </tr>
					<?php
					$total       = $total + intval( $product_price );
					$final_total = $final_total + intval( $product_price ) * intval( $detail['quantity'] );
				}
			} ?>
            </tbody>
            <tfoot>
				 
					<?php
			$_SESSION['customize_pc_total'] = '';
			if($_SESSION['isAssembled'] == 'checked'){ 
				global $wpdb;
				$table = $wpdb->prefix.'charges';
				$sql = $wpdb->get_results("SELECT * FROM $table");
				foreach ($sql as $value) {
					if($final_total >= $value->min_amount && $final_total <= $value->max_amount){
						echo '<br>';
						$charges = $value->charges;
						$final_total = apply_filters('get_assemble_price_preview', $final_total, $charges);
						$_SESSION['customize_pc_total'] =  $final_total;
					}
				}
			?>
			<?php } ?>
				<tr class="woopb-total-preview-custom">
                	<th colspan="2" style="text-align: center"><?php esc_html_e( 'Total', 'woocommerce-product-builder' ) ?></th>
                	<th colspan="2"><?php printf( wc_price( $final_total ) ) ?></th>
            	</tr>         
            </tfoot>
			<?php //do_action( 'woopb_after_preview_table', $final_total ); ?>
        </table>
		<?php
		if ( $settings->get_share_link_enable() ) {
			?>
            <div class="woopb-share">
                <div class="woopb-field">
                    <label class="woopb-share-label"><?php esc_html_e( 'Share', 'woocommerce-product-builder' ) ?></label>
                    <input type="text" class="woopb-share-link" readonly value="<?php echo esc_url( $settings->get_share_link() ) ?>">
                </div>
            </div>
		<?php } ?>

        <div class="woopb-buttons-group">
            <div class="woopb-button-group group-1">
				<?php
				$btn = "<button name='woopb_add_to_cart' class='woopb-button woopb-button-primary woopb-add-to-cart-btn'>" . __( 'Add to cart', 'woocommerce-product-builder' ) . "</button>";
				printf( apply_filters( 'woopb_add_to_cart_button', $btn ) );
				?>
            </div>
            <div class="woopb-button-group group-2">
                <a href="<?php echo esc_url( $back_url ); ?>"
                   class="woopb-button"><?php esc_attr_e( 'Back', 'woocommerce-product-builder' ) ?></a>

				<?php
				if ( $settings->enable_email() ) { ?>
                    <a href="#" id="vi_wpb_sendtofriend" class="woopb-button"><?php esc_attr_e( 'Send email to your friend', 'woocommerce-product-builder' ) ?></a>
				<?php }

				if ( current_user_can( 'manage_options' ) || $settings->get_param( 'get_short_share_link' ) ) {
					$short_link_id = wc()->session->get( 'woopb_edit_short_link' );
					if ( current_user_can( 'manage_options' ) && $short_link_id ) {
						?>
                        <button type='submit' name="woopb_save_edit_short_link" value="<?php echo esc_attr( $short_link_id ) ?>" class='woopb-button'>
							<?php esc_html_e( 'Save short link edited', 'woocommerce-product-builder' ); ?>
                        </button>
						<?php
					}
					?>
                    <div class="woopb-get-short-share-link">
                        <button type='button' id='vi-wpb-get-short-share-link' class='woopb-button'>
							<?php esc_html_e( 'Get short link', 'woocommerce-product-builder' ); ?>
                        </button>
                        <div class="woopb-short-share-link">
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
			<?php } ?>
        </div>
    </form>
</div>

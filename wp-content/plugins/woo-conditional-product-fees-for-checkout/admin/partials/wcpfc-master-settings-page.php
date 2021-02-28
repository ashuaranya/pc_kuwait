<?php
// If this file is called directly, abort.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
?>
<div class="wcpfc-mastersettings">
			<div class="mastersettings-title">
				<h2><?php esc_html_e( 'Master Settings', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h2>
			</div>
			<?php
			$chk_enable_logging         = get_option( 'chk_enable_logging' );
			$chk_enable_coupon_fee         = get_option( 'chk_enable_coupon_fee' );
			$chk_enable_custom_fun         = get_option( 'chk_enable_custom_fun' );

			$chk_enable_logging_checked = ( ( ! empty( $chk_enable_logging ) && 'on' === $chk_enable_logging ) || empty( $chk_enable_logging ) ) ? 'checked' : '';
			$chk_enable_coupon_fee_checked = ( ( ! empty( $chk_enable_coupon_fee ) && 'on' === $chk_enable_coupon_fee ) ) ? 'checked' : '';
			$chk_enable_custom_fun_checked = ( ( ! empty( $chk_enable_custom_fun ) && 'on' === $chk_enable_custom_fun ) ) ? 'checked' : '';

			?>
			<table class="table-mastersettings table-outer" cellpadding="0" cellspacing="0">
				<tbody>
				<tr valign="top" id="enable_logging">
					<td class="table-whattodo"><?php esc_html_e( 'Enable Logging', 'woocommerce-conditional-product-fees-for-checkout' ); ?></td>
					<td>
						<input type="checkbox" name="chk_enable_logging" id="chk_enable_logging"
						       value="on" <?php echo esc_attr( $chk_enable_logging_checked ); ?>>
					</td>
				</tr>
				<tr valign="top" id="enable_coupon_fee">
					<td class="table-whattodo"><?php esc_html_e( 'Remove fees once a 100% discount applies.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></td>
					<td>
						<input type="checkbox" name="chk_enable_coupon_fee" id="chk_enable_coupon_fee"
						       value="on" <?php echo esc_attr( $chk_enable_coupon_fee_checked ); ?>>
					</td>
				</tr>
				<tr valign="top" id="enable_logging">
					<td class="table-whattodo"><?php esc_html_e( 'Display all fees in one label', 'woocommerce-conditional-product-fees-for-checkout' ); ?></td>
					<td>
						<input type="checkbox" name="chk_enable_custom_fun" id="chk_enable_custom_fun"
						       value="on" <?php echo esc_attr( $chk_enable_custom_fun_checked ); ?>>
					</td>
				</tr>
				<tr>
					<td colspan="2">
                        <span class="button-primary" id="save_master_settings"
                              name="save_master_settings"><?php esc_html_e( 'Save Master Settings', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
<?php

namespace ZAddons;

use ZAddons\Model\Addon;

class Addons {
	const DEFAULT_HEADER_TEXT = "Checkout Add-ons";
	const CHECKOUT_ADDON_NAMESPACE = "ZProductAddons";
	const CUSTOMIZE_ADDON_NAMESPACE = "ZAddonsCustomize";
	const PRODUCT_RESTRICTION_ADDON_NAMESPACE = "zProductRestriction";

	public function __construct() {
		add_action( 'wp_ajax_zaddon_save_header_text', [ $this, 'save_header_text' ] );
	}

	public static function is_active_add_on( $namespace, $not_enabled = false ) {
		$active = "\\{$namespace}\\ACTIVE";
		$settings = "\\{$namespace}\\Settings";

		return ( defined( $active ) && constant( $active ) ) ? ( $not_enabled ? true : $settings::is_plugin_enabled() ) : false;
	}

	public static function get_header_text_of( $type = 'cart' ) {
		return get_checkout_addon_option( "zac_header_text_${type}", self::DEFAULT_HEADER_TEXT );
	}

	public static function get_all_add_ons() {
		return [
			new Addon(
				__( 'Product Add-ons Plus', 'product-add-ons-woocommerce' ),
				__( 'Add advanced functionality for Product Add-ons to enable more features',
					'product-add-ons-woocommerce' ),
				self::CUSTOMIZE_ADDON_NAMESPACE,
				'https://www.bizswoop.com/wp/productaddons/plus'
			),
			new Addon(
				__( 'Product Checkout Add-Ons', 'product-add-ons-woocommerce' ),
				__( 'Add Customized Product Checkout Add-Ons for WooCommerce', 'product-add-ons-woocommerce' ),
				self::CHECKOUT_ADDON_NAMESPACE,
				'https://www.bizswoop.com/wp/productaddons/checkout'
			),
			new Addon(
				__( 'Product & Order Restrictions', 'product-add-ons-woocommerce' ),
				__( 'Easily setup checkout product restrictions and order frequency restrictions',
					'product-add-ons-woocommerce' ),
				self::PRODUCT_RESTRICTION_ADDON_NAMESPACE,
				'https://www.bizswoop.com/wp/productaddons/restrictions'
			),
			new Addon(
				__( 'Product Samples', 'product-add-ons-woocommerce' ),
				__( 'Easily add product samples features and functionality for products',
					'product-add-ons-woocommerce' ),
				'',
				'https://www.bizswoop.com/wp/productaddons/samples'
			),
		];
	}

}

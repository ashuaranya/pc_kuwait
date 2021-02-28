<?php

/**
 * Plugin Name: Product Builder for WooCommerce
 * Plugin URI: https://villatheme.com/
 * Description: Increases sales with Building product configuration for your online store. Help build a complete product from small components
 * Version: 1.0.3
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Requires at least: 4.4
 * Tested up to: 5.5
 * WC requires at least: 3.5
 * WC tested up to: 4.7
 * Text Domain: woo-product-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VI_WPRODUCTBUILDER_F_VERSION', '1.0.3' );
/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (  is_plugin_active( 'woocommerce-product-builder/woocommerce-product-builder.php' ) ) {
	return;
}
if ( ! class_exists( 'VI_WPRODUCTBUILDER_F' ) ) {

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woo-product-builder" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
		require_once $init_file;
	}

	class VI_WPRODUCTBUILDER_F {
		public function __construct() {
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
			add_action( 'admin_notices', array( $this, 'global_note' ) );
		}


		/**
		 * Warring: WooCommerce is not active
		 */
		function global_note() {
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				deactivate_plugins( 'woocommerce-product-builder/woocommerce-product-builder.php' );
				unset( $_GET['activate'] );
				?>
				<div id="message" class="error">
					<p><?php _e( 'Please install WooCommerce and active. Product Builder for WooCommerce is going to working.', 'woo-product-builder' ); ?></p>
				</div>
				<?php
			}
		}

		public function install() {
			global $wp_version;
			if ( version_compare( $wp_version, "2.9", "<" ) ) {
				deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
				wp_die( "This plugin requires WordPress version 2.9 or higher." );
			}
			flush_rewrite_rules();
		}

		public function uninstall() {
			flush_rewrite_rules();
		}
	}

	new VI_WPRODUCTBUILDER_F();
}
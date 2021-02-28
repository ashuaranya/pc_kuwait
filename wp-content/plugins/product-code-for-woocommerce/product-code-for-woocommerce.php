<?php
/**
 * Product Code for WooCommerce plugin.
 *
 * @package PcfWooCommerce
 * @wordpress-plugin
 *
 * Plugin Name: Product Code for WooCommerce
 * Plugin URI: http://wordpress.org/plugins/product-code-for-woocommerce
 * Description: Plugin provides a unique internal product identifier in addition to the GTIN, EAN, SKU and UPC throughout the order process. A secondary product code field can be activated from setup.
 * Version: 1.2.4
 * Author: Artios Media
 * Author URI: http://www.artiosmedia.com
 * Developer: westerndeal (email : info@westerndeal.com).
 * Copyright: © 2018-2020 Artios Media (email : steven@artiosmedia.com).
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: product-code-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 3.2.0
 * WC tested up to: 4.8.0
 */

namespace Artiosmedia\WC_Product_Code;

define( 'PRODUCT_CODE_URL', plugins_url( '', __FILE__ ) );
define( 'PRODUCT_CODE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_CODE_FIELD_NAME', '_product_code' );
define( 'PRODUCT_CODE_FIELD_NAME_SECOND', '_product_code_second' );

define( 'PRODUCT_CODE_TEMPLATE_PATH', __DIR__ . '/templates' );
define( 'PRODUCT_CODE_PAYPAL_ID', 'E7LS2JGFPLTH2' );

define( 'PRODUCT_CODE_VERSION', '1.2.4' );
define( 'PRODUCT_CODE_DB_VERSION', '1.2.4' );

load_plugin_textdomain( 'product-code-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' );

require_once( __DIR__ . '/vendor/autoload.php' );

if ( ! class_exists( 'PCFW_Services' ) ) {
    include( PRODUCT_CODE_PATH . 'classes/class-pcfw-services.php' );
}

if ( ! class_exists( 'PCFW_Admin_settings' ) ) {
    include( PRODUCT_CODE_PATH . 'classes/class-pcfw-admin-settings.php' );
}
if ( ! class_exists( 'PCFW_wc_export_filter' ) ) {
    include( PRODUCT_CODE_PATH . 'modules/export/pcfw-export-support.php' );
}




new PCFW_Services();

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    $settings = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=products&section=product_code_settings' ), __( 'Settings', 'product-code-for-woocommerce' ) );

    array_unshift( $links, $settings );
    return $links;
} );

register_activation_hook( __FILE__, function() {
    // Must Be deleted on other update ,
    delete_option( 'product_code_update' );

    $show_product = get_option( 'product_code' );
    if ( ! $show_product )
	add_option( 'product_code', 'yes' );
    add_option( 'product_code_text', 'Product Code' );

    update_option( "product_code_notice_dismiss", date( 'Y-m-d', strtotime( '+30 days' ) ) );
} );

register_deactivation_hook( __FILE__, function() {
    delete_option( 'product_code' );
} );

// run on upgrade
add_action( 'admin_init', function() {


    if ( PRODUCT_CODE_VERSION === "1.0.6" && ( ! $upgrade_db ) ) {
	update_option( "product_code_notice_dismiss", date( 'Y-m-d', strtotime( '+30 days' ) ) );
	update_option( "product_code_update", true );
    }

    if ( PRODUCT_CODE_VERSION === "1.0.7" ) {
	$product_text = get_option( 'product_code_text' );
	if ( ! $product_text ) {
	    update_option( 'product_code_text', 'Product Code' );
	}
    }

    $plugin_options = get_site_option( 'product_code_info' );
    $upgrade_db	 = get_option( "product_code_db_updated" );
    
    // Check if postmeta data is changed to the new name.
    Global $wpdb;
    $query  = "SELECT * from " . $wpdb->prefix . "postmeta WHERE meta_key='_product_code_variant' ";
    $results = $wpdb->get_results( $query );

    // If plugin version is less then 1.2.0 then upgrade and if not update database after version upgrade still display the notice.
    if ( ( $plugin_options[ 'version' ] < "1.2.2" && ( ! $upgrade_db ) ) || ( ! empty( $results ) )  ) {
	add_action( "admin_notices", function() {
	    $url = admin_url( 'admin-ajax.php' );
	    ?>
	    <div class="warning notice">
	        <p><?php _e( '<strong>NOTICE! Product Code For WooCommerce</strong> must update your database to modify meta fields. Consider backing up database first.  <a class="button button-primary" href="' . $url . '?action=product_code_update_database" target="_blank">Update Now</a>', 'product-code-for-woocommerce' ); ?></p>
	    </div>
	    <?php
	} );
    }

    // update the version value
    $product_code_info = array(
	'version'	 => PRODUCT_CODE_VERSION,
	'db_version'	 => PRODUCT_CODE_DB_VERSION
    );
    update_site_option( 'product_code_info', $product_code_info );
} );

// Include files for search
add_filter( 'init', function() {
    include_once PRODUCT_CODE_PATH . 'modules/search/pcfw-filter-search.php';
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( ! is_admin() && is_plugin_active( 'relevanssi/relevanssi.php' ) || ! function_exists( 'wc_clean' ) ) {
	include_once PRODUCT_CODE_PATH . 'modules/search/pcfw-relevanssi-product-code-search.php';
    } else
    if ( ! is_admin() && is_plugin_active( 'searchwp/searchwp.php' ) && is_plugin_active( 'searchwp-woocommerce/searchwp-woocommerce.php' ) ) {
	include_once PRODUCT_CODE_PATH . 'modules/search/pcfw-searchwp-product-code-search.php';
    } else {
	include_once PRODUCT_CODE_PATH . 'modules/search/pcfw-product-code-search.php';
    }
}, 11 );

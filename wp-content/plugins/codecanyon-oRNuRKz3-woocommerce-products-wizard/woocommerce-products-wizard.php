<?php
/**
 * Plugin Name: WooCommerce Products Wizard
 * Description: This plugin helps you sell your products by the step-by-step wizard.
 * Version: 9.1.3
 * Author: troll_winner@mail.ru
 * Author URI: troll_winner@mail.ru
 */

namespace {

    $uploadDir = wp_upload_dir();

    define('WC_PRODUCTS_WIZARD_VERSION', '9.1.3');

    if (!defined('WC_PRODUCTS_WIZARD_DEBUG')) {
        if (defined('SCRIPT_DEBUG')) {
            define('WC_PRODUCTS_WIZARD_DEBUG', SCRIPT_DEBUG);
        } else {
            define('WC_PRODUCTS_WIZARD_DEBUG', false);
        }
    }

    if (!defined('WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR')) {
        define('WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR', 'woocommerce-products-wizard');
    }

    if (!defined('WC_PRODUCTS_WIZARD_PLUGIN_PATH')) {
        define('WC_PRODUCTS_WIZARD_PLUGIN_PATH', plugin_dir_path(__FILE__));
    }

    if (!defined('WC_PRODUCTS_WIZARD_PLUGIN_URL')) {
        define('WC_PRODUCTS_WIZARD_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    if (!defined('WC_PRODUCTS_WIZARD_UPLOADS_PATH')) {
        define(
            'WC_PRODUCTS_WIZARD_UPLOADS_PATH',
            $uploadDir['basedir'] . DIRECTORY_SEPARATOR . 'woocommerce-products-wizard' . DIRECTORY_SEPARATOR
        );
    }

    if (!defined('WC_PRODUCTS_WIZARD_UPLOADS_URL')) {
        define(
            'WC_PRODUCTS_WIZARD_UPLOADS_URL',
            $uploadDir['baseurl'] . DIRECTORY_SEPARATOR . 'woocommerce-products-wizard' . DIRECTORY_SEPARATOR
        );
    }

    require_once('includes/classes/Core.php');
    require_once('includes/global/legacy.php');
    require_once('includes/global/shortcodes.php');
}

namespace WCProductsWizard {

    function Instance()
    {
        return Core::instance();
    }

    $GLOBALS['woocommerceProductsWizard'] = Instance();
}

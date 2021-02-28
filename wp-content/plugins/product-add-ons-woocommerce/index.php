<?php
/**
 * Plugin Name: Product Add-Ons for WooCommerce
 * Plugin URI: https://www.bizswoop.com/wp/productaddons
 * Description: Add Customized Product Add-Ons Support for WooCommerce
 * Version: 2.1.11
 * Text Domain: product-add-ons-woocommerce
 * Domain Path: /lang
 * WC requires at least: 2.4.0
 * WC tested up to: 4.8.0
 * Author: BizSwoop a CPF Concepts, LLC Brand
 * Author URI: http://www.bizswoop.com
 */

namespace ZAddons;
const ACTIVE = true;
const PLUGIN_ROOT = __DIR__;
const PLUGIN_ROOT_FILE = __FILE__;
const REST_NAMESPACE = 'wc-zaddons';

spl_autoload_register(function ($name) {
	$name = explode('\\', $name);
	$name[0] = 'includes';
	$path = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $name) . '.php';

	if (file_exists($path)) {
		require_once $path;
	}
}, false);

new Setup();

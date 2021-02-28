<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs(['product' => null]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$productType = $product->get_type();
$view = $productType;

// WooCommerce Subscriptions plugin support
$viewsAliases = [
    'variable-subscription' => 'variable',
    'subscription' => 'simple'
];

if (isset($viewsAliases[$productType])) {
    $view = $viewsAliases[$productType];
}

do_action('woocommerce_before_add_to_cart_form');

Template::html("form/item/prototype/footer/{$view}", $arguments);

do_action('woocommerce_after_add_to_cart_form');

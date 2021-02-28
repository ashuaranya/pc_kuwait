<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Cart;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'itemTemplate' => 'form/item/type-2',
    'queryArgs' => []
]);

$productsQuery = new WP_Query($arguments['queryArgs']);

echo '<div class="woocommerce-products-wizard-form-layout is-list products">';

while ($productsQuery->have_posts()) {
    $productsQuery->the_post();

    global $product;

    if (!$product instanceof WC_Product) {
        continue;
    }

    $arguments['product'] = $product;
    $arguments['cartItem'] = Cart::getProductById($arguments['id'], $product->get_id(), $arguments['stepId']);

    // EPO product default data pass
    $_POST = !empty($arguments['cartItem']) ? $arguments['cartItem']['request'] : [];

    Template::html($arguments['itemTemplate'], $arguments);
}

echo '</div>';

Template::html('form/pagination', array_merge(['productsQuery' => $productsQuery], $arguments));

$productsQuery->reset_postdata();

<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'itemTemplate' => 'form/item/type-1',
    'queryArgs' => [],
    'grid' => Settings::getStep($id, $stepId, 'grid_column'),
    'gridWithSidebar' => Settings::getStep($id, $stepId, 'grid_with_sidebar_column'),
    'showSidebar' => Settings::isSidebarShowed($id)
]);

$loop = 1;
$colClasses = [];
$gridColumn = $arguments['grid'];
$productsQuery = new WP_Query($arguments['queryArgs']);

if ($arguments['showSidebar']) {
    $gridColumn = $arguments['gridWithSidebar'];
}

if (!isset($gridColumn['xxs'])) {
    $gridColumn['xxs'] = 12;
}

$colClasses[] = "col-{$gridColumn['xxs']}";

unset($gridColumn['xxs']);

foreach ($gridColumn as $col => $value) {
    $colClasses[] = "col-{$col}-{$value}";
}

$colClass = implode(' ', $colClasses);

echo '<div class="woocommerce-products-wizard-form-layout is-grid row products">';

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

    echo '<div class="' . $colClass . '">';

    Template::html($arguments['itemTemplate'], $arguments);

    echo '</div>';

    foreach ($gridColumn as $col => $value) {
        echo $loop % (12 / $value) == 0
            ? '<div class="clearfix d-none visible-' . $col . '"></div>'
            : '';
    }

    $loop++;
}

echo '</div>';

Template::html('form/pagination', array_merge(['productsQuery' => $productsQuery], $arguments));

$productsQuery->reset_postdata();

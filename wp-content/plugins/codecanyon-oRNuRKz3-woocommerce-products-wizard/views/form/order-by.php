<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$orderBy = [];

if (isset($_REQUEST['wcpwOrderBy'])) {
    if (is_string($_REQUEST['wcpwOrderBy'])) {
        parse_str($_REQUEST['wcpwOrderBy'], $orderBy);
    } else {
        $orderBy = $_REQUEST['wcpwOrderBy'];
    }
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'enableOrderByDropdown' => Settings::getStep($id, $stepId, 'enable_order_by_dropdown'),
    'orderBy' => isset($orderBy[$stepId]) ? $orderBy[$stepId] : null,
    'orderByItems' => apply_filters(
        'woocommerce_catalog_orderby',
        [
            'menu_order' => esc_html__('Default sorting', 'woocommerce'),
            'popularity' => esc_html__('Sort by popularity', 'woocommerce'),
            'rating' => esc_html__('Sort by average rating', 'woocommerce'),
            'date' => esc_html__('Sort by latest', 'woocommerce'),
            'price' => esc_html__('Sort by price: low to high', 'woocommerce'),
            'price-desc' => esc_html__('Sort by price: high to low', 'woocommerce')
        ]
    )
]);

if (!$arguments['enableOrderByDropdown']) {
    return;
}
?>
<form action="#" method="get"
    class="woocommerce-products-wizard-form-order-by form-inline mb-3"
    data-component="wcpw-form-order-by"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>">
    <label for="woocommerce-products-wizard-form-order-by-<?php echo esc_attr($arguments['id']); ?>"
        class="woocommerce-products-wizard-form-order-by-label my-1 mr-2"><?php
        esc_html_e('Shop order', 'woocommerce');
        ?></label>
    <select name="wcpwOrderBy[<?php echo esc_attr($arguments['stepId']); ?>]"
        id="woocommerce-products-wizard-form-order-by-<?php echo esc_attr($arguments['id']); ?>"
        class="woocommerce-products-wizard-form-order-by-input form-control my-1 mr-2"><?php
        foreach ($arguments['orderByItems'] as $key => $name) {
            echo '<option value="' . esc_html($key) . '" '
                . selected($key, $arguments['orderBy'])
                . '>' . esc_html($name) . '</option>';
        }
        ?></select>
    <noscript>
        <button type="submit" class="woocommerce-products-wizard-form-order-by-submit my-1 btn btn-secondary"><?php
            esc_html_e('Apply', 'woocommerce-products-wizard');
            ?></button>
    </noscript>
    <?php
    // no-js version forms values binding
    if (isset($_GET['wcpwFilter']) && !empty($_GET['wcpwFilter'])) {
        echo '<input type="hidden" name="wcpwFilter" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwFilter']))
            . '">';
    }

    if (isset($_GET['wcpwProductsPerPage']) && !empty($_GET['wcpwProductsPerPage'])) {
        echo '<input type="hidden" name="wcpwProductsPerPage" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwProductsPerPage']))
            . '">';
    }
    ?>
</form>

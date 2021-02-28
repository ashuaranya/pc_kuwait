<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'product' => null,
    'class' => 'woocommerce-products-wizard-form-item',
    'hidePrices' => false
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product || $arguments['hidePrices']) {
    return;
}

$priceHtml = $product->get_price_html();
?>
<label class="<?php
    echo ($product->get_price() == 0 ? 'is-zero-price ' : '') . esc_attr($arguments['class']);
    ?>-price"
    for="woocommerce-products-wizard-form-item-choose-<?php echo esc_attr($product->get_id()); ?>"
    data-component="wcpw-product-price"
    data-default="<?php echo esc_attr($priceHtml); ?>"><?php
    echo wp_kses_post($priceHtml);
    ?></label>
<!--spacer-->
<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Product;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'footerSubClass' => '',
    'hideChooseElement' => false,
    'showFooterPrice' => true,
    'showFooterChoose' => true,
    'severalProducts' => false,
    'product' => null,
    'defaultAttributes' => []
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product || !$product->is_type('variable')) {
    return;
}

// get default selected attributes
if (empty($arguments['defaultAttributes'])) {
    if (method_exists($product, 'get_default_attributes')) {
        $arguments['defaultAttributes'] = $product->get_default_attributes();
    } elseif (method_exists($product, 'get_variation_default_attributes')) {
        $arguments['defaultAttributes'] = $product->get_variation_default_attributes();
    }
}

// get variations data
$arguments['variationArguments'] = Product::getVariationArguments($arguments);
?>
<form data-component="wcpw-product-footer wcpw-product-variations"
    data-product_id="<?php echo esc_attr($product->get_id()); ?>"
    data-product_variations="<?php echo esc_attr(json_encode($arguments['variationArguments']['variations'])); ?>"
    class="<?php
    echo esc_attr(trim($arguments['footerSubClass'] . ' ' . $arguments['class']));
    ?>-footer cart variations_form">
    <?php
    do_action('woocommerce_before_variations_form');
    Template::html('form/item/prototype/variations/index', $arguments);
    Template::html('form/item/prototype/availability', $arguments);
    do_action('woocommerce_before_add_to_cart_button');

    if ($arguments['showFooterPrice'] || $arguments['showFooterChoose']) {
        $inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
        ?>
        <div class="<?php echo esc_attr($arguments['class']); ?>-check<?php
            echo !$arguments['hideChooseElement'] ? esc_attr(' form-check custom-control custom-' . $inputType) : '';
            ?>">
            <?php
            if ($arguments['showFooterChoose']) {
                Template::html('form/item/prototype/choose', $arguments);
            }

            if ($arguments['showFooterPrice']) {
                Template::html('form/item/prototype/price', $arguments);
            }
            ?>
        </div>
        <?php
    }

    Template::html('form/item/prototype/controls', $arguments);
    do_action('woocommerce_after_add_to_cart_button');
    do_action('woocommerce_after_add_to_cart_form');
    ?>
</form>

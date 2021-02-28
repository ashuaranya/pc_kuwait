<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'product' => null,
    'activeProductsIds' => [],
    'class' => 'woocommerce-products-wizard-form-item',
    'addToCartByQuantity' => Settings::getStep($id, $stepId, 'add_to_cart_by_quantity'),
    'hideChooseElement' => false,
    'severalProducts' => false,
    'formId' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
$productId = $product->get_id();
?>
<input type="hidden"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr("productsToAdd[{$arguments['stepId']}-{$productId}][step_id]"); ?>"
    value="<?php echo esc_attr($arguments['stepId']); ?>">
<input type="hidden"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr("productsToAdd[{$arguments['stepId']}-{$productId}][product_id]"); ?>"
    value="<?php echo esc_attr($productId); ?>">
<?php
if ($arguments['hideChooseElement']) {
    if (!$arguments['addToCartByQuantity'] && in_array($productId, $arguments['activeProductsIds'])) {
        ?>
        <input type="hidden"
            form="<?php echo esc_attr($arguments['formId']); ?>"
            name="productsToAddChecked[<?php echo esc_attr($arguments['stepId']); ?>][]"
            value="<?php echo esc_attr($productId); ?>">
        <?php
    }

    return;
}
?>
<span class="<?php echo esc_attr($arguments['class']); ?>-choose is-<?php echo esc_attr($inputType); ?>">
    <input type="<?php echo esc_attr($inputType); ?>"
        form="<?php echo esc_attr($arguments['formId']); ?>"
        id="woocommerce-products-wizard-form-item-choose-<?php echo esc_attr($productId); ?>"
        name="productsToAddChecked[<?php echo esc_attr($arguments['stepId']); ?>][]"
        value="<?php echo esc_attr($productId); ?>"
        class="custom-control-input"
        data-component="wcpw-product-choose"
        data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"
        aria-label="<?php esc_attr_e('Choose', 'woocommerce-products-wizard'); ?>"
        <?php
        echo in_array($productId, $arguments['activeProductsIds'])
            ? ' checked="checked"'
            : '';
        ?>>
    <span class="custom-control-label d-inline-block"></span>
</span>
<!--spacer-->

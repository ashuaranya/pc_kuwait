<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Cart;
use WCProductsWizard\Product;
use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'class' => 'woocommerce-products-wizard-form-item',
    'soldIndividually' => false,
    'defaultQuantity' => Settings::getStep($id, $stepId, 'product_quantity_by_default'),
    'product' => null,
    'formId' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product || $product->is_sold_individually() || $arguments['soldIndividually']) {
    return;
}

$productInCart = Cart::getProductById($arguments['id'], $product->get_id());

$min = Product::getMinQuantity($arguments['id'], $arguments['stepId']);
$max = Product::getMaxQuantity($arguments['id'], $arguments['stepId'], $product);
$value = $productInCart ? $productInCart['quantity'] : $arguments['defaultQuantity'];
$value = $min ? max($value, $min) : $value;
$value = $max ? min($value, $max) : $value;
$inputId = "woocommerce-products-wizard-{$arguments['id']}-form-{$arguments['stepId']}-"
    . "item-{$product->get_id()}-quantity";

$input = woocommerce_quantity_input(
    [
        'input_id' => $inputId,
        'min_value' => $min,
        'max_value' => $max,
        'input_value' => $value,
        'input_name' => "productsToAdd[{$arguments['stepId']}-{$product->get_id()}][quantity]"
    ],
    null,
    false
);

$input = str_replace(
    [
        '<input',
        'class="input-text '
    ],
    [
        '<input data-component="wcpw-product-quantity-input" form="' . $arguments['formId'] . '"',
        'class="input-text form-control input-sm form-control-sm '
    ],
    $input
);
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-quantity" data-component="wcpw-product-quantity"><?php
    echo $input;
    ?></div>

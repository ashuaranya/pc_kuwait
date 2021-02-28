<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'class' => 'woocommerce-products-wizard-form-item',
    'formId' => null,
    'variationsType' => Settings::getStep($id, $stepId, 'variations_type'),
    'variationArguments' => []
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$variationType = Settings::getProduct($product->get_id(), 'variations_type');
$variationType = strtolower($variationType) != 'default' ? strtolower($variationType) : $arguments['variationsType'];
?>
<dl class="<?php echo esc_attr($arguments['class']); ?>-variations"
    data-component="wcpw-product-variations-data">
    <?php
    foreach ($arguments['variationArguments']['attributes'] as $attributeKey => $attributeValues) {
        Template::html(
            "form/item/prototype/variations/item/{$variationType}",
            array_replace(
                $arguments,
                [
                    'attributeValues' => $attributeValues,
                    'attributeKey' => $attributeKey
                ]
            )
        );
    }
    ?>
</dl>
<input type="hidden"
    class="variation_id"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php
    echo esc_attr("productsToAdd[{$arguments['stepId']}-{$product->get_id()}][variation_id]");
    ?>"
    value=""
    data-component="wcpw-product-variations-variation-id">

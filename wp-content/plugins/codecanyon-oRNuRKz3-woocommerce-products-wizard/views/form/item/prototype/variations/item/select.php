<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'product' => null,
    'formId' => null,
    'stepId' => null,
    'attributeKey' => null,
    'attributeValues' => []
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$fieldName = "productsToAdd[{$arguments['stepId']}-{$product->get_id()}][variation][attribute_"
    . sanitize_title($arguments['attributeKey']) . ']';
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-variations-item form-group is-select">
    <dt class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name-wrapper">
        <label class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name"
            for="<?php echo esc_attr($fieldName); ?>"><?php
            echo wc_attribute_label($arguments['attributeKey']);
            ?></label>
    </dt>
    <dd class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value-wrapper">
        <select name="<?php echo esc_attr($fieldName); ?>"
            id="<?php echo esc_attr($fieldName); ?>"
            class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value form-control is-select"
            form="<?php echo esc_attr($arguments['formId']); ?>"
            data-name="attribute_<?php echo sanitize_title($arguments['attributeKey']); ?>"
            data-component="wcpw-product-variations-item wcpw-product-variations-item-input">
            <?php foreach ($arguments['attributeValues'] as $attributeValue) { ?>
                <option value="<?php echo esc_attr($attributeValue['value']); ?>"
                    data-component="wcpw-product-variations-item-value"<?php
                    echo $attributeValue['selected'] ? ' selected' : '';
                    ?>><?php
                    echo esc_html(apply_filters('woocommerce_variation_option_name', $attributeValue['name']));
                    ?></option>
            <?php } ?>
        </select>
    </dd>
</div>

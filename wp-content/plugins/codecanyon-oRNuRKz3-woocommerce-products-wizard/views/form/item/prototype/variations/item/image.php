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
<div class="<?php echo esc_attr($arguments['class']); ?>-variations-item form-group is-image">
    <dt class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name-wrapper">
        <label class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name"
            for="<?php echo esc_attr($fieldName); ?>"><?php
            echo wc_attribute_label($arguments['attributeKey']);
            ?></label>
    </dt>
    <dd class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value-wrapper">
        <fieldset class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value is-image"
            data-component="wcpw-product-variations-item"
            data-name="attribute_<?php echo sanitize_title($arguments['attributeKey']); ?>">
            <?php
            foreach ($arguments['attributeValues'] as $attributeValue) {
                $image = isset($attributeValue['thumbnailId']) && $attributeValue['thumbnailId']
                    ? wp_get_attachment_image(
                        $attributeValue['thumbnailId'],
                        'thumbnail',
                        false,
                        [
                            'class' => esc_attr($arguments['class']) . '-variations-item-value-thumbnail',
                            'alt' => apply_filters('woocommerce_variation_option_name', $attributeValue['name']),
                            'title' => apply_filters('woocommerce_variation_option_name', $attributeValue['name'])
                        ]
                    )
                : '';
                ?>
                <label class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value-label">
                    <input type="radio"
                        class="<?php
                        echo esc_attr($arguments['class']);
                        ?>-variations-item-value-input sr-only is-hidden"
                        data-component="wcpw-product-variations-item-value wcpw-product-variations-item-input"
                        data-name="attribute_<?php echo sanitize_title($arguments['attributeKey']); ?>"
                        form="<?php echo esc_attr($arguments['formId']); ?>"
                        name="<?php echo esc_attr($fieldName); ?>"
                        value="<?php echo esc_attr($attributeValue['value']); ?>"<?php
                        echo $attributeValue['selected'] ? ' checked' : '';
                        ?>>
                    <span class="<?php
                        echo 'is-' . ($image ? 'image ' : 'button ');
                        echo esc_attr($arguments['class']);
                        ?>-variations-item-value-caption btn btn-outline-secondary"><?php
                        echo $image
                            ? $image
                            : apply_filters('woocommerce_variation_option_name', $attributeValue['name']);
                        ?></span>
                </label>
            <?php } ?>
        </fieldset>
    </dd>
</div>

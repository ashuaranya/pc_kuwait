<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'label' => esc_html__('Category', 'woocommerce-products-wizard'),
    'stepId' => null,
    'key' => 'category',
    'values' => []
]);
?>
<fieldset class="woocommerce-products-wizard-form-filter-field form-group is-radio is-<?php
echo esc_attr($arguments['key']);
?>">
    <legend class="woocommerce-products-wizard-form-filter-field-title"><?php
        echo wp_kses_post($arguments['label']);
        ?></legend>
    <?php foreach ($arguments['values'] as $value) { ?>
        <label class="woocommerce-products-wizard-form-filter-field-value-label checkbox-inline custom-control custom-radio custom-control-inline is-value-<?php echo esc_attr($value['id']); ?>">
            <input type="radio"
                name="<?php echo esc_attr("wcpwFilter[{$arguments['stepId']}][{$arguments['key']}][]"); ?>"
                value="<?php echo esc_attr($value['id']); ?>"
                class="woocommerce-products-wizard-form-filter-field-value-input form-check-input custom-control-input"<?php
                echo $value['isActive'] ? ' checked' : '';
                ?>>
            <span class="woocommerce-products-wizard-form-filter-field-value-name form-check-label custom-control-label"><?php
                echo wp_kses_post($value['name']);
                ?></span>
        </label>
    <?php } ?>
</fieldset>

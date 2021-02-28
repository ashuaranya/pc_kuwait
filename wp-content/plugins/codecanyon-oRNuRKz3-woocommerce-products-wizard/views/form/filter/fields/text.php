<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'label' => esc_html__('Title', 'woocommerce-products-wizard'),
    'stepId' => null,
    'key' => 'title',
    'values' => []
]);
?>
<fieldset class="woocommerce-products-wizard-form-filter-field form-group is-text is-<?php
echo esc_attr($arguments['key']);
?>">
    <legend class="woocommerce-products-wizard-form-filter-field-title"><?php
        echo wp_kses_post($arguments['label']);
        ?></legend>
    <label class="woocommerce-products-wizard-form-filter-field-label sr-only"
        for="woocommerce-products-wizard-form-filter-<?php echo esc_attr($arguments['key']); ?>"><?php
        echo wp_kses_post($arguments['label']);
        ?></label>
    <input type="text"
        class="form-control woocommerce-products-wizard-form-filter-field-value-input"
        name="<?php echo esc_attr("wcpwFilter[{$arguments['stepId']}][{$arguments['key']}]"); ?>"
        value="<?php echo esc_attr($arguments['value']); ?>"
        id="woocommerce-products-wizard-form-filter-<?php echo esc_attr($arguments['key']); ?>">
</fieldset>

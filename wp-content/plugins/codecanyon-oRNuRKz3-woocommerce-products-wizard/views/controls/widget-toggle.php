<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => [],
    'formId' => null,
    'widgetToggleButtonText' => Settings::getPost($id, 'widget_toggle_button_text'),
    'widgetToggleButtonClass' => Settings::getPost($id, 'widget_toggle_button_class')
]);

$isExpanded = isset($_COOKIE["#woocommerce-products-wizard-widget-{$arguments['id']}-expanded"])
    ? $_COOKIE["#woocommerce-products-wizard-widget-{$arguments['id']}-expanded"]
    : 'true';
?>
<a href="#woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
    role="button"
    class="btn woocommerce-products-wizard-control is-widget-toggle <?php
    echo esc_attr($arguments['widgetToggleButtonClass']);
    ?>"
    aria-controls="#woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
    aria-expanded="<?php echo esc_attr($isExpanded); ?>"
    data-component="wcpw-toggle">
    <?php if (count($arguments['cart']) > 0) { ?>
        <span class="woocommerce-products-wizard-control-badge badge badge-pill badge-primary"><?php
            echo count($arguments['cart']);
            ?></span>
    <?php } ?>
    <span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['widgetToggleButtonText']); ?>
        <!--spacer-->
    </span>
</a>
<!--spacer-->

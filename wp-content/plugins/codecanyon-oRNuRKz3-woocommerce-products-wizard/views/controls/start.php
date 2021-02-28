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
    'formId' => null,
    'startButtonText' => Settings::getPost($id, 'start_button_text'),
    'startButtonClass' => Settings::getPost($id, 'start_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-start <?php
    echo esc_attr($arguments['startButtonClass']);
    ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    type="submit" name="submit"
    data-component="wcpw-start wcpw-nav-item"
    data-nav-action="submit"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['startButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->

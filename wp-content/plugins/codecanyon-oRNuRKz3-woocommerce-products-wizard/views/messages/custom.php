<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'message' => '',
    'type' => 'error'
]);
?>
<div class="woocommerce-products-wizard-message custom woocommerce-<?php echo esc_attr($arguments['type']); ?>"
    aria-live="assertive"><?php
    echo wp_kses_post($arguments['message']);
    ?></div>

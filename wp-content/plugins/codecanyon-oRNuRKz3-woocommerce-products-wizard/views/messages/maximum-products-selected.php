<?php
// @deprecated 4.3.0

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
    'maxProductsSelected' => null,
    'maximumProductsSelectedMessage' => Settings::getPost($id, 'maximum_products_selected_message')
]);
?>
<div class="woocommerce-products-wizard-message maximum-products-selected woocommerce-error"><?php
    echo wp_kses_post(sprintf($arguments['maximumProductsSelectedMessage'], $arguments['maxProductsSelected']));
    ?></div>

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
    'minProductsSelected' => null,
    'minimumProductsSelectedMessage' => Settings::getPost($id, 'minimum_products_selected_message')
]);
?>
<div class="woocommerce-products-wizard-message minimum-products-selected woocommerce-error"><?php
    echo wp_kses_post(sprintf($arguments['minimumProductsSelectedMessage'], $arguments['minProductsSelected']));
    ?></div>

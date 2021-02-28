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

$arguments = Template::getHTMLArgs(['nothingFoundMessage' => Settings::getPost($id, 'nothing_found_message')]);
?>
<div class="woocommerce-products-wizard-message no-results woocommerce-info"
    aria-live="polite"><?php
    echo wp_kses_post($arguments['nothingFoundMessage']);
    ?></div>

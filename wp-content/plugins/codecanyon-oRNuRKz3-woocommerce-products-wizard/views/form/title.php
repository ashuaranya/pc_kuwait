<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs(['title' => '']);

if (!$arguments['title']) {
    return;
}
?>
<h2 class="woocommerce-products-wizard-form-title"><?php
    echo wp_kses_post($arguments['title']);
    ?></h2>

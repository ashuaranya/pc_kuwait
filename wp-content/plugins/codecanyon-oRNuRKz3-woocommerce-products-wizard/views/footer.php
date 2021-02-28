<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs();
?>
<footer class="woocommerce-products-wizard-footer"><?php
    Template::html('controls/index', $arguments);
    ?></footer>

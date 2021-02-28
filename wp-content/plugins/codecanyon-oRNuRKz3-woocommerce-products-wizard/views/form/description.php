<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs(['description' => '']);

if (!$arguments['description']) {
    return;
}
?>
<div class="woocommerce-products-wizard-form-description"><?php echo $arguments['description']; ?></div>

<?php

use WCProductsWizard\Settings;
use WCProductsWizard\Template;

if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'attachedMode' => false,
    'formId' => null,
    'stepsIds' => [],
    'stepId' => null,
    'nextButtonText' => Settings::getPost($id, 'next_button_text')
]);

if (!filter_var($arguments['attachedMode'], FILTER_VALIDATE_BOOLEAN)) {
    ?>
    <form action="#" method="POST" enctype="multipart/form-data"
        class="hidden d-none" id="<?php echo esc_attr($arguments['formId']); ?>"
        data-component="wcpw-form">
        <?php // no-js keyboard version of submit. should be upper the other ?>
        <button type="submit" class="sr-only" name="submit"
            data-component="wcpw-next wcpw-nav-item" data-nav-action="submit"><?php
            echo wp_kses_post($arguments['nextButtonText']);
            ?></button>
    </form>
    <?php
} else {
    ?>
    <input type="hidden" form="<?php echo esc_attr($arguments['formId']); ?>" name="attach-to-product">
    <?php
}
?>
<input type="hidden" form="<?php echo esc_attr($arguments['formId']); ?>" name="woocommerce-products-wizard">
<input type="hidden" form="<?php echo esc_attr($arguments['formId']); ?>" name="id"
    value="<?php echo esc_attr($arguments['id']); ?>">

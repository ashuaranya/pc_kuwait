<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => '',
    'state' => '',
    'action' => '',
    'id' => '',
    'name' => '',
    'formId' => ''
]);
?>
<button role="tab"
    class="woocommerce-products-wizard-nav-button btn btn-default btn-light btn-block <?php
    echo esc_attr($arguments['class']);
    ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr($arguments['action']); ?>"
    value="<?php echo esc_attr($arguments['value']); ?>"
    data-component="wcpw-nav-item"
    data-nav-action="<?php echo esc_attr($arguments['action']); ?>"
    data-nav-id="<?php echo esc_attr($arguments['value']); ?>"<?php
    echo $arguments['state'] == 'disabled' ? ' disabled="disabled"' : '';
    ?>><?php
    echo $arguments['thumbnail']
        ? wp_get_attachment_image(
            $arguments['thumbnail'],
            'thumbnail',
            false,
            ['class' => 'woocommerce-products-wizard-nav-button-thumbnail']
        ) . ' '
        : '';
    ?><span class="woocommerce-products-wizard-nav-button-inner"><?php
        echo wp_kses_post($arguments['name']);
        ?></span></button>

<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'footerSubClass' => '',
    'hideChooseElement' => false,
    'showFooterPrice' => true,
    'showFooterChoose' => true,
    'severalProducts' => false
]);
?>
<form data-component="wcpw-product-footer"
    class="<?php echo esc_attr(trim($arguments['footerSubClass'] . ' ' . $arguments['class'])); ?>-footer cart">
    <?php
    do_action('woocommerce_before_add_to_cart_button');
    Template::html('form/item/prototype/availability', $arguments);

    if ($arguments['showFooterPrice'] || $arguments['showFooterChoose']) {
        $inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
        ?>
        <div class="<?php echo esc_attr($arguments['class']); ?>-check<?php
            echo !$arguments['hideChooseElement'] ? esc_attr(' form-check custom-control custom-' . $inputType) : '';
            ?>">
            <?php
            if ($arguments['showFooterChoose']) {
                Template::html('form/item/prototype/choose', $arguments);
            }

            if ($arguments['showFooterPrice']) {
                Template::html('form/item/prototype/price', $arguments);
            }
            ?>
        </div>
        <?php
    }

    Template::html('form/item/prototype/controls', $arguments);
    do_action('woocommerce_after_add_to_cart_button');
    do_action('woocommerce_after_add_to_cart_form');
    ?>
</form>

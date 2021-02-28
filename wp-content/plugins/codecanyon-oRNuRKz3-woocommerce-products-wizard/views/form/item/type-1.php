<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'product' => null,
    'stepId' => null,
    'cartItem' => null,
    'titleSubClass' => 'panel-title card-header m-0 bg-light',
    'footerSubClass' => 'panel-footer card-footer'
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}
?>
<article class="<?php
    echo esc_attr($arguments['class'] . ' type-1' . ($arguments['cartItem'] ? ' is-in-cart' : ''));
    ?> product panel panel-primary card"
    data-component="wcpw-product"
    data-type="<?php echo esc_attr($product->get_type()); ?>"
    data-id="<?php echo esc_attr($product->get_id()); ?>"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"<?php
    echo $arguments['cartItem'] ? (' data-cart-key="' . esc_attr($arguments['cartItem']['key']) . '"') : '';
    ?>>
    <header class="<?php echo esc_attr($arguments['class']); ?>-header panel-heading"><?php
        Template::html('form/item/prototype/title', $arguments);
        ?></header>
    <div class="<?php echo esc_attr($arguments['class']); ?>-body panel-body card-body">
        <div class="<?php echo esc_attr($arguments['class']); ?>-thumbnail-wrapper"><?php
            Template::html('form/item/prototype/thumbnail', $arguments);
            Template::html('form/item/prototype/gallery', $arguments);
            ?></div>
        <div class="<?php echo esc_attr($arguments['class']); ?>-inner"><?php
            Template::html('form/item/prototype/description', $arguments);
            ?></div>
    </div>
    <?php Template::html('form/item/prototype/footer', $arguments); ?>
</article>

<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Cart;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'class' => 'woocommerce-products-wizard-form-item',
    'soldIndividually' => false,
    'individualControls' => Settings::getStep($id, $stepId, 'individual_controls'),
    'individualAddToCartButtonText' => Settings::getPost($id, 'individual_add_to_cart_button_text'),
    'individualAddToCartButtonClass' => Settings::getPost($id, 'individual_add_to_cart_button_class'),
    'individualUpdateButtonText' => Settings::getPost($id, 'individual_update_button_text'),
    'individualUpdateButtonClass' => Settings::getPost($id, 'individual_update_button_class'),
    'individualRemoveButtonText' => Settings::getPost($id, 'individual_remove_button_text'),
    'individualRemoveButtonClass' => Settings::getPost($id, 'individual_remove_button_class'),
    'addToCartBehavior' => Settings::getStep($id, $stepId, 'add_to_cart_behavior'),
    'severalVariationsPerProduct' => Settings::getStep($id, $stepId, 'several_variations_per_product'),
    'formId' => null,
    'product' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$productType = $product->get_type();

// WooCommerce Subscriptions plugin support
$typesAliases = [
    'variable-subscription' => 'variable',
    'subscription' => 'simple'
];

if (isset($typesAliases[$productType])) {
    $productType = $typesAliases[$productType];
}
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-controls input-group">
    <?php
    Template::html('form/item/prototype/quantity', $arguments);

    if ($arguments['individualControls']) {
        $cartKey = Cart::getProductKeyById($arguments['id'], $product->get_id(), $arguments['stepId']);
        ?>
        <div class="<?php echo esc_attr($arguments['class']); ?>-controls-buttons <?php
            echo !$arguments['soldIndividually'] ? 'input-group-append' : 'btn-group';
            ?>">
            <?php if ($cartKey && (!$arguments['severalVariationsPerProduct'] || $productType != 'variable')) { ?>
                <button class="<?php
                    echo esc_attr($arguments['individualUpdateButtonClass'] . ' ' . $arguments['class']);
                    ?>-control woocommerce-products-wizard-control btn is-update-in-cart"
                    form="<?php echo esc_attr($arguments['formId']); ?>"
                    name="update-cart-product"
                    value="<?php echo esc_attr($cartKey); ?>"
                    title="<?php echo esc_attr($arguments['individualUpdateButtonText']); ?>"
                    data-component="wcpw-update-cart-product"
                    data-behavior="<?php echo esc_attr($arguments['addToCartBehavior']); ?>">
                    <!--spacer-->
                    <span class="woocommerce-products-wizard-control-inner"><?php
                        echo wp_kses_post($arguments['individualUpdateButtonText']);
                        ?></span>
                    <!--spacer-->
                </button>
                <button class="<?php
                    echo esc_attr($arguments['individualRemoveButtonClass'] . ' ' . $arguments['class']);
                    ?>-control woocommerce-products-wizard-control btn is-remove-from-cart"
                    form="<?php echo esc_attr($arguments['formId']); ?>"
                    name="remove-cart-product"
                    value="<?php echo esc_attr($cartKey); ?>"
                    title="<?php echo esc_attr($arguments['individualRemoveButtonText']); ?>"
                    data-component="wcpw-remove-cart-product">
                    <!--spacer-->
                    <span class="woocommerce-products-wizard-control-inner"><?php
                        echo wp_kses_post($arguments['individualRemoveButtonText']);
                        ?></span>
                    <!--spacer-->
                </button>
            <?php } else { ?>
                <button class="<?php
                    echo esc_attr($arguments['individualAddToCartButtonClass'] . ' ' . $arguments['class']);
                    ?>-control woocommerce-products-wizard-control btn is-add-to-cart"
                    form="<?php echo esc_attr($arguments['formId']); ?>"
                    name="add-cart-product"
                    value="<?php echo esc_attr($arguments['stepId'] . '-' . $product->get_id()); ?>"
                    title="<?php echo esc_attr($arguments['individualAddToCartButtonText']); ?>"
                    data-component="wcpw-add-cart-product"
                    data-behavior="<?php echo esc_attr($arguments['addToCartBehavior']); ?>">
                    <!--spacer-->
                    <span class="woocommerce-products-wizard-control-inner"><?php
                        echo wp_kses_post($arguments['individualAddToCartButtonText']);
                        ?></span>
                    <!--spacer-->
                </button>
            <?php } ?>
        </div>
        <?php
    }
    ?>
</div>

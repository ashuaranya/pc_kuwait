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
    'hideChooseElement' => false,
    'enableTitleLink' => false,
    'enableThumbnailLink' => false,
    'severalProducts' => false,
    'showFooterPrice' => false,
    'showFooterChoose' => false
]);

$enableTitleLink = $arguments['enableTitleLink'];
$arguments['enableTitleLink'] = false; // hard overwrite
$inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}
?>
<article class="<?php
    echo esc_attr($arguments['class'] . ' type-5' . ($arguments['cartItem'] ? ' is-in-cart' : ''));
    ?> product"
    data-component="wcpw-product"
    data-type="<?php echo esc_attr($product->get_type()); ?>"
    data-id="<?php echo esc_attr($product->get_id()); ?>"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"<?php
    echo $arguments['cartItem'] ? (' data-cart-key="' . esc_attr($arguments['cartItem']['key']) . '"') : '';
    ?>>
    <div class="<?php echo esc_attr($arguments['class']); ?>-body">
        <a href="#<?php echo esc_attr($arguments['class'] . '-modal-' . $product->get_id()); ?>"
            class="<?php echo esc_attr($arguments['class']); ?>-link"
            data-toggle="modal"><?php
            Template::html('form/item/prototype/thumbnail', $arguments);
            Template::html('form/item/prototype/title', $arguments);
            ?></a>
        <div class="<?php echo esc_attr($arguments['class']); ?>-check<?php
            echo !$arguments['hideChooseElement'] ? esc_attr(' form-check custom-control custom-' . $inputType) : '';
            ?>">
            <?php
            Template::html('form/item/prototype/choose', $arguments);
            Template::html('form/item/prototype/price', $arguments);
            ?>
        </div>
    </div>
    <div class="<?php echo esc_attr($arguments['class']); ?>-modal modal fade" tabindex="-1" role="dialog"
        data-component="wcpw-product-modal"
        id="<?php echo esc_attr($arguments['class'] . '-modal-' . $product->get_id()); ?>">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title"><?php
                        if ($enableTitleLink) {
                            echo '<a href="' . $product->get_permalink() . '" target="_blank" class="'
                                . 'woocommerce-products-wizard-outer-link modal-title-link">';
                        }

                        echo $product->get_title();

                        if ($enableTitleLink) {
                            echo '</a>';
                        }
                        ?></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="<?php echo esc_attr($arguments['class']); ?>-content">
                        <div class="<?php echo esc_attr($arguments['class']); ?>-content-thumbnail"><?php
                            Template::html('form/item/prototype/thumbnail', $arguments);
                            Template::html('form/item/prototype/gallery', $arguments);
                            ?></div>
                        <div class="<?php echo esc_attr($arguments['class']); ?>-content-body"><?php
                            Template::html('form/item/prototype/description', $arguments);
                            Template::html('form/item/prototype/footer', $arguments);
                            ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

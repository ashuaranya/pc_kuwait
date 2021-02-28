<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Cart;
use WCProductsWizard\Core;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;
use WCProductsWizard\Utils;

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => [],
    'steps' => [],
    'formId' => null,
    'hidePrices' => false,
    'enableRemoveButton' => false,
    'sidebarPosition' => 'right',
    'removeButtonText' => Settings::getPost($id, 'remove_button_text'),
    'removeButtonClass' => Settings::getPost($id, 'remove_button_class'),
    'stickyWidget' => Settings::getPost($id, 'sticky_widget'),
    'stickyWidgetOffsetTop' => Settings::getPost($id, 'sticky_widget_offset_top'),
    'enableEditButton' => Settings::getPost($id, 'enable_edit_button'),
    'editButtonText' => Settings::getPost($id, 'edit_button_text'),
    'editButtonClass' => Settings::getPost($id, 'edit_button_class'),
    'subtotalString'=> Settings::getPost($id, 'subtotal_string'),
    'discountString'=> Settings::getPost($id, 'discount_string'),
    'totalString'=> Settings::getPost($id, 'total_string'),
    'showStepsInCart'=> Settings::getPost($id, 'show_steps_in_cart'),
    'groupProductsIntoKits'=> Settings::getPost($id, 'group_products_into_kits'),
    'kitsType'=> Settings::getPost($id, 'kits_type'),
    'kitBasePrice'=> Settings::getPost($id, 'kit_base_price'),
    'kitBasePriceString'=> Settings::getPost($id, 'kit_base_price_string'),
    'generateThumbnail' => Settings::getPost($id, 'generate_thumbnail')
]);

$previousStep = null;
$isExpanded = isset($_COOKIE["#woocommerce-products-wizard-widget-{$arguments['id']}-expanded"])
    ? $_COOKIE["#woocommerce-products-wizard-widget-{$arguments['id']}-expanded"]
    : 'true';
?>
<section class="woocommerce-products-wizard-widget panel panel-default card is-position-<?php
    echo esc_attr($arguments['sidebarPosition']);
    ?>"
    id="woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
    aria-expanded="<?php echo esc_attr($isExpanded); ?>"
    data-component="wcpw-widget<?php echo $arguments['stickyWidget'] ? ' wcpw-sticky' : ''; ?>"
    data-sticky-top-offset="<?php echo esc_attr($arguments['stickyWidgetOffsetTop']); ?>"
    data-sticky-parent="[data-component=wcpw-main-row]">
    <?php
    if (empty($arguments['cart'])) {
        Template::html('messages/cart-is-empty', $arguments);
    } else {
        ?>
        <ul class="woocommerce-products-wizard-widget-body">
            <?php
            if ($arguments['generateThumbnail']) {
                $generatedThumbnail = Core::generateThumbnail($arguments['id'], $arguments['cart']);

                if (!empty($generatedThumbnail)) {
                    ?>
                    <li class="woocommerce-products-wizard-widget-body-item is-thumbnail">
                        <figure class="woocommerce-products-wizard-widget-generated-thumbnail">
                            <?php
                            $attributes = [
                                'src' => $generatedThumbnail['url'],
                                'alt' => ''
                            ];

                            $attributes = apply_filters(
                                'wcProductsWizardWidgetGeneratedThumbnailAttributes',
                                $attributes,
                                $arguments['id'],
                                $arguments['cart']
                            );

                            $thumbnail = '<img ' . Utils::attributesArrayToString($attributes) . '>';

                            echo "<a href=\"{$generatedThumbnail['url']}\" "
                                . "data-rel=\"prettyPhoto\" rel=\"lightbox\">{$thumbnail}</a>";
                            ?>
                        </figure>
                    </li>
                    <?php
                }
            }

            foreach ($arguments['cart'] as $cartItemKey => $cartItem) {
                if ((isset($cartItem['data']) && (!$cartItem['data'] || !$cartItem['data']->exists()))
                    || (isset($cartItem['quantity']) && $cartItem['quantity'] <= 0)
                    || (isset($cartItem['value']) && empty($cartItem['value']))
                    || !isset($cartItem['step_id'])
                ) {
                    continue;
                }

                if ($arguments['showStepsInCart'] && $previousStep != $cartItem['step_id']
                    && $arguments['steps'][$cartItem['step_id']]
                ) {
                    $previousStep = $cartItem['step_id'];
                    ?>
                    <li class="woocommerce-products-wizard-widget-body-item is-heading <?php
                        echo esc_attr("is-step-{$cartItem['step_id']}");
                        ?>"><?php echo wp_kses_post($arguments['steps'][$cartItem['step_id']]['name']); ?></li><?php
                }

                if (isset($cartItem['product_id'], $cartItem['data']) && !is_null($cartItem['data'])) {
                    $product = $cartItem['data'];
                    ?>
                    <li class="woocommerce-products-wizard-widget-body-item is-product <?php
                        echo esc_attr("is-step-{$cartItem['step_id']}");
                        ?>">
                        <article class="woocommerce-products-wizard-widget-item is-product">
                            <figure class="woocommerce-products-wizard-widget-item-thumbnail">
                                <?php
                                $href = wp_get_attachment_image_src($product->get_image_id(), 'large');
                                $thumbnail = $product->get_image('shop_thumbnail', ['class' => 'img-thumbnail']);
                                $thumbnail = apply_filters(
                                    'wcProductsWizardWidgetItemThumbnail',
                                    $thumbnail,
                                    $cartItem,
                                    $cartItemKey
                                );

                                // old versions fallback
                                $thumbnail = apply_filters(
                                    'wcProductsWizardCartItemThumbnail',
                                    $thumbnail,
                                    $cartItem,
                                    $cartItemKey
                                );

                                echo "<a href=\"{$href[0]}\" "
                                    . "data-rel=\"prettyPhoto\" rel=\"lightbox\">{$thumbnail}</a>";
                                ?>
                            </figure>
                            <div class="woocommerce-products-wizard-widget-item-inner">
                                <?php if ($arguments['enableEditButton']) { ?>
                                    <button class="<?php
                                        echo esc_attr($arguments['editButtonClass']);
                                        ?> btn woocommerce-products-wizard-widget-item-control woocommerce-products-wizard-control show-icon is-edit-in-cart"
                                        form="<?php echo esc_attr($arguments['formId']); ?>"
                                        name="get-step"
                                        value="<?php echo esc_attr($cartItem['step_id']); ?>"
                                        title="<?php echo esc_attr($arguments['editButtonText']); ?>"
                                        data-component="wcpw-product-edit-in-cart wcpw-nav-item"
                                        data-nav-action="get-step"
                                        data-nav-id="<?php echo esc_attr($cartItem['step_id']); ?>">
                                        <!--spacer-->
                                        <span class="woocommerce-products-wizard-control-inner"><?php
                                            echo wp_kses_post($arguments['editButtonText']);
                                            ?></span>
                                        <!--spacer-->
                                    </button>
                                <?php } ?>
                                <h4 class="woocommerce-products-wizard-widget-item-title"><?php
                                    if (method_exists($product, 'get_name')) {
                                        echo $product->get_name();
                                    }
                                    ?></h4>
                                <?php
                                if ($arguments['hidePrices']
                                    && (!isset($cartItem['sold_individually']) || !$cartItem['sold_individually'])
                                ) {
                                    ?>
                                    <bdi class="woocommerce-products-wizard-widget-item-times">x</bdi>
                                    <span class="woocommerce-products-wizard-widget-item-quantity"><?php
                                        echo $cartItem['quantity'];
                                        ?></span>
                                    <?php
                                }
                                ?>
                                <div class="woocommerce-products-wizard-widget-item-data">
                                    <?php
                                    // Localization variations
                                    $cartItemLocalized = $cartItem;

                                    if (isset($cartItem['variation']) && is_array($cartItem['variation'])) {
                                        foreach ($cartItemLocalized['variation'] as &$variationsItem) {
                                            $variationsItem = urldecode($variationsItem);
                                        }
                                    }

                                    if (function_exists('wc_get_formatted_cart_item_data')) {
                                        echo wc_get_formatted_cart_item_data($cartItemLocalized);
                                    } else {
                                        echo \WC()->cart->get_item_data($cartItemLocalized);
                                    }
                                    ?>
                                </div>
                                <?php if (!$arguments['hidePrices']) { ?>
                                    <span class="woocommerce-products-wizard-widget-item-price<?php
                                        echo $product->get_price() == 0 ? ' is-zero-price ' : '';
                                        ?>"><?php
                                        echo \WC()->cart->get_product_price($product);

                                        if (!isset($cartItem['sold_individually']) || !$cartItem['sold_individually']) {
                                            ?>
                                            <bdi class="woocommerce-products-wizard-widget-item-price-times">x</bdi>
                                            <span class="woocommerce-products-wizard-widget-item-price-quantity"><?php
                                                echo $cartItem['quantity'];
                                                ?></span>
                                            <?php
                                        }
                                        ?></span>
                                <?php } ?>
                                <?php if ($arguments['enableRemoveButton']) { ?>
                                    <button class="<?php
                                        echo esc_attr($arguments['removeButtonClass']);
                                        ?> btn woocommerce-products-wizard-widget-item-control woocommerce-products-wizard-control is-remove-from-cart"
                                        form="<?php echo esc_attr($arguments['formId']); ?>"
                                        name="remove-cart-product"
                                        value="<?php echo esc_attr($cartItemKey); ?>"
                                        title="<?php echo esc_attr($arguments['removeButtonText']); ?>"
                                        data-component="wcpw-remove-cart-product">
                                        <!--spacer-->
                                        <span class="woocommerce-products-wizard-control-inner"><?php
                                            echo wp_kses_post($arguments['removeButtonText']);
                                            ?></span>
                                        <!--spacer-->
                                    </button>
                                <?php } ?>
                            </div>
                        </article>
                    </li>
                    <?php
                } elseif (isset($cartItem['value'], $cartItem['key']) && !empty($cartItem['value'])
                    && !empty($cartItem['key'])
                ) {
                    ?>
                    <li class="woocommerce-products-wizard-widget-body-item is-field <?php
                        echo esc_attr("is-step-{$cartItem['step_id']}");
                        ?>">
                        <dl class="woocommerce-products-wizard-widget-item is-field">
                            <dt class="woocommerce-products-wizard-widget-item-name"><?php
                                echo wp_kses_post($cartItem['key']);
                                ?></dt>
                            <dd class="woocommerce-products-wizard-widget-item-value"><?php
                                echo wp_kses_post($cartItem['value']);
                                ?></dd>
                        </dl>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <footer class="woocommerce-products-wizard-widget-footer">
            <?php
            if ($arguments['groupProductsIntoKits'] && $arguments['kitsType'] == 'combined'
                && $arguments['kitBasePrice'] && !$arguments['hidePrices']
            ) {
                ?>
                <dl class="woocommerce-products-wizard-widget-footer-row is-kit-base-price">
                    <dt class="woocommerce-products-wizard-widget-footer-cell is-caption"><?php
                        echo wp_kses_post($arguments['kitBasePriceString']);
                        ?></dt>
                    <dd class="woocommerce-products-wizard-widget-footer-cell is-value"><?php
                        echo wc_price((float) $arguments['kitBasePrice']);
                        ?></dd>
                </dl>
                <?php
            }

            if (!$arguments['hidePrices']) {
                ?>
                <dl class="woocommerce-products-wizard-widget-footer-row is-total">
                    <dt class="woocommerce-products-wizard-widget-footer-cell is-caption"><?php
                        echo wp_kses_post($arguments['totalString']);
                        ?></dt>
                    <dd class="woocommerce-products-wizard-widget-footer-cell is-value"><?php
						$output = Cart::getTotal($arguments['id']);
						//echo $output;
						apply_filters('get_assemble_price_preview1', $output);
						echo wc_price(Cart::getTotal($arguments['id']));
                        ?></dd>
                </dl>
                <?php
            }
            ?>
        </footer>
        <?php
    }
    ?>
</section>

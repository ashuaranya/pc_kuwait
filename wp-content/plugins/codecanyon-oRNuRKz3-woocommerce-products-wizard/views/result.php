<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : null;
$singleStepMode = isset($singleStepMode) ? $singleStepMode : false;
$notices = WCProductsWizard\Instance()->form->getNotices($singleStepMode ? 'result' : null);

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => [],
    'steps' => [],
    'notices' => $notices,
    'hidePrices' => false,
    'enableRemoveButton' => false,
    'showStepsInCart' => Settings::getPost($id, 'show_steps_in_cart'),
    'showResultsTabTable' => Settings::getPost($id, 'show_results_tab_table'),
    'contactFormTitle' => Settings::getPost($id, 'results_tab_contact_form'),
    'resultsTabDescription' => Settings::getPost($id, 'results_tab_description'),
    'removeButtonText' => Settings::getPost($id, 'remove_button_text'),
    'removeString' => Settings::getPost($id, 'results_remove_string'),
    'priceString' => Settings::getPost($id, 'results_price_string'),
    'thumbnailString' => Settings::getPost($id, 'results_thumbnail_string'),
    'dataString'=> Settings::getPost($id, 'results_data_string'),
    'quantityString'=> Settings::getPost($id, 'results_quantity_string'),
    'subtotalString'=> Settings::getPost($id, 'subtotal_string'),
    'discountString'=> Settings::getPost($id, 'discount_string'),
    'totalString'=> Settings::getPost($id, 'total_string'),
    'groupProductsIntoKits'=> Settings::getPost($id, 'group_products_into_kits'),
    'kitsType'=> Settings::getPost($id, 'kits_type'),
    'kitBasePrice'=> Settings::getPost($id, 'kit_base_price'),
    'kitBasePriceString'=> Settings::getPost($id, 'kit_base_price_string')
]);

$previousStep = null;
$showProductsHeader = true;
$captionColspan = 2 + (int) $arguments['enableRemoveButton'];

if (!empty($arguments['notices'])) {
    foreach ($arguments['notices'] as $notice) {
        Template::html("messages/{$notice['view']}", array_replace($arguments, $notice));
    }
}

if (empty($arguments['cart'])) {
    return Template::html('messages/cart-is-empty', $arguments);
}

if ($arguments['resultsTabDescription']) {
    echo '<div class="woocommerce-products-wizard-results-description">'
        . do_shortcode(wpautop($arguments['resultsTabDescription']))
        . '</div>';
}

if ($arguments['showResultsTabTable']) {
    ?>
    <table class="woocommerce-products-wizard-results-table table table-hover wcpw-table-responsive"
        data-component="wcpw-results-table">
        <?php
        if ($arguments['groupProductsIntoKits'] && $arguments['kitsType'] == 'combined'
            && $arguments['kitBasePrice']
            && !$arguments['hidePrices']) {
            ?>
            <thead class="woocommerce-products-wizard-results-table-header">
                <tr class="woocommerce-products-wizard-results-table-header-row is-kit-base-price">
                    <th class="woocommerce-products-wizard-results-table-header-cell is-caption"
                        colspan="<?php echo $captionColspan; ?>"><?php
                        echo wp_kses_post($arguments['kitBasePriceString']);
                        ?></th>
                    <td class="woocommerce-products-wizard-results-table-header-cell is-value"
                        data-th="<?php echo esc_attr($arguments['kitBasePriceString']); ?>"><?php
                        echo wc_price((float) $arguments['kitBasePrice']);
                        ?></td>
                </tr>
            </thead>
            <?php
        }
        ?>
        <tbody class="woocommerce-products-wizard-results-table-body">
            <?php
            foreach ($arguments['cart'] as $cartItemKey => $cartItem) {
                if ((isset($cartItem['data']) && (!$cartItem['data'] || !$cartItem['data']->exists()))
                    || (isset($cartItem['quantity']) && $cartItem['quantity'] <= 0)
                    || (isset($cartItem['value']) && empty($cartItem['value']))
                    || !isset($cartItem['step_id'])) {
                    continue;
                }

                if ($showProductsHeader && isset($cartItem['product_id'], $cartItem['data']) && $cartItem['data']) {
                    ?>
                    <tr class="woocommerce-products-wizard-results-table-body-row is-products wcpw-table-responsive-hidden">
                        <?php if ($arguments['enableRemoveButton']) { ?>
                            <th class="woocommerce-products-wizard-results-table-header-cell is-remove">
                                <span class="sr-only"><?php echo wp_kses_post($arguments['removeString']); ?></span>
                            </th>
                        <?php } ?>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-thumbnail">
                            <span class="sr-only"><?php echo wp_kses_post($arguments['thumbnailString']); ?></span>
                        </th>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-data"><?php
                            echo wp_kses_post($arguments['dataString']);
                            ?></th>
                        <?php if (!$arguments['hidePrices']) { ?>
                            <th class="woocommerce-products-wizard-results-table-header-cell is-price"><?php
                                echo wp_kses_post($arguments['priceString']);
                                ?></th>
                        <?php } ?>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-quantity"><?php
                            echo wp_kses_post($arguments['quantityString']);
                            ?></th>
                        <?php if (!$arguments['hidePrices']) { ?>
                            <th class="woocommerce-products-wizard-results-table-header-cell is-subtotal"><?php
                                echo wp_kses_post($arguments['subtotalString']);
                                ?></th>
                        <?php } ?>
                    </tr>
                    <?php
                    $showProductsHeader = false;
                }

                if ($arguments['showStepsInCart'] && $previousStep != $cartItem['step_id']
                    && $arguments['steps'][$cartItem['step_id']]
                ) {
                    $previousStep = $cartItem['step_id'];
                    ?>
                <tr class="woocommerce-products-wizard-results-table-body-row is-heading <?php
                echo esc_attr("is-step-{$cartItem['step_id']}");
                ?>"><td class="woocommerce-products-wizard-results-table-body-cell"
                        colspan="<?php echo esc_attr($captionColspan + 1); ?>" data-th=""><?php
                        echo wp_kses_post($arguments['steps'][$cartItem['step_id']]['name'])
                        ?></td></tr><?php
                }
                ?>
                <tr class="woocommerce-products-wizard-results-table-body-row is-item <?php
                echo esc_attr("is-step-{$cartItem['step_id']}");
                ?>">
                    <?php
                    if (isset($cartItem['product_id'], $cartItem['data']) && $cartItem['data']) {
                        $product = $cartItem['data'];
                        ?>
                        <?php if ($arguments['enableRemoveButton']) { ?>
                            <td class="woocommerce-products-wizard-results-table-body-cell is-remove"
                                data-th="<?php echo esc_attr($arguments['removeString']); ?>">
                                <button class="close woocommerce-products-wizard-results-item-remove"
                                    aria-label="<?php echo esc_attr($arguments['removeString']); ?>"
                                    form="<?php echo esc_attr($arguments['formId']); ?>"
                                    name="remove-cart-product"
                                    value="<?php echo esc_attr($cartItemKey); ?>"
                                    title="<?php echo esc_attr($arguments['removeButtonText']); ?>"
                                    data-component="wcpw-remove-cart-product">
                                    <span aria-hidden="true">&times;</span>
                                    <span class="woocommerce-products-wizard-results-item-remove-text"><?php
                                        echo wp_kses_post($arguments['removeButtonText']);
                                        ?></span>
                                </button>
                            </td>
                        <?php } ?>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-thumbnail"
                            data-th="<?php echo esc_attr($arguments['thumbnailString']); ?>">
                            <figure class="woocommerce-products-wizard-results-item-thumbnail"><?php
                                // phpcs:disable
                                $href = wp_get_attachment_image_src($product->get_image_id(), 'full');
                                $thumbnail = $product->get_image('shop_thumbnail', ['class' => 'img-thumbnail']);
                                $thumbnail = apply_filters(
                                    'wcProductsWizardResultItemThumbnail',
                                    $thumbnail,
                                    $cartItem,
                                    $cartItemKey
                                );

                                echo "<a href=\"{$href[0]}\" "
                                    . "data-rel=\"prettyPhoto\" rel=\"lightbox\">{$thumbnail}</a>";
                                // phpcs:enable
                                ?></figure>
                        </td>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-data"
                            data-th="<?php echo esc_attr($arguments['dataString']); ?>">
                            <div class="woocommerce-products-wizard-results-item-title"><?php
                                if (method_exists($product, 'get_name')) {
                                    echo $product->get_name();
                                }
                                ?></div>
                            <div class="woocommerce-products-wizard-results-item-data"><?php
                                // phpcs:disable
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

                                // Backorder notification
                                if ($product->backorders_require_notification()
                                    && $product->is_on_backorder($cartItem['quantity'])) {
                                    echo '<p class="backorder_notification">'
                                        . esc_html__('Available on backorder', 'woocommerce') . '</p>';
                                }
                                // phpcs:enable
                                ?></div>
                        </td>
                        <?php if (!$arguments['hidePrices']) { ?>
                            <td class="woocommerce-products-wizard-results-table-body-cell is-price"
                                data-th="<?php echo esc_attr($arguments['priceString']); ?>">
                                    <span class="woocommerce-products-wizard-results-item-price<?php
                                    echo $product->get_price() == 0 ? ' is-zero-price ' : '';
                                    ?>"><?php echo \WC()->cart->get_product_price($product); ?></span>
                            </td>
                        <?php } ?>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-quantity"
                            data-th="<?php echo esc_attr($arguments['quantityString']); ?>">
                        <span class="woocommerce-products-wizard-results-item-quantity"><?php
                            echo $cartItem['quantity'];
                            ?></span>
                        </td>
                        <?php if (!$arguments['hidePrices']) { ?>
                            <td class="woocommerce-products-wizard-results-table-body-cell is-subtotal"
                                data-th="<?php echo esc_attr($arguments['subtotalString']); ?>">
                                    <span class="woocommerce-products-wizard-results-item-subtotal<?php
                                    echo $product->get_price() == 0 ? ' is-zero-price ' : '';
                                    ?>"><?php
									echo WC()->cart->get_product_subtotal($product, $cartItem['quantity']);
                                        ?></span>
                            </td>
                        <?php } ?>
                        <?php
                    } elseif (isset($cartItem['value'], $cartItem['key']) && !empty($cartItem['value'])
                        && !empty($cartItem['key'])) {
                        $showProductsHeader = true;
                        ?>
                        <th class="woocommerce-products-wizard-results-table-body-cell is-name"
                            colspan="<?php echo 2 + (int) $arguments['enableRemoveButton']; ?>">
                                <span class="woocommerce-products-wizard-results-item-name"><?php
                                    echo wp_kses_post($cartItem['key']);
                                    ?></span>
                        </th>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-value"
                            colspan="3"><span class="woocommerce-products-wizard-results-item-value"><?php
                                echo wp_kses_post($cartItem['value']);
                                ?></span>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <?php if (!$arguments['hidePrices']) { ?>
            <tfoot class="woocommerce-products-wizard-results-table-footer">
                <tr class="woocommerce-products-wizard-results-table-footer-row is-total">
                    <th class="woocommerce-products-wizard-results-table-footer-cell is-caption"
                        colspan="<?php echo $captionColspan; ?>"><?php
                        echo wp_kses_post($arguments['totalString']);
                        ?></th>
                    <td class="woocommerce-products-wizard-results-table-footer-cell is-value"
						colspan="<?php echo $captionColspan+1; ?>"
                        data-th="<?php echo esc_attr($arguments['totalString']); ?>"><?php
                        echo wc_price(Cart::getTotal($arguments['id']));
											  
                        ?></td>
                </tr>
            </tfoot>
        <?php } ?>
    </table>
    <?php
}

if ($arguments['contactFormTitle']) {
    echo '<div class="woocommerce-products-wizard-results-form">'
        . do_shortcode(
            '[contact-form-7 title="' . $arguments['contactFormTitle']
            . '" html_name="wcpw-result-' . $arguments['id'] . '"]'
        )
        . '</div>';
}
?>

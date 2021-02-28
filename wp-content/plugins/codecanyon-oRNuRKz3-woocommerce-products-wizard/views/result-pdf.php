<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Cart;
use WCProductsWizard\Form;
use WCProductsWizard\Utils;
use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$id = isset($id) ? $id : null;

$arguments = Template::getHTMLArgs(
    [
        'cart' => Cart::get($id, ['uploadsSourceType' => 'path']),
        'cartTotal' => Cart::getTotal($id),
        'steps' => Form::getSteps($id),
        'showStepsInCart' => Settings::getPost($id, 'show_steps_in_cart'),
        'resultPdfHeaderContent' => Settings::getPost($id, 'result_pdf_header_content'),
        'resultPdfHeaderHeight' => Settings::getPost($id, 'result_pdf_header_height'),
        'resultPdfFooterContent' => Settings::getPost($id, 'result_pdf_footer_content'),
        'resultPdfFooterHeight' => Settings::getPost($id, 'result_pdf_footer_height'),
        'resultPdfTopDescription' => Settings::getPost($id, 'result_pdf_top_description'),
        'resultPdfBottomDescription' => Settings::getPost($id, 'result_pdf_bottom_description'),
        'hidePrices' => Settings::getPost($id, 'hide_prices'),
        'priceString' => Settings::getPost($id, 'results_price_string'),
        'dataString'=> Settings::getPost($id, 'results_data_string'),
        'quantityString'=> Settings::getPost($id, 'results_quantity_string'),
        'subtotalString'=> Settings::getPost($id, 'subtotal_string'),
        'discountString'=> Settings::getPost($id, 'discount_string'),
        'totalString'=> Settings::getPost($id, 'total_string'),
        'groupProductsIntoKits'=> Settings::getPost($id, 'group_products_into_kits'),
        'kitsType'=> Settings::getPost($id, 'kits_type'),
        'kitBasePrice'=> Settings::getPost($id, 'kit_base_price'),
        'kitBasePriceString'=> Settings::getPost($id, 'kit_base_price_string')
    ],
    ['recursive' => true]
);

if (empty($arguments['cart'])) {
    return;
}

$previousStep = null;
$showProductsHeader = true;
?>
<style>
    @page {
        size: A4;
        margin: <?php
        echo ($arguments['resultPdfHeaderHeight'] + 1) . 'cm 1.5cm ' . ($arguments['resultPdfFooterHeight'] + 1) . 'cm';
        ?>;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
    }

    .wcpw-result-pdf-header,
    .wcpw-result-pdf-footer {
        position: fixed;
        left: -1.5cm;
        right: -1.5cm;
        width: 21cm;
    }

    .wcpw-result-pdf-header {
        top: <?php echo '-' . ($arguments['resultPdfHeaderHeight'] + 1) . 'cm'; ?>;
        height: <?php echo "{$arguments['resultPdfHeaderHeight']}cm"; ?>;
    }

    .wcpw-result-pdf-footer {
        bottom: <?php echo '-' . ($arguments['resultPdfFooterHeight'] + 1) . 'cm'; ?>;
        height: <?php echo "{$arguments['resultPdfFooterHeight']}cm"; ?>;
    }

    .wcpw-result-pdf-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .wcpw-result-pdf-page-number::after {
        content: counter(page);
    }

    th.wcpw-result-pdf-table-header-cell,
    th.wcpw-result-pdf-total-footer-cell {
        background-color: #f5f5f5;
    }

    .wcpw-result-pdf-table-header-cell,
    .wcpw-result-pdf-table-body-cell,
    .wcpw-result-pdf-total-footer-cell {
        padding: 0.33em 0.5em;
        border: 1px solid #ccc;
        border-left: 0;
    }

    .wcpw-result-pdf-table-header-cell:first-child,
    .wcpw-result-pdf-table-body-cell:first-child,
    .wcpw-result-pdf-total-footer-cell:first-child {
        border-left: 1px solid #ccc;
    }

    .wcpw-result-pdf-total-footer-cell.is-value,
    .wcpw-result-pdf-table-body-cell.is-price,
    .wcpw-result-pdf-table-body-cell.is-subtotal {
        white-space: nowrap;
    }

    .wcpw-result-pdf-item-data {
        margin-top: 0.5cm;
    }

    .wcpw-result-pdf-item-data:empty {
        display: none;
    }

    .wcpw-result-pdf-item-value img {
        display: block;
        max-width: 8cm;
        width: auto;
        height: auto;
    }

    td,
    th {
        vertical-align: top;
    }
</style>
<div class="wcpw-result-pdf-header"><?php
    echo Utils::replaceImagesToBase64InHtml(apply_filters('the_content', $arguments['resultPdfHeaderContent']));
    ?></div>
<div class="wcpw-result-pdf-footer"><?php
    echo Utils::replaceImagesToBase64InHtml(apply_filters('the_content', $arguments['resultPdfFooterContent']));
    ?></div>
<div class="wcpw-result-pdf-description top"><?php
    echo Utils::replaceImagesToBase64InHtml(apply_filters('the_content', $arguments['resultPdfTopDescription']));
    ?></div>
<table class="wcpw-result-pdf-table">
    <?php
    if ($arguments['groupProductsIntoKits'] && $arguments['kitsType'] == 'combined'
        && $arguments['kitBasePrice'] && !$arguments['hidePrices']
    ) {
        ?>
        <thead class="wcpw-result-pdf-table-header">
            <tr class="wcpw-result-pdf-table-header-row is-kit-base-price">
                <th class="wcpw-result-pdf-table-header-cell is-caption"
                    align="left"
                    colspan="4"><?php
                    echo wp_kses_post($arguments['kitBasePriceString']);
                    ?></th>
                <td class="wcpw-result-pdf-table-header-cell is-value"
                    align="center"><?php
                    echo wc_price((float) $arguments['kitBasePrice']);
                    ?></td>
            </tr>
        </thead>
        <?php
    }
    ?>
    <tbody class="wcpw-result-pdf-table-body">
        <?php
        foreach ($arguments['cart'] as $cartItemKey => $cartItem) {
            if ((isset($cartItem['data']) && (!$cartItem['data'] || !$cartItem['data']->exists()))
                || (isset($cartItem['quantity']) && $cartItem['quantity'] <= 0)
                || (isset($cartItem['value']) && empty($cartItem['value']))
                || !isset($cartItem['step_id'])
            ) {
                continue;
            }

            if ($showProductsHeader && isset($cartItem['product_id'], $cartItem['data']) && $cartItem['data']) {
                ?>
                <tr class="wcpw-result-pdf-table-body-row is-products">
                    <th class="wcpw-result-pdf-table-header-cell is-thumbnail"></th>
                    <th class="wcpw-result-pdf-table-header-cell is-data" align="left"><?php
                        echo wp_kses_post($arguments['dataString']);
                        ?></th>
                    <?php if (!$arguments['hidePrices']) { ?>
                        <th class="wcpw-result-pdf-table-header-cell is-price"><?php
                            echo wp_kses_post($arguments['priceString']);
                            ?></th>
                    <?php } ?>
                    <th class="wcpw-result-pdf-table-header-cell is-quantity"><?php
                        echo wp_kses_post($arguments['quantityString']);
                        ?></th>
                    <?php if (!$arguments['hidePrices']) { ?>
                        <th class="wcpw-result-pdf-table-header-cell is-subtotal"><?php
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
                <tr class="wcpw-result-pdf-table-body-row is-heading <?php
                    echo esc_attr("is-step-{$cartItem['step_id']}");
                    ?>"><th class="wcpw-result-pdf-table-body-cell"
                    colspan="<?php echo $arguments['hidePrices'] ? 3 : 5; ?>"><?php
                        echo wp_kses_post($arguments['steps'][$cartItem['step_id']]['name'])
                        ?></th></tr><?php
            }
            ?>
            <tr class="wcpw-result-pdf-table-body-row is-item <?php
                echo esc_attr("is-step-{$cartItem['step_id']}");
                ?>">
                <?php
                if (isset($cartItem['product_id'], $cartItem['data']) && $cartItem['data']) {
                    $product = $cartItem['data'];
                    ?>
                    <td class="wcpw-result-pdf-table-body-cell is-thumbnail" align="center" width="60">
                        <?php
                        $path = Utils::getThumbnailPath($product->get_image_id(), 'shop_thumbnail');
    
                        if ($path) {
                            ?>
                            <img src="<?php echo esc_url($path); ?>"
                                alt="<?php
                                if (method_exists($product, 'get_name')) {
                                    echo $product->get_name();
                                }
                                ?>"
                                width="80"
                                class="wcpw-result-pdf-item-thumbnail">
                            <?php
                        }
                        ?>
                    </td>
                    <td class="wcpw-result-pdf-table-body-cell is-data">
                        <div class="wcpw-result-pdf-item-title"><?php
                            if (method_exists($product, 'get_name')) {
                                echo $product->get_name();
                            }
                            ?></div>
                        <div class="wcpw-result-pdf-item-data">
                            <?php
                            // Localization variations
                            $cartItemLocalized = $cartItem;
    
                            if (isset($cartItem['variation'])) {
                                foreach ($cartItemLocalized['variation'] as &$variationsItem) {
                                    $variationsItem = urldecode($variationsItem);
                                }
                            }
    
                            if (function_exists('wc_get_formatted_cart_item_data')) {
                                echo wc_get_formatted_cart_item_data($cartItemLocalized, true);
                            } else {
                                echo \WC()->cart->get_item_data($cartItemLocalized);
                            }
    
                            // Backorder notification
                            if ($product->backorders_require_notification()
                                && $product->is_on_backorder($cartItem['quantity'])
                            ) {
                                echo '<p class="backorder_notification">'
                                    . esc_html__(
                                        'Available on backorder',
                                        'woocommerce'
                                    )
                                    . '</p>';
                            }
                            ?>
                        </div>
                    </td>
                    <?php if (!$arguments['hidePrices']) { ?>
                        <td class="wcpw-result-pdf-table-body-cell is-price" align="center" width="60">
                            <span class="wcpw-result-pdf-item-price<?php
                                echo $product->get_price() == 0 ? ' is-zero-price ' : '';
                                ?>"><?php echo wc_price($product->get_price()); ?></span>
                        </td>
                    <?php } ?>
                    <td class="wcpw-result-pdf-table-body-cell is-quantity" align="center" width="60">
                        <span class="wcpw-result-pdf-item-quantity"><?php
                            echo $cartItem['quantity'];
                            ?></span>
                    </td>
                    <?php
                    if (!$arguments['hidePrices']) {
                        ?>
                        <td class="wcpw-result-pdf-table-body-cell is-subtotal" align="center" width="60">
                            <span class="wcpw-result-pdf-item-subtotal<?php
                                echo $product->get_price() == 0 ? ' is-zero-price ' : '';
                                ?>"><?php echo wc_price($product->get_price() * $cartItem['quantity']); ?></span>
                        </td>
                        <?php
                    }
                } elseif (isset($cartItem['value'], $cartItem['key']) && !empty($cartItem['value'])
                    && !empty($cartItem['key'])
                ) {
                    $showProductsHeader = true;
                    ?>
                    <th class="wcpw-result-pdf-table-body-cell is-name"
                        colspan="<?php echo $arguments['hidePrices'] ? 1 : 2; ?>" align="left">
                        <span class="wcpw-result-pdf-item-name"><?php
                            echo wp_kses_post($cartItem['key']);
                            ?></span>
                    </th>
                    <td class="wcpw-result-pdf-table-body-cell is-value"
                        colspan="<?php echo $arguments['hidePrices'] ? 2 : 3; ?>">
                        <span class="wcpw-result-pdf-item-value"><?php
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
        <tfoot class="wcpw-result-pdf-total-footer">
            <tr class="wcpw-result-pdf-total-footer-row is-total">
                <th class="wcpw-result-pdf-total-footer-cell is-caption" align="left"
                    colspan="4"><?php
                    echo wp_kses_post($arguments['totalString']);
                    ?></th>
                <td class="wcpw-result-pdf-total-footer-cell is-value" align="center"
                    data-th="<?php echo esc_attr($arguments['totalString']); ?>"><?php
                    echo wc_price($arguments['cartTotal']);
                    ?></td>
            </tr>
        </tfoot>
    <?php } ?>
</table>
<div class="wcpw-result-pdf-description bottom"><?php
    echo Utils::replaceImagesToBase64InHtml(apply_filters('the_content', $arguments['resultPdfBottomDescription']));
    ?></div>

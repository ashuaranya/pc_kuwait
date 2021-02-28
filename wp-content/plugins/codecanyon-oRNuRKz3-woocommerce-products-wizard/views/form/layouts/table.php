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

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'queryArgs' => [],
    'titleString' => Settings::getPost($id, 'table_layout_title_string'),
    'thumbnailString' => Settings::getPost($id, 'table_layout_thumbnail_string'),
    'toCartString' => Settings::getPost($id, 'table_layout_to_cart_string'),
    'priceString' => Settings::getPost($id, 'table_layout_price_string'),
    'hidePrices' => false,
    'hideChooseElement' => false,
    'enableTitleLink' => false,
    'severalProducts' => false,
    'thumbnailSize' => [48, 48],
    'showFooterPrice' => false,
    'showFooterChoose' => false
]);

$productsQuery = new WP_Query($arguments['queryArgs']);
$inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
?>
<table class="woocommerce-products-wizard-form-layout is-table products woocommerce-products-wizard-form-table table
    table-bordered table-hover wcpw-table-responsive"
    data-component="wcpw-products-table">
    <thead class="woocommerce-products-wizard-form-table-header">
        <tr>
            <th class="woocommerce-products-wizard-form-table-header-thumbnail"><?php
                echo wp_kses_post($arguments['thumbnailString']);
                ?></th>
            <th class="woocommerce-products-wizard-form-table-header-title"><?php
                echo wp_kses_post($arguments['titleString']);
                ?></th>
            <?php if (!$arguments['hidePrices']) { ?>
                <th class="woocommerce-products-wizard-form-table-header-price"><?php
                    echo wp_kses_post($arguments['priceString']);
                    ?></th>
            <?php } ?>
            <th class="woocommerce-products-wizard-form-table-header-cart"><?php
                echo wp_kses_post($arguments['toCartString']);
                ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($productsQuery->have_posts()) {
            $productsQuery->the_post();

            global $product;

            if (!$product instanceof WC_Product) {
                continue;
            }

            $arguments['product'] = $product;
            $arguments['cartItem'] =
                Cart::getProductById($arguments['id'], $product->get_id(), $arguments['stepId']);

            // EPO product default data pass
            $_POST = !empty($arguments['cartItem']) ? $arguments['cartItem']['request'] : [];
            ?>
            <tr class="woocommerce-products-wizard-form-table-item<?php
                echo $arguments['cartItem'] ? ' is-in-cart' : '';
                ?>"
                data-component="wcpw-product"
                data-type="<?php echo esc_attr($product->get_type()); ?>"
                data-id="<?php echo esc_attr($product->get_id()); ?>"
                data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"<?php
                echo $arguments['cartItem']
                    ? (' data-cart-key="' . esc_attr($arguments['cartItem']['key']) . '"')
                    : '';
                ?>>
                <td class="woocommerce-products-wizard-form-table-item-thumbnail-wrapper"><?php
                    Template::html('form/item/prototype/thumbnail', $arguments);
                    ?></td>
                <td class="woocommerce-products-wizard-form-table-item-title-wrapper">
                    <div class="woocommerce-products-wizard-form-table-item-check<?php
                    echo !$arguments['hideChooseElement']
                        ? esc_attr(' form-check custom-control custom-' . $inputType)
                        : '';
                    ?>">
                        <?php Template::html('form/item/prototype/choose', $arguments); ?>
                        <label class="woocommerce-products-wizard-form-table-item-title"
                            for="woocommerce-products-wizard-form-item-choose-<?php
                            echo esc_attr($product->get_id());
                            ?>"><?php
                            if ($arguments['enableTitleLink']) {
                                echo '<a href="' . $product->get_permalink() . '" target="_blank" '
                                    . 'class="woocommerce-products-wizard-outer-link '
                                    . 'woocommerce-products-wizard-form-table-item-title-link">';
                            }

                            echo $product->get_title();

                            if ($arguments['enableTitleLink']) {
                                echo '</a>';
                            }
                            ?></label>
                    </div>
                    <?php Template::html('form/item/prototype/description', $arguments); ?>
                </td>
                <?php if (!$arguments['hidePrices']) { ?>
                    <td class="woocommerce-products-wizard-form-table-item-price-wrapper"><?php
                        Template::html('form/item/prototype/price', $arguments);
                        ?></td>
                <?php } ?>
                <td class="woocommerce-products-wizard-form-table-item-cart-wrapper"><?php
                    Template::html('form/item/prototype/footer', $arguments);
                    ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php
Template::html('form/pagination', array_merge(['productsQuery' => $productsQuery], $arguments));

$productsQuery->reset_postdata();

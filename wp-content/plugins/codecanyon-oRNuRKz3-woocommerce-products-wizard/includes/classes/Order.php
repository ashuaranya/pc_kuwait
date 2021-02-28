<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Order Class
 *
 * @class Order
 * @version 1.2.1
 */
class Order
{
    /** Class Constructor */
    public function __construct()
    {
        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'metaFilter']);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'productDataFilter'], 10, 4);
        add_action('woocommerce_order_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'orderProductDataAction'], 20, 4);
        add_filter('woocommerce_admin_order_item_thumbnail', [$this, 'adminOrderItemThumbnailFilter'], 10, 3);
        add_filter('woocommerce_order_item_thumbnail', [$this, 'orderItemThumbnailFilter'], 10, 2);
    }

    /**
     * Order product hidden meta filter
     *
     * @param array $items
     *
     * @return array
     */
    public function metaFilter($items)
    {
        $items[] = '_wcpw_thumbnail';
        $items[] = '_wcpw_is_hidden_product';

        return $items;
    }

    /**
     * Order product data filter
     *
     * @param \WC_Order_Item_Product $item
     * @param string $cartItemKey
     * @param array $values
     * @param \WC_Order $order
     */
    public function productDataFilter($item, $cartItemKey, $values, $order)
    {
        // add invisibility product meta
        if (isset($values['wcpw_is_hidden_product'])) {
            $item->add_meta_data('_wcpw_is_hidden_product', $values['wcpw_is_hidden_product']);
        }

        // add children to a grouped kit product
        if (!empty($values['wcpw_kit_children'])) {
            foreach ($values['wcpw_kit_children'] as $child) {
                $data = Cart::getKitChildData($child, $values['wcpw_id'], ['pureUploadsNames' => false]);
                $item->add_meta_data($data['key'], $data['value']);
            }

            return;
        }

        // add kit id to an order's lines
        if (!empty($values['wcpw_kit_id'])) {
            static $kitNumber = 0;
            static $kitId = null;

            if ($kitId != $values['wcpw_kit_id']) {
                $kitNumber++;
                $kitId = $values['wcpw_kit_id'];
            }

            $item->add_meta_data($values['wcpw_kit_title'], $kitNumber);
        }
    }

    /**
     * Order product visibility filter
     *
     * @param bool $visible
     * @param \WC_Order_Item_Product $item
     *
     * @return bool
     */
    public function itemVisibilityFilter($visible, $item)
    {
        $meta = $item->get_meta_data();

        foreach ($meta as $metaItem) {
            $itemData = $metaItem->get_data();

            if ($itemData['key'] != '_wcpw_is_hidden_product') {
                continue;
            }

            return !$itemData['value'];
        }

        return $visible;
    }

    /**
     * Order product data action
     *
     * @param \WC_Order_Item_Product $item
     * @param string $cartItemKey
     * @param array $values
     * @param \WC_Order $order
     */
    public function orderProductDataAction($item, $cartItemKey, $values, $order)
    {
        if (isset($values['wcpw_kit_thumbnail_url'])) {
            $item->add_meta_data('_wcpw_thumbnail', $values['wcpw_kit_thumbnail_url']);
        }
    }

    /**
     * Order product thumbnail filter
     *
     * @param string $image
     * @param object $item
     *
     * @return string
     */
    public function orderItemThumbnailFilter($image, $item)
    {
        $meta = $item->get_meta_data();

        foreach ($meta as $metaItem) {
            $itemData = $metaItem->get_data();

            if ($itemData['key'] != '_wcpw_thumbnail') {
                continue;
            }

            $image = $itemData['value'];
        }

        return $image;
    }

    /**
     * Order product thumbnail filter in the admin part
     *
     * @param string $image
     * @param integer $id
     * @param object $item
     *
     * @return string
     */
    public function adminOrderItemThumbnailFilter($image, $id, $item)
    {
        if (!method_exists($item, 'get_meta_data')) {
            return $image;
        }

        $meta = $item->get_meta_data();

        foreach ($meta as $metaItem) {
            $itemData = $metaItem->get_data();

            if ($itemData['key'] != '_wcpw_thumbnail') {
                continue;
            }

            $attributes = [
                'src' => $itemData['value'],
                'alt' => get_the_title($item->get_id())
            ];

            $attributes = apply_filters('wcProductsWizardOrderItemGeneratedThumbnailAttributes', $attributes, $itemData);
            $output = '<img ' . Utils::attributesArrayToString($attributes) . '>';

            return apply_filters('wcProductsWizardOrderItemGeneratedThumbnail', $output, $itemData);
        }

        return $image;
    }
}

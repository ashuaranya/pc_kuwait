<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Cart Class
 *
 * @class Cart
 * @version 5.6.2
 */
class Cart
{
    /**
     * Session key variable
     * @var string
     */
    public static $sessionKey = 'woocommerce-products-wizard-cart';

    /**
     * Array of items for a further work
     * @var array
     */
    public $itemsBuffer = [];

    /** Class Constructor */
    public function __construct()
    {
        // cart output filters
        add_filter('woocommerce_cart_item_remove_link', [$this, 'itemRemoveLinkFilter'], 10, 2);
        add_filter('woocommerce_cart_item_quantity', [$this, 'itemQuantityFilter'], 10, 3);
        add_filter('woocommerce_cart_item_class', [$this, 'itemClass'], 10, 3);
        add_filter('woocommerce_get_item_data', [$this, 'itemDataFilter'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'beforeCalculateFilter'], 20);
        add_filter('woocommerce_widget_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_checkout_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'itemPriceFilter'], 30, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'itemSubTotalFilter'], 30, 3);
        add_filter('woocommerce_cart_item_thumbnail', [$this, 'itemThumbnailFilter'], 10, 2);

        // item quantity update
        add_action('woocommerce_after_cart_item_quantity_update', [$this, 'quantityUpdateAction'], 10, 4);

        // items remove filters
        add_action('woocommerce_remove_cart_item', [$this, 'itemRemoveAction']);
        add_action('woocommerce_cart_item_removed', [$this, 'itemAfterRemoveAction']);
        add_action('woocommerce_before_cart_item_quantity_zero', [$this, 'itemRemoveAction'], 10);
        add_action('woocommerce_before_cart_item_quantity_zero', [$this, 'itemAfterRemoveAction'], 11);

        // items restore filters
        add_action('woocommerce_restore_cart_item', [$this, 'itemRestoreAction']);
        add_action('woocommerce_cart_item_restored', [$this, 'itemAfterRestoreAction']);

        // add item filters
        add_filter('woocommerce_add_cart_item_data', [$this, 'addCartItemDataFilter'], 10, 4);
    }

    // <editor-fold desc="Get content">
    /**
     * Get cart from the session
     *
     * @param integer $postId
     * @param array $args
     *
     * @return array
     */
    public static function get($postId, $args = [])
    {
        do_action('wcProductsWizardGetCart', $postId, $args);

        $defaults = [
            'checkDefaultContent' => true,
            'pureUploadsNames' => true,
            'uploadsSourceType' => 'url',
            'includeSteps' => [],
            'excludeSteps' => []
        ];

        $args = array_replace($defaults, $args);

        if (!empty($args['includeSteps']) && !is_array($args['includeSteps'])) {
            $args['includeSteps'] = [(int) $args['includeSteps']];
        }

        if (!empty($args['excludeSteps']) && !is_array($args['excludeSteps'])) {
            $args['excludeSteps'] = [(int) $args['excludeSteps']];
        }

        $cart = [];
        $stepsIds = Settings::getStepsIds($postId);
        $storage = Storage::get(self::$sessionKey, $postId);

        // set session from the default cart
        if (!Storage::exists(self::$sessionKey, $postId) && $args['checkDefaultContent']) {
            $defaultCart = (array) get_post_meta($postId, '_default_cart_content', 1);
            $defaultCart = apply_filters('wcProductsWizardDefaultCartContent', $defaultCart, $postId, $args);

            Storage::set(self::$sessionKey, $postId, $defaultCart);
            $storage = $defaultCart;
        }

        if ($storage) {
            foreach (array_filter((array) $storage) as $key => $item) {
                $item = is_array($item) ? $item : (array) unserialize($item);

                // handle product
                if (isset($item['product_id'], $item['step_id']) && $item['product_id']) {
                    if ((!empty($args['includeSteps']) && !in_array((int) $item['step_id'], $args['includeSteps']))
                        || (!empty($args['excludeSteps']) && in_array((int) $item['step_id'], $args['excludeSteps']))
                    ) {
                        continue;
                    }

                    $productId = isset($item['variation_id']) && $item['variation_id']
                        ? $item['variation_id']
                        : $item['product_id'];

                    $item = apply_filters(
                        'woocommerce_get_cart_item_from_session',
                        array_merge($item, ['data' => wc_get_product($productId)]),
                        $item,
                        $key
                    );
                }

                // handle step data
                if (isset($item['key'], $item['value'])) {
                    if (isset($item['type'], $item['name']) && $item['type'] == 'file') {
                        $ssl = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
                        $item['path'] = $item['value'];
                        $item['url'] = $src = 'http' . ($ssl ? 's' : '') . '://' . $_SERVER['HTTP_HOST']
                            . str_replace($_SERVER['DOCUMENT_ROOT'], '', $item['value']);

                        if (isset($item['is_image']) && $item['is_image']) {
                            switch ($args['uploadsSourceType']) {
                                default:
                                case 'path':
                                    $src = $item['path'];
                                    break;

                                case 'url':
                                    $src = $item['url'];
                            }

                            $size = getimagesize($item['path']);
                            $attributes = [
                                'alt' => basename($item['value']),
                                'src' => $src,
                                'width' => $size[0],
                                'height' => $size[1]
                            ];

                            $attributes = apply_filters(
                                'wcProductsWizardCartStepDataImageAttributes',
                                $attributes,
                                $postId,
                                $item,
                                $key
                            );

                            $item['value'] = '<img ' . Utils::attributesArrayToString($attributes) . '>';
                        } elseif ($args['pureUploadsNames']) {
                            $item['value'] = basename($item['name']);
                        }
                    }

                    $item = apply_filters('wcProductsWizardCartStepData', $item, $postId, $key);
                }

                if (!empty($item)) {
                    $cart[$key] = $item;
                }
            }
        }

        // clear cart from any empty values
        $cart = array_filter($cart);

        //  place steps data upper the products
        uasort($cart, function (array $a, array $b) {
            return isset($a['product_id']);
        });

        // sort cart items by steps
        $cartCopy = $cart;
        $cart = [];

        foreach ($stepsIds as $stepId) {
            foreach ($cartCopy as $key => $item) {
                if (!isset($item['step_id']) || $item['step_id'] != $stepId) {
                    continue;
                }

                $cart[$key] = $item;

                unset($cartCopy[$key]);
            }
        }

        // add items out the steps (added through a redirect)
        $cart = array_diff_key($cartCopy, $cart) + $cart;

        return apply_filters('wcProductsWizardCart', $cart, $postId, $args);
    }

    /**
     * Get products and variations IDs from the cart
     *
     * @param integer $postId
     * @param array $args
     *
     * @return array
     */
    public static function getProductsAndVariationsIds($postId, $args = [])
    {
        $cart = self::get($postId, $args);
        $output = [];

        foreach ($cart as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $output[] = $cartItem['product_id'];

            if (isset($cartItem['variation_id']) && !empty($cartItem['variation_id'])) {
                $output[] = $cartItem['variation_id'];
            }
        }

        return apply_filters('wcProductsWizardCartProductsAndVariationsIds', $output, $postId, $args);
    }

    /**
     * Get categories IDs from the cart
     *
     * @param integer $postId
     * @param array $args
     *
     * @return array
     */
    public static function getCategories($postId, $args = [])
    {
        $cart = self::get($postId, $args);
        $output = [];

        foreach ($cart as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $output = array_merge($output, Product::getCategoriesIds($cartItem['product_id']));
        }

        $output = array_unique($output);

        return apply_filters('wcProductsWizardCartCategoriesIds', $output, $postId, $args);
    }

    /**
     * Get products attribute values from the cart
     *
     * @param integer $postId
     * @param string $attribute
     * @param array $args
     *
     * @return array
     */
    public static function getAttributeValues($postId, $attribute, $args = [])
    {
        $cart = self::get($postId, $args);
        $output = [];

        foreach ($cart as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $output = array_merge($output, Product::getAttributeValuesIds($cartItem['product_id'], $attribute));
        }

        $output = array_unique($output);

        return apply_filters('wcProductsWizardCartAttributeValues', $output, $postId, $attribute, $args);
    }

    /**
     * Get cart by step ID
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return array
     */
    public static function getByStepId($postId, $stepId)
    {
        $cart = self::get($postId, ['includeSteps' => [$stepId]]);

        return apply_filters('wcProductsWizardCartByStepId', $cart, $postId, $stepId);
    }

    /**
     * Get cart item by the key
     *
     * @param integer $postId
     * @param string $key
     *
     * @return array
     */
    public static function getItemByKey($postId, $key)
    {
        $cart = self::get($postId);
        $output = isset($cart[$key]) ? $cart[$key] : null;

        return apply_filters('wcProductsWizardCartItemByKey', $output, $postId, $key);
    }

    /**
     * Return cart product data or null
     *
     * @param integer $postId
     * @param integer $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool|null
     */
    public static function getProductById($postId, $productId, $stepId = null)
    {
        $cart = self::get($postId);
        $output = null;

        foreach ($cart as $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $cartItem['product_id'] == $productId && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                $output = $cartItem;

                break;
            }
        }

        return apply_filters('wcProductsWizardCartProductById', $output, $postId, $productId, $stepId);
    }

    /**
     * Get product cart key by product ID
     *
     * @param integer $postId
     * @param integer $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool|null
     */
    public static function getProductKeyById($postId, $productId, $stepId = null)
    {
        $cart = self::get($postId);

        foreach ($cart as $cartItemKey => $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $cartItem['product_id'] == $productId && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                return $cartItemKey;
            }
        }

        return null;
    }

    /**
     * Get product cart key by variation data
     *
     * @param integer $postId
     * @param integer $variationId
     * @param array $variation
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool|null
     */
    public static function getProductKeyByVariation($postId, $variationId, $variation, $stepId = null)
    {
        $cart = self::get($postId);

        foreach ($cart as $cartItemKey => $cartItem) {
            if (isset($cartItem['variation_id']) && $cartItem['variation_id'] == $variationId
                && $variation == $cartItem['variation'] && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                return $cartItemKey;
            }
        }

        return null;
    }

    /**
     * Get step data by key
     *
     * @param integer $postId
     * @param string $key
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool|null
     */
    public static function getStepDataByKey($postId, $key, $stepId = null)
    {
        $cart = self::get($postId);
        $output = null;

        foreach ($cart as $cartItemKey => $cartItem) {
            if (isset($cartItem['key']) && $cartItem['key'] == $key
                && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                $output = $cartItem;

                break;
            }
        }

        return apply_filters('wcProductsWizardCartStepDataByKey', $output, $postId, $key, $stepId);
    }

    /**
     * Check product is in the cart
     *
     * @param integer $postId
     * @param integer $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool
     */
    public static function productIsset($postId, $productId, $stepId = null)
    {
        return (bool) self::getProductById($postId, $productId, $stepId);
    }

    /**
     * Check product variation is in the cart
     *
     * @param integer $postId
     * @param integer $variationId
     * @param array $variation
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool
     */
    public static function variationIsset($postId, $variationId, $variation, $stepId = null)
    {
        return (bool) self::getProductKeyByVariation($postId, $variationId, $variation, $stepId);
    }

    /**
     * Check product category is in the cart
     *
     * @param integer $postId
     * @param integer $categoryId
     *
     * @return bool
     */
    public static function categoryIsset($postId, $categoryId)
    {
        return in_array($categoryId, self::getCategories($postId));
    }
    // </editor-fold>

    // <editor-fold desc="Adding">
    /**
     * Add a product to the cart
     *
     * @param integer $postId - wizard ID
     * @param array $itemData - product data
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function addProduct($postId, $itemData)
    {
        $defaults = [
            'product_id' => null,
            'quantity' => null,
            'variation_id' => '',
            'variation' => [],
            'data' => null,
            'request' => []
        ];

        $itemData = array_replace($defaults, $itemData);
        $cartItem = $itemData;

        // ensure we don't add a variation to the cart directly by variation ID
        $id = (isset($cartItem['variation_id']) && !empty($cartItem['variation_id']))
            ? (int) $cartItem['variation_id']
            : (int) $cartItem['product_id'];

        $variation = isset($cartItem['variation']) ? $cartItem['variation'] : [];
        $variationId = (isset($cartItem['variation_id']) && !empty($cartItem['variation_id']))
            ? $cartItem['variation_id']
            : 0;

        $cartItem['data'] = !empty($cartItem['data']) ? $cartItem['data'] : [];
        $cartItem['data']['step_id'] = $cartItem['step_id'];

        // emulate post data passing
        if (!empty($cartItem['request'])) {
            $_POST = $cartItem['request'];
        }

        // load cart item data - might be added by other plugins
        $cartItemData = (array) apply_filters(
            'woocommerce_add_cart_item_data',
            $cartItem['data'],
            $cartItem['product_id'],
            $variationId,
            $cartItem['quantity']
        );

        // sanitize variations
        if (isset($variation) && is_array($variation)) {
            foreach ($variation as &$variationItem) {
                $variationItem = sanitize_text_field($variationItem);
            }
        }

        // generate key
        $cartItemKey = WC()->cart->generate_cart_id(
            $cartItem['product_id'],
            $variationId,
            $variation,
            $cartItemData
        );

        $cartItem = apply_filters(
            'woocommerce_add_cart_item',
            array_merge(
                $cartItemData,
                [
                    'key' => $cartItemKey,
                    'product_id' => $cartItem['product_id'],
                    'variation_id' => isset($cartItem['variation_id']) ? $cartItem['variation_id'] : '',
                    'variation' => isset($cartItem['variation']) ? $cartItem['variation'] : [],
                    'step_id' => $cartItem['step_id'],
                    'quantity' => (float) $cartItem['quantity'],
                    'request' => $cartItem['request'],
                    'sold_individually' => isset($cartItem['sold_individually'])
                        ? $cartItem['sold_individually']
                        : Settings::getStep($postId, $cartItem['step_id'], 'sold_individually'),
                    'data' => wc_get_product($id)
                ]
            ),
            $cartItemKey
        );

        $cartItem = apply_filters('wcProductsWizardAddToCartItem', $cartItem, $postId, self::$sessionKey);
        $dontAddToCartProducts = (array) Settings::getStep($postId, $cartItem['step_id'], 'dont_add_to_cart_products');

        do_action('wcProductsWizardBeforeAddToCart', $postId, $cartItemKey, $cartItem);

        // add to the session variable
        Storage::set(self::$sessionKey, $postId, $cartItem, $cartItemKey);

        if (Settings::getPost($postId, 'reflect_in_main_cart')
            && !Settings::getPost($postId, 'group_products_into_kits')
            && !Settings::getStep($postId, $cartItem['step_id'], 'dont_add_to_cart')
            && !in_array($cartItem['product_id'], $dontAddToCartProducts)
            && (!isset($cartItem['variation_id']) || !in_array($cartItem['variation_id'], $dontAddToCartProducts))
        ) {
            // reflect to the main cart
            $itemData['data']['wcpw_id'] = $postId;
            $itemData['data']['wcpw_is_cart_bond'] = true;

            Product::addToMainCart($itemData);
        }

        do_action('wcProductsWizardAfterAddToCart', $postId, $cartItemKey, $cartItem);

        return $cartItemKey;
    }

    /**
     * Add a step data line to the cart
     *
     * @param integer $postId - wizard ID
     * @param array $data - step data
     *
     * @return string
     */
    public static function addStepData($postId, $data)
    {
        $defaults = [
            'key' => null,
            'step_id' => null,
            'value' => null,
            'type' => 'string'
        ];

        $cartItem = array_replace($defaults, $data);
        $cartItemKey = $cartItem['step_id'] . '-' . $cartItem['key'];
        $cartItem = apply_filters('wcProductsWizardAddToCartItem', $cartItem, $postId, self::$sessionKey);

        do_action('wcProductsWizardBeforeAddToCart', $postId, $cartItemKey, $cartItem);

        Storage::set(self::$sessionKey, $postId, $cartItem, $cartItemKey);

        do_action('wcProductsWizardAfterAddToCart', $postId, $cartItemKey, $cartItem);

        return $cartItemKey;
    }

    /**
     * Add to cart product data filter
     *
     * @param array $data
     *
     * @return array
     */
    public function addCartItemDataFilter($data)
    {
        if (isset($_REQUEST['wcpw_kit_id'])) {
            $data['wcpw_kit_id'] = esc_sql($_REQUEST['wcpw_kit_id']);
            $data['wcpw_kit_title'] = esc_sql($_REQUEST['wcpw_kit_title']);
            $data['wcpw_is_kit_base'] = (int) esc_sql($_REQUEST['wcpw_is_kit_base']);
            $data['wcpw_is_kit_quantity_fixed'] = (int) esc_sql($_REQUEST['wcpw_is_kit_quantity_fixed']);
        }

        return $data;
    }
    // </editor-fold>

    // <editor-fold desc="Remove and clear">
    /**
     * Remove the product from the cart by the product id
     *
     * @param integer $postId
     * @param integer|string $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool
     */
    public static function removeByProductId($postId, $productId, $stepId = null)
    {
        do_action('wcProductsWizardBeforeRemoveByProductId', $postId, $productId, $stepId);

        $productKey = self::getProductKeyById($postId, $productId, $stepId);

        if (!$productKey) {
            return false;
        }

        self::removeByProductKey($postId, $productKey);

        do_action('wcProductsWizardAfterRemoveByProductId', $postId, $productId, $stepId);

        return true;
    }
    
    /**
     * Remove the product from the cart by variation data
     *
     * @param integer $postId
     * @param integer|string $variationId
     * @param array $variation
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool
     */
    public static function removeByVariation($postId, $variationId, $variation, $stepId = null)
    {
        do_action('wcProductsWizardBeforeRemoveByVariation', $postId, $variationId, $variation, $stepId);

        $productKey = self::getProductKeyByVariation($postId, $variationId, $variation, $stepId);

        if (!$productKey) {
            return false;
        }

        self::removeByProductKey($postId, $productKey);

        do_action('wcProductsWizardAfterRemoveByVariation', $postId, $variationId, $variation, $stepId);

        return true;
    }

    /**
     * Remove the product from the cart by the product cart key
     *
     * @param integer $postId
     * @param integer|string $key
     *
     * @return bool
     */
    public static function removeByProductKey($postId, $key)
    {
        do_action('wcProductsWizardBeforeRemoveByProductKey', $postId, $key);

        if (Settings::getPost($postId, 'reflect_in_main_cart')
            && apply_filters('wcProductsWizardRemoveMainCartReflectedProducts', true)
        ) {
            // reflect to the main cart
            $product = Storage::get(self::$sessionKey, $postId, $key);

            if (isset($product['product_id']) && $product['product_id']) {
                foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
                    if (!isset($cartItem['wcpw_id'], $cartItem['wcpw_is_cart_bond'], $cartItem['product_id'])
                        || $cartItem['wcpw_id'] != $postId
                        || !$cartItem['wcpw_is_cart_bond']
                        || $cartItem['product_id'] != $product['product_id']
                    ) {
                        continue;
                    }

                    \WC()->cart->remove_cart_item($cartItemKey);
                }
            }
        }

        Storage::remove(self::$sessionKey, $postId, $key);

        do_action('wcProductsWizardAfterRemoveByProductKey', $postId, $key);

        return true;
    }

    /**
     * Remove the products from the cart by step Id
     *
     * @param integer $postId
     * @param integer|string $stepId
     */
    public static function removeByStepId($postId, $stepId)
    {
        do_action('wcProductsWizardBeforeRemoveByStepId', $postId, $stepId);

        $cart = self::get($postId);

        foreach ($cart as $key => $item) {
            if ($item['step_id'] != $stepId) {
                continue;
            }

            self::removeByProductKey($postId, $key);
        }

        do_action('wcProductsWizardAfterRemoveByStepId', $postId, $stepId);
    }

    /**
     * Truncate the cart
     *
     * @param integer $postId
     */
    public static function truncate($postId)
    {
        do_action('wcProductsWizardBeforeTruncate', $postId);

        Storage::remove(self::$sessionKey, $postId);

        if (Settings::getPost($postId, 'reflect_in_main_cart')) {
            // reflect to the main cart
            \WC()->cart->empty_cart();
        }

        do_action('wcProductsWizardAfterTruncate', $postId);
    }

    /**
     * Woocommerce cart item removing action
     *
     * @param string $itemKey
     */
    public function itemRemoveAction($itemKey)
    {
        // avoid for recursion of actions calls
        add_filter('wcProductsWizardRemoveMainCartReflectedProducts', '__return_false');

        $cart = \WC()->cart->get_cart();
        $itemData = \WC()->cart->get_cart_item($itemKey);

        // remove this product from the wizard with the reflecting cart option
        if (isset($itemData['wcpw_id'], $itemData['wcpw_is_cart_bond']) && $itemData['wcpw_is_cart_bond']) {
            self::removeByProductId($itemData['wcpw_id'], $itemData['product_id']);
        }

        // remove products from the same kit
        if (isset($itemData['wcpw_kit_id']) && $itemData['wcpw_kit_id'] && $itemData['wcpw_is_kit_base']) {
            foreach ($cart as $cartItemKey => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $cartItem['wcpw_is_kit_base']
                    || $cartItem['wcpw_kit_id'] != $itemData['wcpw_kit_id']
                    || $itemKey == $cartItemKey
                ) {
                    continue;
                }

                $this->itemsBuffer[] = $cartItemKey;
            }
        }
    }

    /** Woocommerce cart item after removing action */
    public function itemAfterRemoveAction()
    {
        // remove all items in the buffer
        if (!empty($this->itemsBuffer)) {
            foreach ($this->itemsBuffer as $cartItemKey) {
                \WC()->cart->remove_cart_item($cartItemKey);
            }

            // clear buffer
            $this->itemsBuffer = [];
        }
    }

    /**
     * Woocommerce cart item restoring action
     *
     * @param string $itemKey
     */
    public function itemRestoreAction($itemKey)
    {
        $removed = \WC()->cart->get_removed_cart_contents();
        $itemData = $removed[$itemKey];

        // restore products from the same kit
        if (isset($itemData['wcpw_kit_id']) && $itemData['wcpw_kit_id'] && $itemData['wcpw_is_kit_base']) {
            foreach ($removed as $cartItemKey => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $cartItem['wcpw_is_kit_base']
                    || $cartItem['wcpw_kit_id'] != $itemData['wcpw_kit_id']
                    || $itemKey == $cartItemKey
                ) {
                    continue;
                }

                $this->itemsBuffer[] = $cartItemKey;
            }
        }
    }

    /** Woocommerce cart item after restoring action */
    public function itemAfterRestoreAction()
    {
        // restore all items in the buffer
        if (!empty($this->itemsBuffer)) {
            foreach ($this->itemsBuffer as $cartItemKey) {
                \WC()->cart->restore_cart_item($cartItemKey);
            }

            // clear buffer
            $this->itemsBuffer = [];
        }
    }
    // </editor-fold>

    // <editor-fold desc="Get price">
    /**
     * Get the total price of the cart
     *
     * @param integer $postId
     * @param array $args
     *
     * @return number
     */
    public static function getTotal($postId, $args = [])
    {
        $defaults = ['reCalculateDiscount' => false];
        $args = array_replace($defaults, $args);

        $groupProductsIntoKits = Settings::getPost($postId, 'group_products_into_kits');
        $kitsType = Settings::getPost($postId, 'kits_type');
        $kitBasePrice = Settings::getPost($postId, 'kit_base_price');
        $output = $groupProductsIntoKits && $kitsType == 'combined' && $kitBasePrice
            ? (float) $kitBasePrice
            : 0;

        foreach (self::get($postId) as $cartItem) {
            if (!isset($cartItem['data']) || !$cartItem['data']) {
                continue;
            }

            $product = $cartItem['data'];

            if (!$product || !$product->exists() || $cartItem['quantity'] <= 0) {
                continue;
            }

            // some problems are possible while PDF sending via CF7
            if (function_exists('WC') && property_exists(\WC(), 'cart')
                && \WC()->cart && property_exists(\WC()->cart, 'display_prices_including_tax')
            ) {
                $includeTaxes = \WC()->cart->display_prices_including_tax();
            } else {
                $includeTaxes = get_option('woocommerce_tax_display_cart') == 'incl';
            }

            if ($includeTaxes) {
                $price = wc_get_price_including_tax($product);
            } else {
                $price = wc_get_price_excluding_tax($product);
            }

            if ($args['reCalculateDiscount']) {
                $productDiscount = null;
                $categoryDiscount = null;

                switch ($product->get_type()) {
                    case 'simple':
                        $productDiscount = Settings::getProduct($product->get_id(), 'discount');
                        break;

                    case 'variation':
                        $productDiscount = Settings::getProductVariation($product->get_id(), 'discount');
                }

                $categories = wp_get_object_terms($product->get_id(), 'product_cat');

                foreach ($categories as $category) {
                    $discount = Settings::getProductCategory($category->term_id, 'discount');

                    if (is_array($discount) && isset($discount['type'], $discount['value'])
                        && !empty($discount['value'])
                    ) {
                        $categoryDiscount = $discount;

                        break;
                    }
                }

                if (is_array($productDiscount) && isset($productDiscount['type'], $productDiscount['value'])
                    && !empty($productDiscount['value'])
                ) {
                    $price = Product::handlePriceWithDiscountRule($price, $productDiscount);
                } elseif ($categoryDiscount) {
                    $price = Product::handlePriceWithDiscountRule($price, $categoryDiscount);
                } else {
                    $commonDiscount = (float) Settings::getPost($postId, 'price_discount');
                    $price = max(0, (float) $price - ($price * $commonDiscount / 100));
                }
            }

            $output += (float) ($price * $cartItem['quantity']);
			
			if($_SESSION['isAssembled'] == 'checked'){							
				global $wpdb;
				$table = $wpdb->prefix.'charges';
				$sql = $wpdb->get_results("SELECT * FROM $table");
				foreach ($sql as $value) {
					if($output >= $value->min_amount && $output <= $value->max_amount){
						$charges = $value->charges;
						$output = $output + $charges;
					}
				}	
			}
			else{
				$output = $output;
			}
        }
        return apply_filters('wcProductsWizardCartTotal', $output, $postId);
    }

	
	
	
	
	
	
	  public function itemAssemblecharges($price, $item, $key)
    {
        if (isset($item['wcpw_kit_price']) && $item['wcpw_kit_price']) {
            return wc_price($item['wcpw_kit_price'] * $item['quantity']);
        }
    }
	
	
	
	
	
	
	
	
	
	
    /**
     * Get item price
     *
     * @param array $cartItem
     *
     * @return number
     */
    public static function getItemPrice($cartItem)
    {
        $price = method_exists($cartItem['data'], 'get_price')
            ? (float) $cartItem['data']->get_price()
            : (float) $cartItem['data']->price;

        $commonDiscount = isset($cartItem['wcpw_discount']) && $cartItem['wcpw_discount']
            ? (float) $cartItem['wcpw_discount']
            : 0;

        $categoryDiscount = isset($cartItem['wcpw_category_discount']) && $cartItem['wcpw_category_discount']
            ? (array) $cartItem['wcpw_category_discount']
            : null;

        $productDiscount = isset($cartItem['wcpw_product_discount']) && $cartItem['wcpw_product_discount']
            ? (array) $cartItem['wcpw_product_discount']
            : null;

        if (isset($cartItem['wcpw_is_base_kit_product']) && $cartItem['wcpw_is_base_kit_product']) {
            // null the base kit product price
            $price = 0;
        }

        if ($productDiscount) {
            $price = Product::handlePriceWithDiscountRule($price, $productDiscount);
        } elseif ($categoryDiscount) {
            $price = Product::handlePriceWithDiscountRule($price, $categoryDiscount);
        } elseif ($commonDiscount) {
            $price = max(0, $price - ($price * $commonDiscount / 100));
        }

        // add extra combined price
        if (isset($cartItem['wcpw_kit_base_price']) && $cartItem['wcpw_kit_base_price']) {
            $price += (float) $cartItem['wcpw_kit_base_price'];
        }

        // calculate total children price
        if (isset($cartItem['wcpw_kit_children']) && is_array($cartItem['wcpw_kit_children'])
            && !empty($cartItem['wcpw_kit_children'])
        ) {
            foreach ($cartItem['wcpw_kit_children'] as $child) {
                if (!isset($child['data']) || !$child['data']) {
                    continue;
                }

                $price += self::getItemPrice($child) * $child['quantity'];
            }
        }

        return apply_filters('wcProductsWizardCartItemPrice', $price, $cartItem);
    }
    // </editor-fold>

    // <editor-fold desc="Setters">
    /**
     * Set item price
     *
     * @param array $cartItem
     * @param float $value
     */
    public static function setItemPrice($cartItem, $value)
    {
        if (method_exists($cartItem['data'], 'set_price')) {
            $cartItem['data']->set_price($value);
        } else {
            $cartItem['data']->price = $value;
        }
    }

    /**
     * Handles on cart item quantity update
     *
     * @param string $itemKey
     * @param integer $newQuantity
     * @param integer $oldQuantity
     * @param object $cart
     */
    public function quantityUpdateAction($itemKey, $newQuantity, $oldQuantity, $cart)
    {
        // change kit products quantity accordingly the base product
        if (isset($cart->cart_contents[$itemKey]['wcpw_is_kit_base'])
            && $cart->cart_contents[$itemKey]['wcpw_is_kit_base']
            && !$cart->cart_contents[$itemKey]['wcpw_is_kit_quantity_fixed']
        ) {
            foreach ($cart->cart_contents as $key => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $itemKey == $key
                    || $cart->cart_contents[$itemKey]['wcpw_kit_id'] != $cartItem['wcpw_kit_id']
                ) {
                    continue;
                }

                if ($oldQuantity == $cartItem['quantity']) {
                    $newChildQuantity = $newQuantity;
                } else {
                    $newChildQuantity = $newQuantity >= $oldQuantity
                        ? $cartItem['quantity'] * $newQuantity
                        : round($cartItem['quantity'] / $oldQuantity);
                }

                $cart->cart_contents[$key]['quantity'] = $newChildQuantity;
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Output">
    /**
     * Filter cart item remove button
     *
     * @param string $html
     * @param string $cartItemKey
     *
     * @return string
     */
    public function itemRemoveLinkFilter($html, $cartItemKey)
    {
        $cartItem = \WC()->cart->get_cart_item($cartItemKey);

        // if isn't from a kit or kit base item
        if (!isset($cartItem['wcpw_kit_id']) || $cartItem['wcpw_is_kit_base']) {
            return $html;
        }

        return '';
    }

    /**
     * Filter cart item quantity input
     *
     * @param string $html
     * @param string $cartItemKey
     * @param array $cartItem
     *
     * @return string
     */
    public function itemQuantityFilter($html, $cartItemKey, $cartItem)
    {
        if (isset($cartItem['wcpw_is_kit_base'])
            && $cartItem['wcpw_is_kit_base']
            && !$cartItem['wcpw_is_kit_quantity_fixed']
        ) {
            return $html;
        }

        if (!isset($cartItem['wcpw_kit_id'])) {
            return $html;
        }

        return $cartItem['quantity'];
    }

    /**
     * Table row class filter
     *
     * @param string $class
     * @param string $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    public function itemClass($class, $cartItem, $cartItemKey)
    {
        if (is_array($cartItem) && isset($cartItem['wcpw_kit_id'], $cartItem['wcpw_is_kit_base'])) {
            $class .= ' wcpw-kit-base';
        }

        return $class;
    }

    /**
     * Returns kit child data
     *
     * @param array $child
     * @param int $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function getKitChildData($child, $wizardId, $args = [])
    {
        $defaults = ['pureUploadsNames' => true];
        $args = array_replace($defaults, $args);
        $key = null;
        $value = null;
        $display = null;

        if (isset($child['data']) && $child['data']) {
            $hidePrices = Settings::getPost($wizardId, 'hide_prices');
            $data = wc_get_formatted_cart_item_data($child, true);
            $price = wc_price(self::getItemPrice($child));
            $key = $child['data']->get_name();
            $valueParts = [trim(preg_replace("/\r|\n/", ', ', $data)), $price, 'x', $child['quantity']];
            $value = implode(
                ' ',
                apply_filters('wcProductsWizardCartKitChildValueParts', $valueParts, $wizardId)
            );

            $display = "<span class=\"wcpw-kit-child\"><span class=\"wcpw-kit-child-meta\">$data</span> "
                . ($hidePrices ? '' : "<span class=\"wcpw-kit-child-price\">$price</span> ")
                . '<bdi class="wcpw-kit-child-times">x</bdi> '
                . "<span class=\"wcpw-kit-child-quantity\">$child[quantity]</span></span>";

            $display = apply_filters('wcProductsWizardCartKitChildDisplay', $display, $wizardId);
        } elseif (isset($child['value']) && $child['value']) {
            $key = $child['key'];
            $value = $child['value'];
            $display = $child['value'];

            if (isset($child['type'], $child['is_image']) && $child['type'] == 'file' && !$child['is_image']) {
                $value = $args['pureUploadsNames'] ? $child['name'] : $child['url'];
                $display = $value;
            }

            $value = apply_filters('wcProductsWizardCartKitChildValueParts', $value, $wizardId);
            $display = apply_filters('wcProductsWizardCartKitChildDisplay', $display, $wizardId);
        }

        $output = [
            'key' => $key,
            'value' => $value,
            'display' => $display,
            'hidden' => false
        ];

        return apply_filters('wcProductsWizardKitChildData', $output);
    }

    /**
     * Item data filter
     *
     * @param array $itemData
     * @param array $cartItem
     *
     * @return array
     */
    public function itemDataFilter($itemData, $cartItem)
    {
        // add children to a combined kit product
        if (isset($cartItem['wcpw_kit_children'], $cartItem['wcpw_kit_type'])
            && $cartItem['wcpw_kit_type'] == 'combined'
        ) {
            $kitBasePrice = isset($cartItem['wcpw_kit_base_price']) && $cartItem['wcpw_kit_base_price']
                ? (float) $cartItem['wcpw_kit_base_price']
                : 0;

            if ($kitBasePrice) {
                $itemData[] = [
                    'key' => isset($cartItem['wcpw_kit_base_price_string'])
                        ? esc_attr($cartItem['wcpw_kit_base_price_string'])
                        : '',
                    'value' => $kitBasePrice,
                    'display' => wc_price($kitBasePrice),
                    'hide' => false
                ];
            }

            foreach ($cartItem['wcpw_kit_children'] as $child) {
                $data = self::getKitChildData($child, $cartItem['wcpw_id']);
                $itemData[] = $data;
            }

            return $itemData;
        }

        // add kit id to an order's lines
        if (isset($cartItem['wcpw_kit_title'], $cartItem['wcpw_kit_id'])
            && !empty($cartItem['wcpw_kit_title']) && !empty($cartItem['wcpw_kit_id'])
        ) {
            $itemData[] = [
                'key' => $cartItem['wcpw_kit_title'],
                'value' => $cartItem['wcpw_kit_id'],
                'display' => '',
                'hidden' => true
            ];
        }

        return $itemData;
    }

    /**
     * Cart calculation action
     *
     * @param object $cart
     */
    public function beforeCalculateFilter($cart)
    {
        if (is_admin() && !defined('DOING_AJAX') || did_action('wcProductsWizardBeforeCalculateTotals')) {
            return;
        }

        do_action('wcProductsWizardBeforeCalculateTotals', $cart);

        $cartContent = $cart->get_cart();

        foreach ($cartContent as $key => &$cartItem) {
            // only for WCPW products
            if (!isset($cartItem['wcpw_id']) || !$cartItem['wcpw_id']) {
                continue;
            }

            // is a kit child product but have no parent
            if (isset($cartItem['wcpw_kit_parent_key']) && !isset($cartContent[$cartItem['wcpw_kit_parent_key']])) {
                $cart->remove_cart_item($key);

                continue;
            }

            // zero price of hidden child product
            if (isset($cartItem['wcpw_is_hidden_product']) && $cartItem['wcpw_is_hidden_product']) {
                self::setItemPrice($cartItem, 0);

                continue;
            }

            // is a kit base product
            if (isset($cartItem['wcpw_kit_children'], $cartItem['wcpw_kit_type'])
                && !empty($cartItem['wcpw_kit_children']) && is_array($cartItem['wcpw_kit_children'])
            ) {
                // change image
                if (isset($cartItem['wcpw_kit_thumbnail_id']) && $cartItem['wcpw_kit_thumbnail_id']) {
                    if (method_exists($cartItem['data'], 'set_image_id')) {
                        $cartItem['data']->set_image_id($cartItem['wcpw_kit_thumbnail_id']);
                    } else {
                        $cartItem['data']->image_id = $cartItem['wcpw_kit_thumbnail_id'];
                    }
                }

                // change visibility
                if (method_exists($cartItem['data'], 'set_catalog_visibility')) {
                    $cartItem['data']->set_catalog_visibility('hidden');

                    // variable products fix
                    if (method_exists($cartItem['data'], 'set_parent_data')
                        && method_exists($cartItem['data'], 'get_parent_data')
                    ) {
                        $parentData = $cartItem['data']->get_parent_data();
                        $parentData['catalog_visibility'] = 'hidden';
                        $cartItem['data']->set_parent_data($parentData);
                    }
                } else {
                    $cartItem['data']->catalog_visibility = 'hidden';
                }

                // set price
                if ($cartItem['wcpw_kit_type'] == 'combined' && isset($cartItem['wcpw_kit_price'])
                    && $cartItem['wcpw_kit_price']
                ) {
                    // set fixed combined price
                    self::setItemPrice($cartItem, (float) $cartItem['wcpw_kit_price']);
                } elseif ($cartItem['wcpw_kit_type'] == 'combined') {
                    // set final kit price
                    self::setItemPrice($cartItem, self::getItemPrice($cartItem));
                } elseif (isset($cartItem['wcpw_is_base_kit_product']) && $cartItem['wcpw_is_base_kit_product']) {
                    // null the real price and show the children price instead
                    $price = self::getItemPrice($cartItem);
                    self::setItemPrice($cartItem, 0);
                    $cartItem['wcpw_kit_price'] = $price;
                    WC()->cart->cart_contents[$key]['wcpw_kit_price'] = $price;
                }

                continue;
            }

            // set final product price with discounts and other
            self::setItemPrice($cartItem, self::getItemPrice($cartItem));
        }

        unset($cartItem);
    }

    /**
     * Product in cart visibility filter
     *
     * @param bool $visible
     * @param array $item
     *
     * @return bool
     */
    public function itemVisibilityFilter($visible, $item)
    {
        if (isset($item['wcpw_is_hidden_product']) && $item['wcpw_is_hidden_product']) {
            $visible = false;
        }

        return $visible;
    }

    /**
     * Filter cart item price
     *
     * @param string $price
     * @param array $item
     * @param string $key
     *
     * @return string
     */
    public function itemPriceFilter($price, $item, $key)
    {
        if (isset($item['wcpw_kit_price']) && $item['wcpw_kit_price']) {
            return wc_price($item['wcpw_kit_price']);
        }

        return $price;
    }

    /**
     * Filter cart item sub total price
     *
     * @param string $price
     * @param array $item
     * @param string $key
     *
     * @return string
     */
    public function itemSubTotalFilter($price, $item, $key)
    {
        if (isset($item['wcpw_kit_price']) && $item['wcpw_kit_price']) {
            return wc_price($item['wcpw_kit_price'] * $item['quantity']);
        }
        return $price;
    }

    /**
     * Filter cart item image
     *
     * @param string $image
     * @param array $itemData
     *
     * @return string
     */
    public function itemThumbnailFilter($image, $itemData)
    {
        if (isset($itemData['wcpw_kit_thumbnail_url'])) {
            $attributes = [
                'src' => $itemData['wcpw_kit_thumbnail_url'],
                'alt' => get_the_title($itemData['product_id'])
            ];

            $attributes = apply_filters('wcProductsWizardCartItemGeneratedThumbnailAttributes', $attributes, $itemData);
            $output = '<img ' . Utils::attributesArrayToString($attributes) . '>';

            return apply_filters('wcProductsWizardCartItemGeneratedThumbnail', $output, $itemData);
        }

        return $image;
    }
    // </editor-fold>

    // <editor-fold desc="Deprecated">
    /**
     * Get products from the cart by the term ID
     *
     * @param integer $postId
     * @param integer $termId
     *
     * @return array
     *
     * @deprecated since 6.0.0 use getProductsByStepId
     */
    public static function getProductsByTermId($postId, $termId)
    {
        return self::getByStepId($postId, $termId);
    }
    // </editor-fold>
}

<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Product Class
 *
 * @class Product
 * @version 6.2.0
 */
class Product
{
    /** Class Constructor */
    public function __construct()
    {
        add_filter('woocommerce_product_get_price', [$this, 'priceFilter'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [$this, 'priceFilter'], 10, 2);
    }

    /**
     * Filter product price filter
     *
     * @param float $price
     * @param \WC_Product $product
     *
     * @return float
     */
    public function priceFilter($price, $product)
    {
        if (!did_action('wcProductsWizardBeforeOutput') || did_action('wcProductsWizardAfterOutput')) {
            return $price;
        }

        static $pricesCache = [];

        $discountType = Settings::getPost(Instance()->activeId, 'price_discount_type');

        if (isset($pricesCache[$product->get_id()])) {
            if ($discountType == 'show-as-sale') {
                $product->set_sale_price($pricesCache[$product->get_id()]);
            }

            return $pricesCache[$product->get_id()];
        }

        $productDiscount = null;
        $categoryDiscount = null;
        $discountedPrice = $price;
        $categories = wp_get_object_terms($product->get_id(), 'product_cat');

        switch ($product->get_type()) {
            case 'simple':
                $productDiscount = Settings::getProduct($product->get_id(), 'discount');
                break;

            case 'variation':
                $productDiscount = Settings::getProductVariation($product->get_id(), 'discount');
        }

        foreach ($categories as $category) {
            $discount = Settings::getProductCategory($category->term_id, 'discount');

            if (is_array($discount) && isset($discount['type'], $discount['value']) && !empty($discount['value'])) {
                $categoryDiscount = $discount;

                break;
            }
        }

        if (is_array($productDiscount) && isset($productDiscount['type'], $productDiscount['value'])
            && !empty($productDiscount['value'])
        ) {
            $discountedPrice = self::handlePriceWithDiscountRule($price, $productDiscount);
        } elseif ($categoryDiscount) {
            $discountedPrice = self::handlePriceWithDiscountRule($price, $categoryDiscount);
        } elseif (Instance()->activeId) {
            $commonDiscount = (float) Settings::getPost(Instance()->activeId, 'price_discount');
            $discountedPrice = max(0, (float) $price - ((float) $price * $commonDiscount / 100));
        }

        $pricesCache[$product->get_id()] = $discountedPrice;

        if ($discountType == 'show-as-sale') {
            $product->set_sale_price($discountedPrice);
        }

        return $discountedPrice;
    }

    /**
     * Get handled product price by discount rule
     *
     * @param float $price
     * @param array $discount
     *
     * @return float
     */
    public static function handlePriceWithDiscountRule($price, $discount)
    {
        switch ($discount['type']) {
            case 'percentage':
                return max(0, $price - ($price * (float) $discount['value'] / 100));

            case 'fixed':
                return max(0, $price - (float) $discount['value']);

            case 'precise_price':
                return max(0, (float) $discount['value']);
        }

        return $price;
    }

    /**
     * Get, filter and return available product attributes and variables
     *
     * @param array $arguments
     *
     * @return array
     */
    public static function getVariationArguments($arguments)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'product' => false,
            'cart' => [],
            'defaultAttributes' => []
        ];

        $arguments = array_replace($defaults, $arguments);

        $product = $arguments['product'];
        $output = [
            'variations' => [],
            'attributes' => []
        ];

        if (!$product instanceof \WC_Product_Variable) {
            return apply_filters('wcProductsWizardVariationArguments', $output, $arguments);
        }

        $defaultSelectedVariations = null;
        $defaultSelectedItems = Settings::getStep($arguments['id'], $arguments['stepId'], 'selected_items_by_default');
        $excludedProductsIds = Settings::getStep($arguments['id'], $arguments['stepId'], 'excluded_products');
        $productId = $product->get_id();
        $variations = $product->get_available_variations();
        $attributes = $product->get_variation_attributes();
        $cartProduct = null;
        $attributesToRemove = [];
        $attributesToSave = [];
        $attributesOutput = [];
        $defaultType = Settings::getStep($arguments['id'], $arguments['stepId'], 'variations_type');
        $type = Settings::getProduct($productId, 'variations_type');
        $type = strtolower($type) != 'default' ? strtolower($type) : $defaultType;
        $minQty = Product::getMinQuantity($arguments['id'], $arguments['stepId']);
        $maxQty = Product::getMaxQuantity($arguments['id'], $arguments['stepId'], $arguments['product']);

        foreach ($variations as $key => &$variation) {
            $availabilityRules = Settings::getProductVariation($variation['variation_id'], 'availability_rules');

            if (in_array($variation['variation_id'], $excludedProductsIds)
                || !Utils::getAvailabilityByRules($arguments['id'], $availabilityRules)
            ) {
                $variation['variation_is_visible'] = 0;
                $variation['variation_is_active'] = 0;

                // save attributes to remove
                foreach ($variation['attributes'] as $attributeItemKey => $attributeItemValue) {
                    $attributesToRemove[$attributeItemKey][] = $attributeItemValue;
                }

                // remove the unmet variation at all
                unset($variations[$key]);

                continue;
            }

            // collect attributes to save
            foreach ($variation['attributes'] as $attributeItemKey => $attributeItemValue) {
                $attributesToSave[$attributeItemKey][] = $attributeItemValue;
            }

            // change quantity values according the settings
            if (is_numeric($minQty)) {
                $variation['min_qty'] = $minQty;
            }

            if (is_numeric($maxQty)) {
                $variation['max_qty'] = $maxQty;
            }

            // define default selected variation by the setting
            if (in_array($variation['variation_id'], $defaultSelectedItems)) {
                $defaultSelectedVariations = $variation;
            }

            // change image size
            if (!empty($variation['image_src'])) {
                $src = wp_get_attachment_image_src(get_post_thumbnail_id($variation['variation_id']), 'shop_catalog');
                $variation['image_src'] = $src[0];
            } elseif (!empty($variation['image']['src'])) {
                $src = wp_get_attachment_image_src(get_post_thumbnail_id($variation['variation_id']), 'shop_catalog');
                $variation['image']['src'] = is_array($src) && isset($src[0]) ? $src[0] : '';
            }
        }

        unset($variation);

        // clean attributes to remove from attributes to save
        foreach ($attributesToSave as $attributeToSaveKey => $attributeToSaveValue) {
            if (!isset($attributesToRemove[$attributeToSaveKey])) {
                continue;
            }

            $attributesToRemove[$attributeToSaveKey] = array_diff(
                $attributesToRemove[$attributeToSaveKey],
                $attributeToSaveValue
            );
        }

        // find and remove unmet product attributes
        foreach ($attributesToRemove as $attributeToRemoveItemKey => $attributeToRemoveItemValue) {
            foreach ($attributes as $attributeKey => $attributeValue) {
                if (urldecode(str_replace('attribute_', '', $attributeToRemoveItemKey))
                    != mb_strtolower($attributeKey)
                ) {
                    continue;
                }

                foreach ($attributeToRemoveItemValue as $attributeToRemoveItemValueItem) {
                    foreach ($attributeValue as $attributeItemValueItemKey => $attributeItemValueItemValue) {
                        if (urldecode($attributeToRemoveItemValueItem) != urldecode($attributeItemValueItemValue)) {
                            continue;
                        }

                        // unset product attribute
                        unset($attributes[$attributeKey][$attributeItemValueItemKey]);
                    }
                }
            }
        }

        // find this product in the cart
        foreach ($arguments['cart'] as $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $productId == $cartItem['product_id'] && $cartItem['step_id'] == $arguments['stepId']
            ) {
                $cartProduct = $cartItem;

                break;
            }
        }

        // get pure attributes array
        foreach ($attributes as $attributeKey => $attributeValue) {
            $selectedAttribute = '';

            // set active product if have in the cart
            if (isset($_REQUEST['attribute_' . sanitize_title($attributeKey)])) {
                $selectedAttribute = $_REQUEST['attribute_' . sanitize_title($attributeKey)];
            } elseif ($cartProduct && isset($cartProduct['variation']['attribute_' . sanitize_title($attributeKey)])) {
                $selectedAttribute = $cartProduct['variation']['attribute_' . sanitize_title($attributeKey)];
            } elseif ($defaultSelectedVariations
                && isset($defaultSelectedVariations['attributes']['attribute_' . sanitize_title($attributeKey)])
            ) {
                $selectedAttribute = $defaultSelectedVariations['attributes'][
                    'attribute_' . sanitize_title($attributeKey)
                ];
            } elseif (isset($arguments['defaultAttributes'][sanitize_title($attributeKey)])) {
                $selectedAttribute = $arguments['defaultAttributes'][sanitize_title($attributeKey)];
            }

            // Get terms if this is a taxonomy - ordered
            if (taxonomy_exists($attributeKey)) {
                switch (wc_attribute_orderby($attributeKey)) {
                    case 'name': {
                        $args = [
                            'orderby' => 'name',
                            'hide_empty' => false,
                            'menu_order' => false
                        ];

                        break;
                    }

                    case 'id': {
                        $args = [
                            'orderby' => 'id',
                            'order' => 'ASC',
                            'menu_order' => false,
                            'hide_empty' => false
                        ];

                        break;
                    }

                    case 'menu_order': {
                        $args = ['menu_order' => 'ASC', 'hide_empty' => false];
                        break;
                    }

                    default: {
                        $args = [];
                    }
                }

                $terms = get_terms($attributeKey, $args);

                foreach ($terms as $term) {
                    if (!in_array($term->slug, $attributeValue)) {
                        continue;
                    }

                    if (!$selectedAttribute && in_array($type, ['radio', 'button', 'image'])
                        && (!isset($attributesOutput[$attributeKey]) || count($attributesOutput[$attributeKey]) == 0)
                    ) {
                        $selected = true;
                    } else {
                        $selected = sanitize_title($selectedAttribute) == sanitize_title($term->slug);
                    }

                    $attributesOutput[$attributeKey][] = [
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'value' => $term->slug,
                        'selected' => $selected,
                        'thumbnailId' => get_term_meta($term->term_id, '_wcpw_thumbnail_id', true)
                    ];
                }
            } else {
                foreach ($attributeValue as $option) {
                    if (!$selectedAttribute && in_array($type, ['radio', 'button', 'image'])
                        && (!isset($attributesOutput[$attributeKey]) || count($attributesOutput[$attributeKey]) == 0)
                    ) {
                        $selected = true;
                    } else {
                        $selected = sanitize_title($selectedAttribute) == sanitize_title($option);
                    }

                    $attributesOutput[$attributeKey][] = [
                        'name' => $option,
                        'value' => $option,
                        'selected' => $selected
                    ];
                }
            }
        }

        $output = [
            'variations' => $variations,
            'attributes' => $attributesOutput
        ];

        return apply_filters('wcProductsWizardVariationArguments', $output, $arguments);
    }

    /**
     * Merge arguments with products query part
     *
     * @param array $args
     * @param array $productsIds - specific products only
     *
     * @return array
     */
    public static function addRequestArgs($args, $productsIds = [])
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'page' => 1,
            'orderBy' => null,
            'productsPerPage' => null,
            'filter' => []
        ];

        $args = array_replace($defaults, $args);
        $productsPerPage = Settings::getStep($args['id'], $args['stepId'], 'products_per_page');
        $allSelectedItemsByDefault = Settings::getStep($args['id'], $args['stepId'], 'all_selected_items_by_default');
        $noSelectedItemsByDefault = Settings::getStep($args['id'], $args['stepId'], 'no_selected_items_by_default');
        $selectedItemsByDefault = Settings::getStep($args['id'], $args['stepId'], 'selected_items_by_default');
        $orderBy = Settings::getStep($args['id'], $args['stepId'], 'order_by');
        $activeProductsIds = Cart::getProductsAndVariationsIds($args['id'], ['includeSteps' => $args['stepId']]);

        // get products by filtered ids
        if (empty($productsIds)) {
            $productsIds = self::getStepProductsIds($args['id'], $args['stepId'], $args);
        }

        // product request by current category
        $queryArgs = [
            'orderby' => $orderBy,
            'order' => Settings::getStep($args['id'], $args['stepId'], 'order'),
            'post_type' => 'product',
            'post__in' => $productsIds,
            'posts_per_page' => -1,
            'numberposts' => -1,
            'paged' => $args['page']
        ];

        // args for price ordering
        if ($args['orderBy']) {
            $orderByValue = explode('-', $args['orderBy']);
            $orderBy = esc_attr($orderByValue[0]);
            $order = !empty($orderByValue[1]) ? $orderByValue[1] : 'ASC';
            $queryArgs = array_replace(
                $queryArgs,
                WC()->query->get_catalog_ordering_args($orderBy, $order)
            );
        }

        if ($queryArgs['orderby'] == 'price') {
            $queryArgs['orderby'] = 'meta_value_num';
            $queryArgs['meta_key'] = '_price';
        }

        // change products per page value
        if (is_numeric($args['productsPerPage']) && $args['productsPerPage']) {
            $queryArgs['posts_per_page'] = $args['productsPerPage'];
            $queryArgs['numberposts'] = $args['productsPerPage'];
        } elseif ($productsPerPage != 0) {
            $queryArgs['posts_per_page'] = $productsPerPage;
            $queryArgs['numberposts'] = $productsPerPage;
        }

        if (empty($activeProductsIds) && !$noSelectedItemsByDefault) {
            if ($allSelectedItemsByDefault) {
                $activeProductsIds = $productsIds;
            } elseif (!empty($selectedItemsByDefault)) {
                $activeProductsIds = $selectedItemsByDefault;
            } else {
                // set the first product as active
                $productsQuery = get_posts(array_replace($queryArgs, ['numberposts' => 1]));
                $activeProductsIds[] = $productsQuery[0]->ID;
            }
        }

        $output = array_replace(
            $args,
            [
                'queryArgs' => $queryArgs,
                'itemTemplate' => 'form/item/' . Template::getFormItemName($args['id'], $args['stepId']),
                'cart' => Cart::get($args['id']),
                'activeProductsIds' => $activeProductsIds,
                'hidePrices' => Settings::getPost($args['id'], 'hide_prices'),
                'severalProducts' => Settings::getStep($args['id'], $args['stepId'], 'several_products'),
                'hideChooseElement' => Settings::getStep($args['id'], $args['stepId'], 'hide_choose_element'),
                'soldIndividually' => Settings::getStep($args['id'], $args['stepId'], 'sold_individually'),
                'mergeThumbnailWithGallery' =>
                    Settings::getStep($args['id'], $args['stepId'], 'merge_thumbnail_with_gallery'),
                'enableTitleLink' => Settings::getStep($args['id'], $args['stepId'], 'enable_title_link')
            ]
        );

        return apply_filters('wcProductsWizardProductsRequestArgs', $output, $productsIds, $args);
    }

    /**
     * Makes the products query considering all conditions
     *
     * @param array $args
     *
     * @return string
     */
    public static function request($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null
        ];

        $args = array_replace($defaults, $args);

        if (!$args['id'] || !$args['stepId']) {
            return '';
        }

        // there are no products
        if (empty(Settings::getStep($args['id'], $args['stepId'], 'categories'))
            && empty(array_filter((array) Settings::getStep($args['id'], $args['stepId'], 'included_products')))
        ) {
            return '';
        }

        $productsIds = self::getStepProductsIds($args['id'], $args['stepId'], $args);

        if (!empty($productsIds)) {
            $args = self::addRequestArgs($args, $productsIds);
            $template = Template::getFormName($args['id'], $args['stepId']);

            return Template::html("form/layouts/{$template}", $args);
        }

        return Template::html('messages/nothing-found', $args);
    }

    /**
     * Add a product to the main woocommerce cart
     *
     * @param array $args
     *
     * @throws \Exception
     *
     * @return string|bool
     */
    public static function addToMainCart($args)
    {
        $defaults = [
            'product_id' => null,
            'quantity' => 1,
            'variation_id' => null,
            'variation' => [],
            'data' => ['wcpw_id' => null],
            'request' => null
        ];

        $args = array_replace_recursive($defaults, $args);
        $cartQuantity = 0;

        do_action('wcProductsWizardBeforeAddToMainCart', $args);

        // get the same product's quantity from the main cart and remove it
        if (apply_filters('wcProductsWizardMergeCartQuantity', false, $args)) {
            $cart = \WC()->cart->get_cart();

            foreach ($cart as $cartItemKey => $cartItem) {
                if ($cartItem['product_id'] != $args['product_id']
                    || $cartItem['variation_id'] != $args['variation_id']
                    || $cartItem['variation'] != $args['variation']
                ) {
                    continue;
                }

                $cartQuantity += (float) $cartItem['quantity'];

                \WC()->cart->remove_cart_item($cartItemKey);
            }
        }

        if ($args['request']) {
            // emulate post data passing
            $_POST = $args['request'];
        }

        return \WC()->cart->add_to_cart(
            $args['product_id'],
            $args['quantity'] + $cartQuantity,
            $args['variation_id'],
            $args['variation'],
            $args['data']
        );
    }

    /**
     * Prepare step products request query considering all conditions
     *
     * @param integer $postId
     * @param integer $stepId
     * @param array $args
     *
     * @return array
     */
    public static function getStepProductsIds($postId, $stepId, $args = [])
    {
        $defaults = ['filter' => []];
        $args = array_replace($defaults, $args);

        if (!$stepId || !is_numeric($stepId)) {
            return apply_filters('wcProductsWizardStepProductsIds', [], $postId, $stepId, $args);
        }

        $productsIds = [];
        $categories = Settings::getStep($postId, $stepId, 'categories');
        $includedProductsIds = array_filter((array) Settings::getStep($postId, $stepId, 'included_products'));
        $excludedProductsIds = Settings::getStep($postId, $stepId, 'excluded_products');
        $excludeAddedProductsOfSteps = Settings::getStep($postId, $stepId, 'exclude_added_products_of_steps');

        // product request by current category
        $queryArgs = [
            'post_type' => 'product',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'numberposts' => -1,
            'tax_query' => ['relation' => 'AND'],
            'meta_query' => ['relation' => 'AND'],
            'post__not_in' => [],
            'post__in' => [],
        ];

        // exclude other steps added products
        if (!empty($excludeAddedProductsOfSteps)) {
            $excludeAddedProducts = Cart::getProductsAndVariationsIds(
                $postId,
                ['includeSteps' => wp_parse_id_list($excludeAddedProductsOfSteps)]
            );

            if (!empty($excludeAddedProducts)) {
                $queryArgs['post__not_in'] += $excludeAddedProducts;
            }
        }

        // exclude products
        if (is_array($excludedProductsIds)) {
            $excludedProductsIds = array_filter($excludedProductsIds);

            if (!empty($excludedProductsIds)) {
                $queryArgs['post__not_in'] += $excludedProductsIds;
            }
        }

        // query products by ids and order
        if (!empty($includedProductsIds)) {
            $queryArgs['post__in'] += $includedProductsIds;
        } elseif (!empty($categories)) {
            foreach ($categories as $key => $categoryId) {
                $availabilityRules = Settings::getProductCategory($categoryId, 'availability_rules');

                if (!Utils::getAvailabilityByRules($postId, $availabilityRules)) {
                    unset($categories[$key]);

                    continue;
                }
            }

            $queryArgs['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $categories
            ];
        }

        // blend in filter args query
        $queryArgs = array_merge_recursive($queryArgs, self::filterArgsToQuery($args['filter']));

        // outer filters
        $queryArgs = apply_filters('wcProductsWizardStepProductsQueryArgs', $queryArgs, $postId, $stepId, $args);

        // make a query
        $products = get_posts($queryArgs);

        foreach ($products as $productId) {
            $availabilityRules = Settings::getProduct((int) $productId, 'availability_rules');
            $product = new \WC_Product((int) $productId);
            $isAvailable = Utils::getAvailabilityByRules($postId, $availabilityRules)
                && $product->is_visible() && $product->is_purchasable()
                && ($product->is_in_stock() || $product->backorders_allowed());

            $isAvailable = apply_filters('wcProductsWizardProductAvailability', $isAvailable, $productId, $postId, $stepId, $args);

            if (!$isAvailable) {
                continue;
            }

            $productsIds[] = (int) $productId;
        }

        return apply_filters('wcProductsWizardStepProductsIds', $productsIds, $postId, $stepId, $args);
    }

    /**
     * Prepare query array from filter value
     *
     * @param array $filter
     *
     * @return array
     */
    public static function filterArgsToQuery($filter)
    {
        $output = [];
        $taxQuery = [];
        $metaQuery = [];

        if (empty($filter) || !is_array($filter)) {
            return $output;
        }

        foreach ($filter as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'price': {
                    $metaQuery[] = [
                        'key' => '_price',
                        'value' => $value['from'],
                        'compare' => '>=',
                        'type' => 'numeric'
                    ];

                    $metaQuery[] = [
                        'key' => '_price',
                        'value' => $value['to'],
                        'compare' => '<=',
                        'type' => 'numeric'
                    ];

                    break;
                }

                case 'category': {
                    $taxQuery[] = [
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $value,
                        'operator' => 'IN'
                    ];

                    break;
                }

                case 'tag': {
                    $taxQuery[] = [
                        'taxonomy' => 'product_tag',
                        'field' => 'id',
                        'terms' => $value,
                        'operator' => 'IN'
                    ];

                    break;
                }

                case 'search': {
                    $output['s'] = $value;
                    break;
                }

                // attribute
                default: {
                    if (!taxonomy_exists("pa_{$key}")) {
                        break;
                    }

                    if (!isset($value['from'])) {
                        // attribute simple
                        $taxQuery[] = [
                            'taxonomy' => "pa_{$key}",
                            'field' => 'id',
                            'terms' => $value,
                            'operator' => 'IN'
                        ];

                        if (filter_var(get_option('woocommerce_hide_out_of_stock_items'), FILTER_VALIDATE_BOOLEAN)) {
                            // skip products with out-of-stock variations by requested attributes
                            global $wpdb;

                            $visibilityTerms = wc_get_product_visibility_term_ids();

                            // using of an SQL query avoids cross wp_queries problems
                            $queryString =
                                "SELECT $wpdb->posts.post_parent, $wpdb->term_relationships.term_taxonomy_id "
                                . "FROM $wpdb->posts "
                                . "LEFT JOIN $wpdb->term_relationships "
                                . "ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) "
                                . "INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) "
                                . "WHERE $wpdb->posts.post_type = 'product_variation' "
                                . "AND $wpdb->posts.post_status IN ('publish', 'private') "
                                . "AND ($wpdb->postmeta.meta_key = 'attribute_pa_{$key}' "
                                . "AND $wpdb->postmeta.meta_value IN ("
                                . implode(',', array_map(
                                    function ($value) {
                                        return "'" . esc_sql($value) . "'";
                                    },
                                    get_terms([
                                        'taxonomy' => "pa_{$key}",
                                        'include' => $value,
                                        'fields' => 'id=>slug'
                                    ])
                                ))
                                . "))";

                            $results = $wpdb->get_results($queryString);
                            $productsIds = [];

                            foreach ($results as $result) {
                                $productsIds[$result->post_parent][] = (int) $result->term_taxonomy_id;
                            }

                            foreach ($productsIds as $id => $variations) {
                                $counts = array_count_values($variations);

                                if (isset($counts[$visibilityTerms['outofstock']])
                                    && $counts[$visibilityTerms['outofstock']] == count($variations)
                                ) {
                                    $output['post__not_in'][] = $id;
                                }
                            }
                        }

                        break;
                    }

                    // attribute range
                    $filterBy = [];
                    $terms = get_terms(['taxonomy' => "pa_{$key}"]);

                    foreach ($terms as $term) {
                        $termValue = (float) $term->name;

                        $issetFirstValue = isset($value['from']) && !empty($value['from']);
                        $firstCondition = $value['from'] <= $termValue;
                        $issetSecondValue = isset($value['to']) && !empty($value['to']);
                        $secondCondition = $value['to'] >= $termValue;

                        if (($issetFirstValue && $firstCondition && $issetSecondValue && $secondCondition)
                            || (!$issetSecondValue && $issetFirstValue && $firstCondition)
                            || (!$issetFirstValue && $issetSecondValue && $secondCondition)
                        ) {
                            $filterBy[] = $term->term_id;
                        }
                    }

                    if (!empty($filterBy)) {
                        $taxQuery[] = [
                            'taxonomy' => "pa_{$key}",
                            'field' => 'id',
                            'terms' => $filterBy,
                            'operator' => 'IN'
                        ];
                    }
                }
            }
        }

        if (!empty($metaQuery)) {
            $output['meta_query'] = $metaQuery;
        }

        if (!empty($taxQuery)) {
            $output['tax_query'] = $taxQuery;
        }

        return apply_filters('wcProductsWizardFilterArgsToQuery', $output, $filter);
    }

    /**
     * Get minimum quantity value
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return integer
     */
    public static function getMinQuantity($postId, $stepId)
    {
        return self::getQuantityValue($postId, $stepId);
    }

    /**
     * Get max quantity value
     *
     * @param integer $postId
     * @param integer $stepId
     * @param object $product
     *
     * @return integer
     */
    public static function getMaxQuantity($postId, $stepId, $product = null)
    {
        $output = self::getQuantityValue($postId, $stepId, 'max');

        // check product stock quantity
        if ($product) {
            $manageStock = false;

            if (method_exists($product, 'get_manage_stock')) {
                $manageStock = $product->get_manage_stock();
            } elseif (isset($product->manage_stock)) {
                $manageStock = $product->manage_stock;
            }

            if (filter_var($manageStock, FILTER_VALIDATE_BOOLEAN)) {
                $stock = (int) $product->get_stock_quantity();

                if ($output) {
                    return min($output, $stock);
                } else {
                    return $stock;
                }
            }
        }

        return $output;
    }

    /**
     * Get min or max quantity value
     *
     * @param integer $postId
     * @param integer $stepId
     * @param string $type
     *
     * @return integer
     */
    public static function getQuantityValue($postId, $stepId, $type = 'min')
    {
        $rule = Settings::getStep($postId, $stepId, "{$type}_product_quantity");

        if (!$rule) {
            return 0;
        }

        if (!is_array($rule)) {
            // @since 3.19.0 - older versions support
            $rule = [
                'type' => 'count',
                'value' => $rule
            ];
        }

        return Form::checkStepQuantitiesRule($postId, $rule);
    }

    /**
     * Get product's categories ids array
     *
     * @param integer $productId
     *
     * @return array
     */
    public static function getCategoriesIds($productId)
    {
        static $categoriesIds = [];

        if (!isset($categoriesIds[$productId])) {
            $categoriesIds[$productId] = wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids']);
        }

        return $categoriesIds[$productId];
    }

    /**
     * Get product's attribute values ids array
     *
     * @param integer $productId
     * @param string $attribute
     *
     * @return array
     */
    public static function getAttributeValuesIds($productId, $attribute)
    {
        static $valuesIds = [];

        if (!isset($valuesIds[$productId])) {
            $valuesIds[$productId] = wp_get_post_terms($productId, $attribute, ['fields' => 'ids']);
        }

        return $valuesIds[$productId];
    }

    // <editor-fold desc="Deprecated">
    /**
     * Get variation view type
     *
     * @param integer $postId
     *
     * @return string
     *
     * @deprecated 4.0.0 Use Settings::getProduct($postId, 'variations_type')
     */
    public static function getVariationType($postId)
    {
        return Settings::getProduct($postId, 'variations_type');
    }
    // </editor-fold>
}

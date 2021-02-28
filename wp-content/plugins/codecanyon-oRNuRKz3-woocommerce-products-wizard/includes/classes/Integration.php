<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Integration Class
 *
 * @class Integration
 * @version 2.3.1
 */
class Integration
{
    // <editor-fold desc="Core">
    /**
     * Ajax actions variable
     * @var array
     */
    public static $ajaxActions = [
        'wcpwSubmit',
        'wcpwAddToMainCart',
        'wcpwGetStep',
        'wcpwSkipStep',
        'wcpwSkipAll',
        'wcpwReset',
        'wcpwAddCartProduct',
        'wcpwRemoveCartProduct',
        'wcpwUpdateCartProduct'
    ];

    /**
     * Step ID used as out of any wizard steps
     * @var integer
     */
    public static $stepIdOutOfSteps = 1000;

    /**
     * Single product add to cart form ID
     * @var string
     */
    public static $productSingleAddToCartFormId = 'woocommerce-single-product-add-to-cart-form';

    /**
     * WC template parts to handle
     * @var array
     */
    public static $templatePartsToFilter = [
        'single-product/add-to-cart/simple.php',
        'single-product/add-to-cart/variable.php'
    ];

    /** Class Constructor */
    public function __construct()
    {
        // extra product options
        add_filter('woocommerce_tm_quick_view', [$this, 'epoQuickViewFilter']);
        add_action('woocommerce_init', [$this, 'reInitEPO']);
        add_filter('wcProductsWizardAddAllToMainCartItems', [$this, 'epoAddAllToMainCart']);
        add_filter('wcProductsWizardCartItemPrice', [$this, 'epoCartItemPriceFilter'], 10, 2);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'epoBeforeAddToCartButton']);
        add_action('tm_epo_register_addons_scripts', [$this, 'epoRegisterAddonsScripts']);

        // cf7
        add_action('wpcf7_before_send_mail', [$this, 'beforeCF7SendMailFilter']);
        add_filter('wpcf7_form_response_output', [$this, 'CF7FormResponseOutputFilter'], 10, 4);

        // cart filters
        add_filter('wcProductsWizardCartSubTotal', [$this, 'cartSubTotalFilter'], 10, 2);

        // outer woocommerce products
        add_action('woocommerce_add_to_cart', [$this, 'redirectOnProductAdd'], 100, 6);
        add_action('woocommerce_add_to_cart', [$this, 'attachToCartProduct'], 20, 6);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'addToCartValidation']);
        add_action('woocommerce_before_template_part', [$this, 'beforeTemplatePartAction']);
        add_action('woocommerce_after_template_part', [$this, 'afterTemplatePartAction']);
        add_action('wp_head', [$this, 'attachToPage']);
    }
    // </editor-fold>

    // <editor-fold desc="Outer products">
    /**
     * Before WC template output action
     *
     * @param string $name
     */
    public function beforeTemplatePartAction($name)
    {
        global $product;

        // attach wizard products to the product added to cart
        if (!$product || !is_object($product)) {
            return;
        }

        $productId = $product->get_id();
        $wizardId = Settings::getProduct($productId, 'attach_wizard');

        if (!$wizardId) {
            foreach (wp_get_post_terms($productId, 'product_cat') as $term) {
                $wizardId = Settings::getProductCategory($term->term_id, 'attach_wizard');

                if ($wizardId) {
                    break;
                }
            }
        }

        if (!$wizardId) {
            return;
        }

        if (in_array($name, self::$templatePartsToFilter)) {
            ob_start();
        }
    }

    /**
     * After WC template output action
     *
     * @param string $name
     */
    public function afterTemplatePartAction($name)
    {
        global $product;

        // attach wizard products to the product added to cart
        if (!$product || !is_object($product)) {
            return;
        }

        $productId = $product->get_id();
        $wizardId = Settings::getProduct($productId, 'attach_wizard');

        if (!$wizardId) {
            foreach (wp_get_post_terms($productId, 'product_cat') as $term) {
                $wizardId = Settings::getProductCategory($term->term_id, 'attach_wizard');

                if ($wizardId) {
                    break;
                }
            }
        }

        if (!$wizardId) {
            return;
        }

        if (in_array($name, self::$templatePartsToFilter)) {
            $html = ob_get_clean();
            $html = preg_replace(
                '/\<form class=/',
                '<form data-component="wcpw-form" id="' . esc_attr(self::$productSingleAddToCartFormId) . '" class=',
                $html,
                1
            );

            echo $html;
        }
    }
    
    /** Output wizard on a single product page */
    public function attachToPage()
    {
        $productId = get_the_ID();

        if (!$productId || get_post_type($productId) != 'product') {
            return;
        }

        // find wizards attached to products
        $wizardId = Settings::getProduct($productId, 'attach_wizard');
        $place = Settings::getProduct($productId, 'attached_wizard_place');
        $tabTitle = Settings::getProduct($productId, 'tab_title');

        if (!$wizardId) {
            foreach (wp_get_post_terms($productId, 'product_cat') as $term) {
                $wizardId = Settings::getProductCategory($term->term_id, 'attach_wizard');

                if ($wizardId) {
                    $place = Settings::getProductCategory($term->term_id, 'attached_wizard_place');
                    $tabTitle = Settings::getProductCategory($term->term_id, 'tab_title');

                    break;
                }
            }
        }

        if (!$wizardId) {
            return;
        }

        $html = Template::html(
            'app',
            [
                'id' => $wizardId,
                'formId' => self::$productSingleAddToCartFormId,
                'attachedMode' => true
            ],
            ['echo' => false]
        );

        switch ($place) {
            case 'before_form':
                add_action('woocommerce_before_add_to_cart_form', function () use ($html) {
                    echo $html;
                });

                break;
            case 'after_form':
                add_action('woocommerce_after_add_to_cart_form', function () use ($html) {
                    echo $html;
                });

                break;
            case 'tab':
                add_filter('woocommerce_product_tabs', function ($tabs) use ($html, $tabTitle) {
                    $tabs['woocommerce_products_wizard'] = [
                        'title' => $tabTitle,
                        'priority' => 5,
                        'callback' => function () use ($html) {
                            echo $html;
                        }
                    ];

                    return $tabs;
                });
        }

        remove_action('wp_head', [$this, 'attachToPage']);
    }

    /**
     * Attach wizard products to the product added to cart
     *
     * @param string $cartItemKey
     * @param int $productId
     * @param int $quantity
     * @param int $variationId
     * @param array $variation
     * @param array $cartItemData
     *
     * @throws \Exception
     */
    public function attachToCartProduct($cartItemKey, $productId, $quantity, $variationId, $variation, $cartItemData)
    {
        if (did_action('wcProductsWizardBeforeAddToMainCart') || !isset($_REQUEST['attach-to-product'])
            || !isset($_REQUEST['woocommerce-products-wizard']) || !isset($_REQUEST['id'])
        ) {
            return;
        }

        $key = null;

        try {
            $productData = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'variation' => $variation,
                'quantity' => 1,
                'step_id' => self::$stepIdOutOfSteps,
                'data' => $cartItemData,
                'request' => $_REQUEST,
                'sold_individually' => true
            ];

            $key = Cart::addProduct($_REQUEST['id'], $productData);
            $productsAdded = Instance()->form->addToMainCart($_REQUEST);

            unset(WC()->cart->cart_contents[$cartItemKey]);

            if ($quantity > 1) {
                // multiple the products quantity
                foreach ($productsAdded as $key => $product) {
                    WC()->cart->set_quantity($key, $quantity * $product['quantity']);
                }
            }
        } catch (\Exception $exception) {
            unset(WC()->cart->cart_contents[$cartItemKey]);

            if ($key) {
                Cart::removeByProductKey($_REQUEST['id'], $key);
            }

            Instance()->form->addNotice(
                $exception->getCode() ? $exception->getCode() : Form::getActiveStepId($_REQUEST['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Validate product before add to cart
     *
     * @param bool $value
     *
     * @return bool
     */
    public function addToCartValidation($value)
    {
        // don't add product to cart while it's a simple wizard action
        if (did_action('wcProductsWizardBeforeAddToMainCart') || !isset($_REQUEST['attach-to-product'])
            || !isset($_REQUEST['woocommerce-products-wizard']) || !isset($_REQUEST['id'])
        ) {
            return $value;
        }

        $actionsToSkip = [
            'add-cart-product',
            'remove-cart-product',
            'update-cart-product',
            'submit',
            'reset',
            'skip-step',
            'skip-all',
            'get-step'
        ];

        foreach ($actionsToSkip as $action) {
            if (isset($_REQUEST[$action])) {
                return false;
            }
        }

        return $value;
    }

    /**
     * Redirect to a wizard on product add to WC cart
     *
     * @param string $cartItemKey
     * @param int $productId
     * @param int $quantity
     * @param int $variationId
     * @param array $variation
     * @param array $cartItemData
     */
    public function redirectOnProductAdd($cartItemKey, $productId, $quantity, $variationId, $variation, $cartItemData)
    {
        if (did_action('wcProductsWizardBeforeAddToMainCart')) {
            return;
        }

        $wizardId = Settings::getProduct($productId, 'redirect_on_add_to_cart');
        $stepId = Settings::getProduct($productId, 'redirect_step_id');
        $link = Settings::getProduct($productId, 'redirect_link');

        if (!$wizardId || !$link) {
            foreach (wp_get_post_terms($productId, 'product_cat') as $term) {
                $wizardId = Settings::getProductCategory($term->term_id, 'redirect_on_add_to_cart');

                if ($wizardId) {
                    $stepId = Settings::getProductCategory($term->term_id, 'redirect_step_id');
                    $stepId = $stepId ? $stepId : $term->term_id;
                    $link = Settings::getProductCategory($term->term_id, 'redirect_link');

                    break;
                }
            }
        }

        if ($wizardId && $link) {
            // if url is absolute
            if (strpos($link, home_url()) === false) {
                $link = home_url() . '/' . $link;
            }

            // product data
            $productData = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'variation' => $variation,
                'quantity' => $quantity,
                'step_id' => is_numeric($stepId) ? $stepId : self::$stepIdOutOfSteps,
                'data' => $cartItemData,
                'request' => $_REQUEST
            ];

            // phpcs:disable
            $productData = apply_filters('wcProductsWizardRedirectToWizardProductData', $productData, $wizardId, $cartItemKey);
            // phpcs:enable

            try {
                // remove WC cart item
                \WC()->cart->remove_cart_item($cartItemKey);

                // clear the wizard's cart and add the new item
                Cart::truncate($wizardId);
                Cart::addProduct($wizardId, $productData);

                // phpcs:disable
                $link = apply_filters('wcProductsWizardRedirectToWizardLink', $link, $wizardId, $cartItemKey, $productData);
                // phpcs:enable

                if (wp_doing_ajax()) {
                    // use ajax-response trick with the link
                    wp_send_json([
                        'error' => true,
                        'product_url' => $link
                    ]);
                }

                wp_redirect($link);

                // js version of redirect
                exit("<script>document.location = '$link';</script>");
            } catch (\Exception $exception) {
                exit($exception->getMessage());
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="EPO plugin">
    /**
     * Filter EPO function value if is ajax action
     *
     * @param bool $qv
     *
     * @return bool
     */
    public function epoQuickViewFilter($qv)
    {
        if (isset($_POST['action']) && in_array($_POST['action'], self::$ajaxActions)) {
            return true;
        }

        return $qv;
    }

    /** ReInit EPO plugin functions */
    public function reInitEPO()
    {
        if (isset($_POST['action'])
            && in_array($_POST['action'], self::$ajaxActions)
            && function_exists('\\TM_EPO')
        ) {
            global $wp_query;

            // required in further code of EPO
            $wp_query->is_page = true;

            if (method_exists(TM_EPO(), 'init_vars')) {
                TM_EPO()->init_vars();
            }

            if (method_exists(TM_EPO(), 'tm_epo_fields')) {
                TM_EPO()->tm_epo_fields(get_the_ID(), get_the_ID());
            }

            if (method_exists(TM_EPO(), 'tm_add_inline_style')) {
                TM_EPO()->tm_add_inline_style();
            }

            if (method_exists(TM_EPO(), 'tm_epo_totals')) {
                TM_EPO()->tm_epo_totals(get_the_ID(), get_the_ID());
            }

            // for different versions
            if (function_exists('TM_EPO_DISPLAY')) {
                if (method_exists(TM_EPO_DISPLAY(), 'tm_epo_fields')) {
                    TM_EPO_DISPLAY()->tm_epo_fields(get_the_ID(), get_the_ID());
                }

                if (method_exists(TM_EPO_DISPLAY(), 'tm_epo_totals')) {
                    TM_EPO_DISPLAY()->tm_epo_totals(get_the_ID(), get_the_ID());
                }
            }
        }
    }

    /** Enqueue EPO scripts action */
    public function epoRegisterAddonsScripts()
    {
        $productId = get_the_ID();

        if (!$productId || get_post_type($productId) != 'product') {
            return;
        }

        // find wizards attached to products
        $wizardId = Settings::getProduct($productId, 'attach_wizard');

        if (!$wizardId) {
            foreach (wp_get_post_terms($productId, 'product_cat') as $term) {
                $wizardId = Settings::getProductCategory($term->term_id, 'attach_wizard');

                if ($wizardId) {
                    break;
                }
            }
        }

        if ($wizardId && function_exists('THEMECOMPLETE_EPO')) {
            THEMECOMPLETE_EPO()->current_option_features[] = 'product';
        }
    }

    /**
     * Add to main cart filter
     *
     * @param array $items
     *
     * @return array
     */
    public function epoAddAllToMainCart($items)
    {
        foreach ($items as &$item) {
            if (!isset($item['tmcartepo']) || empty($item['tmcartepo'])) {
                continue;
            }

            foreach ($item['tmdata']['tmcartepo_data'] as $index => $value) {
                if (!empty($item['request'][$value['attribute']]) || !isset($item['tmcartepo'][$index]['value'])) {
                    continue;
                }

                $item['request'][$value['attribute']] = $item['tmcartepo'][$index]['value'];
            }
        }

        return $items;
    }

    /** Before "add to cart" button output on a product's page */
    public function epoBeforeAddToCartButton()
    {
        if (function_exists('\\TM_EPO') && TM_EPO()->is_edit_mode()) {
            $cart = WC()->cart->get_cart();
            $cartItemKey = TM_EPO()->cart_edit_key;

            if (isset($cart[$cartItemKey], $cart[$cartItemKey]['wcpw_kit_id'])) {
                echo '<input type="hidden" name="wcpw_kit_id" value="'
                    . esc_attr($cart[$cartItemKey]['wcpw_kit_id']) . '" />';
                echo '<input type="hidden" name="wcpw_kit_title" value="'
                    . esc_attr($cart[$cartItemKey]['wcpw_kit_title']) . '" />';
                echo '<input type="hidden" name="wcpw_is_kit_base" value="'
                    . esc_attr($cart[$cartItemKey]['wcpw_is_kit_base']) . '" />';
                echo '<input type="hidden" name="wcpw_is_kit_quantity_fixed" value="'
                    . esc_attr($cart[$cartItemKey]['wcpw_is_kit_quantity_fixed']) . '" />';
            }
        }
    }

    /**
     * Get cart item price filter
     *
     * @param float $price
     * @param array $data
     *
     * @return float
     */
    public function epoCartItemPriceFilter($price, $data)
    {
        if (isset($data['tm_epo_product_price_with_options'])) {
            $price = $data['tm_epo_product_price_with_options'];
        }

        return $price;
    }
    // </editor-fold>

    // <editor-fold desc="CF7">
    /**
     * CF7 response HTML filter
     *
     * @param string $output
     * @param string $class
     * @param string $content
     * @param \WPCF7_ContactForm $instance
     *
     * @return string
     */
    public function CF7FormResponseOutputFilter($output, $class, $content, $instance)
    {
        $htmlName = $instance->shortcode_attr('html_name');

        if (strpos($htmlName, 'wcpw-result-') !== false) {
            $id = str_replace('wcpw-result-', '', $htmlName);
            $output .= '<input type="hidden" name="wcpw-result" value="' . $id .'">';
        }

        return $output;
    }

    /** Handle CF7 form before send */
    public function beforeCF7SendMailFilter()
    {
        if (!class_exists('\WPCF7_Submission')) {
            return;
        }

        $submission = \WPCF7_Submission::get_instance();

        if (!$submission) {
            return;
        }

        $postedData = $submission->get_posted_data();

        if (empty($postedData) || !isset($postedData['wcpw-result']) || empty($postedData['wcpw-result'])) {
            return;
        }

        $pdf = Instance()->saveAndGetResultPDF((int) $postedData['wcpw-result']);
        $submission->add_uploaded_file('wcpw-result-pdf', $pdf['path']);
    }
    // </editor-fold>

    // <editor-fold desc="Cart">
    /**
     * Calculate cart sub-total filter
     *
     * @param float $total
     * @param integer $postId
     *
     * @return float
     */
    public function cartSubTotalFilter($total, $postId)
    {
        foreach (Cart::get($postId) as $cartItem) {
            $product = $cartItem['data'];

            if (!$product || !$product->exists() || $cartItem['quantity'] <= 0) {
                continue;
            }

            // add Subscription products extra price
            if (in_array($product->get_type(), ['subscription', 'subscription_variation'])) {
                $total += (float) $product->get_sign_up_fee();
            }
        }

        return $total;
    }
    // </editor-fold>
}

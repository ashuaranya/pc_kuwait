<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Form Class
 *
 * @class Form
 * @version 6.5.2
 */
class Form
{
    //<editor-fold desc="Properties">
    /**
     * Active steps session keys variable
     * @var string
     */
    public static $activeStepsSessionKey = 'woocommerce-products-wizard-active-step';

    /**
     * Previous steps session keys variable
     * @var string
     */
    public static $previousStepsSessionKey = 'woocommerce-products-wizard-previous-step';

    /**
     * Ajax actions variable
     * @var array
     */
    public $ajaxActions = [];

    /**
     * Notices array
     * @var array
     */
    public $notices = [];
    //</editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $ajaxActions = [
            'wcpwSubmit' => 'submitAjax',
            'wcpwAddToMainCart' => 'addToMainCartAjax',
            'wcpwGetStep' => 'getAjax',
            'wcpwSkipStep' => 'skipAjax',
            'wcpwSkipAll' => 'skipAllAjax',
            'wcpwReset' => 'resetAjax',
            'wcpwAddCartProduct' => 'addCartProductAjax',
            'wcpwRemoveCartProduct' => 'removeCartProductAjax',
            'wcpwUpdateCartProduct' => 'updateCartProductAjax'
        ];

        $this->ajaxActions = apply_filters('wcProductsWizardFormAjaxActions', $ajaxActions);

        // actions
        add_action('wp_loaded', [$this, 'requests']);

        // ajax actions
        foreach ($this->ajaxActions as $ajaxActionKey => $ajaxActionFunction) {
            add_action("wp_ajax_nopriv_{$ajaxActionKey}", [$this, $ajaxActionFunction]);
            add_action("wp_ajax_{$ajaxActionKey}", [$this, $ajaxActionFunction]);
        }
    }

    /** Add request actions */
    public function requests()
    {
        if (is_admin()) {
            return;
        }

        $request = $_REQUEST;

        // handle no-js forms actions
        if (isset($request['woocommerce-products-wizard'], $request['id'])) {
            // adding a product to the cart handler
            if (isset($request['add-cart-product'])) {
                try {
                    $request['productToAddKey'] = $request['add-cart-product'];
                    $this->addCartProduct($request);
                } catch (\Exception $exception) {
                    $this->addNotice(
                        $exception->getCode() ? $exception->getCode() : self::getActiveStepId($request['id']),
                        [
                            'view' => 'custom',
                            'message' => $exception->getMessage()
                        ]
                    );

                    return;
                }
            }

            // removing a product from the cart handling
            if (isset($request['remove-cart-product'])) {
                try {
                    $request['productCartKey'] = $request['remove-cart-product'];
                    $this->removeCartProduct($request);
                } catch (\Exception $exception) {
                    $this->addNotice(
                        $exception->getCode() ? $exception->getCode() : self::getActiveStepId($request['id']),
                        [
                            'view' => 'custom',
                            'message' => $exception->getMessage()
                        ]
                    );

                    return;
                }
            }

            // updating a product in the cart handling
            if (isset($request['update-cart-product'])) {
                try {
                    $request['productCartKey'] = $request['update-cart-product'];
                    $this->updateCartProduct($request);
                } catch (\Exception $exception) {
                    $this->addNotice(
                        $exception->getCode() ? $exception->getCode() : self::getActiveStepId($request['id']),
                        [
                            'view' => 'custom',
                            'message' => $exception->getMessage()
                        ]
                    );

                    return;
                }
            }

            // add all to main cart but not for attached to a product wizard
            if (isset($request['add-to-main-cart']) && !isset($request['attach-to-product'])) {
                try {
                    $this->addToMainCart($request);
                } catch (\Exception $exception) {
                    $this->addNotice(
                        $exception->getCode() ? $exception->getCode() : self::getActiveStepId($request['id']),
                        [
                            'view' => 'custom',
                            'message' => $exception->getMessage()
                        ]
                    );
                }
            }

            // submit form handler
            if (isset($request['submit'])) {
                try {
                    if (is_numeric($request['submit'])) {
                        // get specific step
                        $request['stepId'] = $request['submit'];
                    }

                    $this->submit($request);
                } catch (\Exception $exception) {
                    $this->addNotice(
                        $exception->getCode() ? $exception->getCode() : self::getActiveStepId($request['id']),
                        [
                            'view' => 'custom',
                            'message' => $exception->getMessage()
                        ]
                    );

                    return;
                }
            }

            // simple actions
            if (isset($request['reset'])) {
                self::reset($request);
            }

            if (isset($request['skip-step'])) {
                self::skip($request);
            }

            if (isset($request['skip-all'])) {
                self::skipAll($request);
            }

            if (isset($request['get-step'])) {
                $request['stepId'] = $request['get-step'];

                self::get($request);
            }
        }
    }

    /**
     * Return an AJAX reply and exit
     *
     * @param array $data
     * @param array $postData
     */
    public static function ajaxReply($data, $postData = [])
    {
        if (Settings::getGlobal('send_state_hash_ajax') && !empty($postData)) {
            if (isset($postData['id']) && $postData['id']) {
                $data['stateHash'] = md5(serialize([
                    'cart' => Cart::get($postData['id']),
                    'step' => self::getActiveStepId($postData['id'])
                ]));
            }
        }
        
        Utils::sendJSON($data);
    }
    // </editor-fold>

    // <editor-fold desc="Notices">
    /**
     * Add notice by type into a queue
     *
     * @param string $stepId
     * @param array $massageData
     */
    public function addNotice($stepId, $massageData)
    {
        $this->notices[$stepId][] = $massageData;
    }

    /**
     * Return the queue of notices
     *
     * @param string $stepId - try to get messages from one step by id or output all of messages
     *
     * @return array
     */
    public function getNotices($stepId = null)
    {
        if ($stepId) {
            // return step's messages array or nothing
            return isset($this->notices[$stepId]) ? $this->notices[$stepId] : [];
        } else {
            // return all steps messages
            return array_reduce($this->notices, 'array_merge', []);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Check rules">
    /**
     * Check products min/max quantities step rule
     *
     * @param integer $postId
     * @param array $rule
     *
     * @return integer
     */
    public static function checkStepQuantitiesRule($postId, $rule)
    {
        switch ($rule['type']) {
            case 'selected-from-step': {
                $output = 0;

                foreach (Cart::get($postId, ['includeSteps' => wp_parse_id_list($rule['value'])]) as $cartItem) {
                    if (isset($cartItem['product_id'])) {
                        $output++;
                    }
                }

                break;
            }

            case 'least-from-step': {
                $min = null;

                foreach (Cart::get($postId, ['includeSteps' => wp_parse_id_list($rule['value'])]) as $cartItem) {
                    if (is_null($min)) {
                        $min = $cartItem['quantity'];
                    }

                    $min = min($min, $cartItem['quantity']);
                }

                $output = $min;
                break;
            }

            case 'greatest-from-step': {
                $max = 0;

                foreach (Cart::get($postId, ['includeSteps' => wp_parse_id_list($rule['value'])]) as $cartItem) {
                    $max = max($max, $cartItem['quantity']);
                }

                $output = $max;
                break;
            }

            case 'sum-from-step': {
                $total = 0;

                foreach (Cart::get($postId, ['includeSteps' => wp_parse_id_list($rule['value'])]) as $cartItem) {
                    $total += $cartItem['quantity'];
                }

                $output = $total;
                break;
            }

            default:
            case 'count':
                $output = $rule['value'];
        }

        return apply_filters('wcProductsWizardStepQuantitiesRule', (float) $output, $postId, $rule);
    }

    /**
     * Check step quantities and other rules
     *
     * @param array $args
     * @param string $stepId
     *
     * @throws \Exception
     */
    public static function checkStepRules($args, $stepId)
    {
        $defaults = [
            'id' => null,
            'checkMinProductsSelected' => true,
            'checkMaxProductsSelected' => true,
            'checkMinTotalProductsQuantity' => true,
            'checkMaxTotalProductsQuantity' => true,
            'productsToAdd' => [],
            'productsToAddChecked' => []
        ];

        $args = array_merge($defaults, $args);

        $totalProductsQuantity = 0;
        $productsSelectedCount = !isset($args['productsToAddChecked'][$stepId])
            || !is_array($args['productsToAddChecked'][$stepId])
            ? 0
            : count(array_filter($args['productsToAddChecked'][$stepId]));

        foreach ((array) $args['productsToAdd'] as $product) {
            if (!isset($product['step_id'])
                || $product['step_id'] != $stepId
                || !isset($args['productsToAddChecked'][$stepId])
                || !is_array($args['productsToAddChecked'][$stepId])
                || (isset($product['product_id'])
                    && !in_array($product['product_id'], $args['productsToAddChecked'][$stepId]))
            ) {
                continue;
            }

            $totalProductsQuantity += isset($product['quantity']) ? $product['quantity'] : 1;
        }

        // min products selected check
        if ($args['checkMinProductsSelected']) {
            $minProductsSelected = Settings::getStep($args['id'], $stepId, 'min_products_selected');

            if ($minProductsSelected) {
                if (!is_array($minProductsSelected)) {
                    // @since 3.18.0 - older versions support
                    $minProductsSelected = [
                        'type' => 'count',
                        'value' => $minProductsSelected
                    ];
                }

                if (isset($minProductsSelected['value']) && $minProductsSelected['value']) {
                    $compareWith = self::checkStepQuantitiesRule($args['id'], $minProductsSelected);

                    if ($compareWith && $productsSelectedCount < $compareWith) {
                        throw new \Exception(
                            Settings::getMinimumProductsSelectedMessage(
                                $args['id'],
                                $compareWith,
                                $productsSelectedCount
                            ),
                            $stepId
                        );
                    }
                }
            }
        }

        // max products selected check
        if ($args['checkMaxProductsSelected']) {
            $maxProductsSelected = Settings::getStep($args['id'], $stepId, 'max_products_selected');

            if ($maxProductsSelected) {
                if (!is_array($maxProductsSelected)) {
                    // @since 3.18.0 - older versions support
                    $maxProductsSelected = [
                        'type' => 'count',
                        'value' => $maxProductsSelected
                    ];
                }

                if (isset($maxProductsSelected['value']) && $maxProductsSelected['value']) {
                    $compareWith = self::checkStepQuantitiesRule($args['id'], $maxProductsSelected);

                    if ($compareWith && $productsSelectedCount > $compareWith) {
                        throw new \Exception(
                            Settings::getMaximumProductsSelectedMessage(
                                $args['id'],
                                $compareWith,
                                $productsSelectedCount
                            ),
                            $stepId
                        );
                    }
                }
            }
        }

        // min total products selected check
        if ($args['checkMinTotalProductsQuantity']) {
            $minTotalQuantity = Settings::getStep($args['id'], $stepId, 'min_total_products_quantity');

            if ($minTotalQuantity && isset($minTotalQuantity['value']) && $minTotalQuantity['value']) {
                $compareWith = self::checkStepQuantitiesRule($args['id'], $minTotalQuantity);

                if ($compareWith && $totalProductsQuantity < $compareWith) {
                    throw new \Exception(
                        Settings::getMinimumProductsSelectedMessage($args['id'], $compareWith, $totalProductsQuantity),
                        $stepId
                    );
                }
            }
        }

        // max total products quantity check
        if ($args['checkMaxTotalProductsQuantity']) {
            $maxTotalQuantity = Settings::getStep($args['id'], $stepId, 'max_total_products_quantity');

            if ($maxTotalQuantity && isset($maxTotalQuantity['value']) && $maxTotalQuantity['value']) {
                $compareWith = self::checkStepQuantitiesRule($args['id'], $maxTotalQuantity);

                if ($compareWith && $totalProductsQuantity > $compareWith) {
                    throw new \Exception(
                        Settings::getMaximumProductsSelectedMessage($args['id'], $compareWith, $totalProductsQuantity),
                        $stepId
                    );
                }
            }
        }
    }

    /**
     * Check common quantities and other rules
     *
     * @param integer $id
     * @param array $cart
     *
     * @throws \Exception
     */
    public static function checkCommonRules($id, $cart)
    {
        $cartTotal = Cart::getTotal($id, ['reCalculateDiscount' => true]);
        $totalProductsQuantity = 0;
        $productsSelectedCount = 0;

        foreach ((array) $cart as $cartItem) {
            if (!isset($cartItem['quantity'])) {
                continue;
            }

            $productsSelectedCount++;
            $totalProductsQuantity += $cartItem['quantity'];
        }

        $minProductsSelected = Settings::getPost($id, 'min_products_selected');
        $maxProductsSelected = Settings::getPost($id, 'max_products_selected');
        $minTotalQuantity = Settings::getPost($id, 'min_total_products_quantity');
        $maxTotalQuantity = Settings::getPost($id, 'max_total_products_quantity');
        $minProductsPrice = Settings::getPost($id, 'min_products_price');
        $maxProductsPrice = Settings::getPost($id, 'max_products_price');

        // min products selected check
        if ($minProductsSelected && $productsSelectedCount < $minProductsSelected) {
            throw new \Exception(
                Settings::getMinimumProductsSelectedMessage($id, $minProductsSelected, $productsSelectedCount)
            );
        }

        // max products selected check
        if ($maxProductsSelected && $productsSelectedCount > $maxProductsSelected) {
            throw new \Exception(
                Settings::getMaximumProductsSelectedMessage($id, $maxProductsSelected, $productsSelectedCount)
            );
        }

        // min total products quantity check
        if ($minTotalQuantity && $totalProductsQuantity < $minTotalQuantity) {
            throw new \Exception(
                Settings::getMinimumProductsSelectedMessage($id, $minTotalQuantity, $totalProductsQuantity)
            );
        }

        // max total products quantity check
        if ($maxTotalQuantity && $totalProductsQuantity > $maxTotalQuantity) {
            throw new \Exception(
                Settings::getMaximumProductsSelectedMessage($id, $maxTotalQuantity, $totalProductsQuantity)
            );
        }

        // min products price check
        if ($minProductsPrice && $cartTotal < $minProductsPrice) {
            throw new \Exception(Settings::getMinimumProductsPriceMessage($id, $minProductsPrice, $cartTotal));
        }

        // max products price check
        if ($maxProductsPrice && $cartTotal > $maxProductsPrice) {
            throw new \Exception(Settings::getMaximumProductsPriceMessage($id, $maxProductsPrice, $cartTotal));
        }
    }
    // </editor-fold>

    // <editor-fold desc="Main actions">
    /**
     * Handles form submit
     *
     * @param array $args
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function submit($args)
    {
        $defaults = [
            'id' => null, // wizard ID
            'stepId' => null,
            'incrementActiveStep' => true,
            'dropNotCheckedProducts' => true,
            'productsToAdd' => [],
            'productsToAddChecked' => [],
            'stepsData' => []
        ];

        $args = array_merge($defaults, $args);
        $notCheckedProductsIds = [];

        do_action('wcProductsWizardBeforeSubmitForm', $args);

        // make it an array for sure
        $args['productsToAddChecked'] = $args['productsToAddChecked'] == '[]'
            ? []
            : (array) $args['productsToAddChecked'];

        $cart = Cart::get($args['id']);
        $stepsIds = array_unique(array_filter(array_keys($args['productsToAddChecked'])));
        $allStepsIds = $this->getStepsIds($args['id']);
        $qtyCheckArgs = $args;

        // get cart products for quantity rules check
        foreach ($cart as $cartItem) {
            if (!isset($cartItem['product_id'])
                || isset(
                    $cartItem['product_id'],
                    $args['productsToAdd'][$cartItem['step_id'] . '-' . $cartItem['product_id']]
                )
            ) {
                continue;
            }

            $qtyCheckArgs['productsToAddChecked'][$cartItem['step_id']][] = $cartItem['product_id'];
            $qtyCheckArgs['productsToAdd'] = (array) $qtyCheckArgs['productsToAdd'];
            $qtyCheckArgs['productsToAdd'][$cartItem['step_id'] . '-' . $cartItem['product_id']] = [
                'product_id' => $cartItem['product_id'],
                'step_id' => $cartItem['step_id'],
                'quantity' => $cartItem['quantity']
            ];
        }

        if (is_array($args['productsToAdd']) && !empty($args['productsToAdd'])) {
            foreach ($args['productsToAdd'] as $key => $product) {
                // emulate product selection for positive quantities according to the setting
                if (Settings::getStep($args['id'], $product['step_id'], 'add_to_cart_by_quantity')
                    && isset($product['quantity']) && (float) $product['quantity'] > 0
                ) {
                    $args['productsToAddChecked'][$product['step_id']][] = $product['product_id'];
                    $qtyCheckArgs['productsToAddChecked'][$product['step_id']][] = $product['product_id'];
                }

                if (isset($args['productsToAddChecked'][$product['step_id']])
                    && is_array($args['productsToAddChecked'][$product['step_id']])
                    && in_array($product['product_id'], $args['productsToAddChecked'][$product['step_id']])
                ) {
                    continue;
                }

                // collect product as not-checked and remove it from args
                $notCheckedProductsIds[$product['step_id']][] = $product['product_id'];

                unset($args['productsToAdd'][$key]);

                if (isset($qtyCheckArgs['productsToAdd'][$product['step_id'] . '-' . $product['product_id']])) {
                    unset($qtyCheckArgs['productsToAdd'][$product['step_id'] . '-' . $product['product_id']]);
                }
            }
        }

        foreach ($stepsIds as $stepId) {
            self::checkStepRules($qtyCheckArgs, $stepId);

            if (!Settings::getStep($args['id'], $stepId, 'several_products')) {
                Cart::removeByStepId($args['id'], $stepId);
            }

            if ($args['dropNotCheckedProducts']) {
                if (!empty($notCheckedProductsIds)) {
                    foreach ($notCheckedProductsIds as $stepId => $productsIds) {
                        foreach ($productsIds as $productsId) {
                            Cart::removeByProductId($args['id'], $productsId, $stepId);
                        }
                    }
                }
            }

            if (Settings::getPost($args['id'], 'strict_cart_workflow')) {
                // remove products from the next steps
                $skip = true;

                foreach ($allStepsIds as $allStepId) {
                    if (!$skip) {
                        Cart::removeByStepId($args['id'], $allStepId);
                    }

                    if ((string) $allStepId == (string) $stepId) {
                        $skip = false;
                    }
                }
            }
        }

        if (isset($_FILES['stepsData']) && !empty($_FILES['stepsData'])) {
            // create uploads folder if not exists
            if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH)) {
                mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH, 0777, true);
            }

            if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'uploads')) {
                mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'uploads', 0777, true);
            }

            foreach ($_FILES['stepsData']['error'] as $stepId => $inputNames) {
                foreach ($inputNames as $inputName => $error) {
                    if ($error != UPLOAD_ERR_OK
                        || $_FILES['stepsData']['size'][$stepId][$inputName] > wp_max_upload_size()
                    ) {
                        throw new \Exception(Settings::getPost($args['id'], 'file_upload_max_size_error'));
                    }

                    $temp = $_FILES['stepsData']['tmp_name'][$stepId][$inputName];
                    $name = basename($_FILES['stepsData']['name'][$stepId][$inputName]);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $validate = wp_check_filetype_and_ext($temp, $name);

                    if ($validate['proper_filename'] !== false) {
                        throw new \Exception(Settings::getPost($args['id'], 'file_upload_extension_error'));
                    }

                    $fileName = rtrim($name, ".$ext") . '-' . hash_file('md5', $temp) . ".$ext";
                    $destPath = WC_PRODUCTS_WIZARD_UPLOADS_PATH . "uploads/$fileName";

                    if (!move_uploaded_file($temp, $destPath)) {
                        throw new \Exception(Settings::getPost($args['id'], 'file_upload_error'));
                    }

                    $data = [
                        'key' => $inputName,
                        'step_id' => $stepId,
                        'value' => $destPath,
                        'name' => $name,
                        'type' => 'file',
                        'is_image' => @is_array(getimagesize($destPath))
                    ];

                    Cart::addStepData($args['id'], apply_filters('wcProductsWizardSubmitFormItemData', $data, $args));
                }
            }
        }

        if (is_array($args['stepsData']) && !empty($args['stepsData'])) {
            foreach ($args['stepsData'] as $stepId => $stepData) {
                if (!is_array($stepData)) {
                    continue;
                }

                foreach ($stepData as $key => $value) {
                    $data = [
                        'key' => $key,
                        'step_id' => $stepId,
                        'value' => $value
                    ];

                    Cart::addStepData($args['id'], apply_filters('wcProductsWizardSubmitFormItemData', $data, $args));
                }
            }
        }

        if (is_array($args['productsToAdd']) && !empty($args['productsToAdd'])) {
            foreach ($args['productsToAdd'] as $product) {
                $defaultData = [
                    'product_id' => null,
                    'variation_id' => null,
                    'variation' => [],
                    'quantity' => 1,
                    'step_id' => null,
                    'data' => [],
                    'request' => []
                ];

                $product = array_replace($defaultData, $product);

                // if product isn't selected
                if (!$product['product_id'] || !$product['step_id'] || !$product['quantity']
                    || !isset($args['productsToAddChecked'][$product['step_id']])
                    || !in_array($product['product_id'], $args['productsToAddChecked'][$product['step_id']])
                ) {
                    continue;
                }

                // find variation ID if necessary
                if (!empty($product['variation']) && !$product['variation_id']) {
                    $_product = wc_get_product($product['product_id']);
                    $variations = $_product->get_available_variations();
                    $excludedProductsIds = Settings::getStep($args['id'], $product['step_id'], 'excluded_products');

                    foreach ($variations as $variationKey => $variation) {
                        $availabilityRules =
                            Settings::getProductVariation($variation['variation_id'], 'availability_rules');

                        if (in_array($variation['variation_id'], $excludedProductsIds)
                            || !Utils::getAvailabilityByRules($args['id'], $availabilityRules)
                        ) {
                            unset($variations[$variationKey]);

                            continue;
                        }

                        $attributesMet = 0;

                        foreach ($variation['attributes'] as $attribute => $value) {
                            if (isset($product['variation'][$attribute])
                                && ($product['variation'][$attribute] == $value || $value == '')
                            ) {
                                $attributesMet++;
                            }
                        }

                        if (count($product['variation']) == $attributesMet) {
                            $product['variation_id'] = $variation['variation_id'];
                        }
                    }
                }

                try {
                    if (Settings::getStep($args['id'], $product['step_id'], 'several_variations_per_product')
                        && $product['variation_id'] && !empty($product['variation'])
                    ) {
                        $variationIsset = Cart::variationIsset(
                            $args['id'],
                            $product['variation_id'],
                            $product['variation'],
                            $product['step_id']
                        );

                        if ($variationIsset) {
                            Cart::removeByVariation(
                                $args['id'],
                                $product['variation_id'],
                                $product['variation'],
                                $product['step_id']
                            );
                        }
                    } elseif (Cart::productIsset($args['id'], $product['product_id'], $product['step_id'])) {
                        Cart::removeByProductId($args['id'], $product['product_id'], $product['step_id']);
                    }

                    Cart::addProduct($args['id'], apply_filters('wcProductsWizardSubmitFormItemData', $product, $args));
                } catch (\Exception $exception) {
                    // blend in step id in the exception
                    throw new \Exception($exception->getMessage(), $product['step_id']);
                }
            }
        }

        // change active step
        if ($args['stepId']) {
            $this->setActiveStep($args['id'], $args['stepId']);
        } elseif (filter_var($args['incrementActiveStep'], FILTER_VALIDATE_BOOLEAN)) {
            $this->setActiveStep($args['id'], $this->getNextStepId($args['id']));
        }

        do_action('wcProductsWizardAfterSubmitForm', $args);

        return true;
    }

    /** Handles form submit via ajax */
    public function submitAjax()
    {
        // $post variable might be overwritten
        $postData = Utils::parseArrayOfJSONs($_POST);

        try {
            $this->submit($postData);
        } catch (\Exception $exception) {
            $this->addNotice(
                $exception->getCode() ? $exception->getCode() : self::getActiveStepId($postData['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            self::ajaxReply(
                [
                    'hasError' => true,
                    'message' => $exception->getMessage(),
                    'content' => Template::html('router', $postData, ['echo' => false])
                ],
                $postData
            );
        }

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }

    /**
     * Handles adding products to the cart
     *
     * @param array $args
     *
     * @return array - products added with keys
     *
     * @throws \Exception
     */
    public function addToMainCart($args)
    {
        $defaults = ['incrementActiveStep' => false];
        $args = array_merge($defaults, $args);
        $id = $args['id'];
        $output = [];

        // submit step once again
        $this->submit($args);
        $stepsIds = self::getStepsIds($id);
        $cart = Cart::get($id, ['pureUploadsNames' => false]);
        $cart = apply_filters('wcProductsWizardAddAllToMainCartItems', $cart, $id);

        do_action('wcProductsWizardBeforeAddAllToMainCart', $id, $cart);

        // <editor-fold desc="Steps rules check">
        $qtyCheckArgs = [
            'productsToAddChecked' => [],
            'productsToAdd' => []
        ];

        foreach ($cart as $product) {
            if (!isset($product['product_id'])) {
                continue;
            }

            $qtyCheckArgs['productsToAddChecked'][$product['step_id']][] = $product['product_id'];
            $qtyCheckArgs['productsToAdd'][$product['step_id'] . '-' . $product['product_id']] = $product;
        }

        // check each step once again for all pages
        foreach ($stepsIds as $stepId) {
            self::checkStepRules($qtyCheckArgs, $stepId);
        }
        // </editor-fold>

        // common price and quantity rules
        self::checkCommonRules($id, $cart);

        // products already should be in the cart
        if (Settings::getPost($id, 'reflect_in_main_cart')) {
            return [];
        }

        // clear main cart before
        if (Settings::getPost($id, 'clear_main_cart_on_confirm')) {
            \WC()->cart->empty_cart();
        }

        // main work lower
        if (!empty($cart)) {
            $groupProductsIntoKits = Settings::getPost($id, 'group_products_into_kits');
            $kitsType = Settings::getPost($id, 'kits_type');
            $kitBaseProduct = Settings::getPost($id, 'kit_base_product');
            $kitId = null;
            $kitTitle = null;
            $isKitQuantityFixed = false;
            $baseKitItemCartKey = null;
            $baseKitItemKey = null;
            $baseKitItem = null;
            $commonDiscount = Settings::getPost($id, 'price_discount');

            // define base of the kit
            if ($groupProductsIntoKits) {
                $kitId = apply_filters('wcProductsWizardKitId', date('d-m-Y H:i:s'), $id, $cart);

                // add pre-defined base product to the cart
                if ($kitBaseProduct) {
                    $stepId = reset($stepsIds);
                    $productId = get_post_type($kitBaseProduct) != 'product'
                        ? wp_get_post_parent_id($kitBaseProduct)
                        : $kitBaseProduct;

                    $variationId = $productId != $kitBaseProduct ? $kitBaseProduct : '';
                    $variation = '';
                    $cartItemKey = WC()->cart->generate_cart_id($productId, $variationId, $variation, []);
                    $productData = [
                        'key' => $cartItemKey,
                        'step_id' => $stepId,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'variation' => $variation,
                        'quantity' => 1,
                        'sold_individually' => 0,
                        'data' => wc_get_product($kitBaseProduct)
                    ];

                    $productData = apply_filters('wcProductsWizardKitBaseProductData', $productData, $id, $cart);

                    // unshift base product
                    $cart = [$cartItemKey => $productData] + $cart;
                }
            }

            foreach ($cart as $key => &$cartItem) {
                $skipItems = (array) Settings::getStep($id, $cartItem['step_id'], 'dont_add_to_cart_products');

                // should have a step ID and be not an excluded product/variation/step
                if (!isset($cartItem['step_id']) || !isset($cartItem['product_id'])
                    || (isset($cartItem['key'], $cartItem['value']) && empty($cartItem['value']))
                    || Settings::getStep($id, $cartItem['step_id'], 'dont_add_to_cart')
                    || in_array($cartItem['product_id'], $skipItems)
                    || (isset($cartItem['variation_id']) && in_array($cartItem['variation_id'], $skipItems))
                ) {
                    continue;
                }

                $productData = [
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'variation_id' => isset($cartItem['variation_id']) ? $cartItem['variation_id'] : null,
                    'variation' => isset($cartItem['variation']) ? $cartItem['variation'] : [],
                    'data' => isset($cartItem['data']) && is_array($cartItem['data']) ? $cartItem['data'] : [],
                    'request' => isset($cartItem['request']) && is_array($cartItem['request'])
                        ? $cartItem['request']
                        : null
                ];

                $productData['data']['wcpw_id'] = $id;

                // <editor-fold desc="Check different discount rules">
                // product discount
                if ($productData['variation_id']) {
                    $discount = Settings::getProductVariation($productData['variation_id'], 'discount');
                } else {
                    $discount = Settings::getProduct($productData['product_id'], 'discount');
                }

                if (is_array($discount) && isset($discount['type'], $discount['value']) && !empty($discount['value'])) {
                    $productData['data']['wcpw_product_discount'] = $discount;
                }

                // categories discount
                $categories = wp_get_object_terms($productData['product_id'], 'product_cat');

                foreach ($categories as $category) {
                    $discount = Settings::getProductCategory($category->term_id, 'discount');

                    if (is_array($discount) && isset($discount['type'], $discount['value'])
                        && !empty($discount['value'])
                    ) {
                        $productData['data']['wcpw_category_discount'] = $discount;

                        break;
                    }
                }

                // common discount
                if ($commonDiscount) {
                    $productData['data']['wcpw_discount'] = $commonDiscount;
                }
                // </editor-fold>

                // add kit data to the product
                if ($groupProductsIntoKits) {
                    $productData['data']['wcpw_kit_type'] = $kitsType;

                    // is a base product
                    if (!$baseKitItem) {
                        // if the base item isn't defined yet make it from the first product
                        $baseKitItem = $cartItem;
                        $baseKitItemKey = $key;
                        $kitTitle = apply_filters('wcProductsWizardKitTitle', get_the_title($baseKitItem['product_id']), $id, $cart);
                        $isKitQuantityFixed = isset($cartItem['sold_individually'])
                            ? !$cartItem['sold_individually']
                            : !Settings::getStep($id, $baseKitItem['step_id'], 'sold_individually');

                        if ($kitBaseProduct) {
                            // save info about pre-defined base product
                            $productData['data']['wcpw_is_base_kit_product'] = true;
                        }

                        // check prices
                        if ($kitsType == 'combined') {
                            $kitPrice = Settings::getPost($id, 'kit_price');
                            $kitBasePrice = Settings::getPost($id, 'kit_base_price');

                            if ($kitPrice) {
                                // add fixed price data
                                $productData['data']['wcpw_kit_price'] = $kitPrice;
                            } elseif ($kitBasePrice) {
                                // add base price data
                                $productData['data']['wcpw_kit_base_price'] = $kitBasePrice;
                                $productData['data']['wcpw_kit_base_price_string'] =
                                    Settings::getPost($id, 'kit_base_price_string');
                            }
                        }

                        if ($kitsType == 'combined' || $kitBaseProduct) {
                            $thumbnailId = get_post_thumbnail_id($id);
                            $generatedThumbnail = null;

                            if (Settings::getPost($id, 'generate_thumbnail')) {
                                $generatedThumbnail = Core::generateThumbnail($id, $cart);
                            }

                            $isKitQuantityFixed = false;
                            $productData['quantity'] = 1;
                            $productData['data']['wcpw_kit_children'] = [];

                            if (!empty($generatedThumbnail)) {
                                $productData['data']['wcpw_kit_thumbnail_url'] = $generatedThumbnail['url'];
                                $productData['data']['wcpw_kit_thumbnail_path'] = $generatedThumbnail['path'];
                            } elseif ($thumbnailId) {
                                $productData['data']['wcpw_kit_thumbnail_id'] = $thumbnailId;
                            }

                            // collect children
                            foreach ($cart as $childKey => $child) {
                                $skipItems = (array) Settings::getStep(
                                    $id,
                                    $cartItem['step_id'],
                                    'dont_add_to_cart_products'
                                );

                                // should have a step ID, be not an excluded product/variation/step or input field
                                if (!isset($child['step_id'])
                                    || (isset($child['key'], $child['value']) && empty($child['value']))
                                    || Settings::getStep($id, $child['step_id'], 'dont_add_to_cart')
                                    || (isset($child['product_id']) && in_array($child['product_id'], $skipItems))
                                    || (isset($child['variation_id']) && in_array($child['variation_id'], $skipItems))
                                ) {
                                    continue;
                                }

                                // don't add itself
                                if ($baseKitItem && $childKey == $key) {
                                    continue;
                                }

                                // <editor-fold desc="Check different discount rules">
                                // product discount
                                if ($child['variation_id']) {
                                    $discount = Settings::getProductVariation($child['variation_id'], 'discount');
                                } else {
                                    $discount = Settings::getProduct($child['product_id'], 'discount');
                                }

                                if (is_array($discount) && isset($discount['type'], $discount['value']) && !empty($discount['value'])) {
                                    $child['wcpw_product_discount'] = $discount;
                                }

                                // categories discount
                                $categories = wp_get_object_terms($child['product_id'], 'product_cat');

                                foreach ($categories as $category) {
                                    $discount = Settings::getProductCategory($category->term_id, 'discount');

                                    if (is_array($discount) && isset($discount['type'], $discount['value'])
                                        && !empty($discount['value'])
                                    ) {
                                        $child['wcpw_category_discount'] = $discount;

                                        break;
                                    }
                                }

                                // common discount
                                if ($commonDiscount) {
                                    $child['wcpw_discount'] = $commonDiscount;
                                }
                                // </editor-fold>

                                $productData['data']['wcpw_kit_children'][] = $child;
                            }
                        }
                    }

                    // is a child product
                    if ($baseKitItemKey != $key) {
                        $productData['data']['wcpw_kit_parent_key'] = $baseKitItemCartKey;

                        if ($kitsType == 'combined') {
                            $productData['data']['wcpw_is_hidden_product'] = true;
                        }
                    }

                    $productData['data']['wcpw_kit_id'] = $kitId;
                    $productData['data']['wcpw_kit_title'] = $kitTitle;
                    $productData['data']['wcpw_is_kit_base'] = (int) ($key == $baseKitItemKey);
                    $productData['data']['wcpw_is_kit_quantity_fixed'] = (int) $isKitQuantityFixed;
                }

                $productData = apply_filters('wcProductsWizardMainCartProductData', $productData, $id, $cartItem);
                $cartItemKey = Product::addToMainCart($productData);

                // save kit base product key
                if ($groupProductsIntoKits && $cartItemKey && !$baseKitItemCartKey) {
                    $baseKitItemCartKey = $cartItemKey;
                }

                // save product data to output
                if ($cartItemKey) {
                    $output[$cartItemKey] = $productData;
                }
            }
        }

        // truncate the cart
        Cart::truncate($id);

        // reset active step to the first
        self::setActiveStep($id, reset($stepsIds));

        do_action('wcProductsWizardAfterAddAllToMainCart', $id, $cart, $output);

        return $output;
    }

    /** Handles adding products to the cart via ajax */
    public function addToMainCartAjax()
    {
        // $post variable might be overwritten
        $postData = Utils::parseArrayOfJSONs($_POST);

        try {
            $this->addToMainCart($postData);
        } catch (\Exception $exception) {
            $this->addNotice(
                $exception->getCode() ? $exception->getCode() : self::getActiveStepId($postData['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            self::ajaxReply(
                [
                    'hasError' => true,
                    'message' => $exception->getMessage(),
                    'content' => Template::html('router', $postData, ['echo' => false])
                ],
                $postData
            );
        }

        self::ajaxReply(
            [
                'finalRedirectUrl' => Settings::getFinalRedirectUrl($postData['id']),
                'preventRedirect' => apply_filters('wcProductsWizardPreventFinalRedirect', false, $postData)
            ],
            $postData
        );
    }

    /**
     * Get the form template
     *
     * @param array $args
     */
    public static function get($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'page' => 1
        ];

        $args = array_merge($defaults, $args);

        do_action('wcProductsWizardBeforeGetForm', $args);

        if (Settings::getPost($args['id'], 'strict_cart_workflow')) {
            // remove products from the next steps
            $skip = true;

            foreach (self::getStepsIds($args['id']) as $stepId) {
                if (!$skip) {
                    Cart::removeByStepId($args['id'], $stepId);
                }

                if ($stepId == $args['stepId']) {
                    $skip = false;
                }
            }
        }

        self::setActiveStep($args['id'], $args['stepId']);
        self::resetPreviousStepId($args['id']);

        do_action('wcProductsWizardAfterGetForm', $args);
    }

    /** Get the form template via ajax */
    public function getAjax()
    {
        $postData = Utils::parseArrayOfJSONs($_POST);

        self::get($postData);

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }

    /**
     * Handles form skipping
     *
     * @param array $args
     */
    public static function skip($args)
    {
        $defaults = ['id' => null];
        $args = array_merge($defaults, $args);

        do_action('wcProductsWizardBeforeSkipForm', $args);

        $activeStep = self::getActiveStepId($args['id']);

        Cart::removeByStepId($args['id'], $activeStep);
        self::setActiveStep($args['id'], self::getNextStepId($args['id']));

        do_action('wcProductsWizardAfterSkipForm', $args);
    }

    /** Handles form skipping via ajax */
    public function skipAjax()
    {
        $postData = Utils::parseArrayOfJSONs($_POST);

        self::skip($postData);

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }

    /**
     * Handles all steps skipping
     *
     * @param array $args
     */
    public static function skipAll($args)
    {
        $defaults = ['id' => null];
        $args = array_merge($defaults, $args);

        do_action('wcProductsWizardBeforeSkipAll', $args);

        $stepsIds = self::getStepsIds($args['id']);

        self::setPreviousStepId($args['id'], self::getActiveStepId($args['id']));
        self::setActiveStep($args['id'], end($stepsIds));

        do_action('wcProductsWizardAfterSkipAll', $args);
    }

    /** Handles all steps skipping via ajax */
    public function skipAllAjax()
    {
        $postData = Utils::parseArrayOfJSONs($_POST);

        self::skipAll($postData);

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }

    /**
     * Reset cart and set the form to the first step
     *
     * @param array $args
     */
    public static function reset($args)
    {
        $defaults = ['id' => null];
        $args = array_merge($defaults, $args);

        do_action('wcProductsWizardBeforeResetForm', $args);

        $stepsIds = self::getStepsIds($args['id']);

        if (!Settings::getPost($args['id'], 'reflect_in_main_cart')) {
            $cart = Cart::get($args['id']);

            foreach ($cart as $key => $item) {
                if (!in_array($item['step_id'], $stepsIds)) {
                    continue;
                }

                Cart::removeByProductKey($args['id'], $key);
            }
        } else {
            Cart::truncate($args['id']);
        }

        Storage::remove(self::$previousStepsSessionKey, $args['id']);
        self::setActiveStep($args['id'], reset($stepsIds));

        do_action('wcProductsWizardAfterResetForm', $args);
    }

    /** Reset cart and set the form to the first step via ajax */
    public function resetAjax()
    {
        $postData = Utils::parseArrayOfJSONs($_POST);

        // unset for sure because this leads to a wrong router view
        unset($postData['stepId']);

        self::reset($postData);

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }
    // </editor-fold>

    // <editor-fold desc="Product actions">
    /**
     * Add product to the cart
     *
     * @param array $args
     *
     * @return bool|array
     *
     * @throws \Exception
     */
    public function addCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productToAddKey' => null,
            'productsToAdd' => [],
            'incrementActiveStep' => false,
            'dropNotCheckedProducts' => false,
            'checkMinProductsSelected' => false,
            'checkMinTotalProductsQuantity' => false
        ];

        $args = array_merge($defaults, $args);
        $behavior = Settings::getStep($args['id'], reset($args['productsToAdd'])['step_id'], 'add_to_cart_behavior');

        if (isset($args['productsToAdd'][$args['productToAddKey']])) {
            $productData = $args['productsToAdd'][$args['productToAddKey']];
            $args['productsToAddChecked'] = [$productData['step_id'] => [$productData['product_id']]];
        }

        if ($behavior == 'submit') {
            $args['incrementActiveStep'] = true;
        } elseif ($behavior == 'add-to-main-cart') {
            do_action('wcProductsWizardBeforeAddCartProduct', $args);

            return $this->addToMainCart($args);
        }

        do_action('wcProductsWizardBeforeAddCartProduct', $args);

        return $this->submit($args);
    }

    /**
     * Add product to the cart via ajax
     *
     * @throws \Exception
     */
    public function addCartProductAjax()
    {
        // $post variable might be overwritten
        $postData = Utils::parseArrayOfJSONs($_POST);

        try {
            $this->addCartProduct($postData);
        } catch (\Exception $exception) {
            $this->addNotice(
                $exception->getCode() ? $exception->getCode() : self::getActiveStepId($postData['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            self::ajaxReply(
                [
                    'hasError' => true,
                    'message' => $exception->getMessage(),
                    'content' => Template::html('router', $postData, ['echo' => false])
                ],
                $postData
            );
        }

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }

    /**
     * Remove product from the cart
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public function removeCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productCartKey' => null
        ];

        $args = array_merge($defaults, $args);
        $cart = Cart::get($args['id']);
        $product = isset($cart[$args['productCartKey']]) ? $cart[$args['productCartKey']] : null;
        $activeStepId = self::getActiveStepId($args['id']);

        if ($product && $product['step_id'] != $activeStepId) {
            // collect all other cart products from the same step to check minimum quantities rules
            $cart = Cart::getByStepId($args['id'], $product['step_id']);
            $qtyCheckArgs = [
                'id' => $args['id'],
                'productsToAdd' => [],
                'productsToAddChecked' => []
            ];

            foreach ($cart as $cartItem) {
                if ($cartItem['product_id'] == $product['product_id']) {
                    continue;
                }

                $qtyCheckArgs['productsToAddChecked'][$cartItem['step_id']][] = $cartItem['product_id'];
                $qtyCheckArgs['productsToAdd'][$cartItem['step_id'] . '-' . $cartItem['product_id']] = [
                    'product_id' => $cartItem['product_id'],
                    'step_id' => $cartItem['step_id'],
                    'quantity' => $cartItem['quantity']
                ];
            }

            self::checkStepRules($qtyCheckArgs, $product['step_id']);
        }

        do_action('wcProductsWizardBeforeRemoveCartProduct', $args);

        Cart::removeByProductKey($args['id'], $args['productCartKey']);
    }

    /** Remove item from the cart via ajax */
    public function removeCartProductAjax()
    {
        $postData = Utils::parseArrayOfJSONs($_POST);
        $defaults = ['id' => null];
        $args = array_merge($defaults, $postData);

        try {
            $this->removeCartProduct($args);
        } catch (\Exception $exception) {
            $this->addNotice(
                self::getActiveStepId($args['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            self::ajaxReply(
                [
                    'hasError' => true,
                    'message' => $exception->getMessage(),
                    'content' => Template::html('router', $postData, ['echo' => false])
                ],
                $postData
            );
        }

        self::ajaxReply(['content' => Template::html('router', $args, ['echo' => false])], $postData);
    }

    /**
     * Update product in the cart
     *
     * @param array $args
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function updateCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productCartKey' => null
        ];

        $args = array_merge($defaults, $args);
        $product = Cart::getItemByKey($args['id'], $args['productCartKey']);
        $args['productToAddKey'] = $product ? $product['product_id'] : null;

        do_action('wcProductsWizardBeforeUpdateCartProduct', $args);

        Cart::removeByProductKey($args['id'], $args['productCartKey']);

        return $this->addCartProduct($args);
    }

    /** Update product in the cart via ajax */
    public function updateCartProductAjax()
    {
        // $post variable might be overwritten
        $postData = Utils::parseArrayOfJSONs($_POST);
        $defaults = ['id' => null];
        $args = array_merge($defaults, $postData);

        try {
            $this->updateCartProduct($args);
        } catch (\Exception $exception) {
            $this->addNotice(
                $exception->getCode() ? $exception->getCode() : self::getActiveStepId($args['id']),
                [
                    'view' => 'custom',
                    'message' => $exception->getMessage()
                ]
            );

            self::ajaxReply(
                [
                    'hasError' => true,
                    'message' => $exception->getMessage(),
                    'content' => Template::html('router', $postData, ['echo' => false])
                ],
                $postData
            );
        }

        self::ajaxReply(['content' => Template::html('router', $postData, ['echo' => false])], $postData);
    }
    // </editor-fold>

    // <editor-fold desc="Steps">
    /**
     * Get an array of the steps ids which are used in the wizard
     *
     * @param integer $postId
     *
     * @return array
     */
    public static function getStepsIds($postId)
    {
        // have no cache because of the dynamic workflow
        $output = self::getSteps($postId, ['idsOnly' => true]);

        return apply_filters('wcProductsWizardStepsIds', $output, $postId);
    }

    /**
     * Get an array of the steps which used in the wizard
     *
     * @param integer $postId
     * @param array $args
     *
     * @return array
     */
    public static function getSteps($postId, $args = [])
    {
        $defaults = ['idsOnly' => false];
        $args = array_merge($defaults, $args);
        $output = [];
        $stepsIds = Settings::getStepsIds($postId);

        foreach ($stepsIds as $stepId) {
            $availabilityRules = Settings::getStep($postId, $stepId, 'availability_rules');

            if (!Utils::getAvailabilityByRules($postId, $availabilityRules)) {
                continue;
            }

            if ($args['idsOnly']) {
                $output[] = $stepId;

                continue;
            }

            $output[$stepId] = self::getStep($postId, $stepId);
        }

        if (Settings::getPost($postId, 'enable_description_tab')) {
            // add description tab
            if ($args['idsOnly']) {
                array_unshift($output, 'start');
            } else {
                $output = ['start' => self::getStep($postId, 'start')] + $output;
            }
        }

        if (Settings::getPost($postId, 'enable_results_tab')) {
            // add results tab
            if ($args['idsOnly']) {
                $output[] = 'result';
            } else {
                $output['result'] = self::getStep($postId, 'result');
            }
        }

        return apply_filters('wcProductsWizardSteps', $output, $postId);
    }

    /**
     * Get step data
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return array
     */
    public static function getStep($postId, $stepId)
    {
        static $stepsCache = [];

        // set global variable
        Instance()->activeStepId = $stepId;

        if (isset($stepsCache[$postId], $stepsCache[$postId][$stepId])) {
            return apply_filters('wcProductsWizardStep', $stepsCache[$postId][$stepId], $postId, $stepId);
        }

        $output = [];

        if (is_numeric($stepId)) {
            $description = Settings::getStep($postId, $stepId, 'description');
            $descriptionAutoTags = Settings::getStep($postId, $stepId, 'description_auto_tags');
            $title = Settings::getStep($postId, $stepId, 'title');
            $output = [
                'id' => $stepId,
                'name' => $title ? $title : $stepId,
                'thumbnail' => Settings::getStep($postId, $stepId, 'thumbnail'),
                'categories' => Settings::getStep($postId, $stepId, 'categories'),
                'descriptionPosition' => Settings::getStep($postId, $stepId, 'description_position'),
                'description' => do_shortcode($descriptionAutoTags ? wpautop($description) : $description)
            ];
        } elseif ($stepId == 'start') {
            $output = [
                'id' => 'start',
                'name' => Settings::getPost($postId, 'description_tab_title'),
                'thumbnail' => Settings::getPost($postId, 'description_tab_thumbnail'),
                'description' => do_shortcode(wpautop(get_post_field('post_content', $postId))),
                'descriptionPosition' => 'top'
            ];
        } elseif ($stepId == 'result') {
            $output = [
                'id' => 'result',
                'name' => Settings::getPost($postId, 'results_tab_title'),
                'thumbnail' => Settings::getPost($postId, 'results_tab_thumbnail'),
                'description' => do_shortcode(wpautop(Settings::getPost($postId, 'results_tab_description'))),
                'descriptionPosition' => 'top'
            ];
        }

        $stepsCache[$postId][$stepId] = $output;

        return apply_filters('wcProductsWizardStep', $output, $postId, $stepId);
    }

    /**
     * Get active wizard step id from the session variable
     *
     * @param integer $postId
     *
     * @return string
     */
    public static function getActiveStepId($postId)
    {
        $stepsIds = self::getStepsIds($postId);
        $output = Storage::get(self::$activeStepsSessionKey, $postId);

        if ($output && in_array($output, $stepsIds)) {
            Instance()->activeStepId = $output;

            return apply_filters('wcProductsWizardActiveStepId', $output, $postId);
        }

        $output = reset($stepsIds);
        Instance()->activeStepId = $output;

        return apply_filters('wcProductsWizardActiveStepId', $output, $postId);
    }

    /**
     * Get active wizard step from the session variable
     *
     * @param integer $postId
     *
     * @return array
     */
    public static function getActiveStep($postId)
    {
        $output = self::getStep($postId, self::getActiveStepId($postId));

        return apply_filters('wcProductsWizardActiveStep', $output, $postId);
    }

    /**
     * Set active wizard step to the session variable
     *
     * @param integer $postId
     * @param integer|string $step
     */
    public static function setActiveStep($postId, $step)
    {
        Instance()->activeStepId = $step;

        Storage::set(self::$activeStepsSessionKey, $postId, $step);
    }

    /**
     * Get the next active wizard step from the session variable
     *
     * @param integer $postId
     *
     * @return string|null
     */
    public static function getNextStepId($postId)
    {
        $stepsIds = self::getStepsIds($postId);
        $activeStep = self::getActiveStepId($postId);
        $prevStep = false;

        foreach ($stepsIds as $stepId) {
            if ($prevStep == $activeStep) {
                return $stepId;
            }

            $prevStep = $stepId;
        }

        return null;
    }

    /**
     * Set the previous active wizard step id
     *
     * @param integer $postId
     * @param integer $value
     */
    public static function setPreviousStepId($postId, $value)
    {
        Storage::set(self::$previousStepsSessionKey, $postId, $value);
    }

    /**
     * Reset the previous active wizard step id
     *
     * @param integer $postId
     */
    public static function resetPreviousStepId($postId)
    {
        Storage::remove(self::$previousStepsSessionKey, $postId);
    }

    /**
     * Get the previous active wizard step id
     *
     * @param integer $postId
     *
     * @return string|null
     */
    public static function getPreviousStepId($postId)
    {
        $value = Storage::get(self::$previousStepsSessionKey, $postId);

        if ($value) {
            return $value;
        }

        $stepsIds = self::getStepsIds($postId);
        $activeStep = self::getActiveStepId($postId);
        $prevStep = false;

        foreach ($stepsIds as $stepId) {
            if ($stepId == $activeStep) {
                return $prevStep;
            }

            $prevStep = $stepId;
        }

        return null;
    }
    // </editor-fold>

    // <editor-fold desc="Navigation">
    /**
     * Check previous step existence
     *
     * @param integer $postId
     *
     * @return boolean
     */
    public static function canGoBack($postId)
    {
        $stepsIds = self::getStepsIds($postId);
        $activeStep = self::getActiveStepId($postId);

        return reset($stepsIds) != $activeStep;
    }

    /**
     * Check next step existence
     *
     * @param integer $postId
     *
     * @return boolean
     */
    public static function canGoForward($postId)
    {
        $stepsIds = self::getStepsIds($postId);
        $activeStep = self::getActiveStepId($postId);

        return end($stepsIds) != $activeStep;
    }

    /**
     * Get pagination items array
     *
     * @param array $args
     *
     * @return array
     */
    public static function getPaginationItems($args)
    {
        $output = [];
        $pages = [];
        $defaults = [
            'stepId' => null,
            'page' => 1,
            'productsQuery' => []
        ];

        $args = array_merge($defaults, $args);

        if (!$args['productsQuery'] || empty($args['productsQuery'])) {
            return [];
        }

        if (isset($_REQUEST['wcpwPage'])) {
            if (is_string($_REQUEST['wcpwPage'])) {
                parse_str($_REQUEST['wcpwPage'], $pages);
            } else {
                $pages = $_REQUEST['wcpwPage'];
            }
        }

        $paginationArgs = [
            'format' => '?wcpwPage[' . $args['stepId'] . ']=%#%',
            'base' => '%_%',
            'total' => $args['productsQuery']->max_num_pages,
            'current' => isset($pages[$args['stepId']]) ? (int) $pages[$args['stepId']] : $args['page'],
            'show_all' => false,
            'end_size' => 1,
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => L10N::r('« Previous'),
            'next_text' => L10N::r('Next »'),
            'type' => 'array'
        ];

        $paginationArgs = apply_filters('wcProductsWizardPaginationArgs', $paginationArgs, $args);

        $links = paginate_links($paginationArgs);

        foreach ((array) $links as $link) {
            // add custom classes
            $link = str_replace('page-numbers', 'page-numbers page-link', $link);

            // replace empty href
            $link = str_replace('href=""', 'href="?paged=1"', $link);
            $link = str_replace("href=''", 'href="?paged=1"', $link);

            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $link, $result);

            if (!empty($result) && !empty($result['href'][0])) {
                $href = $result['href'][0];
                $linkParts = parse_url($href);

                parse_str($linkParts['query'], $linkPartsQuery);

                // add custom attributes
                $link = str_replace(
                    ' href=',
                    ' data-component="wcpw-form-pagination-link" data-step-id="' . $args['stepId']
                    . '" data-page="'
                    . (isset($linkPartsQuery['wcpwPage'], $linkPartsQuery['wcpwPage'][$args['stepId']])
                        ? $linkPartsQuery['wcpwPage'][$args['stepId']] : 1)
                    . '" href=',
                    $link
                );
            }

            $output[] = [
                'class' => strpos($link, 'current') !== false ? 'active' : '',
                'innerHtml' => $link
            ];
        }

        return apply_filters('wcProductsWizardPaginationItems', $output, $args);
    }

    /**
     * Return nav tabs items array
     *
     * @param integer $postId
     *
     * @return array
     */
    public static function getNavItems($postId)
    {
        static $navItemsCache = [];

        if (isset($navItemsCache[$postId])) {
            return apply_filters('wcProductsWizardNavItems', $navItemsCache[$postId], $postId);
        }

        $activeStepId = self::getActiveStepId($postId);
        $output = self::getSteps($postId);
        $stepId = "";
        foreach ($output as &$step) {

            if($step['id'] !== "start") {
                $stepId = $step['id'];
                break;
            }
        }

        if($activeStepId == "start")
            self::setActiveStep($postId,$stepId);

        $activeStepId = self::getActiveStepId($postId);
        $nextStepId = self::getNextStepId($postId);
        $previousStepId = self::getPreviousStepId($postId);
        $isPreviousStepIdDefined = Storage::exists(self::$previousStepsSessionKey, $postId);
        $mode = Settings::getPost($postId, 'mode');
        $navAction = Settings::getPost($postId, 'nav_action');
        $isFreeWalk = $mode == 'free-walk';
        $activeNavItem = null;
        $previousNavItem = null;

        foreach ($output as &$step) {
            if ($activeStepId == $step['id']) {
                // active step
                $activeNavItem = $step['id'];
                $step['action'] = 'none';
                $step['state'] = 'active';
                $step['class'] = 'active';
                $step['value'] = $step['id'];
            } elseif ($step['id'] == $nextStepId) {
                // next active step
                $step['action'] = $navAction;
                $step['state'] = 'next-active';
                $step['class'] = 'next-active';
                $step['value'] = $navAction == 'get-step' ? $step['id'] : ''; // empty is needed for step dependencies
            } else {
                // other items
                $step['action'] = $activeNavItem && !$isFreeWalk ? 'none' : $navAction;
                $step['state'] = $activeNavItem && !$isFreeWalk ? 'disabled' : 'default';
                $step['class'] = $activeNavItem && !$isFreeWalk ? 'disabled' : ($activeNavItem ? 'default' : 'past');
                $step['value'] = $step['id'];
            }

            // if was "skip all" action
            if (!$isFreeWalk && $isPreviousStepIdDefined) {
                if ($activeStepId == $step['id']) {
                    // active step
                    $step['action'] = 'none';
                    $step['state'] = 'active';
                    $step['class'] = 'active';
                } elseif ($previousStepId == $step['id']) {
                    // previous active step
                    $previousNavItem = $step['id'];
                    $step['action'] = 'get-step';
                    $step['state'] = 'default';
                    $step['class'] = 'last-active' . ($activeNavItem ? '' : ' past');
                } elseif (!$previousNavItem) {
                    // previous steps
                    $step['action'] = 'get-step';
                    $step['state'] = 'default';
                    $step['class'] = 'past';
                } else {
                    // other items
                    $step['action'] = 'none';
                    $step['state'] = 'disabled';
                    $step['class'] = 'disabled';
                }
            }
        }

        $navItemsCache[$postId] = $output;

        return apply_filters('wcProductsWizardNavItems', $output, $postId);
    }
    // </editor-fold>

    // <editor-fold desc="Filter">
    /**
     * Get step filter fields array
     *
     * @param integer $postId
     * @param integer $stepId
     * @param array $appliedFilters
     *
     * @return array
     */
    public static function getFilterFields($postId, $stepId, $appliedFilters = [])
    {
        $output = [];
        $filters = Settings::getStep($postId, $stepId, 'filters');

        if (empty($filters)) {
            return $output;
        }

        $attributeTaxonomies = wc_get_attribute_taxonomies();

        foreach ($filters as $filter) {
            if (!isset($filter['view']) || !$filter['view'] || !isset($filter['source']) || !$filter['source']) {
                continue;
            }

            $value = null;
            $values = [];
            $key = $filter['source'];
            $label = '';

            switch ($filter['source']) {
                case 'category': {
                    $terms = [];
                    $categories = Settings::getStep($postId, $stepId, 'categories');
                    $includedProductsIds = array_filter(Settings::getStep($postId, $stepId, 'included_products'));
                    $value = isset($appliedFilters['category']) ? $appliedFilters['category'] : null;
                    $label = isset($filter['label']) && $filter['label'] ? $filter['label'] : L10N::r('Category');

                    if (!empty($includedProductsIds)) {
                        foreach ($includedProductsIds as $productId) {
                            $terms = array_merge(
                                $terms,
                                wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids'])
                            );
                        }

                        $terms = array_unique($terms);
                    } else {
                        foreach ($categories as $category) {
                            $terms = array_replace(
                                $terms,
                                [$category => get_term($category, 'product_cat')],
                                Utils::getSubTerms($category, 'product_cat')
                            );
                        }
                    }

                    if (isset($filter['include']) && !empty($filter['include'])) {
                        $ids = wp_parse_id_list($filter['include']);

                        if (!empty($ids)) {
                            foreach ($terms as $key => $term) {
                                $termId = $term;

                                if (is_object($term)) {
                                    $termId = $term->term_id;
                                }

                                if (!in_array($termId, $ids)) {
                                    unset($terms[$key]);
                                }
                            }
                        }
                    } elseif (isset($filter['exclude']) && !empty($filter['exclude'])) {
                        $ids = wp_parse_id_list($filter['exclude']);

                        if (!empty($ids)) {
                            foreach ($terms as $key => $term) {
                                $termId = $term;

                                if (is_object($term)) {
                                    $termId = $term->term_id;
                                }

                                if (in_array($termId, $ids)) {
                                    unset($terms[$key]);
                                }
                            }
                        }
                    }

                    foreach ($terms as $term) {
                        if (!is_object($term)) {
                            $term = get_term($term, 'product_cat');
                        }

                        $values[] = [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'thumbnailId' => get_term_meta($term->term_id, 'thumbnail_id', true),
                            'isActive' => isset($appliedFilters['category'])
                                && in_array($term->term_id, $appliedFilters['category'])
                        ];
                    }

                    break;
                }

                case 'tag': {
                    $tags = get_terms([
                        'taxonomy' => 'product_tag',
                        'include' => isset($filter['include']) && !empty($filter['include']) ? $filter['include'] : '',
                        'exclude' => isset($filter['exclude']) && !empty($filter['exclude']) ? $filter['exclude'] : ''
                    ]);

                    $value = isset($appliedFilters['category']) ? $appliedFilters['category'] : null;
                    $label = isset($filter['label']) && $filter['label'] ? $filter['label'] : L10N::r('Tag');

                    foreach ($tags as $tag) {
                        $values[] = [
                            'id' => $tag->term_id,
                            'name' => $tag->name,
                            'isActive' => isset($appliedFilters['tag'])
                                && in_array($tag->term_id, $appliedFilters['tag'])
                        ];
                    }

                    break;
                }

                case 'price': {
                    $values = Utils::getPriceLimits($postId, $stepId);
                    $values['from'] = $values['min'];
                    $values['to'] = $values['max'];
                    $value = isset($appliedFilters['category']) ? $appliedFilters['category'] : null;
                    $label = isset($filter['label']) && $filter['label'] ? $filter['label'] : L10N::r('Price');

                    if (isset($appliedFilters['price'])) {
                        $values['from'] = $appliedFilters['price']['from'];
                        $values['to'] = $appliedFilters['price']['to'];
                    }

                    break;
                }

                case 'search': {
                    $value = isset($appliedFilters['search']) ? $appliedFilters['search'] : '';
                    $label = isset($filter['label']) && $filter['label'] ? $filter['label'] : L10N::r('Search');

                    break;
                }

                // attribute
                default: {
                    if (!taxonomy_exists("pa_{$key}")) {
                        break;
                    }

                    $attributes = get_terms([
                        'taxonomy' => "pa_{$key}",
                        'include' => isset($filter['include']) && !empty($filter['include']) ? $filter['include'] : '',
                        'exclude' => isset($filter['exclude']) && !empty($filter['exclude']) ? $filter['exclude'] : ''
                    ]);

                    $label = isset($filter['label']) && $filter['label'] ? $filter['label'] : L10N::r('Attribute');

                    foreach ($attributeTaxonomies as $attributeTaxonomy) {
                        if ($attributeTaxonomy->attribute_name != $key) {
                            continue;
                        }

                        $label = isset($filter['label']) && $filter['label']
                            ? $filter['label']
                            : $attributeTaxonomy->attribute_label;
                    }

                    switch ($filter['view']) {
                        case 'range': {
                            $attributeValues = [];

                            foreach ($attributes as $attribute) {
                                $attributeValues[] = (float) $attribute->name;
                            }

                            $values['min'] = min($attributeValues);
                            $values['max'] = max($attributeValues);
                            $values['from'] = $values['min'];
                            $values['to'] = $values['max'];

                            if (isset($appliedFilters[$key])) {
                                $values['from'] = $appliedFilters[$key]['from'];
                                $values['to'] = $appliedFilters[$key]['to'];
                            }

                            break;
                        }

                        default: {
                            foreach ($attributes as $attribute) {
                                $values[] = [
                                    'id' => $attribute->term_id,
                                    'name' => $attribute->name,
                                    'thumbnailId' => get_term_meta($attribute->term_id, '_wcpw_thumbnail_id', true),
                                    'isActive' => isset($appliedFilters[$key])
                                        && in_array($attribute->term_id, $appliedFilters[$key])
                                ];
                            }

                            break;
                        }
                    }

                    break;
                }
            }

            // set default value for radio view
            if (in_array($filter['view'], ['radio', 'inline-radio'])) {
                $hasActive = false;

                foreach ($values as $value) {
                    if (!$value['isActive']) {
                        continue;
                    }

                    $hasActive = true;
                    break;
                }

                if (!$hasActive) {
                    $values[0]['isActive'] = true;
                }
            }

            $output[] = [
                'label' => $label,
                'key' => $key,
                'view' => $filter['view'],
                'value' => $value,
                'values' => $values
            ];
        }

        return apply_filters('wcProductsWizardFilterFields', $output, $postId, $stepId, $appliedFilters);
    }
    // </editor-fold>
}

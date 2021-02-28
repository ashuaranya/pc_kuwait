<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Admin Class
 *
 * @class Admin
 * @version 8.6.1
 */
class Admin
{
    // <editor-fold desc="Properties">
    /**
     * Global settings model variable
     * @var array
     */
    public $globalSettingsModel = [];

    /**
     * Wizard post settings model variable
     * @var array
     */
    public $postSettingsModel = [];

    /**
     * Wizard steps settings model variable
     * @var array
     */
    public $stepSettingsModel = [];

    /**
     * WC products settings model variable
     * @var array
     */
    public $productSettingsModel = [];

    /**
     * WC product variations settings model variable
     * @var array
     */
    public $productVariationSettingsModel = [];

    /**
     * WC products categories settings model variable
     * @var array
     */
    public $productCategorySettingsModel = [];

    /**
     * WC products attributes settings model variable
     * @var array
     */
    public $productAttributeSettingsModel = [];
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $settingsModel = Settings::getModel();

        $this->globalSettingsModel = $settingsModel['global'];
        $this->postSettingsModel = $settingsModel['post'];
        $this->stepSettingsModel = $settingsModel['step'];
        $this->productSettingsModel = $settingsModel['product'];
        $this->productVariationSettingsModel = $settingsModel['productVariation'];
        $this->productCategorySettingsModel = $settingsModel['productCategory'];
        $this->productAttributeSettingsModel = $settingsModel['productAttribute'];

        // scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin'], 9);
        add_action('admin_enqueue_scripts', [$this, 'enqueueInlineAssets'], 10);

        // main actions
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('admin_footer', [$this, 'footerAction'], 9);
        add_action('admin_init', [$this, 'initAction']);
        add_filter('admin_notices', [$this, 'noticesAction'], 10, 2);

        // plugin links
        add_filter('plugin_action_links', [$this, 'actionLinksFilter'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'metaLinksFilter'], 10, 2);
        
        // wizard
        add_action('init', [$this, 'registerWizardPostType']);
        add_filter('manage_wc_product_wizard_posts_columns', [$this, 'wizardColumnsFilter']);
        add_action('manage_wc_product_wizard_posts_custom_column', [$this, 'wizardCustomColumnFilter'], 10, 2);
        add_action('save_post_wc_product_wizard', [$this, 'saveWizardAction']);
        
        // product
        add_action('save_post_product', [$this, 'saveProductAction']);
        add_filter('woocommerce_product_bulk_edit_save', [$this, 'productBulkEditAction'], 10, 3);
        
        // product attribute
        add_action('woocommerce_after_register_post_type', [$this, 'productAttributesActions'], 1);

        // product category
        add_action('product_cat_edit_form_fields', [$this, 'productCategoryMetaBoxCallback'], 10, 2);
        add_action('edited_product_cat', [$this, 'saveProductCategoryFields'], 10, 2);

        // product variation fields
        add_action('woocommerce_product_after_variable_attributes', [$this, 'productVariationFields'], 10, 3);
        add_action('woocommerce_process_product_meta_variable', [$this, 'productVariationFieldsSave'], 10, 1);
        add_action('woocommerce_save_product_variation', [$this, 'productVariationFieldsSave'], 10, 1);

        // settings page
        add_filter('woocommerce_get_settings_pages', [$this, 'addSettingsTab']);

        // ajax
        add_action('wp_ajax_wcpwGetStepSettingsForm', [$this, 'getStepSettingsAjax']);
        add_action('wp_ajax_wcpwSaveStepSettings', [$this, 'saveStepSettingsAjax']);
        add_action('wp_ajax_wcpwCloneStepSettings', [$this, 'cloneStepAjax']);
        add_action('wp_ajax_wcpwSetDefaultCartContentAjax', [$this, 'setDefaultCartContentAjax']);
        add_action('wp_ajax_woocommerce_json_search_terms', [$this, 'jsonSearchTerms']);
        add_action('wp_ajax_wcpwGetThumbnailGeneratorAreaView', [$this, 'thumbnailGeneratorAreaViewAjax']);
    }

    /** Add ajax url property to the window object and bulk action template */
    public static function enqueueInlineAssets()
    {
        $model = Settings::getModel('product');
        ?>
        <template id="wcpw-bulk-edit-fields-template" class="clear">
            <div id="wcpw-bulk-edit-fields">
                <h4 class="clear"><?php L10N::e('Products Wizard'); ?></h4>
                <label>
                    <span class="title"><?php L10N::e('Overwrite availability rules'); ?></span>
                    <span class="input-text-wrap"><?php
                        self::settingFieldView([
                            'label' => L10N::r('Overwrite availability rules'),
                            'type' => 'checkbox',
                            'key' => '_wcpw_overwrite_availability_rules',
                            'default' => false
                        ]);
                        ?></span>
                </label>
                <?php self::settingFieldView($model['availability_rules']); ?>
            </div>
        </template>
        <?php
    }

    /** Styles and scripts enqueue in admin */
    public function enqueueAdmin()
    {
        $path = WC_PRODUCTS_WIZARD_DEBUG ? 'src' : 'assets';
        $suffix = WC_PRODUCTS_WIZARD_DEBUG ? '' : '.min';
        $stylesFolder = WC_PRODUCTS_WIZARD_DEBUG ? 'scss' : 'css';

        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-resizable');

        if (defined('WC_VERSION')
            && ((isset($_GET['post']) && get_post_type((int) $_GET['post']) == 'wc_product_wizard')
            || (isset($_GET['post_type']) && $_GET['post_type'] == 'wc_product_wizard'))
        ) {
            wp_register_script(
                'select2',
                \WC()->plugin_url() . "/assets/js/select2/select2.full$suffix.js",
                ['jquery'],
                '4.0.3'
            );

            wp_register_script(
                'selectWoo',
                WC()->plugin_url() . "/assets/js/selectWoo/selectWoo.full$suffix.js",
                ['jquery'],
                '1.0.0'
            );

            wp_register_script(
                'wc-enhanced-select',
                WC()->plugin_url() . "/assets/js/admin/wc-enhanced-select$suffix.js",
                ['jquery', 'selectWoo'],
                WC_VERSION
            );

            wp_enqueue_script('select2');
            wp_enqueue_script('selectWoo');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('woocommerce_admin_styles', \WC()->plugin_url() . '/assets/css/admin.css', [], WC_VERSION);
        }

        wp_enqueue_script(
            'wcpw-shared-editor-modal',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/shared-editor-modal$suffix.js",
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-data-table',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/data-table$suffix.js",
            [
                'jquery',
                'jquery-ui-sortable'
            ],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-steps',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/steps$suffix.js",
            [
                'jquery',
                'jquery-ui-sortable'
            ],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-multi-select',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/multi-select$suffix.js",
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-ajax-select',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/ajax-select$suffix.js",
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-thumbnail',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/thumbnail$suffix.js",
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-thumbnail-generator',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/thumbnail-generator$suffix.js",
            ['jquery', 'jquery-ui-draggable', 'jquery-ui-resizable'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-hooks',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/js/hooks$suffix.js",
            'jquery',
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_style(
            'wcpw-app',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/admin/$stylesFolder/app$suffix.css",
            [],
            WC_PRODUCTS_WIZARD_VERSION
        );
    }

    /** Add wizard meta-boxes */
    public function addMetaBoxes()
    {
        add_meta_box(
            'products-wizard',
            L10N::r('Products Wizard'),
            [$this, 'productMetaBoxCallback'],
            'product',
            'normal'
        );

        add_meta_box(
            'options',
            L10N::r('Options'),
            [$this, 'wizardMetaBoxCallback'],
            'wc_product_wizard',
            'normal'
        );
    }

    /** WP footer hook */
    public function footerAction()
    {
        if ((isset($_GET['post']) && get_post_type((int) $_GET['post']) == 'wc_product_wizard')
            || (isset($_GET['post_type']) && $_GET['post_type'] == 'wc_product_wizard')
        ) {
            ?>
            <div class="wcpw-modal" data-component="wcpw-step-modal">
                <div class="wcpw-modal-dialog">
                    <a href="#close"
                        title="<?php L10N::e('Close'); ?>"
                        data-component="wcpw-step-modal-close"
                        class="wcpw-modal-close">&times;</a>
                    <div class="wcpw-modal-dialog-body" data-component="wcpw-step-modal-body"></div>
                </div>
            </div>
            <?php
        }
    }

    /** Admin notices hook */
    public function noticesAction()
    {
        require_once('Legacy.php');

        $version = get_option('woocommerce_products_wizard_version', null);

        if (is_null($version)) {
            $version = WC_PRODUCTS_WIZARD_VERSION;
            update_option('woocommerce_products_wizard_version', WC_PRODUCTS_WIZARD_VERSION);
        }

        if (version_compare($version, Legacy::$lastChangedVersion, '<')) {
            $updateURL = wp_nonce_url(
                add_query_arg('do_update_woocommerce_products_wizard', 'true'),
                'wcpw_db_update',
                'wcpw_db_update_nonce'
            );
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong><?php L10N::e('WooCommerce Products Wizard database update required'); ?></strong></p>
                <p><?php
                    L10N::e('WooCommerce Products Wizard has been updated! To keep things running smoothly, we have to update your database to the newest version.');
                    ?></p>
                <p><a href="<?php echo esc_url($updateURL); ?>" class="wcpw-update-now button-primary"><?php
                        L10N::e('Update WooCommerce Products Wizard Database');
                        ?></a></p>
            </div>
            <?php
        }
    }

    /** Admin init hook */
    public function initAction()
    {
        if (!empty($_GET['do_update_woocommerce_products_wizard'])) { // WPCS: input var ok.
            check_admin_referer('wcpw_db_update', 'wcpw_db_update_nonce');

            require_once('Legacy.php');

            // init Legacy class
            new Legacy();

            update_option('woocommerce_products_wizard_version', WC_PRODUCTS_WIZARD_VERSION);

            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>'
                    . L10N::r('WooCommerce Products Wizard database update complete. Thank you for updating to the latest version!')
                    . '</p></div>';
            });
        }
    }

    /**
     * Plugins list meta link filter
     *
     * @param array $links
     * @param string $plugin
     *
     * @return array
     */
    public function metaLinksFilter($links, $plugin)
    {
        if (false === strpos($plugin, 'woocommerce-products-wizard.php')) {
            return $links;
        }

        $extraLinks = [
            'docs' => '<a href="https://products-wizard.troll-winner.ru/wp-content/plugins/'
                . 'woocommerce-products-wizard/documentation/index.html" target="_blank" rel="nofollow noopener">'
                . L10N::r('Docs')
                . '</a>'
        ];

        $links = array_merge($links, $extraLinks);

        return $links;
    }

    /**
     * Plugins list action links filter
     *
     * @param array $links
     * @param string $plugin
     *
     * @return array
     */
    public function actionLinksFilter($links, $plugin)
    {
        if (false === strpos($plugin, 'woocommerce-products-wizard.php')) {
            return $links;
        }

        $extraLinks = [
            'settings' => '<a href="admin.php?page=wc-settings&tab=products_wizard">'
                . L10N::r('Settings') . '</a>'
        ];

        $links = array_merge($extraLinks, $links);

        return $links;
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    /**
     * Add wizard page to the woocommerce options page
     *
     * @param array $includes
     *
     * @return array
     */
    public function addSettingsTab($includes)
    {
        if (!class_exists('\\WCProductsWizard\\Settings_Page')) {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Settings_Page.php');
        }

        $includes[] = new Settings_Page($this->globalSettingsModel);

        return $includes;
    }

    /**
     * Generate html-field from passed args
     *
     * @param array $modelItem
     * @param array $args
     */
    public static function settingFieldView($modelItem, $args = [])
    {
        $defaultArgs = [
            'values' => [],
            'namePattern' => '%key%',
            'idPattern' => '%key%',
            'generateId' => true,
            'asTemplate' => false
        ];

        $args = array_replace($defaultArgs, $args);

        // create name from pattern
        $args['name'] = str_replace('%key%', $modelItem['key'], $args['namePattern']);

        // define value
        $args['value'] = isset($args['values'][$modelItem['key']])
            ? $args['values'][$modelItem['key']]
            : (isset($modelItem['default']) ? $modelItem['default'] : '');

        // define id attribute
        $args['id'] = $args['generateId'] ? str_replace('%key%', $modelItem['key'], $args['idPattern']) : false;

        $viewPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . 'setting-field-views' . DIRECTORY_SEPARATOR . $modelItem['type'] . '.php';

        echo isset($modelItem['before']) ? $modelItem['before'] : '';

        if (file_exists($viewPath)) {
            include($viewPath);
        }

        echo isset($modelItem['after']) ? $modelItem['after'] : '';

        if (isset($modelItem['description'])) {
            echo '<p class="description">';
            echo wp_kses_post($modelItem['description']);
            echo '</p>';
        }
    }

    /** Search for terms and echo json */
    public function jsonSearchTerms()
    {
        ob_start();
        check_ajax_referer('search-products', 'security');

        $args = [
            'taxonomy' => 'product_cat',
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
            'include' => !empty($_GET['include']) ? wp_parse_id_list($_GET['include']) : '',
            'exclude' => !empty($_GET['exclude']) ? wp_parse_id_list($_GET['exclude']) : '',
            'number' => !empty($_GET['limit']) ? intval($_GET['limit']) : '',
            'name__like' => $_GET['term']
        ];

        $terms = get_terms($args);
        $foundTerms = [];

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $foundTerms[$term->term_id] = rawurldecode($term->name);
            }
        }

        $foundTerms = apply_filters('woocommerce_json_search_found_terms', $foundTerms);

        wp_send_json($foundTerms);
    }
    // </editor-fold>

    // <editor-fold desc="Wizard">
    /** Register wizard post-type action */
    public function registerWizardPostType()
    {
        $name = L10N::r('Products Wizard');
        $args = [
            'label' => $name,
            'labels' => [
                'name' => $name,
                'singular_name' => $name,
                'menu_name' => $name
            ],
            'description' => L10N::r('This is where you can add new products wizard items.'),
            'public' => false,
            'show_ui' => true,
            'map_meta_cap' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_in_menu' => current_user_can('manage_woocommerce') ? 'woocommerce' : true,
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => false,
            'supports' => [
                'title',
                'editor',
                'thumbnail'
            ],
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true
        ];

        register_post_type('wc_product_wizard', apply_filters('wcProductsWizardPostTypeArgs', $args));
    }

    /**
     * Save wizard meta values
     *
     * @param integer $postId
     */
    public function saveWizardAction($postId)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can('edit_page', $postId)
            || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save')
        ) {
            return;
        }

        foreach ($this->postSettingsModel as $setting) {
            if (!isset($_POST[$setting['key']])) {
                continue;
            }

            update_post_meta($postId, $setting['key'], $_POST[$setting['key']]);
        }

        // update steps meta
        $stepsIds = isset($_POST['_steps_ids']) ? $_POST['_steps_ids'] : [];
        $stepsSettings = get_post_meta($postId, '_steps_settings', true);
        $stepsSettings = $stepsSettings ? $stepsSettings : [];

        update_post_meta($postId, '_steps_ids', $stepsIds);

        foreach ($stepsSettings as $stepId => $stepSettings) {
            if (!in_array($stepId, $stepsIds)) {
                unset($stepsSettings[$stepId]);
            }
        }

        update_post_meta($postId, '_steps_settings', $stepsSettings);
    }

    /**
     * Products wizard post type metabox content
     *
     * @param object $post
     */
    public function wizardMetaBoxCallback($post)
    {
        $stepsIds = Settings::getStepsIds($post->ID);
        $stepsSettings = Settings::getStepsSettings($post->ID);
        $defaultSettingsUrl = [
            'action' => 'wcpwGetStepSettingsForm',
            'post_id' => $post->ID,
            'step_id' => '%STEP_ID%'
        ];

        $postSettingsModelGroups = [];

        foreach ($this->postSettingsModel as $key => $setting) {
            $postSettingsModelGroups[$setting['group']][$key] = $setting;
        }
        ?>
        <div data-component="wcpw-settings-groups">
            <fieldset class="wcpw-settings-group">
                <a class="button button-large wcpw-settings-group-toggle"
                    href="#" role="button"
                    data-component="wcpw-settings-group-toggle"
                    data-id="basic"><?php L10N::e('Basic'); ?></a>
                <table class="form-table wcpw-settings-table wcpw-settings-group-content is-visible"
                    data-component="wcpw-settings-group-content"
                    data-id="basic">
                    <tr valign="top">
                        <th scope="row">
                            <label for="woocommerce-products-wizard-shortcode"><?php
                                L10N::e('Using');
                                ?></label>
                        </th>
                        <td>
                            <input type="text" id="woocommerce-products-wizard-shortcode" readonly
                                value="<?php echo esc_attr('[woocommerce-products-wizard id="' . $post->ID . '"]'); ?>">
                        </td>
                    </tr>
                    <tr valign="top"
                        data-component="wcpw-steps"
                        data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
                        <th scope="row">
                            <button class="button" data-component="wcpw-steps-add"><?php
                                L10N::e('Add step');
                                ?></button>
                        </th>
                        <td>
                            <table class="wcpw-settings-table wcpw-data-table wcpw-steps-list wp-list-table
                                widefat striped">
                                <tbody data-component="wcpw-steps-list">
                                    <?php
                                    foreach ($stepsIds as $stepId) {
                                        $settingsUrl = [
                                            'action' => 'wcpwGetStepSettingsForm',
                                            'post_id' => $post->ID,
                                            'step_id' => $stepId
                                        ];
                                        ?>
                                        <tr class="wcpw-steps-list-row wcpw-data-table-item"
                                            data-component="wcpw-steps-list-item"
                                            data-id="<?php echo esc_attr($stepId); ?>">
                                            <td>
                                                <span data-component="wcpw-steps-list-item-name"><?php
                                                    echo wp_kses_post("#{$stepId} ");

                                                    if (isset($stepsSettings[$stepId], $stepsSettings[$stepId]['title'])
                                                        && !empty($stepsSettings[$stepId]['title'])
                                                    ) {
                                                        echo wp_kses_post($stepsSettings[$stepId]['title']);
                                                    }
                                                    ?></span>
                                                <input type="hidden"
                                                    data-component="wcpw-steps-list-item-id"
                                                    name="_steps_ids[<?php echo esc_attr($stepId); ?>]"
                                                    value="<?php echo esc_attr($stepId); ?>">
                                            </td>
                                            <td class="wcpw-data-table-item-controls">
                                                <button class="button wcpw-steps-list-item-settings"
                                                    data-component="wcpw-steps-list-item-clone"
                                                    data-settings="<?php
                                                    echo esc_attr(wp_json_encode($settingsUrl));
                                                    ?>"><?php L10N::e('Clone'); ?></button>
                                            </td>
                                            <td class="wcpw-data-table-item-controls">
                                                <button class="button wcpw-steps-list-item-settings"
                                                    data-component="wcpw-steps-list-item-settings"
                                                    data-settings="<?php
                                                    echo esc_attr(wp_json_encode($settingsUrl));
                                                    ?>"><?php L10N::e('Settings'); ?></button>
                                            </td>
                                            <td class="wcpw-data-table-item-controls">
                                                <button class="button"
                                                    data-component="wcpw-steps-list-item-remove">&times;</button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                                <tfoot class="wcpw-steps-list-footer">
                                    <tr class="wcpw-steps-list-row wcpw-data-table-item"
                                        data-component="wcpw-steps-list-item-template">
                                        <td>
                                            <span data-component="wcpw-steps-list-item-name"></span>
                                            <input type="hidden"
                                                data-component="wcpw-steps-list-item-id">
                                        </td>
                                        <td class="wcpw-data-table-item-controls">
                                            <button class="button wcpw-steps-list-item-settings"
                                                data-component="wcpw-steps-list-item-clone"
                                                data-settings="<?php
                                                echo esc_attr(wp_json_encode($defaultSettingsUrl));
                                                ?>"><?php L10N::e('Clone'); ?></button>
                                        </td>
                                        <td class="wcpw-data-table-item-controls">
                                            <button class="button wcpw-steps-list-item-settings"
                                                data-component="wcpw-steps-list-item-settings"
                                                data-settings="<?php
                                                echo esc_attr(wp_json_encode($defaultSettingsUrl));
                                                ?>"><?php L10N::e('Settings'); ?></button>
                                        </td>
                                        <td class="wcpw-data-table-item-controls">
                                            <button class="button"
                                                data-component="wcpw-steps-list-item-remove">&times;</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="wcpw-set-default-cart-content"><?php
                                L10N::e('Set default cart content from your session');
                                ?></label>
                        </th>
                        <td>
                            <button class="button"
                                id="wcpw-set-default-cart-content"
                                data-component="wcpw-set-default-cart-content"
                                data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>"
                                data-id="<?php echo esc_attr(get_the_ID()); ?>"><?php
                                L10N::e('Set default cart');
                                ?></button>
                            <button class="button"
                                id="wcpw-reset-default-cart-content"
                                data-component="wcpw-set-default-cart-content"
                                data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>"
                                data-id="<?php echo esc_attr(get_the_ID()); ?>"
                                data-value="0"><?php
                                L10N::e('Reset default cart');
                                ?></button>
                            <p class="description"><?php
                                // phpcs:disable
                                L10N::e('Use visual part of the wizard for selecting products, but do not add them to the main cart');
                                // phpcs:enable
                                ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <?php foreach ($postSettingsModelGroups as $group => $settingsModel) { ?>
                <fieldset class="wcpw-settings-group">
                    <a class="button button-large wcpw-settings-group-toggle"
                        href="#" role="button"
                        data-component="wcpw-settings-group-toggle"
                        data-id="<?php echo esc_attr($group); ?>"><?php echo wp_kses_post($group); ?></a>
                    <table class="form-table wcpw-settings-table wcpw-settings-group-content"
                        data-component="wcpw-settings-group-content"
                        data-id="<?php echo esc_attr($group); ?>">
                        <?php
                        foreach ($settingsModel as $key => $setting) {
                            if ($key == 'thumbnail_areas') {
                                $thumbnailAreas = Settings::getPost($post->ID, 'thumbnail_areas');
                                $thumbnailCanvasWidth = Settings::getPost($post->ID, 'thumbnail_canvas_width');
                                $thumbnailCanvasHeight = Settings::getPost($post->ID, 'thumbnail_canvas_height');
                                $thumbnailCanvasStyle = "width:${thumbnailCanvasWidth}px;height:${thumbnailCanvasHeight}px;";
                                ?>
                                <tr valign="top">
                                    <td>
                                        <span class="button button-primary wcpw-thumbnail-generator-area-add"
                                            role="button" data-component="wcpw-thumbnail-generator-area-add"><?php
                                            L10N::e('Add item');
                                            ?></span>
                                    </td>
                                    <td align="right">
                                        <label for="wcpw-thumbnail-generator-clear">
                                            <?php L10N::e('Areas added:'); ?>
                                            <span data-component="wcpw-thumbnail-generator-areas-count"><?php
                                                echo count(array_filter($thumbnailAreas));
                                                ?></span>
                                            <span class="screen-reader-text"><?php L10N::e('Clear'); ?></span>
                                        </label>
                                        <button class="button wcpw-thumbnail-generator-clear"
                                            id="wcpw-thumbnail-generator-clear"
                                            data-component="wcpw-thumbnail-generator-clear"><?php
                                            L10N::e('Clear');
                                            ?></button>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <td colspan="2">
                                        <div class="wcpw-thumbnail-generator-canvas-wrapper">
                                            <div class="wcpw-thumbnail-generator-canvas"
                                                data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>"
                                                data-component="wcpw-thumbnail-generator-canvas"
                                                style="<?php echo esc_attr($thumbnailCanvasStyle); ?>"><?php
                                                foreach (array_filter($thumbnailAreas) as $area) {
                                                    if (!is_array($area)) {
                                                        continue;
                                                    }

                                                    $this->thumbnailGeneratorAreaView($area);
                                                }
                                                ?></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                continue;
                            }
                            ?>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="<?php echo esc_attr($setting['key']); ?>"><?php
                                        echo wp_kses_post($setting['label']);
                                        ?></label>
                                </th>
                                <td><?php
                                    self::settingFieldView(
                                        $setting,
                                        ['values' => [$setting['key'] => Settings::getPost($post->ID, $key)]]
                                    );
                                    ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </fieldset>
            <?php } ?>
        </div>
        <?php
        include(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR
            . 'shared-editor-modal.php'
        );
    }

    /**
     * Output item view with admin controls
     *
     * @param array $args
     */
    public function thumbnailGeneratorAreaView($args)
    {
        $defaults = [
            'index' => 0,
            'name' => '',
            'x' => 0,
            'y' => 0,
            'width' => 250,
            'height' => 250,
            'image' => ''
        ];

        $args = array_replace($defaults, $args);

        $styles = [];
        $settingsModel = [
            'name' => [
                'label' => L10N::r('Name'),
                'key' => 'name',
                'type' => 'text',
                'data-component' => 'wcpw-thumbnail-generator-area-name',
                'default' => ''
            ],
            'x' => [
                'label' => L10N::r('X placement'),
                'key' => 'x',
                'type' => 'number',
                'data-component' => 'wcpw-thumbnail-generator-area-x',
                'default' => ''
            ],
            'y' => [
                'label' => L10N::r('Y placement'),
                'key' => 'y',
                'type' => 'number',
                'data-component' => 'wcpw-thumbnail-generator-area-y',
                'default' => ''
            ],
            'width' => [
                'label' => L10N::r('Width'),
                'key' => 'width',
                'type' => 'number',
                'data-component' => 'wcpw-thumbnail-generator-area-width',
                'default' => ''
            ],
            'height' => [
                'label' => L10N::r('Height'),
                'key' => 'height',
                'type' => 'number',
                'data-component' => 'wcpw-thumbnail-generator-area-height',
                'default' => ''
            ],
            'image' => [
                'label' => L10N::r('Image'),
                'key' => 'image',
                'type' => 'thumbnail',
                'data-component' => 'wcpw-thumbnail-generator-area-image wcpw-thumbnail',
                'default' => '',
                'description' => L10N::r('Default image of area')
            ]
        ];
        
        $settingFieldViewArgs = [
            'values' => $args,
            'namePattern' => '_thumbnail_areas[' . esc_attr($args['index']). '][%key%]',
            'idPattern' => 'thumbnail-item-' . $args['index'] . '-%key%'
        ];

        $innerStyles = [
            'top' => "$args[y]px",
            'left' => "$args[x]px",
            'width' => "$args[width]px",
            'height' => "$args[height]px"
        ];

        $randomKey = substr(md5(rand()), 0, 20);
        $settingsModalLink = "wcpw-thumbnail-generator-area-settings-modal-$randomKey";
        ?>
        <div class="wcpw-thumbnail-generator-area" data-component="wcpw-thumbnail-generator-area"
            style="<?php echo Utils::stylesArrayToString($styles); ?>">
            <div class="wcpw-thumbnail-generator-area-inner"
                data-component="wcpw-thumbnail-generator-area-inner"
                style="<?php echo Utils::stylesArrayToString($innerStyles); ?>">
                <a href="<?php echo esc_attr('#' . $settingsModalLink); ?>"
                    data-component="wcpw-thumbnail-generator-area-settings-modal-open"
                    role="button"
                    title="<?php esc_attr(L10N::r('Settings')); ?>"
                    class="wcpw-thumbnail-generator-area-settings-modal-opener">
                    <span class="dashicons dashicons-admin-generic">&nbsp;</span><span class="screen-reader-text"><?php
                        L10n::e('Settings');
                        ?></span>
                </a>
                <?php
                if ($args['image']) {
                    echo wp_get_attachment_image($args['image'], 'large');
                }
                ?>
            </div>
            <div id="<?php echo esc_attr($settingsModalLink); ?>"
                class="wcpw-modal" data-component="wcpw-thumbnail-generator-area-settings-modal">
                <div class="wcpw-modal-dialog">
                    <a href="#close" title="<?php esc_attr(L10N::r('Close')); ?>"
                        class="wcpw-modal-close">&times;</a>
                    <table class="form-table wcpw-settings-table">
                        <tbody>
                            <tr>
                                <th>
                                    <label for="<?php
                                        echo "canvas-item-$args[index]-index";
                                        ?>"><?php L10n::e('Order (from 0)'); ?></label>
                                </th>
                                <td align="left">
                                    <button class="button"
                                        data-component="wcpw-thumbnail-generator-area-move"
                                        data-direction="up">&#9660;</button>
                                    <input type="number"
                                        class="wcpw-thumbnail-generator-area-index"
                                        id="<?php echo "canvas-item-$args[index]-index"; ?>"
                                        name="_thumbnail_areas[<?php echo esc_attr($args['index']); ?>][index]"
                                        value="<?php echo esc_attr($args['index']); ?>"
                                        min="<?php echo esc_attr($args['index']); ?>"
                                        readonly
                                        data-component="wcpw-thumbnail-generator-area-index">
                                    <button class="button"
                                        data-component="wcpw-thumbnail-generator-area-move"
                                        data-direction="down">&#9650;</button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody>
                            <?php foreach ($settingsModel as $modelItem) { ?>
                                <tr>
                                    <th>
                                        <label for="<?php
                                        echo esc_attr("canvas-item-$args[index]-$modelItem[key]");
                                        ?>"><?php L10n::e($modelItem['label']); ?></label>
                                    </th>
                                    <td align="left"><?php
                                        self::settingFieldView($modelItem, $settingFieldViewArgs);
                                        ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tbody>
                            <tr>
                                <th><?php L10n::e('Clone'); ?></th>
                                <td align="left">
                                    <a data-component="wcpw-thumbnail-generator-area-clone"
                                        role="button" class="button">
                                        <span class="dashicons dashicons-admin-page">&nbsp;</span>
                                        <?php L10n::e('Clone'); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><?php L10n::e('Remove'); ?></th>
                                <td align="left">
                                    <a data-component="wcpw-thumbnail-generator-area-remove"
                                        role="button" class="button">
                                        <span class="dashicons dashicons-no">&nbsp;</span>
                                        <?php L10n::e('Remove'); ?>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="wcpw-modal-dialog-footer">
                        <a href="#close" class="button button-primary"><?php L10n::e('Save'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /** Output thumbnail item view with admin controls using ajax */
    public function thumbnailGeneratorAreaViewAjax()
    {
        ob_start();

        $this->thumbnailGeneratorAreaView($_POST);

        wp_send_json(['html' => ob_get_clean()]);
    }

    /**
     * Wizards list columns filter
     *
     * @param array $columns
     *
     * @return array $columns
     */
    public function wizardColumnsFilter($columns)
    {
        $columns['shortcode'] = L10N::r('ShortCode');

        return $columns;
    }

    /**
     * Wizards list line cell
     *
     * @param array $columns
     * @param integer $postId
     */
    public function wizardCustomColumnFilter($columns, $postId)
    {
        if ($columns == 'shortcode') {
            echo '[woocommerce-products-wizard id="' . (int) $postId . '"]';
        }
    }

    /** Set wizard default cart content by ajax */
    public function setDefaultCartContentAjax()
    {
        try {
            $cartContent = $this->setDefaultCartContent($_POST);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        Utils::sendJSON([
            'message' => sprintf(
                L10N::r('Stored products in the cart: %d'),
                count($cartContent)
            )
        ]);

        exit();
    }

    /**
     * Set wizard default cart content
     *
     * @param array $args
     *
     * @throws \Exception if empty post id
     *
     * @return array
     */
    public function setDefaultCartContent($args)
    {
        if (!$args['id']) {
            throw new \Exception('Empty post id');
        }

        $value = Cart::get($args['id'], ['checkDefaultContent' => false]);

        if (isset($args['value'])) {
            $value = $args['value'] == 0 ? [] : (array) $args['value'];
        }

        update_post_meta($args['id'], '_default_cart_content', $value);

        return $value;
    }
    // </editor-fold>

    // <editor-fold desc="Product">
    /**
     * Product page wizard metabox content
     *
     * @param object $post
     */
    public function productMetaBoxCallback($post)
    {
        ?>
        <table class="form-table wcpw-settings-table">
            <?php foreach ($this->productSettingsModel as $key => $setting) { ?>
                <tr class="wcpw-settings-table-row" valign="top">
                    <th scope="row" class="wcpw-settings-table-row-name titledesc">
                        <label for="<?php echo esc_attr($setting['key']); ?>"><?php
                            echo wp_kses_post($setting['label']);
                            ?></label>
                    </th>
                    <td class="wcpw-settings-table-row-value"><?php
                        self::settingFieldView(
                            $setting,
                            ['values' => [$setting['key'] => Settings::getProduct($post->ID, $key)]]
                        );
                        ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
    }
    
    /**
     * Save product meta values
     *
     * @param integer $postId
     */
    public function saveProductAction($postId)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can('edit_page', $postId)
            || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save')
        ) {
            return;
        }

        foreach ($this->productSettingsModel as $setting) {
            if (!isset($_POST[$setting['key']])) {
                continue;
            }

            update_post_meta($postId, $setting['key'], $_POST[$setting['key']]);
        }
    }

    /**
     * Product bulk edit action
     *
     * @param \WC_Product $product
     */
    public function productBulkEditAction($product)
    {
        $postId = $product->get_id();

        if (isset($_REQUEST['_wcpw_overwrite_availability_rules']) && $_REQUEST['_wcpw_overwrite_availability_rules']) {
            $metaKey = '_wcpw_availability_rules';

            update_post_meta($postId, $metaKey, $_REQUEST[$metaKey]);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Product categories">
    /**
     * Add metabox to product category edit page
     *
     * @param object $term
     */
    public function productCategoryMetaBoxCallback($term)
    {
        foreach ($this->productCategorySettingsModel as $key => $setting) {
            ?>
            <tr class="wcpw-settings-table-row" valign="top">
                <th scope="row" class="wcpw-settings-table-row-name titledesc">
                    <label for="<?php echo esc_attr($setting['key']); ?>"><?php
                        echo wp_kses_post($setting['label']);
                        ?></label>
                </th>
                <td class="wcpw-settings-table-row-value"><?php
                    self::settingFieldView(
                        $setting,
                        ['values' => [$setting['key'] => Settings::getProductCategory($term->term_id, $key)]]
                    );
                    ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Save product category meta fields
     *
     * @param integer $termId
     */
    public function saveProductCategoryFields($termId)
    {
        if (!$termId) {
            return;
        }

        foreach ($this->productCategorySettingsModel as $setting) {
            if (!isset($_POST[$setting['key']])) {
                continue;
            }

            update_term_meta($termId, $setting['key'], $_POST[$setting['key']]);
        }
    }

    /**
     * Get the tree of categories
     *
     * @param array $args
     * @param integer $level
     *
     * @return array
     */
    public static function getCategoriesTree($args = [], $level = 0)
    {
        $defaults = [
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'hierarchical' => true,
            'parent' => 0
        ];

        $args = array_merge($defaults, $args);
        $output = [];

        foreach (get_terms($args) as $term) {
            $term->name = str_repeat('—', $level) . $term->name;
            $output[$term->term_id] = $term;
            $output = $output + self::getCategoriesTree(['parent' => $term->term_id], $level + 1);
        }

        return $output;
    }
    // </editor-fold>

    // <editor-fold desc="Product attributes">
    /** Add product attribute page actions */
    public function productAttributesActions()
    {
        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributes = \wc_get_attribute_taxonomies();

            foreach ($attributes as $attribute) {
                add_action(
                    "pa_{$attribute->attribute_name}_edit_form_fields",
                    [$this, 'attributeValueFieldsAdd'],
                    10,
                    2
                );

                add_action("edited_pa_{$attribute->attribute_name}", [$this, 'attributeValueFieldsSave'], 10, 2);
            }
        }
    }
    
    /**
     * Add metabox to attribute value edit page
     *
     * @param object $term
     */
    public function attributeValueFieldsAdd($term)
    {
        foreach ($this->productAttributeSettingsModel as $setting) {
            $value = get_term_meta($term->term_id, $setting['key']);
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($setting['key']); ?>"><?php
                        echo wp_kses_post($setting['label']);
                        ?></label>
                </th>
                <td><?php
                    self::settingFieldView(
                        $setting,
                        ['values' => [$setting['key'] => isset($value[0]) ? $value[0] : null]]
                    );
                    ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Save attribute value meta fields
     *
     * @param integer $termId
     */
    public function attributeValueFieldsSave($termId)
    {
        if (!$termId) {
            return;
        }

        foreach ($this->productAttributeSettingsModel as $setting) {
            if (!isset($_POST[$setting['key']])) {
                continue;
            }

            update_term_meta($termId, $setting['key'], $_POST[$setting['key']]);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Product variations">
    /**
     * Add wizard metabox to the product variations edit block
     *
     * @param integer $loop
     * @param array   $variationData
     * @param object  $variation
     */
    public function productVariationFields($loop, $variationData, $variation)
    {
        echo '<div class="wcpw-settings-table">';

        foreach ($this->productVariationSettingsModel as $key => $setting) {
            ?>
            <div class="wcpw-settings-table-row">
                <div class="wcpw-settings-table-row-name">
                    <label for="<?php echo esc_attr($setting['key']); ?>"><b><?php
                            echo wp_kses_post($setting['label']);
                            ?></b></label>
                </div>
                <div class="wcpw-settings-table-row-value"><?php
                    self::settingFieldView(
                        $setting,
                        [
                            'namePattern' => "%key%[$loop]",
                            'idPattern' => "%key%-$loop",
                            'values' => [$setting['key'] => Settings::getProductVariation($variation->ID, $key)]
                        ]
                    );
                    ?></div>
            </div>
            <?php
        }

        echo '</div>';
    }

    /** Save the product variations meta values */
    public function productVariationFieldsSave()
    {
        if (!isset($_POST['variable_post_id']) || !$_POST['variable_post_id']) {
            return;
        }

        foreach ($_POST['variable_post_id'] as $index => $variationId) {
            foreach ($this->productVariationSettingsModel as $setting) {
                if (!isset($_POST[$setting['key']][$index])) {
                    continue;
                }

                update_post_meta($variationId, $setting['key'], $_POST[$setting['key']][$index]);
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Steps list">
    /** Get wizard step settings form via ajax */
    public function getStepSettingsAjax()
    {
        try {
            $this->getStepSettingsForm($_GET);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        exit();
    }

    /**
     * Get wizard step settings form
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function getStepSettingsForm($args)
    {
        global $_wp_additional_image_sizes;

        $postId = $args['post_id'];
        $stepId = $args['step_id'];

        if (!$stepId || !$postId) {
            throw new \Exception('Empty step or post id');
        }

        $imageSizes = [];
        $settingsModelGroups = [];
        $meta = (array) Settings::getStepsSettings($postId);
        $categories = self::getCategoriesTree();

        // get all registered image sizes and use
        foreach ($_wp_additional_image_sizes as $imageSizeName => $imageSize) {
            $crop = $imageSize['crop'] ? ' crop' : '';
            $imageSizes[] = $imageSizeName != "$imageSize[width]x$imageSize[height]$crop"
                ? "$imageSizeName ($imageSize[width]x$imageSize[height]$crop)"
                : $imageSizeName;
        }

        $this->stepSettingsModel['filter_thumbnail_size']['description'] .= '<details><summary>'
            . L10N::r('More') . '</summary>'
            . implode(', ', $imageSizes) . '</details>';

        // add categories list for using
        foreach ($categories as $category) {
            $this->stepSettingsModel['categories']['values'][$category->term_id] = $category->name;
        }

        // group settings
        foreach ($this->stepSettingsModel as $key => $setting) {
            $settingsModelGroups[$setting['group']][$key] = $setting;
        }

        do_action('wcProductsWizardStepSettingsForm', $this, $args);
        ?>
        <form class="wcpw-step-settings-form"
            data-component="wcpw-step-settings-form wcpw-settings-groups"
            data-step-id="<?php echo esc_attr($stepId); ?>"
            data-post-id="<?php echo esc_attr($postId); ?>">
            <?php foreach ($settingsModelGroups as $group => $settingsModel) { ?>
                <div class="wcpw-settings-group">
                    <a class="button button-large wcpw-settings-group-toggle"
                        href="#" role="button"
                        data-component="wcpw-settings-group-toggle"
                        data-id="<?php echo esc_attr($group); ?>"><?php echo wp_kses_post($group); ?></a>
                    <table class="form-table wcpw-settings-table wcpw-settings-group-content"
                        data-component="wcpw-settings-group-content"
                        data-id="<?php echo esc_attr($group); ?>">
                        <?php foreach ($settingsModel as $setting) { ?>
                            <tr class="form-field">
                                <th scope="row">
                                    <label for="<?php echo esc_attr($setting['key']); ?>"><?php
                                        echo wp_kses_post($setting['label']);
                                        ?></label>
                                </th>
                                <td><?php
                                    self::settingFieldView(
                                        $setting,
                                        ['values' => isset($meta[$stepId]) ? $meta[$stepId] : []]
                                    );
                                    ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php } ?>
            <footer class="wcpw-step-settings-form-footer">
                <button class="button button-primary button-large" type="submit"><?php
                    L10N::e('Save');
                    ?></button>
            </footer>
        </form>
        <?php
    }

    /** Save wizard step settings-form by ajax */
    public function saveStepSettingsAjax()
    {
        $args = $_POST;
        $values = [];
        parse_str($args['values'], $values);
        $args['values'] = $values;

        try {
            $this->saveStepSettings($args);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        exit();
    }

    /**
     * Save wizard step settings-form
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function saveStepSettings($args)
    {
        $postId = $args['post_id'];
        $stepId = $args['step_id'];

        if (!$stepId || !$postId) {
            throw new \Exception('Empty step or post id');
        }

        $settings = (array) Settings::getStepsSettings($postId);
        $settings[$stepId] = isset($args['values']) ? $args['values'] : [];

        update_post_meta($postId, '_steps_settings', $settings);
    }

    /** Clone wizard step by ajax */
    public function cloneStepAjax()
    {
        try {
            $this->cloneStep($_POST);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        exit();
    }

    /**
     * Clone wizard step
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function cloneStep($args)
    {
        $postId = $args['post_id'];
        $sourceStep = $args['source_step'];
        $targetStep = $args['target_step'];

        if (!$postId || !$sourceStep || !$targetStep) {
            throw new \Exception('Empty step or post id');
        }

        $settings = (array) Settings::getStepsSettings($postId);
        $settings[$targetStep] = isset($settings[$sourceStep]) ? $settings[$sourceStep] : [];

        update_post_meta($postId, '_steps_settings', $settings);
    }
    // </editor-fold>
}

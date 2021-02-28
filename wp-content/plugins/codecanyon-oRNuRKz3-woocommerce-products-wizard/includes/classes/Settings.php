<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Settings Class
 *
 * @class Settings
 * @version 6.1.0
 */
class Settings
{
    /** Class Constructor */
    public function __construct()
    {
        add_action('wp_ajax_wcpwGetFilterViewSelectOptions', [$this, 'getFilterViewSelectOptionsAjax']);
    }

    /**
     * Define settings model
     *
     * @param string|null $source - get all or specific only
     *
     * @return array
     */
    public static function getModel($source = null)
    {
        static $modelCache;

        if ($source && $modelCache && isset($modelCache[$source])) {
            return $modelCache[$source];
        } elseif ($modelCache) {
            return apply_filters('wcProductsWizardSettingsModels', $modelCache);
        }

        $wizardIds = ['' => ''];
        $orderFormValues = ['' => ''];

        if (is_admin()) {
            $wizardPosts = get_posts([
                'post_type' => 'wc_product_wizard',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            foreach ($wizardPosts as $wizardPost) {
                $wizardIds[$wizardPost->ID] = $wizardPost->post_title;
            }

            $contactFormPosts = get_posts([
                'post_type' => 'wpcf7_contact_form',
                'numberposts' => -1
            ]);

            foreach ($contactFormPosts as $contactFormPost) {
                $orderFormValues[$contactFormPost->post_title] = $contactFormPost->post_title;
            }
        }

        $availabilityRules = [
            'label' => L10N::r('Availability rules'),
            'type' => 'data-table',
            'inModal' => true,
            'key' => 'availability_rules',
            'default' => [],
            'description' => L10N::r('Show/hide the step according the specific rules'),
            'values' => [
                'source' => [
                    'label' => L10N::r('Source'),
                    'key' => 'source',
                    'type' => 'select',
                    'default' => 'product',
                    'values' => [
                        'product' => L10n::r('Product/variation'),
                        'category' => L10n::r('Category'),
                        'attribute' => L10n::r('Attribute'),
                        'custom_field' => L10n::r('Custom field')
                    ]
                ],
                'product' => [
                    'label' => L10N::r('Products'),
                    'key' => 'product',
                    'type' => 'wc-product-search',
                    'default' => []
                ],
                'category' => [
                    'label' => L10N::r('Categories'),
                    'key' => 'category',
                    'type' => 'wc-terms-search',
                    'default' => []
                ],
                'attribute' => [
                    'label' => L10N::r('Attribute'),
                    'key' => 'attribute',
                    'type' => 'select',
                    'default' => '',
                    'values' => self::getAttributesList()
                ],
                'attribute_values' => [
                    'label' => L10N::r('Attribute values'),
                    'key' => 'attribute_values',
                    'type' => 'text',
                    'default' => '',
                    'pattern' => '([0-9]+.{0,1}[0-9]*,{0,1})*[0-9]',
                    'description' => L10N::r('Define terms IDs separated by a comma')
                ],
                'custom_field_name' => [
                    'label' => L10N::r('Custom field name'),
                    'key' => 'custom_field_name',
                    'type' => 'text',
                    'default' => ''
                ],
                'custom_field_value' => [
                    'label' => L10N::r('Custom field value'),
                    'key' => 'custom_field_value',
                    'type' => 'text',
                    'default' => ''
                ],
                'condition' => [
                    'label' => L10N::r('Condition'),
                    'key' => 'condition',
                    'type' => 'select',
                    'default' => 'in_cart',
                    'values' => [
                        'in_cart' => L10N::r('In cart'),
                        'not_in_cart' => L10N::r('Not in cart')
                    ]
                ],
                'inner_relation' => [
                    'label' => L10N::r('Relation within the items'),
                    'key' => 'inner_relation',
                    'type' => 'select',
                    'default' => 'and',
                    'values' => [
                        'or' => L10N::r('OR'),
                        'and' => L10N::r('AND')
                    ]
                ],
                'outer_relation' => [
                    'label' => L10N::r('Relation with the next rule'),
                    'key' => 'outer_relation',
                    'type' => 'select',
                    'default' => 'or',
                    'values' => [
                        'or' => L10N::r('OR'),
                        'and' => L10N::r('AND')
                    ]
                ]
            ],
            'group' => L10N::r('Query')
        ];

        $thumbnailAreasValues = array_replace(
            [
                'name' => [
                    'label' => L10N::r('Area name'),
                    'key' => 'name',
                    'type' => 'text',
                    'default' => ''
                ],
                'image' => [
                    'label' => L10N::r('Image'),
                    'key' => 'image',
                    'type' => 'thumbnail',
                    'default' => ''
                ]
            ],
            $availabilityRules['values']
        );

        $thumbnailAreasValues['source']['label'] = L10N::r('Availability rules source');
        $thumbnailAreasValues['product']['description'] = L10N::r('Keep empty to not check this field');
        $thumbnailAreasValues['category']['description'] = L10N::r('Keep empty to not check this field');
        $thumbnailAreasValues['custom_field_name']['description'] = L10N::r('Keep empty to not check this field');
        $thumbnailAreasValues['custom_field_value']['description'] = L10N::r('Keep empty to not check this field');

        unset($thumbnailAreasValues['outer_relation']);

        $global = [
            'styles_including_type' => [
                'name' => L10N::r('Styles including type'),
                'default' => 'full',
                'type' => 'select',
                'desc' => L10N::r('For more info see the')
                    . ' <a href="' . WC_PRODUCTS_WIZARD_PLUGIN_URL
                    . 'documentation/index.html#main-settings" target="_blank">'
                    . L10N::r('documentation') . '</a>',
                'id' => 'woocommerce_products_wizard_styles_including_type',
                'key' => 'woocommerce_products_wizard_styles_including_type',
                'options' => [
                    'full' => L10N::r('Full styles file'),
                    'basic' => L10N::r('Basic styles file'),
                    'none' => L10N::r('None'),
                    'custom' => L10N::r('Custom full styles file')
                ],
                'section' => ''
            ],
            'scripts_including_type' => [
                'name' => L10N::r('Scripts including type'),
                'default' => 'single',
                'type' => 'select',
                'desc' => L10N::r('Include scripts separately or in a single file'),
                'id' => 'woocommerce_products_wizard_scripts_including_type',
                'key' => 'woocommerce_products_wizard_scripts_including_type',
                'options' => [
                    'single' => L10N::r('Single file'),
                    'multiple' => L10N::r('Multiple files')
                ],
                'section' => ''
            ],
            'included_scripts' => [
                'name' => L10N::r('Included scripts'),
                'default' => [
                    'app',
                    'elements-events',
                    'hooks',
                    'variation-form',
                    'bootstrap-util',
                    'bootstrap-modal',
                    'sticky-kit',
                    'wNumb',
                    'nouislider',
                    'nouislider-launcher'
                ],
                'type' => 'multiselect',
                'desc' => L10N::r('Select which files will be included with the multiple including type'),
                'id' => 'woocommerce_products_wizard_included_scripts',
                'key' => 'woocommerce_products_wizard_included_scripts',
                'options' => [
                    'app' => 'App',
                    'elements-events' => 'Elements events',
                    'hooks' => 'Hooks',
                    'variation-form' => 'Variation form',
                    'bootstrap-util' => 'Bootstrap util',
                    'bootstrap-modal' => 'Bootstrap modal',
                    'sticky-kit' => 'Sticky-kit',
                    'wNumb' => 'wNumb',
                    'nouislider' => 'noUiSlider',
                    'nouislider-launcher' => 'noUiSlider-launcher'
                ],
                'section' => ''
            ],
            'store_session_in_db' => [
                'name' => L10N::r('Store session in the DB'),
                'default' => 'no',
                'type' => 'checkbox',
                'desc' => L10N::r('Tick in case of problems of the wizard\'s state storing'),
                'id' => 'woocommerce_products_wizard_store_session_in_db',
                'key' => 'woocommerce_products_wizard_store_session_in_db',
                'section' => ''
            ],
            'send_state_hash_ajax' => [
                'name' => L10N::r('Send current state hash via AJAX'),
                'default' => 'no',
                'type' => 'checkbox',
                'desc' => L10N::r('Might help in case of freezing of the wizard\'s state with caching plugins'),
                'id' => 'woocommerce_products_wizard_send_state_hash_ajax',
                'key' => 'woocommerce_products_wizard_send_state_hash_ajax',
                'section' => ''
            ],
            'custom_styles_minification' => [
                'name' => L10N::r('Minify custom styles'),
                'default' => 'yes',
                'type' => 'checkbox',
                'id' => 'woocommerce_products_wizard_custom_styles_minification',
                'key' => 'woocommerce_products_wizard_custom_styles_minification',
                'section' => 'custom_styles'
            ],
            'custom_styles_mode' => [
                'name' => L10N::r('Mode'),
                'default' => 'simple',
                'type' => 'select',
                'id' => 'woocommerce_products_wizard_custom_styles_mode',
                'key' => 'woocommerce_products_wizard_custom_styles_mode',
                'options' => [
                    'simple' => L10N::r('Simple'),
                    'advanced' => L10N::r('Advanced')
                ],
                'section' => 'custom_styles'
            ],
            'style_font_size' => [
                'name' => L10N::r('Font size'),
                'default' => '1rem',
                'type' => 'text',
                'id' => 'woocommerce_products_wizard_style_font_size',
                'key' => 'woocommerce_products_wizard_style_font_size',
                'section' => 'custom_styles'
            ],
            'style_form_item_title_font_size' => [
                'name' => L10N::r('Form item title font size'),
                'default' => '1.1rem',
                'type' => 'text',
                'id' => 'woocommerce_products_wizard_style_form_item_title_font_size',
                'key' => 'woocommerce_products_wizard_style_form_item_title_font_size',
                'section' => 'custom_styles'
            ],
            'style_form_item_price_font_size' => [
                'name' => L10N::r('Form item price font size'),
                'default' => '1.25rem',
                'type' => 'text',
                'id' => 'woocommerce_products_wizard_style_form_item_price_font_size',
                'key' => 'woocommerce_products_wizard_style_form_item_price_font_size',
                'section' => 'custom_styles'
            ],
            'style_color_primary' => [
                'name' => L10N::r('Primary color'),
                'default' => '#007bff',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_primary',
                'key' => 'woocommerce_products_wizard_style_color_primary',
                'section' => 'custom_styles'
            ],
            'style_color_secondary' => [
                'name' => L10N::r('Secondary color'),
                'default' => '#6c757d',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_secondary',
                'key' => 'woocommerce_products_wizard_style_color_secondary',
                'section' => 'custom_styles'
            ],
            'style_color_success' => [
                'name' => L10N::r('Success color'),
                'default' => '#28a745',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_success',
                'key' => 'woocommerce_products_wizard_style_color_success',
                'section' => 'custom_styles'
            ],
            'style_color_info' => [
                'name' => L10N::r('Info color'),
                'default' => '#17a2b8',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_info',
                'key' => 'woocommerce_products_wizard_style_color_info',
                'section' => 'custom_styles'
            ],
            'style_color_warning' => [
                'name' => L10N::r('Warning color'),
                'default' => '#ffc107',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_warning',
                'key' => 'woocommerce_products_wizard_style_color_warning',
                'section' => 'custom_styles'
            ],
            'style_color_danger' => [
                'name' => L10N::r('Danger color'),
                'default' => '#dc3545',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_danger',
                'key' => 'woocommerce_products_wizard_style_color_danger',
                'section' => 'custom_styles'
            ],
            'style_color_light' => [
                'name' => L10N::r('Light color'),
                'default' => '#f8f9fa',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_light',
                'key' => 'woocommerce_products_wizard_style_color_light',
                'section' => 'custom_styles'
            ],
            'style_color_dark' => [
                'name' => L10N::r('Dark color'),
                'default' => '#343a40',
                'type' => 'color',
                'id' => 'woocommerce_products_wizard_style_color_dark',
                'key' => 'woocommerce_products_wizard_style_color_dark',
                'section' => 'custom_styles'
            ],
            'custom_scss' => [
                'name' => L10N::r('Custom SCSS'),
                'default' => str_replace(
                    ' !default',
                    '',
                    file_get_contents(WC_PRODUCTS_WIZARD_PLUGIN_PATH . 'src/front/scss/_variables.scss')
                ),
                'type' => 'textarea',
                'desc' => L10N::r('You can overwrite any bootstrap 4 variables or add custom styles here'),
                'css' => 'height:400px;width:100%',
                'id' => 'woocommerce_products_wizard_custom_scss',
                'key' => 'woocommerce_products_wizard_custom_scss',
                'section' => 'custom_styles'
            ]
        ];

        $post = [
            // <editor-fold desc="Behavior">
            'mode' => [
                'label' => L10N::r('Work mode'),
                'key' => '_mode',
                'type' => 'select',
                'values' => [
                    'step-by-step' => L10N::r('Step by step'),
                    'free-walk' => L10N::r('Free walk'),
                    'single-step' => L10N::r('Single step'),
                    'sequence' => L10N::r('Sequence')
                ],
                'default' => 'step-by-step',
                'group' => L10N::r('Behavior')
            ],
            'nav_action' => [
                'label' => L10N::r('Nav action'),
                'key' => '_nav_action',
                'type' => 'select',
                'values' => [
                    'submit' => L10N::r('Submit'),
                    'get-step' => L10N::r('Get step'),
                    'none' => L10N::r('None')
                ],
                'default' => 'submit',
                'group' => L10N::r('Behavior')
            ],
            'final_redirect_url' => [
                'label' => L10N::r('Final redirect URL'),
                'key' => '_final_redirect_url',
                'type' => 'text',
                'default' => get_permalink(function_exists('wc_get_page_id') ? wc_get_page_id('cart') : ''),
                'group' => L10N::r('Behavior')
            ],
            'check_availability_rules' => [
                'label' => L10N::r('Check availability rules'),
                'key' => '_check_availability_rules',
                'type' => 'checkbox',
                'default' => true,
                'description' => L10N::r('Check availability rules setting everywhere'),
                'group' => L10N::r('Behavior')
            ],
            'strict_cart_workflow' => [
                'label' => L10N::r('Strict cart workflow'),
                'key' => '_strict_cart_workflow',
                'type' => 'checkbox',
                'default' => true,
                'description' => L10N::r('Drop products from steps after the current'),
                'group' => L10N::r('Behavior')
            ],
            'scrolling_top_on_update' => [
                'label' => L10N::r('Scrolling to top on the form update'),
                'key' => '_scrolling_top_on_update',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Behavior'),
                'public' => true
            ],
            'scrolling_up_gap' => [
                'label' => L10N::r('The gap on scrolling up'),
                'key' => '_scrolling_up_gap',
                'type' => 'number',
                'default' => 0,
                'group' => L10N::r('Behavior'),
                'description' => L10N::r('px'),
                'public' => true
            ],
            'hide_prices' => [
                'label' => L10N::r('Hide prices'),
                'key' => '_hide_prices',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Hide product prices within the wizard'),
                'group' => L10N::r('Behavior')
            ],
            'price_discount' => [
                'label' => L10N::r('Price discount'),
                'key' => '_price_discount',
                'type' => 'number',
                'default' => 0,
                'min' => 0,
                'max' => 100,
                'step' => 'any',
                'group' => L10N::r('Behavior')
            ],
            'price_discount_type' => [
                'label' => L10N::r('Price discount type'),
                'key' => '_price_discount_type',
                'type' => 'select',
                'values' => [
                    'replace-prices' => L10N::r('Replace prices'),
                    'show-as-sale' => L10N::r('Show as sale')
                ],
                'default' => 'replace-prices',
                'group' => L10N::r('Behavior')
            ],
            // </editor-fold>
            // <editor-fold desc="Cart">
            'clear_main_cart_on_confirm' => [
                'label' => L10N::r('Clear main cart on the wizard confirm'),
                'key' => '_clear_main_cart_on_confirm',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Cart')
            ],
            'show_steps_in_cart' => [
                'label' => L10N::r('Show steps names in the cart'),
                'key' => '_show_steps_in_cart',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Appears in the cart tab and widget'),
                'group' => L10N::r('Cart')
            ],
            'reflect_in_main_cart' => [
                'label' => L10N::r('Reflect products in the main cart immediately'),
                'key' => '_reflect_in_main_cart',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Adds and removes products in the main cart accordingly to the wizard'),
                'group' => L10N::r('Cart')
            ],
            'min_products_selected' => [
                'label' => L10N::r('Minimum products selected'),
                'key' => '_min_products_selected',
                'type' => 'number',
                'min' => 0,
                'default' => '',
                'description' => L10N::r('Count of selected items of wizard'),
                'group' => L10N::r('Cart')
            ],
            'max_products_selected' => [
                'label' => L10N::r('Maximum products selected'),
                'key' => '_max_products_selected',
                'type' => 'number',
                'min' => 0,
                'default' => '',
                'description' => L10N::r('Count of selected items of wizard'),
                'group' => L10N::r('Cart')
            ],
            'min_total_products_quantity' => [
                'label' => L10N::r('Minimum total products quantity'),
                'key' => '_min_total_products_quantity',
                'type' => 'number',
                'min' => 0,
                'default' => '',
                'description' => L10N::r('Total selected products and their quantities'),
                'group' => L10N::r('Cart')
            ],
            'max_total_products_quantity' => [
                'label' => L10N::r('Maximum total products quantity'),
                'key' => '_max_total_products_quantity',
                'type' => 'number',
                'min' => 0,
                'default' => '',
                'description' => L10N::r('Total selected products and their quantities'),
                'group' => L10N::r('Cart')
            ],
            'min_products_price' => [
                'label' => L10N::r('Minimum products price'),
                'key' => '_min_products_price',
                'type' => 'number',
                'min' => 0,
                'step' => 'any',
                'default' => '',
                'description' => L10N::r('Total price of selected items of wizard'),
                'group' => L10N::r('Cart')
            ],
            'max_products_price' => [
                'label' => L10N::r('Maximum products price'),
                'key' => '_max_products_price',
                'type' => 'number',
                'min' => 0,
                'step' => 'any',
                'default' => '',
                'description' => L10N::r('Total price of selected items of wizard'),
                'group' => L10N::r('Cart')
            ],
            // </editor-fold>
            // <editor-fold desc="Kits">
            'group_products_into_kits' => [
                'label' => L10N::r('Group products into kits after adding to the main cart'),
                'key' => '_group_products_into_kits',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Not working with reflecting products in the main cart option'),
                'group' => L10N::r('Kits')
            ],
            'kits_type' => [
                'label' => L10N::r('Kits type'),
                'key' => '_kits_type',
                'type' => 'select',
                'values' => [
                    'separated' => L10N::r('Separated products'),
                    'combined' => L10N::r('Combined product')
                ],
                'default' => 'separated',
                'group' => L10N::r('Kits')
            ],
            'kit_base_product' => [
                'label' => L10N::r('Kit base product'),
                'key' => '_kit_base_product',
                'type' => 'wc-product-search',
                'multiple' => false,
                'default' => [],
                // phpcs:disable
                'description' => L10N::r('Define specific product to use as a base of the kit. Its price will be zeroed.'),
                // phpcs:enable
                'group' => L10N::r('Kits')
            ],
            'kit_base_price' => [
                'label' => L10N::r('Combined kit base price'),
                'key' => '_kit_base_price',
                'type' => 'number',
                'default' => '',
                'min' => 0,
                'step' => 'any',
                'description' => L10N::r('Set the base price of combined kits. It will be included by default.'),
                'group' => L10N::r('Kits')
            ],
            'kit_price' => [
                'label' => L10N::r('Combined kit fixed price'),
                'key' => '_kit_price',
                'type' => 'number',
                'default' => '',
                'min' => 0,
                'step' => 'any',
                'description' => L10N::r('Set the fixed price of combined kits. Overwrites kit base price!'),
                'group' => L10N::r('Kits')
            ],
            // </editor-fold>
            // <editor-fold desc="Thumbnail">
            'generate_thumbnail' => [
                'label' => L10N::r('Generate thumbnail'),
                'key' => '_generate_thumbnail',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Will be used for combined kits type or kit with a defined base product'),
                'group' => L10N::r('Thumbnail')
            ],
            'thumbnail_canvas_width' => [
                'label' => L10N::r('Thumbnail canvas width'),
                'key' => '_thumbnail_canvas_width',
                'type' => 'number',
                'min' => 100,
                'default' => 540,
                'group' => L10N::r('Thumbnail')
            ],
            'thumbnail_canvas_height' => [
                'label' => L10N::r('Thumbnail canvas height'),
                'key' => '_thumbnail_canvas_height',
                'type' => 'number',
                'min' => 100,
                'default' => 360,
                'group' => L10N::r('Thumbnail')
            ],
            'thumbnail_areas' => [
                'label' => L10N::r('Thumbnail areas'),
                'key' => '_thumbnail_areas',
                'type' => 'data-table',
                'default' => [[]],
                'group' => L10N::r('Thumbnail')
            ],
            // </editor-fold>
            // <editor-fold desc="Layout">
            'nav_template' => [
                'label' => L10N::r('Nav template'),
                'key' => '_nav_template',
                'type' => 'select',
                'values' => Template::getNavList(),
                'default' => 'tabs',
                'description' => L10N::r('For modes with navigation'),
                'group' => L10N::r('Layout')
            ],
            'show_sidebar' => [
                'label' => L10N::r('Show sidebar'),
                'key' => '_show_sidebar',
                'type' => 'select',
                'values' => [
                    'not_empty' => L10N::r('Then isn\'t empty'),
                    'always' => L10N::r('Always'),
                    'not_empty_until_result_step' => L10N::r('Then isn\'t empty until the results step'),
                    'always_until_result_step' => L10N::r('Always until the results step'),
                    'never' => L10N::r('Never')
                ],
                'default' => 'not_empty_until_result_step',
                'group' => L10N::r('Layout')
            ],
            'sidebar_position' => [
                'label' => L10N::r('Sidebar position'),
                'key' => '_sidebar_position',
                'type' => 'select',
                'default' => 'right',
                'values' => [
                    'right' => L10N::r('Right'),
                    'left' => L10N::r('Left'),
                    'top' => L10N::r('Top')
                ],
                'group' => L10N::r('Layout')
            ],
            'sticky_widget' => [
                'label' => L10N::r('Sticky widget'),
                'key' => '_sticky_widget',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Layout')
            ],
            'sticky_widget_offset_top' => [
                'label' => L10N::r('Sticky widget offset top'),
                'key' => '_sticky_widget_offset_top',
                'type' => 'number',
                'default' => 75,
                'description' => L10N::r('px'),
                'group' => L10N::r('Layout')
            ],
            'sticky_header' => [
                'label' => L10N::r('Sticky header'),
                'key' => '_sticky_header',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Layout')
            ],
            'sticky_header_offset_top' => [
                'label' => L10N::r('Sticky header offset top'),
                'key' => '_sticky_header_offset_top',
                'type' => 'number',
                'default' => 0,
                'description' => L10N::r('px'),
                'group' => L10N::r('Layout')
            ],
            'show_steps_names' => [
                'label' => L10N::r('Show steps names'),
                'key' => '_show_steps_names',
                'type' => 'checkbox',
                'default' => true,
                'description' => L10N::r('Show/hide steps names of one screen modes'),
                'group' => L10N::r('Layout')
            ],
            'table_layout_price_string' => [
                'label' => L10N::r('Table layout price string'),
                'key' => '_table_layout_price_string',
                'type' => 'text',
                'default' => L10N::r('Price'),
                'group' => L10N::r('Layout')
            ],
            'table_layout_thumbnail_string' => [
                'label' => L10N::r('Table layout thumbnail string'),
                'key' => '_table_layout_thumbnail_string',
                'type' => 'text',
                'default' => L10N::r('Thumbnail'),
                'group' => L10N::r('Layout')
            ],
            'table_layout_title_string' => [
                'label' => L10N::r('Table layout title string'),
                'key' => '_table_layout_title_string',
                'type' => 'text',
                'default' => L10N::r('Title'),
                'group' => L10N::r('Layout')
            ],
            'table_layout_to_cart_string' => [
                'label' => L10N::r('Table layout to cart string'),
                'key' => '_table_layout_to_cart_string',
                'type' => 'text',
                'default' => L10N::r('To cart'),
                'group' => L10N::r('Layout')
            ],
            // </editor-fold>
            // <editor-fold desc="Tabs">
            'enable_description_tab' => [
                'label' => L10N::r('Enable description tab'),
                'key' => '_enable_description_tab',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Tabs')
            ],
            'description_tab_title' => [
                'label' => L10N::r('Description tab title'),
                'key' => '_description_tab_title',
                'type' => 'text',
                'default' => L10N::r('Welcome'),
                'group' => L10N::r('Tabs')
            ],
            'description_tab_thumbnail' => [
                'label' => L10N::r('Description tab thumbnail'),
                'key' => '_description_tab_thumbnail',
                'type' => 'thumbnail',
                'default' => '',
                'group' => L10N::r('Tabs')
            ],
            'enable_results_tab' => [
                'label' => L10N::r('Enable results tab'),
                'key' => '_enable_results_tab',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Tabs')
            ],
            'results_tab_title' => [
                'label' => L10N::r('Results tab title'),
                'key' => '_results_tab_title',
                'type' => 'text',
                'default' => L10N::r('Total'),
                'group' => L10N::r('Tabs')
            ],
            'results_tab_thumbnail' => [
                'label' => L10N::r('Results tab thumbnail'),
                'key' => '_results_tab_thumbnail',
                'type' => 'thumbnail',
                'default' => '',
                'group' => L10N::r('Tabs')
            ],
            'results_tab_description' => [
                'label' => L10N::r('Results tab description'),
                'key' => '_results_tab_description',
                'type' => 'editor',
                'default' => '',
                'group' => L10N::r('Tabs')
            ],
            'show_results_tab_table' => [
                'label' => L10N::r('Show results tab table'),
                'key' => '_show_results_tab_table',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Tabs')
            ],
            'results_tab_contact_form' => [
                'label' => L10N::r('Results tab contact form'),
                'key' => '_results_tab_contact_form',
                'type' => 'select',
                'default' => '',
                'values' => $orderFormValues,
                'description' =>
                    L10N::r('Might have special shortcodes. For more info see the')
                    . ' <a href="' . WC_PRODUCTS_WIZARD_PLUGIN_URL
                    . 'documentation/index.html#post-settings-tabs" target="_blank">'
                    . L10N::r('documentation') . '</a>',
                'group' => L10N::r('Tabs')
            ],
            'results_price_string' => [
                'label' => L10N::r('Result tab price string'),
                'key' => '_results_price_string',
                'type' => 'text',
                'default' => L10N::r('Price'),
                'group' => L10N::r('Tabs')
            ],
            'results_thumbnail_string' => [
                'label' => L10N::r('Result thumbnail string'),
                'key' => '_results_thumbnail_string',
                'type' => 'text',
                'default' => L10N::r('Thumbnail'),
                'group' => L10N::r('Tabs')
            ],
            'results_data_string' => [
                'label' => L10N::r('Result data string'),
                'key' => '_results_data_string',
                'type' => 'text',
                'default' => L10N::r('Product'),
                'group' => L10N::r('Tabs')
            ],
            'results_remove_string' => [
                'label' => L10N::r('Result remove string'),
                'key' => '_results_remove_string',
                'type' => 'text',
                'default' => L10N::r('Remove'),
                'group' => L10N::r('Tabs')
            ],
            'results_quantity_string' => [
                'label' => L10N::r('Result quantity string'),
                'key' => '_results_quantity_string',
                'type' => 'text',
                'default' => L10N::r('Quantity'),
                'group' => L10N::r('Tabs')
            ],
            // </editor-fold>
            // <editor-fold desc="Strings">
            'empty_cart_message' => [
                'label' => L10N::r('Empty cart'),
                'key' => '_empty_cart_message',
                'type' => 'text',
                'default' => L10N::r('Your cart is empty'),
                'group' => L10N::r('Strings')
            ],
            'nothing_found_message' => [
                'label' => L10N::r('Nothing found'),
                'key' => '_nothing_found_message',
                'type' => 'text',
                'default' => L10N::r('No products were found matching your selection.', 'woocommerce'),
                'group' => L10N::r('Strings')
            ],
            'minimum_products_selected_message' => [
                'label' => L10N::r('Minimum products selected'),
                'key' => '_minimum_products_selected_message',
                'type' => 'text',
                'default' => L10N::r('Minimum selected items are required: %limit%'),
                'description' => L10N::r('"%limit%" - products limit')
                    . '<br>'
                    . L10N::r('"%value%" - current products count'),
                'group' => L10N::r('Strings')
            ],
            'maximum_products_selected_message' => [
                'label' => L10N::r('Maximum products selected'),
                'key' => '_maximum_products_selected_message',
                'type' => 'text',
                'default' => L10N::r('Maximum items selected: %limit%'),
                'description' => L10N::r('"%limit%" - products limit')
                    . '<br>'
                    . L10N::r('"%value%" - current products count'),
                'group' => L10N::r('Strings')
            ],
            'minimum_products_price_message' => [
                'label' => L10N::r('Minimum products price'),
                'key' => '_minimum_products_price_message',
                'type' => 'text',
                'default' => L10N::r('Minimum products price is %limit%. Your cart is only %value%'),
                'description' => L10N::r('"%limit%" - products price limit')
                    . '<br>'
                    . L10N::r('"%value%" - current products price'),
                'group' => L10N::r('Strings')
            ],
            'maximum_products_price_message' => [
                'label' => L10N::r('Maximum products price'),
                'key' => '_maximum_products_price_message',
                'type' => 'text',
                'default' => L10N::r('Maximum products price is %limit%. Your cart is %value%'),
                'description' => L10N::r('"%limit%" - products price limit')
                    . '<br>'
                    . L10N::r('"%value%" - current products price'),
                'group' => L10N::r('Strings')
            ],
            'kit_base_price_string' => [
                'label' => L10N::r('Kit base price string'),
                'key' => '_kit_base_price_string',
                'type' => 'text',
                'default' => L10N::r('Base price'),
                'group' => L10N::r('Strings')
            ],
            'subtotal_string' => [
                'label' => L10N::r('Subtotal string'),
                'key' => '_subtotal_string',
                'type' => 'text',
                'default' => L10N::r('Subtotal'),
                'group' => L10N::r('Strings')
            ],
            'discount_string' => [
                'label' => L10N::r('Discount string'),
                'key' => '_discount_string',
                'type' => 'text',
                'default' => L10N::r('Discount'),
                'group' => L10N::r('Strings')
            ],
            'total_string' => [
                'label' => L10N::r('Total string'),
                'key' => '_total_string',
                'type' => 'text',
                'default' => L10N::r('Total'),
                'group' => L10N::r('Strings')
            ],
            'file_upload_max_size_error' => [
                'label' => L10N::r('File upload max size error'),
                'key' => '_file_upload_max_size_error',
                'type' => 'text',
                'default' => L10N::r('The uploaded file is too large'),
                'group' => L10N::r('Strings')
            ],
            'file_upload_extension_error' => [
                'label' => L10N::r('File upload extension error'),
                'key' => '_file_upload_extension_error',
                'type' => 'text',
                'default' => L10N::r('The uploaded file extension is forbidden'),
                'group' => L10N::r('Strings')
            ],
            'file_upload_error' => [
                'label' => L10N::r('File upload error'),
                'key' => '_file_upload_error',
                'type' => 'text',
                'default' => L10N::r('File upload error'),
                'description' => L10N::r('For other unpredicted error cases'),
                'group' => L10N::r('Strings')
            ],
            // </editor-fold>
            // <editor-fold desc="Controls">
            'start_button_text' => [
                'label' => L10N::r('"Start" button text'),
                'key' => '_start_button_text',
                'type' => 'text',
                'default' => L10N::r('Start'),
                'group' => L10N::r('Controls')
            ],
            'start_button_class' => [
                'label' => L10N::r('"Start" button class'),
                'key' => '_start_button_class',
                'type' => 'text',
                'default' => 'btn-primary',
                'group' => L10N::r('Controls'),
                'description' => L10N::r('For more info see the')
                    . ' <a href="' . WC_PRODUCTS_WIZARD_PLUGIN_URL
                    . 'documentation/index.html#post-settings-controls" target="_blank">'
                    . L10N::r('documentation') . '</a>',
            ],
            'enable_add_to_cart_button' => [
                'label' => L10N::r('Enable "Add to cart" button'),
                'key' => '_enable_add_to_cart_button',
                'type' => 'checkbox',
                'default' => true,
                'description' => L10N::r('Appears at the last page'),
                'group' => L10N::r('Controls')
            ],
            'add_to_cart_button_text' => [
                'label' => L10N::r('"Add to cart" button text'),
                'key' => '_add_to_cart_button_text',
                'type' => 'text',
                'default' => L10N::r('Add to cart'),
                'group' => L10N::r('Controls')
            ],
            'add_to_cart_button_class' => [
                'label' => L10N::r('"Add to cart" button class'),
                'key' => '_add_to_cart_button_class',
                'type' => 'text',
                'default' => 'btn-danger',
                'group' => L10N::r('Controls')
            ],
            'enable_result_pdf_button' => [
                'label' => L10N::r('Enable "Result PDF" button'),
                'key' => '_enable_result_pdf_button',
                'type' => 'checkbox',
                'default' => true,
                'description' => L10N::r('Appears at the last page'),
                'group' => L10N::r('Controls')
            ],
            'result_pdf_button_text' => [
                'label' => L10N::r('"Result PDF" button text'),
                'key' => '_result_pdf_button_text',
                'type' => 'text',
                'default' => L10N::r('Get PDF'),
                'group' => L10N::r('Controls')
            ],
            'result_pdf_button_class' => [
                'label' => L10N::r('"Result PDF" button class'),
                'key' => '_result_pdf_button_class',
                'type' => 'text',
                'default' => 'btn-info',
                'group' => L10N::r('Controls')
            ],
            'enable_back_button' => [
                'label' => L10N::r('Enable "Back" button'),
                'key' => '_enable_back_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Controls')
            ],
            'back_button_text' => [
                'label' => L10N::r('"Back" button text'),
                'key' => '_back_button_text',
                'type' => 'text',
                'default' => L10N::r('Back'),
                'group' => L10N::r('Controls')
            ],
            'back_button_class' => [
                'label' => L10N::r('"Back" button class'),
                'key' => '_back_button_class',
                'type' => 'text',
                'default' => 'btn-default btn-light show-icon-on-mobile icon-right hide-text-on-mobile',
                'group' => L10N::r('Controls')
            ],
            'enable_next_button' => [
                'label' => L10N::r('Enable "Next" button'),
                'key' => '_enable_next_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Controls')
            ],
            'next_button_text' => [
                'label' => L10N::r('"Next" button text'),
                'key' => '_next_button_text',
                'type' => 'text',
                'default' => L10N::r('Next'),
                'group' => L10N::r('Controls')
            ],
            'next_button_class' => [
                'label' => L10N::r('"Next" button class'),
                'key' => '_next_button_class',
                'type' => 'text',
                'default' => 'btn-primary show-icon-on-mobile icon-right hide-text-on-mobile',
                'group' => L10N::r('Controls')
            ],
            'enable_reset_button' => [
                'label' => L10N::r('Enable "Reset" button'),
                'key' => '_enable_reset_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Controls')
            ],
            'reset_button_text' => [
                'label' => L10N::r('"Reset" button text'),
                'key' => '_reset_button_text',
                'type' => 'text',
                'default' => L10N::r('Reset'),
                'group' => L10N::r('Controls')
            ],
            'reset_button_class' => [
                'label' => L10N::r('"Reset" button class'),
                'key' => '_reset_button_class',
                'type' => 'text',
                'default' => 'btn-warning show-icon-on-mobile icon-right hide-text-on-mobile',
                'group' => L10N::r('Controls')
            ],
            'enable_skip_button' => [
                'label' => L10N::r('Enable "Skip" button'),
                'key' => '_enable_skip_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Controls')
            ],
            'skip_button_text' => [
                'label' => L10N::r('"Skip" button text'),
                'key' => '_skip_button_text',
                'type' => 'text',
                'default' => L10N::r('Skip'),
                'group' => L10N::r('Controls')
            ],
            'skip_button_class' => [
                'label' => L10N::r('"Skip" button class'),
                'key' => '_skip_button_class',
                'type' => 'text',
                'default' => 'btn-default btn-light show-icon-on-mobile icon-right hide-text-on-mobile',
                'group' => L10N::r('Controls')
            ],
            'enable_to_results_button' => [
                'label' => L10N::r('Enable "To results" button'),
                'key' => '_enable_to_results_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Controls')
            ],
            'to_results_button_text' => [
                'label' => L10N::r('"To results" button text'),
                'key' => '_to_results_button_text',
                'type' => 'text',
                'default' => L10N::r('To results'),
                'group' => L10N::r('Controls')
            ],
            'to_results_button_class' => [
                'label' => L10N::r('"To results" button class'),
                'key' => '_to_results_button_class',
                'type' => 'text',
                'default' => 'btn-success show-icon-on-mobile icon-right hide-text-on-mobile',
                'group' => L10N::r('Controls')
            ],
            'widget_toggle_button_text' => [
                'label' => L10N::r('"Toggle widget" button text'),
                'key' => '_widget_toggle_button_text',
                'type' => 'text',
                'default' => L10N::r('Toggle cart'),
                'group' => L10N::r('Controls')
            ],
            'widget_toggle_button_class' => [
                'label' => L10N::r('"Toggle widget" button class'),
                'key' => '_widget_toggle_button_class',
                'type' => 'text',
                'default' => 'd-block d-md-none btn-default btn-light show-icon icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            'enable_remove_button' => [
                'label' => L10N::r('Enable "Remove" button'),
                'key' => '_enable_remove_button',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Appears in the cart tab and widget'),
                'group' => L10N::r('Controls')
            ],
            'remove_button_text' => [
                'label' => L10N::r('"Remove" button text'),
                'key' => '_remove_button_text',
                'type' => 'text',
                'default' => L10N::r('Remove'),
                'group' => L10N::r('Controls')
            ],
            'remove_button_class' => [
                'label' => L10N::r('"Remove" button class'),
                'key' => '_remove_button_class',
                'type' => 'text',
                'default' => 'btn-light btn-sm show-icon icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            'enable_edit_button' => [
                'label' => L10N::r('Enable "Edit" button'),
                'key' => '_enable_edit_button',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Appears in the cart widget'),
                'group' => L10N::r('Controls')
            ],
            'edit_button_text' => [
                'label' => L10N::r('"Edit" button text'),
                'key' => '_edit_button_text',
                'type' => 'text',
                'default' => L10N::r('Edit'),
                'group' => L10N::r('Controls')
            ],
            'edit_button_class' => [
                'label' => L10N::r('"Edit" button class'),
                'key' => '_edit_button_class',
                'type' => 'text',
                'default' => 'btn-link btn-sm icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            'individual_add_to_cart_button_text' => [
                'label' => L10N::r('Individual "Add to cart" button text'),
                'key' => '_individual_add_to_cart_button_text',
                'type' => 'text',
                'default' => L10N::r('Add to cart'),
                'group' => L10N::r('Controls')
            ],
            'individual_add_to_cart_button_class' => [
                'label' => L10N::r('Individual "Add to cart" button class'),
                'key' => '_individual_add_to_cart_button_class',
                'type' => 'text',
                'default' => 'btn-primary btn-sm show-icon icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            'individual_update_button_text' => [
                'label' => L10N::r('Individual "Update" button text'),
                'key' => '_individual_update_button_text',
                'type' => 'text',
                'default' => L10N::r('Update'),
                'group' => L10N::r('Controls')
            ],
            'individual_update_button_class' => [
                'label' => L10N::r('Individual "Update" button class'),
                'key' => '_individual_update_button_class',
                'type' => 'text',
                'default' => 'btn-primary btn-sm show-icon icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            'individual_remove_button_text' => [
                'label' => L10N::r('Individual "Remove" button text'),
                'key' => '_individual_remove_button_text',
                'type' => 'text',
                'default' => L10N::r('Remove'),
                'group' => L10N::r('Controls')
            ],
            'individual_remove_button_class' => [
                'label' => L10N::r('Individual "Remove" button class'),
                'key' => '_individual_remove_button_class',
                'type' => 'text',
                'default' => 'btn-danger btn-sm show-icon icon-left hide-text',
                'group' => L10N::r('Controls')
            ],
            // </editor-fold>
            // <editor-fold desc="Result PDF">
            'result_pdf_header_height' => [
                'label' => L10N::r('Result PDF header height'),
                'key' => '_result_pdf_header_height',
                'type' => 'number',
                'min' => 0,
                'default' => 2,
                'description' => L10N::r('cm'),
                'group' => L10N::r('Result PDF')
            ],
            'result_pdf_header_content' => [
                'label' => L10N::r('Result PDF header content'),
                'key' => '_result_pdf_header_content',
                'type' => 'editor',
                'default' => '<div style="padding:2em;background-color:#f5f5f5">' . get_bloginfo('name') . '</div>',
                'group' => L10N::r('Result PDF')
            ],
            'result_pdf_top_description' => [
                'label' => L10N::r('Result PDF top description'),
                'key' => '_result_pdf_top_description',
                'type' => 'editor',
                'default' => 'Our commercial proposal',
                'description' =>
                    L10N::r('Allowed shortcodes: [wcpw-result-pdf-page-number], [wcpw-result-pdf-page-total]'),
                'group' => L10N::r('Result PDF')
            ],
            'result_pdf_footer_height' => [
                'label' => L10N::r('Result PDF footer height'),
                'key' => '_result_pdf_footer_height',
                'type' => 'number',
                'min' => 0,
                'default' => 2,
                'description' => L10N::r('cm'),
                'group' => L10N::r('Result PDF')
            ],
            'result_pdf_footer_content' => [
                'label' => L10N::r('Result PDF footer content'),
                'key' => '_result_pdf_footer_content',
                'type' => 'editor',
                'default' => '<div style="padding:2em;background-color:#f5f5f5;">'
                    . '[wcpw-result-pdf-page-number] / [wcpw-result-pdf-page-total]'
                    . '</div>',
                'description' =>
                    L10N::r('Allowed shortcodes: [wcpw-result-pdf-page-number], [wcpw-result-pdf-page-total]'),
                'group' => L10N::r('Result PDF')
            ],
            'result_pdf_bottom_description' => [
                'label' => L10N::r('Result PDF bottom description'),
                'key' => '_result_pdf_bottom_description',
                'type' => 'editor',
                'default' => 'Get more info: ' . get_bloginfo('url'),
                'group' => L10N::r('Result PDF')
            ],
            // </editor-fold>
            // <editor-fold desc="Filter">
            'filter_label' => [
                'label' => L10N::r('Filter label'),
                'key' => '_filter_label',
                'type' => 'text',
                'default' => L10N::r('Filter'),
                'group' => L10N::r('Filter')
            ],
            'filter_reset_button_text' => [
                'label' => L10N::r('"Reset" button text'),
                'key' => '_filter_reset_button_text',
                'type' => 'text',
                'default' => L10N::r('Reset'),
                'group' => L10N::r('Filter')
            ],
            'filter_submit_button_text' => [
                'label' => L10N::r('"Submit" button text'),
                'key' => '_filter_submit_button_text',
                'type' => 'text',
                'default' => L10N::r('Filter'),
                'group' => L10N::r('Filter')
            ],
            'filter_from_string' => [
                'label' => L10N::r('"From" string'),
                'key' => '_filter_from_string',
                'type' => 'text',
                'default' => L10N::r('From'),
                'group' => L10N::r('Filter')
            ],
            'filter_to_string' => [
                'label' => L10N::r('"To" string'),
                'key' => '_filter_to_string',
                'type' => 'text',
                'default' => L10N::r('To'),
                'group' => L10N::r('Filter')
            ]
            // </editor-fold>
        ];

        $step = [
            // <editor-fold desc="Captions">
            'title' => [
                'label' => L10N::r('Title'),
                'key' => 'title',
                'type' => 'text',
                'default' => '',
                'group' => L10N::r('Captions')
            ],
            'thumbnail' => [
                'label' => L10N::r('Thumbnail'),
                'key' => 'thumbnail',
                'type' => 'thumbnail',
                'default' => '',
                'group' => L10N::r('Captions')
            ],
            'description' => [
                'label' => L10N::r('Description'),
                'key' => 'description',
                'type' => 'editor',
                'inModal' => true,
                'default' => '',
                'description' => L10N::r('For more info see the')
                    . ' <a href="' . WC_PRODUCTS_WIZARD_PLUGIN_URL
                    . 'documentation/index.html#post-settings-step-modal-captions" target="_blank">'
                    . L10N::r('documentation') . '</a>',
                'group' => L10N::r('Captions')
            ],
            'description_position' => [
                'label' => L10N::r('Description position'),
                'key' => 'description_position',
                'type' => 'select',
                'values' => [
                    'top' => L10N::r('Top'),
                    'bottom' => L10N::r('Bottom')
                ],
                'default' => 'top',
                'group' => L10N::r('Captions')
            ],
            'description_auto_tags' => [
                'label' => L10N::r('Handle description with auto tags'),
                'key' => 'description_auto_tags',
                'type' => 'checkbox',
                'default' => true,
                'group' => L10N::r('Captions')
            ],
            'item_description_source' => [
                'label' => L10N::r('Item description source'),
                'key' => 'item_description_source',
                'type' => 'select',
                'values' => [
                    'content' => L10N::r('Product content'),
                    'excerpt' => L10N::r('Product short description'),
                    'none' => L10N::r('None')
                ],
                'default' => 'content',
                'group' => L10N::r('Captions')
            ],
            // </editor-fold>
            // <editor-fold desc="Query">
            'categories' => [
                'label' => L10N::r('Categories for using'),
                'key' => 'categories',
                'type' => 'multi-select',
                'default' => [],
                'values' => [],
                'description' => L10N::r('Select categories to get products'),
                'group' => L10N::r('Query')
            ],
            'included_products' => [
                'label' => L10N::r('Included products'),
                'key' => 'included_products',
                'type' => 'wc-product-search',
                'action' => 'woocommerce_json_search_products',
                'default' => [],
                'description' => L10N::r('Define specific products to output. Overwrites the categories setting!'),
                'group' => L10N::r('Query')
            ],
            'excluded_products' => [
                'label' => L10N::r('Excluded products'),
                'key' => 'excluded_products',
                'type' => 'wc-product-search',
                'default' => [],
                'description' => L10N::r('Exclude specific products or variations'),
                'group' => L10N::r('Query')
            ],
            'exclude_added_products_of_steps' => [
                'label' => L10N::r('Exclude already added products of steps'),
                'key' => 'exclude_added_products_of_steps',
                'type' => 'text',
                'pattern' => '([0-9]+.{0,1}[0-9]*,{0,1})*[0-9]',
                'default' => '',
                'description' =>
                    L10N::r('Hide steps products which are in the cart. Define steps IDs separated by a comma.'),
                'group' => L10N::r('Query')
            ],
            'availability_rules' => $availabilityRules,
            'order' => [
                'label' => L10N::r('Order'),
                'key' => 'order',
                'type' => 'select',
                'values' => [
                    'ASC' => L10N::r('ASC'),
                    'DESC' => L10N::r('DESC')
                ],
                'default' => 'ASC',
                'group' => L10N::r('Query')
            ],
            'order_by' => [
                'label' => L10N::r('Order by'),
                'key' => 'order_by',
                'type' => 'select',
                'values' => [
                    'ID' => L10N::r('ID'),
                    'author' => L10N::r('Author'),
                    'name' => L10N::r('Name'),
                    'date' => L10N::r('Date'),
                    'modified' => L10N::r('Modified'),
                    'rand' => L10N::r('Rand'),
                    'comment_count' => L10N::r('Comment count'),
                    'menu_order' => L10N::r('Menu order'),
                    'post__in' => L10N::r('Included products'),
                    'price' => L10N::r('Price')
                ],
                'default' => 'menu_order',
                'group' => L10N::r('Query')
            ],
            'enable_order_by_dropdown' => [
                'label' => L10N::r('Enable "Order by" dropdown'),
                'key' => 'enable_order_by_dropdown',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Query')
            ],
            'products_per_page' => [
                'label' => L10N::r('Products per page'),
                'key' => 'products_per_page',
                'type' => 'number',
                'min' => 0,
                'default' => 0,
                'description' => L10N::r('Zero is equal infinity'),
                'group' => L10N::r('Query')
            ],
            'products_per_page_items' => [
                'label' => L10N::r('Products per page items'),
                'key' => 'products_per_page_items',
                'type' => 'data-table',
                'showHeader' => false,
                'default' => ['' => ''],
                'values' => [
                    'items' => [
                        'label' => L10N::r('Products per page items'),
                        'key' => 'products_per_page_items',
                        'type' => 'number',
                        'min' => 1,
                        'default' => ''
                    ]
                ],
                'group' => L10N::r('Query')
            ],
            // </editor-fold>
            // <editor-fold desc="Cart">
            'individual_controls' => [
                'label' => L10N::r('Individual controls'),
                'key' => 'individual_controls',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Cart')
            ],
            'add_to_cart_behavior' => [
                'label' => L10N::r('"Add to cart" button behavior'),
                'key' => 'add_to_cart_behavior',
                'type' => 'select',
                'default' => 'default',
                'values' => [
                    'default' => L10N::r('Stay on the same step'),
                    'submit' => L10N::r('Go next'),
                    'add-to-main-cart' => L10N::r('Add to main cart')
                ],
                'group' => L10N::r('Cart')
            ],
            'hide_choose_element' => [
                'label' => L10N::r('Hide choose element'),
                'key' => 'hide_choose_element',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Use with individual controls only'),
                'group' => L10N::r('Cart')
            ],
            'add_to_cart_by_quantity' => [
                'label' => L10N::r('Add to cart by quantity'),
                'key' => 'add_to_cart_by_quantity',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Add all positive quantity products to the cart on submit'),
                'group' => L10N::r('Cart')
            ],
            'dont_add_to_cart' => [
                'label' => L10N::r('Don\'t add to the cart'),
                'key' => 'dont_add_to_cart',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Don\'t add products from this step to WooCommerce cart'),
                'group' => L10N::r('Cart')
            ],
            'dont_add_to_cart_products' => [
                'label' => L10N::r('Don\'t add specific products to the cart'),
                'key' => 'dont_add_to_cart_products',
                'type' => 'wc-product-search',
                'default' => [],
                'description' => L10N::r('Don\'t add specific products from this step to WooCommerce cart'),
                'group' => L10N::r('Cart')
            ],
            'several_products' => [
                'label' => L10N::r('Can select several products'),
                'key' => 'several_products',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Replace radio-inputs with checkboxes'),
                'group' => L10N::r('Cart')
            ],
            'several_variations_per_product' => [
                'label' => L10N::r('Can select several variations per one product'),
                'key' => 'several_variations_per_product',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Cart')
            ],
            'sold_individually' => [
                'label' => L10N::r('Sold individually'),
                'key' => 'sold_individually',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Hide products quantity input'),
                'group' => L10N::r('Cart')
            ],
            'selected_items_by_default' => [
                'label' => L10N::r('Selected items by default'),
                'type' => 'wc-product-search',
                'key' => 'selected_items_by_default',
                'default' => [],
                // phpcs:disable
                'description' => L10N::r('Products and their variations might be selected separately. Keep empty for auto-selecting.'),
                // phpcs:enable
                'group' => L10N::r('Cart')
            ],
            'all_selected_items_by_default' => [
                'label' => L10N::r('All items are selected by default'),
                'key' => 'all_selected_items_by_default',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Cart')
            ],
            'no_selected_items_by_default' => [
                'label' => L10N::r('No selected items by default'),
                'key' => 'no_selected_items_by_default',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Cart')
            ],
            'product_quantity_by_default' => [
                'label' => L10N::r('Default product quantity'),
                'key' => 'product_quantity_by_default',
                'type' => 'number',
                'min' => 0,
                'default' => 1,
                'group' => L10N::r('Cart')
            ],
            'min_products_selected' => [
                'label' => L10N::r('Minimum products selected'),
                'key' => 'min_products_selected',
                'type' => 'group',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Count of selected items per step'),
                'group' => L10N::r('Cart')
            ],
            'max_products_selected' => [
                'label' => L10N::r('Maximum products selected'),
                'key' => 'max_products_selected',
                'type' => 'group',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Count of selected items per step'),
                'group' => L10N::r('Cart')
            ],
            'min_product_quantity' => [
                'label' => L10N::r('Minimum product quantity'),
                'key' => 'min_product_quantity',
                'type' => 'group',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Product quantity input limit'),
                'group' => L10N::r('Cart')
            ],
            'max_product_quantity' => [
                'label' => L10N::r('Maximum product quantity'),
                'key' => 'max_product_quantity',
                'type' => 'group',
                'showHeader' => true,
                'description' => L10N::r('Product quantity input limit'),
                'default' => [],
                'group' => L10N::r('Cart')
            ],
            'min_total_products_quantity' => [
                'label' => L10N::r('Minimum total products quantity'),
                'key' => 'min_total_products_quantity',
                'type' => 'group',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Total selected products and their quantities'),
                'group' => L10N::r('Cart')
            ],
            'max_total_products_quantity' => [
                'label' => L10N::r('Maximum total products quantity'),
                'key' => 'max_total_products_quantity',
                'type' => 'group',
                'showHeader' => true,
                'description' => L10N::r('Total selected products and their quantities'),
                'default' => [],
                'group' => L10N::r('Cart')
            ],
            // </editor-fold>
            // <editor-fold desc="View">
            'template' => [
                'label' => L10N::r('Template'),
                'key' => 'template',
                'type' => 'select',
                'values' => Template::getFormList(),
                'default' => 'list',
                'description' => L10N::r('Use "grid column" setting for grid template configuring'),
                'group' => L10N::r('View')
            ],
            'grid_column' => [
                'label' => L10N::r('Grid column'),
                'key' => 'grid_column',
                'type' => 'group',
                'default' => [],
                'description' => L10N::r('Value from 1 to 12 according to the screen size'),
                'showHeader' => true,
                'values' => [
                    [
                        'label' => L10N::r('Smallest'),
                        'key' => 'xxs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 12
                    ],
                    [
                        'label' => L10N::r('Extra-small'),
                        'key' => 'xs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 6
                    ],
                    [
                        'label' => L10N::r('Small'),
                        'key' => 'sm',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Medium'),
                        'key' => 'md',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Large'),
                        'key' => 'lg',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Extra-large'),
                        'key' => 'xl',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 3
                    ]
                ],
                'group' => L10N::r('View')
            ],
            'grid_with_sidebar_column' => [
                'label' => L10N::r('Grid column then the sidebar is showed'),
                'key' => 'grid_with_sidebar_column',
                'type' => 'group',
                'default' => [
                    'xxs' => 6,
                    'xs' => 6,
                    'sm' => 6,
                    'md' => 4,
                    'lg' => 4
                ],
                'description' => L10N::r('Value from 1 to 12 according to the screen size'),
                'showHeader' => true,
                'values' => [
                    [
                        'label' => L10N::r('Smallest'),
                        'key' => 'xxs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 12
                    ],
                    [
                        'label' => L10N::r('Extra-small'),
                        'key' => 'xs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 6
                    ],
                    [
                        'label' => L10N::r('Small'),
                        'key' => 'sm',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 6
                    ],
                    [
                        'label' => L10N::r('Medium'),
                        'key' => 'md',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Large'),
                        'key' => 'lg',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Extra-large'),
                        'key' => 'xl',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ]
                ],
                'group' => L10N::r('View')
            ],
            'item_template' => [
                'label' => L10N::r('Item template'),
                'key' => 'item_template',
                'type' => 'select',
                'values' => Template::getFormItemList(),
                'default' => 'type-1',
                'description' => L10N::r('Doesn\'t matter for the "Table" template'),
                'group' => L10N::r('View')
            ],
            'merge_thumbnail_with_gallery' => [
                'label' => L10N::r('Merge thumbnail with gallery'),
                'key' => 'merge_thumbnail_with_gallery',
                'type' => 'checkbox',
                'default' => false,
                'description' => L10N::r('Show gallery withing thumbnail element'),
                'group' => L10N::r('View')
            ],
            'gallery_column' => [
                'label' => L10N::r('Gallery column'),
                'key' => 'gallery_column',
                'type' => 'group',
                'default' => [
                    'xxs' => 4,
                    'xs' => 4,
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 3
                ],
                'description' => L10N::r('Value from 1 to 12 according to the screen size'),
                'showHeader' => true,
                'values' => [
                    [
                        'label' => L10N::r('Smallest'),
                        'key' => 'xxs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Extra-small'),
                        'key' => 'xs',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 4
                    ],
                    [
                        'label' => L10N::r('Small'),
                        'key' => 'sm',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 3
                    ],
                    [
                        'label' => L10N::r('Medium'),
                        'key' => 'md',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 3
                    ],
                    [
                        'label' => L10N::r('Large'),
                        'key' => 'lg',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 3
                    ],
                    [
                        'label' => L10N::r('Extra-large'),
                        'key' => 'xl',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 12,
                        'default' => 3
                    ]
                ],
                'group' => L10N::r('View')
            ],
            'variations_type' => [
                'label' => L10N::r('Variation template'),
                'key' => 'variations_type',
                'type' => 'select',
                'values' => Template::getVariationsTypeList(),
                'default' => 'select',
                'group' => L10N::r('View')
            ],
            'enable_title_link' => [
                'label' => L10N::r('Enable title link'),
                'key' => 'enable_title_link',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('View')
            ],
            'show_tags' => [
                'label' => L10N::r('Show product tags'),
                'key' => 'show_tags',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('View')
            ],
            // </editor-fold>
            // <editor-fold desc="Filters">
            'filters' => [
                'label' => L10N::r('Filters'),
                'type' => 'data-table',
                'inModal' => true,
                'key' => 'filters',
                'default' => [],
                'values' => [
                    'source' => [
                        'label' => L10N::r('Source'),
                        'key' => 'source',
                        'type' => 'select',
                        'values' => self::getFilterSourcesList()
                    ],
                    'label' => [
                        'label' => L10N::r('Label'),
                        'key' => 'label',
                        'type' => 'text',
                        'default' => ''
                    ],
                    'include' => [
                        'label' => L10N::r('Include'),
                        'key' => 'include',
                        'type' => 'text',
                        'pattern' => '([0-9]+.{0,1}[0-9]*,{0,1})*[0-9]',
                        'description' =>
                            L10N::r('Include specific values only. Define terms IDs separated by a comma.'),
                        'default' => ''
                    ],
                    'exclude' => [
                        'label' => L10N::r('Exclude'),
                        'key' => 'exclude',
                        'type' => 'text',
                        'pattern' => '([0-9]+.{0,1}[0-9]*,{0,1})*[0-9]',
                        'description' => L10N::r('Exclude specific values. Define terms IDs separated by a comma.'),
                        'default' => ''
                    ],
                    'view' => [
                        'label' => L10N::r('View'),
                        'key' => 'view',
                        'type' => 'ajax-select',
                        'target-parent' => '[data-component="wcpw-data-table-item"]',
                        'target-selector' => '[data-key="source"] select',
                        'action' => 'wcpwGetFilterViewSelectOptions',
                        'default' => ''
                    ]
                ],
                'group' => L10N::r('Filters')
            ],
            'filter_position' => [
                'label' => L10N::r('Filter position'),
                'key' => 'filter_position',
                'type' => 'select',
                'default' => 'before-products',
                'description' => L10N::r('Sidebar filter not working for single-step layouts'),
                'values' => [
                    'before-products' => L10N::r('Before products'),
                    'before-widget' => L10N::r('Before sidebar widget')
                ],
                'group' => L10N::r('Filters')
            ],
            'filter_is_expanded' => [
                'label' => L10N::r('Expand filter by default'),
                'key' => 'filter_is_expanded',
                'type' => 'checkbox',
                'default' => false,
                'group' => L10N::r('Filters')
            ],
            'filter_thumbnail_size' => [
                'label' => L10N::r('Filter thumbnail size'),
                'type' => 'text',
                'key' => 'filter_thumbnail_size',
                'default' => 'thumbnail',
                // phpcs:disable
                'description' => L10N::r('Set width and height separated by a comma or use string value. For example thumbnail, medium, large'),
                // phpcs:enable
                'group' => L10N::r('Filters')
            ]
            // </editor-fold>
        ];

        $stepMinMaxRulesSettingsKeys = [
            'min_products_selected',
            'max_products_selected',
            'min_product_quantity',
            'max_product_quantity',
            'min_total_products_quantity',
            'max_total_products_quantity'
        ];

        $stepMinMaxRulesSettingsExtra = [
            'description' => '</br>' . L10N::r('Use simple numeric value or steps IDs separated by a comma'),
            'values' => [
                'type' => [
                    'label' => L10N::r('Type'),
                    'key' => 'type',
                    'type' => 'select',
                    'default' => 'number',
                    'values' => [
                        'count' => L10N::r('Simple count'),
                        'selected-from-step' => L10N::r('Count of selected products of steps'),
                        'least-from-step' => L10N::r('Least product quantity of steps'),
                        'greatest-from-step' => L10N::r('Greatest product quantity of steps'),
                        'sum-from-step' => L10N::r('Sum of products quantities of steps')
                    ]
                ],
                'value' => [
                    'label' => L10N::r('Value'),
                    'key' => 'value',
                    'type' => 'text',
                    'pattern' => '([0-9]+.{0,1}[0-9]*,{0,1})*[0-9]',
                    'default' => ''
                ]
            ]
        ];

        foreach ($stepMinMaxRulesSettingsKeys as $stepMinMaxRulesSettingKey) {
            $step[$stepMinMaxRulesSettingKey]['description'] .= $stepMinMaxRulesSettingsExtra['description'];
            $step[$stepMinMaxRulesSettingKey]['values'] = $stepMinMaxRulesSettingsExtra['values'];
        }

        $product = [
            'availability_rules' => array_replace(
                $availabilityRules,
                [
                    'key' => '_wcpw_availability_rules',
                    'description' => L10N::r('Show/hide the variation according the specific rules')
                ]
            ),
            'variations_type' => [
                'label' => L10N::r('Variation template'),
                'type' => 'select',
                'key' => '_wcpw_variations_type',
                'default' => 'default',
                'values' => ['default' => L10N::r('Default')] + Template::getVariationsTypeList()
            ],
            'discount' => [
                'label' => L10N::r('Discount'),
                'type' => 'group',
                'key' => '_wcpw_discount',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Reduce simple product price bought using a wizard'),
                'values' => [
                    'type' => [
                        'label' => L10N::r('Discount type'),
                        'key' => 'type',
                        'type' => 'select',
                        'default' => 'percentage',
                        'values' => [
                            'percentage' => L10N::r('Percentage'),
                            'fixed' => L10N::r('Fixed'),
                            'precise_price' => L10N::r('Precise price')
                        ]
                    ],
                    'value' => [
                        'label' => L10N::r('Value'),
                        'key' => 'value',
                        'type' => 'number',
                        'min' => 0,
                        'default' => ''
                    ]
                ]
            ],
            'attach_wizard' => [
                'label' => L10N::r('Attach wizard to the product'),
                'key' => '_wcpw_attach_wizard',
                'type' => 'select',
                'values' => $wizardIds,
                'default' => '',
                'description' => L10N::r('Not working with reflecting products in the main cart option')
            ],
            'attached_wizard_place' => [
                'label' => L10N::r('Attached wizard place'),
                'key' => '_wcpw_attached_wizard_place',
                'type' => 'select',
                'values' => [
                    'before_form' => L10N::r('Before form'),
                    'after_form' => L10N::r('After form'),
                    'tab' => L10N::r('Separate tab')
                ],
                'default' => 'before_form'
            ],
            'tab_title' => [
                'label' => L10N::r('Separate tab title'),
                'key' => '_wcpw_tab_title',
                'type' => 'text',
                'default' => L10N::r('WooCommerce Products Wizard')
            ],
            'redirect_on_add_to_cart' => [
                'label' => L10N::r('Redirect to the wizard on add to cart event'),
                'key' => '_wcpw_redirect_on_add_to_cart',
                'type' => 'select',
                'values' => $wizardIds,
                'default' => ''
            ],
            'redirect_link' => [
                'label' => L10N::r('Redirect link'),
                'key' => '_wcpw_redirect_link',
                'type' => 'text',
                'default' => ''
            ],
            'redirect_step_id' => [
                'label' => L10N::r('Step ID for using after redirect'),
                'key' => '_wcpw_step_id_after_redirect',
                'type' => 'number',
                'default' => '',
                // phpcs:disable
                'description' => L10N::r('If you want the product will be out of any step then set it to any out of steps IDs value')
                // phpcs:enable
            ],
            'thumbnail_areas' => [
                'label' => L10N::r('Generated thumbnail areas data'),
                'type' => 'data-table',
                'key' => '_wcpw_thumbnail_areas',
                'inModal' => true,
                'default' => [],
                'description' => L10N::r('Input the name of the area you want to replace with an image'),
                'values' => $thumbnailAreasValues
            ]
        ];

        $productVariation = [
            'availability_rules' => array_replace(
                $availabilityRules,
                [
                    'label' => L10N::r('Availability rules in wizard'),
                    'key' => '_wcpw_variation_availability_rules',
                    'description' => L10N::r('Show/hide the product according the specific rules')
                ]
            ),
            'discount' => [
                'label' => L10N::r('Products Wizard discount'),
                'type' => 'group',
                'key' => '_wcpw_variation_discount',
                'default' => [],
                'description' => L10N::r('Reduce product variations prices bought using a wizard'),
                'values' => [
                    'type' => [
                        'label' => L10N::r('Discount type'),
                        'key' => 'type',
                        'type' => 'select',
                        'default' => 'percentage',
                        'values' => [
                            'percentage' => L10N::r('Percentage'),
                            'fixed' => L10N::r('Fixed'),
                            'precise_price' => L10N::r('Precise price')
                        ]
                    ],
                    'value' => [
                        'label' => L10N::r('Value'),
                        'key' => 'value',
                        'type' => 'number',
                        'min' => 0,
                        'step' => 'any',
                        'default' => ''
                    ]
                ]
            ],
            'thumbnail_areas' => [
                'label' => L10N::r('Wizard generated thumbnail areas data'),
                'type' => 'data-table',
                'key' => '_wcpw_variation_thumbnail_areas',
                'inModal' => true,
                'default' => [],
                'description' => L10N::r('Input the name of the area you want to replace with an image'),
                'values' => $thumbnailAreasValues
            ]
        ];

        $productCategory = [
            'availability_rules' => array_replace(
                $availabilityRules,
                [
                    'label' => L10N::r('Availability rules in wizard'),
                    'key' => '_wcpw_availability_rules',
                    'description' => L10N::r('Show/hide the category according the specific rules')
                ]
            ),
            'discount' => [
                'label' => L10N::r('Discount in wizard'),
                'type' => 'group',
                'key' => '_wcpw_discount',
                'default' => [],
                'showHeader' => true,
                'description' => L10N::r('Reduce simple product price bought using a wizard'),
                'values' => [
                    'type' => [
                        'label' => L10N::r('Discount type'),
                        'key' => 'type',
                        'type' => 'select',
                        'default' => 'percentage',
                        'values' => [
                            'percentage' => L10N::r('Percentage'),
                            'fixed' => L10N::r('Fixed'),
                            'precise_price' => L10N::r('Precise price')
                        ]
                    ],
                    'value' => [
                        'label' => L10N::r('Value'),
                        'key' => 'value',
                        'type' => 'number',
                        'min' => 0,
                        'default' => ''
                    ]
                ]
            ],
            'attach_wizard' => [
                'label' => L10N::r('Attach wizard to products'),
                'key' => '_wcpw_attach_wizard',
                'type' => 'select',
                'values' => $wizardIds,
                'default' => '',
                'description' => L10N::r('Not working with reflecting products in the main cart option')
            ],
            'attached_wizard_place' => [
                'label' => L10N::r('Attached wizard place'),
                'key' => '_wcpw_attached_wizard_place',
                'type' => 'select',
                'values' => [
                    'before_form' => L10N::r('Before form'),
                    'after_form' => L10N::r('After form'),
                    'tab' => L10N::r('Separate tab')
                ],
                'default' => 'before_form'
            ],
            'tab_title' => [
                'label' => L10N::r('Separate tab title'),
                'key' => '_wcpw_tab_title',
                'type' => 'text',
                'default' => L10N::r('WooCommerce Products Wizard')
            ],
            'redirect_on_add_to_cart' => [
                'label' => L10N::r('Redirect to the wizard on add to cart event'),
                'key' => '_wcpw_redirect_on_add_to_cart',
                'type' => 'select',
                'values' => $wizardIds,
                'default' => ''
            ],
            'redirect_link' => [
                'label' => L10N::r('Redirect link'),
                'key' => '_wcpw_redirect_link',
                'type' => 'text',
                'default' => ''
            ],
            'redirect_step_id' => [
                'label' => L10N::r('Wizard step ID for using after redirect'),
                'key' => '_wcpw_step_id_after_redirect',
                'type' => 'number',
                'default' => '',
                // phpcs:disable
                'description' => L10N::r('If you want the product will be out of any step then set it to any out of steps IDs value')
                // phpcs:enable
            ],
            'thumbnail_areas' => [
                'label' => L10N::r('Wizard generated thumbnail areas data'),
                'type' => 'data-table',
                'key' => '_wcpw_thumbnail_areas',
                'inModal' => true,
                'default' => [],
                'description' => L10N::r('Input the name of the area you want to replace with an image'),
                'values' => $thumbnailAreasValues
            ]
        ];

        $productAttribute = [
            'thumbnail' => [
                'label' => L10N::r('Products Wizard Thumbnail'),
                'key' => '_wcpw_thumbnail_id',
                'type' => 'thumbnail',
                'default' => ''
            ]
        ];

        $modelCache = [
            'global' => apply_filters('wcProductsWizardGlobalSettingsModel', $global),
            'post' => apply_filters('wcProductsWizardPostSettingsModel', $post),
            'step' => apply_filters('wcProductsWizardStepSettingsModel', $step),
            'product' => apply_filters('wcProductsWizardProductSettingsModel', $product),
            'productVariation' => apply_filters('wcProductsWizardProductVariationSettingsModel', $productVariation),
            'productCategory' => apply_filters('wcProductsWizardProductCategorySettingsModel', $productCategory),
            'productAttribute' => apply_filters('wcProductsWizardProductAttributeSettingsModel', $productAttribute)
        ];

        if ($source && isset($modelCache[$source])) {
            return $modelCache[$source];
        }

        return apply_filters('wcProductsWizardSettingsModels', $modelCache);
    }

    /**
     * Handle the setting value according to the type
     *
     * @param mixed $value
     * @param string $type
     *
     * @return string|float|bool|array
     */
    public static function handleSettingType($value, $type = 'string')
    {
        switch ($type) {
            case 'checkbox':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'number':
                $value = (float) $value;
                break;

            case 'array':
                $value = (array) $value;
                break;
        }

        return $value;
    }

    /**
     * Get global setting
     *
     * @param string $setting
     *
     * @return string|float|bool|array
     */
    public static function getGlobal($setting)
    {
        $model = self::getModel('global');

        if (!isset($model[$setting])) {
            return null;
        }

        static $globalSettingsCache;

        if (isset($globalSettingsCache[$setting])) {
            $value = $globalSettingsCache[$setting];

            return apply_filters('wcProductsWizardGlobalSetting', $value, $setting);
        }

        $value = isset($model[$setting]['key']) ? get_option($model[$setting]['key'], null) : null;

        if ($value == null && isset($model[$setting]['default'])) {
            $value = $model[$setting]['default'];
        }

        $value = self::handleSettingType($value, $model[$setting]['type']);
        $globalSettingsCache[$setting] = $value;

        return apply_filters('wcProductsWizardGlobalSetting', $value, $setting);
    }

    /**
     * Get one of post setting
     *
     * @param integer $id
     * @param string $setting
     * @param string $modelSource
     *
     * @return string|float|boolean|array
     */
    public static function getPost($id, $setting, $modelSource = 'post')
    {
        $model = self::getModel($modelSource);

        if (!isset($model[$setting])) {
            return null;
        }

        static $postSettingsCache;

        if (isset($postSettingsCache[$id], $postSettingsCache[$id][$setting])) {
            $value = $postSettingsCache[$id][$setting];

            return apply_filters('wcProductsWizardPostSetting', $value, $id, $setting, $modelSource);
        }

        $value = isset($model[$setting]['key']) ? get_post_meta($id, $model[$setting]['key'], true) : null;

        if ($value == null && isset($model[$setting]['default'])) {
            $value = $model[$setting]['default'];
        }

        $value = self::handleSettingType($value, $model[$setting]['type']);

        if (!isset($postSettingsCache[$id])) {
            $postSettingsCache[$id] = [];
        }

        $postSettingsCache[$id][$setting] = $value;

        return apply_filters('wcProductsWizardPostSetting', $value, $id, $setting, $modelSource);
    }

    /**
     * Return one post settings array
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     */
    public static function getPostArray($id, $args = [])
    {
        $defaults = ['public' => false];
        $args = array_merge($defaults, $args);
        $model = self::getModel('post');
        $output = [];

        foreach ($model as $settingModelKey => $settingModel) {
            if ($args['public'] && (!isset($settingModel['public']) || !$settingModel['public'])) {
                continue;
            }

            $output[$settingModelKey] = self::getPost($id, $settingModelKey);
        }

        return apply_filters('wcProductsWizardPostSettings', $output, $id, $args);
    }

    /**
     * Get an array of the steps ids which are used in the wizard
     *
     * @param integer $id
     *
     * @return array
     */
    public static function getStepsIds($id)
    {
        static $stepsIdsCache;

        if (!isset($stepsIdsCache[$id])) {
            $stepsIdsCache[$id] = (array) get_post_meta($id, '_steps_ids', 1);
        }

        return apply_filters('wcProductsWizardStepsIdsSetting', $stepsIdsCache[$id], $id);
    }

    /**
     * Get steps settings record from DB
     *
     * @param integer $id
     *
     * @return array
     */
    public static function getStepsSettings($id)
    {
        static $stepsSettingsCache;

        if (!isset($stepsSettingsCache[$id])) {
            $stepsSettingsCache[$id] = (array) get_post_meta($id, '_steps_settings', 1);
        }

        return apply_filters('wcProductsWizardStepSettings', $stepsSettingsCache[$id], $id);
    }

    /**
     * Get one of wizard step setting
     *
     * @param integer $id
     * @param integer $stepId
     * @param string $setting
     *
     * @return string|float|boolean|array
     */
    public static function getStep($id, $stepId, $setting)
    {
        static $stepSettingsCache;

        if (isset(
            $stepSettingsCache[$id],
            $stepSettingsCache[$id][$stepId],
            $stepSettingsCache[$id][$stepId][$setting]
        )) {
            $value = $stepSettingsCache[$id][$stepId][$setting];

            return apply_filters('wcProductsWizardStepSetting', $value, $id, $stepId, $setting);
        }

        $model = self::getModel('step');

        if (!isset($model[$setting])) {
            $stepSettingsCache[$id][$stepId][$setting] = null;

            return apply_filters('wcProductsWizardStepSetting', null, $id, $stepId, $setting);
        }

        $meta = self::getStepsSettings($id);

        if ($meta && isset($meta[$stepId], $meta[$stepId][$model[$setting]['key']])) {
            $value = self::handleSettingType($meta[$stepId][$model[$setting]['key']], $model[$setting]['type']);
            $stepSettingsCache[$id][$stepId][$setting] = $value;

            return apply_filters('wcProductsWizardStepSetting', $value, $id, $stepId, $setting);
        }

        if (isset($model[$setting]['default'])) {
            $value = self::handleSettingType($model[$setting]['default'], $model[$setting]['type']);
            $stepSettingsCache[$id][$stepId][$setting] = $value;

            return apply_filters('wcProductsWizardStepSetting', $value, $id, $stepId, $setting);
        }

        $value = null;
        $stepSettingsCache[$id][$stepId][$setting] = $value;

        return apply_filters('wcProductsWizardStepSetting', $value, $id, $stepId, $setting);
    }

    /**
     * Get one of wizard step settings array
     *
     * @param integer $id
     * @param integer $stepId
     * @param array $args
     *
     * @return string|float|boolean|array
     */
    public static function getStepArray($id, $stepId, $args = [])
    {
        $defaults = ['public' => false];
        $args = array_merge($defaults, $args);
        $model = self::getModel('step');
        $output = [];

        foreach ($model as $settingModelKey => $settingModel) {
            if ($args['public'] && (!isset($settingModel['public']) || !$settingModel['public'])) {
                continue;
            }

            $output[$settingModelKey] = self::getStep($id, $stepId, $settingModelKey);
        }

        return apply_filters('wcProductsWizardStepSettings', $output, $id, $args);
    }

    /**
     * Get product setting
     *
     * @param integer $id
     * @param string $setting
     *
     * @return string|float|bool|array
     */
    public static function getProduct($id, $setting)
    {
        return self::getPost($id, $setting, 'product');
    }

    /**
     * Get product variation setting
     *
     * @param integer $id
     * @param string $setting
     *
     * @return string|float|bool|array
     */
    public static function getProductVariation($id, $setting)
    {
        return self::getPost($id, $setting, 'productVariation');
    }

    /**
     * Get product category setting
     *
     * @param integer $id
     * @param string $setting
     *
     * @return string|float|bool|array
     */
    public static function getProductCategory($id, $setting)
    {
        $model = self::getModel('productCategory');

        if (!isset($model[$setting])) {
            return null;
        }

        static $productCategorySettingsCache;

        if (isset($productCategorySettingsCache[$id], $productCategorySettingsCache[$id][$setting])) {
            $value = $productCategorySettingsCache[$id][$setting];

            return apply_filters('wcProductsWizardProductCategorySetting', $value, $id, $setting);
        }

        $value = isset($model[$setting]['key']) ? get_term_meta($id, $model[$setting]['key'], true) : null;

        if ($value == null && isset($model[$setting]['default'])) {
            $value = $model[$setting]['default'];
        }

        $value = self::handleSettingType($value, $model[$setting]['type']);

        if (!isset($productCategorySettingsCache[$id])) {
            $productCategorySettingsCache[$id] = [];
        }

        $productCategorySettingsCache[$id][$setting] = $value;

        return apply_filters('wcProductsWizardProductCategorySetting', $value, $id, $setting);
    }

    /**
     * Is sidebar should be visible
     *
     * @param integer $id
     *
     * @return bool
     */
    public static function isSidebarShowed($id)
    {
        $stepId = Form::getActiveStepId($id);

        switch (self::getPost($id, 'show_sidebar')) {
            case 'always':
                $show = true;
                break;

            case 'never':
                $show = false;
                break;

            case 'always_until_result_step':
                $show = $stepId != 'result';
                break;

            case 'not_empty_until_result_step':
                $show = $stepId != 'result' && !empty(Cart::get($id));
                break;

            default:
            case 'not_empty':
                $show = !empty(Cart::get($id));
                break;
        }

        // show a filter in the sidebar, but not for single step layouts
        if (self::getStep($id, $stepId, 'filter_position') == 'before-widget'
            && !empty(Form::getFilterFields($id, $stepId))
            && !in_array(self::getPost($id, 'mode'), ['single-step', 'sequence'])
        ) {
            $show = true;
        }

        return apply_filters('wcProductsWizardIsSidebarShowed', $show, $id);
    }

    /**
     * Get final redirect URL
     *
     * @param integer $id
     *
     * @return string
     */
    public static function getFinalRedirectUrl($id)
    {
        $url = self::getPost($id, 'final_redirect_url');

        // if the settings is empty
        if (!$url && function_exists('wc_get_page_id')) {
            $url = get_permalink(wc_get_page_id('cart'));
        }

        // if url is absolute
        if (strpos($url, home_url()) === false) {
            $url = home_url() . '/' . $url;
        }

        return apply_filters('wcProductsWizardFinalRedirectUrl', $url, $id);
    }

    /**
     * Get list of possible filter sources
     *
     * @return array
     */
    public static function getFilterSourcesList()
    {
        $output = [
            '' => '',
            'price' => L10N::r('Price'),
            'category' => L10N::r('Category'),
            'tag' => L10N::r('Tag'),
            'search' => L10N::r('Search')
        ];

        $output = array_merge($output, self::getAttributesList());

        return apply_filters('wcProductsWizardFilterSourcesList', $output);
    }

    /**
     * Get list of possible product attributes
     *
     * @return array
     */
    public static function getAttributesList()
    {
        $output = [];

        if (function_exists('wc_get_attribute_taxonomies')) {
            foreach (wc_get_attribute_taxonomies() as $attribute) {
                $output[$attribute->attribute_name] = $attribute->attribute_label;
            }
        }

        return $output;
    }

    /**
     * Get list of the filter views
     *
     * @param string $value
     *
     * @return array
     */
    public function getFilterViewSelectOptions($value)
    {
        $output = [];

        switch ($value) {
            case 'price': {
                $output['range'] = L10N::r('Range');
                break;
            }

            case 'category': {
                $output['select'] = L10N::r('Select');
                $output['inline-radio'] = L10N::r('Inline radio');
                $output['radio'] = L10N::r('Radio');
                $output['image-radio'] = L10N::r('Image radio');
                $output['inline-checkbox'] = L10N::r('Inline checkbox');
                $output['checkbox'] = L10N::r('Checkbox');
                $output['image-checkbox'] = L10N::r('Image checkbox');
                break;
            }

            case 'tag': {
                $output['select'] = L10N::r('Select');
                $output['inline-radio'] = L10N::r('Inline radio');
                $output['radio'] = L10N::r('Radio');
                $output['inline-checkbox'] = L10N::r('Inline checkbox');
                $output['checkbox'] = L10N::r('Checkbox');
                break;
            }

            case 'search': {
                $output['text'] = L10N::r('Text');
                break;
            }

            default: {
                $output['range'] = L10N::r('Range');
                $output['select'] = L10N::r('Select');
                $output['inline-radio'] = L10N::r('Inline radio');
                $output['radio'] = L10N::r('Radio');
                $output['image-radio'] = L10N::r('Image radio');
                $output['inline-checkbox'] = L10N::r('Inline checkbox');
                $output['checkbox'] = L10N::r('Checkbox');
                $output['image-checkbox'] = L10N::r('Image checkbox');
            }
        }

        return $output;
    }

    /** Get list of the filter views via Ajax */
    public function getFilterViewSelectOptionsAjax()
    {
        $output = [];
        $value = (string) $_GET['value'];

        if (!$value) {
            exit;
        }

        $values = $this->getFilterViewSelectOptions($value);

        foreach ($values as $key => $name) {
            $output[] = "<option value=\"$key\">$name</option>";
        }

        echo implode('', $output);

        exit;
    }

    /**
     * Get min products selected message
     *
     * @param integer $id - wizard ID
     * @param integer $limit - products limit
     * @param integer $value - products current value
     *
     * @return string
     */
    public static function getMinimumProductsSelectedMessage($id, $limit, $value)
    {
        $message = str_replace(
            [
                '%limit%',
                '%value%'
            ],
            [
                $limit,
                $value
            ],
            self::getPost($id, 'minimum_products_selected_message')
        );

        return apply_filters('wcProductsWizardMinimumProductsSelectedMessage', $message, $id, $limit, $value);
    }

    /**
     * Get max products selected message
     *
     * @param integer $id - wizard ID
     * @param integer $limit - products limit
     * @param integer $value - products current value
     *
     * @return string
     */
    public static function getMaximumProductsSelectedMessage($id, $limit, $value)
    {
        $message = str_replace(
            [
                '%limit%',
                '%value%'
            ],
            [
                $limit,
                $value
            ],
            self::getPost($id, 'maximum_products_selected_message')
        );

        return apply_filters('wcProductsWizardMaximumProductsSelectedMessage', $message, $id, $limit, $value);
    }

    /**
     * Get min products price message
     *
     * @param integer $id - wizard ID
     * @param integer $limit - products price limit
     * @param integer $value - current products price
     *
     * @return string
     */
    public static function getMinimumProductsPriceMessage($id, $limit, $value)
    {
        $message = str_replace(
            [
                '%limit%',
                '%value%'
            ],
            [
                $limit,
                $value
            ],
            self::getPost($id, 'minimum_products_price_message')
        );

        return apply_filters('wcProductsWizardMinimumProductsPriceMessage', $message, $id, $limit, $value);
    }

    /**
     * Get max products price message
     *
     * @param integer $id - wizard ID
     * @param integer $limit - products price limit
     * @param integer $value - current products price
     *
     * @return string
     */
    public static function getMaximumProductsPriceMessage($id, $limit, $value)
    {
        $message = str_replace(
            [
                '%limit%',
                '%value%'
            ],
            [
                $limit,
                $value
            ],
            self::getPost($id, 'maximum_products_price_message')
        );

        return apply_filters('wcProductsWizardMaximumProductsPriceMessage', $message, $id, $limit, $value);
    }

    // <editor-fold desc="Deprecated">
    /**
     * Get one of wizard step setting
     *
     * @param integer $id
     * @param integer $stepId
     * @param string $setting
     *
     * @return string|float|boolean|array
     *
     * @deprecated 6.0.0 use getStep
     */
    public static function getTerm($id, $stepId, $setting)
    {
        return self::getStep($id, $stepId, $setting);
    }

    /**
     * Is sidebar should be visible
     *
     * @param integer $id
     *
     * @return bool
     *
     * @deprecated 4.5.1
     */
    public static function showSidebar($id)
    {
        return self::isSidebarShowed($id);
    }

    /**
     * Get an array of the terms settings which used in the wizard
     *
     * @param integer $id
     *
     * @return array
     *
     * @deprecated 3.20.1
     */
    public static function getTerms($id)
    {
        return self::getStepsSettings($id);
    }

    /**
     * Return the wizard settings
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     *
     * @deprecated 3.20.1
     */
    public static function getWizard($id, $args = [])
    {
        return self::getPostArray($id, $args);
    }

    /**
     * Return term description text
     *
     * @param integer $id
     * @param integer $termId
     *
     * @return string
     *
     * @deprecated 3.20.1
     */
    public static function getTermDescription($id, $termId)
    {
        return self::getStep($id, $termId, 'description');
    }

    /**
     * Return term description position
     *
     * @param integer $id
     * @param integer $termId
     *
     * @return string
     *
     * @deprecated 3.20.1
     */
    public static function getTermDescriptionPosition($id, $termId)
    {
        return self::getStep($id, $termId, 'description_position');
    }

    /**
     * Return settings value from array or default value
     *
     * @param array $settings
     * @param string $key
     * @param string $default
     * @param string $type
     *
     * @return string|float|bool|array
     *
     * @deprecated 4.0.0
     */
    public static function getValue($settings, $key, $default = '', $type = 'string')
    {
        if (!isset($settings[$key])) {
            return $default;
        }

        $value = $settings[$key];

        switch ($type) {
            case 'string':
                $value = (string) $value;
                break;

            case 'int':
            case 'integer':
                $value = (int) $value;
                break;

            case 'float':
                $value = (float) $value;
                break;

            case 'array':
                $value = (array) $value;
                break;

            case 'bool':
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            default:
                $value = (string) $value;
                break;
        }

        return $value;
    }

    /**
     * Get post settings record from DB
     *
     * @param integer $id
     *
     * @return array
     *
     * @deprecated 4.0.0
     */
    public static function getPostMeta($id)
    {
        static $postSettingsCache;

        if (!isset($postSettingsCache[$id])) {
            $postSettingsCache[$id] = (array) get_post_meta($id, 'settings', 1);
        }

        return $postSettingsCache[$id];
    }
    // </editor-fold>
}

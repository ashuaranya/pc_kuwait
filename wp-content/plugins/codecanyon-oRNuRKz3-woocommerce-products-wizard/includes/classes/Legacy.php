<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Legacy Class
 *
 * @class Legacy
 * @version 2.2.0
 */
class Legacy
{
    public static $lastChangedVersion = '9.1.1';

    /** Class Constructor */
    public function __construct()
    {
        add_action('woocommerce_init', [$this, 'onInitAction']);

        $this->onInitAction();
    }

    /** On WP init action */
    public function onInitAction()
    {
        $this->wizardsUpdate();
        $this->productsUpdate();
        $this->variationsUpdate();
        $this->productCategoriesUpdate();
    }

    /** Migrate wizards meta */
    public function wizardsUpdate()
    {
        $wizards = get_posts([
            'numberposts' => -1,
            'fields' => 'ids',
            'post_type' => 'wc_product_wizard'
        ]);

        foreach ($wizards as $wizardId) {
            $wizardId = (int) $wizardId;
            // @since v4.0.0
            // settings array to meta items
            $settings = get_post_meta($wizardId, 'settings', 1);

            if (!empty($settings)) {
                $keysToReplace = [
                    'dependencies_disable' => '_disable_dependencies',
                    'description_tab_enable' => '_enable_description_tab',
                    'results_tab_enable' => '_enable_results_tab'
                ];

                foreach ($settings as $key => $value) {
                    $newKey = "_$key";

                    if (isset($keysToReplace[$key])) {
                        $newKey = $keysToReplace[$key];
                    }

                    update_post_meta($wizardId, $newKey, $value);
                }

                delete_post_meta($wizardId, 'settings');
            }

            $keysToReplace = [
                'default_cart_content' => '_default_cart_content', // @since v4.3.0
                'terms_list_ids' => '_terms_list_ids', // @since v4.3.0
                'terms_list_settings' => '_terms_list_settings', // @since v4.3.0
                '_sticky_controls_offset_top' => '_sticky_header_offset_top', // @since v8.6.0
                '_sticky_widget_offset_top' => '_sticky_sidebar_offset_top', // @since v8.8.0
                '_clear_woocommerce_cart_on_confirms' => '_clear_main_cart_on_confirm', // @since v9.1.1
            ];

            foreach ($keysToReplace as $old => $new) {
                $meta = get_post_meta($wizardId, $old, 1);

                if (!empty($meta)) {
                    update_post_meta($wizardId, $new, $meta);
                    delete_post_meta($wizardId, $old);
                }
            }

            // @since v4.3.0
            update_post_meta(
                $wizardId,
                '_minimum_products_selected_message',
                str_replace('%d', '%limit%', get_post_meta($wizardId, '_minimum_products_selected_message', 1))
            );

            update_post_meta(
                $wizardId,
                '_maximum_products_selected_message',
                str_replace('%d', '%limit%', get_post_meta($wizardId, '_maximum_products_selected_message', 1))
            );

            // @since v7.0.0
            $enableAllTabsAvailability = get_post_meta($wizardId, '_enable_all_tabs_availability', 1);
            $enableSingleStepMode = get_post_meta($wizardId, '_enable_single_step_mode', 1);

            if (filter_var($enableSingleStepMode, FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($wizardId, '_mode', 'single-step');
                delete_post_meta($wizardId, '_enable_single_step_mode');
            } elseif (filter_var($enableAllTabsAvailability, FILTER_VALIDATE_BOOLEAN)) {
                update_post_meta($wizardId, '_mode', 'free-walk');
                delete_post_meta($wizardId, '_enable_all_tabs_availability');
            }

            // @since v8.0.0
            $stepsIds = get_post_meta($wizardId, '_terms_list_ids', 1);
            $stepsSettings = get_post_meta($wizardId, '_terms_list_settings', 1);

            if (!empty($stepsIds) && !empty($stepsSettings)) {
                foreach ($stepsSettings as $key => $settings) {
                    $stepsSettings[$key]['categories'] = [$key];

                    if (empty($settings['title'])) {
                        $term = get_term($key, 'product_cat');
                        $stepsSettings[$key]['title'] = $term->name;
                    }
                }

                update_post_meta($wizardId, '_steps_ids', $stepsIds);
                update_post_meta($wizardId, '_steps_settings', $stepsSettings);
                delete_post_meta($wizardId, '_terms_list_ids');
                delete_post_meta($wizardId, '_terms_list_settings');
            }

            // @since v9.0.0
            // availability rules setting
            $disableDependencies = get_post_meta($wizardId, '_disable_dependencies', 1);

            if (!is_null($disableDependencies)) {
                $value = filter_var($disableDependencies, FILTER_VALIDATE_BOOLEAN);

                update_post_meta($wizardId, '_check_availability_rules', (int) !$value);
                update_post_meta($wizardId, '_strict_cart_workflow', (int) !$value);
                delete_post_meta($wizardId, '_disable_dependencies');
            }

            $stepsSettings = get_post_meta($wizardId, '_steps_settings', 1);

            if (!empty($stepsSettings)) {
                foreach ($stepsSettings as $key => $settings) {
                    $rules = [];

                    if (isset($settings['required_added_products']) && is_array($settings['required_added_products'])
                        && !empty($settings['required_added_products'])
                    ) {
                        foreach ($settings['required_added_products'] as $dependency) {
                            $rules[] = [
                                'source' => 'product',
                                'product' => $dependency,
                                'condition' => 'in_cart',
                                'inner_relation' => 'and',
                                'outer_relation' => 'or'
                            ];
                        }

                        unset($stepsSettings[$key]['required_added_products']);
                    }

                    if (isset($settings['excluded_added_products']) && is_array($settings['excluded_added_products'])
                        && !empty($settings['excluded_added_products'])
                    ) {
                        foreach ($settings['excluded_added_products'] as $exclusion) {
                            $rules[] = [
                                'source' => 'product',
                                'product' => $exclusion,
                                'condition' => 'not_in_cart',
                                'inner_relation' => 'and',
                                'outer_relation' => 'or'
                            ];
                        }

                        unset($stepsSettings[$key]['excluded_added_products']);
                    }

                    $stepsSettings[$key]['availability_rules'] = array_filter($rules);
                }

                update_post_meta($wizardId, '_steps_settings', $stepsSettings);
            }
        }
    }

    /** Migrate products meta */
    public function productsUpdate()
    {
        $products = get_posts([
            'numberposts' => -1,
            'fields' => 'ids',
            'post_type' => 'product',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wcpw_dependencies',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'dependency_ids',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'products_wizard_variations_type',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        foreach ($products as $productId) {
            $productId = (int) $productId;
            $keysToReplace = [
                'dependency_ids' => '_wcpw_dependencies', // @since v3.16.1
                'products_wizard_variations_type' => '_wcpw_variations_type', // @since v3.16.1
            ];

            foreach ($keysToReplace as $old => $new) {
                $metaValue = get_post_meta($productId, $old, 1);

                if (!empty($metaValue)) {
                    update_post_meta($productId, $new, $metaValue);
                    delete_post_meta($productId, $old);
                }
            }

            // @since v9.0.0
            // availability rules setting
            $dependencies = get_post_meta($productId, '_wcpw_dependencies', 1);
            $exclusions = get_post_meta($productId, '_wcpw_exclusions', 1);

            if ((is_array($dependencies) && !empty($dependencies)) || (is_array($exclusions) && !empty($exclusions))) {
                $rules = [];

                if (!empty($dependencies)) {
                    foreach ($dependencies as $dependency) {
                        if ($dependency == [1]) {
                            continue;
                        }

                        $rules[] = [
                            'source' => 'product',
                            'product' => $dependency,
                            'condition' => 'in_cart',
                            'inner_relation' => 'and',
                            'outer_relation' => 'or'
                        ];
                    }
                }

                if (!empty($exclusions)) {
                    foreach ($exclusions as $exclusion) {
                        $rules[] = [
                            'source' => 'product',
                            'product' => $exclusion,
                            'condition' => 'not_in_cart',
                            'inner_relation' => 'and',
                            'outer_relation' => 'or'
                        ];
                    }
                }

                if (!empty(array_filter($rules))) {
                    update_post_meta($productId, '_wcpw_availability_rules', array_filter($rules));
                }

                delete_post_meta($productId, '_wcpw_dependencies');
                delete_post_meta($productId, '_wcpw_exclusions');
            }
        }
    }

    /** Migrate variations meta */
    public function variationsUpdate()
    {
        $products = get_posts([
            'numberposts' => -1,
            'fields' => 'ids',
            'post_type' => 'product_variation',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'variation_wcpw_dependencies',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => '_wcpw_variation_dependencies',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        foreach ($products as $productId) {
            $productId = (int) $productId;
            $keysToReplace = [
                'variation_wcpw_dependencies' => '_wcpw_variation_dependencies', // @since v3.16.1
            ];

            foreach ($keysToReplace as $old => $new) {
                $metaValue = get_post_meta($productId, $old, 1);

                if (!empty($metaValue)) {
                    update_post_meta($productId, $new, $metaValue);
                    delete_post_meta($productId, $old);
                }
            }

            // @since v9.0.0
            // availability rules setting
            $dependencies = get_post_meta($productId, '_wcpw_variation_dependencies', 1);

            if (is_array($dependencies) && !empty($dependencies)) {
                $rules = [];

                foreach ($dependencies as $dependency) {
                    if ($dependency == [1]) {
                        continue;
                    }

                    $rules[] = [
                        'source' => 'product',
                        'product' => $dependency,
                        'condition' => 'in_cart',
                        'inner_relation' => 'and',
                        'outer_relation' => 'or'
                    ];
                }

                if (!empty(array_filter($rules))) {
                    update_post_meta($productId, '_wcpw_availability_rules', array_filter($rules));
                }

                delete_post_meta($productId, '_wcpw_variation_dependencies');
            }
        }
    }

    /** Migrate products categories meta */
    public function productCategoriesUpdate()
    {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'fields' => 'ids',
            'hide_empty' => false,
            'hierarchical' => false,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wcpw_dependencies',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        if (is_array($terms)) {
            foreach ($terms as $termId) {
                $keysToReplace = [
                    'redirect_to_wizard_on_product_add' => '_wcpw_redirect_to_wizard_on_product_add', // @since v4.0.0
                    'redirect_link' => '_wcpw_redirect_link', // @since v4.0.0
                    'wizard_id' => '_wcpw_wizard_id', // @since v4.0.0
                    '_wcpw_step_id' => '_wcpw_step_id_after_redirect', // @since v4.0.0
                    '_wcpw_redirect_to_wizard_on_product_add' => '_wcpw_redirect_on_add_to_cart' // @since v8.7.0
                ];

                foreach ($keysToReplace as $old => $new) {
                    $metaValue = get_term_meta($termId, $old, 1);

                    if (!empty($metaValue)) {
                        update_term_meta($termId, $new, $metaValue);
                        delete_term_meta($termId, $old);
                    }
                }

                // @since v9.0.0
                // availability rules setting
                $dependencies = get_term_meta($termId, '_wcpw_dependencies', 1);

                if (is_array($dependencies) && !empty($dependencies)) {
                    $rules = [];

                    foreach ($dependencies as $dependency) {
                        if ($dependency == [1]) {
                            continue;
                        }

                        $rules[] = [
                            'source' => 'category',
                            'category' => $dependency,
                            'condition' => 'in_cart',
                            'inner_relation' => 'and',
                            'outer_relation' => 'or'
                        ];
                    }

                    if (!empty(array_filter($rules))) {
                        update_term_meta($termId, '_wcpw_availability_rules', array_filter($rules));
                    }

                    delete_term_meta($termId, '_wcpw_dependencies');
                }
            }
        }
    }
}

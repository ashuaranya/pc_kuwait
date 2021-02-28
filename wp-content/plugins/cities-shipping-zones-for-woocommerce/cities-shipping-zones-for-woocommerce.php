<?php

/**
 * Plugin Name: Cities Shipping Zones for WooCommerce
 * Plugin URI: https://en.condless.com/cities-shipping-zones-for-woocommerce/
 * Description: WooCommerce plugin for turning the state field into a dropdown city field. To be used as shipping zones.
 * Version: 1.0.7
 * Author: Condless
 * Author URI: https://www.condless.com/
 * Developer: Condless
 * Developer URI: https://www.condless.com/
 * Contributors: condless
 * Text Domain: cities-shipping-zones-for-woocommerce
 * Domain Path: /i18n/languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.2
 * Tested up to: 5.7
 * Requires PHP: 7.0
 * WC requires at least: 3.4
 * WC tested up to: 4.9
 */

/**
 * Exit if accessed directly
 */
defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || get_site_option('active_sitewide_plugins') && array_key_exists( 'woocommerce/woocommerce.php', get_site_option( 'active_sitewide_plugins' ) ) ) {

	/**
	 * Cities Shipping Zones for WooCommerce class.
	 */
	class WC_CSZ {

		/**
		 * Construct class
		 */
		public function __construct() {
			add_action( 'plugins_loaded', [ $this, 'init' ] );
		}

		/**
		 * WC init
		 */
		public function init() {
			$this->init_textdomain();
			$this->init_settings();
			if ( ! empty( get_option( 'wc_csz_countries_codes' ) ) ) {
				$this->init_places();
				$this->init_fields_values();
				$this->init_fields_titles();
				$this->init_reports();
				if ( 'yes' === get_option( 'wc_csz_shipping_distance_fee' ) ) {
					$this->init_shipping_distance_fee();
				}
				if ( 'yes' === get_option( 'wc_csz_product_distance_fee' ) ) {
					$this->init_product_distance_fee();
				}
			}
		}

		/**
		 * Loads text domain for internationalitation
		 */
		public function init_textdomain() {
			load_plugin_textdomain( 'cities-shipping-zones-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * WC settings init
		 */
		public function init_settings() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wc_update_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'wc_add_plugin_links' ], 10, 4 );
			add_filter( 'woocommerce_settings_tabs_array', [ $this, 'wc_add_settings_tab' ], 50 );
			add_action( 'woocommerce_settings_tabs_csz', [ $this, 'wc_settings_tab' ] );
			add_action( 'woocommerce_update_options_csz', [ $this, 'wc_update_settings' ] );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_countries_codes', [ $this, 'wc_sanitize_option_wc_csz_countries_codes' ], 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_populate_state', [ $this, 'wc_sanitize_option_wc_csz_populate_state' ], 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_new_state_field', [ $this, 'wc_sanitize_option_wc_csz_new_state_field' ], 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_shipping_distance_fee', [ $this, 'wc_sanitize_option_wc_csz_shipping_distance_fee' ], 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_product_distance_fee', [ $this, 'wc_sanitize_option_wc_csz_product_distance_fee' ], 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_csz_set_zone_locations', [ $this, 'wc_sanitize_option_wc_csz_set_zone_locations' ], 10, 3 );
			add_shortcode( 'csz_cities', [ $this, 'csz_cities_shortcode' ] );
			add_action( 'wp_ajax_csz_match_shipping_zone', [ $this, 'csz_match_shipping_zone' ] );
			add_action( 'wp_ajax_nopriv_csz_match_shipping_zone', [ $this, 'csz_match_shipping_zone' ] );
		}

		/**
		 * WC places init
		 */
		public function init_places() {
			add_filter( 'woocommerce_states', [ $this, 'wc_cities' ], 999 );
		}

		/**
		 * WC fields values init
		 */
		public function init_fields_values() {
			add_action( 'woocommerce_checkout_create_order', [ $this, 'wc_checkout_copy_state_city' ], 10, 2 );
			add_action( 'woocommerce_customer_save_address', [ $this, 'wc_customer_copy_state_city' ], 10, 2 );
		}

		/**
		 * WC fields titles init
		 */
		public function init_fields_titles() {
			add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );
			if ( 'yes' === get_option( 'wc_csz_city_shipping_calc' ) ) {
				add_filter( 'woocommerce_checkout_fields', [ $this, 'wc_fill_empty_fields' ] );
				add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );
			}
			add_filter( 'woocommerce_get_country_locale', [ $this, 'wc_locale_state_city' ], 999 );
			if ( 'yes' === get_option( 'wc_csz_new_state_field' ) ) {
				add_filter( 'woocommerce_shipping_calculator_enable_state', [ $this, 'wc_cart_state_filter' ], 999 );
				add_filter( 'woocommerce_default_address_fields', [ $this, 'wc_state_filter_field' ] );
				add_action( 'woocommerce_after_checkout_form', [ $this, 'wc_new_state_dropdown' ] );
				add_action( 'woocommerce_account_navigation', [ $this, 'wc_new_state_dropdown' ] );
			}
			if ( 'yes' !== get_option( 'wc_csz_populate_state' ) && 'yes' !== get_option( 'wc_csz_new_state_field' ) ) {
				add_filter( 'woocommerce_shipping_calculator_enable_state', [ $this, 'csz_shipping_calculator_custom_state' ], 999 );
				add_filter( 'woocommerce_checkout_fields', [ $this, 'csz_enable_custom_state' ] );
				add_action( 'woocommerce_after_checkout_validation', [ $this, 'csz_disable_state_validation' ], 10, 2 );
			}
		}

		/**
		 * WC reports init
		 */
		public function init_reports() {
			add_filter( 'woocommerce_admin_reports', [ $this, 'wc_admin_cities_report_orders_tab' ] );
			add_filter( 'manage_edit-shop_order_columns', [ $this, 'wc_add_custom_shop_order_column' ] );
			add_action( 'manage_shop_order_posts_custom_column', [ $this, 'wc_shop_order_column_meta_field_value' ] );
			add_filter( 'manage_edit-shop_order_sortable_columns', [ $this, 'wc_shop_order_column_meta_field_sortable' ] );
			add_action( 'pre_get_posts', [ $this, 'wc_shop_order_column_meta_field_sortable_orderby' ] );
			add_filter( 'woocommerce_shop_order_search_fields', [ $this, 'wc_shipping_city_searchable_field' ] );
		}

		/**
		 * WC shipping distance fee init
		 */
		public function init_shipping_distance_fee() {
			add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', [ $this, 'wc_flat_rate_distance_fee_field' ] );
			add_filter( 'woocommerce_package_rates', [ $this, 'wc_distance_fee_calc' ], 999, 2 );
		}

		/**
		 * WC product distance fee init
		 */
		public function init_product_distance_fee() {
			add_filter( 'woocommerce_product_data_tabs', [ $this, 'wc_product_distance_fee_tab' ] );
			add_action( 'woocommerce_product_data_panels', [ $this, 'wc_product_distance_fee_panel' ] );
			add_action( 'woocommerce_process_product_meta_simple', [ $this, 'wc_save_product_custom_fields' ] );
			add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'wc_custom_fields_display' ] );
			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'wc_validate_custom_field' ], 999, 3 );
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'wc_add_custom_field_item_data' ], 10, 4 );
			add_action( 'woocommerce_before_calculate_totals', [ $this, 'wc_before_calculate_totals' ], 999 );
			add_filter( 'woocommerce_cart_item_name', [ $this, 'wc_cart_item_name' ], 10, 3 );
			add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'wc_add_custom_data_to_order' ], 10, 4 );
			add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'wc_replacing_add_to_cart_button' ], 10, 2 );
		}

		/**
		 * Adds plugin links to the plugin menu
		 * @param mixed $links
		 * @return mixed
		 */
		public function wc_update_settings_link( $links ) {
			array_unshift( $links, '<a href=' . esc_url( add_query_arg( 'page', 'wc-settings&tab=csz', get_admin_url() . 'admin.php' ) ) . '>' . __( 'Settings' ) . '</a>' );
			return $links;
		}

		/**
		 * Adds plugin meta links to the plugin menu
		 * @param mixed $links_array
		 * @param mixed $plugin_file_name
		 * @param mixed $plugin_data
		 * @param mixed $status
		 * @return mixed
		 */
		public function wc_add_plugin_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
			if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
				$sub_domain = 'he_IL' === get_locale() ? 'www' : 'en';
				$links_array[] = "<a href=https://$sub_domain.condless.com/cities-shipping-zones-for-woocommerce/>" . __( 'Docs', 'woocommerce' ) . '</a>';
				$links_array[] = "<a href=https://$sub_domain.condless.com/contact/>" . _x( 'Contact', 'Theme starter content' ) . '</a>';
			}
			return $links_array;
		}

		/**
		 * Adds a new settings tab to the WooCommerce settings tabs array
		 * @param array $settings_tabs
		 * @return array
		 */
		public function wc_add_settings_tab( $settings_tabs ) {
			$settings_tabs['csz'] = _x( 'Cities Shipping Zones', 'plugin', 'cities-shipping-zones-for-woocommerce' );
			return $settings_tabs;
		}

		/**
		 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function
		 * @uses woocommerce_admin_fields()
		 * @uses self::wc_get_settings()
		 */
		public function wc_settings_tab() {
			woocommerce_admin_fields( self::wc_get_settings() );
		}

		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function
		 * @uses woocommerce_update_options()
		 * @uses self::wc_get_settings()
		 */
		public function wc_update_settings() {
			woocommerce_update_options( self::wc_get_settings() );
		}

		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function
		 * @return array Array of settings for @see woocommerce_admin_fields() function
		 */
		public function wc_get_settings() {
			$populate_option_default = ! empty( ( include WC()->plugin_path() . '/i18n/states.php' )[ WC()->countries->get_base_country() ] ) ? 'yes' : 'no';
			$zones = [];
			foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
				$zones[ $zone['zone_id'] ] = $zone['zone_name'];
			}
			$selected_countries = [];
			if ( get_option( 'wc_csz_countries_codes' ) ) {
				foreach ( get_option( 'wc_csz_countries_codes' ) as $country ) {
					$selected_countries[ $country ] = WC()->countries->countries[ $country ];
				}
			}
			$settings = [
				'location_section'	=> [
					'name'	=> _x( 'Countries to apply on', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'	=> 'title',
					'id'	=> 'wc_csz_location_section'
				],
				'countries_codes'	=> [
					'name'		=> __( 'Country / Region', 'woocommerce' ),
					'type'		=> 'multi_select_countries',
					'default'	=> WC()->countries->get_base_country(),
					'desc_tip'	=> _x( 'Select supported countries only.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . _x( 'To apply the plugin on the selected countries press:', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . __( 'Save changes', 'woocommerce' ),
					'id'		=> 'wc_csz_countries_codes'
				],
				'location_section_end'	=> [
					'type'	=> 'sectionend',
					'id'	=> 'wc_csz_location_section_end'
				],
				'options_section'	=> [
					'name'	=> __( 'Options' ),
					'type'	=> 'title',
					'id'	=> 'wc_csz_options_section'
				],
				'populate_state'	=> [
					'name'		=> __( 'State / County', 'woocommerce' ),
					'desc'		=> _x( 'Autofill the state in orders based on the selected city', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> $populate_option_default,
					'id'		=> 'wc_csz_populate_state'
				],
				'new_state_field'	=> [
					'name'		=> __( 'Filters', 'woocommerce' ),
					'desc'		=> _x( 'Display a State/County filter for the cities of the store country', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> 'no',
					'id'		=> 'wc_csz_new_state_field'
				],
				'checkout_restrict_states'	=> [
					'name'		=> __( 'Selling location(s)', 'woocommerce' ),
					'desc'		=> _x( 'Sell only to locations that was explicitly selected in shipping zone', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> 'no',
					'id'		=> 'wc_csz_checkout_restrict_states'
				],
				'city_shipping_calc'	=> [
					'name'		=> __( 'Calculations', 'woocommerce' ),
					'desc'		=> _x( 'Calculate shipping options by the selected city even before address is entered', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> 'no',
					'id'		=> 'wc_csz_city_shipping_calc'
				],
				'shipping_distance_fee'	=> [
					'name'		=> __( 'Shipping', 'woocommerce' ) . ' ' . _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'desc'		=> _x( 'Enable to be able to apply distance fee in flat rate shipping methods', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> 'no',
					'id'		=> 'wc_csz_shipping_distance_fee'
				],
				'product_distance_fee'	=> [
					'name'		=> __( 'Product', 'woocommerce' ) . ' ' . _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'desc'		=> _x( 'Enable to be able to apply distance fee in virtual products', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'type'		=> 'checkbox',
					'default'	=> 'no',
					'id'		=> 'wc_csz_product_distance_fee'
				],
				'options_section_end'	=> [
					'type'	=> 'sectionend',
					'id'	=> 'wc_csz_options_section_end'
				],
				'insert_section'	=> [
					'name'	=> __( 'Zone regions', 'woocommerce' ) . ' ' . __( 'Bulk Select' ),
					'type'	=> 'title',
					'desc'	=> _x( 'A tool for bulk insert locations into shipping zone', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'id'	=> 'wc_csz_insert_section'
				],
				'set_zone_locations'	=> [
					'name'		=> __( 'Zone regions', 'woocommerce' ),
					'type'		=> 'text',
					'desc_tip'	=> _x( 'States/Cities names (as they appear in the dashboard) or codes (as they appear in the plugin folder /i18n/cities/ path) sepreated by semi-colon (;).', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'id'		=> 'wc_csz_set_zone_locations'
				],
				'set_zone_country'	=> [
					'name'		=> __( 'Country / Region', 'woocommerce' ),
					'type'		=> 'select',
					'options'	=> $selected_countries,
					'desc_tip'	=> _x( 'Select the country that the locations belong to.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'id'		=> 'wc_csz_set_zone_country'
				],
				'set_zone_id'	=> [
					'name'		=> __( 'Shipping zone name.', 'woocommerce' ),
					'type'		=> 'select',
					'options'	=> $zones,
					'desc_tip'	=> _x( 'The locations of this shipping zone will be overridden.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					'id'		=> 'wc_csz_set_zone_id'
				],
				'insert_section_end'	=> [
					'type'	=> 'sectionend',
					'id'	=> 'wc_csz_insert_section_end'
				]
			];
			return apply_filters( 'wc_csz_settings', $settings );
		}

		/**
		 * Sanitizes the countries codes option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_countries_codes( $value, $option, $raw_value ) {
			$old_option = get_option( 'wc_csz_countries_codes' );
			foreach ( $value as $country_code ) {
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'i18n/cities/' . $country_code . '.php' ) ) {
					if ( ! $old_option || ! in_array( $country_code, $old_option ) ) {
						$added_countries[] = WC()->countries->countries[ $country_code ];
					}
					if ( in_array( $country_code, [ 'US', 'FR' ] ) && ! has_filter( 'csz_states' ) ) {
						WC_Admin_Settings::add_message( _x( 'You have selected a country with long cities list, which can cause a slow cities dropdown, contact your developer to minimize it using the csz_states filter.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . WC()->countries->countries[ $country_code ] );
					}
				} else {
					$value = array_diff( $value, [ $country_code ] );
					$unsupported_countries[] = WC()->countries->countries[ $country_code ];
				}
			}
			if ( ! empty( $added_countries ) ) {
				WC_Admin_Settings::add_message( implode( ', ', $added_countries ) . ': ' . _x( 'locations was added', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . '. '. __( 'Update', 'woocommerce' ) . ' ' . __( 'Shipping settings', 'woocommerce' ) . ': ' . __( 'Shipping zones', 'woocommerce' ) . ': ' . __( 'Zone regions', 'woocommerce' ) . '. ' . _x( 'Drag the relevant shipping zone to the top of the shiping zones list.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
			}
			if ( ! empty( $unsupported_countries ) ) {
				WC_Admin_Settings::add_message( _x( 'Contact to request new country/translation', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . '. ' . __( 'Unsupported' ) . ': ' . implode( ', ', $unsupported_countries ) );
			}
			if ( $old_option ) {
				foreach ( array_diff( $old_option, $value ) as $country_code ) {
					$removed_countries[] = WC()->countries->countries[ $country_code ];
				}
			}
			if ( ! empty( $removed_countries ) ) {
				WC_Admin_Settings::add_message( implode( ', ', $removed_countries ) . ': ' . _x( 'locations was removed', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . '. '. __( 'Update', 'woocommerce' ) . ' ' . __( 'Shipping settings', 'woocommerce' ) . ': ' . __( 'Shipping zones', 'woocommerce' ) . ': ' . __( 'Zone regions', 'woocommerce' ) );
			}
			$store_country = WC()->countries->get_base_country();
			if ( in_array( $store_country, $value ) && ( ! $old_option || $old_option && ! in_array( $store_country, $old_option ) ) ) {
				$cities = [];
				$country_states = '';
				include( 'i18n/cities/' . $store_country . '.php' );
				if ( $country_states ) {
					foreach ( apply_filters( 'csz_states', $country_states ) as $state_code => $state_name ) {
						if ( isset( $country_cities[ $state_code ] ) ) {
							$cities += $country_cities[ $state_code ];
						}
					}
				} else {
					$cities = $country_cities;
				}
				$new_store_city = array_search( WC()->countries->get_base_city(), $cities );
				if ( ! $new_store_city ) {
					$new_store_city = key( $cities );
					WC_Admin_Settings::add_message( __( 'Update', 'woocommerce' ) . ' ' . __( 'WooCommerce settings', 'woocommerce' ) . ': ' . __( 'Store Address', 'woocommerce' ) . ': ' . __( 'Country / State', 'woocommerce' ) );
				}
				update_option( 'woocommerce_default_country', $store_country . ':' . $new_store_city );
			} elseif ( $old_option && in_array( $store_country, $old_option ) && ! in_array( $store_country, $value ) ) {
				$org_states = include WC()->plugin_path() . '/i18n/states.php';
				if ( ! empty( $org_states[ $store_country ] ) ) {
					$first_state = key( $org_states[ $store_country ] );
					update_option( 'woocommerce_default_country', $store_country . ':' . $first_state );
					WC_Admin_Settings::add_message( __( 'Update', 'woocommerce' ) . ' ' . __( 'WooCommerce settings', 'woocommerce' ) . ': ' . __( 'Store Address', 'woocommerce' ) . ': ' . __( 'Country / State', 'woocommerce' ) );
				} else {
					update_option( 'woocommerce_default_country', $store_country );
				}
			}
			if ( ! $old_option && $value && function_exists( 'is_plugin_active' ) ) {
				if ( is_plugin_active( 'woo-checkout-field-editor-pro/checkout-form-designer.php' ) ) {
					WC_Admin_Settings::add_message( _x( 'Checkout Field Editor: Enable the billing/shipping country and state fields, modify the state field label to City.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
				if ( is_plugin_active( 'woocommerce-checkout-manager/woocommerce-checkout-manager.php' ) ) {
					WC_Admin_Settings::add_message( _x( 'Checkout Manager: Enable the billing/shipping country and state fields, clear the state field.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
				if ( is_plugin_active( 'yith-woocommerce-checkout-manager/init.php' ) ) {
					WC_Admin_Settings::add_message( _x( 'Checkout Manager: Enable the billing/shipping country and state fields, modify the state field label to City.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
				if ( is_plugin_active( 'flexible-checkout-fields/flexible-checkout-fields.php' ) ) {
					WC_Admin_Settings::add_message( _x( 'Flexible Checkout Fields: Enable the billing/shipping country and state fields, modify the state field label to City.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
			}
			return $value;
		}

		/**
		 * Sanitizes the populate state option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_populate_state( $value, $option, $raw_value ) {
			if ( 'no' === $value && isset( $_POST['wc_csz_countries_codes'] ) ) {
				if ( array_intersect( $_POST['wc_csz_countries_codes'], [ 'AR', 'BR', 'CA', 'CN', 'IN', 'ID', 'IT', 'JP', 'MX', 'TH', 'US' ] ) ) {
					$value = 'yes';
					WC_Admin_Settings::add_message( _x( 'The State Autofill option must be enabled for the countries you applied the plugin on', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				} elseif ( 'yes' === get_option( 'wc_csz_populate_state' ) ) {
					WC_Admin_Settings::add_message( _x( 'Verify that your integrated payment/shipping/invoicing/ERP software do not require a valid state field for the countries you apply the plugin on', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
			}
			return $value;
		}

		/**
		 * Sanitizes the filters option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_new_state_field( $value, $option, $raw_value ) {
			if ( 'yes' === $value ) {
				if ( empty( $_POST['wc_csz_countries_codes'] ) || ! in_array( WC()->countries->get_base_country(), $_POST['wc_csz_countries_codes'] ) ) {
					WC_Admin_Settings::add_message( _x( 'Filters: The store country was not selected', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . '. ' . WC()->countries->countries[ WC()->countries->get_base_country() ] );
					return 'no';
				} else {
					$country_states = '';
					include( 'i18n/cities/' . WC()->countries->get_base_country() . '.php' );
					if ( empty( $country_states ) ) {
						WC_Admin_Settings::add_message( _x( 'Filters: The store country does not support states, contact us to make it compatible.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
						return 'no';
					}
					if ( 'no' === get_option( 'wc_csz_new_state_field', 'no' ) && function_exists( 'is_plugin_active' ) && ( is_plugin_active( 'woocommerce-checkout-manager/woocommerce-checkout-manager.php' ) || is_plugin_active( 'woo-checkout-field-editor-pro/checkout-form-designer.php' ) ) ) {
						WC_Admin_Settings::add_message( _x( 'Filters: In case the filter does not appear try deactivate the checkout fields editor plugin', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
					}
				}
			}
			return $value;
		}

		/**
		 * Sanitizes the shipping distance fee option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_shipping_distance_fee( $value, $option, $raw_value ) {
			if ( 'yes' === $value ) {
				$value = 'no';
				$country_code = WC()->countries->get_base_country();
				if ( isset( $_POST['wc_csz_countries_codes'] ) && in_array( $country_code, $_POST['wc_csz_countries_codes'] ) && glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) ) {
					if ( 'no' === get_option( 'wc_csz_shipping_distance_fee' ) ) {
						WC_Admin_Settings::add_message( __( 'Update', 'woocommerce' ) . ' ' . __( 'Shipping settings', 'woocommerce' ) . ': ' . __( 'Shipping zones', 'woocommerce' ) . ': ' . __( 'Shipping methods', 'woocommerce' ) . ': ' . __( 'Flat rate', 'woocommerce' ) . ': ' . _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
					}
					return 'yes';
				}
				WC_Admin_Settings::add_message( _x( 'The store country was not selected or does not support shipping distance fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
			}
			return $value;
		}

		/**
		 * Sanitizes the product distance fee option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_product_distance_fee( $value, $option, $raw_value ) {
			if ( 'yes' === $value ) {
				$value = 'no';
				$country_code = WC()->countries->get_base_country();
				if ( isset( $_POST['wc_csz_countries_codes'] ) && in_array( $country_code, $_POST['wc_csz_countries_codes'] ) && glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) ) {
					if ( 'no' === get_option( 'wc_csz_product_distance_fee' ) ) {
						WC_Admin_Settings::add_message( __( 'Update', 'woocommerce' ) . ' ' . __( 'Virtual products', 'woocommerce' ) . ': ' . __( 'Tabbed Content', 'woocommerce' ). ': ' . _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
					}
					return 'yes';
				}
				WC_Admin_Settings::add_message( _x( 'The store country was not selected or does not support product distance fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
			}
			return $value;
		}

		/**
		 * Sanitizes the locations bulk edit option
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 */
		public function wc_sanitize_option_wc_csz_set_zone_locations( $value, $option, $raw_value ) {
			if ( ! empty( $value ) && isset( $_POST['wc_csz_countries_codes'] ) && isset( $_POST['wc_csz_set_zone_country'] ) && isset( $_POST['wc_csz_set_zone_id'] ) ) {
				include( 'i18n/cities/' . $_POST['wc_csz_set_zone_country'] . '.php' );
				$locations_codes = [];
				$unsupported_locations = [];
				$cities = WC()->countries->get_states( $_POST['wc_csz_set_zone_country'] );
				$locations = [];
				if ( '!' === substr( $value, 0, 1 ) ) {
					$limits = preg_split( '/\s*;\s*/', substr( $value, 1 ) );
					if ( file_exists( plugin_dir_path( __FILE__ ) . 'i18n/distances/' . $limits['0'] . '.php' ) ) {
						include( 'i18n/distances/' . $limits['0'] . '.php' );
						if ( isset( $limits['1'] ) && isset( $limits['2'] ) ) {
							foreach ( $cities_distance[ $limits['0'] ] as $city => $distance ) {
								if ( $distance > $limits['1'] && $distance < $limits['2'] ) {
									$locations[] = $city;
								}
							}
						} else {
							$locations = array_keys( $cities_distance[ $limits['0'] ] );
						}
					} else {
						$unsupported_locations[] = $value;
					}
				} else {
					$locations = preg_split( '/\s*;\s*/', $value, -1, PREG_SPLIT_NO_EMPTY );
				}
				foreach ( array_unique( $locations ) as $location ) {
					$city_code = isset( $cities[ $location ] ) ? $location : array_search( $location, $cities );
					if ( $city_code ) {
						$locations_codes[] = [ 'code' => $_POST['wc_csz_set_zone_country'] . ':' . $city_code, 'type' => 'state' ];
					} elseif ( null !== apply_filters( 'csz_states', $country_states ) ) {
						$state_code = isset( apply_filters( 'csz_states', $country_states )[ $location ] ) ? $location : array_search( $location, apply_filters( 'csz_states', $country_states ) );
						if ( $state_code ) {
							foreach ( $country_cities[ $state_code ] as $city_code => $city ) {
								$locations_codes[] = [ 'code' => $_POST['wc_csz_set_zone_country'] . ':' . $city_code, 'type' => 'state' ];
							}
						} else {
							$unsupported_locations[] = $location;
						}
					} else {
						$unsupported_locations[] = $location;
					}
				}
				if ( ! empty( $locations_codes ) ) {
					$zone = WC_Shipping_Zones::get_zone( $_POST['wc_csz_set_zone_id'] );
					$zone->set_locations( $locations_codes );
					$zone->save();
					WC_Admin_Settings::add_message( __( 'Shipping settings', 'woocommerce' ) . ': ' . __( 'Shipping zones', 'woocommerce' ) . ': ' . __( 'Zone regions', 'woocommerce' ) . '. ' . _x( 'locations was added', 'plugin', 'cities-shipping-zones-for-woocommerce' ) );
				}
				if ( ! empty( $unsupported_locations ) ) {
					WC_Admin_Settings::add_message( __( 'Unsupported' ) . ': ' . implode( ', ', $unsupported_locations ) );
				}
				$value = '';
			}
			return $value;
		}

		/**
		 * Adds cities shipping calculator shortcode
		 * @param mixed $atts
		 * @return mixed
		 */
		public function csz_cities_shortcode( $atts ) {
			$atts = shortcode_atts( [
				'international'	=> array_keys( WC()->countries->get_shipping_countries() ) === [ WC()->countries->get_base_country() ] ? false : true,
				'country'	=> ! is_admin() && WC()->customer->get_shipping_country() ? WC()->customer->get_shipping_country() : WC()->countries->get_base_country(),
				'description'	=> __( 'Enter your address to view shipping options.', 'woocommerce' ),
				'class'		=> [ 'form-row', 'address-field', 'form-row-first' ],
				'label'		=> __( 'City', 'woocommerce' ),
				'value'		=> ! is_admin() ? WC()->customer->get_shipping_state() : '',
				'country_description'	=> '',
				'country_class'		=> '',
				'country_label'		=> __( 'Country / Region', 'woocommerce' ),
			], $atts, 'csz_cities' );
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'wc-country-select' );
			add_action( 'wp_footer', [ $this, 'csz_match_javascript' ] );
			$state_args = apply_filters( 'csz_cities_shortcode_state_args', [
				'type'		=> 'state',
				'required'	=> true,
				'label'		=> $atts['label'],
				'class'		=> $atts['class'],
				'description'	=> ! empty( $atts['description'] ) ? $atts['description'] : ' ',
				'country'	=> $atts['international'] ? $atts['country'] : WC()->countries->get_base_country(),
			] );
			ob_start();
			if ( $atts['international'] ) {
				$country_args = apply_filters( 'csz_cities_shortcode_country_args', [
					'type'		=> 'country',
					'required'	=> true,
					'label'		=> $atts['country_label'],
					'class'		=> ! empty( $atts['country_class'] ) ? $atts['country_class'] : $atts['class'],
					'description'	=> ! empty( $atts['country_description'] ) ? $atts['country_description'] : ' ',
				] );
				woocommerce_form_field( 'shipping_country', $country_args, $atts['country'] );
			}
			if ( 'yes' === get_option( 'wc_csz_new_state_field' ) && in_array( WC()->countries->get_base_country(), get_option( 'wc_csz_countries_codes' ) ) ) {
				$country_states = [];
				include( 'i18n/cities/' . WC()->countries->get_base_country() . '.php' );
				$country_states = apply_filters( 'csz_states', $country_states );
				asort( $country_states );
				woocommerce_form_field( 'shipping_new_state', apply_filters( 'csz_cities_shortcode_state_filter_args', [
					'type'		=> 'select',
					'options'	=> [ '' => '' ] + $country_states,
					'class'		=> $atts['class'],
					'input_class'	=> [ 'state_select' ],
					'label'		=> __( 'State / County', 'woocommerce' ),
				] ) );
			}
			woocommerce_form_field( 'shipping_state', $state_args, $atts['value'] );
			return ob_get_clean();
		}

		/**
		 * Matches shipping zone by location
		 * @return mixed
		 */
		public function csz_match_shipping_zone() {
			$output = '';
			$methods_titles = [];
			$methods_ids = [];
			$only_pickup = true;
			$country = wc_clean( $_POST['country'] );
			$state = wc_clean( $_POST['state'] );
			$shipping_methods = wc_get_shipping_zone( [ 'destination' => [ 'country' => $country, 'state' => $state, 'postcode' => '' ] ] )->get_shipping_methods( true );
			foreach ( $shipping_methods as $shipping_method ) {
				$price = $shipping_method->cost ? ': ' . $shipping_method->cost . ' ' . html_entity_decode( get_woocommerce_currency_symbol() ) : '';
				$methods_formatted[] = $shipping_method->title . $price;
				$methods_id[] = $shipping_method->id;
			}
			if ( ! empty( $shipping_methods ) ) {
				if ( ! in_array( 'local_pickup', $methods_ids ) ) {
					$output = __( 'Shipping methods', 'woocommerce' ) . ': ' . implode( ', ', $methods_formatted );
					WC()->customer->set_billing_state( $state );
					WC()->customer->set_shipping_state( $state );
					WC()->customer->set_billing_country( $country );
					WC()->customer->set_shipping_country( $country );
				} else {
					$output = implode( ', ', $methods_formatted );
					WC()->customer->set_billing_state( $state );
					WC()->customer->set_billing_country( $country );
				}
			} else {
				$output = __( 'No shipping methods offered to this zone.', 'woocommerce' );
			}
			wp_send_json( apply_filters( 'csz_cities_shortcode_match_zone', esc_html( $output ), $shipping_methods ) );
		}

		/**
		 * Calls the shipping zone match ajax and displays results
		 */
		public function csz_match_javascript() { ?>
			<script type="text/javascript">
			jQuery( function( $ ) {
				if ( $( '#shipping_country_field' ).length ) {
					show_hide_state();
				} else if ( $( '#shipping_state option:selected' ).val() != '' ) {
					match_shipping_zone();
				}
				$( '#shipping_country' ).on( 'select2:select', function() { show_hide_state(); } );
				$( '#shipping_state' ).on( 'select2:select', function() { match_shipping_zone(); } );
				function show_hide_state() {
					if ( $( '#shipping_country option:selected' ).val() in <?php echo json_encode( WC()->countries->get_states() ); ?> ) {
						$( '#shipping_state_field' ).show();
						$( '#shipping_state' ).on( 'select2:select', function() { match_shipping_zone(); } );
						if ( $( '#shipping_state option:selected' ).val() != '' ) {
							match_shipping_zone();
						}
					} else {
						$( '#shipping_state_field' ).hide();
						if ( $( '#shipping_country option:selected' ).val() != '' ) {
							match_shipping_zone();
						}
					}
				}
				function match_shipping_zone() {
					var data = {
						'action'	: 'csz_match_shipping_zone',
						'country'	: $( '#shipping_country_field' ).is( ':visible' ) ? $( '#shipping_country option:selected' ).val() : '<?php echo WC()->countries->get_base_country(); ?>',
						'state'		: $( '#shipping_state_field' ).is( ':visible' ) ? $( '#shipping_state option:selected' ).val() : '',
					};
					$.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function( response ) {
						$( document ).triggerHandler( 'zone_matched', response );
					} );
					$( document ).on( 'zone_matched', function( event, response ) {
						if ( $( '#shipping_state_field' ).is( ':visible' ) ) {
							$( '#shipping_country-description' ).text( '' );
							$( '#shipping_state-description' ).text( response );
							$( '#shipping_state-description' ).show();
						} else {
							$( '#shipping_country-description' ).text( response );
							$( '#shipping_country-description' ).show();
						}
					} );
				}
				<?php if ( 'yes' === get_option( 'wc_csz_new_state_field' ) ) : ?>
					var store_country = '<?php echo WC()->countries->get_base_country(); ?>';
					$( document ).ready( function() { new_state_visibility() } );
					$( '#shipping_country' ).on( 'select2:select', function() { new_state_visibility() } );
					$( '#shipping_state' ).on( 'select2:open', function() { filter_states() } ).on( 'select2:select', function() { state_update() } );
					function new_state_visibility() {
						if ( ! $( '#shipping_country' ).length || $( '#shipping_country option:selected' ).val() == store_country || $( '#shipping_country' ).val() == store_country ) {
							$( '#shipping_new_state_field' ).show();
							$( '#shipping_state' ).on( 'select2:open', function() { filter_states() } ).on( 'select2:select', function() { state_update() } );
						} else {
							$( '#shipping_new_state_field' ).hide();
						}
					}
					function filter_states() {
						if ( ! $( '#shipping_country' ).length || $( '#shipping_country option:selected' ).val() == store_country || $( '#shipping_country' ).val() == store_country ) {
							$( '#shipping_state' ).data( 'select2' ).dropdown.$search.val( $( '#shipping_new_state option:selected' ).text() + ' - ' ).trigger( 'input' ).trigger( 'query', { term } );
						}
					}
					function state_update() {
						$( '#shipping_new_state option' ).filter( function() { return $( this ).text() == $( '#shipping_state option:selected' ).text().split( ' - ' )[0]; } ).prop( 'selected', true ).trigger( 'change' );
					}
				<?php endif; ?>
			} );
			</script>
		<?php }

		/**
		 * Loads countries cities
		 * @param mixed $states
		 * @return mixed
		 */
		public function wc_cities( $states ) {
			if ( ! is_wc_endpoint_url( 'order-received' ) && ! ( is_admin() && function_exists( 'get_current_screen' ) && isset( get_current_screen()->post_type ) && 'shop_order' === get_current_screen()->post_type ) && apply_filters( 'csz_enable_cities', true ) ) {
				foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
					$states[ $country_code ] = [];
					$country_cities = $country_states = '';
					include( 'i18n/cities/' . $country_code . '.php' );
					if ( $country_states ) {
						foreach ( apply_filters( 'csz_states', $country_states ) as $state_code => $state_name ) {
							if ( isset( $country_cities[ $state_code ] ) ) {
								if ( ( ! is_admin() || apply_filters( 'csz_admin_city_state_prefix', false ) ) && 'yes' === get_option( 'wc_csz_new_state_field' ) && $country_code === WC()->countries->get_base_country() ) {
									foreach ( $country_cities[ $state_code ] as $city_code => $city_name ) {
										$country_cities[ $state_code ][ $city_code ] = $country_states[ $state_code ] . ' - ' . $city_name;
									}
								}
								$states[ $country_code ] += $country_cities[ $state_code ];
							}
						}
					} else {
						$states[ $country_code ] = $country_cities;
					}
				}
				if ( ! is_admin() && 'yes' === get_option( 'wc_csz_checkout_restrict_states' ) ) {
					$selected_states = [];
					for ( $i = 1; $i < 100; $i++ ) {
						if ( WC_Shipping_Zones::get_zone( $i ) ) {
							foreach ( WC_Shipping_Zones::get_zone( $i )->get_zone_locations() as $zone_location ) {
								switch ( $zone_location->type ) {
									case 'country':
										$selected_states[ $zone_location->code ] = $states[ $zone_location->code ];
										break;
									case 'state':
										$country = substr( $zone_location->code, 0, 2 );
										$city = substr( $zone_location->code, 3 );
										$selected_states[ $country ][ $city ] = $states[ $country ][ $city ];
										break;
								}
							}
						}
					}
					foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
						if ( isset( $selected_states[ $country_code ] ) ) {
							$states[ $country_code ] = $selected_states[ $country_code ];
						}
					}
				}
				$states = apply_filters( 'csz_cities', $states );
				foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
					if ( isset( $states[ $country_code ] ) ) {
						asort( $states[ $country_code ] );
					}
				}
			}
			return $states;
		}

		/**
		 * Sets the order city and state fields based on selection on checkout
		 * @param mixed $order
		 * @param mixed $data
		 */
		public function wc_checkout_copy_state_city( $order, $data ) {
			if ( in_array( $data['billing_country'], get_option( 'wc_csz_countries_codes' ) ) ) {
				$city_value = WC()->countries->get_states( $data['billing_country'] )[ $data['billing_state'] ];
				$city_full_name = $city_value ? $city_value : $data['billing_state'];
				$billing_city = 'yes' !== get_option( 'wc_csz_new_state_field' ) || $data['billing_country'] !== WC()->countries->get_base_country() ? $city_full_name : explode( ' - ', $city_full_name, 2 )['1'];
				$order->set_billing_city( $billing_city );
				$order->set_billing_state( '' );
				update_user_meta( $order->get_user_id(), 'billing_city', $billing_city );
				if ( 'yes' === apply_filters( 'csz_populate_state', get_option( 'wc_csz_populate_state' ) ) ) {
					$billing_state_name = $country_states = '';
					include( 'i18n/cities/' . $data['billing_country'] . '.php' );
					foreach ( apply_filters( 'csz_states', $country_states ) as $state_code => $state ) {
						if ( isset( $country_cities[ $state_code ][ $data['billing_state'] ] ) ) {
							if ( isset( ( include WC()->plugin_path() . '/i18n/states.php' )[ $data['billing_country'] ][ $state_code ] ) ) {
								$order->set_billing_state( $state_code );
							} else {
								$order->set_billing_state( $state );
							}
							break;
						}
					}
				}
			}
			if ( in_array( $data['shipping_country'], get_option( 'wc_csz_countries_codes' ) ) ) {
				$city_value = WC()->countries->get_states( $data['shipping_country'] )[ $data['shipping_state'] ];
				$city_full_name = $city_value ? $city_value : $data['shipping_state'];
				$shipping_city = 'yes' !== get_option( 'wc_csz_new_state_field' ) || $data['shipping_country'] !== WC()->countries->get_base_country() ? $city_full_name : explode( ' - ', $city_full_name, 2 )['1'];
				$order->set_shipping_city( $shipping_city );
				$order->set_shipping_state( '' );
				update_user_meta( $order->get_user_id(), 'shipping_city', $shipping_city );
				if ( 'yes' === apply_filters( 'csz_populate_state', get_option( 'wc_csz_populate_state' ) ) ) {
					$shipping_state_name = $country_states = '';
					include( 'i18n/cities/' . $data['shipping_country'] . '.php' );
					foreach ( apply_filters( 'csz_states', $country_states ) as $state_code => $state ) {
						if ( isset( $country_cities[ $state_code ][ $data['shipping_state'] ] ) ) {
							if ( isset( ( include WC()->plugin_path() . '/i18n/states.php' )[ $data['shipping_country'] ][ $state_code ] ) ) {
								$order->set_shipping_state( $state_code );
							} else {
								$order->set_shipping_state( $state );
							}
							break;
						}
					}
				}
			}
			if ( 'yes' === get_option( 'wc_csz_new_state_field' ) ) {
				if ( isset( $data['billing_new_state'] ) ) {
					$order->delete_meta_data( '_billing_new_state' );
				}
				if ( isset( $data['shipping_new_state'] ) ) {
					$order->delete_meta_data( '_shipping_new_state' );
				}
			}
		}

		/**
		 * Copies from the state field to the city field on account page
		 * @param mixed $user_id
		 * @param mixed $load_address
		 */
		public function wc_customer_copy_state_city( $user_id, $load_address ) {
			$customer_country_code = get_user_meta( $user_id, $load_address . '_country', true );
			if ( in_array( $customer_country_code, get_option( 'wc_csz_countries_codes' ) ) ) {
				if ( 'yes' !== get_option( 'wc_csz_new_state_field' ) || $customer_country_code !== WC()->countries->get_base_country() ) {
					update_user_meta( $user_id, $load_address . '_city', WC()->countries->get_states( $customer_country_code )[ get_user_meta( $user_id, $load_address . '_state', true ) ] );
				} else {
					update_user_meta( $user_id, $load_address . '_city', explode( ' - ', WC()->countries->get_states( $customer_country_code )[ get_user_meta( $user_id, $load_address . '_state', true ) ], 2 )['1'] );
				}
			}
		}

		/**
		 * Inserts whitespace as default values of fields to trigger the city shipping calculations
		 * @param mixed $fields
		 * @return mixed
		 */
		public function wc_fill_empty_fields( $fields ) {
			if ( true === $fields['billing']['billing_address_1']['required'] ) {
				$fields['billing']['billing_address_1']['default'] = '&nbsp;';
			}
			if ( true === $fields['billing']['billing_postcode']['required'] ) {
				$fields['billing']['billing_postcode']['default'] = '&nbsp;';
			}
			if ( true === $fields['shipping']['shipping_address_1']['required'] ) {
				$fields['shipping']['shipping_address_1']['default'] = '&nbsp;';
			}
			if ( true === $fields['shipping']['shipping_postcode']['required'] ) {
				$fields['shipping']['shipping_postcode']['default'] = '&nbsp;';
			}
			return $fields;
		}

		/**
		 * Changes fields variables by locale
		 * @param mixed $locale
		 * @return mixed
		 */
		public function wc_locale_state_city( $locale ) {
			foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
				$locale[ $country_code ]['state']['required'] = $locale[ $country_code ]['city']['hidden'] = true;
				$locale[ $country_code ]['state']['label'] = __( 'City', 'woocommerce' );
				$locale[ $country_code ]['city']['required'] = false;
				if ( 'yes' === get_option( 'wc_csz_city_shipping_calc' ) ) {
					$locale[ $country_code ]['state']['priority'] = 45;
				}
			}
			return $locale;
		}

		/**
		 * Adds state filter into the shipping calculator
		 * @param bool $state_enabled
		 * @return bool
		 */
		public function wc_cart_state_filter( $state_enabled ) {
			if ( $state_enabled && in_array( WC()->countries->get_base_country(), get_option( 'wc_csz_countries_codes' ) ) ) {
				$country_states = [];
				include( 'i18n/cities/' . WC()->countries->get_base_country() . '.php' );
				$country_states = apply_filters( 'csz_states', $country_states );
				asort( $country_states );
				woocommerce_form_field( 'calc_shipping_new_state', apply_filters( 'csz_shipping_calculator_state_filter_args', [
					'type'		=> 'select',
					'options'	=> [ '' => '' ] + $country_states,
					'class'		=> [ 'form-row-wide', 'address-field' ],
					'input_class'	=> [ 'state_select' ],
					'placeholder'	=> __( 'State / County', 'woocommerce' ),
				] ) ); ?>
				<script type="text/javascript">
				jQuery( function( $ ) {
					var country = '<?php echo WC()->countries->get_base_country(); ?>';
					$( document ).ready( function() { new_state_visibility() } );
					$( document ).on( 'click', '.shipping-calculator-button', function() {
						$( '#calc_shipping_country' ).on( 'select2:select', function() { new_state_visibility() } );
						$( '#calc_shipping_state' ).on( 'select2:open', function() { filter_states() } ).on( 'select2:select', function() { state_update() } );
					} );
					function new_state_visibility() {
						if ( $( '#calc_shipping_country option:selected' ).val() == country || $( '#calc_shipping_country' ).val() == country ) {
							$( '#calc_shipping_new_state_field' ).show();
							$( '#calc_shipping_state' ).on( 'select2:open', function() { filter_states() } ).on( 'select2:select', function() { state_update() } );
						} else {
							$( '#calc_shipping_new_state_field' ).hide();
						}
					}
					function filter_states() {
						if ( $( '#calc_shipping_country option:selected' ).val() == country || $( '#calc_shipping_country' ).val() == country ) {
							$( '#calc_shipping_state' ).data( 'select2' ).dropdown.$search.val( $( '#calc_shipping_new_state option:selected' ).text() + ' - ' ).trigger( 'input' ).trigger( 'query', { term } );
						}
					}
					function state_update() {
						$( '#calc_shipping_new_state option' ).filter( function() { return $( this ).text() == $( '#calc_shipping_state option:selected' ).text().split( ' - ' )[0]; } ).prop( 'selected', true ).trigger( 'change' );
					}
				} );
				</script>
			<?php }
			return $state_enabled;
		}

		/**
		 * Adds state filter field
		 * @param mixed $fields
		 * @return mixed
		 */
		function wc_state_filter_field( $fields ) {
			if ( is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {
				$country_states = [];
				include( 'i18n/cities/' . WC()->countries->get_base_country() . '.php' );
				$country_states = apply_filters( 'csz_states', $country_states );
				asort( $country_states );
				$country_states = [ '' => '' ] + $country_states;
				$fields['new_state'] = [
					'label'		=> __( 'State / County', 'woocommerce' ),
					'type'		=> 'select',
					'options'	=> $country_states,
					'priority'	=> $fields['state']['priority'] - 3,
					'input_class'	=> [ 'state_select' ],
				];
				if ( 'yes' === get_option( 'wc_csz_city_shipping_calc' ) ) {
					$fields['new_state']['priority'] = $fields['country']['priority'] + 2;
				}
			}
			return $fields;
		}

		/**
		 * Filters the cities by the selected state
		 */
		public function wc_new_state_dropdown( ) {
			if ( in_array( WC()->countries->get_base_country(), get_option( 'wc_csz_countries_codes' ) ) ) { ?>
				<script type="text/javascript">
				jQuery( function( $ ) {
					var country = '<?php echo WC()->countries->get_base_country(); ?>';
					$( '#billing_state' ).on( 'select2:open', function() { billing_filter_states() } ).on( 'select2:select', function() { billing_state_update() } );
					$( '#shipping_state' ).on( 'select2:open', function() { shipping_filter_states() } ).on( 'select2:select', function() { shipping_state_update() } );
					if ( <?php echo json_encode( is_checkout() ); ?> ) {
						$( 'body' ).on( 'updated_checkout', function() { check_country() } );
					} else {
						check_country();
						$( '#billing_country, #shipping_country' ).on( 'select2:select', function() { check_country() } );
					}
					function check_country() {
						if ( $( '#billing_country option:selected' ).val() == country || $( '#billing_country' ).val() == country ) {
							$( '#billing_new_state_field' ).show();
							$( '#billing_state' ).on( 'select2:open', function() { billing_filter_states() } ).on( 'select2:select', function() { billing_state_update() } );
						} else {
							$( '#billing_new_state_field' ).hide();
						}
						if ( $( '#shipping_country option:selected' ).val() == country || $( '#shipping_country' ).val() == country ) {
							$( '#shipping_new_state_field' ).show();
							$( '#shipping_state' ).on( 'select2:open', function() { shipping_filter_states() } ).on( 'select2:select', function() { shipping_state_update() } );
						} else {
							$( '#shipping_new_state_field' ).hide();
						}
					}
					function billing_filter_states() {
						if ( $( '#billing_country option:selected' ).val() == country || $( '#billing_country' ).val() == country ) {
							$( '#billing_state' ).data( 'select2' ).dropdown.$search.val( $( '#billing_new_state option:selected' ).text() + ' - ' ).trigger( 'input' ).trigger( 'query', { term } );
						}
					}
					function shipping_filter_states() {
						if ( $( '#shipping_country option:selected' ).val() == country || $( '#shipping_country' ).val() == country ) {
							$( '#shipping_state' ).data( 'select2' ).dropdown.$search.val( $( '#shipping_new_state option:selected' ).text() + ' - ' ).trigger( 'input' ).trigger( 'query', { term } );
						}
					}
					function billing_state_update() {
						$( '#billing_new_state option' ).filter( function() { return $( this ).text() == $( '#billing_state option:selected' ).text().split( ' - ' )[0]; } ).prop( 'selected', true ).trigger( 'change' );
					}
					function shipping_state_update() {
						$( '#shipping_new_state option' ).filter( function() { return $( this ).text() == $( '#shipping_state option:selected' ).text().split( ' - ' )[0]; } ).prop( 'selected', true ).trigger( 'change' );
					}
				} );
				</script>
			<?php }
		}

		/**
		 * Allows custom city in the cart shipping calculator
		 * @param bool $state_enabled
		 * @return bool
		 */
		public function csz_shipping_calculator_custom_state( $state_enabled ) {
			if ( apply_filters( 'csz_enable_custom_city', false ) && $state_enabled ) { ?>
				<script type="text/javascript">
				jQuery( function( $ ) {
					var countries = <?php echo json_encode( apply_filters( 'csz_custom_cities_countries', get_option( 'wc_csz_countries_codes' ) ) ); ?>;
					if ( $.inArray( $( '#calc_shipping_country option:selected' ).val(), countries ) > -1 ) {
						$( '#calc_shipping_country' ).val( 'default' ).trigger( 'change' );
					}
					$( '#calc_shipping_country' ).on( 'select2:select', function() {
						if ( $.inArray( $( '#calc_shipping_country option:selected' ).val(), countries ) > -1 ) {
							$( '#calc_shipping_state' ).select2( { tags: true } );
						} else if ( $( '#calc_shipping_state' ).hasClass( 'select2-hidden-accessible' ) ) {
							$( '#calc_shipping_state' ).select2( { tags: false } );
						}
					} );
				} );
				</script>
			<?php }
			return $state_enabled;
		}

		/**
		 * Allows custom city in the checkout
		 * @param mixed $fields
		 * @return mixed
		 */
		public function csz_enable_custom_state( $fields ) {
			if ( apply_filters( 'csz_enable_custom_city', false ) ) {
				$fields['billing']['billing_state']['description'] = $fields['shipping']['shipping_state']['description'] = _x( 'If your city is not present write its name and select it', 'plugin' ,'cities-shipping-zones-for-woocommerce' ); ?>
				<script type="text/javascript">
				jQuery( function( $ ) {
					var city_added = false, countries = <?php echo json_encode( apply_filters( 'csz_custom_cities_countries', get_option( 'wc_csz_countries_codes' ) ) ); ?>;
					$( 'body' ).on( 'updated_checkout', function() {
						if ( $.inArray( $( '#billing_country option:selected' ).val(), countries ) > -1 || $.inArray( $( '#billing_country' ).val(), countries ) > -1 ) {
							$( '#billing_state' ).select2( { tags: true } );
							if ( ! city_added ) {
								city_added = true;
								<?php $shipping_country = WC()->checkout->get_value( 'shipping_country' );
								$shipping_state = WC()->checkout->get_value( 'shipping_state' );
								if ( in_array( $shipping_country, apply_filters( 'csz_custom_cities_countries', get_option( 'wc_csz_countries_codes' ) ) ) && ! empty( $shipping_state ) && ! isset( WC()->countries->get_states( $shipping_country )[ $shipping_state ] ) ) : ?>
								var sc = '<?php echo $shipping_country; ?>', ss = '<?php echo $shipping_state; ?>';
								if ( $( '#billing_country option:selected' ).val() == sc || $( '#billing_country' ).val() == sc ) {
									$( '#billing_state' ).append( new Option( ss, ss, false, false ) );
									if ( $( '#billing_state option:selected' ).val() == '' ) {
										$( '#billing_state' ).val( ss ).trigger( 'change' );
									}
								}
								<?php endif; ?>
							}
						} else if ( $( '#billing_state' ).hasClass( 'select2-hidden-accessible' ) ) {
							$( '#billing_state' ).select2( { tags: false } );
						}
						if ( $.inArray( $( '#shipping_country option:selected' ).val(), countries ) > -1 || $.inArray( $( '#shipping_country' ).val(), countries ) > -1 ) {
							$( '#shipping_state' ).select2( { tags: true } );
						} else if ( $( '#shipping_state' ).hasClass( 'select2-hidden-accessible' ) ) {
							$( '#shipping_state' ).select2( { tags: false } );
						}
					} );
				} );
				</script>
			<?php }
			return $fields;
		}

		/**
		 * Disables State validation to allow custom city
		 * @param mixed $fields
		 * @param mixed $errors
		 */
		public function csz_disable_state_validation( $fields, $errors ) {
			if ( apply_filters( 'csz_enable_custom_city', false ) ) {
				if ( in_array( $fields['billing_country'], apply_filters( 'csz_custom_cities_countries', get_option( 'wc_csz_countries_codes' ) ) ) ) {
					$errors->remove( 'billing_state_validation' );
				}
				if ( in_array( $fields['shipping_country'], apply_filters( 'csz_custom_cities_countries', get_option( 'wc_csz_countries_codes' ) ) ) ) {
					$errors->remove( 'shipping_state_validation' );
				}
			}
		}

		/**
		 * Adds cities total sales report tab
		 * @param mixed $reports
		 * @return mixed
		 */
		public function wc_admin_cities_report_orders_tab( $reports ) {
			if ( isset( $reports['orders']['reports'] ) ) {
				$city_tab = [
					'sales_by_city' => [
						'title'		=> _x( 'Sales by city', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
						'description'	=> date( 'Y' ) . ': ' . __( 'Total Sales', 'woocommerce' ),
						'hide_title'	=> true,
						'callback'	=> [ $this, 'wc_sales_by_city' ],
					]
				];
				$reports['orders']['reports'] = array_merge( $reports['orders']['reports'], $city_tab );
			}
			return $reports;
		}

		/**
		 * Calculates cities total sales
		 */
		public function wc_sales_by_city() {
			$orders = wc_get_orders( [
				'limit' => -1,
				'type' => 'shop_order',
				'status' => 'completed',
				'date_completed' => '>' . date( 'Y' ) . '-01-01',
			] );
			$totals = [];
			foreach ( $orders as $order ) {
				if ( ! empty( $order->get_billing_country() ) && 0 < $order->get_total() ) {
					$order_country = $order->get_billing_country();
					$order_city = ! empty( $order->get_billing_city() ) ? $order->get_billing_city() : 'None';
					if ( isset( $totals[ $order_country ][ $order_city ] ) ) {
						$totals[ $order_country ][ $order_city ] += $order->get_total();
					} else {
						$totals[ $order_country ][ $order_city ] = $order->get_total();
					}
				}
			}
			foreach ( $totals as $country => $country_total ) {
				echo '<table><h3>' . esc_html__( 'Country / Region', 'woocommerce' ) . ': ' . WC()->countries->countries[ $country ] . '</h3>';
				echo '<tr><th>' . esc_html__( 'City', 'woocommerce' ) . '</th><td>' . esc_html__( 'Total', 'woocommerce' ) . '</tr>';
				arsort( $country_total );
				foreach ( $country_total as $city => $city_total ) {
					echo '<tr><th>' . esc_html( $city ) . '</th><td>' . wc_price( $city_total ) . '</tr>';
				}
				echo '</table><br>';
			}
		}

		/**
		 * Adds column of shipping city
		 * @param mixed $columns
		 * @return mixed
		 */
		public function wc_add_custom_shop_order_column( $columns ) {
			$order_total = $columns['order_total'];
			$wc_actions = $columns['wc_actions'];
			unset( $columns['wc_actions'] );
			unset( $columns['order_total'] );
			$columns['shipping_city'] = __( 'Shipping City', 'woocommerce' );
			$columns['order_total'] = $order_total;
			$columns['wc_actions'] = $wc_actions;
			return $columns;
		}

		/**
		 * Sets the shipping city column value
		 * @param mixed $column
		 */
		public function wc_shop_order_column_meta_field_value( $column ) {
			if ( 'shipping_city' === $column ) {
				global $post, $the_order;
				if ( ! is_a( $the_order, 'WC_Order' ) ) {
					$the_order = wc_get_order( $post->ID );
				}
				echo $the_order->get_shipping_city();
			}
		}

		/**
		 * Makes shipping city column sortable
		 * @param mixed $columns
		 * @return mixed
		 */
		public function wc_shop_order_column_meta_field_sortable( $columns ) {
			return wp_parse_args( [ 'shipping_city' => '_shipping_city' ], $columns );
		}

		/**
		 * Defines the shipping city sort values
		 * @param mixed $query
		 */
		public function wc_shop_order_column_meta_field_sortable_orderby( $query ) {
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
				if ( isset( $screen->id ) && 'shop_order' === $screen->id && 'edit.php' === $screen->parent_file ) {
					$orderby = $query->get( 'orderby' );
					$meta_key = '_shipping_city';
					if ( '_shipping_city' === $orderby ) {
						$query->set( 'meta_key', $meta_key );
						$query->set( 'orderby', 'meta_value' );
					}
				}
			}
		}

		/**
		 * Adds Distance Fee option to the shipping method instances
		 * @param mixed $settings
		 * @return mixed
		 */
		public function wc_shipping_city_searchable_field( $meta_keys ){
			$meta_keys[] = '_shipping_city';
			return $meta_keys;
		}

		/**
		 * Adds Distance Fee option to the shipping method instances
		 * @param mixed $settings
		 * @return mixed
		 */
		public function wc_flat_rate_distance_fee_field( $settings ) {
			foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
				$src_cities = [];
				$default_src_city = $country_states = '';
				include( 'i18n/cities/' . $country_code . '.php' );
				if ( $country_code === WC()->countries->get_base_country() ) {
					foreach ( glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) as $file ) {
						$city_code = basename( $file, '.php' );
						if ( isset( WC()->countries->get_states( $country_code )[ $city_code ] ) ) {
							include $file;
							if ( ! isset( $key_format[ $city_code ] ) || substr( $key_format[ $city_code ], 0, 2 ) === substr( get_locale(), 0, 2 ) ) {
								$src_cities[ $city_code ] = WC()->countries->get_states( $country_code )[ $city_code ];
							}
						}
					}
				}
			}
			if ( isset( $cities_distance ) ) {
				if ( isset( $cities_distance[ WC()->countries->get_base_state() ] ) ) {
					$default_src_city = WC()->countries->get_base_state();
				} else {
					$store_city_code = WC()->countries->get_base_state();
					foreach ( $cities_distance as $key => $value ) {
						if ( ! isset( $default_src_city ) ) {
							$default_src_city = $key;
						}
						$store_city = ! isset( $key_format[ $key ] ) ? $store_city_code : WC()->countries->get_states( WC()->countries->get_base_country() )[ $store_city_code ];
						if ( isset( $cities_distance[ $key ][ $store_city ] ) && ( ! isset( $cities_distance[ $default_src_city ][ $store_city ] ) || $cities_distance[ $key ][ $store_city ] < $cities_distance[ $default_src_city ][ $store_city ] ) ) {
							$default_src_city = $key;
						}
					}
				}
			}
			$arr = [];
			foreach ( $settings as $key => $value ) {
				if ( 'cost' === $key ) {
					$arr[ $key ] = $value;
					$arr['distance_fee'] = [
						'title'		=> _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
						'type'		=> 'number',
						'default'	=> '',
						'desc_tip'	=> _x( 'Price per KM for domestic shipping.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . _x( 'When distance is not available this shipping method will be disabled.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
					];
					$arr['src_city'] = [
						'title'		=> _x( 'Calculate from', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
						'desc_tip'	=> _x( 'Closest location to where the products are shipped from.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
						'type'		=> 'select',
						'options'	=> $src_cities,
						'default'	=> $default_src_city,
					];
				} else {
					$arr[ $key ] = $value;
				}
			}
			return $arr;
		}

		/**
		 * Calculates distance fee for shipping
		 * @param mixed $rates
		 * @param mixed $package
		 * @return mixed
		 */
		function wc_distance_fee_calc( $rates, $package ) {
			if ( ! empty( $package['destination']['country'] ) && ! empty( $package['destination']['state'] ) ) {
				foreach ( $rates as $rate_id => $rate ) {
					if ( ! empty( get_option( str_replace( ':', '_', "woocommerce_{$rate_id}_settings" ) )['distance_fee'] ) && ! empty( get_option( str_replace( ':', '_', "woocommerce_{$rate_id}_settings" ) )['src_city'] ) ) {
						$per_km = get_option( str_replace( ':', '_', "woocommerce_{$rate_id}_settings" ) )['distance_fee'];
						if ( 0 < $per_km ) {
							$src_city_code = get_option( str_replace( ':', '_', "woocommerce_{$rate_id}_settings" ) )['src_city'];
							include( 'i18n/distances/' . $src_city_code . '.php' );
							$dest_city = ! isset( $key_format[ $src_city_code ] ) ? $package['destination']['state'] : WC()->countries->get_states( $package['destination']['country'] )[ $package['destination']['state'] ];
							if ( isset( $cities_distance[ $src_city_code ][ $dest_city ] ) ) {
								$distance_fee = round( apply_filters( 'csz_distance', $cities_distance[ $src_city_code ][ $dest_city ] ), -1 ) * $per_km;
								$new_taxes = [];
								foreach ( $rate->taxes as $key => $tax ) {
									if ( $tax > 0 ) {
										$new_taxes[ $key ] = round( $tax + $tax / $rate->cost * $distance_fee, 2 );
									}
								}
								if ( ! empty( $new_taxes ) ) {
									$rate->taxes = $new_taxes;
								}
								$rates[ $rate_id ]->cost += $distance_fee;
							} else {
								unset( $rates[ $rate_id ] );
							}
						}
					}
				}
			}
			return $rates;
		}

		/**
		 * Adds custom distance fee tab to the products
		 * @param mixed $tabs
		 * @return mixed
		 */
		public function wc_product_distance_fee_tab( $tabs ) {
			$tabs['distance_fee'] = [
				'label'		=> _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'class'		=> [ 'show_if_virtual' ],
				'target'	=> 'distance_fee_product_data',
			];
			return $tabs;
		}

		/**
		 * Adds distance fee tab content
		 */
		public function wc_product_distance_fee_panel() {
			foreach ( get_option( 'wc_csz_countries_codes' ) as $country_code ) {
				$src_cities = [];
				$country_states = '';
				include( 'i18n/cities/' . $country_code . '.php' );
				if ( $country_code === WC()->countries->get_base_country() ) {
					foreach ( glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) as $file ) {
						$city_code = basename( $file, '.php' );
						if ( isset( WC()->countries->get_states( $country_code )[ $city_code ] ) ) {
							include $file;
							if ( ! isset( $key_format[ $city_code ] ) || substr( $key_format[ $city_code ], 0, 2 ) === substr( get_locale(), 0, 2 ) ) {
								$src_cities[ $city_code ] = WC()->countries->get_states( $country_code )[ $city_code ];
							}
						}
					}
				}
			}
			echo '<div id="distance_fee_product_data" class="panel woocommerce_options_panel hidden">';
			woocommerce_wp_text_input( [
				'id'		=> '_distance_fee',
				'label'		=> _x( 'Distance Fee', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'description'	=> _x( 'Price per KM for domestic shipping.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . _x( 'When distance is not available this product will not be added to cart.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'desc_tip'	=> true,
				'type'		=> 'number',
				'custom_attributes'	=> [ 'min' => '0.1', 'step' => '0.1' ],
			] );
			woocommerce_wp_select( [
				'id'		=> '_calc_type',
				'label'		=> __( 'Calculations', 'woocommerce' ),
				'description'	=> sprintf( __( 'via %s', 'woocommerce' ), _x( 'Warehouse', 'plugin', 'cities-shipping-zones-for-woocommerce' ) ) . ': ' . _x( 'Calculates city to warehouse to city.', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . _x( 'Direct', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ': ' . _x( 'Calculates city to city, accepts only source cities within the raduis configured from a city with distances list.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'desc_tip'	=> true,
				'options'	=> [ 'via' => sprintf( __( 'via %s', 'woocommerce' ), _x( 'Warehouse', 'plugin', 'cities-shipping-zones-for-woocommerce' ) ), 'direct' => _x( 'Direct', 'plugin', 'cities-shipping-zones-for-woocommerce' ) ]
			] );
			woocommerce_wp_select( [
				'id'		=> '_src_city',
				'label'		=> _x( 'Warehouse', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'description'	=> _x( 'Closest location to the warehouse.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'desc_tip'	=> true,
				'options'	=> $src_cities,
			] );
			woocommerce_wp_text_input( [
				'id'		=> '_max_radius',
				'label'		=> _x( 'Direct', 'plugin', 'cities-shipping-zones-for-woocommerce' ) . ' ' . _x( 'Max Radius', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'description'		=> _x( 'Allow to calculate distance fee from city within this radius from the customer selected city.', 'plugin', 'cities-shipping-zones-for-woocommerce' ),
				'desc_tip'	=> true,
				'type'		=> 'number',
				'custom_attributes'	=> [ 'min' => '10', 'step' => '10' ],
			] );
			echo '</div>';
		}

		/**
		 * Saves the product custom distance fields
		 * @param mixed
		 */
		public function wc_save_product_custom_fields( $post_id ) {
			update_post_meta( $post_id, '_distance_fee', wc_clean( $_POST['_distance_fee'] ) );
			update_post_meta( $post_id, '_calc_type', wc_clean( $_POST['_calc_type'] ) );
			update_post_meta( $post_id, '_src_city', wc_clean( $_POST['_src_city'] ) );
			update_post_meta( $post_id, '_max_radius', wc_clean( $_POST['_max_radius'] ) );
		}

		/**
		 * Displays the product custom distance fields
		 */
		public function wc_custom_fields_display() {
			global $product;
			if ( $product->is_virtual() && ! empty( $product->get_meta( '_distance_fee' ) ) && ! empty( $product->get_meta( '_src_city' ) ) ) {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_style( 'select2' );
				wp_enqueue_script( 'wc-country-select' );
				woocommerce_form_field( 'address_field', [
					'type'		=> 'text',
					'class'		=> [ 'form-row-wide' ],
					'label'		=> __( 'Street address', 'woocommerce' ),
					'required'	=> true
				], WC()->customer->get_shipping_address() );
				woocommerce_form_field( 'address_city', [
					'type'		=> 'state',
					'country'	=> substr( get_option( 'wc_csz_src_city' ), 0, 2 ),
					'class'		=> [ 'form-row-wide' ],
					'input_class'	=> [ 'state_select' ],
					'label'		=> __( 'City', 'woocommerce' ),
					'required'	=> true
				], WC()->customer->get_shipping_state() );
				woocommerce_form_field( 'address_field2', [
					'type'		=> 'text',
					'class'		=> [ 'form-row-wide' ],
					'label'		=> __( 'Shipping Address', 'woocommerce' ),
					'required'	=> true
				], WC()->customer->get_shipping_address() );
				woocommerce_form_field( 'address_city2', [
					'type'		=> 'state',
					'country'	=> substr( get_option( 'wc_csz_src_city' ), 0, 2 ),
					'class'		=> [ 'form-row-wide' ],
					'input_class'	=> [ 'state_select' ],
					'label'		=> __( 'Shipping City', 'woocommerce' ),
					'required'	=> true
				], WC()->customer->get_shipping_state() );
			}
		}

		/**
		 * Validates the product custom distance fields
		 * @param mixed $passed
		 * @param mixed $product_id
		 * @param mixed $quantity
		 * @return mixed
		 */
		public function wc_validate_custom_field( $passed, $product_id, $quantity ) {
			$product = wc_get_product( $product_id );
			if ( $product->is_virtual() && ! empty( $product->get_meta( '_distance_fee' ) && ! empty( $product->get_meta( '_src_city' ) ) ) ) {
				if ( empty( $_POST['address_field'] ) ) {
					$passed = false;
					wc_add_notice( sprintf( __( '%s is a required field', 'woocommerce' ), __( 'Street address', 'woocommerce' ) ), 'error' );
				}
				if ( empty( $_POST['address_field2'] ) ) {
					$passed = false;
					wc_add_notice( sprintf( __( '%s is a required field', 'woocommerce' ), __( 'Shipping Address', 'woocommerce' ) ), 'error' );
				}
				if ( ! isset( WC()->countries->get_states( WC()->countries->get_base_country() )[ $_POST['address_city'] ] ) || ! isset( WC()->countries->get_states( WC()->countries->get_base_country() )[ $_POST['address_city2'] ] ) ) {
					wc_add_notice( __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ), 'error' );
					return false;
				}
				if ( 'direct' === $product->get_meta( '_calc_type' ) ) {
					if ( file_exists( plugin_dir_path( __FILE__ ) . 'i18n/distances/' . $_POST['address_city'] . '.php' ) ) {
						$src_city_code = wc_clean( $_POST['address_city'] );
					} else {
						$org_city_code = wc_clean( $_POST['address_city'] );
						$country_code = substr( wc_clean( $_POST['address_city'] ), 0, 2 );
						foreach ( glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) as $file ) {
							include $file;
						}
						foreach ( $cities_distance as $key => $value ) {
							if ( ! isset( $src_city_code ) ) {
								$src_city_code = $key;
							}
							$org_city = ! isset( $key_format[ $key ] ) ? $src_city_code : WC()->countries->get_states( WC()->countries->get_base_country() )[ $org_city_code ];
							if ( isset( $cities_distance[ $key ][ $org_city ] ) && ( ! isset( $cities_distance[ $src_city_code ][ $org_city ] ) || $cities_distance[ $key ][ $org_city ] < $cities_distance[ $src_city_code ][ $org_city ] ) ) {
								$src_city_code = $key;
							}
						}
						if ( ! isset( $cities_distance[ $src_city_code ][ $org_city ] ) || $cities_distance[ $src_city_code ][ $org_city ] > $product->get_meta( '_max_radius' ) ) {
							wc_add_notice( __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ), 'error' );
							return false;
						}
					}
					$dest_city_code = $product->get_meta( '_src_city' );
					$dest_city2_code = wc_clean( $_POST['address_city2'] );
				} elseif ( 'via' === $product->get_meta( '_calc_type' ) ) {
					$src_city_code = $product->get_meta( '_src_city' );
					$dest_city_code = wc_clean( $_POST['address_city'] );
					$dest_city2_code = wc_clean( $_POST['address_city2'] );
				}
				include( 'i18n/distances/' . $src_city_code . '.php' );
				if ( ! isset( $key_format[ $src_city_code ] ) ) {
					$dest_city = $dest_city_code;
					$dest_city2 = $dest_city2_code;
				} else {
					$dest_city = WC()->countries->get_states( substr( $dest_city_code, 0, 2 ) )[ $dest_city_code ];
					$dest_city2 = WC()->countries->get_states( substr( $dest_city2_code, 0, 2 ) )[ $dest_city2_code ];
				}
				if ( ! isset( $cities_distance[ $src_city_code ][ $dest_city ] ) || ! isset( $cities_distance[ $src_city_code ][ $dest_city2 ] ) ) {
					wc_add_notice( __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ), 'error' );
					return false;
				}
			}
			return $passed;
		}

		/**
		 * Adds product custom distance fields to cart
		 * @param mixed $cart_item_data
		 * @param mixed $product_id
		 * @param mixed $variation_id
		 * @param mixed $quantity
		 * @return mixed
		 */
		public function wc_add_custom_field_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
			if ( isset( $_POST['address_field'] ) ) {
				$cart_item_data['address_field'] = wc_clean( $_POST['address_field'] );
			}
			if ( isset( $_POST['address_field2'] ) ) {
				$cart_item_data['address_field2'] = wc_clean( $_POST['address_field2'] );
			}
			if ( isset( $_POST['address_city'] ) && isset( $_POST['address_city2'] ) ) {
				$product = wc_get_product( $product_id );
				$cart_item_data['address_city'] = WC()->countries->get_states( substr( wc_clean( $_POST['address_city'] ), 0, 2 ) )[ wc_clean( $_POST['address_city'] ) ];
				$cart_item_data['address_city2'] = WC()->countries->get_states( substr( wc_clean( $_POST['address_city2'] ), 0, 2 ) )[ wc_clean( $_POST['address_city2'] ) ];
				if ( 'direct' === $product->get_meta( '_calc_type' ) ) {
					if ( file_exists( plugin_dir_path( __FILE__ ) . 'i18n/distances/' . wc_clean( $_POST['address_city'] ) . '.php' ) ) {
						$src_city_code = wc_clean( $_POST['address_city'] );
					} else {
						$org_city_code = wc_clean( $_POST['address_city'] );
						$country_code = substr( wc_clean( $_POST['address_city'] ), 0, 2 );
						foreach ( glob( plugin_dir_path( __FILE__ ) . "i18n/distances/{$country_code}*.php" ) as $file ) {
							include $file;
						}
						foreach ( $cities_distance as $key => $value ) {
							if ( ! isset( $src_city_code ) ) {
								$src_city_code = $key;
							}
							$org_city = ! isset( $key_format[ $key ] ) ? $src_city_code : WC()->countries->get_states( WC()->countries->get_base_country() )[ $org_city_code ];
							if ( isset( $cities_distance[ $key ][ $org_city ] ) && ( ! isset( $cities_distance[ $src_city_code ][ $org_city ] ) || $cities_distance[ $key ][ $org_city ] < $cities_distance[ $src_city_code ][ $org_city ] ) ) {
								$src_city_code = $key;
							}
						}
					}
					$dest_city_code = $product->get_meta( '_src_city' );
					$dest_city2_code = wc_clean( $_POST['address_city2'] );
				} elseif ( 'via' === $product->get_meta( '_calc_type' ) ) {
					$src_city_code = $product->get_meta( '_src_city' );
					$dest_city_code = wc_clean( $_POST['address_city'] );
					$dest_city2_code = wc_clean( $_POST['address_city2'] );
				}
				include( 'i18n/distances/' . $src_city_code . '.php' );
				if ( ! isset( $key_format[ $src_city_code ] ) ) {
					$dest_city = $dest_city_code;
					$dest_city2 = $dest_city2_code;
				} else {
					$dest_city = WC()->countries->get_states( substr( $dest_city_code, 0, 2 ) )[ $dest_city_code ];
					$dest_city2 = WC()->countries->get_states( substr( $dest_city2_code, 0, 2 ) )[ $dest_city2_code ];
				}
				$cart_item_data['distance_fee'] = $product->get_meta( '_distance_fee' ) * ( round( $cities_distance[ $src_city_code ][ $dest_city ] + $cities_distance[ $src_city_code ][ $dest_city2 ], -1 ) );
			}
			return $cart_item_data;
		}

		/**
		 * Updates cart total price
		 * @param mixed $cart_obj
		 */
		public function wc_before_calculate_totals( $cart_obj ) {
			if ( ( ! is_admin() || wp_doing_ajax() ) && 2 > did_action( 'woocommerce_before_calculate_totals' ) ) {
				foreach( $cart_obj->get_cart() as $cart_item ) {
					if ( isset( $cart_item['distance_fee'] ) ) {
						$cart_item['data']->set_price( $cart_item['data']->get_price() + $cart_item['distance_fee'] );
					}
				}
			}
		}

		/**
		 * Displays product custom distance fields values in cart
		 * @param mixed $name
		 * @param mixed $cart_item
		 * @param mixed $cart_item_key
		 * @return mixed
		 */
		public function wc_cart_item_name( $name, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['address_field'] ) ) {
				$name .= sprintf( '<br>%s', $cart_item['address_field'] );
			}
			if ( isset( $cart_item['address_city'] ) ) {
				$name .= sprintf( '<br>%s', $cart_item['address_city'] );
			}
			if ( isset( $cart_item['address_field2'] ) ) {
				$name .= sprintf( '<br>%s', $cart_item['address_field2'] );
			}
			if ( isset( $cart_item['address_city2'] ) ) {
				$name .= sprintf( '<br>%s', $cart_item['address_city2'] );
			}
			return $name;
		}

		/**
		 * Displays product custom distance fields values in order
		 * @param mixed $item
		 * @param mixed $cart_item_key
		 * @param mixed $value
		 * @param mixed $order
		 */
		public function wc_add_custom_data_to_order( $item, $cart_item_key, $values, $order ) {
			foreach( $item as $cart_item_key => $values ) {
				if ( isset( $values['address_field'] ) ) {
					$item->add_meta_data( __( 'Street address', 'woocommerce' ), $values['address_field'], true );
				}
				if ( isset( $values['address_city'] ) ) {
					$item->add_meta_data( __( 'City', 'woocommerce' ), $values['address_city'], true );
				}
				if ( isset( $values['address_field2'] ) ) {
					$item->add_meta_data( __( 'Shipping Address', 'woocommerce' ), $values['address_field2'], true );
				}
				if ( isset( $values['address_city2'] ) ) {
					$item->add_meta_data( __( 'Shipping City', 'woocommerce' ), $values['address_city2'], true );
				}
			}
		}

		/**
		 * Changes add to cart button in archive pages
		 * @param mixed $button
		 * @param mixed $product
		 * @return mixed
		 */
		public function wc_replacing_add_to_cart_button( $button, $product ) {
			if ( $product->is_virtual() && ! empty( $product->get_meta( '_distance_fee' ) ) && ! empty( $product->get_meta( '_src_city' ) ) ) {
				$button = '<a class="button" href="' . $product->get_permalink() . '">' . __( 'Enter address' ) . '</a>';
			}
			return $button;
		}
	}

	/**
	 * Instantiate classes
	 */
	$cities_shipping_zones_for_woocommerce = new WC_CSZ();
};

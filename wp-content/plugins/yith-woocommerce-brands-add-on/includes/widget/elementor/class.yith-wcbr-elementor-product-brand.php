<?php
/**
 * Product Brand widget for Elementor
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Brands Add-on
 * @version 1.3.8
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCBR_Elementor_Product_Brand' ) ) {
	class YITH_WCBR_Elementor_Product_Brand extends \Elementor\Widget_Base {

		/**
		 * Get widget name.
		 *
		 * Retrieve YITH_WCBR_Elementor_Product_Brand widget name.
		 *
		 * @return string Widget name.
		 * @since  1.0.0
		 * @access public
		 */
		public function get_name() {
			return 'yith_wcbr_product_brand';
		}

		/**
		 * Get widget title.
		 *
		 * Retrieve YITH_WCBR_Elementor_Product_Brand widget title.
		 *
		 * @return string Widget title.
		 * @since  1.0.0
		 * @access public
		 */
		public function get_title() {
			return _x( 'YITH Product Brand', 'Elementor widget name', 'yith-woocommerce-brands-add-on' );
		}

		/**
		 * Get widget icon.
		 *
		 * Retrieve YITH_WCBR_Elementor_Product_Brand widget icon.
		 *
		 * @return string Widget icon.
		 * @since  1.0.0
		 * @access public
		 */
		public function get_icon() {
			return 'eicon-favorite';
		}

		/**
		 * Get widget categories.
		 *
		 * Retrieve the list of categories the YITH_WCBR_Elementor_Product_Brand widget belongs to.
		 *
		 * @return array Widget categories.
		 * @since  1.0.0
		 * @access public
		 *
		 */
		public function get_categories() {
			return [ 'general', 'yith' ];
		}

		/**
		 * Register YITH_WCBR_Elementor_Product_Brand widget controls.
		 *
		 * Adds different input fields to allow the user to change and customize the widget settings.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		protected function _register_controls() {

			$this->start_controls_section(
				'general_section',
				[
					'label' => _x( 'General', 'Elementor section title', 'yith-woocommerce-brands-add-on' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$this->add_control(
				'title',
				[
					'label'       => _x( 'Title', 'Elementor control label', 'yith-woocommerce-brands-add-on' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'input_type'  => 'text',
					'placeholder' => '',
				]
			);

			$this->add_control(
				'product_id',
				[
					'label'       => _x( 'Product ID', 'Elementor control label', 'yith-woocommerce-brands-add-on' ),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'input_type'  => 'text',
					'placeholder' => '123',
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'appearance_section',
				[
					'label' => _x( 'Appearance', 'Elementor section title', 'yith-woocommerce-brands-add-on' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$this->add_control(
				'show_logo',
				[
					'label'   => _x( 'Show logo', 'Elementor control label', 'yith-woocommerce-brands-add-on' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'yes' => __( 'Show Logo', 'yith-woocommerce-brands-add-on' ),
						'no'  => __( 'Do not show Logo', 'yith-woocommerce-brands-add-on' ),
					],
					'default' => 'no',
				]
			);

			$this->add_control(
				'show_title',
				[
					'label'   => _x( 'Show Title', 'Elementor control label', 'yith-woocommerce-brands-add-on' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'yes' => __( 'Show Title', 'yith-woocommerce-brands-add-on' ),
						'no'  => __( 'Do not show Title', 'yith-woocommerce-brands-add-on' ),
					],
					'default' => 'no',
				]
			);

			$this->end_controls_section();

		}

		/**
		 * Render YITH_WCBR_Elementor_Product_Brand widget output on the frontend.
		 *
		 * @since  1.0.0
		 * @access protected
		 */
		protected function render() {

			$attribute_string = '';
			$settings         = $this->get_settings_for_display();

			foreach ( $settings as $key => $value ) {
				if ( empty( $value ) || ! is_scalar( $value ) ) {
					continue;
				}
				$attribute_string .= " {$key}=\"{$value}\"";
			}

			echo do_shortcode( "[yith_wcbr_product_brand {$attribute_string}]" );
		}

	}
}
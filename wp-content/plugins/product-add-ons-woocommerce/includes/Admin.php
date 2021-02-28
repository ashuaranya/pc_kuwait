<?php

namespace ZAddons;

class Admin {
	public function __construct() {
		new Admin\ListGroup();
		new Admin\SingleGroup();
		new Admin\Layout();
		add_action( 'wp_ajax_za_get_products', array( $this, 'get_products' ) );
	}

	public static function getUrl( $tab, $subtab = '' ) {
		$args = [
			'post_type' => 'product',
			'page' => 'za_groups',
			'tab' => $tab,
		];
		if ( $subtab ) $args['subtab'] = $subtab;
		return add_query_arg( $args, admin_url( 'edit.php' ) );
	}

	public function get_products() {
		check_ajax_referer( 'search-products', 'security' );

		$term  = (string) wc_clean( wp_unslash( $_GET['term'] ) );
		$limit = absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );

		$data_store = \WC_Data_Store::load( 'product' );
		$ids        = $data_store->search_products( $term, '', true, false, $limit );

		$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
		$products        = [];

		foreach ( $product_objects as $product_object ) {
			$identifier                            = $product_object->get_sku() ? $product_object->get_sku() : '#' . $product_object->get_id();
			$formatted_name                        = sprintf( '%2$s (%1$s)', $identifier, $product_object->get_name() );
			$products[ $product_object->get_id() ] = rawurldecode( $formatted_name );
		}
		wp_send_json( apply_filters( 'woocommerce_json_search_found_products', $products ) );
	}

}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Search {
	public $settings;

	public function __construct() {
		add_action( 'wp_ajax_woopb_search_product_in_step', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_nopriv_woopb_search_product_in_step', array( $this, 'search_products' ) );
	}

	public function search_products() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
		$post_id        = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
		$step           = isset( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : '';
		$search         = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$out            = array();
		if ( $post_id && $step ) {
			$product_ids = $this->get_products( $post_id, $step );
			$arg         = array(
				'limit'   => - 1,
				'status'  => 'publish',
				'include' => $product_ids,
				's'       => $search
			);
			$products    = wc_get_products( $arg );

			ob_start();
			wc_get_template( 'product-template.php', array(
				'id'       => $post_id,
				'products' => $products,
			), '', VI_WPRODUCTBUILDER_TEMPLATES );

			$out = ob_get_clean();
		}
		wp_send_json_success( $out );
		wp_die();
	}

	public function get_products( $post_id, $step_id ) {
		/*Get current step*/
		$items = $this->settings->get_data( $post_id, 'list_content', array() );
		if ( $step_id > count( $items ) ) {
			$step_id = count( $items ) - 1;
		}
		$item_data = isset( $items[ $step_id - 1 ] ) ? $items[ $step_id - 1 ] : array();
		$terms     = $product_ids = $product_ids_of_term = array();

		foreach ( $item_data as $item ) {
			if ( strpos( trim( $item ), 'cate_' ) === false ) {
				$product_ids[] = $item;
			} else {
				$terms[] = str_replace( 'cate_', '', trim( $item ) );
			}
		}

		$args      = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'tax_query'      => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $terms,
					'operator' => 'IN'
				),
			),
			'fields'         => 'ids'
		);
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$product_ids_of_term = $the_query->posts;
		}
		wp_reset_postdata();
		$product_ids = array_unique( array_merge( $product_ids, $product_ids_of_term ) );

		return $product_ids;
	}

}


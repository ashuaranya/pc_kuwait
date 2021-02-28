<?php

namespace ZAddons;

class API {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ], 5 );
	}

	public function register_rest_routes() {
		$classes = [
			API\Groups::class,
		];

		foreach ( $classes as $class ) {
			/* @var $controller \WP_REST_Controller */
			$controller = new $class();
			$controller->register_routes();
		}

	}
}

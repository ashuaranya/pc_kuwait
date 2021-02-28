<?php
require_once( __DIR__ . '/helpers/WCFM.php');
/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
 */

class FlutterVendor extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'wc/v2/flutter';//prefix must be wc/ or wc- to reuse check permission function in woo commerce
	protected $namespace_v3 = 'wc/v3/flutter';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
		add_action('rest_api_init', array($this, 'register_flutter_vendor_routes'));
		add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'prepeare_product_response' ), 31, 3 );
		add_filter( 'dokan_rest_prepare_product_object', array( $this, 'prepeare_product_response' ), 11, 3 );
    }

    public function register_flutter_vendor_routes()
    {
		$media = array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'upload_image' ),
				'args' => $this->get_params_upload(),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
        );
		register_rest_route( $this->namespace,  '/media', $media );
		register_rest_route( $this->namespace_v3,  '/media', $media );

		$product = array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'flutter_create_product' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		);
		register_rest_route( $this->namespace,  '/product', $product );
		register_rest_route( $this->namespace_v3,  '/product', $product );

		register_rest_route( $this->namespace,  '/products/owner', array(
			array(
				'methods' =>"POST",
				'callback' => array( $this, 'flutter_get_products' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		) );

		register_rest_route( $this->namespace,  '/wcfm-stores', array(
			array(
				'methods' =>"GET",
				'callback' => array( $this, 'flutter_get_wcfm_stores' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		));

		register_rest_route( $this->namespace, '/wcfm-stores' . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'wcfm-marketplace-rest-api' ),
                    'type'        => 'integer',
                )
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'flutter_get_wcfm_stores_by_id' ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
	  ));

	  register_rest_route( $this->namespace,  '/shipping-methods', array(
		array(
			'methods' =>"POST",
			'callback' => array( $this, 'flutter_get_shipping_methods' ),
			'permission_callback' => function () {
				return parent::checkApiPermission();
			}
		),
	  ));

	  register_rest_route( $this->namespace,  '/vendor-orders', array(
		array(
			'methods' =>"GET",
			'callback' => array( $this, 'flutter_get_vendor_orders' ),
			'permission_callback' => function () {
				return parent::checkApiPermission();
			}
		),
	  ));
	  register_rest_route( $this->namespace,  '/get-nearby-stores', array(
		array(
			'methods' =>"GET",
			'callback' => array( $this, 'flutter_get_nearby_stores' ),
			'permission_callback' => function () {
				return parent::checkApiPermission();
			}
		),
	  ));
    }

    public function get_params_upload(){
		$params = array(
			'media_attachment' => array(
				'required'          => true,
				'description'       => __( 'Image encoded as base64.', 'image-from-base64' ),
				'type'              => 'string'
			),
			'title' => array(
				'required'          => true,
				'description'       => __( 'The title for the object.', 'image-from-base64' ),
				'type'              => 'json'
			),
			'media_path' => array(
				'description'       => __( 'Path to directory where file will be uploaded.', 'image-from-base64' ),
				'type'              => 'string'
			)
		);
		return $params;
	}

    public function upload_image($request){
		$response = array();
		try{
			if( !empty($request['media_path']) ){
				$this->upload_dir = $request['media_path'];
				$this->upload_dir = '/' . trim($this->upload_dir, '/');
				add_filter( 'upload_dir', array( $this, 'change_wp_upload_dir' ) );
			}

			if( !class_exists('WP_REST_Attachments_Controller') ){
				throw new Exception('WP API not installed.');
            }
			$media_controller = new WP_REST_Attachments_Controller( 'attachment' );

			$filename = $request['title']['rendered'];

			$img = $request['media_attachment'];
			$decoded = base64_decode($img);

			$permission_check = $media_controller->create_item_permissions_check( $request );
			if( is_wp_error($permission_check) ){
				throw new Exception( $permission_check->get_error_message() );
			}

			$request->set_body($decoded);
			$request->add_header('Content-Disposition', "attachment;filename=\"{$filename}\"");
			$result = $media_controller->create_item( $request );
			$response = rest_ensure_response( $result );
		}
        catch(Exception $e){
			$response['result'] = "error";
			$response['message'] = $e->getMessage();
		}

		if( !empty($request['media_path']) ){
			remove_filter( 'upload_dir', array( $this, 'change_wp_upload_dir' ) );
		}

		return $response;
    } 

    public function flutter_create_product($request){
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
		}
        $user = get_userdata($user_id);
		$isSeller = in_array("seller",$user->roles) || in_array("wcfm_vendor",$user->roles);
		
		$requestStatus = "draft";
		if ($request["status"] != null) {
			$requestStatus = $request["status"];
		}

        if ($isSeller) {
            $args = array(	   
                'post_author' => $user_id, 
                'post_content' => $request["content"],
                'post_status' => $requestStatus, // (Draft | Pending | Publish)
                'post_title' => $request["title"],
                'post_parent' => '',
                'post_type' => "product"
            ); 
            // Create a simple WooCommerce product
			$post_id = wp_insert_post( $args );
			$product = wc_get_product($post_id);

			if ( isset( $request['regular_price'] ) ) {
				$product->set_regular_price( $request['regular_price'] );
			}

			// Sale Price.
			if ( isset( $request['sale_price'] ) ) {
				$product->set_sale_price( $request['sale_price'] );
			}

			if ( isset( $request['date_on_sale_from'] ) ) {
				$product->set_date_on_sale_from( $request['date_on_sale_from'] );
			}

			if ( isset( $request['date_on_sale_from_gmt'] ) ) {
				$product->set_date_on_sale_from( $request['date_on_sale_from_gmt'] ? strtotime( $request['date_on_sale_from_gmt'] ) : null );
			}

			if ( isset( $request['date_on_sale_to'] ) ) {
				$product->set_date_on_sale_to( $request['date_on_sale_to'] );
			}

			if ( isset( $request['date_on_sale_to_gmt'] ) ) {
				$product->set_date_on_sale_to( $request['date_on_sale_to_gmt'] ? strtotime( $request['date_on_sale_to_gmt'] ) : null );
			}

			if ( isset( $request['image_ids'] ) ) {
				update_post_meta($post_id,'_product_image_gallery',join(",",$request['image_ids']));
				if(count($request['image_ids']) > 0){
					set_post_thumbnail( $post_id, $request['image_ids'][0] );
				}
			}

			wp_set_object_terms( $post_id,isset( $request['product_type']) ?  $request['product_type'] : "simple", 'product_type' );
			$product->save();
			$product = wc_get_product($post_id);
			if(isset($request["categories"]) && count($request["categories"]) > 0){
				$product->set_category_ids([$request["categories"][0]["id"]] ); 
				$product->save();
			}
            return $product->get_data();
        }else{
            return parent::sendError("invalid_role","You must be seller to create product", 401);
        }
	}
	
	public function flutter_get_products($request){
		$cookie = $request["cookie"];
		$id = $request["id"];
        if (!isset($cookie) && !isset($id)) {
            return parent::sendError("invalid_login","You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = isset($id) ? $id : wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
		}

		$page = isset($request["page"]) ? $request["page"] : 0;
		$limit = isset($request["limit"]) ? $request["limit"] : 10;

		$products = wc_get_products( array(
			'author' => $user_id,
			'limit'=>$limit,
			'page'=>$page
		));
		$ids = array();
		foreach ($products as $object) {
			$ids[] = $object->id;
		}
		if (count($ids) > 0) {
			$api = new WC_REST_Products_Controller();
			$req = new WP_REST_Request('GET');
			$params = array('status' => isset($id) ? 'published' : 'any','include'=>$ids, 'per_page'=>$limit);
			$req->set_query_params($params);

			$response = $api->get_items($req);
			return $response->get_data();
		}else{
			return [];
		}
		
	}
	
	public function flutter_get_wcfm_stores($request)
	{
		$helper = new FlutterWCFMHelper();
		return $helper->flutter_get_wcfm_stores($request);
	}

	public function flutter_get_wcfm_stores_by_id($request)
	{
		$helper = new FlutterWCFMHelper();
		$id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
		return $helper->flutter_get_wcfm_stores_by_id($id);
	}

	public function prepeare_product_response( $response, $object, $request ) {
		$data = $response->get_data();
		$author_id = get_post_field( 'post_author', $data['id'] );
		if( is_plugin_active('dokan-lite/dokan.php' ) ) {
			$store = dokan()->vendor->get( $author_id );
			$dataStore = $store->to_array();
			$dataStore = array_merge( $dataStore, apply_filters( 'dokan_rest_store_additional_fields', [], $store, $request ) );
			$data['store'] = $dataStore;
		}
		if( is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php' ) ) {
			$helper = new FlutterWCFMHelper();
			$data['store'] = $helper->flutter_get_wcfm_stores_by_id($author_id)->get_data();
		}
		
        $response->set_data( $data );
        return $response;
	}

	public function flutter_get_shipping_methods($request){
		$json = file_get_contents('php://input');
		$package = json_decode($json, TRUE);
		$results = [];
		$controller = new WC_REST_Shipping_Zone_Methods_V2_Controller();
		$zone = WC_Shipping_Zones::get_zone_matching_package( $package );
		$request['zone_id'] = $zone->get_id();
		
		if( class_exists('WeDevs\DokanPro\Shipping\ShippingZone' )) {
            $seller_id = $package['seller_id'];
			$shipping_methods = WeDevs\DokanPro\Shipping\ShippingZone::get_shipping_methods( $zone->get_id(), $seller_id );
			if (count($shipping_methods) == 0) {
				$shipping_methods = $controller->get_items($request );
				foreach ( $shipping_methods->data as $method ) {
					if ($method['method_id'] != 'dokan_vendor_shipping') {
						$results[] = $method;
					}
				}
			}else{
				foreach ( $shipping_methods as $key => $method ) {
					$results[] = $method;
				}
			}
		}else{
			$shipping_methods = $controller->get_items($request );
			return $shipping_methods->data;
		}
		return $results;
	}
	
	public function flutter_get_vendor_orders($request){
		$cookie = $request["cookie"];
		if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
		}

		$api = new WC_REST_Orders_V1_Controller();

		$orders = [];
		$results = [];
		if( is_plugin_active( 'dokan-lite/dokan.php' ) ) {
			$orders = dokan_get_seller_orders( $user_id, 'all', null, 10000000, 0 );
		}

		if (is_plugin_active( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php' )) {
			global $wpdb;
			$table_name = $wpdb->prefix . "wcfm_marketplace_orders";
			$orders = $wpdb->get_results( "SELECT * FROM $table_name WHERE vendor_id = '$user_id' ORDER BY order_id DESC" );
		}

		foreach ($orders as $item) {
			$order = wc_get_order($item->order_id);
			if($order != false){
				$response = $api->prepare_item_for_response($order,$request);
				$results[] = $response->get_data();
			}
	   }
		
		return $results;		
	}

	public function flutter_get_nearby_stores($request){
		if( is_plugin_active( 'dokan-lite/dokan.php' ) && is_plugin_active( 'dokan-pro/dokan-pro.php' )) {
			$queries_data = array();
			parse_str($_SERVER['QUERY_STRING'], $queries_data);
			$sellers = dokan_get_sellers( apply_filters( 'dokan_seller_listing_args', $queries_data, $_GET ) );
			if ($sellers["count"] > 0) {
				$data_objects = [];
				$storeController = new WeDevs\Dokan\REST\StoreController();
				foreach ( $sellers["users"] as $user ) {
					$vendor = dokan()->vendor->get( $user->ID );
					$stores_data    = $storeController->prepare_item_for_response( $vendor, $request );
					$data_objects[] = $storeController->prepare_response_for_collection( $stores_data );
				}
	
				$response = rest_ensure_response( $data_objects );
				$response = $storeController->format_collection_response( $response, $request, dokan()->vendor->get_total() );
	
				return $response;
			}else{
				return [];
			}
		}else{
			return parent::sendError("invalid_plugin","Please install Dokan Lite and Dokan Pro plugin", 400);
		}
	}
}

new FlutterVendor;
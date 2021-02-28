<?php

namespace Artiosmedia\WC_Product_Code;

class PCFW_Services {

    private $admin_settings;

    public function __construct() {
	$this->admin_settings = new PCFW_Admin_settings();

	$this->actions();
    }

    public function actions() {
	$this->admin_settings->actions();
	add_action( 'admin_init', array( $this, 'validate_parent_plugin_exists' ) );
	add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	add_action( 'admin_head', [ $this, 'add_css' ] );
	add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_code_to_cart_product' ], 10, 3 );
	add_filter( 'woocommerce_get_item_data', [ $this, 'retrieve_product_code_in_cart' ], 10, 2 );
	add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'process_order_item' ], 10, 4 );
	add_action( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'get_formatted_order_item_meta_data' ], 10, 2 );
	add_action( 'woocommerce_order_item_display_meta_key', [ $this, 'get_order_item_meta_display_key' ], 10, 3 );
	add_action( 'woocommerce_product_meta_start', [ $this, 'display_product_code' ] );
	//add_action( 'woocommerce_product_meta_start', [ $this, 'display_product_code_second' ] );
	add_filter( 'woocommerce_get_sections_products', [ $this, 'add_woocommerce_settings' ] );
	add_filter( 'woocommerce_get_settings_products', [ $this, 'add_product_code_settings' ], 10, 2 );
	add_filter( 'body_class', [ $this, 'PCFW_add_body_class' ] );

	//add_filter( 'woocommerce_get_settings_products', [ $this, 'add_import_export' ], 10, 2 );
	add_action( 'woocommerce_admin_field_file', [ $this, 'add_admin_field_file' ] );
	add_action( 'woocommerce_admin_field_button', [ $this, 'add_admin_field_button' ] );
	add_action( "admin_menu", array( $this, "add_to_wc_submenu" ) );
	add_filter( 'plugin_row_meta', [ $this, 'plugin_row_filter' ], 10, 3 );
	add_action( 'wp_ajax_product_code', [ $this, 'ajax_get_product_code' ] );

	add_action( 'wp_ajax_nopriv_product_code', [ $this, 'ajax_get_product_code' ] );

	//Structured data
	add_filter( 'woocommerce_structured_data_product', array( $this, 'structured_data_product_code' ), 10, 2 );
	
	// Shortcode
	add_shortcode( 'pcfw_display_product_code', array( $this, 'product_code_shortcode' ) );
    }

    public function PCFW_add_body_class( $classes ) {
	if ( get_option( 'product_code_hide_empty_field' ) == "yes" )
	    $classes[] = 'hide_pcode';
	return $classes;
    }

    public function validate_parent_plugin_exists() {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	    add_action( 'admin_notices', array( $this, 'show_woocommerce_missing_notice' ) );
	    add_action( 'network_admin_notices', array( $this, 'show_woocommerce_missing_notice' ) );
	    deactivate_plugins( 'product-code-for-woocommerce/product-code-for-woocommerce.php' );
	    if ( isset( $_GET[ 'activate' ] ) ) {
		// Do not sanitize it because we are destroying the variables from URL
		unset( $_GET[ 'activate' ] );
	    }
	}
    }

    public function enqueue() {
	global $post;

	if ( ! empty( $post ) && $post->post_type == 'product' ):
	    //wp_enqueue_script( 'product-code-frontend', PRODUCT_CODE_URL . '/assets/js/stl_custom.js', [ 'wc-add-to-cart-variation', 'jquery' ]);

	    wp_enqueue_style( 'product-code-frontend', PRODUCT_CODE_URL . '/assets/css/single-product.css' );

	    wp_enqueue_script( 'product-code-for-woocommerce', PRODUCT_CODE_URL . '/assets/js/editor.js', [ 'jquery' ] );

	    wp_localize_script( 'product-code-for-woocommerce', 'PRODUCT_CODE', [ 'ajax' => admin_url( 'admin-ajax.php' ), 'HIDE_EMPTY' => get_option( 'product_code_hide_empty_field' ) ] );
	endif;
    }

    public function add_css() {
	wp_register_style( 'product-code-backend', PRODUCT_CODE_URL . '/assets/css/single-product.css' );
	wp_enqueue_style( 'product-code-backend' );
    }

    public function plugin_row_filter( $links, $plugin_file, $plugin_data ) {
	// Not our plugin.

	if ( strpos( $plugin_file, 'product-code-for-woocommerce.php' ) === false ) {
	    return $links;
	}

	$slug = basename( $plugin_data[ 'PluginURI' ] );
	// $link_template = $this->get_template( 'link' );

	$links[ 2 ] = sprintf( '<a href="%s" title="More information about %s">%s</a>', add_query_arg( [
	    'tab'		 => 'plugin-information',
	    'plugin'	 => $slug,
	    'TB_iframe'	 => 'true',
	    'width'		 => 772,
	    'height'	 => 563,
	], self_admin_url( 'plugin-install.php' ) ), $plugin_data[ 'Name' ], __( 'View Details', 'product-code-for-woocommerce' ) );

	$links[ 'donation' ] = sprintf( '<a href="%s" target="_blank">%s</a>', add_query_arg( [
	    'cmd'			 => '_s-xclick',
	    'hosted_button_id'	 => PRODUCT_CODE_PAYPAL_ID
	], 'https://www.paypal.com/cgi-bin/webscr' ), __( 'Donation for Homeless', 'product-code-for-woocommerce' ) );

	return $links;
    }

    public function add_code_to_cart_product( $cart_item_data, $product_id, $variation_id ) {
	$id = $variation_id ? $variation_id : $product_id;

	$simple_field_name = PRODUCT_CODE_FIELD_NAME;
	if ( get_option( 'product_code' ) == 'yes' ):

	    $simple_value = get_post_meta( $id, $simple_field_name, true );
	    if ( $simple_value ) {
		$cart_item_data[ $simple_field_name ] = $simple_value;
	    }
	endif;
	if ( get_option( 'product_code_second' ) == 'yes' && get_option( 'product_code_second_show' ) == 'yes' ):
	    // Second Product Code adding to Cart
	    $simple_field_name = PRODUCT_CODE_FIELD_NAME_SECOND;

	    $simple_value = get_post_meta( $id, $simple_field_name, true );
	    if ( $simple_value ) {
		$cart_item_data[ $simple_field_name ] = $simple_value;
	    }
	endif;
	//error_log(print_r($cart_item_data,true));
	return $cart_item_data;
    }

    public function retrieve_product_code_in_cart( $cart_item_data, $cart_item ) {
	$simple_field_name = PRODUCT_CODE_FIELD_NAME;

	$txt = get_option( "product_code_text", '' );

	$cart_data = [];
	if ( get_option( 'product_code' ) == 'yes' ):

	    if ( isset( $cart_item[ $simple_field_name ] ) ) {
		$cart_data[] = array(
		    'name'	 => $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ),
		    'value'	 => $cart_item[ $simple_field_name ],
		);
	    }
	endif;

	// Second product
	if ( get_option( 'product_code_second' ) == 'yes' && get_option( 'product_code_second_show' ) == 'yes' ):

	    $simple_field_name = PRODUCT_CODE_FIELD_NAME_SECOND;

	    $txt = get_option( "product_code_text_second", '' );

	    // $cart_data = [];
	    if ( isset( $cart_item[ $simple_field_name ] ) ) {
		$cart_data[] = array(
		    'name'	 => $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ),
		    'value'	 => $cart_item[ $simple_field_name ],
		);
	    }
	endif;
	//error_log(print_r($cart_data,true));
	return array_merge( $cart_item_data, $cart_data );
    }

    public function process_order_item( $item, $cart_item_key, $values, $order ) {
	$simple_field_name	 = PRODUCT_CODE_FIELD_NAME;
	$txt			 = get_option( "product_code_text", '' );

	if ( isset( $values[ $simple_field_name ] ) ) {
	    $item->add_meta_data( ($txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ) ), $values[ $simple_field_name ], false );
	}

	$simple_field_name	 = PRODUCT_CODE_FIELD_NAME_SECOND;
	$txt			 = get_option( "product_code_text_second", '' );

	if ( isset( $values[ $simple_field_name ] ) ) {
	    $item->add_meta_data( ($txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ) ), $values[ $simple_field_name ], false );
	}
    }

    public function get_formatted_order_item_meta_data( $formatted_meta, $item ) {
	$field_name	 = PRODUCT_CODE_FIELD_NAME;
	$txt		 = get_option( "product_code_text", '' );

	foreach ( $formatted_meta as $idx => $meta ) {
	    if ( $meta->key === $field_name ) {
		return $formatted_meta;
	    }
	}

	$value = $item->get_meta( $field_name );
	if ( empty( $value ) ) {
	    return $formatted_meta;
	}

	$formatted_meta[ $field_name ] = (object) [
	    'key'		 => $field_name,
	    'value'		 => $value,
	    'display_key'	 => $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ),
	    'display_value'	 => $value,
	];

	// Second Product Item Meta
	$field_name	 = PRODUCT_CODE_FIELD_NAME_SECOND;
	$txt		 = get_option( "product_code_text_second", '' );

	foreach ( $formatted_meta as $idx => $meta ) {
	    if ( $meta->key === $field_name ) {
		return $formatted_meta;
	    }
	}

	$value = $item->get_meta( $field_name );
	if ( empty( $value ) ) {
	    return $formatted_meta;
	}

	$formatted_meta[ $field_name ] = (object) [
	    'key'		 => $field_name,
	    'value'		 => $value,
	    'display_key'	 => $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' ),
	    'display_value'	 => $value,
	];

	return $formatted_meta;
    }

    public function get_order_item_meta_display_key( $display_key, $meta, $item ) {
	if ( $meta->key === PRODUCT_CODE_FIELD_NAME ) {
	    $txt = get_option( "product_code_text", '' );
	    return $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' );
	}
	if ( $meta->key === PRODUCT_CODE_FIELD_NAME_SECOND ) {
	    $txt = get_option( "product_code_text_second", '' );
	    return $txt ? $txt : __( 'Product Code', 'product-code-for-woocommerce' );
	}

	return $display_key;
    }

    public function display_product_code() {

	if ( get_option( 'product_code' ) == 'yes' ) {
	    $post	 = get_post();
	    $value	 = get_post_meta( $post->ID, PRODUCT_CODE_FIELD_NAME, true );
	    $text	 = get_option( "product_code_text", "" );

	    $value_second	 = get_post_meta( $post->ID, PRODUCT_CODE_FIELD_NAME_SECOND, true );
	    $text_second	 = get_option( "product_code_text_second", "" );

	    include_once( PRODUCT_CODE_TEMPLATE_PATH . '/product-meta-row.php' );
	    return;
	}
    }

    public function add_woocommerce_settings( $sections ) {
	$sections[ 'product_code_settings' ] = __( 'Product Code', 'product-code-for-woocommerce' );
	return $sections;
    }

    public function add_to_wc_submenu() {
	add_submenu_page( "woocommerce", __( "Product Code", "product-code-for-woocommerce" ), __( "Product Code", "product-code-for-woocommerce" ), "manage_options", "wc_product_code", function() {
	    printf( "<script>window.location='%s'</script>", admin_url( "admin.php?page=wc-settings&tab=products&section=product_code_settings" ) );
	} );
    }

    public function add_product_code_settings( $settings, $current_section ) {
	if ( $current_section == 'product_code_settings' ) {
	    $settings_slider	 = array();
	    // Add Title to the Settings
	    $settings_slider[]	 = array(
		'name'	 => __( 'Product Code Settings', 'product-code-for-woocommerce' ),
		'type'	 => 'title',
		'id'	 => 'product_code'
	    );

	    // Add first checkbox option
	    $settings_slider[] = array(
		'name'	 => __( 'Show Product Code', 'product-code-for-woocommerce' ),
		'id'	 => 'product_code',
		'type'	 => 'checkbox',
		'css'	 => 'min-width:300px;',
		'desc'	 => __( 'Display product code on user side on the products posts, checkout, cart, and receipts', 'product-code-for-woocommerce' ),
	    );

	    $settings_slider[]	 = array(
		'name'		 => __( 'Field Title', 'product-code-for-woocommerce' ),
		'desc_tip'	 => true,
		'id'		 => 'product_code_text',
		'type'		 => 'text',
		'default'	 => "Product Code",
		'desc'		 => __( 'Field title can be edited to read anything, no longer than 18 characters including spaces', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[]	 = array(
		'name'	 => __( 'Enable Second Product Code', 'product-code-for-woocommerce' ),
		'id'	 => 'product_code_second_show',
		'type'	 => 'checkbox',
		'css'	 => 'min-width:300px;',
		'desc'	 => __( 'Display second product code field.', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[]	 = array(
		'name'	 => __( 'Show Second Product Code', 'product-code-for-woocommerce' ),
		'id'	 => 'product_code_second',
		'type'	 => 'checkbox',
		'css'	 => 'min-width:300px;',
		'desc'	 => __( 'Display product code on user side on the products posts, checkout, cart, and receipts', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[]	 = array(
		'name'		 => __( 'Second Field Title', 'product-code-for-woocommerce' ),
		'desc_tip'	 => true,
		'id'		 => 'product_code_text_second',
		'type'		 => 'text',
		'default'	 => "Product Code",
		'desc'		 => __( 'Field title can be edited to read anything, no longer than 18 characters including spaces', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[]	 = array(
		'name'	 => __( 'Hide Field When Empty', 'product-code-for-woocommerce' ),
		'id'	 => 'product_code_hide_empty_field',
		'type'	 => 'checkbox',
		'css'	 => 'min-width:300px;',
		'desc'	 => __( 'Hide primary and secondary user side display when field is left blank', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[] = array(
		'name'	 => __( 'Enable Structured Data Product', 'product-code-for-woocommerce' ),
		'id'	 => 'pcfw_structure_data',
		'type'	 => 'checkbox',
		'css'	 => 'min-width:300px;',
		'desc'	 => __( 'Apply structure data property set on the product code.', 'product-code-for-woocommerce' ),
	    );
	    $settings_slider[]	 = array(
		'name'		 => __( 'Structured Data Product', 'product-code-for-woocommerce' ),
		'desc'		 => __( 'Choose the structured data property set when using a trade number for schema compliance.', 'product-code-for-woocommerce' ),
		'id'		 => 'pcfw_structured_data_field',
		'default'	 => 'gtin',
		'type'		 => 'select',
		'options'	 => array(
		    'gtin'	 => 'gtin',
		    'gtin8'	 => 'gtin8',
		    'gtin12' => 'gtin12',
		    'gtin13' => 'gtin13',
		    'gtin14' => 'gtin14',
		    'isbn'	 => 'isbn',
		    'mpn'	 => 'mpn',
		),
		'desc_tip'	 => true,
	    );

	    $settings_slider[] = array( 'type' => 'sectionend', 'id' => 'product_code_settings' );
	    return $settings_slider;
	}
	return $settings;
    }

    public function add_admin_field_file( $value ) {
	?>

	<tr valign="top">
	    <th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value[ 'id' ] ); ?>"><?php echo esc_html( $value[ 'title' ] ); ?></label>

	    </th>

	    <td class="forminp forminp-<?php echo sanitize_title( $value[ 'type' ] ) ?>">

		<input
		    name ="<?php echo esc_attr( $value[ 'name' ] ); ?>"
		    id   ="<?php echo esc_attr( $value[ 'id' ] ); ?>"
		    type ="file"
		    style="<?php echo esc_attr( $value[ 'css' ] ); ?>"
		    value="<?php echo esc_attr( $value[ 'name' ] ); ?>"
		    class="<?php echo esc_attr( $value[ 'class' ] ); ?>"
		    /> 


	    </td>
	</tr>

	<?php
    }

    public function add_admin_field_button( $value ) {
	?>

	<tr valign="top">
	    <th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value[ 'id' ] ); ?>"><?php echo esc_html( $value[ 'title' ] ); ?></label>

	    </th>

	    <td class="forminp forminp-<?php echo sanitize_title( $value[ 'type' ] ) ?>">

		<a     target="_blank"
		       href="<?php echo esc_attr( $value[ 'href' ] ); ?>"
		       name ="<?php echo esc_attr( $value[ 'name' ] ); ?>"
		       id   ="<?php echo esc_attr( $value[ 'id' ] ); ?>"
		       style="<?php echo esc_attr( $value[ 'css' ] ); ?>"
		       value="<?php echo esc_attr( $value[ 'name' ] ); ?>"
		       class="<?php echo esc_attr( $value[ 'class' ] ); ?>"
		       ><?php echo esc_attr( $value[ 'name' ] ); ?></a>


	    </td>
	</tr>

	<?php
    }

    public function ajax_get_product_code() {

	$simple_field_name	 = PRODUCT_CODE_FIELD_NAME;
	if ( $_POST[ 'code_num' ] == 'second' )
	    $simple_field_name	 = PRODUCT_CODE_FIELD_NAME_SECOND;

	$value = get_post_meta( (int) $_POST[ 'product_code_id' ], $simple_field_name, true );

	echo json_encode( [
	    'status' => ! empty( $value ),
	    'data'	 => $value
	] );

	die;
    }

    public function show_woocommerce_missing_notice() {
	echo '<div class="notice notice-error is-dismissible">
            <p>' . __( 'Product Code for WooCommerce Add-on requires Woocommerce plugin to be installed and activated.', 'product-code-for-woocommerce' ) . '</p>
            </div>';
    }

    /**
     * Add the product code to structured data.
     * @param $data
     *
     * @return mixed
     */
    public function structured_data_product_code( $data, $product ) {
	// Applies only when enable
	if( get_option( 'pcfw_structure_data' ) == 'yes' ) {
	    $property		 = apply_filters( 'pcfw_structured_data_property', get_option( 'pcfw_structured_data_field', 'gtin' ), $product );
	
	    $product_code = get_post_meta( $product->get_id(), '_product_code' );
	    //$product_code_second = get_post_meta( $product->get_id(), '_product_code_second' );
	
	    $data[ $property ]	 = ! empty( $product_code ) ? $product_code : "N/A" ;
	    //$data[ $property ][]	 = ! empty( $product_code_second )? $product_code_second : "N/A";
	}
	
	return $data;
    }
    
    /**
     * @param $atts array
     * @return string
     */
    public function product_code_shortcode( $atts ) {
	global $post;

	$atts = shortcode_atts( array(
		'id'            => '',
		'pc_label'      => get_option( 'product_code_text', __( "Product Code:", 'product-code-for-woocommerce' ) ),
		'pcs_label'	=> get_option( 'product_code_text_second', __( "Product Code Second:", 'product-code-for-woocommerce' ) ),
		'wrapper'       => is_shop() ? 'div' : 'span',
		'wrapper_code'  => 'span',
		'class_wrapper' => 'pcfw_code_wrapper',
		'class'         => 'pcfw_code',
	), $atts, 'pcfw_display_product_code' );
	
	if ( ! empty( $atts[ 'id' ] ) ) {
	    $product_data = get_post( $atts[ 'id' ] );
	} elseif ( ! is_null( $post ) ) {
	    $product_data = $post;
	} else {
	    return '';
	}

	$product = is_object( $product_data ) && in_array( $product_data->post_type, array(
	    'product',
	    'product_variation'
	), true ) ? wc_setup_product_data( $product_data ) : false;

	if ( ! $product ) {
	    return '';
	}
	
	// Product Code
	$is_product_code = $product->get_meta( '_product_code' );
	$is_product_code_second = $product->get_meta( '_product_code_second' );
	
	$product_code = ( ! empty( $is_product_code ) ) ? $is_product_code : "N/A";
	$product_code_second = ( ! empty( $is_product_code_second ) ) ? $is_product_code_second : "N/A";
	
	ob_start();
	
	if ( $product_code || ( get_option( 'product_code_hide_empty_field' ) != 'yes' && ! $product_code ) || ( is_single() && $product->is_type( 'variable' ) ) ):
	    echo sprintf( '<%1$s class="%3$s">%2$s: <%4$s class="%5$s" data-product-id="%7$s">%6$s</%4$s></%1$s>', $atts['wrapper'], $atts['pc_label'], esc_attr( $atts['class_wrapper'] ), $atts['wrapper_code'], esc_attr( $atts['class'] ), $product_code, $product->get_id() );
	endif;
	
	if( get_option( 'product_code_second' ) == 'yes' ){ 
	    if( $product_code_second || ( get_option('product_code_hide_empty_field') !='yes' && ! $product_code_second) || ( is_single() && $product->is_type( 'variable' ) ) ):
		echo sprintf( '<%1$s class="%3$s">%2$s: <%4$s class="%5$s" data-product-id="%7$s">%6$s</%4$s></%1$s>', $atts['wrapper'], $atts['pcs_label'], esc_attr( $atts['class_wrapper'] ), $atts['wrapper_code'], esc_attr( $atts['class'] ), $product_code_second, $product->get_id() );
	    endif;
	}
	
	return ob_get_clean();
    }

}

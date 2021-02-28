<?php
/**
 * Electro Child
 *
 * @package electro-child
 */

/**
 * Include all your custom code here
 */


add_filter( 'woocommerce_available_payment_gateways', 'x34fg_gateway_disable_shipping' );

function x34fg_gateway_disable_shipping( $available_gateways ) {

    global $woocommerce;

    if ( !is_admin() ) {

        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

        $chosen_shipping = $chosen_methods[0];

        if ( isset( $available_gateways['cod'] ) && 0 === strpos( $chosen_shipping, 'local_pickup' ) ) {
            unset( $available_gateways['cod'] );
        }

    }

return $available_gateways;
}


add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_uri());
}


/** Code for Bargain Button **/

add_filter('woocommerce_product_data_tabs', 'product_keyword_data_tab' );
function product_keyword_data_tab( $tabs ){
	$tabs['bargain'] = array(
		'label'    => 'Product Option',
		'target'   => 'is_product_bargins',
		'priority' => 22,
	);
	return $tabs; 
}


add_action( 'woocommerce_product_data_panels', 'woo_add_custom_data_fields' );
function woo_add_custom_data_fields() {
  //global $woocommerce, $post;  
	global $product_object;
	$values = $product_object->get_meta('_is_product_bargin');
	//echo $values;
	echo '<div id="is_product_bargins" class="panel woocommerce_options_panel hidden">'; 
	woocommerce_wp_checkbox( 
	array( 
		'id'            => '_is_product_bargin', 
		'wrapper_class' => '', 
		'value' => empty($values) ? 'yes' : $values,
		'label'         => __('Product Bargain', 'woocommerce' ), 
		'description'   => __( 'Unchecked if product is not negotiable', 'woocommerce' ) 
		)
	);		
	echo '</div>';
}

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );
function woo_add_custom_general_fields_save( $post_id ){	
	$woocommerce_is_product_bargin_checkbox = isset( $_POST['_is_product_bargin'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_is_product_bargin', $woocommerce_is_product_bargin_checkbox );	

}

add_action( 'woocommerce_after_add_to_cart_button', 'is_product_bargin_cta', 60);
function is_product_bargin_cta()
{
	global $product;
	$product_id = $product->get_id();
	$is_custom = get_post_meta($product_id, '_is_product_bargin', true);
	//echo $is_custom;
	if ($is_custom == "no"){		
			$my_custom_link = home_url('/my_page_slug/');
			echo '<button class="btn-bargain single_add_to_cart_button button" href="' . esc_url( $my_custom_link ) .'">' . __( "Bargain", "my_theme_slug" )  . '</button>'; 
	}
}


function bargain_form(){
	?>
		<div class="modal fade" id="bargainModal" role="dialog">
			<div class="modal-dialog">

			  <!-- Modal content-->
			  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal">&times;</button>
				  <h4 class="modal-title">Product Bargain</h4>
				</div>
				<div class="modal-body">
					  <?php echo do_shortcode('[contact-form-7 id="8250" title="Bargain Form"]'); ?>
				</div>
			  </div>

			</div>
		  </div>
		
			 <script type="text/javascript">
          jQuery(document).ready(function(){
			  var $ = jQuery.noConflict();
			$('.btn-bargain').click(function(e){
				e.preventDefault();
				$('#product-url').val(window.location.href);
				$('#bargainModal').css({"display": "block", "opacity": "1"});
			})
			  
			$('#bargainModal .close').click(function(e){
				e.preventDefault();
				$('#bargainModal').css({"display": "none", "opacity": "0"});
			})
		})
          
     
						function ifchecked(){
							  var checkBox = document.getElementById("isAssembled");
								 var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
							  if (checkBox.checked == true){
								 var checked = 'checked';
							  }
							else{
								var checked = '';
							}
							 jQuery.ajax({
								 	  type: 'POST',
									  url: ajaxurl,
									  data: {
									      action: 'isAssembled',
										  checked: checked
									  },	
								 	  success:function(res){
								 		window.location.reload();
							 		  }
								  })
							
						}
					</script>
			
	<?php
}
add_action('wp_footer', 'bargain_form');

add_action('init', 'start_session', 1);
function start_session() {
	if(!session_id()) {
		session_start();
	}
}





add_action( 'woocommerce_cart_calculate_fees', 'custom_fee_based_on_cart_total', 10, 1 );
function custom_fee_based_on_cart_total( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    global $wpdb;
    $table = $wpdb->prefix.'charges';
    $sql = $wpdb->get_results("SELECT * FROM $table");
    if(array_key_exists("customize_pc_total", $_SESSION)){
        $final_total = $_SESSION['customize_pc_total'];
        //var_dump($final_total);
        $test = add_filter('woocommerce_cart_item_subtotal', 'itemSubTotalFilter', 30, 0);

        foreach ($sql as $value) {
            if($final_total >= $value->min_amount && $final_total <= $value->max_amount){
                $discount = $value->charges;
            }
        }
        $fee = $discount;
        if ( $fee != 0  && $_SESSION['isAssembled'] == "checked") {
            //$cart->add_fee( __( "Assemble Charges", "woocommerce" ), $fee, false );
            $cart->add_fee( "Assemble Charges", $fee );
        }
    }

}

add_action('wp_logout','end_session');
add_action('wp_login','end_session');
function end_session() {
	session_destroy ();
}

// function add_assemble_price($total_price, $charges){
// 	echo esc_html__( 'Assemble Charges: ', 'woocommerce-product-builder' );
// 	echo '<span class="woocommerce-Price-amount amount"><bdi>'. get_woocommerce_currency_symbol().''.$charges. '</bdi></span><br>'; 
// 	echo esc_html__( 'Subtotal: ', 'woocommerce-product-builder' );
// 	echo '<span class="woocommerce-Price-amount amount"><bdi>'. get_woocommerce_currency_symbol().''.$total_price. '</bdi></span><br>';
// 	$total_price1 = $total_price + $charges;
// 	return $total_price1;
// }
// add_action('get_assemble_price','add_assemble_price', 10, 2);

// function add_assemble_price_table($total_price, $charges){
// 	echo '<tr class="woopb-total-preview-custom"><th colspan="2" style="text-align: center">Assemble Charges</th><th colspan="2">'.$charges.''.get_woocommerce_currency_symbol().'</th></tr><tr class="woopb-total-preview-custom"><th colspan="2" style="text-align: center">Subtotal</th><th colspan="2">'.$total_price.''.get_woocommerce_currency_symbol().'</th></tr>';
// 	$total_price1 = $total_price + $charges;
// 	return $total_price1;
// }
// add_action('get_assemble_price_preview','add_assemble_price_table', 10, 2);

function add_assemble_price_table1($total_price){
	$checked = $_SESSION['isAssembled'];
//	echo "<label><input type='checkbox' name='automatic' id='isAssembled' value='1' onclick='ifchecked()' ".$checked."/> Checked if you want assemble PC</label>";
//	echo '<br>';
	if($_SESSION['isAssembled'] == 'checked'){							
		global $wpdb;
		$table = $wpdb->prefix.'charges';
		$sql = $wpdb->get_results("SELECT * FROM $table");
		foreach ($sql as $value) {
			//echo $value->min_amount.'<br>'.$value->max_amount;
			if($total_price >= $value->min_amount && $total_price <= $value->max_amount){
				$charges = $value->charges;
				$subtotal = $total_price;
				$total_price = $total_price + $charges;
				$_SESSION['customize_pc_total'] = $subtotal;
				echo'<label><span>Subtotal:</span> '.$subtotal.''.get_woocommerce_currency_symbol().'</label><br>';
				echo '<label><span>Assemble Charges:</span> '.$charges.''.get_woocommerce_currency_symbol().'</label><br>';
				echo'<label><span>Total:</span> '.$total_price.''.get_woocommerce_currency_symbol().'</label><br>';
				return $total_price1;
			}
		}	
	}
	else{
		echo'<label> '.$total_price.''.get_woocommerce_currency_symbol().'</label>';
		return $total_price;
	}
}
add_action('get_assemble_price_preview1','add_assemble_price_table1', 10, 1);

add_action('wp_ajax_isAssembled','isAssembled');
add_action('wp_ajax_nopriv_isAssembled','isAssembled');
function isAssembled(){
	$checked = $_POST['checked'];
	$_SESSION['isAssembled'] = $checked;
	echo $checked;
	die();
}

//function remove_dokan_frontend_vendor_registration() {
//    $Dokan_Pro = dokan();
//    remove_action( 'woocommerce_after_my_account', array( $Dokan_Pro, 'dokan_account_migration_button' ));
//}
//add_action( 'init', 'remove_dokan_frontend_vendor_registration' );

add_action( 'wp_footer', 'trigger_for_ajax_add_to_cart' );
    function trigger_for_ajax_add_to_cart() {

       foreach( WC()->cart->get_cart() as $cart_item ){
        $product_id = $cart_item['product_id'];
		$test = $cart_item['wcpw_kit_id'];
            ?>
                <script type="text/javascript">
                    (function($){
                        //alert('dsfdssdfsdsd')
                        //console.log("<?php echo $test?>");
                        $(".post-<?php echo $product_id; ?>").addClass('cart-added-class');

                    })(jQuery);
                </script>
            <?php

      }

}


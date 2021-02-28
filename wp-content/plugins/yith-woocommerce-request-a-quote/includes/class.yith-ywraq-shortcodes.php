<?php
/**
 * YITH_YWRAQ_Shortcodes add shortcodes to the request quote list
 *
 * @class   YITH_YWRAQ_Shortcodes
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class YITH_YWRAQ_Shortcodes
 */
class YITH_YWRAQ_Shortcodes {


	/**
	 * Constructor for the shortcode class
	 */
	public function __construct() {
		add_shortcode( 'yith_ywraq_request_quote', array( $this, 'request_quote_list' ) );
	}

	/**
	 * Print request a quote list.
	 *
	 * @param   array $atts
	 * @param   null  $content
	 *
	 * @return false|string
	 */
	public function request_quote_list( $atts, $content = null ) {

		$raq_content  = YITH_Request_Quote()->get_raq_return();
		$args         = array(
			'raq_content'   => $raq_content,
			'template_part' => 'view',
		);
		$args['args'] = $args;

		ob_start();
		wc_get_template( 'request-quote.php', $args, '', YITH_YWRAQ_TEMPLATE_PATH );
		return ob_get_clean();
	}


}


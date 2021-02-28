<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$image_url = WCPFC_PRO_PLUGIN_URL . 'admin/images/right_click.png';
?>
<div class="dotstore_plugin_sidebar">
	<?php 
	$review_url = '';
	$plugin_at = '';
	$review_url = esc_url( 'https://wordpress.org/plugins/woo-conditional-product-fees-for-checkout/#reviews' );
	$plugin_at = 'WP.org';
	?>
	<div class="dotstore-important-link">
		<div class="image_box">
			<img src="<?php 
echo  esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/rate-us.png' ) ;
?>" alt="">
		</div>
		<div class="content_box">
			<h3><?php esc_html_e( 'Like This Plugin?', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
			<p><?php esc_html_e( 'Your Review is very important to us as it helps us to grow more.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
			<a class="btn_style" href="<?php echo $review_url;?>" target="_blank"><?php esc_html_e( 'Review Us on', 'woocommerce-conditional-product-fees-for-checkout' ); ?> <?php echo $plugin_at; ?></a>
		</div>
	</div>
	<div class="dotstore_discount_voucher">
			<span
				class="dotstore_discount_title"><?php 
esc_html_e( 'Discount Voucher', 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
			<span
				class="dotstore-upgrade"><?php 
esc_html_e( 'Upgrade to premium now and get', 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
			<strong
				class="dotstore-OFF"><?php 
esc_html_e( '10% OFF', 'woocommerce-conditional-product-fees-for-checkout' );
?></strong>
			<span
				class="dotstore-with-code"><?php 
esc_html_e( 'with code', 'woocommerce-conditional-product-fees-for-checkout' );
?>
			<b><?php 
esc_html_e( 'DOT10', 'woocommerce-conditional-product-fees-for-checkout' );
?></b></span>
			<a class="dotstore-upgrade"
			   href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-extra-fees-plugin' ) ;
?>"
			   target="_blank"><?php 
esc_html_e( 'Upgrade Now!', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
		</div>
		
	<div class="dotstore-important-link">
		<div class="video-detail important-link">
			<a href="https://www.youtube.com/watch?v=7JJhUsDwJy4" target="_blank">
				<img width="100%"
				     src="<?php 
echo  esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/plugin-videodemo.png' ) ;
?>"
				     alt="<?php 
esc_html__( 'WooCommerce Conditional Product Fees For Checkout', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
			</a>
		</div>
	</div>

	<div class="dotstore-important-link">
		<h2><span
				class="dotstore-important-link-title"><?php 
esc_html_e( 'Important link', 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
		</h2>
		<div class="video-detail important-link">
			<ul>
				<li>
					<img src="<?php 
echo  esc_url( $image_url ) ;
?>">
					<a target="_blank"
					   href="<?php 
echo  esc_url( "https://www.thedotstore.com/docs/plugins/woocommerce-conditional-product-fees-checkout" ) ;
?>"><?php 
esc_html_e( 'Plugin documentation', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
				</li>
				<li>
					<img src="<?php 
echo  esc_url( $image_url ) ;
?>">
					<a target="_blank"
					   href="<?php 
echo  esc_url( "https://www.thedotstore.com/support/" ) ;
?>"><?php 
esc_html_e( 'Support platform', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
				</li>
				<li>
					<img src="<?php 
echo  esc_url( $image_url ) ;
?>">
					<a target="_blank"
					   href="<?php 
echo  esc_url( "https://www.thedotstore.com/suggest-a-feature" ) ;
?>"><?php 
esc_html_e( 'Suggest A Feature', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
				</li>
				<li>
					<img src="<?php 
echo  esc_url( $image_url ) ;
?>">
					<a target="_blank"
					   href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-conditional-product-fees-checkout" ) ;
?>"><?php 
esc_html_e( 'Buy Plugin', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
				</li>
			</ul>
		</div>
	</div>

	<!-- html for popular plugin !-->
	<!-- html for popular plugin !-->

<div class="dotstore-important-link">
        <h2>
            <span class="dotstore-important-link-title">
                <?php 
esc_html_e( 'Our Popular plugins', 'woocommerce-conditional-product-fees-for-checkout' );
?>
            </span>
        </h2>
        <div class="video-detail important-link">
            <ul>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Advanced-Flat-Rate-Shipping-Method.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Flat Rate Shipping Plugin For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/flat-rate-shipping-plugin-for-woocommerce/" ) ;
?>">
						<?php 
esc_html_e( 'Flat Rate Shipping Plugin For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/plugn-login-128.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Hide Shipping Method For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/hide-shipping-method-for-woocommerce/" ) ;
?>">
						<?php 
esc_html_e( 'Hide Shipping Method For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/WooCommerce Conditional Discount Rules For Checkout.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Conditional Discount Rules For WooCommerce Checkout', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-conditional-discount-rules-for-checkout/" ) ;
?>">
						<?php 
esc_html_e( 'Conditional Discount Rules For WooCommerce Checkout', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/WooCommerce-Blocker-Prevent-Fake-Orders.png' ) ;
?>" alt="<?php 
esc_attr_e( 'WooCommerce Blocker – Prevent Fake Orders', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-blocker-lite-prevent-fake-orders-blacklist-fraud-customers/" ) ;
?>">
						<?php 
esc_html_e( 'WooCommerce Anti-Fraud', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Advanced-Product-Size-Charts-for-WooCommerce.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Product Size Charts Plugin For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-advanced-product-size-charts/" ) ;
?>">
						<?php 
esc_html_e( 'Product Size Charts Plugin For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/wcbm-logo.png' ) ;
?>" alt="<?php 
esc_attr_e( 'WooCommerce Category Banner Management', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-category-banner-management/" ) ;
?>">
						<?php 
esc_html_e( 'WooCommerce Category Banner Management', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/woo-product-att-logo.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Product Attachment For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-product-attachment/" ) ;
?>">
						<?php 
esc_html_e( 'Product Attachment For WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="view-button">
            <a class="view_button_dotstore" href="<?php 
echo  esc_url( "http://www.thedotstore.com/plugins/" ) ;
?>"  target="_blank"><?php 
esc_html_e( 'View All', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
        </div>
    </div>
	<!-- html end for popular plugin !-->
</div>
</div>
</body>
</html>
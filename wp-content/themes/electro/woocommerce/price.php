/**
 * Single Product Price, including microdata for SEO
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $product;
?>
<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">

    <p class="price"> <?php 
        $stockamount = $product->get_stock_quantity();
        $price = $product->get_price_html();
        $pricelabel = "";
        if($stockamount == 0)
        {
            echo $pricelabel;
        }
        else
        {
            echo $price;            
        }; 
    ?>
    </p>

    <meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
    <meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
    <link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />

</div>
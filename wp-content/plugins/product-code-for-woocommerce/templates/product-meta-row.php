<?php
/**
 * A template for row of a product detail on a single product page.
 *
 * @package PcfWooCommerce
 *
 * @var $c callable The function used to retrieve context values.
 */
$product_type = get_the_terms( $post->ID,'product_type')[0]->slug;

?>
<?php 	if($value || (get_option('product_code_hide_empty_field') !='yes' && !$value) || $product_type=="variable" ): ?>
<span class="wo_productcode">
    <input type="hidden" value="<?php echo $post->ID?>" id="product_id" />
	<span><?php echo ( $text ? $text : __( 'Product Code', 'product-code-for-woocommerce' ) )?>:</span>
	<span class="stl_codenum"><?php echo !$value ? __( 'N/A', 'product-code-for-woocommerce' ) : $value?></span>
</span>
<?php endif; ?>
<?php if( get_option( 'product_code_second' ) == 'yes' ){ 
	if($value_second || (get_option('product_code_hide_empty_field') !='yes' && !$value_second) || $product_type=="variable" ):
?>
<span class="wo_productcode_second">
    <input type="hidden" value="<?php echo $post->ID?>" id="product_id_second" />
	<span><?php echo ( $text_second ? $text_second : __( 'Product Code', 'product-code-for-woocommerce' ) )?>:</span>
	<span class="stl_codenum_second"><?php echo !$value_second ? __( 'N/A', 'product-code-for-woocommerce' ) : $value_second?></span>
</span>
	<?php endif; ?>
<?php }?>
<style type="text/css">
	.hide_pcode .product-type-variable .product_meta .wo_productcode,
	.hide_pcode .product-type-variable .product_meta .wo_productcode_second{
	    display:none;
	}
</style>
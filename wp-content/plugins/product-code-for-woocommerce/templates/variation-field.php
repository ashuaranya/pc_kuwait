<?php
/**
 * A template for the variation field.
 *
 * @package PcfWooCommerce
 *
 * @var $c callable The function used to retrieve context values.
 */

?>
<div class="form-row form-row-first">
    <?php 
    $label = $this->get_field_title_text();
    $label_second = $this->get_second_field_title_text();
    woocommerce_wp_text_input([
        'id'          => sprintf( '%s_%d', $field_name, $i ),
        'label'       => $label,
        'desc_tip'    => true,
        'description' => __( $label . ' refers to a company’s unique internal product identifier, needed for online product fulfillment', 'product-code-for-woocommerce' ),
        'value'       => $code,
    ]); 
    
    if($displaySecond == 'yes'){    
	woocommerce_wp_text_input([
	    'id'          => sprintf( '%s_%d', $field_name_second, $i ),
	    'label'       => $label_second,
	    'desc_tip'    => true,
	    'description' => __( $label_second . ' refers to a company’s unique internal product identifier, needed for online product fulfillment', 'product-code-for-woocommerce' ),
	    'value'       => $code_second,
	]);
    }
    ?>
    <!-- <label for="product_code_<?php echo $variation->ID?>">
        <strong>
            <?php echo $label; ?>
        </strong>
    </label>
    <input type="text" value="<?php echo $code?>" name="<?php echo $field_name?>" id="product_code_<?php echo $variation->ID?>" /> -->
</div><div style="clear:both;"></div>

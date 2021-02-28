<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;
use WCProductsWizard\Cart;

$arguments = Template::getHTMLArgs([
    'formId' => null,
    'attachedMode' => false,
    'addToCartButtonText' => Settings::getPost($id, 'add_to_cart_button_text'),
    'addToCartButtonClass' => Settings::getPost($id, 'add_to_cart_button_class')
]);
$total_price = Cart::getTotal($id);
global $wpdb;
$table = $wpdb->prefix.'charges';
$sql = $wpdb->get_results("SELECT * FROM $table");
$_SESSION['isAssembled'] = "";
?>

<button type="button" class="btn woocommerce-products-wizard-control is-add-to-cart <?php
echo esc_attr($arguments['addToCartButtonClass']);
?>" data-toggle="modal" data-target="#exampleModalCenter">
    <span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['addToCartButtonText']); ?>
        <!--spacer-->
    </span>
</button>
<!--spacer-->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Would You like to assemble pc?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mod">
                <p>We also providing option of assembling pc with minimal cost.</p>
                <label><input type='checkbox' name='automatic' id='isAssembled' value='1' onclick='isAssembled()'/> Checked if you want assemble PC</label>
                <div style="display:none" id="with-assemble">
                    <hr>
                <?php
                    global $wpdb;
                    $table = $wpdb->prefix.'charges';
                    $sql = $wpdb->get_results("SELECT * FROM $table");
                    foreach ($sql as $value) {
                         if((floatval($total_price) >= floatval(trim($value->min_amount)) && floatval($total_price) <= floatval(trim($value->max_amount)))){
                            $charges = $value->charges;
                            $subtotal = $total_price;
                            $total_price_new = $total_price + $charges;
                            $_SESSION['customize_pc_total'] = $subtotal;
                            echo'<label><span>Subtotal:</span> '.$subtotal.''.get_woocommerce_currency_symbol().'</label><br>';
                            echo '<label><span>Assemble Charges:</span> '.$charges.''.get_woocommerce_currency_symbol().'</label><br>';
                            echo'<label><span>Total:</span> '.$total_price_new.''.get_woocommerce_currency_symbol().'</label><br>';
                        }
                    }
                ?>
                </div>
                <div id="without-assemble"><hr>
                    <?php echo'<label>&nbsp</label><br>';
                    echo '<label>&nbsp</label><br>';
                     echo'<label><span>Total:</span> '.$total_price.''.get_woocommerce_currency_symbol().'</label>'; ?>
                </div>
                <hr>
                <div id="modal-footer">
                    <button class="btn woocommerce-products-wizard-control is-add-to-cart <?php
                    echo esc_attr($arguments['addToCartButtonClass']);
                    ?>"
                            form="<?php echo esc_attr($arguments['formId']); ?>"
                            type="submit" name="add-to-main-cart"
                        <?php if (filter_var($arguments['attachedMode'], FILTER_VALIDATE_BOOLEAN)) { ?>
                            data-component="wcpw-add-to-cart"
                        <?php } else { ?>
                            data-component="wcpw-add-to-cart wcpw-nav-item"
                            data-nav-action="add-to-main-cart"
                        <?php } ?>><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['addToCartButtonText']); ?>
        <!--spacer-->
    </span></button>
                </div>
            </div>

        </div>
    </div>
</div>
<style>
    #without-assemble{
        min-height: 108px;
    }
    #with-assemble{
        min-height: 108px;
    }
    .mod{
        min-height: 250px;
        text-align:left;
    }
</style>
<script>
    function isAssembled() {
        var checkBox = document.getElementById("isAssembled");
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        if (checkBox.checked == true){
            var checked = 'checked';
            document.getElementById("with-assemble").style.display = "block";
            document.getElementById("without-assemble").style.display = "none";
        }
        else{
            var checked = '';
            document.getElementById("with-assemble").style.display = "none";
            document.getElementById("without-assemble").style.display = "block";
        }
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'isAssembled',
                checked: checked
            },
            success:function(res){
            }
        })
    }

</script>
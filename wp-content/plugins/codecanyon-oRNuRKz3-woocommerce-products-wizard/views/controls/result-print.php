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

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'resultPdfButtonText' => "Print",
    'resultPdfButtonClass' => "btn-success"
]);
?>
<button class="btn woocommerce-products-wizard-control is-result-pdf <?php
echo esc_html($arguments['resultPdfButtonClass']);
?>"
        onclick="print_cart()"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['resultPdfButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->

<script>
    function print_cart() {
        var printWindow = window.open(
            '?wcpw-result-pdf=<?php echo esc_attr($arguments['id']); ?>',
            'Print',
            'left=200',
            'top=200',
            'width=950',
            'height=500',
            'toolbar=0',
            'resizable=0'
        );
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }

</script>

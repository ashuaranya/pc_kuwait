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
    'resultPdfButtonText' => Settings::getPost($id, 'result_pdf_button_text'),
    'resultPdfButtonClass' => Settings::getPost($id, 'result_pdf_button_class')
]);
?>
<a class="btn woocommerce-products-wizard-control is-result-pdf <?php
    echo esc_html($arguments['resultPdfButtonClass']);
    ?>"
    href="?wcpw-result-pdf=<?php echo esc_attr($arguments['id']); ?>"
    target="_blank"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['resultPdfButtonText']); ?>
        <!--spacer-->
    </span></a>
<!--spacer-->

<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Form;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs();
$items = Form::getPaginationItems($arguments);

if (empty($items)) {
    return;
}
?>
<nav class="woocommerce-products-wizard-form-pagination"
    aria-label="<?php esc_attr_e('Page navigation', 'woocommerce-products-wizard'); ?>">
    <ul class="woocommerce-products-wizard-form-pagination-list pagination" data-component="wcpw-form-pagination">
        <?php foreach ($items as $item) { ?>
            <li class="page-item <?php echo esc_attr($item['class']); ?>"><?php
                echo $item['innerHtml'];
                ?></li>
        <?php } ?>
    </ul>
</nav>

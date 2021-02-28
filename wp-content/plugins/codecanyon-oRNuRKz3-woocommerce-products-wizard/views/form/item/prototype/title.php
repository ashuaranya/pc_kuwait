<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'product' => null,
    'titleSubClass' => '',
    'enableTitleLink' => false
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}
?>
<h3 class="<?php echo esc_attr(trim($arguments['titleSubClass'] . ' ' . $arguments['class'])); ?>-title"><?php
    if ($arguments['enableTitleLink']) {
        echo '<a href="' . $product->get_permalink()
            . '" target="_blank" class="woocommerce-products-wizard-outer-link '
            . esc_attr($arguments['class']) . '-title-link">';
    }

    echo $product->get_title();

    if ($arguments['enableTitleLink']) {
        echo '</a>';
    }
    ?></h3>

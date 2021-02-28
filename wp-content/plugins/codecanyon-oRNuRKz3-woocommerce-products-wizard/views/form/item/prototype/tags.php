<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'product' => null,
    'class' => 'woocommerce-products-wizard-form-item'
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$tags = get_the_terms($product->get_id(), 'product_tag');

if (empty($tags) || is_wp_error($tags)) {
    return;
}
?>
<dl class="<?php echo esc_attr($arguments['class']); ?>-tags">
    <dt class="<?php echo esc_attr($arguments['class']); ?>-tags-name sr-only"><?php
        esc_html_e('Tags', 'woocommerce');
        ?></dt>
    <?php foreach ($tags as $tag) { ?>
        <dd class="<?php echo
            esc_attr("is-id-{$tag->term_id} " . $arguments['class']);
            ?>-tags-value badge badge-primary"><?php
            echo wp_kses_post($tag->name);
            ?></dd>
    <?php } ?>
</dl>

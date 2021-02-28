<?php
global $woocommerce;

$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
$default = [
    'action' => 'woocommerce_json_search_products_and_variations',
    'limit' => 30,
    'include' => '',
    'exclude' => 0,
    'allowClear' => true,
    'multiple' => true,
    'placeholder' => esc_html__('Search for a product&hellip;', 'woocommerce')
];

$defaultQueryArgs = [
    'post_type' => ['product', 'product_variation'],
    'post_status' => ['publish', 'future', 'private'],
    'include' => $args['value']
];

$queryArgs = isset($modelItem['queryArgs'])
    ? array_replace($defaultQueryArgs, $modelItem['queryArgs'])
    : $defaultQueryArgs;

$inputAttributes = array_replace($default, $modelItem);
$isMultiply = filter_var($inputAttributes['multiple'], FILTER_VALIDATE_BOOLEAN);

if (version_compare($woocommerce->version, '3.0.0', '>=')) {
    $products = [];

    if (!$args['asTemplate'] && !empty($args['value'])) {
        $productsPosts = get_posts($queryArgs);

        foreach ($productsPosts as $productsItem) {
            $products[$productsItem->ID] = rawurldecode(
                $productsItem->post_title . ' (#' . $productsItem->ID . ')'
            );
        }
    }
    ?>
    <select <?php
        echo $isMultiply ? 'multiple="multiple" ' : '';
        echo ($args['asTemplate'] ? 'data-make-' : '') . 'class="wc-product-search" ';
        echo ($args['asTemplate'] ? 'data-make-' : '')
            . 'name="' . esc_attr($args['name']) . ($isMultiply ? '[]' : '') . '" ';
        ?>
        data-placeholder="<?php echo esc_attr($inputAttributes['placeholder']); ?>"
        data-multiple="<?php echo esc_attr(var_export($isMultiply, true)); ?>"
        data-action="<?php echo esc_attr($inputAttributes['action']); ?>"
        data-allow-clear="<?php echo esc_attr($inputAttributes['allowClear']); ?>"
        data-limit="<?php echo esc_attr($inputAttributes['limit']); ?>"
        data-include="<?php echo esc_attr($inputAttributes['include']); ?>"
        data-exclude="<?php echo esc_attr($inputAttributes['exclude']); ?>">
        <?php
        foreach ($products as $productId => $productTitle) {
            echo '<option value="'
                . esc_attr($productId) . '" '
                . selected(true, true, false) . '>'
                . esc_html($productTitle)
                . '</option>';
        }
        ?>
    </select>
    <?php
} else {
    ?>
    <input type="text" <?php
        echo ($args['asTemplate'] ? 'data-make-' : '') . 'class="wc-product-search" ';
        echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" ';
        ?>
        data-placeholder="<?php echo esc_attr($inputAttributes['placeholder']); ?>"
        data-multiple="<?php echo esc_attr(var_export($isMultiply, true)); ?>"
        data-action="<?php echo esc_attr($inputAttributes['action']); ?>"
        data-allow-clear="<?php echo esc_attr($inputAttributes['allowClear']); ?>"
        data-limit="<?php echo esc_attr($inputAttributes['limit']); ?>"
        data-include="<?php echo esc_attr($inputAttributes['include']); ?>"
        data-exclude="<?php echo esc_attr($inputAttributes['exclude']); ?>"
        data-selected="<?php echo esc_attr(wp_json_encode($args['value'])); ?>"
        value="<?php echo esc_attr(implode(',', array_keys($args['value']))); ?>">
    <?php
}

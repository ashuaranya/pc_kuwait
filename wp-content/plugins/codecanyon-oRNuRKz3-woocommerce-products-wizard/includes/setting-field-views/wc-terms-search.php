<?php
global $woocommerce;

$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
$default = [
    'action' => 'woocommerce_json_search_terms',
    'limit' => 30,
    'include' => '',
    'exclude' => 0,
    'allowClear' => true,
    'multiple' => true,
    'placeholder' => esc_html__('Search for a term&hellip;', 'woocommerce')
];

$defaultQueryArgs = [
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'include' => $args['value']
];

$queryArgs = isset($modelItem['queryArgs'])
    ? array_replace($defaultQueryArgs, $modelItem['queryArgs'])
    : $defaultQueryArgs;

$inputAttributes = array_replace($default, $modelItem);
$isMultiply = filter_var($inputAttributes['multiple'], FILTER_VALIDATE_BOOLEAN);
$terms = [];

if (!$args['asTemplate'] && !empty($args['value'])) {
    $termsObjects = get_terms($queryArgs);

    foreach ($termsObjects as $termObject) {
        $terms[$termObject->term_id] = rawurldecode(
            $termObject->name . ' (#' . $termObject->term_id . ')'
        );
    }
}

if (version_compare($woocommerce->version, '3.0.0', '>=')) {
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
        foreach ($terms as $key => $title) {
            echo '<option value="' . esc_attr($key) . '" '
                . selected(true, true, false) . '>'
                . esc_html($title)
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
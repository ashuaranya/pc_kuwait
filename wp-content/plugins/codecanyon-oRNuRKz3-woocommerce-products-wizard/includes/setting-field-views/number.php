<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
?>
<input type="number" value="<?php echo esc_attr($args['value']); ?>"
    <?php
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" ';
    echo $args['id'] ? ' id="' . esc_attr($args['id']) . '"' : '';
    echo isset($modelItem['min']) ? ' min="' . esc_attr($modelItem['min']) . '"' : '';
    echo isset($modelItem['max']) ? ' max="' . esc_attr($modelItem['max']) . '"' : '';
    echo isset($modelItem['step']) ? ' step="' . esc_attr($modelItem['step']) . '"' : '';
    echo isset($modelItem['placeholder']) ? ' placeholder="' . esc_attr($modelItem['placeholder']) . '"' : '';
    echo isset($modelItem['data-component']) ? ' data-component="' . esc_attr($modelItem['data-component']) . '"' : '';
    ?>>

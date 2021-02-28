<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
?>
<input type="hidden" value="0"
    <?php echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" '; ?>>
<input type="checkbox" value="1"
    <?php
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '"';
    echo $args['id'] ? ' id="' . esc_attr($args['id']) . '"' : '';
    echo isset($modelItem['data-component']) ? ' data-component="' . esc_attr($modelItem['data-component']) . '"' : '';
    echo filter_var($args['value'], FILTER_VALIDATE_BOOLEAN) ? ' checked="checked" ' : '';
    ?>>

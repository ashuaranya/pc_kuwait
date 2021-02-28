<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
?>
<input type="text" value="<?php echo esc_attr($args['value']); ?>"
    <?php
    echo $args['id'] ? ' id="' . esc_attr($args['id']) . '" ' : '';
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" ';
    echo isset($modelItem['readonly']) && $modelItem['readonly'] ? ' readonly' : '';
    echo isset($modelItem['pattern']) ? ' pattern="' . esc_attr($modelItem['pattern']) . '"' : '';
    echo isset($modelItem['placeholder']) ? ' placeholder="' . esc_attr($modelItem['placeholder']) . '"' : '';
    echo isset($modelItem['data-component']) ? ' data-component="' . esc_attr($modelItem['data-component']) . '"' : '';
    ?>>

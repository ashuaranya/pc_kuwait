<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
?>
<textarea cols="30" rows="10"
    <?php
    echo $args['id'] ? ' id="' . esc_attr($args['id']) . '" ' : '';
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" ';
    echo isset($modelItem['placeholder']) ? ' placeholder="' . esc_attr($modelItem['placeholder']) . '"' : '';
    echo isset($modelItem['data-component']) ? ' data-component="' . esc_attr($modelItem['data-component']) . '"' : '';
    ?>><?php echo esc_html($args['value']); ?></textarea>

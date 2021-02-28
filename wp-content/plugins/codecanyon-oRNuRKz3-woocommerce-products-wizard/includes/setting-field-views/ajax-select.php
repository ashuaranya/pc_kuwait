<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
$multiple = isset($modelItem['multiple']) && $modelItem['multiple'];
$targetParent = isset($modelItem['target-parent']) ? $modelItem['target-parent'] : '';
$targetSelector = isset($modelItem['target-selector']) ? $modelItem['target-selector'] : '';
$action = isset($modelItem['action']) ? $modelItem['action'] : '';
$value = isset($args['value']) ? (is_array($args['value']) ? wp_json_encode($args['value']) : $args['value']) : '';
$namespace = 'wcpw';
?>
<select <?php
    echo $args['id'] ? 'id="' . esc_attr($args['id']) . '" ' : '';
    echo $multiple ? 'multiple ' : '';
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name'])
        . ($multiple ? '[]' : '') . '" ';
    ?>
    data-component="<?php echo esc_attr($namespace); ?>-ajax-select"
    data-target-parent="<?php echo esc_attr($targetParent); ?>"
    data-target-selector="<?php echo esc_attr($targetSelector); ?>"
    data-action="<?php echo esc_attr($action); ?>"
    data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>"
    data-value="<?php echo esc_attr($value); ?>"><?php
    if ($value) {
        echo '<option value="' . esc_html($value) . '">' . esc_html($value) . '</option>';
    }
    ?></select>

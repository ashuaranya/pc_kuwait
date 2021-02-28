<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
$multiple = isset($modelItem['multiple']) && $modelItem['multiple'];
$value = $args['value'];
?>
<select <?php
    echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . ($multiple ? '[]' : '') . '"';
    echo $args['id'] ? ' id="' . esc_attr($args['id']) . '"' : '';
    echo isset($modelItem['placeholder']) ? ' placeholder="' . esc_attr($modelItem['placeholder']) . '"' : '';
    echo isset($modelItem['data-component']) ? ' data-component="' . esc_attr($modelItem['data-component']) . '"' : '';
    echo $multiple ? ' multiple size="6"' : '';
    ?>>
    <?php foreach ($modelItem['values'] as $key => $name) { ?>
        <option value="<?php echo esc_attr($key); ?>"<?php
        $isSelected = (is_string($value) && $value != '' && (string) $key == (string) $value)
            || (is_array($value) && in_array($key, $value));

        echo $isSelected ? ' selected="selected"' : '';
        ?>><?php echo esc_html($name); ?></option>
    <?php } ?>
</select>

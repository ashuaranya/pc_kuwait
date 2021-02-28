<?php
$namespace = 'wcpw';
$textDomain = 'woocommerce-products-wizard';
$componentName = "{$namespace}-multi-select";
$defaults = [
    'name' => "{$namespace}-items-selected",
    'values' => []
];

$args = array_replace_recursive($defaults, $args);
$modelItem = isset($modelItem) ? $modelItem : [];
$id = md5(serialize($args));

if (!isset($args['values'][$modelItem['key']])) {
    // should exist
    $args['values'][$modelItem['key']] = [];
} elseif (!is_array($args['values'][$modelItem['key']])) {
    // should be an array
    $args['values'][$modelItem['key']] = [$args['values'][$modelItem['key']]];
}

$selectedItemsSorted = array_flip($args['values'][$modelItem['key']]);
$selectedItemsSorted = array_replace($selectedItemsSorted, $modelItem['values']);
?>
<table class="form-table <?php echo esc_attr($componentName); ?>"
    data-component="<?php echo esc_attr($componentName); ?>">
    <tr>
        <td>
            <label for="<?php echo esc_attr($componentName); ?>-items-available-<?php echo esc_attr($id); ?>"><?php
                esc_html_e('Items available', $textDomain);
                ?></label>
            <select name="<?php echo esc_attr($componentName); ?>-items-available"
                id="<?php echo esc_attr($componentName); ?>-items-available-<?php echo esc_attr($id); ?>"
                data-component="<?php echo esc_attr($componentName); ?>-items-available" size="10" multiple>
                <?php foreach ((array) $modelItem['values'] as $key => $value) { ?>
                    <?php
                    if (in_array($key, $args['values'][$modelItem['key']])) {
                        continue;
                    }
                    ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                <?php } ?>
            </select>
        </td>
        <td width="35">
            <button class="button" data-component="<?php echo esc_attr($componentName); ?>-add">&#9658;</button>
            <br><br>
            <button class="button" data-component="<?php echo esc_attr($componentName); ?>-remove">&#9668;</button>
        </td>
        <td>
            <label for="<?php echo esc_attr($componentName); ?>-items-selected-<?php echo esc_attr($id); ?>"><?php
                esc_html_e('Items selected', $textDomain);
                ?></label>
            <select name="<?php echo esc_attr($componentName); ?>-items-selected-<?php echo esc_attr($id); ?>"
                id="<?php echo esc_attr($componentName); ?>-items-selected-<?php echo esc_attr($id); ?>"
                data-component="<?php echo esc_attr($componentName); ?>-items-selected" size="10" multiple>
                <?php foreach ((array) $selectedItemsSorted as $key => $value) { ?>
                    <?php
                    if (!in_array($key, $args['values'][$modelItem['key']])) {
                        continue;
                    }
                    ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                <?php } ?>
            </select>
            <div data-component="<?php echo esc_attr($componentName); ?>-inputs">
                <?php foreach ((array) $selectedItemsSorted as $key => $value) { ?>
                    <input type="hidden"
                        <?php
                        if (!in_array($key, $args['values'][$modelItem['key']])) {
                            echo 'disabled ';
                        }

                        echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '[]" ';
                        ?>
                        value="<?php echo esc_attr($key); ?>">
                <?php } ?>
            </div>
        </td>
        <td width="35">
            <button class="button" data-component="<?php echo esc_attr($componentName); ?>-move-up">&#9650;</button>
            <br><br>
            <button class="button" data-component="<?php echo esc_attr($componentName); ?>-move-down">&#9660;</button>
        </td>
    </tr>
</table>

<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];

$items = isset($args['values'][$modelItem['key']])
    ? $args['values'][$modelItem['key']]
    : $modelItem['default'];

$items = empty($items) ? [[]] : $items;
$namespace = 'wcpw';
$coreClass = isset($coreClass) ? $coreClass : '\WCProductsWizard\Admin';
$componentName = $namespace . '-group';
?>
<table class="<?php echo esc_attr($componentName); ?> widefat"
    data-component="<?php echo esc_attr($componentName); ?>-item">
    <?php if (isset($modelItem['showHeader']) && $modelItem['showHeader']) { ?>
        <thead>
            <tr>
                <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                    <td class="<?php echo esc_attr($modelItemValue['key'] . ' ' . $componentName); ?>-item"
                        data-component="<?php echo esc_attr($componentName); ?>-head"
                        data-key="<?php echo esc_attr($modelItemValue['key']); ?>"
                        <?php
                        echo isset($modelItemValue['width'])
                            ? ' width="' . esc_attr($modelItemValue['width']) . '"'
                            : ''
                        ?>><?php echo wp_kses_post($modelItemValue['label']); ?></td>
                <?php } ?>
            </tr>
        </thead>
    <?php } ?>
    <tbody>
        <tr>
            <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                <td class="<?php echo esc_attr($modelItemValue['key'] . ' ' . $componentName); ?>-item"
                    data-component="<?php echo esc_attr($componentName); ?>-item"
                    data-key="<?php echo esc_attr($modelItemValue['key']); ?>">
                    <?php
                    if (method_exists($coreClass, 'settingFieldView')) {
                        $fieldArgs = [
                            'values' => $items,
                            'idPattern' => "$args[name]-$modelItemValue[key]",
                            'namePattern' => "$args[name][$modelItemValue[key]]"
                        ];

                        $coreClass::settingFieldView($modelItemValue, $fieldArgs);
                    }
                    ?>
                </td>
            <?php } ?>
        </tr>
    </tbody>
</table>
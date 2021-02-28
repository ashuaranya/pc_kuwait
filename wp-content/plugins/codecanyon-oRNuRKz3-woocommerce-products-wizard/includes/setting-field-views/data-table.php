<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];

$items = isset($args['values'][$modelItem['key']])
    ? $args['values'][$modelItem['key']]
    : $modelItem['default'];

$items = empty($items) ? [[]] : $items;
$isSingleValue = count($modelItem['values']) <= 1;
$namespace = 'wcpw';
$textDomain = 'woocommerce-products-wizard';
$coreClass = isset($coreClass) ? $coreClass : '\WCProductsWizard\Admin';
$componentName = $namespace . '-data-table';
$modelItem['showHeader'] = isset($modelItem['inModal']) && $modelItem['inModal']
    ? false
    : (isset($modelItem['showHeader']) ? $modelItem['showHeader'] : true);
?>
<table class="<?php echo esc_attr($componentName); ?> wp-list-table widefat striped"
    data-component="<?php echo esc_attr($componentName); ?>">
    <?php if (!isset($modelItem['showHeader']) || $modelItem['showHeader']) { ?>
        <thead>
            <tr>
                <td></td>
                <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                    <td data-component="<?php echo esc_attr($componentName); ?>-header-item"
                        data-key="<?php echo esc_attr($modelItemValue['key']); ?>"><?php
                        echo wp_kses_post($modelItemValue['label']);
                        ?></td>
                <?php } ?>
                <td></td>
            </tr>
        </thead>
    <?php } ?>
    <tbody>
        <?php foreach ($items as $itemKey => $itemValue) { ?>
            <tr data-component="<?php echo esc_attr($componentName); ?>-item">
                <td class="<?php echo esc_attr($componentName); ?>-item-controls">
                    <span class="button" role="button"
                        data-component="<?php echo esc_attr($componentName); ?>-item-add">+</span>
                </td>
                <?php if (isset($modelItem['inModal']) && $modelItem['inModal']) { ?>
                    <td>
                        <a href="<?php echo esc_attr("#$args[id]-modal-$itemKey"); ?>"
                            class="button <?php echo esc_attr($componentName); ?>-item-open-modal"><?php
                            esc_html_e('Settings', $textDomain);
                            ?></a>
                        <div id="<?php echo esc_attr("$args[id]-modal-$itemKey"); ?>"
                            class="<?php echo esc_attr($namespace); ?>-modal">
                            <div class="<?php echo esc_attr($namespace); ?>-modal-dialog">
                                <a href="#close"
                                    title="<?php esc_attr_e('Close', $textDomain); ?>"
                                    class="<?php echo esc_attr($namespace); ?>-modal-close">&times;</a>
                                <table class="form-table">
                                    <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                                        <tr data-component="<?php echo esc_attr($componentName); ?>-body-item"
                                            data-key="<?php echo esc_attr($modelItemValue['key']); ?>">
                                            <th><?php echo wp_kses_post($modelItemValue['label']); ?></th>
                                            <td>
                                                <?php
                                                $values = [];
                                                $namePattern = "$args[name][$itemKey]"
                                                    . (!$isSingleValue ? "[$modelItemValue[key]]" : '');

                                                if (isset($args['values'][$modelItem['key']][$itemKey])) {
                                                    if (!$isSingleValue) {
                                                        $values = $args['values'][$modelItem['key']][$itemKey];
                                                    } else {
                                                        $values = [
                                                            $modelItem['key'] =>
                                                                $args['values'][$modelItem['key']][$itemKey]
                                                        ];
                                                    }
                                                }

                                                if (method_exists($coreClass, 'settingFieldView')) {
                                                    $coreClass::settingFieldView(
                                                        $modelItemValue,
                                                        [
                                                            'values' => $values,
                                                            'namePattern' => $namePattern,
                                                            'generateId' => false
                                                        ]
                                                    );
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                                <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-footer">
                                    <a href="#close" class="button button-primary"><?php
                                        esc_html_e('Save', $textDomain);
                                        ?></a>
                                </div>
                            </div>
                        </div>
                    </td>
                <?php } else { ?>
                    <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                        <td data-component="<?php echo esc_attr($componentName); ?>-body-item"
                            data-key="<?php echo esc_attr($modelItemValue['key']); ?>">
                            <?php
                            $values = [];
                            $namePattern = "$args[name][$itemKey]"
                                . (!$isSingleValue ? "[$modelItemValue[key]]" : '');

                            if (isset($args['values'][$modelItem['key']][$itemKey])) {
                                if (!$isSingleValue) {
                                    $values = $args['values'][$modelItem['key']][$itemKey];
                                } else {
                                    $values = [$modelItem['key'] => $args['values'][$modelItem['key']][$itemKey]];
                                }
                            }

                            if (method_exists($coreClass, 'settingFieldView')) {
                                $coreClass::settingFieldView(
                                    $modelItemValue,
                                    [
                                        'values' => $values,
                                        'namePattern' => $namePattern,
                                        'generateId' => false
                                    ]
                                );
                            }
                            ?>
                        </td>
                    <?php } ?>
                <?php } ?>
                <td class="<?php echo esc_attr($componentName); ?>-item-controls">
                    <span class="button" role="button"
                        data-component="<?php echo esc_attr($componentName); ?>-item-remove">-</span>
                </td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot class="<?php echo esc_attr($componentName); ?>-footer">
        <tr data-component="<?php echo esc_attr($componentName); ?>-item-template">
            <td class="<?php echo esc_attr($componentName); ?>-item-controls">
                <span class="button" data-component="<?php echo esc_attr($componentName); ?>-item-add">+</span>
            </td>
            <?php if (isset($modelItem['inModal']) && $modelItem['inModal']) { ?>
                <td>
                    <a href="<?php echo esc_attr("#$args[id]-modal"); ?>"
                        data-component="<?php echo esc_attr($componentName); ?>-modal-open"
                        class="button <?php echo esc_attr($componentName); ?>-item-open-modal"><?php
                        esc_html_e('Settings', $textDomain);
                        ?></a>
                    <div id="<?php echo esc_attr("$args[id]-modal"); ?>"
                        class="<?php echo esc_attr($namespace); ?>-modal"
                        data-component="<?php echo esc_attr($componentName); ?>-modal">
                        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog">
                            <a href="#close"
                                title="<?php esc_attr_e('Close', $textDomain); ?>"
                                class="<?php echo esc_attr($namespace); ?>-modal-close">&times;</a>
                            <table class="form-table">
                                <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                                    <tr data-component="<?php echo esc_attr($componentName); ?>-body-item"
                                        data-key="<?php echo esc_attr($modelItemValue['key']); ?>">
                                        <th><?php echo wp_kses_post($modelItemValue['label']); ?></th>
                                        <td>
                                            <?php
                                            $namePattern = $args['name'] . '[0]'
                                                . (!$isSingleValue ? "[$modelItemValue[key]]" : '');

                                            if (method_exists($coreClass, 'settingFieldView')) {
                                                $coreClass::settingFieldView(
                                                    $modelItemValue,
                                                    [
                                                        'values' => [],
                                                        'namePattern' => $namePattern,
                                                        'asTemplate' => true,
                                                        'generateId' => false
                                                    ]
                                                );
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                            <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-footer">
                                <a href="#close" class="button button-primary"><?php
                                    esc_html_e('Save', $textDomain);
                                    ?></a>
                            </div>
                        </div>
                    </div>
                </td>
            <?php } else { ?>
                <?php foreach ($modelItem['values'] as $modelItemValue) { ?>
                    <td data-component="<?php echo esc_attr($componentName); ?>-body-item"
                        data-key="<?php echo esc_attr($modelItemValue['key']); ?>">
                        <?php
                        $namePattern = $args['name'] . '[0]' . (!$isSingleValue ? "[$modelItemValue[key]]" : '');

                        if (method_exists($coreClass, 'settingFieldView')) {
                            $coreClass::settingFieldView(
                                $modelItemValue,
                                [
                                    'values' => [],
                                    'namePattern' => $namePattern,
                                    'asTemplate' => true,
                                    'generateId' => false
                                ]
                            );
                        }
                        ?>
                    </td>
                <?php } ?>
            <?php } ?>
            <td class="<?php echo esc_attr($componentName); ?>-item-controls">
                <span class="button" data-component="<?php echo esc_attr($componentName); ?>-item-remove">-</span>
            </td>
        </tr>
    </tfoot>
</table>

<?php
$args = isset($args) ? $args : [];
$modelItem = isset($modelItem) ? $modelItem : [];
$namespace = 'wcpw';
$textDomain = 'woocommerce-products-wizard';
$component = isset($modelItem['data-component']) ? $modelItem['data-component'] : $namespace . '-thumbnail';
?>
<div class="<?php echo esc_attr($component); ?>" data-component="<?php echo esc_attr($component); ?>">
    <div class="<?php echo esc_attr($component); ?>-image"
        data-component="<?php echo esc_attr($component); ?>-image">
        <?php if ($args['value']) { ?>
            <img src="<?php echo esc_attr(wp_get_attachment_image_src($args['value'], 'thumbnail')[0]); ?>"
                alt="<?php echo esc_attr(get_the_title($args['value'])); ?>">
        <?php } ?>
    </div>
    <input data-component="<?php echo esc_attr($component); ?>-id" type="hidden"
        <?php echo ($args['asTemplate'] ? 'data-make-' : '') . 'name="' . esc_attr($args['name']) . '" '; ?>
        value="<?php echo esc_attr($args['value']); ?>">
    <p class="hide-if-no-js">
        <?php esc_html_e('Image', $textDomain); ?>:
        <a href="#" data-component="<?php echo esc_attr($component); ?>-set" role="button"><?php
            esc_html_e('Set', $textDomain);
            ?></a>
        /
        <a href="#" data-component="<?php echo esc_attr($component); ?>-remove" role="button"><?php
            esc_html_e('Remove', $textDomain);
            ?></a>
    </p>
</div>

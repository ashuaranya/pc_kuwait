<?php
$namespace = 'wcpw';
$textDomain = 'woocommerce-products-wizard';
?>
<div id="<?php echo esc_attr($namespace); ?>-shared-editor-modal"
    class="<?php echo esc_attr($namespace); ?>-modal"
    data-component="<?php echo esc_attr($namespace); ?>-modal">
    <div class="<?php echo esc_attr($namespace); ?>-modal-dialog">
        <a href="#close"
            id="<?php echo esc_attr($namespace); ?>-shared-editor-close"
            title="<?php esc_attr_e('Close', $textDomain); ?>"
            class="<?php echo esc_attr($namespace); ?>-modal-close"
            data-component="<?php echo esc_attr($namespace); ?>-modal-close">&times;</a>
        <?php wp_editor('', 'shared-editor'); ?>
        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-footer">
            <a href="#close"
                id="<?php echo esc_attr($namespace); ?>-shared-editor-save"
                class="button button-primary"
                data-component="<?php echo esc_attr($namespace); ?>-modal-close"><?php
                esc_html_e('Save', $textDomain);
                ?></a>
        </div>
    </div>
</div>

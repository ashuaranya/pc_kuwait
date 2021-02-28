<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'navItems' => [],
    'formId' => null
]);
?>
<nav class="woocommerce-products-wizard-nav is-pills">
    <ul class="woocommerce-products-wizard-nav-list is-pills nav nav-pills" role="tablist">
        <?php foreach ($arguments['navItems'] as $navItem) { ?>
            <li role="presentation"
                class="woocommerce-products-wizard-nav-list-item nav-item <?php echo esc_attr($navItem['class']); ?>">
                <button type="submit" role="tab"
                    form="<?php echo esc_attr($arguments['formId']); ?>"
                    name="<?php echo esc_attr($navItem['action']); ?>"
                    value="<?php echo esc_attr($navItem['value']); ?>"
                    class="woocommerce-products-wizard-nav-list-item-button nav-link <?php
                    echo esc_attr($navItem['class']);
                    ?>"
                    data-component="wcpw-nav-item"
                    data-nav-action="<?php echo esc_attr($navItem['action']); ?>"
                    data-nav-id="<?php echo esc_attr($navItem['value']); ?>"<?php
                    echo $navItem['state'] == 'disabled' ? ' disabled="disabled"' : '';
                    ?>><?php
                    echo $navItem['thumbnail']
                        ? wp_get_attachment_image(
                            $navItem['thumbnail'],
                            'thumbnail',
                            false,
                            ['class' => 'woocommerce-products-wizard-nav-list-item-button-thumbnail']
                        ) . ' '
                        : '';
                    ?><span class="woocommerce-products-wizard-nav-list-item-button-inner"><?php
                        echo wp_kses_post($navItem['name']);
                        ?></span></button>
            </li>
        <?php } ?>
    </ul>
</nav>

<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'mode' => 'tabs',
    'navItems' => [],
    'navTemplate' => Settings::getPost($id, 'nav_template'),
    'showSidebar' => false,
    'sidebarPosition' => 'right'
]);

if (!$arguments['showSidebar'] || $arguments['sidebarPosition'] == 'top') {
    $mainClass = 'col-xs-12 col-12 col';
} else {
    $mainClass = 'col-lg-9 col-md-8 col-xs-12 col-12 col'
        . ($arguments['sidebarPosition'] == 'right' ? ' col-lg-pull-3 col-md-pull-4' : '');
}
?>
<div class="woocommerce-products-wizard-body row woocommerce-products-wizard-main-row is-tabs <?php
    echo esc_attr("is-{$arguments['mode']}-mode");
    ?>"
    role="tablist"
    data-component="wcpw-main-row">
    <?php
    if ($arguments['showSidebar']) {
        Template::html('sidebar', $arguments);
    }
    ?>
    <div class="woocommerce-products-wizard-main <?php echo $mainClass; ?>">
        <?php
        foreach ($arguments['navItems'] as $navItem) {
            if ($arguments['navTemplate'] != 'none') {
                Template::html('nav/button', array_replace($arguments, $navItem));
            }

            if (isset($navItem['state']) && $navItem['state'] == 'active') {
                if ($arguments['stepId'] == 'result') {
                    Template::html('result', $arguments);
                } else {
                    Template::html('form/index', $arguments);
                }
            }
        }
        ?>
    </div>
</div>

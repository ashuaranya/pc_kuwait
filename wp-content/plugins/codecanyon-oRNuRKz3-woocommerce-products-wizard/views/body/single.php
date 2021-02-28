<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Form;
use WCProductsWizard\Template;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => null,
    'mode' => 'single-step',
    'steps' => [],
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
<div class="woocommerce-products-wizard-body row woocommerce-products-wizard-main-row is-single <?php
    echo esc_attr("is-{$arguments['mode']}-mode");
    ?>"
    data-component="wcpw-main-row">
    <?php
    if ($arguments['showSidebar']) {
        Template::html('sidebar', $arguments);
    }
    ?>
    <div class="woocommerce-products-wizard-main <?php echo $mainClass; ?>">
        <?php
        foreach ($arguments['steps'] as $step) {
            Form::setActiveStep($arguments['id'], $step['id']);

            if ($step['id'] == 'result') {
                Template::html('result', $arguments);
            } else {
                Template::html('form/index', $arguments);
            }

            if ($arguments['mode'] == 'sequence' && $arguments['stepId'] == $step['id']) {
                break;
            }
        }
        ?>
    </div>
</div>

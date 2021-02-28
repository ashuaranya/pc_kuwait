<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'mode' => 'step-by-step',
    'filterPosition' => Settings::getStep($id, $stepId, 'filter_position'),
    'sidebarPosition' => 'right'
]);

$sidebarClass = 'col-lg-3 col-md-4 col-xs-12 col-12 col'
    . ($arguments['sidebarPosition'] == 'right' ? ' order-md-1 col-lg-push-9 col-md-push-8' : '');

if ($arguments['sidebarPosition'] == 'top') {
    $sidebarClass = 'col-xs-12 col-12 col';
}
?>
<aside class="woocommerce-products-wizard-sidebar <?php echo $sidebarClass; ?>"><?php
    if ($arguments['filterPosition'] == 'before-widget' && !in_array($arguments['mode'], ['single-step', 'sequence'])) {
        Template::html('form/filter/index', $arguments);
    }

    Template::html('widget', $arguments);
    ?></aside>
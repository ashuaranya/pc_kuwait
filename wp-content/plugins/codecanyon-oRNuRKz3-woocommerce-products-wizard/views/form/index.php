<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Form;
use WCProductsWizard\Product;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$step = Form::getActiveStep($id);
$stepId = $step['id'];
$filter = [];
$pages = [];

if (isset($_REQUEST['wcpwFilter'])) {
    if (is_string($_REQUEST['wcpwFilter'])) {
        parse_str($_REQUEST['wcpwFilter'], $filter);
    } else {
        $filter = $_REQUEST['wcpwFilter'];
    }
}

if (isset($_REQUEST['wcpwPage'])) {
    if (is_string($_REQUEST['wcpwPage'])) {
        parse_str($_REQUEST['wcpwPage'], $pages);
    } else {
        $pages = $_REQUEST['wcpwPage'];
    }
}

if (isset($_REQUEST['wcpwProductsPerPage'])) {
    if (is_string($_REQUEST['wcpwProductsPerPage'])) {
        parse_str($_REQUEST['wcpwProductsPerPage'], $productsPerPage);
    } else {
        $productsPerPage = $_REQUEST['wcpwProductsPerPage'];
    }
}

if (isset($_REQUEST['wcpwOrderBy'])) {
    if (is_string($_REQUEST['wcpwOrderBy'])) {
        parse_str($_REQUEST['wcpwOrderBy'], $orderBy);
    } else {
        $orderBy = $_REQUEST['wcpwOrderBy'];
    }
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'formId' => null,
    'title' => isset($step['name']) ? $step['name'] : '',
    'description' => isset($step['description']) ? $step['description'] : '',
    'descriptionPosition' => isset($step['descriptionPosition']) ? $step['descriptionPosition'] : 'default',
    'notices' => WCProductsWizard\Instance()->form->getNotices($stepId),
    'page' => isset($pages[$stepId]) ? (int) $pages[$stepId] : 1,
    'filter' => isset($filter[$stepId]) ? (array) $filter[$stepId] : [],
    'productsPerPage' => isset($productsPerPage[$stepId]) ? (int) $productsPerPage[$stepId] : null,
    'orderBy' => isset($orderBy[$stepId]) ? $orderBy[$stepId] : null,
    'filterPosition' => Settings::getStep($id, $stepId, 'filter_position'),
    'showStepsNames' => Settings::getPost($id, 'show_steps_names'),
    'stepPublicSettings' => Settings::getStepArray($id, $stepId, ['public' => true]),
    'mode' => 'step-by-step'
]);

$arguments['stepId'] = $stepId; // force defined step

$hasProductsInCart = !empty(Cart::getByStepId($arguments['id'], $stepId));
?>
<article class="woocommerce-products-wizard-form<?php
    echo $hasProductsInCart ? ' has-products-in-cart' : '';
    echo ' is-step-' . esc_attr($stepId);
    ?>"<?php
    echo !empty($arguments['stepPublicSettings'])
        ? ' data-settings="' . esc_attr(wp_json_encode($arguments['stepPublicSettings'])) . '"'
        : '';
    ?>
    data-component="wcpw-form-step">
    <?php
    if (in_array($arguments['mode'], ['single-step', 'sequence']) && $arguments['showStepsNames']) {
        Template::html('form/title', $arguments);
    }

    if (!empty($arguments['notices'])) {
        foreach ($arguments['notices'] as $notice) {
            Template::html("messages/{$notice['view']}", array_replace($arguments, $notice));
        }
    }

    if ($arguments['description'] && in_array($arguments['descriptionPosition'], ['top', 'default'])) {
        Template::html('form/description', $arguments);
    }

    if (is_numeric($stepId)) {
        ?>
        <input type="hidden"
            form="<?php echo esc_attr($arguments['formId']); ?>"
            name="productsToAddChecked[<?php echo esc_attr($arguments['stepId']); ?>][]"
            value="">
        <div class="woocommerce-products-wizard-form-controls"><?php
            if ($arguments['filterPosition'] == 'before-products') {
                Template::html('form/filter/index', $arguments);
            }

            Template::html('form/order-by', $arguments);
            Template::html('form/products-per-page', $arguments);
            ?></div>
        <?php
        Product::request($arguments);
    }

    if ($arguments['description'] && $arguments['descriptionPosition'] == 'bottom') {
        Template::html('form/description', $arguments);
    }
    ?>
</article>

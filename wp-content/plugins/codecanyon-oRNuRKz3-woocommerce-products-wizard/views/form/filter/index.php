<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Form;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$filter = [];

if (isset($_REQUEST['wcpwFilter'])) {
    if (is_string($_REQUEST['wcpwFilter'])) {
        parse_str($_REQUEST['wcpwFilter'], $filter);
    } else {
        $filter = $_REQUEST['wcpwFilter'];
    }
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'filter' => isset($filter[$stepId]) ? (array) $filter[$stepId] : [],
    'filterLabel'=> Settings::getPost($id, 'filter_label'),
    'filterResetButtonText'=> Settings::getPost($id, 'filter_reset_button_text'),
    'filterSubmitButtonText'=> Settings::getPost($id, 'filter_submit_button_text'),
    'filterThumbnailSize' => Settings::getStep($id, $stepId, 'filter_thumbnail_size'),
    'filterIsExpanded' => Settings::getStep($id, $stepId, 'filter_is_expanded')
]);

$filters = Form::getFilterFields($arguments['id'], $arguments['stepId'], $arguments['filter']);

if (empty($filters)) {
    return;
}

if (strpos($arguments['filterThumbnailSize'], ',') !== false) {
    $arguments['filterThumbnailSize'] = explode(',', $arguments['filterThumbnailSize']);
}

$isExpanded = isset($_COOKIE["#woocommerce-products-wizard-form-filter-content-{$arguments['id']}-{$arguments['stepId']}-expanded"])
    ? $_COOKIE["#woocommerce-products-wizard-form-filter-content-{$arguments['id']}-{$arguments['stepId']}-expanded"]
    : ($arguments['filterIsExpanded'] || !empty($arguments['filter'][$arguments['stepId']]) ? 'true' : 'false');
?>
<form class="woocommerce-products-wizard-form-filter panel panel-default card"
    action="#" method="get"
    data-component="wcpw-filter"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>">
    <div class="woocommerce-products-wizard-form-filter-header panel-heading">
        <h3 class="woocommerce-products-wizard-form-filter-title panel-title card-header h5">
            <a href="#woocommerce-products-wizard-form-filter-content-<?php
                echo esc_attr("{$arguments['id']}-{$arguments['stepId']}");
                ?>"
                class="woocommerce-products-wizard-form-filter-toggle"
                role="button"
                data-component="wcpw-toggle"
                data-target="#woocommerce-products-wizard-form-filter-content-<?php
                echo esc_attr("{$arguments['id']}-{$arguments['stepId']}");
                ?>"
                aria-controls="woocommerce-products-wizard-form-filter-content-<?php
                echo esc_attr("{$arguments['id']}-{$arguments['stepId']}");
                ?>"
                aria-expanded="<?php echo esc_attr($isExpanded); ?>"><?php
                echo wp_kses_post($arguments['filterLabel']);
                ?></a>
        </h3>
    </div>
    <div class="woocommerce-products-wizard-form-filter-content"
        id="woocommerce-products-wizard-form-filter-content-<?php
        echo esc_attr("{$arguments['id']}-{$arguments['stepId']}");
        ?>"
        data-component="woocommerce-products-wizard-form-filter-content"
        aria-expanded="<?php echo esc_attr($isExpanded); ?>">
        <div class="woocommerce-products-wizard-form-filter-body panel-body card-body">
            <?php
            foreach ($filters as $filter) {
                Template::html("form/filter/fields/{$filter['view']}", array_merge($arguments, $filter));
            }
            ?>
        </div>
        <div class="woocommerce-products-wizard-form-filter-footer panel-footer card-footer">
            <button class="woocommerce-products-wizard-form-filter-reset btn btn-default btn-light"
                type="reset"
                data-component="wcpw-filter-reset"
                data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"><?php
                echo wp_kses_post($arguments['filterResetButtonText']);
                ?></button>
            <button class="woocommerce-products-wizard-form-filter-submit btn btn-danger"
                type="submit"
                data-component="wcpw-filter-submit"><?php
                echo wp_kses_post($arguments['filterSubmitButtonText']);
                ?></button>
        </div>
    </div>
    <?php
    // no-js version forms values binding
    if (isset($_GET['wcpwProductsPerPage']) && !empty($_GET['wcpwProductsPerPage'])) {
        echo '<input type="hidden" name="wcpwProductsPerPage" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwProductsPerPage']))
            . '">';
    }

    if (isset($_GET['wcpwOrderBy']) && !empty($_GET['wcpwOrderBy'])) {
        echo '<input type="hidden" name="wcpwOrderBy" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwOrderBy']))
            . '">';
    }
    ?>
</form>

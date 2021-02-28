<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$productsPerPage = [];

if (isset($_REQUEST['wcpwProductsPerPage'])) {
    if (is_string($_REQUEST['wcpwProductsPerPage'])) {
        parse_str($_REQUEST['wcpwProductsPerPage'], $productsPerPage);
    } else {
        $productsPerPage = $_REQUEST['wcpwProductsPerPage'];
    }
}

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'productsPerPage' => isset($productsPerPage[$stepId]) ? (int) $productsPerPage[$stepId] : null,
    'productsPerPageItems' => Settings::getStep($id, $stepId, 'products_per_page_items')
]);

if (empty(array_filter($arguments['productsPerPageItems']))) {
    return;
}
?>
<form action="#" method="get"
    class="woocommerce-products-wizard-form-products-per-page form-inline mb-3"
    data-component="wcpw-form-products-per-page"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>">
    <label for="woocommerce-products-wizard-form-products-per-page-<?php echo esc_attr($arguments['id']); ?>"
        class="woocommerce-products-wizard-form-products-per-page-label my-1 mr-2"><?php
        esc_html_e('Products per page', 'woocommerce-products-wizard');
        ?></label>
    <select name="wcpwProductsPerPage[<?php echo esc_attr($arguments['stepId']); ?>]"
        id="woocommerce-products-wizard-form-products-per-page-<?php echo esc_attr($arguments['id']); ?>"
        class="woocommerce-products-wizard-form-products-per-page-input form-control my-1 mr-2"><?php
        foreach (array_filter($arguments['productsPerPageItems']) as $item) {
            echo '<option value="' . esc_html($item) . '" '
                . selected($item, $arguments['productsPerPage'])
                . '>' . esc_html($item) . '</option>';
        }
        ?></select>
    <noscript>
        <button type="submit" class="woocommerce-products-wizard-form-products-per-page-submit my-1 btn btn-secondary"><?php
            esc_html_e('Apply', 'woocommerce-products-wizard');
            ?></button>
    </noscript>
    <?php
    // no-js version forms values binding
    if (isset($_GET['wcpwFilter']) && !empty($_GET['wcpwFilter'])) {
        echo '<input type="hidden" name="wcpwFilter" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwFilter']))
            . '">';
    }

    if (isset($_GET['wcpwOrderBy']) && !empty($_GET['wcpwOrderBy'])) {
        echo '<input type="hidden" name="wcpwOrderBy" value="'
            . esc_attr(http_build_query((array) $_GET['wcpwOrderBy']))
            . '">';
    }
    ?>
</form>

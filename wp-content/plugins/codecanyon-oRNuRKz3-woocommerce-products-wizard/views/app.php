<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'formId' => "wcpw-form-{$id}",
    'attachedMode' => false,
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'scrollingTopOnUpdate' => Settings::getPost($id, 'scrolling_top_on_update'),
    'scrollingUpGap' => Settings::getPost($id, 'scrolling_up_gap')
]);
?>
<section class="woocommerce-products-wizard <?php echo 'is-id-' . esc_attr($arguments['id']); ?>"
    data-component="wcpw" data-id="<?php echo esc_attr($arguments['id']); ?>"
    data-arguments="<?php echo esc_attr(wp_json_encode($arguments)); ?>"><?php
    Template::html('router', $arguments);
    ?></section>

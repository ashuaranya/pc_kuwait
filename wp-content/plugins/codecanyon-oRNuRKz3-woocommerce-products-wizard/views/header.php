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
    'stickyHeader' => Settings::getPost($id, 'sticky_header'),
    'stickyHeaderOffsetTop' => Settings::getPost($id, 'sticky_header_offset_top')
]);
?>
<header class="woocommerce-products-wizard-header"
    data-component="wcpw-header<?php echo $arguments['stickyHeader'] ? '  wcpw-sticky' : ''; ?>"
    data-sticky-top-offset="<?php echo esc_attr($arguments['stickyHeaderOffsetTop']); ?>"
    data-sticky-parent="[data-component=wcpw]"><?php
    Template::html('controls/index', $arguments);
    ?></header>

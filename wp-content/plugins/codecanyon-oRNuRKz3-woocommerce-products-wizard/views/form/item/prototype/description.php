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

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'descriptionSource' => Settings::getStep($id, $stepId, 'item_description_source')
]);

switch ($arguments['descriptionSource']) {
    default:
    case 'content':
        $description = get_the_content();
        break;

    case 'excerpt':
        $description = get_the_excerpt();
        break;

    case 'none':
        $description = '';
}

$description = do_shortcode(wpautop($description));
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-description"
    data-component="wcpw-product-description"
    data-default="<?php echo esc_attr($description); ?>"><?php
    echo $description;
    ?></div>

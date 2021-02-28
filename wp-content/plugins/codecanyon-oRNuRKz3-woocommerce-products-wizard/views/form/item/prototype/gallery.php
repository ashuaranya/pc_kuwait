<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'mergeThumbnailWithGallery' => false,
    'product' => null,
    'galleryGrid' => Settings::getStep($id, $stepId, 'gallery_column')
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product || $arguments['mergeThumbnailWithGallery']) {
    return;
}

$colClasses = [];
$attachmentIds = [];

if (method_exists($product, 'get_gallery_image_ids')) {
    $attachmentIds = $product->get_gallery_image_ids();
} elseif (method_exists($product, 'get_gallery_attachment_ids')) {
    $attachmentIds = $product->get_gallery_attachment_ids();
}

if (!$attachmentIds || empty($attachmentIds)) {
    return;
}

if (!isset($arguments['galleryGrid']['xxs'])) {
    $arguments['galleryGrid']['xxs'] = 12;
}

$colClasses[] = "col-{$arguments['galleryGrid']['xxs']}";

unset($arguments['galleryGrid']['xxs']);

foreach ($arguments['galleryGrid'] as $col => $value) {
    $colClasses[] = "col-{$col}-{$value}";
}

$colClass = implode(' ', $colClasses);
?>
<section class="<?php echo esc_attr($arguments['class']); ?>-gallery row" data-component="wcpw-product-gallery">
    <?php
    foreach ($attachmentIds as $attachmentId) {
        $imageLink = wp_get_attachment_url($attachmentId);

        if (!$imageLink) {
            continue;
        }

        $imageTitle = esc_attr(get_the_title($attachmentId));
        $imageCaption = esc_attr(get_post_field('post_excerpt', $attachmentId));
        $imageClass = esc_attr($arguments['class']) . '-gallery-item thumbnail zoom';
        $image = wp_get_attachment_image(
            $attachmentId,
            apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail'),
            0,
            $attr = [
                'title' => $imageTitle,
                'alt' => $imageTitle,
                'class' => esc_attr($arguments['class']) . '-gallery-item-image img-thumbnail'
            ]
        );

        echo "<div class=\"{$colClass}\">";

        echo apply_filters(
            'woocommerce_single_product_image_thumbnail_html',
            sprintf(
                '<a href="%s" class="%s" title="%s" rel="lightbox[%s]" '
                . 'data-rel="prettyPhoto[product-gallery-%s]">%s</a>',
                $imageLink,
                $imageClass,
                $imageCaption,
                $product->get_id(),
                $product->get_id(),
                $image
            ),
            $attachmentId,
            $product->get_id(),
            $imageClass
        );

        echo '</div>';
    }
    ?>
</section>

<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Utils;
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs(
    [
        'class' => 'woocommerce-products-wizard-form-item',
        'enableThumbnailLink' => true,
        'thumbnailSize' => 'shop_catalog',
        'thumbnailLink' => wp_get_attachment_image_src(get_post_thumbnail_id(), 'large')[0],
        'thumbnailAttributes' => ['data-component' => 'wcpw-product-thumbnail-image'],
        'mergeThumbnailWithGallery' => false,
        'showTags' => Settings::getStep($id, $stepId, 'show_tags'),
        'product' => null
    ],
    ['recursive' => true]
);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$dimensions = wc_get_image_size($arguments['thumbnailSize']);
$placeholder = '<img src="' . wc_placeholder_img_src()
    . '" alt="' . esc_html__('Placeholder', 'woocommerce')
    . '" width="' . esc_attr($dimensions['width'])
    . '" height="' . esc_attr($dimensions['height']) . '" '
    . Utils::attributesArrayToString($arguments['thumbnailAttributes'])
    . '/>';
?>
<figure class="<?php echo esc_attr($arguments['class']); ?>-thumbnail thumbnail img-thumbnail"
    data-component="wcpw-product-thumbnail"><?php
    if ($arguments['showTags']) {
        Template::html('form/item/prototype/tags', $arguments);
    }

    if ($arguments['mergeThumbnailWithGallery']) {
        $attachmentIds = [];

        if (has_post_thumbnail($product->get_id())) {
            $attachmentIds[] = get_post_thumbnail_id($product->get_id());
        }

        if (method_exists($product, 'get_gallery_image_ids')) {
            $attachmentIds = array_merge($attachmentIds, (array) $product->get_gallery_image_ids());
        } elseif (method_exists($product, 'get_gallery_attachment_ids')) {
            $attachmentIds = array_merge($attachmentIds, (array) $product->get_gallery_attachment_ids());
        }

        $attachmentIds = array_unique(array_filter($attachmentIds));

        echo '<div class="' . esc_attr($arguments['class']) . '-thumbnail-gallery thumbnail has-items-count-'
            . count($attachmentIds) . '">';

        if (empty($attachmentIds)) {
            echo $placeholder;
        }

        foreach ($attachmentIds as $attachmentId) {
            $imageLink = wp_get_attachment_url($attachmentId);
            $imageTitle = get_the_title($attachmentId);
            $itemAttributes = [
                'class' => esc_attr($arguments['class']) . '-thumbnail-gallery-item zoom',
                'title' => esc_attr($imageTitle),
                'rel' => 'lightbox[' . $product->get_id() . ']',
                'data-rel' => 'prettyPhoto[product-gallery-' . $product->get_id() . ']'
            ];

            if ($arguments['enableThumbnailLink'] && $imageLink) {
                $itemAttributes['href'] = $imageLink;
            }

            $imageAttributes = [
                'title' => esc_attr($imageTitle),
                'alt' => trim(strip_tags(get_post_meta($attachmentId, '_wp_attachment_image_alt', true))),
                'class' => esc_attr($arguments['class']) . '-thumbnail-gallery-item-image'
            ];

            if (reset($attachmentIds) == $attachmentId) {
                $itemAttributes['data-component'] = 'wcpw-product-thumbnail-link';
                $itemAttributes['class'] .= ' is-static';
                $imageAttributes = array_replace($imageAttributes, $arguments['thumbnailAttributes']);
                $imageAttributes['class'] .= ' is-static';
            }

            $image = wp_get_attachment_image(
                $attachmentId,
                $arguments['thumbnailSize'],
                0,
                $imageAttributes
            );

            $tag = $arguments['enableThumbnailLink'] ? 'a' : 'span';

            echo '<' . $tag . ' ' . Utils::attributesArrayToString($itemAttributes) .'>' . $imageTitle
                . '</' . $tag . '>'
                . '<span class="' . esc_attr($arguments['class']) . '-thumbnail-gallery-item-image-wrapper">'
                . $image . '</span>';
        }

        echo '</div>';
    } else {
        if ($arguments['thumbnailLink'] && $arguments['enableThumbnailLink']) {
            echo '<a href="' . esc_attr($arguments['thumbnailLink']) . '"
            class="' . esc_attr($arguments['class']) . '-thumbnail-link"
            title="' . esc_attr(get_the_title(get_post_thumbnail_id())) . '"
            data-component="wcpw-product-thumbnail-link"
            data-rel="prettyPhoto[product-gallery-' . esc_attr($product->get_id()) . ']"
            rel="lightbox[' . esc_attr($product->get_id()) . ']">';
        }

        echo $product->get_image($arguments['thumbnailSize'], $arguments['thumbnailAttributes']);

        if ($arguments['thumbnailLink'] && $arguments['enableThumbnailLink']) {
            echo '</a>';
        }
    }
    ?></figure>

<?php
namespace WCProductsWizard;

add_shortcode('woocommerce-products-wizard', __NAMESPACE__. '\\appShortCode');

function appShortCode($attributes = [])
{
    if (is_admin() && !defined('DOING_AJAX') || !Core::$wcIsActive) {
        return 'woocommerce-products-wizard';
    }

    do_action('wcProductsWizardShortCode', $attributes);

    return Instance()->template->html('app', $attributes, ['echo' => false]);
}

add_shortcode('wcpw-result-pdf-page-number', __NAMESPACE__. '\\printCartPageNumberShortCode');

function printCartPageNumberShortCode()
{
    return '<span class="wcpw-result-pdf-page-number"></span>';
}

add_shortcode('wcpw-result-pdf-page-total', __NAMESPACE__. '\\printCartPageTotalShortCode');

function printCartPageTotalShortCode()
{
    $pageCount = Instance()->activePageTotal;

    if (!$pageCount) {
        return '';
    }

    return '<span class="wcpw-result-pdf-page-total">' . $pageCount . '</span>';
}

add_shortcode('wcpw-step-input', __NAMESPACE__. '\\stepInputShortCode');

function stepInputShortCode($attributes = [])
{
    $id = Instance()->activeId;
    $stepId = Instance()->activeStepId;
    $defaults = [
        'class' => '',
        'form' => "wcpw-form-{$id}",
        'name' => '',
        'type' => 'text',
        'value' => '',
        'data-component' => 'wcpw-step-input'
    ];

    $attributes = array_replace($defaults, (array) $attributes);

    $unsupportedTypes = ['button', 'image', 'reset', 'submit'];
    $unsupportedTypes = apply_filters('wcProductsWizardStepInputShortCodeUnsupportedTypes', $unsupportedTypes);

    if (in_array($attributes['type'], $unsupportedTypes)) {
        return '';
    }

    $cartValue = Cart::getItemByKey($id, "{$stepId}-{$attributes['name']}");
    $attributes['name'] = "stepsData[{$stepId}][{$attributes['name']}]";

    if ($cartValue && !empty($cartValue['value'])) {
        if (in_array($attributes['type'], ['checkbox', 'radio'])) {
            if ($cartValue['value'] == $attributes['value']) {
                $attributes['checked'] = 'checked';
            }
        } elseif ($attributes['type'] != 'hidden') {
            $attributes['value'] = $cartValue['value'];
        }
    }

    if ($attributes['type'] == 'textarea') {
        $value = $attributes['value'];

        unset($attributes['value']);
        unset($attributes['type']);

        return '<textarea ' . Utils::attributesArrayToString($attributes) . '>' . esc_html($value) . '</textarea>';
    }

    return '<input ' . Utils::attributesArrayToString($attributes) . '>';
}

add_shortcode('wcpw-generated-thumbnail-url', __NAMESPACE__. '\\generatedThumbnailURL');

function generatedThumbnailURL($attributes = [])
{
    $defaults = ['id' => Instance()->activeId];
    $attributes = array_replace($defaults, (array) $attributes);

    if (!Settings::getPost($attributes['id'], 'generate_thumbnail')) {
        return '';
    }

    $generatedThumbnail = Core::generateThumbnail($attributes['id']);

    if (empty($generatedThumbnail)) {
        return '';
    }

    return $generatedThumbnail['url'];
}

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

$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'stepsIds' => [],
    'mode' => 'step-by-step',
    'showSidebar' => false,
    'minimumProductsSelected' => Settings::getStep($id, $stepId, 'min_products_selected'),
    'resultsTabEnabled' => Settings::getPost($id, 'enable_results_tab'),
    'enableAddToCartButton' => Settings::getPost($id, 'enable_add_to_cart_button'),
    'enableResultPdfButton' => Settings::getPost($id, 'enable_result_pdf_button'),
    'toResultsButtonEnabled' => Settings::getPost($id, 'enable_to_results_button'),
    'skipButtonEnabled' => Settings::getPost($id, 'enable_skip_button'),
    'backButtonEnabled' => Settings::getPost($id, 'enable_back_button'),
    'resetButtonEnabled' => Settings::getPost($id, 'enable_reset_button'),
    'nextButtonEnabled' => Settings::getPost($id, 'enable_next_button'),
    'canGoBack' => Form::canGoBack($id)
]);
?>
<div class="woocommerce-products-wizard-controls" data-component="wcpw-controls"><?php
    if ($arguments['showSidebar']) {
        Template::html('controls/widget-toggle', $arguments);
    }

    if ($arguments['mode'] == 'single-step') {
        // is single-step mode
        if ($arguments['resetButtonEnabled']) {
            Template::html('controls/reset', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/result-pdf', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/result-print', $arguments);
        }

        if ($arguments['enableAddToCartButton']) {
            Template::html('controls/add-to-cart', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/share', $arguments);
        }
    } elseif (is_numeric($arguments['stepId']) && end($arguments['stepsIds']) != $arguments['stepId']) {
        // is a numeric step but not last
        if ($arguments['resetButtonEnabled']) {
            Template::html('controls/reset', $arguments);
        }
        
        if ($arguments['backButtonEnabled'] && $arguments['canGoBack']) {
            Template::html('controls/back', $arguments);
        }

        if ((!isset($arguments['minimumProductsSelected']['value']) || !$arguments['minimumProductsSelected']['value'])
            && $arguments['skipButtonEnabled']
        ) {
            Template::html('controls/skip', $arguments);
        }

        if ($arguments['nextButtonEnabled']) {
            Template::html('controls/next', $arguments);
        }

        if ($arguments['toResultsButtonEnabled'] && $arguments['resultsTabEnabled']) {
            Template::html('controls/to-results', $arguments);
        }
    } elseif ($arguments['stepId'] == 'start') {
        // is the start step
        Template::html('controls/start', $arguments);
    } elseif (end($arguments['stepsIds']) == $arguments['stepId']) {
        // is the last step
        if ($arguments['resetButtonEnabled']) {
            Template::html('controls/reset', $arguments);
        }

        if ($arguments['backButtonEnabled']) {
            Template::html('controls/back', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/result-pdf', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/result-print', $arguments);
        }

        if ($arguments['enableAddToCartButton']) {
            Template::html('controls/add-to-cart', $arguments);
        }

        if ($arguments['enableResultPdfButton']) {
            Template::html('controls/share', $arguments);
        }
    }
    ?></div>

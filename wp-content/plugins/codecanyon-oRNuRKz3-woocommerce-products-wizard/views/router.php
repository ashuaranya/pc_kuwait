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
use WCProductsWizard\Template;
use WCProductsWizard\Settings;

do_action('wcProductsWizardBeforeOutput', $arguments);

$arguments = Template::getHTMLArgs([
    'cart' => Cart::get($id),
    'formId' => "wcpw-form-{$id}",
    'hidePrices' => Settings::getPost($id, 'hide_prices'),
    'enableRemoveButton' => Settings::getPost($id, 'enable_remove_button'),
    'removeButtonText' => Settings::getPost($id, 'remove_button_text'),
    'navItems' => Form::getNavItems($id),
    'showSidebar' => Settings::isSidebarShowed($id),
    'sidebarPosition' => Settings::getPost($id, 'sidebar_position'),
    'mode' => Settings::getPost($id, 'mode'),
    'steps' => Form::getSteps($id),
    'stepId' => Form::getActiveStepId($id),
    'stepsIds' => Form::getStepsIds($id)
]);

$arguments['stepId'] = Form::getActiveStepId($id); // need to get actual even if passed
$bodyTemplate = in_array($arguments['mode'], ['single-step', 'sequence']) ? 'single' : 'tabs';

Template::html('form', $arguments);

if (in_array($arguments['mode'], ['step-by-step', 'free-walk'])) {
    Template::html('nav/index', $arguments);
}

Template::html('header', $arguments);
Template::html("body/{$bodyTemplate}", $arguments);
Template::html('footer', $arguments);

do_action('wcProductsWizardAfterOutput', $arguments);

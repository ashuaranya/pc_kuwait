<?php
// phpcs:disable
// The older versions support
// Since 2.10.5
if (!function_exists('WCProdWiz')) {
    function WCProdWiz()
    {
        return WCProductsWizard\Core::instance();
    }
}

// Since 3.0.0
WCProductsWizard\Instance()->getSteps = function ($id) {
    return WCProductsWizard\Instance()->form->getSteps($id);
};

WCProductsWizard\Instance()->getStepsIds = function ($id) {
    return WCProductsWizard\Instance()->form->getStepsIds($id);
};

WCProductsWizard\Instance()->getActiveStep = function ($postId) {
    return WCProductsWizard\Instance()->form->getActiveStep($postId);
};

WCProductsWizard\Instance()->setActiveStep = function ($postId, $id) {
    WCProductsWizard\Instance()->form->setActiveStep($postId, $id);
};

WCProductsWizard\Instance()->getActiveStepId = function ($postId) {
    return WCProductsWizard\Instance()->form->getActiveStepId($postId);
};

WCProductsWizard\Instance()->getNextStepId = function ($postId) {
    return WCProductsWizard\Instance()->form->getNextStepId($postId);
};

WCProductsWizard\Instance()->getPreviousStepId = function ($postId) {
    return WCProductsWizard\Instance()->form->getPreviousStepId($postId);
};

WCProductsWizard\Instance()->getNavItems = function ($postId) {
    return WCProductsWizard\Instance()->form->getNavItems($postId);
};

WCProductsWizard\Instance()->canGoBack = function ($postId) {
    return WCProductsWizard\Instance()->form->canGoBack($postId);
};

WCProductsWizard\Instance()->getWizardSettings = function ($postId) {
    return WCProductsWizard\Instance()->settings->getPostArray($postId);
};

WCProductsWizard\Instance()->getTermsSettings = function ($postId) {
    return WCProductsWizard\Instance()->settings->getStepsSettings($postId);
};

WCProductsWizard\Instance()->getTermDescription = function ($postId, $termId) {
    return WCProductsWizard\Instance()->settings->getStep($postId, $termId, 'description');
};

WCProductsWizard\Instance()->getTermDescriptionPosition = function ($postId, $termId) {
    return WCProductsWizard\Instance()->settings->getStep($postId, $termId, 'description_position');
};

WCProductsWizard\Instance()->getTemplatePart = function ($slug, $args = [], $settings = []) {
    return WCProductsWizard\Instance()->template->html($slug, $args, $settings);
};

WCProductsWizard\Instance()->getVariationArguments = function ($args) {
    return WCProductsWizard\Instance()->product->getVariationArguments($args);
};

WCProductsWizard\Instance()->productsRequest = function ($id, $stepId, $page) {
    return WCProductsWizard\Instance()->product->request([
        'id' => $id,
        'stepId' => $stepId,
        'page' => $page
    ]);
};
// phpcs:enable
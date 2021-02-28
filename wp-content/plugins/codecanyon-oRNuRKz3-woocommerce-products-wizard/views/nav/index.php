<?php
if (!defined('ABSPATH')) {
    exit();
}

use WCProductsWizard\Template;
use WCProductsWizard\Settings;

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

$arguments = Template::getHTMLArgs(['navTemplate' => Settings::getPost($id, 'nav_template')]);

Template::html('nav/list/' . $arguments['navTemplate'], $arguments);

<?php
if (!defined('ABSPATH')) {
    exit();
}

$id = isset($id) ? $id : false;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Settings;
use WCProductsWizard\Template;
$arguments = Template::getHTMLArgs([
    'id' => $id,
    'resultPdfButtonText' => "Print",
    'resultPdfButtonClass' => "btn-success"
]);
?>
<div id="socialHolder" class="col-md-3">
    <div id="socialShare" class="btn-group share-group">
        <a data-toggle="dropdown" class="btn btn-info">
            <i class="fa fa-share-alt fa-inverse"></i>
        </a>
        <button href="#" data-toggle="dropdown" class="btn btn-info dropdown-toggle share">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a target="_blank" data-original-title="Twitter" rel="tooltip"  href="https://twitter.com/intent/tweet?url=<?= home_url('?wcpw-result-pdf='.esc_attr($arguments['id'])); ?>" class="btn btn-twitter" data-placement="left">
                    <i class="fa fa-twitter"></i>
                </a>
            </li>
            <li>
                <a target="_blank" data-original-title="Facebook" rel="tooltip"  href="https://www.facebook.com/sharer/sharer.php?u=<?= home_url('?wcpw-result-pdf='.esc_attr($arguments['id'])); ?>" class="btn btn-facebook" data-placement="left">
                    <i class="fa fa-facebook"></i>
                </a>
            </li>
            <li>
                <a target="_blank" data-original-title="LinkedIn" rel="tooltip"  href="https://www.linkedin.com/shareArticle?mini=true&url=<?= home_url('?wcpw-result-pdf='.esc_attr($arguments['id'])); ?>" class="btn btn-linkedin" data-placement="left">
                    <i class="fa fa-linkedin"></i>
                </a>
            </li>
            <li>
                <a target="_blank" data-original-title="Pinterest" rel="tooltip" href="https://pinterest.com/pin/create/button/?url=<?= home_url('?wcpw-result-pdf='.esc_attr($arguments['id'])); ?>&media=&description="  class="btn btn-pinterest" data-placement="left">
                    <i class="fa fa-pinterest"></i>
                </a>
            </li>
            <li>
                <a  target="_blank" data-original-title="Email" rel="tooltip" href="mailto:info@example.com?&subject=&cc=&bcc=&body=<?= home_url('?wcpw-result-pdf='.esc_attr($arguments['id'])); ?>" class="btn btn-mail" data-placement="left">
                    <i class="fa fa-envelope"></i>
                </a>
            </li>
        </ul>
    </div>
</div>
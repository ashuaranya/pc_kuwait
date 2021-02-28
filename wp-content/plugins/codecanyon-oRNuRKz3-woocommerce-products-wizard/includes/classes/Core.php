<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Core Class
 *
 * @class Core
 * @version 9.3.3
 */
class Core
{
    // <editor-fold desc="Properties">
    /**
     * Self instance variable
     * @var Core The single instance of the class
     */
    protected static $instance = null;

    /**
     * Is WooCommerce active
     * @var bool
     */
    public static $wcIsActive = false;

    /**
     * Current working wizard ID
     * @var int
     */
    public $activeId = null;

    /**
     * Current working wizard step ID
     * @var string
     */
    public $activeStepId = null;

    /**
     * Current working wizard PDF total pages
     * @var int
     */
    public $activePageTotal = null;

    /**
     * Storage instance variable
     * @var Storage
     */
    public $storage = null;

    /**
     * Cart instance variable
     * @var Cart
     */
    public $cart = null;

    /**
     * Admin part instance variable
     * @var Admin
     */
    protected $admin = null;

    /**
     * Template class instance variable
     * @var Template
     */
    public $template = null;

    /**
     * Product class instance variable
     * @var Product
     */
    public $product = null;

    /**
     * Order class instance variable
     * @var Order
     */
    public $order = null;

    /**
     * Settings class instance variable
     * @var Settings
     */
    public $settings = null;

    /**
     * Form class instance variable
     * @var Form
     */
    public $form = null;

    /**
     * Integration class instance variable
     * @var Integration
     */
    public $integration = null;
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        // include base slave classes
        $requiredClasses = [
            'L10N',
            'Utils',
            'Settings',
            'Storage',
            'Cart',
            'Template',
            'Form',
            'Product',
            'Order',
            'Integration'
        ];

        foreach ($requiredClasses as $requiredClass) {
            if (!class_exists('\\WCProductsWizard\\' . $requiredClass)
                && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $requiredClass . '.php')
            ) {
                include_once(__DIR__ . DIRECTORY_SEPARATOR . $requiredClass . '.php');
            }
        }

        // init classes
        self::$instance = $this;

        $this->settings = new Settings();
        $this->storage = new Storage();
        $this->cart = new Cart();
        $this->template = new Template();
        $this->integration = new Integration();
        $this->product = new Product();
        $this->order = new Order();
        $this->form = new Form();

        // actions
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_action('plugins_loaded', [$this, 'pluginsLoadedHook']);
        add_action('woocommerce_init', [$this, 'wcInitHook'], 1);
        add_action('woocommerce_after_register_post_type', [$this, 'wcProductRegistered'], 1);
        add_action('wcProductsWizardBeforeOutput', [$this, 'beforeOutputAction']);
        add_action('wcProductsWizardAfterOutput', [$this, 'afterOutputAction']);
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        do_action('wcProductsWizardInit', $this);
    }

    /**
     * Old methods callback
     *
     * @param string $method
     * @param array $args
     *
     * @return string|integer|bool|null
     */
    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;

            return call_user_func_array($func, $args);
        }
    }

    /**
     * Get single class instance
     *
     * @static
     * @return Core
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** Handles then plugins are loaded */
    public function pluginsLoadedHook()
    {
        self::$wcIsActive = class_exists('\WooCommerce');

        if (!self::$wcIsActive) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>'
                    . L10N::r('WooCommerce is required for WC Products Wizard')
                    . '</p></div>';
            });
        }
    }

    /** Handles then woocommerce plugin is loaded */
    public function wcInitHook()
    {
        // start WC session variable if needed
        if ($this->settings->getGlobal('store_session_in_db') && function_exists('WC')) {
            if (method_exists(WC(), 'initialize_session')) {
                WC()->initialize_session();
            }

            if (method_exists(WC()->session, 'set_customer_session_cookie')) {
                WC()->session->set_customer_session_cookie(true);
            }
        }

        // if is admin page
        if (is_admin()) {
            if (!class_exists('\\WCProductsWizard\\Admin')) {
                include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Admin.php');
            }

            $this->admin = new Admin();
        }
    }

    /** Handles then woocommerce product is registered */
    public function wcProductRegistered()
    {
        if (isset($_GET['wcpw-result-pdf']) && $_GET['wcpw-result-pdf']) {
            $this->outputResultPDF((int) $_GET['wcpw-result-pdf']);
        }
    }

    /**
     * Handles on output call
     *
     * @param array $args
     */
    public function beforeOutputAction($args)
    {
        if (isset($args['id']) && $args['id']) {
            $this->activeId = (int) $args['id'];
        }
    }

    /** Handles after output call */
    public function afterOutputAction()
    {
        $this->activeId = null;
        $this->activeStepId = null;
    }

    /** Styles and scripts enqueue */
    public function enqueue()
    {
        $path = WC_PRODUCTS_WIZARD_DEBUG ? 'src' : 'assets';
        $suffix = WC_PRODUCTS_WIZARD_DEBUG ? '' : '.min';
        $stylesFolder = WC_PRODUCTS_WIZARD_DEBUG ? 'scss' : 'css';
        $scriptsIncludingType = $this->settings->getGlobal('scripts_including_type');
        $includedScripts = $this->settings->getGlobal('included_scripts');
        $stylesIncludingType = $this->settings->getGlobal('styles_including_type');

        wp_enqueue_script('jquery');

        if ($scriptsIncludingType == 'single' && !WC_PRODUCTS_WIZARD_DEBUG) {
            wp_enqueue_script(
                'woocommerce-products-wizard-scripts',
                WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/front/js/scripts.min.js',
                ['jquery'],
                WC_PRODUCTS_WIZARD_VERSION,
                true
            );
        } elseif ($scriptsIncludingType == 'multiple' || WC_PRODUCTS_WIZARD_DEBUG) {
            if (in_array('bootstrap-util', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-bootstrap-util',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/util$suffix.js",
                    ['jquery'],
                    '4.4.1',
                    true
                );
            }

            if (in_array('bootstrap-modal', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-bootstrap-modal',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/modal$suffix.js",
                    ['jquery', 'woocommerce-products-wizard-bootstrap-util'],
                    '4.4.1',
                    true
                );
            }

            if (in_array('sticky-kit', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-sticky-kit',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/sticky-kit$suffix.js",
                    ['jquery'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }

            if (in_array('wNumb', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-wNumb',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/wNumb$suffix.js",
                    ['jquery'],
                    '1.1.0',
                    true
                );
            }

            if (in_array('nouislider', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-nouislider',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/nouislider$suffix.js",
                    ['jquery', 'woocommerce-products-wizard-wNumb'],
                    '14.6.3',
                    true
                );
            }

            if (in_array('nouislider-launcher', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-nouislider-launcher',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/nouislider-launcher$suffix.js",
                    ['jquery', 'woocommerce-products-wizard-nouislider'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }

            if (in_array('app', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-app',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/app$suffix.js",
                    ['jquery'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }

            if (in_array('elements-events', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-elements-events',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/elements-events$suffix.js",
                    ['jquery'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }

            if (in_array('hooks', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-hooks',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/hooks$suffix.js",
                    ['jquery'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }

            if (in_array('variation-form', $includedScripts)) {
                wp_enqueue_script(
                    'woocommerce-products-wizard-variation-form',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/js/variation-form$suffix.js",
                    ['jquery'],
                    WC_PRODUCTS_WIZARD_VERSION,
                    true
                );
            }
        }

        switch ($stylesIncludingType) {
            case 'custom':
                wp_enqueue_style(
                    'woocommerce-products-wizard-full-custom',
                    WC_PRODUCTS_WIZARD_UPLOADS_URL . 'app-full-custom.css',
                    [],
                    get_option('woocommerce_products_wizard_styles_compiled_time', '')
                );

                break;
            case 'full':
                wp_enqueue_style(
                    'woocommerce-products-wizard-full',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/$stylesFolder/app-full$suffix.css",
                    [],
                    WC_PRODUCTS_WIZARD_VERSION
                );

                break;
            case 'basic':
                wp_enqueue_style(
                    'woocommerce-products-wizard',
                    WC_PRODUCTS_WIZARD_PLUGIN_URL . "$path/front/$stylesFolder/app$suffix.css",
                    [],
                    WC_PRODUCTS_WIZARD_VERSION
                );
        }

        // WooCommerce assets versions before 3.0.0
        if (function_exists('WC') && get_option('woocommerce_enable_lightbox') === 'yes') {
            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assetsPath = str_replace(['http:', 'https:'], '', WC()->plugin_url()) . '/assets';

            wp_enqueue_script(
                'prettyPhoto',
                "$assetsPath/js/prettyPhoto/jquery.prettyPhoto$suffix.js",
                ['jquery'],
                '3.1.6',
                true
            );

            wp_enqueue_script(
                'prettyPhoto-init',
                "$assetsPath/js/prettyPhoto/jquery.prettyPhoto.init$suffix.js",
                ['prettyPhoto'],
                '3.1.6',
                true
            );

            wp_enqueue_style('woocommerce_prettyPhoto', "$assetsPath/css/prettyPhoto.css");
        }
    }

    /** Load text domain */
    public function loadTextDomain()
    {
        load_plugin_textdomain(
            'woocommerce-products-wizard',
            false,
            basename(WC_PRODUCTS_WIZARD_PLUGIN_PATH) . '/languages/'
        );
    }
    // </editor-fold>

    // <editor-fold desc="Custom styles">
    /**
     * Compile custom styles string from settings
     *
     * @return string CSS
     *
     * @throws \Exception
     */
    public static function compileCustomStyles()
    {
        require_once __DIR__ . '/../vendor/scssphp/scss.inc.php';

        $css = '';
        $mode = Settings::getGlobal('custom_styles_mode');
        $scss = new \ScssPhp\ScssPhp\Compiler();
        $scss->setImportPaths(WC_PRODUCTS_WIZARD_PLUGIN_PATH . '/src/front/scss');

        if (Settings::getGlobal('custom_styles_minification')) {
            $scss->setFormatter(new \ScssPhp\ScssPhp\Formatter\Crunched());
        }

        if ($mode == 'simple') {
            $customVariables = [
                'font-size-base' => Settings::getGlobal('style_font_size'),
                'form-item-title-font-size' => Settings::getGlobal('style_form_item_title_font_size'),
                'form-item-price-font-size' => Settings::getGlobal('style_form_item_price_font_size'),
                'primary' => Settings::getGlobal('style_color_primary'),
                'secondary' => Settings::getGlobal('style_color_secondary'),
                'success' => Settings::getGlobal('style_color_success'),
                'info' => Settings::getGlobal('style_color_info'),
                'warning' => Settings::getGlobal('style_color_warning'),
                'danger' => Settings::getGlobal('style_color_danger'),
                'light' => Settings::getGlobal('style_color_light'),
                'dark' => Settings::getGlobal('style_color_dark')
            ];

            $customVariables = apply_filters('wcProductsWizardCustomStylesVariables', $customVariables);
            $scss->setVariables($customVariables);

            $css = $scss->compile('@import "app-full.scss";');
        } elseif ($mode == 'advanced') {
            $customScss = Settings::getGlobal('custom_scss');
            $css = $scss->compile($customScss . ';@import "app-full.scss";');
        }

        return $css;
    }

    /**
     * Compile custom styles file from settings
     *
     * @throws \Exception
     */
    public static function compileCustomStylesFile()
    {
        // create uploads folder if not exists
        if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH)) {
            mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH, 0777, true);
        }

        $css = self::compileCustomStyles();
        $path = WC_PRODUCTS_WIZARD_UPLOADS_PATH . 'app-full-custom.css';

        // find and replace SVGs and fonts with base64
        preg_match_all("/url\(..\/images\/(.*?).svg\)/", $css, $SVGs);
        preg_match_all("/url\(..\/fonts\/(.*?)\)/", $css, $fonts);

        if (isset($SVGs[1]) && !empty($SVGs[1])) {
            foreach ($SVGs[1] as $SVG) {
                $filePath = WC_PRODUCTS_WIZARD_PLUGIN_URL . "src/front/images/{$SVG}.svg";
                $contents = file_get_contents($filePath);
                $css = str_replace(
                    "url(../images/{$SVG}.svg)",
                    'url("data:image/svg+xml,' . Utils::encodeURIComponent($contents) . '")',
                    $css
                );
            }
        }

        if (isset($fonts[1]) && !empty($fonts[1])) {
            foreach ($fonts[1] as $font) {
                $filePath = WC_PRODUCTS_WIZARD_PLUGIN_URL . "src/front/fonts/{$font}";
                $type = pathinfo($filePath, PATHINFO_EXTENSION);
                $contents = file_get_contents($filePath);
                $css = str_replace(
                    "url(../fonts/{$font})",
                    'url("data:font/' . $type . ';base64,' . base64_encode($contents) . '")',
                    $css
                );
            }
        }

        file_put_contents($path, $css);
    }
    // </editor-fold>

    // <editor-fold desc="Result PDF">
    /**
     * Generate and return URL and path for cart content PDF
     *
     * @param integer $id
     *
     * @return null|object
     */
    public function getResultPDFInstance($id)
    {
        require_once __DIR__ . '/../vendor/dompdf/autoload.inc.php';

        $cart = Cart::get($id);

        if (empty($cart)) {
            null;
        }

        do_action('wcProductsWizardBeforeOutput', ['id' => $id]);

        $options = new \Dompdf\Options();
        do_action('wcProductsWizardDomPDFOptions', $options, $id);

        $dompdf = new \Dompdf\Dompdf($options);
        do_action('wcProductsWizardDomPDFInstance', $dompdf, $id);

        $dompdf->loadHtml(Template::html('result-pdf', ['id' => $id], ['echo' => false]));
        $dompdf->render();
        $pageTotal = (int) $dompdf->getCanvas()->get_page_count();
        $this->activePageTotal = $pageTotal;

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(
            Template::html(
                'result-pdf',
                [
                    'id' => $id,
                    'pageTotal' => $pageTotal
                ],
                ['echo' => false]
            )
        );

        $dompdf->render();
        do_action('wcProductsWizardAfterOutput', ['id' => $id]);

        return apply_filters('wcProductsWizardResultPDFInstance', $dompdf, $id);
    }

    /**
     * Save result PDF to a file and return its URL and path
     *
     * @param integer $id
     *
     * @return null|array
     */
    public function saveAndGetResultPDF($id)
    {
        $dompdf = self::getResultPDFInstance($id);

        if (!$dompdf) {
            return null;
        }

        $name = apply_filters('wcProductsWizardResultPDFileName', get_bloginfo('name'));
        $path = WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . "$name.pdf";
        $url = WC_PRODUCTS_WIZARD_UPLOADS_URL . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . "$name.pdf";

        // create uploads folder if not exists
        if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH)) {
            mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH, 0777, true);
        }

        // create pdf folder if not exists
        if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'pdf')) {
            mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'pdf', 0777, true);
        }

        file_put_contents($path, $dompdf->output());

        $data = [
            'url' => $url,
            'path' => $path,
            'pdf' => $dompdf
        ];

        return apply_filters('wcProductsWizardResultPDFile', $data, $id);
    }

    /**
     * Output results PDF
     *
     * @param integer $id
     */
    public function outputResultPDF($id)
    {
        $dompdf = $this->getResultPDFInstance($id);

        if (!$dompdf) {
            return;
        }

        $name = apply_filters('wcProductsWizardResultPDFileName', get_bloginfo('name'));
        $dompdf->stream($name, ['Attachment' => false]);

        exit();
    }
    // </editor-fold>

    // <editor-fold desc="Thumbnail generation">
    public static function generateThumbnail($id, $cart = [])
    {
        static $cache = [];

        if (isset($cache[$id])) {
            return apply_filters('wcProductsWizardGeneratedThumbnail', $cache[$id], $id, $cart);
        }

        $cart = !empty($cart) ? $cart : Cart::get($id);
        $areas = Settings::getPost($id, 'thumbnail_areas');
        $canvasWidth = Settings::getPost($id, 'thumbnail_canvas_width');
        $canvasHeight = Settings::getPost($id, 'thumbnail_canvas_height');
        $finalImage = imagecreatetruecolor($canvasWidth, $canvasHeight);

        // Enable blend mode and save full alpha channel
        imagealphablending($finalImage, true);
        imagesavealpha($finalImage, true);
        imagefill($finalImage, 0, 0, 0x7fff0000);

        $cartAreas = [];

        foreach ($cart as $item) {
            if (!isset($item['product_id']) || !$item['product_id']) {
                continue;
            }

            $thumbnailAreas = [];

            // variation level
            if (isset($item['variation_id']) && $item['variation_id']) {
                $thumbnailAreas = Settings::getProductVariation($item['variation_id'], 'thumbnail_areas');

                foreach ($thumbnailAreas as $key => $area) {
                    if (!isset($area['name'], $area['image'])
                        || !$area['name'] || !$area['image']
                        || !Utils::getAvailabilityByRules($id, [$area])
                    ) {
                        unset($thumbnailAreas[$key]);
                    }
                }
            }

            // product level
            if (empty($thumbnailAreas)) {
                $thumbnailAreas = Settings::getProduct($item['product_id'], 'thumbnail_areas');

                foreach ($thumbnailAreas as $key => $area) {
                    if (!isset($area['name'], $area['image'])
                        || !$area['name'] || !$area['image']
                        || !Utils::getAvailabilityByRules($id, [$area])
                    ) {
                        unset($thumbnailAreas[$key]);
                    }
                }
            }

            // category level
            if (empty($thumbnailAreas)) {
                $categories = wp_get_object_terms($item['product_id'], 'product_cat');

                foreach ($categories as $category) {
                    $thumbnailAreas = Settings::getProductCategory($category->term_id, 'thumbnail_areas');

                    foreach ($thumbnailAreas as $key => $area) {
                        if (!isset($area['name'], $area['image'])
                            || !$area['name'] || !$area['image']
                            || !Utils::getAvailabilityByRules($id, [$area])
                        ) {
                            unset($thumbnailAreas[$key]);
                        }
                    }

                    if (!empty($thumbnailAreas)) {
                        break;
                    }
                }
            }

            if (empty($thumbnailAreas)) {
                continue;
            }

            foreach ($thumbnailAreas as $thumbnailArea) {
                if (!$thumbnailArea['name'] || !$thumbnailArea['image']) {
                    continue;
                }

                $cartAreas[] = [
                    'name' => $thumbnailArea['name'],
                    'image' => $thumbnailArea['image']
                ];
            }
        }

        $cartAreas = apply_filters('wcProductsWizardGeneratedThumbnailCartAreas', $cartAreas, $id, $cart);

        foreach ($areas as $area) {
            // get default area image
            $imagePath = $area['image'] ? get_attached_file($area['image']) : null;

            foreach ($cartAreas as $cartArea) {
                // find cart item area image
                if ($cartArea['name'] == $area['name']) {
                    $imagePath = get_attached_file($cartArea['image']);
                }
            }

            if (!$imagePath) {
                continue;
            }

            $image = null;
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $size = getimagesize($imagePath);

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    break;

                case 'png':
                    $image = imagecreatefrompng($imagePath);
            }

            if (!$image) {
                continue;
            }

            imagecopyresized(
                $finalImage,
                $image,
                $area['x'],
                $area['y'],
                0,
                0,
                $area['width'],
                $area['height'],
                $size[0],
                $size[1]
            );
        }

        $filename = md5(serialize(['id' => (int) $id] + $cartAreas)) . '.png';
        $folderUrl = WC_PRODUCTS_WIZARD_UPLOADS_URL . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR;
        $folderPath = WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR;

        // create uploads folder if not exists
        if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH)) {
            mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH, 0777, true);
        }

        // create thumbnails folder if not exists
        if (!file_exists(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'thumbnails')) {
            mkdir(WC_PRODUCTS_WIZARD_UPLOADS_PATH . DIRECTORY_SEPARATOR . 'thumbnails', 0777, true);
        }

        imagepng($finalImage, $folderPath . $filename);
        imagedestroy($finalImage);

        $output = [
            'url' => $folderUrl . $filename,
            'path' => $folderPath . $filename
        ];

        $cache[$id] = $output;

        return apply_filters('wcProductsWizardGeneratedThumbnailData', $output, $id, $cart);
    }
    // </editor-fold>

    // <editor-fold desc="Deprecated">
    /**
     * Get pagination items array
     *
     * @param array $args
     *
     * @return array
     *
     * @deprecated 3.0.0
     */
    public static function getPaginationItems($args)
    {
        return Form::getPaginationItems($args);
    }

    /**
     * Get product thumbnail image or placeholder path
     *
     * @param integer $attachmentId
     * @param string $size
     *
     * @return string
     *
     * @deprecated 8.2.0
     */
    public static function getThumbnailPath($attachmentId = null, $size = 'thumbnail')
    {
        return Utils::getThumbnailPath($attachmentId, $size);
    }

    /**
     * Find and replace image src URLs by base64 version in HTML
     *
     * @param string $string
     *
     * @return string
     *
     * @deprecated 8.2.0
     */
    public static function replaceImagesToBase64InHtml($string)
    {
        return Utils::replaceImagesToBase64InHtml($string);
    }

    /**
     * Make string of attributes from array
     *
     * @param array $attributes
     *
     * @return string
     *
     * @deprecated 8.2.0
     */
    public static function attributesArrayToString($attributes)
    {
        return Utils::attributesArrayToString($attributes);
    }
    // </editor-fold>
}

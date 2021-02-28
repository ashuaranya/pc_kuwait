<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Template Class
 *
 * @class Template
 * @version 2.4.3
 */
class Template
{
    /**
     * Array of the showed templates
     * @var array
     */
    public static $showed = [];

    /**
     * Array of the showing template args
     * @var array
     */
    public static $currentHTMLArgs = [];

    /**
     * Return step view template name
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return string
     */
    public static function getFormName($postId, $stepId)
    {
        $template = Settings::getStep($postId, $stepId, 'template');

        // @since 3.18.0 - deprecated settings
        if ($template == 'default') {
            $template = 'list';
        }

        if (in_array($template, ['grid-2', 'grid-3', 'grid-4'])) {
            $template = 'grid';
        }

        return apply_filters('wcProductsWizardFormTemplateName', $template, $postId, $stepId);
    }

    /**
     * Get available form templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getFormList()
    {
        $templates = [];
        $pluginFiles = WC_PRODUCTS_WIZARD_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'form'
            . DIRECTORY_SEPARATOR . 'layouts';

        $themeFiles = get_stylesheet_directory() . DIRECTORY_SEPARATOR . WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR
            . DIRECTORY_SEPARATOR . 'form' . DIRECTORY_SEPARATOR . 'layouts';

        foreach ([$pluginFiles, $themeFiles] as $sourceFiles) {
            if (!file_exists($sourceFiles)) {
                continue;
            }

            foreach (scandir($sourceFiles) as $file) {
                if (is_dir($sourceFiles . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $name = str_replace('.php', '', $file);
                $templates[$name] = $name;
            }
        }

        return apply_filters('wcProductsWizardFormTemplates', $templates);
    }

    /**
     * Get available form item templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getFormItemList()
    {
        $templates = [];
        $pluginFiles = WC_PRODUCTS_WIZARD_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'form'
            . DIRECTORY_SEPARATOR . 'item';

        $themeFiles = get_stylesheet_directory() . DIRECTORY_SEPARATOR . WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR
            . DIRECTORY_SEPARATOR . 'form' . DIRECTORY_SEPARATOR . 'item';

        foreach ([$pluginFiles, $themeFiles] as $sourceFiles) {
            if (!file_exists($sourceFiles)) {
                continue;
            }

            foreach (scandir($sourceFiles) as $file) {
                if (is_dir($sourceFiles . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $name = str_replace('.php', '', $file);
                $templates[$name] = $name;
            }
        }

        return apply_filters('wcProductsWizardFormItemTemplates', $templates);
    }

    /**
     * Get available variation type templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getVariationsTypeList()
    {
        $templates = [];
        $pluginFiles = implode(
            DIRECTORY_SEPARATOR,
            [
                WC_PRODUCTS_WIZARD_PLUGIN_PATH,
                'views',
                'form',
                'item',
                'prototype',
                'variations',
                'item'
            ]
        );

        $themeFiles = implode(
            DIRECTORY_SEPARATOR,
            [
                get_stylesheet_directory(),
                WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR,
                'form',
                'item',
                'prototype',
                'variations',
                'item'
            ]
        );

        foreach ([$pluginFiles, $themeFiles] as $sourceFiles) {
            if (!file_exists($sourceFiles)) {
                continue;
            }

            foreach (scandir($sourceFiles) as $file) {
                if (is_dir($sourceFiles . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $name = str_replace('.php', '', $file);
                $templates[$name] = $name;
            }
        }

        return apply_filters('wcProductsWizardVariationsTypeTemplates', $templates);
    }

    /**
     * Return form item view template name
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return string
     */
    public static function getFormItemName($postId, $stepId)
    {
        $template = Settings::getStep($postId, $stepId, 'item_template');

        // @since 3.18.0 - deprecated settings
        if ($template == 'default') {
            $template = 'type-1';
        }

        return apply_filters('wcProductsWizardFormItemTemplateName', $template, $postId, $stepId);
    }

    /**
     * Get available nav list templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getNavList()
    {
        $templates = [];
        $pluginFiles = WC_PRODUCTS_WIZARD_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'nav'
            . DIRECTORY_SEPARATOR . 'list';

        $themeFiles = get_stylesheet_directory() . DIRECTORY_SEPARATOR . WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR
            . DIRECTORY_SEPARATOR . 'nav' . DIRECTORY_SEPARATOR . 'list';

        foreach ([$pluginFiles, $themeFiles] as $sourceFiles) {
            if (!file_exists($sourceFiles)) {
                continue;
            }

            foreach (scandir($sourceFiles) as $file) {
                if (is_dir($sourceFiles . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $name = str_replace('.php', '', $file);
                $templates[$name] = $name;
            }
        }

        return apply_filters('wcProductsWizardNavListTemplates', $templates);
    }

    /**
     * Include php-template by the name.
     * First looking in the "theme folder/woocommerce-products-wizard (WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR)"
     * Second looking in the "plugin folder/views"
     * Making extraction of the arguments as variables
     *
     * @param string $name
     * @param array $arguments
     * @param array $templateSettings
     *
     * @return string
     */
    public static function html($name = '', $arguments = [], $templateSettings = [])
    {
        $defaultSettings = [
            'echo' => true,
            'once' => false,
            'compress' => !WC_PRODUCTS_WIZARD_DEBUG
        ];

        $templateSettings = array_merge($defaultSettings, $templateSettings);

        // show template only once
        if ($templateSettings['once'] && in_array($name, self::$showed)) {
            return null;
        }

        // save template as showed
        self::$showed[] = $name;

        if (is_array($arguments)) {
            extract($arguments, EXTR_PREFIX_SAME, 'data');

            self::$currentHTMLArgs = $arguments;
        }

        $path = get_stylesheet_directory()
            . DIRECTORY_SEPARATOR . WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR
            . DIRECTORY_SEPARATOR . $name . '.php';

        if (!file_exists($path)) {
            $path = WC_PRODUCTS_WIZARD_PLUGIN_PATH
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . $name . '.php';
        }

        $path = apply_filters('wcProductsWizardTemplateHTMLPath', $path, $name, $arguments, $templateSettings);

        if (!file_exists($path)) {
            return '';
        }

        ob_start();

        include($path);

        $html = ob_get_clean();

        if ($templateSettings['compress']) {
            $replace = [
                '/\>[^\S ]+/s' => '>',      // strip whitespaces after tags, except space
                '/[^\S ]+\</s' => '<',      // strip whitespaces before tags, except space
                '/(\s)+/s' => '\\1',        // shorten multiple whitespace sequences
                '/<!--spacer-->/' => ' ',   // replace spacer tag
                '/<!--(.|\s)*?-->/' => ''   // remove HTML comments
            ];

            $html = preg_replace(array_keys($replace), array_values($replace), $html);
        }

        if ($templateSettings['echo']) {
            echo $html;

            return null;
        }

        return $html;
    }

    /**
     * Get requested HTML part arguments
     *
     * @param array $defaults
     * @param array $settings
     *
     * @return array
     */
    public static function getHTMLArgs($defaults = [], $settings = [])
    {
        $defaultsSettings = ['recursive' => false];
        $settings = array_replace($defaultsSettings, $settings);
        $arguments = self::$currentHTMLArgs;

        if (!empty($arguments)) {
            foreach ($defaults as $defaultKey => $_) {
                // find arguments from shortcode attributes
                if (strtolower($defaultKey) == $defaultKey || !isset($arguments[strtolower($defaultKey)])) {
                    continue;
                }

                $arguments[$defaultKey] = $arguments[strtolower($defaultKey)];

                unset($arguments[strtolower($defaultKey)]);
            }

            if ($settings['recursive']) {
                $arguments = array_replace_recursive($defaults, $arguments);
            } else {
                $arguments = array_replace($defaults, $arguments);
            }

            return $arguments;
        }

        return $defaults;
    }
}

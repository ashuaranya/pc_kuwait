<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Localization Class
 *
 * @class L10N
 * @version 1.0.0
 */
class L10N
{
    /**
     * Translate and return string
     *
     * @param string $string
     * @param string $domain
     *
     * @return string
     */
    public static function r($string, $domain = 'woocommerce-products-wizard')
    {
        return esc_html__($string, $domain);
    }

    /**
     * Translate and echo string
     *
     * @param string $string
     * @param string $domain
     */
    public static function e($string, $domain = 'woocommerce-products-wizard')
    {
        esc_html_e($string, $domain);
    }
}

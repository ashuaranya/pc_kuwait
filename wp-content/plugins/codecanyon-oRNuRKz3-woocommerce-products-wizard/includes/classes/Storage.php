<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Storage Class
 *
 * @class Storage
 * @version 2.0.2
 */
class Storage
{
    /**
     * Set variable value
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string|integer|array $value
     * @param string $key - key of the value array to replace
     */
    public static function set($nameSpace, $postId, $value = null, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if ($key) {
            $storedValue[$postId][$key] = $value;
        } else {
            $storedValue[$postId] = $value;
        }

        self::setValue($nameSpace, $storedValue);
    }

    /**
     * Get the variable from the storage
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     *
     * @return array|string|number
     */
    public static function get($nameSpace, $postId = null, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if ($key) {
            if (isset($storedValue[$postId][$key])) {
                return $storedValue[$postId][$key];
            } else {
                return '';
            }
        } elseif ($postId) {
            if (isset($storedValue[$postId])) {
                return $storedValue[$postId];
            } else {
                return '';
            }
        }

        return $storedValue;
    }

    /**
     * Check is variable exists
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     *
     * @return bool
     */
    public static function exists($nameSpace, $postId, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if (($key && isset($storedValue[$postId][$key])) || (!$key && isset($storedValue[$postId]))) {
            return true;
        }

        return false;
    }

    /**
     * Remove the step from the storage
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     */
    public static function remove($nameSpace, $postId, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if ($key) {
            unset($storedValue[$postId][$key]);
        } else {
            unset($storedValue[$postId]);
        }

        self::setValue($nameSpace, $storedValue);
    }

    /**
     * Get session by namespace
     *
     * @param string $nameSpace
     *
     * @return array
     */
    private static function getSession($nameSpace)
    {
        // start session if it doesn't exist
        if (!session_id() && !(is_admin() && !is_ajax())) {
            @session_start();
        }

        return session_id() && isset($_SESSION[$nameSpace]) ? $_SESSION[$nameSpace] : null;
    }

    /**
     * Get session by namespace
     *
     * @param string $nameSpace
     *
     * @return array
     */
    private static function getValue($nameSpace)
    {
        if (Settings::getGlobal('store_session_in_db') && function_exists('WC')) {
            return WC()->session->get($nameSpace);
        }

        return self::getSession($nameSpace);
    }

    /**
     * Set a value to the storage
     *
     * @param string $nameSpace
     * @param string|integer|array $value
     */
    private static function setValue($nameSpace, $value)
    {
        // start session if it doesn't exist
        if (!session_id() && !(is_admin() && !is_ajax())) {
            @session_start();
        }

        $_SESSION[$nameSpace] = $value;

        if (Settings::getGlobal('store_session_in_db') && function_exists('WC')) {
            WC()->session->set($nameSpace, $value);
        }
    }
}

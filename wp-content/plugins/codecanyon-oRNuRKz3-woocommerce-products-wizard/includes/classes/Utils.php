<?php
namespace WCProductsWizard;

/**
 * WCProductsWizard Utils Class
 *
 * @class Utils
 * @version 1.4.0
 */
class Utils
{
    /**
     * Get term children array
     *
     * @param integer $termId
     * @param string $taxonomy
     *
     * @return array
     */
    public static function getSubTerms($termId, $taxonomy)
    {
        $termsIds = get_term_children($termId, $taxonomy);
        $output = [];

        foreach ($termsIds as $termId) {
            $term = get_term_by('id', $termId, $taxonomy);

            $output[$termId] = $term;
        }

        return apply_filters('wcProductsWizardSubTerms', $output, $termId, $taxonomy);
    }

    /**
     * Get min and max products prices of category
     *
     * @param integer $postId
     * @param integer $stepId
     *
     * @return array
     */
    public static function getPriceLimits($postId, $stepId)
    {
        static $priceLimitsCache = [];

        if (isset($priceLimitsCache[$postId][$stepId])) {
            return apply_filters('wcProductsWizardPriceLimits', $priceLimitsCache[$postId][$stepId], $postId, $stepId);
        }

        global $wpdb;

        $productsIds = Product::getStepProductsIds($postId, $stepId);

        // get all related products prices related to a specific product category
        $results = $wpdb->get_col(
            "SELECT pm.meta_value
            FROM {$wpdb->prefix}posts as posts
            INNER JOIN {$wpdb->prefix}postmeta as pm ON posts.ID = pm.post_id
            WHERE posts.ID IN (" . implode(',', $productsIds) . ")
            AND pm.meta_key = '_price'"
        );

        // sorting prices numerically
        sort($results, SORT_NUMERIC);

        // get min and max prices
        $output = [
            'min' => (float) current($results),
            'max' => (float) end($results)
        ];

        $priceLimitsCache[$postId][$stepId] = $output;

        return apply_filters('wcProductsWizardPriceLimits', $output, $postId, $stepId);
    }

    /**
     * Get product thumbnail image or placeholder path
     *
     * @param integer $attachmentId
     * @param string $size
     *
     * @return string
     */
    public static function getThumbnailPath($attachmentId = null, $size = 'thumbnail')
    {
        if (!$attachmentId) {
            $placeholder = get_option('woocommerce_placeholder_image', 0);

            if ($placeholder) {
                $attachmentId = $placeholder;
            } else {
                return WC()->plugin_path() . '/assets/images/placeholder.png';
            }
        }

        $file = get_attached_file($attachmentId, true);

        if (empty($size) || $size == 'full') {
            // for the original size get_attached_file is fine
            return realpath($file);
        }

        if (!wp_attachment_is_image($attachmentId)) {
            // id is not referring to a media
            return null;
        }

        $info = image_get_intermediate_size($attachmentId, $size);

        if (!is_array($info) || !isset($info['file'])) {
            return realpath($file);
        }

        return realpath(str_replace(wp_basename($file), $info['file'], $file));
    }

    /**
     * Parse JSONed request to an array
     *
     * @param array $postData
     *
     * @return array
     */
    public static function parseArrayOfJSONs($postData)
    {
        foreach ($postData as &$value) {
            if (is_string($value)) {
                $decode = json_decode(stripslashes($value), true);
                $value = $decode ? $decode : $value;
            }
        }

        return $postData;
    }

    /**
     * Find image tags in the string
     *
     * @param string $htmlString
     *
     * @return array
     */
    public static function getImagesFromHtml($htmlString)
    {
        $images = [];

        // get all images
        preg_match_all('/<img[^>]+>/i', $htmlString, $imageMatches, PREG_SET_ORDER);

        // loop the images and add the raw img html tag to $images
        foreach ($imageMatches as $imageMatch) {
            $image = [];
            $image['html'] = $imageMatch[0];

            // get attributes
            preg_match_all('/\s+?(.+)="([^"]*)"/U', $imageMatch[0], $image_attr_matches, PREG_SET_ORDER);

            foreach ($image_attr_matches as $image_attr) {
                $image['attr'][$image_attr[1]] = $image_attr[2];
            }

            $images[] = $image;
        }

        return $images;
    }

    /**
     * Find and replace image src URLs by base64 version in HTML
     *
     * @param string $string
     *
     * @return string
     */
    public static function replaceImagesToBase64InHtml($string)
    {
        $images = self::getImagesFromHtml($string);

        foreach ($images as $image) {
            if (!isset($image['attr']['src']) || empty($image['attr']['src'])) {
                continue;
            }

            $type = pathinfo($image['attr']['src'], PATHINFO_EXTENSION);
            $data = file_get_contents($image['attr']['src']);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $string = str_replace($image['attr']['src'], $base64, $string);
        }

        return $string;
    }

    /**
     * Send a JSON request
     *
     * @param array $data
     */
    public static function sendJSON($data)
    {
        wp_send_json(apply_filters('wcProductsWizardSendJSONData', $data));
    }

    /**
     * Make string of attributes from array
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function attributesArrayToString($attributes)
    {
        return implode(
            ' ',
            array_map(
                function ($k, $v) {
                    return $k . '="' . htmlspecialchars($v) . '"';
                },
                array_keys($attributes),
                $attributes
            )
        );
    }

    /**
     * Implode styles array to inline string
     *
     * @param array $array
     *
     * @return string
     */
    public static function stylesArrayToString($array)
    {
        if (!is_array($array)) {
            return '';
        }

        return implode(
            ';',
            array_map(
                function ($value, $key) {
                    return "$key:$value" ;
                },
                array_values($array),
                array_keys($array)
            )
        );
    }

    /**
     * Encode string to URI
     *
     * @param string $str
     *
     * @return string
     */
    public static function encodeURIComponent($str)
    {
        $revert = ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')'];

        return strtr(rawurlencode($str), $revert);
    }

    /**
     * Check the availability rules according to the current state
     *
     * @param integer $postId
     * @param array $rules
     *
     * @return bool
     */
    public static function getAvailabilityByRules($postId, $rules = [])
    {
        if (!$rules || !is_array($rules) || empty($rules)) {
            return true;
        }

        if (!Settings::getPost($postId, 'check_availability_rules')) {
            return true;
        }

        $cartProductsIds = Cart::getProductsAndVariationsIds($postId);
        $cartCategories = Cart::getCategories($postId);
        $metRules = [];
        $previousMet = null;

        foreach ($rules as $rule) {
            if (!isset($rule['source'], $rule['condition'], $rule['inner_relation'])
                || !$rule['source'] || !$rule['condition'] || !$rule['inner_relation']
                || ($rule['source'] == 'product' && empty($rule['product']))
                || ($rule['source'] == 'category' && empty($rule['category']))
                || ($rule['source'] == 'attribute' && (empty($rule['attribute']) || empty($rule['attribute_values'])))
                || ($rule['source'] == 'custom_field'
                    && (empty($rule['custom_field_name']) || empty($rule['custom_field_name'])))
            ) {
                continue;
            }

            $isMet = true;

            switch ($rule['source']) {
                case 'product':
                    $rule['product'] = !is_array($rule['product']) ? [trim($rule['product'])] : $rule['product'];
                    $isMet = $rule['inner_relation'] == 'and'
                        ? count(array_intersect($rule['product'], $cartProductsIds)) == count($rule['product'])
                        : !empty(array_intersect($rule['product'], $cartProductsIds));

                    break;
                case 'category':
                    $rule['category'] = !is_array($rule['category']) ? [trim($rule['category'])] : $rule['category'];
                    $isMet = $rule['inner_relation'] == 'and'
                        ? count(array_intersect($rule['category'], $cartCategories)) == count($rule['category'])
                        : !empty(array_intersect($rule['category'], $cartCategories));

                    break;
                case 'attribute':
                    if (!taxonomy_exists('pa_' . $rule['attribute'])) {
                        break;
                    }

                    $values = Cart::getAttributeValues($postId, 'pa_' . $rule['attribute']);
                    $ids = wp_parse_id_list($rule['attribute_values']);

                    if (empty($ids)) {
                        break;
                    }

                    $isMet = $rule['inner_relation'] == 'and'
                        ? count(array_intersect($ids, $values)) == count($ids)
                        : !empty(array_intersect($ids, $values));

                    break;
                case 'custom_field':
                    $cartItem = Cart::getStepDataByKey($postId, $rule['custom_field_name']);
                    $isMet = $cartItem && $cartItem['value'] == $rule['custom_field_value'];
            }

            if ($rule['condition'] == 'not_in_cart') {
                $isMet = !$isMet;
            }

            if (isset($rule['outer_relation']) && $rule['outer_relation'] == 'and' && end($rules) != $rule) {
                if (!is_null($previousMet)) {
                    $previousMet = (int) $previousMet && $isMet;
                } else {
                    $previousMet = (int) $isMet;
                }
            } else {
                if (!is_null($previousMet)) {
                    $metRules[] = (int) $previousMet && $isMet;
                    $previousMet = null;
                } else {
                    $metRules[] = (int) $isMet;
                }
            }
        }

        if (!empty($metRules) && !in_array(1, $metRules)) {
            return false;
        }
        
        return true;
    }
}

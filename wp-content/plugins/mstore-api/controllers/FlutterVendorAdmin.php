<?php
require_once (__DIR__ . '/helpers/WCFM.php');
require_once (ABSPATH . '/wp-load.php');
require_once (ABSPATH . 'wp-admin' . '/includes/file.php');
require_once (ABSPATH . 'wp-admin' . '/includes/image.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
*/

class FlutterVendorAdmin extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'vendor-admin';
    
    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array(
            $this,
            'register_flutter_vendor_admin_routes'
        ));
        add_filter('woocommerce_rest_prepare_product_object', array(
            $this,
            'prepeare_product_response'
        ) , 11, 3);
    }

    public function register_flutter_vendor_admin_routes()
    {
        /// Product endpoints
        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'vendor_admin_get_products'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));
        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'vendor_admin_create_product'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));

        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => 'PUT',
                'callback' => array(
                    $this,
                    'vendor_admin_update_product'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));

        /// Order endpoints
        register_rest_route($this->namespace, '/orders', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'vendor_admin_get_orders'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));
        

        register_rest_route($this->namespace, '/vendor-orders', array(
            array(
                'methods' => "PUT",
                'callback' => array(
                    $this,
                    'vendor_admin_update_order_status'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));
        
        // register_rest_route($this->namespace, '/orders/(?P<id>[\d]+)/', array(
        //     array(
        //         'methods' => WP_REST_Server::READABLE,
        //         'callback' => array(
        //             $this,
        //             'vendor_admin_get_orders'
        //         ) ,
        //         'permission_callback' => function ()
        //         {
        //             return parent::checkApiPermission();
        //         }
        //     ) ,
        // ));

        // Review endpoints
        register_rest_route($this->namespace, '/reviews', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'flutter_get_reviews_single_vendor'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));

        // Update review status
        register_rest_route($this->namespace, '/reviews/(?P<id>[\d]+)/', array(
            array(
                'methods' => "PUT",
                'callback' => array(
                    $this,
                    'flutter_update_review_status'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));        


        /// Get Sale Stats
        register_rest_route($this->namespace, '/sale-stats', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'flutter_get_sale_stats'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));

        // Get notification
        register_rest_route($this->namespace, '/notifications', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_notification'
                ) ,
                'permission_callback' => function ()
                {
                    return parent::checkApiPermission();
                }
            ) ,
        ));

        /* --------------------------- */
    }

    /*  These functions were added by Toan 03/11/2020  */

    //  Get WCFM Sale stats
    public function flutter_get_sale_stats($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $helper = new FlutterWCFMHelper();
        return $helper->flutter_get_wcfm_sale_stats($user_id);

    }

    protected function get_product_item($id)
    {
        if (!wc_get_product($id)) return parent::sendError("invalid_product", "This product does not exist", 404);
        return wc_get_product($id);
    }

    protected function upload_image_from_mobile($image, $count, $user_id)
    {
        $imgdata = $image;
        $imgdata = trim($imgdata);
        $imgdata = str_replace('data:image/png;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/jpg;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/jpeg;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/gif;base64,', '', $imgdata);
        $imgdata = str_replace(' ', '+', $imgdata);
        $imgdata = base64_decode($imgdata);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
        $type_file = explode('/', $mime_type);
        $avatar = time() . '_' . $count . '.' . $type_file[1];

        $uploaddir = wp_upload_dir();
        $myDirPath = $uploaddir["path"];
        $myDirUrl = $uploaddir["url"];

        file_put_contents($uploaddir["path"] . '/' . $avatar, $imgdata);

        $filename = $myDirUrl . '/' . basename($avatar);
        $wp_filetype = wp_check_filetype(basename($filename) , null);
        $uploadfile = $uploaddir["path"] . '/' . basename($filename);

        $attachment = array(
            "post_mime_type" => $wp_filetype["type"],
            "post_title" => preg_replace("/\.[^.]+$/", "", basename($filename)) ,
            "post_content" => "",
            "post_author" => $user_id,
            "post_status" => "inherit",
            'guid' => $myDirUrl . '/' . basename($filename) ,
        );

        $attachment_id = wp_insert_attachment($attachment, $uploadfile);
        $attach_data = wp_generate_attachment_metadata($attachment_id, $uploadfile);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        return $attachment_id;
    }

    protected function find_image_id($image)
    {
        $image_id = attachment_url_to_postid(stripslashes($image));
        return $image_id;
    }

    protected function http_check($url)
    {
        if ((!(substr($url, 0, 7) == 'http://')) && (!(substr($url, 0, 8) == 'https://')))
        {
            return false;
        }
        return true;
    }

    /// Edit product
    public function vendor_admin_update_product($request)
    {

        /// Validate cookie and user
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        /// Validate product ID
        $id = isset($request['id']) ? absint($request['id']) : 0;
        if (isset($request['id']))
        {
            $product = $this->get_product_item($id);
        }
        else
        {
            return parent::sendError("request_failed", "Invalid data", 400);
        }

        /// Validate requested user_id and product_id
        $post_obj = get_post($product->get_id());
        $author_id = $post_obj->post_author;
        if ($user_id != $author_id)
        {
            return parent::sendError("unauthorized", "You are not allow to do this", 401);
        }

        $featured_image = $request['featuredImage'];
        $product_images = $request['images'];
        $count = 1;

        if (isset($featured_image))
        {
            if(!empty($featured_image)){
                if ($this->http_check($featured_image))
                {
                    $featured_image_id = $this->find_image_id($featured_image);
                    $product->set_image_id($featured_image_id);
                }
                else
                {
                    $featured_image_id = $this->upload_image_from_mobile($featured_image, $count, $user_id);
                    $product->set_image_id($featured_image_id);
                    $count = $count + 1;
                }
            }
            else{
                $product->set_image_id('');
            }

        }

        if (isset($product_images))
        {
            $product_images_array = array_filter(explode(',', $product_images));
            $img_array = array();

            foreach ($product_images_array as $p_img)
            {
                if (!empty($p_img))
                {
                    if ($this->http_check($p_img))
                    {
                        $img_id = $this->find_image_id($p_img);
                        array_push($img_array, $img_id);
                    }
                    else
                    {
                        $img_id = $this->upload_image_from_mobile($p_img, $count, $user_id);
                        array_push($img_array, $img_id);
                        $count = $count + 1;
                    }
                }
            }
            $product->set_gallery_image_ids($img_array);
        }

        /// Set attributes to product
        if (isset($product) && !is_wp_error($product))
        {
            if (isset($request['name']))
            {
                $product->set_name(wp_filter_post_kses($request['name']));
            }
            // Featured Product.
            if (isset($request['featured']))
            {
                $product->set_featured($request['featured']);
            }
            // SKU.
            if (isset($request['sku']))
            {
                $product->set_sku(wc_clean($request['sku']));
            }

            // Catalog Visibility.
            //   if ( isset( $request['catalog_visibility'] ) ) {
            // 	$product->set_catalog_visibility( $request['catalog_visibility'] );
            //   }
            // Check for featured/gallery images, upload it and set it.
            //   if ( isset( $request['images'] ) ) {
            // 	$product = $this->set_product_images( $product, $request['images'] );
            //   }
            // Sales and prices.
            if (in_array($product->get_type() , array(
                'variable',
                'grouped'
            ) , true))
            {
                $product->set_regular_price('');
                $product->set_sale_price('');
                $product->set_date_on_sale_to('');
                $product->set_date_on_sale_from('');
                $product->set_price('');
            }
            else
            {
                // Regular Price.
                if (isset($request['regular_price']))
                {
                    $product->set_regular_price($request['regular_price']);
                }
                // Sale Price.
                if (isset($request['sale_price']))
                {
                    $product->set_sale_price($request['sale_price']);
                }
                if (isset($request['date_on_sale_from']))
                {
                    $product->set_date_on_sale_from($request['date_on_sale_from']);
                }
                if (isset($request['date_on_sale_from_gmt']))
                {
                    $product->set_date_on_sale_from($request['date_on_sale_from_gmt'] ? strtotime($request['date_on_sale_from_gmt']) : null);
                }

                if (isset($request['date_on_sale_to']))
                {
                    $product->set_date_on_sale_to($request['date_on_sale_to']);
                }

                if (isset($request['date_on_sale_to_gmt']))
                {
                    $product->set_date_on_sale_to($request['date_on_sale_to_gmt'] ? strtotime($request['date_on_sale_to_gmt']) : null);
                }

            }

            // Description
            if (isset($request['description']))
            {
                $product->set_description($request['description']);
            }
            if (isset($request['short_description']))
            {
                $product->set_short_description($request['short_description']);
            }

            // Stock status.
            if (isset($request['in_stock']))
            {
                $stock_status = true === $request['in_stock'] ? 'instock' : 'outofstock';
            }
            else
            {
                $stock_status = $product->get_stock_status();
            }

            // Stock data.
            if ('yes' === get_option('woocommerce_manage_stock'))
            {
                // Manage stock.
                if (isset($request['manage_stock']))
                {
                    $product->set_manage_stock($request['manage_stock']);
                }

                // Backorders.
                if (isset($request['backorders']))
                {
                    $product->set_backorders($request['backorders']);
                }

                if ($product->is_type('grouped'))
                {
                    $product->set_manage_stock('no');
                    $product->set_backorders('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status($stock_status);
                }
                elseif ($product->is_type('external'))
                {
                    $product->set_manage_stock('no');
                    $product->set_backorders('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status('instock');
                }
                elseif ($product->get_manage_stock())
                {
                    // Stock status is always determined by children so sync later.
                    if (!$product->is_type('variable'))
                    {
                        $product->set_stock_status($stock_status);
                    }

                    // Stock quantity.
                    if (isset($request['stock_quantity']))
                    {
                        $product->set_stock_quantity(wc_stock_amount($request['stock_quantity']));
                    }
                    elseif (isset($request['inventory_delta']))
                    {
                        $stock_quantity = wc_stock_amount($product->get_stock_quantity());
                        $stock_quantity += wc_stock_amount($request['inventory_delta']);
                        $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                    }
                }
                else
                {
                    // Don't manage stock.
                    $product->set_manage_stock('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status($stock_status);
                }
            }
            elseif (!$product->is_type('variable'))
            {
                $product->set_stock_status($stock_status);
            }

            //Assign categories
            if (isset($request['categories']))
            {
                $categories = array_filter(explode(',', $request['categories']));
                if (!empty($categories))
                {
                    $categoryArray = array();
                    foreach ($categories as $index)
                    {
                        $categoryArray[] = absint($index);
                    }
                    $product->set_category_ids($categoryArray);
                }
            }

            //Description
            //$product->set_short_description( $request['short_description'] );
            //$product->set_description( $request['description'] );
            if (is_wp_error($product))
            {
                return parent::sendError("request_failed", "Bad data", 400);
            }

            $product->save();
            wp_update_post(array(
                'ID' => $product->get_id() ,
                'post_author' => $user_id
            ));
            //print_r($product);
            $image_arr = array();
            $p = $product->get_data();
            foreach (array_filter($p['gallery_image_ids']) as $img)
            {
                $image = wp_get_attachment_image_src($img, 'full');

                if (!is_null($image[0]))
                {
                    $image_arr[] = $image[0];
                }
            }
            $p['images'] = $image_arr;
            $image = wp_get_attachment_image_src($p['image_id'], 'full');
            if (!is_null($image[0]))
            {
                $p['featured_image'] = $image[0];
            }

            $p['type'] = $product->get_type();
            $p['on_sale'] = $product->is_on_sale();

            return new WP_REST_Response(array(
                'status' => 'success',
                'response' => $p,
            ) , 200);
        }
    }

    // UPDATE ORDER STATUS
    public function vendor_admin_update_order_status($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $helper = new FlutterWCFMHelper();
        return $helper->flutter_update_wcfm_order_status($request, $user_id);
    }

    // Get reviews
    public function flutter_get_reviews_single_vendor($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $helper = new FlutterWCFMHelper();
        return $helper->flutter_get_wcfm_reviews($request, $user_id);
    }

    // Update review
    public function flutter_update_review_status($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $helper = new FlutterWCFMHelper();
        return $helper->flutter_update_wcfm_review($request);
    }

    /* ---------------------------*/

    public function vendor_admin_create_product($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $user = get_userdata($user_id);
        $isSeller = in_array("seller", $user->roles) || in_array("wcfm_vendor", $user->roles);

        $requestStatus = "draft";
        if ($request["status"] != null)
        {
            $requestStatus = $request["status"];
        }

        if ($isSeller)
        {
            $args = array(
                'post_author' => $user_id,
                'post_content' => $request["description"],
                'post_status' => $requestStatus, // (Draft | Pending | Publish)
                'post_title' => $request["name"],
                'post_parent' => '',
                'post_type' => "product"
            );
            // Create a simple WooCommerce product
            $post_id = wp_insert_post($args);
            $product = wc_get_product($post_id);

            $featured_image = $request['featuredImage'];
            $product_images = $request['images'];
            $count = 1;
    
            if (isset($featured_image))
            {
                if(!empty($featured_image)){
                    if ($this->http_check($featured_image))
                    {
                        $featured_image_id = $this->find_image_id($featured_image);
                        $product->set_image_id($featured_image_id);
                    }
                    else
                    {
                        $featured_image_id = $this->upload_image_from_mobile($featured_image, $count, $user_id);
                        $product->set_image_id($featured_image_id);
                        $count = $count + 1;
                    }
                }
                else{
                    $product->set_image_id('');
                }
    
            }
    
            if (isset($product_images))
            {
                $product_images_array = array_filter(explode(',', $product_images));
                $img_array = array();
    
                foreach ($product_images_array as $p_img)
                {
                    if (!empty($p_img))
                    {
                        if ($this->http_check($p_img))
                        {
                            $img_id = $this->find_image_id($p_img);
                            array_push($img_array, $img_id);
                        }
                        else
                        {
                            $img_id = $this->upload_image_from_mobile($p_img, $count, $user_id);
                            array_push($img_array, $img_id);
                            $count = $count + 1;
                        }
                    }
                }
                $product->set_gallery_image_ids($img_array);
            }
    
            /// Set attributes to product
            if (isset($product) && !is_wp_error($product))
            {
                if (isset($request['name']))
                {
                    $product->set_name(wp_filter_post_kses($request['name']));
                }
                // Featured Product.
                if (isset($request['featured']))
                {
                    $product->set_featured($request['featured']);
                }
                // SKU.
                if (isset($request['sku']))
                {
                    $product->set_sku(wc_clean($request['sku']));
                }
    
                // Catalog Visibility.
                //   if ( isset( $request['catalog_visibility'] ) ) {
                // 	$product->set_catalog_visibility( $request['catalog_visibility'] );
                //   }
                // Check for featured/gallery images, upload it and set it.
                //   if ( isset( $request['images'] ) ) {
                // 	$product = $this->set_product_images( $product, $request['images'] );
                //   }
                // Sales and prices.
                if (in_array($product->get_type() , array(
                    'variable',
                    'grouped'
                ) , true))
                {
                    $product->set_regular_price('');
                    $product->set_sale_price('');
                    $product->set_date_on_sale_to('');
                    $product->set_date_on_sale_from('');
                    $product->set_price('');
                }
                else
                {
                    // Regular Price.
                    if (isset($request['regular_price']))
                    {
                        $product->set_regular_price($request['regular_price']);
                    }
                    // Sale Price.
                    if (isset($request['sale_price']))
                    {
                        $product->set_sale_price($request['sale_price']);
                    }
                    if (isset($request['date_on_sale_from']))
                    {
                        $product->set_date_on_sale_from($request['date_on_sale_from']);
                    }
                    if (isset($request['date_on_sale_from_gmt']))
                    {
                        $product->set_date_on_sale_from($request['date_on_sale_from_gmt'] ? strtotime($request['date_on_sale_from_gmt']) : null);
                    }
    
                    if (isset($request['date_on_sale_to']))
                    {
                        $product->set_date_on_sale_to($request['date_on_sale_to']);
                    }
    
                    if (isset($request['date_on_sale_to_gmt']))
                    {
                        $product->set_date_on_sale_to($request['date_on_sale_to_gmt'] ? strtotime($request['date_on_sale_to_gmt']) : null);
                    }
    
                }
    
                // Description
                if (isset($request['description']))
                {
                    $product->set_description($request['description']);
                }
                if (isset($request['short_description']))
                {
                    $product->set_short_description($request['short_description']);
                }
    
                // Stock status.
                if (isset($request['in_stock']))
                {
                    $stock_status = true === $request['in_stock'] ? 'instock' : 'outofstock';
                }
                else
                {
                    $stock_status = $product->get_stock_status();
                }
    
                // Stock data.
                if ('yes' === get_option('woocommerce_manage_stock'))
                {
                    // Manage stock.
                    if (isset($request['manage_stock']))
                    {
                        $product->set_manage_stock($request['manage_stock']);
                    }
    
                    // Backorders.
                    if (isset($request['backorders']))
                    {
                        $product->set_backorders($request['backorders']);
                    }
    
                    if ($product->is_type('grouped'))
                    {
                        $product->set_manage_stock('no');
                        $product->set_backorders('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status($stock_status);
                    }
                    elseif ($product->is_type('external'))
                    {
                        $product->set_manage_stock('no');
                        $product->set_backorders('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status('instock');
                    }
                    elseif ($product->get_manage_stock())
                    {
                        // Stock status is always determined by children so sync later.
                        if (!$product->is_type('variable'))
                        {
                            $product->set_stock_status($stock_status);
                        }
    
                        // Stock quantity.
                        if (isset($request['stock_quantity']))
                        {
                            $product->set_stock_quantity(wc_stock_amount($request['stock_quantity']));
                        }
                        elseif (isset($request['inventory_delta']))
                        {
                            $stock_quantity = wc_stock_amount($product->get_stock_quantity());
                            $stock_quantity += wc_stock_amount($request['inventory_delta']);
                            $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                        }
                    }
                    else
                    {
                        // Don't manage stock.
                        $product->set_manage_stock('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status($stock_status);
                    }
                }
                elseif (!$product->is_type('variable'))
                {
                    $product->set_stock_status($stock_status);
                }
    
                //Assign categories
                if (isset($request['categories']))
                {
                    $categories = array_filter(explode(',', $request['categories']));
                    if (!empty($categories))
                    {
                        $categoryArray = array();
                        foreach ($categories as $index)
                        {
                            $categoryArray[] = absint($index);
                        }
                        $product->set_category_ids($categoryArray);
                    }
                }
    
                //Description
                //$product->set_short_description( $request['short_description'] );
                //$product->set_description( $request['description'] );
                if (is_wp_error($product))
                {
                    return parent::sendError("request_failed", "Bad data", 400);
                }
    
                $product->save();
                wp_update_post(array(
                    'ID' => $product->get_id() ,
                    'post_author' => $user_id
                ));
                //print_r($product);
                $image_arr = array();
                $p = $product->get_data();
                foreach (array_filter($p['gallery_image_ids']) as $img)
                {
                    $image = wp_get_attachment_image_src($img, 'full');
    
                    if (!is_null($image[0]))
                    {
                        $image_arr[] = $image[0];
                    }
                }
                $p['images'] = $image_arr;
                $image = wp_get_attachment_image_src($p['image_id'], 'full');
                if (!is_null($image[0]))
                {
                    $p['featured_image'] = $image[0];
                }
                $p['type'] = $product->get_type();
                $p['on_sale'] = $product->is_on_sale();
    
                return new WP_REST_Response(array(
                    'status' => 'success',
                    'response' => $p,
                ) , 200);
            }
        }
        else
        {
            return parent::sendError("invalid_role", "You must be seller to create product", 401);
        }
    }

    public function vendor_admin_get_products($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $page = isset($request["page"]) ? $request["page"] : 1;
        $limit = isset($request["per_page"]) ? $request["per_page"] : 10;

        /* Modified by Toan 03/11/2020 */
        $terms = array(
            'author' => $user_id,
            'limit' => $limit,
            'page' => $page,
        );

        // Added search product feature
        if (isset($request['search']))
        {
            $terms['s'] = $request['search'];
        }

        $products = wc_get_products($terms);
        $products_arr = array();
        foreach ($products as $product)
        {
            $p = $product->get_data();
            $image_arr = array();
            foreach (array_filter($p['gallery_image_ids']) as $img)
            {
                $image = wp_get_attachment_image_src($img, 'full');
                if (!is_null($image[0]))
                {
                    $image_arr[] = $image[0];
                }

            }
            $image = wp_get_attachment_image_src($p['image_id'], 'full');
            if (!is_null($image[0]))
            {
                $p['featured_image'] = $image[0];
            }
            $p['images'] = $image_arr;
            
            $p['type'] = $product->get_type();
            $p['on_sale'] = $product->is_on_sale();
            $products_arr[] = $p;
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $products_arr
        ) , 200);
        /* -------------------------------- */
    }

    public function prepeare_product_response($response, $object, $request)
    {
        $data = $response->get_data();
        $author_id = get_post_field('post_author', $data['id']);
        if (is_plugin_active('dokan-lite/dokan.php'))
        {
            $store = dokan()
                ->vendor
                ->get($author_id);
            $dataStore = $store->to_array();
            $dataStore = array_merge($dataStore, apply_filters('dokan_rest_store_additional_fields', [], $store, $request));
            $data['store'] = $dataStore;
        }
        if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php'))
        {
            $helper = new FlutterWCFMHelper();
            $wcfm_vendors_json_arr = array();
            $data['store'] = $helper->get_formatted_item_data($author_id, $wcfm_vendors_json_arr, null, null, null);
        }

        $response->set_data($data);
        return $response;
    }

    public function vendor_admin_get_orders($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $api = new WC_REST_Orders_V1_Controller();

        $results = [];
        if (is_plugin_active('dokan-lite/dokan.php'))
        {
            $orders = dokan_get_seller_orders($user_id, 'all', null, 10000000, 0);
            foreach ($orders as $item)
            {
                $response = $api->prepare_item_for_response(wc_get_order($item->order_id) , $request);
                $results[] = $response->get_data();
            }
        }

        if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php'))
        {
            global $wpdb;
            $page = 1;
            $per_page = 10;
            if (isset($request['page']))
            {
                $page = $request['page'];
            }
            if (isset($request['per_page']))
            {
                $per_page = $request['per_page'];
            }
            $page= ($page -1) * $per_page;
            $table_name = $wpdb->prefix . "wcfm_marketplace_orders";
            $sql = "SELECT * FROM $table_name WHERE vendor_id = $user_id";
            if(isset($request['status'])){
                $status = $request['status'];
                $sql .= " AND order_status = '$status'";
            }
            if(isset($request['search'])){
                $search = $request['search'];
                $sql .= " AND order_id LIKE '$search%'";
            }
            $sql .= "GROUP BY $table_name.`order_id` ORDER BY $table_name.`order_id` DESC LIMIT $per_page OFFSET $page";
            $items = $wpdb->get_results($sql);
    
            foreach ($items as $item)
            {
                $response = $api->prepare_item_for_response(wc_get_order($item->order_id) , $request);
                $order = $response->get_data();
                $count = count($order['line_items']);
                $order['product_count'] = $count;

                for ($i = 0; $i < $count; $i++){
                    $product_id = absint($order['line_items'][$i]['product_id']);
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id));
                    if (!is_null($image[0]))
                    {
                        $order['line_items'][$i]['featured_image'] = $image[0];
                    }
                }

                // foreach ($order['line_items'] as $lineItem) {
                //     $product_id = absint($lineItem['product_id']);
                //     $image = wp_get_attachment_image_src($product_id);
                //             if (!is_null($image[0]))
                //             {
                //                 $lineItem['image']= $image[0];
                //             }
                //           }
                $results[] = $order;
            }
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $results,
        ) , 200);
    }

    public function get_notification($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }

        $helper = new FlutterWCFMHelper();
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $helper->wcfm_get_wcfm_notification_by_vendor($request, $user_id)
        ) , 200);
    }

    public function update_review_status($request)
    {
        if (isset($request["token"]))
        {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        else
        {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }
        $helper = new FlutterWCFMHelper();
        $helper->flutter_update_wcfm_review($request);
        return new WP_REST_Response(array(
            'status' => 'success',
        ) , 200);
    }

}

new FlutterVendorAdmin;


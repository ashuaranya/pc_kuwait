<?php
/**
 * WooCommerce functions
 *
 * @package Electro/WooCommerce
 */

if ( ! function_exists( 'electro_change_breadcrumb_delimiter' ) ) {
    /**
     * Change the default breadcrumb separator
     */
    function electro_change_breadcrumb_delimiter( $defaults ) {
        $defaults[ 'delimiter' ] = '<span class="delimiter"><i class="fa fa-angle-right"></i></span>';
        return $defaults;
    }
}

if ( ! function_exists( 'electro_reset_woocommerce_loop' ) ) {
    /**
     * Resets WooCommerce loop so that the subcategories and products can have different number of columns
     */
    function electro_reset_woocommerce_loop() {
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '<' ) ) {
            global $woocommerce_loop;
            $woocommerce_loop[ 'columns' ]     = '';
            $woocommerce_loop[ 'loop' ]        = '';
            $woocommerce_loop[ 'type' ]        = '';
            $woocommerce_loop[ 'columns_wide'] = '';
        }
    }
}

if ( ! function_exists( 'electro_set_loop_shop_columns' ) ) {
    /**
     * Sets Shop Loop Columns
     */
    function electro_set_loop_shop_columns() {
        
        $columns = apply_filters( 'electro_shop_loop_products_columns', 3 );

        return $columns;
    }
}

if ( ! function_exists( 'electro_shopping_cart_summary' ) ) {
    /**
     * Display Off Canvas Shopping Cart Summary
     */
    function electro_shopping_cart_summary() {
        electro_get_template( 'sections/shopping-cart-summary.php' );
    }
}

if ( ! function_exists( 'electro_set_loop_shop_columns_wide' ) ) {
    /**
     * Sets Shop Loop Columns Wide
     */
    function electro_set_loop_shop_columns_wide() {
        
        $columns_wide = apply_filters( 'electro_shop_loop_products_columns_wide', 5 );

        return $columns_wide;
    }
}

if ( ! function_exists( 'electro_set_loop_shop_subcategories_columns' ) ) {
    function electro_set_loop_shop_subcategories_columns() {
        return apply_filters( 'electro_shop_loop_subcategories_columns', 4 );
    }
}

if ( ! function_exists( 'electro_set_pagination_args' ) ) {
    /** 
     * Sets arguments for pagination
     */
    function electro_set_pagination_args( $args ) {
        
        $args[ 'end_size' ] = 1;
        $args[ 'mid_size' ] = 2;

        return $args;
    }
}

if ( ! function_exists( 'electro_set_loop_shop_per_page' ) ) {
    /**
     * Sets no of products per page
     */
    function electro_set_loop_shop_per_page() {

        if ( isset( $_REQUEST['wppp_ppp'] ) ) :
            $per_page = intval( $_REQUEST['wppp_ppp'] );
            WC()->session->set( 'products_per_page', intval( $_REQUEST['wppp_ppp'] ) );
        elseif ( isset( $_REQUEST['ppp'] ) ) :
            $per_page = intval( $_REQUEST['ppp'] );
            WC()->session->set( 'products_per_page', intval( $_REQUEST['ppp'] ) );
        elseif ( WC()->session->__isset( 'products_per_page' ) ) :
            $per_page = intval( WC()->session->__get( 'products_per_page' ) );
        else :
            $per_page = electro_set_loop_shop_columns() * 4;
            $per_page = apply_filters( 'electro_loop_shop_per_page', $per_page );
        endif;
        
        return $per_page;
    }
}

if ( ! function_exists( 'electro_before_product_archive_content' ) ) {
    /**
     * Before Product Archive Content
     */
    function electro_before_product_archive_content() {
        
        if ( is_shop() ) {
            /**
             * @hooked electro_featured_products_carousel - 10
             */
            do_action( 'electro_before_product_archive_content' );
        }
    }
}

if ( ! function_exists( 'woocommerce_product_loop_start' ) ) {

    /**
     * Output the start of a product loop. By default this is a UL.
     *
     * @param bool $echo
     * @return string
     */
    function woocommerce_product_loop_start( $echo = true ) {
        ob_start();

        $loop_classes = '';
        $product_loop_classes_arr = apply_filters( 'electro_product_loop_additional_classes', array() );

        $columns      = apply_filters( 'loop_shop_columns', 3 );
        $columns_wide = apply_filters( 'loop_shop_columns_wide', 5 );
        
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '<' ) ) {
            global $woocommerce_loop;
            $woocommerce_loop['loop'] = 0;
            if( isset( $woocommerce_loop['columns'] ) && intval( $woocommerce_loop['columns'] ) ) {
                $columns = $woocommerce_loop['columns'];
            }
        } else {
            wc_set_loop_prop( 'loop', 0 );
            $columns      = wc_get_loop_prop( 'columns', $columns );
            $columns_wide = wc_get_loop_prop( 'columns_wide', $columns_wide );
        }
        
        $product_loop_classes_arr[] = 'columns-' . $columns;
        $product_loop_classes_arr[] = 'columns__wide--' . $columns_wide;

        $data_attr = 'regular-products';
        $data_view = 'grid';

        if ( is_shop() || is_product_category() || is_product_tag() || is_tax( get_object_taxonomies( 'product' ) ) || ( is_dokan_activated() && dokan_is_store_page() ) ) {
            $data_attr = 'shop-products';
            $shop_views = electro_get_shop_views();
            foreach( $shop_views as $shop_view => $shop_view_args) {
                if ( $shop_view_args['active'] ) {
                    $data_view = $shop_view;
                    break;
                }
            }
        }

        if( is_array( $product_loop_classes_arr ) ) {
            $loop_classes = implode( ' ', $product_loop_classes_arr );
        }

        echo '<ul data-view="' . esc_attr( $data_view ) . '" data-toggle="' . esc_attr( $data_attr ) . '" class="products ' . esc_attr( $loop_classes ) . '">';

        if ( $echo )
            echo ob_get_clean();
        else
            return ob_get_clean();
    }
}

if ( ! function_exists( 'electro_get_price_html_from_to' ) ) {
    /**
     * Overwrites the sale price in html format
     */
    function electro_get_price_html_from_to( $price, $from, $to, $product ) {

        $style = electro_get_single_product_style();

        if ( ! ( is_product() && 'extended' === $style ) ){
            $price = '<ins>' . ( ( is_numeric( $to ) ) ? wc_price( $to ) : $to ) . '</ins> <del>' . ( ( is_numeric( $from ) ) ? wc_price( $from ) : $from ) . '</del>';
        }

        return apply_filters( 'electro_get_price_html_from_to', $price, $from, $to, $product );
    }
}

if( ! function_exists( 'electro_wc_format_sale_price' ) ) {
    /**
     * Format a sale price for display.
     *
     * @param  string $regular_price
     * @param  string $sale_price
     * @return string
     */
    function electro_wc_format_sale_price( $price, $regular_price, $sale_price ) {
        $price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del>' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
        return apply_filters( 'electro_wc_format_sale_price', $price, $regular_price, $sale_price );
    }
}

if ( ! function_exists( 'electro_mini_cart_fragment' ) ) {
    /**
     * Cart Fragments
     * Ensure cart contents update when products are added to the cart via AJAX
     * @param  array $fragments Fragments to refresh via AJAX
     * @return array            Fragments to refresh via AJAX
     */
    function electro_mini_cart_fragment( $fragments ) {
        global $woocommerce;

        $fragments['span.cart-items-count'] = '<span class="cart-items-count count header-icon-counter">' . WC()->cart->get_cart_contents_count() . '</span>';
        $fragments['span.cart-items-total-price'] = '<span class="cart-items-total-price total-price">' . WC()->cart->get_cart_subtotal() . '</span>';

        return $fragments;
    }
}

if ( ! function_exists( 'electro_wrap_price_html' ) ) {
    /**
     * Wraps price HTML with <span class="price"></span>
     */
    function electro_wrap_price_html( $html ) {
        return apply_filters( 'electro_price_html', '<span class="electro-price">' . $html . '</span>' );
    }
}

if ( ! function_exists( 'electro_get_shop_views' ) ) {
    /**
     * Get shop views available by electro
     */
    function electro_get_shop_views() {

        $shop_views = apply_filters( 'electro_shop_views_args', array(
            'grid'              => array(
                'label'         => esc_html__( 'Grid View', 'electro' ),
                'icon'          => 'fa fa-th',
                'enabled'       => true,
                'active'        => true,
                'template'      => array( 'slug' => 'content', 'name' => 'product' ),
            ),
            'grid-extended'     => array(
                'label'         => esc_html__( 'Grid Extended View', 'electro' ),
                'icon'          => 'fa fa-align-justify',
                'enabled'       => true,
                'active'        => false,
                'template'      => array( 'slug' => 'templates/contents/content', 'name' => 'product-grid-extended' ),
            ),
            'list-view'         => array(
                'label'         => esc_html__( 'List View', 'electro' ),
                'icon'          => 'fa fa-list',
                'enabled'       => true,
                'active'        => false,
                'template'      => array( 'slug' => 'templates/contents/content', 'name' => 'product-list-view' ),
            ),
            'list-view-small'   => array(
                'label'         => esc_html__( 'List View Small', 'electro' ),
                'icon'          => 'fa fa-th-list',
                'enabled'       => true,
                'active'        => false,
                'template'      => array( 'slug' => 'templates/contents/content', 'name' => 'product-list-small' ),
            )
        ) );

        return $shop_views;
    }
}

if ( ! function_exists( 'electro_wc_products_per_page' ) ) {
    /**
     * Outputs a dropdown for user to select how many products to show per page
     */
    function electro_wc_products_per_page() {
        
        global $wp_query;

        $action             = '';
        $cat                = '';
        $cat                = $wp_query->get_queried_object();
        $method             = apply_filters( 'electro_wc_ppp_method', 'post' );
        $return_to_first    = apply_filters( 'electro_wc_ppp_return_to_first', false );
        $total              = $wp_query->found_posts;
        $per_page           = $wp_query->get( 'posts_per_page' );
        $_per_page          = electro_set_loop_shop_columns() * 4;

        // Generate per page options
        $products_per_page_options = array();
        while( $_per_page < $total ) {
            $products_per_page_options[] = $_per_page;
            $_per_page = $_per_page * 2;
        }

        if ( empty( $products_per_page_options ) ) {
            return;
        }

        $products_per_page_options[] = -1;

        // Set action url if option behaviour is true
        // Paste QUERY string after for filter and orderby support
        $query_string = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . add_query_arg( array( 'ppp' => false ), $_SERVER['QUERY_STRING'] ) : null;

        if ( isset( $cat->term_id ) && isset( $cat->taxonomy ) && $return_to_first ) :
            $action = get_term_link( $cat->term_id, $cat->taxonomy ) . $query_string;
        elseif ( $return_to_first ) :
            $action = get_permalink( wc_get_page_id( 'shop' ) ) . $query_string;
        endif;

        // Only show on product categories
        if ( ! woocommerce_products_will_display() ) :
            return;
        endif;
        
        do_action( 'electro_wc_ppp_before_dropdown_form' );

        ?><form method="POST" action="<?php echo esc_url( $action ); ?>" class="form-electro-wc-ppp"><?php

             do_action( 'electro_wc_ppp_before_dropdown' );

            ?><select name="ppp" onchange="this.form.submit()" class="electro-wc-wppp-select c-select"><?php

                foreach( $products_per_page_options as $key => $value ) :

                    ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $per_page ); ?>><?php
                        $ppp_text = apply_filters( 'electro_wc_ppp_text', __( 'Show %s', 'electro' ), $value );
                        esc_html( printf( $ppp_text, $value == -1 ? __( 'All', 'electro' ) : $value ) ); // Set to 'All' when value is -1
                    ?></option><?php

                endforeach;

            ?></select><?php

            // Keep query string vars intact
            foreach ( $_GET as $key => $val ) :

                if ( 'ppp' === $key || 'submit' === $key ) :
                    continue;
                endif;
                if ( is_array( $val ) ) :
                    foreach( $val as $inner_val ) :
                        ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $inner_val ); ?>" /><?php
                    endforeach;
                else :
                    ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" /><?php
                endif;
            endforeach;

            do_action( 'electro_wc_ppp_after_dropdown' );

        ?></form><?php

        do_action( 'electro_wc_ppp_after_dropdown_form' );
    }
}

if ( ! function_exists( 'electro_advanced_pagination' ) ) {
    /**
     * Displays an advanced pagination
     */
    function electro_advanced_pagination() {

        global $wp_query, $wp_rewrite;

        if ( $wp_query->max_num_pages <= 1 ) {
            return;
        }

        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode( get_pagenum_link() );
        $url_parts    = explode( '?', $pagenum_link );

        // Get max pages and current page out of the current query, if available.
        $total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
        $current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';

        // URL base depends on permalink settings.
        $format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

        $base       = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
        $add_args   = false;

        $output = '';
        $prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
        $next_arrow = is_rtl() ? '&larr;' : '&rarr;';

        if ( $current && 1 < $current ) :
            $link = str_replace( '%_%', 2 == $current ? '' : $format, $base );
            $link = str_replace( '%#%', $current - 1, $link );
            $output .= '<a class="prev page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $prev_arrow . '</a>';
        endif;

        $number_input = '<form method="post" class="form-adv-pagination"><input id="goto-page" size="2" min="1" max="' . esc_attr( $total ) . '" step="1" type="number" class="form-control" value="' . esc_attr( $current ) . '" /></form>';
        $output .= sprintf( esc_html__( '%s of %s', 'electro' ), $number_input, $total );

        if ( $current && ( $current < $total || -1 == $total ) ) :
            $link = str_replace( '%_%', $format, $base );
            $link = str_replace( '%#%', $current + 1, $link );
            $output .= '<a class="next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $next_arrow . '</a>';
        endif;

        $link = str_replace( '%_%', $format, $base );
        ?>
        <nav class="electro-advanced-pagination">
            <?php echo $output; ?>
            <script>
                jQuery(document).ready(function($){
                    $( '.form-adv-pagination' ).on( 'submit', function() {
                        var link        = '<?php echo esc_url( apply_filters( 'paginate_links', $link ) ); ?>',
                            goto_page   = $( '#goto-page' ).val(),
                            new_link    = link.replace( '%#%', goto_page ).replace(/&#038;/g, '&');

                        window.location.href = new_link;
                        return false;
                    });
                });
            </script>
        </nav>
        <?php
    }
}

if ( ! function_exists( 'electro_template_loop_hover' ) ) {
    /**
     * Calls electro loop hover
     */
    function electro_template_loop_hover() {
        ?><div class="hover-area"><?php
        /**
         * @hooked electro_loop_action_buttons - 10
         */
        do_action( 'electro_product_item_hover_area' );
        ?></div><?php
    }
}

if ( ! function_exists( 'electro_loop_action_buttons' ) ) {
    /**
     * 
     */
    function electro_loop_action_buttons() {
        ?><div class="action-buttons"><?php
            do_action( 'electro_loop_action_buttons' );
        ?></div><?php
    }
}

if ( ! function_exists( 'electro_default_wc_footer_widgets' ) ) {
    /**
     * Displays default Footer Widgets when there are no widgets in the array.
     */
    function electro_default_wc_footer_widgets( $args ) {

        if( class_exists( 'WC_Widget_Products' ) ) {
            $args['widget_id'] = 'featured-products-footer';
            the_widget( 'WC_Widget_Products', array( 'title' => __( 'Featured Products', 'electro' ), 'show' => 'featured', 'number' => '3', 'orderby' => 'DESC', 'order' => 'date', 'id' => 'featured-products-footer' ), $args );

            $args['widget_id'] = 'onsale-products-footer';
            the_widget( 'WC_Widget_Products', array( 'title' => __( 'Onsale Products', 'electro' ), 'show' => 'onsale', 'number' => '3', 'orderby' => 'DESC', 'order' => 'date', 'id' => 'onsale-products-footer' ), $args );
        }

        if( class_exists( 'WC_Widget_Top_Rated_Products' ) ) {  
            $args['widget_id'] = 'top-rated-products-footer';
            the_widget( 'WC_Widget_Top_Rated_Products', array( 'title' => __( 'Top Rated Products', 'electro' ), 'number' => '3', 'id' => 'top-rated-products-footer' ), $args );
        }

    }
}

if ( ! function_exists( 'electro_default_wc_fb_widgets' ) ) {
    /**
     * Displays default footer bottom widgets
     */
    function electro_default_wc_fb_widgets( $args ) {
        ?>
        <div class="columns">
            <aside class="widget clearfix">
                <div class="body">
                    <h4 class="widget-title">
                        <?php echo esc_html__( 'Find it Fast', 'electro' ); ?>
                    </h4>
                    <ul class="menu-find-it-fast menu">
                        <?php echo do_shortcode('[popup_anything id="11558"]');  ?><br>
                        <?php echo do_shortcode('[popup_anything id="11571"]'); ?>
                        <?php
                        echo wp_list_categories(
                            array(
                                'title_li'     => '', 
                                'hide_empty'   => 1 , 
                                'taxonomy'     => 'product_cat',
                                'hierarchical' => 1 ,
                                'echo'         => 0 ,
                                'depth'        => 1 ,
                            )
                        );
                    ?>
                    </ul>
                </div>
            </aside>
        </div>
        <?php
    }
}

if( ! function_exists( 'electro_get_product_attr_taxonomies' ) ) {
    /**
     * Get All Product Attribute Taxonomies
     * 
     * @return array
     */
    function electro_get_product_attr_taxonomies() {
        
        $product_taxonomies     = array();
        $attribute_taxonomies   = wc_get_attribute_taxonomies();
        
        if ( $attribute_taxonomies ) {
            foreach ( $attribute_taxonomies as $tax ) {
                $product_taxonomies[wc_attribute_taxonomy_name( $tax->attribute_name )] = $tax->attribute_label;
            }
        }

        return $product_taxonomies;
    }
}

if ( ! function_exists( 'electro_product_category_taxonomy_fields' ) ) {
    /**
     * Sets up Product categories metaboxes
     */
    function electro_product_category_taxonomy_fields() {
        require_once get_template_directory() . '/inc/woocommerce/class-electro-categories.php';
    }
}

if ( ! function_exists( 'electro_setup_brands_taxonomy' ) ) {
    /**
     * Sets up Brands Taxonomy from Product attributes
     */
    function electro_setup_brands_taxonomy() {

        $brand_taxonomy = electro_get_brands_taxonomy();

        if ( ! empty( $brand_taxonomy ) ) {
            require_once get_template_directory() . '/inc/woocommerce/class-electro-brands.php';
        }
    }
}

if ( ! function_exists( 'electro_get_brands_taxonomy' ) ) {
    /**
     * Products Brand Taxonomy
     * 
     * @return string
     */
    function electro_get_brands_taxonomy() {
        return apply_filters( 'electro_product_brand_taxonomy', '' );
    }
}

if ( ! function_exists( 'electro_get_brand_attr' ) ) {
    function electro_get_brand_attr() {

        $brand_taxonomy = electro_get_brands_taxonomy();
        return apply_filters( 'electro_product_brand_attr', str_replace( 'pa_', '', $brand_taxonomy ) );
    }
}

if ( ! function_exists( 'electro_get_shop_layout' ) ) {
    function electro_get_shop_layout() {
        
        if ( is_product() ) {
            $layout = electro_get_single_product_layout();
        } else {
            $layout = apply_filters( 'electro_shop_layout', 'left-sidebar' );
        }

        return $layout;
    }
}

if ( ! function_exists( 'electro_get_shop_catalog_mode' ) ) {
    /**
     * Shop Catelog Mode
     * 
     * @return bool
     */
    function electro_get_shop_catalog_mode() {
        return apply_filters( 'electro_shop_catalog_mode', false );
    }
}

if( ! function_exists( 'electro_shop_archive_jumbotron' ) ) {
    function electro_shop_archive_jumbotron() {
        $static_block_id = '';
        $brands_taxonomy = electro_get_brands_taxonomy();

        if( is_shop() ) {
            $static_block_id = apply_filters( 'electro_shop_jumbotron_id', '' );
        } else if ( is_product_category() || is_tax( $brands_taxonomy ) ) {
            $term               = get_queried_object();
            $term_id            = $term->term_id;
            $static_block_id    = defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.6', '<' ) ? absint( get_woocommerce_term_meta( $term_id, 'static_block_id', true ) ) : absint( get_term_meta( $term_id, 'static_block_id', true ) );
        }

        if( ! empty( $static_block_id ) ) {
            if ( is_elementor_activated() ) {
                $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $static_block_id );
            }

            if( empty( $content ) ) {
                $static_block = get_post( $static_block_id );
                $content = do_shortcode( $static_block->post_content );
            }

            echo $content;
        }
    }
}

if( ! function_exists( 'electro_shop_bottom_archive_jumbotron' ) ) {
    function electro_shop_bottom_archive_jumbotron() {
        $static_block_id = '';
        $brands_taxonomy = electro_get_brands_taxonomy();

        if( is_shop() ) {
            $static_block_id = apply_filters( 'electro_shop_bottom_jumbotron_id', '' );
        } else if ( is_product_category() || is_tax( $brands_taxonomy ) ) {
            $term               = get_queried_object();
            $term_id            = $term->term_id;
            $static_block_id    = defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.6', '<' ) ? absint( get_woocommerce_term_meta( $term_id, 'static_block_bottom_id', true ) ) : absint( get_term_meta( $term_id, 'static_block_bottom_id', true ) );
        }

        if( ! empty( $static_block_id ) ) {
            if ( is_elementor_activated() ) {
                $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $static_block_id );
            }

            if( empty( $content ) ) {
                $static_block = get_post( $static_block_id );
                $content = do_shortcode( $static_block->post_content );
            }

            echo '<div class="shop-archive-bottom">' . $content . '</div>';
        }
    }
}

if( ! function_exists( 'electro_products_live_search' ) ) 
{
// 	echo do_shortcode('[wcas-search-form]');
    function electro_products_live_search()
	{
		
        if ( isset( $_REQUEST['fn'] ) && 'get_ajax_search' == $_REQUEST['fn'] )
		{
           
            if( isset( $_REQUEST['terms'] ) ) 
			{
				
                 $term = $_REQUEST['terms'];
				$term = str_replace(' ', '-', $term); // Replaces spaces with hyphens.
   				$term= preg_replace('/[^A-Za-z0-9\-]/', '', $term); // Removes special chars.
            }

            if ( empty( $term ) ) {
                echo json_encode( array() );
                die();
            }

            if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7', '<' ) ) 
			{
				
                $query_args = apply_filters( 'electro_live_search_query_args', array(
                    'posts_per_page'    => 10,
                    'no_found_rows'     => true,
                    'post_type'         => 'product',
                    'post_status'       => 'publish',
                    'meta_query'        => array(
                        array(
                            'key' => '_visibility',
                            'value' => array( 'search', 'visible' ),
                            'compare' => 'IN'
                        )
                    )
                ) );

                $query_args['s'] = $term;
                $search_query = new WP_Query( $query_args );
                $ids          = wp_list_pluck( $search_query->posts, 'ID' );
                $product_objects = array_map( 'wc_get_product', $ids );
            } else 
			{
                $product_objects = wc_get_products( apply_filters( 'electro_wc_live_search_query_args', array(
                    's'         => $term,
                    'orderby'   => 'relevance',
                    'order'     => 'DESC',
                    'limit'     => 10,
                    'post_status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_visibility',
                            'field'    => 'name',
                            'terms'    => 'exclude-from-catalog',
                            'operator' => 'NOT IN',
                        ),
                    )
                ) ) );
            }

            $results = array();

            if( ! empty( $product_objects ) ) {
				
                foreach ( $product_objects as $product_object )
				{
				
                    $id = electro_wc_get_product_id( $product_object );
                    $title = get_the_title( $id );
                    $title = html_entity_decode( $title.' Price:-KD'.$product_object->get_regular_price(), ENT_QUOTES, 'UTF-8' );
                   
                  
					
                   
                    $brand = '';

                    if ( has_post_thumbnail( $id ) ) {
                        $post_thumbnail_ID = get_post_thumbnail_id( $id );
                        $post_thumbnail_src = wp_get_attachment_image_src( $post_thumbnail_ID, 'thumbnail' );
                    } else{
                        $dimensions = wc_get_image_size( 'thumbnail' );
                        $post_thumbnail_src = array(
                            wc_placeholder_img_src(),
                            esc_attr( $dimensions['width'] ),
                            esc_attr( $dimensions['height'] )
                        );
                    }

                    $brand_taxonomy = electro_get_brands_taxonomy();
                    if( ! empty( $brand_taxonomy ) ) {
                        $terms = wc_get_product_terms( $id, $brand_taxonomy, array( 'fields' => 'names' ) );
                        if ( $terms && ! is_wp_error( $terms ) ) {
                            $brand_links = array();
                            foreach ( $terms as $term ) {
                                if( isset($term->name) ) {
                                    $brand_links[] = $term->name;
                                }
                            }
                            $brand = join( ", ", $brand_links );
                        }
                    }

                    $results[] = apply_filters( 'electro_live_search_results_args', array(
                        'value'     => $title,
                        'url'       => get_permalink( $id ),
                        'tokens'    => explode( ' ', $title ),
                        'image'     => $post_thumbnail_src[0],
                        'price'     => $price,
                        'brand'     => $brand,
                        'id'        => $id
                    ), $product_object );
                }
            }

            wp_reset_postdata();
            echo json_encode( $results );
        }
        die();
    }
	
}

if( ! function_exists( 'electro_wc_get_product_id' ) ) {
    function electro_wc_get_product_id( $product ) {
        if ( ! ( $product instanceof WC_Product ) ) { return 0; }
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7', '<' ) ) {
            return isset( $product->id ) ? $product->id : 0;
        }

        return $product->get_id();
    }
}

if( ! function_exists( 'electro_wc_get_product_type' ) ) {
    function electro_wc_get_product_type( $product ) {
        if ( ! ( $product instanceof WC_Product ) ) { return 'simple'; }
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7', '<' ) ) {
            return isset( $product->product_type ) ? $product->product_type : 'simple';
        }

        return $product->get_type();
    }
}

if ( ! function_exists( 'electro_get_savings_on_sale' ) ) {
    function electro_get_savings_on_sale( $product, $in = 'amount' ) {
        if ( ! ( $product instanceof WC_Product ) ) { return 0; }
        if( ! $product->is_on_sale() ) {
            return 0;
        }

        if( $product->is_type( 'variable' ) ) {
            $var_regular_price = array();
            $var_sale_price = array();
            $var_diff_price = array();
            $available_variations = $product->get_available_variations();
            foreach ( $available_variations as $key => $available_variation ) {
                $variation_id = $available_variation['variation_id']; // Getting the variable id of just the 1st product. You can loop $available_variations to get info about each variation.
                $variable_product = new WC_Product_Variation( $variation_id );

                $variable_product_regular_price = $variable_product->get_regular_price();
                $variable_product_sale_price = $variable_product->get_sale_price();

                if( ! empty( $variable_product_regular_price ) ) {
                    $var_regular_price[] = $variable_product_regular_price;
                } else {
                    $var_regular_price[] = 0;
                }
                if( ! empty( $variable_product_sale_price ) ) {
                    $var_sale_price[] = $variable_product_sale_price;
                } else {
                    $var_sale_price[] = 0;
                }
            }

            foreach( $var_regular_price as $key => $reg_price ) {
                if( isset( $var_sale_price[$key] ) && $var_sale_price[$key] !== 0 ) {
                    $var_diff_price[] = $reg_price - $var_sale_price[$key];
                } else { 
                    $var_diff_price[] = 0;
                }
            }

            $best_key = array_search( max( $var_diff_price ), $var_diff_price );

            $regular_price = $var_regular_price[$best_key];
            $sale_price = $var_sale_price[$best_key];
        } else {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
        }

        $regular_price = wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $regular_price ) );
        $sale_price = wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $sale_price ) );

        if ( 'amount' === $in ) {

            $savings = wc_price( $regular_price - $sale_price );

        } elseif ( 'percentage' === $in ) {

            $savings = '<span class="percentage">' . round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) . '%</span>';
        }

        return $savings;
    }
}

if ( ! function_exists( 'electro_get_sale_flash' ) ) {
    /**
     * Functions for getting sale flash with sale amount.
     */
    function electro_get_sale_flash( $html, $post, $product ) {
        $html = '<span class="onsale">-' . electro_get_savings_on_sale( $product, 'percentage' ) . '</span>';

        return apply_filters( 'electro_get_sale_flash', $html, $post, $product );
    }
}

if ( ! function_exists( 'electro_structured_data_product' ) ) {
    /**
     * Structured data update for brand.
     */
    function electro_structured_data_product( $markup, $product ) {
        $brand_taxonomy = electro_get_brands_taxonomy();
        if( $brand_taxonomy ) {
            $product_brand = $product->get_attribute( $brand_taxonomy );
            if( ! empty( $product_brand ) ) {
                $markup['brand'] = $product_brand;
            }
        }

        return $markup;
    }
}

/**
 * Track product views.
 */
if( ! function_exists( 'electro_wc_track_product_view' ) ) {
    function electro_wc_track_product_view() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }

        global $post;

        if ( empty( $_COOKIE['electro_wc_recently_viewed'] ) ) {
            $viewed_products = array();
        } else {
            $viewed_products = (array) explode( '|', $_COOKIE['electro_wc_recently_viewed'] );
        }

        if ( ! in_array( $post->ID, $viewed_products ) ) {
            $viewed_products[] = $post->ID;
        }

        if ( sizeof( $viewed_products ) > 15 ) {
            array_shift( $viewed_products );
        }

        // Store for session only
        wc_setcookie( 'electro_wc_recently_viewed', implode( '|', $viewed_products ) );
    }
}

/**
 * Get viewed products
 *
 */
if( ! function_exists( 'electro_get_viewed_products' ) ) {
    function electro_get_viewed_products() {
        
        $viewed_products = ! empty( $_COOKIE['electro_wc_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['electro_wc_recently_viewed'] ) : array();
        $viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );

        return $viewed_products;
    }
}

require get_template_directory() . '/inc/woocommerce/functions/shop-loop.php';

/**
 * Electro Wide. Set Columns Wide attribute
 */
add_action( 'woocommerce_shortcode_before_products_loop',               'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_sale_products_loop',          'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_best_selling_products_loop',  'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_top_rated_products_loop',     'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_product_loop',                'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_product_cat_loop',            'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_product_category_loop',       'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_recent_products_loop',        'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_featured_products_loop',      'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_product_attribute_loop',      'electro_set_columns_wide_loop_prop' );
add_action( 'woocommerce_shortcode_before_related_products_loop',       'electro_set_columns_wide_loop_prop' );

add_filter( 'shortcode_atts_products',              'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_sale_products',         'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_best_selling_products', 'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_top_rated_products',    'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_product',               'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_product_category',      'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_recent_products',       'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_featured_products',     'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_product_attribute',     'electro_add_columns_wide_atts', 10, 4 );
add_filter( 'shortcode_atts_related_products',      'electro_add_columns_wide_atts', 10, 4 );

function electro_set_columns_wide_loop_prop( $attr ) {
    if ( isset( $attr['columns_wide'] ) ) {
        wc_set_loop_prop( 'columns_wide', $attr['columns_wide'] );  
    }
}

function electro_add_columns_wide_atts( $out, $pairs, $atts, $shortcode ) {
    if ( isset( $atts['columns_wide'] ) ) {
        $out['columns_wide'] = $atts['columns_wide'];
    }
    return $out;
}
<?php
defined( 'ABSPATH' ) || exit;
global $product;
?>
<div class="woopb-product-left">
    <div class="woopb-product-image">
		<?php
		$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
		$post_thumbnail_id = $product->get_image_id();
		$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		) );
		?>
        <div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
             data-columns="<?php echo esc_attr( $columns ); ?>"
             style="opacity: 0; transition: opacity .25s ease-in-out;">
            <figure class="woocommerce-product-gallery__wrapper">
				<?php
				if ( $product->get_image_id() ) {
					$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
				} else {
					$html = '<div class="woocommerce-product-gallery__image--placeholder">';
					$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
					$html .= '</div>';
				}
				echo $html;
				//				echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS
				//.EscapeOutput.OutputNotEscaped
				?>
            </figure>
        </div>
    </div>
</div>

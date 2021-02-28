<?php

namespace ZAddons\Frontend;

use ZAddons\Model\Group;
use function ZAddons\get_customize_addon_option;

class Product {
	const DEFAULT_TEMPLATE_PATH = __DIR__ . '/templates';

	public function __construct() {
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'show_product_options' ] );

		add_filter( 'woocommerce_add_cart_item', [ $this, 'add_cart_item' ], 20, 1 );

		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 3 );

		add_action( 'woocommerce_cart_item_restored', [ $this, 'cart_item_restored' ], 10, 2 );

		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 20, 2 );

		add_action( 'woocommerce_new_order_item', [ Product::class, 'order_item_meta' ], 10, 2 );

		add_filter( 'woocommerce_get_item_data', [ Product::class, 'add_item_data' ], 10, 2 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hidden_order_itemmeta' ] );

		add_action( 'admin_init', [ $this, 'add_tab_wc_product' ] );

		add_action( 'woocommerce_process_product_meta', [ $this, 'addons_fields_save' ] );

		add_action( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 1, 2 );

		add_action( 'wp_ajax_nopriv_get_variation_section', [ $this, 'get_variation_section' ] );

		add_action( 'wp_ajax_get_variation_section', [ $this, 'get_variation_section' ] );
	}

	public function show_product_options() {
		global $product;
		if ( is_object( $product ) && $product->get_id() > 0 && ! $product instanceof \WC_Product_Grouped ) {
			$groups = Group::getByProduct( $product );
			if ( 'only_with_add_ons' === get_customize_addon_option( 'zac_display_addons' ) && ! sizeof( $groups ) ) {
				return;
			}

			$path = apply_filters( 'za_template_path', self::DEFAULT_TEMPLATE_PATH );
			foreach ( $groups as $group ) {
				foreach ( $group->types as $type ) {
					if ( self::is_shown_children_options( $type->values ) ) {
						include file_exists( $path . '/type.php' ) ? $path . '/type.php' : self::DEFAULT_TEMPLATE_PATH . '/type.php';
					}
				}
			}

			$add_ons_total_label = get_customize_addon_option( 'zac_line_item_text',
				__( 'Add-ons total', 'product-add-ons-woocommerce' ) );
			$price               = get_option( 'woocommerce_tax_display_shop' ) === 'incl' ? wc_get_price_including_tax( $product ) : wc_get_price_excluding_tax( $product );
			?>
            <input type="hidden" id="zaddon_base_price" value="<?= $price; ?>">
            <input type="hidden" id="zaddon_currency" value="<?= \get_woocommerce_currency(); ?>">
            <input type="hidden" id="zaddon_locale" value="<?= get_locale(); ?>">

            <div class="zaddon_data">
				<?php if ( get_customize_addon_option( 'zac_show_subtotal', true ) ) : ?>
                    <div class="zaddon_subtotal">
                        <h4><?php _e( 'Subtotal', 'product-add-ons-woocommerce' ); ?>:</h4>
                        <span class="woocommerce-Price-amount amount"></span>
                    </div>
				<?php endif; ?>
				<?php if ( get_customize_addon_option( 'zac_show_addons_total', true ) ) : ?>
                    <div class="zaddon_additional">
                        <h4><?= $add_ons_total_label ?>:</h4>
                        <p>+&nbsp;<span class="woocommerce-Price-amount amount"></span></p>
                    </div>
				<?php endif; ?>
				<?php if ( get_customize_addon_option( 'zac_show_total', true ) ) : ?>
                    <div class="zaddon_total">
                        <h4><?php _e( 'Total', 'product-add-ons-woocommerce' ); ?>:</h4>
                        <span class="woocommerce-Price-amount amount"></span>
                    </div>
				<?php endif; ?>
            </div>
			<?php
		}
	}

	public function add_cart_item( $cart_item ) {
		$cart_item = $this->cart_adjust_price( $cart_item );

		return $cart_item;
	}

	public function add_cart_item_data( $cart_item_meta, $product_id, $variation_id, $post_data = null ) {
		if ( is_null( $post_data ) ) {
			$post_data = $_POST;
		}
		$zaddon = isset( $post_data['zaddon'] ) ? $post_data['zaddon'] : array();
		$groups = $variation_id ? Group::getByProduct( $variation_id, false,
			true ) : Group::getByProduct( $product_id );
		if ( count( $groups ) === 0 ) {
			return $cart_item_meta;
		}
		$groupIDs = array_map( function ( $group ) {
			return $group->getID();
		}, $groups );

		$zaddon                           = array_filter( $zaddon, function ( $id ) use ( $groupIDs ) {
			return in_array( intval( $id ), $groupIDs );
		}, ARRAY_FILTER_USE_KEY );
		$zaddon                           = array_map( function ( $group ) {
			return array_map( function ( $type ) {
				list( $type, $should_return_current ) = apply_filters( 'zaddon_cart_item_addon_meta_before_default',
					array( $type, false ) );
				if ( $should_return_current ) {
					return $type;
				}
				switch ( $type['type'] ) {
					case 'select':
					case 'radio':
						$type['value'] = isset( $type['value'] ) ? intval( $type['value'] ) : array();

						return $type;
					case 'checkbox':
						$type['value'] = isset( $type['value'] ) ? array_map( 'intval',
							(array) $type['value'] ) : array();

						return $type;
					case 'text':
					default:
						$type['value'] = array_map( 'esc_sql', wp_unslash( (array) $type['value'] ) );

						return $type;
				}
			}, $group );
		}, $zaddon );
		$cart_item_meta['_zaddon_values'] = json_encode( $zaddon );

		return $cart_item_meta;
	}

	public function cart_adjust_price( $cart_item ) {
		if ( ! isset( $cart_item['_zaddon_values'] ) ) {
			return $cart_item;
		}
		$zaddon = json_decode( $cart_item['_zaddon_values'], true );
		if ( ! empty( $zaddon ) ) {
			$product                         = $cart_item['variation_id'] ? wc_get_product( $cart_item['variation_id'] ) : wc_get_product( $cart_item['product_id'] );
			$groups                          = Group::getByProduct( $product, false, $cart_item['variation_id'] );
			$groups                          = array_map( function ( $group ) {
				return $group->getData();
			}, $groups );
			$price                           = get_option( 'woocommerce_tax_display_shop' ) === 'incl' ? wc_get_price_including_tax( $product ) : wc_get_price_excluding_tax( $product );
			$additional                      = array_reduce( $groups,
				function ( $total, $group ) use ( $zaddon, $price, $cart_item ) {
					$groupAddon = sizeof( $group['types'] ) > 0 && isset( $zaddon[ $group['id'] ] ) ? $zaddon[ $group['id'] ] : [];

					return $groupAddon
						? array_reduce( $group['types'],
							function ( $total, $type ) use ( $groupAddon, $price, $cart_item ) {
								$typeAddon = isset( $groupAddon[ $type['id'] ] ) ? $groupAddon[ $type['id'] ] : [];

								return array_reduce( $type['values'],
									function ( $total, $value ) use ( $typeAddon, $type, $price, $cart_item ) {
										$quantity = self::get_addon_quantity( $typeAddon, $value );
										switch ( $type['type'] ) {
											case 'select':
											case 'radio':
												$selected = $value['id'] === $typeAddon['value'];
												break;
											case 'checkbox':
												$selected = in_array( $value['id'], $typeAddon['value'] );
												break;
											case 'text':
											default:
												$selected = ! empty( $typeAddon['value'][ $value['id'] ] );
										}
										$addon_value = $selected ? ( $quantity > 1 ? $quantity * $value['price'] / $cart_item['quantity'] : $value['price'] ) : 0;
										list( $addon_additional, $should_return_current ) = apply_filters( 'zaddon_calculate_cart_item_addon_additional',
											array( 0, false ), $typeAddon, $type, $price, $addon_value, $value['id'] );
										if ( $should_return_current ) {
											return $total + $addon_additional;
										}

										return $total + $addon_value;
									}, $total );
							}, $total )
						: $total;
				}, 0 );
			$cart_item['_zaddon_additional'] = $additional;

			$cart_item['data']->set_price( $cart_item['data']->get_price() + $additional );
		}

		return $cart_item;
	}

	public function cart_item_restored( $cart_item_key, $cart ) {
		if ( isset( $cart->cart_contents[ $cart_item_key ] ) ) {
			$cart_item = $cart->cart_contents[ $cart_item_key ];
			$cart_item = $this->cart_adjust_price( $cart_item );
		}
	}

	public static function order_item_meta( $item_id, $item ) {
		if ( ! $item instanceof \WC_Order_Item_Product ) {
			return;
		}
		$exist = property_exists( $item, 'legacy_values' );
		if ( $exist ) {
			header( 'typer: legacy' );
			$item_data  = $item->legacy_values;
			$product_id = $item_data['product_id'];
			$zaddon     = $item_data['_zaddon_values'];
			$additional = $item_data['_zaddon_additional'];
		} else {
			header( 'typer: get' );
			$product_id = $item->get_product_id();
			$zaddon     = $item->get_meta( '_zaddon_values' );
			$additional = $item->get_meta( '_zaddon_additional' );
		}
		self::add_meta_to_item( $product_id, $zaddon, $additional, $item );
		if ( $exist ) {
			$item->save();
		}
	}

	public static function add_meta_to_item( $product_id, $zaddon, $additional, $item ) {
		$item->add_meta_data( '_zaddon_values', $zaddon, true );
		$is_variation = isset( $item['variation_id'] ) && $item['variation_id'];
		$product_id   = $is_variation ? $item['variation_id'] : $product_id;
		$zaddon_meta  = self::item_meta( $product_id, $zaddon, $is_variation );
		array_walk( $zaddon_meta, function ( $meta, $key ) use ( $item ) {
			$item->add_meta_data( $key, $meta, true );
		} );
		if ( $additional > 0 && get_customize_addon_option( 'zac_show_additional_line_item', true ) ) {
			$item->add_meta_data( '_zaddon_additional', $additional, true );
			$item->add_meta_data(
				get_customize_addon_option( 'zac_additional_line_item',
					__( 'Additional', 'product-add-ons-woocommerce' ) ),
				wc_price( $additional ),
				true
			);
		}
	}

	public static function add_item_data( $item_data, $cart_item ) {
		if ( ! isset( $cart_item['_zaddon_values'] ) ) {
			return $item_data;
		}
		$product     = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
		$zaddon_meta = self::item_meta( $product, $cart_item['_zaddon_values'], $cart_item['variation_id'] );
		$zaddon_meta = array_map( function ( $display, $key ) {
			$key     = strip_tags( $key );
			$display = strip_tags( $display );

			return compact( 'display', 'key' );
		}, $zaddon_meta, array_keys( $zaddon_meta ) );
		$item_data   = array_merge( $item_data, $zaddon_meta );
		if ( $cart_item['_zaddon_additional'] > 0 && get_customize_addon_option( 'zac_show_additional_line_item',
				true ) ) {
			$item_data[] = [
				'display' => strip_tags( wc_price( $cart_item['_zaddon_additional'] ) ),
				'key'     => get_customize_addon_option( 'zac_additional_line_item',
					__( 'Additional', 'product-add-ons-woocommerce' ) ),
			];
		}

		return $item_data;
	}

	protected static function item_meta( $product_id, $zaddon, $variation = false ) {
		if ( ! $zaddon ) {
			return [];
		}
		$zaddon  = json_decode( $zaddon, true );
		$groups  = Group::getByProduct( $product_id, false, $variation );
		$groups  = array_map( function ( $group ) {
			return $group->getData();
		}, $groups );
		$product = wc_get_product( $product_id );
		$price   = get_option( 'woocommerce_tax_display_shop' ) === 'incl' ? wc_get_price_including_tax( $product ) : wc_get_price_excluding_tax( $product );

		$zaddon_meta = array_reduce( $groups, function ( $groups, $group ) use ( $zaddon, $price ) {
			$groupAddon = sizeof( $group['types'] ) > 0 && isset( $zaddon[ $group['id'] ] ) ? $zaddon[ $group['id'] ] : [];
			$types      = $groupAddon ? array_reduce( $group['types'],
				function ( $types, $type ) use ( $groupAddon, $price ) {
					$typeAddon  = isset( $groupAddon[ $type['id'] ] ) ? $groupAddon[ $type['id'] ] : [];
					$valuesMeta = array_reduce( $type['values'],
						function ( $acc, $value ) use ( $typeAddon, $type, $price ) {
							$usedAddon = false;
							switch ( $type['type'] ) {
								case 'select':
								case 'radio':
									if ( $value['id'] === $typeAddon['value'] ) {
										$acc[ $value['id'] ] = $value['title'] . ( $value['price'] ? ' (' . wc_price( $value['price'] ) . ')' : '' );
										$usedAddon           = true;
									}
									break;
								case 'checkbox':
									if ( in_array( $value['id'], $typeAddon['value'] ) ) {
										$acc[ $value['id'] ] = $value['title'] . ( $value['price'] ? ' (' . wc_price( $value['price'] ) . ')' : '' );
										$usedAddon           = true;
									}
									break;
								case 'text':
								default:
									if ( ! empty( $typeAddon['value'][ $value['id'] ] ) ) {
										$acc[ $value['id'] ] = $value['title'] . ' ' . wp_unslash( $typeAddon['value'][ $value['id'] ] ) . ( $value['price'] ? ' (' . wc_price( $value['price'] ) . ')' : '' );
										$usedAddon           = true;
									}
							}

							list( $acc, $usedAddon ) = apply_filters( 'zaddon_cart_item_addon_label',
								array( $acc, $usedAddon ), $typeAddon, $type, $value, $price );

							if ( $usedAddon ) {
								$acc[ $value['id'] ] = apply_filters( 'zaddons_product_meta_label',
									$acc[ $value['id'] ], $typeAddon, $value );
							}

							return $acc;
						}, [] );

					$keysMeta = array_map( function ( $metaKey ) use ( $type ) {
						return '<span id="' . $metaKey . '">' . $type['title'] . '</div>';
					}, array_keys( $valuesMeta ) );

					return array_merge( $types, array_combine( $keysMeta, $valuesMeta ) );
				}, [] ) : [];

			return array_merge( $groups, $types );
		}, [] );

		return $zaddon_meta;
	}

	public function get_cart_item_from_session( $cart_item, $values ) {
		if ( ! empty( $values['_zaddon_values'] ) ) {
			$cart_item['_zaddon_values']     = $values['_zaddon_values'];
			$cart_item['_zaddon_additional'] = $values['_zaddon_additional'];
			$cart_item                       = $this->add_cart_item( $cart_item );
		}

		return $cart_item;
	}

	public function enqueue_scripts() {
		if ( is_single() && is_product() ) {
			wp_enqueue_script( 'za_product.js',
				plugins_url( 'assets/scripts/product.js', \ZAddons\PLUGIN_ROOT_FILE ), [ 'jquery' ], '1.3' );
			wp_localize_script( 'za_product.js', 'ZAddons', [
				'numberOfDecimals'   => wc_get_price_decimals(),
				'displayProductLine' => get_customize_addon_option( 'zac_display_addons' ),
				'adminAjax'          => admin_url( 'admin-ajax.php' ),
			] );
			wp_enqueue_style( 'za_product.css',
				plugins_url( 'assets/styles/product.css', \ZAddons\PLUGIN_ROOT_FILE ), '1.0' );
		}
	}

	public function hidden_order_itemmeta( $meta ) {
		$meta[] = '_zaddon_additional';
		$meta[] = '_zaddon_values';

		return $meta;
	}

	public function add_tab_wc_product() {
		if ( isset( $_GET['zmodal'] ) && $_GET['zmodal'] === 'true' ) {
			add_filter( 'admin_body_class', function ( $classes ) {
				return $classes . ' zmodal';
			} );
			wp_enqueue_style( 'za_admin.css', plugins_url( 'assets/styles/admin.css', \ZAddons\PLUGIN_ROOT_FILE ),
				[], '1.0' );
		}
		if ( ! is_admin( 'post.php' ) && get_post_type() !== "product" && ! get_the_ID() ) {
			return;
		}

		add_action( 'woocommerce_product_data_tabs', function ( $product_data_tabs ) {
			$product_data_tabs['zaddon-product-options'] = [
				'label'  => __( 'Product Add-Ons', 'product-add-ons-woocommerce' ),
				'target' => 'product_zaddons',
			];

			return $product_data_tabs;
		} );

		add_action( 'wp_ajax_zaddon_save_group', [ $this, 'ajax_save_group' ] );

		add_action( 'woocommerce_product_data_panels', function () {
			global $post;
			$this->wc_tab_addons( $post );
		} );

		add_action( 'admin_footer', function () {
			global $post; ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $("#zaddon_group_name").val("");
                    $("#product_zaddons").on("click", "#zaddon_submit", function (e) {
                        e.preventDefault();
                        var data = {
                            "action": "zaddon_save_group",
                            "group_name": $("#zaddon_group_name").val(),
                            "post_id": <?= isset( $post->ID ) ? $post->ID : 0; ?>
                        };
                        $.post(ajaxurl, data, function (response) {
                            $("#product_zaddons").html($(response).html());
                        });
                    });
                });
            </script><?php
		} );
	}

	public function wc_tab_addons( $post ) {
		$product = new \WC_Product( $post );
		$id      = $product->get_id();
		$groups  = Group::getByProduct( $product, true );
		?>

        <div id="product_zaddons" class="panel woocommerce_options_panel">
            <div class="options_group" style="padding: 10px">
                <label><?php _ex( 'Name', 'Option name', 'product-add-ons-woocommerce' ); ?></label>
                <input
                        name="zaddon_group_name"
                        id="zaddon_group_name"
                        placeholder="<?php _e( 'Group name', 'product-add-ons-woocommerce' ); ?>"
                        value=""
                        style="width: 200px;"
                        type="text">
                <input
                        id="zaddon_submit"
                        class="button button-primary"
                        value="<?php _e( 'Add Group', 'product-add-ons-woocommerce' ); ?>"
                        type="button">
            </div>

			<?php if ( ! empty( $groups ) ) { ?>
                <div class="options_group">
                    <table class="wp-list-table widefat fixed striped posts" style="border: 0">
                        <tbody>
						<?php foreach ( $groups as $group ) { ?>
                            <tr class="no-items">
                                <td>
                                    <span class="dashicons dashicons-exerpt-view"
                                          style="margin: 5px 5px 0px 0px;"></span>
                                    <strong style="margin: 5px 5px 0px 0px; min-height: 20px; display: inline-block;">
										<?= $group->title; ?>
                                    </strong>
                                </td>
                                <td style="text-align: right; width: 60px">
                                    <a href="<?= add_query_arg( [
										'zmodal'    => 'true',
										'KeepThis'  => 'true',
										'TB_iframe' => 'true',
										'width'     => 755,
										'height'    => 340
									], $group->getLink() ); ?>" onclick="return false;" class="thickbox button">
										<?php _e( 'Edit', 'product-add-ons-woocommerce' ); ?>
                                    </a></td>
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
			<?php } ?>

            <div class="options_group">
                <p class="form-field">
                    <label for="_zaddon_disable_global"><?php _e( 'Disable globals',
							'product-add-ons-woocommerce' ); ?></label>
                    <input
                            class="checkbox"
                            name="_zaddon_disable_global"
                            id="_zaddon_disable_global"
                            value="yes"
                            type="checkbox"
						<?php checked( 'yes', get_post_meta( $id, '_zaddon_disable_global', true ) ) ?>
                    >
                    <span class="description">
						<?php _e( 'Check this box if you want to disable global groups and use the above ones only!',
							'product-add-ons-woocommerce' ); ?>
					</span>
                </p>
            </div>
        </div>
		<?php
	}

	public function has_values( $values ) {
		return array_reduce( $values, function ( $has_values, $value ) {
			return $has_values || ! empty( $value );
		}, false );
	}

	public function add_to_cart_validation( $status, $product_id ) {
		$product_item = wc_get_product( $product_id );

		if ( is_object( $product_item ) && $product_item->get_id() > 0 ) {
			$is_variation = isset( $_POST['variation_id'] );
			$product_id   = $is_variation ? $_POST['variation_id'] : $product_id;
			$groups       = Group::getByProduct( $product_id, false, $is_variation );

			foreach ( $groups as $group ) {
				foreach ( $group->types as $type ) {
					if ( ! self::is_shown_children_options( $type->values ) ) {
						continue;
					}
					$option_values = $_POST['zaddon'][ $group->getID() ][ $type->getID() ];

					if ( $type->required && 'enable' === $type->status && (
                            ( ! isset( $option_values['value'] ) || empty( $option_values['value'] ) )
                            || ( $type->type === 'text' && $type->required && ! $this->has_values( $option_values['value'] ) )
                    ) ) {
						wc_add_notice(
							sprintf(
								__( 'Option %s is required', 'product-add-ons-woocommerce' ),
								$type->title
							),
							'error' );
						$status = false;
					}
				}
			}
		}

		return $status;
	}


	public function ajax_save_group() {
		$name    = esc_sql( $_POST['group_name'] );
		$post_id = intval( $_POST['post_id'] );
		$post    = get_post( $post_id );
		if ( $post ) {
			$group           = new Group();
			$group->title    = $name;
			$group->products = [ $post_id ];
			$group->apply_to = 'custom';
			$group->save();
		}
		$this->wc_tab_addons( $post );
		exit;
	}

	public function addons_fields_save( $post_id ) {
		// Checkbox
		$woocommerce_checkbox = isset( $_POST['_zaddon_disable_global'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_zaddon_disable_global', $woocommerce_checkbox );
	}

	public static function get_addon_quantity( $typeAddon, $value ) {
		return isset( $typeAddon['quantity'][ $value['id'] ] ) ? $typeAddon['quantity'][ $value['id'] ] : 1;
	}

	public static function is_shown_children_options( $options ) {
		return array_reduce( $options, function ( $exists, $option ) {
			return $exists || ! $option->hide;
		}, false );
	}

	public function get_variation_section() {
		$variation_id = $_GET['variation_id'];
		$applied_ids  = $_GET['applied_ids'];
		$product      = wc_get_product( $variation_id );
		$groups       = Group::getByProduct( $product );

		if ( 'only_with_add_ons' === get_customize_addon_option( 'zac_display_addons' ) && ! sizeof( $groups ) ) {
			return;
		}
		ob_start();

		foreach ( $groups as $group ) {
			foreach ( $group->types as $type ) {
				if ( ! in_array( $type->getId(), $applied_ids ) && self::is_shown_children_options( $type->values ) ) {
					include __DIR__ . '/templates/type.php';
				}
			}
		}
		echo json_encode( array( 'section' => ob_get_clean() ) );
		die();
	}
}

<?php

namespace ZAddons\Admin;

use ZAddons\Addons;
use ZAddons\Admin;
use ZAddons\Model\Group;
use ZAddons\Model\Type;
use ZAddons\Model\Value;
use const ZAddons\REST_NAMESPACE;
use const ZAddons\PLUGIN_ROOT_FILE;

class SingleGroup {
	private $group_page;

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 1000 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
	}

	public function admin_menu() {
		$this->group_page = add_submenu_page(
			null,
			__( 'Create group', 'product-add-ons-woocommerce' ),
			__( 'Create group', 'product-add-ons-woocommerce' ),
			'manage_woocommerce',
			'za_group',
			[ $this, 'process' ]
		);
	}

	public function process() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->update();
		} else {
			$this->render();
		}
	}

	public function admin_scripts() {
		if ( get_current_screen()->base === $this->group_page ) {
			wp_enqueue_media();
			wp_enqueue_script( 'za_group', plugins_url( 'assets/scripts/adminGroup.js', \ZAddons\PLUGIN_ROOT_FILE ),
				[ 'zAddons', 'wc-enhanced-select', 'wp-i18n', 'media-upload' ], '1.0' );
			wp_localize_script( 'za_group', 'ZAddons', [
				'SITE_URL'               => esc_url_raw( get_site_url() ),
				'isCheckoutAddOnActive'  => Addons::is_active_add_on( Addons::CHECKOUT_ADDON_NAMESPACE ),
				'isCustomizeAddOnActive' => Addons::is_active_add_on( Addons::CUSTOMIZE_ADDON_NAMESPACE ),
				'taxSlugs'               => \WC_Tax::get_tax_class_slugs(),
			] );
			wp_set_script_translations( 'za_group', 'product-add-ons-woocommerce',
				plugin_dir_path( PLUGIN_ROOT_FILE ) . 'lang' );
		}
	}

	protected function render() {
		$data           = isset( $_GET ) && isset( $_GET['id'] ) ? Group::getByID( intval( $_GET['id'] ) )->getData( true ) : [];
		$data['zmodal'] = isset( $_GET['zmodal'] ) ? 'true' : 'false';
		$categories     = $this->getCategories();
		$page_data      = compact( 'data', 'categories' );
		?>
        <div class="wrap">
            <h1 class="nav-tab-wrapper woo-nav-tab-wrapper">
                <a href="<?= Admin::getUrl( 'groups' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Groups', 'product-add-ons-woocommerce' ); ?>
                </a>
            </h1>
            <div id="react-root"></div>
            <script>
                renderGroup(<?php echo json_encode( $page_data ); ?>, document.getElementById("react-root"));
            </script>
        </div>
		<?php
	}

	protected function update() {
		$data  = stripslashes_deep( $_POST );
		$group = ( $id = filter_var( $data['id'], FILTER_VALIDATE_INT ) )
			? Group::getByID( $id )
			: new Group();

		if ( $data['delete'] ) {
			$group->delete();
			header( 'Location: ' . Admin::getUrl( 'groups' ) );
			exit();
		}

		$group->title    = esc_sql( $data['title'] );
		$group->priority = filter_var( $data['priority'], FILTER_VALIDATE_INT );
		$group->apply_to = esc_sql( $data['apply_to'] );
		if ( $group->apply_to === "all" ) {
			$group->products   = [];
			$group->categories = [];
		} else {
			$group->products   = array_map( 'intval', (array) $data['products'] );
			$group->categories = array_map( 'intval', (array) $data['categories'] );
		}

		$types = array_values( (array) $data['types'] );

		$group->types = array_map( function ( $typeData ) use ( $group ) {
			if ( $typeData['id'] ) {
				$type = $group->types[ $typeData['id'] ];
			} else {
				$type = new Type();
			}
			$type->type                             = $typeData['type'];
			$type->values_type                      = $typeData['values_type'];
			$type->status                           = $typeData['status'];
			$type->accordion                        = $typeData['accordion'];
			$type->step                             = $typeData['step'];
			$type->title                            = $typeData['title'];
			$type->required                         = boolval( $typeData['required'] );
			$type->hide_description                 = boolval( $typeData['hide_description'] );
			$type->display_description_on_expansion = boolval( $typeData['display_description_on_expansion'] );
			$type->description                      = $typeData['description'];

			$values = array_values( (array) $typeData['values'] );

			$type->values = array_map( function ( $valueData ) use ( $type ) {
				if ( $valueData['id'] ) {
					$value = $type->values[ $valueData['id'] ];
				} else {
					$value = new Value();
				}

				$value->price            = floatval( $valueData['price'] );
				$value->step             = $valueData['step'];
				$value->hide             = $valueData['hide'];
				$value->hide_description = $valueData['hide_description'];
				$value->title            = $valueData['title'];
				$value->checked          = boolval( $valueData['checked'] );
				$value->description      = $valueData['description'];
				$value->tax_status       = $valueData['tax_status'];
				$value->tax_class        = $valueData['tax_class'];
				$value->addons_options   = apply_filters( 'zaddons_update_addons_options', [], $valueData );

				return $value;
			}, $values );

			return $type;
		}, $types );

		$group->save();

		$link = $group->getLink();

		if ( isset( $data['zmodal'] ) && $data['zmodal'] === "true" ) {
			$link = add_query_arg( 'zmodal', 'true', $link );
		}

		header( 'Location: ' . $link );
		exit();
	}

	protected function getCategories() {
		$all_terms = get_terms( [
			'taxonomy'     => 'product_cat',
			'hierarchical' => true,
			'childless'    => false,
		] );
		$all_terms = array_map( function ( $term ) {
			$el         = new \stdClass();
			$el->id     = $term->term_id;
			$el->name   = $term->name;
			$el->parent = $term->parent;

			return $el;
		}, $all_terms );

		return $this->getChildCategories( $all_terms, 0 );
	}

	protected function getChildCategories( $all, $term_id ) {
		$root_terms = array_filter( $all, function ( $term ) use ( $term_id ) {
			return isset( $term->parent ) && $term->parent === $term_id;
		} );

		return array_values( array_map( function ( $term ) use ( $all ) {
			$term->child = $this->getChildCategories( $all, $term->id );
			unset( $term->parent );

			return $term;
		}, $root_terms ) );
	}
}

<?php

namespace ZAddons;

class Setup
{
	public function __construct()
	{
			new Translation();
		new Activate();
		add_action('plugins_loaded', [$this, 'init'], 10);
	}

	public function init()
	{
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', function () {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php _e('Product Add-Ons WooCommerce require WooCommerce', 'product-add-ons-woocommerce'); ?></p>
				</div>
				<?php
			});
			return;
		}
		add_action( 'zaddon_get_plugin_path', function () {
				return PLUGIN_ROOT;
		});

		add_action( 'zaddon_get_plugin_url', function () {
				return plugins_url('', \ZAddons\PLUGIN_ROOT_FILE);
		});

		require_once PLUGIN_ROOT . '/includes/functions.php';

		new Scripts();
		new API();
		new Admin();
		new Addons();
		new Frontend();
	}
}

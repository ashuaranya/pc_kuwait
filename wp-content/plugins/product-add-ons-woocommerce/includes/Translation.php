<?php

namespace ZAddons;

class Translation
{
		public function __construct()
		{
				load_plugin_textdomain(
					'product-add-ons-woocommerce',
					false,
					dirname(plugin_basename(PLUGIN_ROOT_FILE)) . '/lang/'
				);
		}
}

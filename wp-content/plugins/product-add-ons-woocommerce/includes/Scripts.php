<?php

namespace ZAddons;

class Scripts
{
	public function __construct()
	{
		add_action('admin_enqueue_scripts', function () {
			wp_register_script('zAddons', null);
			wp_localize_script('zAddons', 'zAddons', [
				'publicPath' => plugin_dir_url(PLUGIN_ROOT_FILE) . "assets" . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
			]);
		});
	}
}

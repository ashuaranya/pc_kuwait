<?php
namespace ZAddons;

class Activate
{
		public function __construct()
		{
				register_activation_hook(PLUGIN_ROOT_FILE, function () {
						DB::db_activate();
						do_action('zaddon_on_activate');
				});

				add_action('upgrader_process_complete', function () {
						DB::check_new_tables();
				}, 10, 2);

				register_deactivation_hook(PLUGIN_ROOT_FILE, function () {
						if (get_customize_addon_option('zac_delete_data')) {
								do_action('zaddon_on_delete_data');
								DB::drop_tables();
						}
				});
		}
}

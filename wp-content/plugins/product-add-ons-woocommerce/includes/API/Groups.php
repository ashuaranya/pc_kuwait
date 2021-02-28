<?php

namespace ZAddons\API;

use WP_REST_Server, WC_REST_Controller;
use ZAddons\Model\Group;
use const ZAddons\REST_NAMESPACE;

class Groups extends WC_REST_Controller
{
		protected $namespace = REST_NAMESPACE;
		protected $rest_base = 'groups/(?P<id>[\w-]+)';

		public function __construct()
		{
				do_action(__METHOD__, $this, $this->namespace, $this->rest_base);
		}

		public function register_routes()
		{
				do_action(__METHOD__, $this, $this->namespace, $this->rest_base);

				register_rest_route($this->namespace, '/' . $this->rest_base, array(
					array(
						'methods' => WP_REST_Server::DELETABLE,
						'callback' => array($this, 'delete_group'),
						'permission_callback' => array($this, 'delete_groups_permissions_check'),
					),
				));
		}

		public function delete_group($request)
		{
				$success = false;
				$id = $request['id'];
				$group = Group::getByID($id);
				if ($group) {
						$group->delete();
						$success = true;
				}
				return ['success' => $success];
		}

		public function delete_groups_permissions_check($request)
		{
				return current_user_can('manage_woocommerce');
		}
}

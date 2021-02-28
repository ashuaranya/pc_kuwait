<?php

namespace ZAddons\Model;

use ZAddons\DB;

class Group
{
	private $id;
	public $title;
	public $priority;
	public $apply_to;
	protected $created_at = null;
	protected $created_at_gmt = null;
	protected $updated_at = null;
	protected $updated_at_gmt = null;
	public $types = [];
	protected $initialTypes = [];
	protected $categories = [];
	protected $products = [];

	public function __construct($data = null)
	{
		if ($id = filter_var($data, FILTER_VALIDATE_INT)) {
			global $wpdb;
			$prefix = $wpdb->prefix . DB::Prefix;

			$groups = $prefix . DB::Groups;
			$c2g = $prefix . DB::Categories2Groups;
			$p2g = $prefix . DB::Products2Groups;

			$data = $wpdb->get_row(
				$wpdb->prepare(
					"
			SELECT g.*, GROUP_CONCAT(DISTINCT c.category_id) as `categories`, GROUP_CONCAT(DISTINCT p.product_id) as `products` 
				FROM ${groups} as g
			LEFT JOIN ${c2g} as c ON c.group_id = g.id
			LEFT JOIN ${p2g} as p ON p.group_id = g.id
			WHERE g.id = %d
			",
					$id
				)
			);
		}

		if (is_object($data)) {
			$this->id = intval($data->id);
			$this->title = strval($data->title);
			$this->priority = intval($data->priority);
			$this->apply_to = strval($data->apply_to);
			$this->author = intval($data->author);
			$this->created_at = strtotime($data->created_at);
			$this->created_at_gmt = strtotime($data->created_at_gmt);
			$this->updated_at = strtotime($data->updated_at);
			$this->updated_at_gmt = strtotime($data->updated_at_gmt);
			$this->categories = $data->categories === null
				? []
				: array_map('intval', explode(',', $data->categories));
			$this->products = $data->products === null
				? []
				: array_map('intval', explode(',', $data->products));

			$this->types = Type::getByGroupID($this->id);
			$this->initialTypes = $this->types;
		}
	}

	public function delete()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$groups = $prefix . DB::Groups;

		if ($this->id) {
			array_map(function (Type $type) {
				$type->delete();
			}, array_merge($this->types, $this->initialTypes));
			$wpdb->delete($groups, ['id' => $this->id], ['%d']);
		}

		$this->id = null;

		return null;
	}

	public function save()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$groups = $prefix . DB::Groups;
		$c2g = $prefix . DB::Categories2Groups;
		$p2g = $prefix . DB::Products2Groups;

		if ($this->id) {
			$wpdb->update(
				$groups,
				[
					'title' => $this->title,
					'priority' => $this->priority,
					'apply_to' => $this->apply_to,
					'updated_at' => current_time('mysql'),
					'updated_at_gmt' => current_time('mysql', 1),
				],
				['id' => $this->id],
				['%s', '%d', '%s', '%s', '%s'],
				['%d']
			);

			array_map(function (Type $type) {
				$type->delete();
			}, array_diff_key($this->initialTypes, Type::formatResults($this->types)));
		} else {
			$wpdb->insert(
				$groups,
				[
					'author' => wp_get_current_user()->ID,
					'title' => $this->title,
					'priority' => $this->priority,
					'apply_to' => $this->apply_to,
					'created_at' => current_time('mysql'),
					'created_at_gmt' => current_time('mysql', 1),
					'updated_at' => current_time('mysql'),
					'updated_at_gmt' => current_time('mysql', 1),
				],
				['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
			);

			$this->id = $wpdb->insert_id;
		}

		if ($this->apply_to === "all") {
			$this->products = [];
			$this->categories = [];
		}

		$tables = [
			['table' => $p2g, 'id' => 'product_id', 'data' => $this->products],
			['table' => $c2g, 'id' => 'category_id', 'data' => $this->categories],
		];

		array_map(function ($index) use ($wpdb) {
			$table = $index['table'];
			$id = $index['id'];
			$data = $index['data'];
			$wpdb->query($wpdb->prepare("DELETE FROM ${table} WHERE group_id = %d", $this->id));

			array_map(function ($el) use ($wpdb, $table, $id) {
				$wpdb->insert($table, [$id => $el, 'group_id' => $this->id], ['%d', '%d']);
			}, $data);
		}, $tables);

		$types = array_map(function (Type $type) {
			if (!$type->getGroupID()) {
				$type->setGroupId($this->id);
			}
			$type->save();
			return $type;
		}, $this->types);

		$this->types = Type::formatResults($types);

		$this->initialTypes = $this->types;
	}

	public function getID()
	{
		return $this->id;
	}

	public function getLink()
	{
		return add_query_arg([
			'post_type' => 'product',
			'page' => 'za_group',
			'id' => $this->id,
		], admin_url('edit.php'));
	}

	public function __get($name)
	{
		switch ($name) {
			case 'id':
				return $this->getID();
			case 'link':
				return $this->getLink();
			case 'categories':
				return $this->getCategories();
			case 'products':
				return $this->getProducts();
		}
	}

	public function getCategories()
	{
		$categories = array_map(function ($category) {
			$taxonomy = 'product_cat';
			$link = add_query_arg([
				'post_type' => 'product',
				'taxonomy' => $taxonomy,
				'tag_ID' => $category,
			], admin_url('term.php'));
			$term = get_term($category, $taxonomy);
			if ($term === null) {
				return null;
			}
			$name = $term->name;
			$id = $category;
			return compact('name', 'link', 'id');
		}, $this->categories);

		return array_filter($categories, 'boolval');
	}

	public function getProducts()
	{
		$products = array_map(function ($product) {
			$link = add_query_arg(['action' => 'edit', 'post' => $product], admin_url('post.php'));
			$post = get_post($product);
			if ($post === null) {
				return null;
			}
			$name = get_post($product)->post_title;
			$id = $product;
			return compact('name', 'link', 'id');
		}, $this->products);

		return array_filter($products, 'boolval');
	}

	public function __set($name, $value)
	{
		switch ($name) {
			case 'categories':
				return $this->categories = $value;
			case 'products':
				return $this->products = $value;
		}
	}

	public function getData()
	{
		return [
			'id' => $this->getID(),
			'title' => $this->title,
			'priority' => $this->priority,
			'apply_to' => $this->apply_to,
			'link' => $this->getLink(),
			'categories' => $this->getCategories(),
			'products' => $this->getProducts(),
			'types' => array_values(array_map(function (Type $type) {
				return $type->getData();
			}, $this->types)),
		];
	}

	public function getDataAPI()
	{
		return [
			'id' => $this->getID(),
			'title' => $this->title,
			'priority' => $this->priority,
			'apply_to' => $this->apply_to,
            'categories' => $this->categories,
			'products' => $this->products,
			'types' => array_values(array_map(function (Type $type) {
				return $type->getData();
			}, $this->types)),
		];
	}

	public static function getByID($id)
	{
		return new self($id);
	}

	public static function getAll()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Groups;
		$c2g = $prefix . DB::Categories2Groups;
		$p2g = $prefix . DB::Products2Groups;

		$data = $wpdb->get_results(
			"
			SELECT g.*, GROUP_CONCAT(DISTINCT c.category_id) as `categories`, GROUP_CONCAT(DISTINCT p.product_id) as `products` 
				FROM ${table} as g
			LEFT JOIN ${c2g} as c ON c.group_id = g.id
			LEFT JOIN ${p2g} as p ON p.group_id = g.id
			GROUP BY g.id
			ORDER BY g.priority ASC
		"
		);

		return array_map(function ($el) {
			return new self($el);
		}, $data);
	}

	public static function getByProduct($product, $single = false, $variation = false)
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Groups;
		$c2g = $prefix . DB::Categories2Groups;
		$p2g = $prefix . DB::Products2Groups;

		if (!$product instanceof \WC_Product && !$product instanceof \WC_Product_Variation) {
            $id = $product;
            $product = $variation ? new \WC_Product_Variation($product) : new \WC_Product($product);

        } else {
            $id = $product->get_id();
        }

		$related_ids = [$id];
		if ($variation) {
		    array_push($related_ids, $product->get_parent_id());
        }
        $related_ids = '(' . implode(',', $related_ids) . ')';

        $single = get_post_meta($id, '_zaddon_disable_global', true) === "yes";

		if ($single) {
			$query = "
				SELECT
					g.* , GROUP_CONCAT(DISTINCT c.category_id) as `categories`, GROUP_CONCAT(DISTINCT p.product_id) as `products`
				FROM {$table} g
					INNER JOIN {$p2g} AS p ON (p.group_id = g.id)
					LEFT JOIN {$p2g} pf ON (pf.group_id = g.id AND p.product_id <> pf.product_id)
					LEFT JOIN {$c2g} c ON (c.group_id = g.id)
				WHERE pf.product_id IS NULL AND c.group_id IS NULL AND p.product_id IN {$related_ids}
				GROUP BY g.id
				ORDER BY g.priority ASC 
			";
		} else {
		    $product = $product->is_type('variation') ? wc_get_product($product->get_parent_id()) : $product;
			$categories = $product->get_category_ids();
			$categories = array_map('intval', $categories);
			$categories = '(' . implode(',', $categories) . ')';
			$query = "
				SELECT g.*, GROUP_CONCAT(DISTINCT c.category_id) as `categories`, GROUP_CONCAT(DISTINCT p.product_id) as `products` 
					FROM {$table} as g
				LEFT JOIN {$c2g} as c ON c.group_id = g.id
				LEFT JOIN {$p2g} as p ON p.group_id = g.id
					WHERE c.category_id IN {$categories} OR p.product_id IN {$related_ids} OR g.apply_to = 'all'
				GROUP BY g.id
				ORDER BY g.priority ASC
			";
		}

		$data = $wpdb->get_results($query);

		return array_map(function ($el) {
			return new self($el);
		}, $data);
	}
}

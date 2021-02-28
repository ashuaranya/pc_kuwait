<?php
namespace ZAddons\Model;

use ZAddons\DB;

class Value
{
	private $id;
	private $type_id;

	public $title;
	public $step;
	public $price = 0;
	public $description;
	public $hide;
	public $hide_description;
	public $checked;
	public $tax_status;
	public $tax_class;
	public $addons_options = [];

	protected $created_at = null;
	protected $created_at_gmt = null;
	protected $updated_at = null;
	protected $updated_at_gmt = null;

	public function __construct($data = null)
	{
		if ($id = filter_var($data, FILTER_VALIDATE_INT)) {
			global $wpdb;
			$prefix = $wpdb->prefix . DB::Prefix;

			$values = $prefix . DB::Values;

			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ${values} WHERE id = %d", $id));
		}

		if (is_object($data)) {
			$this->id = intval($data->id);
			$this->type_id = intval($data->type_id);

			$this->title = strval($data->title);
			$this->step = intval($data->step);
			$this->price = floatval($data->price);
			$this->description = strval($data->description);
			$this->hide = boolval($data->hide);
			$this->hide_description = boolval($data->hide_description);
			$this->checked = boolval($data->checked);
			$this->tax_status = strval($data->tax_status);
			$this->tax_class = strval($data->tax_class);
			$this->addons_options = apply_filters('zaddons_update_addons_options', [], $data);
			$this->created_at = strtotime($data->created_at);
			$this->created_at_gmt = strtotime($data->created_at_gmt);
			$this->updated_at = strtotime($data->updated_at);
			$this->updated_at_gmt = strtotime($data->updated_at_gmt);
		}
	}

	public function setTypeID($type_id)
	{
		if ($this->type_id) {
			throw new \Exception(__('Type Id is already applied', 'product-add-ons-woocommerce'));
		}

		$this->type_id = $type_id;
	}

	public function getTypeID()
	{
		return $this->type_id;
	}

	public function getData()
	{
		$data = [
			'id' => $this->id,
			'title' => $this->title,
			'step' => $this->step,
			'price' => $this->price,
			'description' => $this->description,
			'hide' => $this->hide,
			'hide_description' => $this->hide_description,
			'checked' => $this->checked,
			'tax_status' => $this->tax_status,
			'tax_class' => $this->tax_class,
		];

		return apply_filters('zaddons_update_addons_options', $data, $this->addons_options);
	}

	public function getID()
	{
		return $this->id;
	}

	public static function getByID($id)
	{
		return new self($id);
	}

	public static function getByTypeID($typeID)
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		$data = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM ${table} WHERE type_id = %d ORDER BY step ASC", $typeID)
		);

		$data = array_map(function ($el) {
			return new self($el);
		}, $data);

		return self::formatResults($data);
	}

	public static function formatResults($results)
	{
		$ids = array_map(function (self $result) {
			return $result->getID();
		}, $results);

		return array_combine($ids, $results);
	}

	public function delete()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		if ($this->id) {
			$wpdb->delete($table, ['id' => $this->id], ['%d']);
		}
		
		$this->id = null;

		return null;
	}

	public function save()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		$data = [
			'title' => $this->title,
			'description' => $this->description,
			'hide' => intval($this->hide),
			'hide_description' => intval($this->hide_description),
			'checked' => intval($this->checked),
			'step' => $this->step,
			'price' => $this->price,
			'tax_status' => $this->tax_status,
			'tax_class' => $this->tax_class,
			'updated_at' => current_time('mysql'),
			'updated_at_gmt' => current_time('mysql', 1),
		];

		$format = ['%s', '%s', '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s', '%s'];

		list($data, $format) = apply_filters('zaddons_update_db_query_params', array($data, $format), $this->addons_options);

		if ($this->id) {
			$wpdb->update(
				$table,
				$data,
				['id' => $this->id],
				$format,
				['%d']
			);
		} else {
			if (!$this->type_id) {
				throw new \Exception("Type Id empty");
			}
			$data['type_id'] = $this->type_id;
			$data['created_at'] = current_time('mysql');
			$data['created_at_gmt'] = current_time('mysql', 1);

			$format[] = '%d';
			$format[] = '%s';
			$format[] = '%s';

			$wpdb->insert(
				$table,
				$data,
				$format
			);
			$this->id = $wpdb->insert_id;
		}
	}
}

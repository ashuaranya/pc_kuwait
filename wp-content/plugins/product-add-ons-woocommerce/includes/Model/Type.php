<?php
namespace ZAddons\Model;

use ZAddons\DB;

class Type
{
	private $id;
	private $group_id;

	public $title;
	public $step;
	public $type;
	public $values_type;
	public $status;
	public $accordion;
	public $required;
	public $description;
	public $hide_description;
	public $display_description_on_expansion;
	public $values = [];
	public $initialValues = [];

	protected $created_at = null;
	protected $created_at_gmt = null;
	protected $updated_at = null;
	protected $updated_at_gmt = null;

	public function __construct($data = null)
	{
		if ($id = filter_var($data, FILTER_VALIDATE_INT)) {
			global $wpdb;
			$prefix = $wpdb->prefix . DB::Prefix;

			$types = $prefix . DB::Types;

			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ${types} WHERE id = %d", $id));
		}

		if (is_object($data)) {
			$this->id = intval($data->id);
			$this->title = strval($data->title);
			$this->step = intval($data->step);
			$this->type = strval($data->type);
			$this->values_type = isset($data->values_type) ? strval($data->values_type) : null;
            $this->accordion = strval($data->accordion);
			$this->status = strval($data->status);
			$this->description = strval($data->description);
			$this->hide_description = boolval($data->hide_description);
			$this->display_description_on_expansion = boolval($data->display_description_on_expansion);
			$this->group_id = intval($data->group_id);
			$this->required = boolval($data->required);
			$this->created_at = strtotime($data->created_at);
			$this->created_at_gmt = strtotime($data->created_at_gmt);
			$this->updated_at = strtotime($data->updated_at);
			$this->updated_at_gmt = strtotime($data->updated_at_gmt);

			$this->values = Value::getByTypeID($this->id);
			$this->initialValues = $this->values;
		}
	}

	public function setGroupID($group_id)
	{
		if ($this->group_id) {
			throw new \Exception(__('Group Id already applied', 'product-add-ons-woocommerce'));
		}

		$this->group_id = $group_id;
	}

	public function getGroupID()
	{
		return $this->group_id;
	}

	public function getData()
	{
		$data = [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'hide_description' => $this->hide_description,
			'display_description_on_expansion' => $this->display_description_on_expansion,
			'step' => $this->step,
			'type' => $this->type,
			'values_type' => $this->values_type,
			'accordion' => $this->accordion,
			'status' => $this->status,
			'required' => $this->required,
			'values' => array_values(array_map(function ($value) {
				return $value->getData();
			}, $this->values)),
		];

		return $data;
	}

	public function getID()
	{
		return $this->id;
	}

	public static function getByID($id)
	{
		return new self($id);
	}

	public static function getByGroupID($groupID)
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Types;

		$data = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM ${table} WHERE group_id = %d GROUP BY step ASC", $groupID)
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

		$table = $prefix . DB::Types;

		if ($this->id) {
			array_map(function (Value $type) {
				$type->delete();
			}, array_merge($this->values, $this->initialValues));
			$wpdb->delete($table, ['id' => $this->id], ['%d']);
		}

		$this->id = null;

		return null;
	}

    public function save()
    {
        global $wpdb;
        $prefix = $wpdb->prefix . DB::Prefix;

        $table = $prefix . DB::Types;

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'hide_description' => $this->hide_description,
            'display_description_on_expansion' => $this->display_description_on_expansion,
            'required' => intval($this->required),
            'step' => $this->step,
            'type' => $this->type,
            'accordion' => $this->accordion,
            'status' => $this->status,
            'updated_at' => current_time('mysql'),
            'updated_at_gmt' => current_time('mysql', 1),
        ];
        $format = ['%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s'];

        list($data, $format) = apply_filters('zaddons_update_db_type_query_params', array($data, $format), $this);

        if ($this->id) {
            $wpdb->update(
                $table,
                $data,
                ['id' => $this->id],
                ['%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );

            array_map(function (Value $type) {
                $type->delete();
            }, array_diff_key($this->initialValues, Value::formatResults($this->values)));
        } else {
            if (!$this->group_id) {
                throw new \Exception(__('Group Id is empty', 'product-add-ons-woocommerce'));
            }
            $data['group_id'] = $this->group_id;
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

        $values = array_map(function (Value $value) {
            if (!$value->getTypeID()) {
                $value->setTypeID($this->id);
            }
            $value->save();
            return $value;
        }, $this->values);

        $this->values = Value::formatResults($values);

        $this->initialValues = $this->values;
    }
}

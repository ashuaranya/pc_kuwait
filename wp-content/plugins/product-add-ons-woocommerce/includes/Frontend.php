<?php

namespace ZAddons;

use ZAddons\Frontend\Product;
use ZAddons\Frontend\Shop;
use ZAddons\Model\Group;

class Frontend
{
	public function __construct()
	{
		new Shop();
		new Product();
	}

	public static function hasTypes($product)
	{
		return array_reduce(Group::getByProduct($product), function ($carry, $group) {
			return $carry || count($group->types) > 0;
		}, false);
	}
}

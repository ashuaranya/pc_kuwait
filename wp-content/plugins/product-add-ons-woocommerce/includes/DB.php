<?php

namespace ZAddons;

class DB {
	const Prefix = "za_";
	/* Tables */
	const Groups = 'groups';
	const Products2Groups = 'products_to_groups';
	const Categories2Groups = 'categories_to_groups';
	const Types = 'types';
	const Values = 'values';
	const Headers = 'headers';

	public static function db_activate() {
		global $wpdb;
		$prefix = $wpdb->prefix . static::Prefix;
		$tables = get_option( 'zaddons_tables', [] );

		if ( ! in_array( 'base', $tables ) ) {
			$groups = $prefix . static::Groups;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${groups}` (
				id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				author BIGINT(20) UNSIGNED DEFAULT 0,
				title TEXT,
				visibility VARCHAR(20) DEFAULT 'public',
				priority INT(2),
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00'
			);"
			);

			$p2g = $prefix . static::Products2Groups;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${p2g}` (
					product_id BIGINT(20) UNSIGNED NOT NULL,
					group_id BIGINT(20) UNSIGNED NOT NULL
				);"
			);

			$c2g = $prefix . static::Categories2Groups;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${c2g}` (
				category_id BIGINT(20) UNSIGNED NOT NULL,
				group_id BIGINT(20) UNSIGNED NOT NULL
			);"
			);

			$types = $prefix . static::Types;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${types}` (
				id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				group_id BIGINT(20) UNSIGNED NOT NULL,
				title TEXT,
				step INT(2),
				type VARCHAR(250),
				status ENUM('enable', 'disable') NOT NULL DEFAULT 'enable', 
				accordion VARCHAR(10),
				description VARCHAR(250),
				required TINYINT(1) DEFAULT 0,
				display_description_on_expansion TINYINT(1) DEFAULT 0,
				hide_description TINYINT(1) DEFAULT 0,
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00'
			);"
			);

			$values = $prefix . static::Values;
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${values}` (
				id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				type_id BIGINT(20) UNSIGNED NOT NULL,
				title TEXT,
				step INT(2),
				price FLOAT,
				description VARCHAR(250),
				checked TINYINT(1) DEFAULT 0,
				hide TINYINT(1) DEFAULT 0,
				hide_description TINYINT(1) DEFAULT 0,
				tax_status ENUM('none', 'taxable') DEFAULT 'none', 
				tax_class VARCHAR (20),
				created_at DATETIME DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME DEFAULT '0000-00-00 00:00:00'
			);"
			);

			$tables[] = 'base';
			$tables[] = 'accordion';
			$tables[] = 'hiding';
			$tables[] = 'tax';
		}

		if ( ! in_array( 'apply_to_groups', $tables ) ) {

			$wpdb->query( "
			  ALTER TABLE `${groups}` ADD `apply_to` ENUM('custom', 'all', 'cart', 'checkout', 'cart_checkout') NOT NULL DEFAULT 'custom';
			" );

			$tables[] = 'apply_to_groups';
			$tables[] = 'additional_enum_values';
		}

		update_option( 'zaddons_tables', $tables );
		self::check_new_tables();

	}

	public static function check_new_tables() {
		global $wpdb;
		$tables = get_option( 'zaddons_tables', [] );

		$prefix = $wpdb->prefix . static::Prefix;
		$groups = $prefix . static::Groups;
		$types  = $prefix . static::Types;
		$values = $prefix . static::Values;


		if ( ! in_array( 'additional_enum_values', $tables ) ) {
			$wpdb->query( "
			  ALTER TABLE `${groups}` MODIFY `apply_to` ENUM('custom', 'all', 'cart', 'checkout', 'cart_checkout') NOT NULL DEFAULT 'custom';
			" );

			$tables[] = 'additional_enum_values';
		}

		$headers = $prefix . static::Headers;

		if ( ! in_array( 'header_text', $tables ) ) {
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `${headers}` (
				id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				header_type ENUM('cart', 'checkout') NOT NULL DEFAULT 'checkout',
				header_text VARCHAR(70)
			    );"
			);
			$tables[] = 'header_text';
			$tables[] = 'header_types';
		}

		if ( ! in_array( 'header_types', $tables ) ) {
			if ( ! self::is_column_exist( $headers, 'header_type' ) ) {
				$wpdb->query(
					"ALTER TABLE `${headers}` ADD COLUMN header_type ENUM('cart', 'checkout');"
				);
			}
			$tables[] = 'header_types';
		}

		if ( ! in_array( 'accordion', $tables ) ) {
			if ( ! self::is_column_exist( $types, 'accordion' ) ) {
				$wpdb->query(
					"ALTER TABLE `${types}` ADD COLUMN accordion VARCHAR(10);"
				);
			}
			$tables[] = 'accordion';
		}

		if ( ! in_array( 'hiding', $tables ) ) {
			if ( ! self::is_column_exist( $types, 'hide_description' ) ) {
				$wpdb->query(
					"ALTER TABLE `${types}` 
                    ADD COLUMN status ENUM('enable', 'disable') NOT NULL DEFAULT 'enable', 
                    ADD COLUMN hide_description TINYINT(1) DEFAULT 0,
                    ADD COLUMN display_description_on_expansion TINYINT(1) DEFAULT 0;"
				);

				$wpdb->query(
					"ALTER TABLE `${values}` 
                    ADD COLUMN hide TINYINT(1) DEFAULT 0,
                    ADD COLUMN hide_description TINYINT(1) DEFAULT 0;"
				);
			}
			$tables[] = 'hiding';
		}

		if ( ! in_array( 'tax', $tables ) ) {
			if ( ! self::is_column_exist( $values, 'tax_status' ) ) {
				$wpdb->query(
					"ALTER TABLE `${values}` 
                    ADD COLUMN tax_status ENUM('none', 'taxable') DEFAULT 'none',
                    ADD COLUMN tax_class VARCHAR (20);"
				);
			}
			$tables[] = 'tax';
		}
		update_option( 'zaddons_tables', $tables );
	}

	public static function drop_tables() {
		global $wpdb;
		$prefix      = $wpdb->prefix . static::Prefix;
		$table_names = [
			$prefix . static::Groups,
			$prefix . static::Products2Groups,
			$prefix . static::Categories2Groups,
			$prefix . static::Types,
			$prefix . static::Values,
			$prefix . static::Headers,
		];
		delete_option( 'zaddons_tables' );

		foreach ( $table_names as $table_name ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );
		}
	}

	public static function is_column_exist( $table_name, $column_name ) {
		global $wpdb;

		return $wpdb->query(
			"SELECT column_name 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA='" . DB_NAME . "' AND TABLE_NAME='${table_name}' AND column_name='${column_name}';"
		);
	}
}

<?php

namespace ZAddons;

function get_customize_addon_option( $name, $default_value = null ) {
	if ( defined( '\ZAddonsCustomize\ACTIVE' ) && \ZAddonsCustomize\ACTIVE ) {
		return get_option( $name, $default_value );
	}

	return $default_value;
}

function get_checkout_addon_option( $name, $default_value = null ) {
	if ( defined( '\ZProductAddons\ACTIVE' ) && \ZProductAddons\ACTIVE ) {
		return get_option( $name, $default_value );
	}

	return $default_value;
}

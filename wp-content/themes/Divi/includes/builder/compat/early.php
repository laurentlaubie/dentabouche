<?php
// Compatibility code that needs to be run early and for each request.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( function_exists( 'ud_get_stateless_media' ) ) {
	// WP Stateless Plugin.
	function et_compat_stateless_skip_cache_busting( $result, $filename ) {
		return $filename;
	}

	add_filter( 'stateless_skip_cache_busting', 'et_compat_stateless_skip_cache_busting', 10, 2 );
}

/**
 * Disable JQuery Body Feature.
 *
 * @since 4.10.3
 *
 * @return void
 */
function et_builder_disable_jquery_body() {
	add_filter( 'et_builder_enable_jquery_body', '__return_false' );
}

if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
	// Disable JQuery Body when SG CachePress JS Combine option is enabled
	// because the two features aren't compatible.
	if ( '1' === get_option( 'siteground_optimizer_combine_javascript' ) ) {
		et_builder_disable_jquery_body();
	}
}

if ( defined( 'WP_ROCKET_SLUG' ) ) {
	// Disable JQuery Body when WP Rocket Defer JS option is enabled
	// because the two features aren't compatible.
	if ( 1 === et_()->array_get( get_option( WP_ROCKET_SLUG ), 'defer_all_js' ) ) {
		et_builder_disable_jquery_body();
	}
}

if ( defined( 'LSCWP_V' ) ) {
	$options = [
		'litespeed.conf.optm-js_comb_ext_inl',
		'litespeed.conf.optm-js_defer',
	];

	// Disable JQuery Body when some LiteSpeed Cache JS options are enabled
	// because the features aren't compatible.
	foreach ( $options as $option ) {
		if ( ! empty( get_option( $option ) ) ) {
			et_builder_disable_jquery_body();
			break;
		}
	}
}

if ( defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
	$options = [
		'autoptimize_js_include_inline',
		'autoptimize_js_defer_inline',
		'autoptimize_js_forcehead',
	];

	// Disable JQuery Body when some Autoptimize JS options are enabled
	// because the features aren't compatible.
	foreach ( $options as $option ) {
		if ( ! empty( get_option( $option ) ) ) {
			et_builder_disable_jquery_body();
			break;
		}
	}
}

if ( defined( 'OP3_VERSION' ) ) {
	// Disable JQuery Body when some OptimizePress is active
	// because the two aren't compatible.
	et_builder_disable_jquery_body();
}

<?php
/**
 * Plugin compatibility for Siteground Optimizer.
 *
 * @package Divi
 * @subpackage Builder
 * @since 4.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin compatibility for SiteGround Optimizer
 *
 * @since 4.11.0
 *
 * @link https://wordpress.org/plugins/sg-cachepress/
 */
class ET_Builder_Plugin_Compat_SiteGround_Optimizer extends ET_Builder_Plugin_Compat_Base {
	/**
	 * Stylesheet handle.
	 *
	 * @var null
	 */
	private $_stylesheet_handle = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_id          = 'sg-cachepress/sg-cachepress.php';
		$this->_stylesheet_handle = ET_Dynamic_Assets::init()->get_style_css_handle();

		$this->init_hooks();
	}

	/**
	 * Hook methods to WordPress.
	 *
	 * @return void
	 */
	public function init_hooks() {

		if ( ! is_plugin_active( $this->plugin_id ) ) {
			return;
		}

		add_filter( 'sgo_css_combine_exclude', array( $this, 'exclude_inline_styles_from_siteground_cache' ) );
	}

	/**
	 * Exclude styles from being combined in SiteGround cache.
	 *
	 * @param array $excluded_stylesheets Excluded styles from being combined.
	 */
	public function exclude_inline_styles_from_siteground_cache( $excluded_stylesheets ) {
		global $shortname;

		$is_critical_enabled = apply_filters( 'et_builder_critical_css_enabled', false );

		// If Critical CSS is enabled, we don't need to process further.
		if ( $is_critical_enabled ) {
			return $excluded_stylesheets;
		}

		$style_prefix = 'divi-builder';

		if ( 'divi' === $shortname ) {
			$style_prefix = 'divi';
		} elseif ( 'extra' === $shortname ) {
			$style_prefix = 'extra';
		}

		$style_prefix = $style_prefix . '-dynamic';

		return array_merge(
			$excluded_stylesheets,
			array( $this->_stylesheet_handle, $style_prefix )
		);
	}
}

new ET_Builder_Plugin_Compat_SiteGround_Optimizer();

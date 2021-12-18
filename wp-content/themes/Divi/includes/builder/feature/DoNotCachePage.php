<?php
/**
 * Prevent Page/Post from being cached.
 *
 * @package Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class to prevent Page/Post from being cached.
 */
class ET_Builder_Do_Not_Cache_Page {
	/**
	 * Instance of `ET_Builder_Do_Not_Cache_Page`.
	 *
	 * @var ET_Builder_Do_Not_Cache_Page
	 */
	private static $_instance;

	/**
	 * Instance of `ET_Builder_Do_Not_Cache_Page`.
	 *
	 * @var bool
	 */
	protected $_processed = false;

	/**
	 * ET_Builder_Do_Not_Cache_Page constructor.
	 */
	public function __construct() {
		add_action( 'get_header', [ $this, 'maybe_prevent_cache' ] );
	}

	/**
	 * When a page loads for the first time, Builder CSS is generated during the request and then
	 * printed inline in the footer which causes CLS issues because the CSS is loading late.
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	public function prevent_cache() {
		// Ensure this only runs once.
		if ( $this->_processed ) {
			return;
		}

		$this->_processed = true;

		// Disable several plugins which do not honor `DONOTCACHEPAGE` (set in `PageResource.php`).
		if ( ! headers_sent() ) {
			// Sending `no-cache` header should prevent CDNs from caching the first request.
			header( 'Cache-Control: no-store, no-cache' );
			// Disable SiteGround Optimizer Dynamic Cache.
			header( 'X-Cache-Enabled: False' );
		}

		// Disable LiteSpeed Cache.
		$reason = esc_html__( 'Generating CSS', 'et_builder' );
		do_action( 'litespeed_control_set_nocache', $reason );

		// Disable WP Fastest Cache.
		if ( class_exists( 'WpFastestCacheCreateCache' ) ) {
			// This Plugin has no hook/API to disable cache programmatically....
			// The only way we can do this is by setting the `exclude_current_page_text` public property
			// to a non empty value... except the class instance is not made available anywhere in the code....
			// However, the instance also adds itself to the `wp` hook so we can find it by scanning the list
			// of all registered actions.
			$hooks = et_()->array_get( $GLOBALS, 'wp_filter.wp.10', [] );
			if ( is_array( $hooks ) ) {
				foreach ( $hooks as $key => $hook ) {
					if (
						isset( $hook['function'] ) &&
						is_array( $hook['function'] ) &&
						is_a( $hook['function'][0], 'WpFastestCacheCreateCache' )
					) {
						$wp_fastest_cache                            = $hook['function'][0];
						$wp_fastest_cache->exclude_current_page_text = "<!-- $reason -->";
						break;
					}
				}
			}
		}

		// Disable Breeze Cache.
		if ( function_exists( 'breeze_cache' ) ) {
			// This Plugin has no hook/API to disable cache programmatically....
			// The only way we can do this is by overwriting its configuration
			// which is exposed as global variable.
			global $breeze_config;
			if ( isset( $breeze_config['cache_options'] ) ) {
				$cache_options =& $breeze_config['cache_options'];
				if ( is_array( $cache_options ) ) {
					$cache_options['breeze-browser-cache'] = 0;
					$cache_options['breeze-desktop-cache'] = 0;
					$cache_options['breeze-mobile-cache']  = 0;
				}
			}
		}

		// Disable Hyper Cache.
		if ( function_exists( 'hyper_cache_callback' ) ) {
			global $hyper_cache_stop;
			$hyper_cache_stop = true;
		}
	}

	/**
	 * Disable Cache on first page request.
	 *
	 * @see prevent_cache()
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	public function maybe_prevent_cache() {
		// Bail if the magic already happened.
		if ( $this->_processed ) {
			return;
		}

		// Disable in the Visual Builder
		if ( et_fb_is_enabled() ) {
			return;
		}

		$post_id          = et_core_page_resource_get_the_ID();
		$is_preview       = is_preview() || is_et_pb_preview();
		$forced_in_footer = $post_id && et_builder_setting_is_on( 'et_pb_css_in_footer', $post_id );
		$forced_inline    = (
			! $post_id ||
			$is_preview ||
			$forced_in_footer ||
			et_builder_setting_is_off( 'et_pb_static_css_file', $post_id ) ||
			et_core_is_safe_mode_active() ||
			ET_GB_Block_Layout::is_layout_block_preview()
		);

		// If the post is password protected and a password has not been provided yet,
		// no content (including any custom style) will be printed.
		// When static css file option is enabled this will result in missing styles.
		if ( ! $forced_inline && post_password_required( $post_id ? $post_id : null ) ) {
			$forced_inline = true;
		}

		// Bail if using inline styles, page content won't be changing between requests anyway.
		if ( $forced_inline ) {
			return;
		}

		$unified = ! $forced_inline && ! $forced_in_footer;
		$owner   = $unified ? 'core' : 'builder';
		$slug    = $unified ? 'unified' : 'module-design';
		$slug   .= $unified && et_builder_post_is_of_custom_post_type( $post_id ) ? '-cpt' : '';
		$slug    = et_theme_builder_decorate_page_resource_slug( $post_id, $slug );

		$resource = et_core_page_resource_get( $owner, $slug, $post_id );

		// Bail if Builder CSS already exists in external file, this is the request we want to cache.
		if ( $resource->has_file() ) {
			return;
		}

		$this->prevent_cache();
	}

	/**
	 * Get the class instance.
	 *
	 * @since 4.11.0
	 *
	 * @return ET_Builder_Do_Not_Cache_Page
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

}

ET_Builder_Do_Not_Cache_Page::instance();

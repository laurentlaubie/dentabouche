<?php
/**
 * Block Templates Compatibility.
 *
 * @package Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Block Templates Compatibility Class.
 *
 * @since 4.9.8
 */
class ET_Builder_Block_Templates {
	/**
	 * Instance of `ET_Builder_Block_Templates`.
	 *
	 * @var ET_Builder_Block_Templates
	 */
	private static $_instance;

	/**
	 * ET_Builder_Block_Templates constructor.
	 */
	public function __construct() {
		$this->set_query_templates_filters();
	}

	/**
	 * Get the class instance.
	 *
	 * @since 4.9.8
	 *
	 * @return ET_Builder_Block_Templates
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Set query templates filters to override block templates.
	 *
	 * @since 4.9.8
	 */
	public function set_query_templates_filters() {
		// Bail early if current active builder is not DBP.
		if ( ! et_is_builder_plugin_active() ) {
			return;
		}

		// Bail early if `locate_block_template` function doesn't exists (WP 5.8).
		if ( ! function_exists( 'locate_block_template' ) ) {
			return;
		}

		// Add those filters only when current active theme supports `block-templates` or
		// has block templates index HTML.
		if ( ! current_theme_supports( 'block-templates' ) && ! is_readable( get_stylesheet_directory() . '/block-templates/index.html' ) ) {
			return;
		}

		/**
		 * List of possible hook names:
		 *  - `404_template`
		 *  - `archive_template`
		 *  - `attachment_template` (Not Included)
		 *  - `author_template`
		 *  - `category_template`
		 *  - `date_template`
		 *  - `embed_template` (Not Included)
		 *  - `frontpage_template`
		 *  - `home_template`
		 *  - `index_template`
		 *  - `page_template`
		 *  - `paged_template`
		 *  - `privacypolicy_template`
		 *  - `search_template`
		 *  - `single_template`
		 *  - `singular_template`
		 *  - `tag_template`
		 *  - `taxonomy_template`
		 *
		 * However we don't include `attachment`, `paged`, and `embed` because they are not
		 * modified or attached to TB tempates.
		 */
		$template_types = array(
			'404_template',
			'archive_template',
			'author_template',
			'category_template',
			'date_template',
			'frontpage_template',
			'home_template',
			'index_template',
			'page_template',
			'privacypolicy_template',
			'search_template',
			'single_template',
			'singular_template',
			'tag_template',
			'taxonomy_template',
		);

		foreach ( $template_types as $template ) {
			add_filter( $template, array( $this, 'get_custom_query_template' ), 30, 3 );
		}
	}

	/**
	 * Get pre-defined query template to override block template (modified default template
	 * or custom template).
	 *
	 * @since 4.9.8
	 *
	 * @param string $template  Path to the template. See locate_template().
	 * @param string $type      Sanitized filename without extension.
	 * @param array  $templates A list of template candidates, in descending order of priority.
	 *
	 * @return string Modified path to the template.
	 */
	public function get_custom_query_template( $template, $type, $templates ) {
		// Bail early if there is no TB templates for current page request.
		if ( empty( et_theme_builder_get_template_layouts() ) ) {
			return $template;
		}

		// 1. Restore - Get pre-defined query template.
		$original_template = $template;
		$template          = locate_template( $templates );

		// If the `locate_template` return empty path because there is no template or theme
		// theme compat found, use builder block template canvas.
		if ( empty( $template ) && 'template-canvas.php' === basename( $original_template ) ) {
			$template = ET_BUILDER_DIR . 'templates/block-template-canvas.php';
		}

		// 2. Remove hooks added for template canvas (block template).
		// Remove viewport meta tag.
		if ( function_exists( '_block_template_viewport_meta_tag' ) ) {
			remove_action( 'wp_head', '_block_template_viewport_meta_tag', 0 );
		}

		// Render conditional title tag for `title-tag` support.
		add_action( 'wp_head', '_wp_render_title_tag', 1 );

		// Remove unconditional title tag.
		if ( function_exists( '_block_template_render_title_tag' ) ) {
			remove_action( 'wp_head', '_block_template_render_title_tag', 1 );
		}

		return $template;
	}
}

ET_Builder_Block_Templates::instance();

<?php
/**
 * Modified block template canvas file to render Visual and Theme Builder.
 *
 * @package Divi
 */

/**
 * Get the template HTML.
 * This needs to run before <head> so that blocks can add scripts and styles in wp_head().
 */
$template_html = get_the_block_template_html();

// Disable deprecated warning temporarily because we're going to use default header.
add_filter( 'deprecated_file_trigger_error', '__return_false' );
get_header();
remove_filter( 'deprecated_file_trigger_error', '__return_false' );

echo $template_html; // phpcs:ignore WordPress.Security.EscapeOutput -- Already escaped.

// Disable deprecated warning temporarily because we're going to use default footer.
add_filter( 'deprecated_file_trigger_error', '__return_false' );
get_footer();
remove_filter( 'deprecated_file_trigger_error', '__return_false' );

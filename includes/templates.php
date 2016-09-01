<?php

/**
 * Create Page Part Templates in exactly the same way as WordPress custom templates:
 * https://developer.wordpress.org/themes/template-files-section/page-template-files/page-templates/#creating-custom-page-templates-for-global-use
 *
 * The only difference is that rather than specifying a "Template Name"
 * in the template header instead add a "Page Part Name".
 *
 * @package     Page Parts
 * @subpackage  Templates
 *
 * @since  1.0
 */

/**
 * Page Parts Templates Class
 *
 * @since  1.0
 */
class Page_Parts_Templates {

	/**
	 * Theme Root
	 *
	 * Used for generating cache keys.
	 *
	 * @since  1.0
	 *
	 * @var  string
	 */
	private $theme_root;

	/**
	 * Stylesheet (theme slug)
	 *
	 * Used for generating cache keys.
	 *
	 * @since  1.0
	 *
	 * @var  string
	 */
	private $stylesheet;

	/**
	 * Cache Hash
	 *
	 * @since  1.0
	 *
	 * @var  string
	 */
	private $cache_hash;

	/**
	 * Cache Expiration Time
	 *
	 * @since  1.0
	 *
	 * @var  integer
	 */
	private static $cache_expiration = 1800;

	/**
	 * Constructor
	 *
	 * @since  1.0
	 *
	 * Sets up cache hache used when getting templates.
	 */
	public function __construct() {

		$this->theme_root = get_theme_root();
		$this->stylesheet = get_stylesheet();

		$this->cache_hash = md5( $this->theme_root . '/' . $this->stylesheet );

	}

	/**
	 * Get Cache
	 *
	 * @since  1.0
	 *
	 * @param   string  $key  Cache key.
	 * @return  array         Cached templates data.
	 */
	private function cache_get( $key ) {
		return wp_cache_get( $key . '-' . $this->cache_hash, 'themes' );
	}

	/**
	 * Add Cache
	 *
	 * @since  1.0
	 *
	 * @param   string   $key   Cache key.
	 * @param   string   $data  Cached templates data.
	 * @return  boolean         Cached successfully.
	 */
	private function cache_add( $key, $data ) {
		return wp_cache_add( $key . '-' . $this->cache_hash, $data, 'themes', self::$cache_expiration );
	}

	/**
	 * Get Page Part Templates
	 *
	 * Gets an array of possible page part templates for a post.
	 *
	 * @since  1.0
	 *
	 * @param   int|WP_Post  $post  Post ID or object.
	 * @return  array               Templates.
	 */
	public function get_page_part_templates( $post = null ) {

		$theme = wp_get_theme();

		// If you screw up your current theme and we invalidate your parent, most things still work. Let it slide.
		if ( $theme->errors() && $theme->errors()->get_error_codes() !== array( 'theme_parent_invalid' ) ) {
			return array();
		}

		$page_templates = $this->cache_get( 'page_part_templates' );

		if ( ! is_array( $page_templates ) ) {
			$page_templates = array();

			$files = (array) $theme->get_files( 'php', 1 );

			foreach ( $files as $file => $full_path ) {
				if ( ! preg_match( '|Page Part Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
					continue;
				}
				$page_templates[ $file ] = $this->cleanup_header_comment( $header[1] );
			}

			$this->cache_add( 'page_part_templates', $page_templates );
		}

		// @todo  Loop through $page_templates and translate names. See WP $theme->translate_header().
		/*
		// Example of WordPress page template functionality.
		if ( $theme->load_textdomain() ) {
			foreach ( $page_templates as &$page_template ) {
				$page_template = $theme->translate_header( 'Template Name', $page_template );
			}
		}
		*/

		// @todo  If theme has parent theme, get parent template if child version does not exist.
		/*
		// Example of WordPress page template functionality.
		if ( $theme->parent() ) {
			$page_templates += $theme->parent()->get_page_part_templates( $post );
		}
		*/

		$return = apply_filters( 'page_part_theme_templates', $page_templates, $theme, $post );

		return array_flip( array_intersect_assoc( $return, $page_templates ) );

	}

	/**
	 * Cleanup Header Comment
	 *
	 * Copy of the WordPress _cleanup_header_comment() function which is marked as private,
	 * not intended for use by plugins.
	 *
	 * Strips close comment and close php tags from file headers.
	 *
	 * @since  1.0
	 * 
	 * @param   string  $str  String to clean.
	 * @return  string        Cleaned string.
	 */
	private function cleanup_header_comment( $str ) {

		return trim( preg_replace( "/\s*(?:\*\/|\?>).*/", '', $str ) );

	}

	/**
	 * Has Page Part Templates
	 *
	 * @since  1.0
	 *
	 * @return  boolean  Post has page part templates?
	 */
	public function has_page_part_templates() {

		$templates = $this->get_page_part_templates( get_post() );

		return count( $templates ) > 0;

	}

	/**
	 * Page Part Template Dropdown
	 *
	 * @since  1.0
	 *
	 * @param   string  $default  Selected template.
	 * @return  string            HTML <option> list for dropdrop.
	 */
	public function page_part_template_dropdown( $default = '' ) {

		$dropdown = '';

		$templates = $this->get_page_part_templates( get_post() );
		ksort( $templates );

		foreach ( array_keys( $templates ) as $template ) {
			$selected = selected( $default, $templates[ $template ], false );
			$dropdown .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $templates[ $template ] ), $selected, esc_html( $template ) );
		}

		return $dropdown;

	}

}

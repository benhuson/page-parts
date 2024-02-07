<?php

/*
Plugin Name: Page Parts
Plugin URI: https://github.com/benhuson/page-parts
Description: Manage subsections of a page.
Version: 1.4.3
Author: Ben Huson
Author URI: https://github.com/benhuson
License: GPL2
*/

define( 'PAGE_PARTS_VERSION', '1.4.3' );
define( 'PAGE_PARTS_FILE', __FILE__ );

class Page_Parts {

	var $admin;
	var $templates;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Language
		load_plugin_textdomain( 'page-parts', false, dirname( plugin_basename( PAGE_PARTS_FILE ) ) . '/languages' );

		add_action( 'init', array( $this, 'register_post_types' ), 6 );
		add_filter( 'post_type_link', array( $this, 'post_part_link' ), 10, 4 );
		add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );

		// Includes
		require_once( dirname( PAGE_PARTS_FILE ) . '/includes/page-part-template-class.php' );

		// Template
		require_once( dirname( PAGE_PARTS_FILE ) . '/includes/templates.php' );
		$this->templates = new Page_Parts_Templates();

		if ( is_admin() ) {
			require_once( dirname( PAGE_PARTS_FILE ) . '/admin/admin.php' );
			$this->admin = new Page_Parts_Admin();
		}
	}

	/**
	 * Register Post Part Post Type
	 */
	public function register_post_types() {
		$args = array(
			'labels'              => array(
				'name'               => _x( 'Page Parts', 'post type general name', 'page-parts' ),
				'singular_name'      => _x( 'Page Part', 'post type singular name', 'page-parts' ),
				'add_new'            => _x( 'Add New', 'magazine', 'page-parts' ),
				'add_new_item'       => __( 'Add New Page Part', 'page-parts' ),
				'edit_item'          => __( 'Edit Page Part', 'page-parts' ),
				'new_item'           => __( 'New Page Part', 'page-parts' ),
				'view_item'          => __( 'View Page Part', 'page-parts' ),
				'search_items'       => __( 'Search Page Parts', 'page-parts' ),
				'not_found'          => __( 'No page parts found', 'page-parts' ),
				'not_found_in_trash' => __( 'No page parts found in Trash', 'page-parts' ),
				'parent_item_colon'  => '',
				'menu_name'          => __( 'Page Parts', 'page-parts' )
			),
			'description'         => __( 'Content that makes up part of a page.', 'page-parts' ),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true, 
			'show_in_menu'        => 'edit.php?post_type=page', 
			'show_in_nav_menus'   => false,
			'query_var'           => true,
			'rewrite'             => true,
			'capability_type'     => 'page',
			'has_archive'         => false, 
			'hierarchical'        => false,
			'menu_position'       => 20,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' )
		);
		$args = apply_filters( 'register_page_part_args', $args );
		register_post_type( 'page-part', $args );
	}

	/**
	 * Post Part Link
	 *
	 * By default, the link for a page part will link to an anchor with the post part slug.
	 * For example http://www.example.com/my-page#my-page-part
	 *
	 * @param   string  $post_link  Post Part URL.
	 * @param   object  $post       Post object.
	 * @param   bool    $leavename  Optional, defaults to false. Whether to keep post name.
	 * @param   bool    $sample     Optional, defaults to false. Is it a sample permalink.
	 * @return  string              Post Part URL.
	 */
	public function post_part_link( $post_link, $post, $leavename, $sample ) {

		if ( $post->post_type == 'page-part' ) {

			if ( $post->post_parent > 0 ) {
				if ( 'page-part' == get_post_type( $post->post_parent ) ) {
					$ancestors = get_ancestors( $post->post_parent, 'post' );
					foreach ( $ancestors as $ancestor ) {
						if ( 'page-part' != get_post_type( $ancestor ) ) {
							$post_link = $this->create_permalink( $ancestor, $post->post_name );
							break;
						}
					}
				} else {
					$post_link = $this->create_permalink( $post->post_parent, $post->post_name );
				}
			}

			return esc_url_raw( apply_filters( 'post_part_post_type_link', $post_link, $post, $leavename, $sample ) );

		}

		return $post_link;

	}

	/**
	 * Create Permalink
	 *
	 * @param   int     $post_id  Post ID.
	 * @param   string  $anchor   Anchor text.
	 * @return  string            URL.
	 */
	private function create_permalink( $post_id, $anchor ) {

		return get_permalink( $post_id ) . '#' . $anchor;

	}

	/**
	 * Add Page Part Post Classes
	 *
	 * This only adds the page part template filename-based class.
	 * It doesn't add a default template class. It also doesn't add
	 * multiple classes based on folder structure as WordPress
	 * custom page templates do.
	 *
	 * This could cause issues if you had multiple page part templates
	 * with the same name existing in different folders.
	 * Let's not worry about that for now though.
	 *
	 * @since  1.0
	 *
	 * @param   array    $classes  Post classes.
	 * @param   string   $class    A comma-separated list of additional classes added to the post.
	 * @param   integer  $post_id  Post ID.
	 * @return  array              Filtered classes.
	 */
	public function post_class( $classes, $class, $post_id ) {

		$post = get_post( $post_id );

		// Only add classes for page parts
		if ( 'page-part' != $post->post_type ) {
			return $classes;
		}

		$template = self::get_page_part_template_slug( $post_id );

		if ( ! empty( $template ) ) {
			$classes[] = 'page-part-template';
			$classes[] = 'page-part-template-' . sanitize_html_class( basename( $template, '.php' ) );
		} else {
			$classes[] = 'page-part-default';
		}

		return $classes;

	}

	/**
	 * Supported Post Types
	 *
	 * Gets an array of suuported post types.
	 *
	 * @since  0.5
	 * @uses  apply_filters  Calls 'page_parts_supported_post_types'.
	 *
	 * @return  array  Supported post types.
	 */
	public function supported_post_types() {

		return apply_filters( 'page_parts_supported_post_types', array( 'page' ) );

	}

	/**
	 * Get the specific template name for a page part.
	 *
	 * @since  1.0
	 *
	 * @param  int           $post_id  Optional. The page ID to check. Defaults to the current post in the loop.
	 * @return string|false            Page template filename. Returns an empty string when the default
	 *                                 page part template is in use. Returns false if the post is not a page part.
	 */
	public static function get_page_part_template_slug( $post_id = null ) {

		$post = get_post( $post_id );
		if ( ! $post || 'page-part' != $post->post_type ) {
			return false;
		}

		$template = get_post_meta( $post->ID, '_page_part_template', true );
		if ( ! $template || 'default' == $template ) {
			return '';
		}

		return $template;

	}

	/**
	 * Get Page Part Template
	 * 
	 * This method is used to load a custom Page Part template from the
	 * current actuve theme. If a custom Page Part template cannot
	 * be found in the theme it will look for a 'page-part.php' template
	 * in the root of the theme.
	 *
	 * If no valid template is found in the theme then it will fallback
	 * to loading 'templates/page-part.php' in the Page Parts plugin folder.
	 *
	 * @since  1.0
	 */
	public static function get_page_part_template() {

		$post = get_post();

		$template_names = array();

		if ( 'page-part' == $post->post_type ) {
			$template = self::get_page_part_template_slug( $post->ID );
			if ( ! empty( $template ) ) {
				$template_names[] = $template;
			}
		}

		$template_names = apply_filters( 'page_part_locate_templates', $template_names );
		$template_names[] = 'page-part.php';

		$located = locate_template( $template_names, true, false );

		if ( ! $located ) {
			load_template( dirname( PAGE_PARTS_FILE ) . '/templates/page-part.php', false );
		}

	}

}

global $Page_Parts;
$Page_Parts = new Page_Parts();

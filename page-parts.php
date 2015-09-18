<?php

/*
Plugin Name: Page Parts
Plugin URI: https://github.com/benhuson/page-parts
Description: Manage subsections of a page.
Version: 0.8
Author: Ben Huson
Author URI: https://github.com/benhuson
License: GPL2
*/

define( 'PAGE_PARTS_VERSION', '0.8' );
define( 'PAGE_PARTS_FILE', __FILE__ );

class Page_Parts {

	var $admin;

	/**
	 * Constructor
	 */
	public function Page_Parts() {

		// Language
		load_plugin_textdomain( 'page-parts', false, dirname( plugin_basename( PAGE_PARTS_FILE ) ) . '/languages' );

		add_action( 'init', array( $this, 'register_post_types' ), 6 );
		add_filter( 'post_type_link', array( $this, 'post_part_link' ), 10, 4 );

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
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' )
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

}

global $Page_Parts;
$Page_Parts = new Page_Parts();

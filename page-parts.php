<?php 

/*
Plugin Name: Page Parts
Version: 0.2
Description: Manage subsections of a page. Requires WordPress 3.4.
Author: Ben Huson
*/

class Page_Parts {
	
	var $admin;
	
	/**
	 * Constructor
	 */
	function Page_Parts() {
		add_action( 'init', array( $this, 'register_post_types' ), 6 );
		add_filter( 'post_type_link', array( $this, 'post_part_link' ), 10, 4 );
		
		if ( is_admin() ) {
			require_once( dirname( __FILE__ ) . '/admin/admin.php' );
			$this->admin = new Page_Parts_Admin();
		}
	}
	
	/**
	 * Register Post Part Post Type
	 */
	function register_post_types() {
		$labels = array(
			'name'               => _x( 'Page Parts', 'post type general name' ),
			'singular_name'      => _x( 'Page Part', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'magazine' ),
			'add_new_item'       => __( 'Add New Page Part' ),
			'edit_item'          => __( 'Edit Page Part' ),
			'new_item'           => __( 'New Page Part' ),
			'view_item'          => __( 'View Page Part' ),
			'search_items'       => __( 'Search Page Parts' ),
			'not_found'          => __( 'No page parts found' ),
			'not_found_in_trash' => __( 'No page parts found in Trash' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Page Parts'
		);
		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Content that makes up part of a page.' ),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true, 
			'show_in_menu'        => 'edit.php?post_type=page', 
			'query_var'           => true,
			'rewrite'             => true,
			'capability_type'     => 'page',
			'has_archive'         => false, 
			'hierarchical'        => false,
			'menu_position'       => 20,
			'supports'            => array( 'title', 'editor', 'thumbnail' )
		);
		register_post_type( 'page-part', $args );
	}
	
	/**
	 * Post Part Link
	 * By default, the link for a page part will link to an anchor with the post part slug.
	 * For example http://www.example.com/my-page#my-page-part
	 */
	function post_part_link( $post_link, $post, $leavename, $sample ) {
		if ( $post->post_type == 'page-part' && $post->post_parent > 0 ) {
			$post_link = get_permalink( $post->post_parent ) . '#' . $post->post_name;
		}
		return apply_filters( 'post_part_post_type_link', $post_link, $post, $leavename, $sample );
	}
	
}

global $Page_Parts;
$Page_Parts = new Page_Parts();

?>
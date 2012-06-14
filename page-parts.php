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
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
		add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );
		add_filter( 'post_type_link', array( $this, 'post_part_link' ), 10, 4 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'manage_edit-page-part_columns', array( $this, 'manage_edit_page_part_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ) );
		
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
	 * Updated Messages
	 */
	function updated_messages( $messages ) {
		global $post, $post_ID;
		
		$messages['page-part'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Page Part updated. <a href="%s">View page part</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.' ),
			3  => __( 'Custom field deleted.' ),
			4  => __( 'Page Part updated.' ),
			// translators: %s: date and time of the revision
			5  => isset( $_GET['revision'] ) ? sprintf( __('Page Part restored to revision from %s' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Page Part published. <a href="%s">View page part</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Page Part saved.' ),
			8  => sprintf( __( 'Page Part submitted. <a target="_blank" href="%s">Preview page part</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf( __( 'Page Part scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page part</a>' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Page Part draft updated. <a target="_blank" href="%s">Preview page part</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
		return $messages;
	}
	
	/**
	 * Contextual Help
	 */
	function contextual_help( $contextual_help, $screen_id, $screen ) { 
		//$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
		if ( 'page-part' == $screen->id ) {
			$contextual_help =
				'<p>' . __( 'Things to remember when adding or editing a page part:' ) . '</p>' .
				'<p>Not a lot.</p>';
		} elseif ( 'edit-page-part' == $screen->id ) {
			$contextual_help = '<p>' . __( 'No page part documentation.' ) . '</p>';
		}
		return $contextual_help;
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
	
	/**
	 * Manage Page Part Columns
	 */
	function manage_edit_page_part_columns( $columns ) {
		$columns['parent'] = 'Parent Page';
		return $columns;
	}
	
	/**
	 * Manage Page Part Columns Output
	 */
	function manage_posts_custom_column( $name ) {
		global $post;
		
		switch ( $name ) {
			case 'parent':
				$parent = $post->post_parent;
				edit_post_link( get_the_title( $post->post_parent ), null, null, $post->post_parent );
		}
	}
	
	/**
	 * Save Page Part
	 */
	function save_post( $post_id ) {
		global $wpdb;
		
		// Verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// Verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( ! isset( $_POST['page_parts_noncename'] ) || ! wp_verify_nonce( $_POST['page_parts_noncename'], plugin_basename( __FILE__ ) ) )
			return;
		
		// Check permissions
		if ( 'page-part' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		}
		
		// OK, we're authenticated: we need to find and save the data
		$parent_id = absint( $_POST['parent_id'] );
		$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_id ), array( 'ID' => $post_id ) );
		
		return $parent_id;
	}
	
	/**
	 * Add Meta Boxes
	 */
	function add_meta_boxes() {
		add_meta_box(
			'page_parts_parent',
			__( 'Parent Page', 'page-parts' ), 
			array( $this, 'parent_meta_box' ),
			'page-part',
			'side',
			'core'
		);
	}
	
	/**
	 * Add Parent Meta Box
	 */
	function parent_meta_box() {
		global $post;
		
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'page_parts_noncename' );
		
		if ( empty( $post->post_parent ) && isset( $_REQUEST['parent_id'] ) )
			$post->post_parent = $_REQUEST['parent_id'];
		
		// The actual fields for data entry
		$args = array(
			'selected'    => absint( $post->post_parent ),
			'echo'        => 0,
			'name'        => 'parent_id',
			'sort_order'  => 'ASC',
			'sort_column' => 'menu_order,post_title',
			'post_type'   => 'page',
	        'post_status' => 'publish,draft'
		);
		echo '<p>' . wp_dropdown_pages( $args ) . '</p>';
		if ( $post->post_parent > 0 ) {
			edit_post_link( 'Edit ' . get_the_title( $post->post_parent ), '<p>', '</p>', $post->post_parent );
		}
	}
	
}

global $Page_Parts;
$Page_Parts = new Page_Parts();

?>
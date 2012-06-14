<?php 

/*
Plugin Name: Page Parts
Version: 0.2
Description: Manage subsections of a page.
Author: Ben @ Camber
*/

$page_parts = new Page_Parts();

class Page_Parts {
	
	var $admin;
	
	function Page_Parts() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		
		add_filter( 'manage_edit-page-part_columns', array( $this, 'manage_edit_page_part_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ) );
		
		add_post_type_support( 'page-part', 'post-formats' );
		
		if ( is_admin() ) {
			require_once( dirname( __FILE__ ) . '/admin/admin.php' );
			$this->admin = new Page_Parts_Admin();
		}
	
	}
	
	function manage_edit_page_part_columns( $columns ) {
		
		$columns['parent'] = 'Parent Page';
		return $columns;
		
	}
	
	function manage_posts_custom_column( $name ) {
	
		global $post;
		switch ( $name ) {
			case 'parent':
				$parent = $post->post_parent;
				edit_post_link( get_the_title( $post->post_parent ), null, null, $post->post_parent );
		}
		
	}
	
	function save_post( $post_id ) {
		
		global $wpdb;
		
		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['page_parts_noncename'], plugin_basename( __FILE__ ) ) )
			return;
		
		// Check permissions
		if ( 'page-part' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		}
		
		// OK, we're authenticated: we need to find and save the data
		$parent_id = absint( $_POST['parent_id'] );
		$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_id ), array( 'ID' => $post_id ) );
		
		return $parent_id;
		
	}
	
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
		echo '<p>' . $this->wp_dropdown_pages( $args ) . '</p>';
		if ( $post->post_parent > 0 ) {
			edit_post_link( 'Edit ' . get_the_title( $post->post_parent ), '<p>', '</p>', $post->post_parent );
		}	
	
	}
	
	/**
	 * Customise version of wp_dropdown_pages
	 * to allow draft pages.
	 * Fixed in WordPress 3.3?
	 */
	function wp_dropdown_pages($args = '') {
	        $defaults = array(
	                'depth' => 0, 'child_of' => 0,
	                'selected' => 0, 'echo' => 1,
	                'name' => 'page_id', 'id' => '',
	                'show_option_none' => '', 'show_option_no_change' => '',
	                'option_none_value' => '',
	                'post_status' => 'publish'
	        );
	
	        $r = wp_parse_args( $args, $defaults );
	        extract( $r, EXTR_SKIP );
	
	        $pages = $this->get_pages($r);
	        $output = '';
	        $name = esc_attr($name);
	        // Back-compat with old system where both id and name were based on $name argument
	        if ( empty($id) )
	                $id = $name;
	
	        if ( ! empty($pages) ) {
	                $output = "<select name=\"$name\" id=\"$id\">\n";
	                if ( $show_option_no_change )
	                        $output .= "\t<option value=\"-1\">$show_option_no_change</option>";
	                if ( $show_option_none )
	                        $output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
	                $output .= walk_page_dropdown_tree($pages, $depth, $r);
	                $output .= "</select>\n";
	        }
	
	        $output = apply_filters('wp_dropdown_pages', $output);
	
	        if ( $echo )
	                echo $output;

	        return $output;
	}
	
	/**
	 * Customise version of get_pages
	 * to allow draft pages.
	 * Fixed in WordPress 3.3?
	 */
	function &get_pages($args = '') {
		global $wpdb;
	
		$defaults = array(
			'child_of' => 0, 'sort_order' => 'ASC',
			'sort_column' => 'post_title', 'hierarchical' => 1,
			'exclude' => array(), 'include' => array(),
			'meta_key' => '', 'meta_value' => '',
			'authors' => '', 'parent' => -1, 'exclude_tree' => '',
			'number' => '', 'offset' => 0,
			'post_type' => 'page', 'post_status' => 'publish',
		);
	
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$number = (int) $number;
		$offset = (int) $offset;
	
		// Make sure the post type is hierarchical
		$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
		if ( !in_array( $post_type, $hierarchical_post_types ) )
			return false;
	
		// Make sure we have a valid post status
		if ( !is_array( $post_status ) )
			$post_status = explode( ',', $post_status );
		if ( array_diff( $post_status, get_post_stati() ) )
			return false;
			
		$cache = array();
		$key = md5( serialize( compact(array_keys($defaults)) ) );
		if ( $cache = wp_cache_get( 'get_pages', 'posts' ) ) {
			if ( is_array($cache) && isset( $cache[ $key ] ) ) {
				$pages = apply_filters('get_pages', $cache[ $key ], $r );
				return $pages;
			}
		}
	
		if ( !is_array($cache) )
			$cache = array();
	
		$inclusions = '';
		if ( !empty($include) ) {
			$child_of = 0; //ignore child_of, parent, exclude, meta_key, and meta_value params if using include
			$parent = -1;
			$exclude = '';
			$meta_key = '';
			$meta_value = '';
			$hierarchical = false;
			$incpages = wp_parse_id_list( $include );
			if ( ! empty( $incpages ) ) {
				foreach ( $incpages as $incpage ) {
					if (empty($inclusions))
						$inclusions = $wpdb->prepare(' AND ( ID = %d ', $incpage);
					else
						$inclusions .= $wpdb->prepare(' OR ID = %d ', $incpage);
				}
			}
		}
		if (!empty($inclusions))
			$inclusions .= ')';
	
		$exclusions = '';
		if ( !empty($exclude) ) {
			$expages = wp_parse_id_list( $exclude );
			if ( ! empty( $expages ) ) {
				foreach ( $expages as $expage ) {
					if (empty($exclusions))
						$exclusions = $wpdb->prepare(' AND ( ID <> %d ', $expage);
					else
						$exclusions .= $wpdb->prepare(' AND ID <> %d ', $expage);
				}
			}
		}
		if (!empty($exclusions))
			$exclusions .= ')';
	
		$author_query = '';
		if (!empty($authors)) {
			$post_authors = preg_split('/[\s,]+/',$authors);
	
			if ( ! empty( $post_authors ) ) {
				foreach ( $post_authors as $post_author ) {
					//Do we have an author id or an author login?
					if ( 0 == intval($post_author) ) {
						$post_author = get_user_by('login', $post_author);
						if ( empty($post_author) )
							continue;
						if ( empty($post_author->ID) )
							continue;
						$post_author = $post_author->ID;
					}
	
					if ( '' == $author_query )
						$author_query = $wpdb->prepare(' post_author = %d ', $post_author);
					else
						$author_query .= $wpdb->prepare(' OR post_author = %d ', $post_author);
				}
				if ( '' != $author_query )
					$author_query = " AND ($author_query)";
			}
		}
	
		$join = '';
		$where = "$exclusions $inclusions ";
		if ( ! empty( $meta_key ) || ! empty( $meta_value ) ) {
			$join = " LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )";
	
			// meta_key and meta_value might be slashed
			$meta_key = stripslashes($meta_key);
			$meta_value = stripslashes($meta_value);
			if ( ! empty( $meta_key ) )
				$where .= $wpdb->prepare(" AND $wpdb->postmeta.meta_key = %s", $meta_key);
			if ( ! empty( $meta_value ) )
				$where .= $wpdb->prepare(" AND $wpdb->postmeta.meta_value = %s", $meta_value);
	
		}
	
		if ( $parent >= 0 )
			$where .= $wpdb->prepare(' AND post_parent = %d ', $parent);
	
		if ( 1 == count( $post_status ) ) {
			$where_post_type = $wpdb->prepare( "post_type = %s AND post_status = %s", $post_type, array_shift( $post_status ) );
		} else {
			$post_status = implode( "', '", $post_status );
			$where_post_type = $wpdb->prepare( "post_type = %s AND post_status IN ('$post_status')", $post_type );
		}
	
		$orderby_array = array();
		$allowed_keys = array('author', 'post_author', 'date', 'post_date', 'title', 'post_title', 'modified',
							  'post_modified', 'modified_gmt', 'post_modified_gmt', 'menu_order', 'parent', 'post_parent',
							  'ID', 'rand', 'comment_count');
		foreach ( explode( ',', $sort_column ) as $orderby ) {
			$orderby = trim( $orderby );
			if ( !in_array( $orderby, $allowed_keys ) )
				continue;
	
			switch ( $orderby ) {
				case 'menu_order':
					break;
				case 'ID':
					$orderby = "$wpdb->posts.ID";
					break;
				case 'rand':
					$orderby = 'RAND()';
					break;
				case 'comment_count':
					$orderby = "$wpdb->posts.comment_count";
					break;
				default:
					if ( 0 === strpos( $orderby, 'post_' ) )
						$orderby = "$wpdb->posts." . $orderby;
					else
						$orderby = "$wpdb->posts.post_" . $orderby;
			}
	
			$orderby_array[] = $orderby;
	
		}
		$sort_column = ! empty( $orderby_array ) ? implode( ',', $orderby_array ) : "$wpdb->posts.post_title";
	
		$sort_order = strtoupper( $sort_order );
		if ( '' !== $sort_order && !in_array( $sort_order, array( 'ASC', 'DESC' ) ) )
			$sort_order = 'ASC';
	
		$query = "SELECT * FROM $wpdb->posts $join WHERE ($where_post_type) $where ";
		$query .= $author_query;
		$query .= " ORDER BY " . $sort_column . " " . $sort_order ;
	
		if ( !empty($number) )
			$query .= ' LIMIT ' . $offset . ',' . $number;
	
		$pages = $wpdb->get_results($query);
	
		if ( empty($pages) ) {
			$pages = apply_filters('get_pages', array(), $r);
			return $pages;
		}
	
		// Sanitize before caching so it'll only get done once
		$num_pages = count($pages);
		for ($i = 0; $i < $num_pages; $i++) {
			$pages[$i] = sanitize_post($pages[$i], 'raw');
		}
	
		// Update cache.
		update_page_cache($pages);
	
		if ( $child_of || $hierarchical )
			$pages = & get_page_children($child_of, $pages);
	
		if ( !empty($exclude_tree) ) {
			$exclude = (int) $exclude_tree;
			$children = get_page_children($exclude, $pages);
			$excludes = array();
			foreach ( $children as $child )
				$excludes[] = $child->ID;
			$excludes[] = $exclude;
			$num_pages = count($pages);
			for ( $i = 0; $i < $num_pages; $i++ ) {
				if ( in_array($pages[$i]->ID, $excludes) )
					unset($pages[$i]);
			}
		}
	
		$cache[ $key ] = $pages;
		wp_cache_set( 'get_pages', $cache, 'posts' );
	
		$pages = apply_filters('get_pages', $pages, $r);
	
		return $pages;
	}
	
}

function page_part_custom_post_type() {
	
	// Magazine Post Type
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
add_action( 'init', 'page_part_custom_post_type' );

function page_part_updated_messages( $messages ) {
	
	global $post, $post_ID;
	
	$messages['page-part'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Page Part updated. <a href="%s">View page part</a>' ), esc_url( get_permalink( $post_ID ) ) ),
		2  => __( 'Custom field updated.' ),
		3  => __( 'Custom field deleted.' ),
		4  => __( 'Page Part updated.' ),
		// translators: %s: date and time of the revision
		5  => isset( $_GET['revision'] ) ? sprintf( __('Page Part restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
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
add_filter( 'post_updated_messages', 'page_part_updated_messages' );

function page_part_contextual_help( $contextual_help, $screen_id, $screen ) { 
	//$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
	if ( 'page-part' == $screen->id ) {
		$contextual_help =
			'<p>' . __( 'Things to remember when adding or editing a page part:' ) . '</p>' .
			'<p>Not a lot.</p>';
	} elseif ( 'edit-page-part' == $screen->id ) {
		$contextual_help = '<p>' . __('No page part documentation.') . '</p>';
	}
	return $contextual_help;
}
add_action( 'contextual_help', 'page_part_contextual_help', 10, 3 );

function page_part_post_type_link( $post_link, $post, $leavename, $sample ) {
	if ( $post->post_type == 'page-part' && $post->post_parent > 0 ) {
		$post_link = get_permalink( $post->post_parent ) . '#' . $post->post_name;
	}
	return apply_filters( 'page_part_post_type_link', $post_link );
}
add_filter( 'post_type_link', 'page_part_post_type_link', 10, 4 );



?>
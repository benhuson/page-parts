<?php

class Page_Parts_Admin {
	
	/**
	 * Constructor
	 */
	function Page_Parts_Admin() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_page_parts_order' ) );
		add_action( 'save_post', array( $this, 'save_page_part_parent' ) );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 5, 2 );
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
		add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );
		add_filter( 'manage_edit-page-part_columns', array( $this, 'manage_edit_page_part_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}
	
	/**
	 * Manage Page Part Columns
	 */
	function manage_edit_page_part_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $column => $value ) {
			$new_columns[$column] = $value;
			if ( $column == 'title' ) {
				$new_columns['parent'] = 'Parent Page';
			}
		}
		return $new_columns;
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
	 * Admin Head
	 */
	function admin_head() {
		echo '
<style>
#page_parts .wp-list-table .media-icon img {
	max-width:80px;
	max-height:60px;
}
</style>
			';
		echo "
<script type=\"text/javascript\">
function sortPageParts() {
}
jQuery(function($) {
	$('#page_parts table.wp-list-table tbody').sortable( {
		accept: 'sortable',
		stop: function(event, ui) {
			var order_count = 0;
			$('#page_parts table.wp-list-table td.order input').each(function(){
				$(this).val(order_count);
				order_count++;
			});
		}
	} );
});
</script>
		";
	}
	
	/**
	 * Admin Enqueue Scripts
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_script( array( 'jquery', 'jquery-ui-core', 'interface', 'jquery-ui-sortable', 'wp-lists' ) );
	}
	
	/**
	 * Admin Menu
	 */
	function admin_menu() {
		if ( function_exists( 'add_meta_box' ) ) {
			add_meta_box( 'page_parts', 'Page Parts', array( 'Page_Parts_Admin', 'page_parts_meta_box' ), 'page', 'advanced' );
		}
	}
	
	/**
	 * Save Page Parts Order
	 */
	function save_page_parts_order( $post_id ) {
		global $wpdb;
		
		// Verify if this is an auto save routine. If it is our form has not been submitted,
		// so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		if ( empty( $_POST ) || ! isset( $_POST['_ajax_nonce-order-page-parts'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce-order-page-parts'], 'order_page_parts' ) ) {
			return $post_id;
		}
		
		// OK, we're authenticated: we need to find and save the data
		if ( isset( $_POST['page_parts_order'] ) && is_array( $_POST['page_parts_order'] ) ) {
			foreach ( $_POST['page_parts_order'] as $key => $val ) {
				if ( absint( $key ) > 0 ) {
					$wpdb->update( $wpdb->posts, array( 'menu_order' => absint( $val ) ), array( 'ID' => absint( $key ) ), array( '%d' ), array( '%d' ) );
				}
			}
		}
		
		return $_POST;
	}
	
	/**
	 * Save Page Part Parent
	 */
	function save_page_part_parent( $post_id ) {
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
	 * Page Parts Meta Box
	 */
	function page_parts_meta_box() {
		global $post, $wp_query;
		
		$temp_post = clone $post;
		
		echo '<p><a href="post-new.php?post_type=page-part&parent_id=' . $post->ID . '">Add new page part</a></p>';
		
		$temp_query = new WP_Query( array(
			'post_type'      => 'page-part',
			'post_parent'    => $post->ID,
			'post_status'    => 'publish,pending,draft,auto-draft,future,private,trash',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => -1
		) );
		
		echo '<table class="wp-list-table widefat fixed pages" cellspacing="0" style="margin:5px 0;">
			<thead>
				<tr>
					<th scope="col" id="preview" class="manage-column column-title desc" style="width:50px;"></th>
					<th scope="col" id="title" class="manage-column column-title desc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Title</div></th>
					<th scope="col" id="order" class="manage-column column-author desc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Order</div></th>
					<th scope="col" id="date" class="manage-column column-date asc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Status</div></th>
				</tr>
			</thead>
			<tbody id="the-list">';
	
		while ( $temp_query->have_posts() ) : $temp_query->the_post();
			echo '<tr id="post-2" class="sortable alternate author-self status-publish format-default iedit" valign="top">
				<td class="column-icon media-icon" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">';
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( array( 80, 60 ) );
			}
			echo '
				</td>
				<td class="post-title page-title column-title" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;"><strong class="row-title">';
					edit_post_link( get_the_title(), null, null, $post->ID );
					echo '</strong>
				</td>
				<td class="order column-author" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">
					<input name="page_parts_order[' . $post->ID . ']" type="text" size="4" id="page_parts_order[' . $post->ID . ']" value="' . $post->menu_order . '">
				</td>
				<td class="date column-date" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">' . get_post_status( $post->ID ) . '</td>
			</tr>';
		endwhile;
		
		echo '</tbody></table>';
		
		echo '<input type="submit" name="orderpageparts" id="orderpagepartssub" class="button" value="Order Page Parts">';
		wp_nonce_field( 'order_page_parts', '_ajax_nonce-order-page-parts' );
		
		wp_reset_postdata();
		rewind_posts();
		$post = clone $temp_post;
	}
	
	/**
	 * Don't do plugin update notifications
	 * props. Mark Jaquith
	 */
	function http_request_args( $r, $url ) {
		if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
			return $r; // Not a plugin update request. Bail immediately.
		$plugins = unserialize( $r['body']['plugins'] );
		unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
		unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
		$r['body']['plugins'] = serialize( $plugins );
		return $r;
	}
	
}

?>
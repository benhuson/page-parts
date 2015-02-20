<?php

class Page_Parts_Admin {

	/**
	 * Constructor
	 */
	public function Page_Parts_Admin() {
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_page_parts' ) );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 5, 2 );
		add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );
		add_filter( 'manage_edit-page-part_columns', array( $this, 'manage_edit_page_part_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_ajax_page_parts_dragndrop_order', array( $this, 'dragndrop_order_ajax_callback' ) );
		add_action( 'wp_ajax_page_parts_location', array( $this, 'location_ajax_callback' ) );
		add_filter( 'post_updated_messages', array( $this, 'page_part_updated_messages' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'admin_menu', array( $this, 'add_documentation_page' ) );
	}

	/**
	 * Manage Page Part Columns
	 *
	 * @param   array  $columns  Key/value pairs of columns.
	 * @return  array            List of columns.
	 */
	public function manage_edit_page_part_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $column => $value ) {
			$new_columns[ $column ] = $value;
			if ( $column == 'title' ) {
				$new_columns['parent'] = __( 'Parent Page', PAGE_PARTS_TEXTDOMAIN );
			}
		}
		return $new_columns;
	}

	/**
	 * Manage Page Part Columns Output
	 *
	 * @param  string  $name  Current column name.
	 */
	public function manage_posts_custom_column( $name ) {
		global $post;

		switch ( $name ) {
			case 'parent' :
				edit_post_link( get_the_title( $post->post_parent ), null, null, $post->post_parent );
		}
	}

	/**
	 * Add Meta Boxes
	 */
	public function add_meta_boxes() {

		global $Page_Parts;

		$post_types = $Page_Parts->supported_post_types();

		foreach ( $post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {

				add_meta_box(
					'page_parts',
					__( 'Page Parts', PAGE_PARTS_TEXTDOMAIN ),
					array( $this, 'page_parts_meta_box' ),
					$post_type,
					'advanced'
				);

			}
		}

		add_meta_box(
			'page_parts_parent',
			__( 'Parent Page', PAGE_PARTS_TEXTDOMAIN ), 
			array( $this, 'parent_meta_box' ),
			'page-part',
			'side',
			'core'
		);
	}

	/**
	 * Add Parent Meta Box
	 */
	public function parent_meta_box() {

		global $post;

		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'page_parts_noncename' );

		if ( empty( $post->post_parent ) && isset( $_REQUEST['parent_id'] ) ) {
			$post->post_parent = $_REQUEST['parent_id'];
		}

		$post->post_parent = absint( $post->post_parent );

		// Handle pages (hierarchical) and non-hierarchical post types
		if ( 0 == $post->post_parent || is_post_type_hierarchical( get_post_type( $post->post_parent ) ) ) {

			$args = array(
				'selected'    => absint( $post->post_parent ),
				'echo'        => 0,
				'name'        => 'parent_id',
				'sort_order'  => 'ASC',
				'sort_column' => 'menu_order,post_title',
				'post_type'   => get_post_type( $post->post_parent ),
				'post_status' => 'publish,draft'
			);
			echo '<p>' . wp_dropdown_pages( $args ) . '</p>';

		} else {

			echo '<p>' . get_the_title( $post->post_parent ) . '</p>';
			echo '<input type="hidden" name="parent_id" value="' . absint( $post->post_parent ) . '" />';

		}

		echo '<p>';
		printf( '<a class="post-edit-link button button-small" href="%s">%s</a> ', esc_url( get_edit_post_link( $post->post_parent ) ), __( 'Edit parent', PAGE_PARTS_TEXTDOMAIN ) );
		printf( '<a class="button button-small button-primary" href="post-new.php?post_type=page-part&parent_id=%s" class="button button-primary">%s</a>', $post->post_parent, __( 'Add new page part', PAGE_PARTS_TEXTDOMAIN ) );
		echo '</p>';

	}

	/**
	 * Contextual Help
	 *
	 * @param   string  $contextual_help  Contextual help HTML.
	 * @param   string  $screen_id        Screen ID.
	 * @param   object  $screen           Screen object.
	 * @return  string                    HTML output.
	 */
	public function contextual_help( $contextual_help, $screen_id, $screen ) { 

		//$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id

		if ( 'page-part' == $screen->id ) {

			// Edit Page Part
			$contextual_help =
				'<p>' . __( 'By default you can only associate a new page part with a page.', PAGE_PARTS_TEXTDOMAIN ) . '</p>' .
				'<p>' . __( 'If additional post types are supported you must create the new page part by editing the post. Once a page part is associated with a page (or post type) the "Parent Page" panel with allow you to change the parent via a dropdown menu for hierarchical post types. There is not yet the option to re-associate a page part with a different parent for non-hierarchical post types.', PAGE_PARTS_TEXTDOMAIN ) . '</p>';

		} elseif ( 'edit-page-part' == $screen->id ) {

			// Page Parts Admin Table
			$contextual_help = '<p>' . __( 'Page parts allow you to add extra content relating to a page.', PAGE_PARTS_TEXTDOMAIN ) . '</p>'
				. '<p>' . __( 'Click on a page part parent to edit the associated page and view that page\'s other page parts.', PAGE_PARTS_TEXTDOMAIN ) . '</p>';

		}

		return $contextual_help;

	}

	/**
	 * Admin Head
	 */
	public function admin_head() {
		?>

		<style>

		#page_parts table.wp-list-table.page-parts {
			position: relative;
			table-layout: fixed;
		}
		#page_parts table.wp-list-table.page-parts .column-preview img {
			max-width: 50px;
			max-height: 50px;
		}
		#page_parts table.wp-list-table.page-parts .column-location {
			width: 140px;
		}
			#page_parts table.wp-list-table.page-parts .column-location select {
				width: 100%;
			}
		#page_parts table.wp-list-table.page-parts .column-status {
			width: 90px;
		}
		.js #page_parts table.wp-list-table.page-parts .column-order {
			text-align: center;
		}
		.js #page_parts table.wp-list-table.page-parts td.column-order .handle {
			cursor: move;
			display: inline-block;
			font-size: 18px;
			line-height: 22px;
			opacity: .6;
			width: 50px;
			height: 22px;
		}
		.js #page_parts table.wp-list-table.page-parts td.column-order .spinner {
			display: none;
			float: none;
		}
		.js #page_parts table.wp-list-table.page-parts td.column-order .handle:hover {
			opacity: 1;
		}
		.js #page_parts table.wp-list-table.page-parts .sortable-placeholder {
			background-color: #fffbcc;
			height: 60px;
		}
		.js #page_parts table.wp-list-table.page-parts .ui-sortable-helper {
			display: block;
		}
		.js #page_parts input#orderpagepartssub {
			display: none;
		}
		</style>

		<script type="text/javascript">
		jQuery( function( $ ) {
			var pagePartsTable = $( '#page_parts table.wp-list-table.page-parts' );
			$( '#page_parts table.wp-list-table tbody' ).sortable( {
				accept               : 'sortable',
				axis                 : 'y',
				containment          : 'parent',
				forceHelperSize      : true,
				forcePlaceholderSize : true,
				handle               : '.handle',
				placeholder          : 'sortable-placeholder',
				stop                 : function( event, ui ) {
					var order_count = 0;
					pagePartsTable.find( 'tr' ).removeClass( 'alternate' );
					pagePartsTable.find( 'tr:odd' ).addClass( 'alternate' );
					pagePartsTable.find( 'td.order input' ).each( function() {
						$( this ).val( order_count );
						order_count++;
					} );
				},
				update               : function( event, ui ) {
					ui.item.find( '.column-order .spinner' ).css( 'display', 'inline-block' );
					ui.item.find( '.column-order .handle' ).hide();
					var data = {
						action    : 'page_parts_dragndrop_order',
						pageParts : $( '#page_parts table.wp-list-table tbody' ).sortable( 'toArray' ),
						ajaxNonce : '<?php echo wp_create_nonce( "order_page_parts" ); ?>'
					};
					$.post( ajaxurl, data, function( response ) {
						setTimeout( function() {
							pagePartsTable.find( '.column-order .spinner' ).css( 'display', 'none' );
							pagePartsTable.find( '.column-order .handle' ).show();
						}, 400 );
					});
				}
			} );
			pagePartsTable.find( 'tbody td.column-order' ).append( '<span class="handle dashicons dashicons-menu"></span><span class="spinner"></span>' );
			pagePartsTable.find( 'tbody td.column-order input' ).css( 'display', 'none' );

			// Page Part Location Menu
			$( '#page_parts table.wp-list-table tbody .column-location select' ).on( 'change', function( e ) {

				var name = $( this ).attr( 'name' );
				var id = name.substr( 20, name.length - 21 );
				var val = $( this ).val();

				$( this ).closest( 'tr' ).find( '.column-order .spinner' ).css( 'display', 'inline-block' );
				$( this ).closest( 'tr' ).find( '.column-order .handle' ).hide();
				var data = {
					action    : 'page_parts_location',
					post_id   : id,
					location  : val,
					ajaxNonce : '<?php echo wp_create_nonce( "page_parts_location" ); ?>'
				};
				$.post( ajaxurl, data, function( response ) {
					setTimeout( function() {
						pagePartsTable.find( '.column-order .spinner' ).css( 'display', 'none' );
						pagePartsTable.find( '.column-order .handle' ).show();
					}, 400 );
				});

			} );

		} );
		</script>

		<?php
	}

	/**
	 * Admin Enqueue Scripts
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( array( 'jquery', 'jquery-ui-core', 'interface', 'jquery-ui-sortable', 'wp-lists' ) );
	}

	/**
	 * Drag 'n' Drop Order AJAX Callback
	 */
	public function dragndrop_order_ajax_callback() {
		global $wpdb;

		// Get array of page part IDs in new order
		$page_parts = array();
		if ( isset( $_POST['pageParts'] ) && is_array( $_POST['pageParts'] ) ) {
			foreach ( $_POST['pageParts'] as $page_part ) {
				$page_parts[] = (int) str_replace( 'page-part-', '', $page_part );
			}
		}

		// Default response
		$response = array(
			'error'       => '',
			'errorIDs'    => array(),
			'message'     => '',
			'pagePartIDs' => $page_parts
		);

		// Update page part orders
		$failed = array();

		if ( count( $page_parts ) > 0 && isset( $_POST['ajaxNonce'] ) && wp_verify_nonce( $_POST['ajaxNonce'], 'order_page_parts' ) ) {

			foreach ( $page_parts as $order => $page_part_id ) {
				$result = $wpdb->update(
					$wpdb->posts,
					array( 'menu_order' => $order ),
					array( 'ID' => $page_part_id ),
					array( '%d' ),
					array( '%d' )
				);
				if ( $result == 0 ) {
					$failed[] = $page_part_id;
				}
			}

		} else {
			$failed[] = 0;
		}

		// Log failed updates
		if ( ! empty( $failed ) ) {
			$response['error'] = __( 'Unable to save the page part sort order. Please try again.', PAGE_PARTS_TEXTDOMAIN );
			$response['errorIDs'] = $failed;
			$error = new WP_Error( 'page_parts_ajax_save_order', $response['error'], $response['errorIDs'] );
		}

		// Response
		echo json_encode( $response );
		die();
	}

	/**
	 * Location AJAX Callback
	 */
	public function location_ajax_callback() {

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$location = isset( $_POST['location'] ) ? $_POST['location'] : '';

		$updated = false;

		// Default response
		$response = array(
			'error'       => '',
			'errorIDs'    => array(),
			'message'     => '',
			'post_id'     => $post_id
		);

		if ( isset( $_POST['ajaxNonce'] ) && wp_verify_nonce( $_POST['ajaxNonce'], 'page_parts_location' ) ) {
			if ( $post_id > 0 ) {
				$updated = update_post_meta( $post_id, '_page_part_location', sanitize_key( $location ) );
			}
		}

		// Log failed updates
		if ( ! $updated ) {
			$response['error'] = __( 'Unable to update the page part location.', PAGE_PARTS_TEXTDOMAIN );
			$response['errorIDs'] = $post_id;
			$error = new WP_Error( 'page_parts_ajax_save_order', $response['error'], $response['errorIDs'] );
		}

		// Response
		echo json_encode( $response );
		die();
	}

	/**
	 * Save Page Parts
	 *
	 * @param  int  $post_id  Post ID.
	 */
	public function save_page_parts( $post_id ) {
		global $wpdb;

		// Verify if this is an auto save routine. If it is our form has not been submitted,
		// so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Save page part parent?
		if ( isset( $_POST['page_parts_noncename'] ) && wp_verify_nonce( $_POST['page_parts_noncename'], plugin_basename( __FILE__ ) ) ) {
			if ( isset( $_POST['parent_id'] ) && current_user_can( 'edit_page', $post_id ) ) {

				$parent_id = absint( $_POST['parent_id'] );
				$post_type_object = get_post_type_object( get_post_type( $parent_id ) );

				if ( 0 == $parent_id || ( $post_type_object && current_user_can( $post_type_object->cap->edit_post, $post_id ) ) ) {
					$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_id ), array( 'ID' => $post_id ) );
				}

			}
		}

		// Save page parts location
		if ( isset( $_POST['_ajax_nonce-page-parts-location'] ) && wp_verify_nonce( $_POST['_ajax_nonce-page-parts-location'], 'page_parts_location' ) ) {
			if ( isset( $_POST['page_parts_location'] ) && is_array( $_POST['page_parts_location'] ) ) {
				foreach ( $_POST['page_parts_location'] as $key => $val ) {
					if ( absint( $key ) > 0 ) {
						update_post_meta( $key, '_page_part_location', $val );
					}
				}
			}
		}

		// Save page parts order
		if ( isset( $_POST['_ajax_nonce-order-page-parts'] ) && wp_verify_nonce( $_POST['_ajax_nonce-order-page-parts'], 'order_page_parts' ) ) {
			if ( isset( $_POST['page_parts_order'] ) && is_array( $_POST['page_parts_order'] ) ) {
				foreach ( $_POST['page_parts_order'] as $key => $val ) {
					if ( absint( $key ) > 0 ) {
						$wpdb->update( $wpdb->posts, array( 'menu_order' => absint( $val ) ), array( 'ID' => absint( $key ) ), array( '%d' ), array( '%d' ) );
					}
				}
			}
		}
	}

	/**
	 * Page Parts Meta Box
	 */
	public function page_parts_meta_box() {
		global $post;
		?>

		<style type="text/css">
		.wp-list-table.page-parts {
			margin: 5px 0;
		}
		.wp-list-table.page-parts .column-preview {
			width: 50px;
		}
		.wp-list-table.page-parts .column-order {
			width: 65px;
		}
		.wp-list-table.page-parts .column-order input {
			width: 100%;
		}
		.wp-list-table.page-parts tbody .column-preview {
		}
		.wp-list-table.page-parts tbody .column-preview img {
			display: block;
			height: auto;
			max-width: 100%;
		}
		.wp-list-table.page-parts tr.ui-sortable-helper {
			background-color: rgba( 255, 255, 255, .9 ) !important;
			border: 1px solid #e5e5e5;
		}
		</style>

		<?php
		require_once( dirname( __FILE__ ) . '/page-parts-list-table.php' );

		$wp_list_table = new Page_Parts_List_Table();
		$wp_list_table->prepare_items();
		$wp_list_table->display();
		?>

		<p>
			<a href="post-new.php?post_type=page-part&parent_id=<?php echo $post->ID ?>" class="button button-primary"><?php _e( 'Add new page part', PAGE_PARTS_TEXTDOMAIN ); ?></a>
			<input type="submit" name="orderpageparts" id="orderpagepartssub" class="button" value="<?php _e( 'Save page parts', PAGE_PARTS_TEXTDOMAIN ); ?>">
		</p>

		<?php wp_nonce_field( 'order_page_parts', '_ajax_nonce-order-page-parts' ); ?>
		<?php wp_nonce_field( 'page_parts_location', '_ajax_nonce-page-parts-location' ); ?>

		<?php
	}

	/**
	 * Don't do plugin update notifications
	 * props. Mark Jaquith
	 */
	public function http_request_args( $r, $url ) {
		if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
			return $r; // Not a plugin update request. Bail immediately.
		}
		$plugins = unserialize( $r['body']['plugins'] );
		unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
		unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
		$r['body']['plugins'] = serialize( $plugins );
		return $r;
	}

	/**
	 * Page Part Updated Messages
	 *
	 * @param   array  $messages  Array of post type messages.
	 * @return  array             Array of messages.
	 */
	public static function page_part_updated_messages( $messages ) {

		global $post, $post_ID;

		$messages['page-part'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Page part updated. <a href="%s">View page part</a> / <a href="%s">Edit parent</a>', PAGE_PARTS_TEXTDOMAIN ), esc_url( get_permalink( $post_ID ) ), esc_url( get_edit_post_link( $post->post_parent ) . '#page_parts' ) ),
			2  => __( 'Custom field updated.', PAGE_PARTS_TEXTDOMAIN ),
			3  => __( 'Custom field deleted.', PAGE_PARTS_TEXTDOMAIN ),
			4  => __( 'Page part updated.', PAGE_PARTS_TEXTDOMAIN ),
			// translators: %s: date and time of the revision
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Page part restored to revision from %s', PAGE_PARTS_TEXTDOMAIN ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Page part published. <a href="%s">View page part</a> / <a href="%s">Edit parent</a>', PAGE_PARTS_TEXTDOMAIN ), esc_url( get_permalink( $post_ID ) ), esc_url( get_edit_post_link( $post->post_parent ) . '#page_parts' ) ),
			7  => __( 'Page part saved.', PAGE_PARTS_TEXTDOMAIN ),
			8  => sprintf( __( 'Page part submitted. <a target="_blank" href="%s">Preview page part</a>', PAGE_PARTS_TEXTDOMAIN ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf( __( 'Page part scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page part</a>', PAGE_PARTS_TEXTDOMAIN ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', PAGE_PARTS_TEXTDOMAIN ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Page part draft updated. <a target="_blank" href="%s">Preview page part</a>', PAGE_PARTS_TEXTDOMAIN ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
		return $messages;
	}

	/**
	 * Plugin Row Meta
	 *
	 * Adds documentation link below the plugin description on the plugins page.
	 *
	 * @since  0.5
	 *
	 * @param   array   $plugin_meta  Plugin meta display array.
	 * @param   string  $plugin_file  Plugin reference.
	 * @return  array                 Plugin meta array.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( plugin_basename( PAGE_PARTS_FILE ) == $plugin_file ) {
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=page-parts-documentation' ), __( 'Documentation', PAGE_PARTS_TEXTDOMAIN ) );
		}

		return $plugin_meta;

	}

	/**
	 * Add Documentation Page
	 *
	 * @since  0.5
	 */
	public function add_documentation_page() {

		add_submenu_page( null, __( 'Page Parts Documentation', PAGE_PARTS_TEXTDOMAIN ), __( 'Page Parts Documentation', PAGE_PARTS_TEXTDOMAIN ), 'manage_options', 'page-parts-documentation', array( $this, 'documentation_page' ) );

	}

	/**
	 * Documentation Page
	 *
	 * @since  0.5
	 */
	public function documentation_page() {

		include( dirname( __FILE__ ) . '/documentation.php' );

	}

}

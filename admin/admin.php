<?php

class Page_Parts_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'add_post_type_part_column' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_page_parts' ) );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 5, 2 );
		add_action( 'load-post.php', array( $this, 'add_help_tabs' ) );
		add_action( 'load-edit.php', array( $this, 'add_help_tabs' ) );
		add_filter( 'manage_edit-page-part_columns', array( $this, 'manage_edit_page_part_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'wp_ajax_page_parts_dragndrop_order', array( $this, 'dragndrop_order_ajax_callback' ) );
		add_action( 'wp_ajax_page_parts_location', array( $this, 'location_ajax_callback' ) );
		add_action( 'wp_ajax_page_parts_template', array( $this, 'template_ajax_callback' ) );
		add_filter( 'post_updated_messages', array( $this, 'page_part_updated_messages' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'admin_menu', array( $this, 'add_documentation_page' ) );
	}

	/**
	 * Add post type page part admin columns
	 *
	 * @since  0.8
	 * @internal
	 */
	public function add_post_type_part_column() {

		global $Page_Parts;

		if ( is_admin() && function_exists( 'get_current_screen' ) ) {

			$current_screen = get_current_screen();

			if ( 'edit' == $current_screen->base ) {

				$post_types = $Page_Parts->supported_post_types();

				if ( in_array( $current_screen->post_type, $post_types ) ) {

					add_filter( 'manage_edit-' . $current_screen->post_type . '_columns', array( $this, 'manage_post_type_part_column' ) );
					add_action( 'manage_' . $current_screen->post_type . '_posts_custom_column', array( $this, 'manage_post_type_part_column_content' ), 10, 2 );

				}

			}

		}

	}

	/**
	 * Add post type page part admin column.
	 *
	 * @since  0.8
	 * @internal
	 *
	 * @param   array  $columns  Columns
	 * @return  array            Updated columns.
	 */
	public function manage_post_type_part_column( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column => $value ) {
			$new_columns[ $column ] = $value;
			if ( 'title' == $column ) {
				$new_columns['page_parts'] = __( 'Page Parts', 'page-parts' );
			}
		}

		return $new_columns;

	}

	/**
	 * Add post type page part admin column content.
	 *
	 * @since  0.8
	 * @internal
	 *
	 * @param  string  $column_name  Column name.
	 * @param  int     $post_id      Post ID
	 */
	public function manage_post_type_part_column_content( $column_name, $post_id ) {

		if ( $column_name == 'page_parts' ) {

			$page_parts = new WP_Query( array(
				'nopaging'       => true,
				'order'          => 'ASC',
				'orderby'        => 'menu_order',
				'post_parent'    => $post_id,
				'post_type'      => 'page-part',
				'posts_per_page' => -1
			) );

			if ( $page_parts->have_posts() ) {

				$page_parts_count = sprintf( _n( '%s Page Part', '%s Page Parts', $page_parts->found_posts, 'page-parts' ), $page_parts->found_posts );

				echo '<div class="post-type-page-part-column">';
				printf( '<span class="dashicons-before dashicons-arrow-right page-parts-count">%s</span><br />', esc_html( $page_parts_count ) );
				echo '<ul>';
				while ( $page_parts->have_posts() ) {
					$page_parts->the_post();
					printf( '<li><a href="%s">%s</a></li>', esc_url( get_edit_post_link( get_the_ID() ) ), esc_html( get_the_title( get_the_ID(), '', '', false ) ) );
				}
				echo '</ul>';
				echo '</div>';
			}
			wp_reset_postdata();

		}

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
				$new_columns['page-part-parent'] = __( 'Parent', 'page-parts' );
				$new_columns['page-part-template'] = __( 'Template', 'page-parts' );
			}
		}
		return $new_columns;
	}

	/**
	 * Manage Page Part Columns Output
	 *
	 * @param  string  $name     Current column name.
	 * @param  int     $post_id  Current post ID.
	 */
	public function manage_posts_custom_column( $name, $post_id ) {

		global $post;

		if ( 'page-part' == get_post_type( $post_id ) ) {

			$ancestors = array_reverse( get_ancestors( $post_id, get_post_type( $post_id ) ) );

			switch ( $name ) {

				case 'page-part-parent' :
					$i = 0;
					foreach ( $ancestors as $ancestor ) {
						if ( $i > 0 ) {
							echo _x( ' &rarr; ', 'Admin hierarchy seperator', 'page-parts' );
						}
						edit_post_link( get_the_title( $ancestor ), null, null, $ancestor );
						$i++;
					}
					break;

				case 'page-part-template' :

					$page_part_template = new Page_Part_Template( $post_id );
					$name = $page_part_template->get_name();

					if ( $page_part_template->is_supported() ) {
						echo $name;
					} else {
						printf( '<del>%s</del>', esc_html( $name ) );
					}

					break;

			}

		}

	}

	/**
	 * Add Meta Boxes
	 *
	 * @param  string   $post_type  Post type.
	 * @param  WP_Post  $post       Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {

		global $Page_Parts;

		$post_types = $Page_Parts->supported_post_types();

		foreach ( $post_types as $type ) {
			if ( post_type_exists( $type ) ) {

				add_meta_box(
					'page_parts',
					__( 'Page Parts', 'page-parts' ),
					array( $this, 'page_parts_meta_box' ),
					$type,
					'advanced'
				);

			}
		}

		add_meta_box(
			'page_parts_parent',
			__( 'Parent Page', 'page-parts' ), 
			array( $this, 'parent_meta_box' ),
			'page-part',
			'side',
			'core'
		);

		/**
		 * Template Meta Box
		 */

		$templates = $Page_Parts->templates->get_page_part_templates( $post );

		if ( ! empty( $templates ) ) {

			add_meta_box(
				'page_parts_template',
				__( 'Page Part Template', 'page-parts' ), 
				array( $this, 'template_meta_box' ),
				'page-part',
				'side',
				'core'
			);

		}

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
		if ( $post->post_parent > 0 && is_post_type_hierarchical( get_post_type( $post->post_parent ) ) ) {

			$args = array(
				'selected'          => absint( $post->post_parent ),
				'echo'              => 0,
				'name'              => 'parent_id',
				'show_option_none'  => sprintf( '–– %s ––', __( 'No Parent', 'page-parts' ) ),
				'option_none_value' => 0,
				'sort_order'        => 'ASC',
				'sort_column'       => 'menu_order,post_title',
				'post_type'         => get_post_type( $post->post_parent ),
				'post_status'       => 'publish,draft,pending,private,future,trash'
			);
			echo '<p>' . wp_dropdown_pages( $args ) . '</p>';

		} else {

			echo '<p>';
			_e( 'Parent ID:', 'page-parts' );
			printf( ' <input type="text" name="parent_id" value="%s" class="small-text" />', $post->post_parent );
			echo '</p>';

		}

		echo '<p>';
		if ( $post->post_parent > 0 ) {
			printf( '<a class="post-edit-link button button-small" href="%s">%s</a> ', esc_url( get_edit_post_link( $post->post_parent ) ), __( 'Edit parent', 'page-parts' ) );
		}
		printf( '<a class="button button-small button-primary" href="post-new.php?post_type=page-part&parent_id=%s" class="button button-primary">%s</a>', $post->post_parent, __( 'Add new page part', 'page-parts' ) );
		echo '</p>';

	}

	/**
	 * Template Meta Box
	 *
	 * @since     1.0
	 * @internal
	 */
	public function template_meta_box() {

		global $post, $Page_Parts;

		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'page_parts_template_noncename' );

		$current_template = Page_Parts::get_page_part_template_slug( $post->ID );

		// Default Template
		if ( apply_filters( 'page_part_show_default_template', true, $post ) ) {
			$options = '<option value="">' . esc_html( $Page_Parts->templates->get_default_template_name() ) . '</option>';
		} else {
			$options = '';
		}

		$options .= $Page_Parts->templates->page_part_template_dropdown( $current_template, $post );

		echo '<select name="template" id="template">' . $options . '</select>';

		// Images

		$templates = $Page_Parts->templates->get_page_part_templates( $post->ID );
		$images = $Page_Parts->templates->get_page_part_template_images( $post );
		$image_grid = '';

		foreach ( $templates as $name => $template ) {

			if ( ! isset( $images[ $template ] ) ) {
				continue;
			}

			$class = $template == $current_template ? 'page-part-image selected' : 'page-part-image';
			$image_grid .= sprintf( '<img src="%s" width="80" height="50" alt="%s" title="%s" rel="%s" class="%s" />', esc_attr( $images[ $template ] ), esc_attr( $name ), esc_attr( $name ), esc_attr( $template ), $class );

		}

		// If there are options...
		if ( ! empty( $image_grid ) ) {
			$class = empty( $current_template ) ? 'page-part-image selected' : 'page-part-image';
			$image_src = apply_filters( 'page_part_theme_default_template_image', plugins_url( 'images/templates/default.png', dirname( __FILE__ ) ) );
			$image_grid = '<div class="page-part-image-container"><img src="' . $image_src . '" width="80" height="50" alt="Remove Template..." title="Remove Template..." class="' . $class . '">' . $image_grid . '</div>';
			echo $image_grid;
		}


	}

	/**
	 * Add Help Tabs
	 *
	 * @internal  Private. Called via the `load-{page}` action.
	 */
	public function add_help_tabs() { 

		$screen = get_current_screen();

		if ( 'page-part' == $screen->id ) {

			// Edit Page Part
			$screen->add_help_tab( array(
				'id'      => 'page_parts_help_tab',
				'title'   => __( 'Page Part', 'page-parts' ),
				'content' => '<p>' . __( 'By default you can only associate a new page part with a page.', 'page-parts' ) . '</p>'
					. '<p>' . __( 'If additional post types are supported you must create the new page part by editing the post. Once a page part is associated with a page (or post type) the "Parent Page" panel with allow you to change the parent via a dropdown menu for hierarchical post types. There is not yet the option to re-associate a page part with a different parent for non-hierarchical post types.', 'page-parts' ) . '</p>',
			) );

		} elseif ( 'edit-page-part' == $screen->id ) {

			// Page Parts Admin Table
			$screen->add_help_tab( array(
				'id'      => 'page_part_help_tab',
				'title'   => __( 'Page Parts', 'page-parts' ),
				'content' => '<p>' . __( 'Page parts allow you to add extra content relating to a page.', 'page-parts' ) . '</p>'
					. '<p>' . __( 'Click on a page part parent to edit the associated page and view that page\'s other page parts.', 'page-parts' ) . '</p>',
			) );

		}

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
		#page_parts table.wp-list-table.page-parts .column-template {
			width: 150px;
		}
			#page_parts table.wp-list-table.page-parts .column-location select,
			#page_parts table.wp-list-table.page-parts .column-template select {
				white-space: nowrap;
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
			float: none;
			visibility: hidden;
		}
		.js #page_parts table.wp-list-table.page-parts td.column-order .spinner.is-active {
			display: inline-block;
			visibility: visible;
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
		.post-type-page-part-column .page-parts-count {
			cursor: pointer;
		}
		.post-type-page-part-column.show .page-parts-count::before {
			-webkit-transform: rotate( 90deg );
			-ms-transform: rotate( 90deg );
			transform: rotate( 90deg );
		}
		.post-type-page-part-column ul {
			display: none;
			margin: 0px;
			padding-left: 1.5em;
		}
		.post-type-page-part-column.show ul {
			display: block;
		}
		.post-type-page-part-column ul li {
			margin: 0px;
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
					ui.item.find( '.column-order .spinner' ).addClass( 'is-active' );
					ui.item.find( '.column-order .handle' ).hide();
					var data = {
						action    : 'page_parts_dragndrop_order',
						pageParts : $( '#page_parts table.wp-list-table tbody' ).sortable( 'toArray' ),
						ajaxNonce : '<?php echo wp_create_nonce( "order_page_parts" ); ?>'
					};
					$.post( ajaxurl, data, function( response ) {
						setTimeout( function() {
							pagePartsTable.find( '.column-order .spinner' ).removeClass( 'is-active' );
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

				$( this ).closest( 'tr' ).find( '.column-order .spinner' ).addClass( 'is-active' );
				$( this ).closest( 'tr' ).find( '.column-order .handle' ).hide();
				var data = {
					action    : 'page_parts_location',
					post_id   : id,
					location  : val,
					ajaxNonce : '<?php echo wp_create_nonce( "page_parts_location" ); ?>'
				};
				$.post( ajaxurl, data, function( response ) {
					setTimeout( function() {
						pagePartsTable.find( '.column-order .spinner' ).removeClass( 'is-active' );
						pagePartsTable.find( '.column-order .handle' ).show();
					}, 400 );
				});

			} );

			// Page Part Template Menu
			$( '#page_parts table.wp-list-table tbody .column-template select' ).on( 'change', function( e ) {

				var name = $( this ).attr( 'name' );
				var id = name.substr( 20, name.length - 21 );
				var val = $( this ).val();

				$( this ).closest( 'tr' ).find( '.column-order .spinner' ).addClass( 'is-active' );
				$( this ).closest( 'tr' ).find( '.column-order .handle' ).hide();
				var data = {
					action    : 'page_parts_template',
					post_id   : id,
					template  : val,
					ajaxNonce : '<?php echo wp_create_nonce( "page_parts_template" ); ?>'
				};
				$.post( ajaxurl, data, function( response ) {
					setTimeout( function() {
						pagePartsTable.find( '.column-order .spinner' ).removeClass( 'is-active' );
						pagePartsTable.find( '.column-order .handle' ).show();
					}, 400 );
				});

			} );

			$( document ).on( 'ready', function() {
				$( '.post-type-page-part-column .page-parts-count' ).on( 'click', function( e ) {
					$( this ).closest( '.post-type-page-part-column' ).toggleClass( 'show' );
				} );
			} );

		} );
		</script>

		<?php
	}

	/**
	 * Admin Enqueue Styles
	 */
	public function admin_enqueue_styles() {

		wp_enqueue_style( 'page-parts-admin', plugins_url( 'admin/css/admin.css', dirname( __FILE__ ) ) );

	}

	/**
	 * Admin Enqueue Scripts
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( array( 'jquery', 'jquery-ui-core', 'interface', 'jquery-ui-sortable', 'wp-lists' ) );
		wp_enqueue_script( 'page-parts-admin', plugins_url( 'admin/js/admin-post.js', dirname( __FILE__ ) ), 'jquery' );
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

				$result = 0;

				if ( ! wp_is_post_revision( $page_part_id ) ) {
					$result = wp_update_post( array(
						'ID'         => $page_part_id,
						'menu_order' => $order
					) );
				}

				if ( $result == 0 ) {
					$failed[] = $page_part_id;
				}

			}

		} else {
			$failed[] = 0;
		}

		// Log failed updates
		if ( ! empty( $failed ) ) {
			$response['error'] = __( 'Unable to save the page part sort order. Please try again.', 'page-parts' );
			$response['errorIDs'] = $failed;
			$error = new WP_Error( 'page_parts_ajax_save_order', $response['error'], $response['errorIDs'] );
		}

		// Response
		echo json_encode( $response );
		die();
	}

	/**
	 * Location AJAX Callback
	 *
	 * @since     1.0
	 * @internal
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
				if ( empty( $location ) ) {
					delete_post_meta( $post_id, '_page_part_location' );
				} else {
					$updated = update_post_meta( $post_id, '_page_part_location', sanitize_key( $location ) );
				}
			}
		}

		// Log failed updates
		if ( ! $updated ) {
			$response['error'] = __( 'Unable to update the page part location.', 'page-parts' );
			$response['errorIDs'] = $post_id;
			$error = new WP_Error( 'page_parts_ajax_save_location', $response['error'], $response['errorIDs'] );
		}

		// Response
		echo json_encode( $response );
		die();
	}

	/**
	 * Template AJAX Callback
	 *
	 * @since  1.0
	 */
	public function template_ajax_callback() {

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$template = isset( $_POST['template'] ) ? $_POST['template'] : '';

		$updated = false;

		// Default response
		$response = array(
			'error'       => '',
			'errorIDs'    => array(),
			'message'     => '',
			'post_id'     => $post_id
		);

		if ( isset( $_POST['ajaxNonce'] ) && wp_verify_nonce( $_POST['ajaxNonce'], 'page_parts_template' ) ) {
			if ( $post_id > 0 ) {
				$updated = update_post_meta( $post_id, '_page_part_template', sanitize_text_field( $template ) );
			}
		}

		// Log failed updates
		if ( ! $updated ) {
			$response['error'] = __( 'Unable to update the page part template.', 'page-parts' );
			$response['errorIDs'] = $post_id;
			$error = new WP_Error( 'page_parts_ajax_save_template', $response['error'], $response['errorIDs'] );
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

		// Don't save changes to revisions
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// Verify if this is an auto save routine. If it is our form has not been submitted,
		// so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Save page part parent?
		if ( isset( $_POST['page_parts_noncename'] ) && wp_verify_nonce( $_POST['page_parts_noncename'], plugin_basename( __FILE__ ) ) ) {
			if ( isset( $_POST['parent_id'] ) && current_user_can( 'edit_page', $post_id ) ) {

				// For WPML compatibility, check this is not a duplicate translation
				if ( ! $this->is_post_a_wpml_duplicate( $post_id ) ) {

					$parent_id = absint( $_POST['parent_id'] );
					$post_type_object = get_post_type_object( get_post_type( $parent_id ) );

					if ( 0 == $parent_id || ( $post_type_object && current_user_can( $post_type_object->cap->edit_post, $post_id ) ) ) {
						$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_id ), array( 'ID' => $post_id ) );
					}

				}

			}
		}

		// Save page part template?
		if ( isset( $_POST['page_parts_template_noncename'] ) && wp_verify_nonce( $_POST['page_parts_template_noncename'], plugin_basename( __FILE__ ) ) ) {
			if ( isset( $_POST['template'] ) && current_user_can( 'edit_page', $post_id ) ) {

				if ( empty( $_POST['template'] ) ) {
					delete_post_meta( $post_id, '_page_part_template' );
				} else {
					update_post_meta( $post_id, '_page_part_template', sanitize_text_field( $_POST['template'] ) );
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
			<a href="post-new.php?post_type=page-part&parent_id=<?php echo $post->ID ?>" class="button button-primary"><?php _e( 'Add new page part', 'page-parts' ); ?></a>
			<input type="submit" name="orderpageparts" id="orderpagepartssub" class="button" value="<?php _e( 'Save page parts', 'page-parts' ); ?>">
		</p>

		<?php wp_nonce_field( 'order_page_parts', '_ajax_nonce-order-page-parts' ); ?>
		<?php wp_nonce_field( 'page_parts_location', '_ajax_nonce-page-parts-location' ); ?>

		<?php
	}

	/**
	 * Check if post is a WPML duplicate.
	 * Used to prevent overriding post parent for duplicate translations.
	 *
	 * Note:
	 * WPML_ELEMENT_IS_NOT_TRANSLATED  = 0
	 * WPML_ELEMENT_IS_TRANSLATED      = 1
	 * WPML_ELEMENT_IS_DUPLICATED      = 2
	 * WPML_ELEMENT_IS_A_DUPLICATE     = 3
	 * 
	 * @param   integer  $post_id
	 * @return  boolean
	 */
	protected function is_post_a_wpml_duplicate( $post_id ) {

		$translation_type = apply_filters( 'wpml_element_translation_type', NULL, $post_id, get_post_type( $post_id ) );

		return 3 === $translation_type;

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
			1  => sprintf( __( 'Page part updated. <a href="%s">View page part</a> / <a href="%s">Edit parent</a>', 'page-parts' ), esc_url( get_permalink( $post_ID ) ), esc_url( get_edit_post_link( $post->post_parent ) . '#page_parts' ) ),
			2  => __( 'Custom field updated.', 'page-parts' ),
			3  => __( 'Custom field deleted.', 'page-parts' ),
			4  => __( 'Page part updated.', 'page-parts' ),
			// translators: %s: date and time of the revision
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Page part restored to revision from %s', 'page-parts' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Page part published. <a href="%s">View page part</a> / <a href="%s">Edit parent</a>', 'page-parts' ), esc_url( get_permalink( $post_ID ) ), esc_url( get_edit_post_link( $post->post_parent ) . '#page_parts' ) ),
			7  => __( 'Page part saved.', 'page-parts' ),
			8  => sprintf( __( 'Page part submitted. <a target="_blank" href="%s">Preview page part</a>', 'page-parts' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf( __( 'Page part scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page part</a>', 'page-parts' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'page-parts' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Page part draft updated. <a target="_blank" href="%s">Preview page part</a>', 'page-parts' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
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
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=page-parts-documentation' ), __( 'Documentation', 'page-parts' ) );
		}

		return $plugin_meta;

	}

	/**
	 * Add Documentation Page
	 *
	 * @since  0.5
	 */
	public function add_documentation_page() {

		add_submenu_page( null, __( 'Page Parts Documentation', 'page-parts' ), __( 'Page Parts Documentation', 'page-parts' ), 'manage_options', 'page-parts-documentation', array( $this, 'documentation_page' ) );

	}

	/**
	 * Documentation Page
	 *
	 * @since  0.5
	 */
	public function documentation_page() {

		include( dirname( __FILE__ ) . '/documentation/index.php' );

	}

}

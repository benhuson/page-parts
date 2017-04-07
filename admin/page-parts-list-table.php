<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Page Parts List Table
 */
class Page_Parts_List_Table extends WP_List_Table {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'page-part',  // singular name of the listed records
			'plural'   => 'page-parts', // plural name of the listed records
			'ajax'     => false   // does this table support ajax?
		) );
	}

	/**
	 * Get Columns
	 *
	 * @uses  apply_filters()  Calls page_parts_admin_columns allowing extra columns to be added to page parts table.
	 *
	 * @return  array  Column IDs and titles.
	 */
	public function get_columns() {

		global $Page_Parts;

		$columns = apply_filters( 'page_parts_admin_columns', array(
			'preview'  => '',
			'title'    => __( 'Title', 'page-parts' ),
			'location' => __( 'Theme Location', 'page-parts' ),
			'template' => __( 'Template', 'page-parts' ),
			'order'    => __( 'Order', 'page-parts' ),
		) );

		// Remove location column if no locations
		if ( isset( $_GET['post'] ) ) {
			$locations = $this->get_locations( get_post_type( absint( $_GET['post'] ) ) );
			if ( 0 == count( $locations ) ) {
				unset( $columns['location'] );
			}
		} else {
			unset( $columns['location'] );
		}

		// Remove template column if no templates
		if ( isset( $_GET['post'] ) ) {
			$templates = $Page_Parts->templates->get_page_part_templates( $_GET['post'] );
			if ( 0 == count( $templates ) ) {
				unset( $columns['template'] );
			}
		} else {
			unset( $columns['template'] );
		}

		// Santize column keys
		$order_column = null;
		$santized_columns = array();
		foreach ( $columns as $key => $label ) {
			if ( 'order' == $key ) {
				$order_column = $label;
			} else {
				$santized_columns[ sanitize_key( $key ) ] = $label;
			}
		}

		// Ensure order column is always last
		if ( ! is_null( $order_column ) ) {
			$santized_columns['order'] = $order_column;
		}

		return $santized_columns;
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @access  protected
	 *
	 * @param  object  $item  The current item
	 */
	public function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr id="page-part-' . $item->ID . '" ' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Handle column content.
	 *
	 * @uses  apply_filters()  Calls page_parts_admin_column_{$column_name} allowing extra column content to be added to page parts table.
	 *
	 * @param   object  $item         Post object.
	 * @param   string  $column_name  Column ID.
	 * @return  string                Column content.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'preview' :
				$value = $this->admin_column_preview( $item );
				break;
			case 'title' :
				$value = $this->admin_column_title( $item );
				break;
			case 'location' :
				$value = $this->admin_column_location( $item );
				break;
			case 'template' :
				$value = $this->admin_column_template( $item );
				break;
			default :
				$value = '';
		}
		return apply_filters( 'page_parts_admin_column_' . $column_name, $value, $item );
	}

	/**
	 * Image Preview Column.
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	public function admin_column_preview( $item ) {
		if ( has_post_thumbnail( $item->ID ) ) {
			return get_the_post_thumbnail( $item->ID, array( 80, 60 ) );
		}
		return '';
	}

	/**
	 * Title Column.
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	public function admin_column_title( $item ) {

		$post_title = get_the_title( $item );

		if ( empty( $post_title ) ) {
			$post_title = __( '(no title)' );
		}

		$title = '<a href="' . get_edit_post_link( $item ) . '">' . $post_title . '</a>';

		if ( ! in_array( $item->post_status, array( 'publish', 'inherit' ) ) ) {
			$title .= ' - <span class="post-state">' . $this->get_post_status_display( $item ) . '</span>';
		}
		return '<strong class="row-title">' . $title . '</strong>';
	}

	/**
	 * Location Column.
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	public function admin_column_location( $item ) {

		$locations = $this->get_locations( get_post_type( $item->post_parent ) );
		$location_value = get_post_meta( $item->ID, '_page_part_location', true );

		$options = '<option value="">–– ' . __( 'Default', 'page-parts' ) . ' ––</option>';
		foreach ( $locations as $key => $location ) {
			$options .= '<option value="' . $key . '"' . selected( $key, $location_value, false ) . '>' . esc_html( $location ) . '</option>';
		}

		return '<select name="page_parts_location[' . $item->ID . ']" id="page_parts_location[' . $item->ID . ']">' . $options . '</select>';

	}

	/**
	 * Template Column.
	 *
	 * @since  1.0
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	public function admin_column_template( $item ) {

		global $Page_Parts;

		$template = Page_Parts::get_page_part_template_slug( $item->ID );

		// Default Template
		if ( apply_filters( 'page_part_show_default_template', true, $item ) ) {
			$options = '<option value="">' . esc_html( $Page_Parts->templates->get_default_template_name() ) . '</option>';
		} else {
			$options = '';
		}

		$options .= $Page_Parts->templates->page_part_template_dropdown( $template, $item );

		return '<select name="page_parts_template[' . $item->ID . ']" id="page_parts_template[' . $item->ID . ']">' . $options . '</select>';

	}

	/**
	 * Get Locations.
	 *
	 * @param   string  $post_type  Post type.
	 * @return  string              Page part locations.
	 */
	public function get_locations( $post_type ) {

		$locations = array();
		$location_names = array_unique( apply_filters( 'page_parts_locations', array(), $post_type ) );

		foreach ( $location_names as $location_name ) {
			$locations[ sanitize_key( $location_name )  ] = $location_name;
		}

		return $locations;

	}

	/**
	 * Order Column.
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	public function column_order( $item ) {
		return '<input name="page_parts_order[' . $item->ID . ']" type="text" size="4" id="page_parts_order[' . $item->ID . ']" value="' . $item->menu_order . '" />';
	}

	/**
	 * Get Post Status Display
	 *
	 * @param   int     $post_id  Post ID.
	 * @return  string            Post status display.
	 */
	public function get_post_status_display( $post_id ) {
		$status = get_post_status( $post_id );
		switch ( $status ) {
			case 'private':
				$status = __( 'Privately Published' );
				break;
			case 'publish':
				$status = __( 'Published' );
				break;
			case 'future':
				$status = __( 'Scheduled' );
				break;
			case 'pending':
				$status = __( 'Pending Review' );
				break;
			case 'draft':
			case 'auto-draft':
				$status = __( 'Draft' );
				break;
		}
		return $status;
	}

	/**
	 * Display Table
	 */
	public function display() {
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
			<tbody id="the-list" data-wp-lists="list:page-part">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		global $post;

		$add_url = admin_url( sprintf( 'post-new.php?post_type=page-part&parent_id=%s', $post->ID ) );

		printf( __( 'No page parts found. <a %s>Add one?</a>', 'page-parts' ), 'href="' . $add_url . '"' );

	}

	/**
	 * Prepare Items
	 * Gets the data to display in the table.
	 */
	public function prepare_items() {
		$hidden = array();
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		if ( isset( $_GET['post'] ) ) {

			$this->items = get_posts( array(
				'order'          => 'ASC',
				'orderby'        => 'menu_order',
				'post_parent'    => absint( $_GET['post'] ),
				'post_status'    => 'all',
				'post_type'      => 'page-part',
				'posts_per_page' => -1
			) );

		} else {

			$this->items = array();

		}

		$total_items = count( $this->items );

		// REQUIRED. We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $total_items,
			'total_pages' => 1
		) );
	}

}

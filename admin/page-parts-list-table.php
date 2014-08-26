<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Page Parts List Table
 */
class Page_Parts_List_Table extends WP_List_Table {

	/**
	 * Constructor
	 */
	function __construct() {
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
	function get_columns() {
		$columns = apply_filters( 'page_parts_admin_columns', array(
			'preview' => '',
			'title'   => __( 'Title', 'page-parts' ),
			'order'   => __( 'Order', 'page-parts' ),
		) );

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
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'preview' :
				$value = $this->admin_column_preview( $item );
				break;
			case 'title' :
				$value = $this->admin_column_title( $item );
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
	function admin_column_preview( $item ) {
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
	function admin_column_title( $item ) {
		$title = '<a href="' . get_edit_post_link( $item ) . '">' . get_the_title( $item ) . '</a>';
		if ( ! in_array( $item->post_status, array( 'publish', 'inherit' ) ) ) {
			$title .= ' - <span class="post-state">' . $this->get_post_status_display( $item ) . '</span>';
		}
		return '<strong class="row-title">' . $title . '</strong>';
	}

	/**
	 * Order Column.
	 *
	 * @param   object  $item  Post object.
	 * @return  string         Column content.
	 */
	function column_order( $item ) {
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
	function display() {
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
	 * Prepare Items
	 * Gets the data to display in the table.
	 */
	function prepare_items() {
		$hidden = array();
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = get_posts( array(
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'post_parent'    => absint( $_GET['post'] ),
			'post_status'    => 'all',
			'post_type'      => 'page-part',
			'posts_per_page' => -1
		) );

		$total_items = count( $this->items );

		// REQUIRED. We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $total_items,
			'total_pages' => 1
		) );
	}

}
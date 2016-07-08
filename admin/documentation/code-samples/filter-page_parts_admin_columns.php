<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Fiter page_parts_admin_columns
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">
&lt;?php

/**
 * Add a column to display the page part ID
 *
 * @param   array  $columns  Columns.
 * @return  array            Updated columns.
 */
function my_page_parts_admin_columns( $columns ) {

	$columns['id'] = __( 'ID' );

	return $columns;

}

add_filter( 'page_parts_admin_columns', 'my_page_parts_admin_columns' );

?&gt;</textarea>

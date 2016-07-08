<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Fiter page_parts_admin_column_{$column_name}
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="17" wrap="off" style="width: 100%;" class="code">
&lt;?php

/**
 * Add a column to display the page part ID
 *
 * @param   array  $columns  Columns.
 * @return  array            Updated columns.
 */
function my_page_parts_admin_column_id( $value, $item ) {

	return $item->ID;

}

add_filter( 'page_parts_admin_column_id', 'my_page_parts_admin_column_id', 10, 2 );

?&gt;</textarea>

<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Filter page_parts_default_template_name
 *
 * @since  1.1
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="18" wrap="off" style="width: 100%;" class="code">
&lt;?php

/**
 * Change Default Template name
 *
 * @param   string  $name  Default Template name.
 * @return  string         Template name.
 */
function my_page_parts_default_template_name( $name ) {

	return __( 'My Default Template' );

}

add_filter( 'page_parts_default_template_name', 'my_page_parts_default_template_name' );

?&gt;</textarea>

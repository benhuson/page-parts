<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Fiter register_page_part_args
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
 * Hide Page Parts Admin Menu
 *
 * @param   array  $args  Post type args.
 * @return  array         Args.
 */
function my_register_page_part_args( $args ) {

	$args['show_in_menu'] = false;

	return $args;

}

add_filter( 'register_page_part_args', 'my_register_page_part_args' );

?&gt;</textarea>

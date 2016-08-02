<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Index
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Tabs
$tabs = array(
	'getting-started' => __( 'Getting Started', 'page-parts' ),
	'templates'       => __( 'Templates', 'page-parts' ),
	'locations'       => __( 'Locations', 'page-parts' ),
	'filters'         => __( 'Filters', 'page-parts' ),
	'examples'        => __( 'Examples', 'page-parts' )
);

// First Tab
$first_tab = current( array_keys( $tabs ) );

// Selected Tab
$tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? sanitize_key( $_GET['tab'] ) : $first_tab;

?>

<div class="wrap">

	<h1><?php esc_html_e( 'Page Parts Documentation', 'page-parts' ); ?></h1>

	<h2 class="nav-tab-wrapper wp-clearfix">
		<?php

		foreach ( $tabs as $tab_id => $tab_title ) {

			$href = $tab_id == $first_tab ? remove_query_arg( 'tab' ) : add_query_arg( 'tab', $tab_id );

			?>
			<a href="<?php echo $href; ?>" class="nav-tab <?php if ( $tab_id == $tab ) echo 'nav-tab-active'; ?>"><?php echo esc_html( $tab_title ); ?></a>
			<?php

		}

		?>
	</h2>

	<?php include( dirname( __FILE__ ) . "/{$tab}.php" ); ?>

</div>

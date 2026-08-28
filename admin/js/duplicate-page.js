( function( wp, settings ) {
	'use strict';

	if ( ! wp || ! settings || ! settings.url || ! wp.components || ! wp.editor || ! wp.element || ! wp.plugins || ! wp.editor.PluginPostStatusInfo ) {
		return;
	}

	wp.plugins.registerPlugin( 'page-parts-duplicate-page', {
		render: function() {
			return wp.element.createElement(
				wp.editor.PluginPostStatusInfo,
				{ className: 'page-parts-duplicate-page' },
				wp.element.createElement(
					wp.components.Button,
					{
						href: settings.url,
						variant: 'secondary'
					},
					settings.label
				)
			);
		}
	} );
}( window.wp, window.pagePartsDuplicatePage ) );


var PagePartsAdminPost;

( function( $ ) {

	PagePartsAdminPost = {

		init : function() {

			$( document ).ready( PagePartsAdminPost.onReady );

		},

		onReady : function() {

			$( '#page_parts_template .page-part-image' ).on( 'click', PagePartsAdminPost.onImageClick );
			$( '#page_parts_template select' ).on( 'change', PagePartsAdminPost.onMenuChange );

		},

		onMenuChange : function() {

			var template_id = $( '#page_parts_template select option:selected' ).val();

			if ( template_id ) {

				$image = $( '#page_parts_template .page-part-image[rel="' + template_id + '"]' );

				if ( $image.length ) {
					PagePartsAdminPost.setSelectedImage( $image );
				} else {
					PagePartsAdminPost.clearSelectedImages();
				}

			} else {

				PagePartsAdminPost.setSelectedImage( $( '#page_parts_template .page-part-image:first' ) );

			}

		},

		onImageClick : function( e ) {

			$image = $( this );

			PagePartsAdminPost.setSelectedImage( $image );

			var template_id = $image.attr( 'rel' );

			if ( template_id ) {
				PagePartsAdminPost.setSelectedMenuOption( $( '#page_parts_template select option[value="' + template_id + '"]' ) );
			} else {
				PagePartsAdminPost.setSelectedMenuOption( $( '#page_parts_template select option:first' ) );
			}

		},

		setSelectedMenuOption : function( $option ) {

			$option.attr( 'selected', 'selected' );

		},

		setSelectedImage : function( $image ) {

			$image.addClass( 'selected' ).siblings().removeClass( 'selected' );

		},

		clearSelectedImages : function() {

			$( '#page_parts_template .page-part-image' ).removeClass( 'selected' );

		}

	};

	PagePartsAdminPost.init();

} )( jQuery );

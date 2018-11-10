/**
 * Custom js for Ud Slider
 *
 * @package Ud Slider
 */

jQuery(
	function () {
		var images_array_JsonString = "";
		function updateslides() {
			var images_array = [];
			jQuery( "#uploaded_images_sortable" ).find( "li" ).each(
				function (index) {
					var imgsrc = jQuery( this ).find( 'img' ).attr( 'src' );
					images_array.push( imgsrc );
				}
			);
			images_array_JsonString = JSON.stringify( images_array );
			jQuery( "#ud_slider_images_options" ).val( "" );

			jQuery( "#ud_slider_images_options" ).val( images_array_JsonString );
		}

		jQuery( "#uploaded_images_sortable" ).sortable(
			{update: function (e, ui) {
				updateslides();
			}}
		);

		jQuery( "#uploaded_images_sortable" ).disableSelection();
		jQuery( "#uploaded_images_sortable .delete_slide" ).click(
			function () {
				jQuery( this ).parent().remove();
				updateslides();
			}
		);
	}
);

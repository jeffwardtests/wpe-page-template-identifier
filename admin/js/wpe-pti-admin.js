/**
* Admin JS will live here
*/
(function( $ ) {
	'use strict';
	$(document).ready(function() {
			
			///////////////////////////////////
			// Settings page JavaScript
			///////////////////////////////////
			$('button[name="revert"]').on('click', function(e){
					var conf = confirm("Are you sure you want to revert these settings?");
					if(conf != true) {
						e.preventDefault();
						return false;
					}
			});

	});
})( jQuery );

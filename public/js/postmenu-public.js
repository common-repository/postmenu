(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	window.LP_Scope = window.LP_Scope || {};

	LP_Scope.handleDuplicatePostFunction = function(postID, messaje){
		$(document).on( 'click', 
		'#wp-admin-bar-lion_pm_duplicate_admin_bar a:first', 
		function() {
			$.post(postmenu_ajax_url,
				{'action': 'postmenu_ajax_duplicate_post','id': postID},
				function(data){
					$('#page').prepend(
						'<div class="notification-messaje">' +
						'<p>' + messaje + '</p>' + '<button class="notification-dismiss">' + 
						'<span>Dismis this notice.</span></button></div>'
					);
				});
				setTimeout(function(){
					$('.notification-messaje').remove();
				}, 5000);
				$(document).on( 'click', '.notification-dismiss',function(){
					$('.notification-messaje').remove();
				})
			return false;
		});
	}

})( jQuery );

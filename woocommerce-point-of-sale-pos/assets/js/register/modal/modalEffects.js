/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
var ModalEffects = (function() {

	function init() {
		var overlay = document.querySelector( '.md-overlay' );

		[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

			var modal = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
				close = modal.querySelector( '.md-close' );

			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );

				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( classie.has( el, 'md-setperspective' ) );
			}

			el.addEventListener( 'click', function( ev ) {
				classie.add( modal, 'md-show' );

				if( classie.has( el, 'md-setperspective' ) ) {
					setTimeout( function() {
						classie.add( document.documentElement, 'md-perspective' );
					}, 25 );
				}
			});
			if(close != null){
				close.addEventListener( 'click', function( ev ) {
					ev.stopPropagation();
					removeModalHandler();
				});
			}

		} );

		[].slice.call( document.querySelectorAll( '.md-openmodal' ) ).forEach( function( modal, i ) {

			var close = modal.querySelector( '.md-close' );

			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );

				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( false );
			}

			classie.add( modal, 'md-show' );

			if( close != null ){
				close.addEventListener( 'click', function( ev ) {
					ev.stopPropagation();
					removeModalHandler();
				});
			}

		} );

		[].slice.call( document.querySelectorAll( '.md-dynamicmodal' ) ).forEach( function( modal, i ) {

			//var close = modal.querySelector( '.md-close' );
			var closes = modal.querySelectorAll( '.md-close' );


			function removeModal( hasPerspective ) {
				classie.remove( modal, 'md-show' );

				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( false );
			}
			if( closes != null ){
				[].forEach.call(closes, function(close) {
					close.addEventListener( 'click', function( ev ) {
						window.openwin = false;
						ev.stopPropagation();
						removeModalHandler();
						var missing = modal.querySelector( '#missing-attributes-select' );
						if(missing != null){
							missing.innerHTML = '';
						}
						var missing = modal.querySelector( '#product-addons-attributes' );
						if(missing != null){
							missing.innerHTML = '';
						}
					});
				});
			}

		} );

	}

	init();

})();
function openModal(modalid, openwin) {
	var modal = document.querySelector( '#'+modalid );
	if( modal != null ){
		classie.add( modal, 'md-show' );
		if(openwin === true ){
			window.openwin = true;
		}
	}
	if( typeof wp != 'undefined' && typeof wp.hooks != 'undefined'){
		wp.hooks.doAction( 'openModal_' + modalid);
	}
}
function closeModal(modalid) {
	var modal;
	if( typeof modalid != 'undefined')
		modal = document.querySelector( '#'+modalid );
	else
		modal = document.querySelector( '.md-modal.md-show' );

	if( modal != null ){
		classie.remove( modal, 'md-show' );
		var missing = modal.querySelector( '#missing-attributes-select' );
		if(missing != null){
			missing.innerHTML = '';
		}
		var missing = modal.querySelector( '#product-addons-attributes' );
		if(missing != null){
			missing.innerHTML = '';
		}
	}
}
function openConfirm(args) {
	var modal = document.querySelector( '#modal-confirm-box' );
	if( modal != null ){

		var source        = document.getElementById("tmpl-confirm-box-content").innerHTML;
		var template      = Handlebars.compile(source);
		var html          = template(args);
	    document.getElementById("modal-confirm-box-content").innerHTML = html;
	    document.getElementById("cancel-button").addEventListener("click", function(){classie.remove( modal, 'md-show' );});
	    document.getElementById("confirm-button").addEventListener("click", function(){
	    	if(typeof args.confirm != 'undefined'){
	    		args.confirm();
	    	}
	    	classie.remove( modal, 'md-show' );
	    });

		classie.add( modal, 'md-show' );
	}
}
function openPromt(args) {
	var modal = document.querySelector( '#modal-confirm-box' );
	if( modal != null ){
		var content = '<input type="text" id="promt_input" autocomplete="off">';
		if( typeof args.content != 'undefined' ){
			args.content += content;
		}else{
			args.content = content;
		}

		var source        = document.getElementById("tmpl-confirm-box-content").innerHTML;
		var template      = Handlebars.compile(source);
		var html          = template(args);
	    document.getElementById("modal-confirm-box-content").innerHTML = html;
	    document.getElementById("cancel-button").addEventListener("click", function(){
	    	classie.remove( modal, 'md-show' );
	    	if(typeof args.cancel != 'undefined'){
	    		args.cancel(false);
	    	}
	    });
	    document.getElementById("confirm-button").addEventListener("click", function(){
	    	classie.remove( modal, 'md-show' );
	    	if(typeof args.confirm != 'undefined'){
	    		var answer = document.getElementById("promt_input").value;
	    		args.confirm(answer);
	    		document.getElementById("promt_input").value = '';
	    	}
	    });
		classie.add( modal, 'md-show' );
		document.getElementById("promt_input").focus();
	}
}
jQuery(document).ready(function($) {
	$('.md-modal .media-menu a').click( function() {
		$parent = $(this).closest('.md-modal');
		$parent.find('.media-menu a').removeClass('active')
		$(this).addClass('active');
		var id  = $(this).attr('href');
		$parent.find('.popup_section').hide();
		$(id).show();
		$('#coupon_tab div.messages').html('');

		if($(this).hasClass('payment_methods')){
			var txt = $(this).text();
			$('h1 span.txt').text(txt);
			var selected_payment_method = $(this).data('bind');
			$('#payment_method_' + selected_payment_method).attr('checked', 'checked');
		}
	return false;
	});
	$('.md-overlay').click(function(event) {
		var $active = $('.md-modal.md-show');
		if( $active.hasClass('md-close-by-overlay')){
			jQuery('.md-close').click();
			//closeModal();
		}
});
});
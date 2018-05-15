;
(function ( $ ) {

	var TCBYoastPlugin = function () {
		YoastSEO.app.registerPlugin( 'tcbYoastPlugin', {status: 'loading'} );

		this.tcb_content = '';

		this.fetchData();
	};

	TCBYoastPlugin.prototype.fetchData = function () {
		var _self = this;

		return $.ajax( {
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				post_id: TCB_Post_Edit_Data.post_id,
				action: 'get_tcb_content'
			}
		} ).done( function ( response ) {

			_self.tcb_content = response.content;
			YoastSEO.app.pluginReady( 'tcbYoastPlugin' );

			/**
			 * @param modification    {string}    The name of the filter
			 * @param callable        {function}  The callable
			 * @param pluginName      {string}    The plugin that is registering the modification.
			 * @param priority        {number}    (optional) Used to specify the order in which the callables
			 *                                    associated with a particular filter are called. Lower numbers
			 *                                    correspond with earlier execution.
			 */
			YoastSEO.app.registerModification( 'content', $.proxy( _self.getTCBContent, _self ), 'tcbYoastPlugin', 5 );

		} );
	};

	TCBYoastPlugin.prototype.getTCBContent = function ( content ) {
		return this.tcb_content ? this.tcb_content : content;
	};

	/**
	 * YoastSEO content analysis integration
	 */
	$( window ).on( 'YoastSEO:ready', function () {
		new TCBYoastPlugin();
	} );


	/**
	 * this is not been used anymore, I don't think we managed to identify whether or not the post has been saved as draft
	 *
	 * @param element
	 */
	function tve_page_not_ready_notification( element ) {
		jQuery( element ).pointer( {
			content: "<h3>You can't edit the post yet!</h3>" +
			         "<p>In order to edit the post with the Content Builder you have to have:-</p>" +
			         "<ol><li>Saved the post before (using the 'Save Draft' button)</li>" +
			         "<li>Set a title for the post / page</li></ol>" +
			         "<p>Make these changes, and you'll be able to click this button and edit the page!",
			position: {
				edge: 'left',
				align: 'center'
			},
			close: function () {
				// Once the close button is hit
			}
		} ).pointer( 'open' );
	}

	function show_loader() {
		$( '#tcb-admin-page-loader' ).show();
	}

	$( function () {
		var $form = $( 'form#post' );
		$form.on( 'click.tcb', '#tcb2-migrate-post', function () {
			var _link = this;
			show_loader();
			$.ajax( {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: {
					_nonce: TCB_Post_Edit_Data.admin_nonce,
					post_id: TCB_Post_Edit_Data.post_id,
					action: 'tcb_admin_ajax_controller',
					route: 'migrate_post_content'
				}
			} ).done( function ( response ) {
				location.href = _link.getAttribute( 'data-edit' );
			} ).error( function ( jqXHR ) {
				alert( 'ERROR: ' + jqXHR.responseText );
			} );
		} )
		     .on( 'click', '#tcb2-show-wp-editor', function () {
			     /**
			      * Enable the hidden input that will disable TCB editor when saving the post
			      */
			     var $editlink = $form.find( '.tcb-enable-editor' ),
				     $postbox = $editlink.closest( '.postbox' );
			     $postbox.next( '.tcb-flags' ).find( 'input' ).prop( 'disabled', false );
			     $postbox.before( $editlink );
			     $postbox.remove();
			     $( 'body' ).removeClass( 'tcb-hide-wp-editor' );
		     } )
		     .on( 'click', '.tcb-enable-editor', function () {
			     $( 'body' ).removeClass( 'tcb-hide-wp-editor' );
			     $.ajax( {
				     type: 'post',
				     url: ajaxurl,
				     dataType: 'json',
				     data: {
					     _nonce: TCB_Post_Edit_Data.admin_nonce,
					     post_id: this.getAttribute( 'data-id' ),
					     action: 'tcb_admin_ajax_controller',
					     route: 'enable_tcb'
				     }
			     } ).done( function ( response ) {
				     $( window ).off( 'beforeunload.edit-post' );
				     $( 'input#save-post' ).click().prop( 'disabled', true );
			     } );
		     } );
	} );

})( jQuery );
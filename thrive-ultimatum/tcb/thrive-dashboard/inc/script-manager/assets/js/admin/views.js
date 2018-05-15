(function ( $ ) {

	var utils = require( '../utils' ),
		models = require( './models' ),
		views = {},
		scriptsCollection,
		doingAjax = false;

	views.ScriptDashboard = Backbone.View.extend( {
		template: utils._t( 'dashboard' ),

		events: {
			'click .tvd-add-modal': 'addScript',
			'click #tvd-sm-theme-link': 'setLinkTarget'
		},

		initialize: function () {
			this.breadcrumbs = new views.Breadcrumbs( {
				el: this.$el.closest( '#tvd-sm-wrapper' ).prev()
			} );

			this.render();

			$( '#tvd-delete-page-level-scripts-modal' ).on( 'click', function () {
				TVE_Dash.modal( views.DeletePageLevelScriptsModal );
			} );

			scriptsCollection = new models.collections.ScriptCollection( TVD_SM_CONST.scripts );

			/* initialize the 3 groups of scripts */
			new views.ScriptGroup( {
				collection: scriptsCollection,
				_type: 'head',
				el: this.$( '#tvd-sm-container-head' )
			} );

			new views.ScriptGroup( {
				collection: scriptsCollection,
				_type: 'body_open',
				el: this.$( '#tvd-sm-container-body-open' )
			} );

			new views.ScriptGroup( {
				collection: scriptsCollection,
				_type: 'body_close',
				el: this.$( '#tvd-sm-container-body-close' )
			} );
		},

		render: function () {
			this.$el.html( this.template() );
		},

		addScript: function () {
			TVE_Dash.modal( views.AddEditScriptModal, {
				collection: scriptsCollection,
				message: TVD_SM_CONST.translations.add_script
			} );
		},

		setLinkTarget: function () {
			localStorage.setItem( 'ThriveOptionsSelectedItem', 'analytics-options' )
		}
	} );

	/* Contains ScriptItems and is responsible of rendering and re-rendering them when they change. */
	views.ScriptGroup = Backbone.View.extend( {
			initialize: function ( options ) {
				var self = this;

				this.options = options;

				/* adds dragging inside and between groups */
				this.$el.sortable( {
					axis: 'y',
					connectWith: $( '.tvd-sm-script-group' ),
					handle: '.tvd-icon-handle',

					start: function ( event, ui ) {
						ui.item.addClass( 'tvd-sm-script-drag' );
					},

					stop: function ( event, ui ) {
						ui.item.removeClass( 'tvd-sm-script-drag' );
					},

					update: function ( event, ui ) {
						var parent = ui.item.parent()[0];

						if ( ! ui.sender && this === parent ) {
							self.updateOrder( event, ui );
						}
						else if ( this !== parent ) {
							if ( self.collection.where( {placement: self.options._type} ).length === 1 ) {
								self.$el.append( self.renderEmpty().$el );
							}
						}

					},
					receive: function ( event, ui ) {
						self.updateOrder( event, ui );
					}
				} );

				/* re-renders each time a script is moved between ScriptGroups */
				this.listenTo( this.collection, 'change', function ( model, options ) {
					/* the add event is flagged with noChange to avoid unnecessary rendering */
					if ( options.flag !== 'noChange' ) {
						this.render();
					}
				} );

				/* each time a script is added or deleted, renders the ScriptGroup with that placement */
				this.listenTo( this.collection, 'add destroy', function ( model ) {
					if ( model.attributes.placement === this.options._type ) {
						this.render();
					}
				} );

				this.render();
			},

			updateOrder: function ( event, ui ) {
				var self = this,
					newScripts = this.$el.sortable( 'toArray', {attribute: 'data-id'} );

				$.post( {
					headers: {
						'X-WP-Nonce': TVD_SM_CONST.nonce
					},
					url: TVD_SM_CONST.routes.scripts_order,
					data: {
						scripts: newScripts
					},
					success: function () {
						/* custom reset */
						var order = 0;
						_.each( newScripts, function ( scriptId ) {
								self.collection.get( scriptId ).set( {order: order ++}, {silent: true} );
							}
						);
						/* update placement */
						if ( ui.sender ) {
							var model = self.collection.get( ui.item.data( 'id' ) );
							model.save(
								{placement: event.target.dataset.placement},
								{silent: true} );

							/* check if group was empty before */
							if ( self.$el.hasClass( 'tvd-sm-no-scripts' ) ) {
								/* remove the empty css */
								self.$el.empty().removeClass( 'tvd-sm-no-scripts' );
								self.renderOne( model );
							}
						}
					}
				} );
			},

			renderOne: function ( item ) {
				this.$el.append( new views.ScriptItem( {
					model: item
				} ).render().$el );
			},

			/*  when there are no scripts in the group, add specific text */
			renderEmpty: function () {
				this.$el.append( TVD_SM_CONST.translations.no_scripts_in_this_group ).addClass( 'tvd-sm-no-scripts' );
				return this;
			},

			render: function () {
				this.$el.empty();
				this.$el.attr( 'data-placement', this.options._type );
				this.collection.sort();

				var currentCollection = this.collection.where( {placement: this.options._type} );
				_.each( currentCollection, function ( script ) {
					this.renderOne( script );
				}, this );

				/* if there are no scripts in this section, add specific text */
				if ( currentCollection.length === 0 ) {
					this.$el.append( this.renderEmpty().$el );
				}
				else {
					/* if a group had no scripts and then something gets added, remove the empty class */
					if ( this.$el.hasClass( 'tvd-sm-no-scripts' ) ) {
						this.$el.removeClass( 'tvd-sm-no-scripts' );
					}
				}
				return this;
			}
		}
	);

	/* ScriptItem = one line in a ScriptGroup */
	views.ScriptItem = Backbone.View.extend( {
		className: 'tvd-sm-script-item',
		template: utils._t( 'script-item' ),
		events: {
			'click .tvd-edit-modal': 'editScript',
			'click .tvd-delete-modal': 'deleteScript',
			'change .tvd-sm-script-elem-switch': 'toggleEnable'
		},

		initialize: function () {
			/* when the model is changed, re-render */
			this.listenTo( this.model, 'change', this.render );
		},

		toggleEnable: function ( event ) {
			var self = this;

			/* wait for the current enable request to finish */
			if ( doingAjax ) {
				setTimeout( function () {
					self.toggleEnable( event );
				}, 200 );
				return;
			}
			/* set the flag to true so other callbacks have to wait */
			doingAjax = true;

			this.model.save( {status: event.target.checked}, {
				flag: 'noChange',
				success: function () {
					doingAjax = false;
					TVE_Dash.success( TVD_SM_CONST.translations.edit_success );
				},
				error: function () {
					TVE_Dash.err( TVD_SM_CONST.translations.edit_error );
				}
			} );
		},

		formIconClass: function () {
			return 'tvd-sm-icon img-' + this.model.get( 'icon' );
		},

		render: function () {
			this.$el.html( this.template( {model: this.model} ) );
			/* add the id of the model to the DOM */
			this.$el.attr( 'data-id', this.model.get( 'id' ) );
			this.$el.attr( 'data-placement', this.model.get( 'placement' ) );
			return this;
		},

		editScript: function () {
			TVE_Dash.modal( views.AddEditScriptModal, {
				model: this.model,
				message: TVD_SM_CONST.translations.edit_script
			} );
		},

		deleteScript: function () {
			TVE_Dash.modal( views.DeleteScriptModal, {
				model: this.model,
				className: 'tvd-modal tvd-sm-delete-script'
			} );
		}
	} );

	/* Adds or edits ScriptItems according to where the modal was opened from. */
	views.AddEditScriptModal = TVE_Dash.views.Modal.extend( {
		template: utils._t( 'modal-add-edit-script' ),
		events: {
			'click .tvd-submit': 'save',
			'change #tvd-sm-script-code': 'recognizeScript',
			'focusin .tvd-sm-input-field': 'focusLabel',
			'blur .tvd-sm-input-field': 'blurLabel'
		},

		save: function () {
			var newScript = {
				label: this.$( '#tvd-sm-script-name' ).val(),
				status: 1,
				placement: this.$( '#tvd-sm-script-placement' ).val(),
				code: this.$( '#tvd-sm-script-code' ).val(),
				icon: this.$( '#tvd-sm-script-icon' ).val()
			};

			/* validation checks */
			if ( ! this.validate( newScript ) ) {
				return false;
			}

			if ( typeof this.collection !== 'undefined' ) {
				this.addScript( newScript );
			}
			else {
				this.editScript( newScript );
			}
		},

		/* validates the input fields */
		validate: function ( newScript ) {
			var code = newScript.code,
				label = newScript.label,
				placement = newScript.placement;

			/* check if the inputs are empty */
			if ( ! code ) {
				this.$( '#tvd-sm-code-empty' ).show();
				return false;
			}
			this.$( '#tvd-sm-code-empty' ).hide();

			if ( ! label ) {
				this.$( '#tvd-sm-label-empty' ).show();
				return false;
			}
			this.$( '#tvd-sm-label-empty' ).hide();

			if ( ! placement ) {
				this.$( '#tvd-sm-placement-empty' ).show();
				return false;
			}
			this.$( '#tvd-sm-placement-empty' ).hide();

			/* check if the provided script properly closes any <script>, <noscript>, <iframe> tags */
			if ( ! this.checkTags( code ) ) {
				this.$( '#tvd-sm-code-invalid' ).show();
				return false;
			}
			this.$( '#tvd-sm-code-invalid' ).hide();
			return true;
		},

		checkTags: function ( code ) {
			/* number of opened tags of a type has to be equal to the number of closed tags */
			if ( typeof _.find( ['script', 'noscript', 'iframe'], function ( elem ) {
					return code.split( '<' + elem ).length !== code.split( '</' + elem + '>' ).length
				} ) === 'undefined' ) {
				return true;
			}
		},

		recognizeScript: function () {
			var code = this.$( '#tvd-sm-script-code' ).val(),
				searchKey =
					/* for every key */
					_.findKey( TVD_SM_CONST.recognized_scripts.keywords, function ( keywords ) {
						/* for every keyword */
						return _.find( keywords, function ( keyword ) {
							return code.indexOf( keyword ) !== - 1;
						} );
					} );

			/* if we found a matching keyword */
			if ( typeof searchKey !== 'undefined' ) {
				var data = TVD_SM_CONST.recognized_scripts.data[searchKey];
				this.$( '#tvd-sm-script-name' ).val( searchKey );
				this.$( '#tvd-sm-script-placement' ).val( data.placement ).change();
				/* the icon name is hidden */
				this.$( '#tvd-sm-script-icon' ).val( data.icon );
			}
			else {
				this.$( '#tvd-sm-script-icon' ).val( 'nonstandard' );
			}
		},

		addScript: function ( newScript ) {
			var self = this,
				currentCollection = this.collection.where( {placement: newScript.placement} );

			/* if unrecognized, assign nonstandard icon */
			if ( ! newScript.icon ) {
				newScript.icon = 'nonstandard';
			}
			/* set the order to be the order of the last element + 1 */
			newScript.order = currentCollection.length > 0 ? currentCollection[currentCollection.length - 1].get( 'order' ) + 1 : 0;

			/* adds the new model to the collection. This triggers the add event inside ScriptGroup which re-renders the view  */
			this.collection.create( newScript, {
				success: function () {
					self.close();
				},
				error: function () {
					TVE_Dash.err( TVD_SM_CONST.translations.add_error );
				},
				flag: 'noChange'
			} );
		},
		editScript: function ( newScript ) {
			var self = this,
				currentCollection = scriptsCollection.where( {placement: newScript.placement} );

			newScript.status = this.model.get( 'status' );

			/* if new placement != old placement */
			if ( this.model.get( 'placement' ) !== newScript.placement ) {
				newScript.order = currentCollection.length > 0 ? currentCollection[currentCollection.length - 1].get( 'order' ) + 1 : 0;
			}

			/* saves the new model, overwriting the old one. Triggers the change event inside ScriptGroup.*/
			this.model.save( newScript, {
				success: function () {
					self.close();
				},
				error: function () {
					TVE_Dash.err( TVD_SM_CONST.translations.edit_error );
				}
			} );
		},
		/* add class to the focused label inside the modal */
		focusLabel: function ( event ) {
			$( event.target ).closest( '.tvd-sm-input-field' ).addClass( 'tvd-sm-focused-label' )

			if ( event.target.classList.contains( 'select2-selection' ) ) {
				this.$( '#tvd-sm-script-placement' ).select2( 'open' )
			}
		},
		/* remove class from the unfocused label inside the modal */
		blurLabel: function ( event ) {
			$( event.target ).closest( '.tvd-sm-input-field' ).removeClass( 'tvd-sm-focused-label' )
		}
	} );

	/* Deletes a ScriptItem . */
	views.DeleteScriptModal = TVE_Dash.views.Modal.extend( {
		template: utils._t( 'modal-delete-script' ),
		events: {
			'click .tvd-submit': 'delete'
		},

		delete: function () {
			this.model.destroy( {
				error: function () {
					TVE_Dash.err( TVD_SM_TVD_SM_CONST.translations.delete_page_level_error );
				}
			} );
			this.close();
		}
	} );

	/* Deletes all page-level scripts. */
	views.DeletePageLevelScriptsModal = TVE_Dash.views.Modal.extend( {
		template: utils._t( 'modal-delete-page-level-scripts' ),
		events: {
			'click .tvd-submit': 'delete'
		},

		delete: function () {
			$.post( {
				headers: {
					'X-WP-Nonce': TVD_SM_CONST.nonce
				},
				url: TVD_SM_CONST.routes.clear_page_level_scripts,
				success: function () {
					TVE_Dash.success( TVD_SM_CONST.translations.delete_page_level_success );
				},
				error: function () {
					TVE_Dash.err( TVD_SM_TVD_SM_CONST.translations.delete_error );
				}
			} );
			this.close();
		}
	} );

	views.Breadcrumbs = Backbone.View.extend( {
		template: utils._t( 'breadcrumbs' ),
		initialize: function () {
			this.render();
		},
		render: function () {
			this.$el.html( this.template() );
			return this;
		}
	} );

	module.exports = views;

})( jQuery );
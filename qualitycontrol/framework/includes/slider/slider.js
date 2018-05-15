(function( $ ){

	var methods = {
		init : function( options ) {
			return this.each(function(){
				var $this = $(this),
					data = $this.data('appthemes_slider');

				var _options = $.extend({}, $.fn.appthemes_slider.defaults, options );

				if ( ! data ) {
					$(this).data('appthemes_slider', {
						options: _options
					});
				}

				var _attachment_count = $this.find('.attachment').length;

				if ( _attachment_count <= 1 ) {
					$this.find('.left-arrow').hide();
					$this.find('.right-arrow').hide();
				}

				$(this).find('.attachment').removeClass('current');
				$(this).find('.attachment').eq(0).addClass('current');

				methods.click_handlers( $this );

			});

		},

		click_handlers: function( that ) {
			var $this = that,
				data = $this.data('appthemes_slider');

			$this.find('.right-arrow').click(function(e){
				e.preventDefault();
				var _attachment_count = $this.find('.attachment').length;
				var _current_position = $this.find('.attachments-slider').css('left');
				var _current_position = methods.un_px( _current_position );

				var _current_slide = parseInt( $this.find('.attachments-slider').data('position') );
				var _next_slide = parseInt( _current_slide ) + 1;

				var _slider_position = $this.find('.attachments-slider').data('position');

				var _slide_amount = methods.el_width( $('#attachment_' + _current_slide ) );

				$('#attachment_' + _current_slide).animate({
					left: '-=' + _slide_amount,
				}, { duration: data.options.slide_duration,
					complete: function() {
						$('#attachment_' + _current_slide).removeClass('current');
						$('#attachment_' + _current_slide).css({ 'left': 0 });
					}
				});

				if( _slider_position >= ( _attachment_count - 1 ) ) {
					$('#attachment_0').css({ 'left': _slide_amount }).addClass('current');
					$('#attachment_0').animate({
						left: '-=' + _slide_amount,
					}, { duration: data.options.slide_duration });

					$this.find('.attachments-slider').data('position', 0 );
				} else {
					$('#attachment_' + _next_slide ).css({ 'left': _slide_amount }).addClass('current');
					$('#attachment_' + _next_slide).animate({
						left: '-=' + _slide_amount,
					}, { duration: data.options.slide_duration });

					$this.find('.attachments-slider').data('position', (_slider_position+=1) );
				}

			});

			$this.find('.left-arrow').click(function(e){
				e.preventDefault();
				var _attachment_count = $this.find('.attachment').length;
				var _last_slide = parseInt( _attachment_count ) - 1;
				var _current_position = $this.find('.attachments-slider').css('left');
				var _current_position = methods.un_px( _current_position );

				var _current_slide = parseInt( $this.find('.attachments-slider').data('position') );
				var _prev_slide = parseInt( _current_slide ) - 1;

				var _slider_position = $this.find('.attachments-slider').data('position');

				var _slide_amount = methods.el_width( $('#attachment_' + _current_slide ) );

				$('#attachment_' + _current_slide).animate({
					left: '+=' + _slide_amount,
				}, { duration: data.options.slide_duration,
					complete: function() {
						$('#attachment_' + _current_slide).removeClass('current');
						$('#attachment_' + _current_slide).css({ 'left': 0 });
					}
				});

				if ( _slider_position == 0 ) {
					$('#attachment_' + _last_slide ).css({ 'left': (-1) * _slide_amount }).addClass('current');
					$('#attachment_' + _last_slide ).animate({
						left: '+=' + _slide_amount,
					}, { duration: data.options.slide_duration });

					$this.find('.attachments-slider').data('position', (_last_slide) );
				} else {
					$('#attachment_' + _prev_slide ).css({ 'left': (-1) * _slide_amount }).addClass('current');
					$('#attachment_' + _prev_slide ).animate({
						left: '+=' + _slide_amount,
					}, { duration: data.options.slide_duration });

					$this.find('.attachments-slider').data('position', (_slider_position-=1) );
				}

			});
		},

		el_width: function( el ) {
			return $(el).width() + methods.un_px( $(el).css('marginLeft') ) + methods.un_px( $(el).css('marginRight') );
		},

		un_px: function( pxed ) {
			return parseInt( methods.str_replace("px", "", pxed ) );
		},

		str_replace: function (search, replace, subject, count) {
			var i = 0,
				j = 0,
				temp = '',
				repl = '',
				sl = 0,
				fl = 0,
				f = [].concat(search),
				r = [].concat(replace),
				s = subject,
				ra = Object.prototype.toString.call(r) === '[object Array]',
				sa = Object.prototype.toString.call(s) === '[object Array]';
				s = [].concat(s);
				if (count) {
				this.window[count] = 0;
			}
	
			for (i = 0, sl = s.length; i < sl; i++) {
				if (s[i] === '') {
					continue;
				}
				for (j = 0, fl = f.length; j < fl; j++) {
					temp = s[i] + '';
					repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
					s[i] = (temp).split(f[j]).join(repl);
					if (count && s[i] !== temp) {
						this.window[count] += (temp.length - s[i].length) / f[j].length;
					}
				}
			}
			return sa ? s : s[0];
		}
	};

	$.fn.appthemes_slider = function( method ) {
		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.appthemes_slider' );
		}
	};

	$.fn.appthemes_slider.defaults = {
		slide_duration: 300
	};

})( jQuery );

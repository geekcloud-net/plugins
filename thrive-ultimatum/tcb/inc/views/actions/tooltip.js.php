<?php echo 'function(trigger,action,config){' ?>
if ( ! config.event_tooltip_text ) {
	return false;
}
ThriveGlobal.$j( '.tve_ui_tooltip' ).remove();
var base = ThriveGlobal.$j( '<div></div>', {
	'class': 'tve_ui_tooltip tve_tooltip_style_' + config.event_tooltip_style + ' tve_tooltip_position_' + config.event_tooltip_position
} );
base.text( config.event_tooltip_text )
	.appendTo( 'body' );
var tooltip_width = base.outerWidth(),
	tooltip_height = base.outerHeight(),
	offset = 10,
	top = 0,
	left = 0;
var rect = this.getBoundingClientRect();
switch ( config.event_tooltip_position ) {
	case 'top':
		left = (rect.right - rect.left - tooltip_width ) / 2 + rect.left;
		top = rect.top - tooltip_height - offset;
		break;
	case 'top_right':
		left = rect.right + offset;
		top = rect.top - tooltip_height - offset;
		break;
	case 'right':
		left = rect.right + offset;
		top = ( rect.bottom - rect.top - tooltip_height ) / 2 + rect.top;
		break;
	case 'bottom_right':
		left = rect.right + offset;
		top = rect.bottom + offset;
		break;
	case 'bottom':
		left = ( rect.right - rect.left - tooltip_width ) / 2 + rect.left;
		top = rect.bottom + offset;
		break;
	case 'bottom_left':
		left = rect.left - tooltip_width - offset;
		top = rect.bottom + offset;
		break;
	case 'left':
		left = rect.left - tooltip_width - offset;
		top = (rect.bottom - rect.top - tooltip_height ) / 2 + rect.top;
		break;
	case 'top_left':
		left = rect.left - tooltip_width - offset;
		top = rect.top - tooltip_height - offset;
		break;
	default:
		left = 1;
		top = 1;
		break;
}
base.css( {
	top: top + 'px',
	left: left + 'px'
} );

ThriveGlobal.$j( this ).on( 'mouseleave', function () {
	ThriveGlobal.$j( '.tve_ui_tooltip' ).remove();
} );
<?php echo 'return false;}';

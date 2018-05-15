<?php
/**
 * Attachments Metabox
 *
 * @package Framework\Metaboxes
 */
class APP_Post_Attachments_Metabox extends APP_Meta_Box {

	public $mime_groups = array( 'image', 'video', 'video_iframe_embed' );
	public $image_mime_types = array( 'image/png', 'image/jpeg', 'image/gif' );
	public $video_mime_types = array( 'video/mp4' );
	public $video_iframe_embed_mime_types = array('video/youtube-iframe-embed', 'video/vimeo-iframe-embed', 'video/iframe-embed');

	public function __construct( $id, $title, $post_type ) {
		parent::__construct( $id, $title, $post_type );

		add_action( 'wp_ajax_appthemes-get-post-attachment', array( $this, 'get_attachment' ) );

	}

	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_script('jquery-ui-sortable');
	}

	private function _get_attachment( $post_id, $url ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_parent = '%d' AND post_type = 'attachment' AND guid = '%s'", $post_id, $url ));
	}

	public function get_attachments( $post ) {
		$attachments = get_posts( array('post_parent' => $post->ID, 'post_status' => 'inherit', 'post_type' => 'attachment', 'nopaging' => true, 'orderby' => 'menu_order', 'order' => 'asc' ) );

		return $attachments;
	}

	public function get_attachment() {

		$attachment = $this->_get_attachment( $_POST['ID'], $_POST['url'] );

		if ( !$attachment ) {
			$attachment = $this->alt_attachment( $_POST['url'], $_POST['ID'], $_POST['title'] );
		}

		$attachment->thumbnail_html = $this->display_attachment_thumbnail( $attachment );
		$attachment->upload_date = appthemes_display_date( $attachment->post_date, 'date' );
		$attachment->dimensions = $this->display_attachment_dimensions( $attachment );

		die( json_encode( $attachment ) );
	}

	public function alt_attachment( $url, $post_id, $title ) {
		global $wpdb;

		if ( strpos( $url, 'youtube' ) !== false ) {
			$attachment = $this->insert_youtube( $url, $post_id, $title );
		} elseif( strpos( $url, 'vimeo' ) !== false ) {
			$attachment = $this->insert_vimeo( $url, $post_id, $title );
		} else {
			$attachment = apply_filters( 'appthemes_insert_alt_attachment', array(), $url, $post_id );
		}

		return $attachment;
	}

	public function get_video_attachment_thumbnail_url( $attachment ) {
		return get_post_meta( $attachment->ID, '_thumbnail_url', true );
	}

	public function thumbnail_image( $url, $h = '', $w = '' ) {
		$h = !empty( $h ) ? 'height:'.$h.'px;' : '';
		$w = !empty( $w ) ? 'width:'.$w.'px;' : '';
		return html( 'img', array( 'src' => $url, 'style' => $h.$w ) );
	}

	public function alt_attachment_defaults() {
		return array (
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_author' => get_current_user_id(),
		);
	}

	public function get_youtube_thumbnail_url( $id ) {
		return 'http://img.youtube.com/vi/' . $id . '/default.jpg';
	}

	public function get_youtube_video_id( $url ) {
		// The below pattern is expecting user input like this: http://www.youtube.com/embed/dThOlMmO4I8
		$pattern = '/.*\/([a-zA-Z0-9]+)/si';// to be improved
		preg_match( $pattern, $url, $matches );

		$id = $matches[1];

		return $id;
	}

	public function format_youtube_url( $url ) {
		// to be improved
		$url = str_ireplace( array( 'http://', 'https://', '//' ), '//', $url );
		return $url;
	}

	public function insert_youtube( $url, $post_id, $title ) {
		$url = $this->format_youtube_url( $url );
		$id = $this->get_youtube_video_id( $url );
		$thumbnail = $this->get_youtube_thumbnail_url( $id );
		$data = $this->alt_attachment_defaults() + array (
			'post_title' => $title,
			'guid' => $url,
			'post_parent' => $post_id,
			'post_mime_type' => 'video/youtube-iframe-embed',
		);

		$attachment_id = wp_insert_post( $data );
		update_post_meta( $attachment_id, '_thumbnail_url', $thumbnail );
		update_post_meta( $attachment_id, '_video_id', $id );

		return $this->_get_attachment( $post_id, $url );
	}

	public function get_vimeo_thumbnail_url( $id ) {
		$url = 'http://vimeo.com/api/v2/video/' . $id . '.json';
		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ) );

		$thumbnail_url = $response[0]->thumbnail_medium;
		return $thumbnail_url;
	}

	public function get_vimeo_video_id( $url ) {
		// The below pattern is expecting user input like this: http://player.vimeo.com/video/23396322
		$pattern = '/.*\/([0-9]+)/si';// to be improved
		preg_match( $pattern, $url, $matches );

		$id = $matches[1];

		return $id;
	}

	public function format_vimeo_url( $url ) {
		// to be improved
		$url = str_ireplace( array( 'http://', 'https://' ), '//', $url );
		return $url;
	}

	public function insert_vimeo( $url, $post_id, $title ) {
		$url = $this->format_vimeo_url( $url );
		$id = $this->get_vimeo_video_id( $url );
		$thumbnail = $this->get_vimeo_thumbnail_url( $id );
		$data = $this->alt_attachment_defaults() + array (
			'post_title' => $title,
			'guid' => $url,
			'post_parent' => $post_id,
			'post_mime_type' => 'video/vimeo-iframe-embed'
		);

		$attachment_id = wp_insert_post( $data );
		update_post_meta( $attachment_id, '_thumbnail_url', $thumbnail );
		update_post_meta( $attachment_id, '_video_id', $id );

		return $this->_get_attachment( $post_id, $url );
	}

	function display_attachment_dimensions( $attachment ) {
		// go get the width and height fields since they are stored in meta data
		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( is_array($meta) && array_key_exists('width', $meta) && array_key_exists('height', $meta) ) {
			$media_dimensions = "<span id='media-dims-" . $attachment->ID."'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";
		} else {
			$media_dimensions = apply_filters( 'appthemes_display_attachment_dimensions', '', $attachment, $meta );
		}

		return $media_dimensions;
	}

	function display_attachment_thumbnail( $attachment ) {

		$mime_type = $attachment->post_mime_type;
		foreach( $this->mime_groups  as $mime_group ) {
			$html = apply_filters( 'appthemes_display_attachment_thumb_mime_group-' . $mime_group, null, $attachment );
			if ( !is_null( $html ) )
					return $html;

			if ( in_array( $mime_type, $this->{ $mime_group . '_mime_types' } ) ) {
				$method = 'display_' . $mime_group;
				$html = $this->$method( $attachment );
				return $html;
			}
		}

		$html = apply_filters( 'appthemes_display_attachment_thumb_mime_type_unknown', '', $attachment );
		return $html;
	}

	function display_image( $attachment ) {
		return wp_get_attachment_image( $attachment->ID, array( 250, 250 ) );
	}

	function display_video( $attachment ) {
		return html( 'img', array( 'src' => wp_mime_type_icon( $attachment->post_mime_type ), 'class' => esc_attr( $attachment->post_mime_type ) ) );
	}

	function display_video_iframe_embed( $attachment ) {
		if ( in_array( $attachment->post_mime_type, array( 'video/youtube-iframe-embed', 'video/vimeo-iframe-embed' ) ) ) {
			$thumbnail_url = $this->get_video_attachment_thumbnail_url( $attachment );
			return $this->thumbnail_image( $thumbnail_url, '', 250 );
		}

		return $this->display_video( $attachment );
	}

	public function display_styles() {
		?>
<style>
#attachments #attachments_header th.thumb {
	width: 250px;
}

#attachments #attachments_header th.title {
	width: auto;
}

#attachments #attachments_header th.date {
	width: 100px;
}

#attachments #attachments_header th.dimensions {
	width: 100px;
}

#attachments #attachments_header th.delete {
	width: 100px;
}

#attachments tbody tr.placeholder {
	background-color: #FFFFFF;
}

#attachments tbody td {
	border-bottom:  1px solid #dfdfdf;
}

#attachments .title input {
	width: 200px;
}

#attachments .icons span {
	display: inline-block;
}

#attachments .icons .delete {
	cursor: pointer;
}

#attachments .icons .grip {
	cursor: move;
}
</style>
	<?php }

	public function display_scripts( $post ) {
		?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {

	var _content_override;

	/* upload an ad image */
	jQuery('input#upload_media_button').click(function() {
		_content_override = 1;
		tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=true');
		return false;
	});

	window.original_send_to_editor = window.send_to_editor;

	/* send the uploaded image url to the field */
	window.send_to_editor = function(html) {
		if( _content_override ){
			var attachment_url;

			var jq_html_obj = jQuery( html );
			var a_href = jQuery( jq_html_obj ).attr('href');
			var a_text = jQuery( jq_html_obj ).text();

			var img_src = jQuery( jq_html_obj ).attr('src');

			if ( a_href ) {
				attachment_url = a_href;
			} else {
				attachment_url = img_src;
			}

			var data = {
				action: 'appthemes-get-post-attachment',
				url: attachment_url,
				title: a_text,
				ID: jQuery('#post_ID').val()
			};

			jQuery.post(ajaxurl, data, function(response) {
				if ( response.ID ) {
					jQuery('.attachments').append('\
					<tr>\
						<td class="thumb">' + response.thumbnail_html + '</td>\
						<td class="title"><input type="text" name="attachment['+response.ID+'][post_title]" value="' + encodeURIComponent( response.post_title ) + '" /></td>\
						<td class="date">' + response.upload_date + '</td>\
						<td class="dimensions">' + response.dimensions + '</td>\
						<td class="icons delete">\
							<span class="delete ui-icon ui-icon-circle-minus"></span>\
							<span class="grip ui-icon ui-icon-grip-solid-horizontal"></span>\
							<input class="menu_order" type="hidden" name="attachment['+response.ID+'][menu_order]" value="0" />\
						</td>\
					</tr>');
					index_attachments_list();
				} else {
					alert('Attachment Failed :(');
					console.log( response );
				}
				tb_remove();
				_content_override = null;
			}, 'json' );
		} else {
			window.original_send_to_editor( html );
		}
	}

	function index_attachments_list(){
		jQuery("#attachments > tbody").find('tr').each(function(i){
				jQuery(this).find('input.menu_order').val(i);
		});
	}

	jQuery("#attachments > tbody").on({
		click: function(){
			if ( !confirm( '<?php echo __( 'Are you sure you want to un-attach this media item from this post?', APP_TD ) ?>' ) )
				return;

			jQuery(this).parents('tr').hide();

			var attachment_id = jQuery(this).parents('tr').attr('id').split('-')[2];
			jQuery(this).append('<input type="hidden" name="attachment['+attachment_id+'][delete]" value="1" />' );
			index_attachments_list();

		} } , "td span.delete.ui-icon-circle-minus" );

	jQuery('#attachments > tbody').sortable({
		axis: "y",
		containment: '#attachments > tbody',
		tolerance: "pointer",
		distance: 5,
		opacity: 0.7,
		placeholder: "placeholder",
		forcePlaceholderSize: true,
		forceHelperSize: true,
		stop: function( a, b ){
			index_attachments_list();
		},
		start: function( a, b ){
			b.item.children('td.thumb').css( { 'width' : jQuery('#attachments_header > .thumb').width() });
			b.item.children('td.title').css( { 'width' : jQuery('#attachments_header > .title').width() });
			b.item.children('td.date').css( { 'width' : jQuery('#attachments_header > .date').width() });
			b.item.children('td.dimensions').css( { 'width' : jQuery('#attachments_header > .dimensions').width() });
			b.item.children('td.delete').css( { 'width' : jQuery('#attachments_header > .delete').width() });

			jQuery( '#attachments > tbody tr.placeholder' ).children('td').css({ 'height': b.item.children('td.thumb').height() });

		}
	});

});
//]]>
</script>
		<?php }

		public function display( $post ) {
			$this->display_styles();
			$this->display_scripts( $post );
		?>
<table id="attachments" class="form-table">
	<thead>
		<tr id="attachments_header">
			<th class="thumb"><?php _e( 'Thumbnail', APP_TD );?></th>
			<th class="title"><?php _e( 'Title', APP_TD ); ?></th>
			<th class="date"><?php _e( 'Upload Date', APP_TD ); ?></th>
			<th class="dimensions"><?php _e( 'Dimensions', APP_TD ); ?></th>
			<th class="delete"><?php _e( 'Detach/Sort', APP_TD ); ?></th>
		</tr>
	</thead>
	<tbody class="attachments">
		<?php
			$menu_order = 0;
			foreach( $this->get_attachments( $post ) as $attachment ) {
		?>
		<tr id="media-attachment-<?php echo $attachment->ID; ?>" class="media-attachment">
			<td class="thumb"><?php echo $this->display_attachment_thumbnail( $attachment ) ; ?></td>
			<td class="title"><input type="text" name="attachment[<?php echo $attachment->ID; ?>][post_title]" value="<?php echo $attachment->post_title; ?>" /></td>
			<td class="date"><?php echo appthemes_display_date( $attachment->post_date, 'date' ); ?></td>
			<td class="dimensions"><?php echo $this->display_attachment_dimensions( $attachment ); ?></td>
			<td class="icons delete">
				<span class="delete ui-icon ui-icon-circle-minus"></span>
				<span class="grip ui-icon ui-icon-grip-solid-horizontal"></span>
				<input class="menu_order" type="hidden" name="attachment[<?php echo $attachment->ID; ?>][menu_order]" value="<?php echo $menu_order; ?>" />
			</td>
		</tr>
		<?php
			$menu_order ++;
			}
		?>
	</tbody>
	<tfoot>
		<tr>
			<td><input id="upload_media_button" class="upload_button button" type="button" value="<?php _e( 'Add Media Attachment', APP_TD ); ?>" /></td>
			<td colspan="4"></td>
		</tr>
	</tfoot>
</table>
		<?php
	}

	function save( $post_id ) {
		if( !empty( $_POST['attachment'] ) ) {
			foreach( $_POST['attachment'] as $attachment_id => $attachment ) {
				if ( !empty( $attachment['delete'] ) ) {
					$updated_data = array(
						'ID' => absint( $attachment_id ),
						'post_parent' => 0
					);
				} else {
					$updated_data = array(
						'ID' => absint( $attachment_id ),
						'menu_order' => absint( $attachment['menu_order'] ),
						'post_title' => $attachment['post_title'],
					);

					update_post_meta( absint( $attachment_id ), '_wp_attachment_image_alt', $attachment['post_title'] );
				}
				wp_update_post( $updated_data );
			}
		}
	}
}

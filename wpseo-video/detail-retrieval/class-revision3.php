<?php
/**
 * @package    Internals
 * @since      1.8.0
 * @version    1.8.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Revision3 Video SEO Details
 *
 * {@internal [JRF] We can get much better information (duration, thumbnail, content_loc from the author RSS feeds,
 * but then we'd need to know the author in advance and the individual item rss address which I currently
 * haven't been able to figure out.
 * Even if we just had the author and episode nr, we'd be better off as we could construct the thumbnail and
 * content_loc, unfortunately, we normally won't have those.
 *
 * For future reference:
 * Content_loc: http://videos.revision3.com/revision3/web/[author handle]/[Episodenr - 4 digits]/[author handle]--[Episodenr]--[Episode title]--large.h264.mp4
 * Thumbnail_loc: http://videos.revision3.com/revision3/images/shows/[author handle]/[Episodenr - 4 digits]/[author handle]--[Episodenr]--[Episode title]--large.thumb.jpg }}
 *
 * @see http://revision3.com/anyhoo/feed/mp4-large For an example of an author RSS feed (not individual video)
 *
 *
 * JSON response format [2014/7/22]:
 * {
 *    "version":"1.0",
 *    "type":"video",
 *    "width":555,
 *    "height":337,
 *    "title":"How Much Can Humans Remember",
 *    "author_name":"Anyhoo",
 *    "author_url":"http://revision3.com/anyhoo/",
 *    "provider_name":"Revision3",
 *    "provider_url":"http://revision3.com",
 *    "cache_age":900,
 *    "html":"<iframe src=\"http://embed.revision3.com/player/embed?videoId=37621&external=true&width=555&height=337\" width=\"555\" height=\"337\" frameborder=\"0\" allowFullScreen mozAllowFullscreen webkitAllowFullScreen oallowfullscreen msallowfullscreen></iframe>"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Revision3' ) ) {

	/**
	 * Class WPSEO_Video_Details_Revision3
	 */
	class WPSEO_Video_Details_Revision3 extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://revision3.com/api/oembed/?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->html ) && preg_match( '`[&\?]videoId=([0-9]+)`', $this->decoded_response->html, $match ) ) {
				$this->vid['id'] = $match[1];
			}
		}

		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://embed.revision3.com/player/embed?videoId=' . urlencode( $this->vid['id'] ) . '&external=true';
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

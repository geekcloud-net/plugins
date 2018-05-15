<?php
/**
 * @package    Internals
 * @since      1.7.0
 * @version    1.7.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Metacafe Video SEO Details
 *
 * Full remote response (XML) format [2014/7/22] - see below class.
 *
 * @todo - maybe use decoding via simplexml ? should work and would make the code simpler
 * Or maybe even better: change this to use embedly. The metacafe proper API often doesn't return
 * any results when it should do, while embedly *will* give results.
 */
if ( ! class_exists( 'WPSEO_Video_Details_Metacafe' ) ) {

	/**
	 * Class WPSEO_Video_Details_Metacafe
	 *
	 * @link  http://help.metacafe.com/?page_id=238 Metacafe API docs - no longer available.
	 */
	class WPSEO_Video_Details_Metacafe extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`/(?:watch|w|embed|fplayer)/([0-9]+)(?:$|[/#\?\.])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://metacafe.com/w/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.metacafe.com/api/item/%s/',
			'replace_key'   => 'id',
			'response_type' => '',
		);


		/**
		 * Check if the response is for a valid video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && preg_match( '`<id>[0-9]+</id>`', $this->decoded_response ) );
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( preg_match( '`<media:content url="([^"]+)"`', $this->decoded_response, $match ) ) {
				$this->vid['content_loc'] = $match[1];
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( preg_match( '`duration="(\d+)"`', $this->decoded_response, $match ) ) {
				$this->vid['duration'] = $match[1];
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.metacafe.com/fplayer/' . rawurlencode( $this->vid['id'] ) . '/metacafe.swf';
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( preg_match( '`<media:thumbnail url="([^"]+)"`', $this->decoded_response, $match ) ) {
				$image = $this->make_image_local( $match[1] );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

/**
 * Remote response (XML) format [2014/7/22]:
 *
<?xml version="1.0" encoding="utf-8"?>
		<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" source="Metacafe">
			<title>Metacafe</title>
			<channel>
					<title></title>
					<link>http://www.metacafe.com/watch/7050424/</link>
					<image>
						<url>http://s.mcstatic.com/Images/MCLogo4RSS.jpg</url>
						<link>http://www.metacafe.com</link>
						<title>Metacafe</title>
						<height>65</height>
						<width>229</width>
					</image>
					<description></description>
					<item>
			<id>7050424</id>
			<author>SplashNews</author>
			<title>Arnold Schwarzenegger Stretches Out</title>
			<link>http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/</link>
					<rank>3.43</rank>
			<category>Entertainment</category>
			<description>
			<![CDATA[
					<a href="http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/"><img src="http://s1.mcstatic.com/thumb/7050424/19573114/4/directors_cut/0/1/arnold_schwarzenegger_stretches_out.jpg" align="right" border="0" alt="Arnold Schwarzenegger Stretches Out" vspace="4" hspace="4" width="134" height="78" /></a>
					<p>
					Is Arnold Schwarzenegger getting in shape now that he's single?					<br>Ranked <strong>3.43</strong> / 5 | 302 views | <a href="http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/">0 comments</a><br/>
					</p>
					<p>
						<a href="http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/"><strong>Click here to watch the video</strong></a> ()<br/>
						Submitted By: 						<a href="http://www.metacafe.com/channels/SplashNews/">SplashNews</a><br/>
						Tags:
						<a href="http://www.metacafe.com/topics/arnold_schwarzenegger/">Arnold Schwarzenegger</a>&nbsp;<a href="http://www.metacafe.com/topics/maria_shriver/">Maria Shriver</a>&nbsp;<a href="http://www.metacafe.com/topics/shape/">Shape</a>&nbsp;<a href="http://www.metacafe.com/topics/working_out/">Working Out</a>&nbsp;<a href="http://www.metacafe.com/topics/biking/">Biking</a>&nbsp;<a href="http://www.metacafe.com/topics/exercise/">Exercise</a>&nbsp;<a href="http://www.metacafe.com/topics/massachusetts/">Massachusetts</a>&nbsp;						<br/>
						Categories: <a href='http://www.metacafe.com/videos/entertainment/'>Entertainment</a> 					</p>
				]]>
			</description>
						<guid isPermaLink="true">http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/</guid>
			<pubDate>Tue, 23 Aug 2011 17:25:02 +0000</pubDate>
						<media:player url="http://www.metacafe.com/watch/7050424/arnold_schwarzenegger_stretches_out/" />
						<media:content url="http://www.metacafe.com/fplayer/7050424/arnold_schwarzenegger_stretches_out.swf" type="application/x-shockwave-flash" medium="video" height="360" width="640" duration="48" />
						<media:thumbnail url="http://s1.mcstatic.com/thumb/7050424/19573114/4/catalog_item5/0/1/arnold_schwarzenegger_stretches_out.jpg" />
			<media:title>Arnold Schwarzenegger Stretches Out</media:title>
			<media:keywords>Arnold Schwarzenegger,Maria Shriver,Shape,Working Out,Biking,Exercise,Massachusetts</media:keywords>
						<media:description>Is Arnold Schwarzenegger getting in shape now that he's single?</media:description>
						<media:credit>SplashNews</media:credit>
							<media:rating scheme="urn:simple">nonadult</media:rating>
				</item>
			</channel></rss>
 */

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
 * Muzu.tv Video SEO Details
 *
 * @see http://www.muzu.tv/api/
 *
 * Full remote response (XML) format [2014/7/22] - see below class.
 */
if ( ! class_exists( 'WPSEO_Video_Details_Muzutv' ) ) {

	/**
	 * Class WPSEO_Video_Details_Muzutv
	 */
	class WPSEO_Video_Details_Muzutv extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]muzu\.tv/(?:[^/]*/)*([0-9]+)(?:$|[/#\?])`';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.muzu.tv/api/video/details/%s?muzuid=b00q0xGOTl',
			'replace_key'   => 'id',
			'response_type' => 'simplexml',
		);


		/**
		 * Check if the response is for a valid video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && ( is_object( $this->xml ) && ( isset( $this->xml->channel->item->description ) && (string) $this->xml->channel->item->description !== 'Invalid video' ) ) );
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			$duration = $this->decoded_response->content->attributes()->duration;
			if ( ! empty( $duration ) ) {
				$this->vid['duration'] = (string) $duration;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			$height = $this->decoded_response->content->attributes()->height;
			if ( ! empty( $height ) ) {
				$this->vid['height'] = (string) $height;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'https://player.muzu.tv/player/getPlayer/i/293053/vidId=' . urlencode( $this->vid['id'] ) . '&autostart=y&dswf=y';
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->xml->channel->image->url ) ) {
				$image = $this->make_image_local( (string) $this->xml->channel->image->url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			$views = $this->xml->channel->item->children( 'http://www.muzu.tv/schemas/muzu/1.0' )->video->info->attributes()->views;
			if ( ! empty( $views ) ) {
				$this->vid['view_count'] = (string) $views;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			$width = $this->decoded_response->content->attributes()->width;
			if ( ! empty( $width ) ) {
				// @todo Why cast to string ? Int would be more logical
				$this->vid['width'] = (string) $width;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

/**
 * Remote response (XML) format [2014/7/22]:
 *
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:muzu="http://www.muzu.tv/schemas/muzu/1.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:av="http://www.searchvideo.com/schemas/av/1.0">
	<channel>
	<title>Sean Paul, Beenie Man - Greatest Gallis</title>
	<link>http://www.muzu.tv/sean-paul-beenie-man/greatest-gallis-music-video/1847016/</link>
	<description></description>
	<image>
	  <url>http://static.muzu.tv/media/images/001/847/016/001/1847016-thb3.jpg</url>
	  <title>Sean Paul, Beenie Man - Greatest Gallis</title>
	  <link>http://www.muzu.tv/sean-paul-beenie-man/greatest-gallis-music-video/1847016/</link>
	</image>
	<language>en-gb</language>
	<item>
	  <title>
		<![CDATA[Greatest Gallis]]>
	  </title>
	  <link>http://www.muzu.tv/sean-paul-beenie-man/greatest-gallis-music-video/1847016/</link>
	  <guid isPermaLink="false">MUZU:1847016</guid>
	  <pubDate>Tue, 23 Apr 2013 00:00:00 0000</pubDate>
	  <media:title>
		<![CDATA[Sean Paul, Beenie Man - Greatest Gallis]]>
	  </media:title>
	  <media:description>
		<![CDATA[Watch the official Greatest Gallis video by Sean Paul, Beenie Man in HD on WWW.MUZU.TV and check out the latest new music releases and playlists for free.]]>
	  </media:description>
	  <media:keywords>
		<![CDATA[dancehall,sean paul  beenie man,greatest gallis]]>
	  </media:keywords>
	  <media:copyright>
		<![CDATA[]]>
	  </media:copyright>
	  <media:content duration="191" medium="video" url="http://player.muzu.tv/player/getPlayer/i/291254/vidId=1847016&amp;la=n" type="application/x-shockwave-flash" bitrate="750" height="360" width="640"/>
	  <media:thumbnail url="http://static.muzu.tv/media/images/001/847/016/001/1847016-thb5.jpg" height="90" width="160"/>
	  <media:thumbnail url="http://static.muzu.tv/media/images/001/847/016/001/1847016-thb3.jpg" height="120" width="160"/>
	  <media:thumbnail url="http://static.muzu.tv/media/images/001/847/016/001/1847016-thb6.jpg" height="162" width="288"/>
	  <media:thumbnail url="http://static.muzu.tv/media/images/001/847/016/001/1847016-thb1.jpg" height="63" width="112"/>
	  <media:thumbnail url="http://static.muzu.tv/media/images/001/847/016/001/1847016-thb2.jpg" height="360" width="640"/>
	  <media:player url="http://www.muzu.tv/sean-paul-beenie-man/greatest-gallis-music-video/1847016/"/>
	  <media:credit role="artist">
		<![CDATA[Sean Paul, Beenie Man]]>
	  </media:credit>
	  <media:credit role="distribution company">
		<![CDATA[Gutty Bling Records / Claims Records]]>
	  </media:credit>
	  <media:category scheme="http://search.yahoo.com/mrss/category_schema">
		<![CDATA[music/Sean Paul, Beenie Man/Greatest Gallis]]>
	  </media:category>
	  <muzu:video>
		<muzu:info hdVersion="1" videotype="1" artistid="70139" sourceid="291254" uploaded="17-Apr-2013" mbid="" advisory="false" id="1847016" views="13727" isrc="USQY51364239" ownerid="52429" genre="Dancehall" upc="" sdVersion="1" keywords=""/>
		<muzu:versions v240p="true" ss="true" audioonly="false" v1080p="false" hls="false" v360p="true" v480p="true" v720p="true" mobileallowed="true"/>
		<muzu:channel vanity="sean-paul-beenie-man" name="Sean Paul, Beenie Man" url="http://www.muzu.tv/sean-paul-beenie-man/" labelvanity="ingroovesrecords"/>
	  </muzu:video>
	  <media:rating scheme="urn:simple">nonadult</media:rating>
	  <media:restriction relationship="allow" type="country">DE DK US ES GB FR FI AT AR AU PT BR CA BE SE CH CO MX NL NZ NO IE IT</media:restriction>
	  <av:videoPlayerEmbedTag>
		<![CDATA[<iframe frameborder="0" width="640" height="366" src="//player.muzu.tv/player/getPlayer/i/291254/?vidId=1847016&la=n" allowfullscreen></iframe>]]>
	  </av:videoPlayerEmbedTag>
	</item>
	</channel>
</rss>
 */

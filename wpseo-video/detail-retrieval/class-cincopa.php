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
 * Cincopa Video SEO Details
 *
 * @todo - maybe change over to using the json interface ?
 * http://www.cincopa.com/media-platform/runtime/json.aspx?fid=AgCAn_Zvs6Zl
 *
 * @see http://help.cincopa.com/entries/444299-Using-feeds-to-integrate-your-site-with-Cincopa
 * @see http://www.cincopa.com/developer/cincopaapi.aspx
 *
 * RSS response format [2014/7/22]:
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
	<link>http://www.cincopa.com/cmp/start.aspx?fid=10464405!25bdfbaa-bc56-4242-a77b-5d53f55faf4b!Zrt-FdXYq8927VNWnam5dB</link>

		<title>National Geographic wpplugin site</title>
		<description></description>

		<item>
			<title>Praesent Feugiat</title>
			<description>Praesent feugiat nulla at lectus lacinia auctor. Ut sed lacus. Sed cursus, metus non ornare mollis, justo tortor mattis turpis, eu malesuada lectus est id elit. Vivamus posuere pulvinar massa.</description>
			<media:thumbnail url="http://ec10.cdn.cincopa.com/1024x768_15.jpg?o=1&amp;res=152&amp;h2=3j4booooj1vsp43hmzqdiu1u40matybn&amp;cdn=ec&amp;p=y&amp;pid=66267&amp;ph3=kcal5kmi4g2351lhmj5s5i0jqt0cqxwv&amp;d=AsDA7AAFBAAAVy6nAYbOIDO" />
			<media:content type="image/jpeg" url="http://ec10.cdn.cincopa.com/1024x768_15.jpg?o=1&amp;res=152&amp;h2=3j4booooj1vsp43hmzqdiu1u40matybn&amp;cdn=ec&amp;p=y&amp;pid=66267&amp;ph3=kcal5kmi4g2351lhmj5s5i0jqt0cqxwv&amp;d=AsDA7AAFBAAAVy6nAYbOIDO&amp;as=mp3" />
			<link>http://www.cincopa.com/cmp/start.aspx?fid=10464405!25bdfbaa-bc56-4242-a77b-5d53f55faf4b!Zrt-FdXYq8927VNWnam5dB</link>
		</item>

		<item>
			....
		</item>
</channel>
</rss>
 *
 * Example JSON response format [2014/7/22]:
 * (
 *    "",
 *    {
 *       "items": [
 *           {
 *               "id":"47860448",
 *               "description":"Praesent feugiat nulla at lectus lacinia auctor. Ut sed lacus. Sed cursus, metus non ornare mollis, justo tortor mattis turpis, eu malesuada lectus est id elit. Vivamus posuere pulvinar massa.",
 *               "link":"http://www.cincopa.com/cmp/start.aspx?fid=10464405!25bdfbaa-bc56-4242-a77b-5d53f55faf4b!Zrt-FdXYq8927VNWnam5dB",
 *               "aspect_ratio":"1.33",
 *               "title":"Praesent Feugiat",
 *               "storage":"71",
 *               "content_url":"http://ec10.cdn.cincopa.com/1024x768_15.jpg?o=1\u0026amp;res=152\u0026amp;h2=3j4booooj1vsp43hmzqdiu1u40matybn\u0026amp;cdn=ec\u0026amp;p=y\u0026amp;pid=66267\u0026amp;ph3=kcal5kmi4g2351lhmj5s5i0jqt0cqxwv\u0026amp;d=AsDA7AAFBAAAVy6nAYbOIDO\u0026amp;as=mp3",
 *               "thumbnail_url":"http://ec10.cdn.cincopa.com/1024x768_15.jpg?o=2\u0026amp;res=152\u0026amp;h2=3j4booooj1vsp43hmzqdiu1u40matybn\u0026amp;cdn=ec\u0026amp;p=y\u0026amp;pid=66267\u0026amp;ph3=kcal5kmi4g2351lhmj5s5i0jqt0cqxwv\u0026amp;d=AsDA7AAFBAAAVy6nAYbOIDO",
 *               "content_type":"image/jpeg"
 *           },
 *           {
 *               ...
 *           }
 *       ],
 *       "title":"National Geographic wpplugin site",
 *       "description":""
 *   }
 * )
 */
if ( ! class_exists( 'WPSEO_Video_Details_Cincopa' ) ) {

	/**
	 * Class WPSEO_Video_Details_Cincopa
	 */
	class WPSEO_Video_Details_Cincopa extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`cp_load_widget\(\'([^\']+)\',[^\)]*\);`i';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.cincopa.com/media-platform/runtime/rss200.aspx?fid=%s',
			'replace_key'   => 'id',
			'response_type' => 'simplexml',
		);


		/**
		 * Retrieve the video id from a known video url based on parsing the url and a regex match.
		 *
		 * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( isset( $this->vid['url'] ) && ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) && $this->id_regex !== '' ) {
				$parse = WPSEO_Video_Analyse_Post::wp_parse_url( $this->vid['url'] );
				if ( isset( $parse['query'] ) && preg_match( $this->id_regex, $parse['query'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			$url = $this->decoded_response->content->attributes()->url;
			if ( ! empty( $url ) ) {
				$this->vid['player_loc'] = (string) $url;
			}
		}

		/**
		 * Set the thumbnail location
		 *
		 * @todo: thumbnails are not working currently b/c $this->make_image_local() strips query parameters
		 * and this video service needs query params to generate thumbnails, look for a more direct approach
		 */
		protected function set_thumbnail_loc() {
			$url = $this->decoded_response->content->attributes()->url;
			if ( ! empty( $url ) ) {
				$image = $this->make_image_local( (string) $url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

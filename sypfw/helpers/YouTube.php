<?php

class YouTube {

	public static function getThumb($url) {
		preg_match("/v=([^&]+)/i", $url, $matches);
		$id = $matches[1];
		return 'http://img.youtube.com/vi/' . $id . '/1.jpg';
	}

	public static function getEmbed($url) {
		preg_match("/v=([^&]+)/i", $url, $matches);
		$id = $matches[1];
		return 'http://www.youtube.com/embed/' . $id;
	}

	public static function getFullEmbed($url) {
		return file_get_contents(YouTube::getEmbed($url));
	}

	public static function getObject($url, $w = '460', $h='345') {
		preg_match("/v=([^&]+)/i", $url, $matches);
		$id = $matches[1];

		// this is your template for generating <strong class="highlight">embed</strong> codes
		$code = '<object width="{w}" height="{h}">
			<param name="movie" value="http://www.youtube.com/v/{id}&hl=en_US&fs=1&"></param>
			<param name="allowFullScreen" value="true"></param>
			<param name="wmode" value="transparent"></param>
			<param name="allowscriptaccess" value="always"></param>
			<embed src="http://www.youtube.com/v/{id}&hl=en_US&fs=1&" 
			type="application/x-shockwave-flash" 
			allowscriptaccess="always" 
			wmode="transparent"
			allowfullscreen="true" 
			width="{w}" height="{h}"></embed>
			</object>';

		// we replace each {id} with the actual ID of the video to get <strong class="highlight">embed</strong> code for this particular video
		$code = str_replace(
				  array('{id}', '{w}', '{h}'),
				  array($id, $w, $h), 
				  $code
		);
		return $code;
	}

}

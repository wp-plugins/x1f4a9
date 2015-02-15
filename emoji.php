<?php
/*
Plugin Name: 💩
Description: Twitters Emoji for WordPress
Version: 0.3-20150215

See https://github.com/twitter/twemoji for the source emoji
*/

class Emoji {
	public static $cdn_url = '//s0.wp.com/wp-content/mu-plugins/emoji/twemoji/72x72/';

	public static function init() {
		wp_register_script( 'twemoji', plugins_url( 'twemoji/twemoji.js',   __FILE__ ) );
		wp_enqueue_script(  'emoji',   plugins_url( 'emoji.js', __FILE__ ), array( 'twemoji' ) );

		wp_enqueue_style( 'emoji-css', plugins_url( 'emoji.css', __FILE__ ) );

		add_action( 'mce_external_plugins', array( __CLASS__, 'add_mce_plugin' ) );
		add_action( 'wp_enqueue_editor',    array( __CLASS__, 'load_mce_script' ) );

		add_action( 'wp_insert_post_data', array( __CLASS__, 'filter_post_fields' ), 10, 1 );

		add_filter( 'smilies_src', array( __CLASS__, 'filter_smileys' ), 10, 2 );

		add_filter( 'the_content_feed', array( __CLASS__, 'feed_emoji' ), 10, 1 );
		add_filter( 'the_excerpt_rss',  array( __CLASS__, 'feed_emoji' ), 10, 1 );
		add_filter( 'comment_text_rss', array( __CLASS__, 'feed_emoji' ), 10, 1 );

		add_filter( 'wp_mail', array( __CLASS__, 'mail_emoji' ), 10, 1 );
	}

	public static function add_mce_plugin( $plugins ) {
		$plugins['emoji'] = plugins_url( 'tinymce/plugin.js', __FILE__ );
		return $plugins;
	}

	public static function load_mce_script( $opts ) {
		if ( $opts['tinymce'] ) {
			wp_enqueue_script( 'emoji' );
		}
	}

	public static function filter_post_fields( $data ) {
		global $wpdb;
		$fields = array( 'post_title', 'post_content', 'post_excerpt' );

		foreach( $fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$charset = $wpdb->get_col_charset( $wpdb->posts, $field );
				if ( 'utf8' === $charset ) {
					$data[ $field ] = Emoji::wp_encode_emoji( $data[ $field ] );
				}
			}
		}
		return $data;
	}

	/**
	 * Convert any 4 byte emoji in a string to their equivalent HTML entity.
	 * Currently, only Unicode 7 emoji are supported. Unicode 8 emoji will be added
	 * when the spec in finalised, along with the new skin-tone modifiers.
	 *
	 * This allows us to store emoji in a DB using the utf8 character set.
	 *
	 * @since 4.2.0
	 * @param string $content The content to encode
	 * @param bool $static Whether to encode the emoji as static image links. Optional, default false.
	 * @return string The encoded content
	 */
	public static function wp_encode_emoji( $content, $static = false ) {
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$regex = '/(
			     \x23\xE2\x83\xA3               # Digits
			     [\x30-\x39]\xE2\x83\xA3
			   | \xF0\x9F[\x85-\x88][\xB0-\xBF] # Enclosed characters
			   | \xF0\x9F[\x8C-\x97][\x80-\xBF] # Misc
			   | \xF0\x9F\x98[\x80-\xBF]        # Smilies
			   | \xF0\x9F\x99[\x80-\x8F]
			   | \xF0\x9F\x9A[\x80-\xBF]        # Transport and map symbols
			   | \xF0\x9F\x99[\x80-\x85]
			)/x';

			$matches = array();
			if ( preg_match_all( $regex, $content, $matches ) ) {
				if ( ! empty( $matches[1] ) ) {
					foreach( $matches[1] as $emoji ) {
						/*
						 * UTF-32's hex encoding is the same as HTML's hex encoding.
						 * So, by converting the emoji from UTF-8 to UTF-32, we magically
						 * get the correct hex encoding.
						 */
						$unpacked = unpack( 'H*', mb_convert_encoding( $emoji, 'UTF-32', 'UTF-8' ) );
						if ( isset( $unpacked[1] ) ) {
							$entity = trim( $unpacked[1], '0' );
							if ( $static ) {
								$entity = '<img src="https:' . Emoji::$cdn_url . $entity . '.png" class="wp-smiley" style="height: 1em;" />';
							} else {
								$entity = '&#x' . $entity . ';';
							}
							$content = str_replace( $emoji, $entity, $content );
						}
					}
				}
			}
		}

		return $content;
	}

	public static function filter_smileys( $url, $img ) {
		switch ( $img ) {
			case 'icon_mrgreen.gif':
				return plugins_url( 'smileys/mrgreen.png', __FILE__ );
			case 'icon_neutral.gif':
				return Emoji::$cdn_url . '1f610.png';
			case 'icon_twisted.gif':
				return Emoji::$cdn_url . '1f608.png';
			case 'icon_arrow.gif':
				return Emoji::$cdn_url . '27a1.png';
			case 'icon_eek.gif':
				return Emoji::$cdn_url . '1f62f.png';
			case 'icon_smile.gif':
				return plugins_url( 'smileys/simple-smile.png', __FILE__ );
			case 'icon_confused.gif':
				return Emoji::$cdn_url . '1f62f.png';
			case 'icon_cool.gif':
				return Emoji::$cdn_url . '1f60e.png';
			case 'icon_evil.gif':
				return Emoji::$cdn_url . '1f47f.png';
			case 'icon_biggrin.gif':
				return Emoji::$cdn_url . '1f604.png';
			case 'icon_idea.gif':
				return Emoji::$cdn_url . '1f4a1.png';
			case 'icon_redface.gif':
				return Emoji::$cdn_url . '1f633.png';
			case 'icon_razz.gif':
				return Emoji::$cdn_url . '1f61b.png';
			case 'icon_rolleyes.gif':
				return plugins_url( 'smileys/rolleyes.png', __FILE__ );
			case 'icon_wink.gif':
				return Emoji::$cdn_url . '1f609.png';
			case 'icon_cry.gif':
				return Emoji::$cdn_url . '1f625.png';
			case 'icon_surprised.gif':
				return Emoji::$cdn_url . '1f62f.png';
			case 'icon_lol.gif':
				return Emoji::$cdn_url . '1f604.png';
			case 'icon_mad.gif':
				return Emoji::$cdn_url . '1f621.png';
			case 'icon_sad.gif':
				return Emoji::$cdn_url . '1f626.png';
			case 'icon_exclaim.gif':
				return Emoji::$cdn_url . '2757.png';
			case 'icon_question.gif':
				return Emoji::$cdn_url . '2753.png';
			default:
				return $url;
		}
	}

	public static function static_emoji( $content ) {
		return Emoji::wp_encode_emoji( $content, true );
	}

	public static function mail_emoji( $mail ) {
		$mail['message'] = Emoji::wp_encode_emoji( $mail['message'], true );
		return $mail;
	}
}

add_action( 'init', array( 'Emoji', 'init' ) );

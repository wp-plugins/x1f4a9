var WPEmoji;

(function() {
	WPEmoji = {
		EMOJI_SIZE: 72,
		BASE_URL: '//s0.wp.com/wp-content/mu-plugins/emoji/twemoji/',

		init: function() {
			var size, base_url;
			if ( typeof EmojiSettings !== 'undefined' ) {
				size = EmojiSettings.size || null;
				base_url = EmojiSettings.base_url || null;
			}

			WPEmoji.parse( document.body, size, base_url );

			if ( typeof infiniteScroll !== 'undefined' ) {
				jQuery( document.body ).on( 'post-load', function( response ) {
					// TODO: ideally, we should only target the newly added elements
					emoji.parse( document.body, size, base_url );
				} );
			}
		},

		parse: function( element, size, base_url ) {
			twemoji.parse( element, {
				size: this.EMOJI_SIZE,
				base: this.BASE_URL,
				callback: function(icon, options, variant) {
					// Ignore some standard characters that TinyMCE recommends in its character map.
					switch ( icon ) {
						case 'a9':
						case 'ae':
						case '2122':
						case '2194':
						case '2660':
						case '2663':
						case '2665':
						case '2666':
							return false;
					}

					// directly from twemoji
					return ''.concat(options.base, options.size, '/', icon, options.ext);
				}
			} );
		},
	}

	if ( window.addEventListener ) {
		window.addEventListener( 'load', WPEmoji.init, false );
	} else if ( window.attachEvent ) {
		window.attachEvent( 'onload', WPEmoji.init );
	}
})();

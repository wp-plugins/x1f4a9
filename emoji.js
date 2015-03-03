var WPEmoji;

(function() {
	WPEmoji = {
		BASE_URL: '//s0.wp.com/wp-content/mu-plugins/emoji/twemoji/72x72',

		parseEmoji: false,
		onlyParseFlags: false,

		init: function() {
			var size, base_url;
			if ( typeof EmojiSettings !== 'undefined' ) {
				base_url = EmojiSettings.base_url || null;
			}

			WPEmoji.parseEmoji = ! WPEmoji.browserSupportsEmoji() || ! WPEmoji.browserSupportsFlagEmoji();
			WPEmoji.onlyParseFlags = ! WPEmoji.browserSupportsFlagEmoji();

			if ( ! WPEmoji.parseEmoji ) {
				return;
			}

			WPEmoji.parse( document.body, size, base_url );

			if ( typeof infiniteScroll !== 'undefined' ) {
				jQuery( document.body ).on( 'post-load', function( response ) {
					WPEmoji.parse( infiniteScroll.scroller.element.get( 0 ), base_url );
				} );
			}
		},

		browserSupportsEmoji: function() {
			var context, smiley;

			if ( ! document.createElement( 'canvas' ).getContext ) {
				return;
			}

			context = document.createElement( 'canvas' ).getContext( '2d' );
			if ( typeof context.fillText != 'function' ) {
				return;
			}

			smile = String.fromCharCode( 55357 ) + String.fromCharCode( 56835 );

			context.textBaseline = "top";
			context.font = "32px Arial";
			context.fillText( smile, 0, 0 );

			return context.getImageData( 16, 16, 1, 1 ).data[0] !== 0;
		},

		browserSupportsFlagEmoji: function() {
			var context, smiley, canvas;

			canvas = document.createElement( 'canvas' );

			if ( ! canvas.getContext ) {
				return;
			}

			context = canvas.getContext( '2d' );

			if ( typeof context.fillText != 'function' ) {
				return;
			}

			smile =  String.fromCharCode(55356) + String.fromCharCode(56812); // [G]
			smile += String.fromCharCode(55356) + String.fromCharCode(56807); // [B]

			context.textBaseline = "top";
			context.font = "32px Arial";
			context.fillText( smile, 0, 0 );

			/*
			 * Sooooo.... this works because the image will be one of three things:
			 * - Two empty squares, if the browser doen't render emoji
			 * - Two squares with 'G' and 'B' in them, if the browser doen't render flag emoji
			 * - The British flag
			 *
			 * The first two will encode to very small images (1-2KB data URLs), the third will encode
			 * to a large image (4-5KB data URL).
			 *
			 * There are probably less dumb ways to do this.
			 */
			return canvas.toDataURL().length > 3000;

		},

		parse: function( element, base_url ) {
			if ( ! WPEmoji.parseEmoji ) {
				return;
			}

			return twemoji.parse( element, {
				base: base_url || this.BASE_URL,
				callback: function( icon, options, variant ) {
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

					if ( WPEmoji.onlyParseFlags && ! icon.match( /1f1(e[6-9a-f]|f[1-9a-f])-1f1(e[6-9a-f]|f[1-9a-f])/ ) ) {
						return false;
					}

					return ''.concat( options.base, '/', icon, options.ext );
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

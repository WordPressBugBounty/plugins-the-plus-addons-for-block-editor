<?php
/**
 * SVG Sanitizer.
 *
 * Removes active/executable content (scripts, event handlers, external and
 * javascript: references, entity definitions, etc.) from SVG markup so that an
 * uploaded SVG cannot run JavaScript in the site origin. This is the content
 * layer of the fix for the "unrestricted SVG upload -> stored XSS" issue; a
 * capability gate on the allowed upload MIME types is the access layer.
 *
 * @package ThePluginAddonsForBlockEditor
 * @since 5.0.2
 */

// Follows this plugin's existing tp-*.php class-file naming instead of class-*.php.
// phpcs:disable WordPress.Files.FileName
// PHP's DOMDocument API exposes camelCase properties (nodeName, childNodes, documentElement, localName, nodeValue, …) that cannot be renamed to snake_case.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tpgb_Svg_Sanitizer' ) ) {

	/**
	 * Class Tpgb_Svg_Sanitizer.
	 *
	 * Allowlist-based SVG scrubber built on DOMDocument.
	 */
	class Tpgb_Svg_Sanitizer {

		/**
		 * Allowed element names (lower-cased local names).
		 *
		 * Anything not listed here is dropped. Notably absent: script,
		 * foreignObject, iframe, embed, audio, video, handler, listener and the
		 * SMIL animation elements (animate/set/…) which can retarget attributes
		 * to event handlers or javascript: URIs.
		 *
		 * @var array
		 */
		private static $allowed_tags = array(
			'a',
			'altglyph',
			'altglyphdef',
			'altglyphitem',
			'circle',
			'clippath',
			'defs',
			'desc',
			'ellipse',
			'feblend',
			'fecolormatrix',
			'fecomponenttransfer',
			'fecomposite',
			'feconvolvematrix',
			'fediffuselighting',
			'fedisplacementmap',
			'fedistantlight',
			'feflood',
			'fefunca',
			'fefuncb',
			'fefuncg',
			'fefuncr',
			'fegaussianblur',
			'feimage',
			'femerge',
			'femergenode',
			'femorphology',
			'feoffset',
			'fepointlight',
			'fespecularlighting',
			'fespotlight',
			'fetile',
			'feturbulence',
			'filter',
			'font',
			'g',
			'glyph',
			'glyphref',
			'hkern',
			'image',
			'line',
			'lineargradient',
			'marker',
			'mask',
			'metadata',
			'mpath',
			'path',
			'pattern',
			'polygon',
			'polyline',
			'radialgradient',
			'rect',
			'stop',
			'style',
			'svg',
			'switch',
			'symbol',
			'text',
			'textpath',
			'title',
			'tref',
			'tspan',
			'use',
			'view',
			'vkern',
		);

		/**
		 * Attributes removed regardless of the element they sit on.
		 *
		 * Event handlers (any name beginning with "on") are handled separately.
		 *
		 * @var array
		 */
		private static $blocked_attrs = array(
			'contentscripttype',
			'contentstyletype',
			'xlink:actuate',
			'seed',
		);

		/**
		 * Attributes whose value is treated as a URI and scheme-checked.
		 *
		 * @var array
		 */
		private static $uri_attrs = array( 'href', 'src', 'xlink:href', 'from', 'to', 'by', 'values', 'attributename', 'begin' );

		/**
		 * Sanitize a raw SVG string.
		 *
		 * @param string $dirty Raw SVG markup.
		 * @return string|false Cleaned SVG, or false when the input is not a
		 *                      parseable SVG / cannot be made safe.
		 */
		public static function sanitize( $dirty ) {
			if ( ! is_string( $dirty ) || '' === trim( $dirty ) ) {
				return false;
			}

			// Strip a UTF-8 BOM if present.
			$dirty = preg_replace( '/^\xEF\xBB\xBF/', '', $dirty );

			// Must actually contain an <svg> root.
			if ( false === stripos( $dirty, '<svg' ) ) {
				return false;
			}

			// Defensive: strip PHP open tags that have no business in an SVG.
			$dirty = preg_replace( '/<\?(?:php|=).*?(?:\?>|$)/is', '', $dirty );

			if ( ! class_exists( 'DOMDocument' ) ) {
				// Cannot parse safely -> fail closed.
				return false;
			}

			$prev_errors = libxml_use_internal_errors( true );

			// On PHP < 8 external entity loading must be disabled explicitly to
			// prevent XXE; on PHP >= 8 it is off by default and the function is
			// deprecated, so it is only called on older runtimes.
			$reset_loader = null;
			if ( PHP_VERSION_ID < 80000 && function_exists( 'libxml_disable_entity_loader' ) ) {
				$reset_loader = libxml_disable_entity_loader( true ); // phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated
			}

			$dom                      = new DOMDocument();
			$dom->preserveWhiteSpace  = false;
			$dom->strictErrorChecking = false;
			$dom->formatOutput        = false;

			// LIBXML_NONET blocks any network access during parsing. Never pass
			// LIBXML_NOENT here: it would enable entity substitution (XXE / entity
			// expansion). Parsing failures are swallowed via internal errors.
			$flags  = LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING;
			$loaded = $dom->loadXML( $dirty, $flags );

			libxml_clear_errors();
			libxml_use_internal_errors( $prev_errors );
			if ( null !== $reset_loader && function_exists( 'libxml_disable_entity_loader' ) ) {
				libxml_disable_entity_loader( $reset_loader ); // phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated
			}

			if ( ! $loaded || null === $dom->documentElement ) {
				return false;
			}

			// Remove any DOCTYPE / entity definitions (XXE, entity-expansion).
			foreach ( iterator_to_array( $dom->childNodes ) as $child ) {
				if ( XML_DOCUMENT_TYPE_NODE === $child->nodeType && $dom->parentNode !== $child ) {
					$dom->removeChild( $child );
				}
			}

			// The root element must be <svg>.
			if ( null === $dom->documentElement || 'svg' !== strtolower( (string) $dom->documentElement->localName ) ) {
				return false;
			}

			self::clean_node( $dom->documentElement );

			$clean = $dom->saveXML( $dom->documentElement );
			if ( false === $clean || '' === trim( (string) $clean ) ) {
				return false;
			}

			return $clean;
		}

		/**
		 * Sanitize an SVG file in place.
		 *
		 * @param string $path Absolute path to the file on disk.
		 * @return bool True when the file now holds safe SVG, false on failure.
		 */
		public static function sanitize_file( $path ) {
			if ( empty( $path ) || ! is_string( $path ) || ! file_exists( $path ) || ! is_readable( $path ) ) {
				return false;
			}

			$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local uploaded file, WP_Filesystem not applicable here.
			if ( false === $raw ) {
				return false;
			}

			$clean = self::sanitize( $raw );
			if ( false === $clean ) {
				return false;
			}

			// Nothing dangerous was present -> avoid a needless rewrite.
			if ( $clean === $raw ) {
				return true;
			}

			return false !== file_put_contents( $path, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local uploaded file, WP_Filesystem not applicable here.
		}

		/**
		 * Recursively scrub a DOM node: drop disallowed elements, comments and
		 * processing instructions, and strip dangerous attributes.
		 *
		 * @param DOMNode $node Node to clean.
		 * @return void
		 */
		private static function clean_node( $node ) {
			// Recurse first (snapshot the live NodeList because we mutate it).
			if ( $node->hasChildNodes() ) {
				foreach ( iterator_to_array( $node->childNodes ) as $child ) {
					if ( XML_ELEMENT_NODE === $child->nodeType ) {
						self::clean_node( $child );
					} elseif ( XML_PI_NODE === $child->nodeType || XML_COMMENT_NODE === $child->nodeType ) {
						$node->removeChild( $child );
					}
				}
			}

			if ( XML_ELEMENT_NODE !== $node->nodeType ) {
				return;
			}

			$tag = strtolower( (string) $node->localName );

			// Drop disallowed elements entirely.
			if ( ! in_array( $tag, self::$allowed_tags, true ) ) {
				if ( $node->parentNode ) {
					$node->parentNode->removeChild( $node );
				}
				return;
			}

			// Scrub <style> text content for CSS-borne vectors.
			if ( 'style' === $tag ) {
				if ( self::has_dangerous_css( (string) $node->textContent ) && $node->parentNode ) {
					$node->parentNode->removeChild( $node );
					return;
				}
			}

			if ( ! $node->attributes || ! $node->attributes->length ) {
				return;
			}

			foreach ( iterator_to_array( $node->attributes ) as $attr ) {
				$name  = strtolower( (string) $attr->nodeName );
				$local = strtolower( (string) $attr->localName );
				$value = (string) $attr->nodeValue;

				$remove = false;

				// Event handlers (onload, onclick, …).
				if ( 0 === strpos( $name, 'on' ) || 0 === strpos( $local, 'on' ) ) {
					$remove = true;
				}

				// Explicit block list.
				if ( ! $remove && in_array( $name, self::$blocked_attrs, true ) ) {
					$remove = true;
				}

				// style="" attribute with CSS-borne vectors.
				if ( ! $remove && 'style' === $local && self::has_dangerous_css( $value ) ) {
					$remove = true;
				}

				// URI-bearing attributes with dangerous schemes.
				if ( ! $remove && in_array( $local, self::$uri_attrs, true ) && self::has_dangerous_uri( $value ) ) {
					$remove = true;
				}

				if ( $remove ) {
					$node->removeAttributeNode( $attr );
				}
			}
		}

		/**
		 * Detect dangerous schemes / payloads inside a URI-like value.
		 *
		 * Allows same-document fragments, http(s), mailto, relative paths and
		 * base64 raster data URIs. Rejects javascript:/vbscript:/data:text|svg
		 * and control-character obfuscation.
		 *
		 * @param string $value Attribute value.
		 * @return bool
		 */
		private static function has_dangerous_uri( $value ) {
			$decoded = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
			// Remove all whitespace / control chars used to obfuscate schemes.
			$check = strtolower( preg_replace( '/[\x00-\x20\x7f]+/', '', $decoded ) );

			if ( '' === $check ) {
				return false;
			}

			// Safe raster data URIs only.
			if ( 0 === strpos( $check, 'data:' ) ) {
				return ! preg_match( '#^data:image/(?:png|jpe?g|gif|webp);base64,#', $check );
			}

			// Explicitly dangerous schemes.
			if ( preg_match( '/^(?:javascript|vbscript|livescript|mocha|about|blob|filesystem):/', $check ) ) {
				return true;
			}

			// A ":" before any "/" or "#" that is not http(s)/mailto/tel/ftp is
			// treated as an unknown (potentially dangerous) scheme.
			if ( preg_match( '/^([a-z][a-z0-9+.\-]*):/', $check, $m ) ) {
				return ! in_array( $m[1], array( 'http', 'https', 'mailto', 'tel', 'ftp' ), true );
			}

			return false;
		}

		/**
		 * Detect dangerous tokens inside CSS (style attribute / <style> body).
		 *
		 * @param string $css CSS text.
		 * @return bool
		 */
		private static function has_dangerous_css( $css ) {
			$check = strtolower( html_entity_decode( $css, ENT_QUOTES, 'UTF-8' ) );
			$check = preg_replace( '/[\x00-\x08\x0b\x0c\x0e-\x1f]+/', '', $check );

			foreach ( array( 'javascript:', 'vbscript:', 'expression(', '@import', 'behavior:', '-moz-binding', 'data:text', 'data:image/svg' ) as $needle ) {
				if ( false !== strpos( $check, $needle ) ) {
					return true;
				}
			}

			// url(...) pointing at anything other than a safe raster data URI or
			// http(s)/relative target.
			if ( preg_match_all( '/url\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)/', $check, $matches ) ) {
				foreach ( $matches[1] as $url ) {
					if ( self::has_dangerous_uri( $url ) ) {
						return true;
					}
				}
			}

			return false;
		}
	}
}

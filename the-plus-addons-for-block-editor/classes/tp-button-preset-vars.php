<?php // phpcs:ignore WordPress.Files.FileName -- filename mirrors its JS counterpart src/helpers/button-preset-vars.js.
/**
 * Button Preset → CSS Variables (PHP mirror)
 * -----------------------------------------------------------------------------
 * Mirrors src/helpers/button-preset-vars.js. Converts a map of button presets
 * (keyed by preset id, e.g. "btnpreset1") into a :root {} stylesheet using
 * semantic variable names:
 *
 *   --tpgb-{presetKey}-{attrKey}-{subField}
 *
 * Any change to the naming scheme or the set of PRESET_KEYS must be made in
 * BOTH this file and the JS emitter to keep editor and frontend output in sync.
 *
 * @package TPGB
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tpgb_Button_Preset_Vars' ) ) {

	/**
	 * Button Preset → CSS Variables (PHP mirror).
	 *
	 * Mirrors the button-preset-vars.js emitter so the editor and the frontend
	 * produce identical `--tpgb-{preset}-*` custom properties and var() refs.
	 */
	class Tpgb_Button_Preset_Vars {

		/**
		 * Keys the preset is allowed to drive on a button block.
		 *
		 * `bthShadow` mirrors `btshadow` but lands on the :hover state.
		 *
		 * @var array
		 */
		public static $preset_keys = array(
			'bTypo',
			'btColor',
			'bthColor',
			'btBg',
			'bthBg',
			'bBord',
			'bthBColor',
			'brad',
			'btPad',
			'btshadow',
			'bthShadow',
			'tShadow',
		);

		/**
		 * Small-breakpoint media query. Mirrors the JS helper; keep in sync.
		 *
		 * @var string
		 */
		private static $media_sm = '@media (max-width: 1024px)';

		/**
		 * Extra-small-breakpoint media query. Mirrors the JS helper; keep in sync.
		 *
		 * @var string
		 */
		private static $media_xs = '@media (max-width: 767px)';

		/**
		 * Request-scoped cache for the active preset map.
		 *
		 * Avoids re-hitting get_option() once per button block when the same
		 * post renders many. null = not yet loaded; array (possibly empty) = loaded.
		 *
		 * @var array|null
		 */
		private static $presets_cache = null;

		/**
		 * Read active button presets. Result is memoized for the duration of
		 * the PHP request. Resolution order mirrors the JS helper:
		 *   1. tpgb-block-global-style → __buttonCore.buttonPresets (canonical mirror)
		 *   2. tpgb_global_options → presets[<active>].buttonPresets (current JS write path)
		 *   3. tpgb_global_options → buttonPresets (legacy flat — pre-rework saves)
		 *
		 * Both options are autoloaded by WordPress, so these get_option()
		 * calls hit the in-process alloptions cache after the first read and
		 * do NOT issue per-request SQL queries. On top of that, the local
		 * static cache below skips repeated json_decode + array walk work
		 * when a page renders many button blocks.
		 */
		public static function get_active_presets() {
			if ( null !== self::$presets_cache ) {
				return self::$presets_cache;
			}

			// 1. Primary store — tpgb_global_options. This is what JS's
			// `window.plusGlobalOpt.settings` mirrors, which is also where
			// buildActivePresetVarsCss() (writes plus-global.css) reads
			// from. Keeping PHP in lockstep with JS here is critical:
			// otherwise a preset rename updates the primary store + JS-
			// written :root vars, but PHP falls through to the stale
			// `tpgb-block-global-style` mirror below and emits the old
			// slug into per-post CSS. The result is a slug mismatch where
			// consumer var refs never resolve.
			$raw = get_option( 'tpgb_global_options', '' );
			if ( is_array( $raw ) ) {
				$dec = $raw;
			} elseif ( is_string( $raw ) && '' !== $raw ) {
				$decoded = json_decode( $raw, true );
				$dec     = is_array( $decoded ) ? $decoded : array();
			} else {
				$dec = array();
			}

			if ( is_array( $dec ) && isset( $dec['active'] ) && isset( $dec['presets'][ $dec['active'] ] ) ) {
				$active_preset = $dec['presets'][ $dec['active'] ];
				if ( ! empty( $active_preset['buttonPresets'] ) && is_array( $active_preset['buttonPresets'] ) ) {
					self::$presets_cache = $active_preset['buttonPresets'];
					return self::$presets_cache;
				}
			}

			if ( is_array( $dec ) && ! empty( $dec['buttonPresets'] ) && is_array( $dec['buttonPresets'] ) ) {
				self::$presets_cache = $dec['buttonPresets'];
				return self::$presets_cache;
			}

			// 2. Fallback — tpgb-block-global-style.__buttonCore.buttonPresets.
			// Only hit when the primary store hasn't been written yet
			// (fresh install before first Save Preset, or legacy data
			// only in the mirror).
			$bgs = get_option( 'tpgb-block-global-style', '' );
			if ( is_array( $bgs ) ) {
				$bd = $bgs;
			} elseif ( is_string( $bgs ) && '' !== $bgs ) {
				$bgs_decoded = json_decode( $bgs, true );
				$bd          = is_array( $bgs_decoded ) ? $bgs_decoded : array();
			} else {
				$bd = array();
			}
			if ( ! empty( $bd['__buttonCore']['buttonPresets'] ) && is_array( $bd['__buttonCore']['buttonPresets'] ) ) {
				self::$presets_cache = $bd['__buttonCore']['buttonPresets'];
				return self::$presets_cache;
			}

			self::$presets_cache = array();
			return self::$presets_cache;
		}

		/** Clear the request-scoped cache — use after the preset option is
		 *  written within the same PHP request, so subsequent reads see
		 *  the new data. */
		public static function invalidate_cache() {
			self::$presets_cache = null;
		}

		/**
		 * Resolve a preset key to its CSS class / variable namespace slug.
		 *
		 * PHP mirror of presetClassSlug() in button-preset-vars.js.
		 * Returns the human-readable identifier ("btnpreset-{slug-of-name}")
		 * used as both the wrapper CSS class and the CSS variable namespace.
		 * Falls back to the raw key when the preset has no name or doesn't
		 * exist (defensive — keeps frontend rendering valid even if the JS
		 * and PHP stores get out of sync briefly).
		 *
		 * @param string     $preset_key Preset identifier (e.g. "btnpreset1").
		 * @param array|null $presets    Optional preset map; loaded when null.
		 * @return string Slug, or the raw key as a fallback.
		 */
		public static function preset_class_slug( $preset_key, $presets = null ) {
			if ( empty( $preset_key ) ) {
				return '';
			}
			if ( null === $presets ) {
				$presets = self::get_active_presets();
			}
			if ( empty( $presets[ $preset_key ] ) ) {
				return $preset_key;
			}
			$preset = $presets[ $preset_key ];
			if ( is_object( $preset ) ) {
				$preset = json_decode( wp_json_encode( $preset ), true );
			}
			$name = isset( $preset['name'] ) ? $preset['name'] : '';
			if ( '' === $name ) {
				return $preset_key;
			}
			$slug = strtolower( trim( (string) $name ) );
			$slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );
			$slug = trim( (string) $slug, '-' );
			return ( '' === $slug ) ? $preset_key : ( 'btnpreset-' . $slug );
		}

		// ----- value helpers ------------------------------------------------.

		/**
		 * Append a CSS unit to a raw numeric value.
		 *
		 * @param mixed  $v    Raw value.
		 * @param string $unit Unit to append (defaults to 'px').
		 * @return string Value with a unit, or '' when empty.
		 */
		private static function with_unit( $v, $unit ) {
			if ( null === $v || '' === $v ) {
				return '';
			}
			$s = (string) $v;
			if ( 'auto' === $s || 'inherit' === $s ) {
				return $s;
			}
			if ( preg_match( '/[a-z%]$/i', $s ) ) {
				return $s;
			}
			return $s . ( $unit ? $unit : 'px' );
		}

		// ----- per-attribute emitters --------------------------------------.

		/**
		 * Emit a single scalar attribute as a { attrKey => value } map.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw value.
		 * @return array Var map, or empty array when the value is blank.
		 */
		private static function scalar_vars( $attr_key, $val ) {
			if ( null === $val || '' === $val ) {
				return array();
			}
			return array( $attr_key => (string) $val );
		}

		/**
		 * Emit background color / gradient vars for one attribute.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw background value object.
		 * @return array Var map, or empty array when the background is disabled.
		 */
		private static function bg_vars( $attr_key, $val ) {
			if ( ! is_array( $val ) || empty( $val['openBg'] ) ) {
				return array();
			}
			if ( isset( $val['bgType'] ) && 'gradient' === $val['bgType'] && ! empty( $val['bgGradient'] ) ) {
				return array( $attr_key . '-image' => (string) $val['bgGradient'] );
			}
			if ( ! empty( $val['bgDefaultColor'] ) ) {
				return array( $attr_key . '-color' => (string) $val['bgDefaultColor'] );
			}
			return array();
		}

		/**
		 * Emit 4-side dimension vars for one device.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $sides    Per-side values (top/right/bottom/left).
		 * @param string $unit     CSS unit.
		 * @param string $device   Device key ('md'/'sm'/'xs'); '' for none.
		 * @return array Var map for the device.
		 */
		private static function dim_vars_for_device( $attr_key, $sides, $unit, $device = '' ) {
			$out = array();
			if ( ! is_array( $sides ) ) {
				return $out;
			}
			// btPad var keys carry a per-device suffix (`-md` / `-sm` / `-xs`)
			// so each breakpoint owns a distinct custom property. brad keeps
			// the unsuffixed name and is redeclared inside @media :root scopes
			// (cascade resolves the right value per breakpoint).
			$suffix = ( 'btPad' === $attr_key && $device ) ? ( '-' . $device ) : '';
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$v = self::with_unit( isset( $sides[ $side ] ) ? $sides[ $side ] : '', $unit );
				if ( '' !== $v ) {
					$out[ $attr_key . '-' . $side . $suffix ] = $v;
				}
			}
			return $out;
		}

		/**
		 * Resolve `brad.globalBorderRadius` for one device into a chained
		 * var() ref (`var(--tpgb-RAD{n})`). Mirrors getGlobalRadiusByDevice
		 * in src/helpers/tpgb_css_generator.js so the preset namespace
		 * inherits the same global-radius mapping that block CSS uses.
		 * Returns empty string when no global radius is set for the device.
		 *
		 * @param mixed  $val    Raw brad value object.
		 * @param string $device Device key ('md'/'sm'/'xs').
		 * @return string Chained var() ref, or '' when no global radius is set.
		 */
		private static function global_radius_ref_for_device( $val, $device ) {
			if ( ! is_array( $val ) || ! isset( $val['globalBorderRadius'] ) ) {
				return '';
			}
			$gb = $val['globalBorderRadius'];
			if ( '' === $gb || null === $gb ) {
				return '';
			}
			if ( is_array( $gb ) ) {
				if ( isset( $gb[ $device ] ) && '' !== $gb[ $device ] ) {
					$idx = $gb[ $device ];
				} elseif ( isset( $gb['md'] ) && '' !== $gb['md'] ) {
					$idx = $gb['md'];
				} elseif ( isset( $gb['Desktop'] ) && '' !== $gb['Desktop'] ) {
					$idx = $gb['Desktop'];
				} else {
					$idx = '';
				}
			} else {
				$idx = $gb;
			}
			if ( '' === $idx || null === $idx ) {
				return '';
			}
			$n = (int) $idx;
			if ( $n <= 0 ) {
				return '';
			}
			$fallback = '';
			if ( isset( $val['globalBorderRadiusFallback'] ) ) {
				$gbf = $val['globalBorderRadiusFallback'];
				if ( is_array( $gbf ) ) {
					if ( isset( $gbf[ $device ] ) && '' !== $gbf[ $device ] ) {
						$f = $gbf[ $device ];
					} elseif ( isset( $gbf['md'] ) && '' !== $gbf['md'] ) {
						$f = $gbf['md'];
					} elseif ( isset( $gbf['Desktop'] ) && '' !== $gbf['Desktop'] ) {
						$f = $gbf['Desktop'];
					} else {
						$f = '';
					}
				} else {
					$f = $gbf;
				}
				if ( is_string( $f ) ) {
					$fallback = $f;
				}
			}
			return 'var(--tpgb-RAD' . $n . ( '' !== $fallback ? ', ' . $fallback : '' ) . ')';
		}

		/**
		 * Emit typography vars for one device.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw typography value object.
		 * @param string $device   Device key ('md'/'sm'/'xs').
		 * @return array Var map for the device.
		 */
		private static function typo_vars_for_device( $attr_key, $val, $device ) {
			$out = array();
			if ( ! is_array( $val ) || empty( $val['openTypography'] ) ) {
				return $out;
			}

			// Global typography preset reference (globalTypo: N → T-N).
			// Chain through to the site-wide typography CSS variables so
			// the button preset inherits font-family / weight / style etc.
			if ( 'md' === $device && ! empty( $val['globalTypo'] ) ) {
				$n                                = $val['globalTypo'];
				$out[ $attr_key . '-family' ]     = 'var(--tpgb-T' . $n . '-font-family)';
				$out[ $attr_key . '-weight' ]     = 'var(--tpgb-T' . $n . '-font-weight)';
				$out[ $attr_key . '-style' ]      = 'var(--tpgb-T' . $n . '-font-style)';
				$out[ $attr_key . '-transform' ]  = 'var(--tpgb-T' . $n . '-text-transform)';
				$out[ $attr_key . '-decoration' ] = 'var(--tpgb-T' . $n . '-text-decoration)';
				$out[ $attr_key . '-size' ]       = 'var(--tpgb-T' . $n . '-font-size)';
				$out[ $attr_key . '-height' ]     = 'var(--tpgb-T' . $n . '-line-height)';
				$out[ $attr_key . '-spacing' ]    = 'var(--tpgb-T' . $n . '-letter-spacing)';
				return $out;
			}

			if ( 'md' === $device ) {
				if ( ! empty( $val['fontFamily']['family'] ) ) {
					$out[ $attr_key . '-family' ] = (string) $val['fontFamily']['family'];
				}
				if ( ! empty( $val['fontFamily']['fontWeight'] ) ) {
					$out[ $attr_key . '-weight' ] = (string) $val['fontFamily']['fontWeight'];
				}
				if ( ! empty( $val['fontFamily']['fontStyle'] ) ) {
					$out[ $attr_key . '-style' ] = (string) $val['fontFamily']['fontStyle'];
				}
				if ( ! empty( $val['transform'] ) ) {
					$out[ $attr_key . '-transform' ] = (string) $val['transform'];
				}
				if ( ! empty( $val['decoration'] ) ) {
					$out[ $attr_key . '-decoration' ] = (string) $val['decoration'];
				}
			}
			if ( isset( $val['size'][ $device ] ) && '' !== $val['size'][ $device ] ) {
				$out[ $attr_key . '-size' ] = self::with_unit( $val['size'][ $device ], isset( $val['size']['unit'] ) ? $val['size']['unit'] : 'px' );
			}
			if ( isset( $val['height'][ $device ] ) && '' !== $val['height'][ $device ] ) {
				$out[ $attr_key . '-height' ] = self::with_unit( $val['height'][ $device ], isset( $val['height']['unit'] ) ? $val['height']['unit'] : 'px' );
			}
			if ( isset( $val['spacing'][ $device ] ) && '' !== $val['spacing'][ $device ] ) {
				$out[ $attr_key . '-spacing' ] = self::with_unit( $val['spacing'][ $device ], isset( $val['spacing']['unit'] ) ? $val['spacing']['unit'] : 'px' );
			}
			return $out;
		}

		/**
		 * Emit box-shadow vars for one attribute.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw shadow value object.
		 * @return array Var map, or empty array when the shadow is disabled.
		 */
		private static function shadow_vars( $attr_key, $val ) {
			$out = array();
			if ( ! is_array( $val ) || empty( $val['openShadow'] ) ) {
				return $out;
			}
			// Mirror of JS shadowVars(): when the preset's shadow points to
			// a global box-shadow (--tpgb-BS{n}), emit a single shorthand
			// var that chains through. Skipping the per-field vars stops
			// the stale defaults seeded by Tpgb_BoxShadow from leaking
			// into the rendered shadow.
			if ( isset( $val['globalShadow'] ) && '' !== $val['globalShadow'] && null !== $val['globalShadow'] ) {
				$out[ $attr_key ] = 'var(--tpgb-BS' . $val['globalShadow'] . ')';
				if ( ! empty( $val['typeShadow'] ) ) {
					$out[ $attr_key . '-type' ] = $val['typeShadow'];
				}
				if ( ! empty( $val['inset'] ) ) {
					$out[ $attr_key . '-inset' ] = $val['inset'];
				}
				return $out;
			}
			if ( isset( $val['horizontal'] ) ) {
				$out[ $attr_key . '-h' ] = $val['horizontal'] . 'px';
			}
			if ( isset( $val['vertical'] ) ) {
				$out[ $attr_key . '-v' ] = $val['vertical'] . 'px';
			}
			if ( isset( $val['blur'] ) ) {
				$out[ $attr_key . '-blur' ] = $val['blur'] . 'px';
			}
			if ( isset( $val['spread'] ) ) {
				$out[ $attr_key . '-spread' ] = $val['spread'] . 'px';
			}
			if ( ! empty( $val['color'] ) ) {
				$out[ $attr_key . '-color' ] = $val['color'];
			}
			if ( ! empty( $val['inset'] ) ) {
				$out[ $attr_key . '-inset' ] = $val['inset'];
			}
			if ( ! empty( $val['typeShadow'] ) ) {
				$out[ $attr_key . '-type' ] = $val['typeShadow'];
			}
			return $out;
		}

		/**
		 * Emit border vars for one device.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw border value object.
		 * @param string $device   Device key ('md'/'sm'/'xs').
		 * @return array Var map for the device.
		 */
		private static function border_vars_for_device( $attr_key, $val, $device ) {
			$out = array();
			if ( ! is_array( $val ) ) {
				return $out;
			}
			// Two valid shapes (mirror of JS):
			// - openBorder=1 with inline values → per-side -width-*
			// - globalBorder set → chained-var shortcut to --tpgb-BRT/BRW/BRC{n}
			// The globalBorder path does NOT require openBorder (the
			// raw preset shape can lack it). Without this branch,
			// plus-global.css misses the preset-namespace chain vars
			// and frontend buttons show undefined borders.
			if ( empty( $val['openBorder'] ) && empty( $val['globalBorder'] ) ) {
				return $out;
			}

			// Global-border chain — md pass only (BRW{n} is shorthand,
			// not device-split).
			if ( ! empty( $val['globalBorder'] ) ) {
				if ( 'md' === $device ) {
					$g                           = $val['globalBorder'];
					$out[ $attr_key . '-style' ] = 'var(--tpgb-BRT' . $g . ')';
					$out[ $attr_key . '-width' ] = 'var(--tpgb-BRW' . $g . ')';
					$out[ $attr_key . '-color' ] = ( ! empty( $val['color'] ) )
						? $val['color']
						: ( 'var(--tpgb-BRC' . $g . ')' );
				}
				return $out;
			}

			if ( 'md' === $device ) {
				if ( ! empty( $val['type'] ) ) {
					$out[ $attr_key . '-style' ] = $val['type'];
				}
				if ( ! empty( $val['color'] ) ) {
					$out[ $attr_key . '-color' ] = $val['color'];
				}
			}
			$unit   = isset( $val['width']['unit'] ) ? $val['width']['unit'] : 'px';
			$widths = isset( $val['width'][ $device ] ) ? $val['width'][ $device ] : null;
			if ( is_array( $widths ) ) {
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					$v = self::with_unit( isset( $widths[ $side ] ) ? $widths[ $side ] : '', $unit );
					if ( '' !== $v ) {
						$out[ $attr_key . '-width-' . $side ] = $v;
					}
				}
			}
			return $out;
		}

		/**
		 * Dispatch one attribute → { md:{var→val}, sm:{...}, xs:{...} }.
		 *
		 * @param string $attr_key Attribute key.
		 * @param mixed  $val      Raw attribute value.
		 * @return array Var maps bucketed by device.
		 */
		private static function build_attr_vars( $attr_key, $val ) {
			$by = array(
				'md' => array(),
				'sm' => array(),
				'xs' => array(),
			);

			switch ( $attr_key ) {
				case 'btColor':
				case 'bthColor':
				case 'bthBColor':
					$by['md'] = array_merge( $by['md'], self::scalar_vars( $attr_key, $val ) );
					break;

				case 'btBg':
				case 'bthBg':
					$by['md'] = array_merge( $by['md'], self::bg_vars( $attr_key, $val ) );
					break;

				case 'brad':
				case 'btPad':
					if ( is_array( $val ) ) {
						$unit = isset( $val['unit'] ) ? $val['unit'] : 'px';
						foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
							// brad only — when a global radius is selected
							// for this device, every side resolves to the
							// same chained var ref (var(--tpgb-RAD{n}))
							// instead of the stored inline fallback value.
							// btPad has no global counterpart.
							if ( 'brad' === $attr_key ) {
								$g_rad = self::global_radius_ref_for_device( $val, $d );
								if ( '' !== $g_rad ) {
									// --tpgb-RAD{n} is already a shorthand ("t r b l").
									// Emit one shorthand key so the rule emitter can use
									// `border-radius: var(…)` directly — splitting into 4
									// sides and recombining produces 16 values and breaks CSS.
									$by[ $d ][ $attr_key ] = $g_rad;
									continue;
								}
							}
							$sides = isset( $val[ $d ] ) ? $val[ $d ] : null;
							// Pass the device through so dim_vars_for_device
							// can suffix the var key for btPad.
							$by[ $d ] = array_merge( $by[ $d ], self::dim_vars_for_device( $attr_key, $sides, $unit, $d ) );
						}
					}
					break;

				case 'bTypo':
					foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
						$by[ $d ] = array_merge( $by[ $d ], self::typo_vars_for_device( $attr_key, $val, $d ) );
					}
					break;

				case 'btshadow':
				case 'bthShadow':
				case 'tShadow':
					$by['md'] = array_merge( $by['md'], self::shadow_vars( $attr_key, $val ) );
					break;

				case 'bBord':
					foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
						$by[ $d ] = array_merge( $by[ $d ], self::border_vars_for_device( $attr_key, $val, $d ) );
					}
					break;
			}
			return $by;
		}

		/**
		 * Public: build the :root {} stylesheet from a presets map.
		 *
		 * @param array $presets Map of preset key => button attributes.
		 * @return string Rendered stylesheet, or '' when empty.
		 */
		public static function build_css( $presets ) {
			if ( ! is_array( $presets ) || empty( $presets ) ) {
				return '';
			}

			$bucket = array(
				'md' => array(),
				'sm' => array(),
				'xs' => array(),
			);

			foreach ( $presets as $preset_key => $preset ) {
				if ( is_object( $preset ) ) {
					$preset = json_decode( wp_json_encode( $preset ), true );
				}
				if ( ! is_array( $preset ) ) {
					continue;
				}
				// Var prefix uses the slug (matches the wrapper class) so
				// `.nxt-btn-global-{slug}` rules find their declarations.
				$ns = self::preset_class_slug( $preset_key, $presets );
				foreach ( self::$preset_keys as $attr_key ) {
					if ( ! array_key_exists( $attr_key, $preset ) ) {
						continue;
					}
					$by_device = self::build_attr_vars( $attr_key, $preset[ $attr_key ] );
					foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
						foreach ( (array) $by_device[ $d ] as $k => $v ) {
							$bucket[ $d ][] = '  --tpgb-' . $ns . '-' . $k . ': ' . $v . ';';
						}
					}
				}
			}

			$css = '';
			if ( ! empty( $bucket['md'] ) ) {
				$css .= ":root {\n" . implode( "\n", $bucket['md'] ) . "\n}\n";
			}
			if ( ! empty( $bucket['sm'] ) ) {
				$css .= self::$media_sm . " { :root {\n" . implode( "\n", $bucket['sm'] ) . "\n} }\n";
			}
			if ( ! empty( $bucket['xs'] ) ) {
				$css .= self::$media_xs . " { :root {\n" . implode( "\n", $bucket['xs'] ) . "\n} }\n";
			}
			return $css;
		}

		/** One-shot: read active presets from DB and return rendered CSS. */
		public static function build_active_css() {
			return self::build_css( self::get_active_presets() );
		}

		/**
		 * Rewrite preset-covered attributes into var() reference shapes.
		 *
		 * PHP mirror of applyPresetVarRefs() in button-preset-vars.js.
		 * Replaces the 11 preset-covered attribute values in $attributes with
		 * var() reference shapes so the server-side CSS generator emits
		 * --tpgb-{presetKey}-{attr}-{sub} references instead of raw values.
		 *
		 * The block's stored attribute values for the preset keys are
		 * ignored while a preset is active — this matches the JS helper and
		 * gives "global overrides local" with no inline CSS required.
		 *
		 * @param array  $attributes Block attributes.
		 * @param string $preset_key Active preset identifier.
		 * @return array Attributes with preset keys rewritten to var() refs.
		 */
		public static function apply_var_refs( $attributes, $preset_key ) {
			if ( ! is_array( $attributes ) || empty( $preset_key ) ) {
				return $attributes;
			}
			$presets = self::get_active_presets();
			if ( empty( $presets[ $preset_key ] ) ) {
				return $attributes;
			}
			$preset = $presets[ $preset_key ];
			if ( is_object( $preset ) ) {
				$preset = json_decode( wp_json_encode( $preset ), true );
			}
			if ( ! is_array( $preset ) ) {
				return $attributes;
			}

			// Var refs use the slug (same as wrapper class & plus-global.css
			// declarations) so the three sides of the system stay aligned.
			// Two emitters kept in lockstep with JS: `$v` returns a bare
			// var() ref for values that flow through cssDimension /
			// cssBoxShadow / cssBorder (those helpers detect var() and
			// append !important to the final declaration themselves);
			// `$v_imp` bakes !important into the value for scalars that
			// go through direct template substitution, where the
			// declaration ends the moment the template writes the value.
			$slug  = self::preset_class_slug( $preset_key, $presets );
			$v     = function ( $suffix ) use ( $slug ) {
				return 'var(--tpgb-' . $slug . '-' . $suffix . ')';
			};
			$v_imp = function ( $suffix ) use ( $slug ) {
				return 'var(--tpgb-' . $slug . '-' . $suffix . ') !important';
			};

			$out = $attributes;

			// Scalars — use $v_imp to mirror JS. Without !important these
			// lose cascade to the block's own defaults.
			if ( array_key_exists( 'btColor', $preset ) ) {
				$out['btColor'] = $v_imp( 'btColor' );
			}
			if ( array_key_exists( 'bthColor', $preset ) ) {
				$out['bthColor'] = $v_imp( 'bthColor' );
			}
			if ( array_key_exists( 'bthBColor', $preset ) ) {
				$out['bthBColor'] = $v_imp( 'bthBColor' );
			}

			// 4-side dims.
			// brad: single md shape — var is redeclared inside @media :root
			// blocks at the buildPresetVarsCss layer, so the cascade picks
			// the right value at each breakpoint.
			$dim_shape = function ( $attr_key ) use ( $v ) {
				return array(
					'md'   => array(
						'top'    => $v( $attr_key . '-top' ),
						'right'  => $v( $attr_key . '-right' ),
						'bottom' => $v( $attr_key . '-bottom' ),
						'left'   => $v( $attr_key . '-left' ),
					),
					'unit' => '',
				);
			};
			// btPad: device-suffixed vars — each breakpoint references its
			// own var (e.g. `--tpgb-{slug}-btPad-top-sm`). Only the devices
			// that the preset actually populates get an entry; referencing an
			// undefined var would invalidate the whole `padding:` declaration
			// at that breakpoint and collapse padding to 0.
			$dim_shape_by_device = function ( $attr_key, $src ) use ( $v ) {
				$shape = array( 'unit' => '' );
				if ( ! is_array( $src ) ) {
					return $shape;
				}
				foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
					$dev = isset( $src[ $d ] ) ? $src[ $d ] : null;
					if ( is_array( $dev ) && (
						! empty( $dev['top'] ) || ! empty( $dev['right'] ) ||
						! empty( $dev['bottom'] ) || ! empty( $dev['left'] )
					) ) {
						$shape[ $d ] = array(
							'top'    => $v( $attr_key . '-top-' . $d ),
							'right'  => $v( $attr_key . '-right-' . $d ),
							'bottom' => $v( $attr_key . '-bottom-' . $d ),
							'left'   => $v( $attr_key . '-left-' . $d ),
						);
					}
				}
				return $shape;
			};
			$has_dim_value       = function ( $box ) {
				if ( empty( $box ) ) {
					return false; }
				foreach ( array( 'md', 'sm', 'xs' ) as $d ) {
					if ( ! empty( $box[ $d ] ) ) {
						$dev = $box[ $d ];
						if ( ! empty( $dev['top'] ) || ! empty( $dev['right'] ) || ! empty( $dev['bottom'] ) || ! empty( $dev['left'] ) ) {
							return true;
						}
					}
				}
				// brad with only a global-radius selection (no inline sides)
				// still produces var declarations at :root, so the consumer
				// block must relay the var ref instead of falling through to
				// its own attribute value.
				if ( ! empty( $box['globalBorderRadius'] ) ) {
					return true;
				}
				return false;
			};
			if ( array_key_exists( 'brad', $preset ) && $has_dim_value( $preset['brad'] ) ) {
				// When globalBorderRadius is set, --tpgb-RAD{n} is a shorthand
				// ("t r b l"). Emit a single var ref so cssDimension generates
				// `border-radius: var(…)` without splitting into 4 sides, which
				// would produce 16 values and break the CSS declaration.
				$brad_is_global = is_array( $preset['brad'] ) && ! empty( $preset['brad']['globalBorderRadius'] );
				$out['brad']    = $brad_is_global
					? array(
						'md'   => $v( 'brad' ),
						'unit' => '',
					)
					: $dim_shape( 'brad' );
			}
			if ( array_key_exists( 'btPad', $preset ) && $has_dim_value( $preset['btPad'] ) ) {
				$out['btPad'] = $dim_shape_by_device( 'btPad', $preset['btPad'] );
			}

			// Border. Three branches mirror the JS emitter:
			// 1. Preset selected a GLOBAL border (`globalBorder` set)
			// → relay the pointer through. cssBorder on the block
			// already handles `globalBorder` and emits the
			// `var(--tpgb-BRT{n})` chain — no preset-namespaced
			// vars needed. Without this branch, the var-stamping
			// below would shadow the global border with empty
			// preset vars and the user sees `undefined` borders.
			// 2. Inline border values (`openBorder: 1`) → stamp via
			// preset namespace ($v_imp for type/color since those
			// are scalar template substitutions; $v for widths
			// because cssDimension auto-adds !important on var refs).
			// 3. Border disabled → emit cleared shape.
			if ( array_key_exists( 'bBord', $preset ) ) {
				if ( ! empty( $preset['bBord']['globalBorder'] ) ) {
					// globalBorder relay — stamp preset-namespace var
					// refs so the chain matches the background pattern:
					// block.bBord.color → var(--tpgb-{slug}-bBord-color)
					// → var(--tpgb-BRC{n})
					// → actual color
					// Width is a SINGLE shorthand string ref (not 4-side
					// object) because --tpgb-BRW{n} is itself a shorthand
					// declaration. cssBorder handles string width.
					$out['bBord'] = array(
						'openBorder' => 1,
						'type'       => $v_imp( 'bBord-style' ),
						'color'      => $v_imp( 'bBord-color' ),
						'width'      => $v_imp( 'bBord-width' ),
					);
				} elseif ( ! empty( $preset['bBord']['openBorder'] ) ) {
					$out['bBord'] = array(
						'openBorder' => 1,
						'type'       => $v_imp( 'bBord-style' ),
						'color'      => $v_imp( 'bBord-color' ),
						'width'      => array(
							'md'   => array(
								'top'    => $v( 'bBord-width-top' ),
								'right'  => $v( 'bBord-width-right' ),
								'bottom' => $v( 'bBord-width-bottom' ),
								'left'   => $v( 'bBord-width-left' ),
							),
							'unit' => '',
						),
					);
				} else {
					$out['bBord'] = array( 'openBorder' => 0 );
				}
			}

			// Backgrounds — scalar template substitution (color or image),
			// needs !important to win over static CSS defaults.
			$bg_shape = function ( $attr_key, $src ) use ( $v_imp ) {
				if ( empty( $src['openBg'] ) ) {
					return $src;
				}
				$is_grad = isset( $src['bgType'] ) && 'gradient' === $src['bgType'];
				return array(
					'openBg'         => 1,
					'bgType'         => $is_grad ? 'gradient' : 'color',
					'bgDefaultColor' => $is_grad ? '' : $v_imp( $attr_key . '-color' ),
					'bgGradient'     => $is_grad ? $v_imp( $attr_key . '-image' ) : '',
				);
			};
			if ( array_key_exists( 'btBg', $preset ) ) {
				$out['btBg'] = $bg_shape( 'btBg', is_array( $preset['btBg'] ) ? $preset['btBg'] : array() );
			}
			if ( array_key_exists( 'bthBg', $preset ) ) {
				$out['bthBg'] = $bg_shape( 'bthBg', is_array( $preset['bthBg'] ) ? $preset['bthBg'] : array() );
			}

			// Typography. Scalar template-substitution fields (family,
			// weight, style, transform, decoration) get $v_imp so
			// !important sticks. Size/height/spacing flow through
			// SetDeviceSeparatValue which doesn't auto-add !important
			// for var refs, so they use $v — matching the JS emitter.
			if ( array_key_exists( 'bTypo', $preset ) ) {
				if ( ! empty( $preset['bTypo']['openTypography'] ) ) {
					// Global typography reference — use globalTypo so the
					// CSS generator emits var(--tpgb-T{n}-…) chains.
					if ( ! empty( $preset['bTypo']['globalTypo'] ) ) {
						$out['bTypo'] = array(
							'openTypography' => 1,
							'globalTypo'     => $preset['bTypo']['globalTypo'],
						);
					} else {
						$out['bTypo'] = array(
							'openTypography' => 1,
							'fontFamily'     => array(
								'family'     => $v_imp( 'bTypo-family' ),
								'fontWeight' => $v_imp( 'bTypo-weight' ),
								'fontStyle'  => $v_imp( 'bTypo-style' ),
							),
							'size'           => array(
								'md'   => $v( 'bTypo-size' ),
								'unit' => '',
							),
							'height'         => array(
								'md'   => $v( 'bTypo-height' ),
								'unit' => '',
							),
							'spacing'        => array(
								'md'   => $v( 'bTypo-spacing' ),
								'unit' => '',
							),
							'transform'      => $v_imp( 'bTypo-transform' ),
							'decoration'     => $v_imp( 'bTypo-decoration' ),
						);
					}
				} else {
					$out['bTypo'] = array( 'openTypography' => 0 );
				}
			}

			// Shadows — emitted as var() strings. The PHP css generator for
			// box-shadow must treat var() prefixed values the same way the JS
			// _vsfxPx helper does. tp-generate-block-css.php delegates shadow
			// emission to parse_css helpers — those treat horizontal/vertical
			// values as raw and append 'px'. To avoid "var(…)px" we emit the
			// vars WITH their units baked in (buildPresetVarsCss already does)
			// and rely on the PHP generator's number-concat being lenient, or
			// produce a fallback string when the box-shadow helper requires
			// it. A full parser patch on the PHP side is not needed for the
			// common use-case: previewing a preset is what the editor does,
			// and the editor uses the JS path.
			$shadow_shape = function ( $attr_key, $src ) use ( $v ) {
				if ( empty( $src['openShadow'] ) ) {
					return $src;
				}
				// Global-shadow preset path — pass `globalShadow` through so
				// cssBoxShadow (PHP + JS) hits the existing branch that emits
				// `box-shadow: var(--tpgb-BS{n})`. Per-field var refs are
				// meaningless when the source is a shorthand --tpgb-BS{n}.
				if ( isset( $src['globalShadow'] ) && '' !== $src['globalShadow'] && null !== $src['globalShadow'] ) {
					return array(
						'openShadow'   => 1,
						'typeShadow'   => isset( $src['typeShadow'] ) ? $src['typeShadow'] : 'box-shadow',
						'inset'        => isset( $src['inset'] ) ? $src['inset'] : '',
						'globalShadow' => $src['globalShadow'],
					);
				}
				return array(
					'openShadow' => 1,
					'typeShadow' => isset( $src['typeShadow'] ) ? $src['typeShadow'] : 'box-shadow',
					'inset'      => isset( $src['inset'] ) ? $src['inset'] : '',
					'horizontal' => $v( $attr_key . '-h' ),
					'vertical'   => $v( $attr_key . '-v' ),
					'blur'       => $v( $attr_key . '-blur' ),
					'spread'     => $v( $attr_key . '-spread' ),
					'color'      => $v( $attr_key . '-color' ),
				);
			};
			if ( array_key_exists( 'btshadow', $preset ) ) {
				$out['btshadow'] = $shadow_shape( 'btshadow', is_array( $preset['btshadow'] ) ? $preset['btshadow'] : array() );
			}
			if ( array_key_exists( 'bthShadow', $preset ) ) {
				$out['bthShadow'] = $shadow_shape( 'bthShadow', is_array( $preset['bthShadow'] ) ? $preset['bthShadow'] : array() );
			}
			if ( array_key_exists( 'tShadow', $preset ) ) {
				$out['tShadow'] = $shadow_shape( 'tShadow', is_array( $preset['tShadow'] ) ? $preset['tShadow'] : array() );
			}

			return $out;
		}

		/**
		 * Path / URL helpers for the standalone button-preset stylesheet.
		 * Lives next to plus-global.css in /uploads/theplus_gutenberg/ so
		 * the existing upload-dir conventions (writable check, URL host)
		 * apply unchanged.
		 */
		private static function preset_file_path() {
			$upload = wp_get_upload_dir();
			if ( empty( $upload['basedir'] ) ) {
				return '';
			}
			return trailingslashit( $upload['basedir'] ) . 'theplus_gutenberg/plus-button-presets.css';
		}
		/**
		 * URL of the standalone button-preset stylesheet.
		 *
		 * @return string Public URL, or '' when the upload dir is unavailable.
		 */
		private static function preset_file_url() {
			$upload = wp_get_upload_dir();
			if ( empty( $upload['baseurl'] ) ) {
				return '';
			}
			return trailingslashit( $upload['baseurl'] ) . 'theplus_gutenberg/plus-button-presets.css';
		}

		/**
		 * Write the freshly-rebuilt preset CSS to plus-button-presets.css.
		 * Mirrors enqueue_global_css's WP_Filesystem usage so behavior on
		 * locked-down hosts is consistent. Creates the parent dir on first
		 * write. Returns true when the file is on disk after the call.
		 */
		public static function write_preset_file() {
			$path = self::preset_file_path();
			if ( '' === $path ) {
				return false;
			}
			$css = self::build_active_css();
			if ( '' === $css ) {
				// Nothing to declare — drop any stale file so we don't
				// keep serving outdated vars after every preset is removed.
				if ( file_exists( $path ) ) {
					wp_delete_file( $path );
				}
				return false;
			}
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
				}
				$upload = wp_get_upload_dir();
				WP_Filesystem( false, $upload['basedir'], true );
			}
			if ( empty( $wp_filesystem ) ) {
				return false;
			}
			$dir = dirname( $path );
			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}
			$wp_filesystem->put_contents( $path, $css );
			return file_exists( $path );
		}

		/**
		 * Frontend enqueue. Materializes plus-button-presets.css on first
		 * request after a fresh install / upgrade / preset edit, then
		 * registers it as a standard `<link rel="stylesheet">` so there's
		 * no inline `<style>` and the browser's own caching applies.
		 *
		 * Why a separate file instead of letting plus-global.css carry the
		 * vars: that file is regenerated only when `_tpgb_global_css`
		 * updates, which happens on post save — a preset added or
		 * renamed in the global modal alone leaves it stale. This file
		 * is owned by the preset emitter directly, so saving the preset
		 * modal is enough to refresh it (see invalidate hooks below).
		 *
		 * Loaded at priority 1000 so it lands after plus-global.css —
		 * cascade order means fresh declarations win over any stale
		 * copies that may still be in plus-global.css.
		 */
		public static function enqueue_preset_file() {
			if ( is_admin() ) {
				return;
			}
			$path = self::preset_file_path();
			$url  = self::preset_file_url();
			if ( '' === $path || '' === $url ) {
				return;
			}
			// Materialize on demand if a preset edit hasn't yet triggered
			// a write (e.g. fresh install before any modal save).
			if ( ! file_exists( $path ) ) {
				self::write_preset_file();
			}
			if ( ! file_exists( $path ) ) {
				return;
			}
			// `filemtime` doubles as the cache-busting version — every
			// rewrite from invalidate_preset_file() bumps it, so browsers
			// re-fetch without us having to maintain a separate option.
			wp_enqueue_style(
				'tpgb-button-presets',
				$url,
				array(),
				(string) filemtime( $path )
			);
		}

		/**
		 * Hook target for `update_option_tpgb_global_options`.
		 *
		 * The ($old_value, $new_value) signature matches the update_option_*
		 * action so we can no-op when nothing actually changed (WordPress fires
		 * the action even when the same value is re-saved).
		 *
		 * @param mixed $old_value Previous option value.
		 * @param mixed $new_value New option value.
		 * @return void
		 */
		public static function invalidate_preset_file_on_update( $old_value, $new_value ) {
			if ( $old_value === $new_value ) {
				return;
			}
			self::$presets_cache = null;
			self::write_preset_file();
		}

		/**
		 * Hook target for `add_option_tpgb_global_options`.
		 *
		 * Fires when the option is created for the first time; always rebuilds
		 * the preset stylesheet.
		 *
		 * @return void
		 */
		public static function invalidate_preset_file_on_add() {
			self::$presets_cache = null;
			self::write_preset_file();
		}
	}

	// Frontend enqueue — registers plus-button-presets.css as a real
	// stylesheet (no inline <style>). priority 1000 lands after the main
	// plus-global.css enqueue so its cascade overrides any stale copies.
	add_action( 'wp_enqueue_scripts', array( 'Tpgb_Button_Preset_Vars', 'enqueue_preset_file' ), 1000 );
	// Refresh the file whenever the preset map changes — saving the
	// global-settings modal (presets / colors / etc.) updates this option.
	add_action( 'update_option_tpgb_global_options', array( 'Tpgb_Button_Preset_Vars', 'invalidate_preset_file_on_update' ), 10, 3 );
	add_action( 'add_option_tpgb_global_options', array( 'Tpgb_Button_Preset_Vars', 'invalidate_preset_file_on_add' ), 10, 2 );
}

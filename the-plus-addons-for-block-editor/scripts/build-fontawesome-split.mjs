/**
 * build-fontawesome-split.mjs — split the combined FontAwesome 5.14.0 stylesheet into two
 * family-scoped files so the front end can load brand icons (fa-brands-400) only on pages
 * that actually use one, instead of unconditionally on every request.
 *
 * Background (see docs/PERF-FIX-PLAN.md finding #4 in the Nexter Blocks workstation):
 * `assets/css/extra/fontawesome.min.css` is ONE minified file with solid/regular/brand
 * icon-name rules interleaved alphabetically — the selectors themselves carry no family
 * marker (a rule just maps an icon name to a unicode codepoint; which *font* renders that
 * codepoint is decided separately by whichever of .fa/.fas/.far/.fab class is combined with
 * it). Splitting "brand rules" out by pattern-matching the shipped CSS was previously ruled
 * unsafe (alphabetical proximity isn't family membership). This script instead uses
 * @fortawesome/fontawesome-free's own published per-icon metadata (metadata/icons.yml,
 * pinned to the exact 5.14.0 build already shipped in assets/fonts/ and previously
 * verified 1:1 against every icon name in the current fontawesome.min.css) to make that
 * split correctly.
 *
 * Critically, this does NOT subset/narrow the icon set at all — Nexter's own icon picker
 * (src/helpers/helper-function.js tpgb_FontIconList()) offers essentially the entire FA
 * free library to every site owner, so no static analysis of "icons referenced in code"
 * can safely predict what a real site's content actually uses. Every icon name currently
 * in fontawesome.min.css is preserved, verbatim, in one of the two output files (dual-style
 * icons appear in both) -- only the FILE an icon's rule lives in changes, not whether it
 * exists. The unicode codepoint for every rule is taken directly from the CURRENTLY SHIPPED
 * fontawesome.min.css (not regenerated from metadata), so every glyph renders byte-identical
 * to today's behaviour.
 *
 * Output:
 *   assets/css/extra/fontawesome-core.min.css    (solid + regular + shared base rules;
 *                                                  always enqueued on the front end)
 *   assets/css/extra/fontawesome-brands.min.css  (brand icons only; enqueued only when a
 *                                                  brand icon is actually detected in the
 *                                                  page's content -- see tp-block-helper.php
 *                                                  Tp_Blocks_Helper::tpgb_needs_fontawesome_brands())
 *   classes/generated/fontawesome-brand-icons.php (PHP array of every brand-only icon slug,
 *                                                  used by that same detection function as a
 *                                                  substring safety net for hand-typed /
 *                                                  legacy-syntax icon classes that don't
 *                                                  carry an explicit "fab" family prefix)
 *
 * -rtl.css twins for the two CSS outputs are produced automatically by the existing
 * scripts/gen-rtl.mjs pass that already runs right after this one in `npm run build` -- this
 * script only needs to write the LTR files.
 *
 * The original combined assets/css/extra/fontawesome.min.css (+ -rtl twin) is left in place,
 * untouched, and still used by the block-editor (admin-only) enqueue path, which needs the
 * full icon set available for the icon picker's live preview regardless of what ends up
 * actually used on the front end.
 *
 * Run: node scripts/build-fontawesome-split.mjs   (chained into `npm run build`)
 */
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const require = createRequire( import.meta.url );
const YAML = require( 'yaml' );

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const SRC_CSS = path.join( ROOT, 'assets/css/extra/fontawesome.min.css' );
const OUT_CORE = path.join( ROOT, 'assets/css/extra/fontawesome-core.min.css' );
const OUT_BRANDS = path.join( ROOT, 'assets/css/extra/fontawesome-brands.min.css' );
const OUT_BRAND_NAMES_PHP = path.join( ROOT, 'classes/generated/fontawesome-brand-icons.php' );

const FA_PKG_ROOT = path.dirname(
	require.resolve( '@fortawesome/fontawesome-free/package.json', { paths: [ ROOT ] } )
);
const FA_META_PATH = path.join( FA_PKG_ROOT, 'metadata/icons.yml' );

function fail( msg ) {
	console.error( 'build-fontawesome-split: ' + msg );
	process.exit( 1 );
}

if ( ! fs.existsSync( SRC_CSS ) ) {
	fail( `source stylesheet not found: ${ SRC_CSS }` );
}
if ( ! fs.existsSync( FA_META_PATH ) ) {
	fail(
		`@fortawesome/fontawesome-free metadata not found at ${ FA_META_PATH } -- ` +
			`is @fortawesome/fontawesome-free installed as a devDependency?`
	);
}

const css = fs.readFileSync( SRC_CSS, 'utf8' );
const faMeta = YAML.parse( fs.readFileSync( FA_META_PATH, 'utf8' ) );

// --- 1. Extract every `.fa-{name}:before{content:"\uXXXX"}` icon rule, in file order. -----
const ICON_RULE_RE = /\.fa-([a-z0-9-]+):before\{content:"(\\[0-9a-fA-F]+)"\}/g;
const iconRules = []; // [{ name, rule }]
const namesSeen = new Set();
let m;
let firstIconStart = -1;
let lastIconEnd = -1;
while ( ( m = ICON_RULE_RE.exec( css ) ) !== null ) {
	if ( firstIconStart === -1 ) firstIconStart = m.index;
	lastIconEnd = m.index + m[ 0 ].length;
	iconRules.push( { name: m[ 1 ], rule: m[ 0 ] } );
	namesSeen.add( m[ 1 ] );
}
if ( iconRules.length === 0 ) {
	fail( 'no icon rules matched in source CSS -- source format may have changed, aborting' );
}

// --- 2. Verify every extracted name matches FA metadata 1:1 (belt-and-braces; this was ---
//        already confirmed by hand for the 5.14.0 pin, re-checked on every build run). ----
const metaNames = new Set( Object.keys( faMeta ) );
const missingFromMeta = [ ...namesSeen ].filter( ( n ) => ! metaNames.has( n ) );
if ( missingFromMeta.length ) {
	fail(
		`${ missingFromMeta.length } icon name(s) in fontawesome.min.css have no entry in ` +
			`@fortawesome/fontawesome-free metadata (version mismatch?): ${ missingFromMeta.slice( 0, 10 ).join( ', ' ) }` +
			( missingFromMeta.length > 10 ? ', ...' : '' )
	);
}

// --- 3. Classify each icon name by family, from FA's own metadata -- not by guessing. -----
const coreRules = []; // solid and/or regular
const brandRules = []; // brands
const brandNames = [];
for ( const entry of iconRules ) {
	const styles = faMeta[ entry.name ].styles || [];
	const isCore = styles.includes( 'solid' ) || styles.includes( 'regular' );
	const isBrand = styles.includes( 'brands' );
	if ( ! isCore && ! isBrand ) {
		fail( `icon "${ entry.name }" has neither a core nor a brand style in metadata: ${ styles.join( ',' ) }` );
	}
	if ( isCore ) coreRules.push( entry.rule );
	if ( isBrand ) {
		brandRules.push( entry.rule );
		brandNames.push( entry.name );
	}
}

// --- 4. Pull out the shared "common" boilerplate (keyframes, sizing/utility classes, ------
//        sr-only helpers) and the three @font-face blocks, using the exact byte ranges ----
//        around the icon rules and font-face declarations -- verified structure of the ----
//        currently-shipped 5.14.0 file: [common][1448 icon rules][sr-only][3x @font-face]. -
const commonPrefix = css.slice( 0, firstIconStart );

const FACE_RE = /@font-face\{[^}]*\}/g;
const faces = [ ...css.matchAll( FACE_RE ) ];
if ( faces.length !== 3 ) {
	fail( `expected exactly 3 @font-face blocks in source CSS, found ${ faces.length }` );
}
const firstFaceStart = faces[ 0 ].index;
const commonSuffix = css.slice( lastIconEnd, firstFaceStart ); // .sr-only / .sr-only-focusable

function faceFamily( block ) {
	if ( block.includes( '"Font Awesome 5 Brands"' ) ) return 'brands';
	if ( block.includes( 'font-weight:400' ) ) return 'regular';
	if ( block.includes( 'font-weight:900' ) ) return 'solid';
	return 'unknown';
}
const faceByFamily = {};
for ( const f of faces ) {
	faceByFamily[ faceFamily( f[ 0 ] ) ] = f[ 0 ];
}
for ( const fam of [ 'brands', 'regular', 'solid' ] ) {
	if ( ! faceByFamily[ fam ] ) fail( `could not identify the "${ fam }" @font-face block` );
}

// --- 5. Assemble the two output files. -----------------------------------------------------
const coreCss =
	commonPrefix +
	coreRules.join( '' ) +
	commonSuffix +
	faceByFamily.regular +
	'.far{font-weight:400}' +
	faceByFamily.solid +
	'.fa,.far,.fas{font-family:"Font Awesome 5 Free"}.fa,.fas{font-weight:900}';

const brandsCss =
	faceByFamily.brands + '.fab{font-family:"Font Awesome 5 Brands"}.fab{font-weight:400}' + brandRules.join( '' );

fs.mkdirSync( path.dirname( OUT_CORE ), { recursive: true } );
fs.writeFileSync( OUT_CORE, coreCss );
fs.writeFileSync( OUT_BRANDS, brandsCss );

// --- 6. Emit the brand-name PHP array used by the runtime detection safety net. ------------
const uniqueBrandNames = [ ...new Set( brandNames ) ].sort();
const phpArrayItems = uniqueBrandNames.map( ( n ) => "\t'" + n.replace( /'/g, "\\'" ) + "'," ).join( '\n' );
const php = `<?php
/**
 * Auto-generated by scripts/build-fontawesome-split.mjs -- do not edit by hand.
 *
 * Every Font Awesome 5.14.0 icon slug that belongs to the "brands" family (the "facebook"
 * in "fab fa-facebook"), used by Tp_Blocks_Helper::tpgb_needs_fontawesome_brands() as a
 * substring safety net for content that references a brand icon without an explicit "fab"
 * class prefix (hand-typed custom icon classes, legacy v4-style markup).
 *
 * @package TPGB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
${ phpArrayItems }
);
`;
fs.mkdirSync( path.dirname( OUT_BRAND_NAMES_PHP ), { recursive: true } );
fs.writeFileSync( OUT_BRAND_NAMES_PHP, php );

// --- 7. Report. ------------------------------------------------------------------------------
console.log( 'build-fontawesome-split: done' );
console.log( `  source icon rules:        ${ iconRules.length }` );
console.log( `  core (solid+regular):     ${ coreRules.length } rules -> ${ OUT_CORE } (${ coreCss.length } bytes)` );
console.log( `  brands:                   ${ brandRules.length } rules -> ${ OUT_BRANDS } (${ brandsCss.length } bytes)` );
console.log( `  brand icon names (PHP):   ${ uniqueBrandNames.length } -> ${ OUT_BRAND_NAMES_PHP }` );

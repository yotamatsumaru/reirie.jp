<?php
/**
 * REIRIE Theme - functions.php
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'REIRIE_VERSION', '1.0.80' );
define( 'REIRIE_DIR', get_template_directory() );
define( 'REIRIE_URI', get_template_directory_uri() );

/* ==========================================================
   テーマセットアップ
========================================================== */
function reirie_setup() {
	load_theme_textdomain( 'reirie', REIRIE_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus( array(
		'primary' => __( 'グローバルナビゲーション', 'reirie' ),
		'footer'  => __( 'フッターメニュー', 'reirie' ),
	) );

	// アイキャッチ画像のサイズ
	add_image_size( 'reirie-jacket', 800, 800, true );
	add_image_size( 'reirie-thumb-16-9', 800, 450, true );
	add_image_size( 'reirie-member', 800, 1000, true );
}
add_action( 'after_setup_theme', 'reirie_setup' );

/* ==========================================================
   CSS / JS の読み込み
========================================================== */
function reirie_enqueue_assets() {
	// Google Fonts — weight を絞って軽量化 + 非同期ロード（後段のフィルタで media を print → all に切替）
	wp_enqueue_style(
		'reirie-google-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Noto+Sans+JP:wght@400;700&family=Zen+Maru+Gothic:wght@500;700;900&family=Shrikhand&display=swap',
		array(),
		null
	);

	// メインCSS（フォント依存を解除 — フォントは後追いで適用）
	wp_enqueue_style(
		'reirie-main',
		REIRIE_URI . '/assets/css/main.css',
		array(),
		REIRIE_VERSION
	);

	// テーマ識別用style.css（必須）
	wp_enqueue_style(
		'reirie-style',
		get_stylesheet_uri(),
		array( 'reirie-main' ),
		REIRIE_VERSION
	);

	// フロントページではヒーロー用ポスター画像を preload（LCP 高速化）
	if ( is_front_page() ) {
		add_action( 'wp_head', function() {
			$webp = REIRIE_URI . '/assets/img/hero-poster.webp';
			$jpg  = REIRIE_URI . '/assets/img/hero-poster.jpg';
			echo '<link rel="preload" as="image" href="' . esc_url( $webp ) . '" type="image/webp" fetchpriority="high">' . "\n";
			echo '<link rel="preload" as="image" href="' . esc_url( $jpg ) . '" type="image/jpeg">' . "\n";
		}, 1 );
	}

	// パーティクル — モバイル/省データ環境では読み込まない
	if ( ! wp_is_mobile() ) {
		wp_enqueue_script(
			'reirie-particles',
			REIRIE_URI . '/assets/js/particles.js',
			array(),
			REIRIE_VERSION,
			true
		);
	}

	// メインJS
	wp_enqueue_script(
		'reirie-main',
		REIRIE_URI . '/assets/js/main.js',
		array(),
		REIRIE_VERSION,
		true
	);

	// メインJS にモバイル情報を渡す
	wp_localize_script( 'reirie-main', 'REIRIE_ENV', array(
		'isMobile' => wp_is_mobile() ? 1 : 0,
	) );
}
add_action( 'wp_enqueue_scripts', 'reirie_enqueue_assets' );

/* ==========================================================
   <head> の先頭に preconnect / dns-prefetch を追加
========================================================== */
function reirie_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com', 'crossorigin' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'reirie_resource_hints', 10, 2 );

/* ==========================================================
   フロントのスクリプトを defer 化
========================================================== */
function reirie_defer_scripts( $tag, $handle, $src ) {
	if ( is_admin() ) return $tag;
	$defer_handles = array( 'reirie-main', 'reirie-particles', 'reirie-contact' );
	if ( in_array( $handle, $defer_handles, true ) && false === strpos( $tag, ' defer' ) ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'reirie_defer_scripts', 10, 3 );

/* ==========================================================
   Google Fonts を非同期ロード (render-blocking 解消)
========================================================== */
function reirie_async_google_fonts( $html, $handle ) {
	if ( is_admin() ) return $html;
	if ( $handle === 'reirie-google-fonts' ) {
		// media="print" → onload で all に切替 (Filament Group の有名な手法)
		$html = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
		$html = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $html );
		// noscript フォールバック
		$html .= '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Noto+Sans+JP:wght@400;700&family=Shrikhand&display=swap"></noscript>' . "\n";
	}
	return $html;
}
add_filter( 'style_loader_tag', 'reirie_async_google_fonts', 10, 2 );

/* ==========================================================
   不要な絵文字スクリプト/スタイルを停止（軽量化）
========================================================== */
function reirie_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', function( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	} );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'reirie_disable_emoji' );

/* ==========================================================
   各機能ファイルの読み込み
========================================================== */
require_once REIRIE_DIR . '/inc/post-types.php';
require_once REIRIE_DIR . '/inc/customizer.php';
require_once REIRIE_DIR . '/inc/template-functions.php';
require_once REIRIE_DIR . '/inc/acf-fallback.php';
require_once REIRIE_DIR . '/inc/admin-notices.php';
require_once REIRIE_DIR . '/inc/admin-dashboard.php';
require_once REIRIE_DIR . '/inc/admin-content.php';
require_once REIRIE_DIR . '/inc/contact-form.php';
require_once REIRIE_DIR . '/inc/legal-pages.php';
require_once REIRIE_DIR . '/inc/admin-members.php';
require_once REIRIE_DIR . '/inc/ajax-schedule.php';
require_once REIRIE_DIR . '/inc/numeric-slugs.php';

/* ==========================================================
   ACF JSON の保存先 / 読み込み元（テーマ内で同期）
========================================================== */
function reirie_acf_json_save_point( $path ) {
	return REIRIE_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'reirie_acf_json_save_point' );

function reirie_acf_json_load_point( $paths ) {
	$paths[] = REIRIE_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'reirie_acf_json_load_point' );

/* ==========================================================
   抜粋の文字数制御
========================================================== */
function reirie_excerpt_length() { return 60; }
add_filter( 'excerpt_length', 'reirie_excerpt_length', 999 );

function reirie_excerpt_more() { return '…'; }
add_filter( 'excerpt_more', 'reirie_excerpt_more' );

/* ==========================================================
   body_class に判別用クラスを追加
========================================================== */
function reirie_body_class( $classes ) {
	if ( is_front_page() ) $classes[] = 'is-front';
	if ( is_singular() )   $classes[] = 'is-singular';
	return $classes;
}
add_filter( 'body_class', 'reirie_body_class' );

/* ==========================================================
   スラッグによるテンプレート自動適用フォールバック
   ----------------------------------------------------------
   固定ページ作成時に「ページテンプレート」のプルダウンから
   選択し忘れても、スラッグが一致すれば自動的に適用する。

   - slug "fanletter"  → page-templates/template-fanletter.php
   - slug "contact"    → page-templates/template-contact.php
   - slug "privacy"    → page-templates/template-privacy.php
   - slug "tokushoho"  → page-templates/template-tokushoho.php
   - slug "company"    → page-templates/template-company.php
========================================================== */
function reirie_auto_page_template( $template ) {
	if ( ! is_page() ) return $template;

	$post = get_queried_object();
	if ( ! $post || empty( $post->post_name ) ) return $template;

	// すでにテンプレートが明示的に設定されていれば、それを優先
	$assigned = get_page_template_slug( $post->ID );
	if ( ! empty( $assigned ) && $assigned !== 'default' ) {
		return $template;
	}

	$slug_map = array(
		'fanletter' => 'page-templates/template-fanletter.php',
		'contact'   => 'page-templates/template-contact.php',
		'privacy'   => 'page-templates/template-privacy.php',
		'tokushoho' => 'page-templates/template-tokushoho.php',
		'company'   => 'page-templates/template-company.php',
	);

	if ( isset( $slug_map[ $post->post_name ] ) ) {
		$candidate = locate_template( $slug_map[ $post->post_name ] );
		if ( $candidate ) {
			return $candidate;
		}
	}

	return $template;
}
add_filter( 'template_include', 'reirie_auto_page_template', 99 );
/* SEO update: 1783100894 */

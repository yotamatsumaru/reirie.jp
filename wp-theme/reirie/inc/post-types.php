<?php
/**
 * カスタム投稿タイプ & タクソノミーの登録
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function reirie_register_post_types() {

	/* ---------- News（お知らせ）---------- */
	register_post_type( 'news', array(
		'labels' => array(
			'name'          => 'News',
			'singular_name' => 'News',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しいお知らせを追加',
			'edit_item'     => 'お知らせを編集',
			'all_items'     => 'すべてのお知らせ',
			'menu_name'     => 'News',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-megaphone',
		'menu_position' => 5,
		'rewrite'      => array( 'slug' => 'news' ),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );

	register_taxonomy( 'news_category', 'news', array(
		'labels' => array(
			'name'          => 'カテゴリー',
			'singular_name' => 'カテゴリー',
		),
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'news-category' ),
	) );

	/* ---------- Schedule（スケジュール）---------- */
	register_post_type( 'schedule', array(
		'labels' => array(
			'name'          => 'Schedule',
			'singular_name' => 'Schedule',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しいスケジュールを追加',
			'edit_item'     => 'スケジュールを編集',
			'all_items'     => 'すべてのスケジュール',
			'menu_name'     => 'Schedule',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-calendar-alt',
		'menu_position' => 6,
		'rewrite'      => array( 'slug' => 'schedule' ),
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );

	register_taxonomy( 'schedule_category', 'schedule', array(
		'labels' => array(
			'name'          => 'カテゴリー',
			'singular_name' => 'カテゴリー',
		),
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'schedule-category' ),
	) );

	/* ---------- Discography（作品）---------- */
	register_post_type( 'discography', array(
		'labels' => array(
			'name'          => 'Discography',
			'singular_name' => 'Discography',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しい作品を追加',
			'edit_item'     => '作品を編集',
			'all_items'     => 'すべての作品',
			'menu_name'     => 'Discography',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-format-audio',
		'menu_position' => 7,
		'rewrite'      => array( 'slug' => 'discography' ),
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );

	/* ---------- Movie（動画）---------- */
	register_post_type( 'movie', array(
		'labels' => array(
			'name'          => 'Movie',
			'singular_name' => 'Movie',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しい動画を追加',
			'edit_item'     => '動画を編集',
			'all_items'     => 'すべての動画',
			'menu_name'     => 'Movie',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-format-video',
		'menu_position' => 8,
		'rewrite'      => array( 'slug' => 'movie' ),
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );

	/* ---------- Member（メンバー）---------- */
	register_post_type( 'member', array(
		'labels' => array(
			'name'          => 'Member',
			'singular_name' => 'Member',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しいメンバーを追加',
			'edit_item'     => 'メンバーを編集',
			'all_items'     => 'すべてのメンバー',
			'menu_name'     => 'Member',
		),
		'public'       => true,
		'has_archive'  => false,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-groups',
		'menu_position' => 9,
		'rewrite'      => array( 'slug' => 'member' ),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	) );

	/* ---------- Goods（グッズ）---------- */
	register_post_type( 'goods', array(
		'labels' => array(
			'name'          => 'Goods',
			'singular_name' => 'Goods',
			'add_new'       => '新規追加',
			'add_new_item'  => '新しいグッズを追加',
			'edit_item'     => 'グッズを編集',
			'all_items'     => 'すべてのグッズ',
			'menu_name'     => 'Goods',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-cart',
		'menu_position' => 10,
		'rewrite'      => array( 'slug' => 'goods' ),
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );
}
add_action( 'init', 'reirie_register_post_types' );

/**
 * テーマ有効化時にパーマリンクをフラッシュ
 */
function reirie_flush_rewrite() {
	reirie_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'reirie_flush_rewrite' );

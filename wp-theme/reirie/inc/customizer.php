<?php
/**
 * テーマカスタマイザー（外観 → カスタマイズ）
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function reirie_customize_register( $wp_customize ) {

	/* ============================================================
	   1. ヒーロー
	   ============================================================ */
	$wp_customize->add_section( 'reirie_hero', array(
		'title'       => 'REIRIE：ヒーロービジョン',
		'description' => 'トップ画面の背景動画・画像・タイトルなどを設定します。',
		'priority'    => 30,
	) );

	// 背景タイプ（動画 or 画像）
	$wp_customize->add_setting( 'reirie_hero_bg_type', array(
		'default'           => 'video',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_bg_type', array(
		'label'       => '背景の種類',
		'description' => '動画と画像のどちらを表示するか選択',
		'section'     => 'reirie_hero',
		'type'        => 'select',
		'choices'     => array(
			'video' => '動画を背景にする',
			'image' => '画像を背景にする',
		),
	) );

	// ヒーロー動画
	$wp_customize->add_setting( 'reirie_hero_video', array(
		'default'           => REIRIE_URI . '/assets/video/hero.mp4',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'reirie_hero_video', array(
		'label'       => 'ヒーロー背景動画（MP4）',
		'description' => '推奨：1920×1080 / 10〜15秒 / 5MB以下',
		'section'     => 'reirie_hero',
		'mime_type'   => 'video',
	) ) );

	// ヒーロー画像（動画代替 or 画像背景時）
	$wp_customize->add_setting( 'reirie_hero_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'reirie_hero_image', array(
		'label'       => 'ヒーロー背景画像',
		'description' => '推奨：1920×1080以上。背景タイプが「画像」の場合に使用、もしくは動画のポスター画像として使用されます。',
		'section'     => 'reirie_hero',
	) ) );

	// オーバーレイの濃さ
	$wp_customize->add_setting( 'reirie_hero_overlay', array(
		'default'           => 30,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'reirie_hero_overlay', array(
		'label'       => 'オーバーレイの濃さ（%）',
		'description' => '背景の上に重ねる暗幕の透明度。文字が読みづらいときに上げてください（0〜80推奨）',
		'section'     => 'reirie_hero',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 80, 'step' => 5 ),
	) );

	// ヒーロー：サブタイトル
	$wp_customize->add_setting( 'reirie_hero_sub', array(
		'default'           => '2-girls IDOL UNIT',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_sub', array(
		'label'   => 'サブタイトル（タイトル上部の小さな英字）',
		'section' => 'reirie_hero',
		'type'    => 'text',
	) );

	// ヒーロー：メインタイトル
	$wp_customize->add_setting( 'reirie_hero_title', array(
		'default'           => 'REIRIE',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_title', array(
		'label'   => 'メインタイトル（英大文字）',
		'section' => 'reirie_hero',
		'type'    => 'text',
	) );

	// ヒーロー：日本語タイトル
	$wp_customize->add_setting( 'reirie_hero_title_jp', array(
		'default'           => 'レイリエ',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_title_jp', array(
		'label'   => 'タイトル（日本語フリガナ）',
		'section' => 'reirie_hero',
		'type'    => 'text',
	) );

	// ヒーロー：キャッチコピー
	$wp_customize->add_setting( 'reirie_hero_catch', array(
		'default'           => 'ふたりで描く、きらめきの世界。',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_catch', array(
		'label'   => 'キャッチコピー',
		'section' => 'reirie_hero',
		'type'    => 'text',
	) );

	// CTAボタン1：ラベル
	$wp_customize->add_setting( 'reirie_hero_cta1_label', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_cta1_label', array(
		'label'       => 'メインボタンのテキスト（任意）',
		'description' => '例：LATEST RELEASE / FANCLUB / TICKET。空欄の場合は表示されません。',
		'section'     => 'reirie_hero',
		'type'        => 'text',
	) );

	// CTAボタン1：URL
	$wp_customize->add_setting( 'reirie_hero_cta1_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'reirie_hero_cta1_url', array(
		'label'   => 'メインボタンのリンク先URL',
		'section' => 'reirie_hero',
		'type'    => 'url',
	) );

	// CTAボタン2：ラベル
	$wp_customize->add_setting( 'reirie_hero_cta2_label', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_hero_cta2_label', array(
		'label'       => 'サブボタンのテキスト（任意）',
		'description' => '2つ目のボタン。空欄の場合は表示されません。',
		'section'     => 'reirie_hero',
		'type'        => 'text',
	) );

	// CTAボタン2：URL
	$wp_customize->add_setting( 'reirie_hero_cta2_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'reirie_hero_cta2_url', array(
		'label'   => 'サブボタンのリンク先URL',
		'section' => 'reirie_hero',
		'type'    => 'url',
	) );

	// マーキーテキスト
	$wp_customize->add_setting( 'reirie_marquee_text', array(
		'default'           => '★ REIRIE OFFICIAL SITE ★ NEW SINGLE OUT NOW ★ 1st ONE-MAN LIVE 2026.07.20 ★',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_marquee_text', array(
		'label'       => 'スライドテロップ',
		'description' => '画面下部を流れるお知らせテキスト',
		'section'     => 'reirie_hero',
		'type'        => 'text',
	) );

	// マーキー表示ON/OFF
	$wp_customize->add_setting( 'reirie_marquee_show', array(
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'reirie_marquee_show', array(
		'label'   => 'スライドテロップを表示する',
		'section' => 'reirie_hero',
		'type'    => 'checkbox',
	) );

	// マーキー速度（1周にかかる秒数。小さいほど速い）
	$wp_customize->add_setting( 'reirie_marquee_speed', array(
		'default'           => 30,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'reirie_marquee_speed', array(
		'label'       => 'スライドテロップの速度（秒）',
		'description' => '1周にかかる秒数。小さいほど速く流れます（推奨: 10〜40）',
		'section'     => 'reirie_hero',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 5,
			'max'  => 120,
			'step' => 1,
		),
	) );

	/* ============================================================
	   2. SNS リンク
	   ============================================================ */
	$wp_customize->add_section( 'reirie_sns', array(
		'title'    => 'REIRIE：SNSリンク',
		'priority' => 35,
	) );

	$sns_list = array(
		'twitter'   => 'X（Twitter）URL',
		'instagram' => 'Instagram URL',
		'tiktok'    => 'TikTok URL',
		'youtube'   => 'YouTube URL',
	);
	foreach ( $sns_list as $key => $label ) {
		$wp_customize->add_setting( 'reirie_sns_' . $key, array(
			'default'           => '#',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'reirie_sns_' . $key, array(
			'label'   => $label,
			'section' => 'reirie_sns',
			'type'    => 'url',
		) );
	}

	/* ============================================================
	   3. お問い合わせ・ファンクラブ
	   ============================================================ */
	$wp_customize->add_section( 'reirie_contact', array(
		'title'    => 'REIRIE：お問い合わせカード',
		'priority' => 40,
	) );

	$contact_cards = array(
		'fanclub' => array(
			'label_default' => 'FANCLUB',
			'title_default' => 'LIE LIE LAND',
			'desc_default'  => 'オフィシャルファンクラブ会員募集中！',
		),
		'press'   => array(
			'label_default' => 'PRESS',
			'title_default' => '取材・出演',
			'desc_default'  => 'メディア・取材のお問い合わせはこちら',
		),
		'fanmail' => array(
			'label_default' => 'FAN MAIL',
			'title_default' => 'ファンレター',
			'desc_default'  => '2人へのお手紙の送り先はこちら',
		),
	);

	foreach ( $contact_cards as $key => $defaults ) {

		$wp_customize->add_setting( 'reirie_contact_' . $key . '_label', array(
			'default'           => $defaults['label_default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'reirie_contact_' . $key . '_label', array(
			'label'   => '［' . $key . '］ラベル',
			'section' => 'reirie_contact',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( 'reirie_contact_' . $key . '_title', array(
			'default'           => $defaults['title_default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'reirie_contact_' . $key . '_title', array(
			'label'   => '［' . $key . '］タイトル',
			'section' => 'reirie_contact',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( 'reirie_contact_' . $key . '_desc', array(
			'default'           => $defaults['desc_default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'reirie_contact_' . $key . '_desc', array(
			'label'   => '［' . $key . '］説明',
			'section' => 'reirie_contact',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( 'reirie_contact_' . $key . '_url', array(
			'default'           => '#',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'reirie_contact_' . $key . '_url', array(
			'label'   => '［' . $key . '］リンクURL',
			'section' => 'reirie_contact',
			'type'    => 'url',
		) );
	}

	/* ============================================================
	   4. フッター
	   ============================================================ */
	$wp_customize->add_section( 'reirie_footer', array(
		'title'    => 'REIRIE：フッター',
		'priority' => 50,
	) );

	$wp_customize->add_setting( 'reirie_footer_copy', array(
		'default'           => '© 2026 REIRIE OFFICIAL. All Rights Reserved.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_footer_copy', array(
		'label'   => 'コピーライト表記',
		'section' => 'reirie_footer',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'reirie_footer_subtitle', array(
		'default'           => '2-girls IDOL UNIT',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_footer_subtitle', array(
		'label'   => 'フッターサブタイトル',
		'section' => 'reirie_footer',
		'type'    => 'text',
	) );
}
add_action( 'customize_register', 'reirie_customize_register' );

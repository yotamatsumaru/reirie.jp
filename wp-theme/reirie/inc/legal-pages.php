<?php
/**
 * 法定ページ管理（プライバシーポリシー / 特商法 / 運営会社）
 *
 * - テーマ有効化時に3つの固定ページを自動生成
 * - Customizer に「運営会社情報」セクションを追加
 * - フッターから呼び出すためのヘルパー関数を提供
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1. 法定ページの定義
   ============================================================ */
function reirie_legal_pages_config() {
	return array(
		'privacy' => array(
			'slug'     => 'privacy-policy',
			'title'    => 'プライバシーポリシー',
			'template' => 'page-templates/template-privacy.php',
			'option'   => 'reirie_page_id_privacy',
		),
		'tokushoho' => array(
			'slug'     => 'tokushoho',
			'title'    => '特定商取引法に基づく表示',
			'template' => 'page-templates/template-tokushoho.php',
			'option'   => 'reirie_page_id_tokushoho',
		),
		'company' => array(
			'slug'     => 'company',
			'title'    => '運営会社',
			'template' => 'page-templates/template-company.php',
			'option'   => 'reirie_page_id_company',
		),
	);
}

/* ============================================================
   2. テーマ有効化時に固定ページを自動生成
   ============================================================ */
function reirie_create_legal_pages() {
	foreach ( reirie_legal_pages_config() as $key => $config ) {
		$existing_id = (int) get_option( $config['option'] );

		// 既存ページがまだ「公開状態」で存在するか確認
		// get_post_status() は trash 状態でも 'trash' を返すため、'publish' のみ許容
		if ( $existing_id ) {
			$status = get_post_status( $existing_id );
			if ( $status === 'publish' ) {
				// 念のためテンプレート設定を維持
				update_post_meta( $existing_id, '_wp_page_template', $config['template'] );
				continue;
			}
			// trash や draft、または削除済み(false) → オプションをクリアして再作成へ
			delete_option( $config['option'] );
		}

		// スラッグで重複チェック（publish 状態のみ許可）
		$existing = get_page_by_path( $config['slug'] );
		if ( $existing && $existing->post_status === 'publish' ) {
			update_option( $config['option'], $existing->ID );
			update_post_meta( $existing->ID, '_wp_page_template', $config['template'] );
			continue;
		}

		// trash にあるページがあれば復元
		if ( $existing && $existing->post_status === 'trash' ) {
			wp_untrash_post( $existing->ID );
			wp_update_post( array(
				'ID'          => $existing->ID,
				'post_status' => 'publish',
			) );
			update_option( $config['option'], $existing->ID );
			update_post_meta( $existing->ID, '_wp_page_template', $config['template'] );
			continue;
		}

		// 新規作成
		$page_id = wp_insert_post( array(
			'post_title'   => $config['title'],
			'post_name'    => $config['slug'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => 1,
			'meta_input'   => array(
				'_wp_page_template' => $config['template'],
			),
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( $config['option'], $page_id );
		}
	}
}
add_action( 'after_switch_theme', 'reirie_create_legal_pages' );

// 管理画面アクセス時にも未作成ページを補完（手動削除時の救済）
function reirie_ensure_legal_pages() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	// 初回のみ実行（パフォーマンス対策のためトランジェント）
	if ( get_transient( 'reirie_legal_pages_checked' ) ) return;
	reirie_create_legal_pages();
	set_transient( 'reirie_legal_pages_checked', 1, HOUR_IN_SECONDS );
}
add_action( 'admin_init', 'reirie_ensure_legal_pages' );

// 前面公開側（未ログイン時）の 404 レスポンスでも、legal ページに限り再作成を試みる
// robots クロールで /privacy-policy/ が 404 を返しつづける事態を防ぐ
function reirie_ensure_legal_pages_on_404() {
	if ( ! is_404() ) return;

	// 現在のリクエストパスがlegalページのslugと一致するか
	$request_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $request_path === '' ) return;

	$request_slug = basename( $request_path );

	$config = reirie_legal_pages_config();
	$target_key = null;
	foreach ( $config as $key => $c ) {
		if ( $c['slug'] === $request_slug ) {
			$target_key = $key;
			break;
		}
	}
	if ( ! $target_key ) return;

	// 5分間の再試行クールダウン（DoS対策）
	if ( get_transient( 'reirie_legal_404_retry_' . $target_key ) ) return;
	set_transient( 'reirie_legal_404_retry_' . $target_key, 1, 5 * MINUTE_IN_SECONDS );

	// 再作成を実行
	reirie_create_legal_pages();

	// 再作成後、成功していればリロード（robots も再クロールで 200 を得る）
	$new_id = (int) get_option( $config[ $target_key ]['option'] );
	if ( $new_id && get_post_status( $new_id ) === 'publish' ) {
		wp_safe_redirect( get_permalink( $new_id ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'reirie_ensure_legal_pages_on_404', 1 );

/* ============================================================
   3. ページURL取得ヘルパー
   ============================================================ */
function reirie_legal_page_url( $key ) {
	$config = reirie_legal_pages_config();
	if ( ! isset( $config[ $key ] ) ) return '#';

	$page_id = (int) get_option( $config[ $key ]['option'] );
	if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
		return get_permalink( $page_id );
	}

	// fallback: スラッグでURL生成
	return home_url( '/' . $config[ $key ]['slug'] . '/' );
}

/* ============================================================
   4. Customizer に「運営会社情報」セクションを追加
   ============================================================ */
function reirie_legal_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'reirie_company', array(
		'title'       => 'REIRIE：運営会社・法定ページ',
		'description' => 'プライバシーポリシー・特定商取引法に基づく表示・運営会社のページに表示される情報を設定します。',
		'priority'    => 200,
	) );

	$fields = array(
		// 会社情報
		'reirie_company_name'      => array( 'label' => '運営会社名',           'default' => 'REIRIE OFFICIAL',              'type' => 'text' ),
		'reirie_company_name_en'   => array( 'label' => '運営会社名（英語表記）', 'default' => '',                              'type' => 'text' ),
		'reirie_company_ceo'       => array( 'label' => '代表者名',             'default' => '',                              'type' => 'text' ),
		'reirie_company_founded'   => array( 'label' => '設立年月日',           'default' => '',                              'type' => 'text' ),
		'reirie_company_zipcode'   => array( 'label' => '郵便番号',             'default' => '',                              'type' => 'text' ),
		'reirie_company_address'   => array( 'label' => '所在地（住所）',       'default' => '',                              'type' => 'textarea' ),
		'reirie_company_business'  => array( 'label' => '事業内容',             'default' => "・アイドルグループ「REIRIE」の運営\n・楽曲制作・販売\n・グッズ企画・販売", 'type' => 'textarea' ),
		'reirie_company_email'     => array( 'label' => 'お問い合わせメール',   'default' => '',                              'type' => 'text' ),
		'reirie_company_tel'       => array( 'label' => '電話番号（任意）',     'default' => '',                              'type' => 'text' ),
		'reirie_company_website'   => array( 'label' => '公式サイトURL',        'default' => home_url( '/' ),                 'type' => 'url' ),

		// 特商法
		'reirie_tokushoho_seller'      => array( 'label' => '販売事業者',                 'default' => '',  'type' => 'text' ),
		'reirie_tokushoho_manager'     => array( 'label' => '運営統括責任者',             'default' => '',  'type' => 'text' ),
		'reirie_tokushoho_price'       => array( 'label' => '販売価格について',           'default' => '各商品ページに表示される価格に準じます（消費税込）。', 'type' => 'textarea' ),
		'reirie_tokushoho_extra_fee'   => array( 'label' => '商品代金以外の必要料金',     'default' => "・送料：全国一律 ¥800（税込）／¥10,000 以上のご注文で送料無料\n・代引き手数料：¥330（税込）", 'type' => 'textarea' ),
		'reirie_tokushoho_payment'     => array( 'label' => 'お支払い方法',               'default' => "・クレジットカード（VISA / MasterCard / JCB / AMEX）\n・銀行振込（前払い）\n・代金引換", 'type' => 'textarea' ),
		'reirie_tokushoho_delivery'    => array( 'label' => 'お届け時期',                 'default' => 'ご注文確認後、3〜7営業日以内に発送いたします。受注生産品については各商品ページに記載の発送時期となります。', 'type' => 'textarea' ),
		'reirie_tokushoho_return'      => array( 'label' => '返品・交換について',         'default' => "商品の特性上、お客様都合による返品・交換はお受けできません。\n万が一、商品に欠陥・不良がございましたら、商品到着後7日以内にメールにてご連絡ください。送料弊社負担にて交換させていただきます。", 'type' => 'textarea' ),

		// プライバシーポリシー
		'reirie_privacy_updated'  => array( 'label' => 'プライバシーポリシー最終改定日', 'default' => date( 'Y年n月j日' ), 'type' => 'text' ),
	);

	foreach ( $fields as $id => $f ) {
		$sanitize = ( $f['type'] === 'url' ) ? 'esc_url_raw' : ( $f['type'] === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field' );
		$wp_customize->add_setting( $id, array(
			'default'           => $f['default'],
			'sanitize_callback' => $sanitize,
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $f['label'],
			'section' => 'reirie_company',
			'type'    => $f['type'],
		) );
	}
}
add_action( 'customize_register', 'reirie_legal_customize_register' );

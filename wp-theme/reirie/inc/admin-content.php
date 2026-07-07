<?php
/**
 * REIRIE コンテンツ管理（AJAX エンドポイント + フィールドスキーマ）
 *
 * REIRIE設定ダッシュボード内でCPT（member/news/schedule/discography/movie/goods/contact_msg）の
 * 一覧表示・新規追加・編集・削除を完結させるための裏側ロジック。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   各CPTのフィールドスキーマ
   ============================================================ */
function reirie_content_schema() {
	return array(
		'member' => array(
			'label'          => 'メンバー',
			'icon'           => 'groups',
			'desc'           => 'アーティストのプロフィール、メンバーカラー、SNS、メッセージを管理します。',
			'singular'       => 'メンバー',
			'thumbnail'      => true,
			'thumbnail_label' => 'プロフィール写真（推奨：縦4:5 / 800×1000）',
			'editor'         => false,
			'menu_order'     => true,
			'columns'        => array(
				array( 'key' => 'thumbnail', 'label' => '写真', 'type' => 'thumbnail' ),
				array( 'key' => 'title',     'label' => '名前（英）', 'type' => 'title' ),
				array( 'key' => 'member_name_jp', 'label' => 'カナ', 'type' => 'meta' ),
				array( 'key' => 'member_color',   'label' => 'カラー', 'type' => 'meta' ),
				array( 'key' => 'menu_order',     'label' => '順序', 'type' => 'menu_order' ),
			),
			'fields' => array(
				array( 'name' => 'member_name_jp',     'label' => '名前（カナ）',          'type' => 'text',   'placeholder' => 'レイ' ),
				array( 'name' => 'member_color',       'label' => 'メンバーカラー名',      'type' => 'text',   'placeholder' => 'PINK / SKY BLUE / LAVENDER' ),
				array( 'name' => 'member_color_class', 'label' => 'カラークラス',          'type' => 'select', 'choices' => array(
					'color-pink'   => 'ピンク',
					'color-blue'   => 'スカイブルー',
					'color-purple' => 'パープル',
					'color-yellow' => 'イエロー',
					'color-green'  => 'グリーン',
				) ),
				array( 'name' => 'member_photo_class', 'label' => 'フォト背景クラス（写真未設定時）', 'type' => 'select', 'choices' => array(
					'photo-rei'    => 'Rei スタイル（ピンク系）',
					'photo-rie'    => 'Rie スタイル（ブルー系）',
					'photo-purple' => 'パープル系',
				) ),
				array( 'name' => 'member_initial',  'label' => 'イニシャル文字',          'type' => 'text',   'desc' => '写真未設定時に表示される一文字' ),
				array( 'name' => 'member_catch',    'label' => 'キャッチフレーズ',        'type' => 'text',   'desc' => '例：太陽みたいな笑顔担当' ),
				array( 'name' => '__divider_profile', 'label' => 'プロフィール詳細', 'type' => 'divider' ),
				array( 'name' => 'member_birthday', 'label' => '誕生日',                  'type' => 'text',   'placeholder' => '9月12日' ),
				array( 'name' => 'member_blood',    'label' => '血液型',                  'type' => 'text',   'placeholder' => 'A型' ),
				array( 'name' => 'member_hometown', 'label' => '出身地',                  'type' => 'text',   'placeholder' => '東京都' ),
				array( 'name' => 'member_height',   'label' => '身長',                    'type' => 'text',   'placeholder' => '158cm' ),
				array( 'name' => 'member_hobby',    'label' => '趣味',                    'type' => 'text' ),
				array( 'name' => 'member_charm',    'label' => 'チャームポイント',        'type' => 'text' ),
				array( 'name' => 'member_skill',    'label' => 'スペシャルスキル',        'type' => 'text' ),
				array( 'name' => 'member_message',  'label' => 'ファンへのメッセージ',    'type' => 'textarea', 'rows' => 4, 'desc' => '120文字程度推奨' ),
				array( 'name' => '__divider_sns',   'label' => 'SNS', 'type' => 'divider' ),
				array( 'name' => 'member_sns_twitter',   'label' => 'X（Twitter）URL', 'type' => 'url' ),
				array( 'name' => 'member_sns_instagram', 'label' => 'Instagram URL',   'type' => 'url' ),
				array( 'name' => 'member_sns_tiktok',    'label' => 'TikTok URL',      'type' => 'url' ),
				array( 'name' => 'member_sns_blog',      'label' => 'ブログ / 個人サイト URL', 'type' => 'url' ),
			),
		),

		'news' => array(
			'label'    => 'お知らせ',
			'icon'     => 'megaphone',
			'desc'     => '最新情報・告知を投稿します。',
			'singular' => 'お知らせ',
			'thumbnail' => true,
			'editor'   => true,
			'columns'  => array(
				array( 'key' => 'thumbnail', 'label' => '画像', 'type' => 'thumbnail' ),
				// 「公開日時」= news_date カスタムフィールド（未設定ならフロント表示時は
				// 投稿の作成日時にフォールバックされる。一覧上は「未設定＝空欄」のまま
				// 表示し、実際に指定されているかどうかが一目で分かるようにする）
				array( 'key' => 'news_date', 'label' => '公開日時', 'type' => 'meta' ),
				// 「作成日時」= WordPress投稿としての作成日時（post_date）。
				// 常に値が入っているため、公開日時が未設定の投稿でも
				// 何かしらの日時情報を確認できるようにするための列。
				array( 'key' => 'created',   'label' => '作成日時', 'type' => 'created' ),
				array( 'key' => 'title',     'label' => 'タイトル', 'type' => 'title' ),
			),
			'fields' => array(
				array( 'name' => 'news_date', 'label' => '公開日時', 'type' => 'datetime', 'desc' => '日付と時刻を指定できます。未入力の場合は投稿の作成日時が使われます。投稿の並び順にも影響します。' ),
				array( 'name' => 'news_link', 'label' => '外部リンクURL（任意）', 'type' => 'url', 'desc' => 'クリック時に外部サイトを開きたい場合のみ' ),
			),
		),

		'schedule' => array(
			'label'    => 'スケジュール',
			'icon'     => 'calendar-alt',
			'desc'     => 'ライブ・イベント情報を登録します。',
			'singular' => 'スケジュール',
			'thumbnail' => false,
			'editor'   => true,
			'columns'  => array(
				array( 'key' => 'schedule_date',  'label' => '開催日', 'type' => 'meta' ),
				array( 'key' => 'title',          'label' => 'イベント名', 'type' => 'title' ),
				array( 'key' => 'schedule_venue', 'label' => '会場', 'type' => 'meta' ),
			),
			'fields' => array(
				array( 'name' => 'schedule_date',      'label' => '開催日 *必須',        'type' => 'date' ),
				array( 'name' => 'schedule_category',  'label' => 'カテゴリー',          'type' => 'select', 'choices' => array(
					''      => '未選択（バッジを表示しない）',
					'LIVE'  => 'LIVE（ライブ）',
					'EVENT' => 'EVENT（イベント）',
					'OTHER' => 'OTHER（その他）',
				), 'desc' => 'スケジュールカードや個別ページに表示されるバッジ種別' ),
				array( 'name' => 'schedule_venue',     'label' => '会場',                'type' => 'text', 'placeholder' => '渋谷 TSUTAYA O-WEST' ),
				array( 'name' => 'schedule_time',      'label' => '開場/開演',           'type' => 'text', 'placeholder' => 'OPEN 18:00 / START 19:00' ),
				array( 'name' => 'schedule_link',      'label' => '詳細ページURL',       'type' => 'url',  'desc' => 'チケット販売サイトなど' ),
				array( 'name' => 'schedule_highlight', 'label' => 'ハイライト表示（ワンマンライブ等を目立たせる）', 'type' => 'checkbox' ),
			),
		),

		'discography' => array(
			'label'    => 'ディスコグラフィ',
			'icon'     => 'album',
			'desc'     => 'シングル・アルバム・ジャケット・配信リンクを登録します。',
			'singular' => '作品',
			'thumbnail' => true,
			'thumbnail_label' => 'ジャケット画像（正方形推奨）',
			'editor'   => false,
			'columns'  => array(
				array( 'key' => 'thumbnail',          'label' => 'ジャケット', 'type' => 'thumbnail' ),
				array( 'key' => 'title',              'label' => '作品名', 'type' => 'title' ),
				array( 'key' => 'disco_category',     'label' => 'カテゴリ', 'type' => 'meta' ),
				array( 'key' => 'disco_release_date', 'label' => 'リリース日', 'type' => 'meta' ),
			),
			'fields' => array(
				array( 'name' => 'disco_category',     'label' => 'カテゴリー',     'type' => 'text',     'placeholder' => '2nd Single' ),
				array( 'name' => 'disco_release_date', 'label' => 'リリース日',     'type' => 'date' ),
				array( 'name' => 'disco_price',        'label' => '価格',           'type' => 'text',     'placeholder' => '¥1,500（税込）' ),
				array( 'name' => 'disco_tracks',       'label' => 'トラックリスト', 'type' => 'textarea', 'rows' => 6, 'desc' => '1行に1曲（例：1. Twinkle Heart）' ),
				array( 'name' => 'disco_is_new',       'label' => 'NEWバッジを表示（最新作にチェック）', 'type' => 'checkbox' ),
				array( 'name' => '__divider_links',    'label' => '購入・配信リンク（入力したリンクのみ表示されます）', 'type' => 'divider' ),
				array( 'name' => 'disco_buy_url',      'label' => '購入ページURL',       'type' => 'url', 'desc' => 'CDショップ・公式ストアなど' ),
				array( 'name' => 'disco_apple_url',    'label' => 'Apple Music',         'type' => 'url' ),
				array( 'name' => 'disco_spotify_url',  'label' => 'Spotify',             'type' => 'url' ),
				array( 'name' => 'disco_youtube_url',  'label' => 'YouTube Music',       'type' => 'url' ),
				array( 'name' => 'disco_linkco_url',   'label' => 'まとめリンク（linkco / Linkfire等）', 'type' => 'url' ),
			),
		),

		'movie' => array(
			'label'    => '動画',
			'icon'     => 'video-alt3',
			'desc'     => 'MV・特典映像・YouTube URLを登録します。',
			'singular' => '動画',
			'thumbnail' => true,
			'thumbnail_label' => 'サムネイル画像（16:9）',
			'editor'   => false,
			'columns'  => array(
				array( 'key' => 'thumbnail',   'label' => 'サムネ', 'type' => 'thumbnail' ),
				array( 'key' => 'title',       'label' => 'タイトル', 'type' => 'title' ),
				array( 'key' => 'movie_label', 'label' => 'ラベル', 'type' => 'meta' ),
				array( 'key' => 'movie_date',  'label' => '公開日', 'type' => 'meta' ),
			),
			'fields' => array(
				array( 'name' => 'movie_url',   'label' => '動画URL（YouTube等）', 'type' => 'url', 'placeholder' => 'https://www.youtube.com/watch?v=xxxx' ),
				array( 'name' => 'movie_date',  'label' => '公開日', 'type' => 'date' ),
				array( 'name' => 'movie_label', 'label' => 'ラベル', 'type' => 'text', 'placeholder' => 'MV / Lyric Video / Behind the Scenes' ),
			),
		),

		'goods' => array(
			'label'    => 'グッズ',
			'icon'     => 'cart',
			'desc'     => '物販グッズの画像・価格・販売ページのURLを登録します。',
			'singular' => 'グッズ',
			'thumbnail' => true,
			'thumbnail_label' => '商品画像（正方形推奨）',
			'editor'   => false,
			'columns'  => array(
				array( 'key' => 'thumbnail',   'label' => '画像', 'type' => 'thumbnail' ),
				array( 'key' => 'title',       'label' => '商品名', 'type' => 'title' ),
				array( 'key' => 'goods_price', 'label' => '価格', 'type' => 'meta' ),
				array( 'key' => 'goods_status','label' => 'ステータス', 'type' => 'meta' ),
			),
			'fields' => array(
				array( 'name' => 'goods_price',  'label' => '価格表示', 'type' => 'text',   'placeholder' => '¥3,800（税込）' ),
				array( 'name' => 'goods_link',   'label' => '商品ページURL', 'type' => 'url', 'desc' => '公式ストア等のリンク' ),
				array( 'name' => 'goods_status', 'label' => 'ステータス', 'type' => 'select', 'choices' => array(
					''        => '（表示しない）',
					'NEW'     => 'NEW（新商品）',
					'SOLD OUT'=> 'SOLD OUT（完売）',
					'COMING'  => 'COMING SOON（販売予定）',
					'LIMITED' => 'LIMITED（数量限定）',
				) ),
			),
		),

		'contact_msg' => array(
			'label'      => '受信メッセージ',
			'icon'       => 'email',
			'desc'       => 'お問い合わせフォームから届いたメッセージ。閲覧・削除のみ可能です。',
			'singular'   => 'メッセージ',
			'thumbnail'  => false,
			'editor'     => false,
			'readonly'   => true,
			'no_create'  => true,
			'columns'  => array(
				array( 'key' => 'date',          'label' => '受信日', 'type' => 'date' ),
				array( 'key' => 'title',         'label' => '件名',   'type' => 'title' ),
				array( 'key' => '_contact_name', 'label' => 'お名前', 'type' => 'meta' ),
				array( 'key' => '_contact_type', 'label' => '種別',   'type' => 'meta' ),
			),
			'fields' => array(
				array( 'name' => '_contact_name',    'label' => 'お名前',         'type' => 'text',     'readonly' => true ),
				array( 'name' => '_contact_email',   'label' => 'メールアドレス', 'type' => 'text',     'readonly' => true ),
				array( 'name' => '_contact_type',    'label' => '種別',           'type' => 'select',   'readonly' => true,
					'choices' => array(
						'press'   => 'メディア取材',
						'casting' => '出演依頼',
						'other'   => 'その他お問い合わせ',
						'fanmail' => 'ファンレター（旧）',
					),
				),
				array( 'name' => '_contact_company', 'label' => '会社名・媒体名', 'type' => 'text',     'readonly' => true ),
				array( 'name' => '_contact_tel',     'label' => '電話番号',       'type' => 'text',     'readonly' => true ),
				array( 'name' => '_contact_message', 'label' => '本文',           'type' => 'textarea', 'rows' => 8, 'readonly' => true ),
				array( 'name' => '_contact_ip',      'label' => 'IPアドレス',     'type' => 'text',     'readonly' => true ),
			),
		),
	);
}

/* ============================================================
   AJAX ハンドラ：一覧取得
   ============================================================ */
function reirie_ajax_list() {
	check_ajax_referer( 'reirie_content_nonce', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( array( 'message' => '権限がありません' ) );

	$cpt   = isset( $_GET['cpt'] ) ? sanitize_key( $_GET['cpt'] ) : '';
	$page  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

	$schema = reirie_content_schema();
	if ( ! isset( $schema[ $cpt ] ) ) wp_send_json_error( array( 'message' => '不明なCPT' ) );

	$args = array(
		'post_type'      => $cpt,
		'posts_per_page' => 30,
		'paged'          => $page,
		'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
		's'              => $search,
		'orderby'        => ! empty( $schema[ $cpt ]['menu_order'] ) ? 'menu_order title' : 'date',
		'order'          => ! empty( $schema[ $cpt ]['menu_order'] ) ? 'ASC' : 'DESC',
	);
	$q = new WP_Query( $args );

	$items = array();
	foreach ( $q->posts as $p ) {
		// パーマリンク取得：future の投稿でも pretty URL を返す
		// （numeric-slugs.php の post_type_link フィルタが ?p=NN を /cpt/NN/ に変換してくれる）
		$permalink = get_permalink( $p );

		// 下書き・承認待ち・非公開はフロントで非表示 → URL を表示しない方が安全
		// publish と future（公開予定）のみコピー可能にする
		$has_public_url = in_array( $p->post_status, array( 'publish', 'future' ), true );

		$row = array(
			'id'         => $p->ID,
			'title'      => $p->post_title ? $p->post_title : '(無題)',
			'status'     => $p->post_status,
			'date'       => mysql2date( 'Y-m-d', $p->post_date ),
			// 「作成日時」列（type:'created'）専用。投稿としてWordPressに作成された
			// 日時（post_date）を時刻付きで返す。「公開日時」（newsのカスタムフィールド
			// news_date 等）とは別物で、常に値が入っている。
			'created_at' => mysql2date( 'Y-m-d H:i', $p->post_date ),
			'menu_order' => $p->menu_order,
			'thumbnail'  => '',
			'permalink'  => $permalink,
			'has_url'    => $has_public_url,
			'meta'       => array(),
		);
		if ( has_post_thumbnail( $p->ID ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $p->ID ), 'thumbnail' );
			if ( $src ) $row['thumbnail'] = $src[0];
		}
		foreach ( $schema[ $cpt ]['columns'] as $col ) {
			if ( $col['type'] === 'meta' ) {
				$mv = get_post_meta( $p->ID, $col['key'], true );
				// 対応するフィールド定義を探して datetime / date の場合は表示用にフォーマット
				$field_type = '';
				if ( ! empty( $schema[ $cpt ]['fields'] ) ) {
					foreach ( $schema[ $cpt ]['fields'] as $ff ) {
						if ( isset( $ff['name'] ) && $ff['name'] === $col['key'] ) {
							$field_type = isset( $ff['type'] ) ? $ff['type'] : '';
							break;
						}
					}
				}
				if ( $mv && $field_type === 'datetime' ) {
					$ts = strtotime( $mv );
					if ( $ts ) $mv = date( 'Y-m-d H:i', $ts );
				} elseif ( $mv && $field_type === 'date' ) {
					if ( preg_match( '/^\d{8}$/', $mv ) ) {
						$mv = substr( $mv, 0, 4 ) . '-' . substr( $mv, 4, 2 ) . '-' . substr( $mv, 6, 2 );
					} else {
						$ts = strtotime( $mv );
						if ( $ts ) $mv = date( 'Y-m-d', $ts );
					}
				}
				$row['meta'][ $col['key'] ] = $mv;
			}
		}
		$items[] = $row;
	}

	wp_send_json_success( array(
		'items'      => $items,
		'total'      => (int) $q->found_posts,
		'maxPages'   => (int) $q->max_num_pages,
		'page'       => $page,
	) );
}
add_action( 'wp_ajax_reirie_content_list', 'reirie_ajax_list' );

/* ============================================================
   コンテンツ本文の改行・HTML相互変換ヘルパー
   ----------------------------------------------------------
   ・編集UIでユーザーが textarea に改行のみで入力した内容を、
     保存時に <br> / <p> 付きHTMLに変換して DB に格納する。
   ・編集UIロード時には、保存済みの <p>/<br> を改行に戻して
     textarea で見やすく表示する。
   ============================================================ */
if ( ! function_exists( 'reirie_content_plain_to_html' ) ) :
/**
 * 保存時：本文を安全な HTML に正規化
 *
 * 入力パターンは2種類:
 *   (A) TinyMCE 経由（<p>, <a>, <strong> 等が含まれる）
 *       → 空段落 <p>&nbsp;</p> を <p><br></p> に変換して保持。
 *         wp_kses_post で危険タグ除去のみ。wpautop はかけない。
 *   (B) プレーンテキスト（改行のみ）
 *       → wpautop で <p>/<br> を付与し、make_clickable で裸URLをリンク化。
 */
function reirie_content_plain_to_html( $content ) {
	if ( ! is_string( $content ) ) return '';
	$content = trim( $content );
	if ( $content === '' ) return '';

	// HTMLタグの有無で分岐
	$has_html = (bool) preg_match( '/<[a-z][^>]*>/i', $content );

	if ( $has_html ) {
		// (A) TinyMCE が吐いた HTML
		// TinyMCE は空行を <p>&nbsp;</p> として表現する。
		// このまま保存すると wpautop / WP標準処理で空段落が削除されるため、
		// 表示時にも確実に空行として残る <p><br /></p> に変換しておく。
		$content = preg_replace( '#<p>(\s|&nbsp;|\xC2\xA0)*</p>#i', '<p><br /></p>', $content );

		// 残りの &nbsp; や U+00A0 は通常スペースに正規化（蓄積防止）
		// ただし <p><br></p> 中の <br> は temporarily 置換して保護
		$content = str_replace( '<p><br /></p>', '<!--REIRIE_BLANK_LINE-->', $content );
		$content = str_replace( '<p><br/></p>', '<!--REIRIE_BLANK_LINE-->', $content );
		$content = str_replace( '<p><br></p>', '<!--REIRIE_BLANK_LINE-->', $content );
		$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$content = str_replace( "\xC2\xA0", ' ', $content );
		$content = str_replace( '<!--REIRIE_BLANK_LINE-->', '<p><br /></p>', $content );

		// wp_kses_post で安全な HTML 投稿許可タグに限定
		$content = wp_kses_post( $content );

		// 裸 URL があれば自動リンク化（TinyMCEがリンク化漏れしたケースに備える）
		$content = make_clickable( $content );
		return $content;
	}

	// (B) プレーンテキスト → 段落化 + 自動リンク
	$content = wpautop( $content );
	$content = make_clickable( $content );
	return $content;
}
endif;

if ( ! function_exists( 'reirie_content_html_to_plain' ) ) :
/**
 * 読み込み時：DB保存内容を編集UI（TinyMCE / textarea）用に整形
 *
 *  - TinyMCE はそのまま HTML を表示できるため、HTML 入りはほぼそのまま返す
 *  - 空段落 <p><br /></p> は TinyMCE 内で空行として認識される形式に変換
 *  - プレーンテキスト保存のケースは &nbsp; などをデコードして渡す
 */
function reirie_content_html_to_plain( $content ) {
	if ( ! is_string( $content ) ) return '';
	if ( $content === '' ) return '';

	// HTMLが含まれない場合（プレーン保存）— &nbsp; や &amp; を解いて返す
	if ( ! preg_match( '/<[a-z][^>]*>/i', $content ) ) {
		$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$content = str_replace( "\xC2\xA0", ' ', $content );
		return $content;
	}

	// HTML が入っている場合
	// U+00A0 を一旦 &nbsp; に統一（TinyMCE が空段落を維持しやすくなる）
	$content = str_replace( "\xC2\xA0", '&nbsp;', $content );

	// <p><br /></p> や <p></p>（空段落）を <p>&nbsp;</p> に統一
	// （TinyMCE はこの形式を空行として確実にレンダリングする）
	$content = preg_replace( '#<p>\s*<br\s*/?>\s*</p>#i', '<p>&nbsp;</p>', $content );
	$content = preg_replace( '#<p>\s*</p>#i', '<p>&nbsp;</p>', $content );

	return $content;
}
endif;

/* ============================================================
   AJAX ハンドラ：1件取得
   ============================================================ */
function reirie_ajax_get() {
	check_ajax_referer( 'reirie_content_nonce', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( array( 'message' => '権限がありません' ) );

	$cpt = isset( $_GET['cpt'] ) ? sanitize_key( $_GET['cpt'] ) : '';
	$id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$schema = reirie_content_schema();
	if ( ! isset( $schema[ $cpt ] ) ) wp_send_json_error( array( 'message' => '不明なCPT' ) );

	$data = array(
		'id'         => 0,
		'title'      => '',
		'content'    => '',
		'status'     => 'publish',
		'menu_order' => 0,
		'thumb_id'   => 0,
		'thumb_url'  => '',
		'permalink'  => '',
		'fields'     => array(),
	);

	if ( $id ) {
		$p = get_post( $id );
		if ( ! $p || $p->post_type !== $cpt ) wp_send_json_error( array( 'message' => '対象が見つかりません' ) );
		$data['id']         = $p->ID;
		$data['title']      = $p->post_title;
		// 編集UI（textarea）で見やすいよう、保存済みの <p> や <br> を改行に戻す
		$data['content']    = reirie_content_html_to_plain( $p->post_content );
		$data['status']     = $p->post_status;
		$data['menu_order'] = $p->menu_order;
		$data['permalink']  = get_permalink( $p );
		$tid = get_post_thumbnail_id( $p->ID );
		if ( $tid ) {
			$data['thumb_id'] = (int) $tid;
			$src = wp_get_attachment_image_src( $tid, 'medium' );
			if ( $src ) $data['thumb_url'] = $src[0];
		}
		foreach ( $schema[ $cpt ]['fields'] as $f ) {
			if ( $f['type'] === 'divider' ) continue;
			$raw_val = get_post_meta( $p->ID, $f['name'], true );

			// date フィールドは ACF 形式（Ymd）も Y-m-d 形式も両対応で <input type="date"> 用の Y-m-d に正規化
			if ( $f['type'] === 'date' && $raw_val ) {
				// 8桁（Ymd）なら Y-m-d に整形
				if ( preg_match( '/^\d{8}$/', $raw_val ) ) {
					$raw_val = substr( $raw_val, 0, 4 ) . '-' . substr( $raw_val, 4, 2 ) . '-' . substr( $raw_val, 6, 2 );
				} else {
					// 他の形式は strtotime で吸収
					$ts = strtotime( $raw_val );
					if ( $ts ) $raw_val = date( 'Y-m-d', $ts );
				}
			}

			// datetime フィールドは <input type="datetime-local"> 用の Y-m-d\TH:i 形式に整形
			// ACFの Ymd（8桁）/ MySQL の Y-m-d H:i:s / Y-m-d など色々なケースを吸収
			if ( $f['type'] === 'datetime' && $raw_val ) {
				if ( preg_match( '/^\d{8}$/', $raw_val ) ) {
					// 8桁 Ymd（時刻なし） → 00:00 補完
					$raw_val = substr( $raw_val, 0, 4 ) . '-' . substr( $raw_val, 4, 2 ) . '-' . substr( $raw_val, 6, 2 ) . 'T00:00';
				} else {
					$ts = strtotime( $raw_val );
					if ( $ts ) $raw_val = date( 'Y-m-d\TH:i', $ts );
				}
			}

			$data['fields'][ $f['name'] ] = $raw_val;
		}
	}

	wp_send_json_success( $data );
}
add_action( 'wp_ajax_reirie_content_get', 'reirie_ajax_get' );

/* ============================================================
   AJAX ハンドラ：保存
   ============================================================ */
function reirie_ajax_save() {
	check_ajax_referer( 'reirie_content_nonce', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( array( 'message' => '権限がありません' ) );

	$cpt = isset( $_POST['cpt'] ) ? sanitize_key( $_POST['cpt'] ) : '';
	$id  = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$schema = reirie_content_schema();
	if ( ! isset( $schema[ $cpt ] ) ) wp_send_json_error( array( 'message' => '不明なCPT' ) );
	if ( ! empty( $schema[ $cpt ]['readonly'] ) && $id === 0 ) {
		wp_send_json_error( array( 'message' => 'このコンテンツは新規追加できません' ) );
	}

	$title      = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	// 注意：ここでは wp_kses_post をかけず、reirie_content_plain_to_html() に丸投げする。
	// （二重がけすると <p>&nbsp;</p> → <p>(空文字)</p> に潰れる可能性があるため）
	$content_raw = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';
	$content    = reirie_content_plain_to_html( $content_raw );
	$status     = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'publish';
	$menu_order = isset( $_POST['menu_order'] ) ? (int) $_POST['menu_order'] : 0;
	$thumb_id   = isset( $_POST['thumb_id'] ) ? absint( $_POST['thumb_id'] ) : 0;

	if ( ! in_array( $status, array( 'publish', 'future', 'draft', 'pending', 'private' ), true ) ) $status = 'publish';

	$postarr = array(
		'post_type'   => $cpt,
		'post_title'  => $title,
		'post_content'=> $content,
		'post_status' => $status,
		'menu_order'  => $menu_order,
	);

	// 投稿挿入時の wp_kses による空段落 <p><br /></p> 破壊を避けるため、
	// kses フィルタを一時的に外して保存する（管理者権限チェックは上で済んでいる）。
	$kses_removed = false;
	if ( function_exists( 'kses_remove_filters' ) ) {
		kses_remove_filters();
		$kses_removed = true;
	}

	if ( $id ) {
		$postarr['ID'] = $id;
		$post_id = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$post_id = wp_insert_post( wp_slash( $postarr ), true );
	}

	if ( $kses_removed && function_exists( 'kses_init_filters' ) ) {
		kses_init_filters();
	}

	if ( is_wp_error( $post_id ) ) wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );

	// アイキャッチ
	if ( $thumb_id ) {
		set_post_thumbnail( $post_id, $thumb_id );
	} else {
		delete_post_thumbnail( $post_id );
	}

	// カスタムフィールド
	$url_types = array( 'url' );
	$checkbox_types = array( 'checkbox' );
	// 「公開予定（future）」を指定したのに、公開日時が過去のため 'publish' に
	// 自動補正された場合に true になる（下の datetime 処理ループ内で判定）。
	$status_downgraded = false;
	foreach ( $schema[ $cpt ]['fields'] as $f ) {
		if ( $f['type'] === 'divider' ) continue;
		$name = $f['name'];
		$raw  = isset( $_POST['fields'][ $name ] ) ? wp_unslash( $_POST['fields'][ $name ] ) : '';
		if ( in_array( $f['type'], $url_types, true ) ) {
			$val = esc_url_raw( $raw );
		} elseif ( in_array( $f['type'], $checkbox_types, true ) ) {
			$val = ! empty( $raw ) ? '1' : '';
		} elseif ( $f['type'] === 'select' ) {
			$choices = isset( $f['choices'] ) ? $f['choices'] : array();
			$val = array_key_exists( $raw, $choices ) ? $raw : '';
		} elseif ( $f['type'] === 'textarea' ) {
			$val = wp_kses_post( $raw );
		} elseif ( $f['type'] === 'date' ) {
			// <input type="date"> は Y-m-d 形式で送信される
			// ACF date_picker は Ymd（8桁）形式で保存するので、ACF互換のため Ymd で保存する
			$val = sanitize_text_field( $raw );
			if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $val, $m ) ) {
				$val = $m[1] . $m[2] . $m[3]; // Ymd 形式に変換
			}
		} elseif ( $f['type'] === 'datetime' ) {
			// <input type="datetime-local"> は Y-m-d\TH:i 形式で送信される
			// MySQL datetime（Y-m-d H:i:s）形式に正規化して保存
			$val = sanitize_text_field( $raw );
			if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/', $val, $m ) ) {
				$sec = isset( $m[6] ) && $m[6] !== '' ? $m[6] : '00';
				$val = $m[1] . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4] . ':' . $m[5] . ':' . $sec;
			}
		} else {
			$val = sanitize_text_field( $raw );
		}
		if ( $val === '' || $val === '0' && $f['type'] !== 'checkbox' ) {
			// 空はメタを残さない（0は文字列として有効なので注意してkeep）
			if ( $val === '' ) {
				delete_post_meta( $post_id, $name );
			} else {
				update_post_meta( $post_id, $name, $val );
			}
		} else {
			update_post_meta( $post_id, $name, $val );
		}

		// datetime フィールドが入力されていれば、投稿の post_date と同期する
		// （並び順や archive 表示で「掲載日時」として一貫させるため）
		// 加えて、未来日時 + 公開希望（publish/future） なら post_status を 'future' に切替
		// （= 予約投稿。ログイン中の管理者/編集者にだけ「公開予定」として表示するロジックは
		// テンプレート側で実装する。）
		//
		// 注意：$val は <input type="datetime-local"> から来た「サイトの設定タイムゾーン
		// （設定 → 一般 → タイムゾーン、通常は東京/JST）における壁時計時刻」であり、
		// PHP サーバーのデフォルトタイムゾーン（多くのレンタルサーバーでは UTC）とは限らない。
		// これを strtotime() / gmdate() でそのまま処理すると、PHPのデフォルトタイムゾーンで
		// 解釈されてしまい、サーバーが UTC の場合は 9 時間ズレて保存される
		// （＝ 20:00 に予約しても実際は 20:00 UTC = 翌 5:00 JST まで公開されないバグ）。
		// そのため WordPress 標準の get_gmt_from_date() を使い、サイトのタイムゾーン設定を
		// 正しく考慮して GMT に変換する。
		if ( $f['type'] === 'datetime' && $val !== '' ) {
			// $val は既に 'Y-m-d H:i:s' 形式（サイトのローカル時刻）に正規化済み
			$post_date_local = $val;
			$post_date_gmt   = get_gmt_from_date( $val ); // サイトのタイムゾーン設定を考慮してGMTへ変換

			// 「未来日時か」の判定も GMT ベースの実時刻同士で比較する
			$ts_gmt = strtotime( $post_date_gmt . ' +0000' );

			if ( $ts_gmt ) {
				$update_arr = array(
					'ID'            => $post_id,
					'post_date'     => $post_date_local,
					'post_date_gmt' => $post_date_gmt,
					'edit_date'     => true,
				);

				// 未来日時のときは予約投稿（future）に切替（publish/future 指定時のみ）
				// draft/pending/private はそのままユーザーの意思を尊重する
				//
				// 重要：WordPress は「post_date が過去なのに post_status が future」という
				// 組み合わせを許さない（wp_insert_post 内部で自動的に publish に補正される）。
				// つまり管理者が「公開予定（予約投稿）」を選んでも、公開日時が過去のまま
				// （＝日時を新しい未来の値に変更し忘れた）だと、ここで自動的に 'publish' に
				// 戻ってしまう。これ自体は正しい仕様（過去の時刻には「予約」できないため）だが、
				// 以前は保存後のメッセージが常に「保存しました」で、この補正が起きたことを
				// 管理者に伝えていなかった。そのため「予約にしたのに反映されない」ように見える
				// バグ報告につながっていた。→ 補正が発生したかどうかを $status_downgraded に
				// 記録し、保存完了メッセージで明示的に警告する。
				// 注意：ここは「1分以上未来」のような猶予（バッファ）を設けてはいけない。
				// 例えば 05:28:00 に予約したいのに保存操作が 05:27:33（27秒前）に行われた
				// 場合、猶予を設けると「実質もう公開時刻だから」とみなして即座に 'publish' に
				// なってしまい、「05:28 に設定したのに 05:27 の時点でもう表示されてしまう」
				// という新たなバグを生む（実際に発生した）。現在時刻より1秒でも未来であれば
				// 予約投稿（future）として扱い、正確に指定時刻まで待たせる。
				$is_future = ( $ts_gmt > time() );
				if ( in_array( $status, array( 'publish', 'future' ), true ) ) {
					$update_arr['post_status'] = $is_future ? 'future' : 'publish';
					if ( $status === 'future' && ! $is_future ) {
						$status_downgraded = true;
					}
				}

				// kses を一時的に外して post_date / status を更新
				$kses_removed2 = false;
				if ( function_exists( 'kses_remove_filters' ) ) {
					kses_remove_filters();
					$kses_removed2 = true;
				}
				wp_update_post( wp_slash( $update_arr ) );
				if ( $kses_removed2 && function_exists( 'kses_init_filters' ) ) {
					kses_init_filters();
				}
			}
		}
	}

	// 保存後の最終ステータスを返却（UI 側で「予約投稿になりました」等を出せるように）
	$final_post = get_post( $post_id );
	$final_status = $final_post ? $final_post->post_status : $status;

	$message = '保存しました';
	if ( $final_status === 'future' ) {
		$message = '公開予定として保存しました（管理者のみフロントに表示されます）';
	} elseif ( $status_downgraded ) {
		// 「公開予定」を選んだのに公開日時が過去だったため、WordPress の仕様上
		// 自動的に「公開」へ戻された（過去の時刻には予約できないため）。
		// これを明示的にユーザーへ伝える（サイレントに見えるバグの主因だったため）。
		$message = '⚠ 公開日時が過去のため「公開」として保存しました。公開予定（予約投稿）にするには、公開日時を未来の日時に変更してください。';
	}

	wp_send_json_success( array(
		'id'                => $post_id,
		'status'            => $final_status,
		'status_downgraded' => $status_downgraded,
		'message'           => $message,
	) );
}
add_action( 'wp_ajax_reirie_content_save', 'reirie_ajax_save' );

/* ============================================================
   AJAX ハンドラ：削除（ゴミ箱へ）
   ============================================================ */
function reirie_ajax_delete() {
	check_ajax_referer( 'reirie_content_nonce', 'nonce' );
	if ( ! current_user_can( 'delete_posts' ) ) wp_send_json_error( array( 'message' => '権限がありません' ) );

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	if ( ! $id ) wp_send_json_error( array( 'message' => 'IDが不正です' ) );

	$result = wp_trash_post( $id );
	if ( ! $result ) wp_send_json_error( array( 'message' => '削除に失敗しました' ) );
	wp_send_json_success( array( 'message' => 'ゴミ箱に移動しました' ) );
}
add_action( 'wp_ajax_reirie_content_delete', 'reirie_ajax_delete' );

/* ============================================================
   AJAX ハンドラ：未公開の予約投稿を強制公開（Missed Schedule 修復）
   ============================================================ */
function reirie_ajax_publish_missed() {
	check_ajax_referer( 'reirie_content_nonce', 'nonce' );
	if ( ! current_user_can( 'publish_posts' ) ) wp_send_json_error( array( 'message' => '権限がありません' ) );

	global $wpdb;
	$now_gmt = gmdate( 'Y-m-d H:i:s' );

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT ID, post_title, post_date FROM {$wpdb->posts}
		 WHERE post_status = 'future'
		 AND post_date_gmt > '0000-00-00 00:00:00'
		 AND post_date_gmt <= %s
		 ORDER BY post_date_gmt ASC
		 LIMIT 50",
		$now_gmt
	) );

	$published = array();
	if ( $rows ) {
		foreach ( $rows as $r ) {
			$pid = (int) $r->ID;
			if ( $pid <= 0 ) continue;
			wp_publish_post( $pid );
			$published[] = array(
				'id'    => $pid,
				'title' => $r->post_title,
				'date'  => $r->post_date,
			);
		}
	}

	// 未来の予約投稿件数も返却（UI 表示用）
	$future_count = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		 WHERE post_status = 'future' AND post_date_gmt > %s",
		$now_gmt
	) );

	wp_send_json_success( array(
		'count'        => count( $published ),
		'published'    => $published,
		'future_count' => $future_count,
		'message'      => count( $published ) > 0
			? sprintf( '%d 件の予約投稿を公開しました', count( $published ) )
			: '公開時刻を過ぎた予約投稿はありません',
	) );
}
add_action( 'wp_ajax_reirie_publish_missed', 'reirie_ajax_publish_missed' );

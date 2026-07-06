<?php
/**
 * ACF が未インストールの場合に最低限の編集UIを提供するフォールバック
 * （Custom Fields のメタボックスを使ってシンプルに入力できるようにする）
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ACF が有効ならフォールバックは動かさない
 */
function reirie_acf_active() {
	return class_exists( 'ACF' ) || function_exists( 'acf_add_local_field_group' );
}

/* ============================================================
   メタボックスの登録（ACFが無い場合のみ）
   ============================================================ */
function reirie_add_meta_boxes() {
	if ( reirie_acf_active() ) return;

	add_meta_box( 'reirie_news_meta', '📢 お知らせ詳細', 'reirie_news_meta_box', 'news', 'normal', 'high' );
	add_meta_box( 'reirie_schedule_meta', '📅 スケジュール詳細', 'reirie_schedule_meta_box', 'schedule', 'normal', 'high' );
	add_meta_box( 'reirie_disco_meta', '💿 作品詳細', 'reirie_disco_meta_box', 'discography', 'normal', 'high' );
	add_meta_box( 'reirie_disco_links', '🔗 購入・配信リンク', 'reirie_disco_links_box', 'discography', 'normal', 'default' );
	add_meta_box( 'reirie_movie_meta', '🎬 動画詳細', 'reirie_movie_meta_box', 'movie', 'normal', 'high' );
	add_meta_box( 'reirie_member_basic', '👯 メンバー基本情報', 'reirie_member_basic_box', 'member', 'normal', 'high' );
	add_meta_box( 'reirie_member_profile', '🌸 プロフィール詳細', 'reirie_member_profile_box', 'member', 'normal', 'default' );
	add_meta_box( 'reirie_member_sns', '📱 メンバーSNS', 'reirie_member_sns_box', 'member', 'normal', 'default' );
	add_meta_box( 'reirie_goods_meta', '🛍️ グッズ詳細', 'reirie_goods_meta_box', 'goods', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'reirie_add_meta_boxes' );

/* ============================================================
   各メタボックスの中身
   ============================================================ */
function reirie_field_input( $name, $label, $type = 'text', $help = '', $options = array() ) {
	global $post;
	$value = get_post_meta( $post->ID, $name, true );
	echo '<p style="margin:14px 0;">';
	echo '<label style="display:block;font-weight:600;margin-bottom:6px;color:#c43a73;">' . esc_html( $label ) . '</label>';
	if ( $type === 'textarea' ) {
		$rows = isset( $options['rows'] ) ? (int) $options['rows'] : 3;
		echo '<textarea name="' . esc_attr( $name ) . '" rows="' . esc_attr( $rows ) . '" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
	} elseif ( $type === 'select' ) {
		echo '<select name="' . esc_attr( $name ) . '" style="width:100%;">';
		foreach ( $options as $opt_val => $opt_label ) {
			$sel = ( $value === $opt_val ) ? ' selected' : '';
			echo '<option value="' . esc_attr( $opt_val ) . '"' . $sel . '>' . esc_html( $opt_label ) . '</option>';
		}
		echo '</select>';
	} else {
		$placeholder = isset( $options['placeholder'] ) ? ' placeholder="' . esc_attr( $options['placeholder'] ) . '"' : '';
		echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $placeholder . ' style="width:100%;">';
	}
	if ( $help ) echo '<small style="color:#888;display:block;margin-top:4px;">' . esc_html( $help ) . '</small>';
	echo '</p>';
}

function reirie_field_checkbox( $name, $label, $help = '' ) {
	global $post;
	$value = get_post_meta( $post->ID, $name, true );
	echo '<p style="margin:14px 0;"><label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( $value, '1', false ) . '> ' . esc_html( $label ) . '</label>';
	if ( $help ) echo '<br><small style="color:#888;margin-left:24px;">' . esc_html( $help ) . '</small>';
	echo '</p>';
}

function reirie_news_meta_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );
	reirie_field_input( 'news_date', '掲載日', 'date', '例: 2026-05-08（未入力の場合は投稿日が使われます）' );
	reirie_field_input( 'news_link', '外部リンクURL（任意）', 'url', 'クリック時に外部サイトを開きたい場合のみ入力' );
}

function reirie_schedule_meta_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );
	reirie_field_input( 'schedule_date', '開催日 *必須', 'date', 'カレンダーに表示される日付' );
	reirie_field_input( 'schedule_category', 'カテゴリー', 'select', 'スケジュールカード・個別ページのバッジ種別', array(
		''      => '未選択（バッジを表示しない）',
		'LIVE'  => 'LIVE（ライブ）',
		'EVENT' => 'EVENT（イベント）',
		'OTHER' => 'OTHER（その他）',
	) );
	reirie_field_input( 'schedule_venue', '会場', 'text', '例: 渋谷 TSUTAYA O-WEST', array( 'placeholder' => '渋谷 TSUTAYA O-WEST' ) );
	reirie_field_input( 'schedule_time', '開場/開演', 'text', '例: OPEN 18:00 / START 19:00' );
	reirie_field_input( 'schedule_link', '詳細ページURL', 'url', 'チケット販売サイトなど' );
	reirie_field_checkbox( 'schedule_highlight', '✨ ハイライト表示（ワンマンライブ等を目立たせる）' );
}

function reirie_disco_meta_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );
	reirie_field_input( 'disco_category', 'カテゴリー', 'text', '例: 2nd Single / Debut Mini Album', array( 'placeholder' => '2nd Single' ) );
	reirie_field_input( 'disco_release_date', 'リリース日', 'date' );
	reirie_field_input( 'disco_price', '価格（任意）', 'text', '例: ¥1,500（税込）' );
	reirie_field_input( 'disco_tracks', 'トラックリスト', 'textarea', '1行に1曲（例: 1. Twinkle Heart）', array( 'rows' => 6 ) );
	reirie_field_checkbox( 'disco_is_new', '🌟 NEWバッジを表示（最新作にチェック）' );
}

/**
 * 購入・配信リンク（NEW）
 */
function reirie_disco_links_box() {
	echo '<p style="background:#fff5f9;padding:10px 14px;border-radius:6px;margin:0 0 16px;color:#666;">入力したリンクのみカード下部にボタンとして表示されます。</p>';

	reirie_field_input( 'disco_buy_url', '🛒 購入ページURL', 'url', 'CDショップ・公式ストアなど' );
	reirie_field_input( 'disco_apple_url', '🍎 Apple Music', 'url' );
	reirie_field_input( 'disco_spotify_url', '🟢 Spotify', 'url' );
	reirie_field_input( 'disco_youtube_url', '▶ YouTube Music', 'url' );
	reirie_field_input( 'disco_linkco_url', '🔗 まとめリンク（linkco / Linkfire等）', 'url', 'すべての配信先をまとめるサービスのURL' );
}

function reirie_movie_meta_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );
	reirie_field_input( 'movie_url', '動画URL（YouTube等）', 'url', '例: https://www.youtube.com/watch?v=xxxx' );
	reirie_field_input( 'movie_date', '公開日', 'date' );
	reirie_field_input( 'movie_label', 'ラベル', 'text', '例: MV / Lyric Video / Behind the Scenes' );
}

/**
 * メンバー：基本情報（NEW・整理）
 */
function reirie_member_basic_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );

	reirie_field_input( 'member_name_jp', '名前（カナ）', 'text', '例: レイ', array( 'placeholder' => 'レイ' ) );

	reirie_field_input( 'member_color', 'メンバーカラー名', 'text', '例: PINK / SKY BLUE / LAVENDER', array( 'placeholder' => 'PINK' ) );

	reirie_field_input( 'member_color_class', 'カラークラス', 'select', 'プロフィール文字色のテーマ', array(
		'color-pink'   => 'ピンク (color-pink)',
		'color-blue'   => 'スカイブルー (color-blue)',
		'color-purple' => 'パープル (color-purple)',
		'color-yellow' => 'イエロー (color-yellow)',
		'color-green'  => 'グリーン (color-green)',
	) );

	reirie_field_input( 'member_photo_class', 'フォト背景クラス', 'select', '写真未設定時のグラデーション背景', array(
		'photo-rei'    => 'Rei スタイル（ピンク系）',
		'photo-rie'    => 'Rie スタイル（ブルー系）',
		'photo-purple' => 'パープル系',
	) );

	reirie_field_input( 'member_initial', 'イニシャル文字', 'text', '写真未設定時に表示される一文字（例: R）' );

	reirie_field_input( 'member_catch', 'キャッチフレーズ（任意）', 'text', '例: 太陽みたいな笑顔担当' );

	echo '<p style="background:#fff5f9;padding:10px 14px;border-radius:6px;margin:14px 0 0;color:#666;font-size:13px;">';
	echo '💡 <strong>並び順：</strong>右サイドバーの「ページ属性 → 順序」の数字で表示順を調整できます（小さい数字が左）。<br>';
	echo '📷 <strong>写真：</strong>右サイドバーの「アイキャッチ画像」から設定（推奨：縦長 4:5 ／ 800×1000）';
	echo '</p>';
}

/**
 * メンバー：プロフィール詳細
 */
function reirie_member_profile_box() {
	reirie_field_input( 'member_birthday', '誕生日', 'text', '例: 9月12日 / 2003.09.12', array( 'placeholder' => '9月12日' ) );
	reirie_field_input( 'member_blood', '血液型', 'text', '例: A型', array( 'placeholder' => 'A型' ) );
	reirie_field_input( 'member_hometown', '出身地', 'text', '例: 東京都', array( 'placeholder' => '東京都' ) );
	reirie_field_input( 'member_height', '身長（任意）', 'text', '例: 158cm' );
	reirie_field_input( 'member_hobby', '趣味', 'text', '例: カフェ巡り / 写真' );
	reirie_field_input( 'member_charm', 'チャームポイント', 'text', '例: くるんとした目' );
	reirie_field_input( 'member_skill', 'スペシャルスキル / 特技（任意）', 'text', '例: クラシックバレエ' );
	reirie_field_input( 'member_mbti', 'MBTI（任意）', 'text', '例: ESFP / INFJ など', array( 'placeholder' => 'ESFP' ) );
	reirie_field_input( 'member_message', 'ファンへのメッセージ', 'textarea', '吹き出しに表示されます（120文字程度推奨）', array( 'rows' => 4 ) );
}

/**
 * メンバー：SNS（NEW）
 */
function reirie_member_sns_box() {
	echo '<p style="background:#fff5f9;padding:10px 14px;border-radius:6px;margin:0 0 16px;color:#666;">入力したSNSのみアイコンとして表示されます。</p>';
	reirie_field_input( 'member_sns_twitter', 'X（Twitter）URL', 'url' );
	reirie_field_input( 'member_sns_instagram', 'Instagram URL', 'url' );
	reirie_field_input( 'member_sns_tiktok', 'TikTok URL', 'url' );
	reirie_field_input( 'member_sns_youtube', 'YouTube URL', 'url' );
	reirie_field_input( 'member_sns_blog', 'ブログ / 個人サイト URL', 'url' );

	echo '<hr style="margin:24px 0;border:none;border-top:1px dashed rgba(255,126,182,.4);">';
	echo '<h4 style="margin:0 0 6px;color:#c43a73;">🎵 TikTok埋め込み動画（個別ページに表示）</h4>';
	echo '<p style="background:#fff5f9;padding:10px 14px;border-radius:6px;margin:0 0 16px;color:#666;font-size:13px;">TikTok動画のURLを貼り付けると、メンバー個別ページに埋め込み再生プレーヤーとして表示されます。<br>例: <code>https://www.tiktok.com/@user/video/1234567890</code></p>';
	reirie_field_input( 'member_tiktok_video_1', 'TikTok動画 ①', 'url', 'TikTokの動画ページURL' );
	reirie_field_input( 'member_tiktok_video_2', 'TikTok動画 ②', 'url', 'TikTokの動画ページURL' );
	reirie_field_input( 'member_tiktok_video_3', 'TikTok動画 ③', 'url', 'TikTokの動画ページURL' );
}

function reirie_goods_meta_box() {
	wp_nonce_field( 'reirie_meta', 'reirie_meta_nonce' );
	reirie_field_input( 'goods_price', '価格表示', 'text', '例: ¥3,800（税込）', array( 'placeholder' => '¥3,800' ) );
	reirie_field_input( 'goods_link', '商品ページURL', 'url', '公式ストア等のリンク' );
	reirie_field_input( 'goods_status', 'ステータス（任意）', 'select', '在庫状況などの表示', array(
		''           => '（表示しない）',
		'NEW'        => 'NEW（新商品）',
		'SOLD OUT'   => 'SOLD OUT（完売）',
		'COMING'     => 'COMING SOON（販売予定）',
		'LIMITED'    => 'LIMITED（数量限定）',
	) );
}

/* ============================================================
   保存処理
   ============================================================ */
function reirie_save_meta( $post_id ) {
	if ( ! isset( $_POST['reirie_meta_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['reirie_meta_nonce'], 'reirie_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = array(
		// News
		'news_date', 'news_link',
		// Schedule
		'schedule_date', 'schedule_category', 'schedule_venue', 'schedule_time', 'schedule_link', 'schedule_highlight',
		// Discography
		'disco_category', 'disco_release_date', 'disco_price', 'disco_tracks', 'disco_is_new',
		'disco_buy_url', 'disco_apple_url', 'disco_spotify_url', 'disco_youtube_url', 'disco_linkco_url',
		// Movie
		'movie_url', 'movie_date', 'movie_label',
		// Member
		'member_color', 'member_color2', 'member_color_hex', 'member_color2_hex',
		'member_color_class', 'member_photo_class', 'member_initial',
		'member_name_jp', 'member_catch', 'member_birthday', 'member_blood', 'member_hometown',
		'member_height', 'member_hobby', 'member_charm', 'member_skill', 'member_mbti', 'member_message',
		'member_sns_twitter', 'member_sns_instagram', 'member_sns_tiktok', 'member_sns_youtube', 'member_sns_blog',
		'member_tiktok_video_1', 'member_tiktok_video_2', 'member_tiktok_video_3',
		// Goods
		'goods_price', 'goods_link', 'goods_status',
	);

	$url_fields = array(
		'news_link', 'schedule_link', 'movie_url',
		'disco_buy_url', 'disco_apple_url', 'disco_spotify_url', 'disco_youtube_url', 'disco_linkco_url',
		'member_sns_twitter', 'member_sns_instagram', 'member_sns_tiktok', 'member_sns_youtube', 'member_sns_blog',
		'member_tiktok_video_1', 'member_tiktok_video_2', 'member_tiktok_video_3',
		'goods_link',
	);

	$checkbox_fields = array( 'schedule_highlight', 'disco_is_new' );

	foreach ( $fields as $f ) {
		if ( isset( $_POST[ $f ] ) ) {
			$val = $_POST[ $f ];
			if ( in_array( $f, $url_fields, true ) ) {
				$val = esc_url_raw( $val );
			} else {
				$val = wp_kses_post( $val );
			}
			update_post_meta( $post_id, $f, $val );
		} else {
			if ( in_array( $f, $checkbox_fields, true ) ) {
				delete_post_meta( $post_id, $f );
			}
		}
	}
}
add_action( 'save_post', 'reirie_save_meta' );

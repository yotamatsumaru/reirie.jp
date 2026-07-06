<?php
/**
 * テンプレート用ヘルパー関数
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * フロント表示クエリで使う post_status 配列を返す
 *
 * - 一般訪問者：'publish' のみ
 * - ログイン中の管理者・編集者：'publish' + 'future'（= 公開予定）も含める
 *   → 未来日時の予約投稿を実機プレビュー可能にする
 *
 * @return array
 */
function reirie_front_post_status() {
	$statuses = array( 'publish' );
	if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_posts' ) ) {
		$statuses[] = 'future';
	}
	return $statuses;
}

/**
 * 投稿が「公開予定（future）」かどうかを返す
 *
 * @param int|null $post_id
 * @return bool
 */
function reirie_is_scheduled( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();
	return ( get_post_status( $post_id ) === 'future' );
}

/**
 * メインクエリで news / schedule / discography / movie アーカイブを表示する際、
 * ログイン中の管理者・編集者には 'future'（公開予定）も含めて表示する。
 * 一般訪問者は 'publish' のみで標準動作。
 */
function reirie_include_future_for_admins( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'edit_posts' ) ) return;

	// アーカイブ / 単体ページの両方で適用
	$cpts = array( 'news', 'schedule', 'discography', 'movie', 'goods' );
	$applies = false;
	foreach ( $cpts as $cpt ) {
		if ( $query->is_post_type_archive( $cpt ) || $query->is_singular( $cpt ) ) {
			$applies = true;
			break;
		}
	}
	if ( ! $applies ) return;

	$query->set( 'post_status', array( 'publish', 'future' ) );
}
add_action( 'pre_get_posts', 'reirie_include_future_for_admins' );

/**
 * 'future'（公開予定）投稿を単体ページで表示するため、
 * WordPress のデフォルトで 404 になる挙動を回避する。
 * - 管理者ログイン時：future の single ページに 200 でアクセス可能
 * - 一般訪問者：標準どおり 404
 */
function reirie_allow_future_single_preview( $posts, $query ) {
	if ( is_admin() || empty( $posts ) ) return $posts;
	if ( ! $query->is_main_query() || ! $query->is_singular() ) return $posts;
	if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'edit_posts' ) ) return $posts;

	// future 投稿のリンクを 200 で見せる
	// （pre_get_posts で post_status に future を含めた上で、念のため posts_results で漏れを補う）
	return $posts;
}
add_filter( 'posts_results', 'reirie_allow_future_single_preview', 10, 2 );

/**
 * ============================================================
 * Missed Schedule（予約投稿が時刻を過ぎても公開されない問題）対策
 * ============================================================
 *
 * WordPress 標準の wp-cron はサイトへの訪問者アクセス時にしか発火しないため、
 * 深夜帯など人が来ない時間帯に予約公開時刻が来ても自動公開されないことがある。
 * → ページアクセスのたびに「公開時刻を過ぎた future 投稿」を検知して即時公開する。
 *
 * - 1リクエストあたり最大10件まで処理（パフォーマンス保護）
 * - 5分に1回までしか実行しない（DBクエリ削減）
 * - 全 CPT 対象（news / schedule / discography / movie / goods）
 */
function reirie_publish_missed_schedules() {
	// 管理画面のAJAXや cron 自体ではスキップ
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;
	if ( wp_doing_ajax() ) return;

	// 5分に1回までしか実行しない
	$last = (int) get_transient( 'reirie_missed_check' );
	if ( $last && ( time() - $last ) < 300 ) return;
	set_transient( 'reirie_missed_check', time(), 300 );

	global $wpdb;
	$now_gmt = gmdate( 'Y-m-d H:i:s' );

	// 公開時刻を過ぎているのに future のままの投稿を取得
	$missed_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_status = 'future'
		 AND post_date_gmt > '0000-00-00 00:00:00'
		 AND post_date_gmt <= %s
		 LIMIT 10",
		$now_gmt
	) );

	if ( empty( $missed_ids ) ) return;

	foreach ( $missed_ids as $missed_id ) {
		$missed_id = (int) $missed_id;
		if ( $missed_id <= 0 ) continue;
		// WordPress 標準の公開関数を使う（future_to_publish フックも正しく発火する）
		wp_publish_post( $missed_id );
	}
}
add_action( 'init', 'reirie_publish_missed_schedules', 99 );

/**
 * WP-Cron の生存状態を確認するヘルパー
 *
 * @return array {
 *   @type bool   $disabled    DISABLE_WP_CRON が true なら true
 *   @type int    $missed      公開時刻を過ぎた future 投稿の件数
 *   @type array  $missed_list 公開時刻を過ぎた投稿のID/タイトル/予定日時の配列
 *   @type int    $next_run    次の wp-cron 実行予定時刻（UNIXタイムスタンプ）
 * }
 */
function reirie_cron_health_check() {
	global $wpdb;
	$now_gmt = gmdate( 'Y-m-d H:i:s' );

	$missed_rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT ID, post_title, post_date FROM {$wpdb->posts}
		 WHERE post_status = 'future'
		 AND post_date_gmt > '0000-00-00 00:00:00'
		 AND post_date_gmt <= %s
		 ORDER BY post_date_gmt ASC
		 LIMIT 50",
		$now_gmt
	) );

	$missed_list = array();
	if ( $missed_rows ) {
		foreach ( $missed_rows as $r ) {
			$missed_list[] = array(
				'id'    => (int) $r->ID,
				'title' => $r->post_title,
				'date'  => $r->post_date,
			);
		}
	}

	return array(
		'disabled'    => ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
		'missed'      => count( $missed_list ),
		'missed_list' => $missed_list,
		'next_run'    => (int) wp_next_scheduled( 'wp_version_check' ),
	);
}

/**
 * SNS リンクを取得
 */
function reirie_get_sns_links() {
	return array(
		'twitter'   => array(
			'url'   => get_theme_mod( 'reirie_sns_twitter', '#' ),
			'label' => 'X (Twitter)',
			'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		),
		'instagram' => array(
			'url'   => get_theme_mod( 'reirie_sns_instagram', '#' ),
			'label' => 'Instagram',
			'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 2.2c3.2 0 3.6 0 4.8.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.9.9 1.4.2.4.4 1.1.4 2.2.1 1.2.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.9.7-1.4.9-.4.2-1.1.4-2.2.4-1.2.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.9-.9-1.4-.2-.4-.4-1.1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.9-.7 1.4-.9.4-.2 1.1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 1.8c-3.1 0-3.5 0-4.7.1-1.1.1-1.7.2-2.1.4-.5.2-.9.4-1.3.8-.4.4-.6.8-.8 1.3-.2.4-.3 1-.4 2.1-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c.1 1.1.2 1.7.4 2.1.2.5.4.9.8 1.3.4.4.8.6 1.3.8.4.2 1 .3 2.1.4 1.2.1 1.6.1 4.7.1s3.5 0 4.7-.1c1.1-.1 1.7-.2 2.1-.4.5-.2.9-.4 1.3-.8.4-.4.6-.8.8-1.3.2-.4.3-1 .4-2.1.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c-.1-1.1-.2-1.7-.4-2.1-.2-.5-.4-.9-.8-1.3-.4-.4-.8-.6-1.3-.8-.4-.2-1-.3-2.1-.4C15.5 4 15.1 4 12 4zm0 3.1c2.7 0 4.9 2.2 4.9 4.9s-2.2 4.9-4.9 4.9-4.9-2.2-4.9-4.9 2.2-4.9 4.9-4.9zm0 8.1c1.8 0 3.2-1.4 3.2-3.2S13.8 8.8 12 8.8 8.8 10.2 8.8 12s1.4 3.2 3.2 3.2zm6.3-8.3c0 .6-.5 1.2-1.2 1.2s-1.2-.5-1.2-1.2.5-1.2 1.2-1.2 1.2.5 1.2 1.2z"/></svg>',
		),
		'tiktok'    => array(
			'url'   => get_theme_mod( 'reirie_sns_tiktok', '#' ),
			'label' => 'TikTok',
			'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V8.7a8.16 8.16 0 0 0 4.77 1.52V6.83a4.85 4.85 0 0 1-1.84-.14z"/></svg>',
		),
		'youtube'   => array(
			'url'   => get_theme_mod( 'reirie_sns_youtube', '#' ),
			'label' => 'YouTube',
			'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M23.5 6.2c-.3-1-1-1.8-2-2C19.7 3.7 12 3.7 12 3.7s-7.7 0-9.5.5c-1 .3-1.8 1-2 2C0 8 0 12 0 12s0 4 .5 5.8c.3 1 1 1.8 2 2 1.8.5 9.5.5 9.5.5s7.7 0 9.5-.5c1-.3 1.8-1 2-2 .5-1.8.5-5.8.5-5.8s0-4-.5-5.8zM9.6 15.6V8.4l6.4 3.6-6.4 3.6z"/></svg>',
		),
	);
}

/**
 * ACF get_field のラッパー（ACF未導入時は空文字を返す）
 */
function reirie_field( $name, $post_id = false, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, $post_id );
		return ( $value === false || $value === null || $value === '' ) ? $default : $value;
	}
	// フォールバック：post_meta から取得
	if ( ! $post_id ) $post_id = get_the_ID();
	$value = get_post_meta( $post_id, $name, true );
	return ( $value === '' ) ? $default : $value;
}

/**
 * 日付フォーマット（Y.m.d）
 * ACF date_picker (Ymd / 8桁) 形式 / Y-m-d 形式 / Y-m-d H:i:s（MySQL datetime）/ Y-m-d\TH:i 形式に対応
 */
function reirie_format_date( $date_str, $format = 'Y.m.d' ) {
	if ( ! $date_str ) return '';
	// ACF date_picker の Ymd 形式（例：20260615）を Y-m-d に変換
	if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $date_str, $m ) ) {
		$date_str = $m[1] . '-' . $m[2] . '-' . $m[3];
	}
	$ts = strtotime( $date_str );
	if ( ! $ts ) return $date_str;
	return date_i18n( $format, $ts );
}

/**
 * 日時フォーマット（Y.m.d H:i）
 * datetime メタ値（MySQL datetime / Y-m-d\TH:i / Ymd 等）を表示用に整形
 * - 時刻が 00:00 の場合は date 部分のみ表示（既存挙動との互換）
 *
 * @param string $date_str 日時文字列
 * @param string $date_format    日付部分のフォーマット
 * @param string $datetime_format 日付+時刻のフォーマット
 * @return string
 */
function reirie_format_datetime( $date_str, $date_format = 'Y.m.d', $datetime_format = 'Y.m.d H:i' ) {
	if ( ! $date_str ) return '';
	// 8桁 Ymd → Y-m-d
	if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $date_str, $m ) ) {
		$date_str = $m[1] . '-' . $m[2] . '-' . $m[3];
	}
	$ts = strtotime( $date_str );
	if ( ! $ts ) return $date_str;
	// 時刻情報が含まれているか判定（T または スペース + 時:分 を検出）
	$has_time = (bool) preg_match( '/[T ]\d{2}:\d{2}/', $date_str );
	if ( $has_time ) {
		// 00:00 のときは時刻表示を省略
		$h = (int) date( 'G', $ts );
		$i = (int) date( 'i', $ts );
		if ( $h === 0 && $i === 0 ) {
			return date_i18n( $date_format, $ts );
		}
		return date_i18n( $datetime_format, $ts );
	}
	return date_i18n( $date_format, $ts );
}

/**
 * News のカテゴリーラベル（最初の1個）を取得
 */
function reirie_get_news_category( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();
	$terms = get_the_terms( $post_id, 'news_category' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return strtoupper( $terms[0]->name );
	}
	return 'INFO';
}

/**
 * Schedule のカテゴリーラベル
 *
 * 優先順位:
 *   1. meta フィールド schedule_category（管理パネルの select で設定）
 *      - 値: '' / 'LIVE' / 'EVENT' / 'OTHER'
 *      - 空文字（未選択）の場合は '' を返す → バッジ非表示
 *   2. taxonomy schedule_category（旧データ互換）
 *   3. どちらもなければ空文字（バッジ非表示）
 */
function reirie_get_schedule_category( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();

	// meta キーが「保存されているか」を判定する
	// （保存されていれば未選択＝空文字でもユーザーの明示的な意思）
	$has_meta = metadata_exists( 'post', $post_id, 'schedule_category' );

	if ( $has_meta ) {
		// 1) meta が保存されていればそれを最優先
		//    空文字なら「未選択」→ '' を返してバッジ非表示
		$meta = get_post_meta( $post_id, 'schedule_category', true );
		return strtoupper( trim( (string) $meta ) );
	}

	// 2) meta が一度も保存されていない（既存データ）→ taxonomy フォールバック
	$terms = get_the_terms( $post_id, 'schedule_category' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		return strtoupper( $terms[0]->name );
	}

	// 3) どちらも無ければ空（バッジ非表示）
	return '';
}

/**
 * グラデーションクラスを順送りで返す（ジャケット/動画サムネ用）
 */
function reirie_color_class( $index, $type = 'jacket' ) {
	$max = ( $type === 'thumb' ) ? 6 : 3;
	$num = ( $index % $max ) + 1;
	return $type . '-' . $num;
}

/**
 * 旧 color-class（color-pink / color-blue / 等）を Hex に変換
 */
function reirie_color_class_to_hex( $cls ) {
	$map = array(
		'color-pink'   => '#ff5b9c',
		'color-blue'   => '#5fb6ff',
		'color-purple' => '#b07aff',
		'color-yellow' => '#ffd96a',
		'color-green'  => '#7ad48a',
	);
	return isset( $map[ $cls ] ) ? $map[ $cls ] : '#ff5b9c';
}

/**
 * メンバーカラーチップのマークアップを返す（2色対応）
 *
 * @param int|null $post_id  メンバーポストID（省略時は現在の投稿）
 * @return string            HTML（カラーが1つもなければ空文字）
 *
 * 仕様:
 *   - メインカラー: member_color (名前) + member_color_hex (色)
 *   - サブカラー  : member_color2 (名前) + member_color2_hex (色)
 *   - hex 未設定なら旧 member_color_class からマッピング
 *   - 名前未設定でも色があれば「●」だけ表示はしない（名前必須）
 */
function reirie_member_color_chips_html( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();

	$name1 = trim( (string) reirie_field( 'member_color',  $post_id, '' ) );
	$hex1  = trim( (string) reirie_field( 'member_color_hex', $post_id, '' ) );
	$name2 = trim( (string) reirie_field( 'member_color2', $post_id, '' ) );
	$hex2  = trim( (string) reirie_field( 'member_color2_hex', $post_id, '' ) );

	// メインの hex が空なら旧 color_class からフォールバック
	if ( $hex1 === '' ) {
		$cls = (string) reirie_field( 'member_color_class', $post_id, 'color-pink' );
		$hex1 = reirie_color_class_to_hex( $cls );
	}
	// hex の安全チェック
	$is_valid_hex = function( $h ) {
		return (bool) preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $h );
	};
	if ( ! $is_valid_hex( $hex1 ) ) $hex1 = '#ff5b9c';
	if ( ! $is_valid_hex( $hex2 ) ) $hex2 = '';

	$chips = array();
	if ( $name1 !== '' ) {
		$chips[] = sprintf(
			'<span class="member-color-chip"><span class="member-color-chip__dot" style="background:%1$s" aria-hidden="true"></span><span class="member-color-chip__name">%2$s</span></span>',
			esc_attr( $hex1 ),
			esc_html( $name1 )
		);
	}
	if ( $name2 !== '' && $hex2 !== '' ) {
		$chips[] = sprintf(
			'<span class="member-color-chip"><span class="member-color-chip__dot" style="background:%1$s" aria-hidden="true"></span><span class="member-color-chip__name">%2$s</span></span>',
			esc_attr( $hex2 ),
			esc_html( $name2 )
		);
	} elseif ( $name2 !== '' ) {
		// 名前あるが色なし → デフォルトカラーで表示
		$chips[] = sprintf(
			'<span class="member-color-chip"><span class="member-color-chip__dot" aria-hidden="true"></span><span class="member-color-chip__name">%s</span></span>',
			esc_html( $name2 )
		);
	}

	if ( empty( $chips ) ) return '';
	return '<span class="member-color-chips">' . implode( '', $chips ) . '</span>';
}

/* ============================================================
   SEO ヘルパー（noindex / canonical 出力）
   ============================================================ */

/**
 * 現在のリクエストにフィルタパラメータが付いているかを判定
 * ?year= ?month= ?cat= ?view=past ?disco_* ?paged= ?orderby= 等
 * → noindex + canonical で親アーカイブに集約すべきページ
 *
 * @return bool
 */
function reirie_is_filtered_archive() {
	$filter_keys = array( 'year', 'month', 'cat', 'view', 'paged', 'orderby', 'disco_cat', 'disco_year' );
	foreach ( $filter_keys as $k ) {
		if ( isset( $_GET[ $k ] ) && $_GET[ $k ] !== '' && $_GET[ $k ] !== '0' ) {
			// view=upcoming はデフォルト値扱い → 例外
			if ( $k === 'view' && $_GET[ $k ] === 'upcoming' ) continue;
			return true;
		}
	}
	return false;
}

/**
 * フィルタ付きアーカイブの canonical URL（親アーカイブURL）を返す
 *
 * @param string $post_type CPT slug (schedule / discography / news / movie / goods 等)
 * @return string
 */
function reirie_canonical_archive_url( $post_type ) {
	$url = get_post_type_archive_link( $post_type );
	return $url ? $url : home_url( '/' );
}

/**
 * <head> に noindex / canonical タグを出力
 * archive-*.php テンプレートの上部で wp_head() より前に呼ばれる想定 → wp_head アクションに登録
 */
function reirie_seo_meta_output() {
	// フィルタパラメータ付きURL（クエリ文字列が空でない）→ 無条件に noindex + canonical
	// これは is_post_type_archive() 等の判定に依存せず動作する
	$has_filter_query = false;
	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		parse_str( $_SERVER['QUERY_STRING'], $qs );
		$filter_keys = array( 'year', 'month', 'cat', 'view', 'paged', 'orderby', 'disco_cat', 'disco_year', 's', 'cal_y', 'cal_m', 'cal_date' );
		foreach ( $filter_keys as $k ) {
			if ( isset( $qs[ $k ] ) && $qs[ $k ] !== '' && $qs[ $k ] !== '0' ) {
				if ( $k === 'view' && $qs[ $k ] === 'upcoming' ) continue;
				$has_filter_query = true;
				break;
			}
		}
	}

	if ( $has_filter_query ) {
		// canonical URLは現在のパス（クエリ除去）
		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
		$canonical = home_url( $path );
		echo '<meta name="robots" content="noindex, follow">' . "\n";
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		return;
	}

	// 404 は noindex（Google のインデックス誤登録を防ぐ）
	if ( is_404() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
		return;
	}

	// サイト内検索結果は noindex
	if ( is_search() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
		return;
	}

	// 通常ページ（single / archive top / front）に canonical
	if ( is_singular() ) {
		$url = get_permalink();
		if ( $url ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
		}
	} elseif ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) $post_type = $post_type[0];
		if ( $post_type ) {
			$url = get_post_type_archive_link( $post_type );
			if ( $url ) {
				echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
			}
		}
	} elseif ( is_front_page() || is_home() ) {
		echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
	}
}
// WordPressコアの rel_canonical と競合しないよう、コアを外してから出力
// priority 2 は wp_head 標準アクションの中で早めだが、WP_Query 判定関数が使えるタイミング
function reirie_seo_meta_boot() {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'wp', 'reirie_seo_meta_boot' );
add_action( 'wp_head', 'reirie_seo_meta_output', 2 );

/* ============================================================
   ?year= ?month= が WP_Query の post_date 予約変数と衝突して 404 を返す問題への対処
   discography / schedule のアーカイブでは、これらの query_var を解除し、
   テンプレート側の $_GET から独自に meta_query として処理させる
   ============================================================ */
function reirie_neutralize_reserved_query_vars( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( ! $query->is_post_type_archive() ) return;
	$pt = $query->get( 'post_type' );
	if ( is_array( $pt ) ) $pt = $pt[0];
	if ( ! in_array( $pt, array( 'discography', 'schedule' ), true ) ) return;

	// year / monthnum / m は $_GET を残しつつ WP_Query のクエリ変数からは解除
	// （テンプレート側で $_GET['year'] を独自に meta_query に流し込むため）
	$query->set( 'year', '' );
	$query->set( 'monthnum', '' );
	$query->set( 'm', '' );
	$query->set( 'day', '' );
}
add_action( 'pre_get_posts', 'reirie_neutralize_reserved_query_vars' );

/**
 * CPT アーカイブでの canonical redirect を抑止
 *
 * WordPress は ?year=YYYY を「年別アーカイブ」と解釈し、/YYYY/ に 301 リダイレクトしてしまう。
 * これにより /discography/?year=2023 → /2026/ (最新年) のような予期しないリダイレクトが発生。
 * → CPT archive では canonical redirect をスキップ
 */
function reirie_disable_canonical_redirect_on_cpt_archive( $redirect_url, $requested_url ) {
	// リクエストされたURLがCPT archiveパスかを判定
	$requested_path = wp_parse_url( $requested_url, PHP_URL_PATH );
	if ( ! $requested_path ) return $redirect_url;

	$first_seg = trim( $requested_path, '/' );
	$first_seg = strtok( $first_seg, '/' );

	$cpt_archives = array( 'discography', 'schedule', 'news', 'movie', 'goods' );
	if ( in_array( $first_seg, $cpt_archives, true ) ) {
		// year / month / m クエリが付いていたら、リダイレクトを完全に停止
		if ( isset( $_GET['year'] ) || isset( $_GET['month'] ) || isset( $_GET['m'] ) || isset( $_GET['monthnum'] ) ) {
			return false;
		}
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'reirie_disable_canonical_redirect_on_cpt_archive', 10, 2 );

/**
 * さらに保険として、CPT の post_type_archive で「本来はコンテンツがある」場合に
 * WordPress が空クエリで 404 化させないよう強制的にステータスを 200 に戻す
 */
function reirie_force_200_on_valid_archive() {
	if ( ! is_404() ) return;

	// リクエストパスが /discography/ /schedule/ 等の CPT archive slug なら 200 に戻す
	$request_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$first_seg = strtok( $request_path, '/' );
	if ( ! $first_seg ) return;

	$cpt_archives = array( 'discography', 'schedule', 'news', 'movie', 'goods' );
	if ( in_array( $first_seg, $cpt_archives, true ) && $request_path === $first_seg ) {
		// ステータスを 200 に戻す
		status_header( 200 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'reirie_force_200_on_valid_archive', 2 );

/* ============================================================
   構造化データ (schema.org JSON-LD) 出力
   ============================================================
   各 CPT 詳細ページ / トップページ / アーカイブに適切な
   schema.org JSON-LD を <head> に出力し、
   Google に「これはどんなコンテンツか」を明確に伝える。
   → Discography 等の薄いページでもインデックスされやすくなる
   ============================================================ */

/**
 * サイト全体で共通の Organization 情報
 */
function reirie_get_organization_schema() {
	$logo_url = get_theme_mod( 'reirie_logo_url', REIRIE_URI . '/assets/img/logo.png' );
	return array(
		'@type'    => 'MusicGroup',
		'@id'      => home_url( '/#organization' ),
		'name'     => 'REIRIE',
		'alternateName' => 'REIRIE OFFICIAL',
		'url'      => home_url( '/' ),
		'logo'     => $logo_url,
		'image'    => $logo_url,
		'genre'    => 'J-Pop, Idol',
		'sameAs'   => array(
			'https://twitter.com/REIRIEofficial',
			'https://instagram.com/reirieofficial',
			'https://www.tiktok.com/@reirieofficial',
			'https://www.youtube.com/@reirieofficial',
		),
	);
}

/**
 * discography 単一投稿の JSON-LD スキーマを構築
 *
 * カテゴリ判定:
 *  - "album" "アルバム" を含む → MusicAlbum
 *  - "single" "シングル" を含む → MusicAlbum (albumProductionType: DemoAlbum/SingleAlbum)
 *  - デフォルト → MusicAlbum
 */
function reirie_build_discography_schema( $post_id ) {
	$cat        = reirie_field( 'disco_category', false, '' );
	$release    = reirie_field( 'disco_release_date' );
	$price      = reirie_field( 'disco_price' );
	$tracks_raw = reirie_field( 'disco_tracks' );
	$buy_url    = reirie_field( 'disco_buy_url' );
	$apple_url  = reirie_field( 'disco_apple_url' );
	$spotify_url= reirie_field( 'disco_spotify_url' );
	$youtube_url= reirie_field( 'disco_youtube_url' );
	$linkco_url = reirie_field( 'disco_linkco_url' );

	$title  = get_the_title( $post_id );
	$url    = get_permalink( $post_id );
	$thumb  = get_the_post_thumbnail_url( $post_id, 'large' );

	// カテゴリを英語スキーマに変換
	$album_type = 'AlbumRelease';
	$cat_lc     = strtolower( $cat );
	if ( strpos( $cat_lc, 'single' ) !== false || strpos( $cat, 'シングル' ) !== false ) {
		$album_type = 'SingleRelease';
	} elseif ( strpos( $cat_lc, 'mini' ) !== false || strpos( $cat, 'ミニ' ) !== false ) {
		$album_type = 'EPRelease';
	} elseif ( strpos( $cat_lc, 'album' ) !== false || strpos( $cat, 'アルバム' ) !== false ) {
		$album_type = 'AlbumRelease';
	}

	// トラックリストを配列に
	$tracks = array();
	if ( $tracks_raw ) {
		$track_lines = preg_split( '/\r\n|\r|\n/', $tracks_raw );
		$track_num = 1;
		foreach ( $track_lines as $t ) {
			$t = trim( $t );
			if ( $t === '' ) continue;
			// "1. 曲名" のような番号付きも許容 → 番号除去
			$t_clean = preg_replace( '/^\s*\d+[\.\)\s]+/', '', $t );
			$tracks[] = array(
				'@type'       => 'MusicRecording',
				'position'    => $track_num,
				'name'        => $t_clean,
				'byArtist'    => array( '@id' => home_url( '/#organization' ) ),
			);
			$track_num++;
		}
	}

	// 外部リンク（sameAs的な役割）
	$same_as = array();
	foreach ( array( $apple_url, $spotify_url, $youtube_url, $linkco_url ) as $u ) {
		if ( ! empty( $u ) ) $same_as[] = esc_url_raw( $u );
	}

	// スキーマ本体
	$schema = array(
		'@context'       => 'https://schema.org',
		'@type'          => 'MusicAlbum',
		'@id'            => $url . '#album',
		'name'           => $title,
		'url'            => $url,
		'byArtist'       => reirie_get_organization_schema(),
		'albumReleaseType' => $album_type,
	);

	if ( $thumb ) {
		$schema['image'] = $thumb;
	}

	if ( $release ) {
		$schema['datePublished'] = $release;
	}

	if ( ! empty( $tracks ) ) {
		$schema['numTracks'] = count( $tracks );
		$schema['track']     = $tracks;
	}

	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = $same_as;
	}

	// 購入可能なら Offer を追加
	if ( ! empty( $buy_url ) ) {
		// 価格から数値だけ抽出（¥1,500(税込) → 1500）
		$price_num = 0;
		if ( $price ) {
			$price_num = (int) preg_replace( '/[^\d]/', '', $price );
		}
		$offer = array(
			'@type'        => 'Offer',
			'url'          => $buy_url,
			'availability' => 'https://schema.org/InStock',
		);
		if ( $price_num > 0 ) {
			$offer['price']         = $price_num;
			$offer['priceCurrency'] = 'JPY';
		}
		$schema['offers'] = $offer;
	}

	// 説明文 (post_content)
	$content = get_post_field( 'post_content', $post_id );
	if ( $content ) {
		$desc = wp_strip_all_tags( $content );
		$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
		if ( mb_strlen( $desc ) > 300 ) {
			$desc = mb_substr( $desc, 0, 300 ) . '…';
		}
		if ( $desc !== '' ) {
			$schema['description'] = $desc;
		}
	}

	return $schema;
}

/**
 * news 単一投稿の JSON-LD スキーマ (Article)
 */
function reirie_build_news_schema( $post_id ) {
	$url   = get_permalink( $post_id );
	$title = get_the_title( $post_id );
	$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
	$date  = reirie_field( 'news_date' );
	if ( ! $date ) $date = get_the_date( 'Y-m-d H:i:s', $post_id );

	$content = get_post_field( 'post_content', $post_id );
	$desc    = wp_strip_all_tags( $content );
	$desc    = trim( preg_replace( '/\s+/', ' ', $desc ) );
	if ( mb_strlen( $desc ) > 200 ) {
		$desc = mb_substr( $desc, 0, 200 ) . '…';
	}

	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'NewsArticle',
		'@id'              => $url . '#article',
		'headline'         => $title,
		'url'              => $url,
		'datePublished'    => date( 'c', strtotime( $date ) ),
		'dateModified'     => date( 'c', strtotime( get_post_modified_time( 'Y-m-d H:i:s', false, $post_id ) ) ),
		'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => $url ),
		'author'           => reirie_get_organization_schema(),
		'publisher'        => reirie_get_organization_schema(),
	);

	if ( $thumb ) {
		$schema['image'] = $thumb;
	}
	if ( $desc ) {
		$schema['description'] = $desc;
	}

	return $schema;
}

/**
 * schedule 単一投稿の JSON-LD スキーマ (MusicEvent)
 */
function reirie_build_schedule_schema( $post_id ) {
	$url       = get_permalink( $post_id );
	$title     = get_the_title( $post_id );
	$thumb     = get_the_post_thumbnail_url( $post_id, 'large' );
	$date      = reirie_field( 'schedule_date' );
	$time      = reirie_field( 'schedule_time' );
	$venue     = reirie_field( 'schedule_venue' );
	$address   = reirie_field( 'schedule_address' );
	$link      = reirie_field( 'schedule_link' );

	if ( ! $date ) return null; // 日付なしはスキーマ出力しない

	$start = $date;
	if ( $time ) {
		// "18:30" のような形式を想定
		$start = $date . 'T' . $time . ':00+09:00';
	} else {
		$start = $date . 'T00:00:00+09:00';
	}

	$schema = array(
		'@context'          => 'https://schema.org',
		'@type'             => 'MusicEvent',
		'@id'               => $url . '#event',
		'name'              => $title,
		'url'               => $url,
		'startDate'         => $start,
		'eventStatus'       => 'https://schema.org/EventScheduled',
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'performer'         => reirie_get_organization_schema(),
		'organizer'         => reirie_get_organization_schema(),
	);

	if ( $thumb ) {
		$schema['image'] = $thumb;
	}

	if ( $venue ) {
		$schema['location'] = array(
			'@type' => 'Place',
			'name'  => $venue,
		);
		if ( $address ) {
			$schema['location']['address'] = $address;
		}
	}

	// 過去のイベントは EventScheduled → EventPostponed に自動判定しない
	// （EventStatusは主催者が決めるので、日付判定でPastは付けない）

	return $schema;
}

/**
 * <head> に構造化データを出力
 */
function reirie_output_structured_data() {
	$schemas = array();

	if ( is_singular( 'discography' ) ) {
		$schemas[] = reirie_build_discography_schema( get_the_ID() );
	} elseif ( is_singular( 'news' ) ) {
		$schemas[] = reirie_build_news_schema( get_the_ID() );
	} elseif ( is_singular( 'schedule' ) ) {
		$s = reirie_build_schedule_schema( get_the_ID() );
		if ( $s ) $schemas[] = $s;
	} elseif ( is_front_page() ) {
		// トップページ: WebSite + Organization
		$schemas[] = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'WebSite',
			'@id'           => home_url( '/#website' ),
			'url'           => home_url( '/' ),
			'name'          => get_bloginfo( 'name' ),
			'description'   => get_bloginfo( 'description' ),
			'publisher'     => array( '@id' => home_url( '/#organization' ) ),
			'inLanguage'    => 'ja',
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
		// Organization スキーマにも @context を付与（単独JSON-LDドキュメントとして有効化）
		$org = reirie_get_organization_schema();
		$org = array_merge( array( '@context' => 'https://schema.org' ), $org );
		$schemas[] = $org;
	}

	// パンくずリスト (single 系全般)
	if ( is_singular( array( 'discography', 'news', 'schedule', 'movie', 'goods' ) ) ) {
		$pt = get_post_type();
		$archive_url = get_post_type_archive_link( $pt );
		$labels = array(
			'discography' => 'Discography',
			'news'        => 'News',
			'schedule'    => 'Schedule',
			'movie'       => 'Movie',
			'goods'       => 'Goods',
		);
		$archive_label = isset( $labels[ $pt ] ) ? $labels[ $pt ] : ucfirst( $pt );

		$schemas[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $archive_label,
					'item'     => $archive_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => get_the_title(),
					'item'     => get_permalink(),
				),
			),
		);
	}

	if ( empty( $schemas ) ) return;

	foreach ( $schemas as $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'reirie_output_structured_data', 30 );

/**
 * <head> に meta description を出力
 * schema.org と併用で SEO 効果を高める
 */
function reirie_output_meta_description() {
	$desc = '';

	if ( is_singular( 'discography' ) ) {
		$title  = get_the_title();
		$cat    = reirie_field( 'disco_category', false, '' );
		$release= reirie_field( 'disco_release_date' );
		$parts  = array();
		if ( $cat ) $parts[] = $cat;
		if ( $release ) $parts[] = reirie_format_date( $release ) . ' リリース';
		$content = wp_strip_all_tags( get_the_content() );
		$content = trim( preg_replace( '/\s+/', ' ', $content ) );

		$desc = 'REIRIE「' . $title . '」';
		if ( ! empty( $parts ) ) $desc .= '（' . implode( '・', $parts ) . '）';
		if ( $content ) {
			$desc .= '。' . mb_substr( $content, 0, 100 );
		} else {
			$desc .= '。作品詳細・トラックリスト・購入・配信リンクはこちら。';
		}
	} elseif ( is_singular( 'news' ) ) {
		$title = get_the_title();
		$content = wp_strip_all_tags( get_the_content() );
		$content = trim( preg_replace( '/\s+/', ' ', $content ) );
		$desc = 'REIRIE NEWS「' . $title . '」';
		if ( $content ) {
			$desc .= '。' . mb_substr( $content, 0, 100 );
		}
	} elseif ( is_singular( 'schedule' ) ) {
		$title  = get_the_title();
		$date   = reirie_field( 'schedule_date' );
		$venue  = reirie_field( 'schedule_venue' );
		$desc = 'REIRIE スケジュール「' . $title . '」';
		if ( $date ) $desc .= ' ' . reirie_format_date( $date );
		if ( $venue ) $desc .= ' @ ' . $venue;
	} elseif ( is_post_type_archive( 'discography' ) ) {
		$desc = 'REIRIE の全楽曲・作品一覧。シングル、アルバム、ミニアルバムなどすべての音源をリリース年ごとに掲載しています。';
	} elseif ( is_post_type_archive( 'schedule' ) ) {
		$desc = 'REIRIE のライブ・イベントスケジュール。今後の予定・過去の出演情報を月ごとに掲載しています。';
	} elseif ( is_post_type_archive( 'news' ) ) {
		$desc = 'REIRIE の最新ニュース・お知らせ一覧。ライブ告知、リリース情報、メディア出演情報を掲載しています。';
	} elseif ( is_front_page() ) {
		$desc = get_bloginfo( 'description' );
		if ( ! $desc ) {
			$desc = 'REIRIE 公式サイト。2人組アイドルグループREIRIE の最新情報、ライブスケジュール、楽曲、動画、グッズをお届けします。';
		}
	}

	if ( $desc !== '' ) {
		$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
		if ( mb_strlen( $desc ) > 160 ) {
			$desc = mb_substr( $desc, 0, 160 ) . '…';
		}
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		// Open Graph description も同時に
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'reirie_output_meta_description', 3 );

/**
 * <head> に Open Graph / Twitter Card メタタグを出力
 */
function reirie_output_og_meta() {
	$title    = '';
	$url      = '';
	$image    = '';
	$type     = 'website';
	$site_name= get_bloginfo( 'name' );

	if ( is_singular() ) {
		$title = get_the_title();
		$url   = get_permalink();
		$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		$type  = 'article';
	} elseif ( is_post_type_archive() ) {
		$pt = get_query_var( 'post_type' );
		if ( is_array( $pt ) ) $pt = $pt[0];
		$labels = array(
			'discography' => 'Discography',
			'news'        => 'News',
			'schedule'    => 'Schedule',
			'movie'       => 'Movie',
			'goods'       => 'Goods',
		);
		$title = ( isset( $labels[ $pt ] ) ? $labels[ $pt ] : ucfirst( $pt ) ) . ' - ' . $site_name;
		$url   = get_post_type_archive_link( $pt );
	} elseif ( is_front_page() || is_home() ) {
		$title = $site_name;
		$url   = home_url( '/' );
	}

	if ( ! $image ) {
		// フォールバック: OG画像 or ロゴ
		$og_default = get_theme_mod( 'reirie_og_image', REIRIE_URI . '/assets/img/hero-poster.jpg' );
		$image = $og_default;
	}

	if ( ! $url ) return;

	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta property="og:locale" content="ja_JP">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:site" content="@REIRIEofficial">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'reirie_output_og_meta', 4 );

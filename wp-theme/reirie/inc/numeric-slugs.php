<?php
/**
 * カスタム投稿タイプの個別ページURLを数字（投稿ID）のみにする
 *
 * 対象: news / schedule / discography / movie / goods （member は除く）
 * 効果:
 *   - 新規/更新時に post_name を投稿ID に書き換え
 *   - 既存記事も管理画面 → 設定 → パーマリンク を一度開けば反映
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 数字スラッグを適用する投稿タイプ
 */
function reirie_numeric_slug_post_types() {
	return apply_filters( 'reirie_numeric_slug_post_types', array(
		'news',
		'schedule',
		'discography',
		'movie',
		'goods',
	) );
}

/**
 * 投稿の保存時に post_name（スラッグ）を投稿IDに書き換える
 *
 * 注: wp_update_post の無限ループを防ぐため remove_action で一時解除
 */
function reirie_force_numeric_slug( $post_id, $post, $update ) {
	// 自動保存・リビジョン・自動下書きはスキップ
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( wp_is_post_autosave( $post_id ) ) return;
	if ( 'auto-draft' === $post->post_status ) return;

	// 対象のCPTかチェック
	if ( ! in_array( $post->post_type, reirie_numeric_slug_post_types(), true ) ) return;

	$desired_slug = (string) $post_id;

	// 既に正しいスラッグならスキップ（ループ防止の最重要条件）
	if ( $post->post_name === $desired_slug ) return;

	// 無限ループ回避: 自分自身を一時解除
	remove_action( 'save_post', 'reirie_force_numeric_slug', 20 );

	wp_update_post( array(
		'ID'        => $post_id,
		'post_name' => $desired_slug,
	) );

	// 再登録
	add_action( 'save_post', 'reirie_force_numeric_slug', 20, 3 );
}
add_action( 'save_post', 'reirie_force_numeric_slug', 20, 3 );

/**
 * 新規投稿のスラッグサニタイズ時にも数字を強制
 * （wp_unique_post_slug は title を元にスラッグを作るため、日本語タイトル → URLエンコード化されてしまう
 *   それを未然に防ぐ）
 */
function reirie_pre_post_name( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
	if ( ! in_array( $post_type, reirie_numeric_slug_post_types(), true ) ) return $slug;
	if ( ! $post_ID ) return $slug;
	return (string) $post_ID;
}
add_filter( 'wp_unique_post_slug', 'reirie_pre_post_name', 10, 6 );

/**
 * future → publish 自動切替時のスラッグ固定（安全策）
 *
 * WordPress の wp_publish_post() 内部処理で post_name が再生成される
 * 可能性があるため、公開予定（予約投稿）が自動公開された瞬間に
 * 改めて post_name = 投稿ID に固定し、SNS で告知済みのURLを保護する。
 *
 * future_to_publish フックは WordPress の予約投稿が cron で自動公開された
 * タイミングで発火するため、ここで再度スラッグを上書きしておけば
 * URL が一瞬たりとも変わることがない。
 */
function reirie_lock_slug_on_future_publish( $post ) {
	if ( empty( $post ) || empty( $post->ID ) ) return;
	if ( ! in_array( $post->post_type, reirie_numeric_slug_post_types(), true ) ) return;

	$desired_slug = (string) $post->ID;
	if ( $post->post_name === $desired_slug ) return;

	// 直接 DB 更新でフック発火を抑え、確実に post_name のみ書き換える
	global $wpdb;
	$wpdb->update(
		$wpdb->posts,
		array( 'post_name' => $desired_slug ),
		array( 'ID' => $post->ID ),
		array( '%s' ),
		array( '%d' )
	);
	clean_post_cache( $post->ID );
}
add_action( 'future_to_publish', 'reirie_lock_slug_on_future_publish', 5 );

/**
 * パーマリンク自体の再生成保険＋公開予定でも pretty URL を返す
 *
 * 通常 WordPress は post_status が 'publish' でない投稿（draft, future, pending）
 * に対して get_permalink() で "?post_type=news&p=96" のような ugly URL を返す。
 * → SNS で公開予定の URL を事前告知できるよう、CPT については常に pretty URL
 *   （/news/96/）を返すように上書きする。
 *
 * さらに post_name が想定外の値（日本語タイトルから生成された URL エンコード等）
 * になっていても、URL 内のスラッグ部分を投稿ID に置換して安全。
 */
function reirie_filter_permalink_to_numeric( $permalink, $post ) {
	if ( empty( $post ) || empty( $post->ID ) ) return $permalink;
	if ( ! in_array( $post->post_type, reirie_numeric_slug_post_types(), true ) ) return $permalink;

	$desired_slug = (string) $post->ID;

	// (A) "?post_type=xxx&p=NN" の ugly URL を pretty URL に強制変換
	//     （future / draft / pending 投稿のときに発生する）
	if ( strpos( $permalink, '?post_type=' ) !== false || strpos( $permalink, '?p=' ) !== false || preg_match( '#/\?p=\d+#', $permalink ) ) {
		// CPT のリライトスラッグを取得（post_type と一致しない場合がある）
		$pt_obj = get_post_type_object( $post->post_type );
		$rewrite_slug = $post->post_type;
		if ( $pt_obj && ! empty( $pt_obj->rewrite['slug'] ) ) {
			$rewrite_slug = $pt_obj->rewrite['slug'];
		}
		return home_url( '/' . $rewrite_slug . '/' . $desired_slug . '/' );
	}

	// (B) pretty URL だが post_name が想定外の場合は数字に置換
	if ( $post->post_name === $desired_slug ) return $permalink;
	$wrong_slug = $post->post_name;
	if ( $wrong_slug && strpos( $permalink, '/' . $wrong_slug ) !== false ) {
		$permalink = preg_replace(
			'#/' . preg_quote( $wrong_slug, '#' ) . '(/|$)#',
			'/' . $desired_slug . '$1',
			$permalink
		);
	}

	return $permalink;
}
add_filter( 'post_type_link', 'reirie_filter_permalink_to_numeric', 10, 2 );
add_filter( 'post_link', 'reirie_filter_permalink_to_numeric', 10, 2 );

/**
 * 既存記事のスラッグ一括正規化（管理者のみ、1日1回まで実行）
 * 管理画面アクセス時に走らせる軽量処理
 */
function reirie_migrate_legacy_slugs() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;

	// 1日1回しか実行しないようにフラグ管理
	$last = get_option( 'reirie_slug_migration_last', 0 );
	if ( ( time() - (int) $last ) < DAY_IN_SECONDS ) return;
	update_option( 'reirie_slug_migration_last', time(), false );

	global $wpdb;
	$types = reirie_numeric_slug_post_types();
	$types_in = "'" . implode( "','", array_map( 'esc_sql', $types ) ) . "'";

	// post_name が数字でない投稿を一括取得（最大50件/日）
	$rows = $wpdb->get_results(
		"SELECT ID, post_name FROM {$wpdb->posts}
		 WHERE post_type IN ($types_in)
		 AND post_status IN ('publish','draft','future','private','pending')
		 AND post_name NOT REGEXP '^[0-9]+$'
		 LIMIT 50"
	);

	if ( empty( $rows ) ) return;

	foreach ( $rows as $row ) {
		$wpdb->update(
			$wpdb->posts,
			array( 'post_name' => (string) $row->ID ),
			array( 'ID' => $row->ID ),
			array( '%s' ),
			array( '%d' )
		);
		clean_post_cache( $row->ID );
	}

	// リライトルールフラッシュ
	flush_rewrite_rules( false );
}
add_action( 'admin_init', 'reirie_migrate_legacy_slugs' );


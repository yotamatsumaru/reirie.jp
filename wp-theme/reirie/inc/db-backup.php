<?php
/**
 * REIRIE データベース自動バックアップ
 *
 * サイトのデータベース（投稿・固定ページ・設定・お問い合わせ内容など）のみを
 * 毎日自動でバックアップする。画像・動画などのメディアファイルは対象外
 * （データベースだけを軽量に、かつ日次で保持するのが目的）。
 *
 * - WP-Cron による毎日自動実行（サーバーへのアクセスがあったタイミングで発火）
 * - 管理画面から「今すぐバックアップ」も可能
 * - バックアップ一覧の閲覧・ダウンロード・削除
 * - 保存日数を超えた古いバックアップは自動削除
 * - （上級者向け）外部Cronから叩ける秘密URLトリガーも用意
 *   → WP-Cronはアクセスが少ない日は発火が遅れることがあるため、
 *     サーバー側の本物のCronで補強したい場合に利用できる。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   保存先ディレクトリ（uploads配下・外部からは直接アクセス不可にする）
   ============================================================ */
function reirie_db_backup_dir() {
	$upload = wp_upload_dir();
	$dir = trailingslashit( $upload['basedir'] ) . 'reirie-db-backups';

	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	// バックアップにはお問い合わせ内容など機微な情報も含まれ得るため、
	// Webサーバー経由での直接アクセスを禁止する（.htaccess + index.php の二重対策）。
	$htaccess = $dir . '/.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		$rules  = "# REIRIE: このディレクトリへの直接アクセスを禁止（自動生成）\n";
		$rules .= "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n";
		$rules .= "<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n";
		@file_put_contents( $htaccess, $rules );
	}
	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}

	return $dir;
}

/* ============================================================
   バックアップ一覧取得（新しい順）
   ============================================================ */
function reirie_db_backup_list() {
	$dir = reirie_db_backup_dir();
	$files = glob( $dir . '/db-backup-*.sql.gz' );
	if ( ! $files ) return array();

	$list = array();
	foreach ( $files as $f ) {
		$list[] = array(
			'file' => basename( $f ),
			'size' => filesize( $f ),
			'time' => filemtime( $f ),
		);
	}
	usort( $list, function( $a, $b ) { return $b['time'] - $a['time']; } );
	return $list;
}

/* ============================================================
   バックアップ本体（データベース全体をSQLダンプ→gzip圧縮）
   ============================================================ */
function reirie_db_backup_run( $manual = false ) {
	global $wpdb;

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 ); // 大きめのDBでもタイムアウトしないように
	}

	$dir = reirie_db_backup_dir();
	$filename = 'db-backup-' . date_i18n( 'Ymd-His' ) . '.sql.gz';
	$filepath = $dir . '/' . $filename;

	$result = array(
		'success' => false,
		'message' => '',
		'file'    => '',
		'manual'  => (bool) $manual,
		'time'    => current_time( 'mysql' ),
	);

	if ( ! function_exists( 'gzopen' ) ) {
		$result['message'] = 'サーバーでgzip圧縮（zlib）が利用できないため、バックアップを作成できませんでした。';
		reirie_db_backup_save_log( $result );
		return $result;
	}

	$gz = @gzopen( $filepath, 'wb9' );
	if ( ! $gz ) {
		$result['message'] = 'バックアップファイルの書き込みに失敗しました（サーバーの空き容量・権限をご確認ください）。';
		reirie_db_backup_save_log( $result );
		return $result;
	}

	gzwrite( $gz, "-- REIRIE Database Backup\n" );
	gzwrite( $gz, "-- Site: " . home_url() . "\n" );
	gzwrite( $gz, "-- Generated: " . current_time( 'mysql' ) . "\n\n" );
	gzwrite( $gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n" );

	$tables = $wpdb->get_col( 'SHOW TABLES' );
	$table_count = 0;

	foreach ( $tables as $table ) {
		$table_count++;

		// テーブル構造
		$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N );
		if ( $create && isset( $create[1] ) ) {
			gzwrite( $gz, "DROP TABLE IF EXISTS `{$table}`;\n" );
			gzwrite( $gz, $create[1] . ";\n\n" );
		}

		// データ（メモリ節約のため500件ずつ分割して書き出す）
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		if ( $count > 0 ) {
			$chunk = 500;
			$cols  = null;
			for ( $offset = 0; $offset < $count; $offset += $chunk ) {
				$rows = $wpdb->get_results( "SELECT * FROM `{$table}` LIMIT {$offset}, {$chunk}", ARRAY_A );
				if ( ! $rows ) continue;
				if ( $cols === null ) $cols = array_keys( $rows[0] );

				$col_list = '`' . implode( '`,`', $cols ) . '`';
				$values_sql = array();
				foreach ( $rows as $row ) {
					$vals = array();
					foreach ( $cols as $c ) {
						$v = $row[ $c ];
						$vals[] = is_null( $v ) ? 'NULL' : "'" . esc_sql( $v ) . "'";
					}
					$values_sql[] = '(' . implode( ',', $vals ) . ')';
				}
				gzwrite( $gz, "INSERT INTO `{$table}` ({$col_list}) VALUES\n" . implode( ",\n", $values_sql ) . ";\n" );
			}
			gzwrite( $gz, "\n" );
		}
	}

	gzwrite( $gz, "SET FOREIGN_KEY_CHECKS=1;\n" );
	gzclose( $gz );

	if ( ! file_exists( $filepath ) || filesize( $filepath ) === 0 ) {
		$result['message'] = 'バックアップファイルの生成に失敗しました。';
		reirie_db_backup_save_log( $result );
		return $result;
	}

	$result['success'] = true;
	$result['file']    = $filename;
	$result['message'] = $table_count . '個のテーブルをバックアップしました。';

	reirie_db_backup_save_log( $result );
	reirie_db_backup_cleanup();

	return $result;
}

function reirie_db_backup_save_log( $result ) {
	update_option( 'reirie_db_backup_last_run', $result, false );
}

/* ============================================================
   保存期間を超えた古いバックアップを自動削除
   ============================================================ */
function reirie_db_backup_cleanup() {
	$keep_days = (int) get_option( 'reirie_db_backup_keep_days', 14 );
	if ( $keep_days <= 0 ) $keep_days = 14;

	$dir = reirie_db_backup_dir();
	$files = glob( $dir . '/db-backup-*.sql.gz' );
	if ( ! $files ) return;

	$cutoff = time() - ( $keep_days * DAY_IN_SECONDS );
	foreach ( $files as $f ) {
		if ( filemtime( $f ) < $cutoff ) {
			@unlink( $f );
		}
	}
}

/* ============================================================
   WP-Cron: 毎日自動実行のスケジュール登録
   ============================================================ */
function reirie_db_backup_cron_run() {
	if ( ! get_option( 'reirie_db_backup_enabled', 1 ) ) return; // 設定で無効化されていれば何もしない
	reirie_db_backup_run( false );
}
add_action( 'reirie_db_backup_cron', 'reirie_db_backup_cron_run' );

function reirie_db_backup_schedule() {
	if ( wp_next_scheduled( 'reirie_db_backup_cron' ) ) return;

	// 初回実行の目安を「サイトのタイムゾーンで当日または翌日の午前4時」に設定。
	// （深夜〜早朝はアクセスが少なく、閲覧者への影響が少ないため）
	$timezone = wp_timezone();
	$now  = new DateTime( 'now', $timezone );
	$next = new DateTime( 'today 04:00', $timezone );
	if ( $next <= $now ) {
		$next->modify( '+1 day' );
	}
	wp_schedule_event( $next->getTimestamp(), 'daily', 'reirie_db_backup_cron' );
}
add_action( 'admin_init', 'reirie_db_backup_schedule' );

// テーマ切り替え時はスケジュールを解除（残骸のcronが走り続けないように）
add_action( 'switch_theme', 'reirie_db_backup_unschedule' );
function reirie_db_backup_unschedule() {
	$timestamp = wp_next_scheduled( 'reirie_db_backup_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'reirie_db_backup_cron' );
	}
}

/* ============================================================
   （上級者向け）外部Cronから叩ける秘密URLトリガー
   WP-Cronはサイトへのアクセスがないと発火しないため、
   アクセスの少ないサイトでも確実に毎日実行したい場合に、
   サーバー側の本物のCronからこのURLを叩いてもらう用途。
   ============================================================ */
function reirie_db_backup_get_secret_key() {
	$key = get_option( 'reirie_db_backup_secret_key' );
	if ( ! $key ) {
		$key = wp_generate_password( 32, false );
		update_option( 'reirie_db_backup_secret_key', $key );
	}
	return $key;
}

add_action( 'init', 'reirie_db_backup_external_trigger' );
function reirie_db_backup_external_trigger() {
	if ( ! isset( $_GET['reirie_db_backup_key'] ) ) return;

	$key = get_option( 'reirie_db_backup_secret_key' );
	if ( ! $key || ! hash_equals( $key, wp_unslash( $_GET['reirie_db_backup_key'] ) ) ) {
		return; // キー不一致は静かに無視（存在を推測されないようにするため）
	}

	$result = reirie_db_backup_run( false );
	wp_die(
		$result['success'] ? ( 'OK: ' . esc_html( $result['message'] ) ) : ( 'NG: ' . esc_html( $result['message'] ) ),
		'REIRIE DB Backup',
		array( 'response' => $result['success'] ? 200 : 500 )
	);
}

/* ============================================================
   管理メニュー登録
   ============================================================ */
add_action( 'admin_menu', 'reirie_db_backup_admin_menu' );
function reirie_db_backup_admin_menu() {
	add_submenu_page( 'reirie-dashboard', 'DBバックアップ', 'DBバックアップ', 'manage_options', 'reirie-db-backup', 'reirie_db_backup_page' );
}

/* ============================================================
   フォーム処理（admin-post.php 経由）
   ============================================================ */
add_action( 'admin_post_reirie_db_backup_run_now', 'reirie_db_backup_handle_run_now' );
function reirie_db_backup_handle_run_now() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( '権限がありません' );
	check_admin_referer( 'reirie_db_backup_run_now' );

	$result = reirie_db_backup_run( true );
	wp_safe_redirect( add_query_arg( array( 'page' => 'reirie-db-backup', 'ran' => $result['success'] ? '1' : '0' ), admin_url( 'admin.php' ) ) );
	exit;
}

add_action( 'admin_post_reirie_db_backup_save_settings', 'reirie_db_backup_handle_save_settings' );
function reirie_db_backup_handle_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( '権限がありません' );
	check_admin_referer( 'reirie_db_backup_save_settings' );

	$days = isset( $_POST['reirie_db_backup_keep_days'] ) ? absint( wp_unslash( $_POST['reirie_db_backup_keep_days'] ) ) : 14;
	if ( $days < 1 )  $days = 1;
	if ( $days > 90 ) $days = 90;
	update_option( 'reirie_db_backup_keep_days', $days );

	$enabled = ! empty( $_POST['reirie_db_backup_enabled'] ) ? 1 : 0;
	update_option( 'reirie_db_backup_enabled', $enabled );

	wp_safe_redirect( add_query_arg( array( 'page' => 'reirie-db-backup', 'settings_saved' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}

add_action( 'admin_post_reirie_db_backup_regen_key', 'reirie_db_backup_handle_regen_key' );
function reirie_db_backup_handle_regen_key() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( '権限がありません' );
	check_admin_referer( 'reirie_db_backup_regen_key' );

	update_option( 'reirie_db_backup_secret_key', wp_generate_password( 32, false ) );

	wp_safe_redirect( add_query_arg( array( 'page' => 'reirie-db-backup', 'key_regen' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}

add_action( 'admin_post_reirie_db_backup_download', 'reirie_db_backup_handle_download' );
function reirie_db_backup_handle_download() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( '権限がありません' );
	check_admin_referer( 'reirie_db_backup_download' );

	$file = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
	$dir  = reirie_db_backup_dir();
	$path = $dir . '/' . $file;
	$real_dir  = realpath( $dir );
	$real_path = $file ? realpath( $path ) : false;

	if ( ! $file || ! $real_path || ! $real_dir || strpos( $real_path, $real_dir ) !== 0 || ! file_exists( $real_path ) ) {
		wp_die( 'ファイルが見つかりません' );
	}

	nocache_headers();
	header( 'Content-Type: application/gzip' );
	header( 'Content-Disposition: attachment; filename="' . $file . '"' );
	header( 'Content-Length: ' . filesize( $real_path ) );
	readfile( $real_path );
	exit;
}

add_action( 'admin_post_reirie_db_backup_delete', 'reirie_db_backup_handle_delete' );
function reirie_db_backup_handle_delete() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( '権限がありません' );
	check_admin_referer( 'reirie_db_backup_delete' );

	$file = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
	$dir  = reirie_db_backup_dir();
	$path = $dir . '/' . $file;
	$real_dir  = realpath( $dir );
	$real_path = $file ? realpath( $path ) : false;

	if ( $file && $real_path && $real_dir && strpos( $real_path, $real_dir ) === 0 && file_exists( $real_path ) ) {
		@unlink( $real_path );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'reirie-db-backup', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}

/* ============================================================
   管理画面：DBバックアップページ
   ============================================================ */
function reirie_db_backup_page() {
	wp_enqueue_style( 'dashicons' );

	$backups     = reirie_db_backup_list();
	$last_run    = get_option( 'reirie_db_backup_last_run' );
	$keep_days   = (int) get_option( 'reirie_db_backup_keep_days', 14 );
	$enabled     = (bool) get_option( 'reirie_db_backup_enabled', 1 );
	$secret_key  = reirie_db_backup_get_secret_key();
	$external_url = add_query_arg( 'reirie_db_backup_key', $secret_key, home_url( '/' ) );
	$next_scheduled = wp_next_scheduled( 'reirie_db_backup_cron' );
	?>
	<div class="reirie-fw-wrap">
		<style>
			#wpcontent { padding-left: 0 !important; }
			.reirie-fw-wrap { margin: 0; padding: 0; background: #fafafa; min-height: 100vh; }
			.reirie-fw-wrap * { box-sizing: border-box; }
			.reirie-backup-inner { padding: 24px 32px 60px; max-width: 1100px; }
			.reirie-fw-header { display: flex; align-items: center; gap: 14px; margin: 0 0 6px; }
			.reirie-fw-header .brand-mark {
				width: 40px; height: 40px; border-radius: 12px;
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				display: inline-flex; align-items: center; justify-content: center;
				box-shadow: 0 6px 16px rgba(255,126,182,0.35);
			}
			.reirie-fw-header .brand-mark .dashicons { color: #fff; font-size: 22px; width: 22px; height: 22px; }
			.reirie-fw-header h1 { font-size: 22px; font-weight: 600; color: #1d1d1f; margin: 0; padding: 0; letter-spacing: 0.02em; }
			.reirie-fw-lead { font-size: 13px; color: #6b6b6b; margin: 0 0 24px 54px; line-height: 1.6; }
			.reirie-backup h2 {
				font-size: 11px; font-weight: 700; color: #888;
				letter-spacing: 0.18em; text-transform: uppercase;
				margin: 30px 0 12px; padding-bottom: 8px;
				border-bottom: 1px solid #ececec;
				display: flex; align-items: center; gap: 8px;
			}
			.reirie-backup h2 .dashicons { font-size: 14px; width: 14px; height: 14px; color: #c43a73; }
			.reirie-backup table.reirie-help-table {
				background: #fff; border: 1px solid #ececec; border-collapse: collapse;
				border-radius: 10px; overflow: hidden; width: 100%;
			}
			.reirie-backup table.reirie-help-table th {
				background: #fafafa; padding: 12px 16px; text-align: left;
				font-size: 11px; font-weight: 600; color: #888;
				letter-spacing: 0.1em; text-transform: uppercase;
				border-bottom: 1px solid #ececec;
			}
			.reirie-backup table.reirie-help-table td {
				padding: 12px 16px; border-bottom: 1px solid #f4f4f6; font-size: 13px; color: #444;
			}
			.reirie-backup table.reirie-help-table tr:last-child td { border-bottom: none; }
			.reirie-backup table.reirie-help-table td:first-child { font-weight: 500; color: #1d1d1f; white-space: nowrap; }
			.reirie-backup-notice {
				padding: 12px 16px; border-radius: 8px; font-size: 13px; margin: 0 0 16px;
			}
			.reirie-backup-notice.success { background: #eaf7ee; color: #2a8a4a; border: 1px solid #cdeed8; }
			.reirie-backup-notice.error   { background: #fdecea; color: #c0392b; border: 1px solid #f6cfcb; }
			.reirie-backup-card {
				background: #fff; border: 1px solid #ececec; border-radius: 10px;
				padding: 18px 20px; margin: 10px 0;
			}
			.reirie-backup-card p { margin: 0 0 10px; font-size: 13px; color: #555; line-height: 1.7; }
			.reirie-backup-card p:last-child { margin-bottom: 0; }
			.reirie-backup .button-primary {
				background: linear-gradient(135deg,#ff7eb6,#b07aff); border: none;
				box-shadow: 0 4px 12px rgba(176,122,255,0.3);
			}
			.reirie-backup .button-primary:hover { background: linear-gradient(135deg,#ff6ba8,#a066ff); }
			.reirie-backup code {
				display: inline-block; background: #f4f4f6; padding: 6px 10px; border-radius: 6px;
				font-size: 12px; word-break: break-all; max-width: 100%;
			}
			@media (max-width: 680px) {
				.reirie-backup-inner { padding: 14px; }
				.reirie-fw-lead { margin-left: 0; }
			}
		</style>

		<div class="reirie-backup-inner reirie-backup">
			<div class="reirie-fw-header">
				<span class="brand-mark"><span class="dashicons dashicons-database"></span></span>
				<h1>データベースバックアップ</h1>
			</div>
			<p class="reirie-fw-lead">
				サイトのデータベース（投稿・固定ページ・サイト設定・お問い合わせ内容など）だけを毎日自動でバックアップします。
				画像・動画などのメディアファイルは対象外です。
			</p>

			<?php if ( isset( $_GET['ran'] ) ) : ?>
				<?php if ( $_GET['ran'] === '1' ) : ?>
					<div class="reirie-backup-notice success">バックアップを作成しました。</div>
				<?php else : ?>
					<div class="reirie-backup-notice error">バックアップに失敗しました。<?php echo $last_run ? esc_html( $last_run['message'] ) : ''; ?></div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="reirie-backup-notice success">バックアップファイルを削除しました。</div>
			<?php endif; ?>
			<?php if ( isset( $_GET['settings_saved'] ) ) : ?>
				<div class="reirie-backup-notice success">設定を保存しました。</div>
			<?php endif; ?>
			<?php if ( isset( $_GET['key_regen'] ) ) : ?>
				<div class="reirie-backup-notice success">外部トリガー用URLを再発行しました。</div>
			<?php endif; ?>

			<h2><span class="dashicons dashicons-info-outline"></span>状態</h2>
			<table class="reirie-help-table">
				<tbody>
					<tr>
						<td>自動バックアップ</td>
						<td><?php echo $enabled ? '<span style="color:#2a8a4a;">有効</span>' : '<span style="color:#c0392b;">無効</span>'; ?></td>
					</tr>
					<tr>
						<td>次回自動実行の目安</td>
						<td><?php echo $next_scheduled ? esc_html( date_i18n( 'Y年n月j日 H:i', $next_scheduled ) ) : '未設定'; ?></td>
					</tr>
					<tr>
						<td>保存期間</td>
						<td><?php echo esc_html( $keep_days ); ?>日間（これより古いファイルは自動削除されます）</td>
					</tr>
					<tr>
						<td>最終実行結果</td>
						<td>
							<?php if ( $last_run ) : ?>
								<?php echo esc_html( $last_run['time'] ); ?>
								<?php echo ! empty( $last_run['manual'] ) ? '（手動実行）' : '（自動実行）'; ?>
								―
								<?php echo ! empty( $last_run['success'] ) ? '<span style="color:#2a8a4a;">成功</span>' : '<span style="color:#c0392b;">失敗</span>'; ?>
								<?php echo esc_html( $last_run['message'] ); ?>
							<?php else : ?>
								まだ実行されていません
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:20px 0;">
				<input type="hidden" name="action" value="reirie_db_backup_run_now">
				<?php wp_nonce_field( 'reirie_db_backup_run_now' ); ?>
				<button type="submit" class="button button-primary button-large">
					<span class="dashicons dashicons-database-export" style="vertical-align:middle;margin-right:4px;"></span>今すぐバックアップを実行
				</button>
			</form>

			<h2><span class="dashicons dashicons-admin-generic"></span>設定</h2>
			<div class="reirie-backup-card">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="reirie_db_backup_save_settings">
					<?php wp_nonce_field( 'reirie_db_backup_save_settings' ); ?>
					<p>
						<label>
							<input type="checkbox" name="reirie_db_backup_enabled" value="1" <?php checked( $enabled ); ?>>
							毎日自動でバックアップする
						</label>
					</p>
					<p>
						<label>
							バックアップの保存期間：
							<input type="number" name="reirie_db_backup_keep_days" value="<?php echo esc_attr( $keep_days ); ?>" min="1" max="90" style="width:70px;">
							日間
						</label>
					</p>
					<button type="submit" class="button button-primary">設定を保存</button>
				</form>
			</div>

			<h2><span class="dashicons dashicons-media-archive"></span>バックアップ一覧</h2>
			<?php if ( empty( $backups ) ) : ?>
				<p style="font-size:13px;color:#888;">まだバックアップファイルがありません。「今すぐバックアップを実行」を押すか、翌日の自動実行をお待ちください。</p>
			<?php else : ?>
				<table class="reirie-help-table">
					<thead><tr><th>ファイル名</th><th>日時</th><th>サイズ</th><th>操作</th></tr></thead>
					<tbody>
					<?php foreach ( $backups as $b ) :
						$download_url = wp_nonce_url( add_query_arg( array( 'action' => 'reirie_db_backup_download', 'file' => $b['file'] ), admin_url( 'admin-post.php' ) ), 'reirie_db_backup_download' );
						$delete_url   = wp_nonce_url( add_query_arg( array( 'action' => 'reirie_db_backup_delete', 'file' => $b['file'] ), admin_url( 'admin-post.php' ) ), 'reirie_db_backup_delete' );
					?>
						<tr>
							<td><?php echo esc_html( $b['file'] ); ?></td>
							<td><?php echo esc_html( date_i18n( 'Y年n月j日 H:i', $b['time'] ) ); ?></td>
							<td><?php echo esc_html( size_format( $b['size'] ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( $download_url ); ?>" class="button button-small">ダウンロード</a>
								<a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small" onclick="return confirm('このバックアップを削除しますか？元に戻せません。');" style="color:#c0392b;">削除</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<h2><span class="dashicons dashicons-admin-tools"></span>より確実に毎日実行したい場合（上級者向け）</h2>
			<div class="reirie-backup-card">
				<p>
					WordPressの自動実行（WP-Cron）はサイトへのアクセスがあったタイミングで動く仕組みのため、アクセスが少ない日は実行が翌日にずれ込むことがあります。
					毎日決まった時刻に確実に実行したい場合は、サーバー会社（Xserverなど）の「Cron設定」機能で、下記URLに1日1回アクセスするよう設定してください。
				</p>
				<p><code><?php echo esc_html( $external_url ); ?></code></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('URLを再発行すると、今のURLは使えなくなります。サーバー側のCron設定も更新してください。よろしいですか？');">
					<input type="hidden" name="action" value="reirie_db_backup_regen_key">
					<?php wp_nonce_field( 'reirie_db_backup_regen_key' ); ?>
					<button type="submit" class="button">このURLを再発行する</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}

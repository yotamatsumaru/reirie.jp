<?php
/**
 * 管理画面の通知
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ACF が未インストールの場合に通知（フォールバックでも動くが推奨案内）
 */
function reirie_admin_notice_acf() {
	if ( reirie_acf_active() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="notice notice-info is-dismissible">
		<p><strong>[REIRIE テーマ]</strong> より便利に編集するために、無料プラグイン
		<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ); ?>">
		Advanced Custom Fields (ACF)</a> のインストールをおすすめします。
		未導入でも標準のメタボックスから入力できます。</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'reirie_admin_notice_acf' );

/**
 * トップページにスタートガイドを表示
 */
function reirie_admin_notice_setup() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'dashboard' ) return;
	if ( get_option( 'reirie_setup_dismissed' ) ) return;
	?>
	<div class="notice notice-success is-dismissible">
		<h3 style="margin:.5em 0;">🎀 REIRIE テーマへようこそ！</h3>
		<p>セットアップは以下の手順で完了します：</p>
		<ol>
			<li><strong>固定ページ「Home」を作成</strong>し、「設定 → 表示設定」でフロントページに指定</li>
			<li>左メニューの <strong>News / Schedule / Discography / Movie / Member / Goods</strong> から各コンテンツを登録</li>
			<li><strong>外観 → カスタマイズ</strong> でヒーロー動画やSNSリンクを設定</li>
		</ol>
		<p>詳しくはテーマフォルダ内の <code>README.md</code> をご覧ください。</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'reirie_admin_notice_setup' );

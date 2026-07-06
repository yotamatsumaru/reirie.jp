<?php
/**
 * 検索フォーム
 *
 * @package REIRIE
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;gap:8px;">
	<label for="s" class="screen-reader-text"><?php esc_html_e( '検索:', 'reirie' ); ?></label>
	<input type="search" id="s" class="search-field" placeholder="<?php esc_attr_e( 'キーワードを入力', 'reirie' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" style="flex:1;padding:10px 16px;border:1px solid rgba(255,126,182,.4);border-radius:30px;font-size:14px;">
	<button type="submit" class="search-submit more-btn" style="padding:10px 24px;font-size:12px;"><?php esc_html_e( 'SEARCH', 'reirie' ); ?></button>
</form>

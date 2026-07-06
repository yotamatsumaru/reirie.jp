<?php
/**
 * Contact Section
 *
 * 各カードのリンク先は、管理画面「REIRIE 設定 > お問い合わせカード」の
 * URL 欄に入力された値に応じて切り替わる。
 *
 *   - 空欄／#／'default' → 既定のお問い合わせフォーム（template-contact.php）
 *                          に form_type を付けて遷移
 *   - http:// または https:// から始まる外部 URL
 *                       → 別タブで外部サイトへ
 *   - / から始まる内部パス、または WP 固定ページの URL
 *                       → 同タブで内部ページへ遷移
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * カードキー → 既定の遷移先
 *   FANCLUB  → お問い合わせフォーム(press)（URL 未設定時のフォールバック）
 *   PRESS    → お問い合わせフォーム(press)
 *   FAN MAIL → ファンレター送付先ページ（template-fanletter.php）
 *
 *  $cards[$key] の値:
 *    - 'press' / 'casting' / 'other' → お問い合わせフォームに ?type= を付与
 *    - 'fanletter'                   → ファンレター専用ページ（住所表示）
 */
$cards = array(
    'fanclub' => 'press',
    'press'   => 'press',
    'fanmail' => 'fanletter',
);

// 既定のお問い合わせページ URL（template-contact.php を割り当てた固定ページ）
$contact_base = home_url( '/contact/' );
$contact_pages = get_posts( array(
    'post_type'      => 'page',
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'page-templates/template-contact.php',
    'posts_per_page' => 1,
    'fields'         => 'ids',
) );
if ( ! empty( $contact_pages ) ) {
    $contact_base = get_permalink( $contact_pages[0] );
}

// ファンレターページ URL（template-fanletter.php を割り当てた固定ページ）
$fanletter_base = home_url( '/fanletter/' );
$fanletter_pages = get_posts( array(
    'post_type'      => 'page',
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'page-templates/template-fanletter.php',
    'posts_per_page' => 1,
    'fields'         => 'ids',
) );
if ( ! empty( $fanletter_pages ) ) {
    $fanletter_base = get_permalink( $fanletter_pages[0] );
}

// サイト自身のホスト名（外部 URL 判定に使用）
$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
?>

<section class="section contact" id="contact">
  <div class="section__head">
    <span class="section__num">07 / Contact</span>
    <h2 class="section__title">Contact<span class="section__title-jp">お問い合わせ</span></h2>
  </div>

  <div class="contact__grid">
    <?php foreach ( $cards as $key => $form_type ) :
      $label    = get_theme_mod( 'reirie_contact_' . $key . '_label' );
      $title    = get_theme_mod( 'reirie_contact_' . $key . '_title' );
      $desc     = get_theme_mod( 'reirie_contact_' . $key . '_desc' );
      $url_raw  = trim( (string) get_theme_mod( 'reirie_contact_' . $key . '_url', '' ) );

      // === リンク先と target を決定 ===
      $is_external = false;
      $final_url   = '';

      if ( $url_raw === '' || $url_raw === '#' || strtolower( $url_raw ) === 'default' ) {
          // 既定の遷移先を決定
          if ( $form_type === 'fanletter' ) {
              // ファンレター送付先ページへ
              $final_url = $fanletter_base;
          } else {
              // お問い合わせフォームに form_type を付けて遷移
              $final_url = add_query_arg( 'type', $form_type, $contact_base );
          }
      } elseif ( preg_match( '#^https?://#i', $url_raw ) ) {
          // 絶対 URL — サイト自身のホスト以外なら外部扱い
          $url_host = wp_parse_url( $url_raw, PHP_URL_HOST );
          if ( $url_host && $site_host && strcasecmp( $url_host, $site_host ) !== 0 ) {
              $is_external = true;
          }
          $final_url = $url_raw;
      } else {
          // 相対パス（/page/ など）や WP 内部リンクとして扱う
          $final_url = $url_raw;
      }
    ?>
      <a href="<?php echo esc_url( $final_url ); ?>"
         class="contact__card<?php echo $is_external ? ' is-external' : ''; ?>"
         data-form-type="<?php echo esc_attr( $form_type ); ?>"
         <?php if ( $is_external ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
        <span class="contact__label"><?php echo esc_html( $label ); ?></span>
        <h3><?php echo esc_html( $title ); ?></h3>
        <p><?php echo esc_html( $desc ); ?></p>
        <span class="arrow"><?php echo $is_external ? '↗' : '→'; ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

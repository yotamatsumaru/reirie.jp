<?php
/**
 * Footer テンプレート
 *
 * @package REIRIE
 */
?>

<footer class="footer">
  <div class="footer__inner">
    <div class="footer__logo">
      <img src="<?php echo esc_url( REIRIE_URI . '/assets/img/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height:60px;width:auto;display:inline-block;">
      <p><?php echo esc_html( get_theme_mod( 'reirie_footer_subtitle', '2-girls IDOL UNIT' ) ); ?></p>
    </div>

    <ul class="footer__sns">
      <?php foreach ( reirie_get_sns_links() as $sns ) : ?>
        <?php if ( ! empty( $sns['url'] ) ) : ?>
          <li><a href="<?php echo esc_url( $sns['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sns['label'] ); ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>

    <?php if ( has_nav_menu( 'footer' ) ) : ?>
      <?php
        wp_nav_menu( array(
          'theme_location' => 'footer',
          'menu_class'     => 'footer__links',
          'container'      => false,
          'depth'          => 1,
        ) );
      ?>
    <?php else : ?>
      <ul class="footer__links">
        <li><a href="<?php echo esc_url( reirie_legal_page_url( 'privacy' ) ); ?>">プライバシーポリシー</a></li>
        <li><a href="<?php echo esc_url( reirie_legal_page_url( 'tokushoho' ) ); ?>">特定商取引法に基づく表示</a></li>
        <li><a href="<?php echo esc_url( reirie_legal_page_url( 'company' ) ); ?>">運営会社</a></li>
      </ul>
    <?php endif; ?>

    <p class="footer__copy"><?php echo esc_html( get_theme_mod( 'reirie_footer_copy', '© ' . date( 'Y' ) . ' REIRIE OFFICIAL. All Rights Reserved.' ) ); ?></p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

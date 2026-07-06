<?php
/**
 * 404 Not Found
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section" style="text-align:center;min-height:60vh;display:flex;flex-direction:column;justify-content:center;">
  <div class="section__head">
    <span class="section__num">404 / Not Found</span>
    <h2 class="section__title" style="font-size:clamp(48px,10vw,120px);">404</h2>
    <p style="margin-top:18px;letter-spacing:.15em;">お探しのページは見つかりませんでした。</p>
  </div>
  <div class="section__more">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO TOP</span></a>
  </div>
</main>

<?php get_footer(); ?>

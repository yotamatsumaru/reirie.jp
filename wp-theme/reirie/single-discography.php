<?php
/**
 * Single Discography Template
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section single-disco">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    $cat        = reirie_field( 'disco_category', false, '' );
    $release    = reirie_field( 'disco_release_date' );
    $price      = reirie_field( 'disco_price' );
    $tracks_raw = reirie_field( 'disco_tracks' );
    $tracks     = $tracks_raw ? preg_split( '/\r\n|\r|\n/', $tracks_raw ) : array();
    $is_new     = reirie_field( 'disco_is_new' );
    $thumb      = get_the_post_thumbnail_url( get_the_ID(), 'large' );

    $links = array(
      'buy'     => array( 'url' => reirie_field( 'disco_buy_url' ),     'label' => 'BUY',      'icon' => '🛒', 'class' => 'btn-buy' ),
      'apple'   => array( 'url' => reirie_field( 'disco_apple_url' ),   'label' => 'Apple Music', 'icon' => '🍎', 'class' => 'btn-apple' ),
      'spotify' => array( 'url' => reirie_field( 'disco_spotify_url' ), 'label' => 'Spotify',  'icon' => '🟢', 'class' => 'btn-spotify' ),
      'youtube' => array( 'url' => reirie_field( 'disco_youtube_url' ), 'label' => 'YT Music', 'icon' => '▶',  'class' => 'btn-youtube' ),
      'linkco'  => array( 'url' => reirie_field( 'disco_linkco_url' ),  'label' => 'LISTEN',   'icon' => '🔗', 'class' => 'btn-linkco' ),
    );
    $has_links = false;
    foreach ( $links as $l ) { if ( ! empty( $l['url'] ) ) { $has_links = true; break; } }
  ?>

    <article class="single-disco__article">

      <div class="section__head">
        <span class="section__num">DISCOGRAPHY</span>
        <h2 class="section__title single-disco__title"><?php the_title(); ?></h2>
        <?php if ( $cat ) : ?>
          <p class="single-disco__cat"><?php echo esc_html( $cat ); ?></p>
        <?php endif; ?>
      </div>

      <div class="single-disco__main">

        <!-- Jacket -->
        <div class="single-disco__jacket">
          <?php if ( $thumb ) : ?>
            <div class="single-disco__jacket-img" style="background-image:url('<?php echo esc_url( $thumb ); ?>');"></div>
          <?php else : ?>
            <div class="jacket-art jacket-1 single-disco__jacket-art">
              <span class="jacket-title"><?php the_title(); ?></span>
            </div>
          <?php endif; ?>
          <?php if ( $is_new ) : ?><span class="disco__badge single-disco__badge">NEW</span><?php endif; ?>
        </div>

        <!-- Info -->
        <div class="single-disco__info">

          <?php if ( $release ) : ?>
            <p class="single-disco__release"><?php echo esc_html( reirie_format_date( $release ) ); ?> RELEASE</p>
          <?php endif; ?>

          <?php if ( $price ) : ?>
            <p class="single-disco__price"><?php echo esc_html( $price ); ?></p>
          <?php endif; ?>

          <?php if ( get_the_content() ) : ?>
            <div class="single-disco__desc"><?php the_content(); ?></div>
          <?php endif; ?>

          <?php if ( ! empty( $tracks ) ) : ?>
            <div class="single-disco__tracks">
              <h3 class="single-disco__sub-head">TRACK LIST</h3>
              <ul class="single-disco__track-list">
                <?php foreach ( $tracks as $t ) : if ( trim( $t ) === '' ) continue; ?>
                  <li><?php echo esc_html( $t ); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if ( $has_links ) : ?>
            <div class="single-disco__buttons">
              <h3 class="single-disco__sub-head">BUY &amp; STREAMING</h3>
              <div class="disco__links">
                <?php foreach ( $links as $key => $l ) : if ( empty( $l['url'] ) ) continue; ?>
                  <a href="<?php echo esc_url( $l['url'] ); ?>" class="disco__link <?php echo esc_attr( $l['class'] ); ?>" target="_blank" rel="noopener">
                    <span class="ico"><?php echo esc_html( $l['icon'] ); ?></span>
                    <span><?php echo esc_html( $l['label'] ); ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <div class="single-disco__back">
        <a href="<?php echo esc_url( home_url( '/#discography' ) ); ?>" class="more-btn"><span>BACK TO DISCOGRAPHY</span></a>
      </div>

    </article>

  <?php endwhile; endif; ?>
</main>

<style>
/* ============ Single Discography 専用スタイル ============ */
.single-disco__article {
  max-width: 1080px;
  margin: 0 auto;
  padding: 0 20px;
}
.single-disco__title { font-size: clamp(32px, 5vw, 56px); }
.single-disco__cat {
  margin-top: 8px;
  font-family: var(--serif);
  letter-spacing: .25em;
  color: var(--pink-deep);
  font-size: 13px;
  text-transform: uppercase;
}
.single-disco__main {
  display: grid;
  grid-template-columns: minmax(280px, 420px) 1fr;
  gap: 48px;
  align-items: start;
  margin-top: 48px;
}
.single-disco__jacket {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 18px 48px rgba(255,126,182,.28);
  aspect-ratio: 1/1;
  width: 100%;
}
.single-disco__jacket-img {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}
.single-disco__jacket-art { height: 100%; }
.single-disco__badge { top: 16px; right: 16px; }
.single-disco__release {
  font-family: var(--serif);
  font-size: 14px;
  letter-spacing: .25em;
  color: #888;
  margin-bottom: 6px;
}
.single-disco__price {
  font-family: var(--serif);
  font-size: 15px;
  color: var(--pink-deep);
  margin-bottom: 18px;
}
.single-disco__desc {
  font-size: 15px;
  line-height: 1.95;
  margin-bottom: 24px;
  color: #444;
}
.single-disco__tracks { margin-bottom: 28px; }
.single-disco__sub-head {
  font-family: var(--serif);
  font-size: 14px;
  letter-spacing: .25em;
  color: var(--pink-deep);
  margin-bottom: 12px;
  border-bottom: 1px dashed rgba(255,126,182,.4);
  padding-bottom: 8px;
}
.single-disco__buttons .single-disco__sub-head { border-bottom: none; padding-bottom: 0; }
.single-disco__track-list {
  list-style: none;
  padding: 0; margin: 0;
  font-size: 15px;
  line-height: 1.9;
}
.single-disco__track-list li {
  padding: 6px 0;
  border-bottom: 1px dotted rgba(255,126,182,.18);
}
.disco__links { display: flex; flex-wrap: wrap; gap: 10px; }
.single-disco__back { text-align: center; margin-top: 60px; }

/* ============ モバイル (≤768px) ============ */
@media (max-width: 768px) {
  .single-disco__article { padding: 0 16px; }
  .single-disco__main {
    grid-template-columns: 1fr;          /* !important 不要（インライン除去済み） */
    gap: 24px;
    margin-top: 28px;
  }
  .single-disco__jacket {
    max-width: 360px;
    width: 100%;
    margin: 0 auto;
    /* aspect-ratio が効かない古いSafari保険 */
    min-height: 280px;
  }
  .single-disco__release { font-size: 12px; margin-bottom: 4px; }
  .single-disco__price   { font-size: 14px; }
  .single-disco__desc    { font-size: 14px; line-height: 1.8; }
  .single-disco__sub-head { font-size: 12px; }
  .single-disco__back { margin-top: 36px; }
}
@media (max-width: 480px) {
  .single-disco__jacket { max-width: 320px; }
}
</style>

<?php get_footer(); ?>

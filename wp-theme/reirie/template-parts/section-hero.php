<?php
/**
 * Hero Section
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$hero_bg_type   = get_theme_mod( 'reirie_hero_bg_type', 'video' );
$hero_video     = get_theme_mod( 'reirie_hero_video', REIRIE_URI . '/assets/video/hero.mp4' );
$hero_image     = get_theme_mod( 'reirie_hero_image', '' );
$hero_overlay   = (int) get_theme_mod( 'reirie_hero_overlay', 30 );
$hero_sub       = get_theme_mod( 'reirie_hero_sub', '2-girls IDOL UNIT' );
$hero_title     = get_theme_mod( 'reirie_hero_title', 'REIRIE' );
$hero_title_jp  = get_theme_mod( 'reirie_hero_title_jp', 'レイリエ' );
$hero_catch     = get_theme_mod( 'reirie_hero_catch', 'ふたりで描く、きらめきの世界。' );
$cta1_label     = get_theme_mod( 'reirie_hero_cta1_label', '' );
$cta1_url       = get_theme_mod( 'reirie_hero_cta1_url', '' );
$cta2_label     = get_theme_mod( 'reirie_hero_cta2_label', '' );
$cta2_url       = get_theme_mod( 'reirie_hero_cta2_url', '' );
$marquee_text   = get_theme_mod( 'reirie_marquee_text', '★ REIRIE OFFICIAL SITE ★ NEW SINGLE OUT NOW ★ 1st ONE-MAN LIVE 2026.07.20 ★' );
$marquee_show   = (int) get_theme_mod( 'reirie_marquee_show', 1 );
$marquee_speed  = (int) get_theme_mod( 'reirie_marquee_speed', 30 ); // 秒数（小さいほど速い）
$marquee_speed  = max( 5, min( 120, $marquee_speed ) ); // 5〜120秒に制限
$overlay_alpha  = max( 0, min( 80, $hero_overlay ) ) / 100;
?>

<section class="hero" id="hero">
  <div class="hero__video-wrap">
    <?php if ( $hero_bg_type === 'image' && $hero_image ) : ?>
      <div class="hero__image" style="background-image:url('<?php echo esc_url( $hero_image ); ?>');background-size:cover;background-position:center;width:100%;height:100%;position:absolute;inset:0;"></div>
    <?php else :
      // 動画 URL（PC / Mobile）
      $hero_video_pc      = $hero_video; // 既存（カスタマイザで上書き可）
      // モバイル版は同名 -mobile.mp4 があればそれを使う、なければ PC と同じ
      $hero_video_mobile  = REIRIE_URI . '/assets/video/hero-mobile.mp4';
      if ( strpos( $hero_video_pc, REIRIE_URI ) === false ) {
        // カスタマイザでアップロードされた場合はそれをモバイルにも流用
        $hero_video_mobile = $hero_video_pc;
      }
      // ポスター画像（実物）
      $hero_poster_webp = REIRIE_URI . '/assets/img/hero-poster.webp';
      $hero_poster_jpg  = REIRIE_URI . '/assets/img/hero-poster.jpg';
      $poster_url       = $hero_image ? esc_url( $hero_image ) : $hero_poster_jpg;
    ?>
      <!-- ポスター画像（瞬時に表示） -->
      <picture class="hero__poster" aria-hidden="true">
        <?php if ( ! $hero_image ) : ?>
          <source srcset="<?php echo esc_url( $hero_poster_webp ); ?>" type="image/webp">
        <?php endif; ?>
        <img src="<?php echo esc_url( $poster_url ); ?>"
             alt=""
             width="1280" height="720"
             decoding="async"
             fetchpriority="high">
      </picture>

      <!-- ヒーロー動画（JS で遅延読み込み、再生開始したらポスター非表示） -->
      <!-- Safari対策: autoplay/muted/playsinline をHTML属性として明示。preload="metadata" でメタデータ先読み -->
      <video
        class="hero__video"
        autoplay
        muted
        loop
        playsinline
        webkit-playsinline="true"
        x5-playsinline="true"
        preload="metadata"
        disablepictureinpicture
        disableremoteplayback
        poster="<?php echo esc_url( $poster_url ); ?>"
        data-src-pc="<?php echo esc_url( $hero_video_pc ); ?>"
        data-src-mobile="<?php echo esc_url( $hero_video_mobile ); ?>"
        aria-hidden="true"
        tabindex="-1">
      </video>
    <?php endif; ?>
    <div class="hero__overlay" style="background:rgba(20,10,30,<?php echo esc_attr( $overlay_alpha ); ?>);"></div>
    <div class="hero__grain"></div>
  </div>

  <div class="hero__content">
    <p class="hero__sub"><?php echo esc_html( $hero_sub ); ?></p>
    <h1 class="hero__title">
      <span class="hero__title-main"><?php echo esc_html( $hero_title ); ?></span>
      <span class="hero__title-jp"><?php echo esc_html( $hero_title_jp ); ?></span>
    </h1>
    <?php if ( ! empty( trim( $hero_catch ) ) ) : ?>
      <p class="hero__catch">
        <span class="hero__catch-quote">“</span>
        <?php echo esc_html( $hero_catch ); ?>
        <span class="hero__catch-quote">”</span>
      </p>
    <?php endif; ?>

    <?php if ( $cta1_label || $cta2_label ) : ?>
      <div class="hero__cta">
        <?php if ( $cta1_label ) : ?>
          <a class="hero__btn hero__btn--primary" href="<?php echo esc_url( $cta1_url ?: '#' ); ?>"><?php echo esc_html( $cta1_label ); ?></a>
        <?php endif; ?>
        <?php if ( $cta2_label ) : ?>
          <a class="hero__btn hero__btn--ghost" href="<?php echo esc_url( $cta2_url ?: '#' ); ?>"><?php echo esc_html( $cta2_label ); ?></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php // SCROLL は .hero 直下に配置（.hero__content の外）でロゴと重ならないようにする ?>
  <a href="#news" class="hero__scroll">
    <span class="hero__scroll-text">SCROLL</span>
    <span class="hero__scroll-line"></span>
  </a>

  <?php if ( $marquee_show && $marquee_text ) : ?>
    <div class="hero__marquee">
      <?php
        // シームレスループのため、テキストを繰り返してから2グループに分ける
        // 短いテキストでも画面幅を超えるよう repeat する
        $unit = esc_html( $marquee_text ) . '　・　';
        $group = str_repeat( $unit, 4 ); // 1グループ = 4回繰り返し
      ?>
      <div class="marquee-track" style="animation-duration: <?php echo esc_attr( $marquee_speed ); ?>s;">
        <div class="marquee-group" aria-hidden="false"><?php echo $group; ?></div>
        <div class="marquee-group" aria-hidden="true"><?php echo $group; ?></div>
      </div>
    </div>
  <?php endif; ?>
</section>

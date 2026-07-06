<?php
/**
 * Discography Section
 *
 * - 最新1作: フィーチャー(大)
 * - 2-5作目: 2列グリッド(コンパクト)
 * - 6作目以降: VIEW ALL ボタンで /discography/ アーカイブへ
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$disco_query = new WP_Query( array(
	'post_type'      => 'discography',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'meta_key'       => 'disco_release_date',
	'orderby'        => 'meta_value',
	'order'          => 'DESC',
) );

// アーカイブへ遷移するため、6作以上あるかも確認
$disco_total = wp_count_posts( 'discography' );
$disco_total_published = isset( $disco_total->publish ) ? (int) $disco_total->publish : 0;
?>

<section class="section discography" id="discography">
  <div class="section__head">
    <span class="section__num">03 / Discography</span>
    <h2 class="section__title">Discography<span class="section__title-jp">作品</span></h2>
  </div>

  <?php if ( $disco_query->have_posts() ) :
    $items = array();
    while ( $disco_query->have_posts() ) : $disco_query->the_post();
      $items[] = array(
        'id'        => get_the_ID(),
        'title'     => get_the_title(),
        'permalink' => get_permalink(),
        'cat'       => reirie_field( 'disco_category', false, '' ),
        'release'   => reirie_field( 'disco_release_date' ),
        'price'     => reirie_field( 'disco_price' ),
        'is_new'    => reirie_field( 'disco_is_new' ),
        'thumb'     => get_the_post_thumbnail_url( get_the_ID(), 'reirie-jacket' ),
      );
    endwhile;
    wp_reset_postdata();

    $featured = array_shift( $items );  // 最新 1 作
    $rest     = $items;                  // 残り最大 2 作
  ?>

    <!-- ===== Discography レイアウト: 左に大サイズ、右に2作積み重ね ===== -->
    <div class="disco-layout">

      <!-- ===== Featured (最新作) — ジャケット上に情報オーバーレイ ===== -->
      <article class="disco-featured">
        <a class="disco-featured__link" href="<?php echo esc_url( $featured['permalink'] ); ?>" aria-label="<?php echo esc_attr( $featured['title'] ); ?> の詳細を見る">
          <div class="disco-featured__jacket">
            <?php if ( $featured['thumb'] ) : ?>
              <div class="jacket-art" style="background-image:url('<?php echo esc_url( $featured['thumb'] ); ?>');"></div>
            <?php else : ?>
              <div class="jacket-art jacket-1"><span class="jacket-title"><?php echo esc_html( $featured['title'] ); ?></span></div>
            <?php endif; ?>
            <?php if ( $featured['is_new'] ) : ?><span class="disco__badge disco__badge--lg">NEW</span><?php endif; ?>

            <!-- 画像上オーバーレイ: カテゴリ + 日付 + タイトル -->
            <div class="disco-featured__overlay">
              <div class="disco-featured__overlay-top">
                <?php if ( $featured['cat'] ) : ?>
                  <span class="disco__cat"><?php echo esc_html( $featured['cat'] ); ?></span>
                <?php endif; ?>
                <?php if ( $featured['release'] ) : ?>
                  <span class="disco-featured__date"><?php echo esc_html( reirie_format_date( $featured['release'] ) ); ?> RELEASE</span>
                <?php endif; ?>
              </div>
              <h3 class="disco-featured__title"><?php echo esc_html( $featured['title'] ); ?></h3>
            </div>
          </div>
        </a>
      </article>

      <!-- ===== 2-3 作目 (横長カード 縦積み) ===== -->
      <?php if ( ! empty( $rest ) ) : ?>
        <div class="disco-sub">
          <?php foreach ( $rest as $item ) : ?>
            <article class="disco-card">
              <a class="disco-card__link" href="<?php echo esc_url( $item['permalink'] ); ?>" aria-label="<?php echo esc_attr( $item['title'] ); ?> の詳細を見る">
                <div class="disco-card__jacket">
                  <?php if ( $item['thumb'] ) : ?>
                    <div class="jacket-art" style="background-image:url('<?php echo esc_url( $item['thumb'] ); ?>');"></div>
                  <?php else : ?>
                    <div class="jacket-art jacket-2"><span class="jacket-title"><?php echo esc_html( $item['title'] ); ?></span></div>
                  <?php endif; ?>
                  <?php if ( $item['is_new'] ) : ?><span class="disco__badge">NEW</span><?php endif; ?>
                </div>
                <div class="disco-card__info">
                  <div class="disco-card__meta">
                    <?php if ( $item['cat'] ) : ?>
                      <span class="disco__cat"><?php echo esc_html( $item['cat'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $item['release'] ) : ?>
                      <span class="disco-card__date"><?php echo esc_html( reirie_format_date( $item['release'] ) ); ?> RELEASE</span>
                    <?php endif; ?>
                  </div>
                  <h3 class="disco-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div><!-- /.disco-layout -->

  <?php else : ?>

    <!-- フォールバック（投稿0件時のダミー） -->
    <article class="disco-featured">
      <div class="disco-featured__link">
        <div class="disco-featured__jacket">
          <div class="jacket-art jacket-1"><span class="jacket-title">Coming<br>Soon</span></div>
        </div>
        <div class="disco-featured__info">
          <span class="disco__cat disco__cat--lg">DEBUT</span>
          <h3 class="disco-featured__title">Coming Soon</h3>
          <p class="disco-featured__date">2026.XX.XX RELEASE</p>
        </div>
      </div>
    </article>

  <?php endif; ?>

  <div class="section__more">
    <a href="<?php echo esc_url( get_post_type_archive_link( 'discography' ) ); ?>" class="more-btn"><span>VIEW ALL</span></a>
  </div>
</section>

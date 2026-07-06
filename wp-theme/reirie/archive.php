<?php
/**
 * アーカイブテンプレート（カスタム投稿タイプ一覧）
 *
 * @package REIRIE
 */

get_header();

$post_type = get_post_type();
$labels = array(
	'news'        => array( 'num' => '01 / News',        'title' => 'News',        'jp' => 'お知らせ' ),
	'schedule'    => array( 'num' => '02 / Schedule',    'title' => 'Schedule',    'jp' => 'スケジュール' ),
	'discography' => array( 'num' => '03 / Discography', 'title' => 'Discography', 'jp' => '作品' ),
	'movie'       => array( 'num' => '04 / Movie',       'title' => 'Movie',       'jp' => '動画' ),
	'goods'       => array( 'num' => '06 / Goods',       'title' => 'Goods',       'jp' => 'グッズ' ),
);
$label = isset( $labels[ $post_type ] ) ? $labels[ $post_type ] : array( 'num' => '—', 'title' => 'Archive', 'jp' => 'アーカイブ' );
?>

<main class="section">
  <div class="section__head">
    <span class="section__num"><?php echo esc_html( $label['num'] ); ?></span>
    <h2 class="section__title"><?php echo esc_html( $label['title'] ); ?><span class="section__title-jp"><?php echo esc_html( $label['jp'] ); ?></span></h2>
  </div>

  <?php if ( have_posts() ) : ?>

    <?php if ( $post_type === 'news' ) : ?>
      <div class="news__list" style="max-width:980px;margin:0 auto;border-top:1px solid rgba(255,126,182,.25);">
        <?php while ( have_posts() ) : the_post();
          $date_meta = reirie_field( 'news_date' );
          $display_date = $date_meta ? reirie_format_datetime( $date_meta ) : get_the_date( 'Y.m.d' );
          $is_scheduled = function_exists( 'reirie_is_scheduled' ) && reirie_is_scheduled();
        ?>
          <article class="news__item<?php echo $is_scheduled ? ' is-scheduled' : ''; ?>">
            <a href="<?php the_permalink(); ?>" class="news__link">
              <span class="news__date"><?php echo esc_html( $display_date ); ?></span>
              <span class="news__cat"><?php echo esc_html( reirie_get_news_category() ); ?></span>
              <span class="news__title"><?php the_title(); ?><?php if ( $is_scheduled ) : ?> <span class="news__scheduled-badge">公開予定</span><?php endif; ?></span>
            </a>
          </article>
        <?php endwhile; ?>
      </div>

    <?php elseif ( $post_type === 'discography' || $post_type === 'movie' ) : ?>
      <div class="<?php echo $post_type === 'discography' ? 'disco__grid' : 'movie__grid'; ?>">
        <?php $i = 0; while ( have_posts() ) : the_post(); ?>
          <?php if ( $post_type === 'discography' ) : ?>
            <?php
              $cat = reirie_field( 'disco_category' );
              $release = reirie_field( 'disco_release_date' );
              $thumb = get_the_post_thumbnail_url( get_the_ID(), 'reirie-jacket' );
            ?>
            <article class="disco__item">
              <div class="disco__jacket">
                <?php if ( $thumb ) : ?>
                  <div class="jacket-art" style="background-image:url('<?php echo esc_url( $thumb ); ?>');background-size:cover;background-position:center;"></div>
                <?php else : ?>
                  <div class="jacket-art jacket-<?php echo esc_attr( ( $i % 3 ) + 1 ); ?>"><span class="jacket-title"><?php the_title(); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="disco__info">
                <?php if ( $cat ) : ?><span class="disco__cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
                <h3 class="disco__title"><?php the_title(); ?></h3>
                <?php if ( $release ) : ?><p class="disco__date"><?php echo esc_html( reirie_format_date( $release ) ); ?> RELEASE</p><?php endif; ?>
              </div>
            </article>
          <?php else : ?>
            <?php $thumb = get_the_post_thumbnail_url( get_the_ID(), 'reirie-thumb-16-9' ); ?>
            <article class="movie__item">
              <a href="<?php the_permalink(); ?>" class="movie__link">
                <?php if ( $thumb ) : ?>
                  <div class="movie__thumb" style="background-image:url('<?php echo esc_url( $thumb ); ?>');background-size:cover;background-position:center;"><span class="play-icon">▶</span></div>
                <?php else : ?>
                  <div class="movie__thumb thumb-<?php echo esc_attr( ( $i % 6 ) + 1 ); ?>"><span class="play-icon">▶</span></div>
                <?php endif; ?>
                <p class="movie__title"><?php the_title(); ?></p>
              </a>
            </article>
          <?php endif; ?>
        <?php $i++; endwhile; ?>
      </div>

    <?php elseif ( $post_type === 'goods' ) : ?>
      <div class="goods__grid">
        <?php $i = 0; while ( have_posts() ) : the_post();
          $price = reirie_field( 'goods_price' );
          $thumb = get_the_post_thumbnail_url( get_the_ID(), 'reirie-jacket' );
        ?>
          <article class="goods__item">
            <a href="<?php the_permalink(); ?>">
              <?php if ( $thumb ) : ?>
                <div class="goods__img" style="background-image:url('<?php echo esc_url( $thumb ); ?>');background-size:cover;background-position:center;"></div>
              <?php else : ?>
                <div class="goods__img goods-<?php echo esc_attr( ( $i % 4 ) + 1 ); ?>"></div>
              <?php endif; ?>
              <p class="goods__name"><?php the_title(); ?></p>
              <?php if ( $price ) : ?><p class="goods__price"><?php echo esc_html( $price ); ?></p><?php endif; ?>
            </a>
          </article>
        <?php $i++; endwhile; ?>
      </div>

    <?php else : /* schedule など */ ?>
      <div class="schedule__list">
        <?php while ( have_posts() ) : the_post();
          $date_str = reirie_field( 'schedule_date' );
          $venue    = reirie_field( 'schedule_venue' );
          $time     = reirie_field( 'schedule_time' );
          $highlight = reirie_field( 'schedule_highlight' );
          $ts = $date_str ? strtotime( $date_str ) : 0;
        ?>
          <article class="schedule__item<?php echo $highlight ? ' highlight' : ''; ?>">
            <div class="schedule__date">
              <span class="day"><?php echo $ts ? date( 'd', $ts ) : '--'; ?></span>
              <span class="month"><?php echo $ts ? strtoupper( date( 'M Y', $ts ) ) : ''; ?></span>
              <span class="weekday"><?php echo $ts ? strtoupper( date( 'D', $ts ) ) : ''; ?></span>
            </div>
            <div class="schedule__body">
              <?php $sched_cat = reirie_get_schedule_category(); if ( $sched_cat ) : ?>
                <span class="schedule__cat"><?php echo esc_html( $sched_cat ); ?></span>
              <?php endif; ?>
              <h3 class="schedule__title"><?php the_title(); ?></h3>
              <p class="schedule__meta">
                <?php if ( $venue ) : ?><span>📍 <?php echo esc_html( $venue ); ?></span><?php endif; ?>
                <?php if ( $time ) : ?><span>🕐 <?php echo esc_html( $time ); ?></span><?php endif; ?>
              </p>
            </div>
            <a href="<?php the_permalink(); ?>" class="schedule__btn">Detail</a>
          </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

    <div class="section__more">
      <?php
        the_posts_pagination( array(
          'prev_text' => '← 前へ',
          'next_text' => '次へ →',
        ) );
      ?>
    </div>

  <?php else : ?>
    <p style="text-align:center;padding:60px 20px;">該当する記事はまだありません。</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>

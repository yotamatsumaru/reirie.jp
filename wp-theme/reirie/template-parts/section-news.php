<?php
/**
 * News Section — サムネイル横スクロール + シンプルリストの併用型
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* 管理者ログイン時は 'future'（公開予定）も含める */
$reirie_news_statuses = function_exists( 'reirie_front_post_status' ) ? reirie_front_post_status() : array( 'publish' );

/* 横スクロール用（最新6件、サムネ重視） */
$news_carousel = new WP_Query( array(
	'post_type'      => 'news',
	'posts_per_page' => 6,
	'post_status'    => $reirie_news_statuses,
) );

/* リスト用（最新5件、テキスト重視） */
$news_list = new WP_Query( array(
	'post_type'      => 'news',
	'posts_per_page' => 5,
	'post_status'    => $reirie_news_statuses,
) );

/* NEWバッジ判定: 公開から14日以内 */
function reirie_is_new_post( $post_id ) {
	$days = ( time() - get_post_time( 'U', false, $post_id ) ) / DAY_IN_SECONDS;
	return $days <= 14;
}
?>

<section class="section news" id="news">
  <div class="section__head">
    <span class="section__num">01 / News</span>
    <h2 class="section__title">News<span class="section__title-jp">お知らせ</span></h2>
  </div>

  <?php /* ============ A) サムネイル横スクロール ============ */ ?>
  <div class="news-carousel" data-news-carousel>
    <div class="news-carousel__head">
      <h3 class="news-carousel__label">PICK UP</h3>
      <div class="news-carousel__controls">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'news' ) ); ?>" class="news-carousel__viewall">
          <span>VIEW ALL</span>
          <svg width="18" height="10" viewBox="0 0 18 10" fill="none" aria-hidden="true">
            <path d="M1 5h15M12 1l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
        <button type="button" class="news-carousel__btn" data-news-prev aria-label="前へ">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M9 1L3 7l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button type="button" class="news-carousel__btn" data-news-next aria-label="次へ">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M5 1l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="news-carousel__track" data-news-track>
      <?php if ( $news_carousel->have_posts() ) : while ( $news_carousel->have_posts() ) : $news_carousel->the_post();
        $date_meta    = reirie_field( 'news_date' );
        $display_date = $date_meta ? reirie_format_datetime( $date_meta ) : get_the_date( 'Y.m.d' );
        $external_link = reirie_field( 'news_link' );
        $link         = $external_link ? $external_link : get_permalink();
        $is_new       = reirie_is_new_post( get_the_ID() );
        $is_scheduled = function_exists( 'reirie_is_scheduled' ) && reirie_is_scheduled();
        $thumb_url    = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
      ?>
        <article class="news-card<?php echo $is_scheduled ? ' is-scheduled' : ''; ?>">
          <a href="<?php echo esc_url( $link ); ?>"<?php echo $external_link ? ' target="_blank" rel="noopener"' : ''; ?> class="news-card__link">
            <div class="news-card__thumb">
              <?php if ( $thumb_url ) : ?>
                <span class="news-card__img" style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');"></span>
              <?php else : ?>
                <span class="news-card__img news-card__img--placeholder" aria-hidden="true"></span>
              <?php endif; ?>
              <?php if ( $is_scheduled ) : ?>
                <span class="news-card__scheduled" title="公開予定（管理者プレビュー）">公開予定</span>
              <?php elseif ( $is_new ) : ?>
                <span class="news-card__new">NEW</span>
              <?php endif; ?>
            </div>
            <div class="news-card__body">
              <span class="news-card__date"><?php echo esc_html( $display_date ); ?></span>
              <h4 class="news-card__title"><?php the_title(); ?></h4>
            </div>
          </a>
        </article>
      <?php endwhile; wp_reset_postdata(); else : ?>

        <?php /* デモ用フォールバック */ ?>
        <article class="news-card">
          <a href="#" class="news-card__link">
            <div class="news-card__thumb">
              <span class="news-card__img news-card__img--placeholder" aria-hidden="true"></span>
              <span class="news-card__new">NEW</span>
            </div>
            <div class="news-card__body">
              <span class="news-card__date">2026.06.12</span>
              <h4 class="news-card__title">REIRIE 第2期メンバー募集のお知らせ！</h4>
            </div>
          </a>
        </article>
        <article class="news-card">
          <a href="#" class="news-card__link">
            <div class="news-card__thumb">
              <span class="news-card__img news-card__img--placeholder" aria-hidden="true"></span>
              <span class="news-card__new">NEW</span>
            </div>
            <div class="news-card__body">
              <span class="news-card__date">2026.06.12</span>
              <h4 class="news-card__title">【新衣装お披露目のお知らせ】</h4>
            </div>
          </a>
        </article>
        <article class="news-card">
          <a href="#" class="news-card__link">
            <div class="news-card__thumb">
              <span class="news-card__img news-card__img--placeholder" aria-hidden="true"></span>
            </div>
            <div class="news-card__body">
              <span class="news-card__date">2026.05.20</span>
              <h4 class="news-card__title">REIRIE 全国ツアー2026「おいでよ！みらいで♡！」開催決定</h4>
            </div>
          </a>
        </article>
        <article class="news-card">
          <a href="#" class="news-card__link">
            <div class="news-card__thumb">
              <span class="news-card__img news-card__img--placeholder" aria-hidden="true"></span>
            </div>
            <div class="news-card__body">
              <span class="news-card__date">2026.04.29</span>
              <h4 class="news-card__title">ツアー開催・デジタル配信スケジュール決定！</h4>
            </div>
          </a>
        </article>

      <?php endif; ?>
    </div>
  </div>

  <?php /* ============ B) シンプルリスト ============ */ ?>
  <div class="news-list-block">
    <div class="news-list-block__head">
      <h3 class="news-list-block__label">ALL UPDATES</h3>
    </div>
    <div class="news__list">
      <?php if ( $news_list->have_posts() ) : while ( $news_list->have_posts() ) : $news_list->the_post();
        $date_meta    = reirie_field( 'news_date' );
        $display_date = $date_meta ? reirie_format_datetime( $date_meta ) : get_the_date( 'Y.m.d' );
        $external_link = reirie_field( 'news_link' );
        $link         = $external_link ? $external_link : get_permalink();
        $is_limited   = false; // 限定公開フラグ（将来の拡張用）
        $is_scheduled = function_exists( 'reirie_is_scheduled' ) && reirie_is_scheduled();
      ?>
        <article class="news__item<?php echo $is_scheduled ? ' is-scheduled' : ''; ?>">
          <a href="<?php echo esc_url( $link ); ?>"<?php echo $external_link ? ' target="_blank" rel="noopener"' : ''; ?> class="news__link">
            <span class="news__date"><?php echo esc_html( $display_date ); ?></span>
            <span class="news__cat"><?php echo esc_html( reirie_get_news_category() ); ?></span>
            <span class="news__title"><?php the_title(); ?><?php if ( $is_scheduled ) : ?> <span class="news__scheduled-badge">公開予定</span><?php endif; ?></span>
          </a>
        </article>
      <?php endwhile; wp_reset_postdata(); else : ?>

        <?php /* デモ用フォールバック */ ?>
        <article class="news__item"><a href="#" class="news__link">
          <span class="news__date">2026.05.08</span>
          <span class="news__cat">RELEASE</span>
          <span class="news__title">2nd Single「Twinkle Heart」5月20日リリース決定！</span>
        </a></article>
        <article class="news__item"><a href="#" class="news__link">
          <span class="news__date">2026.05.01</span>
          <span class="news__cat">LIVE</span>
          <span class="news__title">1st ONE-MAN LIVE「A Lovely Spring」開催決定のお知らせ</span>
        </a></article>
        <article class="news__item"><a href="#" class="news__link">
          <span class="news__date">2026.04.20</span>
          <span class="news__cat">MEDIA</span>
          <span class="news__title">FM802「Pop'n Roll」にREIRIEの2人がゲスト出演します</span>
        </a></article>

      <?php endif; ?>
    </div>
  </div>

  <div class="section__more">
    <a href="<?php echo esc_url( get_post_type_archive_link( 'news' ) ); ?>" class="more-btn"><span>VIEW ALL NEWS</span></a>
  </div>
</section>

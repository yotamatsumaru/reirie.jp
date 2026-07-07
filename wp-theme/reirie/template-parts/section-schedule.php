<?php
/**
 * Schedule Section - リスト(横スクロール) + カレンダー切替
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ----- リスト用 (今後の予定 12 件まで) -----
$today = date( 'Y-m-d' );
$list_query = new WP_Query( array(
	'post_type'      => 'schedule',
	'posts_per_page' => 12,
	'post_status'    => 'publish',
	'meta_key'       => 'schedule_date',
	'orderby'        => 'meta_value',
	// meta_type => 'DATE' を指定し、SQL側で CAST(... AS DATE) してから比較・並び替えする。
	// これにより「Ymd（8桁, 例:20260711）」と「Y-m-d（例:2026-08-22）」が混在していても、
	// 文字列としての比較（'-' が数字より ASCII 順で小さいため 2026-08-22 が 20260711 より
	// 前に並んでしまう）ではなく、正しい日付の大小関係で並び替えられる。
	'meta_type'      => 'DATE',
	'order'          => 'ASC',
	'meta_query'     => array(
		array(
			'key'     => 'schedule_date',
			'value'   => $today,
			'compare' => '>=',
			'type'    => 'DATE',
		),
	),
) );

// ----- カレンダー初期表示 年月 (Ajax 月送り対応のため描画は inc/ajax-schedule.php に委譲) -----
$cal_year  = isset( $_GET['cal_y'] ) ? (int) $_GET['cal_y'] : (int) date( 'Y' );
$cal_month = isset( $_GET['cal_m'] ) ? (int) $_GET['cal_m'] : (int) date( 'n' );
if ( $cal_month < 1 )  { $cal_month = 12; $cal_year--; }
if ( $cal_month > 12 ) { $cal_month = 1;  $cal_year++; }

// LIST view 用バッジ色判定ヘルパー
// 新カテゴリー (LIVE/EVENT/OTHER) を主軸に、旧互換も維持
$get_badge_class = function( $cat ) {
	$cat = strtoupper( $cat );
	if ( $cat === '' )                                                                            return 'badge--event';
	if ( $cat === 'LIVE'  || strpos( $cat, 'LIVE' ) !== false || strpos( $cat, 'ONE-MAN' ) !== false ) return 'badge--live';
	if ( $cat === 'EVENT' || strpos( $cat, 'EVENT' ) !== false )                                  return 'badge--event';
	if ( $cat === 'OTHER' || strpos( $cat, 'OTHER' ) !== false )                                  return 'badge--other';
	// 互換: 旧 taxonomy 由来のキーワード
	if ( strpos( $cat, 'RELEASE' ) !== false )                                                    return 'badge--release';
	if ( strpos( $cat, 'TV' ) !== false || strpos( $cat, 'RADIO' ) !== false || strpos( $cat, 'MEDIA' ) !== false ) return 'badge--media';
	return 'badge--event';
};

// LIST view 用 曜日略称（英）
$weekday_labels = array( 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT' );
?>

<?php
// カレンダー操作時（?cal_y/?cal_m 付き）は CALENDAR タブを初期表示
$default_view = ( isset( $_GET['cal_y'] ) || isset( $_GET['cal_m'] ) ) ? 'calendar' : 'list';
?>
<section class="section schedule" id="schedule">
  <div class="section__head">
    <span class="section__num">02 / Schedule</span>
    <h2 class="section__title">Schedule<span class="section__title-jp">スケジュール</span></h2>
  </div>

  <!-- ビュー切替タブ -->
  <div class="schedule__tabs" role="tablist">
    <button class="schedule__tab<?php echo $default_view === 'list' ? ' is-active' : ''; ?>" data-view="list" role="tab" aria-selected="<?php echo $default_view === 'list' ? 'true' : 'false'; ?>">
      <span class="schedule__tab-en">LIST</span>
      <span class="schedule__tab-jp">一覧</span>
    </button>
    <button class="schedule__tab<?php echo $default_view === 'calendar' ? ' is-active' : ''; ?>" data-view="calendar" role="tab" aria-selected="<?php echo $default_view === 'calendar' ? 'true' : 'false'; ?>">
      <span class="schedule__tab-en">CALENDAR</span>
      <span class="schedule__tab-jp">カレンダー</span>
    </button>
  </div>

  <!-- ===== LIST VIEW (横スクロールカルーセル) ===== -->
  <div class="schedule__view schedule__view--list<?php echo $default_view === 'list' ? ' is-active' : ''; ?>" data-view="list">
    <?php if ( $list_query->have_posts() ) : ?>
      <div class="schedule-carousel" data-carousel>
        <button class="schedule-carousel__arrow schedule-carousel__arrow--prev" aria-label="前へ" type="button">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <div class="schedule-carousel__track" data-carousel-track>
          <?php while ( $list_query->have_posts() ) : $list_query->the_post();
            $date_str  = reirie_field( 'schedule_date' );
            $venue     = reirie_field( 'schedule_venue' );
            $time      = reirie_field( 'schedule_time' );
            $highlight = reirie_field( 'schedule_highlight' );
            $cat       = reirie_get_schedule_category();
            $badge_cls = $get_badge_class( $cat );

            $ts        = $date_str ? strtotime( $date_str ) : 0;
            $date_fmt  = $ts ? date( 'Y.m.d', $ts ) : '----.--.--';
            $weekday_idx = $ts ? (int) date( 'w', $ts ) : 0;
            $weekday   = $weekday_labels[ $weekday_idx ];

            // NEW判定 (7日以内に投稿)
            $is_new = ( time() - get_the_time( 'U' ) ) < ( 7 * DAY_IN_SECONDS );
          ?>
            <article class="schedule-card<?php echo $highlight ? ' is-highlight' : ''; ?>">
              <a href="<?php the_permalink(); ?>" class="schedule-card__link">
                <div class="schedule-card__top">
                  <span class="schedule-card__date"><?php echo esc_html( $date_fmt ); ?></span>
                  <span class="schedule-card__weekday">[<?php echo esc_html( $weekday ); ?>]</span>
                </div>
                <div class="schedule-card__badges">
                  <?php if ( $cat ) : ?>
                    <span class="schedule-badge <?php echo esc_attr( $badge_cls ); ?>"><?php echo esc_html( $cat ); ?></span>
                  <?php endif; ?>
                  <?php if ( $is_new ) : ?>
                    <span class="schedule-badge badge--new">NEW</span>
                  <?php endif; ?>
                </div>
                <h3 class="schedule-card__title"><?php the_title(); ?></h3>
                <?php if ( $venue || $time ) : ?>
                  <div class="schedule-card__meta">
                    <?php if ( $venue ) : ?><span class="schedule-card__venue"><?php echo esc_html( $venue ); ?></span><?php endif; ?>
                    <?php if ( $time ) : ?><span class="schedule-card__time"><?php echo esc_html( $time ); ?></span><?php endif; ?>
                  </div>
                <?php endif; ?>
              </a>
            </article>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <button class="schedule-carousel__arrow schedule-carousel__arrow--next" aria-label="次へ" type="button">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>
      <div class="schedule-carousel__hint">← スワイプ・ドラッグで切替 →</div>
    <?php else : ?>
      <p class="schedule__empty">現在予定されているスケジュールはありません。</p>
    <?php endif; ?>
  </div>

  <!-- ===== CALENDAR VIEW (Ajax 月送り対応) ===== -->
  <div class="schedule__view schedule__view--calendar<?php echo $default_view === 'calendar' ? ' is-active' : ''; ?>" data-view="calendar">
    <div class="schedule-cal" data-cal-container data-cal-y="<?php echo (int) $cal_year; ?>" data-cal-m="<?php echo (int) $cal_month; ?>">
      <?php echo reirie_render_schedule_calendar( $cal_year, $cal_month ); ?>
    </div>
  </div>

  <div class="section__more">
    <a href="<?php echo esc_url( get_post_type_archive_link( 'schedule' ) ); ?>" class="more-btn"><span>VIEW ALL SCHEDULE</span></a>
  </div>
</section>

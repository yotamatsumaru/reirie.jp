<?php
/**
 * Schedule Calendar Ajax Renderer
 *
 * - reirie_render_schedule_calendar( $year, $month ) でカレンダー HTML を返す
 * - admin-ajax.php?action=reirie_schedule_cal で Ajax 取得可能
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * バッジ色判定
 */
function reirie_schedule_badge_class( $cat ) {
	$cat = strtoupper( $cat );
	if ( $cat === '' )                                                                            return 'badge--event';
	if ( $cat === 'LIVE'  || strpos( $cat, 'LIVE' ) !== false || strpos( $cat, 'ONE-MAN' ) !== false ) return 'badge--live';
	if ( $cat === 'EVENT' || strpos( $cat, 'EVENT' ) !== false )                                  return 'badge--event';
	if ( $cat === 'OTHER' || strpos( $cat, 'OTHER' ) !== false )                                  return 'badge--other';
	// 互換: 旧 taxonomy 由来のキーワード
	if ( strpos( $cat, 'RELEASE' ) !== false )                                                    return 'badge--release';
	if ( strpos( $cat, 'TV' ) !== false || strpos( $cat, 'RADIO' ) !== false || strpos( $cat, 'MEDIA' ) !== false ) return 'badge--media';
	return 'badge--event';
}

/**
 * カレンダー HTML をレンダリング
 *
 * @param int $cal_year
 * @param int $cal_month
 * @return string  HTML （.schedule-cal の中身、月送りボタン込み）
 */
function reirie_render_schedule_calendar( $cal_year, $cal_month ) {
	$cal_year  = (int) $cal_year;
	$cal_month = (int) $cal_month;
	if ( $cal_month < 1 )  { $cal_month = 12; $cal_year--; }
	if ( $cal_month > 12 ) { $cal_month = 1;  $cal_year++; }
	if ( $cal_year  < 2000 || $cal_year > 2100 ) {
		$cal_year = (int) date( 'Y' );
	}

	$cal_start = sprintf( '%04d-%02d-01', $cal_year, $cal_month );
	$cal_end   = date( 'Y-m-t', strtotime( $cal_start ) );

	$cal_query = new WP_Query( array(
		'post_type'      => 'schedule',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_key'       => 'schedule_date',
		'orderby'        => 'meta_value',
		// Ymd（8桁）と Y-m-d が混在した schedule_date でも正しい日付順になるよう
		// SQL側で日付型にキャストしてから並び替える。
		'meta_type'      => 'DATE',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'schedule_date',
				'value'   => array( $cal_start, $cal_end ),
				'compare' => 'BETWEEN',
				'type'    => 'DATE',
			),
		),
	) );

	$events_by_day = array();
	if ( $cal_query->have_posts() ) {
		while ( $cal_query->have_posts() ) {
			$cal_query->the_post();
			$dstr = reirie_field( 'schedule_date' );
			if ( ! $dstr ) continue;
			$d = (int) date( 'j', strtotime( $dstr ) );
			$events_by_day[ $d ][] = array(
				'title' => get_the_title(),
				'url'   => get_permalink(),
				'cat'   => reirie_get_schedule_category(),
				'time'  => reirie_field( 'schedule_time' ),
				'venue' => reirie_field( 'schedule_venue' ),
			);
		}
		wp_reset_postdata();
	}

	$weekday_labels = array( 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT' );

	$prev_m = $cal_month - 1; $prev_y = $cal_year; if ( $prev_m < 1 ) { $prev_m = 12; $prev_y--; }
	$next_m = $cal_month + 1; $next_y = $cal_year; if ( $next_m > 12 ) { $next_m = 1; $next_y++; }
	$base_url = home_url( '/' );
	$prev_url = esc_url( add_query_arg( array( 'cal_y' => $prev_y, 'cal_m' => $prev_m ), $base_url ) ) . '#schedule';
	$next_url = esc_url( add_query_arg( array( 'cal_y' => $next_y, 'cal_m' => $next_m ), $base_url ) ) . '#schedule';

	$first_wday = (int) date( 'w', strtotime( $cal_start ) );
	$days_in_month = (int) date( 't', strtotime( $cal_start ) );
	$today_y = (int) date( 'Y' );
	$today_m = (int) date( 'n' );
	$today_d = (int) date( 'j' );

	ob_start();
	?>
	<div class="schedule-cal__head">
		<a href="<?php echo $prev_url; ?>" class="schedule-cal__nav" aria-label="前月"
		   data-cal-y="<?php echo (int) $prev_y; ?>" data-cal-m="<?php echo (int) $prev_m; ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
		</a>
		<div class="schedule-cal__title">
			<span class="schedule-cal__year"><?php echo esc_html( $cal_year ); ?></span>
			<span class="schedule-cal__month"><?php echo esc_html( sprintf( '%02d', $cal_month ) ); ?></span>
			<span class="schedule-cal__month-en"><?php echo esc_html( strtoupper( date( 'M', mktime( 0, 0, 0, $cal_month, 1, $cal_year ) ) ) ); ?></span>
		</div>
		<a href="<?php echo $next_url; ?>" class="schedule-cal__nav" aria-label="翌月"
		   data-cal-y="<?php echo (int) $next_y; ?>" data-cal-m="<?php echo (int) $next_m; ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
		</a>
	</div>

	<div class="schedule-cal__grid">
		<?php foreach ( $weekday_labels as $i => $w ) : ?>
			<div class="schedule-cal__wname<?php echo ( $i === 0 ) ? ' is-sun' : ( ( $i === 6 ) ? ' is-sat' : '' ); ?>"><?php echo esc_html( $w ); ?></div>
		<?php endforeach; ?>

		<?php for ( $i = 0; $i < $first_wday; $i++ ) : ?>
			<div class="schedule-cal__cell is-empty"></div>
		<?php endfor; ?>

		<?php
		for ( $d = 1; $d <= $days_in_month; $d++ ) :
			$wday_idx = (int) date( 'w', mktime( 0, 0, 0, $cal_month, $d, $cal_year ) );
			$is_today = ( $cal_year === $today_y && $cal_month === $today_m && $d === $today_d );
			$has_event = isset( $events_by_day[ $d ] );
			$cell_cls = 'schedule-cal__cell';
			if ( $wday_idx === 0 ) $cell_cls .= ' is-sun';
			if ( $wday_idx === 6 ) $cell_cls .= ' is-sat';
			if ( $is_today )       $cell_cls .= ' is-today';
			if ( $has_event )      $cell_cls .= ' has-event';
		?>
			<div class="<?php echo esc_attr( $cell_cls ); ?>">
				<div class="schedule-cal__day"><?php echo $d; ?></div>
				<?php if ( $has_event ) :
					$events = $events_by_day[ $d ];
					$event_count = count( $events );
					$max_show = 2;
					$shown = array_slice( $events, 0, $max_show );
					$rest  = $event_count - $max_show;
				?>
					<ul class="schedule-cal__events">
						<?php foreach ( $shown as $ev ) :
							$bcls = reirie_schedule_badge_class( $ev['cat'] );
							// バッジ色クラスをイベントリンク自体にも付与 (モバイルでバッジ非表示時も色分けされるように)
							$ev_color_cls = ' is-' . str_replace( 'badge--', '', $bcls );
						?>
							<li>
								<a href="<?php echo esc_url( $ev['url'] ); ?>" class="schedule-cal__event<?php echo esc_attr( $ev_color_cls ); ?>" title="<?php echo esc_attr( $ev['title'] ); ?>">
									<?php if ( $ev['cat'] ) : ?>
										<span class="schedule-badge schedule-badge--sm <?php echo esc_attr( $bcls ); ?>"><?php echo esc_html( $ev['cat'] ); ?></span>
									<?php endif; ?>
									<span class="schedule-cal__event-title"><?php echo esc_html( $ev['title'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
						<?php if ( $rest > 0 ) : ?>
							<li class="schedule-cal__more">+<?php echo (int) $rest; ?> more</li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endfor; ?>
	</div>

	<?php if ( empty( $events_by_day ) ) : ?>
		<p class="schedule-cal__empty">この月にスケジュールはありません。</p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * Ajax エンドポイント
 */
function reirie_ajax_schedule_cal() {
	$y = isset( $_GET['y'] ) ? (int) $_GET['y'] : (int) date( 'Y' );
	$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : (int) date( 'n' );
	echo reirie_render_schedule_calendar( $y, $m );
	wp_die();
}
add_action( 'wp_ajax_reirie_schedule_cal', 'reirie_ajax_schedule_cal' );
add_action( 'wp_ajax_nopriv_reirie_schedule_cal', 'reirie_ajax_schedule_cal' );

/**
 * Ajax URL を JS に渡す
 */
function reirie_localize_ajax_url() {
	wp_localize_script( 'reirie-main', 'REIRIE_AJAX', array(
		'url' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'reirie_localize_ajax_url', 30 );

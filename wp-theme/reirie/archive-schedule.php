<?php
/**
 * Schedule Archive Template
 *
 * 月ごとにグループ化されたスケジュール一覧ページ。
 * URL: /schedule/
 *
 * @package REIRIE
 */

get_header();

/* =========================================================
   現在表示すべき期間を URL パラメータから判定
   ?view=upcoming (default) … 今日以降の予定のみ
   ?view=past                  … 過去の予定のみ
   ?view=all                   … すべて
   ?year=2026&month=07         … 特定の月だけ
========================================================= */
$view  = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'upcoming';
$year  = isset( $_GET['year'] ) ? (int) $_GET['year'] : 0;
$month = isset( $_GET['month'] ) ? (int) $_GET['month'] : 0;

$today = date( 'Y-m-d' );

$meta_query = array( 'relation' => 'AND' );

if ( $year && $month ) {
	$start = sprintf( '%04d-%02d-01', $year, $month );
	$end   = date( 'Y-m-t', strtotime( $start ) );
	$meta_query[] = array(
		'key'     => 'schedule_date',
		'value'   => array( $start, $end ),
		'compare' => 'BETWEEN',
		'type'    => 'DATE',
	);
	$order = 'ASC';
} elseif ( $view === 'past' ) {
	$meta_query[] = array(
		'key'     => 'schedule_date',
		'value'   => $today,
		'compare' => '<',
		'type'    => 'DATE',
	);
	$order = 'DESC';
} elseif ( $view === 'all' ) {
	$order = 'ASC';
} else {
	// upcoming（既定）
	$meta_query[] = array(
		'key'     => 'schedule_date',
		'value'   => $today,
		'compare' => '>=',
		'type'    => 'DATE',
	);
	$order = 'ASC';
}

$query_args = array(
	'post_type'      => 'schedule',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'meta_key'       => 'schedule_date',
	'orderby'        => 'meta_value',
	'order'          => $order,
);
if ( count( $meta_query ) > 1 ) {
	$query_args['meta_query'] = $meta_query;
}

$schedule_query = new WP_Query( $query_args );

// 月ごとにグループ化
$grouped = array();
if ( $schedule_query->have_posts() ) {
	while ( $schedule_query->have_posts() ) {
		$schedule_query->the_post();
		$date_str = reirie_field( 'schedule_date' );
		$ts       = $date_str ? strtotime( $date_str ) : 0;
		if ( ! $ts ) continue;
		$group_key = date( 'Y-m', $ts );
		if ( ! isset( $grouped[ $group_key ] ) ) {
			$grouped[ $group_key ] = array(
				'year_jp'  => date( 'Y', $ts ),
				'month_jp' => (int) date( 'n', $ts ),
				'month_en' => date( 'F', $ts ),
				'posts'    => array(),
			);
		}
		$grouped[ $group_key ]['posts'][] = array(
			'id'        => get_the_ID(),
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
			'ts'        => $ts,
			'date_str'  => $date_str,
			'venue'     => reirie_field( 'schedule_venue' ),
			'time'      => reirie_field( 'schedule_time' ),
			'link'      => reirie_field( 'schedule_link' ),
			'highlight' => reirie_field( 'schedule_highlight' ),
			'category'  => reirie_get_schedule_category(),
		);
	}
	wp_reset_postdata();
}

// 月キーをソート（昇順 or 降順）
if ( $order === 'DESC' ) {
	krsort( $grouped );
} else {
	ksort( $grouped );
}
?>

<main class="section schedule-archive">
	<div class="section__head">
		<span class="section__num">02 / Schedule</span>
		<h2 class="section__title">Schedule<span class="section__title-jp">スケジュール</span></h2>
	</div>

	<?php /* ====== ビュー切替タブ ====== */ ?>
	<div class="schedule-archive__tabs">
		<?php
		$base_url = get_post_type_archive_link( 'schedule' );
		$tabs = array(
			'upcoming' => '今後の予定',
			'past'     => '過去の予定',
			'all'      => 'すべて',
		);
		$current = ( $year && $month ) ? 'month' : $view;
		foreach ( $tabs as $key => $label ) :
			$is_active = ( $current === $key );
			$url = add_query_arg( 'view', $key, $base_url );
		?>
			<a href="<?php echo esc_url( $url ); ?>" class="schedule-archive__tab<?php echo $is_active ? ' is-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
		<?php if ( $year && $month ) : ?>
			<span class="schedule-archive__tab is-active"><?php echo esc_html( $year . '年' . $month . '月' ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $grouped ) ) : ?>

		<div class="schedule-archive__wrap">

			<?php /* ===== 月別アンカーナビ（月数が2以上のとき表示） ===== */ ?>
			<?php if ( count( $grouped ) >= 2 ) : ?>
				<nav class="schedule-archive__nav" aria-label="月別ナビゲーション">
					<span class="schedule-archive__nav-label">月で絞り込む</span>
					<ul>
						<?php foreach ( $grouped as $key => $g ) : ?>
							<li>
								<a href="#month-<?php echo esc_attr( $key ); ?>">
									<?php echo esc_html( $g['year_jp'] . '.' . sprintf( '%02d', $g['month_jp'] ) ); ?>
									<span class="count">（<?php echo count( $g['posts'] ); ?>）</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php /* ===== 月ごとのグループ表示 ===== */ ?>
			<?php foreach ( $grouped as $key => $g ) : ?>
				<section class="schedule-month" id="month-<?php echo esc_attr( $key ); ?>">
					<header class="schedule-month__header">
						<div class="schedule-month__num">
							<span class="month-en"><?php echo esc_html( strtoupper( substr( $g['month_en'], 0, 3 ) ) ); ?></span>
							<span class="month-jp"><?php echo esc_html( sprintf( '%02d', $g['month_jp'] ) ); ?></span>
						</div>
						<div class="schedule-month__title">
							<h3>
								<span class="num"><?php echo esc_html( $g['year_jp'] ); ?></span><span class="kanji">年</span><span class="sep"></span><span class="num"><?php echo esc_html( $g['month_jp'] ); ?></span><span class="kanji">月</span>
							</h3>
							<p><?php echo esc_html( $g['month_en'] . ' ' . $g['year_jp'] ); ?> ／ <?php echo count( $g['posts'] ); ?>件のスケジュール</p>
						</div>
					</header>

					<div class="schedule-month__list">
						<?php foreach ( $g['posts'] as $p ) :
							$ts    = $p['ts'];
							$is_past = $ts < strtotime( $today );
						?>
							<article class="schedule__item<?php echo $p['highlight'] ? ' highlight' : ''; ?><?php echo $is_past ? ' is-past' : ''; ?>">
								<div class="schedule__date">
									<span class="day"><?php echo esc_html( date( 'd', $ts ) ); ?></span>
									<span class="month"><?php echo esc_html( strtoupper( date( 'M Y', $ts ) ) ); ?></span>
									<span class="weekday"><?php echo esc_html( strtoupper( date( 'D', $ts ) ) ); ?></span>
								</div>
								<div class="schedule__body">
									<?php if ( $p['category'] ) : ?>
										<span class="schedule__cat"><?php echo esc_html( $p['category'] ); ?></span>
									<?php endif; ?>
									<h3 class="schedule__title">
										<a href="<?php echo esc_url( $p['permalink'] ); ?>"><?php echo esc_html( $p['title'] ); ?></a>
									</h3>
									<p class="schedule__meta">
										<?php if ( $p['venue'] ) : ?><span>📍 <?php echo esc_html( $p['venue'] ); ?></span><?php endif; ?>
										<?php if ( $p['time'] ) : ?><span>🕐 <?php echo esc_html( $p['time'] ); ?></span><?php endif; ?>
									</p>
								</div>
								<a href="<?php echo esc_url( $p['permalink'] ); ?>" class="schedule__btn">Detail</a>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>

		</div>

	<?php else : ?>

		<div class="schedule-archive__empty">
			<p class="empty-icon">📅</p>
			<p class="empty-text">
				<?php if ( $view === 'past' ) : ?>
					過去のスケジュールはまだありません。
				<?php elseif ( $year && $month ) : ?>
					<?php echo esc_html( $year ); ?>年<?php echo esc_html( $month ); ?>月のスケジュールはありません。
				<?php else : ?>
					今後のスケジュールはまだ公開されていません。<br>
					お楽しみに！
				<?php endif; ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO HOME</span></a>
		</div>

	<?php endif; ?>

	<div class="section__more">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>#schedule" class="more-btn"><span>← BACK TO TOP</span></a>
	</div>
</main>

<?php get_footer(); ?>

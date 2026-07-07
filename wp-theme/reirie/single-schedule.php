<?php
/**
 * Single Schedule Template
 *
 * 各イベントの個別詳細ページ。
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section single-schedule">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
		$date_str  = reirie_field( 'schedule_date' );
		$venue     = reirie_field( 'schedule_venue' );
		$time      = reirie_field( 'schedule_time' );
		$link      = reirie_field( 'schedule_link' );
		$highlight = reirie_field( 'schedule_highlight' );
		$category  = reirie_get_schedule_category();
		$thumb     = get_the_post_thumbnail_url( get_the_ID(), 'large' );

		$ts        = $date_str ? strtotime( $date_str ) : 0;
		$today_ts  = strtotime( date( 'Y-m-d' ) );
		$is_past   = $ts && $ts < $today_ts;
		$is_today  = $ts && date( 'Y-m-d', $ts ) === date( 'Y-m-d' );

		$day       = $ts ? date( 'd', $ts ) : '--';
		$month_en  = $ts ? strtoupper( date( 'M', $ts ) ) : '';
		$year      = $ts ? date( 'Y', $ts ) : '';
		$weekday   = $ts ? strtoupper( date( 'D', $ts ) ) : '';
		$full_jp   = $ts ? date( 'Y年n月j日', $ts ) . '（' . array( '日','月','火','水','木','金','土' )[ (int) date( 'w', $ts ) ] . '）' : '';

		// 同じ月の前後イベントを取得（ナビゲーション用）
		$adjacent_args = array(
			'post_type'      => 'schedule',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'post__not_in'   => array( get_the_ID() ),
			'meta_key'       => 'schedule_date',
			'orderby'        => 'meta_value',
			// Ymd（8桁）と Y-m-d が混在した schedule_date でも正しい日付順になるよう
			// SQL側で日付型にキャストしてから並び替える。
			'meta_type'      => 'DATE',
		);

		$prev_q = new WP_Query( array_merge( $adjacent_args, array(
			'meta_query' => array( array(
				'key'     => 'schedule_date',
				'value'   => $date_str,
				'compare' => '<',
				'type'    => 'DATE',
			) ),
			'order' => 'DESC',
		) ) );
		$next_q = new WP_Query( array_merge( $adjacent_args, array(
			'meta_query' => array( array(
				'key'     => 'schedule_date',
				'value'   => $date_str,
				'compare' => '>',
				'type'    => 'DATE',
			) ),
			'order' => 'ASC',
		) ) );
	?>

	<div class="section__head">
		<span class="section__num">02 / Schedule</span>
		<h2 class="section__title">Schedule<span class="section__title-jp">スケジュール</span></h2>
	</div>

	<article class="single-schedule__wrap<?php echo $highlight ? ' is-highlight' : ''; ?><?php echo $is_past ? ' is-past' : ''; ?>">

		<?php /* ===== ヒーロー部分（日付・カテゴリー・タイトル） ===== */ ?>
		<header class="single-schedule__hero">
			<?php if ( $thumb ) : ?>
				<div class="single-schedule__hero-bg" style="background-image:url('<?php echo esc_url( $thumb ); ?>');"></div>
			<?php endif; ?>
			<div class="single-schedule__hero-inner">
				<div class="single-schedule__date-block">
					<span class="big-day"><?php echo esc_html( $day ); ?></span>
					<div class="big-myw">
						<span class="big-month"><?php echo esc_html( $month_en ); ?></span>
						<span class="big-year"><?php echo esc_html( $year ); ?></span>
						<span class="big-weekday"><?php echo esc_html( $weekday ); ?></span>
					</div>
				</div>

				<div class="single-schedule__heading">
					<?php if ( $is_today ) : ?>
						<span class="single-schedule__badge today">TODAY</span>
					<?php elseif ( $highlight ) : ?>
						<span class="single-schedule__badge highlight">★ HIGHLIGHT</span>
					<?php elseif ( $is_past ) : ?>
						<span class="single-schedule__badge past">PAST EVENT</span>
					<?php else : ?>
						<span class="single-schedule__badge upcoming">UPCOMING</span>
					<?php endif; ?>

					<?php if ( $category ) : ?>
						<p class="single-schedule__cat"><?php echo esc_html( $category ); ?></p>
					<?php endif; ?>

					<h1 class="single-schedule__title"><?php the_title(); ?></h1>

					<?php if ( $full_jp ) : ?>
						<p class="single-schedule__fulldate">📅 <?php echo esc_html( $full_jp ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<?php /* ===== 情報ブロック ===== */ ?>
		<div class="single-schedule__info">
			<dl class="single-schedule__dl">
				<?php if ( $venue ) : ?>
					<dt>会場</dt>
					<dd>📍 <?php echo esc_html( $venue ); ?></dd>
				<?php endif; ?>

				<?php if ( $time ) : ?>
					<dt>開場 / 開演</dt>
					<dd>🕐 <?php echo esc_html( $time ); ?></dd>
				<?php endif; ?>

				<?php if ( $category ) : ?>
					<dt>カテゴリー</dt>
					<dd><?php echo esc_html( $category ); ?></dd>
				<?php endif; ?>
			</dl>

			<?php if ( $link ) : ?>
				<div class="single-schedule__cta">
					<a href="<?php echo esc_url( $link ); ?>" class="more-btn" target="_blank" rel="noopener noreferrer">
						<span>チケット・詳細情報を見る ↗</span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<?php /* ===== 本文 ===== */ ?>
		<?php if ( get_the_content() ) : ?>
			<div class="single-schedule__content">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<?php /* ===== 前後ナビゲーション ===== */ ?>
		<nav class="single-schedule__nav" aria-label="前後のイベント">
			<?php if ( $prev_q->have_posts() ) : $prev_q->the_post();
				$prev_date = reirie_field( 'schedule_date' );
				$prev_ts   = $prev_date ? strtotime( $prev_date ) : 0;
			?>
				<a href="<?php the_permalink(); ?>" class="single-schedule__nav-link prev">
					<span class="nav-arrow">←</span>
					<span class="nav-info">
						<span class="nav-label">前のイベント</span>
						<span class="nav-date"><?php echo $prev_ts ? esc_html( date( 'n月j日', $prev_ts ) ) : ''; ?></span>
						<span class="nav-title"><?php the_title(); ?></span>
					</span>
				</a>
			<?php wp_reset_postdata(); else : ?>
				<span class="single-schedule__nav-link is-disabled"></span>
			<?php endif; ?>

			<a href="<?php echo esc_url( get_post_type_archive_link( 'schedule' ) ); ?>" class="single-schedule__nav-link center">
				<span class="nav-arrow">⊞</span>
				<span class="nav-info">
					<span class="nav-label">スケジュール</span>
					<span class="nav-title">一覧へ戻る</span>
				</span>
			</a>

			<?php if ( $next_q->have_posts() ) : $next_q->the_post();
				$next_date = reirie_field( 'schedule_date' );
				$next_ts   = $next_date ? strtotime( $next_date ) : 0;
			?>
				<a href="<?php the_permalink(); ?>" class="single-schedule__nav-link next">
					<span class="nav-info">
						<span class="nav-label">次のイベント</span>
						<span class="nav-date"><?php echo $next_ts ? esc_html( date( 'n月j日', $next_ts ) ) : ''; ?></span>
						<span class="nav-title"><?php the_title(); ?></span>
					</span>
					<span class="nav-arrow">→</span>
				</a>
			<?php wp_reset_postdata(); else : ?>
				<span class="single-schedule__nav-link is-disabled"></span>
			<?php endif; ?>
		</nav>

	</article>

	<?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>

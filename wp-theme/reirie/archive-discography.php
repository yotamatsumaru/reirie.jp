<?php
/**
 * Discography Archive Template
 *
 * 全作品をリリース年でグルーピングして一覧表示。
 * URL: /discography/
 *
 * @package REIRIE
 */

get_header();

/* =========================================================
   URL クエリ:
   ?cat=single  … カテゴリ絞り込み (例: single / album / mini など disco_category の値)
   ?year=2026   … リリース年絞り込み
========================================================= */
$filter_cat  = isset( $_GET['cat'] )  ? sanitize_text_field( wp_unslash( $_GET['cat'] ) )  : '';
$filter_year = isset( $_GET['year'] ) ? (int) $_GET['year'] : 0;

$meta_query = array( 'relation' => 'AND' );
if ( $filter_cat !== '' ) {
	$meta_query[] = array(
		'key'     => 'disco_category',
		'value'   => $filter_cat,
		'compare' => 'LIKE',
	);
}
if ( $filter_year ) {
	$meta_query[] = array(
		'key'     => 'disco_release_date',
		'value'   => array( sprintf( '%04d-01-01', $filter_year ), sprintf( '%04d-12-31', $filter_year ) ),
		'compare' => 'BETWEEN',
		'type'    => 'DATE',
	);
}

$args = array(
	'post_type'      => 'discography',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'meta_key'       => 'disco_release_date',
	'orderby'        => 'meta_value',
	'order'          => 'DESC',
);
if ( count( $meta_query ) > 1 ) {
	$args['meta_query'] = $meta_query;
}

$dq = new WP_Query( $args );

// 全カテゴリ一覧（フィルタータブ生成用）
$all_categories = array();
$all_years      = array();
$count_all      = 0;
$all_q = new WP_Query( array(
	'post_type'      => 'discography',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'fields'         => 'ids',
) );
foreach ( $all_q->posts as $pid ) {
	$c = get_post_meta( $pid, 'disco_category', true );
	if ( $c !== '' ) $all_categories[ $c ] = ( $all_categories[ $c ] ?? 0 ) + 1;
	$rd = get_post_meta( $pid, 'disco_release_date', true );
	if ( $rd ) {
		$y = (int) substr( $rd, 0, 4 );
		if ( $y > 0 ) $all_years[ $y ] = ( $all_years[ $y ] ?? 0 ) + 1;
	}
	$count_all++;
}
wp_reset_postdata();
krsort( $all_years );

// 年別グルーピング
$grouped = array();
if ( $dq->have_posts() ) {
	while ( $dq->have_posts() ) {
		$dq->the_post();
		$rd = reirie_field( 'disco_release_date' );
		$y  = $rd ? (int) substr( $rd, 0, 4 ) : 0;
		if ( ! $y ) $y = 9999;
		$grouped[ $y ][] = array(
			'id'        => get_the_ID(),
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
			'cat'       => reirie_field( 'disco_category', false, '' ),
			'release'   => $rd,
			'price'     => reirie_field( 'disco_price' ),
			'is_new'    => reirie_field( 'disco_is_new' ),
			'thumb'     => get_the_post_thumbnail_url( get_the_ID(), 'reirie-jacket' ),
		);
	}
	wp_reset_postdata();
}
krsort( $grouped );

$base_url = get_post_type_archive_link( 'discography' );
?>

<main class="section discography-archive">

	<div class="section__head">
		<span class="section__num">03 / Discography</span>
		<h2 class="section__title">Discography<span class="section__title-jp">作品一覧</span></h2>
	</div>

	<?php /* ===== フィルタ タブ ===== */ ?>
	<div class="disco-archive__filters">

		<?php /* カテゴリフィルタ */ ?>
		<?php if ( ! empty( $all_categories ) ) : ?>
			<div class="disco-archive__filter-row">
				<span class="disco-archive__filter-label">CATEGORY</span>
				<a href="<?php echo esc_url( $filter_year ? add_query_arg( 'year', $filter_year, $base_url ) : $base_url ); ?>"
				   class="disco-archive__chip<?php echo $filter_cat === '' ? ' is-active' : ''; ?>">
					ALL<span class="disco-archive__chip-num">(<?php echo (int) $count_all; ?>)</span>
				</a>
				<?php foreach ( $all_categories as $c => $cnt ) :
					$url = add_query_arg( 'cat', rawurlencode( $c ), $base_url );
					if ( $filter_year ) $url = add_query_arg( 'year', $filter_year, $url );
				?>
					<a href="<?php echo esc_url( $url ); ?>"
					   class="disco-archive__chip<?php echo $filter_cat === $c ? ' is-active' : ''; ?>">
						<?php echo esc_html( $c ); ?><span class="disco-archive__chip-num">(<?php echo (int) $cnt; ?>)</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php /* 年フィルタ */ ?>
		<?php if ( ! empty( $all_years ) ) : ?>
			<div class="disco-archive__filter-row">
				<span class="disco-archive__filter-label">YEAR</span>
				<a href="<?php echo esc_url( $filter_cat !== '' ? add_query_arg( 'cat', rawurlencode( $filter_cat ), $base_url ) : $base_url ); ?>"
				   class="disco-archive__chip<?php echo $filter_year === 0 ? ' is-active' : ''; ?>">
					ALL
				</a>
				<?php foreach ( $all_years as $y => $cnt ) :
					$url = add_query_arg( 'year', $y, $base_url );
					if ( $filter_cat !== '' ) $url = add_query_arg( 'cat', rawurlencode( $filter_cat ), $url );
				?>
					<a href="<?php echo esc_url( $url ); ?>"
					   class="disco-archive__chip<?php echo $filter_year === $y ? ' is-active' : ''; ?>">
						<?php echo esc_html( $y ); ?><span class="disco-archive__chip-num">(<?php echo (int) $cnt; ?>)</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>

	<?php if ( ! empty( $grouped ) ) : ?>

		<?php foreach ( $grouped as $y => $items ) : ?>
			<section class="disco-archive__year-block">
				<h3 class="disco-archive__year">
					<span class="disco-archive__year-num"><?php echo (int) $y === 9999 ? 'TBA' : (int) $y; ?></span>
					<span class="disco-archive__year-line"></span>
					<span class="disco-archive__year-count"><?php echo count( $items ); ?> works</span>
				</h3>

				<div class="disco-archive__grid">
					<?php foreach ( $items as $item ) : ?>
						<article class="disco-card disco-card--archive">
							<a class="disco-card__link" href="<?php echo esc_url( $item['permalink'] ); ?>">
								<div class="disco-card__jacket">
									<?php if ( $item['thumb'] ) : ?>
										<div class="jacket-art" style="background-image:url('<?php echo esc_url( $item['thumb'] ); ?>');"></div>
									<?php else : ?>
										<div class="jacket-art jacket-2"><span class="jacket-title"><?php echo esc_html( $item['title'] ); ?></span></div>
									<?php endif; ?>
									<?php if ( $item['is_new'] ) : ?><span class="disco__badge">NEW</span><?php endif; ?>
								</div>
								<div class="disco-card__info">
									<?php if ( $item['cat'] ) : ?>
										<span class="disco__cat"><?php echo esc_html( $item['cat'] ); ?></span>
									<?php endif; ?>
									<?php if ( $item['release'] ) : ?>
										<p class="disco-card__date"><?php echo esc_html( reirie_format_date( $item['release'] ) ); ?> RELEASE</p>
									<?php endif; ?>
									<h3 class="disco-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
								</div>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>

	<?php else : ?>

		<p class="disco-archive__empty">該当する作品はまだありません。</p>

	<?php endif; ?>

	<div class="section__more">
		<a href="<?php echo esc_url( home_url( '/#discography' ) ); ?>" class="more-btn"><span>BACK TO HOME</span></a>
	</div>

</main>

<?php get_footer(); ?>

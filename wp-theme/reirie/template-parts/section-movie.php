<?php
/**
 * Movie Section
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$movie_query = new WP_Query( array(
	'post_type'      => 'movie',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
) );

/**
 * YouTube URL から video ID を抽出
 * 対応: youtube.com/watch?v=XXX, youtu.be/XXX, youtube.com/embed/XXX, youtube.com/shorts/XXX
 */
if ( ! function_exists( 'reirie_extract_youtube_id' ) ) {
	function reirie_extract_youtube_id( $url ) {
		if ( ! $url ) return '';
		$patterns = array(
			'/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/|youtube\.com\/shorts\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/',
		);
		foreach ( $patterns as $p ) {
			if ( preg_match( $p, $url, $m ) ) {
				return $m[1];
			}
		}
		return '';
	}
}
?>

<section class="section movie" id="movie">
  <div class="section__head">
    <span class="section__num">04 / Movie</span>
    <h2 class="section__title">Movie<span class="section__title-jp">動画</span></h2>
  </div>

  <div class="movie__grid">
    <?php if ( $movie_query->have_posts() ) :
      $i = 0;
      while ( $movie_query->have_posts() ) : $movie_query->the_post();
        $movie_url   = reirie_field( 'movie_url' );
        $movie_date  = reirie_field( 'movie_date' );
        $movie_label = reirie_field( 'movie_label' );
        $yt_id       = reirie_extract_youtube_id( $movie_url );

        /* サムネ優先順:
           1) YouTube動画なら YouTube のサムネ（maxresdefault）
           2) 投稿のアイキャッチ画像
           3) フォールバック（thumb-N グラデ） */
        $thumb = '';
        if ( $yt_id ) {
          $thumb = 'https://i.ytimg.com/vi/' . $yt_id . '/maxresdefault.jpg';
        }
        if ( ! $thumb ) {
          $thumb = get_the_post_thumbnail_url( get_the_ID(), 'reirie-thumb-16-9' );
        }
        $link = $movie_url ? $movie_url : get_permalink();
    ?>
      <article class="movie__item">
        <a href="<?php echo esc_url( $link ); ?>"<?php echo $movie_url ? ' target="_blank" rel="noopener"' : ''; ?> class="movie__link">
          <?php if ( $thumb ) : ?>
            <div class="movie__thumb"<?php echo $yt_id ? ' data-yt-id="' . esc_attr( $yt_id ) . '"' : ''; ?> style="background-image:url('<?php echo esc_url( $thumb ); ?>');background-size:cover;background-position:center;">
              <span class="play-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                  <path d="M7 4.5v13l11-6.5L7 4.5z" fill="currentColor"/>
                </svg>
              </span>
              <?php if ( $yt_id ) : ?><span class="movie__yt-badge">YouTube</span><?php endif; ?>
              <?php if ( $movie_label ) : ?><span class="movie__label"><?php echo esc_html( $movie_label ); ?></span><?php endif; ?>
            </div>
          <?php else : ?>
            <div class="movie__thumb thumb-<?php echo esc_attr( ( $i % 6 ) + 1 ); ?>">
              <span class="play-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                  <path d="M7 4.5v13l11-6.5L7 4.5z" fill="currentColor"/>
                </svg>
              </span>
              <?php if ( $movie_label ) : ?><span class="movie__label"><?php echo esc_html( $movie_label ); ?></span><?php endif; ?>
            </div>
          <?php endif; ?>
          <p class="movie__title"><?php the_title(); ?></p>
          <?php if ( $movie_date ) : ?><p class="movie__date"><?php echo esc_html( reirie_format_date( $movie_date ) ); ?></p><?php endif; ?>
        </a>
      </article>
    <?php $i++; endwhile; wp_reset_postdata(); else : ?>

      <?php for ( $i = 1; $i <= 6; $i++ ) :
        $titles = array( '「Twinkle Heart」Music Video', '「Bloom Bloom」Dance Performance', '「Hello, World」Music Video', 'REIRIE Debut Documentary', '「ハートビート☆ガール」Lyric Video', 'REIRIE TV #01' );
      ?>
        <article class="movie__item">
          <a href="#" class="movie__link">
            <div class="movie__thumb thumb-<?php echo $i; ?>">
              <span class="play-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                  <path d="M7 4.5v13l11-6.5L7 4.5z" fill="currentColor"/>
                </svg>
              </span>
            </div>
            <p class="movie__title"><?php echo esc_html( $titles[ $i - 1 ] ); ?></p>
          </a>
        </article>
      <?php endfor; ?>

    <?php endif; ?>
  </div>
</section>

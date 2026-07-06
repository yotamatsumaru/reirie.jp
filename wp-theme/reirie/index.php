<?php
/**
 * メインインデックステンプレート（汎用フォールバック）
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section single-page">
  <div class="section__head">
    <span class="section__num">— / Posts</span>
    <h2 class="section__title"><?php
      if ( is_archive() ) {
        single_post_title();
      } elseif ( is_search() ) {
        echo 'Search: ' . esc_html( get_search_query() );
      } else {
        echo 'Posts';
      }
    ?></h2>
  </div>

  <?php if ( have_posts() ) : ?>
    <div class="news__list" style="max-width:980px;margin:0 auto;border-top:1px solid rgba(255,126,182,.25);">
      <?php while ( have_posts() ) : the_post(); ?>
        <article class="news__item">
          <a href="<?php the_permalink(); ?>" class="news__link">
            <span class="news__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
            <span class="news__cat"><?php echo esc_html( strtoupper( get_post_type() ) ); ?></span>
            <span class="news__title"><?php the_title(); ?></span>
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <div class="section__more">
      <?php
        the_posts_pagination( array(
          'prev_text' => '← 前へ',
          'next_text' => '次へ →',
        ) );
      ?>
    </div>
  <?php else : ?>
    <p style="text-align:center;padding:60px 20px;">該当する記事がありません。</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>

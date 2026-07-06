<?php
/**
 * 検索結果テンプレート
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section">
  <div class="section__head">
    <span class="section__num">— / Search</span>
    <h2 class="section__title">Search<span class="section__title-jp">「<?php echo esc_html( get_search_query() ); ?>」の検索結果</span></h2>
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
      <?php the_posts_pagination(); ?>
    </div>
  <?php else : ?>
    <p style="text-align:center;padding:60px 20px;">「<?php echo esc_html( get_search_query() ); ?>」に一致する記事は見つかりませんでした。</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>

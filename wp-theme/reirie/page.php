<?php
/**
 * 固定ページテンプレート
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article style="max-width:880px;margin:0 auto;">
      <div class="section__head">
        <h2 class="section__title" style="font-size:clamp(36px,6vw,72px);"><?php the_title(); ?></h2>
      </div>

      <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin-bottom:40px;border-radius:18px;overflow:hidden;box-shadow:0 12px 32px rgba(255,126,182,.18);">
          <?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:auto;display:block;' ) ); ?>
        </div>
      <?php endif; ?>

      <div class="single-content" style="font-size:15px;line-height:1.95;">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>

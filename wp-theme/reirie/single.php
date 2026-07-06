<?php
/**
 * 単一記事テンプレート（共通）
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section single-page">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article class="single-article<?php echo ( function_exists( 'reirie_is_scheduled' ) && reirie_is_scheduled() ) ? ' is-scheduled' : ''; ?>" style="max-width:880px;margin:0 auto;">
      <?php if ( function_exists( 'reirie_is_scheduled' ) && reirie_is_scheduled() ) : ?>
        <div class="single-scheduled-notice" style="max-width:880px;margin:0 auto 24px;padding:14px 20px;background:linear-gradient(135deg,#fff7d6,#ffe9f3);border:1px solid #ffb84d;border-radius:14px;color:#a85b00;font-size:14px;line-height:1.7;text-align:center;">
          <strong>📅 公開予定の投稿です</strong> — このページは管理者・編集者のみに表示されています。一般訪問者には公開日時（<?php
            $sd_meta_n = reirie_field( 'news_date' );
            if ( $sd_meta_n && function_exists( 'reirie_format_datetime' ) ) {
                echo esc_html( reirie_format_datetime( $sd_meta_n ) );
            } else {
                echo esc_html( get_the_date( 'Y.m.d H:i' ) );
            }
          ?>）以降に公開されます。
        </div>
      <?php endif; ?>
      <div class="section__head">
        <span class="section__num"><?php echo esc_html( strtoupper( get_post_type() ) ); ?></span>
        <h2 class="section__title single-article__title"><?php the_title(); ?></h2>
        <p style="margin-top:12px;font-family:var(--serif);letter-spacing:.2em;color:var(--pink-deep);">
          <?php
          // news の場合は news_date（datetime）を優先表示
          $sd_meta = ( get_post_type() === 'news' ) ? reirie_field( 'news_date' ) : '';
          if ( $sd_meta && function_exists( 'reirie_format_datetime' ) ) {
              echo esc_html( reirie_format_datetime( $sd_meta ) );
          } else {
              echo esc_html( get_the_date( 'Y.m.d' ) );
          }
          ?>
        </p>
      </div>

      <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin-bottom:40px;border-radius:18px;overflow:hidden;box-shadow:0 12px 32px rgba(255,126,182,.18);">
          <?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:auto;display:block;' ) ); ?>
        </div>
      <?php endif; ?>

      <div class="single-content reirie-rich-content" style="font-size:15px;line-height:1.95;">
        <?php
          // 本文の出力
          // 保存時に既に <p>/<br> 付きHTMLに整形済みのため、wpautop は不要（むしろ空段落を消すので避ける）
          $reirie_raw_content = get_the_content();

          if ( preg_match( '/<p[\s>]/i', $reirie_raw_content ) ) {
              // 既に <p> 入りHTML — wpautop をスキップして、それ以外のフィルタ（ショートコード等）だけ通す
              remove_filter( 'the_content', 'wpautop' );
              $reirie_filtered = apply_filters( 'the_content', $reirie_raw_content );
              add_filter( 'the_content', 'wpautop' );
          } else {
              // 旧データ（プレーン保存）— 標準のフィルタチェーン任せ
              $reirie_filtered = apply_filters( 'the_content', $reirie_raw_content );
          }

          // <a> が無いがURLが含まれる場合のみ make_clickable を実行
          if ( strpos( $reirie_filtered, '<a ' ) === false && preg_match( '#https?://#i', $reirie_filtered ) ) {
              $reirie_filtered = make_clickable( $reirie_filtered );
          }
          echo $reirie_filtered;
        ?>
      </div>

      <div style="text-align:center;margin-top:60px;">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO TOP</span></a>
      </div>
    </article>
  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>

<?php
/**
 * Single Member Template
 *
 * @package REIRIE
 */

get_header(); ?>

<main class="section single-member">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    $color        = reirie_field( 'member_color', false, '' );
    $color_cls    = reirie_field( 'member_color_class', false, 'color-pink' );
    $name_jp      = reirie_field( 'member_name_jp' );
    $catch        = reirie_field( 'member_catch' );
    $birthday     = reirie_field( 'member_birthday' );
    $blood        = reirie_field( 'member_blood' );
    $hometown     = reirie_field( 'member_hometown' );
    $height       = reirie_field( 'member_height' );
    $hobby        = reirie_field( 'member_hobby' );
    $charm        = reirie_field( 'member_charm' );
    $skill        = reirie_field( 'member_skill' );
    $mbti         = reirie_field( 'member_mbti' );
    $message      = reirie_field( 'member_message' );
    $photo        = get_the_post_thumbnail_url( get_the_ID(), 'large' );

    // SNS（SVGアイコン）
    $sns_links = array(
      'twitter' => array(
        'url'  => reirie_field( 'member_sns_twitter' ),
        'aria' => 'X (Twitter)',
        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
      ),
      'instagram' => array(
        'url'  => reirie_field( 'member_sns_instagram' ),
        'aria' => 'Instagram',
        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
      ),
      'tiktok' => array(
        'url'  => reirie_field( 'member_sns_tiktok' ),
        'aria' => 'TikTok',
        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.84-.1z"/></svg>',
      ),
      'youtube' => array(
        'url'  => reirie_field( 'member_sns_youtube' ),
        'aria' => 'YouTube',
        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6z"/></svg>',
      ),
      'blog' => array(
        'url'  => reirie_field( 'member_sns_blog' ),
        'aria' => 'Blog',
        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
      ),
    );

    // TikTok埋め込み動画
    $tiktok_videos = array_filter( array(
      reirie_field( 'member_tiktok_video_1' ),
      reirie_field( 'member_tiktok_video_2' ),
      reirie_field( 'member_tiktok_video_3' ),
    ) );

    // TikTok URLから動画IDを抽出するヘルパー
    $extract_tiktok_id = function( $url ) {
      if ( preg_match( '#/video/(\d+)#', $url, $m ) ) {
        return $m[1];
      }
      return '';
    };
  ?>

    <article class="single-member__article">

      <!-- 戻るボタン -->
      <div class="single-member__back-wrap">
        <a href="<?php echo esc_url( home_url( '/#profile' ) ); ?>" class="single-member__back">
          <span class="single-member__back-arrow">←</span>
          <span>Back</span>
        </a>
      </div>

      <div class="single-member__top">

        <!-- メインフォト -->
        <div class="single-member__photo">
          <?php if ( $photo ) : ?>
            <div class="single-member__photo-img" style="background-image:url('<?php echo esc_url( $photo ); ?>');"></div>
          <?php else : ?>
            <div class="member__photo photo-rei" style="height:100%;">
              <div class="member__photo-inner">
                <span class="initial" style="font-size:120px;"><?php echo esc_html( reirie_field( 'member_initial', false, 'R' ) ); ?></span>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- プロフィール情報 -->
        <div class="single-member__info">

          <!-- 名前 -->
          <h1 class="single-member__name" style="font-family:var(--display);font-size:clamp(48px,9vw,96px);line-height:1.1;margin:0 0 8px;letter-spacing:.02em;overflow:visible;">
            <span class="single-member__name-en <?php echo esc_attr( $color_cls ); ?>" style="background:linear-gradient(135deg,var(--pink-deep),#b07aff);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;display:inline-block;padding:.1em .15em;overflow:visible;line-height:1.1;">
              <?php the_title(); ?>
            </span>
          </h1>

          <?php if ( $name_jp ) : ?>
            <p style="font-family:var(--serif);letter-spacing:.3em;color:#888;margin:0 0 24px;font-size:14px;">
              <?php echo esc_html( $name_jp ); ?>
            </p>
          <?php endif; ?>

          <?php if ( $catch ) : ?>
            <p style="font-size:16px;color:#444;margin-bottom:24px;line-height:1.7;">
              <?php echo esc_html( $catch ); ?>
            </p>
          <?php endif; ?>

          <!-- SNSアイコン -->
          <?php
          $has_sns = false;
          foreach ( $sns_links as $s ) { if ( ! empty( $s['url'] ) ) { $has_sns = true; break; } }
          if ( $has_sns ) : ?>
            <div class="single-member__sns">
              <?php foreach ( $sns_links as $key => $s ) : if ( empty( $s['url'] ) ) continue; ?>
                <a class="member__sns-link member__sns--<?php echo esc_attr( $key ); ?>"
                   href="<?php echo esc_url( $s['url'] ); ?>"
                   target="_blank" rel="noopener"
                   aria-label="<?php echo esc_attr( $s['aria'] ); ?>"
                   title="<?php echo esc_attr( $s['aria'] ); ?>">
                  <span class="member__sns-icon"><?php echo $s['icon']; // SVG ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- プロフィール表 -->
          <dl class="single-member__data" style="display:grid;grid-template-columns:120px 1fr;gap:14px 24px;font-size:15px;border-top:1px dashed rgba(255,126,182,.4);padding-top:24px;">
            <?php
            // value が文字列なら esc_html してそのまま、配列 [ 'html' => '...' ] なら HTML としてそのまま出力
            $color_chips_html = reirie_member_color_chips_html( get_the_ID() );
            $rows = array(
              '生年月日'       => $birthday,
              'メンバーカラー' => $color_chips_html ? array( 'html' => $color_chips_html ) : '',
              '出身地'         => $hometown,
              '身長'           => $height,
              '血液型'         => $blood,
              '趣味'           => $hobby,
              '特技'           => $skill,
              'チャームポイント' => $charm,
              'MBTI'           => $mbti,
            );
            foreach ( $rows as $label => $val ) :
              if ( ! $val ) continue; ?>
              <dt style="font-family:var(--serif);color:var(--pink-deep);letter-spacing:.1em;font-size:13px;align-self:center;">
                <?php echo esc_html( $label ); ?>
              </dt>
              <dd style="margin:0;color:#333;border-bottom:1px dotted rgba(255,126,182,.2);padding-bottom:10px;">
                <?php
                if ( is_array( $val ) && isset( $val['html'] ) ) {
                  echo $val['html']; // 整形済みHTML
                } else {
                  echo esc_html( $val );
                }
                ?>
              </dd>
            <?php endforeach; ?>
          </dl>

          <?php if ( $message ) : ?>
            <div style="margin-top:32px;padding:20px 24px;background:linear-gradient(135deg,#fff5f9,#fff0fa);border-radius:16px;border:1px solid rgba(255,126,182,.2);">
              <p style="margin:0;font-size:15px;line-height:1.85;color:#555;">
                <?php echo esc_html( $message ); ?>
              </p>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- 本文 -->
      <?php if ( trim( strip_tags( get_the_content() ) ) !== '' ) : ?>
        <div class="single-member__content" style="margin-top:64px;font-size:15px;line-height:1.95;color:#444;max-width:780px;">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>

      <!-- TikTok埋め込み -->
      <?php if ( ! empty( $tiktok_videos ) ) : ?>
        <div class="single-member__tiktok" style="margin-top:72px;">
          <div class="section__head" style="text-align:center;margin-bottom:32px;">
            <span class="section__num">TikTok</span>
            <h2 class="section__title" style="font-size:clamp(28px,4vw,44px);">TikTok<span class="section__title-jp">動画</span></h2>
          </div>

          <div class="tiktok__grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(305px,1fr));gap:24px;justify-items:center;">
            <?php foreach ( $tiktok_videos as $tt_url ) :
              $vid = $extract_tiktok_id( $tt_url );
              if ( ! $vid ) continue; ?>
              <div class="tiktok__item" style="width:100%;max-width:325px;">
                <blockquote class="tiktok-embed" cite="<?php echo esc_url( $tt_url ); ?>" data-video-id="<?php echo esc_attr( $vid ); ?>" style="max-width:325px;min-width:325px;margin:0;">
                  <section></section>
                </blockquote>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <script async src="https://www.tiktok.com/embed.js"></script>
      <?php endif; ?>

      <!-- 戻るボタン（下部） -->
      <div style="text-align:center;margin-top:80px;">
        <a href="<?php echo esc_url( home_url( '/#profile' ) ); ?>" class="more-btn"><span>BACK TO PROFILE</span></a>
      </div>

    </article>

  <?php endwhile; endif; ?>
</main>

<style>
/* === 個別ページ レイアウト === */
.single-member__article {
  max-width: 1080px;
  margin: 0 auto;
  padding: 40px 20px 0;
}
.single-member__top {
  display: grid;
  grid-template-columns: minmax(280px, 440px) 1fr;
  gap: 56px;
  align-items: start;
}
.single-member__photo {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 18px 48px rgba(255,126,182,.28);
  aspect-ratio: 4/5;
  width: 100%;
}
.single-member__photo-img {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
}

/* === 個別ページ SNSアイコン（大きめ円形ボタン） === */
.single-member__sns {
  display: flex;
  gap: 12px;
  margin: 8px 0 36px;
  flex-wrap: wrap;
}
.single-member__sns .member__sns-link {
  width: 48px;
  height: 48px;
  padding: 0;          /* 親側インラインstyleの代替 — もう不要 */
  font-size: 0;        /* 万一のラベルテキスト保険 */
}
.single-member__sns .member__sns-icon {
  width: 18px;
  height: 18px;
}
.single-member__back-wrap {
  margin-bottom: 32px;
}
.single-member__back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--pink-deep);
  text-decoration: none;
  font-family: var(--serif);
  letter-spacing: .15em;
  font-size: 14px;
}
.single-member__back-arrow { font-size: 20px; }
.single-member__back:hover { opacity: .7; }

@media (max-width: 820px) {
  .single-member__top {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .single-member__photo {
    max-width: 380px;
    margin: 0 auto;
  }
  .single-member__data {
    grid-template-columns: 100px 1fr !important;
    gap: 10px 16px !important;
    font-size: 14px !important;
  }
  .single-member__sns .member__sns-link { width: 42px; height: 42px; }
  .single-member__sns .member__sns-icon { width: 16px; height: 16px; }
}
@media (max-width: 820px) {
  .single-member__article { padding: 8px 18px 0; }
  .single-member__back-wrap { margin-bottom: 18px; }
}
@media (max-width: 480px) {
  .single-member__article { padding: 0 14px; }
  .single-member__back-wrap { margin-bottom: 12px; }
  .single-member__back { font-size: 13px; }
  .single-member__top { gap: 24px; }
  .single-member__photo { max-width: 100%; }
  .single-member__data {
    grid-template-columns: 90px 1fr !important;
    gap: 8px 14px !important;
    font-size: 13px !important;
  }
}
</style>

<?php get_footer(); ?>

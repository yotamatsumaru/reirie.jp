<?php
/**
 * Profile Section（メンバー紹介）
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$member_query = new WP_Query( array(
	'post_type'      => 'member',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
) );
?>

<section class="section profile" id="profile">
  <div class="section__head">
    <span class="section__num">05 / Profile</span>
    <h2 class="section__title">Profile<span class="section__title-jp">プロフィール</span></h2>
  </div>

  <div class="member__grid">
    <?php if ( $member_query->have_posts() ) :
      $i = 0;
      while ( $member_query->have_posts() ) : $member_query->the_post();
        $color       = reirie_field( 'member_color', false, '' );
        $color_cls   = reirie_field( 'member_color_class', false, ( $i === 0 ? 'color-pink' : 'color-blue' ) );
        $photo_cls   = reirie_field( 'member_photo_class', false, ( $i === 0 ? 'photo-rei' : 'photo-rie' ) );
        $initial     = reirie_field( 'member_initial', false, 'R' );
        $name_jp     = reirie_field( 'member_name_jp' );
        $catch       = reirie_field( 'member_catch' );
        $birthday    = reirie_field( 'member_birthday' );
        $blood       = reirie_field( 'member_blood' );
        $hometown    = reirie_field( 'member_hometown' );
        $height      = reirie_field( 'member_height' );
        $hobby       = reirie_field( 'member_hobby' );
        $charm       = reirie_field( 'member_charm' );
        $skill       = reirie_field( 'member_skill' );
        $message     = reirie_field( 'member_message' );
        $photo       = get_the_post_thumbnail_url( get_the_ID(), 'reirie-member' );

        // SNS（ラベル/SVGアイコン）
        $sns_links = array(
          'twitter' => array(
            'url'    => reirie_field( 'member_sns_twitter' ),
            'label'  => 'X',
            'aria'   => 'X (Twitter)',
            'icon'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
          ),
          'instagram' => array(
            'url'    => reirie_field( 'member_sns_instagram' ),
            'label'  => 'Instagram',
            'aria'   => 'Instagram',
            'icon'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
          ),
          'tiktok' => array(
            'url'    => reirie_field( 'member_sns_tiktok' ),
            'label'  => 'TikTok',
            'aria'   => 'TikTok',
            'icon'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.84-.1z"/></svg>',
          ),
          'youtube' => array(
            'url'    => reirie_field( 'member_sns_youtube' ),
            'label'  => 'YouTube',
            'aria'   => 'YouTube',
            'icon'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6z"/></svg>',
          ),
          'blog' => array(
            'url'    => reirie_field( 'member_sns_blog' ),
            'label'  => 'BLOG',
            'aria'   => 'Blog',
            'icon'   => '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
          ),
        );
    ?>
      <article class="member__card">
        <a class="member__card-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?> のプロフィール詳細を見る">
          <div class="member__photo <?php echo esc_attr( $photo_cls ); ?>"<?php echo $photo ? ' style="background-image:url(' . esc_url( $photo ) . ');background-size:cover;background-position:center;"' : ''; ?>>
            <?php if ( ! $photo ) : ?>
              <div class="member__photo-inner">
                <span class="initial"><?php echo esc_html( $initial ); ?></span>
              </div>
            <?php endif; ?>
          </div>
          <div class="member__info">
            <h3 class="member__name">
              <span class="en"><?php the_title(); ?></span>
              <?php if ( $name_jp ) : ?><span class="ja"><?php echo esc_html( $name_jp ); ?></span><?php endif; ?>
            </h3>
            <?php if ( $catch ) : ?>
              <p class="member__catch"><?php echo esc_html( $catch ); ?></p>
            <?php endif; ?>
            <dl class="member__data">
              <?php if ( $birthday ) : ?><div><dt>Birthday</dt><dd><?php echo esc_html( $birthday ); ?></dd></div><?php endif; ?>
              <?php $color_chips_html = reirie_member_color_chips_html( get_the_ID() ); ?>
              <?php if ( $color_chips_html ) : ?><div><dt>Color</dt><dd><?php echo $color_chips_html; // ヘルパーで整形済HTML ?></dd></div><?php endif; ?>
              <?php if ( $blood ) : ?><div><dt>Blood Type</dt><dd><?php echo esc_html( $blood ); ?></dd></div><?php endif; ?>
              <?php if ( $hometown ) : ?><div><dt>Hometown</dt><dd><?php echo esc_html( $hometown ); ?></dd></div><?php endif; ?>
              <?php if ( $height ) : ?><div><dt>Height</dt><dd><?php echo esc_html( $height ); ?></dd></div><?php endif; ?>
              <?php if ( $hobby ) : ?><div><dt>Hobby</dt><dd><?php echo esc_html( $hobby ); ?></dd></div><?php endif; ?>
              <?php if ( $charm ) : ?><div><dt>Charm Point</dt><dd><?php echo esc_html( $charm ); ?></dd></div><?php endif; ?>
              <?php if ( $skill ) : ?><div><dt>Skill</dt><dd><?php echo esc_html( $skill ); ?></dd></div><?php endif; ?>
            </dl>
            <?php if ( $message ) : ?>
              <p class="member__msg"><?php echo esc_html( $message ); ?></p>
            <?php endif; ?>
          </div>
        </a>

        <?php
        // SNS は外側カードリンクの外に置く（<a>のネスト回避）
        $has_sns = false;
        foreach ( $sns_links as $s ) { if ( ! empty( $s['url'] ) ) { $has_sns = true; break; } }
        if ( $has_sns ) : ?>
          <div class="member__sns">
            <?php foreach ( $sns_links as $key => $s ) : if ( empty( $s['url'] ) ) continue; ?>
              <a class="member__sns-link member__sns--<?php echo esc_attr( $key ); ?>"
                 href="<?php echo esc_url( $s['url'] ); ?>"
                 target="_blank" rel="noopener"
                 aria-label="<?php echo esc_attr( $s['aria'] ); ?>"
                 title="<?php echo esc_attr( $s['aria'] ); ?>">
                <span class="member__sns-icon"><?php echo $s['icon']; // SVG, intentionally not escaped ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </article>
    <?php $i++; endwhile; wp_reset_postdata(); else : ?>

      <?php if ( current_user_can( 'edit_posts' ) ) : ?>
        <div style="grid-column:1/-1;padding:48px 32px;background:linear-gradient(135deg,#fff5f9,#fff0fa);border:2px dashed rgba(255,126,182,.4);border-radius:24px;text-align:center;">
          <p style="font-family:var(--serif);font-size:18px;color:var(--pink-deep);margin:0 0 12px;letter-spacing:.1em;">
            👯 メンバー情報が未登録です
          </p>
          <p style="color:#666;font-size:14px;line-height:1.8;margin:0 0 20px;">
            WordPress 管理画面の「Member」メニューからメンバー投稿を追加すると、<br>
            ここにプロフィールカードが表示され、クリックで個別ページへ遷移できるようになります。
          </p>
          <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=member' ) ); ?>" class="more-btn">
            <span>＋ メンバーを追加</span>
          </a>
          <p style="color:#999;font-size:12px;margin:16px 0 0;">※ このメッセージはログイン中の管理者のみ表示されます</p>
        </div>
      <?php else : ?>
        <article class="member__card">
          <div class="member__photo photo-rei"><div class="member__photo-inner"><span class="initial">R</span></div></div>
          <div class="member__info">
            <p class="member__color color-pink">PINK</p>
            <h3 class="member__name"><span class="en">Rei</span><span class="ja">レイ</span></h3>
            <dl class="member__data">
              <div><dt>Birthday</dt><dd>9月12日</dd></div>
              <div><dt>Hometown</dt><dd>東京都</dd></div>
              <div><dt>Hobby</dt><dd>カフェ巡り / 写真</dd></div>
            </dl>
            <p class="member__msg">Coming Soon...</p>
          </div>
        </article>
        <article class="member__card">
          <div class="member__photo photo-rie"><div class="member__photo-inner"><span class="initial">R</span></div></div>
          <div class="member__info">
            <p class="member__color color-blue">SKY BLUE</p>
            <h3 class="member__name"><span class="en">Rie</span><span class="ja">リエ</span></h3>
            <dl class="member__data">
              <div><dt>Birthday</dt><dd>3月3日</dd></div>
              <div><dt>Hometown</dt><dd>大阪府</dd></div>
              <div><dt>Hobby</dt><dd>お菓子作り / ダンス</dd></div>
            </dl>
            <p class="member__msg">Coming Soon...</p>
          </div>
        </article>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</section>

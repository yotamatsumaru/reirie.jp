<?php
/**
 * Template Name: REIRIE 運営会社
 *
 * @package REIRIE
 */

get_header();

$company_name    = get_theme_mod( 'reirie_company_name', 'REIRIE OFFICIAL' );
$company_name_en = get_theme_mod( 'reirie_company_name_en', '' );
$ceo             = get_theme_mod( 'reirie_company_ceo', '' );
$founded         = get_theme_mod( 'reirie_company_founded', '' );
$zipcode         = get_theme_mod( 'reirie_company_zipcode', '' );
$address         = get_theme_mod( 'reirie_company_address', '' );
$business        = get_theme_mod( 'reirie_company_business', '' );
$email           = get_theme_mod( 'reirie_company_email', '' );
$tel             = get_theme_mod( 'reirie_company_tel', '' );
$website         = get_theme_mod( 'reirie_company_website', home_url( '/' ) );
?>

<main class="section legal-page" id="company">
  <div class="section__head">
    <span class="section__num">— / Company</span>
    <h2 class="section__title">Company<span class="section__title-jp">運営会社</span></h2>
  </div>

  <article class="legal-page__article" style="max-width:880px;margin:0 auto;padding:0 20px;">

    <?php if ( trim( strip_tags( get_post_field( 'post_content', get_the_ID() ) ) ) !== '' ) :
      while ( have_posts() ) : the_post(); ?>
        <div class="legal-page__content" style="font-size:15px;line-height:1.95;color:#444;">
          <?php the_content(); ?>
        </div>
      <?php endwhile;
    else : ?>

      <div class="legal-page__content" style="font-size:15px;line-height:1.95;color:#444;">

        <div style="text-align:center;margin-bottom:48px;padding:32px 24px;background:linear-gradient(135deg,#fff5f9,#fff0fa);border-radius:20px;border:1px solid rgba(255,126,182,.2);">
          <h3 style="font-family:var(--display);font-size:clamp(32px,5vw,48px);margin:0 0 8px;background:linear-gradient(135deg,var(--pink-deep),#b07aff);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:.04em;">
            <?php echo esc_html( $company_name ); ?>
          </h3>
          <?php if ( $company_name_en ) : ?>
            <p style="font-family:var(--serif);letter-spacing:.3em;color:#888;margin:0;font-size:14px;">
              <?php echo esc_html( $company_name_en ); ?>
            </p>
          <?php endif; ?>
        </div>

        <dl class="legal-dl">

          <dt>会社名</dt>
          <dd>
            <?php echo esc_html( $company_name ); ?>
            <?php if ( $company_name_en ) : ?>
              <br><span style="font-size:13px;color:#888;font-family:var(--serif);letter-spacing:.15em;"><?php echo esc_html( $company_name_en ); ?></span>
            <?php endif; ?>
          </dd>

          <?php if ( $ceo ) : ?>
            <dt>代表者</dt>
            <dd><?php echo esc_html( $ceo ); ?></dd>
          <?php endif; ?>

          <?php if ( $founded ) : ?>
            <dt>設立</dt>
            <dd><?php echo esc_html( $founded ); ?></dd>
          <?php endif; ?>

          <?php if ( $zipcode || $address ) : ?>
            <dt>所在地</dt>
            <dd>
              <?php if ( $zipcode ) : ?>〒<?php echo esc_html( $zipcode ); ?><br><?php endif; ?>
              <?php echo nl2br( esc_html( $address ) ); ?>
            </dd>
          <?php endif; ?>

          <?php if ( $business ) : ?>
            <dt>事業内容</dt>
            <dd><?php echo nl2br( esc_html( $business ) ); ?></dd>
          <?php endif; ?>

          <?php if ( $tel ) : ?>
            <dt>電話番号</dt>
            <dd><?php echo esc_html( $tel ); ?></dd>
          <?php endif; ?>

          <?php if ( $email ) : ?>
            <dt>E-mail</dt>
            <dd><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:var(--pink-deep);"><?php echo esc_html( $email ); ?></a></dd>
          <?php endif; ?>

          <dt>お問い合わせ</dt>
          <dd>
            各種お問い合わせは下記フォームよりお願いいたします。<br>
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="color:var(--pink-deep);">→ お問い合わせフォーム</a>
          </dd>

          <?php if ( $website ) : ?>
            <dt>公式サイト</dt>
            <dd><a href="<?php echo esc_url( $website ); ?>" style="color:var(--pink-deep);"><?php echo esc_html( $website ); ?></a></dd>
          <?php endif; ?>

        </dl>

      </div>

    <?php endif; ?>

    <div style="text-align:center;margin-top:60px;">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO TOP</span></a>
    </div>

  </article>
</main>

<?php get_footer(); ?>

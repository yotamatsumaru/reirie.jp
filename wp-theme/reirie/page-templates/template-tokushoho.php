<?php
/**
 * Template Name: REIRIE 特定商取引法に基づく表示
 *
 * @package REIRIE
 */

get_header();

$company_name   = get_theme_mod( 'reirie_company_name', 'REIRIE OFFICIAL' );
$seller         = get_theme_mod( 'reirie_tokushoho_seller', '' );
$manager        = get_theme_mod( 'reirie_tokushoho_manager', '' );
$zipcode        = get_theme_mod( 'reirie_company_zipcode', '' );
$address        = get_theme_mod( 'reirie_company_address', '' );
$tel            = get_theme_mod( 'reirie_company_tel', '' );
$email          = get_theme_mod( 'reirie_company_email', '' );
$price          = get_theme_mod( 'reirie_tokushoho_price', '' );
$extra_fee      = get_theme_mod( 'reirie_tokushoho_extra_fee', '' );
$payment        = get_theme_mod( 'reirie_tokushoho_payment', '' );
$delivery       = get_theme_mod( 'reirie_tokushoho_delivery', '' );
$returns        = get_theme_mod( 'reirie_tokushoho_return', '' );

// 追加項目（お問い合わせ・受付時間・準拠法・紛争解決）
$intro_note     = get_theme_mod( 'reirie_tokushoho_intro_note', "特定商取引法に基づき、事業所の所在地を明示しています。\nご不明な点がございましたら、下記のお問い合わせ先までご連絡ください。" );
$contact_email  = get_theme_mod( 'reirie_tokushoho_contact_email', '' );
$contact_hours  = get_theme_mod( 'reirie_tokushoho_contact_hours', "10:00〜18:00（土日祝日も対応）\n※営業時間外のお問い合わせは翌営業日以降の対応となります" );
$governing_law  = get_theme_mod( 'reirie_tokushoho_governing_law', '本取引は日本法に準拠し、日本法に従って解釈されます。' );
$jurisdiction   = get_theme_mod( 'reirie_tokushoho_jurisdiction', '本サービスに関して紛争が生じた場合、東京地方裁判所を第一審の専属的合意管轄裁判所とします。' );

$seller   = $seller ? $seller : $company_name;
$display_manager = $manager ? $manager : get_theme_mod( 'reirie_company_ceo', '' );
// メールアドレスが個別指定されていなければ運営会社情報のメールを使う
$display_contact_email = $contact_email ? $contact_email : $email;
?>

<main class="section legal-page" id="tokushoho">
  <div class="section__head">
    <span class="section__num">— / Tokushoho</span>
    <h2 class="section__title">Tokushoho<span class="section__title-jp">特定商取引法に基づく表示</span></h2>
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

        <p style="margin-bottom:32px;">
          特定商取引に関する法律第11条に基づき、以下のとおり表示いたします。
        </p>

        <?php if ( $intro_note ) : ?>
          <div class="legal-box" style="margin-bottom:32px;">
            <p style="margin:0;"><?php echo nl2br( esc_html( $intro_note ) ); ?></p>
          </div>
        <?php endif; ?>

        <dl class="legal-dl">

          <dt>販売事業者</dt>
          <dd><?php echo esc_html( $seller ); ?></dd>

          <?php if ( $display_manager ) : ?>
            <dt>運営統括責任者</dt>
            <dd><?php echo esc_html( $display_manager ); ?></dd>
          <?php endif; ?>

          <?php if ( $zipcode || $address ) : ?>
            <dt>所在地</dt>
            <dd>
              <?php if ( $zipcode ) : ?>〒<?php echo esc_html( $zipcode ); ?><br><?php endif; ?>
              <?php echo nl2br( esc_html( $address ) ); ?>
            </dd>
          <?php endif; ?>

          <?php if ( $tel ) : ?>
            <dt>電話番号</dt>
            <dd><?php echo esc_html( $tel ); ?><br><span style="font-size:13px;color:#888;">※お問い合わせはメールフォームを推奨しております。</span></dd>
          <?php endif; ?>

          <?php if ( $email ) : ?>
            <dt>メールアドレス</dt>
            <dd><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:var(--pink-deep);"><?php echo esc_html( $email ); ?></a></dd>
          <?php else : ?>
            <dt>お問い合わせ</dt>
            <dd><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="color:var(--pink-deep);">お問い合わせフォーム</a>よりご連絡ください。</dd>
          <?php endif; ?>

          <dt>販売価格</dt>
          <dd><?php echo nl2br( esc_html( $price ) ); ?></dd>

          <dt>商品代金以外の必要料金</dt>
          <dd><?php echo nl2br( esc_html( $extra_fee ) ); ?></dd>

          <dt>お支払い方法</dt>
          <dd><?php echo nl2br( esc_html( $payment ) ); ?></dd>

          <dt>お支払い時期</dt>
          <dd>
            ・クレジットカード：商品ご注文時にお支払いが確定します。<br>
            ・銀行振込：ご注文後7日以内にお振込みください。<br>
            ・代金引換：商品お受け取り時に配送業者にお支払いください。
          </dd>

          <dt>商品の引渡し時期</dt>
          <dd><?php echo nl2br( esc_html( $delivery ) ); ?></dd>

          <dt>返品・交換について</dt>
          <dd><?php echo nl2br( esc_html( $returns ) ); ?></dd>

          <?php if ( $display_contact_email || $contact_hours ) : ?>
            <dt>お問い合わせ（連絡先）</dt>
            <dd>
              <?php if ( $display_contact_email ) : ?>
                <strong style="display:block;margin-bottom:4px;color:#666;font-weight:600;font-size:13px;letter-spacing:.05em;">メールアドレス</strong>
                <a href="mailto:<?php echo esc_attr( $display_contact_email ); ?>" style="color:var(--pink-deep);"><?php echo esc_html( $display_contact_email ); ?></a>
              <?php endif; ?>
              <?php if ( $contact_hours ) : ?>
                <div style="margin-top:14px;">
                  <strong style="display:block;margin-bottom:4px;color:#666;font-weight:600;font-size:13px;letter-spacing:.05em;">受付時間</strong>
                  <?php echo nl2br( esc_html( $contact_hours ) ); ?>
                </div>
              <?php endif; ?>
            </dd>
          <?php endif; ?>

          <?php if ( $governing_law ) : ?>
            <dt>準拠法</dt>
            <dd><?php echo nl2br( esc_html( $governing_law ) ); ?></dd>
          <?php endif; ?>

          <?php if ( $jurisdiction ) : ?>
            <dt>紛争解決</dt>
            <dd><?php echo nl2br( esc_html( $jurisdiction ) ); ?></dd>
          <?php endif; ?>

          <dt>動作環境</dt>
          <dd>
            本サイトでは以下のブラウザの最新版を推奨しております。<br>
            Google Chrome / Safari / Microsoft Edge / Firefox
          </dd>

        </dl>

      </div>

    <?php endif; ?>

    <div style="text-align:center;margin-top:60px;">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO TOP</span></a>
    </div>

  </article>
</main>

<?php get_footer(); ?>

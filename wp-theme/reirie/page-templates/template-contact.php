<?php
/**
 * Template Name: REIRIE お問い合わせフォーム
 *
 * @package REIRIE
 */

get_header();

// クエリパラメータから種別を初期選択
$initial_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'press';
$labels       = reirie_contact_type_labels();
// ファンレターは別ページに分離したので、互換のため fanmail 指定でも press にフォールバック
if ( ! isset( $labels[ $initial_type ] ) ) {
	$initial_type = 'press';
}
$intro        = get_theme_mod( 'reirie_contact_intro', 'お問い合わせ内容に応じて種別をお選びください。担当者よりご連絡いたします。' );
$privacy_url  = get_theme_mod( 'reirie_privacy_url', '#' );
?>

<main class="section contact-form-section" id="contact-form">
  <div class="section__head">
    <span class="section__num">— / Contact</span>
    <h2 class="section__title">Contact<span class="section__title-jp">お問い合わせ</span></h2>
  </div>

  <div class="contact-form-wrap">
    <p class="contact-form-intro"><?php echo esc_html( $intro ); ?></p>

    <form id="reirie-contact-form" class="reirie-form" novalidate>
      <?php wp_nonce_field( 'reirie_contact_submit', 'reirie_contact_nonce' ); ?>
      <input type="hidden" name="action" value="reirie_contact_submit">
      <input type="hidden" name="form_ts" value="<?php echo esc_attr( time() ); ?>">

      <!-- ハニーポット（CSSで隠す。BOTのみ入力するトラップ） -->
      <div class="reirie-form__honeypot" aria-hidden="true">
        <label>Website（記入しないでください）<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>

      <!-- 種別 -->
      <div class="reirie-form__row">
        <label class="reirie-form__label">お問い合わせ種別<span class="req">必須</span></label>
        <div class="reirie-form__types">
          <?php foreach ( $labels as $key => $label ) : ?>
            <label class="reirie-form__type">
              <input type="radio" name="type" value="<?php echo esc_attr( $key ); ?>"<?php checked( $key, $initial_type ); ?>>
              <span class="reirie-form__type-box">
                <span class="reirie-form__type-icon"><?php
                  switch ( $key ) {
                    case 'press':    echo '📰'; break;
                    case 'casting':  echo '🎬'; break;
                    case 'other':    echo '✉️'; break;
                  }
                ?></span>
                <span class="reirie-form__type-label"><?php echo esc_html( $label ); ?></span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
        <p class="reirie-form__error" data-error="type"></p>
      </div>

      <!-- お名前 -->
      <div class="reirie-form__row">
        <label class="reirie-form__label" for="contact-name">お名前<span class="req">必須</span></label>
        <input type="text" id="contact-name" name="name" class="reirie-form__input" placeholder="例：山田 花子" required>
        <p class="reirie-form__error" data-error="name"></p>
      </div>

      <!-- メール -->
      <div class="reirie-form__row">
        <label class="reirie-form__label" for="contact-email">メールアドレス<span class="req">必須</span></label>
        <input type="email" id="contact-email" name="email" class="reirie-form__input" placeholder="例：example@mail.com" required>
        <p class="reirie-form__error" data-error="email"></p>
      </div>

      <!-- 会社名・媒体名（取材/出演依頼で必須） -->
      <div class="reirie-form__row" data-show-for="press,casting">
        <label class="reirie-form__label" for="contact-company">会社名・媒体名<span class="req req--cond">必須</span></label>
        <input type="text" id="contact-company" name="company" class="reirie-form__input" placeholder="例：株式会社○○ / ○○マガジン">
        <p class="reirie-form__error" data-error="company"></p>
      </div>

      <!-- 電話番号（任意） -->
      <div class="reirie-form__row" data-show-for="press,casting,other">
        <label class="reirie-form__label" for="contact-tel">電話番号<span class="opt">任意</span></label>
        <input type="tel" id="contact-tel" name="tel" class="reirie-form__input" placeholder="例：03-0000-0000">
      </div>

      <!-- 件名（任意） -->
      <div class="reirie-form__row">
        <label class="reirie-form__label" for="contact-subject">件名<span class="opt">任意</span></label>
        <input type="text" id="contact-subject" name="subject" class="reirie-form__input" placeholder="未入力の場合は自動で件名がつきます">
      </div>

      <!-- メッセージ -->
      <div class="reirie-form__row">
        <label class="reirie-form__label" for="contact-message">メッセージ<span class="req">必須</span></label>
        <textarea id="contact-message" name="message" rows="8" class="reirie-form__textarea" placeholder="お問い合わせ内容をご記入ください" required></textarea>
        <p class="reirie-form__counter"><span data-count="0">0</span> / 3000</p>
        <p class="reirie-form__error" data-error="message"></p>
      </div>

      <!-- プライバシーポリシー同意 -->
      <div class="reirie-form__row reirie-form__row--privacy">
        <label class="reirie-form__check">
          <input type="checkbox" name="privacy" value="1" required>
          <span><a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">プライバシーポリシー</a>に同意する</span>
        </label>
        <p class="reirie-form__error" data-error="privacy"></p>
      </div>

      <!-- 送信ボタン -->
      <div class="reirie-form__submit">
        <button type="submit" class="reirie-form__btn">
          <span class="reirie-form__btn-text">SEND MESSAGE</span>
          <span class="reirie-form__btn-loader"></span>
        </button>
      </div>

      <!-- 完了/エラーメッセージ表示エリア -->
      <div class="reirie-form__result" id="reirie-form-result" aria-live="polite"></div>
    </form>
  </div>
</main>

<?php get_footer(); ?>

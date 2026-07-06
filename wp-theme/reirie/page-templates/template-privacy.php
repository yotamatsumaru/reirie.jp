<?php
/**
 * Template Name: REIRIE プライバシーポリシー
 *
 * 全13章構成のプライバシーポリシー。
 * 管理画面 → REIRIE設定 → プライバシーポリシー で
 * 各章の本文・お問い合わせ情報すべてを編集できる。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

/* ============================================================
   設定値の取得（運営会社情報からの自動引用 + 個別設定）
   ============================================================ */
$company_name   = get_theme_mod( 'reirie_company_name', 'REIRIE OFFICIAL' );
$company_email  = get_theme_mod( 'reirie_company_email', '' );

$updated_date   = get_theme_mod( 'reirie_privacy_updated', '' );
$established    = get_theme_mod( 'reirie_privacy_established', '2025年1月1日' );

$service_name   = get_theme_mod( 'reirie_privacy_service_name', 'REIRIE' );
$intro          = get_theme_mod( 'reirie_privacy_intro', '本サービスにおいて取得した個人情報を以下のとおり取り扱います。' );

// 各章の本文
$sec1  = get_theme_mod( 'reirie_privacy_sec1',  '本プライバシーポリシーにおいて「個人情報」とは、個人情報保護法に定める「個人情報」を指し、生存する個人に関する情報であって、当該情報に含まれる氏名、メールアドレス、その他の記述等により特定の個人を識別できる情報、および個人識別符号が含まれる情報を指します。' );

$sec2_intro     = get_theme_mod( 'reirie_privacy_sec2_intro', '当社は、ユーザーが利用登録をする際、および本サービスを利用する際に、以下の個人情報を収集します。' );
$sec2_buyer     = get_theme_mod( 'reirie_privacy_sec2_buyer', "氏名（ニックネームまたは本名）\nメールアドレス\nパスワード（暗号化して保存）\n決済情報（クレジットカード情報等、Stripe経由で取得・処理）\n購入履歴・視聴履歴\nIPアドレス、ブラウザ情報、デバイス情報\nCookie情報" );
$sec2_creator   = get_theme_mod( 'reirie_privacy_sec2_creator', "氏名または法人名\nメールアドレス\nパスワード（暗号化して保存）\n銀行口座情報（売上金精算用）\n事業者情報（法人の場合）\n本人確認書類（必要に応じて）\nコンテンツ情報・販売履歴" );

$sec3_intro     = get_theme_mod( 'reirie_privacy_sec3_intro', '当社は、収集した個人情報を以下の目的で利用します。' );
$sec3_list      = get_theme_mod( 'reirie_privacy_sec3_list', "本サービスの提供、運営、改善\nユーザー登録、認証、本人確認\nチケット購入、決済処理\nクリエイターへの売上金精算\n購入履歴、視聴履歴の管理\nカスタマーサポート、お問い合わせ対応\n本サービスに関する情報のご案内（メールマガジン等）\n利用状況の分析、統計データの作成（個人を特定できない形式）\n不正行為の防止、規約違反への対応\n利用規約違反またはその疑いのある行為への対応\nその他、上記利用目的に付随する目的" );

$sec4           = get_theme_mod( 'reirie_privacy_sec4', "1. 当社は、利用目的が変更前と関連性を有すると合理的に認められる場合に限り、個人情報の利用目的を変更するものとします。\n\n2. 利用目的の変更を行った場合には、変更後の目的について、当社所定の方法により、ユーザーに通知し、または本ウェブサイト上に公表するものとします。" );

$sec5_intro     = get_theme_mod( 'reirie_privacy_sec5_intro', '当社は、次に掲げる場合を除いて、あらかじめユーザーの同意を得ることなく、第三者に個人情報を提供することはありません。' );
$sec5_cases     = get_theme_mod( 'reirie_privacy_sec5_cases', "法令に基づく場合\n人の生命、身体または財産の保護のために必要がある場合であって、本人の同意を得ることが困難であるとき\n公衆衛生の向上または児童の健全な育成の推進のために特に必要がある場合であって、本人の同意を得ることが困難であるとき\n国の機関もしくは地方公共団体またはその委託を受けた者が法令の定める事務を遂行することに対して協力する必要がある場合であって、本人の同意を得ることにより当該事務の遂行に支障を及ぼすおそれがあるとき" );
$sec5_outsource_lead = get_theme_mod( 'reirie_privacy_sec5_outsource_lead', '当社は、利用目的の達成に必要な範囲内において、個人情報の取扱いの全部または一部を委託する場合があります。この場合、委託先との間で適切な個人情報保護に関する契約を締結し、委託先に対する必要かつ適切な監督を行います。' );
$sec5_outsource_list = get_theme_mod( 'reirie_privacy_sec5_outsource_list', "Stripe, Inc. - 決済処理（クレジットカード情報の処理）\nクラウドサーバー事業者 - データ保管\nメール配信サービス事業者 - メール配信" );

$sec6  = get_theme_mod( 'reirie_privacy_sec6',  "1. 当社は、本人から個人情報の開示を求められたときは、本人に対し、遅滞なくこれを開示します。ただし、開示することにより次のいずれかに該当する場合は、その全部または一部を開示しないこともあり、開示しない決定をした場合には、その旨を遅滞なく通知します。\n\n本人または第三者の生命、身体、財産その他の権利利益を害するおそれがある場合\n当社の業務の適正な実施に著しい支障を及ぼすおそれがある場合\nその他法令に違反することとなる場合\n\n2. 前項の定めにかかわらず、履歴情報および特性情報などの個人情報以外の情報については、原則として開示いたしません。" );
$sec7  = get_theme_mod( 'reirie_privacy_sec7',  "1. ユーザーは、当社の保有する自己の個人情報が誤った情報である場合には、当社が定める手続きにより、当社に対して個人情報の訂正、追加または削除（以下「訂正等」といいます）を請求することができます。\n\n2. 当社は、ユーザーから前項の請求を受けてその請求に応じる必要があると判断した場合には、遅滞なく、当該個人情報の訂正等を行うものとします。\n\n3. 当社は、前項の規定に基づき訂正等を行った場合、または訂正等を行わない旨の決定をしたときは遅滞なく、これをユーザーに通知します。" );
$sec8  = get_theme_mod( 'reirie_privacy_sec8',  "1. 当社は、本人から、個人情報が、利用目的の範囲を超えて取り扱われているという理由、または不正の手段により取得されたものであるという理由により、その利用の停止または消去（以下「利用停止等」といいます）を求められた場合には、遅滞なく必要な調査を行います。\n\n2. 前項の調査結果に基づき、その請求に応じる必要があると判断した場合には、遅滞なく、当該個人情報の利用停止等を行います。\n\n3. 当社は、前項の規定に基づき利用停止等を行った場合、または利用停止等を行わない旨の決定をしたときは、遅滞なく、これをユーザーに通知します。\n\n4. 前二項にかかわらず、利用停止等に多額の費用を有する場合その他利用停止等を行うことが困難な場合であって、ユーザーの権利利益を保護するために必要なこれに代わるべき措置をとれる場合は、この代替策を講じるものとします。" );

$sec9_intro     = get_theme_mod( 'reirie_privacy_sec9_intro', '1. 本サービスでは、ユーザーの利便性向上およびサービス改善のため、Cookie（クッキー）を使用します。' );
$sec9_purposes  = get_theme_mod( 'reirie_privacy_sec9_purposes', "ログイン状態の維持\nユーザー設定の保存\nアクセス解析（Google Analytics等）\n広告配信の最適化\nサービス利用状況の把握" );
$sec9_outro     = get_theme_mod( 'reirie_privacy_sec9_outro', "2. ユーザーは、ブラウザの設定によりCookieの受け取りを拒否することができます。ただし、Cookieを無効にした場合、本サービスの一部機能が利用できなくなる場合があります。\n\nCookieの無効化方法は、各ブラウザの設定画面からご確認ください。" );

$sec10_intro    = get_theme_mod( 'reirie_privacy_sec10_intro', '当社は、個人情報の紛失、破壊、改ざんおよび漏洩などのリスクに対して、個人情報の安全管理が図られるよう、当社の従業員に対し、必要かつ適切な監督を行います。' );
$sec10_measures = get_theme_mod( 'reirie_privacy_sec10_measures', "SSL/TLS暗号化通信の採用\nパスワードの暗号化保存（ハッシュ化）\n決済情報のStripe経由での安全な処理（当社はカード情報を保持しません）\nアクセス制限およびアクセスログの管理\nファイアウォールによる不正アクセス防止\n定期的なセキュリティ診断の実施\n従業員への個人情報保護教育の実施" );

$sec11_intro    = get_theme_mod( 'reirie_privacy_sec11_intro', '当社は、個人情報を、利用目的の達成に必要な期間に限り保存します。ただし、法令により保存期間が定められている場合は、当該期間保存します。' );
$sec11_list     = get_theme_mod( 'reirie_privacy_sec11_list', "アカウント情報：アカウント削除後、1年間\n購入履歴：会計法令に基づき、最低7年間\nお問い合わせ履歴：対応完了後、3年間\nアクセスログ：6ヶ月間" );

$sec12          = get_theme_mod( 'reirie_privacy_sec12', '未成年者が本サービスを利用する場合は、親権者その他の法定代理人の同意を得た上で利用するものとします。未成年者が本サービスを利用した場合、当社は、親権者その他の法定代理人の同意があったものとみなします。' );
$sec13          = get_theme_mod( 'reirie_privacy_sec13', "1. 本ポリシーの内容は、法令その他本ポリシーに別段の定めのある事項を除いて、ユーザーに通知することなく、変更することができるものとします。\n\n2. 当社が別途定める場合を除いて、変更後のプライバシーポリシーは、本ウェブサイトに掲載したときから効力を生じるものとします。\n\n3. 本ポリシーの変更後、ユーザーが本サービスを利用した場合、変更後のプライバシーポリシーに同意したものとみなします。" );

// お問い合わせ窓口
$contact_intro  = get_theme_mod( 'reirie_privacy_contact_intro', '本ポリシーに関するお問い合わせ、開示等の請求、その他個人情報の取扱いに関するご質問・ご相談は、以下の窓口までお願いいたします。' );
$contact_manager = get_theme_mod( 'reirie_privacy_contact_manager', '個人情報保護責任者' );
$contact_email  = get_theme_mod( 'reirie_privacy_contact_email', '' );
$contact_hours  = get_theme_mod( 'reirie_privacy_contact_hours', "10:00〜18:00（土日祝日も対応）\n※営業時間外のお問い合わせは翌営業日以降の対応となります" );
$disclosure_note = get_theme_mod( 'reirie_privacy_disclosure_note', '個人情報の開示、訂正、利用停止等をご希望される場合は、本人確認を行った上で対応させていただきます。請求方法の詳細については、上記お問い合わせ先までご連絡ください。' );

// メールアドレスは個別指定がなければ運営会社情報のものを使用
$display_email  = $contact_email ? $contact_email : $company_email;

// 最終更新日が未設定なら本日の日付
if ( ! $updated_date ) {
	$updated_date = date_i18n( 'Y年n月j日' );
}

// 章タイトル & 本文をまとめて配列化（順序固定）
$intro_replaced = str_replace( array( '{company}', '{service}' ), array( $company_name, $service_name ), $intro );

// 改行をリストアイテムに変換するヘルパー
$lines_to_items = function( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$items = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( $line === '' ) continue;
		$line = preg_replace( '/^[・\-\*\s]+/u', '', $line );
		$items[] = $line;
	}
	return $items;
};
?>

<main class="section legal-page privacy-page" id="privacy-policy">
  <div class="section__head">
    <span class="section__num">— / Privacy Policy</span>
    <h2 class="section__title">Privacy Policy<span class="section__title-jp">プライバシーポリシー</span></h2>
  </div>

  <article class="legal-page__article" style="max-width:880px;margin:0 auto;padding:0 20px;">

    <?php
    // 固定ページ本文がある場合はそれを最優先表示
    if ( trim( strip_tags( get_post_field( 'post_content', get_the_ID() ) ) ) !== '' ) :
      while ( have_posts() ) : the_post(); ?>
        <div class="legal-page__content" style="font-size:15px;line-height:1.95;color:#444;">
          <?php the_content(); ?>
        </div>
      <?php endwhile;
    else : ?>

      <div class="legal-page__content privacy-content" style="font-size:15px;line-height:1.95;color:#444;">

        <p class="privacy-updated-top" style="text-align:right;color:#888;font-size:13px;margin-bottom:24px;">
          最終更新日：<?php echo esc_html( $updated_date ); ?>
        </p>

        <p style="margin-bottom:40px;">
          <strong><?php echo esc_html( $company_name ); ?></strong>（以下「当社」といいます）は、本サービス「<?php echo esc_html( $service_name ); ?>」において、<?php echo nl2br( esc_html( $intro_replaced ) ); ?>
        </p>

        <!-- 1. 個人情報の定義 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">1</span>個人情報の定義</h3>
          <p><?php echo nl2br( esc_html( $sec1 ) ); ?></p>
        </section>

        <!-- 2. 個人情報の収集方法 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">2</span>個人情報の収集方法</h3>
          <p><?php echo nl2br( esc_html( $sec2_intro ) ); ?></p>

          <?php $buyer_items = $lines_to_items( $sec2_buyer ); if ( $buyer_items ) : ?>
            <h4 class="privacy-h4">購入者から収集する情報</h4>
            <ul class="legal-list">
              <?php foreach ( $buyer_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php $creator_items = $lines_to_items( $sec2_creator ); if ( $creator_items ) : ?>
            <h4 class="privacy-h4">クリエイターから収集する情報</h4>
            <ul class="legal-list">
              <?php foreach ( $creator_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- 3. 個人情報の利用目的 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">3</span>個人情報の利用目的</h3>
          <p><?php echo nl2br( esc_html( $sec3_intro ) ); ?></p>
          <?php $sec3_items = $lines_to_items( $sec3_list ); if ( $sec3_items ) : ?>
            <ul class="legal-list">
              <?php foreach ( $sec3_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- 4. 利用目的の変更 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">4</span>利用目的の変更</h3>
          <p><?php echo nl2br( esc_html( $sec4 ) ); ?></p>
        </section>

        <!-- 5. 個人情報の第三者提供 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">5</span>個人情報の第三者提供</h3>
          <p><?php echo nl2br( esc_html( $sec5_intro ) ); ?></p>

          <?php $sec5_case_items = $lines_to_items( $sec5_cases ); if ( $sec5_case_items ) : ?>
            <h4 class="privacy-h4">第三者提供が必要な場合</h4>
            <ul class="legal-list">
              <?php foreach ( $sec5_case_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <h4 class="privacy-h4">業務委託先への提供</h4>
          <p><?php echo nl2br( esc_html( $sec5_outsource_lead ) ); ?></p>
          <?php $sec5_out_items = $lines_to_items( $sec5_outsource_list ); if ( $sec5_out_items ) : ?>
            <p style="margin:8px 0 8px;"><strong>主な委託先：</strong></p>
            <ul class="legal-list">
              <?php foreach ( $sec5_out_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- 6. 個人情報の開示 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">6</span>個人情報の開示</h3>
          <p><?php echo nl2br( esc_html( $sec6 ) ); ?></p>
        </section>

        <!-- 7. 個人情報の訂正および削除 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">7</span>個人情報の訂正および削除</h3>
          <p><?php echo nl2br( esc_html( $sec7 ) ); ?></p>
        </section>

        <!-- 8. 個人情報の利用停止等 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">8</span>個人情報の利用停止等</h3>
          <p><?php echo nl2br( esc_html( $sec8 ) ); ?></p>
        </section>

        <!-- 9. Cookie -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">9</span>Cookie（クッキー）その他の技術の利用</h3>
          <p><?php echo nl2br( esc_html( $sec9_intro ) ); ?></p>
          <?php $sec9_items = $lines_to_items( $sec9_purposes ); if ( $sec9_items ) : ?>
            <h4 class="privacy-h4">Cookieの利用目的</h4>
            <ul class="legal-list">
              <?php foreach ( $sec9_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <p><?php echo nl2br( esc_html( $sec9_outro ) ); ?></p>
        </section>

        <!-- 10. 個人情報の安全管理 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">10</span>個人情報の安全管理</h3>
          <p><?php echo nl2br( esc_html( $sec10_intro ) ); ?></p>
          <?php $sec10_items = $lines_to_items( $sec10_measures ); if ( $sec10_items ) : ?>
            <h4 class="privacy-h4">セキュリティ対策</h4>
            <ul class="legal-list">
              <?php foreach ( $sec10_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- 11. 個人情報の保存期間 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">11</span>個人情報の保存期間</h3>
          <p><?php echo nl2br( esc_html( $sec11_intro ) ); ?></p>
          <?php $sec11_items = $lines_to_items( $sec11_list ); if ( $sec11_items ) : ?>
            <ul class="legal-list">
              <?php foreach ( $sec11_items as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- 12. 未成年者の個人情報 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">12</span>未成年者の個人情報</h3>
          <p><?php echo nl2br( esc_html( $sec12 ) ); ?></p>
        </section>

        <!-- 13. プライバシーポリシーの変更 -->
        <section class="privacy-section">
          <h3 class="privacy-h3"><span class="privacy-num">13</span>プライバシーポリシーの変更</h3>
          <p><?php echo nl2br( esc_html( $sec13 ) ); ?></p>
        </section>

        <!-- お問い合わせ窓口 -->
        <section class="privacy-section privacy-contact">
          <h3 class="privacy-h3 privacy-h3--no-num">お問い合わせ窓口</h3>
          <p><?php echo nl2br( esc_html( $contact_intro ) ); ?></p>

          <div class="privacy-contact-card">
            <dl class="privacy-contact-dl">
              <dt>事業者名</dt>
              <dd><?php echo esc_html( $company_name ); ?></dd>

              <?php if ( $contact_manager ) : ?>
                <dt>個人情報保護管理者</dt>
                <dd><?php echo esc_html( $contact_manager ); ?></dd>
              <?php endif; ?>

              <?php if ( $display_email ) : ?>
                <dt>お問い合わせ先</dt>
                <dd><a href="mailto:<?php echo esc_attr( $display_email ); ?>" style="color:var(--pink-deep);"><?php echo esc_html( $display_email ); ?></a></dd>
              <?php endif; ?>

              <?php if ( $contact_hours ) : ?>
                <dt>受付時間</dt>
                <dd><?php echo nl2br( esc_html( $contact_hours ) ); ?></dd>
              <?php endif; ?>
            </dl>
          </div>

          <?php if ( $disclosure_note ) : ?>
            <h4 class="privacy-h4">個人情報の開示等の請求について</h4>
            <p><?php echo nl2br( esc_html( $disclosure_note ) ); ?></p>
          <?php endif; ?>
        </section>

        <p class="legal-updated privacy-footer-meta">
          制定日：<?php echo esc_html( $established ); ?><br>
          最終改訂日：<?php echo esc_html( $updated_date ); ?><br>
          事業者：<?php echo esc_html( $company_name ); ?>
        </p>

      </div>

    <?php endif; ?>

    <div style="text-align:center;margin-top:60px;">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="more-btn"><span>BACK TO TOP</span></a>
    </div>

  </article>
</main>

<?php get_footer(); ?>

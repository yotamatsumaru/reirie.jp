<?php
/**
 * REIRIE 管理ダッシュボードページ
 *
 * すべてのサイト設定をこの1画面で完結できるフォーム形式。
 * 横幅フルワイドレイアウト。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   管理メニュー登録
   ============================================================ */
function reirie_admin_menu() {
	add_menu_page(
		'REIRIE 設定',
		'REIRIE 設定',
		'manage_options',
		'reirie-dashboard',
		'reirie_dashboard_page',
		'dashicons-heart',
		2
	);
	add_submenu_page( 'reirie-dashboard', 'サイト設定', 'サイト設定', 'manage_options', 'reirie-dashboard', 'reirie_dashboard_page' );
	add_submenu_page( 'reirie-dashboard', 'クイックヘルプ', 'クイックヘルプ', 'manage_options', 'reirie-help', 'reirie_help_page' );
}
add_action( 'admin_menu', 'reirie_admin_menu' );

/* ============================================================
   設定項目定義（Customizerのreirie_*設定と同じキーを使用）
   ============================================================ */
function reirie_get_settings_schema() {
	return array(
		'hero' => array(
			'label' => 'ヒーロービジョン',
			'icon'  => 'format-video',
			'desc'  => 'トップ画面の背景・タイトル・キャッチコピー・CTAボタン・テロップを編集します。',
			'fields' => array(
				'reirie_hero_bg_type'    => array( 'type' => 'select', 'label' => '背景の種類', 'choices' => array( 'video' => '動画', 'image' => '画像' ), 'default' => 'video' ),
				'reirie_hero_video'      => array( 'type' => 'media', 'label' => 'ヒーロー背景動画（MP4）', 'mime' => 'video', 'desc' => '推奨：1920×1080 / 10〜15秒 / 5MB以下' ),
				'reirie_hero_image'      => array( 'type' => 'image', 'label' => 'ヒーロー背景画像', 'desc' => '推奨：1920×1080以上。背景タイプ「画像」時、または動画のポスター用' ),
				'reirie_hero_overlay'    => array( 'type' => 'number', 'label' => 'オーバーレイ濃度（%）', 'min' => 0, 'max' => 80, 'step' => 5, 'default' => 30 ),
				'reirie_hero_sub'        => array( 'type' => 'text', 'label' => 'サブタイトル（小さな英字）', 'default' => '2-girls IDOL UNIT' ),
				'reirie_hero_title'      => array( 'type' => 'text', 'label' => 'メインタイトル（英大文字）', 'default' => 'REIRIE' ),
				'reirie_hero_title_jp'   => array( 'type' => 'text', 'label' => 'タイトル（日本語フリガナ）', 'default' => 'レイリエ' ),
				'reirie_hero_catch'      => array( 'type' => 'text', 'label' => 'キャッチコピー', 'default' => 'ふたりで描く、きらめきの世界。' ),
				'reirie_hero_cta1_label' => array( 'type' => 'text', 'label' => 'メインボタン テキスト', 'desc' => '例：LATEST RELEASE / FANCLUB / TICKET。空欄で非表示' ),
				'reirie_hero_cta1_url'   => array( 'type' => 'url',  'label' => 'メインボタン URL' ),
				'reirie_hero_cta2_label' => array( 'type' => 'text', 'label' => 'サブボタン テキスト', 'desc' => '空欄で非表示' ),
				'reirie_hero_cta2_url'   => array( 'type' => 'url',  'label' => 'サブボタン URL' ),
				'reirie_marquee_text'    => array( 'type' => 'text', 'label' => 'スライドテロップ', 'default' => '★ REIRIE OFFICIAL SITE ★ NEW SINGLE OUT NOW ★' ),
				'reirie_marquee_show'    => array( 'type' => 'checkbox', 'label' => 'スライドテロップを表示する', 'default' => 1 ),
			),
		),
		'sns' => array(
			'label' => 'SNSリンク',
			'icon'  => 'share',
			'desc'  => 'ヘッダー・フッターのSNSアイコンのリンク先を設定します。',
			'fields' => array(
				'reirie_sns_twitter'   => array( 'type' => 'url', 'label' => 'X（Twitter）URL', 'default' => '#' ),
				'reirie_sns_instagram' => array( 'type' => 'url', 'label' => 'Instagram URL', 'default' => '#' ),
				'reirie_sns_tiktok'    => array( 'type' => 'url', 'label' => 'TikTok URL', 'default' => '#' ),
				'reirie_sns_youtube'   => array( 'type' => 'url', 'label' => 'YouTube URL', 'default' => '#' ),
			),
		),
		'site' => array(
			'label' => 'ロゴ・サイト基本情報',
			'icon'  => 'admin-customizer',
			'desc'  => 'サイトのタイトル・キャッチフレーズ・ロゴ・ファビコンを設定します。',
			'fields' => array(
				'blogname'         => array( 'type' => 'text', 'label' => 'サイトのタイトル', 'option' => true ),
				'blogdescription'  => array( 'type' => 'text', 'label' => 'キャッチフレーズ', 'option' => true ),
				'custom_logo'      => array( 'type' => 'image', 'label' => 'カスタムロゴ', 'theme_mod' => true, 'attachment_id' => true ),
				'site_icon'        => array( 'type' => 'image', 'label' => 'サイトアイコン（ファビコン）', 'option' => true, 'attachment_id' => true ),
			),
		),
		'contact' => array(
			'label' => 'お問い合わせカード',
			'icon'  => 'index-card',
			'desc'  => 'Contactセクションの3枚のカード（FANCLUB / PRESS / FAN MAIL）の文言とリンク先を編集。URL欄の使い方：①空欄 or 「#」→ 既定のお問い合わせフォームに自動遷移／②「https://」で始まる→ 外部サイトを別タブで開く／③「/page-slug/」→ サイト内の固定ページに遷移。',
			'fields' => array(
				'reirie_contact_fanclub_label' => array( 'type' => 'text', 'label' => '［FANCLUB］ラベル', 'default' => 'FANCLUB' ),
				'reirie_contact_fanclub_title' => array( 'type' => 'text', 'label' => '［FANCLUB］タイトル', 'default' => 'LIE LIE LAND' ),
				'reirie_contact_fanclub_desc'  => array( 'type' => 'text', 'label' => '［FANCLUB］説明', 'default' => 'オフィシャルファンクラブ会員募集中！' ),
				'reirie_contact_fanclub_url'   => array( 'type' => 'url',  'label' => '［FANCLUB］リンクURL', 'default' => '#', 'desc' => 'ファンクラブの外部サイトURL（例：https://lielie.land/）を入力すると別タブで開きます。空欄なら標準のお問い合わせフォームへ遷移。' ),
				'reirie_contact_press_label'   => array( 'type' => 'text', 'label' => '［PRESS］ラベル', 'default' => 'PRESS' ),
				'reirie_contact_press_title'   => array( 'type' => 'text', 'label' => '［PRESS］タイトル', 'default' => '取材・出演' ),
				'reirie_contact_press_desc'    => array( 'type' => 'text', 'label' => '［PRESS］説明', 'default' => 'メディア・取材のお問い合わせはこちら' ),
				'reirie_contact_press_url'     => array( 'type' => 'url',  'label' => '［PRESS］リンクURL', 'default' => '#', 'desc' => '空欄なら標準のお問い合わせフォーム（PRESSタイプ自動選択）へ遷移します。' ),
				'reirie_contact_fanmail_label' => array( 'type' => 'text', 'label' => '［FAN MAIL］ラベル', 'default' => 'FAN MAIL' ),
				'reirie_contact_fanmail_title' => array( 'type' => 'text', 'label' => '［FAN MAIL］タイトル', 'default' => 'ファンレター' ),
				'reirie_contact_fanmail_desc'  => array( 'type' => 'text', 'label' => '［FAN MAIL］説明', 'default' => '2人へのお手紙の送り先はこちら' ),
				'reirie_contact_fanmail_url'   => array( 'type' => 'url',  'label' => '［FAN MAIL］リンクURL', 'default' => '', 'desc' => '通常は空欄でOK（自動でファンレター送付先ページに遷移します）。外部の特設ページに飛ばしたい場合のみURLを入力してください。' ),
			),
		),
		'fanletter' => array(
			'label' => 'ファンレター送付先',
			'icon'  => 'email-alt',
			'desc'  => 'ファンレターの送り先住所と、送付時の案内文を編集します。FAN MAILカードをクリックすると、この情報を表示する専用ページ（/fanletter/）に遷移します。',
			'fields' => array(
				'reirie_fanletter_heading'   => array( 'type' => 'text', 'label' => 'ページ見出し', 'default' => 'FAN LETTER' ),
				'reirie_fanletter_subheading'=> array( 'type' => 'text', 'label' => 'サブ見出し（日本語）', 'default' => 'ファンレター送付先' ),
				'reirie_fanletter_lead'      => array( 'type' => 'textarea', 'label' => 'リード文', 'default' => '2人へのお手紙、贈り物は下記の宛先までお送りください。お送りいただいた品物は責任を持ってメンバーへお届けいたします。' ),
				'reirie_fanletter_postal'    => array( 'type' => 'text', 'label' => '郵便番号', 'default' => '〒000-0000', 'desc' => '例：〒150-0001' ),
				'reirie_fanletter_address'   => array( 'type' => 'textarea', 'label' => '住所', 'default' => "東京都〇〇区〇〇1-2-3\n△△ビル 4F\n株式会社〇〇〇〇 「REIRIE」宛", 'desc' => '改行で複数行入力できます（ビル名、宛名など）。' ),
				'reirie_fanletter_guide'     => array( 'type' => 'textarea', 'label' => 'ご案内・注意事項（本文）', 'default' => "♦会場でのお手紙・プレゼントについて♦\n\n※お手紙・プレゼントにつきまして、プレゼントボックスにお入れください。(設置スペース確保が難しい場合プレゼントボックスをご用意できない場合がございます。スタッフにお渡しください。)\n※宛名（メンバーの名前）、お客様のお名前のご記入をお願いいたします。\n※特典会中などにメンバーへ直接お渡しいただくことはできません。スタッフにお渡しいただくかプレゼントボックスをご利用ください。\n\n♦お手紙・プレゼント送り先♦\n\n〒150-0041\n東京都渋谷区神南1-10-8\nエフビル301\nUMG株式会社\nREIRIE(メンバー名)宛\n\n※お手紙やプレゼントの中身はスタッフが開封確認させて頂きます。スタッフ判断によりメンバーにお渡しできない場合がございますことをご了承くださいませ。\n※禁止な品物、スタッフがお預かりできないと判断したものはメンバーに届くことなく破棄となります。\n※手作りのアクセサリーや手芸品等は身につけた際に危険な箇所がないかのご確認をお願いいたします。\n※メンバーの手元に届くまで日数が掛かる場合がございます。\n\n※以下の品目は禁止とさせていただきます。\n・開封されたもの、使用済みと思われる物\n・高額な商品\n・生物、保冷剤を必要とする既製飲食物、手作りの飲食物\n・持ち帰る際や郵送する際に負担になる大きな物、重量物\n・現金、金券（itunesカードやギフト券等）、記念硬貨、割引券\n・危険物（火薬・花火・刃物 等)\n・生き物（動物・植物・虫 等）※生花のみ可\n・ぬいぐるみ、クッション類\n・お守り・お札等", 'desc' => '改行は自動で反映されます。「♦」「※」「・」などの記号を使った自由なフォーマットで入力できます。「お受け取りできる／できないもの」など見出しのテキストもすべてここで自由に編集できます。' ),
				'reirie_fanletter_footer'    => array( 'type' => 'textarea', 'label' => '末尾メッセージ', 'default' => "いつも応援してくださり、本当にありがとうございます。\nみなさまのお気持ち、しっかりとメンバーに届けます。" ),
			),
		),
		'contact_mail' => array(
			'label' => 'お問い合わせメール設定',
			'icon'  => 'email',
			'desc'  => 'お問い合わせフォームから届く通知メールの受信先・差出人を設定します。「受信先メールアドレス」には、info@reirie.jp のような共有アドレスも含め、複数指定できます。カンマまたは改行で区切ってください（例：info@reirie.jp, staff@reirie.jp）。',
			'fields' => array(
				'reirie_contact_recipients' => array(
					'type'    => 'textarea',
					'label'   => '受信先メールアドレス（複数可）',
					'default' => '',
					'desc'    => 'お問い合わせを受け取るアドレスをカンマか改行で区切って入力。例：info@reirie.jp, staff@reirie.jp',
				),
				'reirie_contact_cc' => array(
					'type'    => 'textarea',
					'label'   => 'CC（複数可）',
					'default' => '',
					'desc'    => '通知メールのCCに含めるアドレス（任意）。カンマか改行区切り。',
				),
				'reirie_contact_bcc' => array(
					'type'    => 'textarea',
					'label'   => 'BCC（複数可）',
					'default' => '',
					'desc'    => '通知メールのBCCに含めるアドレス（任意）。受信者には表示されません。',
				),
				'reirie_contact_from_name' => array(
					'type'    => 'text',
					'label'   => '差出人名（From）',
					'default' => 'REIRIE OFFICIAL',
					'desc'    => '通知メールに表示される差出人の名前。',
				),
				'reirie_contact_from_email' => array(
					'type'    => 'text',
					'label'   => '差出人メールアドレス（From）',
					'default' => '',
					'desc'    => 'サイトと同じドメインの実在アドレス推奨（迷惑メール判定を防ぐため）。例：no-reply@reirie.jp（空欄なら wordpress@reirie.jp を自動使用）',
				),
				'reirie_contact_admin_email' => array(
					'type'    => 'text',
					'label'   => '【旧】受信先（単一）',
					'default' => '',
					'desc'    => '上の「受信先メールアドレス（複数可）」を空にしたときのフォールバック。通常は使いません。',
				),
			),
		),
		'sections' => array(
			'label' => 'セクションの表示／非表示',
			'icon'  => 'visibility',
			'desc'  => 'トップページの各セクション（NEWS / SCHEDULE / DISCOGRAPHY / MOVIE / MEMBER / GOODS / CONTACT）を表示するかどうかを切り替えます。チェックを外すとそのセクションは非表示になります。',
			'fields' => array(
				'reirie_show_news'        => array( 'type' => 'checkbox', 'label' => 'NEWS（ニュース）を表示する',           'default' => 1 ),
				'reirie_show_schedule'    => array( 'type' => 'checkbox', 'label' => 'SCHEDULE（スケジュール）を表示する',  'default' => 1 ),
				'reirie_show_discography' => array( 'type' => 'checkbox', 'label' => 'DISCOGRAPHY（ディスコグラフィー）を表示する', 'default' => 1 ),
				'reirie_show_movie'       => array( 'type' => 'checkbox', 'label' => 'MOVIE（ムービー）を表示する',          'default' => 1 ),
				'reirie_show_profile'     => array( 'type' => 'checkbox', 'label' => 'MEMBER（メンバー紹介）を表示する',     'default' => 1 ),
				'reirie_show_goods'       => array( 'type' => 'checkbox', 'label' => 'GOODS（グッズ）を表示する',            'default' => 1 ),
				'reirie_show_contact'     => array( 'type' => 'checkbox', 'label' => 'CONTACT（お問い合わせ）を表示する',   'default' => 1 ),
			),
		),
		'goods' => array(
			'label' => 'グッズ表示モード',
			'icon'  => 'cart',
			'desc'  => 'グッズセクションの表示方式を切り替えます。「外部ストアへのリンク（1ボタン）」を選ぶと、UMG STOREなど外部サイトへのボタンを1つだけ表示します。「個別商品リスト」を選ぶと、グッズ投稿で登録した商品をグリッド表示します。商品が1件も登録されていない場合は何も表示されません（デモ商品は表示されません）。',
			'fields' => array(
				'reirie_goods_mode'        => array(
					'type'    => 'select',
					'label'   => '表示モード',
					'choices' => array(
						'link' => '外部ストアへのリンク（1ボタン）',
						'list' => '個別商品リスト（投稿で登録した商品を表示）',
					),
					'default' => 'link',
				),
				'reirie_goods_link_url'    => array( 'type' => 'url',  'label' => '外部ストアURL',       'default' => '', 'desc' => '例：https://store.umusic.co.jp/ などストアTOPのURL。「外部ストアへのリンク」モードで使用します。' ),
				'reirie_goods_link_label'  => array( 'type' => 'text', 'label' => 'ボタンのテキスト',     'default' => 'VISIT OFFICIAL STORE', 'desc' => '大きなボタンに表示する文言（例：VISIT OFFICIAL STORE / GO TO UMG STORE）' ),
				'reirie_goods_link_sub'    => array( 'type' => 'text', 'label' => 'ボタン下の補足テキスト', 'default' => 'グッズはオフィシャルストアにてお取り扱いしております。', 'desc' => '空欄なら非表示' ),
			),
		),
		'company' => array(
			'label' => '運営会社情報',
			'icon'  => 'building',
			'desc'  => '「運営会社」「特定商取引法に基づく表示」「プライバシーポリシー」のすべてのページで共通利用される、会社・連絡先の基本情報です。固定ページのテンプレートで「REIRIE 運営会社／特定商取引法／プライバシーポリシー」を選択すると、ここの値が自動で表示されます。',
			'fields' => array(
				'reirie_company_name'     => array( 'type' => 'text',     'label' => '会社名',                   'default' => 'REIRIE OFFICIAL', 'desc' => '例：株式会社レイリエ／REIRIE OFFICIAL' ),
				'reirie_company_name_en'  => array( 'type' => 'text',     'label' => '会社名（英語表記）',       'default' => '', 'desc' => '例：REIRIE Inc.（空欄可）' ),
				'reirie_company_ceo'      => array( 'type' => 'text',     'label' => '代表者名',                 'default' => '', 'desc' => '例：山田 太郎' ),
				'reirie_company_founded'  => array( 'type' => 'text',     'label' => '設立年月日',               'default' => '', 'desc' => '例：2024年4月1日' ),
				'reirie_company_zipcode'  => array( 'type' => 'text',     'label' => '郵便番号',                 'default' => '', 'desc' => '例：150-0001（〒は不要）' ),
				'reirie_company_address'  => array( 'type' => 'textarea', 'label' => '所在地（住所）',           'default' => '', 'desc' => '改行でビル名・階数などを追加できます。例：東京都渋谷区神宮前1-2-3\nREIRIEビル 4F' ),
				'reirie_company_tel'      => array( 'type' => 'text',     'label' => '電話番号',                 'default' => '', 'desc' => '例：03-0000-0000（空欄可）' ),
				'reirie_company_email'    => array( 'type' => 'text',     'label' => 'メールアドレス',           'default' => '', 'desc' => '例：info@reirie.jp（空欄可）' ),
				'reirie_company_business' => array( 'type' => 'textarea', 'label' => '事業内容',                 'default' => '', 'desc' => '改行区切りで複数項目入力可。例：アーティストマネジメント\n音楽コンテンツの企画・制作' ),
				'reirie_company_website'  => array( 'type' => 'url',      'label' => '公式サイトURL',           'default' => '', 'desc' => '空欄ならサイトTOPのURLを自動使用' ),
			),
		),
		'tokushoho' => array(
			'label' => '特定商取引法に基づく表示',
			'icon'  => 'shield',
			'desc'  => '「特定商取引法に基づく表示」ページ（/tokushoho/）に表示される販売情報です。会社名・住所・電話・メールは上の「運営会社情報」から自動的に引用されます。ここでは販売関連の項目のみ入力してください。',
			'fields' => array(
				'reirie_tokushoho_intro_note'    => array( 'type' => 'textarea', 'label' => '冒頭の説明文（住所明示の注記）', 'default' => "特定商取引法に基づき、事業所の所在地を明示しています。\nご不明な点がございましたら、下記のお問い合わせ先までご連絡ください。", 'desc' => 'ページ冒頭に表示される案内文。空欄で非表示' ),
				'reirie_tokushoho_seller'        => array( 'type' => 'text',     'label' => '販売事業者名',                 'default' => '', 'desc' => '空欄なら「運営会社情報」の会社名を自動使用。グッズ販売主体が別の場合のみ入力。' ),
				'reirie_tokushoho_manager'       => array( 'type' => 'text',     'label' => '運営統括責任者',               'default' => '', 'desc' => '空欄なら「運営会社情報」の代表者名を自動使用。販売責任者が別の場合のみ入力。' ),
				'reirie_tokushoho_price'         => array( 'type' => 'textarea', 'label' => '販売価格',                     'default' => '各商品ページに記載の販売価格（消費税込）に準じます。', 'desc' => '改行可' ),
				'reirie_tokushoho_extra_fee'     => array( 'type' => 'textarea', 'label' => '商品代金以外の必要料金',       'default' => "送料：全国一律 ◯◯◯円（税込）\n※◯◯◯円以上のお買い上げで送料無料\n代引手数料：◯◯◯円（税込）", 'desc' => '改行で複数行入力可' ),
				'reirie_tokushoho_payment'       => array( 'type' => 'textarea', 'label' => 'お支払い方法',                 'default' => "クレジットカード決済\n銀行振込\n代金引換", 'desc' => '改行区切り' ),
				'reirie_tokushoho_delivery'      => array( 'type' => 'textarea', 'label' => '商品の引渡し時期',             'default' => 'ご注文確定後、通常3〜7営業日以内に発送いたします。\n受注生産品など、お時間をいただく商品は商品ページに記載しております。', 'desc' => '改行可' ),
				'reirie_tokushoho_return'        => array( 'type' => 'textarea', 'label' => '返品・交換について',           'default' => "商品の特性上、お客様都合による返品・交換はお受けしておりません。\n万一、不良品・配送中の破損・誤配送等がございましたら、商品到着後7日以内に下記までご連絡ください。\n送料当社負担にて、良品とお取り替えいたします。", 'desc' => '改行で複数行入力可' ),
				'reirie_tokushoho_contact_email' => array( 'type' => 'text',     'label' => 'お問い合わせ用メールアドレス', 'default' => 'support-dreamvision@umg-jp.com', 'desc' => '販売・特商法に関する専用メール。空欄なら「運営会社情報」のメールを自動使用。' ),
				'reirie_tokushoho_contact_hours' => array( 'type' => 'textarea', 'label' => '受付時間',                     'default' => "10:00〜18:00（土日祝日も対応）\n※営業時間外のお問い合わせは翌営業日以降の対応となります", 'desc' => '改行可' ),
				'reirie_tokushoho_governing_law' => array( 'type' => 'textarea', 'label' => '準拠法',                       'default' => '本取引は日本法に準拠し、日本法に従って解釈されます。', 'desc' => '改行可' ),
				'reirie_tokushoho_jurisdiction'  => array( 'type' => 'textarea', 'label' => '紛争解決（管轄裁判所）',       'default' => '本サービスに関して紛争が生じた場合、東京地方裁判所を第一審の専属的合意管轄裁判所とします。', 'desc' => '改行可' ),
			),
		),
		'privacy' => array(
			'label' => 'プライバシーポリシー',
			'icon'  => 'privacy',
			'desc'  => '「プライバシーポリシー」ページ（/privacy/）に表示される情報を全章編集できます。事業者名・メールアドレスは「運営会社情報」から自動引用されます。各章の本文は改行で複数段落・複数項目に分けられます。固定ページ本文に何か入力した場合は、そちらが優先表示されます（独自にHTMLを書きたい場合）。',
			'fields' => array(
				// ===== 基本情報 =====
				'reirie_privacy_updated'        => array( 'type' => 'text',     'label' => '最終更新日',                   'default' => '', 'desc' => '例：2026年3月18日（空欄なら本日の日付を自動表示）' ),
				'reirie_privacy_established'    => array( 'type' => 'text',     'label' => '制定日',                       'default' => '2025年1月1日', 'desc' => '例：2025年1月1日' ),
				'reirie_privacy_service_name'   => array( 'type' => 'text',     'label' => 'サービス名',                   'default' => 'REIRIE', 'desc' => '本文中に「本サービス『◯◯』」として表示されます' ),
				'reirie_privacy_intro'          => array( 'type' => 'textarea', 'label' => '冒頭の導入文',                 'default' => '本サービスにおいて取得した個人情報を以下のとおり取り扱います。', 'desc' => '{company} で会社名、{service} でサービス名を埋め込めます' ),

				// ===== 各章本文 =====
				'reirie_privacy_sec1'           => array( 'type' => 'textarea', 'label' => '【1】個人情報の定義',           'default' => '本プライバシーポリシーにおいて「個人情報」とは、個人情報保護法に定める「個人情報」を指し、生存する個人に関する情報であって、当該情報に含まれる氏名、メールアドレス、その他の記述等により特定の個人を識別できる情報、および個人識別符号が含まれる情報を指します。' ),

				'reirie_privacy_sec2_intro'     => array( 'type' => 'textarea', 'label' => '【2】個人情報の収集方法（導入文）', 'default' => '当社は、ユーザーが利用登録をする際、および本サービスを利用する際に、以下の個人情報を収集します。' ),
				'reirie_privacy_sec2_buyer'     => array( 'type' => 'textarea', 'label' => '【2】購入者から収集する情報（箇条書き）', 'default' => "氏名（ニックネームまたは本名）\nメールアドレス\nパスワード（暗号化して保存）\n決済情報（クレジットカード情報等、Stripe経由で取得・処理）\n購入履歴・視聴履歴\nIPアドレス、ブラウザ情報、デバイス情報\nCookie情報", 'desc' => '改行区切りで複数項目入力。空欄なら非表示' ),
				'reirie_privacy_sec2_creator'   => array( 'type' => 'textarea', 'label' => '【2】クリエイターから収集する情報（箇条書き）', 'default' => "氏名または法人名\nメールアドレス\nパスワード（暗号化して保存）\n銀行口座情報（売上金精算用）\n事業者情報（法人の場合）\n本人確認書類（必要に応じて）\nコンテンツ情報・販売履歴", 'desc' => '改行区切り。空欄なら非表示' ),

				'reirie_privacy_sec3_intro'     => array( 'type' => 'textarea', 'label' => '【3】個人情報の利用目的（導入文）', 'default' => '当社は、収集した個人情報を以下の目的で利用します。' ),
				'reirie_privacy_sec3_list'      => array( 'type' => 'textarea', 'label' => '【3】利用目的（箇条書き）', 'default' => "本サービスの提供、運営、改善\nユーザー登録、認証、本人確認\nチケット購入、決済処理\nクリエイターへの売上金精算\n購入履歴、視聴履歴の管理\nカスタマーサポート、お問い合わせ対応\n本サービスに関する情報のご案内（メールマガジン等）\n利用状況の分析、統計データの作成（個人を特定できない形式）\n不正行為の防止、規約違反への対応\n利用規約違反またはその疑いのある行為への対応\nその他、上記利用目的に付随する目的", 'desc' => '改行区切り' ),

				'reirie_privacy_sec4'           => array( 'type' => 'textarea', 'label' => '【4】利用目的の変更', 'default' => "1. 当社は、利用目的が変更前と関連性を有すると合理的に認められる場合に限り、個人情報の利用目的を変更するものとします。\n\n2. 利用目的の変更を行った場合には、変更後の目的について、当社所定の方法により、ユーザーに通知し、または本ウェブサイト上に公表するものとします。" ),

				'reirie_privacy_sec5_intro'     => array( 'type' => 'textarea', 'label' => '【5】個人情報の第三者提供（導入文）', 'default' => '当社は、次に掲げる場合を除いて、あらかじめユーザーの同意を得ることなく、第三者に個人情報を提供することはありません。' ),
				'reirie_privacy_sec5_cases'     => array( 'type' => 'textarea', 'label' => '【5】第三者提供が必要な場合（箇条書き）', 'default' => "法令に基づく場合\n人の生命、身体または財産の保護のために必要がある場合であって、本人の同意を得ることが困難であるとき\n公衆衛生の向上または児童の健全な育成の推進のために特に必要がある場合であって、本人の同意を得ることが困難であるとき\n国の機関もしくは地方公共団体またはその委託を受けた者が法令の定める事務を遂行することに対して協力する必要がある場合であって、本人の同意を得ることにより当該事務の遂行に支障を及ぼすおそれがあるとき", 'desc' => '改行区切り' ),
				'reirie_privacy_sec5_outsource_lead' => array( 'type' => 'textarea', 'label' => '【5】業務委託先への提供（説明文）', 'default' => '当社は、利用目的の達成に必要な範囲内において、個人情報の取扱いの全部または一部を委託する場合があります。この場合、委託先との間で適切な個人情報保護に関する契約を締結し、委託先に対する必要かつ適切な監督を行います。' ),
				'reirie_privacy_sec5_outsource_list' => array( 'type' => 'textarea', 'label' => '【5】主な委託先（箇条書き）', 'default' => "Stripe, Inc. - 決済処理（クレジットカード情報の処理）\nクラウドサーバー事業者 - データ保管\nメール配信サービス事業者 - メール配信", 'desc' => '改行区切り' ),

				'reirie_privacy_sec6'           => array( 'type' => 'textarea', 'label' => '【6】個人情報の開示', 'default' => "1. 当社は、本人から個人情報の開示を求められたときは、本人に対し、遅滞なくこれを開示します。ただし、開示することにより次のいずれかに該当する場合は、その全部または一部を開示しないこともあり、開示しない決定をした場合には、その旨を遅滞なく通知します。\n\n本人または第三者の生命、身体、財産その他の権利利益を害するおそれがある場合\n当社の業務の適正な実施に著しい支障を及ぼすおそれがある場合\nその他法令に違反することとなる場合\n\n2. 前項の定めにかかわらず、履歴情報および特性情報などの個人情報以外の情報については、原則として開示いたしません。" ),
				'reirie_privacy_sec7'           => array( 'type' => 'textarea', 'label' => '【7】個人情報の訂正および削除', 'default' => "1. ユーザーは、当社の保有する自己の個人情報が誤った情報である場合には、当社が定める手続きにより、当社に対して個人情報の訂正、追加または削除（以下「訂正等」といいます）を請求することができます。\n\n2. 当社は、ユーザーから前項の請求を受けてその請求に応じる必要があると判断した場合には、遅滞なく、当該個人情報の訂正等を行うものとします。\n\n3. 当社は、前項の規定に基づき訂正等を行った場合、または訂正等を行わない旨の決定をしたときは遅滞なく、これをユーザーに通知します。" ),
				'reirie_privacy_sec8'           => array( 'type' => 'textarea', 'label' => '【8】個人情報の利用停止等', 'default' => "1. 当社は、本人から、個人情報が、利用目的の範囲を超えて取り扱われているという理由、または不正の手段により取得されたものであるという理由により、その利用の停止または消去（以下「利用停止等」といいます）を求められた場合には、遅滞なく必要な調査を行います。\n\n2. 前項の調査結果に基づき、その請求に応じる必要があると判断した場合には、遅滞なく、当該個人情報の利用停止等を行います。\n\n3. 当社は、前項の規定に基づき利用停止等を行った場合、または利用停止等を行わない旨の決定をしたときは、遅滞なく、これをユーザーに通知します。\n\n4. 前二項にかかわらず、利用停止等に多額の費用を有する場合その他利用停止等を行うことが困難な場合であって、ユーザーの権利利益を保護するために必要なこれに代わるべき措置をとれる場合は、この代替策を講じるものとします。" ),

				'reirie_privacy_sec9_intro'     => array( 'type' => 'textarea', 'label' => '【9】Cookie（導入文）', 'default' => '1. 本サービスでは、ユーザーの利便性向上およびサービス改善のため、Cookie（クッキー）を使用します。' ),
				'reirie_privacy_sec9_purposes'  => array( 'type' => 'textarea', 'label' => '【9】Cookieの利用目的（箇条書き）', 'default' => "ログイン状態の維持\nユーザー設定の保存\nアクセス解析（Google Analytics等）\n広告配信の最適化\nサービス利用状況の把握", 'desc' => '改行区切り' ),
				'reirie_privacy_sec9_outro'     => array( 'type' => 'textarea', 'label' => '【9】Cookie（締めの文）', 'default' => "2. ユーザーは、ブラウザの設定によりCookieの受け取りを拒否することができます。ただし、Cookieを無効にした場合、本サービスの一部機能が利用できなくなる場合があります。\n\nCookieの無効化方法は、各ブラウザの設定画面からご確認ください。" ),

				'reirie_privacy_sec10_intro'    => array( 'type' => 'textarea', 'label' => '【10】安全管理（導入文）', 'default' => '当社は、個人情報の紛失、破壊、改ざんおよび漏洩などのリスクに対して、個人情報の安全管理が図られるよう、当社の従業員に対し、必要かつ適切な監督を行います。' ),
				'reirie_privacy_sec10_measures' => array( 'type' => 'textarea', 'label' => '【10】セキュリティ対策（箇条書き）', 'default' => "SSL/TLS暗号化通信の採用\nパスワードの暗号化保存（ハッシュ化）\n決済情報のStripe経由での安全な処理（当社はカード情報を保持しません）\nアクセス制限およびアクセスログの管理\nファイアウォールによる不正アクセス防止\n定期的なセキュリティ診断の実施\n従業員への個人情報保護教育の実施", 'desc' => '改行区切り' ),

				'reirie_privacy_sec11_intro'    => array( 'type' => 'textarea', 'label' => '【11】保存期間（導入文）', 'default' => '当社は、個人情報を、利用目的の達成に必要な期間に限り保存します。ただし、法令により保存期間が定められている場合は、当該期間保存します。' ),
				'reirie_privacy_sec11_list'     => array( 'type' => 'textarea', 'label' => '【11】保存期間の一覧（箇条書き）', 'default' => "アカウント情報：アカウント削除後、1年間\n購入履歴：会計法令に基づき、最低7年間\nお問い合わせ履歴：対応完了後、3年間\nアクセスログ：6ヶ月間", 'desc' => '改行区切り' ),

				'reirie_privacy_sec12'          => array( 'type' => 'textarea', 'label' => '【12】未成年者の個人情報', 'default' => '未成年者が本サービスを利用する場合は、親権者その他の法定代理人の同意を得た上で利用するものとします。未成年者が本サービスを利用した場合、当社は、親権者その他の法定代理人の同意があったものとみなします。' ),
				'reirie_privacy_sec13'          => array( 'type' => 'textarea', 'label' => '【13】プライバシーポリシーの変更', 'default' => "1. 本ポリシーの内容は、法令その他本ポリシーに別段の定めのある事項を除いて、ユーザーに通知することなく、変更することができるものとします。\n\n2. 当社が別途定める場合を除いて、変更後のプライバシーポリシーは、本ウェブサイトに掲載したときから効力を生じるものとします。\n\n3. 本ポリシーの変更後、ユーザーが本サービスを利用した場合、変更後のプライバシーポリシーに同意したものとみなします。" ),

				// ===== お問い合わせ窓口 =====
				'reirie_privacy_contact_intro'  => array( 'type' => 'textarea', 'label' => '【お問い合わせ窓口】導入文', 'default' => '本ポリシーに関するお問い合わせ、開示等の請求、その他個人情報の取扱いに関するご質問・ご相談は、以下の窓口までお願いいたします。' ),
				'reirie_privacy_contact_manager'=> array( 'type' => 'text',     'label' => '個人情報保護管理者',         'default' => '個人情報保護責任者', 'desc' => '例：個人情報保護責任者 / 山田 太郎' ),
				'reirie_privacy_contact_email'  => array( 'type' => 'text',     'label' => 'お問い合わせ用メールアドレス', 'default' => '', 'desc' => '空欄なら「運営会社情報」のメールを自動使用' ),
				'reirie_privacy_contact_hours'  => array( 'type' => 'textarea', 'label' => '受付時間',                     'default' => "10:00〜18:00（土日祝日も対応）\n※営業時間外のお問い合わせは翌営業日以降の対応となります" ),
				'reirie_privacy_disclosure_note'=> array( 'type' => 'textarea', 'label' => '個人情報の開示等の請求について（補足）', 'default' => '個人情報の開示、訂正、利用停止等をご希望される場合は、本人確認を行った上で対応させていただきます。請求方法の詳細については、上記お問い合わせ先までご連絡ください。' ),
			),
		),
		'footer' => array(
			'label' => 'フッター',
			'icon'  => 'editor-textcolor',
			'desc'  => 'フッターのコピーライト表記・サブタイトルを編集します。',
			'fields' => array(
				'reirie_footer_copy'     => array( 'type' => 'text', 'label' => 'コピーライト表記', 'default' => '© 2026 REIRIE OFFICIAL. All Rights Reserved.' ),
				'reirie_footer_subtitle' => array( 'type' => 'text', 'label' => 'フッターサブタイトル', 'default' => '2-girls IDOL UNIT' ),
			),
		),
	);
}

/* ============================================================
   ヘルパー：セクション表示判定
   ============================================================ */
function reirie_section_is_visible( $key ) {
	// $key: 'news', 'schedule', 'discography', 'movie', 'profile', 'goods', 'contact'
	$mod = get_theme_mod( 'reirie_show_' . $key, 1 );
	return ! empty( $mod );
}

/* ============================================================
   設定値の取得（option / theme_mod を吸収）
   ============================================================ */
function reirie_get_setting_value( $key, $field ) {
	if ( ! empty( $field['option'] ) ) {
		return get_option( $key, isset( $field['default'] ) ? $field['default'] : '' );
	}
	if ( ! empty( $field['theme_mod'] ) ) {
		return get_theme_mod( $key, isset( $field['default'] ) ? $field['default'] : '' );
	}
	return get_theme_mod( $key, isset( $field['default'] ) ? $field['default'] : '' );
}

/* ============================================================
   保存処理
   ============================================================ */
function reirie_handle_settings_save() {
	if ( empty( $_POST['reirie_settings_submit'] ) ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_POST['reirie_settings_nonce'] ) || ! wp_verify_nonce( $_POST['reirie_settings_nonce'], 'reirie_save_settings' ) ) {
		wp_die( 'Security check failed' );
	}

	$schema = reirie_get_settings_schema();
	$debug_log = array(); // 診断用：保存した値の一覧
	$debug_post = array(); // 診断用：受信した $_POST の一覧

	foreach ( $schema as $section_key => $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			$debug_post[ $key ] = is_string( $raw ) ? ( strlen( $raw ) > 200 ? substr( $raw, 0, 200 ) . '...' : $raw ) : $raw;

			switch ( $field['type'] ) {
				case 'url':
				case 'media':
				case 'image':
					$val = esc_url_raw( $raw );
					break;
				case 'number':
					$val = absint( $raw );
					break;
				case 'checkbox':
					$val = ! empty( $raw ) ? 1 : 0;
					break;
				case 'select':
					$choices = isset( $field['choices'] ) ? $field['choices'] : array();
					$val = array_key_exists( $raw, $choices ) ? $raw : ( isset( $field['default'] ) ? $field['default'] : '' );
					break;
				case 'textarea':
					$val = sanitize_textarea_field( $raw );
					break;
				default:
					$val = sanitize_text_field( $raw );
			}

			// 特殊：custom_logo / site_icon は attachment ID を保存
			if ( ! empty( $field['attachment_id'] ) ) {
				$val = absint( $raw );
			}

			if ( ! empty( $field['option'] ) ) {
				update_option( $key, $val );
				$debug_log[ $key ] = 'option=' . ( is_string( $val ) && strlen( $val ) > 200 ? substr( $val, 0, 200 ) . '...' : $val );
			} else {
				set_theme_mod( $key, $val );
				$debug_log[ $key ] = 'theme_mod=' . ( is_string( $val ) && strlen( $val ) > 200 ? substr( $val, 0, 200 ) . '...' : $val );
			}
		}
	}

	// 診断ログを transient に保存（次のページロードで表示）
	set_transient( 'reirie_save_debug', array(
		'saved' => $debug_log,
		'post'  => $debug_post,
		'time'  => current_time( 'mysql' ),
	), 60 );

	add_settings_error( 'reirie_settings', 'reirie_saved', '設定を保存しました。', 'success' );
	set_transient( 'reirie_settings_saved', 1, 30 );
	wp_safe_redirect( add_query_arg( array( 'page' => 'reirie-dashboard', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_init', 'reirie_handle_settings_save' );

/**
 * 隠しの wp_editor を一度出力しておく（モーダル内 TinyMCE 用の初期化スクリプトを揃える）
 * これがないと、後から JS で tinymce.init() してもツールバーが組み立たない場合がある。
 */
function reirie_print_hidden_editor() {
	?>
	<div style="display:none;">
		<?php
		wp_editor( '', 'reirie_hidden_editor', array(
			'media_buttons' => false,
			'tinymce'       => true,
			'quicktags'     => false,
			'teeny'         => true,
		) );
		?>
	</div>
	<?php
}

/* ============================================================
   ダッシュボード本体（フルワイド・タブ式・フォーム埋め込み）
   ============================================================ */
function reirie_dashboard_page() {
	// メディアアップローダー
	wp_enqueue_media();
	wp_enqueue_style( 'dashicons' );

	// TinyMCE エディタ（モーダル内のリッチ本文編集用）
	// wp_enqueue_editor() は TinyMCE / Quicktags / wp_link 関連スクリプトを正しく読み込む
	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	// リンク挿入ダイアログのために wp_editor を一度ダミー出力（必要なJSが揃う）
	add_action( 'admin_print_footer_scripts', 'reirie_print_hidden_editor', 5 );

	$schema = reirie_get_settings_schema();
	$content_schema = function_exists( 'reirie_content_schema' ) ? reirie_content_schema() : array();

	// セットアップ進捗
	$has_home    = (bool) get_option( 'page_on_front' );
	$has_member  = (int) wp_count_posts( 'member' )->publish;
	$has_news    = (int) wp_count_posts( 'news' )->publish;
	$has_disco   = (int) wp_count_posts( 'discography' )->publish;
	$has_contact = get_posts( array(
		'post_type' => 'page', 'meta_key' => '_wp_page_template',
		'meta_value' => 'page-templates/template-contact.php',
		'posts_per_page' => 1, 'fields' => 'ids',
	) );
	$status_items = array(
		array( 'ok' => $has_home, 'label' => 'トップページ設定', 'help' => admin_url( 'options-reading.php' ) ),
		array( 'ok' => $has_member > 0, 'label' => 'メンバー登録 ' . $has_member . '件' ),
		array( 'ok' => $has_news > 0, 'label' => 'News ' . $has_news . '件' ),
		array( 'ok' => $has_disco > 0, 'label' => '作品 ' . $has_disco . '件' ),
		array( 'ok' => ! empty( $has_contact ), 'label' => 'お問い合わせページ' ),
	);

	// サイトのタイムゾーン設定チェック
	// （予約投稿の公開日時ズレの多くは、ここが「東京」以外や未設定のまま
	//   になっていることが原因のため、管理者が気づけるように警告表示する）
	$reirie_tz_string = get_option( 'timezone_string' );
	$reirie_gmt_offset = get_option( 'gmt_offset' );
	$reirie_tz_ok = ( $reirie_tz_string === 'Asia/Tokyo' ) || ( $reirie_tz_string === '' && (float) $reirie_gmt_offset === 9.0 );
	$reirie_tz_label = $reirie_tz_ok
		? 'サイトのタイムゾーン設定（東京）'
		: 'サイトのタイムゾーンが「東京」以外になっています（現在: ' . ( $reirie_tz_string !== '' ? $reirie_tz_string : 'UTC' . ( $reirie_gmt_offset >= 0 ? '+' : '' ) . $reirie_gmt_offset ) . '）。予約投稿の公開時刻がズレる原因になります';
	$status_items[] = array(
		'ok'   => $reirie_tz_ok,
		'label'=> $reirie_tz_label,
		'help' => admin_url( 'options-general.php' ),
	);

	// Missed schedule（公開予定だが時刻を過ぎている投稿）の検出
	$reirie_cron_health = function_exists( 'reirie_cron_health_check' ) ? reirie_cron_health_check() : array( 'missed' => 0, 'missed_list' => array(), 'disabled' => false );
	?>
	<div class="reirie-fw-wrap">

		<style>
			/* ===== Full width override ===== */
			#wpcontent { padding-left: 0 !important; }
			#wpbody-content { padding-bottom: 60px; }
			.reirie-fw-wrap { margin: 0; padding: 0; background: #fafafa; min-height: 100vh; }
			.reirie-fw-wrap * { box-sizing: border-box; }
			.reirie-fw-inner { padding: 24px 32px 60px; max-width: none; }
			@media (max-width: 782px) {
				.reirie-fw-inner { padding: 16px; }
			}

			/* ===== Header ===== */
			.reirie-fw-header {
				display: flex;
				align-items: center;
				gap: 14px;
				margin: 0 0 6px;
			}
			.reirie-fw-header .brand-mark {
				width: 40px; height: 40px;
				border-radius: 12px;
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				display: inline-flex; align-items: center; justify-content: center;
				box-shadow: 0 6px 16px rgba(255,126,182,0.35);
			}
			.reirie-fw-header .brand-mark .dashicons { color: #fff; font-size: 22px; width: 22px; height: 22px; }
			.reirie-fw-header h1 {
				font-size: 22px; font-weight: 600; color: #1d1d1f;
				margin: 0; padding: 0; letter-spacing: 0.02em;
			}
			.reirie-fw-lead {
				font-size: 13px; color: #6b6b6b;
				margin: 0 0 24px 54px; line-height: 1.6;
			}

			/* ===== Saved notice ===== */
			.reirie-saved-notice {
				background: linear-gradient(135deg, #e8f7ee 0%, #ddf3e3 100%);
				border: 1px solid #c0e4cc;
				border-left: 3px solid #1f7a3f;
				border-radius: 8px;
				padding: 12px 16px;
				margin: 0 0 18px;
				font-size: 13px; color: #1f7a3f;
				display: flex; align-items: center; gap: 8px;
			}
			.reirie-saved-notice .dashicons { font-size: 18px; width: 18px; height: 18px; }

			/* ===== Setup status ===== */
			.reirie-fw-status {
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 12px;
				padding: 16px 20px;
				margin: 0 0 24px;
				box-shadow: 0 1px 2px rgba(0,0,0,0.03);
				display: flex; flex-wrap: wrap; align-items: center; gap: 14px;
			}
			.reirie-fw-status-title {
				font-size: 11px; font-weight: 700; color: #888;
				letter-spacing: 0.18em; text-transform: uppercase;
				display: inline-flex; align-items: center; gap: 6px; margin: 0;
			}
			.reirie-fw-status-title .dashicons { font-size: 14px; width: 14px; height: 14px; color: #b07aff; }
			.reirie-fw-status-list { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
			.reirie-fw-status-list li {
				display: inline-flex; align-items: center; gap: 6px;
				font-size: 12px; padding: 4px 12px; border-radius: 999px; background: #f7f7f9; color: #444;
			}
			.reirie-fw-status-list li.is-ok { background: #e8f7ee; color: #1f7a3f; }
			.reirie-fw-status-list li.is-warn { background: #fff6e6; color: #a06800; }
			.reirie-fw-status-list li .dashicons { font-size: 13px; width: 13px; height: 13px; }
			.reirie-fw-status-list a { color: inherit; text-decoration: underline; }

			/* ===== Layout: side tabs + content ===== */
			.reirie-fw-layout {
				display: grid;
				grid-template-columns: 260px 1fr;
				gap: 20px;
				align-items: start;
			}
			@media (max-width: 960px) {
				.reirie-fw-layout { grid-template-columns: 1fr; }
			}

			/* ===== Side nav ===== */
			.reirie-fw-nav {
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 12px;
				padding: 10px;
				box-shadow: 0 1px 2px rgba(0,0,0,0.03);
				position: sticky;
				top: 56px;
			}
			.reirie-fw-nav-title {
				font-size: 10px; font-weight: 700; color: #888;
				letter-spacing: 0.2em; text-transform: uppercase;
				padding: 8px 12px 4px; margin: 0;
			}
			.reirie-fw-nav-list { list-style: none; padding: 0; margin: 6px 0; }
			.reirie-fw-nav-list li { margin: 2px 0; }
			.reirie-fw-nav-list a {
				display: flex; align-items: center; gap: 10px;
				padding: 9px 12px;
				border-radius: 8px;
				color: #444; text-decoration: none;
				font-size: 13px; font-weight: 500;
				transition: background .15s ease, color .15s ease;
			}
			.reirie-fw-nav-list a .dashicons {
				font-size: 16px; width: 16px; height: 16px; color: #c43a73;
			}
			.reirie-fw-nav-list a:hover { background: #fdf3f8; color: #c43a73; }
			.reirie-fw-nav-list a.is-active {
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				color: #fff;
			}
			.reirie-fw-nav-list a.is-active .dashicons { color: #fff; }
			.reirie-fw-nav-divider {
				margin: 8px 12px; height: 1px; background: #ececec;
			}

			/* ===== Content ===== */
			.reirie-fw-content { min-width: 0; }
			.reirie-fw-panel {
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 12px;
				padding: 24px 28px 26px;
				box-shadow: 0 1px 2px rgba(0,0,0,0.03);
				margin: 0 0 16px;
				display: none;
			}
			.reirie-fw-panel.is-active { display: block; }
			.reirie-fw-panel-header {
				display: flex; align-items: center; gap: 12px;
				padding-bottom: 14px; margin-bottom: 18px;
				border-bottom: 1px solid #f1f1f3;
			}
			.reirie-fw-panel-header .icon {
				width: 36px; height: 36px; border-radius: 10px;
				background: linear-gradient(135deg, #fff0f7 0%, #f3eaff 100%);
				display: inline-flex; align-items: center; justify-content: center;
			}
			.reirie-fw-panel-header .icon .dashicons {
				font-size: 18px; width: 18px; height: 18px; color: #c43a73;
			}
			.reirie-fw-panel-header h2 {
				font-size: 17px; font-weight: 600; color: #1d1d1f;
				margin: 0; padding: 0; line-height: 1.3;
			}
			.reirie-fw-panel-header p {
				font-size: 12px; color: #888;
				margin: 2px 0 0; line-height: 1.5;
			}

			/* ===== Form fields ===== */
			.reirie-fw-field { margin: 0 0 18px; }
			.reirie-fw-field label.field-label {
				display: block; font-size: 13px; font-weight: 600;
				color: #1d1d1f; margin: 0 0 6px; letter-spacing: 0.01em;
			}
			.reirie-fw-field input[type="text"],
			.reirie-fw-field input[type="url"],
			.reirie-fw-field input[type="number"],
			.reirie-fw-field select {
				width: 100%; max-width: 640px;
				padding: 9px 12px;
				border: 1px solid #dcdcdc; border-radius: 8px;
				font-size: 13.5px; color: #1d1d1f;
				background: #fff;
				transition: border-color .15s ease, box-shadow .15s ease;
				box-shadow: 0 1px 1px rgba(0,0,0,0.02);
			}
			.reirie-fw-field input[type="number"] { max-width: 140px; }
			.reirie-fw-field input:focus,
			.reirie-fw-field select:focus {
				outline: none; border-color: #ff7eb6;
				box-shadow: 0 0 0 3px rgba(255,126,182,0.18);
			}
			.reirie-fw-field .field-desc {
				font-size: 12px; color: #888; margin: 6px 0 0; line-height: 1.5;
			}
			.reirie-fw-field.checkbox label.checkbox-label {
				display: inline-flex; align-items: center; gap: 8px;
				font-size: 13.5px; color: #1d1d1f; cursor: pointer;
			}
			.reirie-fw-field.checkbox input[type="checkbox"] {
				width: 16px; height: 16px; margin: 0;
			}

			/* ===== Media field ===== */
			.reirie-fw-field .media-row {
				display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
			}
			.reirie-fw-field .media-preview {
				width: 140px; height: 90px; border-radius: 8px;
				background: #f7f7f9 center/cover no-repeat;
				border: 1px solid #ececec;
				display: inline-flex; align-items: center; justify-content: center;
				color: #bbb; font-size: 11px; flex-shrink: 0;
				overflow: hidden; position: relative;
			}
			.reirie-fw-field .media-preview .dashicons {
				font-size: 30px; width: 30px; height: 30px;
			}
			.reirie-fw-field .media-preview video,
			.reirie-fw-field .media-preview img.media-thumb {
				width: 100%; height: 100%; object-fit: cover; display: block;
				background: #000;
			}
			.reirie-fw-field .media-preview .media-badge {
				position: absolute; left: 4px; bottom: 4px;
				background: rgba(0,0,0,0.7); color: #fff;
				font-size: 10px; padding: 2px 6px; border-radius: 4px;
				font-weight: 600; letter-spacing: 0.05em;
			}
			.reirie-fw-field .media-actions { display: flex; gap: 6px; flex-wrap: wrap; }
			.reirie-fw-field .media-actions .button { font-size: 12px; }
			.reirie-fw-field .media-filename {
				font-size: 12px; color: #444; font-weight: 600;
				flex: 1 1 auto; min-width: 0; word-break: break-all;
			}
			.reirie-fw-field .media-filename .media-size {
				display: inline-block; margin-left: 8px;
				font-size: 11px; color: #888; font-weight: 400;
			}
			.reirie-fw-field .media-url {
				font-size: 11px; color: #888; word-break: break-all; flex: 1 1 100%;
				margin-top: 4px;
				background: #fafafa; border: 1px solid #eee; border-radius: 6px;
				padding: 6px 10px; font-family: monospace;
			}

			/* ===== Two column rows ===== */
			.reirie-fw-cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
			@media (max-width: 780px) { .reirie-fw-cols-2 { grid-template-columns: 1fr; } }
			.reirie-fw-cols-2 .reirie-fw-field { margin-bottom: 0; }
			.reirie-fw-cols-2 input[type="text"],
			.reirie-fw-cols-2 input[type="url"] { max-width: 100%; }

			/* ===== Submit bar ===== */
			.reirie-fw-submit-bar {
				position: sticky; bottom: 0;
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 12px;
				padding: 14px 20px;
				display: flex; align-items: center; justify-content: space-between;
				box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
				gap: 14px; flex-wrap: wrap;
				z-index: 10;
			}
			.reirie-fw-submit-bar .hint {
				font-size: 12px; color: #888;
			}
			.reirie-fw-submit-bar .button-primary {
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				border-color: transparent;
				color: #fff;
				padding: 8px 22px; font-size: 13px;
				border-radius: 999px;
				box-shadow: 0 4px 12px rgba(255,126,182,0.35);
				font-weight: 600;
				text-shadow: none;
				/* WordPressコアの .button は min-height:40px を強制するため、
				   padding/font-sizeだけ上書きしても縦に間延びしたボタンになる
				   （実測: border-box高さ約41px、他の同列ボタンと不揃いになっていた）。
				   高さも明示的に上書きして内容に合わせる。 */
				display: inline-flex;
				align-items: center;
				justify-content: center;
				line-height: 1.4;
				min-height: 0;
				height: auto;
			}
			/* WordPressコアには .wp-core-ui .button .dashicons { line-height:1.9; vertical-align:top; }
			   というルールがあり、これは高さ40px前提のデフォルトボタン用に設計されている。
			   本ボタンは上記で高さをコンテンツに合わせて縮めているが、アイコン側の
			   line-height/vertical-alignはコア側の値のまま残るため、チェックマークが
			   テキストより下（ボタン下端寄り）にずれて見える不具合があった
			   （ユーザー指摘のスクリーンショットで実測確認）。
			   アイコンをフォントメトリクスに依存しない固定サイズのflexボックス化し、
			   グリフ自体を中央揃えすることで、ブラウザ・フォント差異に左右されず
			   確実にテキストと同じ高さに揃える。 */
			.reirie-fw-submit-bar .button-primary .dashicons {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 16px;
				height: 16px;
				font-size: 16px;
				line-height: 1;
				vertical-align: middle;
				flex-shrink: 0;
			}
			.reirie-fw-submit-bar .button-primary:hover {
				background: linear-gradient(135deg, #ff63a6 0%, #9a62f0 100%);
				color: #fff;
			}

			/* ===== CPT 一覧テーブル ===== */
			.reirie-cpt-toolbar {
				display: flex; align-items: center; gap: 12px;
				margin: 0 0 14px;
			}
			.reirie-cpt-search {
				flex: 1; max-width: 360px;
				padding: 8px 12px;
				border: 1px solid #dcdcdc; border-radius: 8px;
				font-size: 13px; background: #fff;
			}
			.reirie-cpt-search:focus {
				outline: none; border-color: #ff7eb6;
				box-shadow: 0 0 0 3px rgba(255,126,182,0.18);
			}
			.reirie-cpt-count { font-size: 12px; color: #888; }

			.reirie-cpt-new {
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%) !important;
				border-color: transparent !important;
				color: #fff !important;
				border-radius: 999px !important;
				padding: 6px 16px !important;
				font-weight: 600 !important;
				box-shadow: 0 4px 12px rgba(255,126,182,0.3) !important;
				text-shadow: none !important;
				/* WordPressコアの .button は min-height:40px を強制するため、
				   padding/font-sizeだけ上書きしても縦に間延びしたボタンになる。
				   高さも明示的に上書きして内容に合わせる。 */
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				line-height: 1.4 !important;
				min-height: 0 !important;
				height: auto !important;
			}
			.reirie-cpt-new:hover {
				background: linear-gradient(135deg, #ff63a6 0%, #9a62f0 100%) !important;
				color: #fff !important;
			}
			/* .reirie-fw-submit-bar .button-primary と同様に、WordPressコアの
			   .wp-core-ui .button .dashicons { line-height:1.9; vertical-align:top; }
			   が高さ40px用の値のまま残り、「○○を追加」ボタンのプラスアイコンが
			   テキストより下にずれて見えていた不具合を修正。
			   フォントメトリクスに依存しない固定サイズのflexボックスとして
			   グリフ自体を中央揃えする。 */
			.reirie-cpt-new .dashicons {
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				width: 16px !important;
				height: 16px !important;
				font-size: 16px !important;
				line-height: 1 !important;
				vertical-align: middle !important;
				flex-shrink: 0;
			}

			.reirie-cpt-table-wrap {
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 10px;
				overflow-x: auto;
				overflow-y: hidden;
				-webkit-overflow-scrolling: touch;
			}
			.reirie-cpt-table { width: 100%; min-width: 560px; border-collapse: collapse; }
			.reirie-cpt-table thead th {
				background: #fafafa; padding: 10px 14px; text-align: left;
				font-size: 11px; font-weight: 600; color: #888;
				letter-spacing: 0.1em; text-transform: uppercase;
				border-bottom: 1px solid #ececec; white-space: nowrap;
			}
			.reirie-cpt-table tbody td {
				padding: 12px 14px; border-bottom: 1px solid #f4f4f6;
				font-size: 13px; color: #333; vertical-align: middle;
			}
			.reirie-cpt-table tbody tr:last-child td { border-bottom: none; }
			.reirie-cpt-table tbody tr:hover { background: #fdf8fb; }
			.reirie-cpt-table tbody tr.is-draft td { color: #999; }
			.reirie-cpt-table tbody tr.is-scheduled td { background: linear-gradient(90deg, rgba(255,184,77,.06), rgba(255,138,61,.04)); }
			.reirie-cpt-table .row-status { font-weight: 600; padding: 2px 7px; border-radius: 999px; background: #eee; color: #666; margin-left: 6px; font-size: 11px; }
			.reirie-cpt-table .row-status--scheduled { background: linear-gradient(135deg, #ffb84d, #ff8a3d); color: #fff; }
			.reirie-cpt-table .col-thumbnail { width: 64px; }
			.reirie-cpt-table .col-actions { width: 240px; text-align: right; white-space: nowrap; }
			.reirie-cpt-table .col-menu_order { width: 60px; text-align: center; color: #888; }
			.reirie-cpt-table .row-thumb {
				width: 44px; height: 44px;
				border-radius: 8px;
				background: #f7f7f9 center/cover no-repeat;
				border: 1px solid #ececec;
				display: inline-flex; align-items: center; justify-content: center;
				color: #ccc;
			}
			.reirie-cpt-table .row-thumb .dashicons { font-size: 18px; width: 18px; height: 18px; }
			.reirie-cpt-table .row-title { font-weight: 600; color: #1d1d1f; }
			.reirie-cpt-table .row-title small {
				display: inline-block; margin-left: 6px;
				padding: 1px 7px; border-radius: 999px;
				font-size: 10px; font-weight: 600;
				background: #fff6e6; color: #a06800;
				vertical-align: middle;
			}
			.reirie-cpt-table .row-action {
				display: inline-flex; align-items: center; gap: 4px;
				padding: 5px 10px;
				border-radius: 6px;
				font-size: 12px; font-weight: 500;
				background: transparent; border: 1px solid transparent;
				color: #444; cursor: pointer;
				margin-left: 4px;
			}
			.reirie-cpt-table .row-action:hover { background: #fdf3f8; color: #c43a73; border-color: #ffd6e8; }
			.reirie-cpt-table .row-action.row-action-delete:hover { background: #fff3f3; color: #b8001a; border-color: #f4c2c2; }
			.reirie-cpt-table .row-action.row-action-copy-url:hover { background: #f0f9ff; color: #1976d2; border-color: #b8dcf5; }
			.reirie-cpt-table .row-action.is-copied { background: linear-gradient(135deg,#ff8a3d,#ff5b9c); color: #fff; border-color: transparent; }
			.reirie-cpt-table .row-action.is-copy-failed { background: #fff3f3; color: #b8001a; border-color: #f4c2c2; }
			.reirie-cpt-table .row-action .dashicons { font-size: 14px; width: 14px; height: 14px; }
			.reirie-cpt-loading td, .reirie-cpt-empty td {
				text-align: center; color: #999; padding: 30px 14px !important; font-style: italic;
			}

			.reirie-cpt-pagination {
				display: flex; justify-content: center; gap: 4px;
				margin: 14px 0 0;
			}
			.reirie-cpt-pagination button {
				min-width: 32px; height: 32px; padding: 0 10px;
				border: 1px solid #ececec; background: #fff;
				border-radius: 6px; font-size: 12px; cursor: pointer; color: #444;
			}
			.reirie-cpt-pagination button:hover { border-color: #ffc7df; color: #c43a73; }
			.reirie-cpt-pagination button.is-current {
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				border-color: transparent; color: #fff; font-weight: 600;
			}
			.reirie-cpt-pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

			/* ===== モーダル ===== */
			.reirie-modal-overlay {
				position: fixed; inset: 0;
				background: rgba(20,20,28,0.55);
				backdrop-filter: blur(4px);
				-webkit-backdrop-filter: blur(4px);
				z-index: 99999;
				display: flex; align-items: center; justify-content: center;
				padding: 20px;
				animation: reirie-fade-in .15s ease;
			}
			@keyframes reirie-fade-in { from { opacity: 0; } to { opacity: 1; } }
			@keyframes reirie-modal-in { from { transform: translateY(10px) scale(0.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
			.reirie-modal {
				background: #fff;
				border-radius: 16px;
				width: 100%; max-width: 720px;
				max-height: calc(100vh - 60px);
				display: flex; flex-direction: column;
				box-shadow: 0 30px 80px rgba(0,0,0,0.3);
				animation: reirie-modal-in .2s ease;
				overflow: hidden;
			}
			.reirie-modal-header {
				display: flex; align-items: center; gap: 12px;
				padding: 18px 22px;
				border-bottom: 1px solid #f1f1f3;
			}
			.reirie-modal-icon {
				width: 36px; height: 36px; border-radius: 10px;
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				display: inline-flex; align-items: center; justify-content: center;
				box-shadow: 0 4px 12px rgba(255,126,182,0.3);
			}
			.reirie-modal-icon .dashicons { color: #fff; font-size: 18px; width: 18px; height: 18px; }
			.reirie-modal-title {
				font-size: 16px; font-weight: 600; color: #1d1d1f;
				margin: 0; padding: 0; flex: 1;
			}
			.reirie-modal-close {
				background: none; border: none; cursor: pointer;
				width: 32px; height: 32px;
				border-radius: 8px;
				display: inline-flex; align-items: center; justify-content: center;
				color: #888;
			}
			.reirie-modal-close:hover { background: #f4f4f6; color: #1d1d1f; }
			.reirie-modal-body {
				flex: 1; overflow-y: auto;
				padding: 20px 24px;
			}
			.reirie-modal-footer {
				display: flex; align-items: center; gap: 10px;
				padding: 14px 22px;
				border-top: 1px solid #f1f1f3;
				background: #fafafa;
			}
			.reirie-modal-footer .button-primary {
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				border-color: transparent;
				color: #fff;
				border-radius: 999px;
				padding: 6px 18px;
				font-weight: 600;
				box-shadow: 0 4px 12px rgba(255,126,182,0.3);
				text-shadow: none;
				/* ★アイコンとテキストを横並びで中央揃え */
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 4px;
				line-height: 1;
				min-height: 32px;
			}
			.reirie-modal-footer .button-primary .dashicons {
				font-size: 16px; width: 16px; height: 16px;
				line-height: 1; vertical-align: middle;
				margin: 0;
			}
			.reirie-modal-footer .button-primary:hover {
				background: linear-gradient(135deg, #ff63a6 0%, #9a62f0 100%);
				color: #fff;
			}
			.reirie-modal-footer .reirie-modal-cancel {
				border-radius: 999px; padding: 6px 16px;
				/* キャンセルボタンも統一して中央揃え */
				display: inline-flex;
				align-items: center;
				justify-content: center;
				line-height: 1;
				min-height: 32px;
			}
			.reirie-modal-delete {
				background: none; border: 1px solid #f4c2c2;
				color: #b8001a;
				padding: 6px 12px;
				border-radius: 999px;
				cursor: pointer;
				font-size: 12px;
				display: inline-flex; align-items: center; gap: 4px;
				margin-right: auto;
			}
			.reirie-modal-delete:hover { background: #fff3f3; }
			.reirie-modal-delete .dashicons { font-size: 14px; width: 14px; height: 14px; }
			.reirie-modal-msg { font-size: 12px; color: #888; margin-right: 8px; }
			.reirie-modal-msg.is-error { color: #b8001a; }
			.reirie-modal-msg.is-success { color: #1f7a3f; }

			.reirie-modal-divider {
				margin: 18px 0 12px;
				padding-bottom: 6px;
				border-bottom: 1px solid #f1f1f3;
				font-size: 11px; font-weight: 700;
				color: #888;
				letter-spacing: 0.18em;
				text-transform: uppercase;
			}
			.reirie-modal-body .reirie-fw-field { margin-bottom: 16px; }
			.reirie-modal-body .reirie-fw-field input[type="text"],
			.reirie-modal-body .reirie-fw-field input[type="url"],
			.reirie-modal-body .reirie-fw-field input[type="date"],
			.reirie-modal-body .reirie-fw-field input[type="number"],
			.reirie-modal-body .reirie-fw-field select,
			.reirie-modal-body .reirie-fw-field textarea {
				width: 100%; max-width: 100%;
				padding: 9px 12px;
				border: 1px solid #dcdcdc; border-radius: 8px;
				font-size: 13.5px; color: #1d1d1f;
				background: #fff;
				box-shadow: 0 1px 1px rgba(0,0,0,0.02);
				font-family: inherit;
			}
			.reirie-modal-body .reirie-fw-field textarea { line-height: 1.6; }
			.reirie-modal-body .reirie-fw-field input:focus,
			.reirie-modal-body .reirie-fw-field select:focus,
			.reirie-modal-body .reirie-fw-field textarea:focus {
				outline: none; border-color: #ff7eb6;
				box-shadow: 0 0 0 3px rgba(255,126,182,0.18);
			}
			.reirie-modal-body.is-readonly input,
			.reirie-modal-body.is-readonly textarea,
			.reirie-modal-body.is-readonly select { background: #fafafa; cursor: default; }
			.reirie-modal-body.is-readonly textarea { resize: vertical; }
			/* テキスト選択（コピー）は可能にする */
			.reirie-modal-body.is-readonly input,
			.reirie-modal-body.is-readonly select { caret-color: transparent; }

			/* ===== TinyMCE エディタ（モーダル内本文編集）===== */
			.reirie-modal-body .reirie-fw-field-editor { margin-top: 6px; }
			.reirie-editor-label-row {
				display: flex; align-items: center; justify-content: space-between;
				gap: 10px; margin-bottom: 6px; flex-wrap: wrap;
			}
			/*
			 * .reirie-fw-field label.field-label（class + type セレクタ）は
			 * .reirie-editor-label-row .field-label（class 2つ）より詳細度が高く、
			 * margin: 0 0 6px が優先されてラベル下に余白が残ってしまっていた。
			 * label.field-label まで指定して詳細度を合わせ、確実に margin:0 を効かせる。
			 */
			.reirie-editor-label-row label.field-label { margin: 0; }
			/*
			 * .reirie-insert-image-btn は WordPress コアの .button クラス
			 * （.wp-core-ui .button）を継承しており、コア側で
			 * min-height: 40px; line-height: 2.92307692 (38px 相当) が指定されている。
			 * 以前は padding / font-size / line-height だけを上書きしていたため、
			 * min-height だけがコアの 40px のまま残り、中身（12px文字+16pxアイコン）に対して
			 * 縦にかなり間延びした（実測: 高さ約40px、中身は約20px）不格好なボタンになっていた。
			 * min-height / height を明示的に上書きしてボタン全体のサイズを内容に合わせる。
			 */
			.reirie-insert-image-btn {
				display: inline-flex; align-items: center; gap: 6px;
				font-size: 12px !important; padding: 3px 12px !important;
				height: auto !important; min-height: 0 !important;
				line-height: 1.6 !important; border-radius: 999px !important;
				border-color: #d9cdf0 !important; color: #6a4bb6 !important;
				vertical-align: middle;
			}
			.reirie-insert-image-btn:hover { background: #f6f0ff !important; border-color: #c9b7f0 !important; }
			.reirie-insert-image-btn .dashicons {
				line-height: 1 !important;
			}
			/*
			 * .modal-copy-url-btn（公開URL欄の「URLをコピー」ボタン）は
			 * これまでCSSでの上書きが一切なく、WordPressコアの素の .button
			 * （角ばった四角形、min-height:40px、line-height:2.92307692）の
			 * ままレンダリングされていた。隣接する公開URL入力欄は
			 * border-radius:8px の丸みを帯びたデザインなのに対し、
			 * ボタン側は角ばった形・高さも不揃いで、アイコンも
			 * line-height:1.9のままテキストより下にずれて見えていた。
			 * 入力欄とデザイン・高さを揃え、アイコンも中央揃えする。
			 */
			.modal-copy-url-btn {
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				gap: 4px;
				border-radius: 8px !important;
				border-color: #dcdcdc !important;
				color: #1d1d1f !important;
				background: #fff !important;
				font-size: 13px !important;
				padding: 9px 14px !important;
				height: auto !important;
				min-height: 0 !important;
				line-height: 1.2 !important;
			}
			.modal-copy-url-btn:hover {
				background: #f6f0ff !important;
				border-color: #c9b7f0 !important;
			}
			.modal-copy-url-btn .dashicons {
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				width: 16px !important;
				height: 16px !important;
				font-size: 16px !important;
				line-height: 1 !important;
				vertical-align: middle !important;
				flex-shrink: 0;
			}
			.reirie-modal-body .wp-editor-wrap {
				border: 1px solid #e6e1ee;
				border-radius: 12px;
				overflow: hidden;
				background: #fff;
			}
			.reirie-modal-body .wp-editor-wrap.tmce-active .wp-editor-area,
			.reirie-modal-body .wp-editor-wrap.html-active .wp-editor-area { background: #fff; }
			.reirie-modal-body .wp-editor-tools { background: #faf7ff; padding: 6px 8px; border-bottom: 1px solid #efeaf7; }
			.reirie-modal-body .wp-switch-editor {
				background: #fff; border: 1px solid #e6e1ee; border-radius: 999px;
				color: #6a4bb6; font-size: 11px; padding: 4px 14px; margin-right: 4px;
			}
			.reirie-modal-body .wp-switch-editor:hover { background: #f6f0ff; border-color: #c9b7f0; }
			.reirie-modal-body .mce-tinymce { box-shadow: none !important; border: none !important; }
			.reirie-modal-body .mce-toolbar-grp { background: #fafafa; border-bottom: 1px solid #efeaf7; }
			.reirie-modal-body .mce-edit-area iframe { background: #fff !important; }
			/* TinyMCE モーダル（リンク挿入等）が REIRIE モーダルより前面に来るように */
			.mce-floatpanel, .mce-window { z-index: 200000 !important; }
			#wp-link-wrap { z-index: 200000 !important; }

			/* ===== Quick links grid (bottom) ===== */
			.reirie-fw-quick-title {
				font-size: 11px; font-weight: 700; color: #888;
				letter-spacing: 0.18em; text-transform: uppercase;
				margin: 30px 0 12px; padding-bottom: 8px;
				border-bottom: 1px solid #ececec;
				display: flex; align-items: center; gap: 8px;
			}
			.reirie-fw-quick-title .dashicons { font-size: 14px; width: 14px; height: 14px; color: #c43a73; }
			.reirie-fw-quick-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
				gap: 12px;
			}
			.reirie-fw-quick {
				background: #fff;
				border: 1px solid #ececec;
				border-radius: 10px;
				padding: 14px 16px;
				display: flex; align-items: center; gap: 12px;
				text-decoration: none; color: inherit;
				transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
			}
			.reirie-fw-quick:hover {
				transform: translateY(-1px);
				border-color: #ffc7df;
				box-shadow: 0 6px 14px rgba(255,126,182,0.12);
			}
			.reirie-fw-quick .ic {
				width: 36px; height: 36px; border-radius: 9px;
				background: linear-gradient(135deg, #fff0f7 0%, #f3eaff 100%);
				display: inline-flex; align-items: center; justify-content: center;
				flex-shrink: 0;
			}
			.reirie-fw-quick:hover .ic { background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%); }
			.reirie-fw-quick .ic .dashicons { font-size: 18px; width: 18px; height: 18px; color: #c43a73; transition: color .2s ease; }
			.reirie-fw-quick:hover .ic .dashicons { color: #fff; }
			.reirie-fw-quick .tx strong { display: block; font-size: 13px; color: #1d1d1f; line-height: 1.3; }
			.reirie-fw-quick .tx span { display: block; font-size: 11px; color: #888; margin-top: 2px; }

			/* ============================================================
			   スマートフォン対応（〜680px）
			   このダッシュボードは #wpcontent の余白を自前で潰しているため、
			   WordPress標準のモバイル調整に頼らず、ここで一括して対応する。
			   ============================================================ */
			@media (max-width: 680px) {
				.reirie-fw-inner { padding: 12px; }

				.reirie-fw-header { gap: 10px; margin-bottom: 4px; }
				.reirie-fw-header .brand-mark { width: 34px; height: 34px; border-radius: 10px; }
				.reirie-fw-header .brand-mark .dashicons { font-size: 18px; width: 18px; height: 18px; }
				.reirie-fw-header h1 { font-size: 17px; }
				.reirie-fw-lead { margin-left: 0; font-size: 12px; margin-bottom: 16px; }

				/* セットアップ進捗：横スクロールできる1行チップ表示に */
				.reirie-fw-status { padding: 10px 12px; gap: 8px; }
				.reirie-fw-status-title { width: 100%; }
				.reirie-fw-status-list {
					flex-wrap: nowrap;
					overflow-x: auto;
					-webkit-overflow-scrolling: touch;
					padding-bottom: 2px;
				}
				.reirie-fw-status-list li { flex-shrink: 0; font-size: 11px; padding: 4px 10px; }

				/* サイドナビ：固定(sticky)をやめてスクロール追従の混乱を防ぐ */
				.reirie-fw-nav { position: static; padding: 6px; }
				.reirie-fw-nav-list a { padding: 10px 10px; font-size: 13px; }

				.reirie-fw-panel { padding: 16px 14px 18px; border-radius: 10px; }
				.reirie-fw-panel-header { flex-wrap: wrap; row-gap: 10px; padding-bottom: 12px; margin-bottom: 14px; }
				.reirie-fw-panel-header h2 { font-size: 15px; }
				.reirie-fw-panel-header p { font-size: 11.5px; }
				.reirie-fw-panel-header .reirie-cpt-new {
					margin-left: 0 !important;
					width: 100%;
					text-align: center;
					justify-content: center;
				}

				/* フォーム項目は画面幅いっぱいに */
				.reirie-fw-field input[type="text"],
				.reirie-fw-field input[type="url"],
				.reirie-fw-field select { max-width: 100%; }
				.reirie-fw-field .media-preview { width: 120px; height: 78px; }

				/* 保存バー：固定位置のまま縦積みにしてボタンを押しやすく */
				.reirie-fw-submit-bar {
					flex-direction: column;
					align-items: stretch;
					padding: 12px 14px;
					gap: 8px;
				}
				.reirie-fw-submit-bar .hint { text-align: center; font-size: 11px; }
				.reirie-fw-submit-bar .button-primary,
				.reirie-members-submit-bar .button-primary { width: 100%; text-align: center; }
				.reirie-members-submit-bar {
					flex-direction: column;
					align-items: stretch;
					padding: 14px 14px;
				}
				.reirie-members-submit-bar .hint { text-align: center; }

				/* ===== CPT 一覧：検索欄と件数を縦積みに ===== */
				.reirie-cpt-toolbar { flex-wrap: wrap; gap: 8px; }
				.reirie-cpt-search { flex: 1 1 100%; max-width: 100%; }
				.reirie-cpt-count { flex: 1 1 100%; }

				/* ===== CPT 一覧テーブル：カード表示に変換 =====
				   固定幅カラムでの横崩れ・文字切れを防ぐため、
				   thead を隠し、各行をカード化して data-label を見出しとして表示する。 */
				.reirie-cpt-table-wrap { overflow-x: visible; border: none; background: transparent; }
				.reirie-cpt-table { min-width: 0; width: 100%; }
				.reirie-cpt-table thead { display: none; }
				.reirie-cpt-table, .reirie-cpt-table tbody { display: block; width: 100%; }
				.reirie-cpt-table tr {
					display: block;
					background: #fff;
					border: 1px solid #ececec;
					border-radius: 10px;
					padding: 10px 12px;
					margin: 0 0 10px;
				}
				.reirie-cpt-table tr.reirie-cpt-loading,
				.reirie-cpt-table tr.reirie-cpt-empty { text-align: center; }
				.reirie-cpt-table tbody tr:last-child { margin-bottom: 0; }
				.reirie-cpt-table td {
					display: flex;
					align-items: center;
					justify-content: space-between;
					gap: 10px;
					padding: 6px 0;
					border-bottom: 1px dashed #f1f1f3;
					font-size: 13px;
					width: auto;
				}
				.reirie-cpt-table tr > td:last-child { border-bottom: none; }
				.reirie-cpt-table td[data-label]::before {
					content: attr(data-label);
					flex-shrink: 0;
					font-size: 10.5px;
					font-weight: 700;
					letter-spacing: 0.05em;
					color: #999;
					text-transform: uppercase;
				}
				.reirie-cpt-table .col-thumbnail {
					justify-content: flex-start;
					border-bottom: 1px dashed #f1f1f3;
				}
				.reirie-cpt-table .col-thumbnail::before { content: none; }
				.reirie-cpt-table .col-thumbnail .row-thumb { width: 52px; height: 52px; }
				.reirie-cpt-table .row-title { white-space: normal; word-break: break-word; text-align: right; }
				.reirie-cpt-table .col-actions {
					width: auto; text-align: left; white-space: normal;
					justify-content: flex-end; flex-wrap: wrap; border-bottom: none;
					padding-top: 8px;
				}
				.reirie-cpt-table .col-actions::before { content: none; }
				.reirie-cpt-table .row-action { padding: 6px 10px; margin: 0 0 0 6px; }
				.reirie-cpt-loading td, .reirie-cpt-empty td { display: block; text-align: center; }
				.reirie-cpt-loading td::before, .reirie-cpt-empty td::before { content: none; }

				.reirie-cpt-pagination { flex-wrap: wrap; }

				/* ===== モーダル：スマホでは全画面に近い形で表示 ===== */
				.reirie-modal-overlay { padding: 0; align-items: flex-end; }
				.reirie-modal {
					max-width: 100%;
					width: 100%;
					max-height: calc(100vh - 24px);
					border-radius: 16px 16px 0 0;
				}
				.reirie-modal-header { padding: 14px 16px; }
				.reirie-modal-title { font-size: 14px; }
				.reirie-modal-body { padding: 14px 16px; }
				.reirie-modal-footer { padding: 12px 16px; flex-wrap: wrap; gap: 8px; }
				.reirie-modal-delete { margin-right: 0; order: 3; width: 100%; justify-content: center; }
				.reirie-modal-msg { width: 100%; text-align: center; margin-right: 0; }
				.reirie-modal-footer .reirie-modal-cancel,
				.reirie-modal-footer .button-primary { flex: 1 1 auto; }

				/* Quick links */
				.reirie-fw-quick-grid { grid-template-columns: 1fr; }
			}
		</style>

		<div class="reirie-fw-inner">

			<div class="reirie-fw-header">
				<span class="brand-mark"><span class="dashicons dashicons-heart"></span></span>
				<h1>REIRIE サイト設定</h1>
			</div>
			<p class="reirie-fw-lead">サイト全体のデザインや表示内容を、この画面ですべて編集できます。左メニューから項目を選び、最下部の「保存する」を押してください。</p>

			<?php if ( ! empty( $_GET['saved'] ) ) : ?>
				<div class="reirie-saved-notice">
					<span class="dashicons dashicons-yes-alt"></span>
					設定を保存しました。<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" style="color:#1f7a3f;text-decoration:underline;margin-left:6px;">サイトを表示して確認 →</a>
				</div>
				<?php
				// ===== 診断ログ表示（保存直後のみ） =====
				$dbg = get_transient( 'reirie_save_debug' );
				if ( $dbg && is_array( $dbg ) ) :
					$hero_video_post  = isset( $dbg['post']['reirie_hero_video'] )  ? $dbg['post']['reirie_hero_video']  : '(キーが POST に存在しません)';
					$hero_video_saved = isset( $dbg['saved']['reirie_hero_video'] ) ? $dbg['saved']['reirie_hero_video'] : '(保存されませんでした)';
					$current_theme_mod = get_theme_mod( 'reirie_hero_video', '(未設定)' );
				?>
					<details style="background:#fff8e6;border:1px solid #ffd54f;border-radius:10px;padding:14px 18px;margin-bottom:14px;">
						<summary style="cursor:pointer;font-weight:600;color:#8a6d00;"><span class="dashicons dashicons-info"></span> 診断情報 (保存内容のデバッグ) — クリックで展開</summary>
						<div style="margin-top:12px;font-family:monospace;font-size:12px;line-height:1.7;">
							<p style="margin:0 0 8px;"><strong>POST されたヒーロー動画 URL:</strong></p>
							<div style="background:#fff;border:1px solid #eee;border-radius:6px;padding:8px 10px;word-break:break-all;"><?php echo esc_html( $hero_video_post ); ?></div>
							<p style="margin:12px 0 8px;"><strong>保存処理での値:</strong></p>
							<div style="background:#fff;border:1px solid #eee;border-radius:6px;padding:8px 10px;word-break:break-all;"><?php echo esc_html( $hero_video_saved ); ?></div>
							<p style="margin:12px 0 8px;"><strong>現在 get_theme_mod('reirie_hero_video') が返す値:</strong></p>
							<div style="background:#fff;border:1px solid #eee;border-radius:6px;padding:8px 10px;word-break:break-all;"><?php echo esc_html( $current_theme_mod ); ?></div>
							<p style="margin:12px 0 0;font-size:11px;color:#666;">保存時刻: <?php echo esc_html( $dbg['time'] ); ?></p>
						</div>
					</details>
				<?php
					delete_transient( 'reirie_save_debug' );
				endif;
				?>
			<?php endif; ?>

			<div class="reirie-fw-status">
				<p class="reirie-fw-status-title">
					<span class="dashicons dashicons-chart-bar"></span>セットアップ進捗
				</p>
				<ul class="reirie-fw-status-list">
					<?php foreach ( $status_items as $item ) :
						$cls  = $item['ok'] ? 'is-ok' : 'is-warn';
						$icon = $item['ok'] ? 'yes-alt' : 'warning';
					?>
						<li class="<?php echo esc_attr( $cls ); ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
							<?php if ( ! $item['ok'] && ! empty( $item['help'] ) ) : ?>
								<a href="<?php echo esc_url( $item['help'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $item['label'] ); ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( $reirie_cron_health['missed'] > 0 ) : ?>
				<div class="reirie-fw-missed-alert" id="reirie-missed-alert" style="margin:16px 0;padding:16px 20px;background:linear-gradient(135deg,#fff3d6 0%,#ffe2ec 100%);border:1px solid #ff8a3d;border-radius:14px;color:#a85b00;">
					<div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
						<div style="flex:1;min-width:240px;">
							<p style="margin:0 0 4px;font-weight:700;font-size:15px;">
								⚠ 公開時刻を過ぎた予約投稿が <?php echo (int) $reirie_cron_health['missed']; ?> 件あります
							</p>
							<p style="margin:0;font-size:13px;line-height:1.6;">
								WordPress の自動公開（wp-cron）はサイトへのアクセスがあった時に発火する仕組みです。深夜帯などアクセスが無かった時間帯に予約公開時刻を迎えると、次のアクセスまで未公開のまま残ることがあります。
							</p>
							<details style="margin-top:8px;font-size:12px;">
								<summary style="cursor:pointer;color:#a85b00;">対象投稿を表示</summary>
								<ul style="margin:8px 0 0 18px;padding:0;">
									<?php foreach ( $reirie_cron_health['missed_list'] as $ml ) : ?>
										<li>#<?php echo (int) $ml['id']; ?> 「<?php echo esc_html( $ml['title'] ); ?>」<small style="color:#888;">（予定: <?php echo esc_html( $ml['date'] ); ?>）</small></li>
									<?php endforeach; ?>
								</ul>
							</details>
						</div>
						<button type="button" id="reirie-publish-missed-btn" class="button button-primary" style="background:linear-gradient(135deg,#ff8a3d,#ff5b9c);border:none;height:auto;padding:10px 20px;font-weight:700;">
							今すぐ公開する
						</button>
					</div>
					<p id="reirie-publish-missed-result" style="margin:10px 0 0;font-size:13px;display:none;"></p>
				</div>
				<script>
				(function(){
					var btn = document.getElementById('reirie-publish-missed-btn');
					if (!btn) return;
					btn.addEventListener('click', function(){
						btn.disabled = true;
						btn.textContent = '公開中...';
						var result = document.getElementById('reirie-publish-missed-result');
						var fd = new FormData();
						fd.append('action', 'reirie_publish_missed');
						fd.append('nonce', '<?php echo esc_js( wp_create_nonce( 'reirie_content_nonce' ) ); ?>');
						fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' })
							.then(function(r){ return r.json(); })
							.then(function(j){
								if (j && j.success) {
									result.style.display = 'block';
									result.style.color = '#1f7a3f';
									result.textContent = '✓ ' + j.data.message + '（ページを再読み込みします）';
									setTimeout(function(){ location.reload(); }, 1500);
								} else {
									result.style.display = 'block';
									result.style.color = '#c00';
									result.textContent = '✗ ' + ((j && j.data && j.data.message) || 'エラーが発生しました');
									btn.disabled = false;
									btn.textContent = '今すぐ公開する';
								}
							})
							.catch(function(e){
								result.style.display = 'block';
								result.style.color = '#c00';
								result.textContent = '✗ 通信エラー: ' + e.message;
								btn.disabled = false;
								btn.textContent = '今すぐ公開する';
							});
					});
				})();
				</script>
			<?php endif; ?>

			<form method="post" action="" id="reirie-settings-form" novalidate>
				<?php wp_nonce_field( 'reirie_save_settings', 'reirie_settings_nonce' ); ?>

				<div class="reirie-fw-layout">

					<!-- ===== Side Nav ===== -->
					<aside class="reirie-fw-nav">
						<p class="reirie-fw-nav-title">サイトの設定</p>
						<ul class="reirie-fw-nav-list">
							<?php $first = true; foreach ( $schema as $sec_key => $sec ) : ?>
								<li>
									<a href="#sec-<?php echo esc_attr( $sec_key ); ?>"
										data-target="sec-<?php echo esc_attr( $sec_key ); ?>"
										class="reirie-fw-tab <?php echo $first ? 'is-active' : ''; ?>">
										<span class="dashicons dashicons-<?php echo esc_attr( $sec['icon'] ); ?>"></span>
										<?php echo esc_html( $sec['label'] ); ?>
									</a>
								</li>
							<?php $first = false; endforeach; ?>
						</ul>

						<div class="reirie-fw-nav-divider"></div>
						<p class="reirie-fw-nav-title">コンテンツ</p>
						<ul class="reirie-fw-nav-list">
							<?php foreach ( $content_schema as $cpt_key => $cpt ) : ?>
								<li>
									<a href="#cpt-<?php echo esc_attr( $cpt_key ); ?>"
										data-target="cpt-<?php echo esc_attr( $cpt_key ); ?>"
										data-cpt="<?php echo esc_attr( $cpt_key ); ?>"
										class="reirie-fw-tab reirie-fw-cpt-tab">
										<span class="dashicons dashicons-<?php echo esc_attr( $cpt['icon'] ); ?>"></span>
										<?php echo esc_html( $cpt['label'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>

					<!-- ===== Content ===== -->
					<div class="reirie-fw-content">

						<?php $first = true; foreach ( $schema as $sec_key => $sec ) : ?>
							<section class="reirie-fw-panel <?php echo $first ? 'is-active' : ''; ?>" id="sec-<?php echo esc_attr( $sec_key ); ?>">
								<div class="reirie-fw-panel-header">
									<span class="icon"><span class="dashicons dashicons-<?php echo esc_attr( $sec['icon'] ); ?>"></span></span>
									<div>
										<h2><?php echo esc_html( $sec['label'] ); ?></h2>
										<p><?php echo esc_html( $sec['desc'] ); ?></p>
									</div>
								</div>

								<?php
								// SNS / カード / フッターは 2カラム表示
								$two_col = in_array( $sec_key, array( 'sns', 'contact', 'footer' ), true );
								if ( $two_col ) echo '<div class="reirie-fw-cols-2">';

								foreach ( $sec['fields'] as $key => $field ) :
									$value = reirie_get_setting_value( $key, $field );
									$desc  = isset( $field['desc'] ) ? $field['desc'] : '';
								?>
									<?php if ( $field['type'] === 'checkbox' ) : ?>
										<div class="reirie-fw-field checkbox">
											<label class="checkbox-label">
												<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $value, 1 ); ?>>
												<?php echo esc_html( $field['label'] ); ?>
											</label>
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>

									<?php elseif ( $field['type'] === 'select' ) : ?>
										<div class="reirie-fw-field">
											<label class="field-label" for="f-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
											<select id="f-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>">
												<?php foreach ( $field['choices'] as $cv => $cl ) : ?>
													<option value="<?php echo esc_attr( $cv ); ?>" <?php selected( $value, $cv ); ?>><?php echo esc_html( $cl ); ?></option>
												<?php endforeach; ?>
											</select>
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>

									<?php elseif ( $field['type'] === 'number' ) : ?>
										<div class="reirie-fw-field">
											<label class="field-label" for="f-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
											<input type="number" id="f-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"
												value="<?php echo esc_attr( $value ); ?>"
												min="<?php echo isset( $field['min'] ) ? esc_attr( $field['min'] ) : ''; ?>"
												max="<?php echo isset( $field['max'] ) ? esc_attr( $field['max'] ) : ''; ?>"
												step="<?php echo isset( $field['step'] ) ? esc_attr( $field['step'] ) : 1; ?>">
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>

									<?php elseif ( $field['type'] === 'media' || $field['type'] === 'image' ) :
										// attachment ID 系（custom_logo / site_icon）か、URL 直保存系か
										$is_id = ! empty( $field['attachment_id'] );
										$preview_url = '';
										$display_val = '';
										$file_name = '';
										if ( $is_id ) {
											$display_val = $value; // ID
											if ( $value ) {
												$src = wp_get_attachment_image_src( $value, 'medium' );
												if ( $src ) $preview_url = $src[0];
												$file_name = basename( get_attached_file( $value ) );
											}
										} else {
											$display_val = $value; // URL
											if ( $value ) {
												$preview_url = $value;
												$file_name = basename( wp_parse_url( $value, PHP_URL_PATH ) );
											}
										}
										$is_video = ( $field['type'] === 'media' && ! empty( $field['mime'] ) && $field['mime'] === 'video' );
									?>
										<div class="reirie-fw-field">
											<label class="field-label"><?php echo esc_html( $field['label'] ); ?></label>
											<div class="media-row" data-media-field data-is-id="<?php echo $is_id ? '1' : '0'; ?>" data-mime="<?php echo $is_video ? 'video' : 'image'; ?>">
												<span class="media-preview">
													<?php if ( $is_video && $preview_url ) : ?>
														<video src="<?php echo esc_url( $preview_url ); ?>" muted playsinline preload="metadata" autoplay loop></video>
														<span class="media-badge">VIDEO</span>
													<?php elseif ( $preview_url ) : ?>
														<img class="media-thumb" src="<?php echo esc_url( $preview_url ); ?>" alt="">
													<?php elseif ( $is_video ) : ?>
														<span class="dashicons dashicons-format-video"></span>
													<?php else : ?>
														<span class="dashicons dashicons-format-image"></span>
													<?php endif; ?>
												</span>
												<span class="media-filename">
													<?php if ( $file_name ) : ?>
														<?php echo esc_html( $file_name ); ?>
													<?php else : ?>
														<span style="color:#aaa;font-weight:400;">未設定</span>
													<?php endif; ?>
												</span>
												<span class="media-actions">
													<button type="button" class="button media-select"><?php echo $value ? '差し替え' : '選択'; ?></button>
													<?php if ( $value ) : ?>
														<button type="button" class="button media-clear">削除</button>
													<?php endif; ?>
												</span>
												<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $display_val ); ?>" class="media-value">
												<?php if ( ! $is_id && $value ) : ?>
													<span class="media-url"><?php echo esc_html( $value ); ?></span>
												<?php endif; ?>
											</div>
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>

									<?php elseif ( $field['type'] === 'textarea' ) : ?>
										<div class="reirie-fw-field reirie-fw-field--full">
											<label class="field-label" for="f-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
											<textarea id="f-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5" style="width:100%;font-family:inherit;line-height:1.7;padding:10px 12px;border:1px solid #dcd0d6;border-radius:8px;resize:vertical;"><?php echo esc_textarea( $value ); ?></textarea>
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>

									<?php else : // text / url ?>
										<div class="reirie-fw-field">
											<label class="field-label" for="f-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
											<input type="<?php echo $field['type'] === 'url' ? 'url' : 'text'; ?>"
												id="f-<?php echo esc_attr( $key ); ?>"
												name="<?php echo esc_attr( $key ); ?>"
												value="<?php echo esc_attr( $value ); ?>"
												placeholder="<?php echo isset( $field['default'] ) ? esc_attr( $field['default'] ) : ''; ?>">
											<?php if ( $desc ) : ?><p class="field-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
										</div>
									<?php endif; ?>

								<?php endforeach;
								if ( $two_col ) echo '</div>'; ?>

							</section>
						<?php $first = false; endforeach; ?>

						<!-- Submit bar (only for site settings tabs) -->
						<div class="reirie-fw-submit-bar" id="reirie-settings-submit-bar">
							<span class="hint"><span class="dashicons dashicons-info-outline" style="font-size:14px;width:14px;height:14px;vertical-align:middle;color:#b07aff;"></span> 変更はすべての項目に対して同時に保存されます</span>
							<button type="submit" name="reirie_settings_submit" value="1" class="button button-primary button-large">
								<span class="dashicons dashicons-saved" style="vertical-align:middle;margin-right:4px;"></span>すべての設定を保存する
							</button>
						</div>

						<?php /* ===== コンテンツ管理パネル群（同じレイアウト列内に配置 = サイドバーを常時表示） ===== */ ?>
						<div class="reirie-fw-cpt-panels">
						<?php foreach ( $content_schema as $cpt_key => $cpt ) :
							// メンバーは2人固定の専用UIに置き換えるため、汎用CRUDパネルはスキップ
							if ( $cpt_key === 'member' ) {
								reirie_render_members_panel( $cpt );
								continue;
							}
						?>
							<section class="reirie-fw-panel reirie-fw-cpt-panel" id="cpt-<?php echo esc_attr( $cpt_key ); ?>" data-cpt="<?php echo esc_attr( $cpt_key ); ?>" style="display:none;">
								<div class="reirie-fw-panel-header">
									<span class="icon"><span class="dashicons dashicons-<?php echo esc_attr( $cpt['icon'] ); ?>"></span></span>
									<div>
										<h2><?php echo esc_html( $cpt['label'] ); ?></h2>
										<p><?php echo esc_html( $cpt['desc'] ); ?></p>
									</div>
									<?php if ( empty( $cpt['no_create'] ) ) : ?>
										<button type="button" class="button reirie-cpt-new" data-cpt="<?php echo esc_attr( $cpt_key ); ?>" style="margin-left:auto;">
											<span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;margin-right:2px;"></span><?php echo esc_html( $cpt['singular'] ); ?>を追加
										</button>
									<?php endif; ?>
								</div>

								<div class="reirie-cpt-toolbar">
									<input type="text" class="reirie-cpt-search" placeholder="検索..." data-cpt="<?php echo esc_attr( $cpt_key ); ?>">
									<span class="reirie-cpt-count" data-cpt="<?php echo esc_attr( $cpt_key ); ?>"></span>
								</div>

								<div class="reirie-cpt-table-wrap">
									<table class="reirie-cpt-table" data-cpt="<?php echo esc_attr( $cpt_key ); ?>">
										<thead>
											<tr>
												<?php foreach ( $cpt['columns'] as $col ) : ?>
													<th class="col-<?php echo esc_attr( $col['type'] ); ?>"><?php echo esc_html( $col['label'] ); ?></th>
												<?php endforeach; ?>
												<th class="col-actions">操作</th>
											</tr>
										</thead>
										<tbody class="reirie-cpt-tbody" data-cpt="<?php echo esc_attr( $cpt_key ); ?>">
											<tr class="reirie-cpt-loading"><td colspan="<?php echo count( $cpt['columns'] ) + 1; ?>">読み込み中...</td></tr>
										</tbody>
									</table>
								</div>

								<div class="reirie-cpt-pagination" data-cpt="<?php echo esc_attr( $cpt_key ); ?>"></div>
							</section>
						<?php endforeach; ?>
						</div><!-- /.reirie-fw-cpt-panels -->

					</div><!-- /.reirie-fw-content -->
				</div><!-- /.reirie-fw-layout -->
			</form>

			<?php /* ===== メンバー専用フォーム（HTML5 form属性で関連付け、メイン設定フォームとの入れ子問題を回避） ===== */ ?>
			<form id="reirie-members-form" method="post" action=""></form>

			<?php /* ===== 編集モーダル ===== */ ?>
			<div class="reirie-modal-overlay" id="reirie-modal-overlay" style="display:none;">
				<div class="reirie-modal" role="dialog" aria-modal="true">
					<div class="reirie-modal-header">
						<span class="reirie-modal-icon"><span class="dashicons dashicons-edit"></span></span>
						<h2 class="reirie-modal-title">編集</h2>
						<button type="button" class="reirie-modal-close" aria-label="閉じる">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>
					<div class="reirie-modal-body">
						<form id="reirie-modal-form" autocomplete="off">
							<input type="hidden" name="cpt" id="reirie-modal-cpt" value="">
							<input type="hidden" name="id"  id="reirie-modal-id"  value="0">
							<div id="reirie-modal-fields"></div>
						</form>
					</div>
					<div class="reirie-modal-footer">
						<button type="button" class="reirie-modal-delete" id="reirie-modal-delete" style="display:none;">
							<span class="dashicons dashicons-trash"></span>削除
						</button>
						<span class="reirie-modal-msg" id="reirie-modal-msg"></span>
						<button type="button" class="button reirie-modal-cancel">キャンセル</button>
						<button type="button" class="button button-primary reirie-modal-save" id="reirie-modal-save">
							<span class="dashicons dashicons-saved" style="vertical-align:middle;margin-right:2px;"></span>保存する
						</button>
					</div>
				</div>
			</div>

			<!-- ===== Quick links ===== -->
			<h2 class="reirie-fw-quick-title">
				<span class="dashicons dashicons-admin-links"></span>関連ページへのショートカット
			</h2>
			<div class="reirie-fw-quick-grid">
				<a class="reirie-fw-quick" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">
					<span class="ic"><span class="dashicons dashicons-visibility"></span></span>
					<span class="tx"><strong>ライブプレビュー</strong><span>カスタマイザーで確認</span></span>
				</a>
				<a class="reirie-fw-quick" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">
					<span class="ic"><span class="dashicons dashicons-admin-site-alt3"></span></span>
					<span class="tx"><strong>サイトを表示</strong><span>新しいタブで開く</span></span>
				</a>
				<a class="reirie-fw-quick" href="<?php echo esc_url( admin_url( 'admin.php?page=reirie-help' ) ); ?>">
					<span class="ic"><span class="dashicons dashicons-editor-help"></span></span>
					<span class="tx"><strong>クイックヘルプ</strong><span>使い方ガイド</span></span>
				</a>
				<a class="reirie-fw-quick" href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>">
					<span class="ic"><span class="dashicons dashicons-admin-home"></span></span>
					<span class="tx"><strong>トップページ設定</strong><span>表示するページの指定</span></span>
				</a>
			</div>

		</div><!-- /.reirie-fw-inner -->
	</div><!-- /.reirie-fw-wrap -->

	<script>
	window.REIRIE_ADMIN = {
		ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
		nonce:   <?php echo wp_json_encode( wp_create_nonce( 'reirie_content_nonce' ) ); ?>,
		schema:  <?php echo wp_json_encode( $content_schema ); ?>
	};
	</script>
	<script>
	(function(){
		var R = window.REIRIE_ADMIN;
		var settingsBar = document.getElementById('reirie-settings-submit-bar');

		// ===== タブ切り替え（設定タブ / CPTタブ共通） =====
		// サイドバーは常に表示するため、フォーム自体は隠さない。
		// 個々のパネル（.reirie-fw-panel）と保存バーの表示だけを切り替える。
		function showPanel(target, isCpt){
			document.querySelectorAll('.reirie-fw-tab').forEach(function(t){ t.classList.remove('is-active'); });
			var tab = document.querySelector('.reirie-fw-tab[data-target="' + target + '"]');
			if (tab) tab.classList.add('is-active');

			// 全パネル（設定 + CPT）を非表示
			document.querySelectorAll('.reirie-fw-panel').forEach(function(p){ p.classList.remove('is-active'); p.style.display = 'none'; });

			// 対象パネルだけ表示
			var panel = document.getElementById(target);
			if (panel) {
				panel.classList.add('is-active');
				panel.style.display = 'block';
			}

			// 保存バーは設定タブのときだけ表示（CPTタブのときは非表示）
			if (settingsBar) settingsBar.style.display = isCpt ? 'none' : '';

			window.scrollTo({ top: 0, behavior: 'smooth' });
		}

		document.querySelectorAll('.reirie-fw-tab').forEach(function(tab){
			tab.addEventListener('click', function(e){
				e.preventDefault();
				var target = tab.getAttribute('data-target');
				var isCpt = tab.classList.contains('reirie-fw-cpt-tab');
				showPanel(target, isCpt);
				if (isCpt) {
					var cpt = tab.getAttribute('data-cpt');
					loadList(cpt, 1, '');
				}
			});
		});

		// ===== 設定タブのメディアアップローダー（イベント委譲） =====
		// wp.media は <script> よりあとに読み込まれることがあるため、
		// 直接バインドではなく document への delegation でクリックを受ける。
		// モーダル内 ([data-modal-thumb]) は別途 bindMediaRow() で扱うのでここでは除外。
		document.addEventListener('click', function(e){
			var target = e.target;
			// クリックされた要素が .media-select もしくはその子要素か判定
			var selectBtn = target.closest ? target.closest('.media-select') : null;
			var clearBtn  = target.closest ? target.closest('.media-clear')  : null;

			if (selectBtn) {
				var row = selectBtn.closest('[data-media-field]');
				if (!row) return; // モーダル内のものは [data-modal-thumb] なのでここでは無視
				e.preventDefault();

				if (typeof wp === 'undefined' || !wp.media) {
					alert('メディアライブラリの読み込みに失敗しました。ページを再読み込みしてください。');
					return;
				}

				var isId = row.getAttribute('data-is-id') === '1';
				var mime = row.getAttribute('data-mime');
				var input = row.querySelector('.media-value');
				var preview = row.querySelector('.media-preview');
				var actions = row.querySelector('.media-actions');
				var filenameEl = row.querySelector('.media-filename');

				var frame = wp.media({
					title: mime === 'video' ? '動画を選択' : '画像を選択',
					button: { text: 'これを使う' },
					library: { type: mime === 'video' ? 'video' : 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					if (input) input.value = isId ? attachment.id : attachment.url;

					// ファイル名表示
					if (filenameEl) {
						var fname = attachment.filename || (attachment.url ? attachment.url.split('/').pop() : '');
						filenameEl.textContent = fname;
					}

					// プレビュー再描画（動画なら実際の <video>、画像なら <img>）
					if (preview) {
						if (mime === 'video' && attachment.url) {
							preview.innerHTML =
								'<video src="' + attachment.url + '" muted playsinline preload="metadata" autoplay loop></video>' +
								'<span class="media-badge">VIDEO</span>';
						} else if (attachment.url) {
							preview.innerHTML = '<img class="media-thumb" src="' + attachment.url + '" alt="">';
						}
					}

					// 表示用 URL ライン
					var urlSpan = row.querySelector('.media-url');
					if (attachment.url) {
						if (!urlSpan) {
							urlSpan = document.createElement('span');
							urlSpan.className = 'media-url';
							row.appendChild(urlSpan);
						}
						urlSpan.textContent = attachment.url;
					}

					// 「削除」ボタンが無ければ追加
					if (actions && !actions.querySelector('.media-clear')) {
						var btn = document.createElement('button');
						btn.type = 'button';
						btn.className = 'button media-clear';
						btn.textContent = '削除';
						actions.appendChild(btn);
					}
					selectBtn.textContent = '差し替え';
				});
				frame.open();
				return;
			}

			if (clearBtn) {
				var row2 = clearBtn.closest('[data-media-field]');
				if (!row2) return;
				e.preventDefault();
				var mime2 = row2.getAttribute('data-mime');
				var input2 = row2.querySelector('.media-value');
				var preview2 = row2.querySelector('.media-preview');
				var sel2 = row2.querySelector('.media-select');
				var fnameEl2 = row2.querySelector('.media-filename');
				var urlSpan2 = row2.querySelector('.media-url');
				if (input2) input2.value = '';
				if (preview2) preview2.innerHTML = '<span class="dashicons dashicons-format-' + (mime2 === 'video' ? 'video' : 'image') + '"></span>';
				if (sel2) sel2.textContent = '選択';
				if (fnameEl2) fnameEl2.innerHTML = '<span style="color:#aaa;font-weight:400;">未設定</span>';
				if (urlSpan2 && urlSpan2.parentNode) urlSpan2.parentNode.removeChild(urlSpan2);
				clearBtn.parentNode.removeChild(clearBtn);
				return;
			}
		});

		// ===== ユーティリティ =====
		function escHtml(s){
			if (s === null || s === undefined) return '';
			return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; });
		}
		function escAttr(s){ return escHtml(s); }

		// ===== 一覧読み込み =====
		var listCache = {}; // cpt -> { page, search }
		function loadList(cpt, page, search){
			var tbody = document.querySelector('.reirie-cpt-tbody[data-cpt="' + cpt + '"]');
			var countEl = document.querySelector('.reirie-cpt-count[data-cpt="' + cpt + '"]');
			var pagiEl = document.querySelector('.reirie-cpt-pagination[data-cpt="' + cpt + '"]');
			if (!tbody) return;

			var cpt_schema = R.schema[cpt];
			var colCount = cpt_schema.columns.length + 1;
			tbody.innerHTML = '<tr class="reirie-cpt-loading"><td colspan="' + colCount + '">読み込み中...</td></tr>';

			listCache[cpt] = { page: page, search: search };

			var url = R.ajaxUrl + '?action=reirie_content_list&nonce=' + encodeURIComponent(R.nonce)
				+ '&cpt=' + encodeURIComponent(cpt) + '&paged=' + page + '&s=' + encodeURIComponent(search || '');

			fetch(url, { credentials: 'same-origin' })
				.then(function(r){ return r.json(); })
				.then(function(res){
					if (!res || !res.success) {
						tbody.innerHTML = '<tr class="reirie-cpt-empty"><td colspan="' + colCount + '">読み込みに失敗しました</td></tr>';
						return;
					}
					var items = res.data.items;
					if (!items.length) {
						tbody.innerHTML = '<tr class="reirie-cpt-empty"><td colspan="' + colCount + '">' + (search ? '該当する項目がありません' : 'まだ登録がありません。右上の「追加」ボタンから作成しましょう。') + '</td></tr>';
					} else {
						var html = '';
						items.forEach(function(it){
							html += renderRow(cpt, it);
						});
						tbody.innerHTML = html;
					}
					if (countEl) countEl.textContent = '全 ' + res.data.total + ' 件';
					renderPagination(pagiEl, cpt, res.data.page, res.data.maxPages, search);
				})
				.catch(function(){
					tbody.innerHTML = '<tr class="reirie-cpt-empty"><td colspan="' + colCount + '">通信エラー</td></tr>';
				});
		}

		function renderRow(cpt, it){
			var cpt_schema = R.schema[cpt];
			var rowCls = '';
			if (it.status === 'draft' || it.status === 'pending' || it.status === 'private') rowCls = 'is-draft';
			else if (it.status === 'future') rowCls = 'is-scheduled';
			var html = '<tr data-id="' + it.id + '" class="' + rowCls + '">';
			cpt_schema.columns.forEach(function(col){
				var lbl = escAttr(col.label || '');
				if (col.type === 'thumbnail') {
					var bg = it.thumbnail ? ('style="background-image:url(\'' + escAttr(it.thumbnail) + '\');"') : '';
					html += '<td class="col-thumbnail" data-label="' + lbl + '"><span class="row-thumb" ' + bg + '>'
						+ (!it.thumbnail ? '<span class="dashicons dashicons-format-image"></span>' : '')
						+ '</span></td>';
				} else if (col.type === 'title') {
					var statusLabel = '';
					if (it.status === 'draft')   statusLabel = '下書き';
					else if (it.status === 'future')  statusLabel = '公開予定';
					else if (it.status === 'pending') statusLabel = '承認待ち';
					else if (it.status === 'private') statusLabel = '非公開';
					else if (it.status !== 'publish') statusLabel = it.status;
					var statusBadgeCls = it.status === 'future' ? 'row-status row-status--scheduled' : 'row-status';
					var statusBadge = statusLabel ? ' <small class="' + statusBadgeCls + '">' + statusLabel + '</small>' : '';
					html += '<td data-label="' + lbl + '"><span class="row-title">' + escHtml(it.title) + statusBadge + '</span></td>';
				} else if (col.type === 'date') {
					html += '<td data-label="' + lbl + '">' + escHtml(it.date) + '</td>';
				} else if (col.type === 'menu_order') {
					html += '<td class="col-menu_order" data-label="' + lbl + '">' + it.menu_order + '</td>';
				} else if (col.type === 'meta') {
					var v = (it.meta && it.meta[col.key]) ? it.meta[col.key] : '';
					html += '<td data-label="' + lbl + '">' + escHtml(v) + '</td>';
				}
			});
			var readonly = cpt_schema.readonly;
			html += '<td class="col-actions">';
			// URL コピー（公開・公開予定の投稿のみ）
			if (it.has_url && it.permalink) {
				html += '<button type="button" class="row-action row-action-copy-url" data-url="' + escAttr(it.permalink) + '" title="' + escAttr(it.permalink) + '">'
					+ '<span class="dashicons dashicons-admin-links"></span>URL</button>';
			}
			html += '<button type="button" class="row-action row-action-edit" data-id="' + it.id + '" data-cpt="' + cpt + '">'
				+ '<span class="dashicons dashicons-' + (readonly ? 'visibility' : 'edit') + '"></span>' + (readonly ? '表示' : '編集') + '</button>';
			html += '<button type="button" class="row-action row-action-delete" data-id="' + it.id + '" data-cpt="' + cpt + '">'
				+ '<span class="dashicons dashicons-trash"></span>削除</button>';
			html += '</td>';
			html += '</tr>';
			return html;
		}

		function renderPagination(el, cpt, page, max, search){
			if (!el) return;
			if (max <= 1) { el.innerHTML = ''; return; }
			var html = '';
			html += '<button type="button" data-page="' + (page - 1) + '" ' + (page <= 1 ? 'disabled' : '') + '>‹</button>';
			for (var i = 1; i <= max; i++) {
				if (max > 7 && i > 2 && i < max - 1 && Math.abs(i - page) > 1) {
					if (i === 3 && page > 4) html += '<button type="button" disabled>…</button>';
					if (i === max - 2 && page < max - 3) html += '<button type="button" disabled>…</button>';
					continue;
				}
				html += '<button type="button" data-page="' + i + '" class="' + (i === page ? 'is-current' : '') + '">' + i + '</button>';
			}
			html += '<button type="button" data-page="' + (page + 1) + '" ' + (page >= max ? 'disabled' : '') + '>›</button>';
			el.innerHTML = html;
			el.querySelectorAll('button[data-page]').forEach(function(b){
				b.addEventListener('click', function(){
					var p = parseInt(b.getAttribute('data-page'), 10);
					if (p >= 1 && p <= max) loadList(cpt, p, search);
				});
			});
		}

		// テーブルの編集/削除/URLコピー クリック委譲
		document.addEventListener('click', function(e){
			var btn = e.target.closest('.row-action');
			if (!btn) return;
			var cpt = btn.getAttribute('data-cpt');
			var id  = parseInt(btn.getAttribute('data-id'), 10);
			if (btn.classList.contains('row-action-edit')) {
				openModal(cpt, id);
			} else if (btn.classList.contains('row-action-delete')) {
				if (confirm('ゴミ箱に移動しますか？')) {
					deleteItem(cpt, id);
				}
			} else if (btn.classList.contains('row-action-copy-url')) {
				copyUrlToClipboard(btn);
			}
		});

		// URL をクリップボードへコピー（成功時にトースト表示）
		function copyUrlToClipboard(btn) {
			var url = btn.getAttribute('data-url');
			if (!url) return;
			var done = function(ok){
				showCopyToast(ok ? ('URLをコピーしました\n' + url) : 'コピーに失敗しました', ok);
				// ボタン自体にも一瞬フィードバック
				var orig = btn.innerHTML;
				btn.innerHTML = ok
					? '<span class="dashicons dashicons-yes"></span>コピー済'
					: '<span class="dashicons dashicons-no"></span>失敗';
				btn.classList.add(ok ? 'is-copied' : 'is-copy-failed');
				setTimeout(function(){
					btn.innerHTML = orig;
					btn.classList.remove('is-copied', 'is-copy-failed');
				}, 1800);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(function(){ done(true); }).catch(function(){ done(fallbackCopy(url)); });
			} else {
				done(fallbackCopy(url));
			}
		}
		function fallbackCopy(text) {
			try {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.style.position = 'fixed';
				ta.style.left = '-9999px';
				document.body.appendChild(ta);
				ta.select();
				var ok = document.execCommand('copy');
				document.body.removeChild(ta);
				return ok;
			} catch(e) { return false; }
		}
		function showCopyToast(msg, ok) {
			var existing = document.getElementById('reirie-copy-toast');
			if (existing) existing.remove();
			var t = document.createElement('div');
			t.id = 'reirie-copy-toast';
			t.textContent = msg;
			t.style.cssText = 'position:fixed;left:50%;bottom:32px;transform:translateX(-50%);'
				+ 'background:' + (ok ? 'linear-gradient(135deg,#ff8a3d,#ff5b9c)' : '#c00')
				+ ';color:#fff;padding:12px 24px;border-radius:10px;font-size:13px;font-weight:600;'
				+ 'box-shadow:0 10px 30px rgba(0,0,0,.18);z-index:99999;white-space:pre-line;'
				+ 'opacity:0;transition:opacity .2s,transform .2s;max-width:90vw;line-height:1.5;';
			document.body.appendChild(t);
			requestAnimationFrame(function(){
				t.style.opacity = '1';
				t.style.transform = 'translateX(-50%) translateY(-4px)';
			});
			setTimeout(function(){
				t.style.opacity = '0';
				setTimeout(function(){ if (t.parentNode) t.remove(); }, 250);
			}, 2400);
		}

		// 新規追加ボタン
		document.querySelectorAll('.reirie-cpt-new').forEach(function(btn){
			btn.addEventListener('click', function(){
				openModal(btn.getAttribute('data-cpt'), 0);
			});
		});

		// 検索
		document.querySelectorAll('.reirie-cpt-search').forEach(function(inp){
			var timer = null;
			inp.addEventListener('input', function(){
				var cpt = inp.getAttribute('data-cpt');
				clearTimeout(timer);
				timer = setTimeout(function(){ loadList(cpt, 1, inp.value); }, 250);
			});
		});

		// ===== モーダル =====
		var overlay = document.getElementById('reirie-modal-overlay');
		var fieldsEl = document.getElementById('reirie-modal-fields');
		var modalCpt = document.getElementById('reirie-modal-cpt');
		var modalId  = document.getElementById('reirie-modal-id');
		var modalTitle = document.querySelector('.reirie-modal-title');
		var modalBody = document.querySelector('.reirie-modal-body');
		var modalMsg = document.getElementById('reirie-modal-msg');
		var modalDelete = document.getElementById('reirie-modal-delete');
		var modalSave = document.getElementById('reirie-modal-save');

		function openModal(cpt, id){
			var sc = R.schema[cpt];
			modalCpt.value = cpt;
			modalId.value = id || 0;
			modalTitle.textContent = (id ? sc.singular + 'を編集' : sc.singular + 'を追加');
			modalMsg.textContent = '';
			modalMsg.className = 'reirie-modal-msg';
			fieldsEl.innerHTML = '<p style="text-align:center;color:#999;padding:40px 0;">読み込み中...</p>';

			if (sc.readonly) {
				modalBody.classList.add('is-readonly');
				modalSave.style.display = 'none';
			} else {
				modalBody.classList.remove('is-readonly');
				modalSave.style.display = '';
			}
			modalDelete.style.display = id ? 'inline-flex' : 'none';
			modalDelete.onclick = function(){
				if (id && confirm('ゴミ箱に移動しますか？')) {
					deleteItem(cpt, id, true);
				}
			};

			overlay.style.display = 'flex';
			document.body.style.overflow = 'hidden';

			// データ取得
			var url = R.ajaxUrl + '?action=reirie_content_get&nonce=' + encodeURIComponent(R.nonce)
				+ '&cpt=' + encodeURIComponent(cpt) + '&id=' + (id || 0);
			fetch(url, { credentials: 'same-origin' })
				.then(function(r){ return r.json(); })
				.then(function(res){
					if (!res || !res.success) {
						fieldsEl.innerHTML = '<p style="text-align:center;color:#b8001a;">読み込みに失敗しました</p>';
						return;
					}
					renderModalFields(cpt, res.data);
				});
		}

		function closeModal(){
			// TinyMCE インスタンスがあれば破棄（メモリリーク防止 + 次回オープン時の二重初期化防止）
			if (typeof destroyRichEditor === 'function') destroyRichEditor();
			overlay.style.display = 'none';
			document.body.style.overflow = '';
		}
		overlay.addEventListener('click', function(e){
			if (e.target === overlay) closeModal();
		});
		document.querySelector('.reirie-modal-close').addEventListener('click', closeModal);
		document.querySelector('.reirie-modal-cancel').addEventListener('click', closeModal);
		document.addEventListener('keydown', function(e){
			if (e.key === 'Escape' && overlay.style.display === 'flex') closeModal();
		});

		function renderModalFields(cpt, data){
			var sc = R.schema[cpt];
			var html = '';

			// タイトル
			html += fieldHtml({ type: 'text', label: 'タイトル', name: '__title' }, data.title || '');

			// アイキャッチ画像
			if (sc.thumbnail) {
				var thumbLabel = sc.thumbnail_label || 'アイキャッチ画像';
				html += '<div class="reirie-fw-field">'
					+ '<label class="field-label">' + escHtml(thumbLabel) + '</label>'
					+ '<div class="media-row" data-modal-thumb data-is-id="1" data-mime="image">'
					+   '<span class="media-preview">'
					+     (data.thumb_url ? '<span style="display:block;width:100%;height:100%;background-image:url(\'' + escAttr(data.thumb_url) + '\');background-size:cover;background-position:center;border-radius:7px;"></span>' : '<span class="dashicons dashicons-format-image"></span>')
					+   '</span>'
					+   '<span class="media-actions">'
					+     '<button type="button" class="button media-select">' + (data.thumb_id ? '差し替え' : '選択') + '</button>'
					+     (data.thumb_id ? '<button type="button" class="button media-clear">削除</button>' : '')
					+   '</span>'
					+   '<input type="hidden" class="media-value" name="thumb_id" value="' + (data.thumb_id || '') + '">'
					+ '</div>'
					+ '</div>';
			}

			// 本文エディタ（TinyMCE リッチエディタ）
			if (sc.editor) {
				// 専用ID（TinyMCE初期化に必要）。値はモーダル描画後に setContent で投入
				html += '<div class="reirie-fw-field reirie-fw-field-editor">'
					+ '<div class="reirie-editor-label-row">'
					+   '<label class="field-label">本文</label>'
					+   '<button type="button" class="button reirie-insert-image-btn" data-editor-insert-image>'
					+     '<span class="dashicons dashicons-format-image" style="vertical-align:middle;font-size:16px;width:16px;height:16px;"></span> 画像を挿入'
					+   '</button>'
					+ '</div>'
					+ '<textarea id="reirie-modal-content-editor" name="__content" rows="10" class="reirie-rich-editor">' + escHtml(data.content || '') + '</textarea>'
					+ '<p class="field-desc" style="font-size:12px;color:#888;margin:6px 0 0;">改行は自動で反映されます。URLは自動でリンクになります。ツールバーで太字・リンク挿入や「画像を挿入」ボタンでの画像追加も可能です。</p>'
					+ '</div>';
			}

			// menu_order
			if (sc.menu_order) {
				html += '<div class="reirie-fw-field"><label class="field-label">表示順序（小さい数字が先）</label>'
					+ '<input type="number" name="__menu_order" value="' + (data.menu_order || 0) + '" style="max-width:140px;"></div>';
			}

			// カスタムフィールド
			sc.fields.forEach(function(f){
				if (f.type === 'divider') {
					html += '<div class="reirie-modal-divider">' + escHtml(f.label) + '</div>';
					return;
				}
				var v = (data.fields && data.fields[f.name] !== undefined) ? data.fields[f.name] : '';
				html += fieldHtml(f, v);
			});

			// 公開ステータス
			if (!sc.readonly) {
				var futureNote = (data.status === 'future')
					? '<p class="field-desc" style="font-size:12px;color:#c87800;margin:6px 0 0;">⚠ 現在「公開予定」状態です。公開日時を過ぎた時点で自動的に公開されます。<br>未来日時を指定して「公開」を選ぶと自動的に公開予定になります。</p>'
					: '<p class="field-desc" style="font-size:12px;color:#888;margin:6px 0 0;">公開日時に未来の日時を指定すると、自動的に「公開予定」になり、ログイン中の管理者/編集者だけがフロントで確認できます。</p>';
				html += '<div class="reirie-fw-field">'
					+ '<label class="field-label">公開ステータス</label>'
					+ '<select name="__status">'
					+   '<option value="publish"' + (data.status === 'publish' || !data.id ? ' selected' : '') + '>公開</option>'
					+   '<option value="future"' + (data.status === 'future' ? ' selected' : '') + '>公開予定（予約投稿）</option>'
					+   '<option value="draft"' + (data.status === 'draft' ? ' selected' : '') + '>下書き</option>'
					+   '<option value="private"' + (data.status === 'private' ? ' selected' : '') + '>非公開</option>'
					+ '</select>'
					+ futureNote
					+ '</div>';
			}

			// 公開URL（公開・公開予定のみ）
			if (data.permalink && (data.status === 'publish' || data.status === 'future')) {
				var urlNote = data.status === 'future'
					? '<small style="color:#c87800;">公開予定中 — このURLは管理者のみアクセス可能。公開日時を過ぎると一般訪問者にも見えるようになります。</small>'
					: '<small style="color:#888;">SNS等への告知にこのURLをご利用ください。</small>';
				html += '<div class="reirie-fw-field" data-modal-url-block>'
					+ '<label class="field-label">公開URL</label>'
					+ '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">'
					+   '<input type="text" value="' + escAttr(data.permalink) + '" readonly style="flex:1;min-width:240px;font-family:monospace;font-size:13px;background:#fafafa;" onclick="this.select();">'
					+   '<button type="button" class="button modal-copy-url-btn" data-url="' + escAttr(data.permalink) + '" style="white-space:nowrap;">'
					+     '<span class="dashicons dashicons-admin-links" style="vertical-align:middle;"></span> URLをコピー'
					+   '</button>'
					+ '</div>'
					+ '<p class="field-desc" style="font-size:12px;margin:6px 0 0;">' + urlNote + '</p>'
					+ '</div>';
			}

			fieldsEl.innerHTML = html;

			// 読み取り専用CPT（受信メッセージ等）の場合はHTMLネイティブ属性で編集をブロック
			// （CSSで pointer-events:none にするとスクロールも効かなくなるため、こちらを使う）
			if (sc.readonly) {
				fieldsEl.querySelectorAll('input, textarea').forEach(function(el){
					el.setAttribute('readonly', 'readonly');
				});
				fieldsEl.querySelectorAll('select').forEach(function(el){
					el.setAttribute('disabled', 'disabled');
				});
			}

			// モーダル内のメディアアップローダーをバインド
			var thumbRow = fieldsEl.querySelector('[data-modal-thumb]');
			if (thumbRow && typeof wp !== 'undefined' && wp.media) {
				bindMediaRow(thumbRow);
			}

			// モーダル内の URL コピーボタンをバインド
			fieldsEl.querySelectorAll('.modal-copy-url-btn').forEach(function(b){
				b.addEventListener('click', function(e){
					e.preventDefault();
					copyUrlToClipboard(b);
				});
			});

			// TinyMCE リッチエディタの初期化
			if (sc.editor && !sc.readonly) {
				initRichEditor(data.content || '');
			}

			// 本文エディタの「画像を挿入」ボタンをバインド
			var insertImgBtn = fieldsEl.querySelector('[data-editor-insert-image]');
			if (insertImgBtn && typeof wp !== 'undefined' && wp.media) {
				insertImgBtn.addEventListener('click', function(e){
					e.preventDefault();
					insertImageIntoEditor();
				});
			}
		}

		/**
		 * 本文エディタ（TinyMCE）のカーソル位置に画像を挿入する
		 * サムネイル用の bindMediaRow とは別に、wp.media フレームを都度生成する
		 * （本文中には複数枚挿入できるようにするため、選択のたびに新規フレームを開く）
		 */
		function insertImageIntoEditor(){
			var frame = wp.media({
				title: '本文に挿入する画像を選択',
				button: { text: 'この画像を挿入' },
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				var url = att.url;
				var alt = att.alt || att.title || '';
				var imgHtml = '<img src="' + url + '" alt="' + alt.replace(/"/g, '&quot;') + '" style="max-width:100%;height:auto;" />';

				var ed = (typeof tinymce !== 'undefined') ? tinymce.get('reirie-modal-content-editor') : null;
				if (ed && !ed.isHidden()) {
					// TinyMCEがアクティブな場合はカーソル位置に挿入
					ed.insertContent(imgHtml);
				} else {
					// プレーンtextareaにフォールバックしている場合はカーソル位置にテキスト挿入
					var ta = document.getElementById('reirie-modal-content-editor');
					if (ta) {
						var start = ta.selectionStart || ta.value.length;
						var end = ta.selectionEnd || ta.value.length;
						ta.value = ta.value.slice(0, start) + imgHtml + ta.value.slice(end);
						ta.focus();
						var newPos = start + imgHtml.length;
						ta.setSelectionRange(newPos, newPos);
					}
				}
			});
			frame.open();
		}

		/**
		 * TinyMCE エディタの初期化／破棄
		 */
		function initRichEditor(initialContent){
			// 既存インスタンスがあれば破棄（モーダル再オープン対応）
			destroyRichEditor();

			if (typeof tinymce === 'undefined' || typeof wp === 'undefined' || !wp.editor) {
				// TinyMCE が読み込めていない場合はプレーンtextareaのまま動作
				return;
			}

			// wp.editor.initialize で WordPress 標準のエディタ機能込みで初期化
			try {
				wp.editor.initialize('reirie-modal-content-editor', {
					tinymce: {
						// wpautop: false にすることで TinyMCE が直接 <p>/<br> を出力。
						// （true だと WordPress の wpautop 変換層が間に入り、空段落が潰れることがある）
						wpautop: false,
						plugins: 'lists, link, paste, wplink, wordpress, wpautoresize',
						toolbar1: 'formatselect bold italic underline | bullist numlist | link unlink | undo redo | removeformat',
						menubar: false,
						statusbar: false,
						height: 280,
						// 空段落・空 br を維持（連続改行を残す）
						forced_root_block: 'p',
						remove_trailing_brs: false,
						keep_styles: true,
						entity_encoding: 'raw',
						verify_html: false,
						// <br> や <p> を含む空タグを自動削除しないようにする
						valid_elements: '*[*]',
						content_style: 'body{font-family:-apple-system,"Hiragino Kaku Gothic ProN",sans-serif;font-size:14px;line-height:1.8;} p{margin:0 0 1em;} p:empty:after{content:"";} p:empty{min-height:1em;}',
						setup: function(editor){
							editor.on('init', function(){
								if (initialContent) {
									editor.setContent(initialContent, { format: 'raw' });
								}
							});
							// 保存内容取得時にも raw フォーマットで空段落を維持
							editor.on('BeforeGetContent', function(e){
								if (!e.format) e.format = 'raw';
							});
						}
					},
					quicktags: true,
					mediaButtons: false
				});
			} catch (e) {
				console.warn('TinyMCE init failed, fallback to textarea', e);
			}
		}

		function destroyRichEditor(){
			if (typeof tinymce === 'undefined') return;
			var ed = tinymce.get('reirie-modal-content-editor');
			if (ed) {
				try {
					if (typeof wp !== 'undefined' && wp.editor && wp.editor.remove) {
						wp.editor.remove('reirie-modal-content-editor');
					} else {
						ed.remove();
					}
				} catch(e) {}
			}
		}

		function fieldHtml(f, v){
			var name = f.name;
			var label = f.label;
			var desc = f.desc ? '<p class="field-desc" style="font-size:12px;color:#888;margin:6px 0 0;">' + escHtml(f.desc) + '</p>' : '';

			if (f.type === 'textarea') {
				return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
					+ '<textarea name="' + escAttr(name) + '" rows="' + (f.rows || 3) + '">' + escHtml(v) + '</textarea>'
					+ desc + '</div>';
			}
			if (f.type === 'select') {
				var opts = '';
				Object.keys(f.choices).forEach(function(k){
					opts += '<option value="' + escAttr(k) + '"' + (String(v) === String(k) ? ' selected' : '') + '>' + escHtml(f.choices[k]) + '</option>';
				});
				return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
					+ '<select name="' + escAttr(name) + '">' + opts + '</select>'
					+ desc + '</div>';
			}
			if (f.type === 'checkbox') {
				return '<div class="reirie-fw-field checkbox">'
					+ '<label class="checkbox-label" style="display:inline-flex;align-items:center;gap:8px;">'
					+ '<input type="checkbox" name="' + escAttr(name) + '" value="1"' + (v ? ' checked' : '') + '> ' + escHtml(label)
					+ '</label>' + desc + '</div>';
			}
			if (f.type === 'date') {
				return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
					+ '<input type="date" name="' + escAttr(name) + '" value="' + escAttr(v) + '" style="max-width:220px;">'
					+ desc + '</div>';
			}
			if (f.type === 'datetime') {
				// v は "Y-m-d\TH:i" 形式（PHP側で正規化済み）
				// MySQL の "Y-m-d H:i:s" が来た場合のフォールバック
				var dtVal = v ? String(v) : '';
				if (dtVal) {
					var m = dtVal.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
					if (m) {
						dtVal = m[1] + '-' + m[2] + '-' + m[3] + 'T' + m[4] + ':' + m[5];
					}
				}
				return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
					+ '<input type="datetime-local" name="' + escAttr(name) + '" value="' + escAttr(dtVal) + '" step="60" style="max-width:280px;">'
					+ desc + '</div>';
			}
			if (f.type === 'url') {
				return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
					+ '<input type="url" name="' + escAttr(name) + '" value="' + escAttr(v) + '" placeholder="' + escAttr(f.placeholder || '') + '">'
					+ desc + '</div>';
			}
			// text fallback
			return '<div class="reirie-fw-field"><label class="field-label">' + escHtml(label) + '</label>'
				+ '<input type="text" name="' + escAttr(name) + '" value="' + escAttr(v) + '" placeholder="' + escAttr(f.placeholder || '') + '">'
				+ desc + '</div>';
		}

		function bindMediaRow(row){
			var input = row.querySelector('.media-value');
			var preview = row.querySelector('.media-preview');
			var selectBtn = row.querySelector('.media-select');
			var clearBtn = row.querySelector('.media-clear');

			selectBtn.addEventListener('click', function(e){
				e.preventDefault();
				var frame = wp.media({
					title: '画像を選択',
					button: { text: 'これを使う' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					input.value = att.id;
					preview.innerHTML = '<span style="display:block;width:100%;height:100%;background-image:url(\'' + att.url + '\');background-size:cover;background-position:center;border-radius:7px;"></span>';
					if (!clearBtn) {
						var b = document.createElement('button');
						b.type = 'button'; b.className = 'button media-clear'; b.textContent = '削除';
						selectBtn.parentNode.appendChild(b);
						clearBtn = b;
						bindClear();
					}
					selectBtn.textContent = '差し替え';
				});
				frame.open();
			});
			function bindClear(){
				if (!clearBtn) return;
				clearBtn.addEventListener('click', function(e){
					e.preventDefault();
					input.value = '';
					preview.innerHTML = '<span class="dashicons dashicons-format-image"></span>';
					clearBtn.parentNode.removeChild(clearBtn);
					clearBtn = null;
					selectBtn.textContent = '選択';
				});
			}
			bindClear();
		}

		// 保存
		modalSave.addEventListener('click', function(){
			var cpt = modalCpt.value;
			var id  = parseInt(modalId.value, 10) || 0;
			modalMsg.textContent = '保存中...';
			modalMsg.className = 'reirie-modal-msg';
			modalSave.disabled = true;

			var fd = new FormData();
			fd.append('action', 'reirie_content_save');
			fd.append('nonce', R.nonce);
			fd.append('cpt', cpt);
			fd.append('id', id);

			var form = document.getElementById('reirie-modal-form');
			var titleEl = form.querySelector('[name="__title"]');
			var contentEl = form.querySelector('[name="__content"]');
			var statusEl = form.querySelector('[name="__status"]');
			var orderEl = form.querySelector('[name="__menu_order"]');
			var thumbEl = form.querySelector('[name="thumb_id"]');

			// TinyMCE が初期化されていれば、エディタから最新のHTMLを取得
			// （Visual モードのまま「保存する」を押した時もちゃんと反映される）
			// format: 'raw' で空段落を維持
			var contentValue = contentEl ? contentEl.value : '';
			if (typeof tinymce !== 'undefined') {
				var ed = tinymce.get('reirie-modal-content-editor');
				if (ed && !ed.isHidden()) {
					contentValue = ed.getContent({ format: 'raw' });
				}
			}

			fd.append('title', titleEl ? titleEl.value : '');
			fd.append('content', contentValue);
			fd.append('status', statusEl ? statusEl.value : 'publish');
			fd.append('menu_order', orderEl ? orderEl.value : 0);
			fd.append('thumb_id', thumbEl ? thumbEl.value : '');

			// カスタムフィールド
			var sc = R.schema[cpt];
			var hasPastDateWithFutureStatus = false;
			sc.fields.forEach(function(f){
				if (f.type === 'divider') return;
				var el = form.querySelector('[name="' + f.name + '"]');
				if (!el) return;
				if (f.type === 'checkbox') {
					fd.append('fields[' + f.name + ']', el.checked ? '1' : '');
				} else {
					fd.append('fields[' + f.name + ']', el.value || '');
					// 「公開予定（future）」を選んでいるのに、datetime 系フィールドの値が
					// 既に過去になっている場合は事前に検知しておく（サーバーに送る前に
					// ユーザーへ警告するため）。WordPress は過去日時での「予約」を許可せず
					// 自動的に「公開」に補正するため、ここで気づかせないと
					// 「予約にしたのに反映されない」ように見えてしまう。
					if ((f.type === 'datetime' || f.type === 'date') && statusEl && statusEl.value === 'future' && el.value) {
						var pickedTs = NaN;
						if (f.type === 'datetime') {
							pickedTs = new Date(el.value).getTime();
						} else {
							pickedTs = new Date(el.value + 'T00:00:00').getTime();
						}
						if (!isNaN(pickedTs) && pickedTs <= Date.now()) {
							hasPastDateWithFutureStatus = true;
						}
					}
				}
			});

			if (hasPastDateWithFutureStatus) {
				modalSave.disabled = false;
				modalMsg.textContent = '⚠ 「公開予定（予約投稿）」にするには、公開日時を未来の日時にしてください（現在、過去の日時が指定されています）';
				modalMsg.className = 'reirie-modal-msg is-error';
				return;
			}

			fetch(R.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
				.then(function(r){ return r.json(); })
				.then(function(res){
					modalSave.disabled = false;
					if (!res || !res.success) {
						modalMsg.textContent = (res && res.data && res.data.message) ? res.data.message : '保存に失敗しました';
						modalMsg.className = 'reirie-modal-msg is-error';
						return;
					}

					// 「公開予定」を選んだのに公開日時が過去だったため、サーバー側で
					// 自動的に「公開」へ補正された場合は、はっきり警告を出してモーダルは
					// 閉じずに留める（ユーザーが公開日時を修正できるように）。
					// これを黙って閉じてしまうと「予約にしたのに反映されない」ように
					// 見えてしまうため（実際に報告されたバグの原因）。
					if (res.data && res.data.status_downgraded) {
						modalMsg.textContent = res.data.message || '公開日時が過去のため「公開」として保存されました';
						modalMsg.className = 'reirie-modal-msg is-error';
						// ステータスのセレクトも実際の保存結果（publish）に同期しておく
						if (statusEl) statusEl.value = res.data.status || 'publish';
						// 一覧は最新状態に更新しておく（モーダルは開いたまま）
						var cache1 = listCache[cpt] || { page: 1, search: '' };
						loadList(cpt, cache1.page, cache1.search);
						return;
					}

					modalMsg.textContent = res.data.message || '保存しました';
					modalMsg.className = 'reirie-modal-msg is-success';
					setTimeout(function(){
						closeModal();
						var cache = listCache[cpt] || { page: 1, search: '' };
						loadList(cpt, cache.page, cache.search);
					}, 600);
				})
				.catch(function(){
					modalSave.disabled = false;
					modalMsg.textContent = '通信エラー';
					modalMsg.className = 'reirie-modal-msg is-error';
				});
		});

		function deleteItem(cpt, id, fromModal){
			var fd = new FormData();
			fd.append('action', 'reirie_content_delete');
			fd.append('nonce', R.nonce);
			fd.append('id', id);

			fetch(R.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
				.then(function(r){ return r.json(); })
				.then(function(res){
					if (!res || !res.success) {
						alert(res && res.data && res.data.message ? res.data.message : '削除に失敗しました');
						return;
					}
					if (fromModal) closeModal();
					var cache = listCache[cpt] || { page: 1, search: '' };
					loadList(cpt, cache.page, cache.search);
				});
		}
	})();
	</script>
	<?php
}

/* ============================================================
   ヘルプページ
   ============================================================ */
function reirie_help_page() {
	wp_enqueue_style( 'dashicons' );
	?>
	<div class="reirie-fw-wrap">
		<style>
			#wpcontent { padding-left: 0 !important; }
			.reirie-fw-wrap { margin: 0; padding: 0; background: #fafafa; min-height: 100vh; }
			.reirie-fw-wrap * { box-sizing: border-box; }
			.reirie-help-inner { padding: 24px 32px 60px; max-width: 1100px; }
			.reirie-fw-header { display: flex; align-items: center; gap: 14px; margin: 0 0 6px; }
			.reirie-fw-header .brand-mark {
				width: 40px; height: 40px; border-radius: 12px;
				background: linear-gradient(135deg, #ff7eb6 0%, #b07aff 100%);
				display: inline-flex; align-items: center; justify-content: center;
				box-shadow: 0 6px 16px rgba(255,126,182,0.35);
			}
			.reirie-fw-header .brand-mark .dashicons { color: #fff; font-size: 22px; width: 22px; height: 22px; }
			.reirie-fw-header h1 { font-size: 22px; font-weight: 600; color: #1d1d1f; margin: 0; padding: 0; letter-spacing: 0.02em; }
			.reirie-fw-lead { font-size: 13px; color: #6b6b6b; margin: 0 0 24px 54px; line-height: 1.6; }
			.reirie-help h2 {
				font-size: 11px; font-weight: 700; color: #888;
				letter-spacing: 0.18em; text-transform: uppercase;
				margin: 30px 0 12px; padding-bottom: 8px;
				border-bottom: 1px solid #ececec;
				display: flex; align-items: center; gap: 8px;
			}
			.reirie-help h2 .dashicons { font-size: 14px; width: 14px; height: 14px; color: #c43a73; }
			.reirie-help .qa {
				background: #fff; border: 1px solid #ececec;
				border-left: 3px solid #ff7eb6; border-radius: 8px;
				padding: 14px 18px; margin: 10px 0; font-size: 13px;
			}
			.reirie-help .qa strong {
				color: #c43a73; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;
			}
			.reirie-help .qa strong .dashicons { font-size: 14px; width: 14px; height: 14px; }
			.reirie-help table.reirie-help-table {
				background: #fff; border: 1px solid #ececec; border-collapse: collapse;
				border-radius: 10px; overflow: hidden; width: 100%;
			}
			.reirie-help table.reirie-help-table th {
				background: #fafafa; padding: 12px 16px; text-align: left;
				font-size: 11px; font-weight: 600; color: #888;
				letter-spacing: 0.1em; text-transform: uppercase;
				border-bottom: 1px solid #ececec;
			}
			.reirie-help table.reirie-help-table td {
				padding: 12px 16px; border-bottom: 1px solid #f4f4f6; font-size: 13px; color: #444;
			}
			.reirie-help table.reirie-help-table tr:last-child td { border-bottom: none; }
			.reirie-help table.reirie-help-table td:first-child { font-weight: 500; color: #1d1d1f; }

			@media (max-width: 680px) {
				.reirie-help-inner { padding: 14px; }
				.reirie-fw-lead { margin-left: 0; }
				.reirie-help table.reirie-help-table,
				.reirie-help table.reirie-help-table thead,
				.reirie-help table.reirie-help-table tbody,
				.reirie-help table.reirie-help-table tr,
				.reirie-help table.reirie-help-table td { display: block; width: 100%; }
				.reirie-help table.reirie-help-table thead { display: none; }
				.reirie-help table.reirie-help-table tr {
					border: 1px solid #ececec; border-radius: 8px; margin: 0 0 10px; overflow: hidden;
				}
				.reirie-help table.reirie-help-table td { border-bottom: 1px dashed #f1f1f3; }
				.reirie-help table.reirie-help-table tr:last-child td:last-child,
				.reirie-help table.reirie-help-table td:last-child { border-bottom: none; }
				.reirie-help table.reirie-help-table td:first-child::before {
					content: '編集したい場所：'; display: block; font-size: 10.5px; color: #999; margin-bottom: 2px;
				}
				.reirie-help table.reirie-help-table td:last-child::before {
					content: '編集メニュー：'; display: block; font-size: 10.5px; color: #999; margin-bottom: 2px;
				}
			}
		</style>

		<div class="reirie-help-inner reirie-help">
			<div class="reirie-fw-header">
				<span class="brand-mark"><span class="dashicons dashicons-editor-help"></span></span>
				<h1>クイックヘルプ</h1>
			</div>
			<p class="reirie-fw-lead">REIRIEテーマの使い方・FAQをまとめています。</p>

			<h2><span class="dashicons dashicons-star-filled"></span>はじめての方へ</h2>
			<p style="font-size:13px;color:#555;line-height:1.7;">
				「<strong>REIRIE設定 → サイト設定</strong>」の1画面で、サイト全体のデザインや表示内容をすべて編集できます。<br>
				左側のメニューで項目を切り替え、最下部の「すべての設定を保存する」を押すと一括で反映されます。
			</p>

			<h2><span class="dashicons dashicons-location"></span>編集場所マップ</h2>
			<table class="reirie-help-table">
				<thead><tr><th>編集したい場所</th><th>編集メニュー</th></tr></thead>
				<tbody>
					<tr><td>トップの背景動画・タイトル・CTA</td><td>REIRIE設定 → サイト設定 → ヒーロービジョン</td></tr>
					<tr><td>SNSアイコンのリンク先</td><td>REIRIE設定 → サイト設定 → SNSリンク</td></tr>
					<tr><td>ロゴ・サイトタイトル・ファビコン</td><td>REIRIE設定 → サイト設定 → ロゴ・サイト基本情報</td></tr>
					<tr><td>お問い合わせカード文言</td><td>REIRIE設定 → サイト設定 → お問い合わせカード</td></tr>
					<tr><td>フッターのコピーライト</td><td>REIRIE設定 → サイト設定 → フッター</td></tr>
					<tr><td>News（お知らせ）</td><td>左メニュー → News</td></tr>
					<tr><td>ライブ・イベント</td><td>左メニュー → Schedule</td></tr>
					<tr><td>シングル・アルバム</td><td>左メニュー → Discography</td></tr>
					<tr><td>MV・動画</td><td>左メニュー → Movie</td></tr>
					<tr><td>メンバープロフィール</td><td>左メニュー → Member</td></tr>
					<tr><td>グッズ</td><td>左メニュー → Goods</td></tr>
					<tr><td>受信したお問い合わせ</td><td>左メニュー → お問い合わせ</td></tr>
				</tbody>
			</table>

			<h2><span class="dashicons dashicons-format-chat"></span>よくある質問</h2>

			<div class="qa">
				<strong><span class="dashicons dashicons-format-video"></span>ヒーローの動画を変えたい</strong>
				REIRIE設定 → サイト設定 → ヒーロービジョン → ヒーロー背景動画 から差し替え。MP4・10〜15秒・5MB以下推奨。
			</div>
			<div class="qa">
				<strong><span class="dashicons dashicons-groups"></span>メンバーの並び順を変えたい</strong>
				左メニュー Member → 編集画面右の「ページ属性」→「順序」の数字を変更（小さい数字が左）。
			</div>
			<div class="qa">
				<strong><span class="dashicons dashicons-album"></span>シングルが新着扱いされない</strong>
				Discographyの編集画面下部「Discography 詳細」で「NEWバッジを表示」にチェック。
			</div>
			<div class="qa">
				<strong><span class="dashicons dashicons-admin-links"></span>パーマリンクが404になる</strong>
				設定 → パーマリンク を開いて、何もせず「変更を保存」を押してください。
			</div>
			<div class="qa">
				<strong><span class="dashicons dashicons-email-alt"></span>お問い合わせフォームを表示したい</strong>
				固定ページを新規追加 → ページ属性のテンプレートで「REIRIE お問い合わせフォーム」を選択 → スラッグを <code>contact</code> に。
			</div>
			<div class="qa">
				<strong><span class="dashicons dashicons-warning"></span>通知メールが届かない</strong>
				サーバーのメール送信設定（SMTP）が必要な場合があります。必要に応じて WP Mail SMTP プラグイン等の導入をご検討ください。
			</div>
		</div>
	</div>
	<?php
}

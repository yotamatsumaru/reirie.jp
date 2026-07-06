<?php
/**
 * REIRIE - お問い合わせフォーム（オリジナル実装、プラグイン不要）
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1. Contact 投稿タイプ（受信履歴保存用、非公開）
   ============================================================ */
function reirie_register_contact_post_type() {
	register_post_type( 'contact_msg', array(
		'labels' => array(
			'name'          => 'お問い合わせ',
			'singular_name' => 'お問い合わせ',
			'all_items'     => '受信一覧',
			'edit_item'     => 'お問い合わせを表示',
			'menu_name'     => 'お問い合わせ',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => false,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 25,
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow', // 管理画面から新規追加させない
		),
		'map_meta_cap'        => true,
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'rewrite'             => false,
	) );
}
add_action( 'init', 'reirie_register_contact_post_type' );

/* ============================================================
   2. 管理画面に受信内容を表示
   ============================================================ */
function reirie_contact_admin_columns( $cols ) {
	return array(
		'cb'             => $cols['cb'],
		'title'          => '件名',
		'contact_type'   => '種別',
		'contact_name'   => '送信者',
		'contact_email'  => 'メール',
		'date'           => '受信日時',
	);
}
add_filter( 'manage_contact_msg_posts_columns', 'reirie_contact_admin_columns' );

function reirie_contact_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'contact_type':
			$type = get_post_meta( $post_id, '_contact_type', true );
			$labels = reirie_contact_type_labels();
			echo esc_html( isset( $labels[ $type ] ) ? $labels[ $type ] : $type );
			break;
		case 'contact_name':
			echo esc_html( get_post_meta( $post_id, '_contact_name', true ) );
			break;
		case 'contact_email':
			$email = get_post_meta( $post_id, '_contact_email', true );
			echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '';
			break;
	}
}
add_action( 'manage_contact_msg_posts_custom_column', 'reirie_contact_admin_column_content', 10, 2 );

/**
 * 受信内容詳細を表示するメタボックス
 */
function reirie_contact_detail_metabox() {
	add_meta_box( 'reirie_contact_detail', 'お問い合わせ詳細', 'reirie_contact_detail_render', 'contact_msg', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'reirie_contact_detail_metabox' );

function reirie_contact_detail_render( $post ) {
	$type    = get_post_meta( $post->ID, '_contact_type', true );
	$name    = get_post_meta( $post->ID, '_contact_name', true );
	$email   = get_post_meta( $post->ID, '_contact_email', true );
	$tel     = get_post_meta( $post->ID, '_contact_tel', true );
	$company = get_post_meta( $post->ID, '_contact_company', true );
	$message = get_post_meta( $post->ID, '_contact_message', true );
	$ip      = get_post_meta( $post->ID, '_contact_ip', true );
	$ua      = get_post_meta( $post->ID, '_contact_ua', true );
	$labels  = reirie_contact_type_labels();
	?>
	<style>
		.reirie-contact-table { width:100%; border-collapse:collapse; }
		.reirie-contact-table th, .reirie-contact-table td { padding:12px; border-bottom:1px solid #eee; text-align:left; vertical-align:top; }
		.reirie-contact-table th { width:160px; background:#fff5f8; color:#e94c8a; }
	</style>
	<table class="reirie-contact-table">
		<tr><th>種別</th><td><?php echo esc_html( isset( $labels[ $type ] ) ? $labels[ $type ] : $type ); ?></td></tr>
		<tr><th>お名前</th><td><?php echo esc_html( $name ); ?></td></tr>
		<tr><th>メール</th><td><?php echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '-'; ?></td></tr>
		<?php if ( $tel ) : ?><tr><th>電話番号</th><td><?php echo esc_html( $tel ); ?></td></tr><?php endif; ?>
		<?php if ( $company ) : ?><tr><th>会社名・媒体名</th><td><?php echo esc_html( $company ); ?></td></tr><?php endif; ?>
		<tr><th>メッセージ</th><td><?php echo nl2br( esc_html( $message ) ); ?></td></tr>
		<tr><th>IPアドレス</th><td><?php echo esc_html( $ip ); ?></td></tr>
		<tr><th>UA</th><td style="font-size:11px;color:#888;"><?php echo esc_html( $ua ); ?></td></tr>
	</table>
	<?php
}

/* ============================================================
   3. 種別ラベル定義
   ============================================================ */
function reirie_contact_type_labels() {
	/*
	 * 注意: ファンレターは別ページ（/fanletter/）に分離したため、
	 *       お問い合わせフォームの種別ラジオからは外しています。
	 *       fanmail を送信されても reirie_contact_handle_submit() で
	 *       バリデーションエラーになります。
	 */
	return array(
		'press'    => 'メディア取材',
		'casting'  => '出演依頼',
		'other'    => 'その他お問い合わせ',
	);
}

/* ============================================================
   4. AJAX送信エンドポイント
   ============================================================ */
function reirie_contact_handle_submit() {
	// ノンス検証
	if ( ! isset( $_POST['reirie_contact_nonce'] ) || ! wp_verify_nonce( $_POST['reirie_contact_nonce'], 'reirie_contact_submit' ) ) {
		wp_send_json_error( array( 'message' => 'セキュリティ検証に失敗しました。ページを再読み込みしてください。' ), 403 );
	}

	// ハニーポット（ボット対策）— 隠しフィールドに値が入っていたらBOT扱い
	if ( ! empty( $_POST['website'] ) ) {
		// あえて成功扱いにしてBOTを錯覚させる
		wp_send_json_success( array( 'message' => 'お問い合わせありがとうございました。' ) );
	}

	// 送信時間チェック（3秒未満はBOT疑い）
	$ts_field = isset( $_POST['form_ts'] ) ? intval( $_POST['form_ts'] ) : 0;
	if ( $ts_field && ( time() - $ts_field ) < 3 ) {
		wp_send_json_error( array( 'message' => '送信が早すぎます。もう一度お試しください。' ), 400 );
	}

	// 入力取得
	$type    = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : '';
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$tel     = isset( $_POST['tel'] ) ? sanitize_text_field( wp_unslash( $_POST['tel'] ) ) : '';
	$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$privacy = ! empty( $_POST['privacy'] );

	// バリデーション
	$errors = array();
	$labels = reirie_contact_type_labels();
	if ( ! isset( $labels[ $type ] ) ) {
		$errors['type'] = 'お問い合わせ種別を選択してください。';
	}
	if ( $name === '' ) {
		$errors['name'] = 'お名前を入力してください。';
	} elseif ( mb_strlen( $name ) > 100 ) {
		$errors['name'] = 'お名前は100文字以内で入力してください。';
	}
	if ( $email === '' || ! is_email( $email ) ) {
		$errors['email'] = '有効なメールアドレスを入力してください。';
	}
	if ( $message === '' ) {
		$errors['message'] = 'メッセージ本文を入力してください。';
	} elseif ( mb_strlen( $message ) > 3000 ) {
		$errors['message'] = 'メッセージは3000文字以内で入力してください。';
	}
	// 取材・出演依頼のときは会社名必須
	if ( in_array( $type, array( 'press', 'casting' ), true ) && $company === '' ) {
		$errors['company'] = '会社名・媒体名を入力してください。';
	}
	if ( ! $privacy ) {
		$errors['privacy'] = 'プライバシーポリシーへの同意が必要です。';
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( array(
			'message' => '入力内容にエラーがあります。',
			'errors'  => $errors,
		), 400 );
	}

	// 件名（自動生成）
	$type_label = $labels[ $type ];
	$auto_subject = $subject ? $subject : sprintf( '[%s] %s 様より', $type_label, $name );

	// IPアドレス・UA取得
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';

	// 投稿として保存
	$post_id = wp_insert_post( array(
		'post_type'   => 'contact_msg',
		'post_status' => 'publish',
		'post_title'  => $auto_subject,
		'post_content'=> $message,
	), true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => '送信処理でエラーが発生しました。時間をおいて再度お試しください。' ), 500 );
	}

	update_post_meta( $post_id, '_contact_type',    $type );
	update_post_meta( $post_id, '_contact_name',    $name );
	update_post_meta( $post_id, '_contact_email',   $email );
	update_post_meta( $post_id, '_contact_tel',     $tel );
	update_post_meta( $post_id, '_contact_company', $company );
	update_post_meta( $post_id, '_contact_message', $message );
	update_post_meta( $post_id, '_contact_ip',      $ip );
	update_post_meta( $post_id, '_contact_ua',      $ua );

	// メール送信
	reirie_contact_send_emails( array(
		'type'    => $type,
		'type_label' => $type_label,
		'name'    => $name,
		'email'   => $email,
		'tel'     => $tel,
		'company' => $company,
		'subject' => $auto_subject,
		'message' => $message,
	) );

	wp_send_json_success( array(
		'message' => 'お問い合わせを受け付けました。ご入力いただいたメールアドレス宛に確認メールをお送りしました。',
	) );
}
add_action( 'wp_ajax_reirie_contact_submit',        'reirie_contact_handle_submit' );
add_action( 'wp_ajax_nopriv_reirie_contact_submit', 'reirie_contact_handle_submit' );

/* ============================================================
   5. メール送信（管理者通知 + 自動返信）
   ============================================================ */

/**
 * 受信先メールアドレス（複数対応）を配列で取得
 *
 * 優先順位:
 *   1. reirie_contact_recipients（新・複数アドレス対応、カンマ・改行区切り）
 *   2. reirie_contact_admin_email（旧・単一アドレス、後方互換）
 *   3. WP の管理者メール
 */
function reirie_contact_get_recipients() {
	$recipients = array();

	// 新フィールド：複数アドレス（カンマ・改行・セミコロン区切り）
	$raw = get_theme_mod( 'reirie_contact_recipients', '' );
	if ( $raw ) {
		$parts = preg_split( '/[\s,;]+/u', $raw );
		foreach ( $parts as $p ) {
			$p = trim( $p );
			if ( $p && is_email( $p ) ) {
				$recipients[] = sanitize_email( $p );
			}
		}
	}

	// 旧フィールド（後方互換）：単一アドレス
	if ( empty( $recipients ) ) {
		$legacy = get_theme_mod( 'reirie_contact_admin_email', '' );
		if ( $legacy && is_email( $legacy ) ) {
			$recipients[] = sanitize_email( $legacy );
		}
	}

	// 最終フォールバック：WPの管理者メール
	if ( empty( $recipients ) ) {
		$recipients[] = get_option( 'admin_email' );
	}

	// 重複排除
	return array_values( array_unique( $recipients ) );
}

/**
 * 通知メールの差出人（From）情報を取得
 */
function reirie_contact_get_from() {
	$from_name  = get_theme_mod( 'reirie_contact_from_name', get_bloginfo( 'name' ) );
	$from_email = get_theme_mod( 'reirie_contact_from_email', '' );

	if ( ! $from_email || ! is_email( $from_email ) ) {
		// デフォルトは wordpress@<ドメイン>
		$host = parse_url( home_url(), PHP_URL_HOST );
		if ( $host ) {
			$host = preg_replace( '/^www\./i', '', $host );
			$from_email = 'wordpress@' . $host;
		}
	}

	return array(
		'name'  => $from_name,
		'email' => $from_email,
	);
}

function reirie_contact_send_emails( $data ) {
	$recipients = reirie_contact_get_recipients();
	$site_name  = get_bloginfo( 'name' );
	$from       = reirie_contact_get_from();

	// 共通ヘッダー
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
	);
	if ( $from['email'] ) {
		$headers[] = 'From: ' . sprintf( '%s <%s>', $from['name'], $from['email'] );
	}

	// CC / BCC（カンマ/改行/セミコロン区切り）
	$cc_raw  = get_theme_mod( 'reirie_contact_cc', '' );
	$bcc_raw = get_theme_mod( 'reirie_contact_bcc', '' );
	foreach ( array( 'Cc' => $cc_raw, 'Bcc' => $bcc_raw ) as $label => $raw ) {
		if ( ! $raw ) continue;
		$parts = preg_split( '/[\s,;]+/u', $raw );
		$valid = array();
		foreach ( $parts as $p ) {
			$p = trim( $p );
			if ( $p && is_email( $p ) ) $valid[] = $p;
		}
		if ( $valid ) {
			$headers[] = $label . ': ' . implode( ', ', $valid );
		}
	}

	/* ---------- 管理者宛 ---------- */
	$admin_subject = sprintf( '[%s] %s', $site_name, $data['subject'] );
	$admin_body    = "REIRIE 公式サイトよりお問い合わせがありました。\n\n";
	$admin_body   .= "■ 種別: " . $data['type_label'] . "\n";
	$admin_body   .= "■ お名前: " . $data['name'] . "\n";
	$admin_body   .= "■ メール: " . $data['email'] . "\n";
	if ( $data['tel'] )     $admin_body .= "■ 電話: " . $data['tel'] . "\n";
	if ( $data['company'] ) $admin_body .= "■ 会社・媒体: " . $data['company'] . "\n";
	$admin_body   .= "\n----- メッセージ -----\n";
	$admin_body   .= $data['message'] . "\n";
	$admin_body   .= "----------------------\n\n";
	$admin_body   .= "管理画面で履歴を確認: " . admin_url( 'edit.php?post_type=contact_msg' );

	$admin_headers = array_merge( $headers, array(
		'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>',
	) );

	// wp_mail の To は配列でもカンマ区切り文字列でもOK（複数宛先一括送信）
	wp_mail( $recipients, $admin_subject, $admin_body, $admin_headers );

	/* ---------- 送信者宛（自動返信） ---------- */
	$auto_subject = sprintf( '[%s] お問い合わせを受け付けました', $site_name );
	$auto_body    = $data['name'] . " 様\n\n";
	$auto_body   .= "REIRIE 公式サイトへのお問い合わせ、誠にありがとうございます。\n";
	$auto_body   .= "下記の内容で受け付けいたしました。\n";
	$auto_body   .= "内容を確認の上、担当者よりご連絡いたします。\n\n";
	$auto_body   .= "■ 種別: " . $data['type_label'] . "\n";
	$auto_body   .= "■ お名前: " . $data['name'] . "\n";
	$auto_body   .= "■ メール: " . $data['email'] . "\n";
	if ( $data['tel'] )     $auto_body .= "■ 電話: " . $data['tel'] . "\n";
	if ( $data['company'] ) $auto_body .= "■ 会社・媒体: " . $data['company'] . "\n";
	$auto_body   .= "\n----- メッセージ -----\n";
	$auto_body   .= $data['message'] . "\n";
	$auto_body   .= "----------------------\n\n";
	$auto_body   .= "※ このメールは自動配信です。\n";
	$auto_body   .= "※ お返事までお時間をいただく場合がございます。何卒ご了承ください。\n\n";
	$auto_body   .= $site_name . "\n";
	$auto_body   .= home_url( '/' ) . "\n";

	// 自動返信には Reply-To に管理者代表アドレス（1件目）を設定
	$reply_address = ! empty( $recipients[0] ) ? $recipients[0] : get_option( 'admin_email' );
	$auto_headers  = array_merge( $headers, array(
		'Reply-To: ' . $site_name . ' <' . $reply_address . '>',
	) );

	wp_mail( $data['email'], $auto_subject, $auto_body, $auto_headers );
}

/* ============================================================
   6. カスタマイザーに「管理者メール」設定を追加
   ============================================================ */
function reirie_contact_customizer( $wp_customize ) {
	$wp_customize->add_section( 'reirie_contact_form', array(
		'title'    => 'REIRIE：お問い合わせフォーム',
		'priority' => 45,
	) );

	/* --- 受信先メールアドレス（複数対応・新方式） --- */
	$wp_customize->add_setting( 'reirie_contact_recipients', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'reirie_contact_recipients', array(
		'label'       => '受信先メールアドレス（複数可）',
		'description' => 'お問い合わせの通知先。複数指定する場合はカンマか改行で区切ってください。例: info@reirie.jp, staff@reirie.jp',
		'section'     => 'reirie_contact_form',
		'type'        => 'textarea',
	) );

	/* --- CC --- */
	$wp_customize->add_setting( 'reirie_contact_cc', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'reirie_contact_cc', array(
		'label'       => 'CC（複数可）',
		'description' => '通知メールのCC。カンマか改行で区切ってください。',
		'section'     => 'reirie_contact_form',
		'type'        => 'textarea',
	) );

	/* --- BCC --- */
	$wp_customize->add_setting( 'reirie_contact_bcc', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'reirie_contact_bcc', array(
		'label'       => 'BCC（複数可）',
		'description' => '通知メールのBCC。受信者にはアドレスが表示されません。',
		'section'     => 'reirie_contact_form',
		'type'        => 'textarea',
	) );

	/* --- 差出人名 --- */
	$wp_customize->add_setting( 'reirie_contact_from_name', array(
		'default'           => get_bloginfo( 'name' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'reirie_contact_from_name', array(
		'label'       => '差出人名（From）',
		'description' => '通知メールの差出人として表示される名前。',
		'section'     => 'reirie_contact_form',
		'type'        => 'text',
	) );

	/* --- 差出人メールアドレス --- */
	$wp_customize->add_setting( 'reirie_contact_from_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'reirie_contact_from_email', array(
		'label'       => '差出人メールアドレス（From）',
		'description' => 'サーバー上で実在するアドレスを推奨（迷惑メール判定を防ぐため、サイトと同じドメインのアドレスが理想）。例: no-reply@reirie.jp',
		'section'     => 'reirie_contact_form',
		'type'        => 'email',
	) );

	/* --- 旧：単一アドレス（後方互換用、非表示でも値は残る） --- */
	$wp_customize->add_setting( 'reirie_contact_admin_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'reirie_contact_admin_email', array(
		'label'       => '【旧】受信先メールアドレス（単一）',
		'description' => '上の「受信先メールアドレス（複数可）」を空にした場合のフォールバック。通常は使いません。',
		'section'     => 'reirie_contact_form',
		'type'        => 'email',
	) );

	$wp_customize->add_setting( 'reirie_contact_intro', array(
		'default'           => 'お問い合わせ内容に応じて種別をお選びください。担当者よりご連絡いたします。',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'reirie_contact_intro', array(
		'label'   => 'フォーム上部の案内文',
		'section' => 'reirie_contact_form',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'reirie_privacy_url', array(
		'default'           => '#',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'reirie_privacy_url', array(
		'label'   => 'プライバシーポリシーURL',
		'section' => 'reirie_contact_form',
		'type'    => 'url',
	) );
}
add_action( 'customize_register', 'reirie_contact_customizer', 20 );

/* ============================================================
   7. AJAX用のスクリプトデータを渡す
   ============================================================ */
function reirie_contact_localize() {
	if ( ! is_page_template( 'page-templates/template-contact.php' ) && ! is_page( 'contact' ) ) return;

	wp_localize_script( 'reirie-contact', 'REIRIE_CONTACT', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'reirie_contact_submit' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'reirie_contact_localize', 20 );

/**
 * フォームページでのみ JS を読み込み
 */
function reirie_contact_enqueue() {
	if ( ! is_page_template( 'page-templates/template-contact.php' ) && ! is_page( 'contact' ) ) return;

	wp_enqueue_script(
		'reirie-contact',
		REIRIE_URI . '/assets/js/contact.js',
		array(),
		REIRIE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'reirie_contact_enqueue', 15 );

/* ============================================================
   10. お問い合わせ固定ページの自動生成
   ============================================================
   - スラッグ "contact" の固定ページを自動作成
   - template-contact.php を割り当て
   - テーマ有効化時 + admin_init で補完（手動削除時の救済）
   ============================================================ */
function reirie_contact_page_config() {
	return array(
		'slug'     => 'contact',
		'title'    => 'お問い合わせ',
		'template' => 'page-templates/template-contact.php',
		'option'   => 'reirie_page_id_contact',
	);
}

function reirie_create_contact_page() {
	$config = reirie_contact_page_config();
	$existing_id = (int) get_option( $config['option'] );

	// 既存ページがまだ存在するか確認
	if ( $existing_id && get_post_status( $existing_id ) ) {
		// テンプレート割り当てが外れていたら補正
		$current_tpl = get_post_meta( $existing_id, '_wp_page_template', true );
		if ( $current_tpl !== $config['template'] ) {
			update_post_meta( $existing_id, '_wp_page_template', $config['template'] );
		}
		return;
	}

	// スラッグで重複チェック（既存ページがあれば再利用）
	$existing = get_page_by_path( $config['slug'] );
	if ( $existing ) {
		update_option( $config['option'], $existing->ID );
		update_post_meta( $existing->ID, '_wp_page_template', $config['template'] );
		return;
	}

	// 新規作成
	$page_id = wp_insert_post( array(
		'post_title'   => $config['title'],
		'post_name'    => $config['slug'],
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
		'post_author'  => 1,
		'meta_input'   => array(
			'_wp_page_template' => $config['template'],
		),
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( $config['option'], $page_id );
	}
}
add_action( 'after_switch_theme', 'reirie_create_contact_page' );

// 管理画面アクセス時にも未作成ページを補完（手動削除時の救済）
function reirie_ensure_contact_page() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	// 初回のみ実行（パフォーマンス対策のためトランジェント）
	if ( get_transient( 'reirie_contact_page_checked' ) ) return;
	reirie_create_contact_page();
	set_transient( 'reirie_contact_page_checked', 1, HOUR_IN_SECONDS );
}
add_action( 'admin_init', 'reirie_ensure_contact_page' );

// プライバシーポリシーURLのCustomizerフォールバック（legal-pagesと連携）
function reirie_contact_privacy_url_filter( $value ) {
	if ( ! empty( $value ) && $value !== '#' ) return $value;
	if ( function_exists( 'reirie_legal_page_url' ) ) {
		$privacy_url = reirie_legal_page_url( 'privacy' );
		if ( $privacy_url && $privacy_url !== '#' ) return $privacy_url;
	}
	return $value;
}
add_filter( 'theme_mod_reirie_privacy_url', 'reirie_contact_privacy_url_filter' );

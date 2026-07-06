<?php
/**
 * REIRIE メンバー管理（REI / RIE 2人専用UI）
 *
 * REIRIE は2人組ユニットのため、汎用CRUDではなく
 * 「2人分のプロフィールを並べて編集する固定UI」を提供する。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   2人のスロット定義
   ============================================================ */
function reirie_members_slots() {
	return array(
		'rei' => array(
			'label'        => 'REI',
			'default_jp'   => 'レイ',
			'default_color'      => 'PINK',
			'default_color_cls'  => 'color-pink',
			'default_color_hex'  => '#ff5b9c',
			'default_color2_name' => '',
			'default_color2_hex'  => '#c98bff',
			'default_photo_cls'  => 'photo-rei',
			'default_initial'    => 'R',
			'menu_order'   => 1,
			'theme_color'  => '#ff7eb6',
		),
		'rie' => array(
			'label'        => 'RIE',
			'default_jp'   => 'リエ',
			'default_color'      => 'SKY BLUE',
			'default_color_cls'  => 'color-blue',
			'default_color_hex'  => '#5fb6ff',
			'default_color2_name' => '',
			'default_color2_hex'  => '#8ad4ff',
			'default_photo_cls'  => 'photo-rie',
			'default_initial'    => 'R',
			'menu_order'   => 2,
			'theme_color'  => '#8ad4ff',
		),
	);
}

/* ============================================================
   各スロットに紐づくWP投稿を取得 or 作成
   ============================================================ */
function reirie_members_get_post_for_slot( $slot_key ) {
	$slots = reirie_members_slots();
	if ( ! isset( $slots[ $slot_key ] ) ) return null;
	$slot = $slots[ $slot_key ];

	// option に保存されたID を最優先
	$option_key = 'reirie_member_post_id_' . $slot_key;
	$post_id = (int) get_option( $option_key );

	if ( $post_id && get_post_status( $post_id ) ) {
		return get_post( $post_id );
	}

	// 既存メタ "_reirie_member_slot" で検索（手動作成や旧データ救済）
	$query = new WP_Query( array(
		'post_type'      => 'member',
		'posts_per_page' => 1,
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'meta_key'       => '_reirie_member_slot',
		'meta_value'     => $slot_key,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	if ( ! empty( $query->posts ) ) {
		$pid = (int) $query->posts[0];
		update_option( $option_key, $pid );
		return get_post( $pid );
	}

	// それでも無ければ新規作成
	$new_id = wp_insert_post( array(
		'post_type'   => 'member',
		'post_status' => 'publish',
		'post_title'  => $slot['label'],
		'menu_order'  => $slot['menu_order'],
		'meta_input'  => array(
			'_reirie_member_slot' => $slot_key,
			'member_name_jp'      => $slot['default_jp'],
			'member_color'        => $slot['default_color'],
			'member_color_class'  => $slot['default_color_cls'],
			'member_photo_class'  => $slot['default_photo_cls'],
			'member_initial'      => $slot['default_initial'],
		),
	) );

	if ( $new_id && ! is_wp_error( $new_id ) ) {
		update_option( $option_key, $new_id );
		return get_post( $new_id );
	}
	return null;
}

/* ============================================================
   汎用CPT一覧から member を除外（メニュー上のmember CPT行は隠す）
   ※ ただしダッシュボード内部の content_schema には member を残しているのは、
     ステップ進捗のカウントに使用されているため。
   ============================================================ */

/* ============================================================
   2人分のメンバーカードUIをレンダリング
   このフォームは独立したフォームとして送信される（メイン settings フォームとは別）
   ============================================================ */
function reirie_render_members_panel( $cpt ) {
	$slots = reirie_members_slots();

	// メイン設定フォームと独立させるためのターゲットフォームをここで開く
	// （閉じタグはパネルの最後で出力）
	$form_id = 'reirie-members-form';
	?>
	<section class="reirie-fw-panel reirie-fw-cpt-panel reirie-members-panel" id="cpt-member" data-cpt="member" style="display:none;">
	<?php /* メンバー専用フォームは admin-dashboard.php 側で本パネルの外（メイン設定 </form> の後）に
	   <form id="reirie-members-form" method="post" action=""></form> として配置されている。
	   ここの input/select/textarea は HTML5 の form 属性でそのフォームに紐付ける。 */ ?>
		<div class="reirie-fw-panel-header">
			<span class="icon"><span class="dashicons dashicons-groups"></span></span>
			<div>
				<h2>メンバー</h2>
				<p>REIRIE は 2人組ユニットです。下の2枚のカードで REI / RIE のプロフィール、メンバーカラー、SNS、TikTok動画を編集できます。</p>
			</div>
		</div>

		<div class="reirie-members-grid">
			<?php foreach ( $slots as $slot_key => $slot ) :
				$post = reirie_members_get_post_for_slot( $slot_key );
				$pid  = $post ? $post->ID : 0;

				$v = function( $k, $default = '' ) use ( $pid ) {
					if ( ! $pid ) return $default;
					$val = get_post_meta( $pid, $k, true );
					return $val !== '' ? $val : $default;
				};

				$photo_url = $pid ? get_the_post_thumbnail_url( $pid, 'reirie-member' ) : '';
				$photo_id  = $pid ? (int) get_post_thumbnail_id( $pid ) : 0;

				$prefix = 'reirie_member[' . esc_attr( $slot_key ) . ']';
			?>
				<div class="reirie-member-card" data-slot="<?php echo esc_attr( $slot_key ); ?>" style="--member-accent:<?php echo esc_attr( $slot['theme_color'] ); ?>;">
					<div class="reirie-member-card-head">
						<span class="reirie-member-badge"><?php echo esc_html( $slot['label'] ); ?></span>
						<span class="reirie-member-slot-desc">
							<?php echo $pid ? '投稿ID: #' . esc_html( $pid ) : '（未作成 — 保存時に自動作成）'; ?>
						</span>
					</div>

					<!-- 写真 -->
					<div class="reirie-member-photo-block">
						<label class="reirie-member-label">プロフィール写真（推奨：縦4:5 / 800×1000）</label>
						<div class="reirie-member-photo-preview" data-slot="<?php echo esc_attr( $slot_key ); ?>">
							<?php if ( $photo_url ) : ?>
								<img src="<?php echo esc_url( $photo_url ); ?>" alt="">
							<?php else : ?>
								<div class="reirie-member-photo-empty">
									<span class="dashicons dashicons-format-image"></span>
									<span>写真未設定</span>
								</div>
							<?php endif; ?>
						</div>
						<input type="hidden" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[thumbnail_id]" value="<?php echo esc_attr( $photo_id ); ?>" class="reirie-member-thumb-id">
						<div class="reirie-member-photo-buttons">
							<button type="button" class="button reirie-member-photo-select" data-slot="<?php echo esc_attr( $slot_key ); ?>">
								<span class="dashicons dashicons-upload"></span>写真を選択
							</button>
							<button type="button" class="button reirie-member-photo-remove" data-slot="<?php echo esc_attr( $slot_key ); ?>"<?php echo $photo_url ? '' : ' style="display:none;"'; ?>>
								<span class="dashicons dashicons-no-alt"></span>削除
							</button>
						</div>
					</div>

					<!-- 名前 -->
					<div class="reirie-member-row">
						<label class="reirie-member-label">名前（英字 / タイトル）</label>
						<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[title]" value="<?php echo esc_attr( $post ? $post->post_title : $slot['label'] ); ?>" placeholder="<?php echo esc_attr( $slot['label'] ); ?>">
					</div>

					<div class="reirie-member-row">
						<label class="reirie-member-label">名前（カナ）</label>
						<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_name_jp]" value="<?php echo esc_attr( $v( 'member_name_jp', $slot['default_jp'] ) ); ?>" placeholder="<?php echo esc_attr( $slot['default_jp'] ); ?>">
					</div>

					<!-- メンバーカラー設定（メイン + サブの 2色） -->
					<div class="reirie-member-section-title" style="margin-top:8px;">
						<span class="dashicons dashicons-art"></span>メンバーカラー
						<small style="font-weight:normal;color:#888;margin-left:8px;">カラーホイールで自由に色を選べます。サブカラーは空欄なら表示しません。</small>
					</div>
					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">メインカラー名<small>（例：PINK / LAVENDER）</small></label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_color]" value="<?php echo esc_attr( $v( 'member_color', $slot['default_color'] ) ); ?>" placeholder="<?php echo esc_attr( $slot['default_color'] ); ?>">
						</div>
						<div>
							<label class="reirie-member-label">メインカラー（カラーホイール）</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" class="reirie-color-picker" name="<?php echo esc_attr( $prefix ); ?>[member_color_hex]" value="<?php echo esc_attr( $v( 'member_color_hex', $slot['default_color_hex'] ) ); ?>" data-default-color="<?php echo esc_attr( $slot['default_color_hex'] ); ?>">
						</div>
					</div>
					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">サブカラー名<small>（例：WHITE / GOLD。空欄でOK）</small></label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_color2]" value="<?php echo esc_attr( $v( 'member_color2', $slot['default_color2_name'] ) ); ?>" placeholder="（任意）">
						</div>
						<div>
							<label class="reirie-member-label">サブカラー（カラーホイール）</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" class="reirie-color-picker" name="<?php echo esc_attr( $prefix ); ?>[member_color2_hex]" value="<?php echo esc_attr( $v( 'member_color2_hex', $slot['default_color2_hex'] ) ); ?>" data-default-color="<?php echo esc_attr( $slot['default_color2_hex'] ); ?>">
						</div>
					</div>
					<!-- 互換用：旧 color-class（テーマ全体のフォールバックで使用） -->
					<input type="hidden" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_color_class]" value="<?php echo esc_attr( $v( 'member_color_class', $slot['default_color_cls'] ) ); ?>">

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">フォト背景クラス<small>（写真未設定時）</small></label>
							<select form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_photo_class]">
								<?php
								$photo_choices = array(
									'photo-rei'    => 'Rei スタイル（ピンク系）',
									'photo-rie'    => 'Rie スタイル（ブルー系）',
									'photo-purple' => 'パープル系',
								);
								$current_pc = $v( 'member_photo_class', $slot['default_photo_cls'] );
								foreach ( $photo_choices as $cv => $cl ) {
									$sel = selected( $current_pc, $cv, false );
									echo '<option value="' . esc_attr( $cv ) . '"' . $sel . '>' . esc_html( $cl ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<label class="reirie-member-label">イニシャル文字<small>（写真未設定時）</small></label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_initial]" value="<?php echo esc_attr( $v( 'member_initial', $slot['default_initial'] ) ); ?>" maxlength="2">
						</div>
					</div>

					<div class="reirie-member-row">
						<label class="reirie-member-label">キャッチフレーズ<small>（例：太陽みたいな笑顔担当）</small></label>
						<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_catch]" value="<?php echo esc_attr( $v( 'member_catch' ) ); ?>">
					</div>

					<!-- プロフィール詳細 -->
					<div class="reirie-member-section-title">
						<span class="dashicons dashicons-id"></span>プロフィール詳細
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">誕生日</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_birthday]" value="<?php echo esc_attr( $v( 'member_birthday' ) ); ?>" placeholder="9月12日">
						</div>
						<div>
							<label class="reirie-member-label">血液型</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_blood]" value="<?php echo esc_attr( $v( 'member_blood' ) ); ?>" placeholder="A型">
						</div>
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">出身地</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_hometown]" value="<?php echo esc_attr( $v( 'member_hometown' ) ); ?>" placeholder="東京都">
						</div>
						<div>
							<label class="reirie-member-label">身長</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_height]" value="<?php echo esc_attr( $v( 'member_height' ) ); ?>" placeholder="158cm">
						</div>
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">趣味</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_hobby]" value="<?php echo esc_attr( $v( 'member_hobby' ) ); ?>">
						</div>
						<div>
							<label class="reirie-member-label">チャームポイント</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_charm]" value="<?php echo esc_attr( $v( 'member_charm' ) ); ?>">
						</div>
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">スペシャルスキル / 特技</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_skill]" value="<?php echo esc_attr( $v( 'member_skill' ) ); ?>">
						</div>
						<div>
							<label class="reirie-member-label">MBTI</label>
							<input type="text" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_mbti]" value="<?php echo esc_attr( $v( 'member_mbti' ) ); ?>" placeholder="ESFP">
						</div>
					</div>

					<div class="reirie-member-row">
						<label class="reirie-member-label">ファンへのメッセージ<small>（120文字程度推奨）</small></label>
						<textarea form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_message]" rows="3"><?php echo esc_textarea( $v( 'member_message' ) ); ?></textarea>
					</div>

					<!-- SNS -->
					<div class="reirie-member-section-title">
						<span class="dashicons dashicons-share"></span>SNS
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">X (Twitter) URL</label>
							<input type="url" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_sns_twitter]" value="<?php echo esc_attr( $v( 'member_sns_twitter' ) ); ?>" placeholder="https://twitter.com/...">
						</div>
						<div>
							<label class="reirie-member-label">Instagram URL</label>
							<input type="url" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_sns_instagram]" value="<?php echo esc_attr( $v( 'member_sns_instagram' ) ); ?>" placeholder="https://instagram.com/...">
						</div>
					</div>

					<div class="reirie-member-row-2col">
						<div>
							<label class="reirie-member-label">TikTok URL</label>
							<input type="url" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_sns_tiktok]" value="<?php echo esc_attr( $v( 'member_sns_tiktok' ) ); ?>" placeholder="https://tiktok.com/@...">
						</div>
						<div>
							<label class="reirie-member-label">ブログ / 個人サイト URL</label>
							<input type="url" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_sns_blog]" value="<?php echo esc_attr( $v( 'member_sns_blog' ) ); ?>">
						</div>
					</div>

					<!-- TikTok埋め込み -->
					<div class="reirie-member-section-title">
						<span class="dashicons dashicons-format-video"></span>TikTok埋め込み動画（個別ページに表示）
					</div>
					<p class="reirie-member-hint">TikTok 動画のページURLを貼り付けると、メンバー個別ページに埋め込み再生プレーヤーとして表示されます。</p>

					<?php for ( $n = 1; $n <= 3; $n++ ) : ?>
						<div class="reirie-member-row">
							<label class="reirie-member-label">TikTok動画 <?php echo $n; ?></label>
							<input type="url" form="<?php echo esc_attr( $form_id ); ?>" name="<?php echo esc_attr( $prefix ); ?>[member_tiktok_video_<?php echo $n; ?>]" value="<?php echo esc_attr( $v( 'member_tiktok_video_' . $n ) ); ?>" placeholder="https://www.tiktok.com/@user/video/...">
						</div>
					<?php endfor; ?>

					<?php if ( $pid ) : ?>
						<div class="reirie-member-card-foot">
							<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>" target="_blank" class="button-link">
								<span class="dashicons dashicons-edit" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span>標準エディタで開く
							</a>
							<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" target="_blank" class="button-link">
								<span class="dashicons dashicons-external" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span>個別ページを表示
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 保存バー（メンバー専用） -->
		<div class="reirie-members-submit-bar">
			<span class="hint"><span class="dashicons dashicons-info-outline" style="font-size:14px;width:14px;height:14px;vertical-align:middle;color:#b07aff;"></span> 2人分のプロフィールを同時に保存します</span>
			<?php
			// nonce フィールドも対象フォームに属させる
			$nonce_val = wp_create_nonce( 'reirie_members_save' );
			?>
			<input type="hidden" form="<?php echo esc_attr( $form_id ); ?>" name="reirie_members_nonce" value="<?php echo esc_attr( $nonce_val ); ?>">
			<button type="submit" form="<?php echo esc_attr( $form_id ); ?>" name="reirie_members_submit" value="1" class="button button-primary button-large">
				<span class="dashicons dashicons-saved" style="vertical-align:middle;margin-right:4px;"></span>メンバー情報を保存する
			</button>
		</div>
	</section>

	<style>
	.reirie-members-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 28px;
		margin-top: 20px;
	}
	@media (max-width: 1280px) {
		.reirie-members-grid { grid-template-columns: 1fr; }
	}
	.reirie-member-card {
		background: #fff;
		border: 2px solid var(--member-accent, #ff7eb6);
		border-radius: 16px;
		padding: 20px 22px 22px;
		box-shadow: 0 6px 18px rgba(255,126,182,.10);
		position: relative;
	}
	.reirie-member-card-head {
		display: flex;
		align-items: center;
		gap: 12px;
		margin: -8px -8px 18px;
		padding-bottom: 14px;
		border-bottom: 1px dashed rgba(255,126,182,.3);
	}
	.reirie-member-badge {
		display: inline-block;
		background: var(--member-accent, #ff7eb6);
		color: #fff;
		font-family: 'Cormorant Garamond', serif;
		font-size: 22px;
		font-weight: 700;
		letter-spacing: .15em;
		padding: 4px 16px;
		border-radius: 999px;
		line-height: 1.4;
	}
	.reirie-member-slot-desc {
		font-size: 12px;
		color: #999;
		letter-spacing: .05em;
	}
	.reirie-member-row {
		margin-bottom: 14px;
	}
	.reirie-member-row-2col {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 14px;
		margin-bottom: 14px;
	}
	.reirie-member-label {
		display: block;
		font-size: 12px;
		color: #555;
		margin-bottom: 4px;
		font-weight: 600;
		letter-spacing: .04em;
	}
	.reirie-member-label small {
		font-weight: 400;
		color: #999;
		margin-left: 6px;
	}
	.reirie-member-card input[type=text],
	.reirie-member-card input[type=url],
	.reirie-member-card select,
	.reirie-member-card textarea {
		width: 100%;
		padding: 7px 10px;
		font-size: 13px;
		border: 1px solid #ddd;
		border-radius: 6px;
		background: #fff;
	}
	.reirie-member-card input[type=text]:focus,
	.reirie-member-card input[type=url]:focus,
	.reirie-member-card select:focus,
	.reirie-member-card textarea:focus {
		outline: none;
		border-color: var(--member-accent, #ff7eb6);
		box-shadow: 0 0 0 2px rgba(255,126,182,.15);
	}
	.reirie-member-section-title {
		margin: 22px 0 10px;
		padding: 6px 0 6px;
		font-size: 13px;
		color: #c43a73;
		font-weight: 700;
		letter-spacing: .12em;
		border-top: 1px dashed rgba(255,126,182,.25);
		padding-top: 14px;
		display: flex;
		align-items: center;
		gap: 6px;
	}
	.reirie-member-section-title .dashicons {
		font-size: 16px;
		width: 16px;
		height: 16px;
		color: var(--member-accent, #ff7eb6);
	}
	.reirie-member-hint {
		font-size: 12px;
		color: #888;
		margin: -4px 0 12px;
		line-height: 1.6;
	}
	.reirie-member-photo-block {
		margin-bottom: 18px;
	}
	.reirie-member-photo-preview {
		width: 100%;
		max-width: 220px;
		aspect-ratio: 4/5;
		border-radius: 12px;
		overflow: hidden;
		background: linear-gradient(135deg,#fff5f9,#fff0fa);
		border: 2px dashed rgba(255,126,182,.4);
		display: flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 10px;
	}
	.reirie-member-photo-preview img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}
	.reirie-member-photo-empty {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 6px;
		color: #c8a3b8;
		font-size: 12px;
	}
	.reirie-member-photo-empty .dashicons {
		font-size: 36px;
		width: 36px;
		height: 36px;
	}
	.reirie-member-photo-buttons {
		display: flex;
		gap: 8px;
	}
	.reirie-members-submit-bar {
		margin-top: 28px;
		padding: 16px 20px;
		background: linear-gradient(135deg,#fff5f9,#fff0fa);
		border: 1px solid rgba(255,126,182,.25);
		border-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 14px;
		flex-wrap: wrap;
	}
	.reirie-members-submit-bar .hint {
		font-size: 12px;
		color: #888;
	}
	.reirie-member-card-foot {
		margin-top: 14px;
		padding-top: 12px;
		border-top: 1px dashed rgba(255,126,182,.25);
		display: flex;
		gap: 18px;
		flex-wrap: wrap;
		font-size: 12px;
	}
	.reirie-member-card-foot .button-link {
		color: #b07aff;
		text-decoration: none;
	}
	.reirie-member-card-foot .button-link:hover {
		text-decoration: underline;
	}
	</style>

	<script>
	(function(){
		// メディアアップローダー（写真選択）— event delegation
		document.addEventListener('click', function(e){
			var btn = e.target.closest('.reirie-member-photo-select');
			if (btn) {
				e.preventDefault();
				var slot = btn.getAttribute('data-slot');
				if (typeof wp === 'undefined' || !wp.media) {
					alert('メディアライブラリが読み込まれていません。ページを再読み込みしてください。');
					return;
				}
				var frame = wp.media({
					title: 'メンバー写真を選択（' + slot.toUpperCase() + '）',
					button: { text: 'この画像を使う' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					var card = document.querySelector('.reirie-member-card[data-slot="' + slot + '"]');
					if (!card) return;
					var hidden = card.querySelector('.reirie-member-thumb-id');
					if (hidden) hidden.value = att.id;
					var preview = card.querySelector('.reirie-member-photo-preview');
					if (preview) {
						var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
						preview.innerHTML = '<img src="' + url + '" alt="">';
					}
					var removeBtn = card.querySelector('.reirie-member-photo-remove');
					if (removeBtn) removeBtn.style.display = '';
				});
				frame.open();
				return;
			}

			var removeBtn = e.target.closest('.reirie-member-photo-remove');
			if (removeBtn) {
				e.preventDefault();
				var slot = removeBtn.getAttribute('data-slot');
				var card = document.querySelector('.reirie-member-card[data-slot="' + slot + '"]');
				if (!card) return;
				var hidden = card.querySelector('.reirie-member-thumb-id');
				if (hidden) hidden.value = '0';
				var preview = card.querySelector('.reirie-member-photo-preview');
				if (preview) {
					preview.innerHTML = '<div class="reirie-member-photo-empty"><span class="dashicons dashicons-format-image"></span><span>写真未設定</span></div>';
				}
				removeBtn.style.display = 'none';
			}
		});
	})();
	</script>
	<?php
}

/* ============================================================
   保存処理
   admin-dashboard の保存ハンドラーが走る前に admin_init で処理
   ============================================================ */
function reirie_members_save_handler() {
	if ( ! is_admin() ) return;
	if ( empty( $_POST['reirie_members_submit'] ) ) return;
	// 管理ダッシュボード（reirie-dashboard）と同じ capability に揃える
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'メンバー情報を編集する権限がありません。', '権限エラー', array( 'back_link' => true ) );
	}
	if ( empty( $_POST['reirie_members_nonce'] ) || ! wp_verify_nonce( $_POST['reirie_members_nonce'], 'reirie_members_save' ) ) {
		wp_die( 'セキュリティチェックに失敗しました。', '保存エラー', array( 'back_link' => true ) );
	}

	$input = isset( $_POST['reirie_member'] ) && is_array( $_POST['reirie_member'] ) ? $_POST['reirie_member'] : array();
	$slots = reirie_members_slots();

	$meta_fields_text = array(
		'member_name_jp', 'member_color', 'member_color2', 'member_color_class', 'member_photo_class', 'member_initial',
		'member_catch', 'member_birthday', 'member_blood', 'member_hometown', 'member_height',
		'member_hobby', 'member_charm', 'member_skill', 'member_mbti', 'member_message',
	);
	// カラーコード（#rrggbb / #rgb / 空文字 のみ許可）
	$meta_fields_color = array( 'member_color_hex', 'member_color2_hex' );
	$meta_fields_url = array(
		'member_sns_twitter', 'member_sns_instagram', 'member_sns_tiktok', 'member_sns_blog',
		'member_tiktok_video_1', 'member_tiktok_video_2', 'member_tiktok_video_3',
	);

	$saved = 0;
	foreach ( $slots as $slot_key => $slot ) {
		$post = reirie_members_get_post_for_slot( $slot_key );
		if ( ! $post ) continue;
		$pid = $post->ID;
		$data = isset( $input[ $slot_key ] ) && is_array( $input[ $slot_key ] ) ? $input[ $slot_key ] : array();

		// タイトル
		$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : $slot['label'];
		if ( $title === '' ) $title = $slot['label'];

		wp_update_post( array(
			'ID'         => $pid,
			'post_title' => $title,
			'menu_order' => $slot['menu_order'],
		) );

		// スロット識別メタを必ず残す
		update_post_meta( $pid, '_reirie_member_slot', $slot_key );

		// テキスト系メタ
		foreach ( $meta_fields_text as $f ) {
			$val = isset( $data[ $f ] ) ? sanitize_textarea_field( $data[ $f ] ) : '';
			update_post_meta( $pid, $f, $val );
		}
		// カラーコードメタ（#rrggbb / #rgb のみ。それ以外は空文字に正規化）
		foreach ( $meta_fields_color as $f ) {
			$raw = isset( $data[ $f ] ) ? trim( (string) $data[ $f ] ) : '';
			$val = '';
			if ( $raw !== '' && preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $raw ) ) {
				$val = strtolower( $raw );
			}
			update_post_meta( $pid, $f, $val );
		}
		// URL系メタ
		foreach ( $meta_fields_url as $f ) {
			$val = isset( $data[ $f ] ) ? esc_url_raw( $data[ $f ] ) : '';
			update_post_meta( $pid, $f, $val );
		}

		// アイキャッチ
		if ( isset( $data['thumbnail_id'] ) ) {
			$tid = (int) $data['thumbnail_id'];
			if ( $tid > 0 ) {
				set_post_thumbnail( $pid, $tid );
			} else {
				delete_post_thumbnail( $pid );
			}
		}

		$saved++;
	}

	// リダイレクトしてメンバータブを開いた状態で戻る
	// ※ 管理メニューの実スラッグは reirie-dashboard
	$redirect = add_query_arg(
		array( 'page' => 'reirie-dashboard', 'members_saved' => $saved, 'tab' => 'cpt-member' ),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'reirie_members_save_handler', 5 );

/* ============================================================
   メンバータブを開いた状態で復帰するためのフラグ表示
   ============================================================ */
function reirie_members_saved_notice() {
	if ( ! isset( $_GET['members_saved'] ) ) return;
	$count = (int) $_GET['members_saved'];
	echo '<div class="notice notice-success is-dismissible reirie-members-saved-notice" style="margin:12px 0 0;"><p><span class="dashicons dashicons-yes" style="color:#46b450;"></span> メンバー情報を保存しました（' . esc_html( $count ) . '人分）。</p></div>';
}
add_action( 'admin_notices', 'reirie_members_saved_notice' );

/* ============================================================
   ダッシュボード読み込み時にメディアライブラリを enqueue
   （admin-dashboard 側で既に呼ばれているはずだが念のため）
   ============================================================ */
function reirie_members_enqueue_media( $hook ) {
	if ( strpos( (string) $hook, 'reirie-settings' ) === false ) return;
	wp_enqueue_media();
}

/* ============================================================
   wp-color-picker（カラーホイール）を REIRIE 設定画面にロード
   ============================================================ */
function reirie_members_enqueue_color_picker( $hook ) {
	// ダッシュボード本体は reirie-dashboard、メンバー編集はそのタブ内
	if ( strpos( (string) $hook, 'reirie' ) === false ) return;
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker',
		'jQuery(function($){' .
			'$(".reirie-color-picker").wpColorPicker({' .
				'palettes: ["#ff5b9c","#ff7eb6","#ffb3d1","#c98bff","#b07aff","#8a4dff","#5fb6ff","#8ad4ff","#6fd1d1","#ffd96a","#ffb84d","#ff8a8a","#7ad48a","#333333","#ffffff"],' .
				'change: function(event, ui){' .
					'$(this).val(ui.color.toString()).trigger("change");' .
				'}' .
			'});' .
		'});'
	);
}
add_action( 'admin_enqueue_scripts', 'reirie_members_enqueue_color_picker' );
add_action( 'admin_enqueue_scripts', 'reirie_members_enqueue_media' );

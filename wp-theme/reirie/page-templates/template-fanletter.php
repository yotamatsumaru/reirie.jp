<?php
/**
 * Template Name: REIRIE ファンレター送付先
 *
 * 固定ページにこのテンプレートを割り当てると、
 * 「FAN MAIL」カードを押した時の遷移先になる。
 * REIRIE 管理画面 → サイト設定 → 「ファンレター送付先」で
 * 住所・注意事項を編集できる。
 *
 * @package REIRIE
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// テーマカスタマイズから設定値を取得
$heading     = get_theme_mod( 'reirie_fanletter_heading', 'FAN LETTER' );
$subheading  = get_theme_mod( 'reirie_fanletter_subheading', 'ファンレター送付先' );
$lead        = get_theme_mod( 'reirie_fanletter_lead', '2人へのお手紙、贈り物は下記の宛先までお送りください。' );
$postal      = get_theme_mod( 'reirie_fanletter_postal', '〒000-0000' );
$address     = get_theme_mod( 'reirie_fanletter_address', '' );
$footer_msg  = get_theme_mod( 'reirie_fanletter_footer', '' );

// 自由テキスト（推奨）：ガイド全文を1つの textarea で編集
$guide_text  = get_theme_mod( 'reirie_fanletter_guide', '' );

$addr_lines  = preg_split( '/\r\n|\r|\n/', (string) $address );
?>

<main class="section fanletter-section single-page" id="fanletter">

	<div class="section__head">
		<span class="section__num">— / Fan Letter</span>
		<h2 class="section__title"><?php echo esc_html( $heading ); ?><span class="section__title-jp"><?php echo esc_html( $subheading ); ?></span></h2>
	</div>

	<div class="fanletter-wrap">

		<?php if ( $lead ) : ?>
		<p class="fanletter-lead"><?php echo nl2br( esc_html( $lead ) ); ?></p>
		<?php endif; ?>

		<!-- 住所カード -->
		<div class="fanletter-card fanletter-card--address">
			<div class="fanletter-card-icon" aria-hidden="true">
				<svg class="fanletter-envelope" viewBox="0 0 64 48" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Envelope">
					<defs>
						<linearGradient id="reirie-env-body" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stop-color="#fff"/>
							<stop offset="100%" stop-color="#fff3f7"/>
						</linearGradient>
						<linearGradient id="reirie-env-flap" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stop-color="#ffd6e6"/>
							<stop offset="100%" stop-color="#ffb8d2"/>
						</linearGradient>
						<linearGradient id="reirie-env-heart" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stop-color="#ff7eb2"/>
							<stop offset="100%" stop-color="#ff5b9c"/>
						</linearGradient>
					</defs>
					<!-- 本体 -->
					<rect x="3" y="9" width="58" height="36" rx="4" ry="4" fill="url(#reirie-env-body)" stroke="#f0c8d8" stroke-width="1"/>
					<!-- 側面の折り目 -->
					<path d="M3,13 L32,32 L61,13" fill="none" stroke="#f0c8d8" stroke-width="1" stroke-linejoin="round"/>
					<path d="M3,45 L24,28" fill="none" stroke="#f0c8d8" stroke-width="1" stroke-linejoin="round"/>
					<path d="M61,45 L40,28" fill="none" stroke="#f0c8d8" stroke-width="1" stroke-linejoin="round"/>
					<!-- 上のフラップ -->
					<path d="M3,9 L32,30 L61,9 Z" fill="url(#reirie-env-flap)" stroke="#e89bbb" stroke-width="1" stroke-linejoin="round"/>
					<!-- ハートシール -->
					<g transform="translate(32,28)">
						<circle r="8" fill="#fff" stroke="#ffd0e0" stroke-width="0.8"/>
						<path d="M0,3 C-3.5,0 -6,-2 -6,-4.5 C-6,-6.5 -4.5,-7.8 -3,-7.8 C-1.8,-7.8 -0.7,-7.1 0,-6 C0.7,-7.1 1.8,-7.8 3,-7.8 C4.5,-7.8 6,-6.5 6,-4.5 C6,-2 3.5,0 0,3 Z" fill="url(#reirie-env-heart)"/>
					</g>
				</svg>
			</div>
			<div class="fanletter-card-label">SEND TO / 送付先住所</div>
			<?php if ( $postal ) : ?>
				<p class="fanletter-postal"><?php echo esc_html( $postal ); ?></p>
			<?php endif; ?>
			<address class="fanletter-address">
				<?php
				foreach ( $addr_lines as $line ) :
					$line = trim( $line );
					if ( $line === '' ) continue;
				?>
					<span class="fanletter-address-line"><?php echo esc_html( $line ); ?></span>
				<?php endforeach; ?>
			</address>
		</div>

		<!-- ガイド本文（自由記入） -->
		<?php if ( $guide_text ) : ?>
		<div class="fanletter-guide">
			<?php echo nl2br( esc_html( $guide_text ) ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $footer_msg ) : ?>
		<div class="fanletter-footer-msg">
			<p><?php echo nl2br( esc_html( $footer_msg ) ); ?></p>
		</div>
		<?php endif; ?>

		<!-- 固定ページの本文も追加で表示できるように（任意） -->
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); $content = get_the_content();
			if ( trim( wp_strip_all_tags( $content ) ) !== '' ) : ?>
			<div class="fanletter-page-content">
				<?php the_content(); ?>
			</div>
		<?php endif; endwhile; endif; ?>

		<div class="fanletter-back">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fanletter-back-btn">
				<span aria-hidden="true">←</span> TOP に戻る
			</a>
		</div>

	</div>
</main>

<?php get_footer(); ?>

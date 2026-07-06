<?php
/**
 * Header テンプレート
 *
 * @package REIRIE
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) wp_body_open(); ?>

<!-- パーティクル背景キャンバス -->
<canvas id="particles-canvas"></canvas>

<!-- カスタムカーソル -->
<div class="cursor-dot" id="cursor-dot"></div>
<div class="cursor-ring" id="cursor-ring"></div>

<!-- ヘッダー / ナビ -->
<header class="header" id="header">
  <div class="header__inner">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo">
      <?php if ( has_custom_logo() ) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <img src="<?php echo esc_url( REIRIE_URI . '/assets/img/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="header__logo-img">
      <?php endif; ?>
    </a>

    <nav class="gnav" id="gnav">
      <ul class="gnav__list">
        <li><a href="<?php echo esc_url( home_url( '/#news' ) ); ?>" class="gnav__link"><span class="num">01</span><span class="en">News</span><span class="ja">お知らせ</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#schedule' ) ); ?>" class="gnav__link"><span class="num">02</span><span class="en">Schedule</span><span class="ja">スケジュール</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#discography' ) ); ?>" class="gnav__link"><span class="num">03</span><span class="en">Discography</span><span class="ja">作品</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#movie' ) ); ?>" class="gnav__link"><span class="num">04</span><span class="en">Movie</span><span class="ja">動画</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#profile' ) ); ?>" class="gnav__link"><span class="num">05</span><span class="en">Profile</span><span class="ja">プロフィール</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#goods' ) ); ?>" class="gnav__link"><span class="num">06</span><span class="en">Goods</span><span class="ja">グッズ</span></a></li>
        <li><a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="gnav__link"><span class="num">07</span><span class="en">Contact</span><span class="ja">お問い合わせ</span></a></li>
      </ul>

      <ul class="gnav__sns">
        <?php foreach ( reirie_get_sns_links() as $sns ) : ?>
          <?php if ( ! empty( $sns['url'] ) ) : ?>
            <li><a href="<?php echo esc_url( $sns['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $sns['label'] ); ?>"><?php echo $sns['icon']; // SVG ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>

    <button class="hamburger" id="hamburger" aria-label="メニューを開く">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

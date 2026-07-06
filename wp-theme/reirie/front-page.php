<?php
/**
 * フロントページ（トップページ）テンプレート
 *
 * @package REIRIE
 */

get_header(); ?>

<?php
// セクションの表示／非表示は REIRIE 管理画面 > サイト設定 > セクションの表示／非表示 で切替
$reirie_show = function_exists( 'reirie_section_is_visible' ) ? 'reirie_section_is_visible' : null;
?>

<?php get_template_part( 'template-parts/section', 'hero' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'news' ) )        get_template_part( 'template-parts/section', 'news' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'schedule' ) )    get_template_part( 'template-parts/section', 'schedule' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'discography' ) ) get_template_part( 'template-parts/section', 'discography' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'movie' ) )       get_template_part( 'template-parts/section', 'movie' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'profile' ) )     get_template_part( 'template-parts/section', 'profile' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'goods' ) )       get_template_part( 'template-parts/section', 'goods' ); ?>
<?php if ( ! $reirie_show || $reirie_show( 'contact' ) )     get_template_part( 'template-parts/section', 'contact' ); ?>

<?php get_footer(); ?>

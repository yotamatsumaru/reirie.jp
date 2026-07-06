<?php
/**
 * Goods Section
 *
 * 表示モード（reirie_goods_mode）:
 *   - 'link' : 外部ストアへの大きなボタン 1 つだけを表示
 *   - 'list' : 投稿（CPT: goods）で登録した商品をグリッド表示
 *
 *   ※ 商品が 1 件もない場合は何も表示しません（デモは出ません）。
 *
 * @package REIRIE
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$goods_mode       = get_theme_mod( 'reirie_goods_mode', 'link' );
$goods_link_url   = trim( (string) get_theme_mod( 'reirie_goods_link_url', '' ) );
$goods_link_label = trim( (string) get_theme_mod( 'reirie_goods_link_label', 'VISIT OFFICIAL STORE' ) );
$goods_link_sub   = trim( (string) get_theme_mod( 'reirie_goods_link_sub', 'グッズはオフィシャルストアにてお取り扱いしております。' ) );

// list モード時のみクエリを実行
$goods_query = null;
if ( $goods_mode === 'list' ) {
	$goods_query = new WP_Query( array(
		'post_type'      => 'goods',
		'posts_per_page' => 8,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );
	// 1 件もない場合はセクションごと非表示
	if ( ! $goods_query->have_posts() ) {
		return;
	}
}
?>

<section class="section goods<?php echo $goods_mode === 'link' ? ' goods--link-mode' : ''; ?>" id="goods">
  <div class="section__head">
    <span class="section__num">06 / Goods</span>
    <h2 class="section__title">Goods<span class="section__title-jp">グッズ</span></h2>
  </div>

  <?php if ( $goods_mode === 'link' ) :
    // 1 ボタン表示モード
    $url = $goods_link_url !== '' ? $goods_link_url : '#';
    $is_external = (bool) preg_match( '#^https?://#i', $url );
  ?>
    <div class="goods__single-link">
      <a href="<?php echo esc_url( $url ); ?>" class="goods__store-btn"
         <?php if ( $is_external ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
        <span class="goods__store-btn-label"><?php echo esc_html( $goods_link_label ); ?></span>
        <span class="goods__store-btn-arrow" aria-hidden="true"><?php echo $is_external ? '↗' : '→'; ?></span>
      </a>
      <?php if ( $goods_link_sub !== '' ) : ?>
        <p class="goods__store-note"><?php echo esc_html( $goods_link_sub ); ?></p>
      <?php endif; ?>
    </div>

  <?php else :
    // 個別商品リストモード
  ?>
    <div class="goods__grid">
      <?php
      $i = 0;
      while ( $goods_query->have_posts() ) : $goods_query->the_post();
        $price  = reirie_field( 'goods_price' );
        $link   = reirie_field( 'goods_link', false, get_permalink() );
        $status = reirie_field( 'goods_status' );
        $thumb  = get_the_post_thumbnail_url( get_the_ID(), 'reirie-jacket' );
        $is_external = ( $link !== get_permalink() );
      ?>
        <article class="goods__item<?php echo $status === 'SOLD OUT' ? ' is-soldout' : ''; ?>">
          <a href="<?php echo esc_url( $link ); ?>"<?php echo $is_external ? ' target="_blank" rel="noopener"' : ''; ?>>
            <?php if ( $thumb ) : ?>
              <div class="goods__img" style="background-image:url('<?php echo esc_url( $thumb ); ?>');background-size:cover;background-position:center;">
                <?php if ( $status ) : ?><span class="goods__status goods__status--<?php echo esc_attr( strtolower( str_replace( ' ', '-', $status ) ) ); ?>"><?php echo esc_html( $status ); ?></span><?php endif; ?>
              </div>
            <?php else : ?>
              <div class="goods__img goods-<?php echo esc_attr( ( $i % 4 ) + 1 ); ?>">
                <?php if ( $status ) : ?><span class="goods__status goods__status--<?php echo esc_attr( strtolower( str_replace( ' ', '-', $status ) ) ); ?>"><?php echo esc_html( $status ); ?></span><?php endif; ?>
              </div>
            <?php endif; ?>
            <p class="goods__name"><?php the_title(); ?></p>
            <?php if ( $price ) : ?><p class="goods__price"><?php echo esc_html( $price ); ?></p><?php endif; ?>
          </a>
        </article>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>

    <div class="section__more">
      <a href="<?php echo esc_url( get_post_type_archive_link( 'goods' ) ); ?>" class="more-btn"><span>OFFICIAL STORE</span></a>
    </div>
  <?php endif; ?>
</section>

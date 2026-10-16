<?php
if (!defined('ABSPATH')) exit;
$is_product_search = isset($_GET['post_type']) && sanitize_key(wp_unslash($_GET['post_type'])) === 'product';
if (class_exists('WooCommerce') && $is_product_search && locate_template('woocommerce/archive-product.php')) {
    require get_theme_file_path('woocommerce/archive-product.php');
    return;
}
get_header();
$query = get_search_query();
$posts = new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>20,'s'=>$query]);
?>
<?php echo tesra_crumb([['نتایج جست‌وجو']]); ?>
<section class="box search-results-page">
 <div class="search-results-heading"><span class="tg">جست‌وجوی سایت</span><h1 class="ph">نتایج جست‌وجو برای «<?php echo esc_html($query); ?>»</h1><p class="mu"><?php echo esc_html(number_format_i18n((int)$posts->found_posts)); ?> نتیجه پیدا شد.</p></div>
 <?php if ($posts->have_posts()): ?>
  <div class="article-grid">
  <?php while ($posts->have_posts()): $posts->the_post(); $image=get_the_post_thumbnail_url(get_the_ID(),'large')?:tesra_placeholder(get_the_ID()); ?>
   <article class="article-card"><a class="article-cover" href="<?php the_permalink(); ?>"><img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></a><div class="article-content"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),22)); ?></p><a class="article-more" href="<?php the_permalink(); ?>">ادامه مطلب ←</a></div></article>
  <?php endwhile; ?>
  </div>
 <?php else: ?>
  <div class="search-empty-state"><span class="search-empty-icon"><svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="21" cy="21" r="13"/><path d="m31 31 10 10M15 21h12M21 15v12"/></svg></span><h2>نتیجه‌ای پیدا نشد</h2><p>عبارت دیگری را جست‌وجو کنید یا محصولات فروشگاه را ببینید.</p><a class="btn" href="<?php echo esc_url(tesra_url('shop')); ?>">رفتن به فروشگاه</a></div>
 <?php endif; ?>
</section>
<?php wp_reset_postdata(); get_footer(); ?>

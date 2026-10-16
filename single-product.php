<?php
if (!defined('ABSPATH')) exit;
get_header();
global $product;
$product = wc_get_product(get_queried_object_id());
if (!$product) {
 echo '<div class="box empty"><h2>محصول پیدا نشد</h2><a class="btn" href="'.esc_url(tesra_url('shop')).'">بازگشت به فروشگاه</a></div>';
 get_footer();
 return;
}
$id=$product->get_id();
$terms=get_the_terms($id,'product_cat');
$cat=$terms&&!is_wp_error($terms)?$terms[0]:null;
$rating=(float)$product->get_average_rating();
$count=(int)$product->get_review_count();
$gallery=$product->get_gallery_image_ids();
$similar=$cat?tesra_shop_products(['limit'=>6,'category'=>[$cat->slug],'exclude'=>[$id],'orderby'=>'date','order'=>'DESC']):[];
$attributes=$product->get_attributes();
$stock_status=$product->get_stock_status();
$stock_label=$stock_status==='instock'?'موجود در انبار':($stock_status==='onbackorder'?'قابل پیش‌خرید':'ناموجود');
$star_fill = max(0, min(5, (int) round($rating ?: 5)));
$stars_str = str_repeat('★', $star_fill) . str_repeat('☆', 5 - $star_fill);
?>
<?php echo tesra_crumb([['فروشگاه',tesra_url('shop')],[$cat?$cat->name:'', $cat?get_term_link($cat):tesra_url('shop')],[$product->get_name()]]); ?>
<div class="pdp pdp-pro">
 <div class="pdp-gallery">
  <div class="gm"><img id="gi" src="<?php echo esc_url(tesra_product_image($product,'woocommerce_single')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>"></div>
  <?php if($gallery): ?><div class="th"><button data-th="0" class="on" type="button" aria-label="تصویر اصلی"><img src="<?php echo esc_url(tesra_product_image($product,'woocommerce_gallery_thumbnail')); ?>" data-full="<?php echo esc_url(tesra_product_image($product,'woocommerce_single')); ?>" alt=""></button><?php foreach(array_slice($gallery,0,5) as $k=>$image): $thumb=wp_get_attachment_image_url($image,'woocommerce_gallery_thumbnail'); $full=wp_get_attachment_image_url($image,'woocommerce_single'); if(!$thumb) continue; ?><button data-th="<?php echo esc_attr($k+1); ?>" type="button" aria-label="تصویر <?php echo esc_attr($k+2); ?>"><img src="<?php echo esc_url($thumb); ?>" data-full="<?php echo esc_url($full?:$thumb); ?>" alt=""></button><?php endforeach; ?></div><?php endif; ?>
 </div>
 <div class="pi pdp-info">
  <div class="pdp-topline"><span class="tg"><?php echo esc_html($cat?$cat->name:'محصول'); ?></span><span class="pdp-stock <?php echo esc_attr($stock_status); ?>"><i data-i="<?php echo $stock_status==='instock'?'check-circle':'alert-circle'; ?>"></i><?php echo esc_html($stock_label); ?></span></div>
  <h1><?php echo esc_html($product->get_name()); ?></h1>
  <?php if($product->get_short_description()): ?><div class="mu pdp-short"><?php echo wp_kses_post(apply_filters('woocommerce_short_description',$product->get_short_description())); ?></div><?php endif; ?>
  <a class="rt pdp-rating" href="#tp-c" data-tab="c"><span class="pdp-stars" aria-label="<?php echo esc_attr(sprintf('امتیاز %s از ۵', number_format_i18n($rating, 1))); ?>"><?php echo esc_html($stars_str); ?></span><b><?php echo esc_html(number_format_i18n($rating,1)); ?></b><span><?php echo esc_html(number_format_i18n($count)); ?> نظر ثبت‌شده</span></a>
  <div class="pb2 pdp-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
  <?php if($product->is_type('simple')): ?>
   <div class="by pdp-buy"><div class="qty"><button data-q="-1" aria-label="کم کردن تعداد" type="button"><i data-i="minus"></i></button><span id="qn">۱</span><button data-q="1" aria-label="افزایش تعداد" type="button"><i data-i="plus"></i></button></div><button class="btn" id="ad" data-add="<?php echo esc_attr($id); ?>" <?php disabled(!$product->is_purchasable()||!$product->is_in_stock()); ?>>افزودن به سبد خرید</button><?php if($product->is_purchasable()&&$product->is_in_stock()): ?><a class="btn dk2" href="<?php echo esc_url(add_query_arg('add-to-cart',$id,wc_get_checkout_url())); ?>">خرید فوری</a><?php endif; ?><button class="hb2" type="button" data-wish="<?php echo esc_attr($id); ?>" aria-label="افزودن به علاقه‌مندی‌ها"><i data-i="heart"></i></button></div>
  <?php else: ?><div class="pdp-native-cart"><?php woocommerce_template_single_add_to_cart(); ?></div><?php endif; ?>
  <div class="pdp-meta"><div><span>شناسه محصول</span><strong><?php echo esc_html($product->get_sku()?:'—'); ?></strong></div><div><span>دسته‌بندی</span><strong><?php if($terms&&!is_wp_error($terms)){foreach($terms as $i=>$term){if($i)echo '، ';echo '<a href="'.esc_url(get_term_link($term)).'">'.esc_html($term->name).'</a>';}}else{echo '—';} ?></strong></div><?php $tag_names=wc_get_product_tag_list($id,'، '); if($tag_names): ?><div><span>برچسب‌ها</span><strong><?php echo wp_kses_post($tag_names); ?></strong></div><?php endif; ?></div>
  <ul class="ft pdp-benefits"><li><i data-i="shield-check"></i>پرداخت امن و پشتیبانی سفارش</li><li><i data-i="refresh-cw"></i>امکان پیگیری و پشتیبانی محصول</li><li><i data-i="truck"></i>اطلاعات ارسال در مرحله ثبت سفارش</li></ul>
 </div>
</div>
<div class="sec pdp-details"><div class="tb" role="tablist"><button data-tab="d" class="on" type="button" role="tab" aria-selected="true">توضیحات محصول</button><button data-tab="s" type="button" role="tab" aria-selected="false">مشخصات فنی</button><button data-tab="c" type="button" role="tab" aria-selected="false">نظرات و امتیازها (<?php echo esc_html(number_format_i18n($count)); ?>)</button></div>
 <div class="tp box on" id="tp-d" role="tabpanel"><div class="pdp-description"><?php echo $product->get_description()?wp_kses_post(apply_filters('the_content',$product->get_description())):'<p>توضیحات تکمیلی برای این محصول ثبت نشده است.</p>'; ?></div></div>
 <div class="tp box" id="tp-s" role="tabpanel"><table class="sp"><tbody><?php if($product->get_sku()): ?><tr><td>شناسه محصول</td><td><?php echo esc_html($product->get_sku()); ?></td></tr><?php endif; ?><tr><td>وضعیت موجودی</td><td><?php echo esc_html($stock_label); ?></td></tr><tr><td>نوع محصول</td><td><?php echo esc_html(wc_get_product_types()[$product->get_type()]??$product->get_type()); ?></td></tr><?php foreach($attributes as $attribute): if(!$attribute->get_visible())continue; $name=wc_attribute_label($attribute->get_name(),$product); $value=$product->get_attribute($attribute->get_name()); if($value==='')continue; ?><tr><td><?php echo esc_html($name); ?></td><td><?php echo esc_html($value); ?></td></tr><?php endforeach; ?><?php if(!$product->get_sku()&&!array_filter($attributes,static fn($a)=>$a->get_visible()&&$product->get_attribute($a->get_name())!=='')): ?><tr><td colspan="2">مشخصات فنی جداگانه‌ای برای این محصول ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div>
 <div class="tp box" id="tp-c" role="tabpanel"><?php comments_template(); ?></div>
</div>
<?php if($similar): ?><div class="sec"><div class="sh"><div class="ttl"><h2>محصولات مشابه</h2></div><div class="arr"><button data-rail="rrel" aria-label="قبلی" type="button"><i data-i="chevR"></i></button><button data-rail="rrel" aria-label="بعدی" type="button"><i data-i="chevL"></i></button></div></div><div class="rail" id="rrel"><?php foreach($similar as $p) echo tesra_product_card($p); ?></div></div><?php endif; ?>
<?php get_footer(); ?>

<?php if (!defined('ABSPATH')) exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class('tesra-body'); ?>>
<?php wp_body_open(); ?>
<div class="tesra-page-loader" id="tesra-page-loader" role="status" aria-live="polite" aria-label="در حال بارگذاری">
 <div class="tesra-loader-emblem"><span class="tesra-loader-ring"></span><span class="mark"></span></div>
 <strong>تسرا</strong><span class="tesra-loader-caption">در حال آماده‌سازی تجربه خرید شما</span><div class="tesra-loader-track"><i></i></div>
</div>
<script>
(function(){
 var done=false;
 function closeTesraLoader(){
  if(done)return;
  var loader=document.getElementById('tesra-page-loader');
  if(!loader){done=true;return;}
  done=true;
  loader.setAttribute('data-closing','1');
  loader.classList.add('is-hidden');
  loader.setAttribute('aria-hidden','true');
  window.setTimeout(function(){if(loader.parentNode)loader.parentNode.removeChild(loader);},450);
 }
 if(document.readyState==='complete')window.setTimeout(closeTesraLoader,250);
 else window.addEventListener('load',function(){window.setTimeout(closeTesraLoader,250);},{once:true});
 window.addEventListener('pageshow',function(){window.setTimeout(closeTesraLoader,250);});
 window.setTimeout(closeTesraLoader,6500);
})();
</script>
<?php if(function_exists('elementor_theme_do_location') && elementor_theme_do_location('header')): ?>
<?php else: ?>
<div class="top"><div class="wrap top-benefits"><span><i data-i="truck"></i><span class="top-benefit-copy"><b>ارسال سریع</b><small>تحویل سریع سفارش‌ها</small></span></span><span><i data-i="chat"></i><span class="top-benefit-copy"><b>پشتیبانی واقعی</b><small>پاسخ‌گویی سریع به مشتریان</small></span></span><span><i data-i="shield"></i><span class="top-benefit-copy"><b>ضمانت اصالت</b><small>اطمینان از کیفیت و سلامت کالا</small></span></span><span><i data-i="refresh"></i><span class="top-benefit-copy"><b>بازگشت وجه</b><small>طبق شرایط فروشگاه</small></span></span></div></div>
<header class="wrap">
<div class="hd">
<a class="logo" href="<?php echo esc_url(home_url('/')); ?>"><?php if(has_custom_logo()){the_custom_logo();}else{echo '<span class="mark"></span><span>تسرا</span>';} ?></a>
<form class="search" id="sf" role="search" action="<?php echo esc_url(tesra_url('shop')); ?>" method="get">
<span class="search-decor" aria-hidden="true"><span class="search-decor-icon"><svg viewBox="0 0 24 24"><path d="M12 2.8 14.2 9.8 21.2 12l-7 2.2-2.2 7-2.2-7L2.8 12l7-2.2L12 2.8Z"/><path d="m19 2 .7 2.3L22 5l-2.3.7L19 8l-.7-2.3L16 5l2.3-.7L19 2Z"/></svg></span><span class="search-decor-copy">جستجوی سریع</span></span>
<input type="search" name="tesra_search" value="<?php echo (isset($_GET['tesra_search'])&&is_string($_GET['tesra_search']))?esc_attr(sanitize_text_field(wp_unslash($_GET['tesra_search']))):((isset($_GET['s'])&&is_string($_GET['s']))?esc_attr(get_search_query()):''); ?>" placeholder="جستجوی محصول، برند یا دسته‌بندی…">
<button class="sb" type="submit" aria-label="جستجو"><i data-i="search"></i></button>
</form>
<div class="acts">
<a class="pill" href="<?php echo esc_url(is_user_logged_in()?tesra_url('account'):tesra_url('login')); ?>"><i data-i="user"></i><span><?php echo is_user_logged_in()?esc_html(wp_get_current_user()->display_name):'ورود / ثبت‌نام'; ?></span></a>
<a class="cartb" id="cartb" href="<?php echo esc_url(tesra_url('checkout')); ?>" aria-label="رفتن به پرداخت"><span class="cico"><i data-i="cart"></i><b id="cc"><?php echo esc_html(number_format_i18n(tesra_cart_count())); ?></b></span><span class="ct2"><small>سبد خرید</small><strong id="ct"><?php $tesraCart=tesra_cart_instance(); echo esc_html($tesraCart?tesra_money($tesraCart->get_subtotal()):'۰ تومان'); ?></strong></span></a>
</div>
</div>
<div class="navr">
<div class="category-menu" id="tesra-category-menu">
<button class="allc" data-category-toggle type="button" aria-expanded="false" aria-controls="tesra-category-mega"><i data-i="menu"></i><span>دسته‌بندی محصولات</span></button>
<div class="category-mega" id="tesra-category-mega" hidden>
<div class="category-mega-head"><div><strong>دسته‌بندی محصولات</strong><span>دسته اصلی را انتخاب کنید تا زیرمجموعه‌ها نمایش داده شوند</span></div><a href="<?php echo esc_url(tesra_url('shop')); ?>">مشاهده همه محصولات ←</a></div>
<div class="category-mega-grid">
<div class="category-parent-list" role="tablist" aria-label="دسته‌های اصلی">
<?php $tesra_roots=tesra_categories(); foreach($tesra_roots as $i=>$term): ?><button class="category-parent<?php echo $i===0?' is-active':''; ?>" type="button" role="tab" data-mega-parent="<?php echo (int)$term->term_id; ?>" aria-selected="<?php echo $i===0?'true':'false'; ?>"><span class="category-dot"><i data-i="bag"></i></span><span class="category-parent-copy"><b><?php echo esc_html($term->name); ?></b><small><?php echo esc_html(number_format_i18n($term->count)); ?> محصول</small></span><span class="category-chevron">‹</span></button><?php endforeach; ?>
</div>
<div class="category-sub-panels">
<?php foreach($tesra_roots as $i=>$term): $term_link=get_term_link($term); if(is_wp_error($term_link)) continue; $sub_links=tesra_category_descendant_links($term->term_id); ?>
<section class="category-sub-panel<?php echo $i===0?' is-active':''; ?>" data-mega-panel="<?php echo (int)$term->term_id; ?>" role="tabpanel" <?php echo $i===0?'':'hidden'; ?>>
<div class="category-sub-heading"><a href="<?php echo esc_url($term_link); ?>"><strong><?php echo esc_html($term->name); ?></strong><span>مشاهده همه این دسته ←</span></a></div>
<?php if($sub_links): ?><div class="category-sub-list"><?php echo $sub_links; ?></div><?php else: ?><div class="category-sub-empty">زیر‌دسته‌ای برای این گروه ثبت نشده است.</div><?php endif; ?>
</section><?php endforeach; ?>
</div></div></div></div>
<nav><?php
if(has_nav_menu('primary')){
 wp_nav_menu(['theme_location'=>'primary','container'=>false,'items_wrap'=>'%3$s','fallback_cb'=>false]);
 $locations=get_nav_menu_locations();$menu_items=isset($locations['primary'])?wp_get_nav_menu_items($locations['primary']):[];$has_articles=false;
 foreach((array)$menu_items as $menu_item){if(strpos((string)$menu_item->url,'/articles')!==false||strpos((string)$menu_item->url,'مقالات')!==false||strpos((string)$menu_item->title,'مقالات')!==false){$has_articles=true;break;}}
 if(!$has_articles) echo '<a href="'.esc_url(tesra_url('articles')).'">مقالات</a>';
}
else{echo '<a href="'.esc_url(home_url('/')).'">صفحه اصلی</a><a href="'.esc_url(tesra_url('shop')).'">فروشگاه</a><a href="'.esc_url(add_query_arg('orderby','popularity',tesra_url('shop'))).'">پرفروش‌ترین‌ها</a><a href="'.esc_url(add_query_arg('orderby','rating',tesra_url('shop'))).'">محبوب‌ترین‌ها</a><a href="'.esc_url(add_query_arg('orderby','date',tesra_url('shop'))).'">جدیدترین‌ها</a><a href="'.esc_url(tesra_url('articles')).'">مقالات</a><a href="'.esc_url(tesra_url('about')).'">درباره ما</a><a href="'.esc_url(tesra_url('contact')).'">تماس با ما</a>';}
?></nav>
<a class="off" href="<?php echo esc_url(add_query_arg('on_sale','1',tesra_url('shop'))); ?>"><i data-i="bolt"></i>پیشنهادهای ویژه این هفته</a>
</div>
</header>
<?php endif; ?>
<main class="wrap" id="app">

<?php if (!defined('ABSPATH')) exit; get_header();
$term=get_queried_object();
$search_term=(isset($_GET['tesra_search'])&&is_string($_GET['tesra_search']))?sanitize_text_field(wp_unslash($_GET['tesra_search'])):(is_search()?get_search_query():'');
$title=is_tax('product_cat')?$term->name:($search_term!==''?'نتایج جستجو برای «'.$search_term.'»':'فروشگاه');
$args=['status'=>'publish','limit'=>100,'orderby'=>'date','order'=>'DESC'];
if(is_tax('product_cat')) $args['category']=[$term->slug];
if(isset($_GET['product_cat'])&&!is_tax('product_cat')){
 $cat=sanitize_title(wp_unslash($_GET['product_cat']));
 if($cat) $args['category']=[$cat];
}
if(isset($_GET['orderby'])){
 $o=sanitize_text_field(wp_unslash($_GET['orderby']));
 if($o==='popularity'){$args['orderby']='popularity';$args['order']='DESC';}
 if($o==='rating'){$args['orderby']='rating';$args['order']='DESC';}
 if($o==='price'){$args['orderby']='price';$args['order']='ASC';}
 if($o==='price-desc'){$args['orderby']='price';$args['order']='DESC';}
 if($o==='date'){$args['orderby']='date';$args['order']='DESC';}
}
if(isset($_GET['on_sale'])) $args['on_sale']=true;
if(isset($_GET['stock_status']) && $_GET['stock_status']==='instock') $args['stock_status']='instock';
if(isset($_GET['min_price']) && is_numeric($_GET['min_price'])) $args['min_price']=max(0,(float)wp_unslash($_GET['min_price']));
if(isset($_GET['max_price']) && is_numeric($_GET['max_price'])) $args['max_price']=max(0,(float)wp_unslash($_GET['max_price']));
if(isset($_GET['rating_filter']) && (int)$_GET['rating_filter']>0){
 $args['meta_query']=[['key'=>'_wc_average_rating','value'=>min(5,max(1,(int)$_GET['rating_filter'])),'type'=>'DECIMAL(3,2)','compare'=>'>=']];
}
if($search_term!=='') $args['s']=$search_term;
$products=tesra_shop_products($args);
$cats=tesra_categories();
$max=0; foreach($products as $p) $max=max($max,(float)$p->get_price());
$max=ceil(max(1000000,$max)/10000)*10000;
$current_cat=is_tax('product_cat')?$term->slug:(isset($_GET['product_cat'])?sanitize_title(wp_unslash($_GET['product_cat'])):'');
?>
<?php echo tesra_crumb([['فروشگاه',tesra_url('shop')],[$title]]); ?>
<div class="shop-head"><h1 class="ph"><?php echo esc_html($title); ?></h1><button class="filter-trigger" type="button" data-modal="filters"><i data-i="grid"></i><span>فیلترها</span></button></div>
<div class="lay">
<aside class="fb">
 <div>
  <h4>دسته‌بندی</h4>
  <a class="cl<?php echo is_shop()&&!$current_cat?' on':''; ?>" href="<?php echo esc_url(tesra_url('shop')); ?>"><span>همه محصولات</span><span><?php echo esc_html(array_sum(array_map(fn($x)=>(int)$x->count,$cats))); ?></span></a>
  <?php foreach($cats as $c): ?><a class="cl<?php echo $current_cat===$c->slug?' on':''; ?>" href="<?php echo esc_url(get_term_link($c)); ?>"><span><?php echo esc_html($c->name); ?></span><span><?php echo esc_html($c->count); ?></span></a><?php endforeach; ?>
 </div>
 <form class="desktop-filter" method="get" action="<?php echo esc_url(tesra_url('shop')); ?>">
  <?php if($search_term!==''): ?><input type="hidden" name="tesra_search" value="<?php echo esc_attr($search_term); ?>"><?php endif; ?>
  <?php if($current_cat): ?><input type="hidden" name="product_cat" value="<?php echo esc_attr($current_cat); ?>"><?php endif; ?>
  <div><h4>حداکثر قیمت</h4><input type="number" name="max_price" min="0" step="10000" value="<?php echo isset($_GET['max_price'])?esc_attr(wp_unslash($_GET['max_price'])):''; ?>" placeholder="مثلاً ۵۰٬۰۰۰٬۰۰۰"></div>
  <div><h4>حداقل قیمت</h4><input type="number" name="min_price" min="0" step="10000" value="<?php echo isset($_GET['min_price'])?esc_attr(wp_unslash($_GET['min_price'])):''; ?>" placeholder="مثلاً ۵۰۰٬۰۰۰"></div>
  <label class="chk"><input type="checkbox" name="on_sale" value="1" <?php checked(isset($_GET['on_sale'])); ?>> فقط کالاهای تخفیف‌دار</label>
  <label class="chk"><input type="checkbox" name="stock_status" value="instock" <?php checked(isset($_GET['stock_status'])&&$_GET['stock_status']==='instock'); ?>> فقط کالاهای موجود</label>
  <label class="fld">حداقل امتیاز<select name="rating_filter"><option value="">همه امتیازها</option><?php for($r=5;$r>=3;$r--): ?><option value="<?php echo $r; ?>" <?php selected((int)($_GET['rating_filter']??0),$r); ?>><?php echo esc_html(number_format_i18n($r)); ?> به بالا</option><?php endfor; ?></select></label>
  <label class="fld">مرتب‌سازی<select name="orderby"><option value="date">جدیدترین</option><option value="popularity" <?php selected($_GET['orderby']??'','popularity'); ?>>پرفروش‌ترین</option><option value="rating" <?php selected($_GET['orderby']??'','rating'); ?>>بیشترین امتیاز</option><option value="price" <?php selected($_GET['orderby']??'','price'); ?>>ارزان‌ترین</option><option value="price-desc" <?php selected($_GET['orderby']??'','price-desc'); ?>>گران‌ترین</option></select></label>
  <button class="btn sm" type="submit">اعمال فیلتر</button>
 </form>
</aside>
<div><div class="rs" id="cnt"></div><?php if($products): ?><div class="grid" id="lg"><?php foreach($products as $p) echo tesra_product_card($p); ?></div><div class="tesra-pager" id="tesra-pager" aria-label="صفحه‌بندی محصولات"></div><?php else: ?><div class="search-empty-state"><span class="search-empty-icon"><svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="21" cy="21" r="13"/><path d="m31 31 10 10M15 21h12M21 15v12"/></svg></span><h2>محصولی پیدا نشد</h2><p><?php echo $search_term!==''?'عبارت جست‌وجو را تغییر دهید یا املای آن را بررسی کنید.':'این بخش هنوز محصولی ندارد.'; ?></p><a class="btn" href="<?php echo esc_url(tesra_url('shop')); ?>">نمایش همه محصولات</a></div><?php endif; ?></div>
</div>
<?php get_footer(); ?>

<?php
if (!defined('ABSPATH')) exit;

define('TESRA_VERSION','1.8.6');
define('TESRA_DEMO_CATALOG_VERSION',4);


function tesra_setup(){
 add_theme_support('title-tag');
 add_theme_support('post-thumbnails');
 add_theme_support('custom-logo', ['height'=>56,'width'=>180,'flex-height'=>true,'flex-width'=>true]);
 add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
 add_theme_support('woocommerce',['thumbnail_image_width'=>360,'gallery_thumbnail_image_width'=>100,'single_image_width'=>760,'product_grid'=>['default_rows'=>3,'min_rows'=>2,'max_rows'=>8,'default_columns'=>4,'min_columns'=>2,'max_columns'=>6]]);
 add_theme_support('wc-product-gallery-zoom');
 add_theme_support('wc-product-gallery-lightbox');
 add_theme_support('wc-product-gallery-slider');
 add_theme_support('elementor');
 add_theme_support('elementor-pro-theme-builder');
 register_nav_menus(['primary'=>'منوی اصلی']);
}
add_action('after_setup_theme','tesra_setup');

function tesra_assets(){
 wp_enqueue_style('tesra-vazir-font','https://cdnjs.cloudflare.com/ajax/libs/vazir-font/30.0.0/font-face.css',[], '30.0.0');
 wp_enqueue_style('tesra-main',get_template_directory_uri().'/assets/css/tessera.css',['tesra-vazir-font'],TESRA_VERSION);
 wp_enqueue_style('tesra-wp',get_stylesheet_uri(),['tesra-main'],TESRA_VERSION);
 wp_enqueue_script('tesra',get_template_directory_uri().'/assets/js/tessera.js',['jquery'],TESRA_VERSION,true);
 if (class_exists('WooCommerce')) {
  wp_enqueue_script('wc-add-to-cart');
  wp_enqueue_script('wc-cart-fragments');
 }
 wp_localize_script('tesra','tesraData',[
  'ajax'=>admin_url('admin-ajax.php'),
  'nonce'=>wp_create_nonce('tesra_nonce'),
  'cart'=>function_exists('wc_get_cart_url')?wc_get_cart_url():home_url('/cart/'),
  'checkout'=>function_exists('wc_get_checkout_url')?wc_get_checkout_url():home_url('/checkout/'),
  'shop'=>function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/'),
  'account'=>function_exists('wc_get_page_permalink')?wc_get_page_permalink('myaccount'):home_url('/account/'),
  'themeUrl'=>get_template_directory_uri(),
  'currentCategory'=>(is_tax('product_cat')?get_queried_object()->slug:(isset($_GET['product_cat'])?sanitize_title(wp_unslash($_GET['product_cat'])):'')),
  'categories'=>array_map(function($t){return ['name'=>$t->name,'slug'=>$t->slug,'url'=>get_term_link($t),'count'=>(int)$t->count];},tesra_categories()),
  'filters'=>[
   'min_price'=>isset($_GET['min_price'])?(float)wp_unslash($_GET['min_price']):'',
   'max_price'=>isset($_GET['max_price'])?(float)wp_unslash($_GET['max_price']):'',
   'sale'=>isset($_GET['on_sale'])?'1':'',
   'stock'=>isset($_GET['stock_status'])?sanitize_text_field(wp_unslash($_GET['stock_status'])):'',
   'rating'=>isset($_GET['rating_filter'])?(int)$_GET['rating_filter']:'',
   'orderby'=>isset($_GET['orderby'])?sanitize_text_field(wp_unslash($_GET['orderby'])):''
  ]
 ]);
}
add_action('wp_enqueue_scripts','tesra_assets');

function tesra_normalize_product_search_url(){
 if(!is_search()) return;
 $requested=get_query_var('post_type');
 $is_product=($requested==='product')||(is_array($requested)&&in_array('product',$requested,true));
 if(!$is_product&&isset($_GET['post_type'])&&is_string($_GET['post_type'])) $is_product=sanitize_key(wp_unslash($_GET['post_type']))==='product';
 if(!$is_product) return;
 $query=trim(sanitize_text_field(get_search_query(false)));
 if($query==='') return;
 $shop=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');
 wp_safe_redirect(add_query_arg('tesra_search',$query,$shop),302,'Tessera Product Search');
 exit;
}
add_action('template_redirect','tesra_normalize_product_search_url',1);


function tesra_page($slug,$title,$content=''){
 $p=get_page_by_path($slug);
 if(!$p){
  $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$content]);
  return $id;
 }
 return $p->ID;
}

function tesra_after_switch(){
 tesra_page('about','درباره ما');
 tesra_page('contact','تماس با ما');
 tesra_page('faq','سؤالات متداول');
 tesra_page('terms','قوانین و شرایط بازگشت وجه');
 tesra_page('login','ورود / ثبت‌نام');
 tesra_page('account','حساب کاربری');
 tesra_page('articles','مقالات');
 tesra_page('admin','پنل مدیریت');
 if(class_exists('WooCommerce')){
  $shop=tesra_page('shop','فروشگاه');
  $cart=tesra_page('cart','سبد خرید','[woocommerce_cart]');
  $checkout=tesra_page('checkout','ثبت سفارش','[woocommerce_checkout]');
  $account=tesra_page('account','حساب کاربری','');
  update_option('woocommerce_shop_page_id',$shop);
  update_option('woocommerce_cart_page_id',$cart);
  update_option('woocommerce_checkout_page_id',$checkout);
  update_option('woocommerce_myaccount_page_id',$account);
 }
 flush_rewrite_rules();
}
add_action('after_switch_theme','tesra_after_switch');

function tesra_demo_asset($path){
 return get_template_directory_uri().'/assets/demo/'.ltrim($path,'/');
}

function tesra_demo_image_id($file,$title=''){
 $key='tesra_demo_media_'.md5($file);
 $cached=(int)get_option($key,0);
 if($cached&&get_post($cached)) return $cached;
 $src=get_template_directory().'/assets/demo/'.ltrim($file,'/');
 if(!file_exists($src)) return 0;
 $up=wp_upload_dir();
 $dir=trailingslashit($up['basedir']).'tesra-demo';
 wp_mkdir_p($dir);
 $dest=trailingslashit($dir).basename($file);
 if(!file_exists($dest)) copy($src,$dest);
 $url=trailingslashit($up['baseurl']).'tesra-demo/'.basename($file);
 $ft=wp_check_filetype(basename($file),null);
 $att=['guid'=>$url,'post_mime_type'=>$ft['type']?:'image/png','post_title'=>$title?:preg_replace('/\.[^.]+$/','',basename($file)),'post_status'=>'inherit'];
 $id=wp_insert_attachment($att,$dest);
 if(is_wp_error($id)) return 0;
 require_once ABSPATH.'wp-admin/includes/image.php';
 $meta=wp_generate_attachment_metadata($id,$dest);
 if($meta) wp_update_attachment_metadata($id,$meta);
 update_option($key,$id,false);
 return $id;
}

function tesra_demo_media($key,$url,$title=''){
 $opt='tesra_demo_media_url_'.sanitize_key($key);
 $cached=(int)get_option($opt,0);
 if($cached&&get_post($cached)) return $cached;
 require_once ABSPATH.'wp-admin/includes/file.php';
 require_once ABSPATH.'wp-admin/includes/media.php';
 require_once ABSPATH.'wp-admin/includes/image.php';
 $tmp=download_url($url,30);
 if(is_wp_error($tmp)) return 0;
 $file=['name'=>sanitize_file_name($key).'.jpg','tmp_name'=>$tmp];
 $id=media_handle_sideload($file,0,$title?:$key);
 if(is_wp_error($id)){@unlink($tmp);return 0;}
 update_option($opt,$id,false);
 return $id;
}

function tesra_demo_media_url($key,$url,$title='',$size='large'){
 $id=tesra_demo_media($key,$url,$title);
 return $id?wp_get_attachment_image_url($id,$size):$url;
}

function tesra_seed_demo_content(){
 if(!class_exists('WooCommerce')) return;
 if((int)get_option('tesra_demo_catalog_version',0)!==TESRA_DEMO_CATALOG_VERSION){
  $ids=get_posts(['post_type'=>'product','post_status'=>'any','numberposts'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'_sku','value'=>'TESRA-DEMO-','compare'=>'LIKE']]]);
  foreach($ids as $id) wp_delete_post($id,true);
  foreach(['shop','portfolio','blog','dashboard','landing','plugin','corporate'] as $old){$term=get_term_by('slug',$old,'product_cat');if($term&&$term->count===0) wp_delete_term($term->term_id,'product_cat');}
  update_option('tesra_demo_catalog_version',TESRA_DEMO_CATALOG_VERSION,false);
  delete_option('tesra_demo_seeded');
 }
 if(get_option('tesra_demo_seeded')) return;
 $cats=[
  'electronics'=>'موبایل و تبلت',
  'computers'=>'لپ‌تاپ و کامپیوتر',
  'fashion'=>'پوشاک',
  'shoes-bags'=>'کفش و کیف',
  'home-appliances'=>'لوازم خانگی',
  'audio-video'=>'صوتی و تصویری',
  'home-kitchen'=>'خانه و آشپزخانه',
  'beauty'=>'زیبایی و بهداشت'
 ];
 $cat_ids=[];
 foreach($cats as $slug=>$name){
  $term=term_exists($name,'product_cat');
  if(!$term) $term=wp_insert_term($name,'product_cat',['slug'=>$slug]);
  if(!is_wp_error($term)) $cat_ids[$slug]=(int)(is_array($term)?$term['term_id']:$term);
 }
 $items=[
  ['گوشی هوشمند آریا X12','نمایشگر ۶.۵ اینچ · حافظه ۲۵۶ گیگابایت','electronics',18900000,16900000,4.8,224,'phone'],
  ['گوشی نوا Pro 5G','دوربین حرفه‌ای · شارژ سریع','electronics',26400000,23900000,4.9,187,'phone2'],
  ['هدفون بی‌سیم آریو','حذف نویز · بلوتوث ۵.۳','audio-video',4200000,3490000,4.7,143,'headphones'],
  ['لپ‌تاپ نوت‌بوک Air 15','رم ۱۶ گیگابایت · SSD ۵۱۲ گیگابایت','computers',42900000,39900000,4.8,96,'laptop'],
  ['تی‌شرت پنبه‌ای کلاسیک','پارچه نخ پنبه · مناسب استفاده روزمره','fashion',890000,690000,4.7,318,'shirt'],
  ['کتانی شهری Urban One','رویه تنفس‌پذیر · کفی نرم','shoes-bags',3180000,2790000,4.9,201,'shoes'],
  ['یخچال فریزر نوردیک ۲۸ فوت','کم‌مصرف · طراحی استیل','home-appliances',68900000,64900000,4.8,68,'fridge'],
  ['ماشین لباسشویی ۹ کیلویی','موتور کم‌صدا · ۱۴ برنامه شستشو','home-appliances',35800000,32900000,4.7,84,'washer'],
  ['تلویزیون هوشمند ۵۵ اینچ','4K · سیستم عامل هوشمند','audio-video',28900000,25900000,4.8,131,'tv'],
  ['اسپرسوساز خانگی Barista','پمپ ۱۵ بار · نازل بخار','home-kitchen',12900000,10900000,4.9,116,'coffee'],
  ['جاروبرقی ایستاده Vento','فیلتر HEPA · مکش قدرتمند','home-appliances',11900000,9900000,4.6,91,'washer'],
  ['مانیتور ۲۷ اینچ ProView','QHD · نرخ نوسازی ۱۴۴ هرتز','computers',17800000,15900000,4.8,77,'laptop'],
  ['هودی مینیمال مشکی','پارچه دورس · دوخت تقویت‌شده','fashion',1490000,1190000,4.8,154,'shirt'],
  ['کفش اسپرت روزمره','سبک و مناسب پیاده‌روی','shoes-bags',2450000,2190000,4.7,142,'shoes'],
  ['ست مبل راحتی سه‌نفره','پارچه مقاوم · طراحی مدرن','home-kitchen',45900000,42900000,4.9,54,'sofa'],
  ['ساندبار سینمای خانگی','صدای فراگیر · اتصال بی‌سیم','audio-video',14900000,13200000,4.8,72,'tv'],
  ['تبلت ۱۱ اینچ NovaTab','۱۲۸ گیگابایت · قلم هوشمند','electronics',14900000,13200000,4.7,103,'phone2'],
  ['مخلوط‌کن شیشه‌ای ProMix','توان ۱۰۰۰ وات · پارچ شیشه‌ای','home-kitchen',5890000,4990000,4.8,119,'coffee'],
  ['کوله‌پشتی شهری ضدآب','جای لپ‌تاپ · مناسب سفر و کار','shoes-bags',1890000,1590000,4.9,174,'laptop'],
  ['ماشین اصلاح چندکاره','بدنه مقاوم · شارژ USB-C','beauty',2890000,2390000,4.7,165,'headphones'],
  ['گوشی هوشمند Vision 14','نمایشگر OLED · حافظه ۵۱۲ گیگابایت','electronics',32900000,29900000,4.9,142,'phone'],
  ['ساعت هوشمند Fit Max','پایش فعالیت · نمایشگر AMOLED','electronics',7690000,6790000,4.8,133,'phone2'],
  ['کیبورد مکانیکال Nova','نورپردازی · سوییچ نرم','computers',4980000,4290000,4.7,88,'laptop'],
  ['ماوس بی‌سیم Pro Mouse','سنسور دقیق · اتصال دوگانه','computers',2190000,1890000,4.8,174,'headphones'],
  ['شلوار جین کلاسیک','دنیم باکیفیت · برش راحت','fashion',1690000,1390000,4.7,206,'shirt'],
  ['کتانی رانینگ AirStep','سبک · مناسب دویدن و پیاده‌روی','shoes-bags',3890000,3390000,4.9,187,'shoes'],
  ['چمدان مسافرتی ۲۸ اینچ','بدنه مقاوم · چهار چرخ روان','shoes-bags',4290000,3790000,4.8,91,'laptop'],
  ['ماشین ظرفشویی ۱۴ نفره','برنامه اقتصادی · کم‌صدا','home-appliances',41900000,38900000,4.8,73,'washer'],
  ['مایکروویو دیجیتال','توان ۱۰۰۰ وات · پنل لمسی','home-kitchen',12900000,10900000,4.7,84,'coffee'],
  ['پلوپز چندکاره هوشمند','۸ برنامه پخت · دیگ نچسب','home-kitchen',5890000,5190000,4.8,112,'coffee'],
  ['پروژکتور خانگی Full HD','تصویر بزرگ · اسپیکر داخلی','audio-video',11900000,9990000,4.6,64,'tv'],
  ['دوربین دیجیتال Compact','لنز زوم · فیلم‌برداری 4K','audio-video',22900000,20900000,4.8,59,'phone2'],
  ['سرم آبرسان پوست','ترکیبات سبک · مناسب مصرف روزانه','beauty',1280000,1090000,4.9,221,'shirt'],
  ['کرم ضدآفتاب روزانه','محافظت بالا · بافت سبک','beauty',890000,760000,4.8,267,'headphones']
 ];
 foreach($items as $i=>$row){
  [$name,$sub,$cat,$regular,$sale,$rating,$sales,$img]=$row;
  $sku='TESRA-DEMO-'.($i+1);
  $p=new WC_Product_Simple();
  $p->set_name($name);$p->set_status('publish');$p->set_catalog_visibility('visible');$p->set_sku($sku);
  $p->set_regular_price((string)$regular);if($sale)$p->set_sale_price((string)$sale);
  $p->set_virtual(false);$p->set_stock_status('instock');$p->set_manage_stock(false);$p->set_total_sales($sales);
  $p->set_description('<p>'.$sub.'؛ محصول نمایشی برای بررسی فروشگاه تسرا.</p><p>قیمت، مشخصات و موجودی این محصول از پنل فروشگاه قابل تغییر است.</p>');
  $p->set_short_description($sub);$p->set_category_ids([$cat_ids[$cat]]);
  $id=$p->save();
  if($id){
   $urls=[
    'phone'=>'https://images.pexels.com/photos/12882840/pexels-photo-12882840.png?cs=srgb&dl=pexels-altman-12882840.jpg&fm=jpg',
    'phone2'=>'https://images.pexels.com/photos/8408537/pexels-photo-8408537.jpeg?cs=srgb&dl=pexels-sarahdorweiler-8408537.jpg&fm=jpg',
    'headphones'=>'https://images.pexels.com/photos/12799296/pexels-photo-12799296.jpeg?cs=srgb&dl=pexels-omar-ramadan-1739260-12799296.jpg&fm=jpg',
    'coffee'=>'https://images.pexels.com/photos/4993062/pexels-photo-4993062.jpeg?cs=srgb&dl=pexels-rachel-claire-4993062.jpg&fm=jpg',
    'shoes'=>'https://images.pexels.com/photos/20298288/pexels-photo-20298288.png?cs=srgb&dl=pexels-sole-heaven-1013230921-20298288.jpg&fm=jpg',
    'shirt'=>'https://images.pexels.com/photos/18186105/pexels-photo-18186105.jpeg?cs=srgb&dl=pexels-dayong-tien-681073045-18186105.jpg&fm=jpg',
    'washer'=>'https://images.pexels.com/photos/8146199/pexels-photo-8146199.jpeg?cs=srgb&dl=pexels-artbovich-8146199.jpg&fm=jpg',
    'laptop'=>'https://images.pexels.com/photos/23228196/pexels-photo-23228196.jpeg?cs=srgb&dl=pexels-olenkabohovyk-23228196.jpg&fm=jpg',
    'fridge'=>'https://images.pexels.com/photos/7168108/pexels-photo-7168108.jpeg?cs=srgb&dl=pexels-curtis-adams-1694007-7168108.jpg&fm=jpg',
    'sofa'=>'https://images.pexels.com/photos/8092165/pexels-photo-8092165.jpeg?cs=srgb&dl=pexels-artbovich-8092165.jpg&fm=jpg',
    'tv'=>'https://images.pexels.com/photos/11701160/pexels-photo-11701160.jpeg?cs=srgb&dl=pexels-artbovich-11701160.jpg&fm=jpg'
   ];
   $key='product-'.$img.'-'.$i;
   $attachment=tesra_demo_media($key,$urls[$img],$name);
   if($attachment) set_post_thumbnail($id,$attachment);
   update_post_meta($id,'_wc_average_rating',(string)$rating);
   update_post_meta($id,'_wc_review_count',(string)max(12,(int)round($sales*.18)));
  }
 }
 $banner_urls=[
  'hero'=>'https://images.pexels.com/photos/7168108/pexels-photo-7168108.jpeg?cs=srgb&dl=pexels-curtis-adams-1694007-7168108.jpg&fm=jpg',
  'promo'=>'https://images.pexels.com/photos/23228196/pexels-photo-23228196.jpeg?cs=srgb&dl=pexels-olenkabohovyk-23228196.jpg&fm=jpg'
 ];
 tesra_demo_media('hero-banner',$banner_urls['hero'],'بنر اصلی فروشگاه');
 tesra_demo_media('promo-banner',$banner_urls['promo'],'بنر تبلیغاتی فروشگاه');
 update_option('tesra_demo_seeded',1,false);
}
add_action('init','tesra_seed_demo_content',20);

function tesra_seed_demo_subcategories(){
 if(!class_exists('WooCommerce')||(int)get_option('tesra_demo_subcategory_version',0)>=1) return;
 $defs=[
  'electronics'=>[
   ['mobile-phones','گوشی موبایل',['TESRA-DEMO-1','TESRA-DEMO-2','TESRA-DEMO-21']],
   ['tablets','تبلت',['TESRA-DEMO-17']],
   ['smartwatches','ساعت هوشمند',['TESRA-DEMO-22']],
   ['mobile-accessories','لوازم جانبی موبایل',[]]
  ],
  'computers'=>[
   ['laptops','لپ‌تاپ',['TESRA-DEMO-4']],
   ['monitors','مانیتور',['TESRA-DEMO-12']],
   ['computer-accessories','لوازم جانبی کامپیوتر',['TESRA-DEMO-23','TESRA-DEMO-24']]
  ],
  'fashion'=>[
   ['menswear','پوشاک مردانه',['TESRA-DEMO-13','TESRA-DEMO-25']],
   ['casual-clothing','تی‌شرت و لباس روزمره',['TESRA-DEMO-5']],
   ['sportswear','پوشاک ورزشی',[]]
  ],
  'shoes-bags'=>[
   ['sneakers','کفش اسپرت',['TESRA-DEMO-6','TESRA-DEMO-14','TESRA-DEMO-26']],
   ['bags-backpacks','کیف و کوله‌پشتی',['TESRA-DEMO-19']],
   ['luggage','چمدان مسافرتی',['TESRA-DEMO-27']]
  ],
  'home-appliances'=>[
   ['refrigerators','یخچال و فریزر',['TESRA-DEMO-7']],
   ['washing-machines','ماشین لباسشویی',['TESRA-DEMO-8']],
   ['vacuum-cleaners','جاروبرقی',['TESRA-DEMO-11']],
   ['dishwashers','ماشین ظرفشویی',['TESRA-DEMO-28']]
  ],
  'audio-video'=>[
   ['televisions-projectors','تلویزیون و ویدئوپروژکتور',['TESRA-DEMO-9','TESRA-DEMO-31']],
   ['headphones','هدفون',['TESRA-DEMO-3']],
   ['speakers-soundbars','اسپیکر و ساندبار',['TESRA-DEMO-16']],
   ['digital-cameras','دوربین دیجیتال',['TESRA-DEMO-32']]
  ],
  'home-kitchen'=>[
   ['kitchen-appliances','لوازم آشپزخانه',['TESRA-DEMO-18','TESRA-DEMO-29','TESRA-DEMO-30']],
   ['coffee-makers','قهوه‌ساز و اسپرسوساز',['TESRA-DEMO-10']],
   ['furniture','مبلمان و دکوراسیون',['TESRA-DEMO-15']]
  ],
  'beauty'=>[
   ['skincare','مراقبت از پوست',['TESRA-DEMO-33','TESRA-DEMO-34']],
   ['personal-care','لوازم بهداشت شخصی',['TESRA-DEMO-20']]
  ]
 ];
 $sku_to_product=[];
 $products=get_posts(['post_type'=>'product','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'_sku','value'=>'TESRA-DEMO-','compare'=>'LIKE']]]);
 foreach($products as $pid){$sku=(string)get_post_meta($pid,'_sku',true);if($sku!=='')$sku_to_product[$sku]=$pid;}
 foreach($defs as $root_slug=>$children){
  $parent=get_term_by('slug',$root_slug,'product_cat');
  if(!$parent||is_wp_error($parent)) continue;
  foreach($children as $child){
   [$slug,$name,$skus]=$child;
   $term=get_term_by('slug',$slug,'product_cat');
   if(!$term){$created=wp_insert_term($name,'product_cat',['slug'=>$slug,'parent'=>(int)$parent->term_id]);if(is_wp_error($created))continue;$term=get_term((int)$created['term_id'],'product_cat');update_term_meta($term->term_id,'_tesra_demo_subcategory',1);}
   if(!$term||is_wp_error($term)) continue;
   if((int)$term->parent!==(int)$parent->term_id){
    if(get_term_meta($term->term_id,'_tesra_demo_subcategory',true)){wp_update_term($term->term_id,'product_cat',['parent'=>(int)$parent->term_id]);$term=get_term($term->term_id,'product_cat');}
    else continue;
   }
   foreach($skus as $sku){
    if(empty($sku_to_product[$sku])) continue;
    $pid=$sku_to_product[$sku];
    $current=wp_get_object_terms($pid,'product_cat',['fields'=>'ids']);
    if(is_wp_error($current))$current=[];
    $ids=array_values(array_unique(array_map('intval',array_merge($current,[(int)$term->term_id]))));
    wp_set_object_terms($pid,$ids,'product_cat',false);
   }
  }
 }
 update_option('tesra_demo_subcategory_version',1,false);
}
add_action('init','tesra_seed_demo_subcategories',30);

function tesra_enable_demo_reviews(){
 if(!class_exists('WooCommerce') || get_option('tesra_demo_reviews_enabled')) return;
 $ids=get_posts(['post_type'=>'product','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'_sku','value'=>'TESRA-DEMO-','compare'=>'LIKE']]]);
 foreach($ids as $id){if(get_post_meta($id,'_reviews_allowed',true)==='')update_post_meta($id,'_reviews_allowed','yes');}
 update_option('tesra_demo_reviews_enabled',1,false);
}
add_action('admin_init','tesra_enable_demo_reviews',30);

function tesra_register_elementor_locations($manager){
 if(method_exists($manager,'register_all_core_location')) $manager->register_all_core_location();
}
add_action('elementor/theme/register_locations','tesra_register_elementor_locations');

function tesra_url($slug){
 if($slug==='home') return home_url('/');
 if($slug==='shop' && function_exists('wc_get_page_permalink')) return wc_get_page_permalink('shop');
 if($slug==='cart' && function_exists('wc_get_cart_url')) return wc_get_cart_url();
 if($slug==='checkout' && function_exists('wc_get_checkout_url')) return wc_get_checkout_url();
 if($slug==='account' && function_exists('wc_get_page_permalink')) return wc_get_page_permalink('myaccount');
 $p=get_page_by_path($slug);
 return $p?get_permalink($p):home_url('/'.$slug.'/');
}

function tesra_money($n){return number_format_i18n((float)$n).' تومان';}
add_filter('woocommerce_currency_symbol',function($symbol,$currency){return strtoupper($currency)==='USD'?'تومان':$symbol;},10,2);
function tesra_placeholder($i=0){
 $g=[['#C9BCFF','#7C5CFF'],['#FFC9B8','#FF7F5C'],['#B8F0DC','#3CCFA0'],['#FFE9A8','#FFC24D'],['#D9D0FF','#9B86FF'],['#FFD0DD','#FF7FA3'],['#BFE3FF','#5EA8FF'],['#E3F5B8','#9AD14B']][$i%8];
 $svg='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 360"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="'.$g[0].'"/><stop offset="1" stop-color="'.$g[1].'"/></linearGradient></defs><rect width="400" height="360" fill="url(#g)"/><circle cx="100" cy="90" r="65" fill="#fff" opacity=".35"/><path d="M400 360V210Q300 150 220 250T60 360z" fill="#fff" opacity=".28"/><rect x="130" y="110" width="140" height="140" rx="48" fill="#fff" opacity=".9"/><circle cx="200" cy="180" r="30" fill="'.$g[1].'"/></svg>';
 return 'data:image/svg+xml;base64,'.base64_encode($svg);
}

function tesra_product_image($product,$size='woocommerce_thumbnail',$i=0){
 $url=$product->get_image_id()?wp_get_attachment_image_url($product->get_image_id(),$size):'';
 return $url?:tesra_placeholder($product->get_id()+$i);
}
function tesra_category_image($term){
 if(!$term||is_wp_error($term)) return tesra_demo_asset('products/product-1.png');
 $thumbnail_id=(int)get_term_meta($term->term_id,'thumbnail_id',true);
 if($thumbnail_id){$url=wp_get_attachment_image_url($thumbnail_id,'woocommerce_thumbnail');if($url)return $url;}
 if(function_exists('wc_get_products')){
  $products=wc_get_products(['status'=>'publish','limit'=>1,'category'=>[$term->slug],'orderby'=>'popularity','order'=>'DESC']);
  if($products&&!empty($products[0])&&$products[0]->get_image_id()) return tesra_product_image($products[0],'woocommerce_thumbnail');
 }
 return tesra_demo_asset('products/product-1.png');
}
function tesra_categories(){
 if(!class_exists('WooCommerce')) return [];
 $default=(int)get_option('default_product_cat');
 $args=['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0];
 if($default) $args['exclude']=[$default];
 $terms=get_terms($args);
 if(is_wp_error($terms)) return [];
 return array_values(array_filter($terms,function($term){return $term->slug!=='uncategorized';}));
}

function tesra_category_descendant_links($parent_id,$depth=0){
 $children=get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>(int)$parent_id,'orderby'=>'name','order'=>'ASC']);
 if(is_wp_error($children)||!$children) return '';
 $html='';
 foreach($children as $child){
  if($child->slug==='uncategorized') continue;
  $link=get_term_link($child);
  if(is_wp_error($link)) continue;
  $html.='<a class="category-subcategory" data-depth="'.(int)$depth.'" href="'.esc_url($link).'"><span class="category-sub-dot">›</span><span>'.esc_html($child->name).'</span><small>'.esc_html(number_format_i18n($child->count)).'</small></a>';
  $html.=tesra_category_descendant_links($child->term_id,$depth+1);
 }
 return $html;
}

function tesra_cart_instance(){
 if(!function_exists('WC')) return null;
 if(!WC()->cart && function_exists('wc_load_cart')) wc_load_cart();
 return WC()->cart;
}

function tesra_cart_count(){
 $cart=tesra_cart_instance();
 return $cart?(int)$cart->get_cart_contents_count():0;
}
function tesra_product_card($product,$rank=0,$stock=false){
 if(!$product) return '';
 $id=$product->get_id();
 $price=$product->get_price();
 $regular=$product->get_regular_price();
 $sale_price=$product->get_sale_price();
 $reg_num=(float)$regular;
 $price_num=(float)$price;
 $disc=($reg_num>0 && $price_num>0 && $sale_price!=='' && $price_num<$reg_num)?(int)round(100-($price_num/$reg_num*100)):0;
 $shape=['150px 150px 28px 28px','4px 90px 4px 90px','50% 50% 28px 28px/36% 36% 28px 28px','60px','90px 28px 90px 28px','28px 28px 120px 28px','120px 28px 28px 28px','50px 50px 28px 28px'][$id%8];
 $cat='';
 $terms=get_the_terms($id,'product_cat');
 if($terms&&!is_wp_error($terms)) $cat=$terms[0]->name;
 $rating=(float)$product->get_average_rating();
 $count=(int)$product->get_rating_count();
 ob_start();
 ?>
 <article class="pc">
  <div class="pw">
   <a class="pv" href="<?php echo esc_url(get_permalink($id)); ?>" style="--sh:<?php echo esc_attr($shape); ?>;border-radius:var(--sh)"><img src="<?php echo esc_url(tesra_product_image($product)); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy"></a>
   <?php if($disc): ?><span class="bd">٪<?php echo esc_html($disc); ?></span><?php endif; ?>
   <?php if($rank): ?><span class="rk"><?php echo esc_html($rank); ?></span><?php endif; ?>
   <button class="hb" type="button" data-wish="<?php echo esc_attr($id); ?>" aria-label="علاقه‌مندی"><i data-i="heart"></i></button>
   <button class="hb qv" type="button" data-qv="<?php echo esc_attr($id); ?>" data-qv-title="<?php echo esc_attr($product->get_name()); ?>" data-qv-image="<?php echo esc_url(tesra_product_image($product,'woocommerce_single')); ?>" data-qv-price="<?php echo esc_attr(tesra_money($price)); ?>" data-qv-url="<?php echo esc_url(get_permalink($id)); ?>" data-qv-add="<?php echo ($product->get_type()==='simple'&&$product->is_purchasable()&&$product->is_in_stock())?'1':'0'; ?>" aria-label="مشاهده سریع"><i data-i="eye"></i></button>
  </div>
  <div class="pt"><h3><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html($product->get_name()); ?></a></h3><p><?php echo esc_html($cat?:'محصول دیجیتال'); ?></p><div class="rt"><i data-i="star"></i><b><?php echo esc_html(number_format_i18n($rating,1)); ?></b><span>(<?php echo esc_html(number_format_i18n($count)); ?> نظر)</span></div></div>
  <div class="pf"><div class="pr"><s><?php echo $disc?esc_html(tesra_money($regular)):''; ?></s><strong><?php echo esc_html(tesra_money($price)); ?></strong></div>
   <?php if($product->is_purchasable() && $product->is_in_stock() && $product->get_type()==='simple'): ?><button type="button" class="add" data-add="<?php echo esc_attr($id); ?>" data-qty="1"><i data-i="cart"></i><span>افزودن به سبد</span></button><?php else: ?><a class="add" href="<?php echo esc_url(get_permalink($id)); ?>"><span>مشاهده محصول</span></a><?php endif; ?>
  </div>
 </article>
 <?php
 return ob_get_clean();
}

function tesra_shop_products($args=[]){
 $defaults=['status'=>'publish','limit'=>12,'orderby'=>'date','order'=>'DESC','return'=>'objects'];
 return wc_get_products(array_merge($defaults,$args));
}

function tesra_cart_item_summary($item){
 $summary='';
 if(function_exists('wc_get_formatted_cart_item_data')){
  $summary=trim(wp_strip_all_tags((string)wc_get_formatted_cart_item_data($item,true)));
  if($summary!=='') return $summary;
 }
 $product=$item['data']??null;
 if(!$product||!method_exists($product,'get_attributes')) return '';
 $parts=[];
 foreach((array)$product->get_attributes() as $attribute){
  if(!is_object($attribute)||!method_exists($attribute,'get_name')) continue;
  $name=(string)$attribute->get_name();
  if($name==='') continue;
  if(method_exists($attribute,'is_taxonomy')&&$attribute->is_taxonomy()){
   $values=function_exists('wc_get_product_terms')?wc_get_product_terms($product->get_id(),$name,['fields'=>'names']):[];
   $label=function_exists('wc_attribute_label')?wc_attribute_label($name,$product):$name;
  }else{
   $values=method_exists($attribute,'get_options')?$attribute->get_options():[];
   $label=function_exists('wc_attribute_label')?wc_attribute_label($name,$product):$name;
  }
  $values=array_filter(array_map('strval',(array)$values),'strlen');
  if($values) $parts[]=$label.': '.implode(', ',$values);
 }
 return implode(' · ',$parts);
}

function tesra_cart_drawer_html(){
 $cart=tesra_cart_instance();
 if(!$cart) return '<div class="empty">سبد خرید خالی است</div>';
 ob_start();
 $items=$cart->get_cart();
 echo '<h3>سبد خرید ('.esc_html($cart->get_cart_contents_count()).')</h3><div class="dl">';
 if(!$items) echo '<div class="empty">سبد خرید خالی است</div>';
 foreach($items as $key=>$item){
  $p=$item['data']; $qty=(int)$item['quantity']; $summary=tesra_cart_item_summary($item);
  echo '<div class="cr"><a class="cart-preview-image" href="'.esc_url(get_permalink($p->get_id())).'"><img src="'.esc_url(tesra_product_image($p,'woocommerce_thumbnail')).'" alt="'.esc_attr($p->get_name()).'"></a><div class="cart-preview-info"><a href="'.esc_url(get_permalink($p->get_id())).'"><b>'.esc_html($p->get_name()).'</b></a>'.($summary!==''?'<div class="mu">'.esc_html($summary).'</div>':'').'<div class="cart-preview-controls"><button type="button" data-cart-qty="'.esc_attr($key).'" data-step="-1" aria-label="کم کردن تعداد">−</button><span>'.esc_html($qty).'</span><button type="button" data-cart-qty="'.esc_attr($key).'" data-step="1" aria-label="زیاد کردن تعداد">+</button><button type="button" class="cart-preview-remove" data-cart-remove="'.esc_attr($key).'">حذف</button></div></div><div class="cpr"><strong>'.esc_html(tesra_money((float)$p->get_price()*$qty)).'</strong></div></div>';
 }
 echo '</div><div class="df"><div class="rw t"><span>جمع</span><span>'.esc_html(tesra_money($cart->get_subtotal())).'</span></div><a class="btn o2" href="'.esc_url(wc_get_cart_url()).'">مشاهده سبد خرید</a><a class="btn" href="'.esc_url(wc_get_checkout_url()).'">تسویه حساب</a></div>';
 return ob_get_clean();
}
function tesra_normalize_phone($phone){
 $phone=(string)$phone;
 $phone=strtr($phone,['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
 $digits=preg_replace('/\D+/','',$phone);
 if(strpos($digits,'0098')===0) $digits=substr($digits,4);
 elseif(strpos($digits,'98')===0&&strlen($digits)===12) $digits=substr($digits,2);
 if(strlen($digits)===10&&strpos($digits,'9')===0) $digits='0'.$digits;
 return $digits;
}

function tesra_user_by_phone($phone){
 $phone=tesra_normalize_phone($phone);
 if($phone==='') return false;
 $users=get_users(['number'=>1,'count_total'=>false,'meta_query'=>['relation'=>'OR',['key'=>'tesra_phone','value'=>$phone,'compare'=>'='],['key'=>'billing_phone','value'=>$phone,'compare'=>'=']]]);
 if($users) return $users[0];
 $by_login=get_user_by('login',$phone);
 return $by_login?:false;
}

function tesra_is_local_environment(){
 $host=strtolower((string)wp_parse_url(home_url('/'),PHP_URL_HOST));
 return in_array($host,['localhost','127.0.0.1','::1'],true)||substr($host,-5)==='.test'||substr($host,-6)==='.local';
}

function tesra_register_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $name=sanitize_text_field(wp_unslash($_POST['name']??''));
 $phone=tesra_normalize_phone(sanitize_text_field(wp_unslash($_POST['phone']??'')));
 $email=sanitize_email(wp_unslash($_POST['email']??''));
 $pass=(string)wp_unslash($_POST['password']??'');
 if(!$name||strlen($phone)<10||strlen($phone)>15||strlen($pass)<6||(!empty($_POST['email'])&&!is_email($email))) wp_send_json_error(['message'=>'نام، شماره موبایل و رمز عبور را درست وارد کنید. ایمیل اختیاری است.']);
 if(tesra_user_by_phone($phone)||username_exists($phone)) wp_send_json_error(['message'=>'این شماره موبایل قبلاً ثبت شده است.']);
 if($email!==''&&email_exists($email)) wp_send_json_error(['message'=>'این ایمیل قبلاً ثبت شده است.']);
 $id=wp_insert_user(['user_login'=>$phone,'user_pass'=>$pass,'user_email'=>$email,'display_name'=>$name,'first_name'=>$name,'role'=>'customer']);
 if(is_wp_error($id)) wp_send_json_error(['message'=>'ثبت‌نام انجام نشد. لطفاً اطلاعات را بررسی کنید.']);
 update_user_meta($id,'tesra_phone',$phone);
 update_user_meta($id,'billing_phone',$phone);
 wp_set_current_user($id);wp_set_auth_cookie($id,true,is_ssl());
 wp_send_json_success(['url'=>tesra_url('account')]);
}
add_action('wp_ajax_tesra_register','tesra_register_ajax');
add_action('wp_ajax_nopriv_tesra_register','tesra_register_ajax');

function tesra_sms_reset_key($phone){return 'tesra_sms_reset_'.md5(tesra_normalize_phone($phone));}
function tesra_verified_reset_key($token){return 'tesra_sms_reset_verified_'.hash('sha256',(string)$token);}

function tesra_request_sms_reset_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $phone=tesra_normalize_phone(sanitize_text_field(wp_unslash($_POST['phone']??'')));
 if(strlen($phone)<10||strlen($phone)>15) wp_send_json_error(['message'=>'شماره موبایل را به‌درستی وارد کنید.']);
 $key=tesra_sms_reset_key($phone);
 $existing=get_transient($key);
 if(is_array($existing) && !empty($existing['sent_at']) && (time()-(int)$existing['sent_at'])<60) wp_send_json_error(['message'=>'لطفاً ۱ دقیقه صبر کنید و سپس دوباره درخواست کد بدهید.']);
 $user=tesra_user_by_phone($phone);
 if(!$user) wp_send_json_error(['message'=>'حسابی با این شماره پیدا نشد. شماره ثبت‌شده در حساب را بررسی کنید.']);
 $code=(string)wp_rand(100000,999999);
 set_transient($key,['user_id'=>(int)$user->ID,'code_hash'=>wp_hash_password($code),'attempts'=>0,'sent_at'=>time()],10*MINUTE_IN_SECONDS);
 $sent=(bool)apply_filters('tesra_send_sms_reset_code',false,$phone,$code,(int)$user->ID);
 if($sent){wp_send_json_success(['message'=>'کد تأیید به شماره موبایل شما پیامک شد.','phone'=>$phone,'demo_mode'=>false]);}
 if(tesra_is_local_environment()){
  wp_send_json_success(['message'=>'حالت آزمایشی: پیامک واقعی ارسال نشده است. برای بررسی فرم از کد آزمایشی استفاده کنید.','phone'=>$phone,'demo_mode'=>true,'demo_code'=>$code]);
 }
 delete_transient($key);
 wp_send_json_error(['message'=>'سرویس پیامکی به سایت متصل نیست. برای ارسال واقعی کد، درگاه پیامکی را پیکربندی کنید.']);
}
add_action('wp_ajax_tesra_request_sms_reset','tesra_request_sms_reset_ajax');
add_action('wp_ajax_nopriv_tesra_request_sms_reset','tesra_request_sms_reset_ajax');

function tesra_verify_sms_reset_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $phone=tesra_normalize_phone(sanitize_text_field(wp_unslash($_POST['phone']??'')));
 $code=(string)wp_unslash($_POST['code']??'');$code=strtr($code,['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);$code=preg_replace('/\D+/','',$code);
 $key=tesra_sms_reset_key($phone);$entry=get_transient($key);
 if(!is_array($entry)||empty($entry['user_id'])||empty($entry['code_hash'])) wp_send_json_error(['message'=>'کد منقضی شده است. دوباره درخواست کد بدهید.']);
 if(!wp_check_password($code,$entry['code_hash'])){
  $entry['attempts']=(int)($entry['attempts']??0)+1;
  if($entry['attempts']>=5) delete_transient($key); else set_transient($key,$entry,10*MINUTE_IN_SECONDS);
  wp_send_json_error(['message'=>$entry['attempts']>=5?'تعداد تلاش‌ها بیش از حد مجاز است. دوباره کد بگیرید.':'کد تأیید درست نیست. دوباره بررسی کنید.']);
 }
 $token=wp_generate_password(48,false,false);
 set_transient(tesra_verified_reset_key($token),['user_id'=>(int)$entry['user_id'],'phone'=>$phone],5*MINUTE_IN_SECONDS);
 delete_transient($key);
 wp_send_json_success(['message'=>'شماره موبایل تأیید شد. رمز عبور جدید را تعیین کنید.','token'=>$token]);
}
add_action('wp_ajax_tesra_verify_sms_reset','tesra_verify_sms_reset_ajax');
add_action('wp_ajax_nopriv_tesra_verify_sms_reset','tesra_verify_sms_reset_ajax');

function tesra_set_sms_reset_password_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $token=sanitize_text_field(wp_unslash($_POST['token']??''));
 $password=(string)wp_unslash($_POST['password']??'');
 $confirm=(string)wp_unslash($_POST['confirm_password']??'');
 $key=tesra_verified_reset_key($token);$entry=get_transient($key);
 if(!$token||!is_array($entry)||empty($entry['user_id'])) wp_send_json_error(['message'=>'نشست بازیابی منقضی شده است. مراحل را دوباره انجام دهید.']);
 if(strlen($password)<8) wp_send_json_error(['message'=>'رمز جدید باید حداقل ۸ کاراکتر باشد.']);
 if($password!==$confirm) wp_send_json_error(['message'=>'تکرار رمز عبور با رمز جدید یکسان نیست.']);
 wp_set_password($password,(int)$entry['user_id']);
 delete_transient($key);
 wp_send_json_success(['message'=>'رمز عبور با موفقیت تغییر کرد. اکنون وارد حساب شوید.','url'=>tesra_url('login')]);
}
add_action('wp_ajax_tesra_set_sms_reset_password','tesra_set_sms_reset_password_ajax');
add_action('wp_ajax_nopriv_tesra_set_sms_reset_password','tesra_set_sms_reset_password_ajax');

function tesra_contact_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $name=sanitize_text_field(wp_unslash($_POST['name']??''));$email=str_replace(["\r","\n"],'',sanitize_email(wp_unslash($_POST['email']??'')));$subject=sanitize_text_field(wp_unslash($_POST['subject']??''));$msg=sanitize_textarea_field(wp_unslash($_POST['msg']??''));
 if(!$name||!is_email($email)||!$subject||!$msg) wp_send_json_error(['message'=>'لطفاً همه فیلدها را کامل کنید.']);
 wp_mail(get_option('admin_email'),'تماس جدید: '.$subject,$msg."\n\nنام: ".$name."\nایمیل: ".$email,['Reply-To: '.$email]);
 wp_send_json_success(['message'=>'پیام شما ارسال شد.']);
}
add_action('wp_ajax_tesra_contact','tesra_contact_ajax');
add_action('wp_ajax_nopriv_tesra_contact','tesra_contact_ajax');

function tesra_newsletter_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $email=sanitize_email(wp_unslash($_POST['email']??''));
 if(!is_email($email)) wp_send_json_error(['message'=>'ایمیل معتبر نیست.']);
 $list=get_option('tesra_newsletter',[]);if(!in_array($email,$list,true)){$list[]=$email;update_option('tesra_newsletter',$list,false);}
 wp_send_json_success(['message'=>'عضویت شما در خبرنامه ثبت شد.']);
}
add_action('wp_ajax_tesra_newsletter','tesra_newsletter_ajax');
add_action('wp_ajax_nopriv_tesra_newsletter','tesra_newsletter_ajax');

function tesra_save_comment_rating($comment_id){
 if(isset($_POST['rating'])){
  $rating=max(1,min(5,(int)wp_unslash($_POST['rating'])));
  update_comment_meta($comment_id,'rating',$rating);
  $comment=get_comment($comment_id);
  if($comment && class_exists('WC_Comments')) WC_Comments::clear_transients((int)$comment->comment_post_ID);
 }
}
add_action('comment_post','tesra_save_comment_rating');

function tesra_cart_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $cart=tesra_cart_instance();
 wp_send_json_success([
  'html'=>tesra_cart_drawer_html(),
  'count'=>$cart?(int)$cart->get_cart_contents_count():0,
  'subtotal'=>$cart?tesra_money($cart->get_subtotal()):'۰ تومان'
 ]);
}
add_action('wp_ajax_tesra_cart','tesra_cart_ajax');
add_action('wp_ajax_nopriv_tesra_cart','tesra_cart_ajax');
function tesra_cart_update_ajax(){
 check_ajax_referer('tesra_nonce','nonce');
 $cart=tesra_cart_instance(); if(!$cart) wp_send_json_error(['message'=>'سبد خرید در دسترس نیست.']);
 $key=sanitize_text_field(wp_unslash($_POST['key']??''));
 if(!isset($cart->get_cart()[$key])) wp_send_json_error(['message'=>'این کالا در سبد موجود نیست.']);
 if(isset($_POST['remove']) && (string)$_POST['remove']==='1') $cart->remove_cart_item($key);
 else { $step=(int)($_POST['step']??0); $item=$cart->get_cart_item($key); $qty=max(0,(int)($item['quantity']??1)+$step); if($qty<1)$cart->remove_cart_item($key); else $cart->set_quantity($key,$qty,true); }
 $cart->calculate_totals(); wp_send_json_success(['html'=>tesra_cart_drawer_html(),'count'=>(int)$cart->get_cart_contents_count(),'subtotal'=>tesra_money($cart->get_subtotal())]);
}
add_action('wp_ajax_tesra_cart_update','tesra_cart_update_ajax');
add_action('wp_ajax_nopriv_tesra_cart_update','tesra_cart_update_ajax');

add_filter('woocommerce_add_to_cart_fragments',function($fragments){
 $count=tesra_cart_count();
 $cart=tesra_cart_instance();
 $fragments['#cc']='<b id="cc">'.esc_html(number_format_i18n($count)).'</b>';
 $fragments['#ct']='<strong id="ct">'.esc_html($cart?tesra_money($cart->get_subtotal()):'۰ تومان').'</strong>';
 return $fragments;
});

function tesra_ensure_core_pages(){
 if(get_option('tesra_core_pages_ensured')===TESRA_VERSION) return;
 if(!get_page_by_path('articles')) tesra_page('articles','مقالات');
 if(!get_page_by_path('forgot-password')) tesra_page('forgot-password','فراموشی رمز عبور');
 update_option('tesra_core_pages_ensured',TESRA_VERSION,false);
}
add_action('init','tesra_ensure_core_pages',5);

function tesra_seed_demo_articles(){
 if((int)get_option('tesra_demo_articles_version',0)>=2) return;
 $category=get_term_by('slug','buying-guides','category');
 if(!$category){$created=wp_insert_term('راهنمای خرید','category',['slug'=>'buying-guides']);if(!is_wp_error($created))$category=get_term((int)$created['term_id'],'category');}
 $samples=[
  ['راهنمای انتخاب گوشی هوشمند برای استفاده روزمره','برای انتخاب گوشی، پیش از هر چیز به نوع استفاده، عمر باتری، کیفیت نمایشگر و مدت پشتیبانی نرم‌افزاری توجه کنید. لازم نیست همیشه گران‌ترین مدل را انتخاب کنید؛ مدلی متناسب با نیاز و بودجه شما معمولاً انتخاب بهتری است.','products/product-1.png'],
  ['قبل از خرید لپ‌تاپ به چه مشخصاتی توجه کنیم؟','پردازنده، حافظه رم، فضای ذخیره‌سازی و کیفیت نمایشگر چهار بخش مهم در انتخاب لپ‌تاپ هستند. برای استفاده روزمره، دانشجویی یا کار حرفه‌ای نیازها متفاوت‌اند؛ ابتدا کار اصلی خود را مشخص کنید و سپس بین مدل‌ها مقایسه انجام دهید.','products/product-4.png'],
  ['چطور لباس باکیفیت را از روی مشخصات بشناسیم؟','ترکیب الیاف، کیفیت دوخت، نوع برش و راهنمای شست‌وشو به شما کمک می‌کند انتخاب مطمئن‌تری داشته باشید. به جدول اندازه‌ها نیز دقت کنید؛ اندازه استاندارد ممکن است بین برندهای مختلف متفاوت باشد.','products/product-5.png'],
  ['راهنمای ساده خرید لوازم خانگی کم‌مصرف','ظرفیت، مصرف انرژی، ابعاد محل نصب، میزان صدا و دسترسی به خدمات پس از فروش را با هم بررسی کنید. اندازه‌گیری محل قرارگیری وسیله قبل از سفارش، از مشکلات رایج هنگام تحویل جلوگیری می‌کند.','products/product-7.png'],
  ['هدفون مناسب کار، موسیقی و رفت‌وآمد','راحتی روی گوش، کیفیت میکروفن، عمر باتری و نوع حذف نویز را با توجه به کاربرد خود بسنجید. اگر تماس‌های روزانه زیادی دارید، کیفیت مکالمه اهمیت بیشتری از امکانات جانبی دارد.','products/product-3.png'],
  ['راهنمای خرید تلویزیون برای خانه','اندازه صفحه، فاصله نشستن، وضوح تصویر، درگاه‌ها و سیستم عامل را با فضای خانه و شیوه استفاده هماهنگ کنید.','products/product-9.png'],
  ['چه کفشی برای پیاده‌روی روزانه مناسب است؟','وزن کفش، انعطاف‌پذیری، تهویه و اندازه درست روی راحتی روزانه تأثیر زیادی دارد.','products/product-6.png'],
  ['نکات مهم خرید یخچال فریزر','ابعاد آشپزخانه، ظرفیت خانواده، مصرف انرژی و فضای باز شدن درها را پیش از انتخاب بررسی کنید.','products/product-7.png'],
  ['راهنمای انتخاب ماشین لباسشویی','ظرفیت شست‌وشو، دور موتور، میزان صدا، مصرف آب و خدمات پس از فروش را در اولویت قرار دهید.','products/product-8.png'],
  ['چطور هدفون بی‌سیم بخریم؟','عمر باتری، کیفیت تماس، تأخیر صدا و راحتی گوشی‌ها را با توجه به کاربرد اصلی مقایسه کنید.','products/product-3.png'],
  ['لوازم ضروری برای یک میز کار خانگی','نور مناسب، صندلی استاندارد، مانیتور متناسب با کار و مدیریت کابل‌ها می‌تواند کار روزانه را راحت‌تر کند.','products/product-4.png'],
  ['راهنمای خرید تبلت برای درس و کار','اندازه نمایشگر، وزن، پشتیبانی قلم، باتری و برنامه‌های قابل نصب مهم‌ترین معیارها هستند.','products/product-1.png'],
  ['از کجا بفهمیم پارچه لباس باکیفیت است؟','به تراکم بافت، کیفیت دوخت، ثبات رنگ و دستور شست‌وشو توجه کنید و اندازه‌ها را با جدول همان برند تطبیق دهید.','products/product-5.png'],
  ['چطور اندازه مناسب کفش را انتخاب کنیم؟','اندازه پا را در ساعات پایانی روز بررسی کنید و فضای کافی برای انگشتان و جوراب در نظر بگیرید.','products/product-6.png'],
  ['نکات خرید اسپرسوساز خانگی','فضای آشپزخانه، فشار پمپ، نظافت دستگاه و دسترسی به قطعات مصرفی را پیش از خرید بسنجید.','products/product-10.png'],
  ['راهنمای انتخاب جاروبرقی','قدرت مکش، نوع فیلتر، وزن، صدای موتور و در دسترس بودن کیسه یا قطعات را مقایسه کنید.','products/product-11.png'],
  ['چه مانیتوری برای طراحی و بازی مناسب است؟','وضوح، دقت رنگ، نرخ نوسازی، نوع پنل و درگاه‌های تصویر باید متناسب با کاربرد انتخاب شوند.','products/product-12.png'],
  ['چطور از وسایل دیجیتال مراقبت کنیم؟','از شارژر استاندارد استفاده کنید، دستگاه را از گرمای زیاد دور نگه دارید و به‌روزرسانی‌ها را منظم نصب کنید.','products/product-2.png'],
  ['راهنمای انتخاب کوله‌پشتی روزانه','وزن خالی، بندهای قابل تنظیم، بخش لپ‌تاپ، جنس پارچه و مقاومت در برابر باران را بررسی کنید.','products/product-5.png'],
  ['مهم‌ترین معیارها برای خرید ساعت هوشمند','سازگاری با تلفن، عمر باتری، دقت حسگرها، مقاومت در برابر آب و خوانایی نمایشگر را مقایسه کنید.','products/product-2.png'],
  ['چطور برای خانه لوازم کم‌مصرف انتخاب کنیم؟','برچسب انرژی، ظرفیت مناسب، حالت‌های صرفه‌جویی و هزینه سرویس در بلندمدت اهمیت دارند.','products/product-8.png'],
  ['راهنمای خرید مخلوط‌کن و خردکن','توان موتور، جنس پارچ، تیغه‌های قابل شست‌وشو و ایمنی قفل درپوش را با نیاز آشپزخانه بسنجید.','products/product-10.png'],
  ['چه نکاتی در خرید لباس ورزشی مهم است؟','تنفس‌پذیری، کشسانی، راحتی دوخت و خشک شدن سریع پارچه هنگام فعالیت اهمیت زیادی دارند.','products/product-5.png'],
  ['راهنمای خرید کفش مخصوص دویدن','نوع سطح دویدن، فرم پا، میزان ضربه‌گیری و فاصله‌های تمرین را در انتخاب مدل در نظر بگیرید.','products/product-6.png'],
  ['چگونه فضای خانه را با محصولات کاربردی بهتر کنیم؟','ابعاد واقعی فضا را اندازه بگیرید و محصولاتی انتخاب کنید که هم نیاز روزمره را پاسخ دهند و هم راه عبور را مسدود نکنند.','products/product-10.png'],
  ['قبل از خرید دوربین و لوازم جانبی چه کنیم؟','نوع پروژه، لنزهای سازگار، باتری، وزن و هزینه لوازم جانبی را در بودجه کل حساب کنید.','products/product-2.png'],
  ['راهنمای ساده نگهداری از لوازم خانگی','دفترچه دستگاه را مطالعه کنید، فیلترها را در زمان مناسب تمیز کنید و فاصله تهویه را رعایت کنید.','products/product-7.png'],
  ['چطور بین لپ‌تاپ سبک و قدرتمند انتخاب کنیم؟','اگر رفت‌وآمد زیادی دارید وزن و باتری مهم‌تر می‌شوند؛ برای کار سنگین باید به خنک‌کاری و توان پردازنده هم توجه کرد.','products/product-4.png'],
  ['چگونه خرید اینترنتی مطمئن‌تری داشته باشیم؟','مشخصات دقیق، شرایط ارسال، ضمانت، راه ارتباط با فروشنده و سیاست بازگشت را پیش از پرداخت بررسی کنید.','products/product-1.png']
 ];
 $existing_titles=[];
 $existing_ids=get_posts(['post_type'=>'post','post_status'=>'any','numberposts'=>-1,'fields'=>'ids','meta_key'=>'_tesra_demo_article','meta_value'=>'1']);
 foreach($existing_ids as $existing_id) $existing_titles[strtolower(get_the_title($existing_id))]=true;
 foreach($samples as $i=>$sample){
  if(isset($existing_titles[strtolower($sample[0])])) continue;
  $id=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>$sample[0],'post_name'=>sanitize_title($sample[0]),'post_content'=>'<p>'.esc_html($sample[1]).'</p><p>در فروشگاه تسرا می‌توانید مشخصات محصولات را بررسی کنید و گزینه‌های مختلف را با توجه به نیازتان مقایسه کنید.</p>','post_excerpt'=>$sample[1],'comment_status'=>'open','post_category'=>$category?[(int)$category->term_id]:[]]);
  if(!is_wp_error($id)&&$id){update_post_meta($id,'_tesra_demo_article','1');$image=tesra_demo_image_id($sample[2],$sample[0]);if($image)set_post_thumbnail($id,$image);}
 }
 update_option('tesra_demo_articles_seeded',1,false);
 update_option('tesra_demo_articles_version',2,false);
}
add_action('init','tesra_seed_demo_articles',25);

function tesra_articles_archive(){
 $page=max(1,absint($_GET['article_page']??1));
 $search=isset($_GET['article_search'])?sanitize_text_field(wp_unslash($_GET['article_search'])):'';
 $posts_page_id=(int)get_option('page_for_posts');
 $archive_url=(is_home()&&$posts_page_id)?get_permalink($posts_page_id):(get_queried_object_id()?get_permalink(get_queried_object_id()):tesra_url('articles'));
 $args=['post_type'=>'post','post_status'=>'publish','posts_per_page'=>25,'paged'=>$page,'ignore_sticky_posts'=>true];
 if($search!=='') $args['s']=$search;
 $q=new WP_Query($args);
 $html=tesra_crumb([['مقالات']]).'<div class="articles-heading"><div><h1 class="ph">مقالات و راهنمای خرید</h1><p class="mu">راهنماها، نکته‌ها و پیشنهادهایی برای انتخاب بهتر محصولات</p></div><form class="article-search" method="get" action="'.esc_url($archive_url).'" role="search"><label class="screen-reader-text" for="article-search-input">جست‌وجوی مقالات</label><input id="article-search-input" type="search" name="article_search" value="'.esc_attr($search).'" placeholder="جست‌وجو در عنوان و متن مقاله…"><button type="submit" aria-label="جست‌وجو"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg><span>جست‌وجو</span></button>'.($search!==''?'<a class="article-search-clear" href="'.esc_url($archive_url).'">پاک کردن</a>':'').'</form></div>';
 if($search!=='') $html.='<p class="article-results-label">نتیجه جست‌وجو برای «'.esc_html($search).'»: '.esc_html(number_format_i18n((int)$q->found_posts)).' مقاله</p>';
 if($q->have_posts()){
  $html.='<div class="article-grid">';
  while($q->have_posts()){$q->the_post();$id=get_the_ID();$image=get_the_post_thumbnail_url($id,'large')?:tesra_placeholder($id);$cats=get_the_category($id);$cat=$cats?$cats[0]->name:'مقالات';
   $html.='<article class="article-card"><a class="article-cover" href="'.esc_url(get_permalink($id)).'"><img src="'.esc_url($image).'" alt="'.esc_attr(get_the_title()).'" loading="lazy"></a><div class="article-content"><div class="article-meta"><span>'.esc_html($cat).'</span><time datetime="'.esc_attr(get_the_date('c',$id)).'">'.esc_html(get_the_date('Y/m/d',$id)).'</time></div><h2><a href="'.esc_url(get_permalink($id)).'">'.esc_html(get_the_title()).'</a></h2><p>'.esc_html(wp_trim_words(get_the_excerpt(),20)).'</p><a class="article-more" href="'.esc_url(get_permalink($id)).'">ادامه مطلب <span aria-hidden="true">←</span></a></div></article>';
  }
  $html.='</div>';
  $pages=(int)$q->max_num_pages;
  if($pages>1){
   $html.='<nav class="article-pagination" aria-label="صفحه‌های مقالات">';
   if($page>1)$html.='<a class="article-page-prev" href="'.esc_url(add_query_arg(['article_page'=>$page-1,'article_search'=>$search],$archive_url)).'">صفحه قبل</a>';
   $start=max(1,$page-2);$end=min($pages,$page+2);
   if($start>1){$html.='<a href="'.esc_url(add_query_arg(['article_page'=>1,'article_search'=>$search],$archive_url)).'">۱</a>';if($start>2)$html.='<span class="article-page-dots">…</span>';}
   for($n=$start;$n<=$end;$n++){$url=add_query_arg(['article_page'=>$n,'article_search'=>$search],$archive_url);$html.='<a '.($n===$page?'class="current" aria-current="page"':'').' href="'.esc_url($url).'">'.esc_html(number_format_i18n($n)).'</a>';}
   if($end<$pages){if($end<$pages-1)$html.='<span class="article-page-dots">…</span>';$html.='<a href="'.esc_url(add_query_arg(['article_page'=>$pages,'article_search'=>$search],$archive_url)).'">'.esc_html(number_format_i18n($pages)).'</a>';}
   if($page<$pages)$html.='<a class="article-page-next" href="'.esc_url(add_query_arg(['article_page'=>$page+1,'article_search'=>$search],$archive_url)).'">صفحه بعد</a>';
   $html.='</nav>';
  }
 } else {$html.='<div class="box empty"><h2>مقاله‌ای پیدا نشد</h2><p>'.($search!==''?'عبارت دیگری را جست‌وجو کنید.':'مقالات منتشرشده از پیشخوان وردپرس در این بخش نمایش داده می‌شوند.').'</p>'.($search!==''?'<a class="btn o2" href="'.esc_url($archive_url).'">نمایش همه مقالات</a>':'').'</div>';}
 wp_reset_postdata();
 return $html;
}

function tesra_article_single(){
 if(!have_posts()) return tesra_box('مقاله پیدا نشد','<p class="mu">این مقاله در دسترس نیست.</p>');
 ob_start();while(have_posts()){the_post();
  echo tesra_crumb([['مقالات',tesra_url('articles')],[get_the_title()]]);
  $article_cats=get_the_category();$article_cat=$article_cats?$article_cats[0]->name:'مقالات';
  echo '<article class="article-single"><header class="article-single-head"><div class="article-meta"><span>'.esc_html($article_cat).'</span><time datetime="'.esc_attr(get_the_date('c')).'">'.esc_html(get_the_date('Y/m/d')).'</time></div><h1 class="ph">'.esc_html(get_the_title()).'</h1><p class="mu">'.esc_html(get_the_author()).'</p></header>';
  if(has_post_thumbnail())echo '<div class="article-featured">'.get_the_post_thumbnail(get_the_ID(),'large',['loading'=>'eager']).'</div>';
  echo '<div class="article-body">'.apply_filters('the_content',get_the_content()).'</div></article>';
  if(comments_open()||get_comments_number()){echo '<section class="box article-comments"><h2>دیدگاه‌ها</h2>';comments_template();echo '</section>';}
 }
 return ob_get_clean();
}

function tesra_logout_url(){
 return wp_nonce_url(add_query_arg('tesra_action','logout',tesra_url('account')),'tesra_logout');
}
function tesra_handle_front_auth(){
 if(isset($_GET['tesra_action'])&&sanitize_key(wp_unslash($_GET['tesra_action']))==='logout'){
  $nonce=sanitize_text_field(wp_unslash($_GET['_wpnonce']??''));
  if(wp_verify_nonce($nonce,'tesra_logout')){wp_logout();wp_safe_redirect(tesra_url('login'));exit;}
 }
 if(empty($_POST['tesra_front_login'])) return;
 $nonce=sanitize_text_field(wp_unslash($_POST['tesra_login_nonce']??''));
 if(!wp_verify_nonce($nonce,'tesra_front_login')){wp_safe_redirect(add_query_arg('login','failed',tesra_url('login')));exit;}
 $login=sanitize_text_field(wp_unslash($_POST['log']??''));
 if(is_email($login)){$emailUser=get_user_by('email',$login);if($emailUser)$login=$emailUser->user_login;}elseif(preg_match('/^[+0-9۰-۹٠-٩()\s-]+$/u',$login)){$login=tesra_normalize_phone($login);}
 $creds=['user_login'=>$login,'user_password'=>(string)wp_unslash($_POST['pwd']??''),'remember'=>!empty($_POST['rememberme'])];
 $user=wp_signon($creds,is_ssl());
 if(is_wp_error($user)){wp_safe_redirect(add_query_arg('login','failed',tesra_url('login')));exit;}
 wp_set_current_user((int)$user->ID);
 $redirect=wp_validate_redirect(wp_unslash($_POST['redirect_to']??''),tesra_url('account'));
 wp_safe_redirect($redirect?:tesra_url('account'));exit;
}
add_action('init','tesra_handle_front_auth',1);

function tesra_render_page_content(){
 $slug=get_post_field('post_name',get_the_ID());
 if($slug==='about') return tesra_about();
 if($slug==='contact') return tesra_contact();
 if($slug==='faq') return tesra_faq();
 if($slug==='terms') return tesra_terms();
 if($slug==='login') return tesra_login();
 if($slug==='forgot-password') return tesra_forgot_password();
 if($slug==='account') return tesra_account();
 if($slug==='articles') return tesra_articles_archive();
 if($slug==='admin') return tesra_admin();
 return apply_filters('the_content',get_the_content());
}
function tesra_crumb($items=[]){
 $s='<div class="crumb"><a href="'.esc_url(home_url('/')).'">خانه</a>';
 foreach($items as $it){$s.='<span>‹</span>'.(isset($it[1])?'<a href="'.esc_url($it[1]).'">'.esc_html($it[0]).'</a>':'<b>'.esc_html($it[0]).'</b>');}
 return $s.'</div>';
}
function tesra_box($title,$body){return tesra_crumb([[$title]]).'<div class="box"><h1 class="ph">'.esc_html($title).'</h1>'.$body.'</div>';}
function tesra_about(){
 return tesra_crumb([['درباره ما']]).'<section class="box"><h1 class="ph">درباره تسرا</h1><p>تسرا از یک ایده ساده شروع شد: فروشگاه‌های اینترنتی می‌توانند هم زیبا باشند، هم سریع و هم متفاوت. ما محصولاتی را گردآوری و طراحی می‌کنیم که تجربه خرید را برای مشتری و مدیریت فروشگاه برای شما ساده می‌کند.</p></section><section class="stt"><div><b>۱۲٬۰۰۰+</b><span>مشتری راضی</span></div><div><b>۲۸۰+</b><span>محصول فعال</span></div><div><b>۵</b><span>سال تجربه</span></div><div><b>۹۸٪</b><span>رضایت خریداران</span></div></section><section class="vl"><div class="box"><h3>کیفیت</h3><p class="mu">هر محصول پیش از انتشار روی دستگاه‌های مختلف آزمایش می‌شود.</p></div><div class="box"><h3>پشتیبانی</h3><p class="mu">تیم ما در کمتر از دو ساعت به پیام‌های شما پاسخ می‌دهد.</p></div><div class="box"><h3>شفافیت</h3><p class="mu">قیمت، ضمانت و شرایط بازگشت وجه همیشه روشن و بدون شرط پنهان است.</p></div></section><section class="tm">'.implode('',array_map(function($x,$i){[$n,$r]=explode('|',$x);return '<figure><img src="'.esc_url(tesra_placeholder($i+3)).'" alt="'.esc_attr($n).'"><b>'.esc_html($n).'</b><div class="mu">'.esc_html($r).'</div></figure>';},['سارا احمدی|مدیر محصول','نیما صادقی|مدیر فنی','الهام کرمی|پشتیبانی مشتریان','پویا فرهادی|طراح رابط کاربری'],array_keys(['a','b','c','d']))).'</section>';
}
function tesra_contact(){
 return tesra_crumb([['تماس با ما']]).'<div class="l2"><form class="box fg" id="tesra-contact"><h1 class="ph">تماس با ما</h1><div class="fr"><label class="fld">نام<input name="name" required></label><label class="fld">ایمیل<input name="email" type="email" required></label></div><label class="fld">موضوع<input name="subject" required></label><label class="fld">پیام شما<textarea name="msg" required></textarea></label><button class="btn" type="submit">ارسال پیام</button></form><aside class="box"><h3>راه‌های ارتباطی</h3><div class="ci3"><i data-i="phone"></i><span>۰۲۱-۱۲۳۴۵۶۷۸</span></div><div class="ci3"><i data-i="mail"></i><span>support@tesra.example</span></div><div class="ci3"><i data-i="pin"></i><span>تهران، خیابان ولیعصر، پلاک ۱۴۰۵</span></div><a class="btn o2" href="'.esc_url(tesra_url('faq')).'">سؤالات متداول</a></aside></div>';
}
function tesra_faq(){
 $qs=[['چگونه خرید کنم؟','محصول را به سبد اضافه کنید، وارد حساب کاربری شوید، آدرس را ثبت کنید و پرداخت را انجام دهید.'],['از کد تخفیف چطور استفاده کنم؟','کد را در صفحه سبد خرید در بخش «کد تخفیف» وارد کنید و دکمه اعمال را بزنید.'],['هزینه ارسال چقدر است؟','ارسال عادی برای خریدهای بالای ۱٬۵۰۰٬۰۰۰ تومان رایگان است.'],['آیا امکان بازگشت وجه وجود دارد؟','بله، تا ۷ روز پس از خرید بدون هیچ سؤالی وجه شما بازگردانده می‌شود.'],['چطور نظر و عکس ثبت کنم؟','در صفحه هر محصول، بخش «نظرات» را باز کنید و فرم را ارسال کنید.']];
 $h=''; foreach($qs as $q)$h.='<details><summary>'.esc_html($q[0]).'</summary><p>'.esc_html($q[1]).'</p></details>';
 return tesra_crumb([['سؤالات متداول']]).'<h1 class="ph">سؤالات متداول</h1><div class="fq">'.$h.'</div>';
}
function tesra_terms(){return tesra_box('قوانین و شرایط بازگشت وجه','<p class="mu">خریداران تا ۷ روز پس از تحویل می‌توانند درخواست بازگشت وجه ثبت کنند. کالا باید استفاده‌نشده و در بسته‌بندی اصلی باشد. هزینه بازگشت وجه حداکثر ظرف ۷ روز کاری به حساب شما واریز می‌شود.</p>');}
function tesra_login(){
 if(is_user_logged_in()) return tesra_box('حساب کاربری','<div class="account-shortcuts"><p>برای مدیریت سفارش‌ها و اطلاعات حساب از پنل کاربری استفاده کنید.</p><a class="btn" href="'.esc_url(tesra_url('account')).'">رفتن به حساب کاربری</a><a class="btn o2" href="'.esc_url(tesra_logout_url()).'">خروج از حساب</a></div>');
 $error=isset($_GET['login'])&&sanitize_key(wp_unslash($_GET['login']))==='failed'?'<p class="auth-error" role="alert">نام کاربری یا رمز عبور صحیح نیست. دوباره تلاش کنید.</p>':'';
 ob_start();
 echo tesra_crumb([['ورود / ثبت‌نام']]).'<div class="au box"><div class="auth-intro"><span class="tg">حساب کاربری تسرا</span><h1 class="ph">ورود یا ایجاد حساب</h1><p class="mu">برای پیگیری سفارش‌ها و مدیریت اطلاعات خود وارد شوید.</p></div><div class="tb" style="margin:0 auto 20px"><button data-tab="l" class="on" type="button">ورود</button><button data-tab="r" type="button">ثبت‌نام</button></div><div class="tp on" id="tp-l" style="margin:0">'.$error.'<form method="post" class="fg tesra-login-form"><label class="fld">شماره موبایل، ایمیل یا نام کاربری<input name="log" autocomplete="username" required></label><label class="fld">رمز عبور<input type="password" name="pwd" autocomplete="current-password" required></label><label class="chk"><input type="checkbox" name="rememberme" value="forever"> مرا به خاطر بسپار</label><input type="hidden" name="redirect_to" value="'.esc_url(tesra_url('account')).'"><input type="hidden" name="tesra_front_login" value="1">'.wp_nonce_field('tesra_front_login','tesra_login_nonce',true,false).'<button class="btn" type="submit">ورود به حساب</button><a class="lost-password" href="'.esc_url(tesra_url('forgot-password')).'">رمز عبور را فراموش کرده‌اید؟</a></form></div><div class="tp" id="tp-r" style="margin:0"><form id="tesra-register" class="fg"><label class="fld">نام و نام خانوادگی<input name="name" autocomplete="name" required></label><label class="fld">شماره موبایل<input type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="مثلاً 09123456789" required></label><label class="fld">ایمیل <small>(اختیاری)</small><input type="email" name="email" autocomplete="email"></label><label class="fld">رمز عبور<input type="password" name="password" autocomplete="new-password" minlength="6" required></label><button class="btn" type="submit">ساخت حساب کاربری</button><p class="mu">شماره موبایل برای ساخت حساب الزامی است. می‌توانید بعداً با شماره موبایل یا ایمیل وارد شوید.</p></form></div></div>';
 return ob_get_clean();
}
function tesra_forgot_password(){
 if(is_user_logged_in()) return tesra_box('بازیابی رمز عبور','<p>برای تغییر رمز عبور از بخش اطلاعات حساب استفاده کنید.</p><a class="btn" href="'.esc_url(tesra_url('account')).'">رفتن به حساب کاربری</a>');
 $login=tesra_url('login');
 $html=tesra_crumb([['ورود / ثبت‌نام',$login],['بازیابی رمز عبور']]);
 $html.='<section class="reset-password-box box"><div class="reset-password-art"><span class="reset-art-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><rect x="13" y="27" width="38" height="29" rx="8"/><path d="M21 27v-8a11 11 0 0 1 22 0v8"/><circle cx="32" cy="40" r="4"/><path d="M32 44v5"/></svg></span><span class="tg">بازیابی امن حساب</span><h1 class="ph">رمز عبورت را فراموش کرده‌ای؟</h1><p>شماره موبایلی را وارد کن که هنگام ثبت‌نام در حساب تسرا ثبت کرده‌ای.</p></div><div class="reset-password-steps"><div class="reset-step is-active" data-reset-step-label="1"><span>۱</span><small>شماره موبایل</small></div><i></i><div class="reset-step" data-reset-step-label="2"><span>۲</span><small>کد تأیید</small></div><i></i><div class="reset-step" data-reset-step-label="3"><span>۳</span><small>رمز جدید</small></div></div><div id="tesra-reset-message" class="reset-message" role="status" aria-live="polite" hidden></div><form id="tesra-reset-request" class="fg reset-stage"><label class="fld">شماره موبایل ثبت‌شده<input type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="مثلاً 09123456789" required></label><button class="btn" type="submit">ارسال کد تأیید</button><p class="mu reset-note">برای بازیابی، باید به شماره موبایل ثبت‌شده در حساب دسترسی داشته باشید.</p></form><form id="tesra-reset-verify" class="fg reset-stage" hidden><input type="hidden" name="phone" id="tesra-reset-phone"><label class="fld">کد شش‌رقمی پیامک‌شده<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9۰-۹٠-٩]{6}" maxlength="6" placeholder="کد ۶ رقمی" required></label><button class="btn" type="submit">تأیید کد</button><button class="btn o2 reset-back" type="button" data-reset-back="phone">تغییر شماره موبایل</button></form><form id="tesra-reset-password" class="fg reset-stage" hidden><input type="hidden" name="token" id="tesra-reset-token"><label class="fld">رمز عبور جدید<input type="password" name="password" minlength="8" autocomplete="new-password" required></label><label class="fld">تکرار رمز عبور جدید<input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label><button class="btn" type="submit">ذخیره رمز جدید</button></form><a class="reset-back-login" href="'.esc_url($login).'">بازگشت به صفحه ورود</a></section>';
 return $html;
}

function tesra_account_address_has_data($user_id,$type='billing'){
 $prefix=$type.'_';
 foreach(['address_1','city','postcode','state'] as $key){if(trim((string)get_user_meta($user_id,$prefix.$key,true))!=='')return true;}
 return false;
}
function tesra_account_address_card($user_id,$type,$title){
 $prefix=$type.'_';$has=tesra_account_address_has_data($user_id,$type);
 $url=add_query_arg(['tesra_address_form'=>'1','tesra_address_type'=>$type],tesra_url('account'));
 $html='<article class="account-address-card"><div class="account-address-card-head"><span class="account-address-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg></span><div><h3>'.esc_html($title).'</h3><p>'.($has?'نشانی ذخیره‌شده':'اطلاعات نشانی شما').'</p></div><a class="account-address-edit" href="'.esc_url($url).'">'.($has?'ویرایش':'افزودن').' <span>←</span></a></div>';
 if(!$has){$html.='<div class="account-address-empty"><p>آدرسی افزوده نشده است.</p><a class="btn o2" href="'.esc_url($url).'">افزودن آدرس</a></div></article>';return $html;}
 $name=trim((string)get_user_meta($user_id,$prefix.'first_name',true).' '.(string)get_user_meta($user_id,$prefix.'last_name',true));
 $lines=[];
 foreach(['company','address_1','address_2'] as $key){$v=trim((string)get_user_meta($user_id,$prefix.$key,true));if($v!=='')$lines[]=$v;}
 $city=trim((string)get_user_meta($user_id,$prefix.'state',true).'، '.(string)get_user_meta($user_id,$prefix.'city',true));$city=trim($city,' ،');if($city!=='')$lines[]=$city;
 $postcode=trim((string)get_user_meta($user_id,$prefix.'postcode',true));if($postcode!=='')$lines[]='کد پستی: '.$postcode;
 $phone=trim((string)get_user_meta($user_id,$prefix.'phone',true));if($phone!=='')$lines[]='تلفن: '.$phone;
 $html.='<div class="account-address-details">'.($name!==''?'<b>'.esc_html($name).'</b>':'');foreach($lines as $line)$html.='<span>'.esc_html($line).'</span>';$html.='</div></article>';return $html;
}
function tesra_account_address_form($user,$type){
 $type='billing';$prefix='billing_';$country=(string)get_user_meta($user->ID,'billing_country',true);
 if($country===''){$storeCountry=(string)get_option('woocommerce_default_country','IR');$country=explode(':',$storeCountry)[0]?:'IR';}
 $field=function($key,$label,$required=false,$wide=false,$kind='text')use($user,$prefix,$country){
  $value=get_user_meta($user->ID,$prefix.$key,true);$class='fld'.($wide?' address-field-wide':'');
  if($key==='state'&&function_exists('WC')&&WC()->countries){$states=WC()->countries->get_states($country);if(is_array($states)&&$states){$control='<select name="state"><option value="">استان را انتخاب کنید</option>';foreach($states as $code=>$stateName)$control.='<option value="'.esc_attr($code).'" '.selected((string)$value,(string)$code,false).'>'.esc_html($stateName).'</option>';$control.='</select>';return '<label class="'.esc_attr($class).'">'.esc_html($label).$control.'</label>';}}
  return '<label class="'.esc_attr($class).'">'.esc_html($label).($required?' <em>*</em>':'').'<input type="'.esc_attr($kind).'" name="'.esc_attr($key).'" value="'.esc_attr((string)$value).'" '.($required?'required':'').' autocomplete="off"></label>';
 };
 $back=add_query_arg('view-addresses','1',tesra_url('account'));
 $html='<section class="account-address-editor"><div class="account-page-heading"><span class="account-kicker">مدیریت نشانی</span><h1 class="ph">آدرس من</h1><p>نشانی را ذخیره کنید تا در خریدهای بعدی نیز استفاده شود.</p></div>';
 if(isset($_GET['address_error']))$html.='<div class="account-form-notice is-error">ذخیره انجام نشد. نام، نام خانوادگی، شماره تماس، شهر و نشانی اصلی را بررسی کنید.</div>';
 $html.='<form method="post" action="" class="account-address-form"><input type="hidden" name="tesra_save_address" value="1"><input type="hidden" name="tesra_address_country" value="'.esc_attr($country).'">'.wp_nonce_field('tesra_save_address','tesra_address_nonce',true,false).'<div class="account-address-form-grid">';
 $html.=$field('first_name','نام',true).$field('last_name','نام خانوادگی',true).$field('phone','شماره تماس',true,false,'tel').$field('postcode','کد پستی').$field('state','استان').$field('city','شهر',true,true).$field('address_1','نشانی اصلی',true,true).$field('address_2','جزئیات تکمیلی (اختیاری)',false,true);
 $html.='</div><div class="account-address-form-actions"><a class="btn o2" href="'.esc_url($back).'">بازگشت به آدرس‌ها</a><button type="submit" class="btn">ذخیره آدرس</button></div></form></section>';return $html;
}
function tesra_save_account_address(){
 if(empty($_POST['tesra_save_address'])||!is_user_logged_in())return;
 $nonce=sanitize_text_field(wp_unslash($_POST['tesra_address_nonce']??''));
 if(!wp_verify_nonce($nonce,'tesra_save_address')){$url=add_query_arg(['tesra_address_form'=>'1','address_error'=>'1'],tesra_url('account'));wp_safe_redirect($url);exit;}
 $user_id=get_current_user_id();$prefix='billing_';$values=[];
 foreach(['first_name','last_name','address_1','address_2','city','state','postcode'] as $key){$raw=wp_unslash($_POST[$key]??'');$values[$key]=in_array($key,['address_1','address_2'],true)?sanitize_textarea_field($raw):sanitize_text_field($raw);}
 $phone=sanitize_text_field(wp_unslash($_POST['phone']??''));
 if($values['first_name']===''||$values['last_name']===''||$values['address_1']===''||$values['city']===''||$phone===''){$url=add_query_arg(['tesra_address_form'=>'1','address_error'=>'1'],tesra_url('account'));wp_safe_redirect($url);exit;}
 foreach($values as $key=>$value){update_user_meta($user_id,'billing_'.$key,$value);update_user_meta($user_id,'shipping_'.$key,$value);}
 $country=sanitize_text_field(wp_unslash($_POST['tesra_address_country']??''));$country=preg_match('/^[A-Z]{2}$/',$country)?$country:'IR';
 update_user_meta($user_id,'billing_country',$country);update_user_meta($user_id,'shipping_country',$country);
 update_user_meta($user_id,'billing_phone',$phone);update_user_meta($user_id,'shipping_phone',$phone);update_user_meta($user_id,'billing_email',sanitize_email((string)wp_get_current_user()->user_email));
 clean_user_cache($user_id);
 if(function_exists('WC')&&WC()->customer&&WC()->customer->get_id()===$user_id){
  foreach($values as $key=>$value){$billingMethod='set_billing_'.$key;$shippingMethod='set_shipping_'.$key;if(method_exists(WC()->customer,$billingMethod))WC()->customer->$billingMethod($value);if(method_exists(WC()->customer,$shippingMethod))WC()->customer->$shippingMethod($value);}
  WC()->customer->set_billing_country($country);WC()->customer->set_shipping_country($country);WC()->customer->set_billing_phone($phone);WC()->customer->set_shipping_phone($phone);WC()->customer->save();
 }
 $url=add_query_arg(['view-addresses'=>'1','address_saved'=>'1'],tesra_url('account'));wp_safe_redirect($url);exit;
}
add_action('init','tesra_save_account_address',5);
function tesra_account_orders_html($user){
 $orders=function_exists('wc_get_orders')?wc_get_orders(['customer_id'=>$user->ID,'limit'=>12,'orderby'=>'date','order'=>'DESC']):[];
 $html='<section class="account-orders-page"><div class="account-page-heading"><span class="account-kicker">تاریخچه خرید</span><h1 class="ph">سفارش‌های من</h1><p>وضعیت و جزئیات خریدهای ثبت‌شده‌تان را از این بخش دنبال کنید.</p></div>';
 if(!$orders){$html.='<div class="account-empty-orders"><span class="account-empty-orders-art"><svg viewBox="0 0 96 96" aria-hidden="true"><path d="M20 34h56l-5 42H25l-5-42Z"/><path d="M34 35V26a14 14 0 0 1 28 0v9"/><path d="m38 54 7 7 14-15"/></svg></span><span class="account-kicker">اولین خرید شما منتظر است</span><h2>هنوز سفارشی ثبت نشده</h2><p>وقتی خریدی انجام دهید، سفارش‌ها، مبلغ و وضعیت آن‌ها در همین صفحه نمایش داده می‌شوند.</p><div class="account-empty-actions"><a class="btn" href="'.esc_url(tesra_url('shop')).'">شروع خرید</a><a class="btn o2" href="'.esc_url(add_query_arg('orderby','popularity',tesra_url('shop'))).'">دیدن محصولات محبوب</a></div></div></section>';return $html;}
 $html.='<div class="account-orders-list">';
 foreach($orders as $order){$id=$order->get_id();$url=wc_get_endpoint_url('view-order',$id,tesra_url('account'));$date=$order->get_date_created();$firstItem=null;foreach($order->get_items() as $item){$firstItem=$item;break;}$product=$firstItem&&method_exists($firstItem,'get_product')?$firstItem->get_product():null;$img=$product?tesra_product_image($product,'woocommerce_gallery_thumbnail') :tesra_placeholder($id);$itemCount=(int)$order->get_item_count();$status=wc_get_order_status_name($order->get_status());
  $html.='<article class="account-order-card"><a class="account-order-thumb" href="'.esc_url($url).'"><img src="'.esc_url($img).'" alt=""></a><div class="account-order-main"><span class="account-order-number">سفارش #'.esc_html($order->get_order_number()).'</span><h3><a href="'.esc_url($url).'">'.esc_html($itemCount).' قلم کالا</a></h3><p>'.($date?esc_html(wc_format_datetime($date,'Y/m/d')):'').' <span>•</span> '.esc_html($status).'</p><span class="tesra-order-confirmation '.($order->is_paid()?'is-paid':(in_array($order->get_status(),['failed','cancelled'],true)?'is-failed':'is-pending')).'">'.($order->is_paid()?'پرداخت با موفقیت انجام شد':(in_array($order->get_status(),['failed','cancelled'],true)?'پرداخت ناموفق بوده است':'سفارش ثبت شده؛ در انتظار تأیید پرداخت')).'</span></div><div class="account-order-total"><span>مبلغ نهایی</span><b>'.wp_kses_post($order->get_formatted_order_total()).'</b><a class="account-order-view" href="'.esc_url($url).'">مشاهده جزئیات <span>←</span></a></div></article>';
 }
 $html.='</div></section>';return $html;
}
function tesra_account(){
 if(!is_user_logged_in()) return tesra_login();
 $base=tesra_url('account');$current=wp_get_current_user();$endpoint='';
 if(function_exists('is_wc_endpoint_url')) foreach(['orders','edit-address','edit-account','payment-methods','add-payment-method','customer-logout','view-order'] as $candidate){if(is_wc_endpoint_url($candidate)){$endpoint=$candidate;break;}}
 if(isset($_GET['tesra_address_form'])||isset($_GET['tesra_address_type'])||isset($_GET['view-addresses'])) $endpoint='edit-address';
 if($endpoint==='customer-logout') return '<script>window.location.href='.wp_json_encode(tesra_logout_url()).';</script>'.tesra_box('خروج از حساب','<p>در حال خروج امن از حساب…</p>');
 $endpoint_links=[
  ['dashboard','پیشخوان',$base],
  ['orders','سفارش‌ها',function_exists('wc_get_account_endpoint_url')?wc_get_account_endpoint_url('orders'):trailingslashit($base).'orders/'],
  ['edit-address','آدرس‌ها',add_query_arg('view-addresses','1',$base)],
  ['edit-account','اطلاعات حساب',function_exists('wc_get_account_endpoint_url')?wc_get_account_endpoint_url('edit-account'):trailingslashit($base).'edit-account/'],
 ];
 $activeEndpoint=$endpoint==='view-order'?'orders':$endpoint;$menu='';foreach($endpoint_links as $link)$menu.='<a class="cl '.((($activeEndpoint===''&&$link[0]==='dashboard')||$activeEndpoint===$link[0])?'on':'').'" href="'.esc_url($link[2]).'">'.esc_html($link[1]).'</a>';
 $menu.='<a class="cl logout-link" href="'.esc_url(tesra_logout_url()).'">خروج از حساب</a>';$content='';
 if($endpoint==='orders'&&function_exists('WC'))$content=tesra_account_orders_html($current);
 elseif($endpoint==='edit-address'&&function_exists('WC')){$addressSection=sanitize_key((string)get_query_var('edit-address'));if(isset($_GET['tesra_address_type']))$addressSection=sanitize_key(wp_unslash($_GET['tesra_address_type']));$openAddressForm=(isset($_GET['tesra_address_form'])&&sanitize_text_field(wp_unslash($_GET['tesra_address_form']))==='1')||($addressSection==='billing'&&!isset($_GET['view-addresses']));$content=$openAddressForm?tesra_account_address_form($current,'billing'):'<section class="account-addresses-page"><div class="account-page-heading"><span class="account-kicker">نشانی شما</span><h1 class="ph">آدرس من</h1><p>یک نشانی ذخیره کنید یا نشانی فعلی خود را ویرایش کنید.</p></div>'.(isset($_GET['address_saved'])?'<div class="account-form-notice">آدرس با موفقیت ذخیره شد و برای خریدهای بعدی آماده است.</div>':'').'<div class="account-addresses-grid single-address-grid">'.tesra_account_address_card($current->ID,'billing','آدرس من').'</div></section>';}

 elseif($endpoint==='edit-account'&&function_exists('WC')){ob_start();do_action('woocommerce_account_edit-account_endpoint',get_query_var('edit-account'));$content='<section class="account-subpage"><div class="account-page-heading"><span class="account-kicker">حریم شخصی</span><h1 class="ph">اطلاعات حساب</h1><p>مشخصات خود را به‌روز نگه دارید.</p></div>'.ob_get_clean().'</section>';}
 elseif($endpoint==='payment-methods'&&function_exists('WC')){ob_start();do_action('woocommerce_account_payment-methods_endpoint',get_query_var('payment-methods'));$content='<section class="account-subpage"><div class="account-page-heading"><span class="account-kicker">پرداخت امن</span><h1 class="ph">روش‌های پرداخت</h1></div>'.ob_get_clean().'</section>';}
 elseif($endpoint==='view-order'&&function_exists('WC')){ob_start();do_action('woocommerce_account_view-order_endpoint',get_query_var('view-order'));$content='<section class="account-subpage"><div class="account-page-heading"><span class="account-kicker">جزئیات خرید</span><h1 class="ph">جزئیات سفارش</h1></div>'.ob_get_clean().'</section>';}
 else{
  $orderCount=function_exists('wc_get_customer_order_count')?wc_get_customer_order_count($current->ID):0;$hasAddress=tesra_account_address_has_data($current->ID,'billing')||tesra_account_address_has_data($current->ID,'shipping');
  $content='<section class="account-dashboard"><div class="account-page-heading"><span class="account-kicker">به فضای شخصی خود خوش آمدید</span><h1 class="ph">حساب کاربری</h1><p>سفارش‌ها، نشانی‌ها و مشخصات خود را در یک‌جا مدیریت کنید.</p></div><div class="account-stats"><div><span>سفارش‌های ثبت‌شده</span><b>'.esc_html(number_format_i18n($orderCount)).'</b></div><div><span>وضعیت نشانی</span><b class="account-address-status">'.($hasAddress?'آدرس ثبت شده':'نیاز به افزودن آدرس').'</b></div></div><div class="account-shortcuts"><a class="btn" href="'.esc_url($endpoint_links[1][2]).'">پیگیری سفارش‌ها</a><a class="btn o2" href="'.esc_url($endpoint_links[2][2]).'">مدیریت آدرس‌ها</a><a class="btn o2" href="'.esc_url($endpoint_links[3][2]).'">ویرایش اطلاعات حساب</a></div>';
  $recent=function_exists('wc_get_orders')?wc_get_orders(['customer_id'=>$current->ID,'limit'=>3,'orderby'=>'date','order'=>'DESC']):[];
  if($recent){$content.='<h3 class="account-recent-title">آخرین سفارش‌ها</h3><div class="account-order-list">';foreach($recent as $order){$url=wc_get_endpoint_url('view-order',$order->get_id(),$base);$content.='<a class="account-order" href="'.esc_url($url).'"><span>سفارش #'.esc_html($order->get_order_number()).'</span><span>'.esc_html(wc_get_order_status_name($order->get_status())).'</span><b>'.wp_kses_post($order->get_formatted_order_total()).'</b></a>';}$content.='</div>';}
  $content.='</section>';
 }
 if(trim(wp_strip_all_tags($content))==='')$content='<div class="account-empty-orders compact"><h2>هنوز اطلاعاتی در این بخش ندارید</h2><p>با ثبت اطلاعات یا انجام خرید، جزئیات در این قسمت نمایش داده می‌شود.</p></div>';
 return tesra_crumb([['حساب کاربری']]).'<div class="ac"><aside class="box am" aria-label="منوی حساب">'.$menu.'</aside><section class="box account-content account-content-'.esc_attr($activeEndpoint?:'dashboard').'">'.$content.'</section></div>';
}
function tesra_admin(){
 if(!current_user_can('manage_options')) return tesra_box('پنل مدیریت','<div class="empty"><h2>دسترسی مجاز نیست.</h2><a class="btn" href="'.esc_url(wp_login_url()).'">ورود</a></div>');
 return tesra_box('پنل مدیریت','<div class="stack"><section class="box"><h3>مدیریت وردپرس</h3><p class="mu">برای مدیریت واقعی محصولات، سفارش‌ها، کاربران، تخفیف‌ها و افزونه‌ها از پنل اصلی وردپرس استفاده کنید.</p><a class="btn" href="'.esc_url(admin_url()).'">باز کردن پنل مدیریت</a></section></div>');
}


function tesra_comment($comment,$args,$depth){
 $avatar=get_avatar($comment,46);
 $stars='';
 $rating=(int)get_comment_meta($comment->comment_ID,'rating',true);
 for($i=0;$i<$rating;$i++) $stars.='<i data-i="star"></i>';
 echo '<div class="cmt"><div class="av">'.$avatar.'</div><div><b>'.esc_html(get_comment_author($comment)).'</b> <small class="mu">'.esc_html(get_comment_date('Y/m/d',$comment)).'</small>'.($stars?'<div class="rt">'.$stars.'</div>':'').'<p>'.esc_html(get_comment_text($comment)).'</p></div></div>';
}

function tesra_footer_drawer(){
 echo '<div id="tesra-drawer" class="ov dr" hidden><div class="md"><button class="cl2" data-close aria-label="بستن"><i data-i="x"></i></button><div id="tesra-drawer-body">'.tesra_cart_drawer_html().'</div></div></div>';
 echo '<div id="tesra-modal" class="ov" hidden><div class="md"><button class="cl2" data-close aria-label="بستن"><i data-i="x"></i></button><div id="tesra-modal-body"></div></div></div>';
 echo '<div class="ts" hidden></div>';
}

function tesra_customize_register($wp_customize){
 $wp_customize->add_section('tesra_home_images',['title'=>'تصاویر صفحه اصلی تسرا','priority'=>145,'description'=>'تصاویر بنر اصلی و کادرهای پرفروش‌ترین‌ها و تخفیف ویژه را بارگذاری کنید. هر تصویر در تمام کادر مربوط به خودش نمایش داده می‌شود.']);
 foreach([['tesra_home_hero_image','تصویر بنر اصلی صفحه'],['tesra_home_best_image','تصویر کادر پرفروش‌ترین‌ها'],['tesra_home_sale_image','تصویر کادر تخفیف ویژه']] as $image){$wp_customize->add_setting($image[0],['default'=>0,'sanitize_callback'=>'absint']);$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,$image[0],['section'=>'tesra_home_images','label'=>$image[1]]));}
 $wp_customize->add_section('tesra_footer_settings',['title'=>'تنظیمات فوتر تسرا','priority'=>160,'description'=>'نشانی شبکه‌های اجتماعی و تصویر مجوزهای واقعی فروشگاه را وارد کنید.']);
 $fields=[
  'tesra_instagram_url'=>['نشانی اینستاگرام','url'],
  'tesra_telegram_url'=>['نشانی تلگرام','url'],
  'tesra_whatsapp_url'=>['نشانی واتساپ','url'],
  'tesra_facebook_url'=>['نشانی فیسبوک','url'],
  'tesra_youtube_url'=>['نشانی یوتیوب','url'],
  'tesra_license_1_url'=>['لینک تأیید مجوز اول','url'],
  'tesra_license_2_url'=>['لینک تأیید مجوز دوم','url'],
 ];
 foreach($fields as $id=>$field){$wp_customize->add_setting($id,['default'=>'','sanitize_callback'=>'esc_url_raw']);$wp_customize->add_control($id,['section'=>'tesra_footer_settings','label'=>$field[0],'type'=>$field[1]]);}
 foreach([['tesra_license_1_image','تصویر مجوز اول'],['tesra_license_2_image','تصویر مجوز دوم']] as $image){$wp_customize->add_setting($image[0],['default'=>0,'sanitize_callback'=>'absint']);$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,$image[0],['section'=>'tesra_footer_settings','label'=>$image[1]]));}
}
add_action('customize_register','tesra_customize_register');

add_filter('woocommerce_checkout_posted_data',function($data){
 $store=(string)get_option('woocommerce_default_country','IR');$store=explode(':',$store)[0]?:'IR';
 if(empty($data['billing_country']))$data['billing_country']=$store;
 if(empty($data['shipping_country']))$data['shipping_country']=$data['billing_country'];
 return $data;
},20);
add_filter('woocommerce_checkout_fields',function($fields){
 if(isset($fields['billing']['billing_country']))unset($fields['billing']['billing_country']);
 if(isset($fields['shipping']['shipping_country']))unset($fields['shipping']['shipping_country']);
 return $fields;
},30);

function tesra_is_local_payment_environment(){
 $host=strtolower((string)wp_parse_url(home_url('/'),PHP_URL_HOST));
 return in_array($host,['localhost','127.0.0.1','::1'],true)||substr($host,-5)==='.test'||substr($host,-6)==='.local';
}
if(class_exists('WC_Payment_Gateway')&&!class_exists('TESRA_Gateway_Local_Test')){
 class TESRA_Gateway_Local_Test extends WC_Payment_Gateway{
  public function __construct(){
   $this->id='tesra_local_test';$this->method_title='Tessera Local Test';$this->method_description='روش آزمایشی فقط برای محیط محلی؛ هیچ وجه واقعی دریافت نمی‌شود.';$this->has_fields=false;$this->supports=['products'];
   $this->init_form_fields();$this->init_settings();$this->enabled=$this->get_option('enabled','yes');$this->title=$this->get_option('title','پرداخت آزمایشی (فقط محیط محلی)');$this->description=$this->get_option('description','این روش فقط برای بررسی روند ثبت سفارش در localhost است و هیچ تراکنش واقعی انجام نمی‌دهد.');
  }
  public function init_form_fields(){
   $this->form_fields=[
    'enabled'=>['title'=>'فعال‌سازی','type'=>'checkbox','label'=>'فعال بودن پرداخت آزمایشی','default'=>'yes'],
    'title'=>['title'=>'عنوان','type'=>'text','default'=>'پرداخت آزمایشی (فقط محیط محلی)'],
    'description'=>['title'=>'توضیحات','type'=>'textarea','default'=>'این روش فقط برای بررسی روند ثبت سفارش در localhost است و هیچ تراکنش واقعی انجام نمی‌دهد.']
   ];
  }
  public function is_available(){return tesra_is_local_payment_environment()&&parent::is_available();}
  public function payment_fields(){if($this->description)echo wpautop(wp_kses_post($this->description));}
  public function process_payment($order_id){
   $order=wc_get_order($order_id);if(!$order)return ['result'=>'failure'];
   $order->add_order_note('پرداخت آزمایشی در محیط محلی انجام شد؛ هیچ مبلغ واقعی دریافت نشده است.');
   $order->update_meta_data('_tesra_local_test_payment','yes');$order->payment_complete();$order->save();
   if(WC()->cart)WC()->cart->empty_cart();
   return ['result'=>'success','redirect'=>$this->get_return_url($order)];
  }
 }
 add_filter('woocommerce_payment_gateways',function($gateways){$gateways[]='TESRA_Gateway_Local_Test';return $gateways;});
}
function tesra_checkout_confirmation_notice($order_id){
 if(!$order_id)return;$order=wc_get_order($order_id);if(!$order||in_array($order->get_status(),['failed','cancelled'],true))return;
 $paid=$order->is_paid();$title=$paid?'پرداخت با موفقیت انجام شد':'سفارش شما با موفقیت ثبت شد';
 $description=$paid?'پرداخت سفارش شما تأیید شده است. کد پیگیری سفارش را برای پیگیری‌های بعدی نگه دارید.':'سفارش ثبت شده است؛ وضعیت پرداخت و سفارش در حساب کاربری قابل پیگیری است.';
 echo '<section class="tesra-payment-success '.($paid?'is-paid':'is-pending').'"><span class="tesra-payment-success-icon">✓</span><div><h2>'.esc_html($title).'</h2><p>'.esc_html($description).'</p><b>کد پیگیری: #'.esc_html($order->get_order_number()).'</b><a href="'.esc_url($order->get_view_order_url()).'">مشاهده سفارش</a></div></section>';
}
add_action('woocommerce_thankyou','tesra_checkout_confirmation_notice',8);


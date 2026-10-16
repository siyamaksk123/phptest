</main>
<?php if(function_exists('elementor_theme_do_location') && elementor_theme_do_location('footer')): ?>
<?php else: ?>
<footer class="wrap tesra-footer">
<div class="footer-ribbon">
 <div class="footer-ribbon-brand">
  <span class="footer-ribbon-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 8h16l-1.4 12H5.4L4 8Z"/><path d="M8 9V6a4 4 0 0 1 8 0v3"/></svg></span>
  <div><strong>خریدی راحت‌تر، انتخابی بهتر</strong><span>محصولات متنوع را پیدا کن، مقایسه کن و با خیال راحت انتخاب کن.</span></div>
 </div>
 <a class="footer-ribbon-cta" href="<?php echo esc_url(tesra_url('shop')); ?>">گشت‌وگذار در فروشگاه <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
 <span class="footer-ribbon-glow" aria-hidden="true"></span>
</div>
<div class="foot">
<div class="fc"><a class="logo" href="<?php echo esc_url(home_url('/')); ?>"><span class="mark"></span>تسرا</a><p>فروشگاه بزرگ و چنددسته‌ای برای خرید محصولات دیجیتال، پوشاک، لوازم خانگی، صوتی و تصویری و کالاهای روزمره.</p><form class="nl" id="nl"><input type="email" name="email" placeholder="ایمیل خود را وارد کنید" required><button class="btn sm">عضویت</button></form></div>
<div class="fl"><b>فروشگاه</b><a href="<?php echo esc_url(tesra_url('shop')); ?>">همه محصولات</a><a href="<?php echo esc_url(add_query_arg('product_cat','electronics',tesra_url('shop'))); ?>">موبایل و تبلت</a><a href="<?php echo esc_url(add_query_arg('product_cat','fashion',tesra_url('shop'))); ?>">پوشاک</a><a href="<?php echo esc_url(add_query_arg('orderby','popularity',tesra_url('shop'))); ?>">پرفروش‌ترین‌ها</a></div>
<div class="fl"><b>راهنما</b><a href="<?php echo esc_url(tesra_url('articles')); ?>">مقالات و راهنمای خرید</a><a href="<?php echo esc_url(tesra_url('faq')); ?>">نحوه خرید</a><a href="<?php echo esc_url(tesra_url('cart')); ?>">سبد خرید</a><a href="<?php echo esc_url(tesra_url('faq')); ?>">سؤالات متداول</a><a href="<?php echo esc_url(tesra_url('terms')); ?>">قوانین بازگشت وجه</a></div>
<div class="fl"><b>تسرا</b><a href="<?php echo esc_url(tesra_url('about')); ?>">درباره ما</a><a href="<?php echo esc_url(tesra_url('contact')); ?>">تماس با ما</a><a href="<?php echo esc_url(tesra_url('account')); ?>">حساب کاربری</a><a href="<?php echo esc_url(tesra_url('admin')); ?>">پنل مدیریت</a></div>
</div>
<div class="footer-extras">
<section class="footer-social"><h3>ما را دنبال کنید</h3><p>از تازه‌ترین محصولات و پیشنهادها باخبر شوید.</p><div class="social-links">
<?php $socials=[
 ['instagram','اینستاگرام','tesra_instagram_url','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>'],
 ['telegram','تلگرام','tesra_telegram_url','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.5 3.8 18.2 20c-.25 1.15-.92 1.43-1.86.9l-5.15-3.8-2.49 2.4c-.28.28-.52.52-1.07.52l.38-5.35 9.75-8.81c.42-.38-.09-.59-.65-.21L5.07 13.28.01 11.7c-1.1-.35-1.13-1.1.23-1.63L19.99 2.45c.92-.34 1.73.22 1.51 1.35Z" fill="currentColor"/></svg>'],
 ['whatsapp','واتساپ','tesra_whatsapp_url','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.3 3.7A11.55 11.55 0 0 0 2.1 17.6L.5 23.5l6-1.57A11.55 11.55 0 0 0 20.3 3.7ZM12 21a9.4 9.4 0 0 1-4.8-1.32l-.34-.2-3.56.93.95-3.47-.22-.36A9.43 9.43 0 1 1 12 21Zm5.17-7.06c-.28-.14-1.67-.82-1.93-.91-.26-.1-.45-.14-.64.14-.19.28-.73.91-.9 1.1-.17.19-.33.21-.61.07-.28-.14-1.18-.44-2.25-1.39-.83-.74-1.4-1.66-1.57-1.94-.17-.28-.02-.43.13-.57.12-.12.28-.33.42-.5.14-.16.18-.28.28-.47.1-.19.05-.35-.02-.49-.07-.14-.64-1.54-.88-2.1-.23-.55-.47-.47-.64-.48l-.55-.01c-.19 0-.49.07-.74.35-.26.28-.97.95-.97 2.32s.99 2.7 1.12 2.89c.14.19 1.95 2.98 4.72 4.18.66.28 1.17.45 1.57.58.66.21 1.26.18 1.73.11.53-.08 1.67-.68 1.9-1.34.24-.66.24-1.22.17-1.34-.07-.12-.25-.19-.53-.33Z" fill="currentColor"/></svg>'],
 ['facebook','فیسبوک','tesra_facebook_url','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.7 21v-8.2h2.75l.41-3.2H13.7V7.56c0-.93.26-1.57 1.6-1.57h1.7V3.12A22.4 22.4 0 0 0 14.52 3c-2.45 0-4.13 1.5-4.13 4.25V9.6H7.6v3.2h2.79V21h3.31Z" fill="currentColor"/></svg>'],
 ['youtube','یوتیوب','tesra_youtube_url','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8ZM9.55 15.6V8.4L15.8 12l-6.25 3.6Z" fill="currentColor"/></svg>']
]; foreach($socials as $social): $social_url=trim((string)get_theme_mod($social[2],'')); ?>
<?php if($social_url): ?><a class="social-icon-link <?php echo esc_attr($social[0]); ?>" href="<?php echo esc_url($social_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social[1]); ?>"><span class="social-mark <?php echo esc_attr($social[0]); ?>"><?php echo $social[3]; ?></span><span><?php echo esc_html($social[1]); ?></span></a><?php else: ?><a class="social-icon-link <?php echo esc_attr($social[0]); ?>" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($social[1]); ?>"><span class="social-mark <?php echo esc_attr($social[0]); ?>"><?php echo $social[3]; ?></span><span><?php echo esc_html($social[1]); ?></span></a><?php endif; ?>
<?php endforeach; ?></div><small>نشانی شبکه‌های اجتماعی از بخش نمایش ← سفارشی‌سازی قابل تنظیم است.</small></section>
<section class="footer-licenses"><h3>مجوزها و اعتماد</h3><div class="license-list">
<?php foreach([['tesra_license_1_image','tesra_license_1_url','مجوز کسب‌وکار'],['tesra_license_2_image','tesra_license_2_url','نماد اعتماد']] as $license): $license_image=(int)get_theme_mod($license[0],0);$license_url=trim((string)get_theme_mod($license[1],'')); ?>
<?php if($license_image&&wp_get_attachment_image_url($license_image,'medium')): ?><?php if($license_url): ?><a class="license-item" href="<?php echo esc_url($license_url); ?>" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url(wp_get_attachment_image_url($license_image,'medium')); ?>" alt="<?php echo esc_attr($license[2]); ?>"></a><?php else: ?><div class="license-item"><img src="<?php echo esc_url(wp_get_attachment_image_url($license_image,'medium')); ?>" alt="<?php echo esc_attr($license[2]); ?>"></div><?php endif; ?><?php else: ?><div class="license-placeholder"><span class="license-seal" aria-hidden="true"><?php if($license[0]==='tesra_license_2_image'): ?><svg viewBox="0 0 48 48"><path d="M24 4 39 10v12c0 10-6 17-15 22C15 39 9 32 9 22V10Z"/><path d="m16 24 5 5 11-12"/></svg><?php else: ?><svg viewBox="0 0 48 48"><rect x="10" y="5" width="28" height="38" rx="5"/><path d="M17 15h14M17 22h14M17 29h8"/><circle cx="31" cy="34" r="7"/><path d="m28 34 2 2 4-5"/></svg><?php endif; ?></span><b><?php echo esc_html($license[2]); ?></b><small>جایگاه تصویر رسمی مجوز</small><em>تصویر واقعی را از سفارشی‌سازی بارگذاری کنید</em></div><?php endif; ?>
<?php endforeach; ?></div></section>
</div>
<div class="cp">© <?php echo esc_html(wp_date('Y')); ?> تسرا؛ تمامی حقوق محفوظ است.</div>
</footer>
<?php tesra_footer_drawer(); ?>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

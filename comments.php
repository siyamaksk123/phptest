<?php if(!defined('ABSPATH')) exit; ?>
<?php if(have_comments()): ?>
<div id="comments"><h3><?php echo esc_html(number_format_i18n(get_comments_number()).' دیدگاه'); ?></h3><?php wp_list_comments(['style'=>'div','callback'=>'tesra_comment','max_depth'=>3]); ?></div>
<?php endif; ?>
<?php if(comments_open()):
$is_product = is_singular('product');
$user = wp_get_current_user();
?>
<form class="fg comment-form" method="post" action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>">
<div class="fr">
<?php if(!is_user_logged_in()): ?>
<label class="fld">نام<input name="author" autocomplete="name" required></label>
<label class="fld">ایمیل<input type="email" name="email" autocomplete="email" required></label>
<?php else: ?>
<p class="mu comment-logged-in" style="grid-column:1/-1;margin:0">وارد شده با عنوان <strong><?php echo esc_html($user->display_name); ?></strong></p>
<?php endif; ?>
<?php if($is_product): ?><div class="fld">امتیاز شما<div class="sx"><?php for($n=1;$n<=5;$n++): ?><button type="button" class="<?php echo $n<=5?'on':''; ?>" data-star="<?php echo esc_attr($n); ?>" aria-label="<?php echo esc_attr(sprintf('%d ستاره',$n)); ?>"><i data-i="star"></i></button><?php endfor; ?></div><input type="hidden" name="rating" value="5"></div><?php endif; ?>
</div>
<label class="fld">متن دیدگاه<textarea name="comment" rows="4" required></textarea></label>
<?php
comment_id_fields();
do_action('comment_form', get_the_ID());
?>
<button class="btn" type="submit">ثبت دیدگاه</button>
</form>
<?php else: ?><p class="mu">ثبت دیدگاه برای این محتوا بسته شده است.</p><?php endif; ?>

<?php get_header(); ?>
<?php if(function_exists('is_cart') && is_cart()){ ?><div class="box"><?php echo do_shortcode('[woocommerce_cart]'); ?></div><?php } elseif(function_exists('is_checkout') && is_checkout()){ ?><div class="box"><?php echo do_shortcode('[woocommerce_checkout]'); ?></div><?php } elseif(get_post_field('post_name',get_the_ID())==='shop'){ wp_safe_redirect(tesra_url('shop')); exit; } else { echo tesra_render_page_content(); } ?>
<?php get_footer(); ?>

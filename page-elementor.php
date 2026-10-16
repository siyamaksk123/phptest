<?php
/*
Template Name: Elementor Full Width
*/
get_header();
?>
<main class="wrap elementor-page-content">
<?php while(have_posts()):the_post();the_content();endwhile; ?>
</main>
<?php get_footer(); ?>

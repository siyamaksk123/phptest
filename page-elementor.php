<?php
/*
Template Name: Elementor Full Width
*/
if (!defined('ABSPATH')) exit;
get_header();
?>
<div class="elementor-page-content">
<?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</div>
<?php get_footer(); ?>

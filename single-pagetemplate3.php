<?php
/*
Template Name: Page Template, 3
*/
get_header();

// Elementor `single` location
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
	get_template_part( 'template-parts/single' );
}

get_footer();
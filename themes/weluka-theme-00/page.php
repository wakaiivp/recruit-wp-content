<?php
/**
 * The page template file.
 *
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * 　
 */
get_header();
global $weluka_themename;

if ( have_posts() ) :
	get_template_part( 'content', get_post_format() );
else:
	get_template_part( 'content', 'none' );

endif;
get_footer();
?>

<?php
/**
 * The main template file.
 * blog tyle home or archive
 *
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * 　
 */

if( check_weluka_plugin() !== "" ) return;

get_header();
global $weluka_themename;

if( isset( $_GET['mode'] ) && $_GET['mode'] === 'cp' ) {
	get_template_part( 'content', 'color' );

}elseif ( is_404() ) {
	get_template_part( 'content', 'none' );

} else {

	if( is_home() ) :
	else :
?>
	<h2 class="page-title sp-pad">
<?php
		if ( is_search() ) :
			printf( __( 'Search Results for: %s', $weluka_themename ), '<span>' . get_search_query() . '</span>' );
	
		elseif ( is_tag() ) :
			single_tag_title();
	
		elseif ( is_tax() || is_category() ) :
			single_term_title();
	
		elseif ( is_author() ) :
			printf( __( 'Author: %s', $weluka_themename ), '<span class="vcard">' . get_the_author() . '</span>' );

		elseif ( is_day() ) :
			printf( __( 'Day: %s', $weluka_themename ), '<span>' . get_the_date() . '</span>' );

		elseif ( is_month() ) :
			printf( __( 'Month: %s', $weluka_themename ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', $weluka_themename ) ) . '</span>' );

		elseif ( is_year() ) :
			printf( __( 'Year: %s', $weluka_themename ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', $weluka_themename ) ) . '</span>' );

		else :
			_e( 'Archives', $weluka_themename );

		endif;
?>
	</h2>
<?php
	endif;

	if ( have_posts() ) : ?>

	<article class="archive-list sp-pad clearfix">
	<?php
    	get_template_part( 'content', 'archive' );
	?>
	</article>
	<?php weluka_pagination(); ?>

	<?php
	else:
		get_template_part( 'content', 'none' );
	endif;
} //404 endif
get_footer();
?>

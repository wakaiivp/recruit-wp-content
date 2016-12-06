<?php
/**
 * The page or single content template file.
 *
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * @update
 * ver1.0.1
 * taglcloud show or hide
 * ver1.0.3
 * eye-catch image
 * ver1.0.4
 * ver1.0.6
 */
global $weluka_themename, $welukaThemeOptions, $welukaPageSetting, $welukaBuilder, $welukaOnepageMode, $welukaOutContainerType, $post;

$is_single		= is_singular();
$hide_title		= isset( $welukaPageSetting['hide_title'] ) ? $welukaPageSetting['hide_title'] : 0;
$hide_postmeta	= isset( $welukaPageSetting['hide_postmeta'] ) ? $welukaPageSetting['hide_postmeta'] : 0;
$dispItem		= !empty( $welukaThemeOptions[WelukaThemeOptions::POST_SINGLE_META_ARRAY] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_SINGLE_META_ARRAY] : array();

//ver 1.0.1
$hide_tagcloud		= isset( $welukaThemeOptions[WelukaThemeOptions::HIDE_POST_TAGCLOUD] ) ? $welukaThemeOptions[WelukaThemeOptions::HIDE_POST_TAGCLOUD] : 0;
$tagcloud_pos		= isset( $welukaThemeOptions[WelukaThemeOptions::POST_TAGCLOUD_POSITION] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_TAGCLOUD_POSITION] : 'metabottom';
$hide_ps_tagcloud	= isset( $welukaPageSetting['hide_post_tagcloud'] ) ? $welukaPageSetting['hide_post_tagcloud'] : -1;
$hide_ps_tagcloud_pos	= isset( $welukaPageSetting['post_tagcloud_pos'] ) ? $welukaPageSetting['post_tagcloud_pos'] : '';
if( $hide_ps_tagcloud != -1 ) { $hide_tagcloud	= $hide_ps_tagcloud; }
if( $hide_ps_tagcloud_pos != '' ) { $tagcloud_pos	= $hide_ps_tagcloud_pos; }
$hide_post_paging	= isset( $welukaThemeOptions[WelukaThemeOptions::HIDE_POST_PAGING] ) ? $welukaThemeOptions[WelukaThemeOptions::HIDE_POST_PAGING] : 0;
$hide_ps_paging		= isset( $welukaPageSetting['hide_post_paging'] ) ? $welukaPageSetting['hide_post_paging'] : -1;
if( $hide_ps_paging != -1 ) { $hide_post_paging	= $hide_ps_paging; }

//ver 1.0.3
$post_eyecatch		= isset( $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH] : 0;
$post_eyecatch_align	= isset( $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_ALIGN] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_ALIGN] : '';
$post_eyecatch_shape	= isset( $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_SHAPE] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_SHAPE] : '';
$post_eyecatch_fit	= isset( $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_FITWIDTH] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_EYECATCH_FITWIDTH] : '';
$page_eyecatch		= isset( $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH] ) ? $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH] : 0;
$page_eyecatch_align	= isset( $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_ALIGN] ) ? $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_ALIGN] : '';
$page_eyecatch_shape	= isset( $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_SHAPE] ) ? $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_SHAPE] : '';
$page_eyecatch_fit	= isset( $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_FITWIDTH] ) ? $welukaThemeOptions[WelukaThemeOptions::PAGE_EYECATCH_FITWIDTH] : '';

//page setting
$eyecatch	= isset( $welukaPageSetting['hide_eyecatch'] ) ? $welukaPageSetting['hide_eyecatch'] : -1;
$eyecatch_align	= isset( $welukaPageSetting['eyecatch_align'] ) ? $welukaPageSetting['eyecatch_align'] : '';
$eyecatch_shape	= isset( $welukaPageSetting['eyecatch_shape'] ) ? $welukaPageSetting['eyecatch_shape'] : '';
$eyecatch_fit	= isset( $welukaPageSetting['eyecatch_fitwidth'] ) ? $welukaPageSetting['eyecatch_fitwidth'] : -1;

if( is_page() ) {
	if( $eyecatch == -1 ) { $eyecatch	= $page_eyecatch; }
	if( $eyecatch_align == '' ) { $eyecatch_align	= $page_eyecatch_align; }
	if( $eyecatch_shape == '' ) { $eyecatch_shape	= $page_eyecatch_shape; }
	if( $eyecatch_fit == -1 ) { $eyecatch_fit	= $page_eyecatch_fit; }
} elseif( is_single() ) {
	if( $eyecatch == -1 ) { $eyecatch	= $post_eyecatch; }
	if( $eyecatch_align == '' ) { $eyecatch_align	= $post_eyecatch_align; }
	if( $eyecatch_shape == '' ) { $eyecatch_shape	= $post_eyecatch_shape; }
	if( $eyecatch_fit == -1 ) { $eyecatch_fit	= $post_eyecatch_fit; }
}
if( $eyecatch_fit == 'on' ) { $eyecatch_fit = ' weluka-img-fullwidth'; } else { $eyecatch_fit = ''; }
//ver 1.0.3 end

while ( have_posts() ) : the_post();
	if ( ! $is_single ) : ?>
		<h2 class="page-title sp-pad"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

<?php else :
	if( ! $hide_title ) {
		//if( $welukaOnepageMode ) {
		if( $welukaOutContainerType !== '' ) {
			echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
		} ?>
        <h2 class="page-title sp-pad"><?php the_title(); ?></h2>
		<?php
        //if( $welukaOnepageMode ) {
		if( $welukaOutContainerType !== '' ) {
			echo '</div></div></div>';
		}
 	}
	endif;
?>

<article <?php post_class( 'entry' ); ?>>
<?php
	$tagclouds = '';

	if ( ! $is_single ) : ?>

	<?php else :

		//meta
		if( is_single() && ! $hide_postmeta ) {
			//sticky post
			$stickyHtml = "";
			$dateHtml = "";
			$catHtml = "";
			$authorHtml = "";
			$commentHtml = "";

			if ( is_sticky() && ! is_paged() ) {
				$stickyHtml = sprintf( '<span class="weluka-post-sticky">%s</span>', __( 'Featured', Weluka::$settings['plugin_name'] ) );
			}

			if( empty( $dispItem[WelukaThemeOptions::POST_META_DATE] ) ) {
				//$_date = get_the_date($welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT]);
				$_date = weluka_get_the_date(); //v1.0.4
				$dateHtml = '<span class="weluka-post-date">' . $_date . '</span>';
			}

			if( empty( $dispItem[WelukaThemeOptions::POST_META_CATEGORY] ) ) {
				$cats = get_the_category();
				if ( !empty( $cats ) ) {
					foreach ( $cats as $index => $cat ) {
						$catHtml .= '&nbsp;&nbsp;<span class="weluka-post-category-name"><a href="' . get_category_link( $cat ) . '">' . esc_html($cat->name) . '</a></span>';
					}
				}
			}

			if( empty( $dispItem[WelukaThemeOptions::POST_META_AUTHOR] ) ) {
				$_author = get_the_author();
				$authorHtml = '&nbsp;|&nbsp;<span class="weluka-post-author"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html($_author) . '</a></span>';
			}

			if( empty( $dispItem[WelukaThemeOptions::POST_META_COMMENT] ) ) {
				$_cnt = get_comments_number();
				$commentHtml = '&nbsp;|&nbsp;<span class="weluka-post-commentnum"><i class="fa fa-commenting"></i>' . $_cnt . '</span>';
			}
	
			if( $stickyHtml || $dateHtml || $catHtml || $authorHtml || $commentHtml ) {
				//if( $welukaOnepageMode ) {
				if( $welukaOutContainerType !== '' ) {
					echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
				}
				echo '<div class="weluka-single-meta sp-pad">' . $stickyHtml . $dateHtml . $catHtml . $authorHtml . $commentHtml . '</div>';
				//if( $welukaOnepageMode ) {
				if( $welukaOutContainerType !== '' ) {
					echo '</div></div></div>';
				}
			}
		}
		
		//ver1.0.1
		$tagclouds = get_the_tags();
		if( is_single() && ! $hide_tagcloud && ( $tagcloud_pos === 'metabottom' || $tagcloud_pos === 'both' ) ) {
			//weluka_tagcloud($tagclouds, $welukaOnepageMode); 
			weluka_tagcloud($tagclouds, $welukaOutContainerType); 
		}
		
		//ver 1.0.3 eyecatch
		if( $eyecatch && has_post_thumbnail() ){
			$size = "full"; //"medium"; //"full"
			$thumbnail =  wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
			$_thumb_src = esc_url( $thumbnail[0] );

			//Nelio External Featured Image plugin 対応 v1.0.6
			if ( function_exists( 'uses_nelioefi' ) && uses_nelioefi( $post->ID ) ) {
				$_thumb_src = nelioefi_get_thumbnail_src( $post->ID );
			}
			//Nelio v1.0.6 addend

			echo '<div class="sp-pad weluka-eyecatch ' . $eyecatch_align . '"><a href="' . $_thumb_src . '" class="fancybox" rel="gallery"><img src="' . $_thumb_src . '" title="' . get_the_title() . '" class="img-responsive ' . $eyecatch_shape . $eyecatch_fit . '" /></a></div>';			
		}

	endif; //$is_single endif

    the_content();
	
	//ver1.0.1
	if( is_single() && ! $hide_tagcloud && ( $tagcloud_pos === 'bottom' || $tagcloud_pos === 'both' ) ) {
		//weluka_tagcloud($tagclouds, $welukaOnepageMode, 'weluka-mgtop-l' ); 
		weluka_tagcloud($tagclouds, $welukaOutContainerType, 'weluka-mgtop-l' ); 
	}
?>
	<?php //TODO <!--nextpage--> wp_link_pages(array('before' => '<p><strong>' . esc_html__('Pages', $weluka_themename).':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
</article>
<?php
	//comment
	//if ( comments_open() || get_comments_number() ) :
	if( comments_open() ) :
		//if( $welukaOnepageMode ) {
		if( $welukaOutContainerType !== '' ) {
			echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
		}
		comments_template();
		if( $welukaOutContainerType !== '' ) {
			echo '</div></div></div>';
		}
	endif;

endwhile;
if( is_single() ) {
	if( ! $hide_post_paging ) { //ver1.0.1
		//if( $welukaOnepageMode ) {
		if( $welukaOutContainerType !== '' ) {
			echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
		} ?>
		<div class="post-nav-link weluka-mgtop-xl sp-pad clearfix">
			<div class="pull-left"><?php previous_post_link('%link', __( '&laquo; Prev', $weluka_themename ) ); ?></div>
			<div class="pull-right"><?php next_post_link('%link', __( 'Next &raquo;', $weluka_themename ) ); ?> </div>
    	</div>
		<?php if( $welukaOutContainerType !== '' ) {
				echo '</div></div></div>';
		}
	}
}
//edit_post_link( __( 'Edit', $weluka_themename ), '<div class="edit-link weluka-mgtop-s sp-pad">', '</div>' );
?>
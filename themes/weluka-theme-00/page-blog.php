<?php
/**
 * Template Name: Blog page
 *
 * The page template file.
 *
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * @update
 * ver1.0.1
 * ver1.0.4
 * ver1.0.5
 * ver1.0.6
 */

get_header();
global $weluka_themename, $welukaThemeOptions, $post, $welukaPageSetting, $welukaBuilder, $welukaOutContainerType;

$weluka_ptmp_settings	= maybe_unserialize( $weluka->get_postmeta($post->ID, WELUKA_PTMP_POST_META_NAME) );
$blog_cats				= isset( $weluka_ptmp_settings['weluka_ptemp_blogcats'] ) ? (array) $weluka_ptmp_settings['weluka_ptemp_blogcats'] : array();
$_limit					= !empty( $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_POST_NUM] ) ? (int) $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_POST_NUM] : '';
$weluka_blog_perpage	= isset( $weluka_ptmp_settings['weluka_ptemp_blog_perpage'] ) ? (int) $weluka_ptmp_settings['weluka_ptemp_blog_perpage'] : $_limit;
//$_excerptNum			= !empty( $welukaThemeOptions[WelukaThemeOptions::EXCERPT_STRING_NUM] ) ? (int) $welukaThemeOptions[WelukaThemeOptions::EXCERPT_STRING_NUM] : ''; v1.0.6
$_excerptNum			= isset( $welukaThemeOptions[WelukaThemeOptions::EXCERPT_STRING_NUM] ) && strlen( trim($welukaThemeOptions[WelukaThemeOptions::EXCERPT_STRING_NUM]) ) > 0 ? (int) trim($welukaThemeOptions[WelukaThemeOptions::EXCERPT_STRING_NUM]) : '';
$hide_title = isset( $welukaPageSetting['hide_title'] ) ? $welukaPageSetting['hide_title'] : 0;

//ver 1.0.1
$hide_tagcloud	= isset( $welukaThemeOptions[WelukaThemeOptions::HIDE_POSTLIST_TAGCLOUD] ) ? $welukaThemeOptions[WelukaThemeOptions::HIDE_POSTLIST_TAGCLOUD] : 0;
$tagcloud_pos	= isset( $welukaThemeOptions[WelukaThemeOptions::POSTLIST_TAGCLOUD_POSITION] ) ? $welukaThemeOptions[WelukaThemeOptions::POSTLIST_TAGCLOUD_POSITION] : 'metabottom';

//v1.0.6
if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_tagcloud'] ) ) {
	if( $weluka_ptmp_settings['weluka_ptemp_blog_tagcloud'] !== 9 ) {
		$hide_tagcloud = $weluka_ptmp_settings['weluka_ptemp_blog_tagcloud'];
	}
}
if( ! empty( $weluka_ptmp_settings['weluka_ptemp_blog_tagcloud_pos'] ) ) {
	$tagcloud_pos = $weluka_ptmp_settings['weluka_ptemp_blog_tagcloud_pos'];
}

$_dateFormat = '';
$_modifyDate = 0;
if( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT] ) { $_dateFormat = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT]; }
if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_date_format'] ) && strlen( trim( $weluka_ptmp_settings['weluka_ptemp_blog_date_format'] ) ) > 0 ) {
	$_dateFormat = $weluka_ptmp_settings['weluka_ptemp_blog_date_format'];
}
if( !empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ) { $_modifyDate = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY]; } 
if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_date_modify'] ) ) {
	if( $weluka_ptmp_settings['weluka_ptemp_blog_date_modify'] !== 9 ) {
		$_modifyDate = $weluka_ptmp_settings['weluka_ptemp_blog_date_modify'];
	}
}

$_excerptNum = isset( $weluka_ptmp_settings['weluka_ptemp_blog_excerpt_num'] ) && strlen( trim( $weluka_ptmp_settings['weluka_ptemp_blog_excerpt_num'] ) ) > 0 ? (int) trim( $weluka_ptmp_settings['weluka_ptemp_blog_excerpt_num'] ) : $_excerptNum;

$_buttonText = isset( $welukaThemeOptions[WelukaThemeOptions::MORE_BUTTON_TEXT] ) && strlen( trim($welukaThemeOptions[WelukaThemeOptions::MORE_BUTTON_TEXT]) ) > 0 ? trim( $welukaThemeOptions[WelukaThemeOptions::MORE_BUTTON_TEXT] ) : __('Read more', $weluka_themename);
$_buttonText = isset( $weluka_ptmp_settings['weluka_ptemp_blog_button_text'] ) && strlen( trim( $weluka_ptmp_settings['weluka_ptemp_blog_button_text'] ) ) > 0 ? trim( $weluka_ptmp_settings['weluka_ptemp_blog_button_text'] ) : $_buttonText;
//v1.0.6 addend

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		if( ! $hide_title ) {
			//if( $welukaOnepageMode ) {
			if( $welukaOutContainerType !== '' ) {
				echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
			} ?>
        	<h2 class="page-title sp-pad"><?php the_title(); ?></h2>
			<?php if( $welukaOutContainerType !== '' ) {
				echo '</div></div></div>';
			}
 		}

	    the_content();

		$cat_query = '';
		if ( !empty($blog_cats) ) $cat_query = '&cat=' . implode( ",", $blog_cats );
	
		$_paged = get_query_var('paged') ? get_query_var('paged') : 1 ;
		
		//v1.0.4
		$_orderby = '';
		//if( !empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ){ $_orderby = '&orderby=modified'; } v1.0.6
		if( $_modifyDate ){ $_orderby = '&orderby=modified'; }

		//$blog_page_query = new WP_Query( "posts_per_page=$weluka_blog_perpage&paged=" . $_paged . $cat_query );
		$blog_page_query = new WP_Query( "posts_per_page=$weluka_blog_perpage&paged=" . $_paged . $cat_query . $_orderby );
		//v1.0.4 end
		
		if ( $blog_page_query->have_posts() ) :

			$rowColumn		= isset($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS]) && strlen(trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS])) > 0 ? trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS]) : 1;
			$rowColumn		= isset( $weluka_ptmp_settings['weluka_ptemp_blog_cols'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_cols'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_cols'] : $rowColumn;
			$colNum			= WelukaBuilder::MAX_COLUMN / (int)$rowColumn; //$rowColumn = (1 or 2 or 3 or 4 or 6)
			$rowCnt			= 0;
			$colCnt			= 0;
			$listFormat		= isset( $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT] ) ? $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT] : 'mediatop';
			$listFormat		= isset( $weluka_ptmp_settings['weluka_ptemp_blog_format'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_format'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_format'] : $listFormat;
			$dispItem		= !empty( $welukaThemeOptions[WelukaThemeOptions::POST_LIST_META_ARRAY] ) ? $welukaThemeOptions[WelukaThemeOptions::POST_LIST_META_ARRAY] : array();
			$ct				= "";

			//v1.0.6 add
			$listFormatSm	= '';
			$rowColumnSm	= 1;
			$colNumSm		= 12;
			$colCntSm		= 0;
			$ctSm			= '';
			$listFormatXs	= '';
			$rowColumnXs	= 1;
			$colNumXs		= 12;
			$colCntXs		= 0;
			$ctXs			= '';
			$_const = 'WelukaThemeOptions::ARCHIVE_LIST_FORMAT_SM';
			if ( defined ( $_const ) ) :
				$listFormatSm	= isset( $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_SM] ) && strlen(trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_SM])) > 0 ? $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_SM] : '';
				$listFormatSm	= isset( $weluka_ptmp_settings['weluka_ptemp_blog_format_sm'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_format_sm'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_format_sm'] : $listFormatSm;
				$rowColumnSm	= isset($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_SM]) && strlen(trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_SM])) > 0 ? trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_SM]) : 1;
				$rowColumnSm	= isset( $weluka_ptmp_settings['weluka_ptemp_blog_cols_sm'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_cols_sm'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_cols_sm'] : $rowColumnSm;

				$colNumSm		= WelukaBuilder::MAX_COLUMN / (int)$rowColumnSm;
				$colCntSm		= 0;
				$ctSm = "";

				$listFormatXs	= isset( $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_XS] ) && strlen(trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_XS])) > 0 ? $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_FORMAT_XS] : '';
				$listFormatXs	= isset( $weluka_ptmp_settings['weluka_ptemp_blog_format_xs'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_format_xs'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_format_xs'] : $listFormatXs;
				$rowColumnXs	= isset($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_XS]) && strlen(trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_XS])) > 0 ? trim($welukaThemeOptions[WelukaThemeOptions::ARCHIVE_LIST_COLS_XS]) : 1;
				$rowColumnXs	= isset( $weluka_ptmp_settings['weluka_ptemp_blog_cols_xs'] ) && strlen( $weluka_ptmp_settings['weluka_ptemp_blog_cols_xs'] ) > 0 ? $weluka_ptmp_settings['weluka_ptemp_blog_cols_xs'] : $rowColumnXs;
	
				$colNumXs		= WelukaBuilder::MAX_COLUMN / (int)$rowColumnXs;
				$colCntXs		= 0;
				$ctXs = "";
			endif;
			
			$_hideDate	= !empty( $dispItem[WelukaThemeOptions::POST_META_DATE] ) ? $dispItem[WelukaThemeOptions::POST_META_DATE] : 0;
			if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_date'] ) ) {
				if( $weluka_ptmp_settings['weluka_ptemp_blog_date'] !== 9 ) {
					$_hideDate = $weluka_ptmp_settings['weluka_ptemp_blog_date'];
				}
			}

			$_hideCategory	= !empty( $dispItem[WelukaThemeOptions::POST_META_CATEGORY] ) ? $dispItem[WelukaThemeOptions::POST_META_CATEGORY] : 0;
			if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_category'] ) ) {
				if( $weluka_ptmp_settings['weluka_ptemp_blog_category'] !== 9 ) {
					$_hideCategory = $weluka_ptmp_settings['weluka_ptemp_blog_category'];
				}
			}
			
			$_hideAuthor	= !empty( $dispItem[WelukaThemeOptions::POST_META_AUTHOR] ) ? $dispItem[WelukaThemeOptions::POST_META_AUTHOR] : 0;
			if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_author'] ) ) {
				if( $weluka_ptmp_settings['weluka_ptemp_blog_author'] !== 9 ) {
					$_hideAuthor = $weluka_ptmp_settings['weluka_ptemp_blog_author'];
				}
			}

			$_hideComment	= !empty( $dispItem[WelukaThemeOptions::POST_META_COMMENT] ) ? $dispItem[WelukaThemeOptions::POST_META_COMMENT] : 0;
			if( isset( $weluka_ptmp_settings['weluka_ptemp_blog_comment'] ) ) {
				if( $weluka_ptmp_settings['weluka_ptemp_blog_comment'] !== 9 ) {
					$_hideComment = $weluka_ptmp_settings['weluka_ptemp_blog_comment'];
				}
			}
			//v1.0.6 add end
			
			if( $welukaOutContainerType !== '' ) {
				echo '<div class="' . $welukaOutContainerType . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
			} ?>
			<article class="archive-list sp-pad weluka-mgtop-xl clearfix">

			<?php while ( $blog_page_query->have_posts() ) : $blog_page_query->the_post();

			$_link	= get_the_permalink();
			$_title = get_the_title();
		
			$_ct = "";

			$titleHtml = "";
			$titleHtml = '<h3 class="weluka-list-title">{%TITLE%}</h3>';
			$s = "";
			if( $_link ) {
				$s = '<a href="' . esc_url($_link) . '" title="' . esc_attr($_title) . '">' . $_title . '</a>';
			} else {
				$s = $_title;
			}
			$titleHtml = str_replace("{%TITLE%}", $s, $titleHtml);

			//sticky post
			$stickyHtml = "";
			if ( is_sticky() && ! is_paged() ) {
				$stickyHtml = sprintf( '<span class="weluka-post-sticky">%s</span>', __( 'Featured', Weluka::$settings['plugin_name'] ) );
			}

			$dateHtml = "";
			$catHtml = "";
			$authorHtml = "";
			$commentHtml = "";

			//ver1.0.1 add
			$tagcloud_metabottom = '';
			$tagcloud_bottom = '';
		
			// post ony meta display
			if ( 'post' == get_post_type() ) {
				//if( empty( $dispItem[WelukaThemeOptions::POST_META_DATE] ) ) { //v1.0.6
				if( ! $_hideDate ) {
					$_date = weluka_get_the_date(false, $_dateFormat, $_modifyDate); //v1.0.4 v1.0.6 modify
					$dateHtml = '<span class="weluka-post-date">' . $_date . '</span>';
				}

				//if( empty( $dispItem[WelukaThemeOptions::POST_META_CATEGORY] ) ) { //v1.0.6
				if( ! $_hideCategory ) {
					$cats = get_the_category();
					if ( !empty( $cats ) ) {
						$n = 0;
						foreach ( $cats as $index => $cat ) {
							$p = $n === 0 ? '&nbsp;|&nbsp;' : '&nbsp;&nbsp;'; 
							$catHtml .= $p . '<span class="weluka-post-category-name"><a href="' . get_category_link( $cat ) . '">' . esc_html($cat->name) . '</a></span>';
							$n++;
						}
					}
				}

				//if( empty( $dispItem[WelukaThemeOptions::POST_META_AUTHOR] ) ) { //v1.0.6
				if( ! $_hideAuthor ) {
					$_author = get_the_author();
					$authorHtml = '&nbsp;|&nbsp;<span class="weluka-post-author"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html($_author) . '</a></span>';
				}

				//if( empty( $dispItem[WelukaThemeOptions::POST_META_COMMENT] ) ) { //v1.0.6
				if (! $_hideComment ) {
					$_cnt = get_comments_number();
					$commentHtml = '<span class="weluka-post-commentnum"><i class="fa fa-commenting"></i>' . $_cnt . '</span>';
				}

				//ver1.0.1 add
				$tagclouds = '';
				$tagclouds = get_the_tags();
				if( ! $hide_tagcloud && ( $tagcloud_pos === 'metabottom' || $tagcloud_pos === 'both' ) ) {
					$tagcloud_metabottom = weluka_tagcloud($tagclouds, '', 'weluka-mgtop-s', false); 
				}
				if( ! $hide_tagcloud && ( $tagcloud_pos === 'bottom' || $tagcloud_pos === 'both' ) ) {
					$tagcloud_bottom = weluka_tagcloud($tagclouds, '', 'weluka-mgtop-m weluka-mgbottom-m', false); 
				}
			//ver1.0.1 add end

			} // posttype=post endif

			$mediaHtml = "";
			if( has_post_thumbnail() ){
				$size = "post-medium"; //"medium"; //"full"
				$thumbnail =  wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
				$_thumb_src = esc_url( $thumbnail[0] );

				//Nelio External Featured Image plugin 対応 v1.0.6
				if ( function_exists( 'uses_nelioefi' ) && uses_nelioefi( $post->ID ) ) {
					$_thumb_src = nelioefi_get_thumbnail_src( $post->ID );
				}
			
			} else {
				//no-image
				$_thumb_src = esc_url( get_weluka_noimage() );
			}
		
			$mediaHtml = '<div class="weluka-list-media">';
			$media = array();
			if( $_link ) {
				$media['link']['action']	= WelukaBuilder::LINK_ACTION_GOTOLINK;
				$media['link']['href']		= $_link;
				$media['link']['target']	= "";
			}
			$media['alt']			= $_title;
			$media['fitwidth']		= 1;
			$media['title']			= $_title;
			$media['url']			= $_thumb_src;
			$media['border_color']	= "";
			$media['border_size']	= "";
			$media['border_style']	= "";
			$media['shape']			= "";

			$align = 'weluka-text-center';
			if( $listFormat === 'medialeft' || $listFormat === 'mediaright' ){  $align = ''; }
			$media['class_name']	= $align;
			$mediaHtml .= $welukaBuilder->widget_img_html($media, false);
			
			if( $stickyHtml ) { $mediaHtml .= $stickyHtml; }
			if( $commentHtml ) { $mediaHtml .= $commentHtml; }
			
			$mediaHtml .= '</div>';

			$metaHtml = '';
			if( $dateHtml || $catHtml || $authorHtml ) {
				$metaHtml = '<div class="weluka-list-meta">' . $dateHtml . $authorHtml . $catHtml . '</div>';
			}

			$excerptHtml = "";
			if( $_excerptNum !== '' ) { $_excerpt = $welukaBuilder->truncate_content( $post, $_excerptNum, false, true ); }
			else { $_excerpt = get_the_excerpt(); }
			$excerptHtml = '<div class="weluka-list-content">' . $_excerpt . '</div>';

			$moreHtml = "";
			if( $_link ) {
				//$button['text']	= !empty( $welukaThemeOptions[WelukaThemeOptions::MORE_BUTTON_TEXT] ) ? $welukaThemeOptions[WelukaThemeOptions::MORE_BUTTON_TEXT] : __('Read more', $weluka_themename); v1.0.6
				$button['text']	= $_buttonText;
				$button['link']['action']	= WelukaBuilder::LINK_ACTION_GOTOLINK;
				$button['link']['href']		= $_link;
				$button['link']['target']	= "";
				$button['button_color']		= 'weluka-btn-primary';
				$button['class_name']		= 'weluka-text-right';
				$moreHtml	= $welukaBuilder->widget_button_html($button, false);
			}

/* not display
			//editlink
			$editHtml = '';
			$editLink = get_edit_post_link();
			if( $editLink ) { 
				$editHtml = '<span class="edit-link"><a href="' . $editLink . '">'. __( 'Edit', $weluka_themename ) .'</a></span>';
			}
*/

			//v1.0.6
			$_arr = array(
				'media'		=> $mediaHtml,
				'title'		=> $titleHtml,
				'meta'		=> $metaHtml,
				'excerpt'	=> $excerptHtml,
				'tag_metabottom'	=> $tagcloud_metabottom,
				'tag_bottom'	=> $tagcloud_bottom,
				'more'			=> $moreHtml
			);
			$_ct = weluka_archivelist_block( $listFormat, $_arr, 'md' );
			$_ctSm = weluka_archivelist_block( $listFormatSm, $_arr, 'sm' );
			$_ctXs = weluka_archivelist_block( $listFormatXs, $_arr, 'xs' );
			$ct .= weluka_archivelist( $listFormat, $_ct, $rowColumn, $rowCnt, $colNum, $colCnt, 'md' );
			$ctSm .= weluka_archivelist( $listFormatSm, $_ctSm, $rowColumnSm, $rowCnt, $colNumSm, $colCntSm, 'sm' );
			$ctXs .= weluka_archivelist( $listFormatXs, $_ctXs, $rowColumnXs, $rowCnt, $colNumXs, $colCntXs, 'xs' );
			//v1.0.6 modify end

			$rowCnt++;
		
			endwhile;

			//v1.0.6
			$ct .= weluka_archivelist_end( $listFormat, $rowColumn, $colCnt );
			$ctSm .= weluka_archivelist_end( $listFormatSm, $rowColumnSm, $colCntSm );
			$ctXs .= weluka_archivelist_end( $listFormatXs, $rowColumnXs, $colCntXs );
			$hiddenClass = '';
			if( $listFormatSm ) { $hiddenClass = 'hidden-sm'; }
			if( $listFormatXs ) { $hiddenClass .= ' hidden-xs'; }
			$ct = '<div class="' . $hiddenClass . '">' . $ct . '</div>';

			if( $ctSm ) {
				$hiddenClass = '';
				$hiddenClass = 'hidden-lg hidden-md hidden-xs';
				$ctSm = '<div class="' . $hiddenClass . '">' . $ctSm . '</div>';
				$ct .= $ctSm;
			}
			if( $ctXs ) {
				$hiddenClass = '';
				$hiddenClass = 'hidden-lg hidden-md hidden-sm';
				$ctXs = '<div class="' . $hiddenClass . '">' . $ctXs . '</div>';
				$ct .= $ctXs;
			}
			//v1.0.6 addend
			echo do_shortcode(stripslashes($ct)); ?>

		</article>
		<?php weluka_pagination( $blog_page_query );
		if( $welukaOutContainerType !== '' ) :
				echo '</div></div></div>';
		endif;

	else :
		wp_reset_postdata();

	endif; //blog_page_query hav_post endif

	endwhile; //main loop

else:

	get_template_part( 'content', 'none' );

endif;

get_footer();
?>

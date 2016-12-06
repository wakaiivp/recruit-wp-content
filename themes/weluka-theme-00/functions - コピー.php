<?php
/**
 * @package Weluka
 * @since Weluka Theme 00 1.0
 *
 */

// config
define( 'WELUKA_PLUGIN_OFFICIAL_URL', '//www.weluka.me' ); //@since 1.0
define( 'WELUKA_PTMP_POST_META_NAME', 'weluka_ptmp_meta' ); //@since 1.0

//revision num
//define('WP_POST_REVISIONS', 15); //20before

/**
 * @since 1.0
 * @update
 * 1.0.2
 * 1.0.4
 */
add_action( 'after_setup_theme', 'weluka_setup_theme' );
if ( ! function_exists( 'weluka_setup_theme' ) ){
	function weluka_setup_theme() {
		global $weluka_themename, $weluka_theme_short, $welukaBuilder, $welukaTheme, $welukaThemeOptions, $welukaThemeColors, $welukaContainerClass,
			   $welukaDefaultNoImage, $weluka_copyright, $weluka_update_theme_checker;

		//error_reporting(0);

		$template_directory = get_template_directory();
		$weluka_themename = get_stylesheet();
		$weluka_theme_short = ""; //mb_strtolower( str_replace( array("-", " "), "", $weluka_themename ) );
		load_theme_textdomain( $weluka_themename, $template_directory . '/languages' );

		// wodpress support functions
		//title tag auto create wp4.1
		//add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
	 	 * Switch default core markup for search form, comment form, and comments
	     * to output valid HTML5.
	     */
		add_theme_support( 'html5', array(
			'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
		) );
		
		// Post Thumbnails (eyecatch)
		if ( function_exists( 'add_image_size' ) ) {
			add_theme_support( 'post-thumbnails' );
			add_image_size( 'post-large', 1200, 675, true );
			add_image_size( 'post-medium', 800, 450, true );
			add_image_size( 'post-thumb', 400, 225, true );
		}
		
		if( check_weluka_plugin() === "" ){ 

			require_once( $template_directory . '/includes/class-theme-options.php' );
					
			$weluka					= Weluka::get_instance();

			if( $weluka->check_license() ) {
				require_once( $template_directory . '/includes/theme-update-checker.php' );
				add_action('admin_init', 'weluka_theme_update' );
			}

			$welukaBuilder			= WelukaBuilder::get_instance();
			$welukaTheme			= WelukaThemeOptions::get_instance();
			$welukaTheme->init();
			$weluka_theme_short		= $welukaTheme->get_option_name();
//v1.0.2
			//$welukaThemeOptions 	= WelukaAdminSettings::get_site_options( $weluka_theme_short );
			//$welukaThemeColors 	= WelukaAdminSettings::get_site_options( $weluka_theme_short . '_color' );
			$welukaThemeOptions 	= get_option( $weluka_theme_short );
			$welukaThemeColors 		= get_option( $weluka_theme_short . '_color' );
//v1.0.2
			$welukaContainerClass	= WelukaAdminSettings::get_options( WelukaAdminSettings::CONTAINER_TYPE );
			if( ! $welukaContainerClass ) {
				$welukaContainerClass = 'weluka-container';
			}
			
			//$welukaDefaultNoImage = $weluka->set_url( get_template_directory_uri() . '/images/noimage.gif' );
			$welukaDefaultNoImage = Weluka::$settings['noimage'];
			//admin
			add_action( 'admin_enqueue_scripts', 'weluka_admin_load_scripts_styles' );
			add_action( 'admin_init', 'weluka_page_templates_metabox' );
			add_action( 'save_post', 'weluka_page_template_save_meta', 10, 2 );
			add_action( 'wp_enqueue_scripts', 'weluka_theme_load_scripts_styles' );

			register_nav_menus(
				array(
					'primary-menu'	=> __( 'Primary Menu', $weluka_themename ),
					'footer-menu'	=> __( 'Footer Menu', $weluka_themename )
				)
			);
		
			weluka_register_sidebar();

			add_action( 'pre_get_posts', 'weluka_pre_home_query' );
			add_action( 'pre_get_posts', 'weluka_pre_archive_query' );

			$weluka_copyright = '© 2015 <a href="//www.weluka.me" target="_blank">weluka.</a>';

			add_editor_style('editor-style.css');
			//tinymce body class weluka plugin apply_filter
			add_filter('weluka_tinymce_body_class_args', 'weluka_set_tinymce_body_class_args', 10001);

			add_action( 'wp_head', 'weluka_theme_add_head', 999);
			
			add_filter('image_send_to_editor', 'weluka_image_send_to_editor', 10, 8);

			//ver1.0.1 add
			if( !empty( $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED_BUILDER_ENABLE] ) ) {
				add_action('wp_footer', 'weluka_theme_add_footer');
			}else{
				if( ! $weluka->is_active_builder() ) { add_action('wp_footer', 'weluka_theme_add_footer'); }
			}
		} //weluka endif
	}
}

/**
 * @since 1.0
 */
function check_weluka_plugin() {
	global $weluka_themename;
	$errmsg = "";
	if ( ! class_exists( 'Weluka', false ) ) {
		$errmsg = 
			'<div class="error"><p>' . sprintf( __('This theme does not work with Weluka plugin is not valid. Please Activate by introducing Weluka plugin. <a href="%s" target="_blank">Please click here for Weluka plugin.</a>', $weluka_themename ), WELUKA_PLUGIN_OFFICIAL_URL ) . '</p></div>';
		echo $errmsg;
	}
	return $errmsg; 
}

/**
 * @since 1.0.4
 * @update
 * v1.0.6
 */
//function weluka_get_the_date( $echo = false ) {
function weluka_get_the_date( $echo = false, $format = '', $modify = '' ) {
	global $welukaThemeOptions;
	$ret = '';

	$const = 'WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY';
	if ( class_exists( 'Weluka' ) && defined ( $const ) ) :
		$_format = '';
		$_modifyDate = 0;
		
		//v1.0.6
		if( $format !== '' ) { $_format = $format; }
		else {
			if( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT] ) { $_format = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT]; }
		}
		if( $modify !== '' ) { $_modifyDate = $modify; }
		else {
			if( !empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ) { $_modifyDate = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY]; } 
		}
		/*
		if( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT] ) { $_format = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_FORMAT]; }
		if( !empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ) { $_modifyDate = $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY]; } 
		*/
		//v1.0.6 end
		$ret = Weluka::get_instance()->get_the_postdate( $_modifyDate, $_format, false );
	else :
		$ret = get_the_date();
	endif;
	
	if( $echo ) { echo $ret; } else { return $ret; }
}

if ( class_exists( 'Weluka' ) ) :

/**
 * @since 1.0.1
 */
//function weluka_tagcloud($tags, $onepage = false, $class="weluka-mgtop-s", $echo = true) {
function weluka_tagcloud($tags, $outct = '', $class="weluka-mgtop-s", $echo = true) {
	$ret = '';
	if ($tags) {
		//if( $onepage ) {
		if( $outct !== '' ) {
			$ret .= '<div class="' . $outct . '"><div class="weluka-row clearfix"><div class="weluka-col weluka-col-md-12">';
		}
        $ret .= '<div class="tagcloud ' . $class .'">';
		foreach($tags as $tag) :
			$tag_link = get_tag_link($tag->term_id);
			$ret .= '<a href="' . esc_url( $tag_link ) . '" title="' . esc_attr($tag->name) . '">' . $tag->name . '</a>';
		endforeach;
		$ret .= '</div>';
		if( $outct ) { $ret .= '</div></div></div>'; }
    }
	
	if( $echo ) {
		echo $ret;
	}else{
		return $ret;
	}
}

/**
 * @since 1.0
 */
function weluka_set_tinymce_body_class_args($body_class) {
	global $welukaContainerClass;
	$body_class = $welukaContainerClass;
	return $body_class;
}

/**
 * @since 1.0
 */
function weluka_admin_load_scripts_styles( $hook_suffix ) {
	global $weluka_theme_short, $weluka;
	if ( in_array($hook_suffix, array('post.php','post-new.php')) ) {
		wp_enqueue_script( $weluka_theme_short . '-script', $weluka->set_url( get_template_directory_uri() ) . '/js/admin_script.js', array('jquery'), '1.0', true);	
	}
}

/**
 * @since 1.0
 */
function weluka_theme_load_scripts_styles(){
	global $weluka_theme_short, $weluka;
	$template_url = get_template_directory_uri();

	wp_enqueue_style( $weluka_theme_short . '-style', $weluka->set_url( get_stylesheet_uri() ) );

	$colorKey = get_weluka_theme_color( WelukaThemeOptions::COLOR_TYPE );
	if( isset( $_GET['mode'] ) && $_GET['mode'] === 'cp' ) {
		$colorKey = isset( $_POST[WelukaThemeOptions::COLOR_TYPE] ) ? $_POST[WelukaThemeOptions::COLOR_TYPE] : 'default';
	}

	if( !empty( $colorKey ) && $colorKey !== 'default' ) {
		wp_enqueue_style( $weluka_theme_short . '-style-' . $colorKey, $weluka->set_url( $template_url ) . '/style-' . $colorKey . '.css' );
	}
}

/** 
 * @since 1.0
 */
function weluka_theme_add_head() {
	global $weluka, $weluka_theme_short, $welukaThemeOptions;

	if( isset( $_GET['mode'] ) && $_GET['mode'] === 'cp' ) :
		echo '<meta name="robots" content="noindex,nofollow" />';
		$colorData = $_POST;
	else:
		$colorData = get_weluka_theme_color( WelukaThemeOptions::COLOR_ARRAY );
    endif;

	$customCss = "";
	if( !empty( $welukaThemeOptions[WelukaThemeOptions::CUSTOM_CSS] ) ) { $customCss = wp_unslash( $welukaThemeOptions[WelukaThemeOptions::CUSTOM_CSS] ); }

	if( !empty( $colorData ) ) :
		$style = '<style type="text/css">';

		$body = '';
		$panel = '';
		if( $colorData['body_bg_color'] !== '' ) {
			$body .= 'background-color:' . $colorData['body_bg_color'] . ';';			
		}
		if( $colorData['body_text_color'] !== '' ) {
			$body .= 'color:' . $colorData['body_text_color'] . ';';
		}
		if( $body !== '' ) $style .= 'body { ' . $body . ' }';

		//sitename
		if( $colorData['sitename_color'] !== '' ) {
			$style .= '.logo { color:' . $colorData['sitename_color'] . ' !important; }';			
		}

		//block
		$block = "";
		if( $colorData['block_bg_color'] !== '' ) {
			$block .= 'background-color:' . $colorData['block_bg_color'] . ';';
		}
		if( $colorData['block_border_color'] !== '' ) {
			$block .= 'border-color:' . $colorData['block_border_color'] . ';';
		}
		if( $block !== '' ) {
			$style .= '#main-content article.entry,' .
					'.archive-list .weluka-list-row.medialeft,' .
					'.weluka-sidebar .weluka-list-row.medialeft,' .
					'.archive-list .weluka-list-row.mediaright,' .
					'.weluka-sidebar .weluka-list-row.mediaright,' .
					'.archive-list .weluka-list-row [class*="weluka-col-"] .wrap,' .
					'.weluka-sidebar .weluka-list-row [class*="weluka-col-"] .wrap,' .
					'.weluka-sidebar .textwidget,' .
					'.weluka-sidebar .widget > ul,' .
					'.weluka-sidebar .widget ul.menu, '.
					'.weluka-sidebar .widget .tagcloud,' .
					'.page-title,'.
					'.weluka-sidebar .widgettitle,' .
					'.weluka-sidebar .weluka-text,' .
					/*'.weluka-sidebar #calendar_wrap,' . */
					'#comments {' . $block . ' }';
		}

		//heading
		if( $colorData['heading_text_color'] !== '' ) {
			$style .= 'h1,h2,h3,h4,h5,h6,.page-title { color:' . $colorData['heading_text_color'] . '; }';
			$style .= '.page-title, blockquote { border-color:' . $colorData['heading_text_color'] . '; }';
			$style .= '#wp-calendar thead th { background-color:' . $colorData['heading_text_color'] . '; }';
		}
		
		//link
		if( $colorData['link_color'] !== '' ) {
			$style .= 'a { color:' . $colorData['link_color'] . '; }';
		}
		if( $colorData['link_hover_color'] !== '' ) {
			$style .= 'a:hover, a:focus { color:' . $colorData['link_hover_color'] . '; }';
		}

		//header
		$header = '';
		if( $colorData['hd_bg_color'] !== '' ) {
			$header .= 'background-color:' . $colorData['hd_bg_color'] . ';';			
		}
		if( $colorData['hd_text_color'] !== '' ) {
			$header .= 'color:' . $colorData['hd_text_color'] . ';';
		}
		//header border
		if( $colorData['hd_border_color'] !== '' ) {
			$header .= 'border-bottom-color:' . $colorData['hd_border_color'] . ';';
		}
		if( $header !== '' ) $style .= '#weluka-main-header { ' . $header . '; }';

		//header heading
		if( $colorData['hd_heading_text_color'] !== '' ) {
			$style .= '#weluka-main-header h1, #weluka-main-header h2, #weluka-main-header h3, #weluka-main-header h4, #weluka-main-header h5, #weluka-main-header h6 { color:' . $colorData['hd_heading_text_color'] . '; }';
		}
		//header link
		if( $colorData['hd_link_color'] !== '' ) {
			$style .= '#weluka-main-header a { color:' . $colorData['hd_link_color'] . '; }';
		}
		if( $colorData['hd_link_hover_color'] !== '' ) {
			$style .= '#weluka-main-header a:hover, #weluka-main-header a:focus { color:' . $colorData['hd_link_hover_color'] . '; }';
		}
		//main navi
		if( $colorData['mainnav_color'] !== '' ) {
			$style .= '#main-nav button, #main-nav .nav { background-color:' .  $colorData['mainnav_color'] . '; }';
		}
		//main navi border
		if( $colorData['mainnav_border_color'] !== '' ) {
			$style .= '#main-nav button, #main-nav .nav { border-color:' . $colorData['mainnav_border_color'] . '; }';
			$style .= '#main-nav button:hover, #main-nav button:focus, #main-nav a:hover, #main-nav a:focus, #main-nav .open > a, #main-nav .open a:hover, #main-nav .open a:focus { background-color:' .  $colorData['mainnav_border_color'] . '; }';
		}
		//main navi text
		if( $colorData['mainnav_text_color'] !== '' ) {
			$style .= '#main-nav a, #main-nav a:hover, #main-nav a:focus, #main-nav .open > a, #main-nav .open a:hover, #main-nav .open a:focus { color:' . $colorData['mainnav_text_color'] . '; }';
			$style .= '#main-nav button .icon-bar { background-color:' . $colorData['mainnav_text_color'] . '; }';
		}

		//footer
		$footer = '';
		if( $colorData['ft_bg_color'] !== '' ) {
			$footer .= 'background-color:' . $colorData['ft_bg_color'] . ';';			
		}
		if( $colorData['ft_text_color'] !== '' ) {
			$footer .= 'color:' . $colorData['ft_text_color'] . ';';
		}
		//footer border
		if( $colorData['ft_border_color'] !== '' ) {
			$footer .= 'border-top-color:' . $colorData['ft_border_color'] . ';';
		}
		if( $footer !== '' ) $style .= '#weluka-main-footer { ' . $footer . '; }';

		//footer heading
		if( $colorData['ft_heading_text_color'] !== '' ) {
			$style .= '#weluka-main-footer h1, #weluka-main-footer h2, #weluka-main-footer h3, #weluka-main-footer h4, #weluka-main-footer h5, #weluka-main-footer h6 { color:' . $colorData['ft_heading_text_color'] . '; }';
		}
		//footer link
		if( $colorData['ft_link_color'] !== '' ) {
			$style .= '#weluka-main-footer a { color:' . $colorData['ft_link_color'] . '; }';
		}
		if( $colorData['ft_link_hover_color'] !== '' ) {
			$style .= '#weluka-main-footer a:hover, #weluka-main-footer a:focus { color:' . $colorData['ft_link_hover_color'] . '; }';
		}
		
		//primary
		if( $colorData['primary_color'] !== '' ) {
			$style .= '.weluka-text-primary { color:' . $colorData['primary_color'] . '; }';
			$style .= '.weluka-bg-primary { background-color:' . $colorData['primary_color'] . '; }';
			$style .= '.label-primary { background-color:' . $colorData['primary_color'] . '; }';

			//button
			$style .= '.weluka-btn-primary, .weluka-btn-primary .badge, .weluka-btn-primary.disabled, .weluka-btn-primary[disabled], fieldset[disabled] .weluka-btn-primary, .weluka-btn-primary.disabled:hover, .weluka-btn-primary[disabled]:hover, fieldset[disabled] .weluka-btn-primary:hover, .weluka-btn-primary.disabled:focus, .weluka-btn-primary[disabled]:focus, fieldset[disabled] .weluka-btn-primary:focus, .weluka-btn-primary.disabled.focus, .weluka-btn-primary[disabled].focus, fieldset[disabled] .weluka-btn-primary.focus, .weluka-btn-primary.disabled:active, .weluka-btn-primary[disabled]:active, fieldset[disabled] .weluka-btn-primary:active, .weluka-btn-primary.disabled.active, .weluka-btn-primary[disabled].active, fieldset[disabled] .weluka-btn-primary.active { background-color:' . $colorData['primary_color'] . '; }';

			$style .= '.weluka-btn-primary:hover, .weluka-btn-primary:focus, .weluka-btn-primary.focus, .weluka-btn-primary:active, .weluka-btn-primary.active, .open > .dropdown-toggle.weluka-btn-primary { border-color:' .  $colorData['primary_color'] . ' !important; }';

			$style .= '.weluka-btn-link, .weluka-btn-link .badge { color:' . $colorData['primary_color'] . '; }';

			//pagination comment link
			$style .= '.pagination > .active > a, .pagination > .active > span, .pagination > .active > a:hover, .pagination > .active > span:hover, .pagination > .active > a:focus, .pagination > .active > span:focus { background-color:' . $colorData['primary_color'] . ';border-color:' . $colorData['primary_color'] . '; }';
			$style .= '.pagination > li > a, .pagination > li > span { color:' . $colorData['primary_color'] . '; }';
			$style .= '.pagination > li > a:hover, .pagination > li > span:hover, .pagination > li > a:focus, .pagination > li > span:focus { color:' . $colorData['primary_color'] . '; }';

			$style .= '.weluka-pagination .current, .comment-form input[type="submit"], .weluka-pagination a:hover, .post-nav-link a:hover, .comment-navigation a:hover { background-color:' . $colorData['primary_color'] . '; }';
			$style .= '.weluka-pagination span, .weluka-pagination a, .post-nav-link a, .comment-navigation a { color:' . $colorData['primary_color'] . '; }';

			//nav
			$style .= '.weluka-navbar-darkblue, .weluka-navbar-darkblue .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-darkblue, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue { background-color:' . $colorData['primary_color'] . '; }';

			//accordion panel
			$style .= '.panel-primary > .panel-heading { background-color:' . $colorData['primary_color'] . '; }';
			$style .= '.panel-primary > .panel-heading .badge { color:' . $colorData['primary_color'] . '; }';

			//tab
			$style .= '.nav-tabs a, .nav-pills > li > a { color:' . $colorData['primary_color'] . '; }';
			$style .= '.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus { background-color:' . $colorData['primary_color'] . '; }';

			//list-gourp
			$style .= '.list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus { background-color:' . $colorData['primary_color'] . ';border-color:' . $colorData['primary_color'] . '; }';
			
			//tagcloud
			$style .= '.tagcloud a { background-color:' . $colorData['primary_color'] . '; }';
		}
		//primary border
		if( $colorData['primary_border_color'] !== '' ) {
			//bg
			$style .= 'a.weluka-bg-primary:hover { background-color:' . $colorData['primary_border_color'] . '; }';
			//button
			$style .= '.weluka-btn-primary, .weluka-btn-primary.disabled, .weluka-btn-primary[disabled], fieldset[disabled] .weluka-btn-primary, .weluka-btn-primary.disabled:hover, .weluka-btn-primary[disabled]:hover, fieldset[disabled] .weluka-btn-primary:hover, .weluka-btn-primary.disabled:focus, .weluka-btn-primary[disabled]:focus, fieldset[disabled] .weluka-btn-primary:focus, .weluka-btn-primary.disabled.focus, .weluka-btn-primary[disabled].focus, fieldset[disabled] .weluka-btn-primary.focus, .weluka-btn-primary.disabled:active, .weluka-btn-primary[disabled]:active, fieldset[disabled] .weluka-btn-primary:active, .weluka-btn-primary.disabled.active, .weluka-btn-primary[disabled].active, fieldset[disabled] .weluka-btn-primary.active { border-color:' . $colorData['primary_border_color'] . ' !important; }';
			$style .= '.weluka-btn-primary:hover, .weluka-btn-primary:focus, .weluka-btn-primary.focus, .weluka-btn-primary:active, .weluka-btn-primary.active, .open > .dropdown-toggle.weluka-btn-primary { background-color:' . $colorData['primary_border_color'] . '; }';
			
			$style .= '.weluka-btn-link:hover, .weluka-btn-link:focus { color:' . $colorData['primary_border_color'] . '; }';

			//nav
			$style .= '.weluka-navbar-darkblue, .weluka-navbar-darkblue .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-darkblue, .weluka-navbar-darkblue a:hover, .weluka-navbar-darkblue a:focus, .weluka-navbar-darkblue .open > a, .weluka-navbar-darkblue .open a:hover, .weluka-navbar-darkblue .open a:focus, .weluka-navbar-darkblue .dropdown-menu a:hover, .weluka-navbar-darkblue .dropdown-menu a:focus, .weluka-navbar-darkblue .navbar-toggle, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue, .weluka-navbar-darkblue .navbar-collapse, .weluka-navbar-darkblue .navbar-form, .weluka-nav-bar-v.weluka-navbar-darkblue .nav, .weluka-nav-bar-v.weluka-navbar-darkblue .nav li, .weluka-nav-bar-ham .nav.weluka-navbar-darkblue { border-color:' . $colorData['primary_border_color'] . ' !important; }';
			$style .= '.weluka-navbar-darkblue a:hover, .weluka-navbar-darkblue a:focus, .weluka-navbar-darkblue .open > a, .weluka-navbar-darkblue .open a:hover, .weluka-navbar-darkblue .open a:focus, .weluka-navbar-darkblue .dropdown-menu a:hover, .weluka-navbar-darkblue .dropdown-menu a:focus, .weluka-navbar-darkblue .navbar-toggle:hover, .weluka-navbar-darkblue .navbar-toggle:focus, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue:hover, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue:focus { background-color:' . $colorData['primary_border_color'] . '; }';
			
			//accordion panel
			$style .= '.panel-primary > .panel-heading { border-color:' . $colorData['primary_border_color'] . '; }';
			$style .= '.panel-primary > .panel-heading + .panel-collapse > .panel-body { border-top-color:' . $colorData['primary_border_color'] . '; }';
			$style .= '.panel-primary > .panel-footer + .panel-collapse > .panel-body { border-bottom-color:' . $colorData['primary_border_color'] . '; }';
			$style .= '.panel-primary { border-color:' . $colorData['primary_border_color'] . '; }';

			//list-group
			$style .= '.list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus { background-color:' . $colorData['primary_border_color'] . ';border-color:' . $colorData['primary_border_color'] . '; }';

			//comment
			$style .= '.comment-form input[type="submit"]:hover, .comment-form input[type="submit"]:focus { background:' . $colorData['primary_border_color'] . '; }';
			//tag cloud
			$style .= '.tagcloud a:hover { background-color:' . $colorData['primary_border_color'] . '; }';
		}
		//primary text
		if( $colorData['primary_text_color'] !== '' ) {
			$style .= '.weluka-bg-primary { color:' . $colorData['primary_text_color'] . '; }';
			$style .= '.label-primary { color:' . $colorData['primary_text_color'] . '; }';

			//button, a.bg
			$style .= '.weluka-btn-primary, .weluka-btn-primary:hover, .weluka-btn-primary:focus, .weluka-btn-primary.focus, .weluka-btn-primary:active, .weluka-btn-primary.active, .open > .dropdown-toggle.weluka-btn-primary, a.weluka-bg-primary:hover { color:' . $colorData['primary_text_color'] . ' !important; }';
			//$style .= '.weluka-btn-link { color:' . $colorData['primary_text_color'] . '; }';

			//nav
			$style .= '.weluka-navbar-darkblue, .weluka-navbar-darkblue a, .weluka-navbar-darkblue .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-darkblue { color: ' . $colorData['primary_text_color'] . ' !important; }';
			$style .= '.weluka-navbar-darkblue .navbar-toggle .icon-bar, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-darkblue .icon-bar { background-color:' . $colorData['primary_text_color'] . '; }';

			//accordion panel
			$style .= '.panel-primary > .panel-heading { color:' . $colorData['primary_text_color'] . '; }';

			//list group
			$style .= '.list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus { color:' . $colorData['primary_text_color'] . '; }';
			
			//tagcloud
			$style .= '.tagcloud a, .tagcloud a:hover { color:' . $colorData['primary_text_color'] . '; }';			
		}

		//success
		if( $colorData['success_color'] !== '' ) {
			$style .= '.weluka-text-success { color:' . $colorData['success_color'] . '; }';
			$style .= '.weluka-bg-success { background-color:' . $colorData['success_color'] . '; }';
			$style .= '.label-success { background-color:' . $colorData['success_color'] . '; }';

			//button
			$style .= '.weluka-btn-success, .weluka-btn-success .badge, .weluka-btn-success.disabled, .weluka-btn-success[disabled], fieldset[disabled] .weluka-btn-success, .weluka-btn-success.disabled:hover, .weluka-btn-success[disabled]:hover, fieldset[disabled] .weluka-btn-success:hover, .weluka-btn-success.disabled:focus, .weluka-btn-success[disabled]:focus, fieldset[disabled] .weluka-btn-success:focus, .weluka-btn-success.disabled.focus, .weluka-btn-success[disabled].focus, fieldset[disabled] .weluka-btn-success.focus, .weluka-btn-success.disabled:active, .weluka-btn-success[disabled]:active, fieldset[disabled] .weluka-btn-success:active, .weluka-btn-success.disabled.active, .weluka-btn-success[disabled].active, fieldset[disabled] .weluka-btn-success.active { background-color:' . $colorData['success_color'] . '; }';

			$style .= '.weluka-btn-success:hover, .weluka-btn-success:focus, .weluka-btn-success.focus, .weluka-btn-success:active, .weluka-btn-success.active, .open > .dropdown-toggle.weluka-btn-success { border-color:' .  $colorData['success_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-green, .weluka-navbar-green .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-green, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green { background-color:' . $colorData['success_color'] . '; }';

			//accordion panel
			$style .= '.panel-success > .panel-heading { background-color:' . $colorData['success_color'] . '; }';
			$style .= '.panel-success > .panel-heading .badge { color:' . $colorData['success_color'] . '; }';
			
			//alert
			$style .= '.alert-success { background-color:' . $colorData['success_color'] . '; }';
			
			//list-gourp
			$style .= '.list-group-item-success { background-color:' . $colorData['success_color'] . '; }';
			
			//table
			$style .= '.table > thead > tr > td.success, .table > tbody > tr > td.success, .table > tfoot > tr > td.success, .table > thead > tr > th.success, .table > tbody > tr > th.success, .table > tfoot > tr > th.success, .table > thead > tr.success > td, .table > tbody > tr.success > td, .table > tfoot > tr.success > td, .table > thead > tr.success > th, .table > tbody > tr.success > th, .table > tfoot > tr.success > th { background-color:' .  $colorData['success_color'] . '; }';
		}
		//success border
		if( $colorData['success_border_color'] !== '' ) {
			//bg
			$style .= 'a.weluka-bg-success:hover { background-color:' . $colorData['success_border_color'] . '; }';

			//button
			$style .= '.weluka-btn-success, .weluka-btn-success.disabled, .weluka-btn-success[disabled], fieldset[disabled] .weluka-btn-success, .weluka-btn-success.disabled:hover, .weluka-btn-success[disabled]:hover, fieldset[disabled] .weluka-btn-success:hover, .weluka-btn-success.disabled:focus, .weluka-btn-success[disabled]:focus, fieldset[disabled] .weluka-btn-success:focus, .weluka-btn-success.disabled.focus, .weluka-btn-success[disabled].focus, fieldset[disabled] .weluka-btn-success.focus, .weluka-btn-success.disabled:active, .weluka-btn-success[disabled]:active, fieldset[disabled] .weluka-btn-success:active, .weluka-btn-success.disabled.active, .weluka-btn-success[disabled].active, fieldset[disabled] .weluka-btn-success.active { border-color:' . $colorData['success_border_color'] . ' !important; }';
			$style .= '.weluka-btn-success:hover, .weluka-btn-success:focus, .weluka-btn-success.focus, .weluka-btn-success:active, .weluka-btn-success.active, .open > .dropdown-toggle.weluka-btn-success { background-color:' . $colorData['success_border_color'] . '; }';
			
			//nav
			$style .= '.weluka-navbar-green, .weluka-navbar-green .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-green, .weluka-navbar-green a:hover, .weluka-navbar-green a:focus, .weluka-navbar-green .open > a, .weluka-navbar-green .open a:hover, .weluka-navbar-green .open a:focus, .weluka-navbar-green .dropdown-menu a:hover, .weluka-navbar-green .dropdown-menu a:focus, .weluka-navbar-green .navbar-toggle, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green, .weluka-navbar-green .navbar-collapse, .weluka-navbar-green .navbar-form, .weluka-nav-bar-v.weluka-navbar-green .nav, .weluka-nav-bar-v.weluka-navbar-green .nav li, .weluka-nav-bar-ham .nav.weluka-navbar-green { border-color:' . $colorData['success_border_color'] . ' !important; }';
			$style .= '.weluka-navbar-green a:hover, .weluka-navbar-green a:focus, .weluka-navbar-green .open > a, .weluka-navbar-green .open a:hover, .weluka-navbar-green .open a:focus, .weluka-navbar-green .dropdown-menu a:hover, .weluka-navbar-green .dropdown-menu a:focus, .weluka-navbar-green .navbar-toggle:hover, .weluka-navbar-green .navbar-toggle:focus, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green:hover, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green:focus { background-color:' . $colorData['success_border_color'] . '; }';
			
			//accordion panel
			$style .= '.panel-success > .panel-heading { border-color:' . $colorData['success_border_color'] . '; }';
			$style .= '.panel-success > .panel-heading + .panel-collapse > .panel-body { border-top-color:' . $colorData['success_border_color'] . '; }';
			$style .= '.panel-success > .panel-footer + .panel-collapse > .panel-body { border-bottom-color:' . $colorData['success_border_color'] . '; }';
			$style .= '.panel-success { border-color:' . $colorData['success_border_color'] . '; }';

			//alert
			$style .= '.alert-success { border-color:' . $colorData['success_border_color'] . '; }';

			//list-group
			$style .= 'a.list-group-item-success:hover, a.list-group-item-success:focus { background-color:' . $colorData['success_border_color'] . '; }';
			$style .= 'a.list-group-item-success.active, a.list-group-item-success.active:hover, a.list-group-item-success.active:focus { background-color:' . $colorData['success_border_color'] . ';border-color:' . $colorData['success_border_color'] . '; }';

			//table
			$style .= '.table-hover > tbody > tr > td.success:hover, .table-hover > tbody > tr > th.success:hover, .table-hover > tbody > tr.success:hover > td, .table-hover > tbody > tr:hover > .success, .table-hover > tbody > tr.success:hover > th { background-color:' . $colorData['success_border_color'] . '; }';
		}
		//success text
		if( $colorData['success_text_color'] !== '' ) {
			$style .= '.weluka-bg-success { color:' . $colorData['success_text_color'] . '; }';
			$style .= '.label-success { color:' . $colorData['success_text_color'] . '; }';

			//button, a.bg
			$style .= '.weluka-btn-success, .weluka-btn-success:hover, .weluka-btn-success:focus, .weluka-btn-success.focus, .weluka-btn-success:active, .weluka-btn-success.active, .open > .dropdown-toggle.weluka-btn-success, a.weluka-bg-success:hover { color:' . $colorData['success_text_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-green, .weluka-navbar-green a, .weluka-navbar-green .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-green { color: ' . $colorData['success_text_color'] . ' !important; }';
			$style .= '.weluka-navbar-green .navbar-toggle .icon-bar, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-green .icon-bar { background-color:' . $colorData['success_text_color'] . '; }';

			//accordion panel
			$style .= '.panel-success > .panel-heading { color:' . $colorData['success_text_color'] . '; }';

			//alert
			$style .= '.alert-success { color:' . $colorData['success_text_color'] . '; }';

			//list group
			$style .= '.list-group-item-success, a.list-group-item-success, a.list-group-item-success:hover, a.list-group-item-success:focus, a.list-group-item-success.active, a.list-group-item-success.active:hover, a.list-group-item-success.active:focus { color:' . $colorData['success_text_color'] . '; }';

			//table
			$style .= '.table > thead > tr > td.success, .table > tbody > tr > td.success, .table > tfoot > tr > td.success, .table > thead > tr > th.success, .table > tbody > tr > th.success, .table > tfoot > tr > th.success, .table > thead > tr.success > td, .table > tbody > tr.success > td, .table > tfoot > tr.success > td, .table > thead > tr.success > th, .table > tbody > tr.success > th, .table > tfoot > tr.success > th, .table-hover > tbody > tr > td.success:hover, .table-hover > tbody > tr > th.success:hover, .table-hover > tbody > tr.success:hover > td, .table-hover > tbody > tr:hover > .success, .table-hover > tbody > tr.success:hover > th { color:' . $colorData['success_text_color'] . '; }';
		}

		//info
		if( $colorData['info_color'] !== '' ) {
			$style .= '.weluka-text-info { color:' . $colorData['info_color'] . '; }';
			$style .= '.weluka-bg-info { background-color:' . $colorData['info_color'] . '; }';
			$style .= '.label-info { background-color:' . $colorData['info_color'] . '; }';

			//button
			$style .= '.weluka-btn-info, .weluka-btn-info .badge, .weluka-btn-info.disabled, .weluka-btn-info[disabled], fieldset[disabled] .weluka-btn-info, .weluka-btn-info.disabled:hover, .weluka-btn-info[disabled]:hover, fieldset[disabled] .weluka-btn-info:hover, .weluka-btn-info.disabled:focus, .weluka-btn-info[disabled]:focus, fieldset[disabled] .weluka-btn-info:focus, .weluka-btn-info.disabled.focus, .weluka-btn-info[disabled].focus, fieldset[disabled] .weluka-btn-info.focus, .weluka-btn-info.disabled:active, .weluka-btn-info[disabled]:active, fieldset[disabled] .weluka-btn-info:active, .weluka-btn-info.disabled.active, .weluka-btn-info[disabled].active, fieldset[disabled] .weluka-btn-info.active { background-color:' . $colorData['info_color'] . '; }';

			$style .= '.weluka-btn-info:hover, .weluka-btn-info:focus, .weluka-btn-info.focus, .weluka-btn-info:active, .weluka-btn-info.active, .open > .dropdown-toggle.weluka-btn-info { border-color:' .  $colorData['info_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-aqua, .weluka-navbar-aqua .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-aqua, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua { background-color:' . $colorData['info_color'] . '; }';

			//accordion panel
			$style .= '.panel-info > .panel-heading { background-color:' . $colorData['info_color'] . '; }';
			$style .= '.panel-info > .panel-heading .badge { color:' . $colorData['info_color'] . '; }';
			
			//alert
			$style .= '.alert-info { background-color:' . $colorData['info_color'] . '; }';
			
			//list-gourp
			$style .= '.list-group-item-info { background-color:' . $colorData['info_color'] . '; }';
			
			//table
			$style .= '.table > thead > tr > td.info, .table > tbody > tr > td.info, .table > tfoot > tr > td.info, .table > thead > tr > th.info, .table > tbody > tr > th.info, .table > tfoot > tr > th.info, .table > thead > tr.info > td, .table > tbody > tr.info > td, .table > tfoot > tr.info > td, .table > thead > tr.info > th, .table > tbody > tr.info > th, .table > tfoot > tr.info > th { background-color:' .  $colorData['info_color'] . '; }';
		}
		//info border
		if( $colorData['info_border_color'] !== '' ) {
			//bg
			$style .= 'a.weluka-bg-info:hover { background-color:' . $colorData['info_border_color'] . '; }';

			//button
			$style .= '.weluka-btn-info, .weluka-btn-info.disabled, .weluka-btn-info[disabled], fieldset[disabled] .weluka-btn-info, .weluka-btn-info.disabled:hover, .weluka-btn-info[disabled]:hover, fieldset[disabled] .weluka-btn-info:hover, .weluka-btn-info.disabled:focus, .weluka-btn-info[disabled]:focus, fieldset[disabled] .weluka-btn-info:focus, .weluka-btn-info.disabled.focus, .weluka-btn-info[disabled].focus, fieldset[disabled] .weluka-btn-info.focus, .weluka-btn-info.disabled:active, .weluka-btn-info[disabled]:active, fieldset[disabled] .weluka-btn-info:active, .weluka-btn-info.disabled.active, .weluka-btn-info[disabled].active, fieldset[disabled] .weluka-btn-info.active { border-color:' . $colorData['info_border_color'] . ' !important; }';
			$style .= '.weluka-btn-info:hover, .weluka-btn-info:focus, .weluka-btn-info.focus, .weluka-btn-info:active, .weluka-btn-info.active, .open > .dropdown-toggle.weluka-btn-info { background-color:' . $colorData['info_border_color'] . '; }';
			
			//nav
			$style .= '.weluka-navbar-aqua, .weluka-navbar-aqua .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-aqua, .weluka-navbar-aqua a:hover, .weluka-navbar-aqua a:focus, .weluka-navbar-aqua .open > a, .weluka-navbar-aqua .open a:hover, .weluka-navbar-aqua .open a:focus, .weluka-navbar-aqua .dropdown-menu a:hover, .weluka-navbar-aqua .dropdown-menu a:focus, .weluka-navbar-aqua .navbar-toggle, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua, .weluka-navbar-aqua .navbar-collapse, .weluka-navbar-aqua .navbar-form, .weluka-nav-bar-v.weluka-navbar-aqua .nav, .weluka-nav-bar-v.weluka-navbar-aqua .nav li, .weluka-nav-bar-ham .nav.weluka-navbar-aqua { border-color:' . $colorData['info_border_color'] . ' !important; }';
			$style .= '.weluka-navbar-aqua a:hover, .weluka-navbar-aqua a:focus, .weluka-navbar-aqua .open > a, .weluka-navbar-aqua .open a:hover, .weluka-navbar-aqua .open a:focus, .weluka-navbar-aqua .dropdown-menu a:hover, .weluka-navbar-aqua .dropdown-menu a:focus, .weluka-navbar-aqua .navbar-toggle:hover, .weluka-navbar-aqua .navbar-toggle:focus, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua:hover, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua:focus { background-color:' . $colorData['info_border_color'] . '; }';
			
			//accordion panel
			$style .= '.panel-info > .panel-heading { border-color:' . $colorData['info_border_color'] . '; }';
			$style .= '.panel-info > .panel-heading + .panel-collapse > .panel-body { border-top-color:' . $colorData['info_border_color'] . '; }';
			$style .= '.panel-info > .panel-footer + .panel-collapse > .panel-body { border-bottom-color:' . $colorData['info_border_color'] . '; }';
			$style .= '.panel-info { border-color:' . $colorData['info_border_color'] . '; }';

			//alert
			$style .= '.alert-info { border-color:' . $colorData['info_border_color'] . '; }';

			//list-group
			$style .= 'a.list-group-item-info:hover, a.list-group-item-info:focus { background-color:' . $colorData['info_border_color'] . '; }';
			$style .= 'a.list-group-item-info.active, a.list-group-item-info.active:hover, a.list-group-item-info.active:focus { background-color:' . $colorData['info_border_color'] . ';border-color:' . $colorData['info_border_color'] . '; }';

			//table
			$style .= '.table-hover > tbody > tr > td.info:hover, .table-hover > tbody > tr > th.info:hover, .table-hover > tbody > tr.info:hover > td, .table-hover > tbody > tr:hover > .info, .table-hover > tbody > tr.info:hover > th { background-color:' . $colorData['info_border_color'] . '; }';
		}
		//info text
		if( $colorData['info_text_color'] !== '' ) {
			$style .= '.weluka-bg-info { color:' . $colorData['info_text_color'] . '; }';
			$style .= '.label-info { color:' . $colorData['info_text_color'] . '; }';

			//button, a.bg
			$style .= '.weluka-btn-info, .weluka-btn-info:hover, .weluka-btn-info:focus, .weluka-btn-info.focus, .weluka-btn-info:active, .weluka-btn-info.active, .open > .dropdown-toggle.weluka-btn-info, a.weluka-bg-info:hover { color:' . $colorData['info_text_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-aqua, .weluka-navbar-aqua a, .weluka-navbar-aqua .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-aqua { color: ' . $colorData['info_text_color'] . ' !important; }';
			$style .= '.weluka-navbar-aqua .navbar-toggle .icon-bar, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-aqua .icon-bar { background-color:' . $colorData['info_text_color'] . '; }';

			//accordion panel
			$style .= '.panel-info > .panel-heading { color:' . $colorData['info_text_color'] . '; }';

			//alert
			$style .= '.alert-info { color:' . $colorData['info_text_color'] . '; }';

			//list group
			$style .= '.list-group-item-info, a.list-group-item-info, a.list-group-item-info:hover, a.list-group-item-info:focus, a.list-group-item-info.active, a.list-group-item-info.active:hover, a.list-group-item-info.active:focus { color:' . $colorData['info_text_color'] . '; }';

			//table
			$style .= '.table > thead > tr > td.info, .table > tbody > tr > td.info, .table > tfoot > tr > td.info, .table > thead > tr > th.info, .table > tbody > tr > th.info, .table > tfoot > tr > th.info, .table > thead > tr.info > td, .table > tbody > tr.info > td, .table > tfoot > tr.info > td, .table > thead > tr.info > th, .table > tbody > tr.info > th, .table > tfoot > tr.info > th, .table-hover > tbody > tr > td.info:hover, .table-hover > tbody > tr > th.info:hover, .table-hover > tbody > tr.info:hover > td, .table-hover > tbody > tr:hover > .info, .table-hover > tbody > tr.info:hover > th { color:' . $colorData['info_text_color'] . '; }';
		}

		//warning
		if( $colorData['warning_color'] !== '' ) {
			$style .= '.weluka-text-warning { color:' . $colorData['warning_color'] . '; }';
			$style .= '.weluka-bg-warning { background-color:' . $colorData['warning_color'] . '; }';
			$style .= '.label-warning { background-color:' . $colorData['warning_color'] . '; }';

			//button
			$style .= '.weluka-btn-warning, .weluka-btn-warning .badge, .weluka-btn-warning.disabled, .weluka-btn-warning[disabled], fieldset[disabled] .weluka-btn-warning, .weluka-btn-warning.disabled:hover, .weluka-btn-warning[disabled]:hover, fieldset[disabled] .weluka-btn-warning:hover, .weluka-btn-warning.disabled:focus, .weluka-btn-warning[disabled]:focus, fieldset[disabled] .weluka-btn-warning:focus, .weluka-btn-warning.disabled.focus, .weluka-btn-warning[disabled].focus, fieldset[disabled] .weluka-btn-warning.focus, .weluka-btn-warning.disabled:active, .weluka-btn-warning[disabled]:active, fieldset[disabled] .weluka-btn-warning:active, .weluka-btn-warning.disabled.active, .weluka-btn-warning[disabled].active, fieldset[disabled] .weluka-btn-warning.active { background-color:' . $colorData['warning_color'] . '; }';

			$style .= '.weluka-btn-warning:hover, .weluka-btn-warning:focus, .weluka-btn-warning.focus, .weluka-btn-warning:active, .weluka-btn-warning.active, .open > .dropdown-toggle.weluka-btn-warning { border-color:' .  $colorData['warning_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-orange, .weluka-navbar-orange .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-orange, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange { background-color:' . $colorData['warning_color'] . '; }';

			//accordion panel
			$style .= '.panel-warning > .panel-heading { background-color:' . $colorData['warning_color'] . '; }';
			$style .= '.panel-warning > .panel-heading .badge { color:' . $colorData['warning_color'] . '; }';
			
			//alert
			$style .= '.alert-warning { background-color:' . $colorData['warning_color'] . '; }';
			
			//list-gourp
			$style .= '.list-group-item-warning { background-color:' . $colorData['warning_color'] . '; }';
			
			//table
			$style .= '.table > thead > tr > td.warning, .table > tbody > tr > td.warning, .table > tfoot > tr > td.warning, .table > thead > tr > th.warning, .table > tbody > tr > th.warning, .table > tfoot > tr > th.warning, .table > thead > tr.warning > td, .table > tbody > tr.warning > td, .table > tfoot > tr.warning > td, .table > thead > tr.warning > th, .table > tbody > tr.warning > th, .table > tfoot > tr.warning > th { background-color:' .  $colorData['warning_color'] . '; }';
		}
		//warning border
		if( $colorData['warning_border_color'] !== '' ) {
			//bg
			$style .= 'a.weluka-bg-warning:hover { background-color:' . $colorData['warning_border_color'] . '; }';

			//button
			$style .= '.weluka-btn-warning, .weluka-btn-warning.disabled, .weluka-btn-warning[disabled], fieldset[disabled] .weluka-btn-warning, .weluka-btn-warning.disabled:hover, .weluka-btn-warning[disabled]:hover, fieldset[disabled] .weluka-btn-warning:hover, .weluka-btn-warning.disabled:focus, .weluka-btn-warning[disabled]:focus, fieldset[disabled] .weluka-btn-warning:focus, .weluka-btn-warning.disabled.focus, .weluka-btn-warning[disabled].focus, fieldset[disabled] .weluka-btn-warning.focus, .weluka-btn-warning.disabled:active, .weluka-btn-warning[disabled]:active, fieldset[disabled] .weluka-btn-warning:active, .weluka-btn-warning.disabled.active, .weluka-btn-warning[disabled].active, fieldset[disabled] .weluka-btn-warning.active { border-color:' . $colorData['warning_border_color'] . ' !important; }';
			$style .= '.weluka-btn-warning:hover, .weluka-btn-warning:focus, .weluka-btn-warning.focus, .weluka-btn-warning:active, .weluka-btn-warning.active, .open > .dropdown-toggle.weluka-btn-warning { background-color:' . $colorData['warning_border_color'] . '; }';
			
			//nav
			$style .= '.weluka-navbar-orange, .weluka-navbar-orange .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-orange, .weluka-navbar-orange a:hover, .weluka-navbar-orange a:focus, .weluka-navbar-orange .open > a, .weluka-navbar-orange .open a:hover, .weluka-navbar-orange .open a:focus, .weluka-navbar-orange .dropdown-menu a:hover, .weluka-navbar-orange .dropdown-menu a:focus, .weluka-navbar-orange .navbar-toggle, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange, .weluka-navbar-orange .navbar-collapse, .weluka-navbar-orange .navbar-form, .weluka-nav-bar-v.weluka-navbar-orange .nav, .weluka-nav-bar-v.weluka-navbar-orange .nav li, .weluka-nav-bar-ham .nav.weluka-navbar-orange { border-color:' . $colorData['warning_border_color'] . ' !important; }';
			$style .= '.weluka-navbar-orange a:hover, .weluka-navbar-orange a:focus, .weluka-navbar-orange .open > a, .weluka-navbar-orange .open a:hover, .weluka-navbar-orange .open a:focus, .weluka-navbar-orange .dropdown-menu a:hover, .weluka-navbar-orange .dropdown-menu a:focus, .weluka-navbar-orange .navbar-toggle:hover, .weluka-navbar-orange .navbar-toggle:focus, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange:hover, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange:focus { background-color:' . $colorData['warning_border_color'] . '; }';
			
			//accordion panel
			$style .= '.panel-warning > .panel-heading { border-color:' . $colorData['warning_border_color'] . '; }';
			$style .= '.panel-warning > .panel-heading + .panel-collapse > .panel-body { border-top-color:' . $colorData['warning_border_color'] . '; }';
			$style .= '.panel-warning > .panel-footer + .panel-collapse > .panel-body { border-bottom-color:' . $colorData['warning_border_color'] . '; }';
			$style .= '.panel-warning { border-color:' . $colorData['warning_border_color'] . '; }';

			//alert
			$style .= '.alert-warning { border-color:' . $colorData['warning_border_color'] . '; }';

			//list-group
			$style .= 'a.list-group-item-warning:hover, a.list-group-item-warning:focus { background-color:' . $colorData['warning_border_color'] . '; }';
			$style .= 'a.list-group-item-warning.active, a.list-group-item-warning.active:hover, a.list-group-item-warning.active:focus { background-color:' . $colorData['warning_border_color'] . ';border-color:' . $colorData['warning_border_color'] . '; }';

			//table
			$style .= '.table-hover > tbody > tr > td.warning:hover, .table-hover > tbody > tr > th.warning:hover, .table-hover > tbody > tr.warning:hover > td, .table-hover > tbody > tr:hover > .warning, .table-hover > tbody > tr.warning:hover > th { background-color:' . $colorData['warning_border_color'] . '; }';
		}
		//warning text
		if( $colorData['warning_text_color'] !== '' ) {
			$style .= '.weluka-bg-warning { color:' . $colorData['warning_text_color'] . '; }';
			$style .= '.label-warning { color:' . $colorData['warning_text_color'] . '; }';

			//button, a.bg
			$style .= '.weluka-btn-warning, .weluka-btn-warning:hover, .weluka-btn-warning:focus, .weluka-btn-warning.focus, .weluka-btn-warning:active, .weluka-btn-warning.active, .open > .dropdown-toggle.weluka-btn-warning, a.weluka-bg-warning:hover { color:' . $colorData['warning_text_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-orange, .weluka-navbar-orange a, .weluka-navbar-orange .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-orange { color: ' . $colorData['warning_text_color'] . ' !important; }';
			$style .= '.weluka-navbar-orange .navbar-toggle .icon-bar, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-orange .icon-bar { background-color:' . $colorData['warning_text_color'] . '; }';

			//accordion panel
			$style .= '.panel-warning > .panel-heading { color:' . $colorData['warning_text_color'] . '; }';

			//alert
			$style .= '.alert-warning { color:' . $colorData['warning_text_color'] . '; }';

			//list group
			$style .= '.list-group-item-warning, a.list-group-item-warning, a.list-group-item-warning:hover, a.list-group-item-warning:focus, a.list-group-item-warning.active, a.list-group-item-warning.active:hover, a.list-group-item-warning.active:focus { color:' . $colorData['warning_text_color'] . '; }';

			//table
			$style .= '.table > thead > tr > td.warning, .table > tbody > tr > td.warning, .table > tfoot > tr > td.warning, .table > thead > tr > th.warning, .table > tbody > tr > th.warning, .table > tfoot > tr > th.warning, .table > thead > tr.warning > td, .table > tbody > tr.warning > td, .table > tfoot > tr.warning > td, .table > thead > tr.warning > th, .table > tbody > tr.warning > th, .table > tfoot > tr.warning > th, .table-hover > tbody > tr > td.warning:hover, .table-hover > tbody > tr > th.warning:hover, .table-hover > tbody > tr.warning:hover > td, .table-hover > tbody > tr:hover > .warning, .table-hover > tbody > tr.warning:hover > th { color:' . $colorData['warning_text_color'] . '; }';
		}

		//danger
		if( $colorData['danger_color'] !== '' ) {
			$style .= '.weluka-text-danger { color:' . $colorData['danger_color'] . '; }';
			$style .= '.weluka-bg-danger { background-color:' . $colorData['danger_color'] . '; }';
			$style .= '.label-danger { background-color:' . $colorData['danger_color'] . '; }';

			//button
			$style .= '.weluka-btn-danger, .weluka-btn-danger .badge, .weluka-btn-danger.disabled, .weluka-btn-danger[disabled], fieldset[disabled] .weluka-btn-danger, .weluka-btn-danger.disabled:hover, .weluka-btn-danger[disabled]:hover, fieldset[disabled] .weluka-btn-danger:hover, .weluka-btn-danger.disabled:focus, .weluka-btn-danger[disabled]:focus, fieldset[disabled] .weluka-btn-danger:focus, .weluka-btn-danger.disabled.focus, .weluka-btn-danger[disabled].focus, fieldset[disabled] .weluka-btn-danger.focus, .weluka-btn-danger.disabled:active, .weluka-btn-danger[disabled]:active, fieldset[disabled] .weluka-btn-danger:active, .weluka-btn-danger.disabled.active, .weluka-btn-danger[disabled].active, fieldset[disabled] .weluka-btn-danger.active { background-color:' . $colorData['danger_color'] . '; }';

			$style .= '.weluka-btn-danger:hover, .weluka-btn-danger:focus, .weluka-btn-danger.focus, .weluka-btn-danger:active, .weluka-btn-danger.active, .open > .dropdown-toggle.weluka-btn-danger { border-color:' .  $colorData['danger_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-red, .weluka-navbar-red .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-red, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red { background-color:' . $colorData['danger_color'] . '; }';

			//accordion panel
			$style .= '.panel-danger > .panel-heading { background-color:' . $colorData['danger_color'] . '; }';
			$style .= '.panel-danger > .panel-heading .badge { color:' . $colorData['danger_color'] . '; }';
			
			//alert
			$style .= '.alert-danger { background-color:' . $colorData['danger_color'] . '; }';
			
			//list-gourp
			$style .= '.list-group-item-danger { background-color:' . $colorData['danger_color'] . '; }';
			
			//table
			$style .= '.table > thead > tr > td.danger, .table > tbody > tr > td.danger, .table > tfoot > tr > td.danger, .table > thead > tr > th.danger, .table > tbody > tr > th.danger, .table > tfoot > tr > th.danger, .table > thead > tr.danger > td, .table > tbody > tr.danger > td, .table > tfoot > tr.danger > td, .table > thead > tr.danger > th, .table > tbody > tr.danger > th, .table > tfoot > tr.danger > th { background-color:' .  $colorData['danger_color'] . '; }';
		}
		//danger border
		if( $colorData['danger_border_color'] !== '' ) {
			//bg
			$style .= 'a.weluka-bg-danger:hover { background-color:' . $colorData['danger_border_color'] . '; }';

			//button
			$style .= '.weluka-btn-danger, .weluka-btn-danger.disabled, .weluka-btn-danger[disabled], fieldset[disabled] .weluka-btn-danger, .weluka-btn-danger.disabled:hover, .weluka-btn-danger[disabled]:hover, fieldset[disabled] .weluka-btn-danger:hover, .weluka-btn-danger.disabled:focus, .weluka-btn-danger[disabled]:focus, fieldset[disabled] .weluka-btn-danger:focus, .weluka-btn-danger.disabled.focus, .weluka-btn-danger[disabled].focus, fieldset[disabled] .weluka-btn-danger.focus, .weluka-btn-danger.disabled:active, .weluka-btn-danger[disabled]:active, fieldset[disabled] .weluka-btn-danger:active, .weluka-btn-danger.disabled.active, .weluka-btn-danger[disabled].active, fieldset[disabled] .weluka-btn-danger.active { border-color:' . $colorData['danger_border_color'] . ' !important; }';
			$style .= '.weluka-btn-danger:hover, .weluka-btn-danger:focus, .weluka-btn-danger.focus, .weluka-btn-danger:active, .weluka-btn-danger.active, .open > .dropdown-toggle.weluka-btn-danger { background-color:' . $colorData['danger_border_color'] . '; }';
			
			//nav
			$style .= '.weluka-navbar-red, .weluka-navbar-red .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-red, .weluka-navbar-red a:hover, .weluka-navbar-red a:focus, .weluka-navbar-red .open > a, .weluka-navbar-red .open a:hover, .weluka-navbar-red .open a:focus, .weluka-navbar-red .dropdown-menu a:hover, .weluka-navbar-red .dropdown-menu a:focus, .weluka-navbar-red .navbar-toggle, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red, .weluka-navbar-red .navbar-collapse, .weluka-navbar-red .navbar-form, .weluka-nav-bar-v.weluka-navbar-red .nav, .weluka-nav-bar-v.weluka-navbar-red .nav li, .weluka-nav-bar-ham .nav.weluka-navbar-red { border-color:' . $colorData['danger_border_color'] . ' !important; }';
			$style .= '.weluka-navbar-red a:hover, .weluka-navbar-red a:focus, .weluka-navbar-red .open > a, .weluka-navbar-red .open a:hover, .weluka-navbar-red .open a:focus, .weluka-navbar-red .dropdown-menu a:hover, .weluka-navbar-red .dropdown-menu a:focus, .weluka-navbar-red .navbar-toggle:hover, .weluka-navbar-red .navbar-toggle:focus, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red:hover, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red:focus { background-color:' . $colorData['danger_border_color'] . '; }';
			
			//accordion panel
			$style .= '.panel-danger > .panel-heading { border-color:' . $colorData['danger_border_color'] . '; }';
			$style .= '.panel-danger > .panel-heading + .panel-collapse > .panel-body { border-top-color:' . $colorData['danger_border_color'] . '; }';
			$style .= '.panel-danger > .panel-footer + .panel-collapse > .panel-body { border-bottom-color:' . $colorData['danger_border_color'] . '; }';
			$style .= '.panel-danger { border-color:' . $colorData['danger_border_color'] . '; }';

			//alert
			$style .= '.alert-danger { border-color:' . $colorData['danger_border_color'] . '; }';

			//list-group
			$style .= 'a.list-group-item-danger:hover, a.list-group-item-danger:focus { background-color:' . $colorData['danger_border_color'] . '; }';
			$style .= 'a.list-group-item-danger.active, a.list-group-item-danger.active:hover, a.list-group-item-danger.active:focus { background-color:' . $colorData['danger_border_color'] . ';border-color:' . $colorData['danger_border_color'] . '; }';

			//table
			$style .= '.table-hover > tbody > tr > td.danger:hover, .table-hover > tbody > tr > th.danger:hover, .table-hover > tbody > tr.danger:hover > td, .table-hover > tbody > tr:hover > .danger, .table-hover > tbody > tr.danger:hover > th { background-color:' . $colorData['danger_border_color'] . '; }';
		}
		//danger text
		if( $colorData['danger_text_color'] !== '' ) {
			$style .= '.weluka-bg-danger { color:' . $colorData['danger_text_color'] . '; }';
			$style .= '.label-danger { color:' . $colorData['danger_text_color'] . '; }';

			//button, a.bg
			$style .= '.weluka-btn-danger, .weluka-btn-danger:hover, .weluka-btn-danger:focus, .weluka-btn-danger.focus, .weluka-btn-danger:active, .weluka-btn-danger.active, .open > .dropdown-toggle.weluka-btn-danger, a.weluka-bg-danger:hover { color:' . $colorData['danger_text_color'] . ' !important; }';

			//nav
			$style .= '.weluka-navbar-red, .weluka-navbar-red a, .weluka-navbar-red .dropdown-menu, .weluka-nav-bar-ham .nav.weluka-navbar-red { color: ' . $colorData['danger_text_color'] . ' !important; }';
			$style .= '.weluka-navbar-red .navbar-toggle .icon-bar, .weluka-nav-bar-ham .weluka-toggle.weluka-navbar-red .icon-bar { background-color:' . $colorData['danger_text_color'] . '; }';

			//accordion panel
			$style .= '.panel-danger > .panel-heading { color:' . $colorData['danger_text_color'] . '; }';

			//alert
			$style .= '.alert-danger { color:' . $colorData['danger_text_color'] . '; }';

			//list group
			$style .= '.list-group-item-danger, a.list-group-item-danger, a.list-group-item-danger:hover, a.list-group-item-danger:focus, a.list-group-item-danger.active, a.list-group-item-danger.active:hover, a.list-group-item-danger.active:focus { color:' . $colorData['danger_text_color'] . '; }';

			//table
			$style .= '.table > thead > tr > td.danger, .table > tbody > tr > td.danger, .table > tfoot > tr > td.danger, .table > thead > tr > th.danger, .table > tbody > tr > th.danger, .table > tfoot > tr > th.danger, .table > thead > tr.danger > td, .table > tbody > tr.danger > td, .table > tfoot > tr.danger > td, .table > thead > tr.danger > th, .table > tbody > tr.danger > th, .table > tfoot > tr.danger > th, .table-hover > tbody > tr > td.danger:hover, .table-hover > tbody > tr > th.danger:hover, .table-hover > tbody > tr.danger:hover > td, .table-hover > tbody > tr:hover > .danger, .table-hover > tbody > tr.danger:hover > th { color:' . $colorData['danger_text_color'] . '; }';
		}

//ver1.0.1 add
		//tagcloud
		if( !empty($colorData['tagcloud_color']) ) {
			$style .= '.tagcloud a { background-color:' . $colorData['tagcloud_color'] . '; }';
		}
		if( !empty($colorData['tagcloud_hoverbg_color']) ) {
			$style .= '.tagcloud a:hover { background-color:' . $colorData['tagcloud_hoverbg_color'] . '; }';
		}
		if( !empty($colorData['tagcloud_text_color']) ) {
			$style .= '.tagcloud a, .tagcloud a:hover { color:' . $colorData['tagcloud_text_color'] . '; }';			
		}
//ver1.0.1 addend
		$style .= '</style>';		
		echo $style;
	endif;
	
	if( !empty( $customCss ) ) :
		echo '<style type="text/css">' . $customCss . '</style>';
	endif;
}

/**** [ admin page template metabox ] ****/

/**
 * @since 1.0
 */
function weluka_page_templates_metabox(){
	global $weluka_themename;
	add_meta_box("weluka_ptemp_meta", esc_html__( 'Weluka Page Template Settings', $weluka_themename ), "weluka_ptemp_meta", "page", "side");
}

/**
 * blog temaple metabox input
 * @since 1.0
 * @update
 * ver1.0.6
 */
function weluka_ptemp_meta( $param ) {
		global $post, $weluka_themename, $weluka;
		$temp_array = array();

		$temp_array = maybe_unserialize( $weluka->get_postmeta($post->ID, WELUKA_PTMP_POST_META_NAME) );
		
		$blogcats = isset( $temp_array['weluka_ptemp_blogcats'] ) ? (array) $temp_array['weluka_ptemp_blogcats'] : array();
		$blog_perpage = isset( $temp_array['weluka_ptemp_blog_perpage'] ) ? (int) $temp_array['weluka_ptemp_blog_perpage'] : '';

		//v1.0.6 add
		$blog_date			= isset( $temp_array['weluka_ptemp_blog_date'] ) ? (int) $temp_array['weluka_ptemp_blog_date'] : 9;
		$blog_author		= isset( $temp_array['weluka_ptemp_blog_author'] ) ? (int) $temp_array['weluka_ptemp_blog_author'] : 9;
		$blog_category		= isset( $temp_array['weluka_ptemp_blog_category'] ) ? (int) $temp_array['weluka_ptemp_blog_category'] : 9;
		$blog_comment		= isset( $temp_array['weluka_ptemp_blog_comment'] ) ? (int) $temp_array['weluka_ptemp_blog_comment'] : 9;
		$blog_tagcloud		= isset( $temp_array['weluka_ptemp_blog_tagcloud'] ) ? (int) $temp_array['weluka_ptemp_blog_tagcloud'] : 9;
        $blog_tagcloud_pos	= isset( $temp_array['weluka_ptemp_blog_tagcloud_pos'] ) ? $temp_array['weluka_ptemp_blog_tagcloud_pos'] : '';
		$blog_date_format	= isset( $temp_array['weluka_ptemp_blog_date_format'] ) ? $temp_array['weluka_ptemp_blog_date_format'] : '';
		$blog_date_modify	= isset( $temp_array['weluka_ptemp_blog_date_modify'] ) ? (int) $temp_array['weluka_ptemp_blog_date_modify'] : 9;
		$blog_excerpt_num	= isset( $temp_array['weluka_ptemp_blog_excerpt_num'] ) ? $temp_array['weluka_ptemp_blog_excerpt_num'] : '';
		$blog_format		= isset( $temp_array['weluka_ptemp_blog_format'] ) ? $temp_array['weluka_ptemp_blog_format'] : '';
		$blog_format_sm		= isset( $temp_array['weluka_ptemp_blog_format_sm'] ) ? $temp_array['weluka_ptemp_blog_format_sm'] : '';
		$blog_format_xs		= isset( $temp_array['weluka_ptemp_blog_format_xs'] ) ? $temp_array['weluka_ptemp_blog_format_xs'] : '';
		$blog_cols			= isset( $temp_array['weluka_ptemp_blog_cols'] ) ? $temp_array['weluka_ptemp_blog_cols'] : '';
		$blog_cols_sm		= isset( $temp_array['weluka_ptemp_blog_cols_sm'] ) ? $temp_array['weluka_ptemp_blog_cols_sm'] : '';
		$blog_cols_xs		= isset( $temp_array['weluka_ptemp_blog_cols_xs'] ) ? $temp_array['weluka_ptemp_blog_cols_xs'] : '';
		$blog_button_text	= isset( $temp_array['weluka_ptemp_blog_button_text'] ) ? $temp_array['weluka_ptemp_blog_button_text'] : '';
		//v1.0.6 addend
		
		wp_nonce_field( 'weluka_ptemp_nonce', '_wpnonce_weluka_ptemp_save' ); ?>
		
		<div style="margin: 13px 0 11px 4px;" class="weluka_pt_info">
			<p><?php esc_html_e( 'If the page attribute "template" is selected, additional settings are displayed here.', $weluka_themename ); ?></p>
		</div>
		
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
			<p style="font-weight:bold;"><?php esc_html_e( 'Number of posts', Weluka::$settings['plugin_name'] ); ?></p>
			<input type="text" class="small-text" value="<?php echo esc_attr( $blog_perpage ); ?>" id="weluka_ptemp_blog_perpage" name="weluka_ptemp_blog_perpage"  />
            <div class="weluka-mgtop-s help-block"><?php _e('Please enter the maximum number to be displayed on one page.', $weluka_themename ); ?></div>
		</div>
		
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
			<p style="font-weight:bold;"><?php esc_html_e( 'Select blog categories', $weluka_themename ); ?></p>

			<?php $cats_array = get_categories('hide_empty=0');
			$site_cats = array();
			foreach ($cats_array as $categs) {
				$checked = '';
				
				if (!empty($blogcats)) {
					if (in_array($categs->cat_ID, $blogcats)) $checked = 'checked="checked"';
				} ?>
				
				<label style="padding-bottom: 5px; display: block;" for="<?php echo esc_attr( 'weluka_ptemp_blogcats-' . $categs->cat_ID ); ?>">
					<input type="checkbox" name="weluka_ptemp_blogcats[]" id="<?php echo esc_attr( 'weluka_ptemp_blogcats-' . $categs->cat_ID ); ?>" value="<?php echo esc_attr($categs->cat_ID); ?>" <?php echo $checked; ?> />&nbsp;&nbsp;<?php echo esc_html( $categs->cat_name ); ?>
				</label>							
			<?php } ?>
		</div>

		<?php //v1.0.6 add ?>
        <h4><?php _e( 'List Style', $weluka_themename ); ?></h4>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
        	<p style="font-weight:bold;"><?php _e( 'Meta', $weluka_themename ); ?></p>
			<div style="font-weight:bold;"><?php _e('Date', $weluka_themename); ?></div>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_date" id="weluka_ptemp_blog_date_9" value="9" <?php if( $blog_date === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_date" id="weluka_ptemp_blog_date_0" value="0" <?php if( $blog_date === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Show', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_date" id="weluka_ptemp_blog_date_1" value="1" <?php if( $blog_date === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Hide', $weluka_themename); ?>
			</label>

			<div style="font-weight:bold;margin-top:8px;"><?php _e('Author', $weluka_themename); ?></div>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_author" id="weluka_ptemp_blog_author_9" value="9" <?php if( $blog_author === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_author" id="weluka_ptemp_blog_author_0" value="0" <?php if( $blog_author === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Show', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_author" id="weluka_ptemp_blog_author_1" value="1" <?php if( $blog_author === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Hide', $weluka_themename); ?>
			</label>

			<div style="font-weight:bold;margin-top:8px;"><?php _e('Category', $weluka_themename); ?></div>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_category" id="weluka_ptemp_blog_category_9" value="9" <?php if( $blog_category === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_category" id="weluka_ptemp_blog_category_0" value="0" <?php if( $blog_category === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Show', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_category" id="weluka_ptemp_blog_category_1" value="1" <?php if( $blog_category === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Hide', $weluka_themename); ?>
			</label>

			<div style="font-weight:bold;margin-top:8px;"><?php _e('Comment Count', $weluka_themename); ?></div>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_comment" id="weluka_ptemp_blog_comment_9" value="9" <?php if( $blog_comment === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_comment" id="weluka_ptemp_blog_comment_0" value="0" <?php if( $blog_comment === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Show', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_comment" id="weluka_ptemp_blog_comment_1" value="1" <?php if( $blog_comment === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Hide', $weluka_themename); ?>
			</label>            
        </div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
        	<p style="font-weight:bold;"><?php _e( 'Tagcloud', $weluka_themename ); ?></p>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_tagcloud" id="weluka_ptemp_blog_tagcloud_9" value="9" <?php if( $blog_tagcloud === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_tagcloud" id="weluka_ptemp_blog_tagcloud_0" value="0" <?php if( $blog_tagcloud === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Show', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_tagcloud" id="weluka_ptemp_blog_tagcloud_1" value="1" <?php if( $blog_tagcloud === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Hide', $weluka_themename); ?>
			</label>            
        </div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
        	<p style="font-weight:bold;"><?php _e( 'Tagcloud position to post list', $weluka_themename ); ?></p>
			<?php WelukaBuilder::get_instance()->weluka_display_tagcloud_pos_settings('weluka_ptemp_blog_tagcloud_pos', '', false, true, $blog_tagcloud_pos); ?>
        </div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
	       	<p style="font-weight:bold;"><?php _e( 'Post Date Format', $weluka_themename ); ?></p>
            <input type="text" id="weluka_ptemp_blog_date_format" name="weluka_ptemp_blog_date_format" value="<?php echo esc_attr( $blog_date_format ); ?>" />
		</div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
        	<p style="font-weight:bold;"><?php _e( 'View the updated date and time rather than posting date and time.', $weluka_themename ); ?></p>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_date_modify" id="weluka_ptemp_blog_date_modify_9" value="9" <?php if( $blog_date_modify === 9 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Non-select', $weluka_themename); ?>
			</label>
            <label class="selectit" style="margin-right:8px;">
				<input type="radio" name="weluka_ptemp_blog_date_modify" id="weluka_ptemp_blog_date_modify_0" value="0" <?php if( $blog_date_modify === 0 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Registered', $weluka_themename); ?>
			</label>
            <label class="selectit">
				<input type="radio" name="weluka_ptemp_blog_date_modify" id="weluka_ptemp_blog_date_modify_1" value="1" <?php if( $blog_date_modify === 1 ) { echo 'checked="checked"'; } ?> />&nbsp;&nbsp;<?php _e('Updated', $weluka_themename); ?>
			</label>            
		</div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
	       	<p style="font-weight:bold;"><?php _e( 'Excerpt String Number', $weluka_themename ); ?></p>
            <input type="text" class="small-text" id="weluka_ptemp_blog_excerpt_num" name="weluka_ptemp_blog_excerpt_num" value="<?php echo esc_attr( $blog_excerpt_num ); ?>" />
		</div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
	       	<p style="font-weight:bold;"><?php _e( 'List Format', $weluka_themename ); ?></p>
            <?php WelukaBuilder::get_instance()->weluka_display_listlayout_settings( 'weluka_ptemp_blog_format', '', false, true, $blog_format); ?>
			<div style="margin-top:10px;border:1px solid #ccc; padding:10px;">
            	<p style="font-weight:bold;margin-top:0;"><?php _e( 'Responsive',  $weluka_themename ); ?></p>
				<label><span style="margin-right:8px;"><?php _e('Small View(768px - 991px)', $weluka_themename ); ?></span></label>
        	    <?php WelukaBuilder::get_instance()->weluka_display_listlayout_settings( 'weluka_ptemp_blog_format_sm', '', false, true, $blog_format_sm); ?>
				<label><span style="margin-right:8px;"><?php _e('XSmall View(Less than 768px)', $weluka_themename ); ?></span></label>
        	    <?php WelukaBuilder::get_instance()->weluka_display_listlayout_settings( 'weluka_ptemp_blog_format_xs', '', false, true, $blog_format_xs); ?>
			</div>
		</div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
	       	<p style="font-weight:bold;"><?php _e( 'List Row Column', $weluka_themename ); ?></p>
            <?php WelukaBuilder::get_instance()->weluka_display_listlayout_rowcolumn_settings( 'weluka_ptemp_blog_cols', '', false, true, $blog_cols, false, true); ?>
			<div style="margin-top:10px;border:1px solid #ccc; padding:10px;">
             	<p style="font-weight:bold;margin-top:0;"><?php _e( 'Responsive', $weluka_themename ); ?></p>
				<label><span style="margin-right:8px;"><?php _e('Small View(768px - 991px)', $weluka_themename ); ?></span></label>
                <?php WelukaBuilder::get_instance()->weluka_display_listlayout_rowcolumn_settings( 'weluka_ptemp_blog_cols_sm', '', false, true, $blog_cols_sm, false, true); ?>
				<label><span style="margin-right:8px;"><?php _e('XSmall View(Less than 768px)', $weluka_themename ); ?></span></label>
                <?php WelukaBuilder::get_instance()->weluka_display_listlayout_rowcolumn_settings( 'weluka_ptemp_blog_cols_xs', '', false, true, $blog_cols_xs, false, true); ?>
			</div>
		</div>
		<div style="margin: 13px 0 11px 4px; display: none;" class="weluka_pt_blog">
	       	<p style="font-weight:bold;"><?php _e( 'List Button Text', $weluka_themename ); ?></p>
            <input type="text" id="weluka_ptemp_blog_button_text" name="weluka_ptemp_blog_button_text" value="<?php echo esc_attr( $blog_button_text ); ?>" />
		</div>
        <div style="margin-top:13px;" class="weluka_pt_blog"><?php _e('Archive if you do not set the list style is set in weluka theme settings list style is applied.', $weluka_themename ); ?></div>
        <?php //v1.0.6 addend ?>
<?php
}

/**
 * @since 1.0
 * @update
 * ver1.0.6
 */
function weluka_page_template_save_meta( $post_id, $post ){
	global $pagenow, $weluka;
	
	if ( 'post.php' != $pagenow ) return $post_id;

	if ( 'page' != $post->post_type )
		return $post_id;
			
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;
		
	$post_type = get_post_type_object( $post->post_type );
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	if ( ! isset( $_POST['_wpnonce_weluka_ptemp_save'] ) || ! wp_verify_nonce( $_POST['_wpnonce_weluka_ptemp_save'], 'weluka_ptemp_nonce' ) )
        return $post_id;

	if ( !isset( $_POST["page_template"] ) )
		return $post_id;
		
	if ( !in_array( $_POST["page_template"], array('page-blog.php') ) )
		return $post_id;
		
	$temp_array = array();	
	if ( 'page-blog.php' == $_POST["page_template"] ) {
		if (isset($_POST["weluka_ptemp_blogcats"])) $temp_array['weluka_ptemp_blogcats'] = (array) $_POST["weluka_ptemp_blogcats"];
		if (isset($_POST["weluka_ptemp_blog_perpage"])) $temp_array['weluka_ptemp_blog_perpage'] = (int) $_POST["weluka_ptemp_blog_perpage"];

		//v1.0.6 add
		if (isset($_POST["weluka_ptemp_blog_date"])) $temp_array['weluka_ptemp_blog_date'] = (int) $_POST["weluka_ptemp_blog_date"];
		if (isset($_POST["weluka_ptemp_blog_author"])) $temp_array['weluka_ptemp_blog_author'] = (int) $_POST["weluka_ptemp_blog_author"];
		if (isset($_POST["weluka_ptemp_blog_category"])) $temp_array['weluka_ptemp_blog_category'] = (int) $_POST["weluka_ptemp_blog_category"];
		if (isset($_POST["weluka_ptemp_blog_comment"])) $temp_array['weluka_ptemp_blog_comment'] = (int) $_POST["weluka_ptemp_blog_comment"];
		if (isset($_POST["weluka_ptemp_blog_tagcloud"])) $temp_array['weluka_ptemp_blog_tagcloud'] = (int) $_POST["weluka_ptemp_blog_tagcloud"];
		if (isset($_POST["weluka_ptemp_blog_tagcloud_pos"])) $temp_array['weluka_ptemp_blog_tagcloud_pos'] = $_POST["weluka_ptemp_blog_tagcloud_pos"];
		if (isset($_POST["weluka_ptemp_blog_date_format"])) $temp_array['weluka_ptemp_blog_date_format'] = $_POST["weluka_ptemp_blog_date_format"];
		if (isset($_POST["weluka_ptemp_blog_date_modify"])) $temp_array['weluka_ptemp_blog_date_modify'] = (int) $_POST["weluka_ptemp_blog_date_modify"];
		if (isset($_POST["weluka_ptemp_blog_excerpt_num"])) $temp_array['weluka_ptemp_blog_excerpt_num'] = $_POST["weluka_ptemp_blog_excerpt_num"];
		if (isset($_POST["weluka_ptemp_blog_format"])) $temp_array['weluka_ptemp_blog_format'] = $_POST["weluka_ptemp_blog_format"];
		if (isset($_POST["weluka_ptemp_blog_format_sm"])) $temp_array['weluka_ptemp_blog_format_sm'] = $_POST["weluka_ptemp_blog_format_sm"];
		if (isset($_POST["weluka_ptemp_blog_format_xs"])) $temp_array['weluka_ptemp_blog_format_xs'] = $_POST["weluka_ptemp_blog_format_xs"];
		if (isset($_POST["weluka_ptemp_blog_cols"])) $temp_array['weluka_ptemp_blog_cols'] = $_POST["weluka_ptemp_blog_cols"];
		if (isset($_POST["weluka_ptemp_blog_cols_sm"])) $temp_array['weluka_ptemp_blog_cols_sm'] = $_POST["weluka_ptemp_blog_cols_sm"];
		if (isset($_POST["weluka_ptemp_blog_cols_xs"])) $temp_array['weluka_ptemp_blog_cols_xs'] = $_POST["weluka_ptemp_blog_cols_xs"];
		if (isset($_POST["weluka_ptemp_blog_button_text"])) $temp_array['weluka_ptemp_blog_button_text'] = $_POST["weluka_ptemp_blog_button_text"];
	}
	
	$weluka->save_postmeta($post_id, WELUKA_PTMP_POST_META_NAME, $temp_array);
}
/**** [ end admin page template metabox ] ****/

/**
 * @since 1.0
 * @update
 * ver1.0.6
 */
function get_weluka_theme_layout( $name ) {
	global $welukaThemeOptions;
	
	$ret = $welukaThemeOptions[$name];
	if( ! $ret ) {
		if( $name === WelukaThemeOptions::HOME_LAYOUT ) {
			$ret = WelukaThemeOptions::LAYOUT_ONE_COL;
		} elseif( $name === WelukaThemeOptions::ARCHIVE_LAYOUT ) {	//v1.0.6
			$ret = get_weluka_theme_layout( WelukaThemeOptions::COMMON_LAYOUT );
		} elseif( $name === WelukaThemeOptions::PAGE_LAYOUT ) {	//v1.0.6
			$ret = get_weluka_theme_layout( WelukaThemeOptions::COMMON_LAYOUT );
		} elseif( $name === WelukaThemeOptions::POST_LAYOUT ) {	//v1.0.6
			$ret = get_weluka_theme_layout( WelukaThemeOptions::COMMON_LAYOUT );
		} else {
			$ret = WelukaThemeOptions::LAYOUT_TWO_COL_LEFT;
		}
	}
	return $ret;
}

/**
 * @since 1.0
 */
function get_weluka_theme_color( $name = "" ) {
	global $welukaThemeColors;
	
	$ret = isset( $welukaThemeColors ) ? $welukaThemeColors : "";
	if( $name !== "" && $ret !== "" ) {
		$ret = isset( $ret[$name] ) ? $ret[$name] : "";
	}
	return $ret;
}

/**
 * home blog style pre_get_posts hook
 * @since 1.0
 */
function get_weluka_page_setting() {
	global $post, $weluka, $welukaBuilder;
	$ret = null;

	if ( is_front_page() || is_page() || is_single() ) {
		$mode = WelukaBuilder::CONTENT_POSTMETA_KEY_PUBLISH;
		if( $weluka->is_active_builder() || is_preview() ) {
			$mode = WelukaBuilder::CONTENT_POSTMETA_KEY_DRAFT;
		}
		$ret = $welukaBuilder->get_builder_data($post->ID, $mode, 'pagesetting' );
	}
	return $ret;
}

/**
 * @since 1.0
 */
function get_weluka_noimage() {
	global $welukaThemeOptions, $welukaDefaultNoImage;
	
	$ret = $welukaDefaultNoImage;
	if( ! empty( $welukaThemeOptions[WelukaThemeOptions::NO_IMAGE_FILE] ) ) {
		$ret = $welukaThemeOptions[WelukaThemeOptions::NO_IMAGE_FILE];
	}
	return $ret;
}

/**
 * @since 1.0
 */
function get_weluka_site_logo() {
	global $welukaThemeOptions;
	
	return ! empty( $welukaThemeOptions[WelukaThemeOptions::LOGO_IMAGE] ) ? $welukaThemeOptions[WelukaThemeOptions::LOGO_IMAGE] : ''; 
}

/**
 * @since 1.0
 */
function get_weluka_custom_header( $setting ) {
	global $welukaBuilder, $welukaTheme, $welukaThemeOptions;

	$cptId	= ! empty( $setting['hd_id'] ) ? $setting['hd_id'] : '';
	if( $cptId === '' ) {
		$cptId = ! empty( $welukaThemeOptions[WelukaThemeOptions::COMMON_PAGE_HEADER] ) ? $welukaThemeOptions[WelukaThemeOptions::COMMON_PAGE_HEADER] : '';
	}
		
	$cptHd = '';
	if( $cptId !== '' ) {
		//get publish header content
		$cptHd = do_shortcode( $welukaBuilder->get_builder_data( $cptId, WelukaBuilder::CONTENT_POSTMETA_KEY_PUBLISH, 'content' ) );
	}
	
	return $cptHd;
}

/**
 * @since 1.0
 */
function get_weluka_custom_footer( $setting ) {
	global $welukaBuilder, $welukaTheme, $welukaThemeOptions;

	$cptId	= ! empty( $setting['ft_id'] ) ? $setting['ft_id'] : '';
	if( $cptId === '' ) {
		$cptId = ! empty( $welukaThemeOptions[WelukaThemeOptions::COMMON_PAGE_FOOTER] ) ? $welukaThemeOptions[WelukaThemeOptions::COMMON_PAGE_FOOTER] : '';
	}
		
	$cptFt = '';
	if( $cptId !== '' ) {
		//get publish footer content
		$cptFt = do_shortcode( $welukaBuilder->get_builder_data( $cptId, WelukaBuilder::CONTENT_POSTMETA_KEY_PUBLISH, 'content' ) );
	}
	
	return $cptFt;
}

/**
 * @since 1.0
 */
function get_weluka_custom_sidebar( $setting, $mode = 'main' ) {
	global $welukaBuilder, $welukaTheme, $welukaThemeOptions;

	$settingKey	= $mode === 'sub' ? 'sd_sub_id' : 'sd_main_id';
	$optionKey	= $mode === 'sub' ? WelukaThemeOptions::COMMON_SIDEBAR_SUB : WelukaThemeOptions::COMMON_SIDEBAR_MAIN;

	$cptId	= ! empty( $setting[$settingKey] ) ? $setting[$settingKey] : '';
	if( $cptId === '' ) {
		$cptId = ! empty( $welukaThemeOptions[$optionKey] ) ? $welukaThemeOptions[$optionKey] : '';
	}
		
	$cptSd = '';
	if( $cptId !== '' ) {
		//get publish sidebar content
		$cptSd = do_shortcode( $welukaBuilder->get_builder_data( $cptId, WelukaBuilder::CONTENT_POSTMETA_KEY_PUBLISH, 'content' ) );
	}
	
	return $cptSd;
}

/**
 * home blog style pre_get_posts hook
 * @since 1.0
 * ver1.0.4
 * ver1.0.5.2
 */
function weluka_pre_home_query( $query ) {
	global $welukaThemeOptions;

	if( ! is_home() || is_admin() || ! $query->is_main_query() || ! is_a( $query, 'WP_Query' ) ) return;

	//home blog style post_per_page
	$_limit = ! empty( $welukaThemeOptions[WelukaThemeOptions::HOME_POST_NUM] ) ? (int) $welukaThemeOptions[WelukaThemeOptions::HOME_POST_NUM] : '';
	if( $_limit ) {
		$query->set( 'posts_per_page', $_limit );
	}

	$exclude_cats = ! empty( $welukaThemeOptions[WelukaThemeOptions::HOME_EXCLUDE_CATEGORY] ) ? (array) $welukaThemeOptions[WelukaThemeOptions::HOME_EXCLUDE_CATEGORY] : '';
	//if ( $exclude_cats ) $query->set( 'category__not_in', array_map( 'intval', $exclude_cats ) );
	//if ( $exclude_cats ) $query->set( 'category__not_in', $ex );
	//v1.0.5.3
	if ( $exclude_cats ) {
		$ex = explode(",", $exclude_cats[0]);
		$query->set( 'category__not_in', $ex );
	}
	
	//v1.0.4 add
	$const = 'WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY';
	if( defined ( $const ) ) {
		if( ! empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ){
			$query->set( 'orderby', 'modified' );
		}
	}
}

/**
 * archive pre_get_posts hook
 * @since 1.0
 * @update
 * ver1.0.4
 */
function weluka_pre_archive_query( $query ) {
	global $welukaThemeOptions;

	if( ( ! is_archive() && ! is_search() && ! is_tax() ) || is_admin() || ! $query->is_main_query() || ! is_a( $query, 'WP_Query' ) ) return;

	//archive or search or taxonomy post_per_page
	$_limit = ! empty( $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_POST_NUM] ) ? (int) $welukaThemeOptions[WelukaThemeOptions::ARCHIVE_POST_NUM] : '';
	if( $_limit ) {
		$query->set( 'posts_per_page', $_limit );
	}

	//v1.0.4 add
	$const = 'WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY';
	if( defined ( $const ) ) {
		if( ! empty( $welukaThemeOptions[WelukaThemeOptions::POST_DATE_DISPLAY_MODIFY] ) ){
			$query->set( 'orderby', 'modified' );
		}
	}
}

/**
 * pagination
 * @since 1.0
 */
function weluka_pagination( $query = null, $pos = 'bottom', $echo = true ) {
	global $wp_query;

	$ct = '';
	$big = 999999999; // need an unlikely integer	
	$paging =  paginate_links( array(
		'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, get_query_var( 'paged' ) ),
		'total'   => isset( $query ) ? $query->max_num_pages : $wp_query->max_num_pages
	) );
	
	if( !empty( $paging ) ) {
		$mg = $pos === 'bottom' ? ' weluka-mgtop-l weluka-mgbottom-xl' : ' weluka-mgbottom-l';
		$ct = '<div class="weluka-pagination sp-pad' . $mg . '">' . $paging . '</div>';
	}
	
	if( $echo ) { echo $ct; } else { return $ct; }
}

/**
 * Display navigation to next/previous comments when applicable.
 *
 * @since 1.0
 */
function weluka_comment_nav() {
	global $weluka_themename;
	
	// Are there comments to navigate through?
	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
	?>
	<nav class="comment-navigation clearfix" role="navigation">
		<?php
			if ( $prev_link = get_previous_comments_link( __( '&laquo; Older Comments', $weluka_themename ) ) ) :
				printf( '<div class="prev pull-left">%s</div>', $prev_link );
			endif;

			if ( $next_link = get_next_comments_link( __( 'Newer Comments &raquo;', $weluka_themename ) ) ) :
				printf( '<div class="next pull-right">%s</div>', $next_link );
			endif;
		?>
	</nav><!-- .comment-navigation -->
	<?php
	endif;
}

/**
 * admin_init hook
 * @since 1.0
 */
function weluka_theme_update() {
	global $weluka_update_theme_checker;
	
	$jsonFile = 'http://www.weluka.me/5GDJ6_ok7lq/theme/00/v1x/weluka_theme_00_v1x.json';
	$theme_dirname	= 'weluka-theme-00';

	$weluka_update_theme_checker = new ThemeUpdateChecker(
    	$theme_dirname,
    	$jsonFile
	);
}

/**
 * @since 1.0
 */
function weluka_register_sidebar() {
	global $weluka_themename;
	register_sidebar( array(
		'name' 			=> __( 'Header Right', $weluka_themename ),
		'id' 			=> 'sidebar-hd-1',
		'before_widget' => '<div id="%1$s" class="headerwidget widget">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="headerwidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
	register_sidebar( array(
		'name' 			=> __( 'Footer Sidebar Left', $weluka_themename ),
		'id' 			=> 'sidebar-ft-1',
		'before_widget' => '<div id="%1$s" class="footerwidget widget">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="footerwidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
	register_sidebar( array(
		'name' 			=> __( 'Footer Sidebar Center', $weluka_themename ),
		'id' 			=> 'sidebar-ft-2',
		'before_widget' => '<div id="%1$s" class="footerwidget widget">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="footerwidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
	register_sidebar( array(
		'name' 			=> __( 'Footer Sidebar Right', $weluka_themename ),
		'id' 			=> 'sidebar-ft-3',
		'before_widget' => '<div id="%1$s" class="footerwidget widget">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="footerwidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
	register_sidebar( array(
		'name' 			=> __( 'One Sidebar (left or right)', $weluka_themename ),
		'id' 			=> 'sidebar-1',
		'before_widget' => '<div id="%1$s" class="sidebarwidget widget sp-pad">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="sidewidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
	register_sidebar( array(
		'name' 			=> __( '3 Colum Second Sidebar', $weluka_themename ),
		'id' 			=> 'sidebar-2',
		'before_widget' => '<div id="%1$s" class="sidebarwidget2 widget sp-pad">',
		'after_widget' 	=> '</div>',
		'before_title' 	=> '<h4 class="sidewidgettitle widgettitle">',
		'after_title' 	=> '</h4>',
	) );
}

/**
 * image_send_to_editor hook
 * @since 1.0
 */
function weluka_image_send_to_editor($html, $id, $caption, $title, $align, $url, $size, $alt) {
	$html = get_image_tag($id, $alt, '', $align, $size);

	if ( $url ) {
		//$rel = $rel ? ' "attachment wp-att-' . esc_attr($id).' gallery"' : '"gallery"';
		$rel = '"gallery"';
		$rel = "rel=" . $rel; 
		$html = '<a href="' . esc_url($url) . "\" class=\"fancybox\" $rel>$html</a>";
	}
	return $html;
}

/**
 * get headroom header fixed option
 * @since 1.0.1
 */
function get_weluka_theme_hdfixed_options(){
	global $welukaThemeOptions, $welukaTheme;
	$ret = array(WelukaThemeOptions::HEAD_FIXED => 0, WelukaThemeOptions::HEAD_FIXED_CLASS => '', WelukaThemeOptions::HEAD_FIXED_AUTO_HEIGHT_PADDING => 0 );
	if( !empty( $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED] ) ) { $ret[WelukaThemeOptions::HEAD_FIXED] = $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED]; }
	if( !empty( $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED_CLASS] ) ) { $ret[WelukaThemeOptions::HEAD_FIXED_CLASS] = $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED_CLASS]; }
	if( !empty( $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED_AUTO_HEIGHT_PADDING] ) ) { $ret[WelukaThemeOptions::HEAD_FIXED_AUTO_HEIGHT_PADDING] = $welukaThemeOptions[WelukaThemeOptions::HEAD_FIXED_AUTO_HEIGHT_PADDING]; }
	return $ret;
}

/**
 * wp_footer hook
 * @since 1.0.1
 */
function weluka_theme_add_footer() {
	$opts = get_weluka_theme_hdfixed_options();
	if( !empty( $opts[WelukaThemeOptions::HEAD_FIXED] ) ) {
?>
<script type="text/javascript">
	var _target = "<?php if( $opts[WelukaThemeOptions::HEAD_FIXED] == 2 && !empty( $opts[WelukaThemeOptions::HEAD_FIXED_CLASS] ) ){ echo '.' . $opts[WelukaThemeOptions::HEAD_FIXED_CLASS]; }else{ echo '#weluka-main-header'; } ?>";
	jQuery(document).ready(function($) {
		if($(_target).length) {
			$(_target).addClass('weluka-headroom');
			$('.weluka-headroom').addClass('navbar-fixed-top');
			$(".weluka-headroom").headroom({
   				"offset": 50, /*205,*/ /* ここで設定した数値だけスクロールしたらアクション */
   				"tolerance": 5, /* offset位置からここで設定した値文動いたらアクション */
   				"classes": {
   					"initial": "weluka-headroom",
   					"pinned": "headroom--pinned",
					"unpinned": "headroom--unpinned"
				}
			});
		}
	});

	<?php if( $opts[WelukaThemeOptions::HEAD_FIXED_AUTO_HEIGHT_PADDING] ) { ?>
	jQuery(window).load( welukaHeadroomResize );
	jQuery(window).on('resize', welukaHeadroomResize );
 	function welukaHeadroomResize() {
		var hdHeight = jQuery(_target).outerHeight();
		var _insertDom = '<div class="weluka-headroom-auto-pd" style="height:'  + hdHeight + 'px;"></div>';
		if( jQuery('.weluka-headroom-auto-pd').length ) {
			jQuery('.weluka-headroom-auto-pd').remove();
		}
		jQuery(_insertDom).insertAfter(_target);
	}
	<?php } ?>
</script>
<?php
	}
}

/**
 * archive list block create
 * @since 1.0.6
 */
function weluka_archivelist_block( $format, $data, $colMode = 'md' ) {
	$ret = '';
	if($format === 'mediatop') {
		$ret = $data['media'];
		$ret .= $data['title'];
		$ret .= $data['meta'];
		$ret .= $data['tag_metabottom'];
		$ret .= $data['excerpt'];
		$ret .= $data['tag_bottom'];
		$ret .= $data['more'];

	} elseif ($format === 'mediamiddle') {
		$ret = $data['title'];
		$ret .= $data['meta'];
		$ret .= $data['tag_metabottom'];
		$ret .= $data['media'];
		$ret .= $data['excerpt'];
		$ret .= $data['tag_bottom'];
		$ret .= $data['more'];

	} elseif ($format === 'mediabottom') {
		$ret = $data['title'];
		$ret .= $data['meta'];
		$ret .= $data['tag_metabottom'];
		$ret .= $data['excerpt'];
		$ret .= $data['tag_bottom'];
		$ret .= $data['more'];
		$ret .= $data['media'];

	} elseif ($format === 'mediaright') {
		$ret = '<div class="weluka-col weluka-col-' . $colMode . '-8 lt">';
		$ret .= $data['title'];
		$ret .= $data['meta'];
		$ret .= $data['tag_metabottom'];
		$ret .= $data['excerpt'];
		$ret .= $data['tag_bottom'];
		$ret .= $data['more'];
		$ret .= '</div>';
		$ret .= '<div class="weluka-col weluka-col-' . $colMode .'-4 rt">' . $data['media'] . '</div>';

	} elseif ($format === 'medialeft') {
		$ret = '<div class="weluka-col weluka-col-' . $colMode . '-4 lt">' . $data['media'] . '</div>';
		$ret .= '<div class="weluka-col-' . $colMode . '-8 rt">';
		$ret .= $data['title'];
		$ret .= $data['meta'];
		$ret .= $data['tag_metabottom'];
		$ret .= $data['excerpt'];
		$ret .= $data['tag_bottom'];
		$ret .= $data['more'];
		$ret .= '</div>';
	}
	return $ret;
}

/**
 * archivelist cerate
 * @since 1.0.6
 */
function weluka_archivelist( $format, $ct, $rowColumn, $rowCnt, $colNum, &$colCnt, $colMode = 'md' ) {
	$ret = '';
	$topNoMargin = (int)$rowCnt === 0 ? ' top-nomargin' : '';

 	if( $format === 'mediatop' ) {
		$colCnt++;
		if((int)$colCnt === 1) {
			$ret .= '<div class="weluka-list-row weluka-row clearfix' . $topNoMargin . ' ' . $format . '">';
		}
		$ret .= '<div class="weluka-col weluka-col-' . $colMode . '-' . $colNum . '"><div class="wrap">' . $ct . '</div></div>';
		
		if((int)$colCnt === (int)$rowColumn) {
			$ret .= '</div>';	//row end div
			$colCnt = 0;
		}

	}elseif( $format === 'mediamiddle' ) {
		$colCnt++;
		if((int)$colCnt === 1) {
			$ret .= '<div class="weluka-list-row weluka-row clearfix' . $topNoMargin . ' ' . $format . '">';
		}
		$ret .= '<div class="weluka-col weluka-col-' . $colMode . '-' . $colNum . '"><div class="wrap">' . $ct . '</div></div>';

		if((int)$colCnt === (int)$rowColumn) {
			$ret .= '</div>';	//row end div
			$colCnt = 0;
		}

	}elseif( $format === 'mediabottom' ) {
		$colCnt++;
		if((int)$colCnt === 1) {
			$ret .= '<div class="weluka-list-row weluka-row clearfix' . $topNoMargin . ' ' . $format . '">';
		}
					
		$ret .= '<div class="weluka-col weluka-col-' . $colMode . '-' . $colNum . '"><div class="wrap">' . $ct . '</div></div>';

		if((int)$colCnt === (int)$rowColumn) {
			$ret .= '</div>';	//row end div
			$colCnt = 0;
		}

	}elseif( $format === 'mediaright' ) {
		$ret = '<div class="weluka-list-row weluka-row clearfix' . $topNoMargin . ' ' . $format . '">' . $ct . '</div>';

	}elseif( $format === 'medialeft' ) {
		$ret = '<div class="weluka-list-row weluka-row clearfix' . $topNoMargin . ' ' . $format . '">' . $ct . '</div>';
	}
	return $ret;
}

function weluka_archivelist_end( $format, $rowColumn, $colCnt ) {
	$ret = '';
	if( $format !== 'medialeft' && $format !== 'mediaright' ) :
		if((int)$colCnt !== 0 && (int)$colCnt !== (int)$rowColumn) { $ret = '</div>'; }
	endif;
	return $ret;
}

endif; //Weluka Plugin check endif;

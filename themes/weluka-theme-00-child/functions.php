<?php
/**
子function.php(子→親 の順でread)
 */
//同ディレクトリのstule.css を読み込む
//https://github.com/wckansai2016/plugin-hands-on/blob/master/plugin_hands_on_4.md
function add_file_links() {
    wp_enqueue_style( 'child-sub-common-style', get_stylesheet_directory_uri() .'/css/sub-common-style.css' ); //CSS
    wp_enqueue_style( 'child-sub-free-style', get_stylesheet_directory_uri() . '/css/sub-free-style.css' ); //CSS
    wp_enqueue_script( 'child-library-jquery-fixHeightSimple', get_stylesheet_directory_uri() . '/js/library/jquery-fixHeightSimple.js' ); // 行の高さをそろえるプラグイン
        wp_enqueue_script( 'child-library-jquery-rwdImageMaps', get_stylesheet_directory_uri() . '/js/library/jquery.rwdImageMaps.min.js' ); // イメージマップをレスポンシブ対応させる
        wp_enqueue_script( 'child-common-js', get_stylesheet_directory_uri() . '/js/sub-common-js.js' ); //JS
    wp_enqueue_script( 'child-sub-free-js', get_stylesheet_directory_uri() . '/js/sub-free-js.js' ); //JS

}



//'wp_enqueue_scripts'はワードプレスに登録してあるスクリプトを読み込むタイミングで実行する。
//→※wp_enqueue_scripts アクションフックは登録されているスクリプトを読み込むタイミングで実行されるものです。
//上の関数を実行


/*どのスタイルシートよりも遅く読ませるため、200 に設定*/
add_action( 'wp_enqueue_scripts', 'add_file_links',200 );
//管理が目のpost.php でも読み込ませる
add_action('admin_head-post.php', 'add_file_links',200 );


/*リンクを絶対パスに変更*/
function delete_hostname_from_attachment_url( $url ) {
    $regex = '/^http(s)?:\/\/[^\/\s]+(.*)$/';
    if ( preg_match( $regex, $url, $m ) ) {
        $url = $m[2];
    }
    return $url;
}
add_filter( 'wp_get_attachment_url', 'delete_hostname_from_attachment_url' );
add_filter( 'attachment_link', 'delete_hostname_from_attachment_url' );



/*-------------------------------------------*/
/*  <head>タグ内に自分の追加したいタグを追加する
/*-------------------------------------------*/
function add_wp_head_custom(){ ?>
<!-- header-sns from function-->
<?php get_template_part('header-sns');?>
<!-- //header-sns -->
<?php }
add_action( 'wp_head', 'add_wp_head_custom',1);

function add_wp_footer_custom(){ ?>
<!-- footerに書きたいコード -->
<?php }
add_action( 'wp_footer', 'add_wp_footer_custom', 1 );
?>
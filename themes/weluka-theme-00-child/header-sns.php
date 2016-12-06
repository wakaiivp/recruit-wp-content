
<?php 
/*
FB用OPG、Twitterカードの作成
All in ONE SEO をインストールしているならば、SEO設定のタイトルやディスクリプションを参照する。
All in One SEO のソーシャルメディアは無効する。(タグが重複する)
*/

//all in One SEo 用　title と discription を取得
$title =  get_post_meta($post->ID, _aioseop_title, true); 

if(!isset($title)) $title = the_title();

$discription =  get_post_meta($post->ID, _aioseop_description, true); 
if(!isset($discription)) $discription = mb_substr(get_the_excerpt(), 0, 100);

$url = home_url();

if (is_single()){
if(have_posts()): while(have_posts()): the_post();
    $discription = mb_substr(get_the_excerpt(), 0, 100);

endwhile; endif;

    $url = the_permalink();
} else {


}
?>
<meta property="og:type" content="blog">
<meta property="og:description" content="<?php echo $discription ;?>">
<meta property="og:title" content="<?php echo $title ;?>">
<meta property="og:url" content="<?php echo $url ;?>">

<?php

$str = $post->post_content;
$searchPattern = '/<img.*?src=(["\'])(.+?)\1.*?>/i';
if (is_single()){
  if (has_post_thumbnail()){
    $image_id = get_post_thumbnail_id();
    $image = wp_get_attachment_image_src( $image_id, 'full');
    $ogp_image = $image[0];
  } else if ( preg_match( $searchPattern, $str, $imgurl ) && !is_archive()) {
    $ogp_image = $imgurl[2];
  } else {
    $ogp_image = get_template_directory_uri().'/images/og-image.jpg';
  }
} else {
  if (get_header_image()){
     $ogp_image = get_header_image();
  } else {
   $wp_upload_dir = wp_upload_dir();
   $ogp_image = $wp_upload_dir['baseurl'].'/screenshot.png';
         
  }
}
?>
<meta property="og:image" content="<?php echo $ogp_image; ?>">
<meta property="og:site_name" content="<?php $title ?>">
<meta property="og:locale" content="ja_JP" />


<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="<?php $title ; ?>" />
<meta name="twitter:description" content="<?php echo $discription ; ?>" />
<meta name="twitter:image" content="<?php echo $ogp_image ; ?>" />
<meta itemprop="image" content="<?php echo $ogp_image ; ?>" />
<?php
/*
<meta property="fb:app_id" content="App-ID（15文字の半角数字）">
<meta name="twitter:site" content="@Twitterアカウント">
*/
?>
<?php
/**
 * Template Name: Story
 *
 * @package Weluka
 * @since Weluka Theme 00 1.0
 * 　Weluka用固定ページサンプル
 */
get_header();
global $weluka_themename;
?>

<div id="" class="weluka-section story-topimg-section story-topimg-section-skin " style="">
    <div class="weluka-container clearfix">
        <div id="" class="weluka-row clearfix ">
            <div id="" class="weluka-col weluka-col-md-12 story-topimg-clm story-topimg-clm-skin ">
            <div id="" class="weluka-text weluka-content story-topimg-text story-topimg-text-skin" style="">
                <h2>オフショア開発までの道のり</h2>
            </div>
            </div>
        </div>
    </div>
</div>
 
<?php
if ( have_posts() ) :
	get_template_part( 'content', get_post_format() );
else:
	get_template_part( 'content', 'none' );

endif;
?>

<div id="" class="weluka-section story-list-section story-list-section-skin " style="">
    <div class="weluka-container clearfix">
        <div id="" class="weluka-row clearfix  img-rounded">
            <div id="" class="weluka-col weluka-col-md-12 ">
                <div id="" class="weluka-text weluka-content flow-head-text  flow-head-text-only " style="">
                    <h3>記事一覧</h3>
                </div>
            </div>
        </div>
        <div id="" class="weluka-row clearfix ">
            <div id="" class="weluka-col weluka-col-md-12 ">
                <div id="" class="weluka-text weluka-content story-list-text story-list-text-skin " style="">
                    <ul>
                        <li><a href="/story1">第1話　はじめに</a>
                        </li>
                        <li><a href="/story2">第2話　最初は中国オフショアラボでの失敗</a>
                        </li>
                        <li><a href="/story3">第3話　今度は自分達で中国オフショアラボにチャレンジ</a>
                        </li>
                        <li><a href="/story4">第4話　三度目の正直・フィリピンでの再チャレンジ</a>
                        </li>
                        <li><a href="/story5">第5話　フィリピンオフショア開発の実際</a>
                        </li>
                        <li><a href="/story6">第6話　フィリピンオフショア開発では難しい事</a>
                        </li>
                        <li><a href="/story7">第7話　文化について</a>
                        </li>
                        <li><a href="/story8">第8話　最後に</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?>

<?php
/*
Plugin Name: BcChloe Recent Posts Widget
Plugin URI: https://github.com/ifNoob/
Description: <strong>最近の投稿 Post ウィジットプラグイン & Tpoix表示</strong> the portfolio posts from a selected category
Author: BcChloe
Author URI: https://bcchloe.jp
Text Domain: bcchloe-recent-widget
Version: 1.4
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**----------------
* top page 使い方
* $obj = new BcChloe_Recent_Posts();
* $content .= $obj->print_widget_body_topix('news', 'true', $show);
*
* thumbnail 変数値 変更
*	TopPage News & Topix 記事固定として出るよう修正 2016/3
-----------------*/
	global $recent_posts;

/* Widget Show */
	$recent_posts['plugin_url'] = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	$recent_posts['dummy_pic'] = true;
	$recent_posts['dummy_thumbnail'] = 'images/thumbnail_460x460.png';
	$recent_posts['dummy_thumbnail_url'] = $recent_posts['plugin_url'] . $recent_posts['dummy_thumbnail'];
	$recent_posts['widget_thumbnail_width'] = 80;			# widget thumbnail 幅
	$recent_posts['widget_thumbnail_height'] = 65;		# widget thumbnail 高
	$recent_posts['widget_content'] = true;						# widget post content の出力

/* topic show */
	$recent_posts['topix_intval'] = 3;								# topix 表示数
	$recent_posts['topix_thumbnail_width'] = 120;			# topix thumbnail 幅
	$recent_posts['topix_thumbnail_height'] = 100;		# topix thumbnail 高
	$recent_posts['topix_width_img'] = '250px';				# topix tag部 画像サイズ 横 px記入要
	$recent_posts['topix_height_img'] = '120px';			# topix tag部 画像サイズ 縦
	$recent_posts['topix_post_w'] = '250';						# topix data-defwidth部
	$recent_posts['widget_content'] = true;						# content 出力
	$recent_posts['widget_content_br'] = false;				# <br> true | <br><span> false
	$recent_posts['widget_content_count'] = 40;				# content 出力文字数

//	require_once 'lib/aq_resizer.php';	# thumbnail library read

/**==========================
 * Adds Portfolio Items Widget
===========================*/
class BcChloe_Recent_Posts extends WP_Widget {

	private $theme_name = 'BcChloe';
	private $prefix = 'bc_';

/**----------------
* Register widget with WordPress.
-----------------*/
	public function __construct() {
		parent::__construct(
	 		$this->prefix.'recent_posts',																			# Base ID
			$this->theme_name.' Recent Posts',																# Name
			array( 'description' => __( 'Recent Posts Widget', 'pexeto' ), )	# Args
		);
	}

/**
 * Front-end display of widget.
 * @see WP_Widget::widget()
 * @param array $args     Widget arguments.
 * @param array $instance Saved values from database.
 */
	public function widget($args, $instance) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( ! empty( $title ) ){
			echo $before_title . $title . $after_title;
		}
		$this->print_widget_body($instance);	// post表示 (echoあり)
		echo $after_widget;
	}

/**----------------
*	post ウィジット表示
-----------------*/
	private function print_widget_body($instance){

		$number = isset($instance['item_num']) ? intval($instance['item_num']) : 8;
		$exclude_fromats = array();
		$cat_id = isset($instance['category']) ? $instance['category'] : '-1';

		global $recent_posts;

		$post_formats = get_terms(array('post_format'));
		foreach ($post_formats as $format) {
			if(isset($instance['exclude_'.$format->slug])){
				$exclude_fromats[]=$format->slug;
			}
		}

		$args = array('showposts' => $number, 'ignore_sticky_posts' => 1, 'suppress_filters' => false);

		$tax_query = array('ralation' => 'AND' );
		if($cat_id!='' && $cat_id!='-1'){
	      	$tax_query[]=array(
						array(
							'taxonomy' => 'category',
							'field' => 'id',
							'terms' => array( $cat_id ),
							'operator' => 'IN' )
					);
	    }
	    if(!empty($exclude_fromats)){
	    	$tax_query[]=array(
	    		array(
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => $exclude_fromats,
						'operator' => 'NOT IN' )
	    		);
		}

		$posts = get_posts($args);
?>

		<div class="sidebar-latest-posts">

		<?php foreach ($posts as $post) { ?>
			<div class="lp-wrapper" style="content:'.';display:block;clear:both;">
			<?php

/* thumbnailがない場合、dummy画像 */
				if(!has_post_thumbnail($post->ID)) {
					//	$thumbnail=bc_get_resized_image($recent_posts['dummy_thumbnail_url'], $recent_posts['widget_thumbnail_width'], $recent_posts['widget_thumbnail_height'] );
					?><a href="<?php echo get_permalink($post->ID); ?>"> <img src="<?php echo $recent_posts['dummy_thumbnail_url']; ?>" alt="<?php echo $alt; ?>" class="alignleft img-frame" width="<?php echo $recent_posts['widget_thumbnail_width'];?>"/></a><?php

/* thumbnailがある場合 */
				} elseif (has_post_thumbnail($post->ID)) {

			 	$thumb_id = get_post_thumbnail_id($post->ID);
				if(function_exists('bc_get_resized_image')){
//					$large_image_url = wp_get_attachment_image_src( $thumb_id, 'medium');
//					$thumbnail=bc_get_resized_image($large_image_url[0], $recent_posts['widget_thumbnail_width'], $recent_posts['widget_thumbnail_height'] );
				}else{
					$thumb_data = wp_get_attachment_image_src($thumb_id, 'thumbnail' );
					$thumbnail = $thumb_data[0];
				}
				$alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
			?>
			<a href="<?php echo get_permalink($post->ID); ?>"> <img src="<?php echo $thumbnail; ?>" alt="<?php echo $alt; ?>" class="alignleft img-frame" width="<?php echo $recent_posts['widget_thumbnail_width'];?>"/></a>

<?php } //has_post_thumbnail($post->ID) ?>

			<div class="lp-info-wrapper">
				<h3 class="lp-title" style="margin:0;padding:0"><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></h3>
				<span class="lp-post-info"><small style="font-size:12px;"><?php echo get_the_time('Y年 M j日', $post); ?></small></span><br/>
				<span class="lp-content"><small>
<?php 	/* post content 出力 & tag 出力 */

				if ($recent_posts['widget_content']) {																						# content 出力
					$content = mb_substr(strip_tags($post->post_content, '<br>'), 0, 36, 'UTF-8');	# <br>は出力
					echo $content . '...';
			 }
?>
				</small></span>
				</div>
				<div class="clear"></div>
		    </div><!--/.lp-info-wrapper-->
			<?php
			}		# foreach ($posts as $post)
		?>
		</div>
		<?php
	}


/**----------------
*	topix表示用 2015/7 追加
*	$echo = 表示フラグ
*	return $show = content topix内容
-----------------*/
	public function print_widget_body_topix($instance_cat, $echo, $show){

		global $recent_posts;
		$instance = array();
		$instance['category'] = $instance_cat;

		$sticky_flag = get_option( 'sticky_posts' );		# 固定表示有る無し
		if (isset($sticky_flag[0])) {	$interval_count = $recent_posts['topix_intval']; 					#先頭表示が有る場合
		} else {											$interval_count = $recent_posts['topix_intval'] + 1; }		#先頭表示がない場合

		$number = isset($instance['item_num']) ? intval($instance['item_num']) : $interval_count;	// 8 origin;
		$exclude_fromats = array();
		$cat_id = isset($instance['category']) ? $instance['category'] : '-1';
		$post_formats = get_terms(array('post_format'));
		foreach ($post_formats as $format) {
			if(isset($instance['exclude_'.$format->slug])){
				$exclude_fromats[]=$format->slug;
			}
		}

		# origin ignore_sticky_posts <= post 最上部へ固定用フラグ
		# $args_in は, 最上部記事のみ
		$args_in = array('showposts' => $number, 'post__in' => get_option( 'sticky_posts' ), 'suppress_filters' => false);
		# $args_not は, 最上部固定以外 カテゴリ追加
		$args_not = array('showposts' => $number, 'post__not_in' => get_option( 'sticky_posts' ), 'category_name' => 'news', 'suppress_filters' => false);
		# $$numberは, 表示記事数 この場合配列結合の為,最上部の記事を-1とする
		$args_not['showposts'] = $args_not['showposts']-1;

		$args = array('showposts' => $number, 'ignore_sticky_posts' => 1, 'suppress_filters' => false);
		$tax_query = array('ralation' => 'AND' );

		if($cat_id!='' && $cat_id!='-1'){
					$tax_query[] = array(
						array(
							'taxonomy' => 'category',
							'field' => 'id',
							'terms' => array( $cat_id ),
							'operator' => 'IN' )
					);
		}
	    if(!empty($exclude_fromats)){
	    	$tax_query[]=array(
	    		array(
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => $exclude_fromats,
						'operator' => 'NOT IN' )
				);
		}

	  $args['tax_query'] = $tax_query;#print_r($args);
#		posts = get_posts($args);																			#	origin

		$posts_in = query_posts($args_in);														# 上部固定1つの記事
		$posts_not = query_posts($args_not);													# 上部固定以外
		wp_reset_query();																							# query_posts()呼び出し後のお呪い

	if ( isset($sticky_flag[0])) {
		$posts = array_merge_recursive($posts_in, $posts_not);				# 先頭表示がある場合 先頭とcategory抽出
	} else {
		$posts = $posts_not;																					# 先頭表示がない場合 category抽出
	}
#print_r($posts);

		$width_img = $recent_posts['topix_width_img']; $height_img = $recent_posts['topix_height_img'];	# topix thumbnail サイズ 追加2015/7 不要 2015/8
		$post_w = $recent_posts['topix_post_w'];
		$show .= "\n".'<section class="topix-latest-posts" style="text-align:justify; -moz-column-count:'.$recent_posts['topix_intval'].'; -moz-column-gap:12px; -moz-column-rule: 1px solid #c4c8cc; -webkit-column-count:'.$recent_posts['topix_intval'].'; -webkit-column-gap:12px; -webkit-column-rule: 1px solid #c4c8cc;">'."\n";

		foreach ($posts as $post) {																		# origin

/* thumbnail がない場合 dummy画像 */
			 if ( !has_post_thumbnail($post->ID) ) {
				$thumbnail = $recent_posts['dummy_thumbnail_url'];
				$show .= '<article class="topix-wrapper">'."\n";
				$show .= '<a href="' .get_permalink($post->ID). '" class="topix_href">';
				$width_10 = $recent_posts['topix_width_img'] + 10;
				$show .= '<div class="item-topix"' . ' data-itemid="'. $post->ID . '" data-type="standard" data-defwidth="' . $width_10 . '" style="opacity:1; width:' . $post_w . '; top:0px;">';
				$alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
				$show .= '<div class="img_topix">';
				$show .= '<img src="' . $thumbnail . '" class="aligncenter img-frame_s" style="opacity:1; width:'. $width_img .';"/>';
				$show .= '</div>';					# img_hidden
				$show .= '<div class="topix-info-wrapper" style="text-align:center">';
				$show .= '<h3 class="topix-title">' . $post->post_title . '</h3>';
				$show .= '<span class="topix-post-info"><small>' .$show_times. '</small></span>';
				$show .= '</div></div>';		#</.lp-info-wrapper></.pg_item>
				$show .= '</a>'."\n";
				$show .= '</article>'."\n";	#</.lp-wrapper_topix>

/* thumbnail がある場合 */
			 } elseif (has_post_thumbnail($post->ID)) {

			 	$thumb_id = get_post_thumbnail_id($post->ID);
				if(function_exists('bc_get_resized_image')){
//					$large_image_url = wp_get_attachment_image_src( $thumb_id, 'medium');
//					$thumbnail=bc_get_resized_image($large_image_url[0], $topix_thumbnail_width, $topix_thumbnail_height);
				} else {
					$thumb_data = wp_get_attachment_image_src($thumb_id, 'thumbnail' );		# サムネイルサイズ取得
					$thumbnail = $thumb_data[0];
				}
					if (get_the_modified_date() != get_the_time()) {											# 投稿日か更新日 修正 2017/2
						$show_times = date("Y-m-d", strtotime($post->post_date));
					} else {																															# 更新日
						$show_times = date("Y-m-d", strtotime($post->post_date));
					}	# has_post_thumbnail($post->ID)

				$show .= '<article class="topix-wrapper">'."\n";
				$show .= '<a href="' .get_permalink($post->ID). '" class="topix_href">';
				$width_10 = $recent_posts['topix_width_img'] + 10;
				$show .= '<div class="item-topix"' . ' data-itemid="'. $post->ID . '" data-type="standard" data-defwidth="' . $width_10 . '" style="opacity:1; width:' . $post_w . '; top:0px;">';
				$alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
				$show .= '<div class="img_topix">';
				$show .= '<img src="' . $thumbnail . '" class="aligncenter img-frame_s" style="opacity:1; width:'. $width_img .';"/>';
				$show .= '</div>';					# img_hidden
				$show .= '<div class="topix-info-wrapper" style="text-align:center">';
				$show .= '<h3 class="topix-title">' . $post->post_title . '</h3>';
				$show .= '<span class="topix-post-info"><small>' .$show_times. '</small></span>';
				$show .= '</div></div>';		#</.lp-info-wrapper></.pg_item>
				$show .= '</a>'."\n";
				$show .= '</article>'."\n";	#</.lp-wrapper_topix>
			}
	}		# endforeach;

				$show .= '</section>'."\n";		#<!--/.topix-latest-posts-->
		return $show;
	}


/**
* Sanitize widget form values as they are saved.
* @see WP_Widget::update()
* @param array $new_instance Values just sent to be saved.
* @param array $old_instance Previously saved values from database.
* @return array Updated safe values to be saved.
*/
	public function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		return $instance;
	}

/**
* Back-end widget form.
* @see WP_Widget::form()
* @param array $instance Previously saved values from database.
*/
	public function form( $instance ) {
		$title = isset($instance[ 'title' ]) ? $instance['title']:'';
		$cat_id = isset($instance['category']) ? $instance['category']:'-1';
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php $item_num = isset($instance[ 'item_num' ]) ? $instance['item_num']:5; ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'item_num' ); ?>"><?php _e( 'Number of items to show:' ); ?></label>
		<input size="3" id="<?php echo $this->get_field_id( 'item_num' ); ?>" name="<?php echo $this->get_field_name( 'item_num' ); ?>" type="text" value="<?php echo esc_attr( $item_num ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:' ); ?></label>
		<select class="widefat" name="<?php echo $this->get_field_name( 'category' ); ?>" id="<?php echo $this->get_field_id( 'category' ); ?>">
			<option value="-1">ALL</option>
		<?php
		$cats=get_categories();
		foreach ($cats as $cat) {
			if($cat_id==$cat->term_id){
				echo '***';
			}else{
				echo '^^^';
			}
	        $option = '<option';
	        if($cat_id==$cat->term_id){
	            $option.=' selected="selected"';
	        }
	        $option.=' value="'.$cat->term_id.'">';
	        $option .= $cat->name;
	        $option .= '</option>';
	        echo $option;
	    }
	    ?>
			</select>
		</p>
		<p>
			<label><?php _e( 'Exclude Post Formats:' ); ?></label><br/>
		<?php $post_formats = get_terms(array('post_format'));
	foreach ($post_formats as $format) {
		?><input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_'.$format->slug ); ?>"
		id="<?php echo $this->get_field_id( 'exclude_'.$format->slug ); ?>"
		value="<?php echo $format->slug; ?>"
		<?php if(isset($instance['exclude_'.$format->slug])){ ?>
		checked="checked"
		<?php } ?>
		><label><?php echo $format->name; ?></label><br/>
	<?php
	}
	?>
		</p>
	<?php
}

	/**
	 * Gets the URL for a Timthumb resized image.
	 * @param string  $imgurl the original image URL
	 * @param string  $width  the width to which the image will be cropped
	 * @param string  $height the height to which the image will be cropped
	 * @param string  $crop whether to crop the image to exact proportions
	 * @return string the URL of the image resized with Timthumb
	 */
	public function bc_get_resized_image( $imgurl, $width, $height='', $crop = false, $increase_size = false ) {
		if($height && !$crop){
			$crop = true;
		}
		$width = (int)$width;
		$height = (int)$height;

		if($increase_size){
			$new_width = $width+150;
			$new_height = $new_width*$height/$width;
		}else{
			$new_width = $width;
			$new_height = $height;
		}
		$resized_img = aq_resize( $imgurl, $new_width, $new_height, $crop, true, true );
		if(!$resized_img){
			//the Aqua Resizer script could not crop the image, return the original image
			$resized_img = $imgurl;
		}
		return $resized_img;
	}

} // class Foo_Widget


function bc_register_recent_posts_widget(){
	register_widget("BcChloe_Recent_Posts");
}

add_action('widgets_init', 'bc_register_recent_posts_widget');

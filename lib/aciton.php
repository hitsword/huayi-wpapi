<?php
/*=================插件自带操作====================*/
/* 获取所有分类法 */
function huayi_wpapi_get_taxonomies(){
  $taxonomies = get_taxonomies();
  unset($taxonomies['nav_menu'],$taxonomies['link_category'],$taxonomies['post_format']);
  return $taxonomies;
}

/*
 * 获取指定分类法下的所有分类
 * @param string taxonomy 分类法
 *   系统分类法:
 *     category 文章分类(默认)
 *     post_tag 文章标签
 */
function huayi_wpapi_categories_list($taxonomy=''){
  if (empty($taxonomy) && !empty($_POST['taxonomy'])) {//自动获取 $taxonomy 参数
    $taxonomy = $_POST['taxonomy'];
  }
  
  $args = array(
    'orderby'=>'ID',
    'hide_empty'=>0,
    'show_count'=>1,
  );
  !empty($taxonomy) && $args['taxonomy'] = $taxonomy;
  return get_categories($args);
}

/* 获取所有POST类型 */
function huayi_wpapi_get_post_types(){
  return get_post_types(array('public'=>true));
}

/* 
 * 内容搜索
 * @param string post_type POST类型(多个用','分割) post/page/自定义
 * @param int paged 当前分页(分页输出)
 * @param int posts_per_page 每页数据
 * @param string keyword 搜索关键词
 */
function huayi_wpapi_post_search($post_type='',$paged=1,$posts_per_page=50,$keyword=''){
  !empty($_POST['post_type']) && $post_type = $_POST['post_type'];
  !empty($_POST['paged']) && $paged = $_POST['paged'];
  !empty($_POST['posts_per_page']) && $posts_per_page = $_POST['posts_per_page'];
  !empty($_POST['keyword']) && $keyword = $_POST['keyword'];
  
  $args = array(
    'post_status' => 'publish',
    's' => $keyword,
	  'paged' => empty($paged) ? 1 : $paged,
	  'posts_per_page' => empty($posts_per_page) ? 50 : $posts_per_page,
  );
  if (!empty($post_type)) {
    $post_type = explode(",",$post_type);
    is_array($post_type) && array_filter($post_type);//过滤空白
    $args['post_type'] = $post_type;
  }

  $query = new WP_Query( $args );
  if ( $query->have_posts() ) {
    // 通过查询的结果，开始主循环
    while ( $query->have_posts() ) {
      $query->the_post();
      $data = array('ID'=>get_the_ID());
      if (get_post_type() == 'post' && get_post_format()) {
        $data['post_type'] = get_post_format();
      } else {
        $data['post_type'] = get_post_type();
      }
      if($data['post_type']!='page'){
        $cat = wp_get_post_terms($data['ID'],'category',array('fields'=>'ids'));
        $data['cat_ID'] = implode(",",$cat);
      }
      $data['title'] = get_the_title();
      $data['excerpt'] = wp_trim_words( do_shortcode(get_the_content('',true)), 300);
      $data['url'] = esc_url( get_permalink($data['ID']) );
      if (get_option('huayi_wpapi_thumbnail_size')) {
        $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null,get_option('huayi_wpapi_thumbnail_size'));
      } else {
        $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null);
      }

      $res[] = apply_filters( 'huayi_wpapi_posts_filters',$data );//改变最终返回结果
    }
  } else {
    $res = '404';
  }
  // 重置请求数据
  wp_reset_postdata();
  return $res;
}

/* 
 * 获取 POST 列表
 * @param string post_type POST类型(单个) post/page/自定义
 * @param int paged 当前分页(分页输出)
 * @param int posts_per_page 每页数据
 * @param int cat_id 分类ID/空
 * @param string taxonomy 自定义分类法/false
 */
function huayi_wpapi_post_list($post_type='',$paged=1,$posts_per_page=50,$cat_id='',$taxonomy=false){
  !empty($_POST['post_type']) && $post_type = $_POST['post_type'];
  !empty($_POST['paged']) && $paged = $_POST['paged'];
  !empty($_POST['posts_per_page']) && $posts_per_page = $_POST['posts_per_page'];
  !empty($_POST['cat_id']) && $cat_id = $_POST['cat_id'];
  !empty($_POST['taxonomy']) && $taxonomy = $_POST['taxonomy'];
  
  $args = array(
    'post_status' => 'publish',
	  'post_type' => $post_type,
	  'paged' => empty($paged) ? 1 : $paged,
	  'posts_per_page' => empty($posts_per_page) ? 50 : $posts_per_page,
  );
  if (!empty($cat_id)) {
    if (!empty($taxonomy)) {
      $args['tax_query'] = array(
        array(
          'taxonomy' => $taxonomy,
          'field'    => 'term_id',
          'terms'    => $cat_id,
        ),
      );
    }else {
      $args['cat'] = $cat_id;
    }
  }

  $query = new WP_Query( $args );
  if ( $query->have_posts() ) {
    // 通过查询的结果，开始主循环
    while ( $query->have_posts() ) {
      $query->the_post();
      $data = array('ID'=>get_the_ID());
      if (get_post_type() == 'post' && get_post_format()) {
        $data['post_type'] = get_post_format();
      } else {
        $data['post_type'] = get_post_type();
      }
      if($data['post_type']!='page'){
        $cat = wp_get_post_terms($data['ID'],'category',array('fields'=>'ids'));
        $data['cat_ID'] = implode(",",$cat);
      }
      $data['title'] = get_the_title();
      $data['excerpt'] = wp_trim_words( do_shortcode(get_the_content('',true)), 300);
      $data['url'] = esc_url( get_permalink($data['ID']) );
      if (get_option('huayi_wpapi_thumbnail_size')) {
        $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null,get_option('huayi_wpapi_thumbnail_size'));
      } else {
        $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null);
      }

      $res[] = apply_filters( 'huayi_wpapi_posts_filters',$data );//改变最终返回结果
    }
  } else {
    $res = '404';
  }
  // 重置请求数据
  wp_reset_postdata();
  return $res;
}

/* 
 * 根据post_id获取POST内容
 * @param int post_id
 */
function huayi_wpapi_post_by_id($post_id=''){
  !empty($_POST['post_id']) && $post_id = $_POST['post_id'];
  
  $post = get_post( $post_id );
  if ( $post ) {
    $data['ID'] = $post->ID;
    $data['post_status'] = $post->post_status;
    if ($post->post_type == 'post' && get_post_format($post)) {
      $data['post_type'] = get_post_format($post);
    } else {
      $data['post_type'] = $post->post_type;
    }
    if($data['post_type']!='page'){
      $cat = wp_get_post_terms($data['ID'],'category',array('fields'=>'ids'));
      $data['cat_ID'] = implode(",",$cat);
    }
    $data['title'] = $post->post_title;
    $data['excerpt'] = wp_trim_words( do_shortcode($post->post_content), 300);
    $data['url'] = esc_url( get_permalink($data['ID']) );
    if (get_option('huayi_wpapi_thumbnail_size')) {
      $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null,get_option('huayi_wpapi_thumbnail_size'));
    } else {
      $data['thumbnail'] = huayi_wpapi_get_post_thumbnail_url(null);
    }
    $res = apply_filters( 'huayi_wpapi_posts_filters',$data );//改变最终返回结果
  } else {
    $res = '404';
  }
  return $res;
}


/*=================下方增加或修改====================*/

/* 检查请求有效性 */
function huayi_wpapi_check_action($action=''){//在增加Action时需要在下方Action列表增加
  $action = empty($action) ? $_POST['action'] : $action;
  $action_list = array(
    'huayi_wpapi_get_taxonomies',
    'huayi_wpapi_categories_list',
    'huayi_wpapi_get_post_types',
    'huayi_wpapi_post_search',
    'huayi_wpapi_post_list',
    'huayi_wpapi_post_by_id',
    'huayi_wpapi_media',//获取媒体
    'huayi_wpapi_media_delete',//删除媒体
    'huayi_wpapi_media_add',//添加媒体
    'huayi_wpapi_media_by_id',//根据ID查媒体
  );
  if (function_exists($action) && in_array($action,$action_list)) {
    return true;
  }
  return false;
}

/* 
 * 获取媒体 action = huayi_wpapi_media
 * @param int paged 页码
 * @param int posts_per_page 每页数量
 * @param string keyword 搜索关键词
 */
function huayi_wpapi_media($paged=1,$posts_per_page=50,$keyword=''){
  !empty($_POST['paged']) && $paged = $_POST['paged'];
  !empty($_POST['posts_per_page']) && $posts_per_page = $_POST['posts_per_page'];
  !empty($_POST['keyword']) && $keyword = $_POST['keyword'];
  
  $args = array(
    'post_status' => 'any',
    's' => $keyword,
	  'paged' => empty($paged) ? 1 : $paged,
	  'posts_per_page' => empty($posts_per_page) ? 50 : $posts_per_page,
  );
  $args['post_type'] = 'attachment';
  
  $query = new WP_Query( $args );
  if ( $query->have_posts() ) {
    $res['total'] = $query->found_posts;
    // 通过查询的结果，开始主循环
    while ( $query->have_posts() ) {
      $query->the_post();
      $data = get_post( get_the_ID() );
      if (function_exists('get_fields')) {//返回所有ACF自定义字段
        $data->acf_fields = get_fields($data->ID);
      }
      $res['lists'][] = apply_filters( 'huayi_wpapi_media_filters',$data );//改变最终返回结果
    }
  } else {
    $res = '404';
  }
  // 重置请求数据
  wp_reset_postdata();
  return $res;
}

/* 
 * 根据post_id获取媒体 action = huayi_wpapi_media_by_id
 * @param int post_id
 */
function huayi_wpapi_media_by_id($post_id=''){
  !empty($_POST['post_id']) && $post_id = $_POST['post_id'];
  
  $post = get_post( $post_id );
  if ( $post->post_type = 'attachment' ) {
    $data = $post;
    if (function_exists('get_fields')) {//返回所有ACF自定义字段
      $data->acf_fields = get_fields($data->ID);
    }
    $res = apply_filters( 'huayi_wpapi_media_filters',$data );//改变最终返回结果
  } else {
    $res = '404';
  }
  return $res;
}

/* 
 * 删除媒体 action = huayi_wpapi_media_delete
 * @param int attachmentid 附件ID
 */
function huayi_wpapi_media_delete($attachmentid=0 ){
  !empty($_POST['attachmentid']) && $attachmentid = $_POST['attachmentid'];
  if ($attachmentid > 0) {
    $res = wp_delete_attachment( $attachmentid );
  }else {
    $res = '404';
  }
  return $res;
}

/* 
 * 添加媒体 action = huayi_wpapi_media_add
 * @param string file 附件字段
 * @param int post_id 附件
 */
function huayi_wpapi_media_add($file, $post_id=0){
  !empty($_POST['file']) && $file = $_POST['file'];
  !empty($_POST['post_id']) && $post_id = $_POST['post_id'];
  
	// These files need to be included as dependencies when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
	// Let WordPress handle the upload.
	// Remember, $file is the name of our file input in our form above.
	$attachment_id = media_handle_upload( $file, $post_id );
	
	if ( is_wp_error( $attachment_id ) ) {
		$res['msg'] = '上传失败';
	} else {
		$res['msg'] = '上传成功';
    $res['ID'] = $attachment_id;
    $res['media'] = huayi_wpapi_media_by_id($attachment_id);
	}
	return $res;
}
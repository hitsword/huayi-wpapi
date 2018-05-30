<?php
/*=================插件自带操作====================*/
/* 获取所有分类法 */
function huayi_get_taxonomies(){
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
function huayi_categories_list($taxonomy=''){
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
function huayi_get_post_types(){
  return get_post_types(array('public'=>true));
}

/* 
 * 内容搜索
 * @param string post_type POST类型(多个用','分割) post/page/自定义
 * @param int paged 当前分页(分页输出)
 * @param int posts_per_page 每页数据
 * @param string keyword 搜索关键词
 */
function huayi_post_search($post_type='',$paged=1,$posts_per_page=50,$keyword=''){
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
function huayi_post_list($post_type='',$paged=1,$posts_per_page=50,$cat_id='',$taxonomy=false){
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
function huayi_post_by_id($post_id=''){
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
function huayi_check_action($action=''){//在增加Action时需要在下方Action列表增加
  $action = empty($action) ? $_POST['action'] : $action;
  $action_list = array(
    'huayi_get_taxonomies',
    'huayi_categories_list',
    'huayi_get_post_types',
    'huayi_post_search',
    'huayi_post_list',
    'huayi_post_by_id',
  );
  if (function_exists($action) && in_array($action,$action_list)) {
    return true;
  }
  return false;
}

/* 
 * 改变插件自带的'huayi_post_search'&'huayi_post_list'&'huayi_post_by_id'的返回结果
 */
function huayi_wpapi_posts_filters($data){//改变最终返回结果
  if ($data['post_type']=='product') {
    $cat = wp_get_post_terms($data['ID'],'product_category',array('fields'=>'ids'));//获取指定分类法的分类
    $data['cat_ID'] = implode(",",$cat);
    
    if (function_exists('get_field')) {//获取自定义参数
      $data['spec_n_price'] = get_field('spec_n_price',$data['ID']);
      $data['ent_id'] = get_field('ent_id',$data['ID']);
      $data['type'] = get_field('type',$data['ID']);
      $data['point'] = get_field('point',$data['ID']);
    }
  }
  return $data;
}
add_filter( 'huayi_wpapi_posts_filters', 'huayi_wpapi_posts_filters' );
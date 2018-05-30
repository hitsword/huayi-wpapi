<?php
/*
Plugin Name: HuaYi WordPress API
Plugin URI: http://www.huayizhiyun.com
Description: WordPress被动接口与主动接口.
Version: 2.0
Author: 华怡智云
Author URI: http://www.huayizhiyun.com
License: GPL
Copyright: 华怡智云
*/

/* 定义常量 */
define('HUAYI_WPAPI_URL',plugin_dir_url(__FILE__));//定义插件目录常量

/* 加载基本文件 */
require_once('lib/init.php');//调用初始化文件
require_once('lib/function.php');//调用公共函数文件

/*================被动接口====================*/

require_once('lib/aciton.php');//调用接口操作文件

/* 增加Api路由 */
function huayi_wpapi_init() {
  add_rewrite_rule( '^huayi-wpapi','index.php?huayi_route=/','top' );
  
  global $wp;
  $wp->add_query_var( 'huayi_route' );
}
add_action( 'init', 'huayi_wpapi_init' );

/* 返回Api请求结果 */
function huayi_wpapi_rest(){
  if (empty($GLOBALS['wp']->query_vars['huayi_route'])) {
    return;
  } else {
    if (!huayi_wpapi_ip_check()) {//检查IP
      $res = array('errcode'=>'403','errmsg'=>'无权访问!');
    }elseif (!huayi_wpapi_signature_check()) {//检查签名
      $res = array('errcode'=>'403','errmsg'=>'签名无效!');
    } else {
      if (!huayi_check_action()) {//检查请求
        $res = array('errcode'=>'404','errmsg'=>'无效请求!');
      } else {//执行请求
        $res = array('errcode'=>'1','errmsg'=>'请求成功!');
        $res['data'] = $_POST['action']();
      }
    }
    header ( "Pragma: no-cache" );
    echo json_encode($res);exit;
  }
  exit;
}
if( !is_admin() ) {
  add_action( 'parse_request', 'huayi_wpapi_rest' );
}

/*================主动接口====================*/

require_once('lib/callback.php');//调用接口操作文件
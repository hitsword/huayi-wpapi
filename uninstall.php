<?php
/**
 * uninstalling huayi wordpress api
 */
if(! defined('WP_UNINSTALL_PLUGIN'))exit(); // 如果 uninstall 不是从 WordPress 调用，则退出
/* 删除 wp_options 表中的对应记录 */ 
delete_option('huayi_wpapi_center'); 
delete_option('huayi_wpapi_callback');
delete_option('huayi_wpapi_token'); 
delete_option('huayi_wpapi_ips'); 
delete_option('huayi_wpapi_thumbnail_size');
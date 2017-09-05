<?php
/* 加载类库 */
require_once('class/Acquisition.class.php');
$Acquisition = new Acquisition();

/* Callback操作 */

/* 保存Post时Callback */
function huay_save_post_callback( $post_id ) {
  $callback = get_option('huayi_wpapi_callback');
  if (!empty($callback)){
    global $Acquisition;

    $conf['post'] = huayi_signature_make();
    $conf['post']['data'] = huayi_post_by_id($post_id);
    //$conf['post']['data'] = get_post($post_id);

    $Acquisition->HuayiCurl($callback, $conf);
  }
}
add_action( 'save_post', 'huay_save_post_callback' );
<?php
/* 插件函数部分 */

/* Discuz 提取的加密解密函数 */
if(!function_exists('huayi_authcode')) {
 /**
  * @param string $string 原文或者密文
  * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
  * @param string $key 密钥
  * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
  * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
  *
  * @example
  *
  *  $a = authcode('abc', 'ENCODE', 'key');
  *  $b = authcode($a, 'DECODE', 'key');  // $b(abc)
  *
  *  $a = authcode('abc', 'ENCODE', 'key', 3600);
  *  $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
  **/
  function huayi_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0){
    if($operation == 'DECODE') {
       $string = str_replace('[a]','+',$string);
       $string = str_replace('[b]','&',$string);
       $string = str_replace('[c]','/',$string);
     }
    $ckey_length = 4;
    $key = md5($key ? $key : 'livcmsencryption ');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
   
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
      $ustr = $keyc.str_replace('=', '', base64_encode($result));
      $ustr = str_replace('+','[a]',$ustr);
      $ustr = str_replace('&','[b]',$ustr);
      $ustr = str_replace('/','[c]',$ustr);
      return $ustr;
    }
  }
} 

/* CURL GET */
if(!function_exists('huayi_vget')) {//TODO废弃
    /**
      * @param string $url GET的URL
      *
      * @example
      *
      *  $url = "http://www.baidu.com";
      *  $a = huayi_vget($url);
      *
    **/
  function huayi_vget($url) { // 模拟提交数据函数
        $curl = curl_init (); // 启动一个CURL会话
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
        curl_setopt ( $curl, CURLOPT_ENCODING, "gzip, deflate" );//gzip压缩
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 20 ); // 设置超时限制防止死循环，用来CC设置2-3为佳
        curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec ( $curl ); // 执行操作
        if (curl_errno ( $curl )) {
                echo 'Errno' . curl_error ( $curl );
        }
        curl_close ( $curl ); // 关键CURL会话
        return $tmpInfo; // 返回数据
  }
} 

/* CURL POST */
if(!function_exists('huayi_vpost')) {//TODO废弃
 /**
  * @param string $url GET的URL
  * @param array/string $postdata 发送的数据
  * @return string
  *
  * @example
  *
  *  $url = "http://www.baidu.com";
  *  $postdata = array('a'=>'1','b'=>'2');
  *  $a = huayi_vget($url,$postdata);
  *
 **/
  function huayi_vpost($url, $postdata) { // 模拟提交数据函数
    $curl = curl_init (); // 启动一个CURL会话
    curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
    curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
    curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
    is_array($postdata) && $postdata = http_build_query($postdata);//自动把数组转换为HTTP请求
    curl_setopt ( $curl, CURLOPT_POSTFIELDS, $postdata ); // Post提交的数据包
    curl_setopt ( $curl, CURLOPT_TIMEOUT, 20 ); // 设置超时限制防止死循环
    curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
    curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec ( $curl ); // 执行操作
    if (curl_errno ( $curl )) {
      print curl_error ( $curl );
    }
    curl_close ( $curl ); // 关键CURL会话
    return $tmpInfo; // 返回数据
  }
} 

/* 获取当前页面完整地址，自动判断HTTPS和端口 */
if(!function_exists('huayi_page_url')) {
  function huayi_page_url(){ 
    $pageURL = 'http'; 
    if (!empty($_SERVER["HTTPS"])) { 
      $pageURL .= "s"; 
    } 
    $pageURL .= "://"; 
    if ($_SERVER["SERVER_PORT"] != "80") { 
      $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"]; 
    } else { 
      $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]; 
    } 
    return $pageURL; 
  } 
}

/* 获取访客真实IP，CDN有效 */
if(!function_exists('huayi_get_ip')) {
  function huayi_get_ip(){ 
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $array = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
      return $array[0];
    } else {
      return $_SERVER['REMOTE_ADDR'];
    }
  }
}

/* 开启SESSION */
function huayi_session_start() {
    if(!session_id())session_start();
}

/* 关闭SESSION */
function huayi_session_destroy() {
    session_destroy ();
}

/* 获取SESSION值 */
if(!function_exists('huayi_session_get')) {
 /**
  *
  * @param string $key SESSION的键
  * @param string $default SESSION为空时的值
  * @return string 返回SESSION的值
  * 
  * @example
  * $a = huayi_session_set('a','1');
 **/
  function huayi_session_get($key, $default='') {
    if(isset($_SESSION[$key])) {
        return $_SESSION[$key];
    } else {
        return $default;
    }
  }
}

/* 设置SESSION值 */
if(!function_exists('huayi_session_set')) {
 /**
  *
  * @param string $key SESSION的键
  * @param string $value SESSION的值
  *
  * @example
  * $key = 'a';
  * $value = '1';
  * huayi_session_set($key, $value);
 **/
  function huayi_session_set($key, $value) {
    $_SESSION[$key] = $value;
  }
}

/* 获取文章缩略图 */
if(!function_exists('huayi_get_post_thumbnail_url')) {
 /**
  * $size: thumbnail:缩略图 medium：中图 large：大图 full：原图
 **/
  function huayi_get_post_thumbnail_url($post_id,$size='medium'){
    $post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    if ( has_post_thumbnail($post_id) ) {
      $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), $size);
    } else {
    	$thumbnail[0] = HUAYI_WPAPI_URL . 'assets/images/noimage.png';
	  }
	  return $thumbnail[0];
  }
}
/* 签名生成 */
if (!function_exists('huayi_signature_make')) {
  function huayi_signature_make(){
    $token = get_option('huayi_wpapi_token');
    $time = time();
    $signature = md5($token.$time);
    return array('time'=>$time,'signature'=>$signature);
  }
}
/* 签名验证 */
if (!function_exists('huayi_signature_check')) {
  function huayi_signature_check($signature='',$time=''){
    $signature = empty($signature) ? $_POST['signature'] : $signature;
    $time = empty($time) ? $_POST['time'] : $time;
    $token = get_option('huayi_wpapi_token');
    if ( time()-$time < 600 ) {//时间大于10分钟无效
      if (md5($token.$time)==$signature) {//判断签名
        return true;
      }
    }
    return false;
  }
}
/* IP地址验证 */
if (!function_exists('huayi_ip_check')) {
  function huayi_ip_check($ip=''){
    $ip = empty($ip) ? huayi_get_ip() : $ip;//获取访客IP
    $ips = trim(get_option('huayi_wpapi_ips'));//获取IP白名单
    $ips_arr = explode(",",$ips);//转换IP白名单为数组
    if (empty($ips)) {//白名单为空
      return true;
    } elseif(in_array($ip, $ips_arr)) {//在白名单
      return true;
    }
    return false;
  }
}

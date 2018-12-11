<?php
// +----------------------------------------------------------------------
// | HuayiSoftware
// +----------------------------------------------------------------------

/**
 * WordPress API类
 * WordPress API类，依赖Acquisition类
 * @category   ORG
 * @package  ORG
 * @subpackage  Huayi
 * @author    HITSword <admin@hitsword.com>
 *
 *   $options = array(
 *      'api'=>'apiurl', //API地址
 *      'token'=>'token', //TOKEN
 *      'ips'=>'127.0.0.1', //白名单IP列表，半角逗号分隔
 *   );
 *   $WPObj = new WordPress($options);
 */
class WordPress {
  public function __construct($option) {
    $this->WPAPI = isset($option['api']) ? $option['api'] : '';
    $this->WPTOKEN = isset($option['token']) ? $option['token'] : '';
    $this->WPIPS = isset($option['ips']) ? $option['ips'] : '';
  }
  
  /** 
   * 签名生成
  **/
  public function SignatureMake(){
    $token = $this->WPTOKEN;
    $time = time();
    $signature = md5($token.$time);
    return array('time'=>$time,'signature'=>$signature);
  }
  
  /** 
   * 签名校验
   * $signature
   * $time
  **/
  public function SignatureCheck($signature,$time){
    $token = $this->WPTOKEN;
    if ( time()-$time < 600 ) {//时间大于10分钟无效
      if (md5($token.$time)==$signature) {//判断签名
        return true;
      }
    }
    return false;
  }
  
  /** 
   * IP地址校验
   * $ip 接口请求IP
  **/
  public function IpCheck($ip=''){
    $ips = trim($this->WPIPS);//获取IP白名单
    $ips_arr = explode(",",$ips);//转换IP白名单为数组
    $ip = empty($ip) ? get_client_ip() : $ip;//获取访客IP
    if (empty($ips)) {//白名单为空
      return true;
    } elseif(in_array($ip, $ips_arr)) {//在白名单
      return true;
    }
    return false;
  }

  /** 
   * 公共请求函数
   * @param $data 传递的数据
   * 
  **/
  public function Query($data=array()){
    $conf['post'] = self::SignatureMake();
    $conf['post'] = array_merge($data,$conf['post']);
    if (stripos($this->WPAPI,"ttps://");) {
      $conf['ssl'] = 1;
    }
    import('ORG.Huayi.Acquisition');
    $res = Acquisition::HuayiCurl($this->WPAPI,$conf);
    return json_decode($res,true);
  }
}//类定义结束
<?php
class WPDemoAction extends Action {
  /*
   * 网站接口实例化
   * 
  */
  public function SiteObj(){//OK
    import('ORG.Huayi.WordPress');
    $options = array(
 			'api'=>'http://test.com/huayi-wpapi', //API地址
 			'token'=>'token', //TOKEN
 			'ips'=>'', //白名单IP列表，半角逗号分隔
 		);
 	  return new WordPress($options);
  }
  /*
   * 网站最新动态获取
   * 
  */
  public function SiteGetNews(){//OK
    $data = array(
      'action'=>'huayi_wpapi_post_list',
      'post_type'=>'post',
      'paged'=>1,
      'posts_per_page'=>6,
      'cat_id'=>1,//WordPress分类ID
    );
    $SiteObj = self::SiteObj();
    return $SiteObj->Query($data);
  }
  

  /**
   * 接口回调
   * @param $data 传递的数据
   * 
  **/
  public function Callback(){//OK
    //F('callback'.time(),$_POST);die;//调试用：接收WordPress发送过来的内容
    $SiteObj = self::SiteObj();
    if ($SiteObj->SignatureCheck($_POST['signature'],$_POST['time']) && $SiteObj->IpCheck()) {//验证签名与IP地址
      $Data = $_POST['data'];
      switch ($Data['post_type']) {//删除缓存
        case 'post':
          S('post-'.$Data['ID'],NULL);
        break;
        case 'page':
          S('page-'.$Data['ID'],NULL);
        break;
        default:
          return false;
      }
    }
  }
}
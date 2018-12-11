<?php
// +----------------------------------------------------------------------
// | HuayiSoftware
// +----------------------------------------------------------------------

/**
 * Acquisition工具类 //TODO:增加内容处理（切割等）
 * 提供一系列的采集方法
 * @category   ORG
 * @package  ORG
 * @subpackage  Huayi
 * @author    HITSword <admin@hitsword.com>
 */
class Acquisition {
	
   /** //TODO:升级支持Cookie
    * 使用 Curl 采集远程内容
    * @static
    * @access public
    * @param string $url 远程URL
    * @param array $conf 其他配置信息
    *        string host 指定主机头(域名)
    *        string|int ssl CA证书文件路径/1为忽略证书验证
    *        string post      post的内容,字符串或数组,key=value&形式
    *        string useragent 自定义UserAgent
    *        string outip 自定义出口IP
    *        mixed showhead 显示返回的Header
    *        mixed nobody 不显示返回的Body
    *        mixed noerror 不显示错误
    *        string referer   来路域名
    *        int    timeout   采集超时时间
    *        int    json      JSON模式提交0是默认post,1是json
    * @return mixed
    */
    static public function HuayiCurl($url, $conf = array()) {
        $curl = curl_init (); // 启动一个CURL会话
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        if ( isset($conf['host']) ) {//指定主机头(域名)
          curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Host: '.$conf['host']));
        }
        if ( isset($conf['ssl']) ) {
          if ($conf['ssl'] == 1) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书  
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名  
          } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);// 检查证书中是否设置域名
            curl_setopt($curl, CURLOPT_CAINFO, $conf['ssl']);
          }
        }
        if ( isset($conf['post']) ) {
          if ( isset($conf['json']) )  {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($conf['post'])
              )
            );
          }else {
            is_array($conf['post']) && $conf['post'] = http_build_query($conf['post']);//自动把数组转换为HTTP请求
          }
          curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
          curl_setopt ( $curl, CURLOPT_POSTFIELDS, $conf['post'] ); // Post提交的数据包
        }else {
         	curl_setopt ( $curl, CURLOPT_ENCODING, "gzip, deflate" );//gzip压缩
        }
        curl_setopt ( $curl, CURLOPT_TIMEOUT, isset($conf['timeout']) ? $conf['timeout'] : 60 ); // 设置超时限制防止死循环
        curl_setopt ( $curl, CURLOPT_REFERER, isset($conf['referer']) ? $conf['referer'] : '-' );//来路域名
        curl_setopt ( $curl, CURLOPT_USERAGENT, isset($conf['useragent']) ? $conf['useragent'] : 'baidu');//自定义UserAgent 
        if ( isset($conf['outip']) ) {
         	curl_setopt ( $curl, CURLOPT_INTERFACE, $conf['outip'] );
        }
        if ( isset($conf['showhead']) ){ // 显示返回的Header区域内容
          curl_setopt ( $curl, CURLOPT_HEADER, 1 );
        }
        if ( isset($conf['nobody']) ){ // 不显示BODY内容
          curl_setopt ( $curl, CURLOPT_NOBODY, 1 );
        }
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec ( $curl ); // 执行操作
        if ( curl_errno ($curl) && empty($conf['noerror']) ) {
          $tmpInfo.= 'Errno' . curl_error ( $curl );
        }
        curl_close ( $curl ); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }
    	
   /**
    * 使用 fsockopen 通过 HTTP 协议直接访问(采集)远程内容
    * 如果主机或服务器没有开启 CURL 扩展可考虑使用
    * fsockopen 比 CURL 稍慢,但性能稳定
    * @static
    * @access public
    * @param string $url 远程URL
    * @param array $conf 其他配置信息
    *        int   limit 分段读取字符个数
    *        string post  post的内容,字符串或数组,key=value&形式
    *        string cookie 携带cookie访问,该参数是cookie内容
    *        string ip    如果该参数传入,$url将不被使用,ip访问优先
    *        int    timeout 采集超时时间
    *        bool   block 是否阻塞访问,默认为true
    * @return mixed
    */
    static public function HuayiFsockopen($url, $conf = array()) {
        $return = '';
        if(!is_array($conf)) return $return;

        $matches = parse_url($url);
        !isset($matches['host']) 	&& $matches['host'] 	= '';
        !isset($matches['path']) 	&& $matches['path'] 	= '';
        !isset($matches['query']) 	&& $matches['query'] 	= '';
        !isset($matches['port']) 	&& $matches['port'] 	= '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;

        $conf_arr = array(
            'limit'		=>	0,
            'post'		=>	'',
            'cookie'	=>	'',
            'ip'		=>	'',
            'timeout'	=>	15,
            'block'		=>	TRUE,
            );

        foreach (array_merge($conf_arr, $conf) as $k=>$v) ${$k} = $v;

        if($post) {
            if(is_array($post))
            {
                $post = http_build_query($post);
            }
            $out  = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: '.strlen($post)."\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out  = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if(!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if(!$status['timed_out']) {
                while (!feof($fp)) {
                    if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while(!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
    }	

   /**
    * 获取mime_content类型返回图片后缀
    * @static
    * @access public
    * @param string $url 图片URL
    * @return string jpg
    */
    static public function HuayiMimeImgSuffix($url) {
        static $contentType = array(
			    'image/bmp'		=> 'bmp',
			    'image/gif'		=> 'gif',
			    'image/ief'		=> 'ief',
			    'image/jpeg'	  => 'jpg',
			    'image/jpg'		=> 'jpg',
		    	'image/x-portable-bitmap'		=> 'pbm',
		    	'image/x-portable-graymap'		=> 'pgm',
		    	'image/png'		=> 'png',
		    	'image/x-portable-anymap'		=> 'pnm',
		    	'image/x-portable-pixmap'		=> 'ppm',
		    	'image/x-cmu-raster'		=> 'ras',
		    	'image/x-rgb'		=> 'rgb',
		    	'image/tiff'		=> 'tif',
		    	'image/vnd.wap.wbmp'	=> 'wbmp',
		    	'image/x-xbitmap'		=> 'xbm',
		    	'image/x-xpixmap'		=> 'xpm',
		    	'image/x-xwindowdump'		=> 'xwd',
        );
        $header = get_headers($url,true);//获取文件头
        if (is_array($header['Content-Type'])) {
          $header['Content-Type'] = end($header['Content-Type']);
        }
        
        foreach ($contentType as $key => $value){
        	if (stripos($key,$header['Content-Type'])>-1) {
        	 	return $value;
        	}
        }
        
        return false;
    }

   /**
    * PHP获取页面中的所有链接
    * @static
    * @access public
    * @param string $url 文件名内容
    * @param string $data 本地保存路径与文件名
    * @param int $onlyout 是否仅外链
    * @param int $onlyhost 是否仅域名
    * @return array
    */
    static public function HuayiGetPageLink($url,$data,$onlyout=0,$onlyhost=0){
    	set_time_limit(0);
    	$html = empty($data) ? file_get_contents($url) : $data;
	    preg_match_all("/<a(s*[^>]+s*)href=([\"|']?)([^\"'>\s]+)([\"|']?)/ies",$html,$out);
	    $arrLink=$out[3];
	    $arrUrl=parse_url($url);
	    $dir='';
	    if( !empty($arrUrl['path']) ){
	    	$dir=str_replace("\\","/",$dir=dirname($arrUrl['path']));
	    	if($dir=="/"){
	    		$dir="";
	    	}
	    }
	    if(is_array($arrLink)&&count($arrLink)>0){
		    $arrLink=array_unique($arrLink);
		    foreach($arrLink as $key=>$val){
		    	$val=strtolower($val);
		    	if(preg_match('/^#*$/isU',$val)){
			    	unset($arrLink[$key]);
			    }elseif(preg_match('/^\//isU',$val)){
			    	$arrLink[$key] = $arrUrl['scheme'].'://'.$arrUrl['host'].$val;
			    }elseif(preg_match('/^javascript/isU',$val)){
			    	unset($arrLink[$key]);
			    }elseif(preg_match('/^mailto:/isU',$val)){
			    	unset($arrLink[$key]);
			    }elseif(!preg_match('/^\//isU',$val)&&strpos($val,'http://')===FALSE&&strpos($val,'https://')===FALSE){
			    	$arrLink[$key] = $arrUrl['scheme'].$arrUrl['host'].$dir.'/'.$val;
			    }
			    if ( isset($arrLink[$key]) && !empty($onlyout) && strstr($arrLink[$key],$arrUrl['host']) ) {
			      unset($arrLink[$key]);
			    }
			    if ( isset($arrLink[$key]) && !empty($onlyhost) ){
			      unset($tmp);
			      $tmp = parse_url($arrLink[$key]);
			      $arrLink[$key] = $tmp['scheme'].'://'.$tmp['host'];
			    }
	    	}
    	}
	    sort($arrLink);
	    return $arrLink;
    }
    
   /**
    * 输入始末关键词提取主要内容
    * @static
    * @access public
    * @param string $str 提取前内容
    * @param string $start 开始关键词
    * @param string $end 结束关键词
    * @param int $option 选项 1:不包含前后关键词 2:包含前关键词 3:包含后关键词 其他(默认):包含前后关键词
    * @return str
    */
    static public function HuayiGetBody($str,$start,$end,$option){
			$strarr=explode($start,$str);
			$tem=$strarr[1];
			if(empty($end)){
			  return $tem;
			}else{
			  $strarr=explode($end,$tem);
			  if($option==1){
			    return $strarr[0];
			  }
			  if($option==2){
			    return $start.$strarr[0];
			  }
			  if($option==3){
			    return $strarr[0].$end;
			  }
			  else{
			    return $start.$strarr[0].$end;
			  }
			}
	  }
	    
   /**
    * 创建并写入文件
    * @static
    * @access public
    * @param string $data 文件名内容
    * @param string $file 本地保存路径与文件名
    * @return boolean
    */
    static public function HuayiAddFile($data,$file){
	      $fp = fopen($file,"w");
	      fwrite($fp,$data); 
	      fclose($fp);
	      if ( file_get_contents($file) == $data ) {
	       	return true;
	      }else {
	       	return false;
	      }
	  }
	  


    /**
     * 输出HTTP错误 //暂时无用
     * @param int $num
     */
    static function HuayiSendHttpStatus($code) {
        static $_status = array(
	    		// Informational 1xx
	    		100 => 'Continue',
		    	101 => 'Switching Protocols',

		    	// Success 2xx
		    	200 => 'OK',
		    	201 => 'Created',
		    	202 => 'Accepted',
		    	203 => 'Non-Authoritative Information',
	    		204 => 'No Content',
	    		205 => 'Reset Content',
	    		206 => 'Partial Content',

		    	// Redirection 3xx
		    	300 => 'Multiple Choices',
		    	301 => 'Moved Permanently',
		    	302 => 'Found',  // 1.1
	    		303 => 'See Other',
	    		304 => 'Not Modified',
	    		305 => 'Use Proxy',
	    		// 306 is deprecated but reserved
	    		307 => 'Temporary Redirect',

	    		// Client Error 4xx
	    		400 => 'Bad Request',
	    		401 => 'Unauthorized',
	    		402 => 'Payment Required',
	    		403 => 'Forbidden',
	    		404 => 'Not Found',
	    		405 => 'Method Not Allowed',
	    		406 => 'Not Acceptable',
	    		407 => 'Proxy Authentication Required',
	    		408 => 'Request Timeout',
	    		409 => 'Conflict',
		    	410 => 'Gone',
	    		411 => 'Length Required',
		    	412 => 'Precondition Failed',
		    	413 => 'Request Entity Too Large',
		    	414 => 'Request-URI Too Long',
		    	415 => 'Unsupported Media Type',
		    	416 => 'Requested Range Not Satisfiable',
		    	417 => 'Expectation Failed',

		    	// Server Error 5xx
		    	500 => 'Internal Server Error',
		    	501 => 'Not Implemented',
		    	502 => 'Bad Gateway',
		    	503 => 'Service Unavailable',
		    	504 => 'Gateway Timeout',
		    	505 => 'HTTP Version Not Supported',
		    	509 => 'Bandwidth Limit Exceeded'
	    	);
		    if(isset($_status[$code])) {
		    	header('HTTP/1.1 '.$code.' '.$_status[$code]);
	    	}
    }
}//类定义结束
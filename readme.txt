=== HuaYi WordPress API ===
Contributors: 华怡软件
Tags: WordPress,API
Requires at least: 3.7.0
Tested up to: 4.8.1
Stable tag: 2.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Description ==

/**
 * 参数调用：
 *  get_option('huayi_wpapi_center')
 *  get_option('huayi_wpapi_callback')
 *  get_option('huayi_wpapi_token')
 *  get_option('huayi_wpapi_ips')
 *  get_option('huayi_thumbnail_size')
 *
 * 被动接口：
 *   关闭伪静态：index.php?huayi_route=huayi-wpapi
 *   开启伪静态: /huayi-wpapi
 * 获取$_GET变量:
 *   关闭伪静态: index.php?huayi_route=huayi-wpapi&get1=1&get2=2
 *   开启伪静态: /huayi-wpapi?get1=1&get2=2
 * 请求参数：
 * @post string action 操作 列表:/lib/action.php
 * @post string signature 签名 算法:md5(token.time)
 * @post int time 时间戳 备注:不能大于当前时间10分钟
 * 返回内容：JSON:
   {
     "errcode": "1",
     "errmsg": "请求成功!",
     "data": {}
   }
**/

文件结构：
 /assets/ 静态素材文件(图片、CSS、JS)
 /lib/ 库目录
 /lib/ init.php 初始化插件(一般无需修改)
 /lib/ function.php 插件函数(一般无需修改)
 /lib/ action.php 被动接口操作
 /lib/ callback.php 主动接口操作
 /lib/class/ 第三方类库
 /huayi-wpapi.php 插件入口文件(一般无需修改)
 /uninstall.php 卸载插件时调用(一般无需修改)
 /readme.txt 当前文件(一般无需修改)
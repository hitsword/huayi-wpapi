<?php
/* 注册激活插件时要调用的函数 */ 
  register_activation_hook( __FILE__, 'huayi_wpapi_install');   
  /* 在数据库的 wp_options 表中添加一条记录，第二个参数为默认值 */ 
  function huayi_wpapi_install() {
    add_option('huayi_wpapi_center', '', '', 'yes');//Center URL 用于实现转跳等操作
    add_option('huayi_wpapi_callback', '', '', 'yes');//回调URL(主动接口)
    add_option('huayi_wpapi_token', 'token', '', 'yes');//接口Token 验证算法：$signature = md5($token.$time)
    add_option('huayi_wpapi_ips', '', '', 'yes');//被动接口IP白名单
    add_option('huayi_wpapi_thumbnail_size', '', '', 'yes');//默认缩略图尺寸
  }

/* 注册停用插件时要调用的函数 */ 
  //register_deactivation_hook( __FILE__, 'huayi_wpapi_remove' );  
  /* 删除 wp_options 表中的对应记录 */ 
  function huayi_wpapi_remove() {
    delete_option('huayi_wpapi_center');
    delete_option('huayi_wpapi_callback');
    delete_option('huayi_wpapi_token');
    delete_option('huayi_wpapi_ips');
    delete_option('huayi_wpapi_thumbnail_size');
  }

  if( is_admin() ) {
    /*  利用 admin_menu 钩子，添加菜单 */
    add_action('admin_menu', 'huayi_wpapi_menu');
  }

  function huayi_wpapi_menu() {
    /* add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);  */
    /* 页名称，菜单名称，访问级别，菜单别名，点击该菜单时的回调函数（用以显示设置页面） */
    add_menu_page('WordPress Api', 'WPAPI', 'administrator','huayi_wpapi', 'huayi_wpapi_html_page');
  }

  function huayi_wpapi_html_page() {
    ?>
      <div class="wrap">
        <h2>WordPress API</h2>
        <form method="post" action="options.php" novalidate="novalidate">
          <!-- 下面这行代码用来保存表单中内容到数据库 -->  
          <?php wp_nonce_field('update-options'); ?>  
          <input type="hidden" name="page_options" value="huayi_wpapi_center,huayi_wpapi_callback,huayi_wpapi_token,huayi_wpapi_ips,huayi_wpapi_thumbnail_size" />
          <input type="hidden" name="action" value="update" />
          <p>WordPress Api设置</p>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="huayi_wpapi_center" ><?php _e('Center URL') ?></label></th>
              <td><input name="huayi_wpapi_center" type="text" id="huayi_wpapi_center" value="<?php form_option('huayi_wpapi_center'); ?>" class="regular-text" />
              <p class="description" id="center-description"><?php _e( 'Center URL. 用于转跳调用等功能.' ) ?></p></td>
            </tr>
            <tr>
              <th scope="row"><label for="huayi_wpapi_callback" ><?php _e('Callback URL') ?></label></th>
              <td><input name="huayi_wpapi_callback" type="text" id="huayi_wpapi_callback" value="<?php form_option('huayi_wpapi_callback'); ?>" class="regular-text" />
              <p class="description" id="callback-description"><?php _e( 'Callback URL. 回调接口.' ) ?></p></td>
            </tr>
            <tr>
              <th scope="row"><label for="huayi_wpapi_token" ><?php _e('Api Token') ?></label></th>
              <td><input name="huayi_wpapi_token" type="text" id="huayi_wpapi_token" value="<?php form_option('huayi_wpapi_token'); ?>" class="regular-text" />
              <p class="description" id="token-description"><?php _e( 'Api 秘钥.' ) ?></p></td>
            </tr>
            <tr>
              <th scope="row"><label for="huayi_wpapi_ips" ><?php _e('Api IP') ?></label></th>
              <td>
                <textarea name="huayi_wpapi_ips" rows="10" cols="50" id="huayi_wpapi_ips" class="regular-text code"><?php form_option('huayi_wpapi_ips'); ?></textarea>
                <p class="description" id="ips-description">API IP地址白名单列表,用逗号','分割，如192.168.1.1,192.168.1.2.留空则不限制。<br>你当前的IP: <?php echo huayi_get_ip(); ?></p>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="huayi_wpapi_thumbnail_size" ><?php _e('图片尺寸') ?></label></th>
              <td><input name="huayi_wpapi_thumbnail_size" type="text" id="huayi_wpapi_thumbnail_size" value="<?php form_option('huayi_wpapi_thumbnail_size'); ?>" class="regular-text" />
              <p class="description" id="thumbnail_size-description"><?php _e( '默认图片尺寸,系统自带尺寸：thumbnail/medium/large/full.如在主题或其他插件中定义了其他尺寸,可以输入定义的尺寸名。' ) ?></p></td>
            </tr>
          </table>
          <?php submit_button(); ?>
        </form>
        <?php $huayi_debug = huayi_wpapi_signature_make();?>
        <table class="form-table">
          <tr>
            <th scope="row">被动API地址:</th>
            <td><?php echo site_url();?>/huayi-wpapi/ <br>
            	  <?php echo site_url();?>/index.php?huayi_route=huayi-wpapi
            </td>
          </tr>
          <tr>
            <th scope="row">当前时间戳:</th>
            <td><?php echo $huayi_debug['time'];?></td>
          </tr>
          <tr>
            <th scope="row">当前签名:</th>
            <td><?php echo $huayi_debug['signature'];?></td>
          </tr>
        </table>
      </div>
    <?php  
  }
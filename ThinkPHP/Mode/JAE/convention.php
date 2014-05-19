<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: sunan <red.wols.s.husbang@gmail.com>
// +----------------------------------------------------------------------

defined('THINK_PATH') or exit();
return array(
    'DB_TYPE'           =>  'PDO',     // 数据库类型
    'DB_DEPLOY_TYPE'    =>  1,
    'DB_RW_SEPARATE'    =>  true,
    'DB_HOST'           =>  '', // 服务器地址
    'DB_NAME'           =>  'mysql',        // 数据库名
    'DB_USER'           =>  '',    // 用户名
    'DB_PWD'            =>  '',         // 密码
    'DB_PORT'           =>  '',        // 端口
    
    //更改模板替换变量，让普通能在所有平台下显示
    'TMPL_ENGINE_TYPE'      =>  'JAESmarty',
    'DATA_CACHE_TYPE'       =>  'tair',
);

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
    'DB_TYPE'           =>  'PDO',     // ���ݿ�����
    'DB_DEPLOY_TYPE'    =>  1,
    'DB_RW_SEPARATE'    =>  true,
    'DB_HOST'           =>  '', // ��������ַ
    'DB_NAME'           =>  'mysql',        // ���ݿ���
    'DB_USER'           =>  '',    // �û���
    'DB_PWD'            =>  '',         // ����
    'DB_PORT'           =>  '',        // �˿�
    
    //����ģ���滻����������ͨ��������ƽ̨����ʾ
    'TMPL_ENGINE_TYPE'      =>  'JAESmarty',
    'DATA_CACHE_TYPE'       =>  'tair',
);

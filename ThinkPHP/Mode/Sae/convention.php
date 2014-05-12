<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>
// +----------------------------------------------------------------------

/**
 * SAEģʽ���������ļ�
 * ���ļ��벻Ҫ�޸ģ����Ҫ���ǹ������õ�ֵ������Ӧ�������ļ����趨�͹���������������
 * �������ƴ�Сд���⣬ϵͳ��ͳһת����Сд
 * �������ò�������������Чǰ��̬�ı�
 */
defined('THINK_PATH') or exit();
$st =   new SaeStorage();
return array(
    //SAE�¹̶�mysql����
    'DB_TYPE'           =>  'mysql',     // ���ݿ�����
    'DB_DEPLOY_TYPE'    =>  1,
    'DB_RW_SEPARATE'    =>  true,
    'DB_HOST'           =>  SAE_MYSQL_HOST_M.','.SAE_MYSQL_HOST_S, // ��������ַ
    'DB_NAME'           =>  SAE_MYSQL_DB,        // ���ݿ���
    'DB_USER'           =>  SAE_MYSQL_USER,    // �û���
    'DB_PWD'            =>  SAE_MYSQL_PASS,         // ����
    'DB_PORT'           =>  SAE_MYSQL_PORT,        // �˿�
    //����ģ���滻����������ͨ��������ƽ̨����ʾ
    'TMPL_PARSE_STRING' =>  array(
        // __PUBLIC__/upload  -->  /Public/upload -->http://appname-public.stor.sinaapp.com/upload
        '/Public/upload'    =>  $st->getUrl('public','upload')
    ),
    'LOG_TYPE'          =>  'Sae',
    'DATA_CACHE_TYPE'   =>  'Memcachesae',
    'CHECK_APP_DIR'     =>  false,
);

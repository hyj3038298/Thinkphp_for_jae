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
 * ThinkPHP SAEӦ��ģʽ�����ļ�
 */
return array(
    // �����ļ�
    'config'    =>  array(
        THINK_PATH.'Conf/convention.php',   // ϵͳ��������
        CONF_PATH.'config'.CONF_EXT,      // Ӧ�ù�������
        MODE_PATH.'Sae/convention.php',//[sae] sae�Ĺ�������
    ),

    // ��������
    'alias'     =>  array(
        'Think\Log'               => CORE_PATH . 'Log'.EXT,
        'Think\Log\Driver\File'   => CORE_PATH . 'Log/Driver/File'.EXT,
        'Think\Exception'         => CORE_PATH . 'Exception'.EXT,
        'Think\Model'             => CORE_PATH . 'Model'.EXT,
        'Think\Db'                => CORE_PATH . 'Db'.EXT,
        'Think\Template'          => CORE_PATH . 'Template'.EXT,
        'Think\Cache'             => CORE_PATH . 'Cache'.EXT,
        'Think\Cache\Driver\File' => CORE_PATH . 'Cache/Driver/File'.EXT,
        'Think\Storage'           => CORE_PATH . 'Storage'.EXT,
    ),

    // ���������ļ�
    'core'      =>  array(
        THINK_PATH.'Common/functions.php',
        COMMON_PATH.'Common/function.php',
        CORE_PATH . 'Hook'.EXT,
        CORE_PATH . 'App'.EXT,
        CORE_PATH . 'Dispatcher'.EXT,
        //CORE_PATH . 'Log'.EXT,
        CORE_PATH . 'Route'.EXT,
        CORE_PATH . 'Controller'.EXT,
        CORE_PATH . 'View'.EXT,
        BEHAVIOR_PATH . 'ParseTemplateBehavior'.EXT,
        BEHAVIOR_PATH . 'ContentReplaceBehavior'.EXT,
    ),
    // ��Ϊ��չ����
    'tags'  =>  array(
        'app_begin'     =>  array(
            'Behavior\ReadHtmlCacheBehavior', // ��ȡ��̬����
        ),
        'app_end'       =>  array(
            'Behavior\ShowPageTraceBehavior', // ҳ��Trace��ʾ
        ),
        'view_parse'    =>  array(
            'Behavior\ParseTemplateBehavior', // ģ����� ֧��PHP������ģ������͵�����ģ������
        ),
        'template_filter'=> array(
            'Behavior\ContentReplaceBehavior', // ģ������滻
        ),
        'view_filter'   =>  array(
            'Behavior\WriteHtmlCacheBehavior', // д�뾲̬����
        ),
    ),
);

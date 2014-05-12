<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP Ĭ�ϵĵ���ģʽ�����ļ�
 */
defined('THINK_PATH') or exit();
// ����ģʽ����Ĭ������ ������Ӧ������Ŀ¼�����¶��� debug.php ����
return  array(
    'LOG_RECORD'            =>  true,  // ������־��¼
    'LOG_EXCEPTION_RECORD'  =>  true,    // �Ƿ��¼�쳣��Ϣ��־
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,INFO,DEBUG,SQL',  // �����¼����־����
    'DB_FIELDS_CACHE'       =>  false, // �ֶλ�����Ϣ
    'DB_SQL_LOG'            =>  true, // ��¼SQL��Ϣ
    'TMPL_CACHE_ON'         =>  false,        // �Ƿ���ģ����뻺��,��Ϊfalse��ÿ�ζ������±���
    'TMPL_STRIP_SPACE'      =>  false,       // �Ƿ�ȥ��ģ���ļ������html�ո��뻻��
    'SHOW_ERROR_MSG'        =>  true,    // ��ʾ������Ϣ
    'URL_CASE_INSENSITIVE'  =>  false,  // URL���ִ�Сд
);
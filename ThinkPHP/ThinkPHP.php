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

//----------------------------------
// ThinkPHP��������ļ�
//----------------------------------
// ��¼��ʼ����ʱ��
$GLOBALS['_beginTime'] = microtime(TRUE);
// ��¼�ڴ��ʼʹ��
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
// �汾��Ϣ
const THINK_VERSION     =   '3.2.2';

// URL ģʽ����
const URL_COMMON        =   0;  //��ͨģʽ
const URL_PATHINFO      =   1;  //PATHINFOģʽ
const URL_REWRITE       =   2;  //REWRITEģʽ
const URL_COMPAT        =   3;  // ����ģʽ
// ���ļ���׺
const EXT               =   '.class.php'; 

// ϵͳ��������
defined('THINK_PATH')   or define('THINK_PATH',     __DIR__.'/');
defined('APP_PATH')     or define('APP_PATH',       dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('APP_STATUS')   or define('APP_STATUS',     ''); // Ӧ��״̬ ���ض�Ӧ�������ļ�
defined('APP_DEBUG')    or define('APP_DEBUG',      false); // �Ƿ����ģʽ

if(function_exists('saeAutoLoader')){// �Զ�ʶ��SAE����
    defined('APP_MODE')     or define('APP_MODE',      'sae');
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',  'Sae');
}else{
    defined('APP_MODE')     or define('APP_MODE',       'common'); // Ӧ��ģʽ Ĭ��Ϊ��ͨģʽ    
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',   'File'); // �洢���� Ĭ��ΪFile    
}

defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH.'Runtime/');   // ϵͳ����ʱĿ¼
defined('LIB_PATH')     or define('LIB_PATH',       realpath(THINK_PATH.'Library').'/'); // ϵͳ�������Ŀ¼
defined('CORE_PATH')    or define('CORE_PATH',      LIB_PATH.'Think/'); // Think���Ŀ¼
defined('BEHAVIOR_PATH')or define('BEHAVIOR_PATH',  LIB_PATH.'Behavior/'); // ��Ϊ���Ŀ¼
defined('MODE_PATH')    or define('MODE_PATH',      THINK_PATH.'Mode/'); // ϵͳӦ��ģʽĿ¼
defined('VENDOR_PATH')  or define('VENDOR_PATH',    LIB_PATH.'Vendor/'); // ���������Ŀ¼
defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'Common/'); // Ӧ�ù���Ŀ¼
defined('CONF_PATH')    or define('CONF_PATH',      COMMON_PATH.'Conf/'); // Ӧ������Ŀ¼
defined('LANG_PATH')    or define('LANG_PATH',      COMMON_PATH.'Lang/'); // Ӧ������Ŀ¼
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'Html/'); // Ӧ�þ�̬Ŀ¼
defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'Logs/'); // Ӧ����־Ŀ¼
defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'Temp/'); // Ӧ�û���Ŀ¼
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/'); // Ӧ������Ŀ¼
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // Ӧ��ģ�建��Ŀ¼
defined('CONF_EXT')     or define('CONF_EXT',       '.php'); // �����ļ���׺
defined('CONF_PARSE')   or define('CONF_PARSE',     '');    // �����ļ���������

// ϵͳ��Ϣ
if(version_compare(PHP_VERSION,'5.4.0','<')) {
    ini_set('magic_quotes_runtime',0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}else{
    define('MAGIC_QUOTES_GPC',false);
}
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {
    // ��ǰ�ļ���
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGIģʽ��
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}

// ���غ���Think��
require CORE_PATH.'Think'.EXT;
// Ӧ�ó�ʼ�� 

Think\Think::start();
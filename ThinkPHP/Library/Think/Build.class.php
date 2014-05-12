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
namespace Think;
/**
 * ����ThinkPHP���Զ�����
 */
class Build {

    static protected $controller   =   '<?php
namespace [MODULE]\Controller;
use Think\Controller;
class [CONTROLLER]Controller extends Controller {
    public function index(){
        $this->show(\'<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "΢���ź�"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>��ӭʹ�� <b>ThinkPHP</b>��</p><br/>[ �����ڷ��ʵ���[MODULE]ģ���[CONTROLLER]������ ]</div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>\',\'utf-8\');
    }
}';

    static protected $model         =   '<?php
namespace [MODULE]\Model;
use Think\Model;
class [MODEL]Model extends Model {

}';
    // ���Ӧ��Ŀ¼�Ƿ���Ҫ�Զ�����
    static public function checkDir($module){
        if(!is_dir(APP_PATH.$module)) {
            // ����ģ���Ŀ¼�ṹ
            self::buildAppDir($module);
        }elseif(!is_dir(LOG_PATH)){
            // ��黺��Ŀ¼
            self::buildRuntime();
        }
    }

    // ����Ӧ�ú�ģ���Ŀ¼�ṹ
    static public function buildAppDir($module) {
        // û�д����Ļ��Զ�����
        if(!is_dir(APP_PATH)) mkdir(APP_PATH,0755,true);
        if(is_writeable(APP_PATH)) {
            $dirs  = array(
                COMMON_PATH,
                COMMON_PATH.'Common/',
                CONF_PATH,
                APP_PATH.$module.'/',
                APP_PATH.$module.'/Common/',
                APP_PATH.$module.'/Controller/',
                APP_PATH.$module.'/Model/',
                APP_PATH.$module.'/Conf/',
                APP_PATH.$module.'/View/',
                RUNTIME_PATH,
                CACHE_PATH,
                LOG_PATH,
                TEMP_PATH,
                DATA_PATH,
                );
            foreach ($dirs as $dir){
                if(!is_dir($dir))  mkdir($dir,0755,true);
            }
            // д��Ŀ¼��ȫ�ļ�
            self::buildDirSecure($dirs);
            // д��Ӧ�������ļ�
            if(!is_file(CONF_PATH.'config.php'))
                file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'������'=>'����ֵ'\n);");
            // д��ģ�������ļ�
            if(!is_file(APP_PATH.$module.'/Conf/config.php'))
                file_put_contents(APP_PATH.$module.'/Conf/config.php',"<?php\nreturn array(\n\t//'������'=>'����ֵ'\n);");
            // ����ģ��Ĳ��Կ�����
            if(defined('BUILD_CONTROLLER_LIST')){
                // �Զ����ɵĿ������б�ע���Сд��
                $list = explode(',',BUILD_CONTROLLER_LIST);
                foreach($list as $controller){
                    self::buildController($module,$controller);
                }
            }else{
                // ����Ĭ�ϵĿ�����
                self::buildController($module);
            }
            // ����ģ���ģ��
            if(defined('BUILD_MODEL_LIST')){
                // �Զ����ɵĿ������б�ע���Сд��
                $list = explode(',',BUILD_MODEL_LIST);
                foreach($list as $model){
                    self::buildModel($module,$model);
                }
            }            
        }else{
            header('Content-Type:text/html; charset=utf-8');
            exit('Ӧ��Ŀ¼['.APP_PATH.']����д��Ŀ¼�޷��Զ����ɣ�<BR>���ֶ�������ĿĿ¼~');
        }
    }

    // ��黺��Ŀ¼(Runtime) ������������Զ�����
    static public function buildRuntime() {
        if(!is_dir(RUNTIME_PATH)) {
            mkdir(RUNTIME_PATH);
        }elseif(!is_writeable(RUNTIME_PATH)) {
            header('Content-Type:text/html; charset=utf-8');
            exit('Ŀ¼ [ '.RUNTIME_PATH.' ] ����д��');
        }
        mkdir(CACHE_PATH);  // ģ�建��Ŀ¼
        if(!is_dir(LOG_PATH))   mkdir(LOG_PATH);    // ��־Ŀ¼
        if(!is_dir(TEMP_PATH))  mkdir(TEMP_PATH);   // ���ݻ���Ŀ¼
        if(!is_dir(DATA_PATH))  mkdir(DATA_PATH);   // �����ļ�Ŀ¼
        return true;
    }

    // ������������
    static public function buildController($module,$controller='Index') {
        $file   =   APP_PATH.$module.'/Controller/'.$controller.'Controller'.EXT;
        if(!is_file($file)){
            $content = str_replace(array('[MODULE]','[CONTROLLER]'),array($module,$controller),self::$controller);
            if(!C('APP_USE_NAMESPACE')){
                $content    =   preg_replace('/namespace\s(.*?);/','',$content,1);
            }
            file_put_contents($file,$content);
        }
    }

    // ����ģ����
    static public function buildModel($module,$model) {
        $file   =   APP_PATH.$module.'/Model/'.$model.'Model'.EXT;
        if(!is_file($file)){
            $content = str_replace(array('[MODULE]','[MODEL]'),array($module,$model),self::$model);
            if(!C('APP_USE_NAMESPACE')){
                $content    =   preg_replace('/namespace\s(.*?);/','',$content,1);
            }
            file_put_contents($file,$content);
        }
    }

    // ����Ŀ¼��ȫ�ļ�
    static public function buildDirSecure($dirs=array()) {
        // Ŀ¼��ȫд�루Ĭ�Ͽ�����
        defined('BUILD_DIR_SECURE')  or define('BUILD_DIR_SECURE',    true);
        if(BUILD_DIR_SECURE) {
            defined('DIR_SECURE_FILENAME')  or define('DIR_SECURE_FILENAME',    'index.html');
            defined('DIR_SECURE_CONTENT')   or define('DIR_SECURE_CONTENT',     ' ');
            // �Զ�д��Ŀ¼��ȫ�ļ�
            $content = DIR_SECURE_CONTENT;
            $files = explode(',', DIR_SECURE_FILENAME);
            foreach ($files as $filename){
                foreach ($dirs as $dir)
                    file_put_contents($dir.$filename,$content);
            }
        }
    }
}
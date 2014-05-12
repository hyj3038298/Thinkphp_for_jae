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
namespace Behavior;
use Think\Storage;
/**
 * ϵͳ��Ϊ��չ����̬�����ȡ
 */
class ReadHtmlCacheBehavior {
    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        // ������̬����
        if(IS_GET && C('HTML_CACHE_ON'))  {
            $cacheTime = $this->requireHtmlCache();
            if( false !== $cacheTime && $this->checkHTMLCache(HTML_FILE_NAME,$cacheTime)) { //��̬ҳ����Ч
                // ��ȡ��̬ҳ�����
                echo Storage::read(HTML_FILE_NAME,'html');
                exit();
            }
        }
    }

    // �ж��Ƿ���Ҫ��̬����
    static private function requireHtmlCache() {
        // ������ǰ�ľ�̬����
         $htmls = C('HTML_CACHE_RULES'); // ��ȡ��̬����
         if(!empty($htmls)) {
            $htmls = array_change_key_case($htmls);
            // ��̬�����ļ������ʽ actionName=>array('��̬����','����ʱ��','���ӹ���')
            // 'read'=>array('{id},{name}',60,'md5') ���뱣֤��̬�����Ψһ�� �� ���ж���
            // ��⾲̬����
            $controllerName = strtolower(CONTROLLER_NAME);
            $actionName     = strtolower(ACTION_NAME);
            if(isset($htmls[$controllerName.':'.$actionName])) {
                $html   =   $htmls[$controllerName.':'.$actionName];   // ĳ���������Ĳ����ľ�̬����
            }elseif(isset($htmls[$controllerName.':'])){// ĳ���������ľ�̬����
                $html   =   $htmls[$controllerName.':'];
            }elseif(isset($htmls[$actionName])){
                $html   =   $htmls[$actionName]; // ���в����ľ�̬����
            }elseif(isset($htmls['*'])){
                $html   =   $htmls['*']; // ȫ�־�̬����
            }
            if(!empty($html)) {
                // �����̬����
                $rule   = is_array($html)?$html[0]:$html;
                // ��$_��ͷ��ϵͳ����
                $callback = function($match){ 
                    switch($match[1]){
                        case '_GET':        $var = $_GET[$match[2]]; break;
                        case '_POST':       $var = $_POST[$match[2]]; break;
                        case '_REQUEST':    $var = $_REQUEST[$match[2]]; break;
                        case '_SERVER':     $var = $_SERVER[$match[2]]; break;
                        case '_SESSION':    $var = $_SESSION[$match[2]]; break;
                        case '_COOKIE':     $var = $_COOKIE[$match[2]]; break;
                    }
                    return (count($match) == 4) ? $match[3]($var) : $var;
                };
                $rule     = preg_replace_callback('/{\$(_\w+)\.(\w+)(?:\|(\w+))?}/', $callback, $rule);
                // {ID|FUN} GET�����ļ�д
                $rule     = preg_replace_callback('/{(\w+)\|(\w+)}/', function($match){return $match[2]($_GET[$match[1]]);}, $rule);
                $rule     = preg_replace_callback('/{(\w+)}/', function($match){return $_GET[$match[1]];}, $rule);
                // ����ϵͳ����
                $rule   = str_ireplace(
                    array('{:controller}','{:action}','{:module}'),
                    array(CONTROLLER_NAME,ACTION_NAME,MODULE_NAME),
                    $rule);
                // {|FUN} ����ʹ�ú���
                $rule  = preg_replace_callback('/{|(\w+)}/', function($match){return $match[1]();},$rule);
                $cacheTime  =   C('HTML_CACHE_TIME',null,60);
                if(is_array($html)){
                    if(!empty($html[2])) $rule    =   $html[2]($rule); // Ӧ�ø��Ӻ���
                    $cacheTime  =   isset($html[1])?$html[1]:$cacheTime; // ������Ч��
                }else{
                    $cacheTime  =   $cacheTime;
                }
                
                // ��ǰ�����ļ�
                define('HTML_FILE_NAME',HTML_PATH . $rule.C('HTML_FILE_SUFFIX',null,'.html'));
                return $cacheTime;
            }
        }
        // ���軺��
        return false;
    }

    /**
     * ��龲̬HTML�ļ��Ƿ���Ч
     * �����Ч��Ҫ���¸���
     * @access public
     * @param string $cacheFile  ��̬�ļ���
     * @param integer $cacheTime  ������Ч��
     * @return boolean
     */
    static public function checkHTMLCache($cacheFile='',$cacheTime='') {
        if(!is_file($cacheFile)){
            return false;
        }elseif (filemtime(\Think\Think::instance('Think\View')->parseTemplate()) > Storage::get($cacheFile,'mtime','html')) {
            // ģ���ļ�������¾�̬�ļ���Ҫ����
            return false;
        }elseif(!is_numeric($cacheTime) && function_exists($cacheTime)){
            return $cacheTime($cacheFile);
        }elseif ($cacheTime != 0 && NOW_TIME > Storage::get($cacheFile,'mtime','html')+$cacheTime) {
            // �ļ��Ƿ�����Ч��
            return false;
        }
        //��̬�ļ���Ч
        return true;
    }

}
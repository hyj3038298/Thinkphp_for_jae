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
 * Think ϵͳ������
 */

/**
 * ��ȡ���������ò��� ֧����������
 * @param string|array $name ���ñ���
 * @param mixed $value ����ֵ
 * @param mixed $default Ĭ��ֵ
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config = array();
    // �޲���ʱ��ȡ����
    if (empty($name)) {
        return $_config;
    }
    // ����ִ�����û�ȡ��ֵ
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return;
        }
        // ��ά�������úͻ�ȡ֧��
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // ��������
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return;
    }
    return null; // ����Ƿ�����
}

/**
 * ���������ļ� ֧�ָ�ʽת�� ��֧��һ������
 * @param string $file �����ļ���
 * @param string $parse ���ý������� ��Щ��ʽ��Ҫ�û��Լ�����
 * @return void
 */
function load_config($file,$parse=CONF_PARSE){
    $ext  = pathinfo($file,PATHINFO_EXTENSION);
    switch($ext){
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml': 
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if(function_exists($parse)){
                return $parse($file);
            }else{
                E(L('_NOT_SUPPERT_').':'.$ext);
            }
    }
}

/**
 * �׳��쳣����
 * @param string $msg �쳣��Ϣ
 * @param integer $code �쳣���� Ĭ��Ϊ0
 * @return void
 */
function E($msg, $code=0) {
    throw new Think\Exception($msg, $code);
}

/**
 * ��¼��ͳ��ʱ�䣨΢�룩���ڴ�ʹ�����
 * ʹ�÷���:
 * <code>
 * G('begin'); // ��¼��ʼ���λ
 * // ... �������д���
 * G('end'); // ��¼������ǩλ
 * echo G('begin','end',6); // ͳ����������ʱ�� ��ȷ��С����6λ
 * echo G('begin','end','m'); // ͳ�������ڴ�ʹ�����
 * ���end���λû�ж��壬����Զ��Ե�ǰ��Ϊ���λ
 * ����ͳ���ڴ�ʹ����Ҫ MEMORY_LIMIT_ON ����Ϊtrue����Ч
 * </code>
 * @param string $start ��ʼ��ǩ
 * @param string $end ������ǩ
 * @param integer|string $dec С��λ����m
 * @return mixed
 */
function G($start,$end='',$dec=4) {
    static $_info       =   array();
    static $_mem        =   array();
    if(is_float($end)) { // ��¼ʱ��
        $_info[$start]  =   $end;
    }elseif(!empty($end)){ // ͳ��ʱ����ڴ�ʹ��
        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
        if(MEMORY_LIMIT_ON && $dec=='m'){
            if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
            return number_format(($_mem[$end]-$_mem[$start])/1024);
        }else{
            return number_format(($_info[$end]-$_info[$start]),$dec);
        }

    }else{ // ��¼ʱ����ڴ�ʹ��
        $_info[$start]  =  microtime(TRUE);
        if(MEMORY_LIMIT_ON) $_mem[$start]           =  memory_get_usage();
    }
}

/**
 * ��ȡ���������Զ���(�����ִ�Сд)
 * @param string|array $name ���Ա���
 * @param mixed $value ����ֵ���߱���
 * @return mixed
 */
function L($name=null, $value=null) {
    static $_lang = array();
    // �ղ����������ж���
    if (empty($name))
        return $_lang;
    // �ж����Ի�ȡ(������)
    // ��������,ֱ�ӷ���ȫ��д$name
    if (is_string($name)) {
        $name   =   strtoupper($name);
        if (is_null($value)){
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        }elseif(is_array($value)){
            // ֧�ֱ���
            $replace = array_keys($value);
            foreach($replace as &$v){
                $v = '{$'.$v.'}';
            }
            return str_replace($replace,$value,isset($_lang[$name]) ? $_lang[$name] : $name);        
        }
        $_lang[$name] = $value; // ���Զ���
        return;
    }
    // ��������
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return;
}

/**
 * ��Ӻͻ�ȡҳ��Trace��¼
 * @param string $value ����
 * @param string $label ��ǩ
 * @param string $level ��־����
 * @param boolean $record �Ƿ��¼��־
 * @return void
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
    return Think\Think::trace($value,$label,$level,$record);
}


/**
����JAE
*/
function php_strip_whitespace($filename){
    return file_get_contents($filename);
}
/**
 * �����ļ�
 * @param string $filename �ļ���
 * @return string
 */
function compile($filename) {
    $content    =   php_strip_whitespace($filename);
    $content    =   trim(substr($content, 5));
    // �滻Ԥ����ָ��
    $content    =   preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
    if(0===strpos($content,'namespace')){
        $content    =   preg_replace('/namespace\s(.*?);/','namespace \\1{',$content,1);
    }else{
        $content    =   'namespace {'.$content;
    }
    if ('?>' == substr($content, -2))
        $content    = substr($content, 0, -2);
    return $content.'}';
}

/**
 * ��ȡģ���ļ� ��ʽ ��Դ://ģ��@����/������/����
 * @param string $name ģ����Դ��ַ
 * @param string $layer ��ͼ�㣨Ŀ¼������
 * @return string
 */
function T($template='',$layer=''){

    // ����ģ����Դ��ַ
    if(false === strpos($template,'://')){
        $template   =   'http://'.str_replace(':', '/',$template);
    }
    $info   =   parse_url($template);
    $file   =   $info['host'].(isset($info['path'])?$info['path']:'');
    $module =   isset($info['user'])?$info['user'].'/':MODULE_NAME.'/';
    $extend =   $info['scheme'];
    $layer  =   $layer?$layer:C('DEFAULT_V_LAYER');

    // ��ȡ��ǰ�����ģ��·��
    $auto   =   C('AUTOLOAD_NAMESPACE');
    if($auto && isset($auto[$extend])){ // ��չ��Դ
        $baseUrl    =   $auto[$extend].$module.$layer.'/';
    }elseif(C('VIEW_PATH')){ // ָ����ͼĿ¼
        $baseUrl    =   C('VIEW_PATH');
    }else{
        $baseUrl    =   APP_PATH.$module.$layer.'/';
    }

    // ��ȡ����
    $theme  =   substr_count($file,'/')<2 ? C('DEFAULT_THEME') : '';

    // ����ģ���ļ�����
    $depr   =   C('TMPL_FILE_DEPR');
    if('' == $file) {
        // ���ģ���ļ���Ϊ�� ����Ĭ�Ϲ���λ
        $file = CONTROLLER_NAME . $depr . ACTION_NAME;
    }elseif(false === strpos($file, '/')){
        $file = CONTROLLER_NAME . $depr . $file;
    }elseif('/' != $depr){
        $file   =   substr_count($file,'/')>1 ? substr_replace($file,$depr,strrpos($file,'/'),1) : str_replace('/', $depr, $file);
    }
    return $baseUrl.($theme?$theme.'/':'').$file.C('TMPL_TEMPLATE_SUFFIX');
}

/**
 * ��ȡ������� ֧�ֹ��˺�Ĭ��ֵ
 * ʹ�÷���:
 * <code>
 * I('id',0); ��ȡid���� �Զ��ж�get����post
 * I('post.name','','htmlspecialchars'); ��ȡ$_POST['name']
 * I('get.'); ��ȡ$_GET
 * </code>
 * @param string $name ���������� ֧��ָ������
 * @param mixed $default �����ڵ�ʱ��Ĭ��ֵ
 * @param mixed $filter �������˷���
 * @param mixed $datas Ҫ��ȡ�Ķ�������Դ
 * @return mixed
 */
function I($name,$default='',$filter=null,$datas=null) {
    if(strpos($name,'.')) { // ָ��������Դ
        list($method,$name) =   explode('.',$name,2);
    }else{ // Ĭ��Ϊ�Զ��ж�
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'path'    :   
            $input  =   array();
            if(!empty($_SERVER['PATH_INFO'])){
                $depr   =   C('URL_PATHINFO_DEPR');
                $input  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));            
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        case 'data'    :   $input =& $datas;      break;
        default:
            return NULL;
    }
    if(empty($name)) { // ��ȡȫ������
        $data       =   $input;
        array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // ��������
            }
        }
    }elseif(isset($input[$name])) { // ȡֵ����
        $data       =   $input[$name];
        is_array($data) && array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // ��������
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // ����Ĭ��ֵ
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
}

function array_map_recursive($filter, $data) {
     $result = array();
     foreach ($data as $key => $val) {
         $result[$key] = is_array($val)
             ? array_map_recursive($filter, $val)
             : call_user_func($filter, $val);
     }
     return $result;
 }

/**
 * ���úͻ�ȡͳ������
 * ʹ�÷���:
 * <code>
 * N('db',1); // ��¼���ݿ��������
 * N('read',1); // ��¼��ȡ����
 * echo N('db'); // ��ȡ��ǰҳ�����ݿ�����в�������
 * echo N('read'); // ��ȡ��ǰҳ���ȡ����
 * </code>
 * @param string $key ��ʶλ��
 * @param integer $step ����ֵ
 * @return mixed
 */
function N($key, $step=0,$save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
    if(false !== $save){ // ������
        S('N_'.$key,$_num[$key],$save);
    }
}

/**
 * �ַ����������ת��
 * type 0 ��Java���ת��ΪC�ķ�� 1 ��C���ת��ΪJava�ķ��
 * @param string $name �ַ���
 * @param integer $type ת������
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * �Ż���require_once
 * @param string $filename �ļ���ַ
 * @return boolean
 */
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

/**
 * ���ִ�Сд���ļ������ж�
 * @param string $filename �ļ���ַ
 * @return boolean
 */
function file_exists_case($filename) {
    if (is_file($filename)) {
        if (IS_WIN && APP_DEBUG) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 * ������������ ͬjava��Import �������л��湦��
 * @param string $class ��������ռ��ַ���
 * @param string $baseUrl ��ʼ·��
 * @param string $ext ������ļ���չ��
 * @return boolean
 */
function import($class, $baseUrl = '', $ext=EXT) {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut     = explode('/', $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
            //���ص�ǰģ������
            $baseUrl = MODULE_PATH;
            $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
        }elseif (in_array($class_strut[0],array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$class_strut[0])) {
            // ϵͳ�����͵���������
            $baseUrl = LIB_PATH;
        }else { // ��������ģ������
            $baseUrl = APP_PATH;
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl    .= '/';
    $classfile       = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // ����಻���� ��������ļ�
        return require_cache($classfile);
    }
}

/**
 * ���������ռ䷽ʽ���뺯����
 * load('@.Util.Array')
 * @param string $name �����������ռ��ַ���
 * @param string $baseUrl ��ʼ·��
 * @param string $ext ������ļ���չ��
 * @return void
 */
function load($name, $baseUrl='', $ext='.php') {
    $name = str_replace(array('.', '#'), array('/', '.'), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) {//���ص�ǰģ�麯����
            $baseUrl    =   MODULE_PATH.'Common/';
            $name       =   substr($name, 2);
        } else { //��������ģ�麯����
            $array      =   explode('/', $name);
            $baseUrl    =   APP_PATH . array_shift($array).'/Common/';
            $name       =   implode('/',$array);
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl       .= '/';
    require_cache($baseUrl . $name . $ext);
}

/**
 * ���ٵ�������������� ���е�������ܵ�����ļ�ͳһ�ŵ� ϵͳ��VendorĿ¼����
 * @param string $class ���
 * @param string $baseUrl ����Ŀ¼
 * @param string $ext ����׺
 * @return boolean
 */
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}

/**
 * ʵ����ģ���� ��ʽ [��Դ://][ģ��/]ģ��
 * @param string $name ��Դ��ַ
 * @param string $layer ģ�Ͳ�����
 * @return Model
 */
function D($name='',$layer='') {
    if(empty($name)) return new Think\Model;
    static $_model  =   array();
    $layer          =   $layer? : C('DEFAULT_M_LAYER');
    if(isset($_model[$name.$layer]))
        return $_model[$name.$layer];
    $class          =   parse_res_name($name,$layer);
    if(class_exists($class)) {
        $model      =   new $class(basename($name));
    }elseif(false === strpos($name,'/')){
        // �Զ����ع���ģ�������ģ��
        if(!C('APP_USE_NAMESPACE')){
            import('Common/'.$layer.'/'.$class);
        }else{
            $class      =   '\\Common\\'.$layer.'\\'.$name.$layer;
        }
        $model      =   class_exists($class)? new $class($name) : new Think\Model($name);
    }else {
        Think\Log::record('D����ʵ����û�ҵ�ģ����'.$class,Think\Log::NOTICE);
        $model      =   new Think\Model(basename($name));
    }
    $_model[$name.$layer]  =  $model;
    return $model;
}

/**
 * ʵ����һ��û��ģ���ļ���Model
 * @param string $name Model���� ֧��ָ������ģ�� ���� MongoModel:User
 * @param string $tablePrefix ��ǰ׺
 * @param mixed $connection ���ݿ�������Ϣ
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'Think\\Model';
    }
    $guid           =   (is_array($connection)?implode('',$connection):$connection).$tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}

/**
 * ������Դ��ַ����������ļ�
 * ���� module/controller addon://module/behavior
 * @param string $name ��Դ��ַ ��ʽ��[��չ://][ģ��/]��Դ��
 * @param string $layer �ֲ�����
 * @return string
 */
function parse_res_name($name,$layer,$level=1){
    if(strpos($name,'://')) {// ָ����չ��Դ
        list($extend,$name)  =   explode('://',$name);
    }else{
        $extend  =   '';
    }
    if(strpos($name,'/') && substr_count($name, '/')>=$level){ // ָ��ģ��
        list($module,$name) =  explode('/',$name,2);
    }else{
        $module =   MODULE_NAME;
    }
    $array  =   explode('/',$name);
    if(!C('APP_USE_NAMESPACE')){
        $class  =   parse_name($name, 1);
        import($module.'/'.$layer.'/'.$class.$layer);
    }else{
        $class  =   $module.'\\'.$layer;
        foreach($array as $name){
            $class  .=   '\\'.parse_name($name, 1);
        }
        // ������Դ���
        if($extend){ // ��չ��Դ
            $class      =   $extend.'\\'.$class;
        }
    }
    return $class.$layer;
}

/**
 * ����ʵ�������ʿ�����
 * @param string $name ��������
 * @param string $path �����������ռ䣨·����
 * @return Controller|false
 */
function controller($name,$path=''){
    $layer  =   C('DEFAULT_C_LAYER');
    if(!C('APP_USE_NAMESPACE')){
        $class  =   parse_name($name, 1);
        import(MODULE_NAME.'/'.$layer.'/'.$class.$layer);
    }else{
        $class  =   MODULE_NAME.'\\'.($path?$path.'\\':'').$layer;
        $array  =   explode('/',$name);
        foreach($array as $name){
            $class  .=   '\\'.parse_name($name, 1);
        }
        $class .=   $layer;
    }
    if(class_exists($class)) {
        return new $class();
    }else {
        return false;
    }
}

/**
 * ʵ������������ ��ʽ��[��Դ://][ģ��/]������
 * @param string $name ��Դ��ַ
 * @param string $layer ���Ʋ�����
 * @param integer $level ���������
 * @return Controller|false
 */
function A($name,$layer='',$level='') {
    static $_action = array();
    $layer  =   $layer? : C('DEFAULT_C_LAYER');
    $level  =   $level? : ($layer == C('DEFAULT_C_LAYER')?C('CONTROLLER_LEVEL'):1);
    if(isset($_action[$name.$layer]))
        return $_action[$name.$layer];
    
    $class  =   parse_res_name($name,$layer,$level);
    if(class_exists($class)) {
        $action             =   new $class();
        $_action[$name.$layer]     =   $action;
        return $action;
    }else {
        return false;
    }
}

/**
 * Զ�̵��ÿ������Ĳ������� URL ������ʽ [��Դ://][ģ��/]������/����
 * @param string $url ���õ�ַ
 * @param string|array $vars ���ò��� ֧���ַ���������
 * @param string $layer Ҫ���õĿ��Ʋ�����
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {
    $info   =   pathinfo($url);
    $action =   $info['basename'];
    $module =   $info['dirname'];
    $class  =   A($module,$layer);
    if($class){
        if(is_string($vars)) {
            parse_str($vars,$vars);
        }
        return call_user_func_array(array(&$class,$action.C('ACTION_SUFFIX')),$vars);
    }else{
        return false;
    }
}

/**
 * �����ǩ��չ
 * @param string $tag ��ǩ����
 * @param mixed $params �������
 * @return mixed
 */
function tag($tag, &$params=NULL) {
    return \Think\Hook::listen($tag,$params);
}

/**
 * ִ��ĳ����Ϊ
 * @param string $name ��Ϊ����
 * @param string $tag ��ǩ���ƣ���Ϊ�����贫�룩 
 * @param Mixed $params ����Ĳ���
 * @return void
 */
function B($name, $tag='',&$params=NULL) {
    if(''==$tag){
        $name   .=  'Behavior';
    }
    return \Think\Hook::exec($name,$tag,$params);
}

/**
 * ȥ�������еĿհ׺�ע��
 * @param string $content ��������
 * @return string
 */
function strip_whitespace($content) {
    $stripStr   = '';
    //����phpԴ��
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //���˸���PHPע��
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //���˿ո�
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * �Զ����쳣����
 * @param string $msg �쳣��Ϣ
 * @param string $type �쳣���� Ĭ��ΪThink\Exception
 * @param integer $code �쳣���� Ĭ��Ϊ0
 * @return void
 */
function throw_exception($msg, $type='Think\\Exception', $code=0) {
    Think\Log::record('����ʹ��E�������throw_exception',Think\Log::NOTICE);
    if (class_exists($type, false))
        throw new $type($msg, $code);
    else
        Think\Think::halt($msg);        // �쳣���Ͳ����������������Ϣ�ִ�
}

/**
 * ������Ѻõı������
 * @param mixed $var ����
 * @param boolean $echo �Ƿ���� Ĭ��ΪTrue ���Ϊfalse �򷵻�����ַ���
 * @param string $label ��ǩ Ĭ��Ϊ��
 * @param boolean $strict �Ƿ��Ͻ� Ĭ��Ϊtrue
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

/**
 * ���õ�ǰҳ��Ĳ���
 * @param string|false $layout �������� Ϊfalse��ʱ���ʾ�رղ���
 * @return void
 */
function layout($layout) {
    if(false !== $layout) {
        // ��������
        C('LAYOUT_ON',true);
        if(is_string($layout)) { // �����µĲ���ģ��
            C('LAYOUT_NAME',$layout);
        }
    }else{// ��ʱ�رղ���
        C('LAYOUT_ON',false);
    }
}

/**
 * URL��װ ֧�ֲ�ͬURLģʽ
 * @param string $url URL���ʽ����ʽ��'[ģ��/������/����#ê��@����]?����1=ֵ1&����2=ֵ2...'
 * @param string|array $vars ����Ĳ�����֧��������ַ���
 * @param string $suffix α��̬��׺��Ĭ��Ϊtrue��ʾ��ȡ����ֵ
 * @param boolean $domain �Ƿ���ʾ����
 * @return string
 */
function U($url='',$vars='',$suffix=true,$domain=false) {
    // ����URL
    $info   =  parse_url($url);
    $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
    if(isset($info['fragment'])) { // ����ê��
        $anchor =   $info['fragment'];
        if(false !== strpos($anchor,'?')) { // ��������
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }        
        if(false !== strpos($anchor,'@')) { // ��������
            list($anchor,$host)    =   explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) { // ��������
        list($url,$host)    =   explode('@',$info['path'], 2);
    }
    // ����������
    if(isset($host)) {
        $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
    }elseif($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // ��������������
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '������'=>array('ģ��[/������]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule   =   is_array($rule)?$rule[0]:$rule;
                if(false === strpos($key,'*') && 0=== strpos($url,$rule)) {
                    $domain = $key.strstr($domain,'.'); // ���ɶ�Ӧ������
                    $url    =  substr_replace($url,'',0,strlen($rule));
                    break;
                }
            }
        }
    }

    // ��������
    if(is_string($vars)) { // aaa=1&bbb=2 ת��������
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // ������ַ������� �ϲ���vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }
    
    // URL��װ
    $depr       =   C('URL_PATHINFO_DEPR');
    $urlCase    =   C('URL_CASE_INSENSITIVE');
    if($url) {
        if(0=== strpos($url,'/')) {// ����·��
            $route      =   true;
            $url        =   substr($url,1);
            if('/' != $depr) {
                $url    =   str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // ��ȫ�滻
                $url    =   str_replace('/',$depr,$url);
            }
            // ����ģ�顢�������Ͳ���
            $url        =   trim($url,$depr);
            $path       =   explode($depr,$url);
            $var        =   array();
            $varModule      =   C('VAR_MODULE');
            $varController  =   C('VAR_CONTROLLER');
            $varAction      =   C('VAR_ACTION');
            $var[$varAction]       =   !empty($path)?array_pop($path):ACTION_NAME;
            $var[$varController]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
            if($maps = C('URL_ACTION_MAP')) {
                if(isset($maps[strtolower($var[$varController])])) {
                    $maps    =   $maps[strtolower($var[$varController])];
                    if($action = array_search(strtolower($var[$varAction]),$maps)){
                        $var[$varAction] = $action;
                    }
                }
            }
            if($maps = C('URL_CONTROLLER_MAP')) {
                if($controller = array_search(strtolower($var[$varController]),$maps)){
                    $var[$varController] = $controller;
                }
            }
            if($urlCase) {
                $var[$varController]   =   parse_name($var[$varController]);
            }
            $module =   '';
            
            if(!empty($path)) {
                $var[$varModule]    =   array_pop($path);
            }else{
                if(C('MULTI_MODULE')) {
                    if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
                        $var[$varModule]=   MODULE_NAME;
                    }
                }
            }
            if($maps = C('URL_MODULE_MAP')) {
                if($_module = array_search(strtolower($var[$varModule]),$maps)){
                    $var[$varModule] = $_module;
                }
            }
            if(isset($var[$varModule])){
                $module =   $var[$varModule];
                unset($var[$varModule]);
            }
            
        }
    }

    if(C('URL_MODEL') == 0) { // ��ͨģʽURLת��
        $url        =   __APP__.'?'.C('VAR_MODULE')."={$module}&".http_build_query(array_reverse($var));
        if($urlCase){
            $url    =   strtolower($url);
        }        
        if(!empty($vars)) {
            $vars   =   http_build_query($vars);
            $url   .=   '&'.$vars;
        }
    }else{ // PATHINFOģʽ���߼���URLģʽ
        if(isset($route)) {
            $url    =   __APP__.'/'.rtrim($url,$depr);
        }else{
            $module =   defined('BIND_MODULE') ? '' : $module;
            $url    =   __APP__.'/'.($module?$module.MODULE_PATHINFO_DEPR:'').implode($depr,array_reverse($var));
        }
        if($urlCase){
            $url    =   strtolower($url);
        }
        if(!empty($vars)) { // ��Ӳ���
            foreach ($vars as $var => $val){
                if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
            }                
        }
        if($suffix) {
            $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
            if($pos = strpos($suffix, '|')){
                $suffix = substr($suffix, 0, $pos);
            }
            if($suffix && '/' != substr($url,-1)){
                $url  .=  '.'.ltrim($suffix,'.');
            }
        }
    }
    if(isset($anchor)){
        $url  .= '#'.$anchor;
    }
    if($domain) {
        $url   =  (is_ssl()?'https://':'http://').$domain.$url;
    }
    return $url;
}

/**
 * ��Ⱦ���Widget
 * @param string $name Widget����
 * @param array $data ����Ĳ���
 * @return void
 */
function W($name, $data=array()) {
    return R($name,$data,'Widget');
}

/**
 * �ж��Ƿ�SSLЭ��
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * URL�ض���
 * @param string $url �ض����URL��ַ
 * @param integer $time �ض���ĵȴ�ʱ�䣨�룩
 * @param string $msg �ض���ǰ����ʾ��Ϣ
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //����URL��ַ֧��
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "ϵͳ����{$time}��֮���Զ���ת��{$url}��";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * �������
 * @param mixed $name �������ƣ����Ϊ�����ʾ���л�������
 * @param mixed $value ����ֵ
 * @param mixed $options �������
 * @return mixed
 */
function S($name,$value='',$options=null) {
    static $cache   =   '';
    if(is_array($options) && empty($cache)){
        // ���������ͬʱ��ʼ��
        $type       =   isset($options['type'])?$options['type']:'';
        $cache      =   Think\Cache::getInstance($type,$options);
    }elseif(is_array($name)) { // �����ʼ��
        $type       =   isset($name['type'])?$name['type']:'';
        $cache      =   Think\Cache::getInstance($type,$name);
        return $cache;
    }elseif(empty($cache)) { // �Զ���ʼ��
        $cache      =   Think\Cache::getInstance();
    }
    if(''=== $value){ // ��ȡ����
        return $cache->get($name);
    }elseif(is_null($value)) { // ɾ������
        return $cache->rm($name);
    }else { // ��������
        if(is_array($options)) {
            $expire     =   isset($options['expire'])?$options['expire']:NULL;
        }else{
            $expire     =   is_numeric($options)?$options:NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * �����ļ����ݶ�ȡ�ͱ��� ��Լ��������� �ַ���������
 * @param string $name ��������
 * @param mixed $value ����ֵ
 * @param string $path ����·��
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH) {
    static $_cache  =   array();
    $filename       =   $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // ɾ������
            if(false !== strpos($name,'*')){
                return false; // TODO 
            }else{
                unset($_cache[$name]);
                return Think\Storage::unlink($filename,'F');
            }
        } else {
            Think\Storage::put($filename,serialize($value),'F');
            // ��������
            $_cache[$name]  =   $value;
            return ;
        }
    }
    // ��ȡ��������
    if (isset($_cache[$name]))
        return $_cache[$name];
    if (Think\Storage::has($filename,'F')){
        $value      =   unserialize(Think\Storage::read($filename,'F'));
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}

/**
 * ����PHP�������ͱ�������Ψһ��ʶ��
 * @param mixed $mix ����
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * XML����
 * @param mixed $data ����
 * @param string $root ���ڵ���
 * @param string $item �����������ӽڵ���
 * @param string $attr ���ڵ�����
 * @param string $id   ���������ӽڵ�keyת����������
 * @param string $encoding ���ݱ���
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr   = trim($attr);
    $attr   = empty($attr) ? '' : " {$attr}";
    $xml    = "<?xml version=\"1.0\" encoding=\"gb2312\"?>";
    $xml   .= "<{$root}{$attr}>";
    $xml   .= data_to_xml($data, $item, $id);
    $xml   .= "</{$root}>";
    return $xml;
}

/**
 * ����XML����
 * @param mixed  $data ����
 * @param string $item ��������ʱ�Ľڵ�����
 * @param string $id   ��������keyת��Ϊ��������
 * @return string
 */
function data_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}

/**
 * session������
 * @param string|array $name session���� ���Ϊ�������ʾ����session����
 * @param mixed $value sessionֵ
 * @return mixed
 */
function session($name='',$value='') {
    $prefix   =  C('SESSION_PREFIX');
    if(is_array($name)) { // session��ʼ�� ��session_start ֮ǰ����
        if(isset($name['prefix'])) C('SESSION_PREFIX',$name['prefix']);
        if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])){
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        }elseif(isset($name['id'])) {
            session_id($name['id']);
        }
        if('common' != APP_MODE){ // ����ģʽ���ܲ�֧��
            ini_set('session.auto_start', 0);
        }
        if(isset($name['name']))            session_name($name['name']);
        if(isset($name['path']))            session_save_path($name['path']);
        if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
        if(isset($name['expire']))          ini_set('session.gc_maxlifetime', $name['expire']);
        if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
        if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
        if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
        if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
        if(isset($name['type']))            C('SESSION_TYPE',$name['type']);
        if(C('SESSION_TYPE')) { // ��ȡsession����
            $type   =   C('SESSION_TYPE');
            $class  =   strpos($type,'\\')? $type : 'Think\\Session\\Driver\\'. ucwords(strtolower($type));
            $hander =   new $class();
            session_set_save_handler(
                array(&$hander,"open"), 
                array(&$hander,"close"), 
                array(&$hander,"read"), 
                array(&$hander,"write"), 
                array(&$hander,"destroy"), 
                array(&$hander,"gc")); 
        }
        // ����session
        if(C('SESSION_AUTO_START'))  session_start();
    }elseif('' === $value){ 
        if(''===$name){
            // ��ȡȫ����session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        }elseif(0===strpos($name,'[')) { // session ����
            if('[pause]'==$name){ // ��ͣsession
                session_write_close();
            }elseif('[start]'==$name){ // ����session
                session_start();
            }elseif('[destroy]'==$name){ // ����session
                $_SESSION =  array();
                session_unset();
                session_destroy();
            }elseif('[regenerate]'==$name){ // ��������id
                session_regenerate_id();
            }
        }elseif(0===strpos($name,'?')){ // ���session
            $name   =  substr($name,1);
            if(strpos($name,'.')){ // ֧������
                list($name1,$name2) =   explode('.',$name);
                return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
            }else{
                return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){ // ���session
            if($prefix) {
                unset($_SESSION[$prefix]);
            }else{
                $_SESSION = array();
            }
        }elseif($prefix){ // ��ȡsession
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;  
            }else{
                return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;                
            }            
        }else{
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;  
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }            
        }
    }elseif(is_null($value)){ // ɾ��session
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }else{ // ����session
        if($prefix){
            if (!isset($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }
}

/**
 * Cookie ���á���ȡ��ɾ��
 * @param string $name cookie����
 * @param mixed $value cookieֵ
 * @param mixed $options cookie����
 * @return mixed
 */
function cookie($name='', $value='', $option=null) {
    // Ĭ������
    $config = array(
        'prefix'    =>  C('COOKIE_PREFIX'), // cookie ����ǰ׺
        'expire'    =>  C('COOKIE_EXPIRE'), // cookie ����ʱ��
        'path'      =>  C('COOKIE_PATH'), // cookie ����·��
        'domain'    =>  C('COOKIE_DOMAIN'), // cookie ��Ч����
        'httponly'  =>  C('COOKIE_HTTPONLY'), // httponly����
    );
    // ��������(�Ḳ���a������)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    if(!empty($config['httponly'])){
        ini_set("session.cookie_httponly", 1);
    }
    // ���ָ��ǰ׺������cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // Ҫɾ����cookieǰ׺����ָ����ɾ��config���õ�ָ��ǰ׺
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// ���ǰ׺Ϊ���ַ�������������ֱ�ӷ���
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }elseif('' === $name){
        // ��ȡȫ����cookie
        return $_COOKIE;
    }
    $name = $config['prefix'] . str_replace('.', '_', $name);
    if ('' === $value) {
        if(isset($_COOKIE[$name])){
            $value =    $_COOKIE[$name];
            if(0===strpos($value,'think:')){
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
            }else{
                return $value;
            }
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // ɾ��ָ��cookie
        } else {
            // ����cookie
            if(is_array($value)){
                $value  = 'think:'.json_encode(array_map('urlencode',$value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

/**
 * ���ض�̬��չ�ļ�
 * @return void
 */
function load_ext_file($path) {
    // �����Զ����ⲿ�ļ�
    if($files = C('LOAD_EXT_FILE')) {
        $files      =  explode(',',$files);
        foreach ($files as $file){
            $file   = $path.'Common/'.$file.'.php';
            if(is_file($file)) include $file;
        }
    }
    // �����Զ���Ķ�̬�����ļ�
    if($configs = C('LOAD_EXT_CONFIG')) {
        if(is_string($configs)) $configs =  explode(',',$configs);
        foreach ($configs as $key=>$config){
            $file   = $path.'Conf/'.$config.CONF_EXT;
            if(is_file($file)) {
                is_numeric($key)?C(load_config($file)):C($key,load_config($file));
            }
        }
    }
}

/**
 * ��ȡ�ͻ���IP��ַ
 * @param integer $type �������� 0 ����IP��ַ 1 ����IPV4��ַ����
 * @param boolean $adv �Ƿ���и߼�ģʽ��ȡ���п��ܱ�αװ�� 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP��ַ�Ϸ���֤
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * ����HTTP״̬
 * @param integer $code ״̬��
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // ȷ��FastCGIģʽ������
        header('Status:'.$code.' '.$_status[$code]);
    }
}

// ���˱��еı��ʽ
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}

// �����ִ�Сд��in_arrayʵ��
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}

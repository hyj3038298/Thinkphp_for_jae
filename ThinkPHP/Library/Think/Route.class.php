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
 * ThinkPHP·�ɽ�����
 */
class Route {
    
    // ·�ɼ��
    public static function check(){
        $depr   =   C('URL_PATHINFO_DEPR');
        $regx   =   preg_replace('/\.'.__EXT__.'$/i','',trim($_SERVER['PATH_INFO'],$depr));
        // �ָ����滻 ȷ��·�ɶ���ʹ��ͳһ�ķָ���
        if('/' != $depr){
            $regx = str_replace($depr,'/',$regx);
        }
        // URLӳ�䶨�壨��̬·�ɣ�
        $maps   =   C('URL_MAP_RULES');
        if(isset($maps[$regx])) {
            $var    =   self::parseUrl($maps[$regx]);
            $_GET   =   array_merge($var, $_GET);
            return true;                
        }        
        // ��̬·�ɴ���
        $routes =   C('URL_ROUTE_RULES');
        if(!empty($routes)) {
            foreach ($routes as $rule=>$route){
                if(is_numeric($rule)){
                    // ֧�� array('rule','adddress',...) ����·��
                    $rule   =   array_shift($route);
                }
                if(is_array($route) && isset($route[2])){
                    // ·�ɲ���
                    $options    =   $route[2];
                    if(isset($options['ext']) && __EXT__ != $options['ext']){
                        // URL��׺���
                        continue;
                    }
                    if(isset($options['method']) && REQUEST_METHOD != $options['method']){
                        // �������ͼ��
                        continue;
                    }
                    // �Զ�����
                    if(!empty($options['callback']) && is_callable($options['callback'])) {
                        if(false === call_user_func($options['callback'])) {
                            continue;
                        }
                    }                    
                }
                if(0===strpos($rule,'/') && preg_match($rule,$regx,$matches)) { // ����·��
                    if($route instanceof \Closure) {
                        // ִ�бհ�
                        $result = self::invokeRegx($route, $matches);
                        // ������ز���ֵ �����ִ��
                        return is_bool($result) ? $result : exit;
                    }else{
                        return self::parseRegex($matches,$route,$regx);
                    }
                }else{ // ����·��
                    $len1   =   substr_count($regx,'/');
                    $len2   =   substr_count($rule,'/');
                    if($len1>=$len2 || strpos($rule,'[')) {
                        if('$' == substr($rule,-1,1)) {// ����ƥ��
                            if($len1 != $len2) {
                                continue;
                            }else{
                                $rule =  substr($rule,0,-1);
                            }
                        }
                        $match  =  self::checkUrlMatch($regx,$rule);
                        if(false !== $match)  {
                            if($route instanceof \Closure) {
                                // ִ�бհ�
                                $result = self::invokeRule($route, $match);
                                // ������ز���ֵ �����ִ��
                                return is_bool($result) ? $result : exit;
                            }else{
                                return self::parseRule($rule,$route,$regx);
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    // ���URL�͹���·���Ƿ�ƥ��
    private static function checkUrlMatch($regx,$rule) {
        $m1 = explode('/',$regx);
        $m2 = explode('/',$rule);
        $var = array();         
        foreach ($m2 as $key=>$val){
            if(0 === strpos($val,'[:')){
                $val    =   substr($val,1,-1);
            }
                
            if(':' == substr($val,0,1)) {// ��̬����
                if($pos = strpos($val,'|')){
                    // ʹ�ú�������
                    $val   =   substr($val,1,$pos-1);
                }
                if(strpos($val,'\\')) {
                    $type = substr($val,-1);
                    if('d'==$type) {
                        if(isset($m1[$key]) && !is_numeric($m1[$key]))
                            return false;
                    }
                    $name = substr($val, 1, -2);
                }elseif($pos = strpos($val,'^')){
                    $array   =  explode('-',substr(strstr($val,'^'),1));
                    if(in_array($m1[$key],$array)) {
                        return false;
                    }
                    $name = substr($val, 1, $pos - 1);
                }else{
                    $name = substr($val, 1);
                }
                $var[$name] = isset($m1[$key])?$m1[$key]:'';
            }elseif(0 !== strcasecmp($val,$m1[$key])){
                return false;
            }
        }
        // �ɹ�ƥ��󷵻�URL�еĶ�̬��������
        return $var;
    }

    // �����淶��·�ɵ�ַ
    // ��ַ��ʽ [������/����?]����1=ֵ1&����2=ֵ2...
    private static function parseUrl($url) {
        $var  =  array();
        if(false !== strpos($url,'?')) { // [������/����?]����1=ֵ1&����2=ֵ2...
            $info   =  parse_url($url);
            $path   = explode('/',$info['path']);
            parse_str($info['query'],$var);
        }elseif(strpos($url,'/')){ // [������/����]
            $path = explode('/',$url);
        }else{ // ����1=ֵ1&����2=ֵ2...
            parse_str($url,$var);
        }
        if(isset($path)) {
            $var[C('VAR_ACTION')] = array_pop($path);
            if(!empty($path)) {
                $var[C('VAR_CONTROLLER')] = array_pop($path);
            }
            if(!empty($path)) {
                $var[C('VAR_MODULE')]  = array_pop($path);
            }
        }
        return $var;
    }

    // ��������·��
    // '·�ɹ���'=>'[������/����]?�������1=ֵ1&�������2=ֵ2...'
    // '·�ɹ���'=>array('[������/����]','�������1=ֵ1&�������2=ֵ2...')
    // '·�ɹ���'=>'�ⲿ��ַ'
    // '·�ɹ���'=>array('�ⲿ��ַ','�ض������')
    // ·�ɹ����� :��ͷ ��ʾ��̬����
    // �ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // 'news/:month/:day/:id'=>array('News/read?cate=1','status=1'),
    // 'new/:id'=>array('/new.php?id=:1',301), �ض���
    private static function parseRule($rule,$route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  is_array($route)?$route[0]:$route;
        // ��ȡURL��ַ�еĲ���
        $paths = explode('/',$regx);
        // ����·�ɹ���
        $matches  =  array();
        $rule =  explode('/',$rule);
        foreach ($rule as $item){
            $fun    =   '';
            if(0 === strpos($item,'[:')){
                $item   =   substr($item,1,-1);
            }
            if(0===strpos($item,':')) { // ��̬������ȡ
                if($pos = strpos($item,'|')){ 
                    // ֧�ֺ�������
                    $fun  =  substr($item,$pos+1);
                    $item =  substr($item,0,$pos);                    
                }
                if($pos = strpos($item,'^') ) {
                    $var  =  substr($item,1,$pos-1);
                }elseif(strpos($item,'\\')){
                    $var  =  substr($item,1,-2);
                }else{
                    $var  =  substr($item,1);
                }
                $matches[$var] = !empty($fun)? $fun(array_shift($paths)) : array_shift($paths);
            }else{ // ����URL�еľ�̬����
                array_shift($paths);
            }
        }

        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            if(strpos($url,':')) { // ���ݶ�̬����
                $values = array_values($matches);
                $url = preg_replace_callback('/:(\d+)/', function($match) use($values){ return $values[$match[1] - 1]; }, $url);
            }
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  self::parseUrl($url);
            // ����·�ɵ�ַ����Ķ�̬����
            $values  =  array_values($matches);
            foreach ($var as $key=>$val){
                if(0===strpos($val,':')) {
                    $var[$key] =  $values[substr($val,1)-1];
                }
            }
            $var   =   array_merge($matches,$var);
            // ����ʣ���URL����
            if(!empty($paths)) {
                preg_replace_callback('/(\w+)\/([^\/]+)/', function($match) use(&$var){ $var[strtolower($match[1])]=strip_tags($match[2]);}, implode('/',$paths));
            }
            // ����·���Զ��������
            if(is_array($route) && isset($route[1])) {
                if(is_array($route[1])){
                    $params     =   $route[1];
                }else{
                    parse_str($route[1],$params);
                }                
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // ��������·��
    // '·������'=>'[������/����]?����1=ֵ1&����2=ֵ2...'
    // '·������'=>array('[������/����]?����1=ֵ1&����2=ֵ2...','�������1=ֵ1&�������2=ֵ2...')
    // '·������'=>'�ⲿ��ַ'
    // '·������'=>array('�ⲿ��ַ','�ض������')
    // ����ֵ���ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // '/new\/(\d+)\/(\d+)/'=>array('News/read?id=:1&page=:2&cate=1','status=1'),
    // '/new\/(\d+)/'=>array('/new.php?id=:1&page=:2&status=1','301'), �ض���
    private static function parseRegex($matches,$route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  is_array($route)?$route[0]:$route;
        $url   =  preg_replace_callback('/:(\d+)/', function($match) use($matches){return $matches[$match[1]];}, $url); 
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  self::parseUrl($url);
            // ������
            foreach($var as $key=>$val){
                if(strpos($val,'|')){
                    list($val,$fun) = explode('|',$val);
                    $var[$key]    =   $fun($val);
                }
            }
            // ����ʣ���URL����
            $regx =  substr_replace($regx,'',0,strlen($matches[0]));
            if($regx) {
                preg_replace_callback('/(\w+)\/([^\/]+)/', function($match) use(&$var){
                    $var[strtolower($match[1])] = strip_tags($match[2]);
                }, $regx);
            }
            // ����·���Զ��������
            if(is_array($route) && isset($route[1])) {
                if(is_array($route[1])){
                    $params     =   $route[1];
                }else{
                    parse_str($route[1],$params);
                }
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // ִ������ƥ���µıհ����� ֧�ֲ�������
    static private function invokeRegx($closure, $var = array()) {
        $reflect = new \ReflectionFunction($closure);
        $params  = $reflect->getParameters();
        $args    = array();
        array_shift($var);
        foreach ($params as $param){
            if(!empty($var)) {
                $args[] = array_shift($var);
            }elseif($param->isDefaultValueAvailable()){
                $args[] = $param->getDefaultValue();
            }
        }
        return $reflect->invokeArgs($args);
    }

    // ִ�й���ƥ���µıհ����� ֧�ֲ�������
    static private function invokeRule($closure, $var = array()) {
        $reflect = new \ReflectionFunction($closure);
        $params  = $reflect->getParameters();
        $args    = array();
        foreach ($params as $param){
            $name = $param->getName();
            if(isset($var[$name])) {
                $args[] = $var[$name];
            }elseif($param->isDefaultValueAvailable()){
                $args[] = $param->getDefaultValue();
            }
        }
        return $reflect->invokeArgs($args);
    }

}
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
 * ThinkPHP ������
 */
class Think {

    // ��ӳ��
    private static $_map      = array();

    // ʵ��������
    private static $_instance = array();

    /**
     * Ӧ�ó����ʼ��
     * @access public
     * @return void
     */
    static public function start() {
      // ע��AUTOLOAD����
      spl_autoload_register('Think\Think::autoload');      
      // �趨������쳣����
      register_shutdown_function('Think\Think::fatalError');
      set_error_handler('Think\Think::appError');
      set_exception_handler('Think\Think::appException');

      // ��ʼ���ļ��洢��ʽ
      Storage::connect(STORAGE_TYPE);
      $runtimefile  = RUNTIME_PATH.APP_MODE.'~runtime.php';
      if(!APP_DEBUG && Storage::has($runtimefile)){
          Storage::load($runtimefile);
      }else{
          if(Storage::has($runtimefile))
              Storage::unlink($runtimefile);
          
          $content =  '';
          // ��ȡӦ��ģʽ
          $mode   =   include is_file(CONF_PATH.'core.php')?CONF_PATH.'core.php':MODE_PATH.APP_MODE.'.php';

          // ���غ����ļ�
          foreach ($mode['core'] as $file){
              if(is_file($file)) {
                include $file;
                if(!APP_DEBUG) $content   .= compile($file);
              }else{
                //echo $file."not exists";
              }
          }

          // ����Ӧ��ģʽ�����ļ�
          foreach ($mode['config'] as $key=>$file){
              is_numeric($key)?C(load_config($file)):C($key,load_config($file));
          }

          // ��ȡ��ǰӦ��ģʽ��Ӧ�������ļ�
          if('common' != APP_MODE && is_file(CONF_PATH.'config_'.APP_MODE.CONF_EXT))
              C(load_config(CONF_PATH.'config_'.APP_MODE.CONF_EXT));  

          // ����ģʽ��������
          if(isset($mode['alias'])){
              self::addMap(is_array($mode['alias'])?$mode['alias']:include $mode['alias']);
          }

          // ����Ӧ�ñ��������ļ�
          if(is_file(CONF_PATH.'alias.php'))
              self::addMap(include CONF_PATH.'alias.php');

          // ����ģʽ��Ϊ����
          if(isset($mode['tags'])) {
              Hook::import(is_array($mode['tags'])?$mode['tags']:include $mode['tags']);
          }

          // ����Ӧ����Ϊ����
          if(is_file(CONF_PATH.'tags.php'))
              // ����Ӧ�����ӿ���ģʽ���ö���
              Hook::import(include CONF_PATH.'tags.php');   

          // ���ؿ�ܵײ����԰�
          L(include THINK_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');

          if(!APP_DEBUG){
              if(APP_MODE == "JAE"){
                $content  .=  "\nnamespace { Think::addMap(".var_export(self::$_map,true).");";
                $content  .=  "\nL(".var_export(L(),true).");\nC(".var_export(C(),true).');use Think;Think\Hook::import('.var_export(Hook::get(),true).');}';  
              }else{
                $content  .=  "\nnamespace { Think::addMap(".var_export(self::$_map,true).");";
                $content  .=  "\nL(".var_export(L(),true).");\nC(".var_export(C(),true).');Think\Hook::import('.var_export(Hook::get(),true).');}';  
              }
              Storage::put($runtimefile,('<?php '.$content));
          }else{
            // ����ģʽ����ϵͳĬ�ϵ������ļ�
            C(include THINK_PATH.'Conf/debug.php');
            // ��ȡӦ�õ��������ļ�
            if(is_file(CONF_PATH.'debug'.CONF_EXT))
                C(include CONF_PATH.'debug'.CONF_EXT);           
          }
      }

      // ��ȡ��ǰӦ��״̬��Ӧ�������ļ�
      if(APP_STATUS && is_file(CONF_PATH.APP_STATUS.CONF_EXT))
          C(include CONF_PATH.APP_STATUS.CONF_EXT);   

      // ����ϵͳʱ��
      date_default_timezone_set(C('DEFAULT_TIMEZONE'));

      // ���Ӧ��Ŀ¼�ṹ ������������Զ�����
      if(C('CHECK_APP_DIR')) {
          $module     =   defined('BIND_MODULE') ? BIND_MODULE : C('DEFAULT_MODULE');
          if(!is_dir(APP_PATH.$module) || !is_dir(LOG_PATH)){
              // ���Ӧ��Ŀ¼�ṹ
              Build::checkDir($module);
          }
      }

      // ��¼�����ļ�ʱ��
      G('loadTime');
      // ����Ӧ��
      App::run();
    }

    // ע��classmap
    static public function addMap($class, $map=''){
        if(is_array($class)){
            self::$_map = array_merge(self::$_map, $class);
        }else{
            self::$_map[$class] = $map;
        }        
    }

    // ��ȡclassmap
    static public function getMap($class=''){
        if(''===$class){
            return self::$_map;
        }elseif(isset(self::$_map[$class])){
            return self::$_map[$class];
        }else{
            return null;
        }
    }

    /**
     * ����Զ�����
     * @param string $class ��������
     * @return void
     */
    public static function autoload($class) {
        // ����Ƿ����ӳ��
        //echo "<p> class=".$class ." name=$name filename=$filename  false need to load</p>";
        
        if(isset(self::$_map[$class])) {
            include self::$_map[$class];
        }elseif(false !== strpos($class,'\\')){

          $name           =   strstr($class, '\\', true);
          
          if(in_array($name,array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$name)){ 
              // LibraryĿ¼����������ռ��Զ���λ

              $path       =   LIB_PATH;
          }else{
              // ����Զ��������ռ� �������ģ��Ϊ�����ռ�
              $namespace  =   C('AUTOLOAD_NAMESPACE');
              $path       =   isset($namespace[$name])? dirname($namespace[$name]).'/' : APP_PATH;
          }
          $filename       =   $path . str_replace('\\', '/', $class) . EXT;
          //echo "<p> class=".$class ." name=$name filename=$filename  false need to load</p>";
          if(is_file($filename)) {
              // Win���������ϸ����ִ�Сд
              if (IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT)){
                  return ;
              }
              include $filename;
          }
        }elseif (!C('APP_USE_NAMESPACE')) {
            // �Զ����ص�����
            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer){
                if(substr($class,-strlen($layer))==$layer){
                    if(require_cache(MODULE_PATH.$layer.'/'.$class.EXT)) {
                        return ;
                    }
                }            
            }
            // �����Զ�����·�����ý��г�������
            foreach (explode(',',C('APP_AUTOLOAD_PATH')) as $path){
                if(import($path.'.'.$class))
                    // ���������ɹ��򷵻�
                    return ;
            }
        }
    }

    /**
     * ȡ�ö���ʵ�� ֧�ֵ�����ľ�̬����
     * @param string $class ��������
     * @param string $method ��ľ�̬������
     * @return object
     */
    static public function instance($class,$method='') {
        $identify   =   $class.$method;
        if(!isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    self::$_instance[$identify] = call_user_func(array(&$o, $method));
                else
                    self::$_instance[$identify] = $o;
            }
            else
                self::halt(L('_CLASS_NOT_EXIST_').':'.$class);
        }
        return self::$_instance[$identify];
    }

    /**
     * �Զ����쳣����
     * @access public
     * @param mixed $e �쳣����
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
        Log::record($error['message'],Log::ERR);
        // ����404��Ϣ
        header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
        self::halt($error);
    }

    /**
     * �Զ��������
     * @access public
     * @param int $errno ��������
     * @param string $errstr ������Ϣ
     * @param string $errfile �����ļ�
     * @param int $errline ��������
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
      switch ($errno) {
          case E_ERROR:
          case E_PARSE:
          case E_CORE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:
            ob_end_clean();
            $errorStr = "$errstr ".$errfile." �� $errline ��.";
//            if(C('LOG_RECORD')) Log::write("[$errno] ".$errorStr,Log::ERR);
            self::halt($errorStr);
            break;
          default:
            $errorStr = "[$errno] $errstr ".$errfile." �� $errline ��.";
            self::trace($errorStr,'','NOTIC');
            break;
      }
    }
    
    // �������󲶻�
    static public function fatalError() {
        Log::save();
        if ($e = error_get_last()) {
            switch($e['type']){
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:  
                ob_end_clean();
                self::halt($e);
                break;
            }
        }
    }

    /**
     * �������
     * @param mixed $error ����
     * @return void
     */
    static public function halt($error) {
        $e = array();
        if (APP_DEBUG || IS_CLI) {
            //����ģʽ�����������Ϣ
            if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['line']      = $trace[0]['line'];
                ob_start();
                //debug_print_backtrace();
                $e['trace']     = ob_get_clean();
            } else {
                $e              = $error;
            }
            if(IS_CLI){
                exit(iconv('UTF-8','gbk',$e['message']).PHP_EOL.'FILE: '.$e['file'].'('.$e['line'].')'.PHP_EOL.$e['trace']);
            }
        } else {
            //�����򵽴���ҳ��
            $error_page         = C('ERROR_PAGE');
            if (!empty($error_page)) {
                redirect($error_page);
            } else {
                $message        = is_array($error) ? $error['message'] : $error;
                $e['message']   = C('SHOW_ERROR_MSG')? $message : C('ERROR_MESSAGE');
            }
        }

        // �����쳣ҳ��ģ��
        echo "<p>error ".$error['message']. " " .$error['file']." on ".$error['line']."</p>";
        echo "<p>".$error['trace']"</p>";
        //$exceptionFile =  C('TMPL_EXCEPTION_FILE',null,THINK_PATH.'Tpl/think_exception.tpl');
        //include $exceptionFile;
        exit;
    }

    /**
     * ��Ӻͻ�ȡҳ��Trace��¼
     * @param string $value ����
     * @param string $label ��ǩ
     * @param string $level ��־����(����ҳ��Trace��ѡ�)
     * @param boolean $record �Ƿ��¼��־
     * @return void
     */
    static public function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
        static $_trace =  array();
        if('[think]' === $value){ // ��ȡtrace��Ϣ
            return $_trace;
        }else{
            $info   =   ($label?$label.':':'').print_r($value,true);
            $level  =   strtoupper($level);
            
            if((defined('IS_AJAX') && IS_AJAX) || /*!C('SHOW_PAGE_TRACE')  ||*/ $record) {
                Log::record($info,$level,$record);
            }else{
                if(!isset($_trace[$level]) || count($_trace[$level])>C('TRACE_MAX_RECORD')) {
                    $_trace[$level] =   array();
                }
                $_trace[$level][]   =   $info;
            }
        }
    }
}

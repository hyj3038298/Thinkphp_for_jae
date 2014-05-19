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
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();
/**
 * �ļ����ͻ�����
 */
class tair extends Cache {

    /**
     * �ܹ�����
     * @access public
     */
    public function __construct($options=array()) {
        if(!empty($options)) {
            $this->options =  $options;
        }
        $this->options['temp']      =   !empty($options['temp'])?   $options['temp']    :   C('DATA_CACHE_PATH');
        $this->options['prefix']    =   isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['expire']    =   isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['length']    =   isset($options['length'])?  $options['length']  :   0;
        if(substr($this->options['temp'], -1) != '/')    $this->options['temp'] .= '/';
        $this->init();
    }

    /**
     * ��ʼ�����
     * @access private
     * @return boolean
     */
    private function init() {
        // ����Ӧ�û���Ŀ¼
        if (!is_dir($this->options['temp'])) {
            mkdir($this->options['temp']);
        }
    }



    /**
     * ��ȡ����
     * @access public
     * @param string $name ���������
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        return $cacheService -> get($name);
    }

    /**
     * д�뻺��
     * @access public
     * @param string $name ���������
     * @param mixed $value  �洢����
     * @param int $expire  ��Чʱ�� 0Ϊ����
     * @return boolean
     */
    public function set($name,$value,$expire=null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire =  $this->options['expire'];
        }
        return $cacheService -> set($name, $value, $expire);
    }

    /**
     * ɾ������
     * @access public
     * @param string $name ���������
     * @return boolean
     */
    public function rm($name) {
        return unlink($this->filename($name));
    }

    /**
     * �������
     * @access public
     * @param string $name ���������
     * @return boolean
     */
    public function clear() {
        $path   =  $this->options['temp'];
        $files  =   scandir($path);
        if($files){
            foreach($files as $file){
                if ($file != '.' && $file != '..' && is_dir($path.$file) ){
                    array_map( 'unlink', glob( $path.$file.'/*.*' ) );
                }elseif(is_file($path.$file)){
                    unlink( $path . $file );
                }
            }
            return true;
        }
        return false;
    }
}
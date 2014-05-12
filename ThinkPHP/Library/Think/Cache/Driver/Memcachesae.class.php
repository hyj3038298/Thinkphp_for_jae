<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;

defined('THINK_PATH') or exit();
/**
 * Memcache��������
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author    liu21st <liu21st@gmail.com>
 */
class Memcachesae extends Cache {

    /**
     * �ܹ�����
     * @param array $options �������
     * @access public
     */
    function __construct($options=array()) {
        if(empty($options)) {
            $options = array (
                'host'        =>  C('MEMCACHE_HOST') ? C('MEMCACHE_HOST') : '127.0.0.1',
                'port'        =>  C('MEMCACHE_PORT') ? C('MEMCACHE_PORT') : 11211,
                'timeout'     =>  C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent'  =>  false,
            );
        }
        $this->options      =   $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
      //  $func               =   isset($options['persistent']) ? 'pconnect' : 'connect';
        $this->handler      =  memcache_init();//[sae] ��ʵ����
        //[sae] �²�������
        $this->connected=true;
        // $this->connected    =   $options['timeout'] === false ?
        //     $this->handler->$func($options['host'], $options['port']) :
        //     $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * �Ƿ�����
     * @access private
     * @return boolean
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
     * ��ȡ����
     * @access public
     * @param string $name ���������
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        return $this->handler->get($_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name);
    }

    /**
     * д�뻺��
     * @access public
     * @param string $name ���������
     * @param mixed $value  �洢����
     * @param integer $expire  ��Чʱ�䣨�룩
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        if($this->handler->set($_SERVER['HTTP_APPVERSION'].'/'.$name, $value, 0, $expire)) {
            if($this->options['length']>0) {
                // ��¼�������
                $this->queue($name);
            }
            return true;
        }
        return false;
    }

    /**
     * ɾ������
     * @access public
     * @param string $name ���������
     * @return boolean
     */
    public function rm($name, $ttl = false) {
        $name   =   $_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name;
        return $ttl === false ?
            $this->handler->delete($name) :
            $this->handler->delete($name, $ttl);
    }

    /**
     * �������
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flush();
    }

    /**
     * ���л���
     * @access protected
     * @param string $key ������
     * @return mixed
     */
    //[sae] ����дqueque���л��淽��
    protected function queue($key) {
        $queue_name=isset($this->options['queue_name'])?$this->options['queue_name']:'think_queue';
        $value  =  F($queue_name);
        if(!$value) {
            $value   =  array();
        }
        // ����
        if(false===array_search($key, $value)) array_push($value,$key);
        if(count($value) > $this->options['length']) {
            // ����
            $key =  array_shift($value);
            // ɾ������
            $this->rm($key);
            if (APP_DEBUG) {
                    //����ģʽ�¼�¼���Ӵ���
                        $counter = Think::instance('SaeCounter');
                        if ($counter->exists($queue_name.'_out_times'))
                            $counter->incr($queue_name.'_out_times');
                        else
                            $counter->create($queue_name.'_out_times', 1);
           }
        }
        return F($queue_name,$value);
    }

}

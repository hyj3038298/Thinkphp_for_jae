<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
// �ֲ�ʽ�ļ��洢��
class Storage {

    /**
     * �������
     * @var string
     * @access protected
     */
    static protected $handler    ;

    /**
     * ���ӷֲ�ʽ�ļ�ϵͳ
     * @access public
     * @param string $type �ļ�����
     * @param array $options  ��������
     * @return void
     */
    static public function connect($type='File',$options=array()) {
        $class  =   'Think\\Storage\\Driver\\'.ucwords($type);
        self::$handler = new $class($options);
    }

    static public function __callStatic($method,$args){
        //���û��������ķ���
        if(method_exists(self::$handler, $method)){
           return call_user_func_array(array(self::$handler,$method), $args);
        }
    }
}

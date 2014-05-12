<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
/**
 * ThinkPHPϵͳ����ʵ��
 */
class Hook {

    static private  $tags       =   array();

    /**
     * ��̬��Ӳ����ĳ����ǩ
     * @param string $tag ��ǩ����
     * @param mixed $name �������
     * @return void
     */
    static public function add($tag,$name) {
        if(!isset(self::$tags[$tag])){
            self::$tags[$tag]   =   array();
        }
        if(is_array($name)){
            self::$tags[$tag]   =   array_merge(self::$tags[$tag],$name);
        }else{
            self::$tags[$tag][] =   $name;
        }
    }

    /**
     * ����������
     * @param array $data �����Ϣ
     * @param boolean $recursive �Ƿ�ݹ�ϲ�
     * @return void
     */
    static public function import($data,$recursive=true) {
        if(!$recursive){ // ���ǵ���
            self::$tags   =   array_merge(self::$tags,$data);
        }else{ // �ϲ�����
            foreach ($data as $tag=>$val){
                if(!isset(self::$tags[$tag]))
                    self::$tags[$tag]   =   array();            
                if(!empty($val['_overlay'])){
                    // �������ĳ����ǩָ������ģʽ
                    unset($val['_overlay']);
                    self::$tags[$tag]   =   $val;
                }else{
                    // �ϲ�ģʽ
                    self::$tags[$tag]   =   array_merge(self::$tags[$tag],$val);
                }
            }            
        }
    }

    /**
     * ��ȡ�����Ϣ
     * @param string $tag ���λ�� ���ջ�ȡȫ��
     * @return array
     */
    static public function get($tag='') {
        if(empty($tag)){
            // ��ȡȫ���Ĳ����Ϣ
            return self::$tags;
        }else{
            return self::$tags[$tag];
        }
    }

    /**
     * ������ǩ�Ĳ��
     * @param string $tag ��ǩ����
     * @param mixed $params �������
     * @return void
     */
    static public function listen($tag, &$params=NULL) {
        if(isset(self::$tags[$tag])) {
            if(APP_DEBUG) {
                G($tag.'Start');
                trace('[ '.$tag.' ] --START--','','INFO');
            }
            foreach (self::$tags[$tag] as $name) {
                APP_DEBUG && G($name.'_start');
                $result =   self::exec($name, $tag,$params);
                if(APP_DEBUG){
                    G($name.'_end');
                    trace('Run '.$name.' [ RunTime:'.G($name.'_start',$name.'_end',6).'s ]','','INFO');
                }
                if(false === $result) {
                    // �������false ���жϲ��ִ��
                    return ;
                }
            }
            if(APP_DEBUG) { // ��¼��Ϊ��ִ����־
                trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
            }
        }
        return;
    }

    /**
     * ִ��ĳ�����
     * @param string $name �������
     * @param string $tag ����������ǩ����     
     * @param Mixed $params ����Ĳ���
     * @return void
     */
    static public function exec($name, $tag,&$params=NULL) {
        if('Behavior' == substr($name,-8) ){
            // ��Ϊ��չ������run��ڷ���
            $tag    =   'run';
        }
        $addon   = new $name();
        return $addon->$tag($params);
    }
}

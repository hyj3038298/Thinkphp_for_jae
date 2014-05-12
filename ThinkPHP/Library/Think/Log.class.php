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
 * ��־������
 */
class Log {

    // ��־���� ���ϵ��£��ɵ͵���
    const EMERG     = 'EMERG';  // ���ش���: ����ϵͳ�����޷�ʹ��
    const ALERT     = 'ALERT';  // �����Դ���: ���뱻�����޸ĵĴ���
    const CRIT      = 'CRIT';  // �ٽ�ֵ����: �����ٽ�ֵ�Ĵ�������һ��24Сʱ�����������25Сʱ����
    const ERR       = 'ERR';  // һ�����: һ���Դ���
    const WARN      = 'WARN';  // �����Դ���: ��Ҫ��������Ĵ���
    const NOTICE    = 'NOTIC';  // ֪ͨ: ����������е��ǻ����������Ĵ���
    const INFO      = 'INFO';  // ��Ϣ: ���������Ϣ
    const DEBUG     = 'DEBUG';  // ����: ������Ϣ
    const SQL       = 'SQL';  // SQL��SQL��� ע��ֻ�ڵ���ģʽ����ʱ��Ч

    // ��־��Ϣ
    static protected $log       =  array();

    // ��־�洢
    static protected $storage   =   null;

    // ��־��ʼ��
    static public function init($config=array()){
        $type   =   isset($config['type'])?$config['type']:'File';
        $class  =   strpos($type,'\\')? $type: 'Think\\Log\\Driver\\'. ucwords(strtolower($type));           
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * ��¼��־ ���һ����δ�����õļ���
     * @static
     * @access public
     * @param string $message ��־��Ϣ
     * @param string $level  ��־����
     * @param boolean $record  �Ƿ�ǿ�Ƽ�¼
     * @return void
     */
    static function record($message,$level=self::ERR,$record=false) {
        if($record || false !== strpos(C('LOG_LEVEL'),$level)) {
            self::$log[] =   "{$level}: {$message}\r\n";
        }
    }

    /**
     * ��־����
     * @static
     * @access public
     * @param integer $type ��־��¼��ʽ
     * @param string $destination  д��Ŀ��
     * @return void
     */
    static function save($type='',$destination='') {
        if(empty(self::$log)) return ;

        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Think\\Log\\Driver\\'. ucwords($type);
            self::$storage = new $class();            
        }
        $message    =   implode('',self::$log);
        self::$storage->write($message,$destination);
        // ����������־����
        self::$log = array();
    }

    /**
     * ��־ֱ��д��
     * @static
     * @access public
     * @param string $message ��־��Ϣ
     * @param string $level  ��־����
     * @param integer $type ��־��¼��ʽ
     * @param string $destination  д��Ŀ��
     * @return void
     */
    static function write($message,$level=self::ERR,$type='',$destination='') {
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Think\\Log\\Driver\\'. ucwords($type);
            self::$storage = new $class();            
        }
        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';        
        self::$storage->write("{$level}: {$message}", $destination);
    }
}
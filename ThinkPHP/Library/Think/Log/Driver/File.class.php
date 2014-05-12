<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Log\Driver;

class File {

    protected $config  =   array(
        'log_time_format'   =>  ' c ',
        'log_file_size'     =>  2097152,
        'log_path'          =>  '',
    );

    // ʵ�������������
    public function __construct($config=array()){
        $this->config   =   array_merge($this->config,$config);
    }

    /**
     * ��־д��ӿ�
     * @access public
     * @param string $log ��־��Ϣ
     * @param string $destination  д��Ŀ��
     * @return void
     */
    public function write($log,$destination='') {
        $now = date($this->config['log_time_format']);
        if(empty($destination))
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        //�����־�ļ���С���������ô�С�򱸷���־�ļ���������
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) )
              rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        error_log("[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
    }
}

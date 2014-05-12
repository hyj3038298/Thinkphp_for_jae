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
namespace Think\Storage\Driver;
use Think\Storage;
// �����ļ�д��洢��
class File extends Storage{

    private $contents=array();

    /**
     * �ܹ�����
     * @access public
     */
    public function __construct() {
    }

    /**
     * �ļ����ݶ�ȡ
     * @access public
     * @param string $filename  �ļ���
     * @return string     
     */
    public function read($filename,$type=''){
        return $this->get($filename,'content',$type);
    }

    /**
     * �ļ�д��
     * @access public
     * @param string $filename  �ļ���
     * @param string $content  �ļ�����
     * @return boolean         
     */
    public function put($filename,$content,$type=''){
        $dir         =  dirname($filename);
        if(!is_dir($dir))
            mkdir($dir,0755,true);
        if(false === file_put_contents($filename,$content)){
            E(L('_STORAGE_WRITE_ERROR_').':'.$filename);
        }else{
            $this->contents[$filename]=$content;
            return true;
        }
    }

    /**
     * �ļ�׷��д��
     * @access public
     * @param string $filename  �ļ���
     * @param string $content  ׷�ӵ��ļ�����
     * @return boolean        
     */
    public function append($filename,$content,$type=''){
        if(is_file($filename)){
            $content =  $this->read($filename,$type).$content;
        }
        return $this->put($filename,$content,$type);
    }

    /**
     * �����ļ�
     * @access public
     * @param string $filename  �ļ���
     * @param array $vars  �������
     * @return void        
     */
    public function load($filename,$vars=null){
        if(!is_null($vars))
            extract($vars, EXTR_OVERWRITE);
        include $filename;
    }

    /**
     * �ļ��Ƿ����
     * @access public
     * @param string $filename  �ļ���
     * @return boolean     
     */
    public function has($filename,$type=''){
        return is_file($filename);
    }

    /**
     * �ļ�ɾ��
     * @access public
     * @param string $filename  �ļ���
     * @return boolean     
     */
    public function unlink($filename,$type=''){
        unset($this->contents[$filename]);
        return is_file($filename) ? unlink($filename) : false; 
    }

    /**
     * ��ȡ�ļ���Ϣ
     * @access public
     * @param string $filename  �ļ���
     * @param string $name  ��Ϣ�� mtime����content
     * @return boolean     
     */
    public function get($filename,$name,$type=''){
        if(!isset($this->contents[$filename])){
            if(!is_file($filename)) return false;
           $this->contents[$filename]=file_get_contents($filename);
        }
        $content=$this->contents[$filename];
        $info   =   array(
            'mtime'     =>  filemtime($filename),
            'content'   =>  $content
        );
        return $info[$name];
    }
}

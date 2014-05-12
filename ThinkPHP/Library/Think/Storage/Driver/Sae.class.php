<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>
// +----------------------------------------------------------------------
namespace Think\Storage\Driver;
use Think\Storage;
// SAE�����ļ�д��洢��
class Sae extends Storage{

    /**
     * �ܹ�����
     * @access public
     */
    private $mc;
    private $kvs        =   array();
    private $htmls      =   array();
    private $contents   =   array();
    public function __construct() {
        if(!function_exists('memcache_init')){
              header('Content-Type:text/html;charset=utf-8');
              exit('����SAEƽ̨�����д��롣');
        }
        $this->mc       =   @memcache_init();
        if(!$this->mc){
              header('Content-Type:text/html;charset=utf-8');
              exit('��δ��ͨMemcache��������SAE����ƽ̨��ʼ��Memcache����');
        }
    }

    /**
     * ���SaeKv����
     */
    private function getKv(){
        static $kv;
        if(!$kv){
           $kv  =   new \SaeKV();
           if(!$kv->init())
               E('��û�г�ʼ��KVDB������SAE����ƽ̨��ʼ��KVDB����');
        }
        return $kv;
    }


    /**
     * �ļ����ݶ�ȡ
     * @access public
     * @param string $filename  �ļ���
     * @return string
     */
    public function read($filename,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv     =   $this->getKv();
                if(!isset($this->kvs[$filename])){
                    $this->kvs[$filename]=$kv->get($filename);
                }
                return $this->kvs[$filename];
            default:
                return $this->get($filename,'content',$type);
        }        
    }

    /**
     * �ļ�д��
     * @access public
     * @param string $filename  �ļ���
     * @param string $content  �ļ�����
     * @return boolean
     */
    public function put($filename,$content,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv         =   $this->getKv();
                $this->kvs[$filename] = $content;
                return $kv->set($filename,$content);
            case 'html':    
                $kv         =   $this->getKv();
                $content    =   time().$content;
                $this->htmls[$filename] =   $content;
                return $kv->set($filename,$content);
            default:
                $content    =   time().$content;
                if(!$this->mc->set($filename,$content,MEMCACHE_COMPRESSED,0)){
                    E(L('_STORAGE_WRITE_ERROR_').':'.$filename);
                }else{
                    $this->contents[$filename] = $content;
                    return true;
                }            
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
        if($old_content = $this->read($filename,$type)){
            $content =  $old_content.$content;
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
        eval('?>'.$this->read($filename));
    }

    /**
     * �ļ��Ƿ����
     * @access public
     * @param string $filename  �ļ���
     * @return boolean
     */
    public function has($filename,$type=''){
        if($this->read($filename,$type)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * �ļ�ɾ��
     * @access public
     * @param string $filename  �ļ���
     * @return boolean
     */
    public function unlink($filename,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv     =   $this->getKv();
                unset($this->kvs[$filename]);
                return $kv->delete($filename);
            case 'html':    
                $kv     =   $this->getKv();
                unset($this->htmls[$filename]);
                return $kv->delete($filename);
            default:
                unset($this->contents[$filename]);
                return $this->mc->delete($filename);            
        }        
    }

    /**
     * ��ȡ�ļ���Ϣ
     * @access public
     * @param string $filename  �ļ���
     * @param string $name  ��Ϣ�� mtime����content
     * @return boolean
     */
    public function get($filename,$name,$type=''){
        switch(strtolower($type)){
            case 'html':
                if(!isset($this->htmls[$filename])){
                    $kv = $this->getKv();
                    $this->htmls[$filename] = $kv->get($filename);
                }
                $content = $this->htmls[$filename];
                break;
            default:
                if(!isset($this->contents[$filename])){
                    $this->contents[$filename] = $this->mc->get($filename);
                }
                $content =  $this->contents[$filename];
        }
        if(false===$content){
            return false;
        }
        $info   =   array(
            'mtime'     =>  substr($content,0,10),
            'content'   =>  substr($content,10)
        );
        return $info[$name];        
    }

}
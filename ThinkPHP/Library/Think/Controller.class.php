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
 * ThinkPHP ���������� ������
 */
abstract class Controller {

    /**
     * ��ͼʵ������
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * ����������
     * @var config
     * @access protected
     */      
    protected $config   =   array();

   /**
     * �ܹ����� ȡ��ģ�����ʵ��
     * @access public
     */
    public function __construct() {
        Hook::listen('action_begin',$this->config);
        //ʵ������ͼ��
        $this->view     = Think::instance('Think\View');
        //��������ʼ��
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

    /**
     * ģ����ʾ �������õ�ģ��������ʾ������
     * @access protected
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @param string $charset �������
     * @param string $contentType �������
     * @param string $content �������
     * @param string $prefix ģ�建��ǰ׺
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }

    /**
     * ��������ı����԰���Html ��֧�����ݽ���
     * @access protected
     * @param string $content �������
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     * @param string $prefix ģ�建��ǰ׺
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='',$prefix='') {
        $this->view->display('',$charset,$contentType,$content,$prefix);
    }

    /**
     *  ��ȡ���ҳ������
     * �������õ�ģ������fetch������
     * @access protected
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @param string $content ģ���������
     * @param string $prefix ģ�建��ǰ׺* 
     * @return string
     */
    protected function fetch($templateFile='',$content='',$prefix='') {
        return $this->view->fetch($templateFile,$content,$prefix);
    }

    /**
     *  ������̬ҳ��
     * @access protected
     * @htmlfile ���ɵľ�̬�ļ�����
     * @htmlpath ���ɵľ�̬�ļ�·��
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content    =   $this->fetch($templateFile);
        $htmlpath   =   !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile   =   $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        Storage::put($htmlfile,$content,'html');
        return $content;
    }

    /**
     * ģ����������
     * @access protected
     * @param string $theme ģ������
     * @return Action
     */
    protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    /**
     * ģ�������ֵ
     * @access protected
     * @param mixed $name Ҫ��ʾ��ģ�����
     * @param mixed $value ������ֵ
     * @return Action
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }

    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * ȡ��ģ����ʾ������ֵ
     * @access protected
     * @param string $name ģ����ʾ����
     * @return mixed
     */
    public function get($name='') {
        return $this->view->get($name);      
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * ���ģ�������ֵ
     * @access public
     * @param string $name ����
     * @return boolean
     */
    public function __isset($name) {
        return $this->get($name);
    }

    /**
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     * @access public
     * @param string $method ������
     * @param array $args ����
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // ���������_empty���� �����
                $this->_empty($method,$args);
            }elseif(file_exists_case($this->view->parseTemplate())){
                // ����Ƿ����Ĭ��ģ�� �����ֱ�����ģ��
                $this->display();
            }else{
                E(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     * ����������ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ������Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * �����ɹ���ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ��ʾ��Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax��ʽ�������ݵ��ͻ���
     * @access protected
     * @param mixed $data Ҫ���ص�����
     * @param String $type AJAX�������ݸ�ʽ
     * @return void
     */
    protected function ajaxReturn($data,$type='') {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // ����xml��ʽ����
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');  
            case 'EVAL' :
                // ���ؿ�ִ�е�js�ű�
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
            default     :
                // ������չ�������ظ�ʽ����
                Hook::listen('ajax_return',$data);
        }
    }

    /**
     * Action��ת(URL�ض��� ֧��ָ��ģ�����ʱ��ת
     * @access protected
     * @param string $url ��ת��URL���ʽ
     * @param array $params ����URL����
     * @param integer $delay ��ʱ��ת��ʱ�� ��λΪ��
     * @param string $msg ��ת��ʾ��Ϣ
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * Ĭ����ת���� ֧�ִ��������ȷ��ת
     * ����ģ����ʾ Ĭ��ΪpublicĿ¼�����successҳ��
     * ��ʾҳ��Ϊ������ ֧��ģ���ǩ
     * @param string $message ��ʾ��Ϣ
     * @param Boolean $status ״̬
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX�ύ
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // ��ʾ����
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //��������˹رմ��ڣ�����ʾ��Ϻ��Զ��رմ���
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // ״̬
        //��֤������ܾ�̬����Ӱ��
        C('HTML_CACHE_ON',false);
        if($status) { //���ͳɹ���Ϣ
            $this->assign('message',$message);// ��ʾ��Ϣ
            // �ɹ�������Ĭ��ͣ��1��
            if(!isset($this->waitSecond))    $this->assign('waitSecond','1');
            // Ĭ�ϲ����ɹ��Զ����ز���ǰҳ��
            if(!isset($this->jumpUrl)) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// ��ʾ��Ϣ
            //��������ʱ��Ĭ��ͣ��3��
            if(!isset($this->waitSecond))    $this->assign('waitSecond','3');
            // Ĭ�Ϸ�������Ļ��Զ�������ҳ
            if(!isset($this->jumpUrl)) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // ��ִֹ��  �����������ִ��
            exit ;
        }
    }

   /**
     * ��������
     * @access public
     */
    public function __destruct() {
        // ִ�к�������
        Hook::listen('action_end');
    }
}
// ���ÿ��������� ��������
if(function_exists("class_alias")){
    class_alias('Think\Controller','Think\Action');    
}

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
 * ThinkPHP ��ͼ��
 */
class View {
    /**
     * ģ���������
     * @var tVar
     * @access protected
     */ 
    protected $tVar     =   array();

    /**
     * ģ������
     * @var theme
     * @access protected
     */ 
    protected $theme    =   '';

    /**
     * ģ�������ֵ
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * ȡ��ģ�������ֵ
     * @access public
     * @param string $name
     * @return mixed
     */
    public function get($name=''){
        if('' === $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name])?$this->tVar[$name]:false;
    }

    /**
     * ����ģ���ҳ����� ���Է����������
     * @access public
     * @param string $templateFile ģ���ļ���
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     * @param string $content ģ���������
     * @param string $prefix ģ�建��ǰ׺
     * @return mixed
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        G('viewStartTime');
        // ��ͼ��ʼ��ǩ
        Hook::listen('view_begin',$templateFile);
        // ��������ȡģ������
        $content = $this->fetch($templateFile,$content,$prefix);
        // ���ģ������
        $this->render($content,$charset,$contentType);
        // ��ͼ������ǩ
        Hook::listen('view_end');
    }

    /**
     * ��������ı����԰���Html
     * @access private
     * @param string $content �������
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     * @return mixed
     */
    private function render($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // ��ҳ�ַ�����
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: '.C('HTTP_CACHE_CONTROL'));  // ҳ�滺�����
        header('X-Powered-By:ThinkPHP');
        // ���ģ���ļ�
        echo $content;
    }

    /**
     * �����ͻ�ȡģ������ �������
     * @access public
     * @param string $templateFile ģ���ļ���
     * @param string $content ģ���������
     * @param string $prefix ģ�建��ǰ׺
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix='') {
        if(empty($content)) {
            $templateFile   =   $this->parseTemplate($templateFile);
            // ģ���ļ�������ֱ�ӷ���
            if(!is_file($templateFile)) E(L('_TEMPLATE_NOT_EXIST_').':'.$templateFile);
        }
        // ҳ�滺��
        ob_start();
        ob_implicit_flush(0);
        if('php' == strtolower(C('TMPL_ENGINE_TYPE'))) { // ʹ��PHPԭ��ģ��
            // ģ�����б����ֽ��Ϊ��������
            extract($this->tVar, EXTR_OVERWRITE);
            // ֱ������PHPģ��
            empty($content)?include $templateFile:eval('?>'.$content);
        }else{
            // ��ͼ������ǩ
            $params = array('var'=>$this->tVar,'file'=>$templateFile,'content'=>$content,'prefix'=>$prefix);
            Hook::listen('view_parse',$params);
        }
        // ��ȡ����ջ���
        $content = ob_get_clean();
        // ���ݹ��˱�ǩ
        Hook::listen('view_filter',$content);
        // ���ģ���ļ�
        return $content;
    }

    /**
     * �Զ���λģ���ļ�
     * @access protected
     * @param string $template ģ���ļ�����
     * @return string
     */
    public function parseTemplate($template='') {
        if(is_file($template)) {
            return $template;
        }
        $depr       =   C('TMPL_FILE_DEPR');
        $template   =   str_replace(':', $depr, $template);
        // ��ȡ��ǰ��������
        $theme = $this->getTemplateTheme();

        // ��ȡ��ǰģ��
        $module   =  MODULE_NAME;
        if(strpos($template,'@')){ // ��ģ�����ģ���ļ�
            list($module,$template)  =   explode('@',$template);
        }
        // ��ȡ��ǰ�����ģ��·��
        if(!defined('THEME_PATH')){
            define('THEME_PATH', C('VIEW_PATH')? C('VIEW_PATH').$theme : APP_PATH.$module.'/'.C('DEFAULT_V_LAYER').'/'.$theme);
        }

        // ����ģ���ļ�����
        if('' == $template) {
            // ���ģ���ļ���Ϊ�� ����Ĭ�Ϲ���λ
            $template = CONTROLLER_NAME . $depr . ACTION_NAME;
        }elseif(false === strpos($template, $depr)){
            $template = CONTROLLER_NAME . $depr . $template;
        }
        $file   =   THEME_PATH.$template.C('TMPL_TEMPLATE_SUFFIX');
        if(C('TMPL_LOAD_DEFAULTTHEME') && THEME_NAME != C('DEFAULT_THEME') && !is_file($file)){
            // �Ҳ�����ǰ����ģ���ʱ��λĬ�������е�ģ��
            $file   =   dirname(THEME_PATH).'/'.C('DEFAULT_THEME').'/'.$template.C('TMPL_TEMPLATE_SUFFIX');
        }
        return $file;
    }

    /**
     * ���õ�ǰ�����ģ������
     * @access public
     * @param  mixed $theme ��������
     * @return View
     */
    public function theme($theme){
        $this->theme = $theme;
        return $this;
    }

    /**
     * ��ȡ��ǰ��ģ������
     * @access private
     * @return string
     */
    private function getTemplateTheme() {
        if($this->theme) { // ָ��ģ������
            $theme = $this->theme;
        }else{
            /* ��ȡģ���������� */
            $theme =  C('DEFAULT_THEME');
            if(C('TMPL_DETECT_THEME')) {// �Զ����ģ������
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])){
                    $theme = $_GET[$t];
                }elseif(cookie('think_template')){
                    $theme = cookie('think_template');
                }
                if(!in_array($theme,explode(',',C('THEME_LIST')))){
                    $theme =  C('DEFAULT_THEME');
                }
                cookie('think_template',$theme,864000);
            }
        }
        defined('THEME_NAME') || define('THEME_NAME',   $theme);                  // ��ǰģ����������
        return $theme?$theme . '/':'';
    }

}
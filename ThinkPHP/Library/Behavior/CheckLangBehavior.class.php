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
namespace Behavior;
/**
 * ���Լ�� ���Զ��������԰�
 */
class CheckLangBehavior {

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        // �������
        $this->checkLanguage();
    }

    /**
     * ���Լ��
     * ��������֧�����ԣ����Զ��������԰�
     * @access private
     * @return void
     */
    private function checkLanguage() {
        // ���������԰����ܣ��������ؿ�������ļ�ֱ�ӷ���
        if (!C('LANG_SWITCH_ON',null,false)){
            return;
        }
        $langSet = C('DEFAULT_LANG');
        $varLang =  C('VAR_LANGUAGE',null,'l');
        $langList = C('LANG_LIST',null,'zh-cn');
        // ���������԰�����
        // �����Ƿ������Զ�������û�ȡ����ѡ��
        if (C('LANG_AUTO_DETECT',null,true)){
            if(isset($_GET[$varLang])){
                $langSet = $_GET[$varLang];// url�����������Ա���
                cookie('think_language',$langSet,3600);
            }elseif(cookie('think_language')){// ��ȡ�ϴ��û���ѡ��
                $langSet = cookie('think_language');
            }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){// �Զ�������������
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                cookie('think_language',$langSet,3600);
            }
            if(false === stripos($langList,$langSet)) { // �Ƿ����Բ���
                $langSet = C('DEFAULT_LANG');
            }
        }
        // ���嵱ǰ����
        define('LANG_SET',strtolower($langSet));

        // ��ȡ������԰�
        $file   =   THINK_PATH.'Lang/'.LANG_SET.'.php';
        if(LANG_SET != C('DEFAULT_LANG') && is_file($file))
            L(include $file);

        // ��ȡӦ�ù������԰�
        $file   =  LANG_PATH.LANG_SET.'.php';
        if(is_file($file))
            L(include $file);
        
        // ��ȡģ�����԰�
        $file   =   MODULE_PATH.'Lang/'.LANG_SET.'.php';
        if(is_file($file))
            L(include $file);

        // ��ȡ��ǰ���������԰�
        $file   =   MODULE_PATH.'Lang/'.LANG_SET.'/'.strtolower(CONTROLLER_NAME).'.php';
        if (is_file($file))
            L(include $file);
    }
}

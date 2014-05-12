<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
/**
 * ϵͳ��Ϊ��չ������������
 */
class TokenBuildBehavior {

    public function run(&$content){
        if(C('TOKEN_ON')) {
            list($tokenName,$tokenKey,$tokenValue)=$this->getToken();
            $input_token = '<input type="hidden" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
            $meta_token = '<meta name="'.$tokenName.'" content="'.$tokenKey.'_'.$tokenValue.'" />';
            if(strpos($content,'{__TOKEN__}')) {
                // ָ��������������λ��
                $content = str_replace('{__TOKEN__}',$input_token,$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
                // �������ɱ�����������
                $content = str_replace($match[0],$input_token.$match[0],$content);
            }
            $content = str_ireplace('</head>',$meta_token.'</head>',$content);
        }else{
            $content = str_replace('{__TOKEN__}','',$content);
        }
    }

    //���token
    private function getToken(){
        $tokenName  = C('TOKEN_NAME',null,'__hash__');
        $tokenType  = C('TOKEN_TYPE',null,'md5');
        if(!isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName]  = array();
        }
        // ��ʶ��ǰҳ��Ψһ��
        $tokenKey   =  md5($_SERVER['REQUEST_URI']);
        if(isset($_SESSION[$tokenName][$tokenKey])) {// ��ͬҳ�治�ظ�����session
            $tokenValue = $_SESSION[$tokenName][$tokenKey];
        }else{
            $tokenValue = $tokenType(microtime(TRUE));
            $_SESSION[$tokenName][$tokenKey]   =  $tokenValue;
            if(IS_AJAX && C('TOKEN_RESET',null,true))
                header($tokenName.': '.$tokenKey.'_'.$tokenValue); //ajax��Ҫ������header���滻ҳ����meta�е�tokenֵ
        }
        return array($tokenName,$tokenKey,$tokenValue); 
    }
}
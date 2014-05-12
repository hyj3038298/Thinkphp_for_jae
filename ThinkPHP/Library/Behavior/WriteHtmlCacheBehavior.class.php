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
namespace Behavior;
use Think\Storage;
/**
 * ϵͳ��Ϊ��չ����̬����д��
 */
class WriteHtmlCacheBehavior {

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$content){
        if(C('HTML_CACHE_ON') && defined('HTML_FILE_NAME'))  {
            //��̬�ļ�д��
            Storage::put(HTML_FILE_NAME , $content,'html');
        }
    }
}
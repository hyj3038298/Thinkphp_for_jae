<?php

namespace Think\Template\Driver;
use Think\Template;
class JAESmarty{
	
	private	$smarty = new \Smarty();
	
	public function fetch($templateFile,$var){
		if(!is_file($templateFile)){
			echo $templateFile;
			return;
		}
		foreach ($var as $k => $v) {
            $this->smarty->assign($k, $v);
        }
        echo $this->smarty->fetch($templateFile);	
	}
}

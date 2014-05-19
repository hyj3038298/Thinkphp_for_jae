<?php

namespace Think\Template\Driver;
use Think\Template;
class JAESmarty{
	
	private	$smarty = new \Smarty();
	
	public function fetch($templateFile,$var){
		echo ("templateFile is". $templateFile);
		$this->smarty->fetch($templateFile);
        foreach ($var as $k => $v) {
            $this->smarty->assign($k, $v);
        }
        echo $this->smarty->fetch($templateFile);	
	}
}

<?php
namespace Think\Template\Driver;
use Think\Template;
class JAESmarty extends Smarty{
	//private $smarty = new smarty();
	public function fetch(){
		echo "asdf";
		parent::fetch();	
	}
	$smarty = new Smarty();
    foreach ($variables as $k => $v) {
        $smarty->assign($k, $v);
    }
}
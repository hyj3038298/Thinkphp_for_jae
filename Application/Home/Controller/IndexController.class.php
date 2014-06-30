<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>[ 您现在访问的是Home模块的Index控制器 ]</div><script type="text/javascript" src="/Public/debug.js"></script>','utf-8');
    }
    public function test(){
    	echo "in test";
    }
	
	 public function dbdemo(){
        // 这是插入语句数组中的值也是空
        $i = M('Auth')->add(array('username' => 'weide1'));
        //echo M()->getLastsql();
        // 这是产出语句，where打印出来是没值的
        $j = M('Auth')->where(array('username' => 'weide'))->delete();
        //echo M()->getLastsql();
        //dump($i);
        $data = M('Auth')->where(array("username"	=>	"weide1"))->find();
		print_r(M()->getLastSql());
        dump($data);
    }

    public function foo(){
        $mod = new \Think\Model();
        $r = $mod->query("show databases");
        
        $mod = D("user");
        $mod->query("create table test(id int(11))");
        print_r($mod->query("show tables"));
        //print_r($mod);
        print_r($mod->where("1")->count());
        $d = array("sadf", 123);
        $this->assign("data", $d);
        $this->assign("foo", "canyou see this");
        var_dump(S("jason", "sunan"));
        var_dump(S("jason"));
        $this->display();

    }

    public function js(){
    	echo "function test(){alert();}";
    }
}
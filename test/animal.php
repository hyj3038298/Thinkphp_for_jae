<?php
class Animal {
	static private  $handler;

	public static function birth(){
		echo "try to birth ";
		self::$handler = new people();
	}
	public static function staticfun(){
		echo "staticfun";
	}
	static public function __callStatic($method,$args){
        //���û��������ķ���
        print_r(self::$handler);
        echo " is trying to execute static method: $method";
        var_dump(method_exists(self::$handler, $method));
        if(method_exists(self::$handler, $method)){
           return call_user_func_array(array(self::$handler,$method), $args);
        }
    } 
}
<?php
namespace Jason;
class people extends Animal{
	public static function speak(){
		echo "Yes, I can speak";
		Animal::test();
	}
	public static function test(){
		echo "I can test too";
	}
}
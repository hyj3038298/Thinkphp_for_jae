<?php
function appError($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
      case E_ERROR:
      case E_PARSE:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
        ob_end_clean();
        $errorStr = "$errstr ".$errfile." 第 $errline 行.";
        echo ($errstr);
        break;
      default:
        $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
        echo "NOTICE:" . $errstr;
        break;
  }
}

// 致命错误捕获
function fatalError() {
    if ($e = error_get_last()) {
        switch($e['type']){
          case E_ERROR:
          case E_PARSE:
          case E_CORE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:  
            ob_end_clean();
            echo "<p>".$e['message']. " " .$e['file']." on ".$e['line']."</p>";
            break;
        }
    }
}

register_shutdown_function('fatalError');
set_error_handler('appError');


include "animal.php";
include "people.php";


animal::birth();
animal::speak();

?>
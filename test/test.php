<?php
function appError($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
      case E_ERROR:
      case E_PARSE:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
        ob_end_clean();
        $errorStr = "$errstr ".$errfile." �� $errline ��.";
        echo ($errstr);
        break;
      default:
        $errorStr = "[$errno] $errstr ".$errfile." �� $errline ��.";
        echo "NOTICE:" . $errstr;
        break;
  }
}

// �������󲶻�
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
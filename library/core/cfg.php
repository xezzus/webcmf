<?php
namespace core;

class cfg {

  static private $cfg;

  static function __callStatic($name,$arg){
    if(is_null(self::$cfg)){
      $cfg[0] = __DIR__.'/../../config/config.default.php';
      if(is_file($cfg[0])) $cfg[0] = require_once($cfg[0]);
      else $cfg[0] = [];
      $cfg[1] = __DIR__.'/../../config/config.php';
      if(is_file($cfg[1])) $cfg[1] = require_once($cfg[1]);
      else $cfg[1] = [];
      if(!is_array($cfg[1])) $cfg[1] = [];
      self::$cfg = array_replace_recursive($cfg[0],$cfg[1]);
    } 
    if(isset(self::$cfg[$name])) {
      if(isset($arg[1])) return self::$cfg[$name][$arg[0]][$arg[1]];
      elseif(isset($arg[0])) return self::$cfg[$name][$arg[0]];
      else return self::$cfg[$name];
    }
  }

  private function __clone(){}
  private function __wakeup(){}

}
?>

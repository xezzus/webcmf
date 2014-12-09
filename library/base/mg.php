<?php 
namespace base;

use core\cfg;

class mg {

  static private $dbs;
  static private $shard = 0;

  public function __construct($num){
    self::$shard = $num;
  }

  public static function __callStatic($name,$argv){
    return self::instance($name,$argv);
  }

  public function __call($name,$argv){
    return self::instance($name,$argv);
  }

  public static function instance($name,$argv){
    if(!isset(self::$dbs[self::$shard])){
      $m = new \MongoClient('mongodb://'.cfg::mg('user').':'.cfg::mg('pass').'@'.cfg::mg('host').'/'.cfg::mg('base'));
      self::$dbs[self::$shard] = $m->selectDB(cfg::mg('base'));
    }
    return call_user_func_array([self::$dbs[self::$shard],$name],$argv);
  }

  public static function shard($num){
    return new mg($num);
  }

  private function __clone(){}
  private function __wakeup(){}
}
?>

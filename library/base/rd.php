<?php 
namespace base;

use core\cfg;
use \Redis;


class rd {

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
      self::$dbs[self::$shard] = new Redis();
      self::$dbs[self::$shard]->connect(cfg::rd(self::$shard)['host'],cfg::rd(self::$shard)['port']);
      self::$dbs[self::$shard]->auth(cfg::rd(self::$shard)['auth']);
    }
    return call_user_func_array([self::$dbs[self::$shard],$name],$argv);
  }

  public static function shard($num){
    return new rd($num);
  }

  private function __clone(){}
  private function __wakeup(){}
}
?>

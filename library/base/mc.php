<?php 
namespace base;

use core\cfg;
use \Memcache;

class mc {

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
      self::$dbs[self::$shard] = new Memcache();
      self::$dbs[self::$shard]->addServer(cfg::mc(self::$shard)['host'],cfg::mc(self::$shard)['port'],cfg::mc(self::$shard)['weight']);
    }
    return call_user_func_array([self::$dbs[self::$shard],$name],$argv);
  }

  public static function shard($num){
    return new mc($num);
  }

  private function __clone(){}
  private function __wakeup(){}
}
?>

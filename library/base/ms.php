<?php 
namespace base;

use \PDO;
use core\cfg;

class ms {

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
      self::$dbs[self::$shard] = new PDO('mysql:host='.cfg::pg(self::$shard)['host'].';dbname='.cfg::pg(self::$shard)['base'],cfg::pg(self::$shard)['user'],cfg::pg(self::$shard)['pass'],array(PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8',PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC));
    }
    return call_user_func_array([self::$dbs[self::$shard],$name],$argv);
  }

  public static function shard($num){
    return new ms($num);
  }

  private function __clone(){}
  private function __wakeup(){}
}
?>

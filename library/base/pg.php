<?php 
namespace base;

use \PDO;
use core\cfg;

class pg {

  static private $dbs;
  static private $shard = 0;
  static private $fetch = [];
  static private $fetchAll = [];

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
      self::$dbs[self::$shard] = new PDO('pgsql:dbname='.cfg::pg(self::$shard)['base'].';host='.cfg::pg(self::$shard)['host'].';port='.cfg::pg(self::$shard)['port'].';',cfg::pg(self::$shard)['user'],cfg::pg(self::$shard)['pass']);
      self::$dbs[self::$shard]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return call_user_func_array([self::$dbs[self::$shard],$name],$argv);
  }

  public static function shard($num){
    return new pg($num);
  }

  public static function fetch($sql){
    $uniq = sha1($sql);
    if(!isset(self::$fetch[$uniq])){
      self::$fetch[$uniq] = self::query($sql)->fetch();
    }
    return self::$fetch[$uniq];
  }

  public static function fetchAll($sql){
    $uniq = sha1($sql);
    if(!isset(self::$fetchAll[$uniq])){
      self::$fetchAll[$uniq] = self::query($sql)->fetchAll();
    }
    return self::$fetchAll[$uniq];
  }

  public function execute($sql){
    return self::exec($sql);
  }

  private function __clone(){}
  private function __wakeup(){}
}
?>

<?php
namespace core;

class factoryInvoke {

  public $namespace;

  public function __construct($namespace=null){
    $this->namespace = $namespace;
  }

  public function __get($namespace){
    return new factoryInvoke($namespace);
  }

  public function __call($name,$value){
    $class = "{$this->namespace}\\$name";
    $object = new $class;
    return call_user_func_array($object,$value);
  }

}
?>

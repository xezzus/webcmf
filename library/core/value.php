<?php
namespace core;

class value {

  public function __construct($value){
    foreach($value as $param=>$value){ $this->{$param} = $value; }
  }

  public function __get($name){
    return "::$name::";
  }

}
?>

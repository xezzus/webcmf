<?php
namespace core;

class load {

  private $___view = [];
  private $___apps = [];

  public function __get($name){
    $this->___view[$name] = $name;
    $file = $this->___findView($name); if(is_file($file)) require($file);
  }

  public function __call($name,$value){
    $this->___apps[$name] = $name;
    if(empty($value)) $value[0] = [];
    # filter
    $value = $this->___filter($value[0]);
    # call
    $appsFile = __DIR__."/../apps/$name.php";
    if(is_file($appsFile)){
      $appsName = "apps\\".$name;
      $apps = new $appsName;
      if(is_callable($apps)) { 
        if(is_array($value)){
          $value = call_user_func_array($apps,$value);
        } else {
          $value = call_user_func($apps,$value);
        }
      }
    }

    # include
    $file = __DIR__."/../../public/apps/$name/index.phtml";
    if(is_file($file)) {
      if(is_array($value)) $value = new value($value);
      ob_start();
      require($file);
      $require = ob_get_contents();
      ob_end_flush();
      return $require;
    } else {
      return $value;
    }
  }

  public function ___filter($value){
    if(is_array($value)){
      $filter = __DIR__."/../../config/filter.php";
      if(!is_file($filter)) die('{"err":"cannot find filter file"}');
      $filter = require($filter);
      foreach($value as $param=>$v){
       $continue = false;
       if(isset($filter[$param])) {
         if(preg_match("/{$filter[$param]}/",$v)) $continue = true;
       }
       if($continue === true) {
         $value[$param] = $v;
         continue;
       }
       die('{"err":"cannot validate: view-'.$name.', param-'.$param.'"}');
      }
    }
    return $value;
  }

  public function ___createSrc($type,$local=null){
    foreach($this->___view as $view){
      $file = $this->___findView($view,$type);
      if(!is_file($file)) continue;
      $js['view'][$view] = $view;
    }
    foreach($this->___apps as $apps){
      $file = $this->___findApps($apps,$type);
      if(!is_file($file)) continue;
      $js['apps'][$apps] = $apps;
    }
    if(isset($js['view'])) $src[] = 'view='.implode(',',$js['view']);
    if(isset($js['apps'])) $src[] = 'apps='.implode(',',$js['apps']);
    if(!empty($src) && !empty($src) && !empty($local)) $src[] = 'local';
    if(isset($src)){
      $src = '?'.implode('&',$src);
    } else {
      $src = '';
    }
    return $src;
  }

  public function ___page(){
    $file = $this->___findView();
    if(is_file($file)) {
      ob_start();
      require($file);
      $contents = ob_get_contents();
      ob_end_clean();
      $srcJs = $this->___createSrc('js');
      $srcCss = $this->___createSrc('css');
      echo preg_replace("/\<\/HEAD\>/i","\n\t<link charset='UTF-8' media='screen' rel='stylesheet' type='text/css' href='/index.css{$srcCss}' />\n\t<script src='/index.js{$srcJs}'></script>\n</head>",$contents);
    }
  }

  public function ___view($name){
    $file = $this->___findView($name);
    if(is_file($file)) require($file);
  }

  public function ___findView($name=null,$type='phtml',$path=null){
    $dir = __DIR__.'/../../public/'.((is_null($name)) ? 'page' : "view/$name")."/";
    if(is_null($path)) $path = URLPATH;
    $route = __DIR__.'/../../config/route.php';
    if(is_file($route)) $route = require($route);
    else $route = [];
    foreach($route as $find=>$value){
      if(preg_match("/$find/",$path)) { $path = $value; break; }
    }
    $path = explode('/',$path);
    foreach($path as $value){
      $value = implode('/',$path);
      if(!empty($name)) $fileIndex = $dir.$value.'/index';
      else $fileIndex = $dir.$value;
      if(is_file("$fileIndex.phtml")) { $file = "$fileIndex.$type"; return $file; }
      array_pop($path);
    }
    if(!isset($file) || !is_file($file)) $file = $dir."/index.$type";
    if(is_file($file)) return $file;
    return null;
  }

  public function ___findApps($name=null,$type='phtml'){
    $dir = __DIR__."/../../public/apps/$name/";
    return $dir."/index.$type";
  }

}

?>

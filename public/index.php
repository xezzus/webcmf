<?php
# header
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');

# load
require_once(__DIR__.'/../load.php');

# pathname
$pathname = explode('/',$_SERVER['REQUEST_URI']);
$pathname = array_filter($pathname,function($val){ if(!empty(trim($val))) return $val; });
$pathname = implode('/',$pathname);
$pathname = parse_url($pathname);
if(isset($pathname['path'])){
  $pathname = $pathname['path'];
  $pathname = explode('.',$pathname)[0];
  $pathname = explode('/',$pathname);
  foreach($pathname as $k=>$v){
    $v = trim($v);
    if(empty($v)) unset($pathname[$k]);
  }
  $pathname = implode('/',$pathname);
} else {
  $pathname = '';
}
define('pathname',$pathname);
unset($pathname,$k,$v,$val);

# pathname to referer
if(isset($_SERVER['HTTP_REFERER'])) $pathname = parse_url($_SERVER['HTTP_REFERER'])['path'];
else $pathname = '';

# JavaScript
if(parse_url($_SERVER['REQUEST_URI'])['path'] == '/index.js'){

  $contents = '';
  if(!isset($_GET['local'])){
    # include global js
    foreach(glob(__DIR__.'/js/*.js') as $file){
      $contents .= file_get_contents($file).';';
    }
    # include page js
    $file = core\load::___findView(null,'js',$pathname);
    if(is_file($file)) $contents .= file_get_contents($file).';';
  }
  # include apps js
  if(isset($_GET['apps'])){
    foreach(explode(',',$_GET['apps']) as $apps){
      $file = core\load::___findApps($apps,'js');
      if(is_file($file)) $contents .= file_get_contents($file).';';
    }
  }
  # include view js
  if(isset($_GET['view'])){
    foreach(explode(',',$_GET['view']) as $view){
      $file = core\load::___findView($view,'js',$pathname);
      if(is_file($file)) $contents .= file_get_contents($file).';';
    }
  }
  header('Content-Type: text/javascript');
  echo $contents;

exit;
}

# CSS
if(parse_url($_SERVER['REQUEST_URI'])['path'] == '/index.css'){
  $contents = '';
  if(!isset($_GET['local'])){
    # include global css
    foreach(glob(__DIR__.'/css/*.css') as $file){
      $contents .= file_get_contents($file);
    }
    # include page js
    $file = core\load::___findView(null,'css',$pathname);
    if(is_file($file)) $contents .= file_get_contents($file);
  }
  # include apps css
  if(isset($_GET['apps'])){
    foreach(explode(',',$_GET['apps']) as $apps){
      $file = core\load::___findApps($apps,'css');
      if(is_file($file)) $contents .= file_get_contents($file);
    }
  }
  # include view css
  if(isset($_GET['view'])){
    foreach(explode(',',$_GET['view']) as $view){
      $file = core\load::___findView($view,'css',$pathname);
      if(is_file($file)) $contents .= file_get_contents($file);
    }
  }
  header('Content-type: text/css');
  echo $contents;

exit;
}

# Load
$load = new core\load;
$input = file_get_contents('php://input');
$input = json_decode($input,1);
switch($_SERVER['HTTP_ACCEPT']){
  case "application/apps":
    header('Content-type: application/json');
    foreach($input as $appsName=>$appsValue){
      ob_start();
      $echo[$appsName] = $load->{$appsName}($appsValue);
      $require = ob_get_contents();
      ob_end_clean();
      if(!empty($require)) $echo[$appsName] = $require;
    }
    die(json_encode($echo));
  break;
  case "application/view":
    header('Content-type: application/json');
    foreach($input as $view=>$value){
      ob_start();
      $load->___view($view);
      $return[$view] = ob_get_contents();
      ob_end_clean();
    }
    $js = $load->___createSrc('js',true);
    $css = $load->___createSrc('css',true);
    if(isset($return)) die(json_encode(['view'=>$return,'js'=>$js,'css'=>$css]));
  break;
  default:
    header('Content-type: text/html');
    $load->___page();
}

?>

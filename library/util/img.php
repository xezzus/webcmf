<?php
namespace util;

class img {
  private $img;
  private $src;
  private $new;
  private $type;
  private $width;
  private $height;

  public function __construct($img){
    if(!is_file($img)) die('{"err":"1","msg":"core\img cannot find file"}');
    list($this->width,$this->height,$this->type) = getimagesize($img);
    switch($this->type){
      case IMAGETYPE_PNG:
        $this->src = imagecreatefrompng($img);
      break;
      case IMAGETYPE_JPEG:
        $this->src = imagecreatefromjpeg($img);
      break;
      case IMAGETYPE_JPEG2000:
        $this->src = imagecreatefromjpeg($img);
      break;
    }
    $this->img = $img;
  }

  public function __destruct(){
    if(is_resource($this->src)) imagedestroy($this->src);
  }

  # Resize only horizontal
  public function resize_x($width){
    $height = $this->height*$width/$this->width;
    $this->new = imagecreatetruecolor($width,$height);
    imagecopyresampled($this->new, $this->src, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
  }

  # Save file
  public function out($type=IMAGETYPE_PNG,$file=false){
    if(is_resource($this->new)) {
      if($file === false) $file = $this->img;
      switch($type){
        case IMAGETYPE_JPEG:
          imagejpeg($this->new,$file);
        break;
        default:
          imagepng($this->new,$file);
      }
      imagedestroy($this->new);
    }
  }

  public static function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return $rgb;
  }
}
?>

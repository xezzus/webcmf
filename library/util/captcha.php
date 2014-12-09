<?php
namespace util;

class captcha {

  /* Show Captcha Image */
  public static function get($opt) {
    $font = $opt['font'];
    if(is_file($font) && !file_exists($font)) return array('err'=>1,'msg'=>'cannot font for captcha');
    $im = imagecreate($opt['width'], $opt['height']);
    $color_bg = img::hex2rgb($opt['color_bg']);
    $color_bg = ImageColorAllocateAlpha($im, $color_bg[0], $color_bg[1], $color_bg[2], 0);
    $color_tx = img::hex2rgb($opt['color_tx']);
    $color_tx = imagecolorallocate($im, $color_tx[0], $color_tx[1], $color_tx[2]);
    ImageFill($im, 0, 0, $color_bg);
    # code
    $range = $opt['range'];
    $count = count($range)-1;
    for($i=1;$i<=$opt['length'];$i++){ $code[] = $range[rand(0,$count)]; }
    $code = implode('',$code);
    # ---
    imagettftext($im, $opt['size'], $opt['turn'], $opt['left'], $opt['top'], $color_tx, $font, $code);
    return array('code'=>$code,'img'=>$im);
  }

  private static function code($opt) {
    return $code;
  }
}
?>

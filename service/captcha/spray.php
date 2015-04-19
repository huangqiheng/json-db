<?php
session_start();
header ("Content-type: image/png");

$bk_color = array(242, 239, 220);
$fg_color = array(50, 50, 50);

$str = "2,3,4,7,8,9,A,C,D,E,H,K,M,N,P,R,T,V,W,X,Y";      //要显示的字符，可自己进行增删
$list = explode(",", $str);
$cmax = count($list) - 1;
$code = '';
for ( $i=0; $i < 4; $i++ ){
      $randnum = mt_rand(0, $cmax);
      $code .= $list[$randnum];           //取出字符，组合成为我们要的验证码字符
}

$_SESSION['captcha'] = $code;

$im = @imagecreate (230, 80) or die ("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate ($im, $bk_color[0], $bk_color[1], $bk_color[2]);
$color = imagecolorallocate ($im, $fg_color[0],$fg_color[1],$fg_color[2]);

$ylist = @array(400);
$current = 0;
$d = 1;
for($j = 0; $j < 400; $j++) {

   if($current>10) {
      $d = -1;
   } else if($current <-10) {
      $d = 1;
   } else if(mt_rand(0, 400) < 200) {
      $d = -$d;
   }
   if(mt_rand(0, 400) < 350) {
      $current = $current + $d;
   }

   $ylist[$j] = $current;
}

function imagecharx($img, $char, $x0, $y0,$ylist)
{
    global $bk_color,$fg_color;
    $da = @imagecreate (10, 20) or die ("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate ($da, $bk_color[0], $bk_color[1], $bk_color[2]);
    $text_color = imagecolorallocate ($da, $fg_color[0],$fg_color[1],$fg_color[2]);
    $color = imagecolorallocate ($img, $fg_color[0],$fg_color[1],$fg_color[2]);
    $arg = rand(0,18)/100.0 * pi();
    imagestring($da, 18, 0, 0, $char, $text_color);
    for($i = 0; $i < 200; $i++) {
        $y = @floor($i/10);
        $x = $i%10;
        $point_color = imagecolorat($da,$x,$y);
        
        if($point_color == $text_color){
            for($j = 0; $j < 12; $j++) {
                $dx = 0; $dy = 0;
                $p = 6;
                for($s = 0; $s < $p; $s++) {
                    $dx += rand(0, 1000/$p)/100;
                    $dy += rand(0, 1000/$p)/100;
                }
                $xx = $x*5+$dx - 25;
                $yy = $y*5+$dy - 50;
                
                $x1 = cos($arg)*$xx - sin($arg)*$yy + 25;
		            $y1 = sin($arg)*$xx + cos($arg)*$yy + 50;
                
                imagesetpixel($img,$x0+$x1,$y0+$y1,$color);
            }
        }
    }
    imagedestroy($da);
}
for($j = 0; $j < 800; $j++) {
     $rx = mt_rand(0, 400);
     $ry = mt_rand(0, 100);
     imagesetpixel($im,$rx,$ry,$color);
}
for ( $i=0; $i < 7; $i++ ){
    imagecharx($im,substr($code,$i,1),$i*43+25,0,$ylist);
}

$current = 0;
$d = 1;
for($j = 0; $j < 300; $j+=10) {

   if($current>30) {
      $d = -1;
   } else if($current <-30) {
      $d = 1;
   } else if(mt_rand(0, 400) < 70) {
      $d = -$d;
   }
   if(mt_rand(0, 400) < 380) {
      $current = $current + $d;
   }

   for($l = 0; $l < 12; $l++) {
       $dx = 0; $dy = 0;
       $p = 2;
       for($s = 0; $s < $p; $s++) {
            $dx += rand(0, 1000/$p)/100;
            $dy += rand(0, 1000/$p)/100;
       }
                
       imagesetpixel($im,$j+$dy,15+$dx+$current,$color);
   }
}

$current = 0;
$d = 1;
for($j = 0; $j < 300; $j+=10) {

   if($current>30) {
      $d = -1;
   } else if($current <-30) {
      $d = 1;
   } else if(mt_rand(0, 400) < 70) {
      $d = -$d;
   }
   if(mt_rand(0, 400) < 280) {
      $current = $current + $d;
   }

   for($l = 0; $l < 12; $l++) {
       $dx = 0; $dy = 0;
       $p = 2;
       for($s = 0; $s < $p; $s++) {
            $dx += rand(0, 1000/$p)/100;
            $dy += rand(0, 1000/$p)/100;
       }
                
       imagesetpixel($im,$j+$dy,45+$dx+$current,$color);
   }
}

imagepng ($im);
imagedestroy ($im);
?>

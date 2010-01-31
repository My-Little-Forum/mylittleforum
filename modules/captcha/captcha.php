<?php
/*******************************************************************************
* PHP CAPTCHA class (C) 2008 alex@mylittlehomepage.net                         *
* http://mylittlehomepage.net/                                                 *
*******************************************************************************/

/*******************************************************************************
* This program is free software: you can redistribute it and/or modify         *
* it under the terms of the GNU General Public License as published by         *
* the Free Software Foundation, either version 3 of the License, or            *
* (at your option) any later version.                                          *
*                                                                              *
* This program is distributed in the hope that it will be useful,              *
* but WITHOUT ANY WARRANTY; without even the implied warranty of               *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
* GNU General Public License for more details.                                 *
*                                                                              *
* You should have received a copy of the GNU General Public License            *
* along with this program.  If not, see <http://www.gnu.org/licenses/>.        *
*******************************************************************************/

class Captcha
 {
  function check_captcha($code,$entered_code)
   {
    if(strtolower($entered_code) == strtolower($code)) return true;
    else return false;
   }

  function generate_code($letters='abcdefhjkmnpqrstuvwxyz234568')
   {
    mt_srand((double)microtime()*1000000);
    $code='';
    for($i=0;$i<5;$i++)
     {
      $code.=substr($letters,mt_rand(0,strlen($letters)-1),1);
     }
    return $code;
   }

  function generate_image($code,$backgrounds_folder='',$fonts_folder='')
   {
    $font_size = 23;
    $font_pos_x = 10;
    $font_pos_y = 30;

    // get background images:
    if($backgrounds_folder!='')
     {
      $handle=opendir($backgrounds_folder);
      while ($file = readdir($handle))
       {
        if(preg_match('/\.png$/i', $file) || preg_match('/\.gif$/i', $file) || preg_match('/\.jpg$/i', $file)) $backgrounds[] = $file;
       }
      closedir($handle);
     }

    // get fonts:
    if($fonts_folder!='')
     {
      $handle=opendir($fonts_folder);
      while($file = readdir($handle))
       {
        if(preg_match('/\.ttf$/i', $file)) $fonts[] = $file;
       }
      closedir($handle);
     }

    // split code into chars:
    $code_length = strlen($code);
      for($i=0;$i<$code_length;$i++)
       {
        $code_chars_array[] = substr($code,$i,1);
       }

    // if background images are available, craete image from one of them:
    if(isset($backgrounds))
     {
      $bg = $backgrounds[mt_rand(0,count($backgrounds)-1)];
      if(preg_match('/\.png$/i', $bg)) $im = ImageCreateFromPNG($backgrounds_folder.$bg);
      elseif(preg_match('/\.gif$/i', $bg)) $im = ImageCreateFromGIF($backgrounds_folder.$bg);
      else $im = ImageCreateFromJPEG($backgrounds_folder.$bg);
      if(function_exists('imagerotate') && mt_rand(0,1)==1) $im = imagerotate($im, 180, 0);
     }
    // if not, create an empty image:
    else
     {
      $im = ImageCreate(180, 40);
      $background_color = ImageColorAllocate ($im, 234, 234, 234);
     }

    // set text color:
    $text_color = ImageColorAllocate ($im, 0, 0, 0);

    // use fonts, if available:
    if(isset($fonts))
     {
      foreach($code_chars_array as $char)
       {
        $angle = intval(rand((30 * -1), 30));
        ImageTTFText($im, $font_size, $angle, $font_pos_x, $font_pos_y, $text_color, $fonts_folder.$fonts[mt_rand(0,count($fonts)-1)],$char);
        $font_pos_x=$font_pos_x+($font_size+13);
       }
     }
    // if not, use internal font:
    else
     {
      ImageString($im, 5, 30, 10, $code, $text_color);
     }
    header("Expires: Expires: Sat, 20 Oct 2007 00:00:00 GMT");
    header("Cache-Control: max-age=0");
    header("Content-type: image/png");
    ImagePNG($im);
    exit();
   }

  function generate_dummy_image()
   {
    $im = @ImageCreate(180, 40);
    $background_color = ImageColorAllocate ($im, 234, 234, 234);
    $text_color = ImageColorAllocate ($im, 0, 0, 0);
    #ImageString($im, 3, 7, 4, 'CAPTCHA not available', $text_color);
    header("Expires: Expires: Sat, 20 Oct 2007 00:00:00 GMT");
    header("Cache-Control: max-age=0");
    header("Content-type: image/png");
    ImagePNG($im);
   }

  // for math CAPTCHA:
  function generate_math_captcha($number1from=1,$number1to=10,$number2from=0,$number2to=10)
   {
    $number[0] = rand($number1from,$number1to);
    $number[1] = rand($number2from,$number2to);
    $number[2] = $number[0] + $number[1];
    return $number;
   }

  function check_math_captcha($result, $entered_result)
   {
    if(intval($result) == intval($entered_result)) return true;
    else return false;
   }
 }
?>

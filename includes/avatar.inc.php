<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

// upload folder:
$uploaded_images_path = 'images/avatars/';

if($settings['avatars']>0 && isset($_SESSION[$settings['session_prefix'].'user_id']))
 {
  if(isset($_GET['delete']))
   {
    if(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.jpg'))
     {
      @chmod($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.jpg', 0777);
      @unlink($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.jpg');
     }
    if(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.png'))
     {
      @chmod($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.png', 0777);
      @unlink($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.png');
     }
    if(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.gif'))
     {
      @chmod($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.gif', 0777);
      @unlink($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.gif');
     }
    header('Location: index.php?mode=avatar&deleted=true');
    exit;
   }

  if(isset($_FILES['probe']) && $_FILES['probe']['size'] != 0 && !$_FILES['probe']['error'])
   {
    unset($errors);
    $image_info = getimagesize($_FILES['probe']['tmp_name']);

    if(!is_array($image_info) || $image_info[2] != 1 && $image_info[2] != 2 && $image_info[2] != 3) $errors[] = 'invalid_file_format';

    if(empty($errors))
     {
      if($_FILES['probe']['size'] > $settings['avatar_max_filesize']*1000 || $image_info[0] > $settings['avatar_max_width'] || $image_info[1] > $settings['avatar_max_height'])
       {
        #$compression = 10;
        $width=$image_info[0];
        $height=$image_info[1];

        // resize if too large:
        if($width != $settings['avatar_max_width'] || $height != $settings['avatar_max_height'])
         {
          if($width >= $height)
           {
            $new_width = $settings['avatar_max_width'];
            $new_height = intval($height*$new_width/$width);
           }
          else
           {
            $new_height = $settings['avatar_max_height'];
            $new_width = intval($width*$new_height/$height);
           }
          #$new_width = $settings['avatar_max_width'];
          #$new_height = $settings['avatar_max_height'];
         }
        else
         {
          $new_width=$width;
          $new_height=$height;
         }

        $img_tmp_name = uniqid(rand()).'.tmp';

        for($compression = 100; $compression>1; $compression=$compression-10)
         {
          if(!resize_image($_FILES['probe']['tmp_name'], $uploaded_images_path.$img_tmp_name, $new_width, $new_height, $compression))
           {
            $file_size = $_FILES['probe']['size']; // @filesize($_FILES['probe']['tmp_name']);
            break;
           }
          $file_size = @filesize($uploaded_images_path.$img_tmp_name);
          if($image_info[2]!=2 && $file_size > $settings['avatar_max_filesize']*1000) break;
          if($file_size <= $settings['avatar_max_filesize']*1000) break;
         }
        if($file_size > $settings['avatar_max_filesize']*1000)
         {
          $smarty->assign('width',$image_info[0]);
          $smarty->assign('height',$image_info[1]);
          $smarty->assign('filesize',number_format($_FILES['probe']['size']/1000,0,',',''));
          $smarty->assign('max_width',$settings['avatar_max_width']);
          $smarty->assign('max_height',$settings['avatar_max_height']);
          $smarty->assign('max_filesize',$settings['avatar_max_filesize']);
          $errors[] = 'file_too_large';
         }
        if(isset($errors))
         {
          if(file_exists($uploaded_images_path.$img_tmp_name))
           {
            @chmod($uploaded_images_path.$img_tmp_name, 0777);
            @unlink($uploaded_images_path.$img_tmp_name);
           }
         }
       }
     }

    if(empty($errors))
     {
      $nr = 0;
      switch($image_info[2])
       {
        case 1: $filename = $_SESSION[$settings['session_prefix'].'user_id'].'.gif'; break;
        case 2: $filename = $_SESSION[$settings['session_prefix'].'user_id'].'.jpg'; break;
        case 3: $filename = $_SESSION[$settings['session_prefix'].'user_id'].'.png'; break;
       }
      if(isset($img_tmp_name))
       {
        @rename($uploaded_images_path.$img_tmp_name, $uploaded_images_path.$filename) or $errors[] = 'upload_error';
        $smarty->assign('image_downsized',true);
        $smarty->assign('new_width',$new_width);
        $smarty->assign('new_height',$new_height);
        $smarty->assign('new_filesize',number_format($file_size/1000,0,',',''));
       }
      else
       {
        @move_uploaded_file($_FILES['probe']['tmp_name'], $uploaded_images_path.$filename) or $errors[] = 'upload_error';
       }
     }
    if(empty($errors))
     {
      @chmod($uploaded_images_path.$filename, 0644);
      $smarty->assign('avatar_uploaded',true);
     }
    else
     {
      $smarty->assign('errors',$errors);
      $smarty->assign('form',true);
     }
   }

  if(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.jpg'))
   {
    $avatar = $uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.jpg';
   }
  elseif(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.png'))
   {
    $avatar = $uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.png';
   }
  elseif(file_exists($uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.gif'))
   {
    $avatar = $uploaded_images_path.$_SESSION[$settings['session_prefix'].'user_id'].'.gif';
   }

  if(isset($avatar))
   {
    $avatar .= '?u='.uniqid();
    $smarty->assign('avatar', $avatar);
   }
  else $smarty->assign('upload', 'true');

  if(isset($_GET['deleted'])) $smarty->assign('avatar_deleted', true);


  if(empty($errors) && isset($_FILES['probe']['error']))
   {
    $smarty->assign('server_max_filesize', ini_get('upload_max_filesize'));
    $errors[] = 'upload_error_2';
    $smarty->assign('errors',$errors);
   }

  }

$template = 'avatar.tpl';
?>

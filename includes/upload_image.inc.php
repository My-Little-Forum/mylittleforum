<?php
if(!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

// upload folder:
$uploaded_images_path = 'images/uploaded/';
$images_per_page = 5;

if (($settings['upload_images'] == 1 && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] > 0) || ($settings['upload_images'] == 2 && isset($_SESSION[$settings['session_prefix'].'user_id'])) || ($settings['upload_images'] == 3)) {
	// upload:image:
	if (isset($_FILES['probe']) && $_FILES['probe']['size'] != 0 && !$_FILES['probe']['error']) {
		unset($errors);
		$user_id = (isset($_SESSION[$settings['session_prefix'].'user_id'])) ? intval($_SESSION[$settings['session_prefix'].'user_id']) : NULL;
		$img_tmp_name = uniqid(rand()).'.tmp';
		$imageTemp = validate_image($_FILES['probe']['tmp_name'], $uploaded_images_path.$img_tmp_name);
		if (!file_exists($uploaded_images_path.$img_tmp_name))
			$errors[] = 'upload_error';
		if ($imageTemp === false)
			$errors[] = 'invalid_file_format';

		if (empty($errors) && $imageTemp['valid'] === true) {
			clearstatcache();
			$image_info = getimagesize($uploaded_images_path.$img_tmp_name);
			$imageSize = @filesize($uploaded_images_path.$img_tmp_name);
			if ($imageSize > $settings['upload_max_img_size'] * 1000 || $image_info[0] > $settings['upload_max_img_width'] || $image_info[1] > $settings['upload_max_img_height']) {
				// resize if too large:
				$imgResized = true;
				if ($image_info[0] > $settings['upload_max_img_width'] || $image_info[1] > $settings['upload_max_img_height']) {
					if ($image_info[0] >= $image_info[1]) {
						$new_width = $settings['upload_max_img_width'];
						$new_height = intval($image_info[1] * $new_width / $image_info[0]);
					} else {
						$new_height = $settings['upload_max_img_height'];
						$new_width = intval($image_info[0] * $new_height / $image_info[1]);
					}
				} else {
					$new_width = $image_info[0];
					$new_height = $image_info[1];
				}
				for ($compression = 100; $compression > 1; $compression = $compression - 10) {
				clearstatcache();
					if (!resize_image($uploaded_images_path.$img_tmp_name, $uploaded_images_path.$img_tmp_name, $new_width, $new_height, $compression)) {
						$imageSize = filesize($uploaded_images_path.$img_tmp_name);
						break;
					}
					if ($imageMIME != 'image/jpeg' && $file_size > $settings['upload_max_img_size'] * 1000) break;
					$imageSize = @filesize($uploaded_images_path.$img_tmp_name);
					if ($imageSize <= $settings['upload_max_img_size'] * 1000) break;
				}
				if ($imageSize > $settings['upload_max_img_size'] * 1000) {
					$smarty->assign('width', $image_info[0]);
					$smarty->assign('height', $image_info[1]);
					$smarty->assign('filesize', number_format($imageSize / 1000, 0, ',', ''));
					$smarty->assign('max_width', $settings['upload_max_img_width']);
					$smarty->assign('max_height', $settings['upload_max_img_height']);
					$smarty->assign('max_filesize', $settings['upload_max_img_size']);
					$errors[] = 'file_too_large';
				}
				if (isset($errors)) {
					if (file_exists($uploaded_images_path.$img_tmp_name)) {
						@chmod($uploaded_images_path.$img_tmp_name, 0777);
						@unlink($uploaded_images_path.$img_tmp_name);
					}
				}
			}
		}

		if (empty($errors)) {
			$filename = gmdate("YmdHis").uniqid('');
			switch($imageMIME) {
				case 'image/gif':
					$filename .= '.gif';
				break;
				case 'image/jpeg':
					$filename .= '.jpg';
				break;
				case 'image/png':
					$filename .= '.png';
				break;
				case 'image/webp':
					$filename .= '.webp';
				break;
			}
			if (isset($img_tmp_name)) {
				@rename($uploaded_images_path.$img_tmp_name, $uploaded_images_path.$filename) or $errors[] = 'upload_error';
				$smarty->assign('image_downsized', true);
				$smarty->assign('new_width', $new_width);
				$smarty->assign('new_height', $new_height);
			} else {
				@move_uploaded_file($_FILES['probe']['tmp_name'], $uploaded_images_path.$filename) or $errors[] = 'upload_error';
				$smarty->assign('new_filesize', number_format($imageSize / 1000, 0, ',', ''));
			}
		}
		if (empty($errors)) {
			@chmod($uploaded_images_path.$filename, 0644);
			// $user_id can be NULL (see around line #15), because of that do not handle it with intval()
			// see therefore variable definition of $user_id around line 15 of this script
			$qSetUpload = "INSERT INTO " . $db_settings['uploads_table'] . " (uploader, pathname, tstamp) VALUES (". $user_id .", '" . mysqli_real_escape_string($connid, $filename) . "', NOW())";
			mysqli_query($connid, $qSetUpload);
			$smarty->assign('uploaded_file', $filename);
		} else {
			$smarty->assign('errors', $errors);
			$smarty->assign('form', true);
		}
	}

	// delete image:
	elseif (isset($_REQUEST['delete']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] > 0) {
		if (empty($_REQUEST['delete_confirm'])) {
			$smarty->assign('delete_confirm', true);
			$smarty->assign('delete', htmlspecialchars($_REQUEST['delete']));
			if (isset($_REQUEST['current'])) $smarty->assign('current', intval($_REQUEST['current']));
		} else {
			if (preg_match('/^([a-z0-9]+)\.(gif|jpg|png|webp)$/', $_REQUEST['delete']) && file_exists($uploaded_images_path.$_REQUEST['delete'])) {
				@chmod($uploaded_images_path.$_REQUEST['delete'], 0777);
				@unlink($uploaded_images_path.$_REQUEST['delete']);
			}
			if (isset($_REQUEST['current'])) $bi = '&browse_images='.intval($_REQUEST['current']);
			else $bi = '&browse_images=1';
			header('Location: index.php?mode=upload_image'.$bi);
			exit;
		}
	}

	// browse uploaded images:
	elseif (isset($_GET['browse_images'])) {
		$images = array();
		$browse_images = intval($_GET['browse_images']);
		if ($browse_images < 1) $browse_images = 1;
		$handle = opendir($uploaded_images_path);
		while ($file = readdir($handle)) {
			if (preg_match('/\.(gif|png|jpe?g|svg|webp)$/i', $file)) {
				$images[] = $file;
			}
		}
		closedir($handle);
		if ($images) {
			rsort($images);
			$images_count = count($images);
			if ($browse_images > ceil($images_count / $images_per_page)) $browse_images = ceil($images_count / $images_per_page);
			$start = $browse_images * $images_per_page - $images_per_page;
			$show_images_to = $browse_images * $images_per_page;
			if ($show_images_to > $images_count) $show_images_to = $images_count;
		}
		else $images_count = 0;
		$smarty->assign('current',$browse_images);
		if ($browse_images*$images_per_page < $images_count) $smarty->assign('next', $browse_images + 1);
		if ($browse_images > 1) $smarty->assign('previous', $browse_images - 1);
		$smarty->assign('browse_images', true);
		$smarty->assign('images_per_page', $images_per_page);
		if (isset($images)) $smarty->assign('images', $images);
		if (isset($start)) $smarty->assign('start', $start);
	}

	// display form to upload image:
	elseif (empty($_GET['browse_images'])) {
		$smarty->assign('form',true);
	}
	if (empty($errors) && isset($_FILES['probe']['error'])) {
		$smarty->assign('server_max_filesize', ini_get('upload_max_filesize'));
		$errors[] = 'upload_error_2';
		$smarty->assign('errors', $errors);
	}
}

$template = 'upload_image.tpl';
?>

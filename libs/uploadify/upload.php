<?php
//echo EX_ADMIN_PATH . '/uploads/products/';
$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once($root.'/wp-load.php');
require_once($root.'/wp-admin/includes/admin.php');

require_once(ABSPATH . '/wp-admin/includes/image.php');
//function image_resize( $file, $max_w, $max_h, $crop=false, $suffix=null, $dest_path=null, $jpeg_quality=90
global $wpdb;


if ($_FILES['images']['size'] > 0) {
	//$image_title = $_POST['image_title'];]
	$element = $_REQUEST['element_name'] ? $_REQUEST['element_name'] : 'images';
	$gid		= $_REQUEST['gid'];
	$json         = array();
	$json['mode'] = "ajax";
	$file        = $_FILES[$element]['name'];
	$name        = (substr($file, 0, strrpos($file, '.')));
	$ext         = strtolower(substr($file, strrpos($file, '.') + 1, strlen($file)));
	$srcFile     = $_FILES[$element]['tmp_name'];
	$name = preg_replace("/\s+|\(|\)/", "_", $name);
	$filename   = $name . '.' . $ext;
	
	$path = MS_GALLERY_DIR."/{$gid}";//.$_REQUEST['gid'];
				
	$gallery = $wpdb->get_row("SELECT * FROM {$wpdb->ms_gallery} WHERE id={$gid}");
	
	$params = json_decode($gallery->params);
	
	//print_r($params);
	if(!is_dir($path))
		if(!mkdir($path)){}
	
	$thumbPath = $path."/thumbs";
	if(!is_dir($thumbPath))
		if(!mkdir($thumbPath)){}
					
	$targetFile = $path."/" . $filename;

	if(file_exists($targetFile)){
		$name = generate_file_name($path, $name);
		$targetFile = $path."/" . $name.".".$ext;
	}
	if (($ext != "jpg") && ($ext != "jpeg") && ($ext != "png") && ($ext != "gif")) {
		$json['error'][] = $file . " is not support image file";
	} else {
		if (move_uploaded_file($srcFile, $targetFile)) {
			$thumbname = image_resize($targetFile, ($params->thumb_width > 0 ? $params->thumb_width : 100), ($params->thumb_height > 0 ? $params->thumb_height : 100), true, null, $thumbPath);
			if($thumbname) $thumbname = basename($thumbname);
			$maxOrder = $wpdb->get_var("SELECT max(ordering) FROM {$wpdb->ms_images} WHERE gid='{$gid}'");
			$maxOrder++;
			$sql = "
				INSERT INTO
				{$wpdb->ms_images}(title, description, filename, thumbname, gid, ordering)
				VALUES('$name', '$name', '$filename', '$thumbname', '$gid', '$maxOrder')
			";
			$wpdb->query($sql);
			$json['sql'] = update_image($gid, $targetFile, $path, $thumbPath, $params);			
		} else {
			$json['error'][] = "error(s) occured while uploading " . $file;
		}
	}
	echo json_encode($json);
	die();
}
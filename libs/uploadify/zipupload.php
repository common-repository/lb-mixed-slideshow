<?php
//echo EX_ADMIN_PATH . '/uploads/products/';
$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once($root.'/wp-load.php');
require_once($root.'/wp-admin/includes/admin.php');

require_once(ABSPATH . '/wp-admin/includes/image.php');
//function image_resize( $file, $max_w, $max_h, $crop=false, $suffix=null, $dest_path=null, $jpeg_quality=90
global $wpdb;

if ($_FILES['images']['size'] > 0) {
	$json         = array();
	$json['mode'] = "ajax";	
	$json['error'] = array();
	$fileName =  $_FILES['images']['name'];

	if(!$fileName) return;
	$sourcePath = $_FILES['images']['tmp_name'];
	$uploadPath = MS_TMP_DIR;
	
	$ext = array_pop(explode(".", $fileName));

	$allows= array("zip", "tar");
	
	if(!in_array($ext, $allows)){ return false;}
	
	if(move_uploaded_file($sourcePath, $uploadPath."/".$fileName)){

		WP_Filesystem();
		ob_start();
		$basename = basename($fileName, '.zip');
		rmdirr($uploadPath."/".$basename);
		$result = unzip_file($uploadPath."/".$fileName, $uploadPath."/".$basename);
		if($files = list_files($uploadPath."/".$basename)){
			//print_r($files);
			$gid = $_REQUEST['gid'];
			$gpath = MS_GALLERY_DIR."/{$gid}";//.$_REQUEST['gid'];			
			$gallery = $wpdb->get_row("SELECT * FROM {$wpdb->ms_gallery} WHERE id={$gid}");			
			$params = json_decode($gallery->params);
			
			if(!is_dir($gpath))
				if(!mkdir($gpath)){}
			
			$thumbPath = $gpath."/thumbs";
			if(!is_dir($thumbPath))
				if(!mkdir($thumbPath)){}
			$image_allow = array("jpg", "jpeg", "png", "gif", "bmp");
			
			foreach($files as $file){
				$file 		 = str_replace("\\", "/", $file);
				$name        = (substr($file, strrpos($file, "/")+1, strrpos($file, '.')));
				$ext         = strtolower(substr($file, strrpos($file, '.') + 1, strlen($file)));

				$name = preg_replace("/\s+|\(|\)/", "_", $name);
				$targetFile = $gpath."/" . $name . '.' . $ext;

				if(file_exists($targetFile)){
					$name = generate_file_name($gpath, $name);
					
				}
				$targetFile = $gpath."" . $name.".".$ext;
				
				if (!in_array($ext, $image_allow)) {
					$json['error'][] = $file . " is not support image file";
				} else {
					echo $file; echo ",", $targetFile,"\n";
					if(@copy($file, $targetFile)){
						ms_update_image($gid, $targetFile, $gpath, $thumbPath, $params);
						$json['sql']=$sql;
					}
				}
			}
		}	
		ms_rmdirr($uploadPath."/".$basename);
		@unlink($uploadPath."/".$fileName);
		$html = ob_get_clean();
	}	
	file_put_contents(MS_TMP_DIR."/log.ini", $html);
	echo json_encode($json);

	die();
	//$image_title = $_POST['image_title'];]
	//$element = $_REQUEST['element_name'] ? $_REQUEST['element_name'] : 'images';
	$gid		= $_REQUEST['gid'];
	
	$file        = $_FILES[$element]['name'];
	$name        = (substr($file, 0, strrpos($file, '.')));
	$ext         = strtolower(substr($file, strrpos($file, '.') + 1, strlen($file)));
	$srcFile     = $_FILES[$element]['tmp_name'];
	$name = preg_replace("/\s+|\(|\)/", "_", $name);
	$filename   = $name . '.' . $ext;
	
	$path = PS_GALLERY_DIR."/{$gid}";//.$_REQUEST['gid'];
				
	$gallery = $wpdb->get_row("SELECT * FROM {$wpdb->ps_gallery} WHERE id={$gid}");
	
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
			$maxOrder = $wpdb->get_var("SELECT max(ordering) FROM {$wpdb->ps_images} WHERE gid='{$gid}'");
			$maxOrder++;
			$sql = "
				INSERT INTO
				{$wpdb->ps_images}(title, description, filename, thumbname, gid, ordering)
				VALUES('$name', '$name', '$filename', '$thumbname', '$gid', '$maxOrder')
			";
			$wpdb->query($sql);
			$json['sql']=$sql;
			
		} else {
			$json['error'][] = "error(s) occured while uploading " . $file;
		}
	}
	echo json_encode($json);
	die();
}
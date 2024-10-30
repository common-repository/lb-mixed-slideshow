<?php
function ms_yes_no($name, $selected, $echo = true){
	ob_start();
?>
<select name="<?php echo $name;?>" id="<?php echo $name;?>">
	<option value="0" <?php echo ($selected ? "" : 'selected="selected"');?>>No</option>
	<option value="1" <?php echo (!$selected ? "" : 'selected="selected"');?>>Yes</option>    
</select>
<?php
	$html = ob_get_clean();
	if($echo) echo $html;
	return $html;
}

function ms_list_thumbnails($name, $selected, $echo = true){
	ob_start();
?>
<select name="<?php echo $name;?>" id="<?php echo $name;?>">
	<option value="thumbnail" <?php echo ($selected == 'thumbnail' ? 'selected="selected"' : '');?>>Thumbnail</option>
	<option value="medium" <?php echo ($selected == 'medium'? 'selected="selected"' : '');?>>Medium</option>   
    <option value="large" <?php echo ($selected == 'large'? 'selected="selected"' : '');?>>Large</option>   
    <option value="full" <?php echo ($selected == 'full'? 'selected="selected"' : '');?>>Full</option>    
</select>
<?php
	$html = ob_get_clean();
	if($echo) echo $html;
	return $html;
}
function ms_get_shortcode(){
	$option = ms_get_config();
	$shortcode = array();
	$shortcode[] = '[mixed-slideshow';
	foreach($option as $k=>$v){
		if(!is_null($v) && $v!="")
			$shortcode[] = '<span style="color:#961313;">'.$k.'</span>=<span style="color:#6D02F8;">"'.(is_array($v) ? implode(" ", $v) : $v).'"</span>';
	}
	$shortcode[] = ']';
	
	return implode(" ", $shortcode);
}
function ms_get_config(){
	$config = get_option("slideshow_config");
	$default = array(
		'width' => 800,
		'height' => 600,
		'border_width' => 0,
		'border_color' => null,
		'border_image' => null,
		'gid' => null,
		'limitstart' => 0,
		'limit' => 100,
		'transitions' => null,
		'show_description' => true,
		'show_thumb' => true,
		'time_delay' => 5000,
		'show_nav' => 1,
		'show_top_nav' => 1
	);
	if(is_array($config)){
		$config = array_merge($default, $config);
	}else{
		$config = $default;
	}
	if(is_array($config['transitions'])) $config['transitions'] = implode(" ", $config['transitions']);
	return $config;
}
function ms_get_slideshow_config($id){

}
function ms_set_config($config){
	if($config['transitions'][0] == '*')
		$config['transitions'] = '*';
	update_option("slideshow_config", $config);
}
?>
<?php
/**
* Delete a file, or a folder and its contents
*
* @author Aidan Lister <aidan@php.net>
* @version 1.0.2
* @param string $dirname Directory to delete
* @return bool Returns TRUE on success, FALSE on failure
*/
function ms_rmdirr($dirname){
	// Sanity check
	if (!file_exists($dirname)) {
		return false;
	}
	
	// Simple delete for a file
	if (is_file($dirname)) {
		return unlink($dirname);
	}
	
	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
	
		// Recurse
		ms_rmdirr("$dirname/$entry");
	}
	
	// Clean up
	$dir->close();
	return rmdir($dirname);
}

function ms_get_list_transitions($name, $id = '', $size = 1, $multiple = false, $selected = array()){
	global $wpdb;
	$sql = "SELECT name, `key`
		FROM {$wpdb->ms_transitions}
		ORDER BY name ASC
	";
	$trans = $wpdb->get_results($sql);
	if(!is_array($selected)) $selected = explode(" ", $selected);
	settype($selected, 'array');
	ob_start();
	?>
    <select style="height:auto;" name="<?php echo $name;?>" id="<?php echo $id;?>" <?php echo $multiple ? 'multiple="multiple"' : '';?> size="<?php echo $size;?>" >
    	<option value="*" <?php echo in_array("*", $selected) ? 'selected="selected"' : '';?>>--All--</option>
    <?php
	foreach($trans as $key=>$tran){
	?>
    	<option value="<?php echo $tran->key;?>" <?php echo (in_array($tran->key, $selected) || $selected[0] == '*') ? 'selected="selected"' : '';?>><?php echo $tran->name;?></option>
    <?php
	}
	?>
    </select>
    <?php
	return ob_get_clean();
}
function ms_out($out){
	echo "<pre>";print_r($out);echo "</pre>";
}

function ms_show_items_per_page_list($name, $selected = null){
	$list = array(5, 10, 15, 20, 50, 100);
?>
<select name="<?php echo $name;?>" onchange="this.form.submit();">
<?php foreach($list as $opt){?>
	<option value="<?php echo $opt;?>" <?php echo $opt==$selected ? 'selected="selected"' : '';?>><?php echo $opt;?></option>
<?php }?>
</select>
<?php
}

function ms_do_mixed_slideshow($attrs){
	$config = ms_get_config();
	$option = array_merge($config, $attrs);
	$shortcode = array();
	$shortcode[] = '[mixed-slideshow';
	foreach($option as $k=>$v){
		if(!is_null($v) && $v != "")
			$shortcode[] = $k.'="'.(is_array($v) ? implode(" ", $v) : $v).'"';
	}
	$shortcode[] = ']';
	echo do_shortcode(implode(" ", $shortcode));
}

function ms_generate_file_name($dir, $name){
	$return = $name;
	if ($handle = opendir($dir)) {
		$files = array();
		while (false !== ($file = readdir($handle))) {
			if( 0== strpos($file, $name)){
				$files[] = substr($file, 0, strrpos($file, '.'));
			}
		}	
		$i = 0;
		while(true){
			$i++;
			$return = $name."-".$i;
			if(!in_array($return, $files)){
				break;
			}
			if($i>=1000) break;
		}		
		closedir($handle);
	}
	return $return;
}

function ms_update_image($gid, $sourceFile, $gPath, $thumbPath, $params){
	ob_start();
	global $wpdb;
	if($params->auto_resize){
		$croppedFile = image_resize($sourceFile, $params->image_width, $params->image_height, true, null, $gPath);
			
		@unlink($sourceFile);
		$sourceFile = $croppedFile;
	}
	$fullname = basename($sourceFile);
				
	$thumbname = image_resize($sourceFile, ($params->thumb_width > 0 ? $params->thumb_width : 100), ($params->thumb_height > 0 ? $params->thumb_height : 100), true, null, $thumbPath);
	
	if($thumbname) $thumbname = basename($thumbname);
	$title = array_pop(array_reverse(explode(".", $fullname)));
	$maxOrder = $wpdb->get_var("SELECT max(ordering) FROM {$wpdb->ms_images} WHERE gid='{$gid}'");
	$maxOrder++;
	$sql = "
		INSERT INTO
		{$wpdb->ms_images}(title, description, filename, thumbname, gid, ordering)
		VALUES('".$title."', '".$title."', '".$fullname."', '".$thumbname."', '$gid', '$maxOrder')
	";
	
	$wpdb->query($sql);
	$html = ob_get_clean();
	file_put_contents("log.ini", $html);
	return $sql;
}

function ms_get_gallery_shortcode($gid){
	global $wpdb;
	$sql = "
		SELECT *
		FROM {$wpdb->ms_gallery}
		WHERE id='$gid'
	";
	$result = $wpdb->get_row($sql);
	
	$default = ms_get_config();
	
	$shortcode = array();
	$shortcode[] = '[mixed-slideshow';
	if($params = json_decode($result->shortcode)){
		foreach($params as $k=>$v){
			if($v=="" || is_null($v)){
				$v = $default[$k];
			}
			if(!is_null($v))
				$shortcode[] = '<span style="color:#961313;">'.$k.'</span>=<span style="color:#6D02F8;">"'.(is_array($v) ? implode(" ", $v) : $v).'"</span>';
		}
	}	
	return implode(" ", $shortcode)."]";

}
function ms_get_gallery_attr($gid){
	global $wpdb;
	$sql = "
		SELECT *
		FROM {$wpdb->ms_gallery}
		WHERE id='$gid'
	";
	$result = $wpdb->get_row($sql);
	$default = ms_get_config();
	
	if($params = json_decode($result->shortcode)){
		foreach($default as $k=>$v){
			if($params->$k!=='' && !is_null($params->$k))
				$default[$k] = $params->$k;
		}
	}
	if($default['transitions'][0] == '*') $default['transitions'] = '*';
	else if(is_array($default['transitions'])) $default['transitions'] = implode(" ", $default['transitions']);
	$default['gid'] = $gid;
	return $default;
}

function ms_build_query($array, $table){
	global $wpdb;
	$sql = "
		SHOW COLUMNS FROM {$table}
	";
	$result = $wpdb->get_results($sql);
	$insertQuery = "";
	$updateQuery = array();
	
	$insertFields = array();
	$insertValues = array();
	for($i = 0, $n = count($result); $i < $n; $i++){
		$field = $result[$i]->Field;
		if(isset($array[$field])){
			$insertFields[] = $field;
			
			if(is_array($array[$field]))
				$val = json_encode($array[$field]);
			else $val = $array[$field];
			$insertValues[] = $val;
			
			$updateQuery[] = $field."='".$val."'";
		}
	}
	return array(
		'insert' => "INSERT INTO {$table}(".implode(",", $insertFields).") VALUES('".implode("','", $insertValues)."')",
		'update' => "UPDATE {$table} SET ".implode(",", $updateQuery)
	);
}
?>
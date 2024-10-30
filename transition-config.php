<?php
require_once('../../../wp-admin/admin.php');
function display_page(){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
<?php
wp_admin_css( 'global' );
wp_admin_css();
wp_admin_css( 'colors' );
wp_admin_css( 'ie' );
if ( is_multisite() )
	wp_admin_css( 'ms' );
wp_enqueue_script('utils');

wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
// Check callback name for 'media'
if ( ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) ) || 0 === strpos( $content_func, 'media' ) )
	wp_enqueue_style( 'media' );
wp_enqueue_style( 'ie' );
?>
<link rel="stylesheet" href="<?php echo PS_URL;?>/assets/css/jquery/jquery.ui.theme.css" type="text/css" media="all" /> 
<link rel="stylesheet" href="<?php echo PS_URL;?>/assets/css/jquery/jquery.ui.slider.css" type="text/css" media="all" /> 
<style type="text/css">
body{
	font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
	font-size:11px;
}
body a.ui-slider-handle{
	outline:none;
}
</style>			
            
            
			
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time(); ?>'};
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = 'media-upload-popup', adminpage = 'media-upload-popup';
//]]>
</script>
<?php
do_action('admin_enqueue_scripts', $hook_suffix);
do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');

if ( is_string($content_func) )
	do_action( "admin_head_{$content_func}" );
?>
<script src="<?php echo PS_URL;?>/assets/js/jquery/jquery.ui.core.js"></script>
<script src="<?php echo PS_URL;?>/assets/js/jquery/jquery.ui.widget.js"></script>
<script src="<?php echo PS_URL;?>/assets/js/jquery/jquery.ui.mouse.js"></script>
<script src="<?php echo PS_URL;?>/assets/js/jquery/jquery.ui.slider.js"></script>
</head>
<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?> style="margin:0px;">

<?php
$id = $_GET['id'];

if(strtolower($_SERVER['REQUEST_METHOD'])=='post'){
	$post = $_POST['slideshow_config'];
	$data = json_encode($post);
	
	global $wpdb;
	$wpdb->query("UPDATE {$wpdb->ps_transitions} SET config = '{$data}' WHERE id={$id}");
	
	$msg = "Transition configuaration has been saved!";
	$_GET['nodisplay'] = 1;
	require_once(PS_DIR."/manager-transitions.php");
	new ManagerTransitions();
?>
<script>
jQuery(document).ready(function(){
	setTimeout(function(){
		self.parent.tb_remove();
	}, 1000);
});
</script>
<?php
}else{
	$msg = "";
}

function get_transition_config($id){
	global $wpdb;
	$query = "SELECT config FROM {$wpdb->ps_transitions} WHERE id={$id}";
	$row = $wpdb->get_row($query);
	//print_r($row);
	$config = json_decode($row->config);
	
	return $config;
}
$configs = get_transition_config($id);
?>

<script type="text/javascript">
function doCheck(){
	var len = <?php echo count($configs);?>;
	var elements = document.configForm.elements("slideshow_config[0]");
	for(var i=0;i<len;i++){
		var value = document.configForm.elements("slideshow_config["+i+"][value]");
		var min = document.configForm.elements("slideshow_config["+i+"][min]");
		var max = document.configForm.elements("slideshow_config["+i+"][max]");
		if(isNaN(value.value) || parseInt(value.value) > parseInt(max.value) || parseInt(value.value) < parseInt(min.value)){
			alert("Please enter value valid");
			value.focus();
			return false;
		}
	}
	return true;
}
var sliderOptions = [];
</script>
<form method="post" name="configForm" action="" onsubmit="return doCheck();">
<table width="100%" align="center">
<tr>
   	<td align="center"><?php echo $msg;?></td>
</tr>  
<tr>
	<td align="center">
<table cellpadding="3" cellspacing="3" border="0" style="border-collapse:collapse;">    
	<tr>
    	<td colspan="5" align="center" height="50" valign="middle"><b>Transition Configuration</b></td>
    </tr>
          
<?php $total = 0;
	foreach($configs as $key=>$conf){
		$total++;
		($configs[$key]->min ? "" : $configs[$key]->min = 0);
		($configs[$key]->max ? "" : $configs[$key]->max = 30);		
?>	
    <tr>
    	<td width="100" style="min-width:50;"><?php echo $configs[$key]->title;?></td>
    	<td>
        	<input type="hidden" name="slideshow_config[<?php echo $key;?>][title]" value="<?php echo $configs[$key]->title;?>"  />
            <input type="hidden" name="slideshow_config[<?php echo $key;?>][name]" value="<?php echo $configs[$key]->name;?>"  />
            <input type="hidden" name="slideshow_config[<?php echo $key;?>][min]" value="<?php echo $configs[$key]->min;?>"  />
            <input type="hidden" name="slideshow_config[<?php echo $key;?>][max]" value="<?php echo $configs[$key]->max;?>"  />
            <input type="hidden" name="slideshow_config[<?php echo $key;?>][step]" value="<?php echo $configs[$key]->step;?>"  />
        </td>	  
        <td><div id="slider_<?php echo $key;?>" style="width:200px;"></div></td>
        <td width="100" align="right">
          	<input type="text" style="text-align:right;font-weight:bold;" size="5" name="slideshow_config[<?php echo $key;?>][value]" value="<?php echo $configs[$key]->value;?>" id="value_<?php echo $key;?>" />
        </td>
        <td>
        	(<?php echo $configs[$key]->min;?> <= <?php echo $configs[$key]->name;?> <= <?php echo $configs[$key]->max;?>)   
            <script>
			sliderOptions.push({
				'key': <?php echo $key;?>,
				'min': <?php echo $configs[$key]->min;?>,
                'max': <?php echo $configs[$key]->max;?>,
                'value': <?php echo $configs[$key]->value;?>,
				'step': <?php echo $configs[$key]->step ? $configs[$key]->step : 1;?>
			});
			</script>      
        </td>        
    </tr>
<?php
	}
?>
	
<?php if(!$total){ ?>
	<tr>
		<td align="center" colspan="5">No configuration for this transition</td>
	</tr>        
<?php }else{ ?>
	<tr>
    	<td></td>
    	<td colspan="4"><button type="button" name="save" class="button" onclick="this.form.submit();">Save</button> </td>
    </tr>
<?php }?>
</table>
</td>
</tr>
</table>
</form>
<script>
(function($){
	for(var i=0;i<sliderOptions.length;i++){
		(function(opt){
			var obj = $( "#slider_"+opt.key )
			obj.slider({
					range: "min",
					value: opt.value,
					min: opt.min,
					max: opt.max,
					step: opt.step,
					slide: function( event, ui ) {
						$( "#value_"+opt.key ).val( ui.value );
					}
			});
			$( "#value_"+opt.key ).change(function(){
				var val = $(this).val()
				if(isNaN(val) || parseInt(val) < obj.slider("option", "min") || parseInt(val)>obj.slider("option", "max")){
					$(this).val(obj.slider("option", "min"));
				}
				obj.slider("option","value",$(this).val())
			});
		})(sliderOptions[i]);
	}
})(jQuery)
</script>
<?php
	do_action('admin_print_footer_scripts');
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
<?
} // end function display_page

display_page();
?>
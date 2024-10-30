<?php
/*
Plugin Name: LB Mixed Slideshow
Plugin URI: http://lessbugs.com/index.php?option=com_simpleshopping&Itemid=62&ctrl=product&id=3&task=showDetails
Description: This plugin allow you easy to create a slideshow for Wordpress Blog with a lot of transition effects
Author: Tu Nguyen
Version: 1.0
Author URI: http://lessbugs.com
*/
/* 
	Copyright 2011  Tu Nguyen  (email : tunnhn@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once("widget.php");
class MixedSlideshow{	
	function __construct(){
		$this->defines();
		$this->define_tables();
		$this->requires(); 	
		$this->load_textdomain();
		// add actions
		add_action("admin_head", array($this, 'admin_head'));
		add_action("admin_footer", array($this, 'admin_footer'));
		add_action("admin_menu", array($this, 'admin_menu'));
		add_action('wp_head', array($this, 'slideshow_frontend_head'));
		add_action("wp_footer", array($this, 'frontend_footer_script'));
		
		
		register_activation_hook(MS_FILE, array($this, "on_active"));
		register_deactivation_hook(MS_FILE, array($this, "on_deactive"));
		
		///register_sidebar_widget(__('Post Slideshow'), array($this, 'widget_wpslideshow'));    
		
		// shortcode
		add_shortcode('mixed-slideshow', array($this, 'mixed_slideshow_shortcode'));
	}
	function on_active(){
		$role = get_role('administrator');
		if(!$role->has_cap('mixed_slideshow')) {
			$role->add_cap('mixed_slideshow');
		}
		require_once("ms-install.php");
		require_once("manage-transitions.php");
		new ManageTransitions();
	}
	function on_deactive(){
		global $wpdb;
		
		$role = get_role('administrator');
		if($role->has_cap('mixed_slideshow')) {
			$role->remove_cap('mixed_slideshow');
		}
	}
	/*
		load language for plugin
	*/
	function load_textdomain() {		
		load_plugin_textdomain('msslideshow', false, PS_LANG);
	}
	/*
		define constants
	*/
	function defines(){
		define("MS_FILE", __FILE__);
		define("MS_DIR", dirname(__FILE__));
		define('MS_FOLDER', plugin_basename(MS_DIR));
		define("MS_TMP_DIR", MS_DIR . "/tmp");
		define("MS_GALLERY_DIR", MS_DIR . "/gallery");
		define("MS_LANG", MS_DIR . "/lang");
		define("MS_DOMAIN", "msslideshow");
		if(!is_dir(MS_TMP_DIR)) {
			if (!mkdir(MS_TMP_DIR, 777, true)) {
		    	//die('Failed to create folders...');
			}
		}
		define('MS_URL', get_bloginfo("siteurl")."/wp-content/plugins/".MS_FOLDER);
		define("MS_GALLERY_URL", MS_URL . "/gallery");
	}
	/*
		define tables
	*/
	function define_tables(){
		global $wpdb;
		$wpdb->ms_transitions 	= $wpdb->prefix . 'ms_transitions';
		$wpdb->ms_gallery		= $wpdb->prefix . 'ms_gallery';
		$wpdb->ms_images		= $wpdb->prefix . 'ms_images';
		
		
	}
	/*
		require libraries/functions
	*/
	function requires(){
		require_once(MS_DIR."/includes/functions.php");
	}
	/*
		add js/css into admin header
	*/
	function admin_head(){
		global $root_site;
		if(!in_array($_GET['page'], array('lb-mixed-slideshow', 'ms-manage-gallery', 'ms-manage-images', 'ms-manage-transitions', 'ms-about', 'ms-uninstall'))) return;
		wp_register_script("jquery", $root_site."/wp-includes/js/jquery/jquery.js");		
		wp_register_script("global", MS_URL."/assets/js/admin.global.js");
		wp_register_script("ZeroClipboard", MS_URL."/libs/ZeroClipboard/ZeroClipboard.js");
		wp_register_script("ms_color_picker", MS_URL."/libs/colorpicker/js/colorpicker.js");
   		wp_register_script("ms_swfobject", MS_URL."/libs/uploadify/js/swfobject.js");
		wp_register_script("ms_uploadify", MS_URL."/libs/uploadify/js/jquery.uploadify.v2.1.0.min.js");
	
		wp_register_style("ms_color_picker", MS_URL."/libs/colorpicker/css/colorpicker.css");
		wp_register_style("ms_admin_style", MS_URL."/assets/css/admin.style.css");
		wp_register_style("ms_uploadify", MS_URL."/libs/uploadify/css/uploadify.css");

		wp_print_styles(array(
			'ms_color_picker',
			'ms_admin_style',
			'ms_uploadify'
		));		
							
		wp_register_script("ms_prototype", MS_URL."/assets/js/colorpicker/lib/prototype.js");
		wp_register_script("ms_scriptaculous", MS_URL."/assets/js/colorpicker/scriptaculous/scriptaculous.js");
		wp_register_script("ms_yahoo_color", MS_URL."/assets/js/colorpicker/yahoo.color.js");
		wp_print_scripts(array(
			'ms_prototype',	
			'ms_scriptaculous',
			'ms_yahoo_color',
			'ZeroClipboard',
			'ms_color_picker',
			'ms_swfobject',
			'ms_uploadify'
		));

		
		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox');		
		wp_print_styles(array(
			'thickbox'
		));
		wp_print_scripts(array(
			'jquery',
			'global',	
			'thickbox'
		));		
		?>
        <script type="text/javascript">
			var msConfig = {
				MS_URL: '<?php echo MS_URL;?>',
				maxUpSize: <?php echo (int) (str_replace("M"," ",ini_get('upload_max_filesize'))*1024*1024); ?>
			}
		</script>
        <?php
	}
	function admin_footer(){
		if(!is_admin()) return;
	}
	/*
		add menu to admin page
	*/
	function admin_menu(){
		add_menu_page( 
			'Mixed Slideshow', // page title
			'Mixed Slideshow', // menu title
			'mixed_slideshow', // cap
			MS_FOLDER, // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'Global Config', // page title 
			'Global Config', // menu title
			'mixed_slideshow', // cap
			MS_FOLDER, // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'Manage Gallery', // page title 
			'Manage Gallery', // menu title
			'mixed_slideshow', // cap
			'ms-manage-gallery', // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'Manage Images', // page title 
			'Manage Images', // menu title
			'mixed_slideshow', // cap
			'ms-manage-images', // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'Manage Transitions', // page title 
			'Manage Transitions', // menu title
			'mixed_slideshow', // cap
			'ms-manage-transitions', // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'About', // page title 
			'About', // menu title
			'mixed_slideshow', // cap
			'ms-about', // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			MS_FOLDER , // parent slug
			'Reset/Uninstall', // page title 
			'Reset/Uninstall', // menu title
			'mixed_slideshow', // cap
			'ms-uninstall', // slug
			array (&$this, 'show_menu') // function
		);
	}
	/*
		control page by menu item
	*/
	function show_menu(){
		if( $_SERVER['HTTP_HOST'] != 'localhost'){

		}	
		switch($_GET['page']){
			case MS_FOLDER:
				require_once("default-config.php");
				break;
			case 'ms-manage-transitions':
				require_once("manage-transitions.php");
				break;
			case 'ms-manage-gallery':
				require_once("manage-gallery.php");
				break;
			case 'ms-manage-images':
				require_once("manage-images.php");
				break;
			case 'ms-about':
				require_once("about.php");
				break;
			case 'ms-uninstall':
				require_once("uninstall.php");
				break;
		}
	}
	/*
		add script and style into page header
	*/
	function slideshow_frontend_head(){
		global $root_site;

		wp_register_script("jquery", $root_site."/wp-includes/js/jquery/jquery.js");
		wp_register_script("global", MS_URL."/assets/js/global.js");		
		wp_register_script("jquery_easing", MS_URL."/assets/js/jquery.easing.1.3.js");
		wp_register_script("md5", MS_URL."/assets/js/md5.js");
		wp_register_script("mixed_slideshow_script", MS_URL."/assets/js/mixslideshow.min.js");
		wp_register_script("mixed_slideshow_animate_clip", MS_URL."/assets/js/jquery.animate.clip.js");		
		wp_register_script("mixed_slideshow_transitions", MS_URL."/assets/js/mixslideshow.transitions.min.js");	
	
		
		wp_register_style("mixed_slideshow_style", MS_URL."/assets/css/style.css");
		
		wp_print_styles(array(
			'mixed_slideshow_style'
		));
		wp_print_scripts(array(
			'jquery',	
			'global',
			'jquery_easing',
			'md5',	
			'mixed_slideshow_script',
			'mixed_slideshow_animate_clip',
			'mixed_slideshow_config',
			'mixed_slideshow_transitions',
			'mixed_slideshow_zigzag',
			'mixed_slideshow_transitions_wipe',
			'mixed_slideshow_transitions_slide',
			'mixed_slideshow_transitions_push'
		));
	}
	
	/* shortcode for post/page */
	function mixed_slideshow_shortcode($atts){
		$gallery_config = ms_get_gallery_attr($atts['gid']);
		$global_config = ms_get_config();
		
		
		$attr = (shortcode_atts($gallery_config, $atts));
	
		return $this->mixed_slideshow($attr);
	}
	function mixed_slideshow($attr){
		global $root_path, $root_site;
		global $post, $plugin_url, $wpdb;

		$gid = $attr['gid'];
		$sql = "
			SELECT *
			FROM {$wpdb->ms_images}
			WHERE gid='{$gid}'
			ORDER BY ordering
		";
		$images = $wpdb->get_results($sql);

		$images = array_slice($images, $attr['limitstart'], $attr['limit']);
		if(count($images) == 0) return "[LB Mixed Slideshow \"No Image\"]";
		ob_start();
		?>   
		<ul class="mix-slideshow">
		<?php
		foreach($images as $img){		
			$thumb = MS_URL."/gallery/{$gid}/thumbs/".$img->thumbname;
			$src = MS_URL."/gallery/{$gid}/".$img->filename;
		?>
			<li>
				<a class="content" href="" rel="" src="<?php echo $src;?>"><?php echo $src;?></a>
				<a class="thumb" href="<?php echo $thumb;?>"></a>
				<h1 class="title"><?php echo $img->title;?></h1>
				<h2 class="desc"><?php echo $img->description;?></h2>
			</li>   
		<?php		
		}
		?>
		</ul>
        <?php
		$contents = ob_get_clean();
		ob_start();
		?>
        <script type="text/javascript">
			mixSlideshowOptions.push({
				width: <?php echo $attr['width'];?>,
				height: <?php echo $attr['height'];?>,
				showDescription: <?php echo $attr['show_description'] ? 'true' : 'false';?>,
				showNav: <?php echo $attr['show_nav'] ? 'true' : 'false';?>,
				showTopNav: <?php echo $attr['show_top_nav'] ? 'true' : 'false';?>,
				transitions: '<?php echo $attr['transitions'];?>',
				timeDelay: <?php echo $attr['time_delay'];?>,
				borderWidth: <?php echo (int)$attr['border_width'] ? $attr['border_width'] : 0;?>,
				borderColor: '#<?php echo $attr['border_color'];?>',
				borderImage: '<?php echo $attr['border_image'];?>'
			});			
		</script>
        <?php
		$js = ob_get_clean();
		$this->js .= $js;
				
		return $contents;
	}
	function post_slideshow_bak($attr){
		global $root_path, $root_site;
		global $post, $plugin_url;

		//print_r($post);
		if($attr['pid']){
			if($attrs['exclude_original']){
				$pID = explode(",", $attr['pid']);
			}else{
				$pID = array_merge(explode(",", $attr['pid']), array($post->ID));
			}
		}else{
			if($attrs['exclude_original']){
				$pID = array();
			}else{
				$pID = array($post->ID);
			}
		}
		
		$images = array();

		for($i=0;$i<count($pID);$i++)
			$images = array_merge($images, get_children( array( 'post_parent' => $pID[$i], 'post_type' => 'attachment', 'post_mime_type' => '', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) ));	
			
		$images = array_slice($images, $attr['limitstart'], $attr['limit']);
		ob_start();
		
		?>      
		<ul class="mix-slideshow">
		<?php
		foreach($images as $img){		
			$thumb = wp_get_attachment_image_src( $img->ID );
		?>
			<li>
				<a class="content" href="" rel="" src="<?php echo $img->guid;?>"><?php echo $img->guid;?></a>
				<a class="thumb" href="<?php echo $thumb[0];?>"></a>
				<h1 class="title"><?php echo $img->post_title;?></h1>
				<h2 class="desc"><?php echo $img->post_content;?></h2>
			</li>   
		<?php		
		}
		?>
		</ul>
        <style>
		#xxx ul{
			list-style:none;
			margin:0;
			padding:3px;	
		}
		#xxx ul li{
			list-style:none;
			float:left;
			width:200px;
		}
		#xxx ul li.current{
			background-color:#999999;	
		}
		</style>
        <div id="xxx">        	
        	<ul>
            </ul>
        </div>
		
		<?php
		$contents = ob_get_clean();
		ob_start();
		?>
        <script type="text/javascript">
			mixSlideshowOptions.push({
				width: <?php echo $attr['width'];?>,
				height: <?php echo $attr['height'];?>,
				showDescription: <?php echo $attr['show_description'] ? 'true' : 'false';?>,
				showNav: <?php echo $attr['show_nav'];?>,
				showTopNav: <?php echo $attr['show_top_nav'];?>,
				transitions: '<?php echo $attr['transitions'];?>',
				timeDelay: <?php echo $attr['time_delay'];?>,
				borderWidth: <?php echo $attr['border_width'];?>,
				borderColor: '#<?php echo $attr['border_color'];?>',
				borderImage: '<?php echo $attr['border_image'];?>'
			});			
		</script>
        
        <?php
		$js = ob_get_clean();
		$this->js .= $js;
				
		return $contents;
	}
	function frontend_footer_script(){
		ob_start();
	?>
    <script type="text/javascript">
		jQuery.noConflict();
		var mixSlideshowOptions = [];
	</script>
    <?php
		echo $this->js;
	?>
    <script type="text/javascript">
	jQuery.each(jQuery(".mix-slideshow"), function(i, elem){
		jQuery(this).mixSlideshow(mixSlideshowOptions[i]);
	});
	</script>
    <?php
		echo ob_get_clean();
	}
}
new MixedSlideshow();
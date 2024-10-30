<?php
class ManageGallery{	
	function __construct(){
		global $wpdb;
		$task = $_REQUEST['task'];
		if($task){		
			$id = $_POST['id'];
			if($post = $_POST['form']){
				global $current_user;
				get_currentuserinfo();
				if(!$id){
					$post['shortcode'] = ms_get_config();
				}
				
				$query = ms_build_query(array_merge($post, array('author'=>$current_user->data->user_login)), $wpdb->ms_gallery);
				//echo "<pre>";print_r($query);echo "</pre>";
				//echo "<pre>";print_r($post);echo "</pre>";
				//die();
				if(!$id){
					$sql = $query['insert'];
					$wpdb->query($sql);
					$id = $wpdb->insert_id;
				}else{
					$sql = $query['update'] . " WHERE id='".$id."'";
					$wpdb->query($sql);
				}
			}
			switch($task){
				case 'remove':
					$wpdb->query("DELETE FROM $wpdb->ms_gallery WHERE id IN(".$_REQUEST['id'].")");
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-gallery');
					break;
				case 'save':					
				case 'cancel':
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-gallery');
					break;
				case 'apply':
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-gallery&act=edit&id='.$id);
					break;
				case 'save_new':
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-gallery&act=add');
					break;	
				case 'save_upload':
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$id);
					break;	
				case 'save_setting':
					$shortcode = json_encode($_POST['config']);
					$sql = "UPDATE {$wpdb->ms_gallery} SET shortcode='$shortcode' WHERE id={$id}";
					$wpdb->query($sql);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-gallery&act=edit&id='.$id);
					break;			
			}
		}
		if($bulk_action = $_POST['bulk_action']){
			$gid = $_POST['gid'];
			if(count($gid)){
				switch($bulk_action){
					case 'remove':
						$sql = "
							DELETE FROM {$wpdb->ms_gallery}
							WHERE id IN (".implode(",", $gid).")
						";
						$wpdb->query($sql);
						break;
				}
			}
		}		
	}
	
	function display(){		
		global $wpdb;
		$task = $_REQUEST['act'];
		switch($task){
			case 'add':
				$page_title = "Add New Gallery";
				$file = "gallery-form.php";
				break;
			case 'edit':
				$page_title = "Edit Gallery";
				$sql = "
					SELECT g.*
					FROM {$wpdb->ms_gallery} g
					WHERE g.id='".$_REQUEST['id']."'
				";
				$item = $wpdb->get_row($sql);
				
				$featured = $this->get_image_by_ID($item->featured_image);
				
				$item->params = json_decode($item->params);
				$item->thumbname = $featured->thumbname;
				$item->filename = $featured->filename;
				$images = $wpdb->get_results("
					SELECT * FROM {$wpdb->ms_images} WHERE gid='".$_REQUEST['id']."'
				");
				ob_start();
				for($i = 0, $n = count($images); $i < $n; $i++){
				?>
                <option value="<?php echo $images[$i]->id;?>" <?php echo $item->featured_image == $images[$i]->id ? 'selected="selected"' : '';?>><?php echo $images[$i]->filename;?></option>
                <?php
				}		
				$item->images_list = ob_get_clean();
				
				$file = "gallery-form.php";
				break;
			case 'get_shortcode_settings':
				$this->get_shortcode_setting();
				break;
			default:
				$page_title = __("Manage Gallery", MS_DOMAIN);
				$file = "";
		}
		?>        
        <div class="wrap">
            <div class="icon32 icon-slideshow-32"><br>
            </div>
            <h2><?php echo $page_title;?></h2>
            <div id="post-body">
            	<table width="100%">
                <tr><td valign="top">
                <?php 
				if($file){
					require_once($file);
                }else{
					$this->manage_gallery_box();
				}
				?>
                </td>
                </tr>
                </table>
			</div> <!-- id="post-body" -->
        </div>       
		<?php
	}
	function upload_transition_box(){
	?>
    <form method="post" enctype="multipart/form-data">
    	<input type="file" name="file_name" />
        <input type="hidden" name="do_install" value="1" />
        <button type="submit" class="button"><?php _e("Upload And Install", MS_DOMAIN);?></button>
    </form>
    <?php
	}
	function get_image_by_ID($id){
		global $wpdb;
		return $wpdb->get_row("SELECT * FROM {$wpdb->ms_images} WHERE id='$id'");
	}
	
	function get_shortcode_setting($item){
		global $wpdb;
		$config = json_decode($item->shortcode);
	?>
	<table>
    	<thead>
        	<tr>
            	<td width="200"></td>
                <td width="200"><?php _e("Param Name", MS_DOMAIN);?></td>
                <td><?php _e("Value", MS_DOMAIN);?></td>
                <td></td>
			</tr>
        </thead>
        <tbody>        
    	<tr>
        	<td class="label"><?php _e("Width", MS_DOMAIN);?></td><td>(width)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[width]" value="<?php echo $config->width;?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td><?php _e("Height", MS_DOMAIN);?></td><td>(height)</td>
            <td>&nbsp;</td>
            <td><input type="text" name="config[height]" value="<?php echo $config->height;?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td><?php _e("Border Width", MS_DOMAIN);?></td><td>(border_width)</td>
            <td>&nbsp;</td>
            <td><input type="text" name="config[border_width]" value="<?php echo $config->border_width;?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td><?php _e("Border Color", MS_DOMAIN);?></td><td>(border_color)</td>
            <td align="right"></td>
            <td>
    			<div class="colorSelector">
	                <span>&nbsp;</span>
                </div>
                (#
            	<input type="text" id="config_border_color" readonly="readonly" maxlength="6" size="6" name="config[border_color]" value="<?php echo $config->border_color;?>" />
                )
            </td>
            <td></td>
        </tr>
        <tr>
        	<td><?php _e("Border Image", MS_DOMAIN);?></td><td>(border_image)</td>
            <td>&nbsp;</td>
            <td>
            	<input type="text" name="config[border_image]" value="<?php echo $config->border_image;?>" />
            </td>
            <td></td>
        </tr>
        <tr>
        	<td class="label"><?php _e("Limit Start", MS_DOMAIN);?></td><td>(limitstart)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[limitstart]" value="<?php echo $config->limitstart;?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label"><?php _e("Limit", MS_DOMAIN);?></td><td>(limit)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[limit]" value="<?php echo $config->limit;?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label"><?php _e("Transitions", MS_DOMAIN);?></td><td>(transitions)</td>
            <td>&nbsp;</td>
            <td class="value">
           	<?php echo ms_get_list_transitions('config[transitions][]', 'config_transitions', 10, true, $config->transitions);?>            
           	</td>
            <td></td>
        </tr>
        <tr>
        	<td class="label"><?php _e("Time Delay", MS_DOMAIN);?></td><td>(time_delay)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[time_delay]" value="<?php echo $config->time_delay;?>" /></td>
            <td></td>
        </tr>
        <!--
        <tr>
        	<td class="label">Randomize Transitions</td><td>(random_transitions)</td>
            <td class="value"><?php ms_yes_no('config[random_transitions]', $config->random_transitions);?></td>
            <td>?</td>
        </tr>
         <tr>
        	<td class="label">Randomize Slide</td><td>(random_slide)</td>
            <td class="value"><?php ms_yes_no('config[random_slide]', $config->random_slide);?></td>
            <td>?</td>
        </tr>
        -->
        <tr>
        	<td class="label"><?php _e("Show Description", MS_DOMAIN);?></td><td>(show_description)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_description]', $config->show_description);?></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label"><?php _e("Show Thumbnails", MS_DOMAIN);?></td><td>(show_thumb)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_thumb]', $config->show_thumb);?></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">S<?php _e("how Top Navigator", MS_DOMAIN);?></td><td>(show_top_nav)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_top_nav]', $config->show_top_nav);?></td>
            <td></td>
        </tr>
         <tr>
        	<td class="label"><?php _e("Show Navigator", MS_DOMAIN);?></td><td>(show_nav)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_nav]', $config->show_nav);?></td>
            <td></td>
        </tr>
        <tr>
        	<td></td>
        	<td colspan="4" align="left" style="background-color:#FFF;padding:3px;">
            <?php _e("Shortcode (copy code below and paste it into your post/page)", MS_DOMAIN);?>
            <p id="shortcode"><?php echo ms_get_gallery_shortcode($item->id);?></p>
            </td>
        </tr>
        <tr>
        	<td></td>
            <td colspan="4">
            	<button type="submit" class="button"><?php _e("Save Configuration", MS_DOMAIN);?></button>
		        <button type="button" class="button" id="b_copy_shortcode"><?php _e("Copy Shortcode to Clipboard", MS_DOMAIN);?></button>
			</td>
        </tr>
        </tbody>
    </table>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		var clip = null;
		// Enable Rich HTML support (Flash Player 10 Only)
		ZeroClipboard.setMoviePath( msConfig.MS_URL+"/libs/ZeroClipboard/ZeroClipboard10.swf" );
	
		// Create our clipboard object as per usual
		clip = new ZeroClipboard.Client();
		clip.setHandCursor( true );
		
		clip.addEventListener('load', function (client) {
			//debugstr("Flash movie loaded and ready.");
		});
		
		clip.addEventListener('mouseOver', function (client) {
			// update the text on mouse over
			var testObj = jQuery('<p></p>');
			var text = $("#shortcode").html();
			testObj.html(text);
			
			jQuery.each(testObj.find('span'), function(){
				var html = $(this).html();
				$(this).replaceWith(html);
			})
			
			text = testObj.html();
			
			clip.setText( text );
		});
		
		clip.addEventListener('complete', function (client, text) {
			alert("The shortcode was copy to clipboard");
		});
		
		clip.glue( 'b_copy_shortcode' );
	});
	</script>
    <?php
	}
	function manage_gallery_box(){	
		$list_table = new WP_List_Table();
				
		$limit = $_POST['galleries_per_page'];
		if(!$limit) $limit = get_option(MS_FOLDER."_galleries_per_page");
		if(!$limit) $limit = 10;
		
		update_option(MS_FOLDER."_galleries_per_page", $limit);
		
		$page = $list_table->get_pagenum();

		$result = $this->get_galleries($limit, $page);
		$count = $result['total'];
		$galleries = $result['items'];
		$list_table->set_pagination_args(array(
			'total_items' => $count,
			'per_page' => $limit
			)
		);
	?>
    <form name="transitions_list" method="post">
    <div class="tablenav top">
    	<table width="100%">
        	<tr>
            	<td>
                Show <?php ms_show_items_per_page_list('galleries_per_page', $limit);?> items
                <select name="bulk_action">
                	<option value="">Bulk Actions</option>
                    <option value="remove">Remove</option>                   
                </select>
                <button class="button">Apply</button>
                <a class="button" href="?page=ms-manage-gallery&act=add">Add New</a>
                </td>
                <td>
				<?php $list_table->pagination("top");?>
                </td>                
			</tr>
		</table>                            
    </div>
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
            	<th style="width:60px;text-align:right;" >#</th>
                <th class="check-column" scope="col" width="30" align="center" ><input type="checkbox" class="chk-all"></th>                
                <th scope="col" >Name</th>
                <th scope="col" >Title</th>
                <th scope="col" style="width:80px;">Author</th>
                <th  style="width:120px;">Featured</th>
                <th width="130" >Manage Images</th>
                <th width="60">Edit</th>
                <th width="80">Remove</th>
            </tr>
        </thead>
        <tfoot>
        	<tr>
	            <th style="width:60px;text-align:right;">#</th>            
            	<th class="check-column" scope="col"><input type="checkbox" class="chk-all"></th>
                <th scope="col" align="left">Name</th>
                <th scope="col">Title</th>
                <th scope="col">Author</th>
                <th>Featured</th>
                <th>Manage Images</th>
                <th>Edit</th>
                <th>Remove</th>
            </tr>
        </tfoot>
	    <tbody>
       	<?php if($count){$i=0;?>
        <?php foreach($galleries as $row){?>
        <?php	
			
			$remove_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];				
			$remove_link = remove_query_arg( array( 'task', 'id' ), $active_link );
			$remove_link .= '&noheader=true&task=remove&id='.$row->id;
			$remove_link = "if(confirm('Are you sure?')) location.href='".$remove_link."'";
			$remove_title = "Remove this gallery";			
			$remove_icon = MS_URL."/assets/images/".($row->default ? "150.png" : "delete_16x16.gif");
			
			$edit_link = get_option('siteurl')."/wp-admin/admin.php?page=ms-manage-gallery&act=edit&id=".$row->id;				
			$edit_title = "Click to edit this gallery";
			$edit_icon = MS_URL."/assets/images/gtk-edit.png";

			$class = ($i % 2 == 0) ? 'row0' : 'row1';
		?>
        	<tr class="<?php echo $class;?>">
            	<td align="right"><?php echo $i+1;?></td>            
            	<td align="center"><input type="checkbox" name="gid[]" class="chk-row" value="<?php echo $row->id;?>" /></td>
                <td align="left">
					<a href="<?php echo $edit_link?>"><?php echo $row->name;?></a>
				</td>
                <td align="left"><?php echo $row->title;?></td>
                <td align="left"><?php echo $row->author;?>
                </td>
                <td align="left">
                <?php
				if($row->featured_image){
					$featured = $this->get_image_by_ID($row->featured_image);
					$preview = MS_GALLERY_URL."/{$row->id}/thumbs/".$featured->thumbname;
					echo '<img src="'.$preview.'" style="max-width:100px;max-height:100px;" />';
				}else{
					//$preview = MS_URL."/assets/images/no_image.png";
					_e("No Preview", MS_DOMAIN);
				}
				?>                	
                </td>
                <td align="center">
                	<a href="<?php echo get_option('siteurl')."/wp-admin/admin.php?page=ms-manage-images&gid=".$row->id;?>">Images (<?php echo $row->image_count;?>)</a>
                </td>
                <td align="center" width="">
                	<a href="<?php echo $edit_link;?>" title="<?php echo $edit_title;?>">
                		<img src="<?php echo $edit_icon;?>" border="0" align="<?php echo $edit_title;?>" />
                    </a>
				</td>
                <td align="center" width="">
                	<a href="javascript: void();" onclick="<?php echo $remove_link;?>" title="<?php echo $remove_title;?>">
                		<img src="<?php echo $remove_icon;?>" border="0" align="<?php echo $remove_title;?>" />
                    </a>
				</td>
            </tr>
		<?php 
			$i++;
			}?>            
		<?php }else{?>            
        	<tr>
            	<td colspan="9">No gallery(ies) found</td>
            </tr>
        <?php }?>
        </tbody>
        
    </table>
    <div class="tablenav bottom">
		<?php $list_table->pagination("bottom");?>
    </div>
	</form>
    <?php
	}
	function get_galleries($limit, $page){
		global $wpdb;
		$start = ($page-1)*$limit;
		$sql = "
			SELECT * FROM {$wpdb->ms_gallery}
		";
		$total = count($wpdb->get_results($sql));
		$sql = "
			SELECT g.*, count(i.id) as image_count 
			FROM {$wpdb->ms_gallery} g
			LEFT JOIN {$wpdb->ms_images} i ON g.id = i.gid
			GROUP BY g.id
			ORDER BY g.id ASC
			LIMIT $start, $limit			
		";
		$galleries = $wpdb->get_results($sql);
		return array('items' => $galleries, 'total' => $total);
	}
}

$obj = new ManageGallery();
$obj->display();
?>
    
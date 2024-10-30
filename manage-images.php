<?php
class ManageImages{
	function __construct(){
		global $wpdb;		
		$this->gid = $_REQUEST['gid'];
		if(!$this->gid){
			$this->gid = $wpdb->get_var("SELECT id FROM {$wpdb->ms_gallery} LIMIT 0,1");
			if($this->gid){
				$redirect = 'admin.php?page=ms-manage-images&gid='.$this->gid;
			}else{
				$redirect = 'admin.php?page=ms-manage-gallery&act=add';
			}
			?>
            <script type="text/javascript">location.href='<?php echo $redirect; ?>';</script>
            <?php
		}
		
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
			if(isset($_POST['rows'])){
				$rows = $_POST['rows'];
				foreach($rows as $k=>$v){
					$query = ms_build_query(array_merge($_POST['rows'][$k], array('ordering'=>$_POST['ordering'][$k])), $wpdb->ms_images);			
				
					$sql = $query['update'] . " WHERE id='".$k."'";
					$wpdb->query($sql);
				}
				
			}else{
				$id = $_POST['id'];
				$query = $this->buildQuery($_POST['form'], $wpdb->ms_images);			
				
				if(!$id){
					$sql = $query['insert'];
					
					$id = $wpdb->insert_id;
				}else{
					$sql = $query['update'] . " WHERE id='".$id."'";
				}
				$wpdb->query($sql);
			}
			
			if($bulk_action = $_POST['bulk_action']){
				if($iid = $_POST['iid']){
					switch($bulk_action){
						case 'active':
						case 'deactive':
							$sql = "
								UPDATE {$wpdb->ms_images}
								SET status = '".($bulk_action=='active' ? 1 : 0)."'
								WHERE id IN(".implode(",", $iid).")
							";
							//die();
							$wpdb->query($sql);
							break;
						case 'remove':
							$images = $wpdb->get_results("SELECT gid, filename, thumbname FROM {$wpdb->ms_images} WHERE id IN(".implode(",", $iid).")");
							$sql = "
								DELETE FROM {$wpdb->ms_images}								
								WHERE id IN(".implode(",", $iid).")
							";
							$wpdb->query($sql);
							if(count($images)){
								foreach($images as $image){
									@unlink(MS_GALLERY_DIR."/{$image->gid}/{$image->filename}");
									@unlink(MS_GALLERY_DIR."/{$image->gid}/thumbs/{$image->thumbname}");
								}
							}
							break;
					}
				}
			}
		}
		if(!$id) $id = $_REQUEST['id'];
		$paged = $_REQUEST['paged'];
		if($act = $_REQUEST['act']){
			switch($act){
				case 'remove':
					$image = $wpdb->get_row("SELECT * FROM $wpdb->ms_images WHERE id=".$id);
					$wpdb->query("DELETE FROM $wpdb->ms_images WHERE id=".$id);
	
					@unlink(MS_GALLERY_DIR."/{$image->gid}/{$image->filename}");
					@unlink(MS_GALLERY_DIR."/{$image->gid}/thumbs/{$image->thumbname}");
					
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$_REQUEST['gid']. ($paged ? '&paged='.$paged : ''));
					break;
				case 'active':
					$wpdb->query("UPDATE $wpdb->ms_images SET status=1 WHERE id=".$id);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$id);
					break;
				case 'deactive':
					$wpdb->query("UPDATE $wpdb->ms_images SET status=0 WHERE id=".$id);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$id);
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
				case 'featured':			
					$wpdb->query("UPDATE $wpdb->ms_gallery SET featured_image='".$_REQUEST['id']."' WHERE id=".$_REQUEST['gid']);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$_REQUEST['gid']. ($paged ? '&paged='.$paged : ''));
					break;	
				case 'unfeatured':			
					$wpdb->query("UPDATE $wpdb->ms_gallery SET featured_image='' WHERE id=".$_REQUEST['gid']);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$_REQUEST['gid']. ($paged ? '&paged='.$paged : ''));
					break;	
				case 'moveup':
					$ordering = $_GET['ordering'];
					$sql = "
						SELECT id,ordering 
						FROM {$wpdb->ms_images}
						WHERE 	gid=".$_REQUEST['gid']." 
								AND ordering=(SELECT max(ordering) FROM {$wpdb->ms_images} WHERE ordering<$ordering AND gid=".$_REQUEST['gid']." ORDER BY ordering)
					";
					$result = $wpdb->get_row($sql);
					$wpdb->query("UPDATE {$wpdb->ms_images} SET ordering={$ordering} WHERE id=".$result->id);
					$wpdb->query("UPDATE {$wpdb->ms_images} SET ordering=".$result->ordering." WHERE id=".$id);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$_REQUEST['gid']. ($paged ? '&paged='.$paged : ''));
					break;	
				case 'movedown':
					$ordering = $_GET['ordering'];
					$sql = "
						SELECT id,ordering 
						FROM {$wpdb->ms_images}
						WHERE 	gid=".$_REQUEST['gid']." 
								AND ordering=(SELECT min(ordering) FROM {$wpdb->ms_images} WHERE ordering>$ordering AND gid=".$_REQUEST['gid']." ORDER BY ordering)
					";
					$result = $wpdb->get_row($sql);
					$wpdb->query("UPDATE {$wpdb->ms_images} SET ordering={$ordering} WHERE id=".$result->id);
					$wpdb->query("UPDATE {$wpdb->ms_images} SET ordering=".$result->ordering." WHERE id=".$id);
					wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-images&gid='.$_REQUEST['gid']. ($paged ? '&paged='.$paged : ''));
					break;	
				
			}
		}
	}	
	function get_gallery($gid){
		global $wpdb;
		return $wpdb->get_row("SELECT * FROM {$wpdb->ms_gallery} WHERE id={$gid}");
	}
	function display(){
		global $wpdb;
		$act = $_REQUEST['act'];
		
		$gallery = $this->get_gallery($this->gid);
		switch($act){
			case 'add':
				$page_title = "Add New Gallery";
				$file = "gallery-form.php";
				break;
			case 'edit':
				$page_title = "Edit Gallery";
				$sql = "
					SELECT *
					FROM $wpdb->ms_gallery
					WHERE id='".$_REQUEST['id']."'
				";
				$item = $wpdb->get_row($sql);
				$item->params = json_decode($item->params);
				$file = "gallery-form.php";
				break;
			
			default:
				$page_title = "Manage Images [<b>".$gallery->name."</b>]";
				
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
					$this->manage_image_box();
					$this->upload_box();
				}
				?>
                </td>
                </tr>
                </table>
			</div> <!-- id="post-body" -->
        </div>       
		<?php
	}
	function upload_box(){
	?>
    <div class="metabox-holder">
    	<div class="postbox">
            <h3>Bulk Upload Images</h3>
            <div style="padding:10px;">
            <table width="100%">
              <tr>
              	<td width="40%" valign="top">
                <form action="<?php echo MS_URL; ?>/libs/uploadify/upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="images" class="image_uploads" id="imageFileUpload" />
                    <div id="imageQueue"></div>
                    <p>Max Size of File <strong><?php echo ini_get  ( "upload_max_filesize"  ); ?></strong></p>
                    <p>File Type ( *.jpg, *.jpeg, *.gif, *.png, *.bmp )</p>
                    <input type="submit" name="add_album" class="button-primary" value="Upload" id="btnImageUpload" />
                </form>
                </td>
                <td align="center" valign="middle">--Or--</td>
                <td width="40%" valign="top">
                <form action="<?php echo MS_URL; ?>/libs/uploadify/upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="images" class="image_uploads" id="zipFileUpload" />
                    <div id="zipQueue"></div>
                    <p>Max Size of File <strong><?php echo ini_get  ( "upload_max_filesize"  ); ?></strong></p>
                    <p>File Type ( *.zip )</p>
                    <input type="submit" name="add_album" class="button-primary" value="Upload" id="btnZipUpload" />
                </form>
                </td>
			  </tr>
            </table>                  
            </div>
            
            <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#imageQueue').hide();
                $("#imageFileUpload").uploadify({
                    'uploader': '<?php echo MS_URL; ?>/libs/uploadify/uploadify.swf',
                    'script': '<?php echo MS_URL; ?>/libs/uploadify/upload.php',
                    'cancelImg': '<?php echo MS_URL; ?>/libs/uploadify/images/cancel.png',
                    'folder': 'uploads',
                    'scriptData': {
                        'gid': '<?php echo $this->gid;?>'
                    },
                    'queueID': 'imageQueue',
                    'auto': false,
                    'fileDataName': 'images',
                    'wmode':'transparent',
                    'method': 'POST',
                    'fileDesc': 'Image Files',
                    'fileExt': '*.jpg;*.jpeg;*.png;*.gif;*.bmp',
                    'multi': true,
                    'buttonImg':'<?php echo MS_URL; ?>/libs/uploadify/images/browse-files.png',
                    'width' : 81, 
                    'sizeLimit': msConfig.maxUpSize,
                    'onSelect': function () {
                        $('#imageQueue').show();
                    },
                    'onAllComplete': function () {
                        window.location = window.location;                        
                    }
                });
                $('#btnImageUpload').live('click', function () {
                    $('#imageFileUpload').uploadifyUpload();
                    return false;
                });

                $('#zipQueue').hide();
                $("#zipFileUpload").uploadify({
                    'uploader': '<?php echo MS_URL; ?>/libs/uploadify/uploadify.swf',
                    'script': '<?php echo MS_URL; ?>/libs/uploadify/zipupload.php',
                    'cancelImg': '<?php echo MS_URL; ?>/libs/uploadify/images/cancel.png',
                    'folder': 'uploads',
                    'scriptData': {
                        'gid': '<?php echo $this->gid;?>'
                    },
                    'queueID': 'zipQueue',
                    'auto': false,
                    'fileDataName': 'images',
                    'wmode':'transparent',
                    'method': 'POST',
                    'fileDesc': 'Zip File',
                    'fileExt': '*.zip',
                    'multi': false,
                    'buttonImg':'<?php echo MS_URL; ?>/libs/uploadify/images/select-zip.png',
                    'width' : 84, 
                    'sizeLimit': msConfig.maxUpSize,
                    'onSelect': function () {
                        $('#zipQueue').show();
                    },
                    'onAllComplete': function () {
                        window.location = window.location;                        
                    }
                });
                $('#btnZipUpload').live('click', function () {
                    $('#zipFileUpload').uploadifyUpload();
                    return false;
                });
            });
            </script>
    	</div>
    </div>
    <?php
	}
	function manage_image_box(){	
		global $wpdb;
		$list_table = new WP_List_Table();
				
		$limit = $_POST['images_per_page'];

		if(!$limit) $limit = get_option(MS_FOLDER."_images_per_page");
		if(!$limit) $limit = 10;
		
		
		if($limit == get_option(MS_FOLDER."_images_per_page"))		
			$page = $list_table->get_pagenum();
		else $page=1;
		
		update_option(MS_FOLDER."_images_per_page", $limit);
		
		$result = $this->get_images($limit, $page);
		$count = $result['total'];
		$transitions = $result['items'];
		$list_table->set_pagination_args(array(
			'total_items' => $count,
			'per_page' => $limit
			)
		);
		$gid = $this->gid;
		$ginfo = $this->get_gallery($gid);
		
	?>
    <form name="image_list" method="post">
    <div class="tablenav top">
    	<table width="100%">
        	<tr>
            	<td>
    			Change Gallery 
                <?php echo $this->get_list_galleries($gid);?>
                Show <?php ms_show_items_per_page_list('images_per_page', $limit);?> items
                <select name="bulk_action">
                	<option value="">Bulk Actions</option>
                    <option value="active">Active</option>
                    <option value="deactive">Deactive</option>
                    <option value="remove">Remove</option>                    
                </select>
                <button class="button">Apply</button>
                <button class="button-primary" type="submit">Save All</button>
                <a class="button-primary" href="?page=ms-manage-gallery">Back to Gallery</a>
                
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
            	<th scope="col" style="text-align:right;width:50px;">#</th>
                <th class="check-column" scope="col" width="30" ><input type="checkbox" class="chk-all"></th>
                <th scope="col" >Title</th>
                <th scope="col" >Desc</th>
                <th width="120">Preview</th>
                <th width="100">Sort</th>
                <th style="width:80px;text-align:center;" >Active</th>
                <th style="text-align:center;width:80px;">Remove</th>
            </tr>
        </thead>
        <tfoot>
        	<tr>
                <th scope="col" style="text-align:right;">#</th>
            	<th class="check-column" scope="col"><input type="checkbox" class="chk-all"></th>
                <th scope="col">Title</th>
                <th scope="col">Desc</th> 
                <th>Preview</th>
                <th>Sort</th>
                <th style="text-align:center;width:80px;">Active</th>
                <th style="text-align:center;width:80px;">Remove</th>
            </tr>
        </tfoot>
	    <tbody>
       	<?php if($count){ $i = 0; ?>
        <?php foreach($transitions as $row){?>
        <?php	
			$paged = $_REQUEST['paged'];
			$active_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			
			$active_link = remove_query_arg( array( 'act', 'id' ), $active_link );
			
			$active_link .= '&act='.($row->status==1 ? 'deactive' : 'active').'&noheader=true&id='.$row->id . ($paged ? '&paged='.$paged : '');
			$active_text = ($row->status==1 ? 'Active' : 'Deactive');
			
			///$class = ($row->default==0 ? 'default' : '');
			
			$active_icon = MS_URL."/assets/images/".($row->status ? "tick.png" : "untick.png");
			$active_title = ($row->status==1 ? "Deactive" : "Active")." this transition";

			$remove_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];				
			$remove_link = remove_query_arg( array( 'act', 'id' ), $remove_link );
			$remove_link .= '&act=remove&noheader=true&id='.$row->id . ($paged ? '&paged='.$paged : '');
			$remove_link = "if(confirm('Are you sure')) location.href='".$remove_link."'";
			$remove_title = "Remove this image";
			
			$remove_icon = MS_URL."/assets/images/".($row->default ? "150.png" : "delete_16x16.gif");
			///////////////
			$class = ($i % 2 == 0) ? 'row0' : 'row1';
			$index = ($page-1)*$limit+$i+1;
		?>
        	<tr class="<?php echo $class;?>">
                <td align="right"><?php echo $index;?>
                </td>
            	<td align="center"><input type="checkbox" name="iid[]" value="<?php echo $row->id;?>" class="chk-row" /></td>
                
                <td align="left" title="Click to edit">
                	<textarea class="editable" name="rows[<?php echo $row->id;?>][title]"><?php echo $row->title;?></textarea>
					<span><?php echo $row->title;?></span>
				</td>
                <td align="left" title="Click to edit">
                	<textarea class="editable" name="rows[<?php echo $row->id;?>][description]"><?php echo $row->description;?></textarea>
					<span><?php echo $row->description;?></span>
				</td>
                <td align="left">
                <?php
				if($row->thumbname){
					$preview = MS_GALLERY_URL."/{$ginfo->id}/thumbs/".$row->thumbname;
				}else{
					$preview = MS_URL."/assets/images/no_image.png";
				}
				
				?>
                	<img src="<?php echo $preview;?>" style="max-width:80px;" /><br />
                <?php
                if($ginfo->featured_image != $row->id){
					$flink = get_option('siteurl')."/wp-admin/admin.php?page=ms-manage-images&act=featured&gid={$gid}&id={$row->id}&noheader=true";
                    echo '<a href="'.$flink. ($paged ? '&paged='.$paged : '').'">Set as Featured</a>';
				}else{
					$flink = get_option('siteurl')."/wp-admin/admin.php?page=ms-manage-images&act=unfeatured&gid={$gid}&id={$row->id}&noheader=true";
                    echo '<a href="'.$flink. ($paged ? '&paged='.$paged : '').'"><font color="#FF0000">Remove Featured</font></a>';
				}
				?>
                </td>
                <td>
                	<input type="text" name="ordering[<?php echo $row->id;?>]" value="<?php echo $row->ordering;?>" size="3" />
                    <?php if($index>1){?>
                    <a href="javascript:void(0);">
                	<img src="<?php echo MS_URL;?>/assets/images/arrow_large_up.png" onclick="moverow(<?php echo $row->id;?>, <?php echo $row->ordering;?>, 'moveup');" />
                    </a>
                    <?php }else{?>
                    <img src="<?php echo MS_URL;?>/assets/images/arrow_large_up_gray.png" onclick="moverow(<?php echo $row->id;?>, <?php echo $row->ordering;?>, 'moveup');" />
                    <?php }?>
                    <?php if($index<$count){?>
                    <a href="javascript:void(0);">
					<img src="<?php echo MS_URL;?>/assets/images/arrow_large_down.png" onclick="moverow(<?php echo $row->id;?>, <?php echo $row->ordering;?>,'movedown');" />
                    </a>
                	<?php }else{?>
                    <img src="<?php echo MS_URL;?>/assets/images/arrow_large_down_gray.png" onclick="moverow(<?php echo $row->id;?>, <?php echo $row->ordering;?>,'movedown');" />
                    <?php }?>
                </td>
                <td align="center">
                <a href="<?php echo $active_link;?>"><img src="<?php echo $active_icon;?>" border="0" /></a>
                </td>
                <td style="width:80px;text-align:center;"><a href="javascript: void();" onclick="<?php echo $remove_link;?>" title="<?php echo $remove_title;?>">
                		<img src="<?php echo $remove_icon;?>" border="0" align="<?php echo $remove_title;?>" />
                    </a>
				</td>
            </tr>
		<?php 
			$i++;
			}
		?>            
		<?php }else{?>            
        	<tr>
            	<td colspan="8">No image(s) found</td>
            </tr>
        <?php }?>
        </tbody>        
    </table>
    <div class="tablenav bottom">
		<?php $list_table->pagination("bottom");?>
    </div>
	</form>
    <script>
	
	(function($){		
		$.each($(".editable"), function(){
			var $this = $(this);
			var $parent = $this.parent();
			$this.change(function(){
			}).blur(function(){
				$(this).css({display: 'none'});
				$(this)
				.parent()
				.find("span")
				.css({display: 'block'})
				.html($(this).val());
			});
			$parent.click(function(){
				var editable = $(this).find(".editable").css("display", "block");
				editable.val($(this).find("span").css({display: 'none'}).html()).focus();
				
				$this.css({width: $parent.width()-6, height: $parent.height()-6});
			});
		});
	})(jQuery);
	</script>
    <?php
	}

	function get_images($limit, $page){
		global $wpdb;
		$start = ($page-1)*$limit;
		$sql = "
			SELECT * FROM {$wpdb->ms_images}
			WHERE gid='".$this->gid."'
		";
		$total = count($wpdb->get_results($sql));
		$sql = "
			SELECT * FROM {$wpdb->ms_images}
			WHERE gid='".$this->gid."'
			ORDER BY ordering ASC
			LIMIT $start, $limit			
		";
		$gallery = $wpdb->get_results($sql);
		return array('items' => $gallery, 'total' => $total);
	}
	function get_list_galleries($selected = 0){
		global $wpdb;
		$sql = "
			SELECT * 
			FROM {$wpdb->ms_gallery}
		";
		ob_start();
		$link = get_option('siteurl')."/wp-admin/admin.php?page=ms-manage-images&gid=";
		echo '<select name="gid" onchange="location.href=\''.$link.'\'+this.value;">';
		if($result = $wpdb->get_results($sql)){
			foreach($result as $k=>$v){
				echo '<option value="'.$v->id.'"'.($selected == $v->id ? ' selected="selected"' : '').'>'.$v->name.'</option>';
			}
		}
		echo '</select>';
		return ob_get_clean();
	}
}

$obj = new ManageImages();
$obj->display();

?>
    
<?php
require_once(MS_DIR."/libs/JavaScriptPacker.php");
class ManageTransitions{
	function __construct(){
		global $wpdb;		
		$this->do_install();
		if($task = $_GET['task']){
			switch($task){
				case 'deactive':
					$status = 0;
				case 'active':
					if(!isset($status)) $status = 1;
					$id = $_GET['id'];
					$sql = "
						UPDATE {$wpdb->ms_transitions}
						SET status = '$status'
						WHERE id = '$id'
					";			
					$wpdb->query($sql);
					break;;
				case 'remove':
					$id = $_GET['id'];
					$sql = "
						DELETE FROM {$wpdb->ms_transitions}
						WHERE id = '$id'
					";			
					$wpdb->query($sql);
					break;
			}
			wp_redirect(get_option('siteurl').'/wp-admin/admin.php?page=ms-manage-transitions');
		}
		if($bulk_action = $_POST['bulk_action']){
			$tid = $_POST['tid'];
			if(count($tid)){
				switch($bulk_action){
					case 'active':	
						$sql = "UPDATE {$wpdb->ms_transitions} SET status = '1' WHERE id IN(".implode(",", $tid).")";		
						break;
					case 'deactive':
						$sql = "UPDATE {$wpdb->ms_transitions} SET status = '0' WHERE id IN(".implode(",", $tid).")";
						break;
					case 'remove':
						$sql = "DELETE FROM {$wpdb->ms_transitions} WHERE id IN(".implode(",", $tid).")";
						break;
				}
				$wpdb->query($sql);
			}
		}
		if($results=$wpdb->get_results("select `key`, code, config from wp_ms_transitions where status=1")){
			$js = array();
			$config = array();
			foreach($results as $a){
				$js[] = base64_decode($a->code);
				if($conf = $a->config){
					$conf = json_decode($conf);
					$config[] = "jQuery.fn.mixSlideshow.configs['$a->key']={";
					for($i = 0, $n = count($conf); $i < $n; $i++){
						$config[] = "\t".$conf[$i]->name.":".$conf[$i]->value.($i == $n-1 ? '' : ",");
						
					}
					$config[] = "};";
				}
			}
			$packer = new JavaScriptPacker(implode(";", $js).";".implode("\n", $config));
			$js = $packer->pack();
			file_put_contents(MS_DIR."/assets/js/mixslideshow.transitions.min.js", $js);
			
			/*$packer = new JavaScriptPacker(file_get_contents(MS_DIR."/assets/js/script.js"));
			$js = $packer->pack();
			file_put_contents(MS_DIR."/assets/js/mixslideshow.min.js", $js);	*/
		}
	}
	function do_install(){
		if(!$_POST['do_install']) return;

		$fileName =  $_FILES['file_name']['name'];

		if(!$fileName) return;
		$sourcePath = $_FILES['file_name']['tmp_name'];
		$uploadPath = MS_TMP_DIR;
		
		$ext = array_pop(explode(".", $fileName));

		$allows= array("zip", "tar");
		
		if(!in_array($ext, $allows)){ return false;}
		
		if(move_uploaded_file($sourcePath, $uploadPath."/".$fileName)){

			WP_Filesystem();
			
			$basename = basename($fileName, '.zip');
			ms_rmdirr($uploadPath."/".$basename);
			$result = unzip_file($uploadPath."/".$fileName, $uploadPath."/".$basename);
			if($files = list_files($uploadPath."/".$basename)){
				global $wpdb;
				foreach($files as $file){
					$content = file_get_contents($file);
					
					preg_match_all("#(Transition\s+Name):(.*)#", $content, $name);
					preg_match_all("#(Screenshot):(.*)#", $content, $screenshot);
					preg_match_all("#(Params):(.*)#", $content, $params);

					if(!count($name)) continue;			

					$name = trim($name[2][0]);
					$screenshot = trim($screenshot[2][0]);
					$params = trim($params[2][0]);
										
					$key = strtolower(preg_replace("/\s+/", "_", $name));
					
					$sql = "
						SELECT `key` FROM {$wpdb->ms_transitions} WHERE `key`='$key'
					";
					$code = preg_replace("#\/\*(.*)\*\/#msU", "", $content);
					if($result=$wpdb->get_results($sql)){
						$sql = "
							UPDATE {$wpdb->ms_transitions}
							SET
								code='".base64_encode($code)."',
								screenshot = '".$screenshot."',
								config = '".$params."'
							WHERE `key`='$key'
						";
						$wpdb->query($sql);
					}else{
						$sql = "
							INSERT INTO {$wpdb->ms_transitions}(name, `default`, `status`, description, `code`, `key`, `screenshot`, `config`)
							VALUES('$name', 0, 1, '', '".base64_encode($code)."', '".$key."', '".$screenshot."', '".$params."')
						";
						$wpdb->query($sql);
					}
					//echo $sql;
				}
			}
		}else return;
		
		return $fileName;
		
	}
	function meta_box_context(){
		return 'xxx';
	}
	function display(){
		add_meta_box(
			/*id=*/ 'ms_transitions_box',
			/*title=*/ __('Transitions Manager'),
			/*callback=*/ array(&$this, 'manager_transitions_box'),
			/*page=*/$this->meta_box_context(),
			/*context =*/ $this->meta_box_context()
		);
		add_meta_box(
			/*id=*/ 'ms_upload_box',
			/*title=*/ __('Upload Transition(s)'),
			/*callback=*/ array(&$this, 'upload_transition_box'),
			/*page=*/$this->meta_box_context(),
			/*context =*/ $this->meta_box_context()
		);
		
		?>
        <style>
		.widefat{}
		.widefat tr.default td{
			background-color:#F4F4F4;	
		}
		.widefat td{
			vertical-align:middle;
		}
		</style>
        <div class="wrap">
            <div class="icon32 icon-slideshow-32"><br>
            </div>
            <h2>Manage Transitions</h2>
            <div id="post-body">
            	<table width="100%">
                	<tr>
                    	<td>
            	<?php $this->manager_transitions_box();?>
                		</td>
					</tr>
                    <tr>
                    	<td>                        
                        <div class="metabox-holder">
                            <div class="postbox">
                                <h3>Install new Transitions</h3>   
                                <div style="padding:10px;">             
                                <?php $this->upload_transition_box(); ?>
                                </div>
                            </div>
                        </div>      
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
        <button type="submit" class="button">Upload And Install</button>
    </form>
    <?php
	}
	function manager_transitions_box(){	
		$list_table = new WP_List_Table();
				
		$limit = $_POST['transitions_per_page'];
		if(!$limit) $limit = get_option(MS_FOLDER."_transitions_per_page");
		if(!$limit) $limit = 10;
		
		update_option(MS_FOLDER."_transitions_per_page", $limit);
		
		$page = $list_table->get_pagenum();

		$result = $this->get_transitions($limit, $page);
		$count = $result['total'];
		$transitions = $result['items'];
		$list_table->set_pagination_args(array(
			'total_items' => $count,
			'per_page' => $limit
			)
		);
	?>
    <style>
	.widefat thead th,
	.widefat tfoot th{
		text-align:center;
	}
	</style>
    <form name="transitions_list" method="post">
    <div class="tablenav top">
    	<table width="100%">
        	<tr>
            	<td>
                Show <?php ms_show_items_per_page_list('transitions_per_page', $limit);?> items
                <select name="bulk_action">
                	<option value="">Bulk Action</option>
                	<option value="active">Active</option>
                    <option value="deactive">Decctive</option>
                    <option value="remove">Remove</option>
                </select>
                <button type="submit" class="button">Apply</button>
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
                <th class="check-column" scope="col" width="50" style="text-align:center;"><input type="checkbox" class="chk-all"></th>
                <th width="50" style="text-align:right;">#</th>
                <th scope="col" style="text-align:left;">Name</th>
                <th scope="col" width="80" align="center">Active</th>
                <th width="100" align="center">Screenshot</th>
                <th width="100" align="center">Config</th>
                <th width="80">Remove</th>
            </tr>
        </thead>
        <tfoot>
        	<tr>
            	<th class="check-column" scope="col" style="text-align:center;"><input type="checkbox" class="chk-all"></th>
                <th  style="text-align:right;">ID</th>
                <th scope="col" style="text-align:left;">Name</th>
                <th scope="col">Active</th>
                <th>Screenshot</th>
                <th>Config</th>
                <th>Remove</th>
            </tr>
        </tfoot>
	    <tbody>
       	<?php if($count){$i=0;?>
        <?php foreach($transitions as $row){?>
        <?php	
			$active_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			
			$active_link = remove_query_arg( array( 'task', 'id' ), $active_link );
			
			$active_link .= '&noheader=true&&task='.($row->status==1 ? 'deactive' : 'active').'&id='.$row->id;
			$active_text = ($row->status==1 ? 'Active' : 'Deactive');						
			
			$active_icon = MS_URL."/assets/images/".($row->status ? "tick.png" : "untick.png");
			$active_title = ($row->status==1 ? "Deactive" : "Active")." this transition";
			////////////
			
			$remove_link = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];				
			$remove_link = remove_query_arg( array( 'task', 'id' ), $active_link );
			$remove_link .= '&noheader=true&task=remove&id='.$row->id;
			$remove_link = "if(confirm('Are you sure')) location.href='".$remove_link."'";
			$remove_title = "Remove this transition";
			$remove_icon = MS_URL."/assets/images/delete_16x16.gif";
			
			$class = ($i%2==0 ? 'row1' : 'row0');
			$i++;
			$index = ($page-1)*$limit+$i;
			///////////////
			
		?>
        	<tr class="<?php echo $class;?>">
            	<td style="text-align:center;"><input type="checkbox" name="tid[]" value="<?php echo $row->id;?>" class="chk-row" /></td>
            	<td align="right"><?php echo $index;?></td>
                <td align="left"><?php echo $row->name;?></td>
                <td align="center"><a href="<?php echo $active_link;?>" title="<?php echo $active_title;?>">
					<img src="<?php echo $active_icon;?>" border="0" />
                </a></td>
                <td align="center">
                <?php
				if($row->screenshot){
					$screenshot = "data:image/png;base64,".$row->screenshot;
					echo '<img src="<?php echo $screenshot;?>" style="max-width:34px;max-height:26px;" />';
				}else{
					_e('No screenshot', MS_DOMAIN);
				}
				?>
                	
                </td>
                <td align="center">
                <?php if($row->config){?>
                <a href="<?php echo MS_URL;?>/transition-config.php?id=<?php echo $row->id;?>&TB_iframe=1&width=600&height=300" class="thickbox">
                	<img src="<?php echo MS_URL."/assets/images/config.png";?>" border="0" />
                </a>
                <?php }else{?>
                No config
                <?php }?>
                </td>
                <td align="center" width="">
                	<a href="javascript: void();" onclick="<?php echo $remove_link;?>" title="<?php echo $remove_title;?>">
                		<img src="<?php echo $remove_icon;?>" border="0" align="<?php echo $remove_title;?>" />
                    </a>
				</td>
            </tr>
		<?php }?>            
		<?php }else{?>            
        	<tr>
            	<td colspan="7">No transition(s) found</td>
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

	function get_transitions($limit, $page){
		global $wpdb;
		$start = ($page-1)*$limit;
		$sql = "
			SELECT * FROM {$wpdb->ms_transitions}
		";
		$total = count($wpdb->get_results($sql));
		$sql = "
			SELECT * FROM {$wpdb->ms_transitions}
			ORDER BY name ASC
			LIMIT $start, $limit			
		";
		$transitsions = $wpdb->get_results($sql);
		return array('items' => $transitsions, 'total' => $total);
	}
}

$obj = new ManageTransitions();
$obj->display();

?>
    
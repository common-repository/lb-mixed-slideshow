<div class="metabox-holder">
<div class="postbox">
<h3>Gallery Settings</h3>
<form method="post" action="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=ms-manage-gallery&noheader=true" enctype="multipart/form-data" name="adminForm"> 
<table cellpadding="10" cellspacing="10" class="form-table">
	<tr>
    	<th width="30%">Name</td>
        <td><input type="text" name="form[name]" value="<?php echo $item->name;?>" size="100" /></td>
    </tr>
    <tr>
    	<td>Title</td>
        <td><input type="text" name="form[title]" value="<?php echo $item->title;?>" size="100" /></td>
    </tr>
    <tr>
    	<td>Description</td>
        <td><textarea name="form[description]" cols="100" rows="10"><?php echo $item->description;?></textarea></td>
    </tr>
    <tr>
    	<td>Preview</td>
        <td>
        	<?php if($item->thumbname){?>
        	<img src="<?php echo MS_URL."/gallery/{$item->id}/thumbs/".$item->thumbname;?>" style="max-width:100px;max-height:100px;" /><br />
            <?php }?>
            <select name="form[featured_image]">
            	<option value="">No selected</option>
            	<?php echo $item->images_list;?>
            </select>
            <?php if(!$item->id || !$item->images_list){?>
            <font color="#FF0000">
            	<?php if(!$item->id){?>(Please save gallery first)
				<?php }else{?>
                (Please <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=ms-manage-images&gid=<?php echo $item->id;?>">upload images</a> to gallery first)
                <?php }?>
                </font>
            <?php }?>
        </td>
    </tr>
    <tr>
    	<td>Image Size (width x height)</td>
        <td>
        	<input type="text" value="<?php echo $item->params->image_width;?>" name="form[params][image_width]" /> x 
            <input type="text" value="<?php echo $item->params->image_height;?>" name="form[params][image_height]" />
            (px)
             
        </td>
    </tr>
    <tr>
    	<td>Auto resize on upload</td>
        <td>
        	<input type="checkbox" name="form[params][auto_resize]" <?php echo $item->params->auto_resize ? 'checked="checked"' : '';?> />
        	
        </td>
    </tr>
    <tr>
    	<td>Thumbnail (width x height)</td>
        <td>
        	<input type="text" value="<?php echo $item->params->thumb_width;?>" name="form[params][thumb_width]" /> x 
            <input type="text" value="<?php echo $item->params->thumb_height;?>" name="form[params][thumb_height]" />
            (px)
        </td>
    </tr>
    <tr>
    	<td></td>
        <td>
        	<button type="button" class="button" onclick="doTask('save');">Save</button>
            <button type="button" class="button" onclick="doTask('apply');">Apply</button>
            <button type="button" class="button" onclick="doTask('save_new');">Save & New</button>
            <button type="button" class="button" onclick="doTask('save_upload');">Save & Upload Images</button>            
            <button type="button" class="button" onclick="doTask('cancel');">Cancel</button>
        </td>
    </tr>
    <?php if($item->idx){?>
    <tr>
    	<td></td>
        <td>
        	<?php $this->upload_box();?>
        </td>
    </tr>
    <?php }?>
</table>
<input type="hidden" name="task" />
<input type="hidden" name="noheader" value="true" />
<input type="hidden" name="id" value="<?php echo $item->id;?>" />
</form>
</div>
</div>
<div class="metabox-holder">
    <div class="postbox">
	    <h3>Shortcode Settings</h3>
        <div style="padding:5px;">
        	<form action="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=ms-manage-gallery&noheader=true" method="post">
            	<input type="hidden" name="task" value="save_setting" />
                <input type="hidden" name="id" value="<?php echo $item->id;?>" />
                <div  id="shortcode-settings">
                	<?php if($item->id){?>
                    <?php $this->get_shortcode_setting($item);?>
                	
                    <?php }else{?>
                    Shortcode not available until you save the gallery
                    <?php }?>
                </div>
            
            </form>
        </div>
    </div>
</div>
<script>
function doTask(task){
	var form = document.adminForm;
	form.task.value = task;
	form.submit();
}
</script>

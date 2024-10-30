<?php
class msUninstall{
	private $confirm = 0;
	private $message = null;
	function __construct(){
		$this->confirm = $_POST['confirm'];
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
			switch($_POST['action']){
				case 'remove-table':
					//if(!$this->confirm){
					//	$this->confirm = 1;
					//	$this->setMessage("Please confirm that you want to remove all database tables");
					//}else{
						$this->removeTables();
					//}
					break;
				case 'remove-option':
					$this->removeOptions();
					break;
				case 'reset-options':
					$this->resetOption();
					break;
			}
		}
		$this->display();
	}
	function setMessage($mess){
		$this->message = $mess;
	}
	function showMessage(){
		if($this->message){
	?>
    <p style="background-color:#FFFBCC;border:1px solid #E6DB55;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;text-align:center;padding:5px;color:#FF0000;">
    <?php print_r($this->message);?>
    </p>
	<?php
			$this->setMessage("");
		}
	}
	function resetOptions(){
	
	}
	function removeOptions(){
		delete_option(MS_FOLDER."_galleries_per_page");
		delete_option(MS_FOLDER."_images_per_page");
		delete_option(MS_FOLDER."_transitions_per_page");
		delete_option("slideshow_config");
		$this->setMessage("All options are removed");		
	}
	function removeTables(){
		global $wpdb;
		$tables = array($wpdb->ms_images, $wpdb->ms_gallery, $wpdb->ms_transitions);
		$query = "DROP TABLE `".implode("`,`", $tables)."`";
		$wpdb->query($query);
		$this->setMessage("All tables are removed");
	}
	function display(){			
?>
<style type="text/css">
table.options-wrapper{
	width:100%;	
}
table.options-wrapper td{
	width:50%;	
}
table.options-wrapper td .option{
	margin:3px;
	-webkit-border-radius:4px;
	-moz-border-radius:4px;
	border-radius:4px;
	border:1px solid #E6DB55;	
	background-color:##FFFB97;
	padding:30px;
}
table.options-wrapper td .option ul li{
	margin-left:20px;
	font-style:italic;
	list-style:square inside;
}
</style>
<div class="wrap">
	<div class="icon32 icon-slideshow-32"><br>
	</div>
	<h2>Reset / Uninstall</h2>
    <p>Thank you for using lb Mixed Slideshow.</p>
	<p>You can remove all tables, reset or remove options before uninstall plugin in wordpress plugin page.</p>
	<p>This action can not be undone so please make sure that you realy want to reset/uninstall this plugin.</p>
    <table width="100%" class="options-wrapper">
    	<tr>
        	<td width="33%" valign="top"><?php $this->removeTablesForm();?></td>
            <td width="33%" valign="top"><?php $this->removeOptionsForm();?></td>
		</tr>            
    </table>
</div>
<?php 
	}
	function removeTablesForm(){
		global $wpdb;
	?>
    <div class="option">
    <p>You should backup database tables for lb Mixed Slideshow first, tables are listed below</p>
    <ul>
    	<li><?php echo $wpdb->ms_images;?></li>
        <li><?php echo $wpdb->ms_gallery;?></li>
        <li><?php echo $wpdb->ms_transitions;?></li>
    </ul>
    <?php if($_POST['action'] == 'remove-table') $this->showMessage();?>
	<form action="" method="post">
    	<input type="hidden" name="action" value="remove-table" />        
        <p>
        	<input type="checkbox" onchange="this.form.button_uninstall.disabled = !this.checked;" />
            I am sure that I want to remove all tables
        </p>
		<p><button name="button_uninstall" type="button" class="button" onclick="this.form.submit();" disabled="disabled">Uninstall</button></p>
	</form>
    </div>
    <?php
	}
	function resetOptionsForm(){
	?>
    <div class="option">
    <p>Reset options</p>
	<form action="" method="post">
    	<input type="hidden" name="action" value="remove-table" />
        <input type="hidden" name="confirm" value="<?php echo $this->confirm;?>" />
        <?php $this->showMessage();?>
        <p>
        	<input type="checkbox" onchange="this.form.button_uninstall.disabled = !this.checked;" />
            I am sure that I want to reset all options
        </p>
		<p><button name="button_uninstall" type="button" class="button" onclick="this.form.submit();" disabled="disabled">Uninstall</button></p>
	</form>
    </div>
    <?php
	}
	function removeOptionsForm(){
	?>
    <div class="option">
    <p>Remove all options are used by lb Mixed Slideshow</p>
    <?php $this->showMessage();?>
	<form action="" method="post">
    	<input type="hidden" name="action" value="remove-option" />
        <input type="hidden" name="confirm" value="<?php echo $this->confirm;?>" />
        <?php if($_POST['action'] == 'remove-option') $this->showMessage();?>
        <p>
        	<input type="checkbox" onchange="this.form.button_uninstall.disabled = !this.checked;" />
            I am sure that I want to remove all options
        </p>
		<p><button name="button_uninstall" type="button" class="button" onclick="this.form.submit();" disabled="disabled">Remove</button></p>
	</form>
    </div>
    <?php
	}
}
new msUninstall();
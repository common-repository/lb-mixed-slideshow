<?php
if(strtolower($_SERVER['REQUEST_METHOD'])=='post'){
	ms_set_config($_POST['config']);
}
$config = ms_get_config();
?>
<div class="wrap">
	<div class="icon32 icon-slideshow-32"><br>
	</div>
	<h2>Default Configuration</h2>
    <p>Set default parameter for all slideshow, when you add shortcode with out an option, slideshow will be get value of that option from default configuration</p>
    <p>E.g : If you not set the width and height of slideshow, and you set the default configuration of width and height are 800 and 600 then the size of slideshow is 800x600
    </p> 
    <p>Change any value you want and click 'Save Configuration'</p>
    <form name="" action="" method="post">
    <table>
    	<thead>
        	<tr><td width="200"></td><td width="200">Param Name</td><td width="10">&nbsp;</td><td>Default Value</td><td></td></tr>
        </thead>
        <tbody>
    	<tr>
        	<td class="label">Width</td><td>(width)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[width]" value="<?php echo $config['width'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td>Height</td><td>(height)</td>
            <td>&nbsp;</td>
            <td><input type="text" name="config[height]" value="<?php echo $config['height'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td>Border Width</td><td>(border_width)</td>
            <td>&nbsp;</td>
            <td><input type="text" name="config[border_width]" value="<?php echo $config['border_width'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td>Border Color</td><td>(border_color)</td>
            <td align="right">#</td>
            <td><input type="text" id="config_border_color" name="config[border_color]" value="<?php echo $config['border_color'];?>" />(e.g: 333333)</td>
            <td></td>
        </tr>
        <tr>
        	<td>Border Image</td><td>(border_image)</td>
            <td>&nbsp;</td>
            <td><input type="text" name="config[border_image]" value="<?php echo $config['border_image'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Limit Start</td><td>(limitstart)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[limitstart]" value="<?php echo $config['limitstart'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Limit</td><td>(limit)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[limit]" value="<?php echo $config['limit'];?>" /></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Transitions</td><td>(transitions)</td>
            <td>&nbsp;</td>
            <td class="value">
           	<?php echo ms_get_list_transitions('config[transitions][]', 'config_transitions', 10, true, explode(" ", $config['transitions']));?>
            
           	</td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Time Delay</td><td>(time_delay)</td>
            <td>&nbsp;</td>
            <td class="value"><input type="text" name="config[time_delay]" value="<?php echo $config['time_delay'];?>" /></td>
            <td></td>
        </tr>
        <!--
        <tr>
        	<td class="label">Randomize Transitions</td><td>(random_transitions)</td>
            <td class="value"><?php ms_yes_no('config[random_transitions]', $config['random_transitions']);?></td>
            <td>?</td>
        </tr>
         <tr>
        	<td class="label">Randomize Slide</td><td>(random_slide)</td>
            <td class="value"><?php ms_yes_no('config[random_slide]', $config['random_slide']);?></td>
            <td>?</td>
        </tr>
        -->
        <tr>
        	<td class="label">Show Description</td><td>(show_description)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_description]', $config['show_description']);?></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Show Thumbnails</td><td>(show_thumb)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_thumb]', $config['show_thumb']);?></td>
            <td></td>
        </tr>
        <tr>
        	<td class="label">Show Top Navigator</td><td>(show_top_nav)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_top_nav]', $config['show_top_nav']);?></td>
            <td></td>
        </tr>
         <tr>
        	<td class="label">Show Navigator</td><td>(show_nav)</td>
            <td>&nbsp;</td>
            <td class="value"><?php ms_yes_no('config[show_nav]', $config['show_nav']);?></td>
            <td></td>
        </tr>       
        <tr>
        	<td></td>
        	<td colspan="4" align="left" style="background-color:#FFF;padding:3px;">
            Shortcode (copy code below and paste it into your post/page)
            <p id="shortcode"><?php echo ms_get_shortcode();?></p>
            </td>
        </tr>
        <tr>
        	<td></td>
            <td colspan="4">
            	<button type="submit" class="button">Save Configuration</button>
                <button type="button" class="button" id="b_copy_shortcode">Copy Shortcode to Clipboard</button>
			</td>
        </tr>
        </tbody>
    </table>
    </form>
</div>   
<script type="text/javascript" src="<?php echo MS_URL."/libs/ZeroClipboard/ZeroClipboard.js";?>"></script>
<script type="text/javascript">
jQuery(document).ready(function($){
	var clip = null;
	// Enable Rich HTML support (Flash Player 10 Only)
	ZeroClipboard.setMoviePath( '<?php echo MS_URL."/libs/ZeroClipboard/ZeroClipboard10.swf";?>' );

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
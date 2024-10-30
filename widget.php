<?php
class mixedSlideshowWidget extends WP_Widget {
    /** constructor */
    function mixedSlideshowWidget() {
        parent::WP_Widget(false, $name = 'mixedSlideshowWidget');	
    }
    /**  */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget; 
		if ( $title ){
			echo $before_title . $title . $after_title; 
		}
		$default = ms_get_gallery_attr($instance['gid']);
		//echo $instance['gid'];
		$instance = array_merge($default, $instance);
		//print_r($instance);
		ms_do_mixed_slideshow($instance);
		//echo ssovit_slideshow($instance['slideshowid'],$instance['width'],$instance['height']);
		echo $after_widget; 
    }
    /* */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['gid'] = $new_instance['gid'];
		$instance['width'] =  $new_instance['width'];
		$instance['height'] = $new_instance['height'];
		$instance['time_delay'] = $new_instance['time_delay'];
		
		return $instance;
    }
    /**  */
    function form($instance) {		
        $title = esc_attr($instance['title']);
        $width = $instance['width'] ? esc_attr($instance['width']) : 100;
        $height = $instance['height'] ? esc_attr($instance['height']) : 100;
		$delay = $instance['time_delay'] ? esc_attr($instance['time_delay']) : 3000;
        ?>
        <p>
        	<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?> 
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
		</p>
        <p>
        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Slideshow:'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('gid'); ?>" id="<?php echo $this->get_field_id('slideshowid'); ?>" class="widefat">
			<?php
			global $wpdb;
			$sql = "
				SELECT *
				FROM {$wpdb->ms_gallery}
			";
			$galleries = $wpdb->get_results($sql);
			if($galleries) {
				foreach($galleries as $g) {
				echo '<option value="'.$g->id.'" ';
				if ($g->id == $instance['gid']) echo 'selected="selected"';
				echo '>'.$g->title.'</option>'."\n\t"; 
				}
			}
?>
				</select>
</p>
            <p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" /></label></p>
        <?php
		$uniqueID = "more-settings-".uniqid(md5(time()));
		?>
		<div id="<?php echo $uniqueID; ?>">
        	<p>
            	<?php _e('Delay:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('time_delay'); ?>" name="<?php echo $this->get_field_name('time_delay'); ?>" type="text" value="<?php echo $delay; ?>" />
            </p>
        </div>   
        <?php
		/*
        ?>
		<p><a id="btn-more-settings" href="javascript:void(0);" onclick="tonggle_settings('<?php echo $uniqueID;?>', this);"><?php _e("...more", PS_DOMAIN);?></a></p>
        <script type="text/javascript">
		var tonggle_settings = null;
		(function($){
			tonggle_settings = function(elem, self){
				$("#"+elem).toggle("slow");
				if($(self).html()=='<?php _e("...more", PS_DOMAIN);?>'){
					$(self).html('<?php _e("...less", PS_DOMAIN);?>');
				}else{
					$(self).html('<?php _e("...more", PS_DOMAIN);?>');
				}
				//alert($("#more-settings").html());
			}
		})(jQuery);
		</script>
        <?php 
		*/
    }
    
} // class FooWidget
add_action('widgets_init', create_function('', 'return register_widget("mixedSlideshowWidget");'));
?>
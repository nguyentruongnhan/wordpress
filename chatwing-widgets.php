<?php

class WidgetChatWing extends WP_Widget {

	function __construct() {
		$id_base = 'widget-chatwing';
		$name = 'ChatWing widget';
		$widget_options = array( 'classname' => 'widget-chatwing', 'description' => 'ChatWing Widget' );

		parent::__construct($id_base, $name, $widget_options);
	}

	function WidgetChatWing() {
		WidgetChatWing::__construct();
	}

	function form($instance) {
		$config = cw_get_config('display');
		$instance = wp_parse_args((array)$instance, array('title' => '', 'width' => $config['width'], 'height' => $config['height']) );
		extract($instance, EXTR_SKIP);

		$chatboxes = cw_get_chatbox();
		?>
<p>
	<label for="<?php echo $this->get_field_id('title') ?>">Title</label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo esc_attr($title) ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('chatbox') ?>">Chatbox</label>
	<select id="<?php echo $this->get_field_id('chatbox') ?>" name="<?php echo $this->get_field_name('chatbox') ?>" class="widefat">
		<option value="">Choose a chatbox</option>
		<?php foreach ((array)$chatboxes as $cb) : ?>
		<option <?php selected($cb['key'], $chatbox) ?> value="<?php echo $cb['key'] ?>"><?php printf('%s (%s)', $cb['name'], $cb['alias']) ?> </option>
		<?php endforeach; ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('width') ?>">Width</label>
	<input type="text" class="cw-slider small-text" min="200" max="1000" step="1" id="<?php echo $this->get_field_id('width') ?>" name="<?php echo $this->get_field_name('width') ?>" value="<?php echo esc_attr($width) ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('height') ?>">Height</label>
	<input type="text" class="cw-slider small-text" min="200" max="1000" step="1" id="<?php echo $this->get_field_id('height') ?>" name="<?php echo $this->get_field_name('height') ?>" value="<?php echo esc_attr($height) ?>" />
</p>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['chatbox'] = $new_instance['chatbox'];
		$instance['width'] = $new_instance['width'];
		$instance['height'] = $new_instance['height'];

		return $instance;
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		echo $before_widget;

		if ( $title)
			echo $before_title.$title.$after_title;

		$atts = '';
		if ( !empty($instance) ) {
			foreach ($instance as $k => $v) {
				$atts .= sprintf(' %s="%s"', $k, $v);
			}
		}

		echo do_shortcode('[chatwing '.$atts.']');

		echo $after_widget;
	}

}

/**
 * Init ChatWing widget
 */
function cw_widgets_init() {
	register_widget('WidgetChatWing');
}
add_action('widgets_init', 'cw_widgets_init');


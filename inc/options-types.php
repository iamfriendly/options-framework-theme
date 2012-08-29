<?php

	/**
	 * An extendable class for all of our options types. Subclasses need only overwrite html() and can conditionally
	 * load extra styles and or js
	 */

	abstract class OF_Field
	{

		/**
		 * Set up our options, stored values and run the routine to output the form fields
		 */

		public function __construct( $counter, $value, $val, $select_value, $checked, $option_name, $menu, $output, $explain_value, $allowedtags )
		{

			$this->counter = $counter;
			$this->value = $value;
			$this->val = $val;
			$this->select_value = $select_value;
			$this->checked = $checked;
			$this->option_name = $option_name;
			$this->menu = $menu;
			$this->output = $output;
			$this->explain_value = $explain_value;
			$this->allowedtags = $allowedtags;

			add_action( 'admin_footer', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_footer', array( &$this, 'enqueue_styles' ) );

			$this->display();

		}/* __construct() */


		public function display()
		{

			$this->html();

			if ( ( $this->value['type'] != "heading" ) && ( $this->value['type'] != "info" ) ){

				$this->output .= '</div>';

				if ( ( $this->value['type'] != "checkbox" ) && ( $this->value['type'] != "editor" ) ) {
					$this->output .= '<div class="explain">' . wp_kses( $this->explain_value, $this->allowedtags) . '</div>'."\n";
				}

				$this->output .= '</div></div>'."\n";

			}


		}/* display() */


		/**
		 * New fields can overwrite this function to load new CSS (loaded in the footer)
		 * ToDo: Need to ensure we're only loading these on the options page
		 */

		public function enqueue_styles(){}/* enqueue_styles */

		/**
		 * New fields can overwrite this function to load new js (loaded in the footer)
		 * ToDo: Need to ensure we're only loading these on the options page
		 */

		public function enqueue_scripts(){}/* enqueue_scripts */

	}/* class OF_Field */


	/* ============================================================================================================ */

	/**
	 * The 'heading' class which gives us a container to place our options
	 */

	class OF_Field_heading extends OF_Field
	{

		public function html()
		{

			if ($this->counter >= 2) {
				$this->output .= '</div>'."\n";
			}

			$jquery_click_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($this->value['name']) );
			$jquery_click_hook = "of-option-" . $jquery_click_hook;
		
			$this->output .= '<div class="group" id="' . esc_attr( $jquery_click_hook ) . '">';
			$this->output .= '<h3>' . esc_html( $this->value['name'] ) . '</h3>' . "\n";

			return $this->output;
		
		}/* html() */

	}/* class OF_Field_heading */


	/* ============================================================================================================ */

	/**
	 * Simple text input field but also shows examples of how to load css/js per field item
	 */

	class OF_Field_text extends OF_Field
	{

		public function html()
		{

			$this->output .= '<input id="' . esc_attr( $this->value['id'] ) . '" class="of-input" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . ']' ) . '" type="text" value="' . esc_attr( $this->val ) . '" />';

			return $this->output;

		}/* html() */


		public function enqueue_scripts()
		{

			parent::enqueue_scripts();

			//wp_enqueue_script( 'testing', OPTIONS_FRAMEWORK_DIRECTORY . 'js/test.js', 'jquery' );

		}/* enqueue_scripts() */


		public function enqueue_styles()
		{

			parent::enqueue_styles();

			//wp_enqueue_style() call here

		}/* enqueue_styles() */

	}/* class OF_Field_text */


	/* ============================================================================================================ */

	/**
	 * Simple textarea
	 */

	class OF_Field_textarea extends OF_Field
	{

		public function html()
		{

			if ( isset( $this->value['settings']['rows'] ) ) {
				$custom_rows = $this->value['settings']['rows'];
				if ( is_numeric( $custom_rows ) ) {
					$rows = $custom_rows;
				}
			}else{
				$rows = 8;
			}

			$val = stripslashes( $this->val );
			$this->output .= '<textarea id="' . esc_attr( $this->value['id'] ) . '" class="of-input" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . ']' ) . '" rows="' . $rows . '">' . esc_textarea( $this->val ) . '</textarea>';

			return $this->output;

		}/* html() */

	}/* OF_Field_textarea */


	/* ============================================================================================================ */

	/**
	 * A simple <select> dropdown
	 */

	class OF_Field_select extends OF_Field
	{

		public function html()
		{

			$this->output .= '<select class="of-input" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . ']' ) . '" id="' . esc_attr( $this->value['id'] ) . '">';

			foreach ($this->value['options'] as $key => $option ) {
				$selected = '';
				if ( $this->val != '' ) {
					if ( $this->val == $key) { $selected = ' selected="selected"';}
				}
				$this->output .= '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
			}
			$this->output .= '</select>';

			return $this->output;

		}/* html() */

	}/* OF_Field_select */


	/* ============================================================================================================ */

	/**
	 * Radio list items
	 */

	class OF_Field_radio extends OF_Field
	{

		public function html()
		{

			$name = $this->option_name .'['. $this->value['id'] .']';
			foreach ($this->value['options'] as $key => $option) {
				$id = $this->option_name . '-' . $this->value['id'] .'-'. $key;
				$this->output .= '<input class="of-input of-radio" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="'. esc_attr( $key ) . '" '. checked( $this->val, $key, false) .' /><label for="' . esc_attr( $id ) . '">' . esc_html( $option ) . '</label>';
			}

			return $this->output;

		}/* html() */

	}/* class OF_Field_radio */


	/* ============================================================================================================ */

	/**
	 * An info bar which shows just some text and a heading
	 */

	class OF_Field_info extends OF_Field
	{

		public function html()
		{

			$id = '';
			$class = 'section';
			if ( isset( $this->value['id'] ) ) {
				$id = 'id="' . esc_attr( $this->value['id'] ) . '" ';
			}
			if ( isset( $this->value['type'] ) ) {
				$class .= ' section-' . $this->value['type'];
			}
			if ( isset( $this->value['class'] ) ) {
				$class .= ' ' . $this->value['class'];
			}

			$this->output .= '<div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";
			if ( isset($this->value['name']) ) {
				$this->output .= '<h4 class="heading">' . esc_html( $this->value['name'] ) . '</h4>' . "\n";
			}
			if ( $this->value['desc'] ) {
				$this->output .= apply_filters('of_sanitize_info', $this->value['desc'] ) . "\n";
			}
			$this->output .= '</div>' . "\n";

			return $this->output;

		}/* html() */

	}/* class OF_Field_info */


	/* ============================================================================================================ */

	/**
	 * A single checkbox
	 */

	class OF_Field_checkbox extends OF_Field
	{

		public function html()
		{

			$this->output .= '<input id="' . esc_attr( $this->value['id'] ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . ']' ) . '" '. checked( $this->val, 1, false) .' />';
			$this->output .= '<label class="explain" for="' . esc_attr( $this->value['id'] ) . '">' . wp_kses( $this->explain_value, $this->allowedtags) . '</label>';

			return $this->output;

		}/* html() */

	}/* class OF_Field_checkbox */


	/* ============================================================================================================ */

	/**
	 * An upload field
	 */

	class OF_Field_upload extends OF_Field
	{

		public function html()
		{

			return $this->output .= optionsframework_medialibrary_uploader( $this->value['id'], $this->val, null );

		}/* html() */

	}/* OF_Field_upload */


	/* ============================================================================================================ */

	/**
	 * A spoofed radio list which uses images instead of radio buttons
	 */

	class OF_Field_images extends OF_Field
	{

		public function html()
		{

			$name = $this->option_name .'['. $this->value['id'] .']';
			foreach ( $this->value['options'] as $key => $option ) {
				$selected = '';
				$checked = '';
				if ( $this->val != '' ) {
					if ( $this->val == $key ) {
						$selected = ' of-radio-img-selected';
						$checked = ' checked="checked"';
					}
				}
				$this->output .= '<input type="radio" id="' . esc_attr( $this->value['id'] .'_'. $key) . '" class="of-radio-img-radio" value="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" '. $checked .' />';
				$this->output .= '<div class="of-radio-img-label">' . esc_html( $key ) . '</div>';
				$this->output .= '<img src="' . esc_url( $option ) . '" alt="' . $option .'" class="of-radio-img-img' . $selected .'" onclick="document.getElementById(\''. esc_attr($this->value['id'] .'_'. $key) .'\').checked=true;" />';
			}

			return $this->output;

		}/* html() */

	}/* class OF_Field_images */


	/* ============================================================================================================ */

	/**
	 * Background CSS options
	 */

	class OF_Field_background extends OF_Field
	{

		public function html()
		{

			$background = $this->val;

			// Background Color
			$this->output .= '<div id="' . esc_attr( $this->value['id'] ) . '_color_picker" class="colorSelector"><div style="' . esc_attr( 'background-color:' . $background['color'] ) . '"></div></div>';
			$this->output .= '<input class="of-color of-background of-background-color" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][color]' ) . '" id="' . esc_attr( $this->value['id'] . '_color' ) . '" type="text" value="' . esc_attr( $background['color'] ) . '" />';

			// Background Image - New AJAX Uploader using Media Library
			if (!isset($background['image'])) {
				$background['image'] = '';
			}

			$this->output .= optionsframework_medialibrary_uploader( $this->value['id'], $background['image'], null, '', 0, 'image');
			$class = 'of-background-properties';
			if ( '' == $background['image'] ) {
				$class .= ' hide';
			}
			$this->output .= '<div class="' . esc_attr( $class ) . '">';

			// Background Repeat
			$this->output .= '<select class="of-background of-background-repeat" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][repeat]'  ) . '" id="' . esc_attr( $this->value['id'] . '_repeat' ) . '">';
			$repeats = of_recognized_background_repeat();

			foreach ($repeats as $key => $repeat) {
				$this->output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['repeat'], $key, false ) . '>'. esc_html( $repeat ) . '</option>';
			}
			$this->output .= '</select>';

			// Background Position
			$this->output .= '<select class="of-background of-background-position" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][position]' ) . '" id="' . esc_attr( $this->value['id'] . '_position' ) . '">';
			$positions = of_recognized_background_position();

			foreach ($positions as $key=>$position) {
				$this->output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['position'], $key, false ) . '>'. esc_html( $position ) . '</option>';
			}
			$this->output .= '</select>';

			// Background Attachment
			$this->output .= '<select class="of-background of-background-attachment" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][attachment]' ) . '" id="' . esc_attr( $this->value['id'] . '_attachment' ) . '">';
			$attachments = of_recognized_background_attachment();

			foreach ($attachments as $key => $attachment) {
				$this->output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['attachment'], $key, false ) . '>' . esc_html( $attachment ) . '</option>';
			}
			$this->output .= '</select>';
			$this->output .= '</div>';

			return $this->output;

		}/* html() */

	}/* class OF_Field_background */


	/* ============================================================================================================ */

	/**
	 * Multichecks
	 */

	class OF_Field_multicheck extends OF_Field
	{

		public function html()
		{

			foreach ($this->value['options'] as $key => $option) {
				$checked = '';
				$label = $option;
				$option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

				$id = $this->option_name . '-' . $this->value['id'] . '-'. $option;
				$name = $this->option_name . '[' . $this->value['id'] . '][' . $option .']';

				if ( isset($this->val[$option]) ) {
					$checked = checked($this->val[$option], 1, false);
				}

				$this->output .= '<input id="' . esc_attr( $id ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $name ) . '" ' . $checked . ' /><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			}

			return $this->output;

		}/* html() */

	}/* class OF_Field_multicheck */


	/* ============================================================================================================ */

	/**
	 * Color picker
	 * ToDo: Remove the colorpicker js from the core framework and load here instead
	 */

	class OF_Field_color extends OF_Field
	{

		public function html()
		{

			$this->output .= '<div id="' . esc_attr( $this->value['id'] . '_picker' ) . '" class="colorSelector"><div style="' . esc_attr( 'background-color:' . $this->val ) . '"></div></div>';
			$this->output .= '<input class="of-color" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . ']' ) . '" id="' . esc_attr( $this->value['id'] ) . '" type="text" value="' . esc_attr( $this->val ) . '" />';

			return $this->output;

		}/* html() */

	}/* class OF_Field_color */


	/* ============================================================================================================ */

	/**
	 * Typography options
	 */

	class OF_Field_typography extends OF_Field
	{

		public function html()
		{

			unset( $font_size, $font_style, $font_face, $font_color );
			
			$typography_defaults = array(
				'size' => '',
				'face' => '',
				'style' => '',
				'color' => ''
			);
			
			$typography_stored = wp_parse_args( $this->val, $typography_defaults );
			
			$typography_options = array(
				'sizes' => of_recognized_font_sizes(),
				'faces' => of_recognized_font_faces(),
				'styles' => of_recognized_font_styles(),
				'color' => true
			);
			
			if ( isset( $this->value['options'] ) ) {
				$typography_options = wp_parse_args( $this->value['options'], $typography_options );
			}

			// Font Size
			if ( $typography_options['sizes'] ) {
				$font_size = '<select class="of-typography of-typography-size" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][size]' ) . '" id="' . esc_attr( $this->value['id'] . '_size' ) . '">';
				$sizes = $typography_options['sizes'];
				foreach ( $sizes as $i ) {
					$size = $i . 'px';
					$font_size .= '<option value="' . esc_attr( $size ) . '" ' . selected( $typography_stored['size'], $size, false ) . '>' . esc_html( $size ) . '</option>';
				}
				$font_size .= '</select>';
			}

			// Font Face
			if ( $typography_options['faces'] ) {
				$font_face = '<select class="of-typography of-typography-face" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][face]' ) . '" id="' . esc_attr( $this->value['id'] . '_face' ) . '">';
				$faces = $typography_options['faces'];
				foreach ( $faces as $key => $face ) {
					$font_face .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['face'], $key, false ) . '>' . esc_html( $face ) . '</option>';
				}
				$font_face .= '</select>';
			}

			// Font Styles
			if ( $typography_options['styles'] ) {
				$font_style = '<select class="of-typography of-typography-style" name="'.$this->option_name.'['.$this->value['id'].'][style]" id="'. $this->value['id'].'_style">';
				$styles = $typography_options['styles'];
				foreach ( $styles as $key => $style ) {
					$font_style .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['style'], $key, false ) . '>'. $style .'</option>';
				}
				$font_style .= '</select>';
			}

			// Font Color
			if ( $typography_options['color'] ) {
				$font_color = '<div id="' . esc_attr( $this->value['id'] ) . '_color_picker" class="colorSelector"><div style="' . esc_attr( 'background-color:' . $typography_stored['color'] ) . '"></div></div>';
				$font_color .= '<input class="of-color of-typography of-typography-color" name="' . esc_attr( $this->option_name . '[' . $this->value['id'] . '][color]' ) . '" id="' . esc_attr( $this->value['id'] . '_color' ) . '" type="text" value="' . esc_attr( $typography_stored['color'] ) . '" />';
			}

			// Allow modification/injection of typography fields
			$typography_fields = compact( 'font_size', 'font_face', 'font_style', 'font_color' );
			$typography_fields = apply_filters( 'of_typography_fields', $typography_fields, $typography_stored, $this->option_name, $this->value );
			$this->output .= implode( '', $typography_fields );

			return $this->output;

		}/* html() */

	}/* class OF_Field_typography */


	/* ============================================================================================================ */

	/**
	 * tinymce editor
	 */

	class OF_Field_editor extends OF_Field
	{

		public function html()
		{

			$this->output .= '<div class="explain">' . wp_kses( $this->explain_value, $this->allowedtags) . '</div>'."\n";
			echo $this->output;
			$textarea_name = esc_attr( $this->option_name . '[' . $this->value['id'] . ']' );
			$default_editor_settings = array(
				'textarea_name' => $textarea_name,
				'media_buttons' => false,
				'tinymce' => array( 'plugins' => 'wordpress' )
			);
			$editor_settings = array();
			if ( isset( $this->value['settings'] ) ) {
				$editor_settings = $this->value['settings'];
			}
			$editor_settings = array_merge($editor_settings, $default_editor_settings);
			wp_editor( $this->val, $this->value['id'], $editor_settings );
			$this->output = '';

			return $this->output;

		}/* html() */

	}/* class OF_Field_editor */


	/* ============================================================================================================ */

?>
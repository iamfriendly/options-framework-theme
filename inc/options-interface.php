<?php

/**
 * Generates the tabs that are used in the options menu
 */

function optionsframework_tabs() {

	$optionsframework_settings = get_option('optionsframework');
	$options = optionsframework_options();
	$menu = '';

	foreach ($options as $value) {
		// Heading for Navigation
		if ($value['type'] == "heading") {
			$jquery_click_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['name']) );
			$jquery_click_hook = "of-option-" . $jquery_click_hook;
			$added_class = ( isset( $value['class'] ) ) ? $value['class'] : "";
			$menu .= '<a id="'.  esc_attr( $jquery_click_hook ) . '-tab" class="nav-tab ' . $added_class . '" title="' . esc_attr( $value['name'] ) . '" href="' . esc_attr( '#'.  $jquery_click_hook ) . '">' . esc_html( $value['name'] ) . '</a>';
		}
	}

	return $menu;
}

/**
 * Generates the options fields that are used in the form.
 */

function optionsframework_fields() {

	global $allowedtags;
	$optionsframework_settings = get_option('optionsframework');

	// Gets the unique option id
	if ( isset( $optionsframework_settings['id'] ) ) {
		$option_name = $optionsframework_settings['id'];
	}
	else {
		$option_name = 'optionsframework';
	};

	$settings = get_option($option_name);
	$options = optionsframework_options();

	$counter = 0;
	$menu = '';

	foreach ( $options as $value ) {

		$counter++;
		$val = '';
		$select_value = '';
		$checked = '';
		$output = '';

		// Wrap all options
		if ( ( $value['type'] != "heading" ) && ( $value['type'] != "info" ) ) {

			// Keep all ids lowercase with no spaces
			$value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']) );

			$id = 'section-' . $value['id'];

			$class = 'section ';
			if ( isset( $value['type'] ) ) {
				$class .= ' section-' . $value['type'];
			}
			if ( isset( $value['class'] ) ) {
				$class .= ' ' . $value['class'];
			}

			$group = ( isset( $value['group'] ) ) ? "data-group='" . $value['group'] . "'" : "";

			$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . '" ' . $group . '>'."\n";
			if ( isset( $value['name'] ) ) {
				$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
			}
			if ( $value['type'] != 'editor' ) {
				$output .= '<div class="option">' . "\n" . '<div class="controls">' . "\n";
			}
			else {
				$output .= '<div class="option">' . "\n" . '<div>' . "\n";
			}
		}

		// Set default value to $val
		if ( isset( $value['std'] ) ) {
			$val = $value['std'];
		}

		// If the option is already saved, ovveride $val
		if ( ( $value['type'] != 'heading' ) && ( $value['type'] != 'info') ) {
			if ( isset( $settings[($value['id'])]) ) {
				$val = $settings[($value['id'])];
				// Striping slashes of non-array options
				if ( !is_array($val) ) {
					$val = stripslashes( $val );
				}
			}
		}

		// If there is a description save it for labels
		$explain_value = '';
		if ( isset( $value['desc'] ) ) {
			$explain_value = $value['desc'];
		}

		$field_class = "OF_Field_" . $value['type'];
		$field = new $field_class( $counter, $value, $val, $select_value, $checked, $option_name, $menu, $output, $explain_value, $allowedtags );

		echo $field->output;

		//echo $output;
	}
	echo '</div>';
}
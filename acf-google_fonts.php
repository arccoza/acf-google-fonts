<?php

/*
Plugin Name: Advanced Custom Fields: Google Fonts
Plugin URI: PLUGIN_URL
Description: Google Fonts for ACF.
Version: 1.0.0
Author: Adrien de Pierres
Author URI: AUTHOR_URL
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/




// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-google_fonts', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 




// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_google_fonts( $version ) {
	
	include_once('acf-google_fonts-v5.php');
	
}

add_action('acf/include_field_types', 'include_field_types_google_fonts');	




// 3. Include field type for ACF4
function register_fields_google_fonts() {
	
	include_once('acf-google_fonts-v4.php');
	
}

add_action('acf/register_fields', 'register_fields_google_fonts');	


\add_action('wp_enqueue_scripts', function() {
	//wp_register_style( 'my-plugin', plugins_url( 'my-plugin/css/plugin.css' ) );
	//wp_enqueue_style( 'my-plugin' );

	$queue = json_decode(\get_transient('acf_google_fonts_queue') ?: '{}', true);

	foreach ($queue as $acf_post_id => $acf_fields) {
		$acf_post_id = str_replace('post_id:', '', $acf_post_id);
		
		foreach ($acf_fields as $acf_field_name => $v) {
			$acf_field = \get_field($acf_field_name, $acf_post_id);
			$font = str_replace(' ', '+', $acf_field['font']);
			$weight = $acf_field['weight'];
			$style = $acf_field['style'] == 'italic' ? 'italic' : '';
			$url = '//fonts.googleapis.com/css?family='.$font.':'.$weight.$style;

			\wp_register_style($acf_field_name, $url, null, null);
			\wp_enqueue_style($acf_field_name);
		}
	}
});


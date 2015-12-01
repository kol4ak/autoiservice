<?php 

if ( ! defined( 'ABSPATH' ) ) exit('You\'re not allowed to see this page'); // Exit if accessed directly

	//css
	add_action('yasr_add_front_script_css', 'yasr_pro_front_script_css' );

		function yasr_pro_front_script_css () {

			//if visitors stats are enabled
	        if (YASR_VISITORS_STATS === 'yes') {
	            wp_enqueue_style( 'jquery-ui','//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css', FALSE, NULL, 'all' );
	            wp_enqueue_style( 'dashicons' ); //dashicons
	        }

		}

	//js
	add_action('yasr_add_front_script_js', 'yasr_pro_front_script_js' );

		function yasr_pro_front_script_js () {

			//if visitors stats are enabled
	        if (YASR_VISITORS_STATS === 'yes') {
	            wp_enqueue_script( 'jquery-ui-progressbar' ); //script
	            wp_enqueue_script( 'jquery-ui-tooltip' ); //script
	        }

	    }


/****** Settings Pages ******/

	//add tab on settings page
	add_action( 'yasr_add_settings_tab', 'yasr_free_settings_tab');

		function yasr_free_settings_tab ($active_tab) {

			?>

			<a href="?page=yasr_settings_page&tab=go_pro" class="nav-tab <?php if ($active_tab == 'go_pro') echo 'nav-tab-active'; ?>" > <?php _e("Pro Features!", 'yet-another-stars-rating'); ?> </a>

			<?php
		}


	//content of the bottom in the settings tab
	add_action( 'yasr_settings_check_active_tab', 'yasr_free_check_active_tab' );

		function yasr_free_check_active_tab ($active_tab) {

			if ($active_tab == 'go_pro') {

	            yasr_go_pro(); 

	            yasr_fb_box ();

		        yasr_ask_rating ();

			}

		}


/****** End Settings Pages ******/

	//Always return False
	add_filter( 'yasr_filter_schema_microdata', 'yasr_filter_schema_microdata_callback');

		function yasr_filter_schema_microdata_callback () {

			return FALSE;

		}

	//Always return False
	add_filter( 'yasr_filter_schema_jsonld', 'yasr_filter_schema_jsonld_callback');

		function yasr_filter_schema_jsonld_callback () {

			return FALSE;

		}

?>
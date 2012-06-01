<?php

include_once( 'download-monitor-admin-settings.php' );
include_once( 'download-monitor-admin-cpt.php' );
include_once( 'download-monitor-admin-writepanels.php' );

/**
 * Queue admin CSS
 */
add_action( 'admin_enqueue_scripts', 'download_monitor_admin_css' ); 

function download_monitor_admin_css() {
	global $download_monitor;
	
	wp_enqueue_script( 'blockui', $download_monitor->plugin_url() . '/assets/js/blockui.min.js', '2.39', array( 'jquery' ) );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	wp_enqueue_style( 'dowload_monitor_admin_css', $download_monitor->plugin_url() . '/assets/css/admin.css' );	
}


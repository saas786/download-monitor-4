<?php
/**
 * Download Monitor Ajax Handlers
 * 
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		Mike Jolley
 * @category 	AJAX
 * @package 	Download Monitor
 */
 
/**
 * Delete file via ajax function
 */
add_action('wp_ajax_download_monitor_remove_file', 'download_monitor_remove_file');

function download_monitor_remove_file() {
	
	check_ajax_referer( 'remove-file', 'security' );
	$file_id = intval( $_POST['file_id'] );
	$file = get_post( $file_id );
	if ( $file && $file->post_type == "download_file" ) wp_delete_post( $file_id );
	die();
	
}

/**
 * Add file via ajax function
 */
add_action('wp_ajax_download_monitor_add_file', 'download_monitor_add_file');

function download_monitor_add_file() {
	
	check_ajax_referer( 'add-file', 'security' );
	
	$post_id = intval( $_POST['post_id'] );

	$file = array(
		'post_title' => 'Download #' . $post_id . ' File',
		'post_content' => '',
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
		'post_parent' => $post_id,
		'post_type' => 'download_file'
	);
	$file_id = wp_insert_post( $file );
	
	echo $file_id;
	
	die();
	
}

/**
 * Get folder contents when browsing for a file
 */
add_action('wp_ajax_download_monitor_list_files', 'download_monitor_ajax_list_files');

function download_monitor_ajax_list_files() {
	
	check_ajax_referer( 'list-files', 'security' );
	
	if ( ! current_user_can('manage_downloads') ) return false;
	
	$path = esc_attr( stripslashes( $_POST['path'] ) );

	if ( $path ) {
		$files = download_monitor_list_files( $path );
		
		foreach( $files as $found_file ) {
			
			$file = pathinfo( $found_file['path'] );
			
			if ( $found_file['type'] == 'folder' ) {
				
				echo '<li><a href="#" class="folder" data-path="' . trailingslashit( $file['dirname'] ) . $file['basename']  . '">' . $file['basename'] . '</a></li>';
				
			} else {
			
				$filename = $file['basename'];
				$extension = ( empty( $file['extension'] ) ) ? '' : $file['extension'];
				
				if ( substr( $filename, 0, 1 ) == '.' ) continue; // Ignore files starting with . like htaccess
				if ( in_array( $extension, array( '', 'php', 'html', 'htm', 'tmp' ) )  ) continue; // Ignored file types
				
				echo '<li><a href="#" class="file filetype-' . sanitize_title( $extension ) . '" data-path="' . trailingslashit( $file['dirname'] ) . $file['basename']  . '">' . $file['basename'] . '</a></li>';
			
			}
			
		}
	}
	
	die();
	
}

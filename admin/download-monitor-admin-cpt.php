<?php
/**
 * CPT Admin Functions
 *
 * @author 		Mike Jolley
 * @category 	Admin
 * @package 	Download Monitor
 */

/**
 * Title boxes
 */
add_filter('enter_title_here', 'download_monitor_enter_title_here', 1, 2);

function download_monitor_enter_title_here( $text, $post ) {
	if ($post->post_type=='download') return __('Download title', 'download_monitor');
	return $text;
}

/**
 * Columns for Downloads page
 **/
add_filter('manage_edit-download_columns', 'download_monitor_cpt_column_headers');
add_action('manage_download_posts_custom_column', 'download_monitor_cpt_columns', 2 );

function download_monitor_cpt_column_headers( $columns ) {
	global $download_monitor;
	
	$columns = array();
	
	$columns["cb"] 				= "<input type=\"checkbox\" />";
	$columns["thumb"] 			= __("Image", 'download_monitor');
	$columns["title"] 			= __("Title", 'download_monitor');
	$columns["id"] 				= __("ID", 'download_monitor');
	$columns["file"] 			= __("File", 'download_monitor');
	$columns["version"] 		= __("Version", 'download_monitor');
	$columns["download_cat"] 	= __("Categories", 'download_monitor');
	$columns["download_tag"] 	= __("Tags", 'download_monitor');

	$columns["hits"] = '<img src="' . $download_monitor->plugin_url() . '/assets/images/hits_head.png" alt="' . __("Hits", 'download_monitor') . '" />';
	
	$columns["members_only"] = '<img src="' . $download_monitor->plugin_url() . '/assets/images/member_head.png" alt="' . __("Members only", 'download_monitor') . '" />';
	
	
	$columns["date"] 			= __("Date posted", 'download_monitor');
	
	return $columns;
}

function download_monitor_cpt_columns( $column ) {
	global $post, $download_monitor;
	
	$download 	= new WP_Download_Monitor_File( $post->ID );
	$file 		= $download->get_file();

	switch ($column) {
		case "thumb" :
			$download->get_image();
		break;
		case "id" :
			echo $post->ID;
		break;
		case "download_cat" :
			if ( ! $terms = get_the_term_list( $post->ID, 'download_cat', '', ', ', '' ) ) echo '<span class="na">&ndash;</span>'; else echo $terms;
		break;
		case "download_tag" :
			if ( ! $terms = get_the_term_list( $post->ID, 'download_tag', '', ', ', '' ) ) echo '<span class="na">&ndash;</span>'; else echo $terms;
		break;
		case "members_only" :
		
			if ( $download->is_members_only() ) 
				echo '<img src="' . $download_monitor->plugin_url() . '/assets/images/on.png" alt="yes" />';
			else 
				echo '<span class="na">&ndash;</span>';
				
		break;
		case "file" :
			if ( $file )
				echo '<code>' . basename( current( (array) get_post_meta( $file->ID, 'url', true ) ) ) . '</code>';
			else
				echo '<span class="na">&ndash;</span>';
		break;
		case "version" :
			if ( $file )
				echo current( (array) get_post_meta( $file->ID, 'version', true ) );
			else
				echo '<span class="na">&ndash;</span>';
		break;
		case "hits" :
			echo $download->get_download_count();
		break;
		case "featured" :

			if ( $download->is_featured() ) 
				echo '<img src="' . $download_monitor->plugin_url() . '/assets/images/on.png" alt="yes" />';
			else 
				echo '<span class="na">&ndash;</span>';
				
		break;
	}
}


/**
 * Make download columns sortable
 * https://gist.github.com/906872
 *
add_filter("manage_edit-product_sortable_columns", 'download_monitor_custom_product_sort');

function download_monitor_custom_product_sort($columns) {
	$custom = array(
		'is_in_stock' 	=> 'inventory',
		'price'			=> 'price',
		'featured'		=> 'featured',
		'sku'			=> 'sku',
		'name'			=> 'title'
	);
	return wp_parse_args($custom, $columns);
}*/

/**
 * Post updated messages
 */
add_filter('post_updated_messages', 'download_monitor_cpt_updated_messages');

function download_monitor_cpt_updated_messages( $messages ) {
	global $post, $post_ID;
	
	$messages['download'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Download updated. <a href="%s">View Download</a>', 'download_monitor'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.', 'download_monitor'),
		3 => __('Custom field deleted.', 'download_monitor'),
		4 => __('Download updated.', 'download_monitor'),
		5 => isset($_GET['revision']) ? sprintf( __('Download restored to revision from %s', 'download_monitor'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Download published. <a href="%s">View Download</a>', 'download_monitor'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Download saved.', 'download_monitor'),
		8 => sprintf( __('Download submitted. <a target="_blank" href="%s">Preview Download</a>', 'download_monitor'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Download scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Download</a>', 'download_monitor'),
		  date_i18n( __( 'M j, Y @ G:i', 'download_monitor' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Download draft updated. <a target="_blank" href="%s">Preview Download</a>', 'download_monitor'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	
	return $messages;
}

<?php
/**
 * Writepanels
 *
 * @author 		Mike Jolley
 * @category 	Admin
 * @package 	Download Monitor
 */

/**
 * Init the meta boxes
 */
add_action( 'add_meta_boxes', 'download_monitor_meta_boxes' );

function download_monitor_meta_boxes() {
	global $post;
	
	add_meta_box( 'download-monitor-options', __('Download Options', 'download_monitor'), 'download_monitor_file_options', 'download', 'side', 'default' );
	add_meta_box( 'download-monitor-file', __('Downloadable Files', 'download_monitor'), 'download_monitor_files', 'download', 'normal', 'high' );
	
	// Excerpt
	if ( function_exists('wp_editor') ) {
		remove_meta_box( 'postexcerpt', 'download', 'normal' );
		add_meta_box( 'postexcerpt', __('Short Description', 'download_monitor'), 'download_monitor_short_description_meta_box', 'download', 'normal', 'high' );
	}

}

/**
 * Download Short Description
 * 
 * Replaces excerpt with a visual editor
 */
function download_monitor_short_description_meta_box( $post ) {
	
	$settings = array(
		'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
		'textarea_name'	=> 'excerpt',
		'quicktags' 	=> true,
		'tinymce' 		=> true,
		'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:200px; width:100%;}</style>'
		);
		
	wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', $settings );
}


function download_monitor_file_options() {
	global $post, $thepostid;
	
	$thepostid = $post->ID;

	echo '<div class="dlm_options_panel">';
	
	download_monitor_wp_checkbox( array( 'id' => '_featured', 'label' => __('Featured', 'download_monitor'), 'description' => __('Mark this download as featured.', 'download_monitor') ) );

	download_monitor_wp_checkbox( array( 'id' => '_do_not_force', 'label' => __('Do not force downloading', 'download_monitor'), 'description' => __('If enabled, downloading of this file will never be forced (useful for large files which can be unreliable when served by PHP).', 'download_monitor') ) );	
	
	download_monitor_wp_checkbox( array( 'id' => '_members_only', 'label' => __('Members only', 'download_monitor'), 'description' => __('Only logged in users will be able to access the file via a download link if this is enabled.', 'download_monitor') ) );
	
	echo '<div class="access_permissions"><h4>' . esc_attr__('Access Permissions', 'download_monitor') . '</h4><ul>';
	
	$user_count = count_users();
	
	$roles = (array) get_post_meta( $thepostid, '_member_roles', true );
	
	foreach ( get_editable_roles() as $role => $details ) {
		
		$count = isset( $user_count['avail_roles'][$role]  ) ? $user_count['avail_roles'][$role] : 0;
		
		$checked = checked( in_array( $role, $roles ), true, false );
		
		echo '<li><label class="selectit"><input value="' . $role . '" type="checkbox" name="_member_roles[]" ' . $checked . ' /> ' . $details['name'] . ' (' . sprintf( _n( '<strong>%s</strong> user',  '<strong>%s</strong> users', $count, 'download_monitor' ), $count ) . ')'. '</label></li>';

	}
	
	echo '</ul></div>';

	echo '</div>';
	
	?>
	<script type="text/javascript">
		jQuery(function() {
		
			jQuery('#_members_only').change(function(){
				
				if ( jQuery(this).is(':checked') ) {
					jQuery('.access_permissions').slideDown();
				} else {
					jQuery('.access_permissions').slideUp();
				}
				
			}).change();
		
		});
	</script>
	<?php
}

function download_monitor_files() {
	global $post, $download_monitor;
	
	wp_nonce_field( 'download_monitor_save_data', 'download_monitor_meta_nonce' );
	?>
	<div class="download_monitor_files dlm-metaboxes-wrapper">

		<p class="toolbar">
			<a href="#" class="button plus add_file"><?php _e('Add version', 'download_monitor'); ?></a>
			<a href="#" class="close_all"><?php _e('Close all', 'download_monitor'); ?></a><a href="#" class="expand_all"><?php _e('Expand all', 'download_monitor'); ?></a>
		</p>

		<div class="dlm-metaboxes downloadable_files">
		
			<?php
				$i = 0;
				$files = get_posts( 'post_parent=' . $post->ID . '&post_type=download_file&orderby=menu_order&order=ASC&post_status=any&numberposts=-1' );
				
				if ( $files ) foreach ( $files as $file ) {
					$hits 		= (int) get_post_meta( $file->ID, '_hits', true );
					$version 	= get_post_meta( $file->ID, '_version', true );
					$file_urls 	= (array) get_post_meta( $file->ID, '_files', true ); 
					
					if ( ! $version ) $version = '';
					?>
		    		<div class="dlm-metabox closed downloadable_file" data-file="<?php echo $file->ID; ?>">
						<h3>
							<button type="button" class="remove_file button"><?php _e('Remove', 'download_monitor'); ?></button>
							<div class="handlediv" title="<?php _e('Click to toggle', 'download_monitor'); ?>"></div>
							<strong>#<?php echo $file->ID; ?> &mdash; <?php echo sprintf( __('Version <span class="version">%s</span> (%s)', 'download_monitor'), ( $version ) ? $version : __('n/a', 'download_monitor'), date_i18n( get_option('date_format'), strtotime( $file->post_date ) ) ); ?> &mdash; <?php echo sprintf( _n('Downloaded %s time', 'Downloaded %s times', $hits, 'download_monitor'), $hits ); ?></strong>
							<input type="hidden" name="downloadable_file_id[<?php echo $i; ?>]" value="<?php echo $file->ID; ?>" />
							<input type="hidden" class="file_menu_order" name="downloadable_file_menu_order[<?php echo $i; ?>]" value="<?php echo $i; ?>" />
						</h3>
						<table cellpadding="0" cellspacing="0" class="dlm-metabox-content">
							<tbody>	
								<tr>
									<td width="1%">
										<label><?php _e('Version', 'download_monitor'); ?>:</label>
										<input type="text" class="short" name="downloadable_file_version[<?php echo $i; ?>]" placeholder="<?php _e('n/a', 'download_monitor'); ?>" value="<?php echo $version; ?>" />
									</td>
									<td rowspan="3">
										
										<label><?php _e('File URL(s)', 'download_monitor'); ?>:</label>
										<textarea name="downloadable_files[<?php echo $i; ?>]" cols="5" rows="5" placeholder="<?php _e('Enter one file path/URL per line - multiple files will be used as mirrors (chosen at random).', 'download_monitor'); ?>"><?php echo esc_textarea( implode( "\n", $file_urls ) ); ?></textarea>										
										<p>
											<a href="#" class="button upload_file"><?php _e('Upload file', 'download_monitor'); ?></a>
											<a href="#" class="button browse_for_file"><?php _e('Browse for file', 'download_monitor'); ?></a>
										</p>
											
									</td>
								</tr>
								<tr>
									<td>
										<label><?php _e('Download count', 'download_monitor'); ?>:</label>
										<input type="text" class="short" name="downloadable_file_hits[<?php echo $i; ?>]" placeholder="<?php echo $hits; ?>" />
									</td>
								</tr>
								<tr>
									<td>
										<label><?php _e('File Date', 'download_monitor'); ?>:</label>
										<input type="text" class="date-picker-field" name="downloadable_file_date[<?php echo $i; ?>]" maxlength="10" value="<?php echo date('Y-m-d', strtotime( $file->post_date ) ); ?>" /> @ <input type="text" class="hour" placeholder="<?php _e('h', 'download_monitor') ?>" name="downloadable_file_date_hour[<?php echo $i; ?>]" maxlength="2" size="2" value="<?php echo date('H', strtotime( $file->post_date ) ); ?>" />:<input type="text" class="minute" placeholder="<?php _e('m', 'download_monitor') ?>" name="downloadable_file_date_minute[<?php echo $i; ?>]" maxlength="2" size="2" value="<?php echo date('i', strtotime( $file->post_date ) ); ?>" />
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php
					$i++;
				}
			?>
		</div>
		
	</div>	
	<?php
	ob_start();
	?>
	jQuery(function(){
		
		// Expand all files
		jQuery('.expand_all').click(function(){
			jQuery(this).closest('.dlm-metaboxes-wrapper').find('.dlm-metabox table').show();
			return false;
		});
		
		// Close all files
		jQuery('.close_all').click(function(){
			jQuery(this).closest('.dlm-metaboxes-wrapper').find('.dlm-metabox table').hide();
			return false;
		});
		
		// Open/close
		jQuery('.dlm-metaboxes-wrapper').on('click', '.dlm-metabox h3', function(event){
			// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
			if ($(event.target).filter(':input, option').length) return;

			jQuery(this).next('.dlm-metabox-content').toggle();
		});
		
		// Closes all to begin
		jQuery('.dlm-metabox.closed').each(function(){
			jQuery(this).find('.dlm-metabox-content').hide();
		});
		
		// Date picker
		$( ".date-picker-field" ).datepicker({
			dateFormat: "yy-mm-dd",
			numberOfMonths: 1,
			showButtonPanel: true,
		});


		// Ordering
		$('.downloadable_files').sortable({
			items:'.downloadable_file',
			cursor:'move',
			axis:'y',
			handle: 'h3',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'dlm-metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css('background-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
				downloadable_file_row_indexes();
			}
		});
		
		function downloadable_file_row_indexes() {
			$('.downloadable_files .downloadable_file').each(function(index, el){ 
				$('.file_menu_order', el).val( parseInt( $(el).index('.downloadable_files .downloadable_file') ) ); 
			});
		};

		// Add a file
		jQuery('.download_monitor_files').on('click', 'a.add_file', function(){
		
			jQuery('.download_monitor_files').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $download_monitor->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
					
			var data = {
				action: 'download_monitor_add_file',
				post_id: <?php echo $post->ID; ?>,
				security: '<?php echo wp_create_nonce("add-file"); ?>'
			};

			jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
				
				var file_id = parseInt(response);
				
				var loop = jQuery('.downloadable_file').size();
				
				jQuery('.downloadable_files').prepend('<div class="dlm-metabox closed downloadable_file" data-file="' + file_id + '">\
						<h3>\
							<button type="button" class="remove_file button"><?php _e('Remove file', 'download_monitor'); ?></button>\
							<div class="handlediv" title="<?php _e('Click to toggle', 'download_monitor'); ?>"></div>\
							<strong>#' + file_id + ' &mdash; <?php echo sprintf( __('Version <span class="version">%s</span> (%s)', 'download_monitor'), __('n/a', 'download_monitor'), date_i18n( get_option('date_format'), current_time('timestamp') ) ); ?> &mdash; <?php _e('Downloaded 0 times', 'download_monitor'); ?></strong>\
							<input type="hidden" name="downloadable_file_id[' + loop + ']" value="' + file_id + '" />\
							<input type="hidden" class="file_menu_order" name="downloadable_file_menu_order[' + loop + ']" value="' + loop + '" />\
						</h3>\
						<table cellpadding="0" cellspacing="0" class="dlm-metabox-content">\
							<tbody>\
								<tr>\
									<td width="1%">\
										<label><?php _e('Version', 'download_monitor'); ?>:</label>\
										<input type="text" class="short" name="downloadable_file_version[' + loop + ']" />\
									</td>\
									<td rowspan="3">\
										<label><?php _e('File URL(s)', 'download_monitor'); ?>:</label>\
										<textarea name="downloadable_files[' + loop + ']" cols="5" rows="5" placeholder="<?php _e('Enter one file path/URL per line - multiple files will be used as mirrors (chosen at random).', 'download_monitor'); ?>"><?php echo esc_textarea( '' ); ?></textarea>\
										<p>\
											<a href="#" class="button upload_file"><?php _e('Upload file', 'download_monitor'); ?></a>\
											<a href="#" class="button browse_for_file"><?php _e('Browse for file', 'download_monitor'); ?></a>\
										</p>\
									</td>\
								</tr>\
								<tr>\
									<td>\
										<label><?php _e('Download count', 'download_monitor'); ?>:</label>\
										<input type="text" class="short" name="downloadable_file_hits[' + loop + ']" placeholder="0" />\
									</td>\
								</tr>\
								<tr>\
									<td>\
										<label><?php _e('File Date', 'download_monitor'); ?>:</label>\
										<input type="text" class="date-picker-field" name="downloadable_file_date[' + loop + ']" maxlength="10" value="<?php echo date('Y-m-d', current_time('timestamp') ); ?>" /> @ <input type="text" class="hour" placeholder="<?php _e('h', 'download_monitor') ?>" name="downloadable_file_date_hour[' + loop + ']" maxlength="2" size="2" value="<?php echo date('H', current_time('timestamp') ); ?>" />:<input type="text" class="minute" placeholder="<?php _e('m', 'download_monitor') ?>" name="downloadable_file_date_minute[' + loop + ']" maxlength="2" size="2" value="<?php echo date('i', current_time('timestamp') ); ?>" />\
									</td>\
								</tr>\
							</tbody>\
						</table>\
					</div>');
				
				downloadable_file_row_indexes();
				jQuery('.download_monitor_files').unblock();

			});

			return false;
		
		});
		
		// Remove a file
		jQuery('.download_monitor_files').on('click', 'button.remove_file', function(e){
			e.preventDefault();
			var answer = confirm('<?php _e('Are you sure you want to remove this file?', 'download_monitor'); ?>');
			if ( answer ) {
				
				var el = jQuery(this).closest('.downloadable_file');
				var file_id = el.attr('data-file');
				
				if ( file_id > 0 ) {
				
					jQuery(el).block({ message: null, overlayCSS: { background: '#fff url(<?php echo $download_monitor->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
					
					var data = {
						action: 		'download_monitor_remove_file',
						file_id: 		file_id,
						download_id: 	'<?php echo $post->ID; ?>',
						security: 		'<?php echo wp_create_nonce("remove-file"); ?>'
					};
	
					jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
						jQuery(el).fadeOut('300');
						jQuery(el).find('input').val('');
					});
					
				} else {
					jQuery(el).fadeOut('300');
					jQuery(el).find('input').val('');
				}
				
			}
			return false;
		});
		
		// Upload a file
		var downloadable_files_field;
		
		window.send_to_editor_default = window.send_to_editor;
		
		jQuery('.download_monitor_files').on('click', 'a.upload_file', function(e){
			
			downloadable_files_field = jQuery(this).closest('.downloadable_file').find('textarea[name^="downloadable_files"]');
		
			formfield = jQuery(downloadable_files_field).attr('name');
			
			window.send_to_editor = window.send_to_download_url;
			
			tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=downloadable_file&amp;from=wpdlm01&amp;TB_iframe=true');
			
			return false;	
		});

		window.send_to_download_url = function(html) {
			
			var file_url = jQuery(html).attr('href');
			
			if (file_url) {
				old = jQuery.trim( jQuery(downloadable_files_field).val() );
				if ( old ) old = old + "\n";
				jQuery(downloadable_files_field).val( old + file_url);
			}
			
			tb_remove();
			
			window.send_to_editor = window.send_to_editor_default;
			
		}
		
		// Browse for file
		jQuery('.download_monitor_files').on('click', 'a.browse_for_file', function(e){
			
			downloadable_files_field = jQuery(this).closest('.downloadable_file').find('textarea[name^="downloadable_files"]');
			
			window.send_to_editor = window.send_to_browse_file_url;
			
			tb_show('<?php esc_attr_e('Browse for a file', 'download_monitor'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=downloadable_file_browser&amp;from=wpdlm01&amp;TB_iframe=true');
			
			return false;	
		});
		
		window.send_to_browse_file_url = function(html) {
			
			if ( html ) {
				old = jQuery.trim( jQuery(downloadable_files_field).val() );
				if ( old ) old = old + "\n";
				jQuery(downloadable_files_field).val( old + html );
			}
			
			tb_remove();
			
			window.send_to_editor = window.send_to_editor_default;
			
		}
		
	});
	<?php
	$js_code = ob_get_clean();	
	$download_monitor->add_inline_js( $js_code );
}

/**
 * Directory for uploads
 */
add_filter('upload_dir', 'download_monitor_upload_dir');

function download_monitor_upload_dir( $pathdata ) {

	/*if (isset($_POST['type']) && $_POST['type'] == 'downloadable_product') :
		
		// Uploading a downloadable file
		$subdir = '/woocommerce_uploads'.$pathdata['subdir'];
	 	$pathdata['path'] = str_replace($pathdata['subdir'], $subdir, $pathdata['path']);
	 	$pathdata['url'] = str_replace($pathdata['subdir'], $subdir, $pathdata['url']);
		$pathdata['subdir'] = str_replace($pathdata['subdir'], $subdir, $pathdata['subdir']);
		return $pathdata;
		
	endif;*/
	
	return $pathdata;
}

/**
 * Media uploader
 */
add_action('media_upload_downloadable_file', 'download_monitor_media_upload');

function download_monitor_media_upload() {
	do_action('media_upload_file');
}

/**
 * Media browser
 */
add_action('media_upload_downloadable_file_browser', 'download_monitor_media_browser');

function download_monitor_media_browser() {
	global $download_monitor;
	
	$files = download_monitor_list_files( ABSPATH, 1 );
	
	echo '<!DOCTYPE html><html lang="en"><head><title>' . __('Browse for a file', 'download_monitor') . '</title>';
	
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head');
	
	echo '<meta charset="utf-8" /><link rel="stylesheet" type="text/css" href="' . $download_monitor->plugin_url() . '/assets/css/admin.css" /></head><body>';
	
	echo '<ul class="download_monitor_file_browser">';
	
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
	
	echo '</ul>';
	
	?>
	<script type="text/javascript">
		jQuery(function() {
			jQuery('.download_monitor_file_browser').on('click', 'a', function(){
				
				var $link = jQuery(this);
				var $parent = $link.closest('li');
				
				if ( $link.is('.file') ) {
				
					var win = window.dialogArguments || opener || parent || top;
					
					win.send_to_editor( $link.attr('data-path') );
				
				} else if ( $link.is('.folder_open') ) {
					
					$parent.find('ul').remove();
					$link.removeClass('folder_open');
					
				} else {
				
					$link.after('<ul class="load_tree loading"></ul>');
					
					var data = {
						action: 	'download_monitor_list_files',
						path: 		jQuery(this).attr('data-path'),
						security: 	'<?php echo wp_create_nonce("list-files"); ?>'
					};
		
					jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
						
						$link.addClass('folder_open');
						
						if ( response ) {
							$parent.find('.load_tree').html( response );
						} else {
							$parent.find('.load_tree').html( '<li class="nofiles"><?php _e('No files found', 'download_monitor'); ?></li>' );
						}
						$parent.find('.load_tree').removeClass('load_tree loading');
					
					});
					
				}
				
				return false;
			});
		});
	</script>
	<?php
	
	echo '</body></html>';
	

}

/**
 * Change label for insert buttons
 */
add_filter( 'gettext', 'download_monitor_change_insert_into_post', null, 2 );

function download_monitor_change_insert_into_post( $translation, $original ) {
    if( ! isset( $_REQUEST['from'] ) ) return $translation;
	
	$original = strtolower(trim($original));
	
    if( $_REQUEST['from'] == 'wpdlm01' && ($original == 'insert into post' || $original == 'use this image') ) return __('Add this file', 'download_monitor' );

    return $translation;
}

/**
 * Save meta boxes
 */
add_action( 'save_post', 'download_monitor_save_post', 1, 2 );

function download_monitor_save_post( $post_id, $post ) {
	if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
	if ( is_int( wp_is_post_revision( $post ) ) ) return;
	if ( is_int( wp_is_post_autosave( $post ) ) ) return;
	if ( empty($_POST['download_monitor_meta_nonce']) || ! wp_verify_nonce( $_POST['download_monitor_meta_nonce'], 'download_monitor_save_data' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( $post->post_type != 'download' ) return;
		
	do_action( 'download_monitor_process_download_meta_boxes', $post_id, $post );
}

/**
 * Download Data Save
 * 
 * Function for processing and storing all download data.
 */
add_action('download_monitor_process_download_meta_boxes', 'download_monitor_process_download_meta_boxes', 1, 2 );

function download_monitor_process_download_meta_boxes( $post_id, $post ) {
	global $wpdb, $download_monitor;
	
	// Default meta
	add_post_meta( $post_id, '_hits', 0 ); // Stores total hits
	
	// Update options
	$_featured = ( isset( $_POST['_featured'] ) ) ? 'yes' : 'no';
	$_members_only = ( isset( $_POST['_members_only'] ) ) ? 'yes' : 'no';
	$_do_not_force = ( isset( $_POST['_do_not_force'] ) ) ? 'yes' : 'no';
	
	update_post_meta( $post_id, '_featured', $_featured );
	update_post_meta( $post_id, '_members_only', $_members_only );
	update_post_meta( $post_id, '_do_not_force', $_do_not_force );
	
	// Process permissions
	if ( $_members_only == 'yes' ) {
		$roles = array_filter( array_map( 'sanitize_title', array_map( 'trim', (array) $_POST['_member_roles'] ) ) );
		update_post_meta( $post_id, '_member_roles', $roles );
	} else {
		delete_post_meta( $post_id, '_member_roles' );
	}

	// Process files
	if ( isset( $_POST['downloadable_file_id'] ) ) {
		
		$downloadable_file_id 			= $_POST['downloadable_file_id'];
		$downloadable_file_menu_order	= $_POST['downloadable_file_menu_order'];
		$downloadable_file_version		= $_POST['downloadable_file_version'];
		$downloadable_files				= $_POST['downloadable_files'];
		$downloadable_file_date			= $_POST['downloadable_file_date'];
		$downloadable_file_date_hour	= $_POST['downloadable_file_date_hour'];
		$downloadable_file_date_minute	= $_POST['downloadable_file_date_minute'];
		$downloadable_file_hits			= $_POST['downloadable_file_hits'];
		
		for ( $i = 0; $i < sizeof( $downloadable_file_id ); $i ++ ) {
			
			$file_id 			= (int) $downloadable_file_id[$i];
			$file_menu_order 	= (int) $downloadable_file_menu_order[$i];
			$file_version 		= $downloadable_file_version[$i];
			$files 				= array_filter( array_map( 'trim', explode( "\n", $downloadable_files[$i] ) ) );
			$file_date 			= $downloadable_file_date[$i];
			$file_date_hour 	= (int) $downloadable_file_date_hour[$i];
			$file_date_minute 	= (int) $downloadable_file_date_minute[$i];
			$file_hits			= $downloadable_file_hits[$i];
			
			if ( ! $file_id ) continue;
			
			// Generate a useful post title
			$file_post_title = 'Download #' . $post_id . ' File';
			
			// Generate date
			if ( empty( $file_date ) ) {
				$date = current_time('timestamp');
			} else {
				$date = strtotime( $file_date . ' ' . $file_date_hour . ':' . $file_date_minute . ':00' );
			}

			// Update
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish', 'post_title' => $file_post_title, 'menu_order' => $file_menu_order, 'post_date' => date( 'Y-m-d H:i:s', $date ) ), array( 'ID' => $file_id ) );
			
			// Update post meta
			update_post_meta( $file_id, '_version', esc_html( $file_version ) );
			if ( $file_hits !== '' ) update_post_meta( $file_id, '_hits', $file_hits );
			update_post_meta( $file_id, '_files', $files );
		 	
		}
		 
	}
}


/**
 * Writepanel form elements
 */
function download_monitor_wp_text_input( $field ) {
	global $thepostid, $post, $download_monitor;
	
	if (!$thepostid) $thepostid = $post->ID;
	if (!isset($field['placeholder'])) $field['placeholder'] = '';
	if (!isset($field['class'])) $field['class'] = 'short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($thepostid, $field['id'], true);
	
	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><input type="text" class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.esc_attr( $field['value'] ).'" placeholder="'.$field['placeholder'].'" /> ';
	
	if ( isset( $field['description'] ) && $field['description'] ) {
		
		if ( isset( $field['desc_tip'] ) ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . $download_monitor->plugin_url() . '/assets/images/help.png" />';
		} else {
			echo '<span class="description">' . $field['description'] . '</span>';
		}

	}		
	echo '</p>';
}

function download_monitor_wp_hidden_input( $field ) {
	global $thepostid, $post;
	if (!$thepostid) $thepostid = $post->ID;
	if (!isset($field['value'])) $field['value'] = get_post_meta($thepostid, $field['id'], true);
	echo '<input type="hidden" class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.esc_attr( $field['value'] ).'" /> ';
}

function download_monitor_wp_textarea_input( $field ) {
	global $thepostid, $post, $download_monitor;
	
	if (!$thepostid) $thepostid = $post->ID;
	if (!isset($field['placeholder'])) $field['placeholder'] = '';
	if (!isset($field['class'])) $field['class'] = 'short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($thepostid, $field['id'], true);
	
	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><textarea class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.$field['placeholder'].'" rows="2" cols="20">'.esc_textarea( $field['value'] ).'</textarea> ';
	
	if ( isset( $field['description'] ) && $field['description'] ) {
		
		if ( isset( $field['desc_tip'] ) ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . $download_monitor->plugin_url() . '/assets/images/help.png" />';
		} else {
			echo '<span class="description">' . $field['description'] . '</span>';
		}

	}		
	echo '</p>';
}

function download_monitor_wp_checkbox( $field ) {
	global $thepostid, $post;
	
	if (!$thepostid) $thepostid = $post->ID;
	if (!isset($field['class'])) $field['class'] = 'checkbox';
	if (!isset($field['wrapper_class'])) $field['wrapper_class'] = '';
	if (!isset($field['value'])) $field['value'] = get_post_meta($thepostid, $field['id'], true);
	
	echo '<p class="form-field form-field-checkbox '.$field['id'].'_field '.$field['wrapper_class'].'"><input type="checkbox" class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" ';
	
	checked($field['value'], 'yes');
	
	echo ' /> <label for="'.$field['id'].'">'.$field['label'].'</label>';
	
	if (isset($field['description']) && $field['description']) echo '<span class="description">' .$field['description'] . '</span>';
		
	echo '</p>';
}

function download_monitor_wp_select( $field ) {
	global $thepostid, $post, $download_monitor;
	
	if (!$thepostid) $thepostid = $post->ID;
	if (!isset($field['class'])) $field['class'] = 'select short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($thepostid, $field['id'], true);
	
	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><select id="'.$field['id'].'" name="'.$field['id'].'" class="'.$field['class'].'">';
	
	foreach ($field['options'] as $key => $value) :
		
		echo '<option value="'.$key.'" ';
		selected($field['value'], $key);
		echo '>'.$value.'</option>';
		
	endforeach;
	
	echo '</select> ';
	
	if ( isset( $field['description'] ) && $field['description'] ) {
		
		if ( isset( $field['desc_tip'] ) ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . $download_monitor->plugin_url() . '/assets/images/help.png" />';
		} else {
			echo '<span class="description">' . $field['description'] . '</span>';
		}

	}
		
	echo '</p>';
}

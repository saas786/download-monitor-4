<?php
include_once( 'download-monitor-admin-import-directory.php' );

add_action( 'admin_init', 'download_monitor_settings_init' );
add_action( 'admin_menu', 'download_monitor_settings_menu', 12 );

/**
 * Define Options
 */
global $download_monitor_settings;

$download_monitor_settings = (
	array( 
		array(
			'',  
			'',
			array(
				array(
					'name' 		=> 'download_monitor_browse_for_files_path', 
					'std' 		=> '', 
					'placeholder'	=> ABSPATH,
					'label' 	=> __('Browse for files path', 'download_monitor'),  
					'desc'		=> __('This path will be browsable when adding downloads.', 'download_monitor')
				),
				array(
					'name' 		=> 'downoad_monitor_404_redirect', 
					'std' 		=> '', 
					'placeholder'	=> __('No redirect', 'download_monitor'), 
					'label' 	=> __('404 Redirect', 'download_monitor'),  
					'desc'		=> __('When a download does not exist. Use <code>{referrer}</code> for the referring url.', 'download_monitor')
				),
				array(
					'name' 		=> 'downoad_monitor_access_denied_redirect', 
					'std' 		=> '',
					'placeholder'	=> __('No redirect', 'download_monitor'), 
					'label' 	=> __('Access-denied redirect', 'download_monitor'),  
					'desc'		=> __('When the user cannot access a download. Use <code>{referrer}</code> for the referring url.', 'download_monitor')
				),
				array(
					'name' 		=> 'download_monitor_enable_logging', 
					'std' 		=> '1', 
					'label' 	=> __('Enable logging', 'download_monitor'),  
					'desc'		=> __('Log download attempts.', 'download_monitor'),
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'download_monitor_log_timeout', 
					'std' 		=> '', 
					'label' 	=> __('Log Timeout', 'download_monitor'),  
					'placeholder'	=> __('Log all downloads', 'download_monitor'), 
					'desc'		=> __('Optionally set a timeout in minutes - this can prevent downloads by the same person being logged multiple times.', 'download_monitor')
				),
				array(
					'name' 			=> 'download_monitor_ip_blacklist', 
					'std' 			=> '192.168.0.*', 
					'label' 		=> __('Blacklist IPs', 'download_monitor'),  
					'desc'			=> __('List IP Addresses to blacklist, 1 per line. Use <code>*</code> for a wildcard.', 'download_monitor'),
					'placeholder' 	=> '',
					'type' 			=> 'textarea'
				),
				array(
					'name' 		=> 'download_monitor_user_agent_blacklist', 
					'std' 		=> 'Googlebot', 
					'label' 	=> __('Blacklist user agents', 'download_monitor'),  
					'desc'		=> __('List browser user agents to blacklist, 1 per line.', 'download_monitor'),
					'placeholder' => '',
					'type' 			=> 'textarea'
				),
			)
		),
	)
);
	
/**
 * Init plugin options to white list our options
 */
function download_monitor_settings_init() {

	global $download_monitor_settings;

	foreach ( $download_monitor_settings as $section ) {
		foreach ( $section[2] as $option ) {
			if ( isset( $option['std'] ) ) add_option( $option['name'], $option['std'] );
			register_setting( 'download_monitor', $option['name'] );
		}
	}

	
}

/**
 * Load up the menu page
 */
function download_monitor_settings_menu() {
	add_submenu_page( 'edit.php?post_type=download', __('Tools &amp; settings', 'download_monitor'), __('Tools &amp; settings', 'download_monitor'), 'manage_options', 'download-monitor-settings', 'download_monitor_settings');
}

/**
 * Create the options page
 */
function download_monitor_settings() {

	$tab = ( isset( $_REQUEST['tab'] ) ) ? esc_attr( $_REQUEST['tab'] ) : 'settings';

	?>
	<div class="wrap">
		<?php screen_icon(); ?>
 
	    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
	    	<a href="<?php echo admin_url('edit.php?post_type=download&page=download-monitor-settings'); ?>" class="nav-tab <?php echo ($tab == 'settings') ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'download_monitor'); ?></a><a href="<?php echo admin_url('edit.php?post_type=download&page=download-monitor-settings&tab=output_formats'); ?>" class="nav-tab <?php echo ($tab == 'output_formats') ? 'nav-tab-active' : ''; ?>"><?php _e('Output Formats', 'download_monitor'); ?></a><a href="<?php echo admin_url('edit.php?post_type=download&page=download-monitor-settings&tab=import_directory'); ?>" class="nav-tab <?php echo ($tab == 'import_directory') ? 'nav-tab-active' : ''; ?>"><?php _e('Import Directory', 'download_monitor'); ?></a><a href="<?php echo admin_url('edit.php?post_type=download&page=download-monitor-settings&tab=import_directory'); ?>" class="nav-tab <?php echo ($tab == 'import_directory') ? 'nav-tab-active' : ''; ?>"><?php _e('Logs', 'download_monitor'); ?></a>
	    </h2>
		
		<?php 
			switch ( $tab ) {
				case "import_directory" :
					download_monitor_import_directory();
					break;
				default :
					download_monitor_settings_form();
			}
		?>

	</div>
	<?php
}

/**
 * Output the settings form
 */
function download_monitor_settings_form() {
	global $download_monitor_settings;
	
	?>
	<form method="post" action="options.php">
	
		<?php settings_fields( 'download_monitor' ); ?>
	
		<?php
		foreach ( $download_monitor_settings as $section ) {
		
			if ( $section[0] ) echo '<h3 class="title">' . $section[0] . '</h3>';
			
			if ( $section[1] ) echo '<p>' . $section[1] . '</p>';
			
			echo '<table class="form-table">';
			
			foreach ( $section[2] as $option ) {
			
				$placeholder = ( ! empty( $option['placeholder'] ) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
				
				echo '<tr valign="top"><th scope="row">' . $option['label'] . '</th><td>';
				
				if ( ! isset( $option['type'] ) ) $option['type'] = '';
				
				switch ( $option['type'] ) {
					
					case "checkbox" :
					
						$value = get_option($option['name']);
						
						?><label><input id="<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> /> <?php echo $option['desc']; ?></label><?php
					
					break;
					case "textarea" :
						
						$value = get_option($option['name']);
						
						?><textarea id="<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo $option['name']; ?>" <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php
					
						if ( $option['desc'] ) echo ' <span class="description">' . $option['desc'] . '</span>';

					break;
					default :
						
						$value = get_option($option['name']);
						
						?><input id="<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo $placeholder; ?> /><?php
						
						if ( $option['desc'] ) echo ' <span class="description">' . $option['desc'] . '</span>';
					
					break;
					
				}
								
				echo '</td></tr>';
			}
			
			echo '</table>';
			
		}
		?>
	
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'download_monitor'); ?>" />
		</p>
	</form>
	<?php
}
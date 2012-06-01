<?php

################################################################################
// let_to_num used for file sizes
################################################################################

function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}


/**
 * Returns a listing of all files in the specified folder and all subdirectories up to 100 levels deep.
 * The depth of the recursiveness can be controlled by the $levels param.
 *
 * @see list_files
 */
function download_monitor_list_files( $folder = '' ) {
	if ( empty($folder) )
		return false;

	$files = array();
	if ( $dir = @opendir( $folder ) ) {
		while (($file = readdir( $dir ) ) !== false ) {
			if ( in_array($file, array('.', '..') ) )
				continue;
			if ( is_dir( $folder . '/' . $file ) ) {
				
				$files[] = array(
					'type' 	=> 'folder',
					'path'	=> $folder . '/' . $file
				);
				
			} else {
			
				$files[] = array(
					'type' 	=> 'file',
					'path'	=> $folder . '/' . $file
				);
				
			}
		}
	}
	@closedir( $dir );
	return $files;
}
<?php
/**
 * Plugin Name: Download Monitor
 * Plugin URI: http://wordpress.org/extend/plugins/download-monitor/
 * Description: A full solution for managing downloadable files, monitoring downloads and outputting download links and file information on your WordPress powered site.
 * Version: 4.0
 * Author: Mike Jolley
 * Author URI: http://mikejolley.com
 * Requires at least: 3.3
 * Tested up to: 3.3
 *
 * Text Domain: download_monitor
 * Domain Path: /languages/
 *
 * Copyright 2012 Mike Jolley
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_Download_Monitor' ) ) {

/**
 * WP_Download_Monitor class.
 *
 * Main Class which inits the CPT and plugin
 */
class WP_Download_Monitor {

	/** Version ***************************************************************/
	
	var $version = '4.0';
	
	/** URLS ******************************************************************/
	
	var $plugin_url;
	var $plugin_path;
	var $template_url;
	
	/** Inline JavaScript *****************************************************/

	private $_inline_js = '';

	/**
	 * Download Monitor Constructor
	 *
	 * Gets things started
	 */
	function __construct() {

		// Include required files
		$this->includes();
		
		// Installation
		//if ( is_admin() && !defined('DOING_AJAX') ) $this->install();

		// Actions
		add_action( 'init', array( &$this, 'init' ), 0 );
		add_action( 'init', array( &$this, 'include_template_functions' ), 25 );
		add_action( 'after_setup_theme', array( &$this, 'compatibility' ) );
		
		// Loaded action
		do_action( 'download_monitor_loaded' );
	}

	/**
	 * Include required core files
	 **/
	function includes() {
	
		if ( is_admin() ) 
			$this->admin_includes();
			
		if ( defined('DOING_AJAX') ) 
			$this->ajax_includes();
			
		if ( ! is_admin() || defined('DOING_AJAX') ) 
			$this->frontend_includes();
		
		include_once( 'classes/class-wp-download-monitor-file.php' );	// Downloadable file class
		include_once( 'download-monitor-core-functions.php' );			// Contains core functions for the front/back end
		include_once( 'widgets/widget-init.php' );						// Widget classes
	}
	
	/**
	 * Include required admin files
	 **/
	function admin_includes() {
		include_once( 'admin/download-monitor-admin-init.php' );	// Admin section
	}
	
	/**
	 * Include required ajax files
	 **/
	function ajax_includes() {
		include_once( 'download-monitor-ajax.php' );				// Ajax functions for admin and the front-end
	}
	
	/**
	 * Include required frontend files
	 **/
	function frontend_includes() {
		include_once( 'download-monitor-hooks.php' );				// Template hooks used on the front-end
		include_once( 'download-monitor-functions.php' );			// Contains functions for various front-end events
		include_once( 'shortcodes/shortcodes-init.php' );			// Init the shortcodes
	}
	
	/**
	 * Function used to Init Template Functions - This makes them pluggable by plugins and themes
	 **/
	function include_template_functions() {
		include( 'download-monitor-template.php' );
	}
	
	/**
	 * Install upon activation
	 **/
	function install() {
		register_activation_hook( __FILE__, 'activate_download_monitor' );
		register_activation_hook( __FILE__, 'flush_rewrite_rules' );
		if ( get_option('download_monitor_db_version') != $this->version ) 
			add_action( 'init', 'install_download_monitor', 1 );
	}
	
	/**
	 * Init when WordPress Initialises
	 **/
	function init() {
		// Set up localisation
		$this->load_plugin_textdomain();

		// Variables
		$this->template_url			= apply_filters( 'download_monitor_template_url', 'download_monitor/' );

		// Classes/actions loaded for the frontend and for ajax requests
		if ( ! is_admin() || defined('DOING_AJAX') ) {
			add_filter( 'template_include', array(&$this, 'template_loader') );
			add_action( 'wp_enqueue_scripts', array(&$this, 'frontend_scripts') );
			add_action( 'wp_footer', array(&$this, 'output_inline_js'), 25 );
		}

		// Actions
		add_action( 'the_post', array( &$this, 'setup_download_data' ) );
		add_action( 'admin_footer', array( &$this, 'output_inline_js' ), 25 );

		// Register globals for WC environment
		$this->register_globals();

		// Init user roles
		$this->init_user_roles();
		
		// Init taxonomies
		$this->init_taxonomy();
		
		// Init Images sizes
		$this->init_image_sizes();
		
		// Init styles
		if ( ! is_admin() ) $this->init_styles();
		
		// Init action
		do_action( 'download_monitor_init' );
	}
	
	/**
	 * Localisation
	 **/
	function load_plugin_textdomain() {
		load_textdomain( 'download_monitor', WP_LANG_DIR . '/download-monitor/download_monitor-'.get_locale().'.mo' );
		load_plugin_textdomain( 'download_monitor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	
	/**
	 * template_loader
	 * 
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. dlm looks for theme 
	 * overides in /theme/download_monitor/ by default
	 */
	function template_loader( $template ) {
		
		$find 	= array();
		$file 	= '';
		
		if ( is_single() && get_post_type() == 'download' ) {
			
			$file 	= 'single-download.php';
			$find[] = $file;
			$find[] = $this->template_url . $file;

		} elseif ( is_tax('download_cat') || is_tax('download_tag') ) {
			
			$term = get_queried_object();
			
			$file 		= 'taxonomy-' . $term->taxonomy . '.php';
			$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= $this->template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= $file;
			$find[] 	= $this->template_url . $file;
						
		} elseif ( is_post_type_archive('download') ) {
			
			$file 	= 'archive-download.php';
			$find[] = $file;
			$find[] = $this->template_url . $file;
			
		}
		
		if ( $file ) {
			$template = locate_template( $find );
			if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
		}
		
		return $template;
	}

	/**
	 * Register environment globals
	 **/
	function register_globals() {
		$GLOBALS['download'] = null;
	}
	
	/**
	 * When the_post is called, get product data too
	 **/
	function setup_download_data( $post ) {
		if ( is_int( $post ) ) $post = get_post( $post );
		if ( $post->post_type !== 'download' ) return;
		unset( $GLOBALS['download'] );
		$GLOBALS['download'] = new WP_Download_Monitor_File( $post->ID );
		return $GLOBALS['download'];
	}
	
	/**
	 * Add Compatibility for various bits
	 **/
	function compatibility() {
	
		// Post thumbnail support
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
			remove_post_type_support( 'post', 'thumbnail' );
			remove_post_type_support( 'page', 'thumbnail' );
		} else {
			add_post_type_support( 'download', 'thumbnail' );
		}

	}
	
	/**
	 * Init user roles
	 **/
	function init_user_roles() {
		global $wp_roles;
	
		if ( class_exists('WP_Roles') ) if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();	
		
		if ( is_object($wp_roles) ) {
			$wp_roles->add_cap( 'administrator', 'manage_downloads' );
			$wp_roles->add_cap( 'administrator', 'view_download_logs' );
		}
	}

	/**
	 * Init taxonomies
	 **/
	function init_taxonomy() {
		
		if ( post_type_exists('download') ) return;
		
		/**
		 * Slugs
		 **/
		$download_permalink = apply_filters( 'download_monitor_cpt_permalink', 'downloads' );
		$download_cat_permalink = apply_filters( 'download_monitor_tax_product_cat_permalink', 'download-category' );
		$download_tag_permalink = apply_filters( 'download_monitor_tax_product_tag_permalink', 'download-tag' );
		
		/**
		 * Taxonomies
		 **/
		register_taxonomy( 'download_cat',
	        array('download'),
	        array(
	            'hierarchical' 			=> true,
	            'update_count_callback' => '_update_post_term_count',
	            'label' 				=> __( 'Categories', 'download_monitor'),
	            'labels' => array(
	                    'name' 				=> __( 'Categories', 'download_monitor'),
	                    'singular_name' 	=> __( 'Download Category', 'download_monitor'),
	                    'search_items' 		=> __( 'Search Download Categories', 'download_monitor'),
	                    'all_items' 		=> __( 'All Download Categories', 'download_monitor'),
	                    'parent_item' 		=> __( 'Parent Download Category', 'download_monitor'),
	                    'parent_item_colon' => __( 'Parent Download Category:', 'download_monitor'),
	                    'edit_item' 		=> __( 'Edit Download Category', 'download_monitor'),
	                    'update_item' 		=> __( 'Update Download Category', 'download_monitor'),
	                    'add_new_item' 		=> __( 'Add New Download Category', 'download_monitor'),
	                    'new_item_name' 	=> __( 'New Download Category Name', 'download_monitor')
	            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> 'manage_downloads',
	            	'edit_terms' 		=> 'manage_downloads',
	            	'delete_terms' 		=> 'manage_downloads',
	            	'assign_terms' 		=> 'manage_downloads',
	            ),
	            'rewrite' 				=> array( 'slug' => $download_cat_permalink, 'with_front' => false, 'hierarchical' => true ),
	        )
	    );
	    
		register_taxonomy( 'download_tag',
	        array('download'),
	        array(
	            'hierarchical' 			=> false,
	            'label' 				=> __( 'Tags', 'download_monitor'),
	            'labels' => array(
	                    'name' 				=> __( 'Tags', 'download_monitor'),
	                    'singular_name' 	=> __( 'Download Tag', 'download_monitor'),
	                    'search_items' 		=> __( 'Search Download Tags', 'download_monitor'),
	                    'all_items' 		=> __( 'All Download Tags', 'download_monitor'),
	                    'parent_item' 		=> __( 'Parent Download Tag', 'download_monitor'),
	                    'parent_item_colon' => __( 'Parent Download Tag:', 'download_monitor'),
	                    'edit_item' 		=> __( 'Edit Download Tag', 'download_monitor'),
	                    'update_item' 		=> __( 'Update Download Tag', 'download_monitor'),
	                    'add_new_item' 		=> __( 'Add New Download Tag', 'download_monitor'),
	                    'new_item_name' 	=> __( 'New Download Tag Name', 'download_monitor')
	            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> 'manage_downloads',
	            	'edit_terms' 		=> 'manage_downloads',
	            	'delete_terms' 		=> 'manage_downloads',
	            	'assign_terms' 		=> 'manage_downloads',
	            ),
	            'rewrite' 				=> array( 'slug' => $download_tag_permalink, 'with_front' => false, 'hierarchical' => true ),
	        )
	    );

	    /**
		 * Post Types
		 **/
		register_post_type( "download",
			array(
				'labels' => array(
						'name' 					=> __( 'Downloads', 'download_monitor' ),
						'singular_name' 		=> __( 'Download', 'download_monitor' ),
						'add_new' 				=> __( 'Add Download', 'download_monitor' ),
						'add_new_item' 			=> __( 'Add New Download', 'download_monitor' ),
						'edit' 					=> __( 'Edit', 'download_monitor' ),
						'edit_item' 			=> __( 'Edit Download', 'download_monitor' ),
						'new_item' 				=> __( 'New Download', 'download_monitor' ),
						'view' 					=> __( 'View Download', 'download_monitor' ),
						'view_item' 			=> __( 'View Download', 'download_monitor' ),
						'search_items' 			=> __( 'Search Downloads', 'download_monitor' ),
						'not_found' 			=> __( 'No Downloads found', 'download_monitor' ),
						'not_found_in_trash' 	=> __( 'No Downloads found in trash', 'download_monitor' ),
						'parent' 				=> __( 'Parent Download', 'download_monitor' )
					),
				'description' => __( 'This is where you can create and manage downloads for your site.', 'download_monitor' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'capability_type' 		=> 'post',
				'capabilities' => array(
					'publish_posts' 		=> 'manage_downloads',
					'edit_posts' 			=> 'manage_downloads',
					'edit_others_posts' 	=> 'manage_downloads',
					'delete_posts' 			=> 'manage_downloads',
					'delete_others_posts'	=> 'manage_downloads',
					'read_private_posts'	=> 'manage_downloads',
					'edit_post' 			=> 'manage_downloads',
					'delete_post' 			=> 'manage_downloads',
					'read_post' 			=> 'manage_downloads'
				),
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> false,
				'hierarchical' 			=> false,
				'rewrite' 				=> array( 'slug' => $download_permalink, 'with_front' => false ),
				'query_var' 			=> true,			
				'supports' 				=> apply_filters( 'download_monitor_cpt_supports', array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields' ) ),
				'has_archive' 			=> $download_permalink,
				'show_in_nav_menus' 	=> false
			)
		);

		register_post_type( "download_file",
			array(
				'labels' => array(
						'name' 					=> __( 'Files', 'download_monitor' ),
						'singular_name' 		=> __( 'File', 'download_monitor' ),
						'add_new' 				=> __( 'Add File', 'download_monitor' ),
						'add_new_item' 			=> __( 'Add New File', 'download_monitor' ),
						'edit' 					=> __( 'Edit', 'download_monitor' ),
						'edit_item' 			=> __( 'Edit File', 'download_monitor' ),
						'new_item' 				=> __( 'New File', 'download_monitor' ),
						'view' 					=> __( 'View File', 'download_monitor' ),
						'view_item' 			=> __( 'View File', 'download_monitor' ),
						'search_items' 			=> __( 'Search Files', 'download_monitor' ),
						'not_found' 			=> __( 'No Files found', 'download_monitor' ),
						'not_found_in_trash' 	=> __( 'No Files found in trash', 'download_monitor' ),
						'parent' 				=> __( 'Parent Download', 'download_monitor' )
					),
				'public' 				=> true,
				'show_ui' 				=> false,
				'capability_type' 		=> 'post',
				'capabilities' => array(
					'publish_posts' 		=> 'manage_downloads',
					'edit_posts' 			=> 'manage_downloads',
					'edit_others_posts' 	=> 'manage_downloads',
					'delete_posts' 			=> 'manage_downloads',
					'delete_others_posts'	=> 'manage_downloads',
					'read_private_posts'	=> 'manage_downloads',
					'edit_post' 			=> 'manage_downloads',
					'delete_post' 			=> 'manage_downloads',
					'read_post' 			=> 'manage_downloads'
				),
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> true,
				'hierarchical' 			=> false,
				'rewrite' 				=> false,
				'query_var'				=> true,			
				'supports' 				=> array( 'title', 'editor', 'custom-fields', 'page-attributes', 'thumbnail' ),
				'show_in_nav_menus' 	=> false
			)
		);

	}
	
	/**
	 * Init images
	 */
	function init_image_sizes() {

		add_image_size( 'download_monitor_thumbnail', apply_filters('download_monitor_thumbnail_width', 64), apply_filters('download_monitor_thumbnail_height', 64), apply_filters('download_monitor_thumbnail_crop', true) );
		
		add_image_size( 'download_monitor_single', apply_filters('download_monitor_single_width', 200), apply_filters('download_monitor_single_height', 200), apply_filters('download_monitor_single_crop', true) );
		
	}
	
	/**
	 * Init frontend CSS
	 */
	function init_styles() {
		
		wp_enqueue_style( 'download_monitor', $this->plugin_url() . '/assets/css/frontend.css' );
			
	}
	
	/**
	 * Register/queue frontend scripts
	 */
	function frontend_scripts() {}

	/** Helper functions ******************************************************/
	
	/**
	 * Get the plugin url
	 */
	function plugin_url() { 
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}
	
	/**
	 * Get the plugin path
	 */
	function plugin_path() { 	
		if ( $this->plugin_path ) return $this->plugin_path;
		
		return $this->plugin_path = plugin_dir_path( __FILE__ );
	}
	 
	/**
	 * Ajax URL
	 */ 
	function ajax_url() { 
		$url = admin_url( 'admin-ajax.php' );
		
		$url = ( is_ssl() ) ? $url : str_replace( 'https', 'http', $url );
	
		return $url; 
	} 
				
	/** Transients ************************************************************/
		
	/**
	 * Clear Transients
	 */
	function clear_transients( $post_id = 0 ) {}
	
	/** Inline JavaScript Helper **********************************************/
		
	function add_inline_js( $code ) {
		$this->_inline_js .= "\n" . $code . "\n";
	}
	
	function output_inline_js() {
		if ($this->_inline_js) {
			
			echo "<!-- Download Monitor JavaScript-->\n<script type=\"text/javascript\">\njQuery(document).ready(function($) {";
			
			echo $this->_inline_js;
			
			echo "});\n</script>\n";
			
			$this->_inline_js = '';
			
		}
	}
}

/**
 * Init download_monitor class
 */
$GLOBALS['download_monitor'] = new WP_Download_Monitor();

} // class_exists check
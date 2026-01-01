<?php
namespace ElementorPro\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Updater {
	
	public function __construct() {
		// COMPLETELY DISABLE ALL UPDATE FUNCTIONALITY
		add_filter( 'site_transient_update_plugins', [ $this, 'remove_elementor_updates' ], 999999 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'remove_elementor_updates' ], 999999 );
		add_filter( 'plugins_api', [ $this, 'block_plugins_api' ], 10, 3 );
		
		// Remove update row
		remove_action( 'after_plugin_row_elementor-pro/elementor-pro.php', 'wp_plugin_update_row' );
	}
	
	public function remove_elementor_updates( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		
		// Remove Elementor Pro from updates
		if ( isset( $transient->response['elementor-pro/elementor-pro.php'] ) ) {
			unset( $transient->response['elementor-pro/elementor-pro.php'] );
		}
		
		// Mark as no update available
		if ( ! isset( $transient->no_update ) ) {
			$transient->no_update = [];
		}
		
		$transient->no_update['elementor-pro/elementor-pro.php'] = true;
		
		return $transient;
	}
	
	public function block_plugins_api( $result, $action, $args ) {
		// Block Elementor Pro update info
		if ( $action === 'plugin_information' && 
			 isset( $args->slug ) && 
			 ( $args->slug === 'elementor-pro' || $args->slug === 'elementor_pro' ) ) {
			return false;
		}
		
		return $result;
	}
}
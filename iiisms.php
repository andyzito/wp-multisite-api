<?php
/**
 * Plugin Name:     Multisite REST API Extensions
 * Description:     Provides API endpoints for managing multisite networks.
 * Author:          Lafayette College ITS
 * Text Domain:     multisite-api
 * Domain Path:     /languages
 * Version:         0.0.0
 *
 * @package         multisite-api
 */

/**
 * Send a text message.
 *
 * @param WP_REST_Request $request The request object.
 * @return boolean
 */
function multisite_api_list_sites( WP_REST_Request $request ) {
	// Get plugin options.
	// $options = get_option( 'multisite_api_settings' );

	// Do nothing if we're missing the catalog URL.
	// if ( ! $options['multisite_api_url'] ) {
	// 	return false;
	// }

	echo json_encode(get_sites());
	exit;
}

function multisite_api_create_site( WP_REST_Request $request ) {

	$params = $request->get_params();

	$site = get_current_site();
	$domain = $site->domain;
	$path = '/' . $params->name;
	$title = $params->title;
	$user_id = 1;

	wpmu_create_blog( $domain, $path, $title, $user_id );
	exit;
}

add_action( 'rest_api_init', function() {
	register_rest_route( 'multisite/v2', '/list/', array(
		'methods' => 'POST',
		'callback' => 'multisite_api_list_sites',
	) );
	register_rest_route( 'multisite/v2', '/create/', array(
		'methods' => 'POST',
		'callback' => 'multisite_api_create_site',
		'args' => array(
			'number' => array(
				'default' => false,
			),
			// 'item' => array(
			// 	'default' => false,
			// ),
			// 'bib' => array(
			// 	'default' => false,
			// ),
		),
		) );
} );

// if ( is_admin() ) {
// 	add_action( 'admin_menu', 'multisite_api_add_admin_menu' );
// 	add_action( 'admin_init', 'multisite_api_settings_init' );
// }

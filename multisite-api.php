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

require_once(ABSPATH . 'wp-admin/includes/ms.php');

class Multisite_API_Controller {
	public function __construct() {
		$this->namespace = '/multisite/v2';
		$this->register_routes();
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/list/', array(
			'methods' => 'POST',
			'callback' => array($this, 'list_sites'),
		) );
		register_rest_route( $this->namespace, '/delete/', array(
			'methods' => 'POST',
			'callback' => array($this, 'delete_site'),
			'args' => array(
				'id' => array(
					'default' => false,
				),
				'path' => array(
					'default' => false,
				),
				'drop' => array(
					'default' => true,
				)
			)
		) );
		register_rest_route( $this->namespace, '/create/', array(
			'methods' => 'POST',
			'callback' => array($this, 'create_site'),
			'args' => array(
				'path' => array(
					'default' => false,
				),
				'title' => array(
					'default' => false,
				),
				'admin' => array(
					'default' => 1,
				)
			),
		) );
	}

	private function extract_site( $params ) {
		if ( array_key_exists( 'id', $params ) && is_numeric( $params['id'] ) ) {
			$site = get_blog_details( $params['id'] );
		} elseif ( array_key_exists( 'path', $params ) && is_string( $params['path'] ) ) {
			$site = get_blog_details( $params['path'] );
		}

		if (!$site) {
			return false;
		} else {
			return $site;
		}
	}

	public function list_sites( WP_REST_Request $request ) {
		// Get plugin options.
		// $options = get_option( 'multisite_api_settings' );

		// Do nothing if we're missing the catalog URL.
		// if ( ! $options['multisite_api_url'] ) {
		// 	return false;
		// }

		echo json_encode(get_sites());
		exit;
	}

	public function create_site( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = get_current_site();
		$domain = $site->domain;
		$path   = '/' . ltrim( $params['path'], '/\\' );
		$title  = $params['title'];
		$admin  = $params['admin'];

		if (!is_numeric($admin)) {
			$admin = get_user_by('login', $params['admin'])->id;
		}

		echo "Attempting to create blog with:\n";
		echo "  Domain: $domain\n";
		echo "  Path: $path\n";
		echo "  Title: $title\n";
		echo "  Admin ID: $admin\n";

		$result = wpmu_create_blog( $domain, $path, $title, $user_id );

		if (is_numeric($result)) {
			echo "Site created! ID: $result";
		} else {
			echo "Sorry, site could not be created because...\n";
			echo json_encode($result);
		}
		exit;
	}

	public function delete_site( WP_REST_Request $request ) {
		$params = $request->get_params();
		$id     = $params['id'];
		$path   = $params['path'];
		$drop   = $params['drop'];

		$site = $this->extract_site( $params );

		wpmu_delete_blog( $site->blog_id, $drop );
		exit;
	}

	public function archive_site( WP_REST_Request $request ) {

		$params = $request->get_params();
		$id     = $params['id'];
		$path   = $params['path'];

		$site = $this->extract_site( $params );

		update_blog_status( $site->blog_id, 'archived', 1 );
		exit;
	}

	public function unarchive_site( WP_REST_Request $request ) {

		$params = $request->get_params();
		$id     = $params['id'];
		$path   = $params['path'];

		$site = $this->extract_site( $params );

		update_blog_status( $site->blog_id, 'archived', 0 );
	}

}
add_action( 'rest_api_init', function() {
	new Multisite_API_Controller();
} );

// if ( is_admin() ) {
// 	add_action( 'admin_menu', 'multisite_api_add_admin_menu' );
// 	add_action( 'admin_init', 'multisite_api_settings_init' );
// }

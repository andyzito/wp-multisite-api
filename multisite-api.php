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
		if ( !is_multisite() ) {
			exit('This is not a multisite');
		}
		$this->namespace = '/multisite/v2';
		$this->register_routes();
	}

	private function register_post_route($name, $baseargs, $addargs=array()) {
		register_rest_route( $this->namespace, "/$path/", array(
			'methods' => 'POST',
			'callback' => array($this, "command_$name"),
			'args' => array_merge($baseargs, $addargs)
		) );
	}

	public function register_routes() {

		$base_args = array(
			'path' => array(
				'default' => false,
			),
		);

		$site_exists_args = array_merge( array(
			'id' => array(
				'default' => false,
			)
		), $base_args );

		$this->register_post_route( 'activate', $site_exists_args );

		$this->register_post_route( 'archive', $site_exists_args );

		$this->register_post_route( 'create', $site_exists_args,
			array(
				'admin' => array(
					'default' => 1,
				)
			) );

		$this->register_post_route( 'deactivate', $site_exists_args );

		$this->register_post_route( 'delete', $site_exists_args,
			array(
				'drop' => array(
					'default' => true,
				)
			) );

		$this->register_post_route( 'empty', $site_exists_args );

		$this->register_post_route( 'list', array( ) );

		$this->register_post_route( 'mature', $site_exists_args );

		$this->register_post_route( 'meta', $site_exists_args );

		$this->register_post_route( 'option', $site_exists_args );

		$this->register_post_route( 'private', $site_exists_args );

		$this->register_post_route( 'public', $site_exists_args );

		$this->register_post_route( 'spam', $site_exists_args );

		$this->register_post_route( 'switch-language', $site_exists_args );

		$this->register_post_route( 'unarchive', $site_exists_args );

		$this->register_post_route( 'unmature', $site_exists_args );

		$this->register_post_route( 'unspam', $site_exists_args );
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

	public function command_list( WP_REST_Request $request ) {
		// Get plugin options.
		// $options = get_option( 'multisite_api_settings' );

		// Do nothing if we're missing the catalog URL.
		// if ( ! $options['multisite_api_url'] ) {
		// 	return false;
		// }

		echo json_encode(get_sites());
		exit;
	}

	public function command_create( WP_REST_Request $request ) {

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

	public function command_delete( WP_REST_Request $request ) {
		$params = $request->get_params();
		$id     = $params['id'];
		$path   = $params['path'];
		$drop   = $params['drop'];

		$site = $this->extract_site( $params );

		wpmu_delete_blog( $site->blog_id, $drop );
		exit;
	}

	public function command_archive( WP_REST_Request $request ) {

		$params = $request->get_params();
		$id     = $params['id'];
		$path   = $params['path'];

		$site = $this->extract_site( $params );

		update_blog_status( $site->blog_id, 'archived', 1 );
		exit;
	}

	public function command_unarchive( WP_REST_Request $request ) {

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

<?php
/**
 * Plugin Name:     Multisite REST API Extensions
 * Description:     Provides API endpoints for managing multisite networks.
 * Author:          Lafayette College ITS
 * Text Domain:     multisite-api
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         multisite-api
 * @author          Andrew Zito
 * @copyright       Lafayette College 2018 onwards
 */

require_once(ABSPATH . 'wp-admin/includes/ms.php');

/**
 * Multisite_API_Controller handles REST routes and callbacks for the Multisite API plugin.
 *
 * @package   mutlisite-api
 * @author    Andrew Zito
 * @copyright Lafayette College 2018 onwards
 * @since     1.0.0
 */
class Multisite_API_Controller {

	/**
	 * The constructor function.
	 *
	 * In its constructor, Multisite_API_Controller checks that the current site
	 * is a multisite/network, and registers all the REST routes needed by the API.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( !is_multisite() ) {
			exit('This is not a multisite');
		}

		$this->namespace = '/multisite/v2';
		$this->register_routes();
	}

	/**
	 * Extracts a site object from REST request params.
	 *
	 * This function takes a params array from a REST request, and looks for the
	 * parameters 'id' and 'slug'. It uses whichever one it finds to look up the
	 * site, and return a data object.
	 *
	 * @param array $params The parameter array from WP_REST_Request->get_params
	 *
	 * @return object $site A WP data object with details about the site found
	 */
	private function extract_site( $params ) {
		if ( array_key_exists( 'id', $params ) && is_numeric( $params['id'] ) ) {
			$site = get_blog_details( $params['id'] );
		} elseif ( array_key_exists( 'slug', $params ) && is_string( $params['slug'] ) ) {
			$site = get_blog_details( $params['slug'] );
		} else {
			echo "Please specify a site, either by id or slug.";
			exit;
		}

		if ( ! $site ) {
			echo "The site you specified was not found.";
			exit;
		}

		return $site;
	}

	private function update_site_status ( $site, $pref, $value ) {
		if ( $pref == 'archived' && $value == 1 ) {
			$action = 'archived';
		} else if ( $pref == 'archived' && $value == 0) {
			$action = 'unarchived';
		} else if ( $pref == 'deleted' && $value == 1 ) {
			$action = 'deactivated';
		} else if ( $pref == 'deleted' && $value == 0 ) {
			$action = 'activated';
		} else if ( $pref == 'spam' && $value == 1 ) {
			$action = 'marked as spam';
		} else if ( $pref == 'spam' && $value == 0 ) {
			$action = 'removed from spam';
		} else if ( $pref == 'public' && $value == 1 ) {
			$action = 'marked as public';
		} else if ( $pref == 'public' && $value == 0 ) {
			$action = 'marked as private';
		} else if ( $pref == 'mature' && $value == 1 ) {
			$action = 'marked as mature';
		} else if ( $pref == 'mature' && $value == 0 ) {
			$action = 'marked as unmature';
		}

		if ( is_main_site( $site->blog_id ) ) {
			echo "You are not allowed to change the main site.";
			exit;
		}

		$old = get_blog_status( $site->blog_id, $pref );

		if ( $old == $value ) {
			echo "Site {$site->siteurl} is already $action.";
			exit;
		}

		$result = update_blog_status( $site->blog_id, $pref, $value );

		if ( $result === $value ) {
			echo "Site {$site->siteurl} $action.";
		} else {
			echo "Error: Site {$site->siteurl} could not be $action.";
		}
		exit;
	}

	/**
	 * Registers a REST route with the POST method.
	 *
	 * This is a convenience function to handle bits of the rest route registration
	 * that are frequently repeated.
	 *
	 * @param string $name Name of the route, used for the path and the callback
	 * @param array $baseargs The set of arguments to register for the route
	 * @param array $addargs An additional set of arguments - this allows a call
	 * to this function to set base arguments from an external variable, and then
	 * add one or two of its own.
	 *
	 * @return void
	 */
	private function register_post_route( $name, $baseargs, $addargs = array()) {
		register_rest_route( $this->namespace, "/$name/", array(
			'methods' => 'POST',
			'callback' => array( $this, "command_$name" ),
			'args' => array_merge( $baseargs, $addargs )
		) );
	}

	/**
	 * Registers all REST routes needed by Multisite API.
	 *
	 * @return void
	 */
	public function register_routes() {

		$base_args = array(
			'slug' => array(
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
				'keep-tables' => array(
					'default' => false,
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

		$this->register_post_route( 'unarchive', $site_exists_args );

		$this->register_post_route( 'unmature', $site_exists_args );

		$this->register_post_route( 'unspam', $site_exists_args );
	}

	/**
	 * Activates a site.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to activate
	 *   slug: Slug of the site to activate
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_activate( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'deleted', 0 );
		exit;
	}

	/**
	 * Archives a site.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to archive
	 *   slug: Slug of the site to archive
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_archive( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'archived', 1 );
		exit;
	}

	/**
	 * Creates a new site within a multisite network.
	 *
	 * Accepts the following parameters:
	 *   slug:               Slug of the new site (used as path)
	 *   title:              Title of the new site
	 *   admin [admin user]: ID or username of user who will own the new site. Defaults to user with ID 1
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_create( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = get_current_site();
		$domain = $site->domain;
		$path   = '/' . ltrim( $params['slug'], '/\\' );
		$title  = $params['title'];
		$admin  = $params['admin'];

		if (!is_numeric($admin)) {
			$admin = get_user_by( 'login', $params['admin'] )->id;
		}

		echo "Attempting to create blog with:\n";
		echo "  Domain: $domain\n";
		echo "  Path: $path\n";
		echo "  Title: $title\n";
		echo "  Admin ID: $admin\n";

		$result = wpmu_create_blog( $domain, $path, $title, $admin );

		if ( is_numeric( $result ) ) {
			echo "Site created! ID: $result";
		} else {
			echo "Sorry, site could not be created because...\n";
			echo json_encode($result);
		}
		exit;
	}

	/**
	 * Deactivates a site.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to deactivate
	 *   slug: Slug of the site to deactivate
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_deactivate( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'deleted', 1 );
		exit;
	}

	/**
	 * Deletes a site.
	 *
	 * Accepts the following parameters:
	 *   id:                  ID of the site to delete
	 *   slug:                Slug of the site to delete
	 *   keep-tables [false]: Delete site but preserve database tables
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_delete( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );
		$drop   = ! $params['keep-tables'];

		if ( is_main_site( $site->blog_id ) ) {
			echo "You cannot delete the root site.";
			exit;
		}

		wpmu_delete_blog( $site->blog_id, $drop );
		echo "The site {$site->siteurl} was deleted.";
		exit;
	}

	/**
	 * Lists all sites in a multisite.
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_list( WP_REST_Request $request ) {
		echo json_encode( get_sites() );
		exit;
	}

	/**
	 * Marks a site as mature.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be marked as mature
	 *   slug: Slug of the site to be marked as mature
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_mature( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'mature', 1 );
		exit;
	}

	/**
	 * Makes a site private.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be made private
	 *   slug: Slug of the site to be made private
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_private( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'public', 0 );
		exit;
	}

	/**
	 * Makes a site public.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be made public
	 *   slug: Slug of the site to be made public
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_public( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'public', 1 );
		exit;
	}

	/**
	 * Marks a site as spam.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be marked as spam
	 *   slug: Slug of the site to be marked as spam
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_spam( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'spam', 1 );
		exit;
	}

	/**
	 * Unarchives a site.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be unarchive
	 *   slug: Slug of the site to be unarchived
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_unarchive( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'archived', 0 );
	}

	/**
	 * Marks a site as immature.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be marked as immature
	 *   slug: Slug of the site to be marked as immature
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_unmature( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'mature', 0 );
		exit;
	}

	/**
	 * Unmarks a site as spam.
	 *
	 * Accepts the following parameters:
	 *   id:   ID of the site to be unmarked as spam
	 *   slug: Slug of the site to be unmarked as spam
	 *
	 * @param WP_REST_Request A WP REST Request
	 *
	 * @return void
	 */
	public function command_unspam( WP_REST_Request $request ) {

		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_blog_status( $site, 'spam', 0 );
		exit;
	}

}
add_action( 'rest_api_init', function() {
	new Multisite_API_Controller();
} );

// if ( is_admin() ) {
// 	add_action( 'admin_menu', 'multisite_api_add_admin_menu' );
// 	add_action( 'admin_init', 'multisite_api_settings_init' );
// }

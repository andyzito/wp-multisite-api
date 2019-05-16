<?php

require_once ABSPATH . 'wp-admin/includes/ms.php';

/**
 * Multisite_API_Controller handles REST routes and callbacks for the Multisite API plugin.
 *
 * @package   multisite-api
 * @author    Andrew Zito
 * @copyright Lafayette College 2018 onwards
 * @since     1.0.0
 */
class Multisite_API_Controller {

	public $namespace = 'multisite/v2';

	/**
	 * The constructor function.
	 *
	 * In its constructor, Multisite_API_Controller checks that the current site
	 * is a multisite/network, and registers all the REST routes needed by the API.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! is_multisite() ) {
			exit( 'This is not a multisite' );
		}

		$this->register_routes();
	}

	/**
	 * Extracts a site object from REST request params.
	 *
	 * This function takes a params array from a REST request, and looks for the
	 * parameters 'id' and 'slug'. It uses whichever one it finds to look up the
	 * site, and return a data object.
	 *
	 * @param array $params The parameter array from WP_REST_Request->get_params.
	 *
	 * @return object $site A WP data object with details about the site found.
	 */
	private function extract_site( array $params ) {
		if ( array_key_exists( 'id', $params ) && is_numeric( $params['id'] ) ) {
			// If ID is passed, use that.
			$site = get_blog_details( $params['id'] );
		} elseif ( array_key_exists( 'slug', $params ) && is_string( $params['slug'] ) ) {
			// If slug is passed, used that.
			$site = get_blog_details( $params['slug'] );
		} else {
			// If neither is passed, die.
			echo 'Please specify a site, either by id or slug.';
			exit;
		}

		// If site not found, die.
		if ( ! $site ) {
			echo 'The site you specified was not found.';
			exit;
		}

		return $site;
	}

	/**
	 * Handles updating site status and returning success/failure message.
	 *
	 * @param WP_Site $site WP site data object (from get_blog_details).
	 * @param string  $pref Meta key that needs to be updated.
	 * @param int     $value Value we're updating the blog meta key to.
	 *
	 * @return void
	 */
	private function update_site_status( WP_Site $site, string $pref, int $value ) {
		// Translate key to be updated to human readable action string.
		// All credit to wp-cli.
		if ( 'archived' === $pref && 1 === $value ) {
			$action = 'archived';
		} elseif ( 'archived' === $pref && 0 === $value ) {
			$action = 'unarchived';
		} elseif ( 'deleted' === $pref && 1 === $value ) {
			$action = 'deactivated';
		} elseif ( 'deleted' === $pref && 0 === $value ) {
			$action = 'activated';
		} elseif ( 'spam' === $pref && 1 === $value ) {
			$action = 'marked as spam';
		} elseif ( 'spam' === $pref && 0 === $value ) {
			$action = 'removed from spam';
		} elseif ( 'public' === $pref && 1 === $value ) {
			$action = 'marked as public';
		} elseif ( 'public' === $pref && 0 === $value ) {
			$action = 'marked as private';
		} elseif ( 'mature' === $pref && 1 === $value ) {
			$action = 'marked as mature';
		} elseif ( 'mature' === $pref && 0 === $value ) {
			$action = 'marked as unmature';
		}

		// Do not allow updating of main site.
		if ( is_main_site( $site->blog_id ) ) {
			echo 'You are not allowed to change the main site.';
			exit;
		}

		// Check if site is already set to target.
		$old = get_blog_status( $site->blog_id, $pref );

		if ( (int) $old === (int) $value ) {
			echo "Site {$site->siteurl} is already $action.";
			exit;
		}

		// Update status.
		$result = update_blog_status( $site->blog_id, $pref, $value );

		// Did it work?
		if ( $result === $value ) {
			echo "Site {$site->siteurl} $action.";
		} else {
			echo "Error: Site {$site->siteurl} could not be $action.";
		}
		exit;
	}

	/**
	 * Registers a REST route.
	 *
	 * This is a convenience function to handle bits of the REST route registration
	 * that are frequently repeated.
	 *
	 * @param string $method HTTP methods.
	 * @param string $name Name of the route, used for the path and the callback.
	 * @param array  $baseargs The set of arguments to register for the route.
	 * @param string $capability The capability required to use this route.
	 *
	 * @return void
	 */
	private function register_route( array $methods, string $name, array $args, string $capability ) {
		register_rest_route(
			$this->namespace,
			"/$name/",
			array(
				'methods'  => $methods,
				'callback' => array( $this, "command_$name" ),
				'args'     => array_merge( $args ),
				'permission_callback' => function () use ($capability) {
					return current_user_can( $capability );
				}
			)
		);
	}

	/**
	 * Registers all REST routes needed by Multisite API.
	 *
	 * @return void
	 */
	private function register_routes() {
		$site_args = array(
			'id' => array(
				'default' => false,
			),
			'slug' => array(
				'default' => false,
			),
		);

		$this->register_route( ['POST'], 'activate', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'archive', $site_args, 'manage_sites' );

		$this->register_route(
			['POST'],
			'create',
			array_merge( $site_args,
				array(
					'admin' => array(
						'default' => 1,
					),
				)
			),
			'create_sites'
		);

		$this->register_route( ['POST'], 'deactivate', $site_args, 'manage_sites' );

		$this->register_route(
			['POST'],
			'delete',
			array_merge( $site_args,
				array(
					'keep-tables' => array(
						'default' => false,
					),
				)
			),
			'delete_sites'
		);

		// TODO $this->register_route(  'empty', $site_args );

		$this->register_route(
			['GET'],
			'list',
			array(
				'fields' => array(
					'default' => false,
				),
				'filter' => array(
					'default' => false,
				),
			),
			'manage_sites'
		);

		$this->register_route( ['POST'], 'mature', $site_args, 'manage_sites' );

		// TODO $this->register_route( 'meta', $site_args );

		// TODO $this->register_route( 'option', $site_args );

		$this->register_route( ['POST'], 'private', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'public', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'spam', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'unarchive', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'unmature', $site_args, 'manage_sites' );

		$this->register_route( ['POST'], 'unspam', $site_args, 'manage_sites' );

	}

	/**
	 * Activates a site.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to activate
	 *   slug: Slug of the site to activate
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_activate( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'deleted', 0 );
		exit;
	}

	/**
	 * Archives a site.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to archive
	 *   slug: Slug of the site to archive
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_archive( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'archived', 1 );
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
	 * @param WP_REST_Request $request A WP REST Request.
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

		if ( ! is_numeric( $admin ) ) {
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
			echo wp_json_encode( $result );
		}
		exit;
	}

	/**
	 * Deactivates a site.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to deactivate
	 *   slug: Slug of the site to deactivate
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_deactivate( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'deleted', 1 );
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
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_delete( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );
		$drop   = ! $params['keep-tables'];

		if ( is_main_site( $site->blog_id ) ) {
			echo 'You cannot delete the root site.';
			exit;
		}

		wpmu_delete_blog( $site->blog_id, $drop );
		echo "The site {$site->siteurl} was deleted.";
		exit;
	}

	/**
	 * Lists all sites in a multisite.
	 *
	 * Accepts the following parameters:
	 *   fields: Fields to return - specifying a single field will retrieve a flat array
	 *   filter: A single filter with format [key]=[val]
	 *
	 * For both 'fields' and 'filter', only the default WP site data fields are available.
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_list( WP_REST_Request $request ) {
		$params = $request->get_params();
		$fields = $params['fields'];
		$filter = $params['filter'];

		$sites = get_sites();

		if ( $filter ) {
			$filter = explode( '=', $filter );
			$sites  = array_filter(
				$sites,
				function( $s ) use ( $filter ) {
					return $s->{$filter[0]} === $filter[1];
				}
			);
		}

		if ( $fields ) {
			$fields = explode( ',', $fields );

			if ( count( $fields ) === 1 ) {
				$sites = array_map(
					function( $s ) use ( $fields ) {
						return $s->{$fields[0]};
					},
					$sites
				);
			} else {
				$sites = array_map(
					function( $s ) use ( $fields ) {
						$new = new stdClass();
						foreach ( $fields as $field ) {
							$new->{$field} = $s->{$field};
						}
						return $new;
					},
					$sites
				);
			}
		}

		echo wp_json_encode( $sites );
		exit;
	}

	/**
	 * Marks a site as mature.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be marked as mature
	 *   slug: Slug of the site to be marked as mature
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_mature( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'mature', 1 );
		exit;
	}

	/**
	 * Makes a site private.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be made private
	 *   slug: Slug of the site to be made private
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_private( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'public', 0 );
		exit;
	}

	/**
	 * Makes a site public.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be made public
	 *   slug: Slug of the site to be made public
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_public( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'public', 1 );
		exit;
	}

	/**
	 * Marks a site as spam.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be marked as spam
	 *   slug: Slug of the site to be marked as spam
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_spam( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'spam', 1 );
		exit;
	}

	/**
	 * Unarchives a site.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be unarchive
	 *   slug: Slug of the site to be unarchived
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_unarchive( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'archived', 0 );
	}

	/**
	 * Marks a site as immature.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be marked as immature
	 *   slug: Slug of the site to be marked as immature
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_unmature( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'mature', 0 );
		exit;
	}

	/**
	 * Unmarks a site as spam.
	 *
	 * Accepts (at least one of) the following parameters:
	 *   id:   ID of the site to be unmarked as spam
	 *   slug: Slug of the site to be unmarked as spam
	 *
	 * @param WP_REST_Request $request A WP REST Request.
	 *
	 * @return void
	 */
	public function command_unspam( WP_REST_Request $request ) {
		$params = $request->get_params();
		$site   = $this->extract_site( $params );

		$this->update_site_status( $site, 'spam', 0 );
		exit;
	}

}

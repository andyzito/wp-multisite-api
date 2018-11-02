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

	echo "TEST";
	exit;
}

/**
 * Get the item title from the catalog.
 *
 * @param string $url The catalog item URL.
 * @return string
 */
// function multisite_api_get_item_title( $url ) {
// 	$response = wp_remote_get( $url );
// 	if ( is_array( $response ) ) {
// 		$body = $response['body'];
// 		preg_match( '/fieldtag=t(.*)fieldtag=p/s', $body, $matches ); // Get the right section of code.
// 		preg_match( '/<strong>([^:]*).*<\/strong>/s', $matches[1], $matches2 ); // Grab the title text before the colon.
// 		$title = trim( $matches2[1] );
// 		return $title;
// 	}
// 	return false;
// }

/**
 * Format the location and call number.
 *
 * @param string $item The item metadata.
 * @return string
 */
// function multisite_api_format_item( $item ) {
// 	// Logic derived from multisite_api application; author unknown.
// 	$itemarray = explode( '|', $item );

// 	// Format the location.
// 	$location = trim( $itemarray[0] );
// 	$location = trim( preg_replace( '/[^A-Za-z0-9\-_\.\s]/', '', $location ) );

// 	// Format the call number.
// 	$callnumber = trim( $itemarray[1] );

// 	$item = "\nLoc: " . $location . "\nCall: " . $callnumber;

// 	return $item;
// }

/**
 * Add the settings page.
 */
// function multisite_api_add_admin_menu() {
// 	add_options_page( 'Library SMS', 'Library SMS', 'manage_options', 'multisite_api', 'multisite_api_settings_page' );
// }

/**
 * Plugin settings.
 */
// function multisite_api_settings_init() {
// 	register_setting( 'multisite_api_settings_group', 'multisite_api_settings', 'multisite_api_settings_sanitize' );
// 	add_settings_section(
// 		'multisite_api_settings_section',
// 		__( 'Plugin settings', 'multisite_api' ),
// 		'',
// 		'multisite_api_settings_group'
// 	);

// 	add_settings_field(
// 		'multisite_api_url',
// 		__( 'Library Catalog URL', 'multisite_api' ),
// 		'multisite_api_settings_url_callback',
// 		'multisite_api_settings_group',
// 		'multisite_api_settings_section'
// 	);

// 	add_settings_field(
// 		'multisite_api_ip',
// 		__( 'IP restriction', 'multisite_api' ),
// 		'multisite_api_settings_ip_callback',
// 		'multisite_api_settings_group',
// 		'multisite_api_settings_section'
// 	);
// }

/**
 * Sanitize input from the settings page.
 *
 * @param array $input Input from the settings page.
 * @return array
 */
// function multisite_api_settings_sanitize( $input ) {
// 	$new_input = array();
// 	if ( isset( $input['multisite_api_ip'] ) ) {
// 		$new_input['multisite_api_ip'] = $input['multisite_api_ip'];
// 	}
// 	if ( isset( $input['multisite_api_url'] ) ) {
// 		$new_input['multisite_api_url'] = $input['multisite_api_url'];
// 	}
// 	return $new_input;
// }

/**
 * Render the URL option.
 */
// function multisite_api_settings_url_callback() {
// 		$options = get_option( 'multisite_api_settings' );
// 		echo "<input name=\"multisite_api_settings[multisite_api_url]\" id=\"multisite_api_settings[multisite_api_url]\" type=\"text\" value=\"{$options['multisite_api_url']}\">";
// }

/**
 * Render the IP option.
 */
// function multisite_api_settings_ip_callback() {
// 		$options = get_option( 'multisite_api_settings' );
// 		echo "<input name=\"multisite_api_settings[multisite_api_ip]\" id=\"multisite_api_settings[multisite_api_ip]\" type=\"text\" value=\"{$options['multisite_api_ip']}\">";
// }

/**
 * Display the settings form.
* function multisite_api_settings_page() {
* 		?>
* 		<form action='options.php' method='post'>
* 		<?php
* 				settings_fields( 'multisite_api_settings_group' );
* 				do_settings_sections( 'multisite_api_settings_group' );
* 				submit_button();
* 		?>
* 		</form>
* 		<?php
* }
*/

add_action( 'rest_api_init', function() {
	register_rest_route( 'multisite/v2', '/list/', array(
		'methods' => 'POST',
		'callback' => 'multisite_api_list_sites',
		// 'args' => array(
			// 'number' => array(
			// 	'default' => false,
			// ),
			// 'item' => array(
			// 	'default' => false,
			// ),
			// 'bib' => array(
			// 	'default' => false,
			// ),
		// ),
	) );
} );

// if ( is_admin() ) {
// 	add_action( 'admin_menu', 'multisite_api_add_admin_menu' );
// 	add_action( 'admin_init', 'multisite_api_settings_init' );
// }

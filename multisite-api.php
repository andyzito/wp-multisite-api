<?php
/**
 * Plugin Name:     Multisite REST API
 * Description:     Provides API endpoints for managing multisite networks.
 * Author:          Andrew Zito
 * Copyright:       Lafayette College ITS 2018 onwards
 * Text Domain:     multisite-api
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         multisite-api
 * @author          Andrew Zito
 * @copyright       Lafayette College 2018 onwards
 */

// Pull in REST Controller class.
require_once 'class-multisite-api-controller.php';

add_action(
	'rest_api_init',
	function() {
		new Multisite_API_Controller();
	}
);

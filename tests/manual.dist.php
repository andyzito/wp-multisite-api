<?php

// This file can be used to manually spot-check this API. DO NOT USE THIS AGAINST A PRODUCTION SERVER, OBVIOUSLY.

function Call( $path, $data = array(), $method = 'POST' ) {
  $curl = curl_init();

  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

  // Authentication: Basic
  // This is user/pass auth, and again, SHOULD NOT BE USED IN A PRODUCTION SIUATION.
  // You can either use the Basic Auth plugin - https://github.com/WP-API/Basic-Auth
  // Or the Application Passwords plugin - https://wordpress.org/plugins/application-passwords/
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_USERPWD, "user:password");

  $params = http_build_query($data);
  curl_setopt($curl, CURLOPT_URL, 'localhost/wp-json/multisite/v2/' . $path . '?' . $params  );
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

  $result = curl_exec($curl);

  curl_close($curl);

  return $result;
}

// List sites data
echo ">> Listing all sites:\n";
$response = Call( 'list', [], 'GET' );
print_r($response);
echo "\n-------------------------------\n\n";

echo ">> Listing all sites, limited fields:\n";
$response = Call( 'list', [
  'fields' => 'blog_id,path,registered'
], 'GET' );
print_r($response);
echo "\n-------------------------------\n\n";

// Create site
$slug = 'test-site-' . time();
echo ">> Create a new site:\n";
$response = Call( 'create', [
  'slug' => $slug,
  'title' => 'Test Site',
] );
print_r($response);
echo "\n-------------------------------\n\n";

// Test all option updates.
echo ">> Testing various site status updates:\n";
$cmds = array(
  'deactivate',
  'deactivate',
  'activate',
  'activate',
  'archive',
  'archive',
  'unarchive',
  'unarchive',
  'spam',
  'spam',
  'unspam',
  'unspam',
  'mature',
  'mature',
  'unmature',
  'unmature',
  'public',
  'public',
  'private',
  'private',
);

foreach ( $cmds as $cmd ) {
  $response = Call( $cmd, [
    'slug' => $slug,
  ], 'PATCH' );
  echo $response . "\n";
}
echo "\n-------------------------------\n\n";

// Delete the site
echo ">> Deleting the site:\n";
$response = Call( 'delete', [
  'slug' => $slug,
], 'DELETE' );
print_r($response);
echo "\n-------------------------------\n\n";
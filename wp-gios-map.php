<?php

/*
Plugin Name: wp-gios-map
Plugin URI: http://github.com/gios-asu/wp-gios-map
Description: A WordPress plugin for displaying the GIOS research map
Version: 0.0.1
Author: Julie Ann Wrigley Global Institute of Sustainability
License: Copyright 2018

GitHub Plugin URI: https://github.com/gios-asu/wp-gios-map
GitHub Branch: production
*/


if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

define( 'WP_GIOS_MAP_PLUGIN_VERSION', '1.1.2' );

require __DIR__ . '/vendor/autoload.php';

$registry = new \Honeycomb\Services\Register();
$registry->register(
    require __DIR__ . '/src/registry/wordpress-registry.php'
);
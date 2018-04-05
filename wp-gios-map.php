<?php

/*
Plugin Name: gios-researchmap-wordpress-plugin
Plugin URI: https://github.com/gios-asu/gios-researchmap-wordpress-plugin
Description: A WordPress plugin for displaying the GIOS research map
Version: 1.0.0
Author: Julie Ann Wrigley Global Institute of Sustainability
License: Copyright 2018
*/


if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

define( 'WP_GIOS_MAP_PLUGIN_VERSION', '1.0.0' );

require __DIR__ . '/vendor/autoload.php';

//$registry = new \Honeycomb\Services\Register();
$registry = new \Honeycomb\Services\Register;
$registry->register(
    require __DIR__ . '/src/registry/wordpress-registry.php'
);

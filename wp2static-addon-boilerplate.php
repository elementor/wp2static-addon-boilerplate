<?php

/**
 * Plugin Name:       WP2Static Add-on: Boilerplate Add-on Example
 * Plugin URI:        https://github.com/WP2Static/wp2static-addon-boilerplate
 * Description:       Boilerplate add-on for WP2Static.
 * Version:           1.0-alpha-001
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-boilerplate
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_BOILERPLATE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_BOILERPLATE_VERSION', '1.0-alpha-006' );

if ( file_exists( WP2STATIC_BOILERPLATE_PATH . 'vendor/autoload.php' ) ) {
    require_once WP2STATIC_BOILERPLATE_PATH . 'vendor/autoload.php';
}

function run_wp2static_addon_boilerplate() {
    $controller = new WP2StaticBoilerplate\Controller();
    $controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticBoilerplate\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticBoilerplate\Controller', 'deactivate' ]
);

run_wp2static_addon_boilerplate();


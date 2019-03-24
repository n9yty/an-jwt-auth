<?php
/**
 * JWT Authentication for WP REST API 
 *
 * @package   an-jwt-auth
 * @link      https://github.com/SashokNekulin
 * @author    Alexandr Nikulin <nekulin@mail.ru>
 * @copyright 2018-2019 Alexandr Nikulin
 * @license   GPL v2 or later
 *
 * Plugin Name:  AN JWT AUTH
 * Description:  Extends the WP REST API using JSON Web Tokens Authentication as an authentication method.
 * Version:      1.0.1
 * Plugin URI:   https://github.com/SashokNekulin/an-jwt-auth
 * Author:       Alexandr Nikulin
 * Author URI:   https://github.com/SashokNekulin
 * Text Domain:  an-jwt-auth
 * Domain Path:  /languages/
 * Requires PHP: 5.6.0
 * GitHub Plugin URI: https://github.com/SashokNekulin/an-jwt-auth
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Защита от прямого захода
 */
define( 'AN_JWT_AUTH', true );
/**
 * Полный системный путь от корня в папку плагина
 */
define( 'AN_JWT_AUTH_ROOT', plugin_dir_path( __FILE__  ) );
/**
 * URL в папку плагина
 */
define( 'AN_JWT_AUTH_URL', plugin_dir_url( __FILE__  ) );
/**
 * Версия плагина
 */
define( 'AN_JWT_AUTH_VERSION', 'V1.0.1' );
/**
 * Действия при активации плагина
 */
register_activation_hook( __FILE__, 'an_jwt_auth_activate' );
function an_jwt_auth_activate(){
	require_once AN_JWT_AUTH_ROOT . 'activation.php';
}
// composer

require_once 'lib/vendor/autoload.php';

// config

function an_jwt_auth_setup() {
	load_plugin_textdomain( 'an-jwt-auth', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'an_jwt_auth_setup' );

// core
require_once 'core/bootstrap.php';

// update
$plugin = new SashokNekulin\WpAutoUpdate\PluginUpdate( __FILE__ );
$plugin->set_username( 'SashokNekulin' );
$plugin->set_repository( 'an-jwt-auth' );
$plugin->initialize();

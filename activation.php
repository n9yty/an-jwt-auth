<?php if( !defined( 'AN_JWT_AUTH' ) || AN_JWT_AUTH!==true ) die();
/**
 * Действия при активации плагина
 */

global $wpdb;
function an_jwt_auth_db_prefix( $table_name ){
    global $wpdb;
    return $wpdb->prefix . $table_name;
}

if($wpdb->get_var("SHOW TABLES LIKE '" . an_jwt_auth_db_prefix( 'an_jwt_token' ) . "'") != an_jwt_auth_db_prefix( 'an_jwt_token' )) {
	$wpdb->query( "CREATE TABLE `" . an_jwt_auth_db_prefix( 'an_jwt_token' ) . "` ( `user_id` int(11) NOT NULL, `hash` varchar(32) CHARACTER SET utf8 NOT NULL, `exp` int(11) NOT NULL, `ip` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" );
	$wpdb->query( "ALTER TABLE `" . an_jwt_auth_db_prefix( 'an_jwt_token' ) . "` ADD KEY `hash` (`hash`), ADD KEY `user_id` (`user_id`)" );
}
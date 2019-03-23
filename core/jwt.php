<?php  
use \Firebase\JWT\JWT;
if( !defined('AN_JWT_AUTH') || AN_JWT_AUTH!==true ) die();

class AnJwt {
    static public function ValidateTokenServer(){
        $auth = isset( $_SERVER[ 'HTTP_AUTHORIZATION' ] ) ? $_SERVER[ 'HTTP_AUTHORIZATION' ] : false;
        if ( !$auth ) {
            $auth = isset( $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ? $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] : false;
        }
        if ( !$auth ) {
            return null;
        }

        list( $token ) = sscanf( $auth, 'Bearer %s' );
        if ( !$token ) {
            return null;
        }
        $jwt_key = get_option( '_jwt_key', false );
        $secret_key = $jwt_key ? $jwt_key : false;
        if ( !$secret_key ) {
            return null;
        }
    
        try {
			$token = JWT::decode( $token, $secret_key, array( 'HS256' ) ); 
            if ( !isset( $token->id ) ) { 
                return null;
			}
            wp_set_current_user( $token->id );
            return true;
			
        } catch ( Exception $e ) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                $e->getMessage(),
                array(
                    'status' => 401,
                )
            );
        }
    }
    static public function oAuth(){
		if( isset( $_REQUEST[ 'username' ] ) and isset( $_REQUEST[ 'password' ] ) ){
			$auth = wp_authenticate( $_REQUEST[ 'username' ], $_REQUEST[ 'password' ] );
			if ( is_wp_error( $auth ) ) {
				return (object) array( 'code' => 'wrong_login_or_password', 'message' => 'Wrong login or password', 'status' => 403 );
			}
			else {
				return self::newToken( $auth->data->ID );
			}
		}
    }
    static public function Refresh(){
		if( isset( $_REQUEST[ 'refreshToken' ] ) ){
			try{
                $jwt_key = get_option( '_jwt_key', false );
				$decoded = JWT::decode( $_REQUEST[ 'refreshToken' ], $jwt_key, array( 'HS256' ) );
			} catch (Exception $e) {
				return (object) array( 'message' => $e->getMessage(), 'code' => 'token_failed_validation', 'status' => 403 );
			}
            //Не тот токен
            if( !isset( $decoded->token ) ){
				return (object) array( 'message' => 'Invalid token type', 'code' => 'invalid_token_type', 'status' => 403 );
			} 
			if( $decoded->token != 'refresh' ){
				return (object) array( 'message' => 'Invalid token type', 'code' => 'invalid_token_type', 'status' => 403 );
			} 
			global $wpdb;
			$row = $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "ac_jwt_token` WHERE `hash` = '" . $decoded->hash ."'" );
			if ( count( $row ) > 0){
				$wpdb->query("DELETE FROM `" . $wpdb->prefix . "ac_jwt_token` WHERE `hash` = '" . $decoded->hash . "'");
				//новые токены
				return self::newToken($decoded->id, $decoded->hash);
			}else{
				//Не валидный токен
				return (object) array( 'message' => 'Token out of date', 'code' => 'token_out_of_date', 'status' => 403 );
			}
		}
		
    }
    private function newToken($user_id , $reload_hash = false){
		global $wpdb;
        $time = time();
        $jwt_key = get_option( '_jwt_key', false );
        $jwt_nbf = get_option( '_jwt_nbf', 3600 );
        $jwt_nbf_session = get_option( '_jwt_nbf_session', false );
		$reload_hash ? $hash = $reload_hash : $hash = md5( $time . 'HS256' . $user_id . $jwt_nbf . $time );
		
		$token = array( 
			'iat' => $time, 
			'nbf' => $time,
			'exp' => $time + $jwt_nbf,			
			'id' => $user_id, 
			'token' => 'access',
			'hash' => $hash
			);
		$token_reflesh = array( 
			'iat' => $time,
			'nbf' => $time,
			'exp' => $time + $jwt_nbf_session,			
			'id' => $user_id, 
			'token' => 'refresh', 
			'hash' => $hash 
			);
		
		$access_token = JWT::encode($token, $jwt_key, 'HS256' );
		$refresh_token = JWT::encode($token_reflesh, $jwt_key, 'HS256' );
		self::clearToken();
		$ip = self::get_the_user_ip();
		$row = $wpdb->insert( $wpdb->prefix . 'ac_jwt_token', array( 'user_id' => $user_id, 'hash' => $hash , 'exp' => $time + $jwt_nbf_session, 'ip' => $ip ), array( '%d', '%s', '%d', '%s' ) );
		if( $row ){
			header('token: ' . $access_token . '');
            header('refreshToken: ' . $refresh_token . '');
            $user = get_userdata( $user_id );
            $rezult = (object) array( 
                'accessToken' => $access_token, 
                'refreshToken' => $refresh_token,
                'data' => (object) array(
                    'user_id' => $user->ID,
                    'user_email' => $user->user_email,
                    'user_nicename' => $user->user_nicename,
                    'user_display_name' => $user->display_name,
                    'client_ip' => $ip
                )
            );
			return apply_filters( 'jwt_auth_token_before_dispatch', $rezult );
		}else{
			return (object) array( 'message' => 'Error writing to database', 'code' => 'error_writing_to_database', 'status' => 500 );
		}
    }
    private function clearToken(){
		global $wpdb;
		$wpdb->query( "DELETE FROM `" . $wpdb->prefix . "ac_jwt_token` WHERE `exp` < " . time() );
    }
    private function get_the_user_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

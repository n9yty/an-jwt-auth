<?php  
use \Firebase\JWT\JWT;
if( !defined('AN_JWT_AUTH') || AN_JWT_AUTH!==true ) die();

class AN_OAUTH extends WP_REST_Controller{
	
	function __construct() {
		$this->namespace     = 'jwt-auth/v1';
		$this->resource_token = 'token';
		$this->resource_refresh = 'tokenRefresh';
		$this->resource_logout = 'logout';
	}
	
	
	function register_routes(){
		register_rest_route( $this->namespace, "/$this->resource_token", array(
			array(
				'methods'   => 'GET, POST',
				'callback'  => array( $this, 'oauth_token' ),
				'permission_callback' => false,
				'args'     => array(
					"username"     => $this->args_in(true,"Логин"),
					"password"     => $this->args_in(true,"Пароль"),
					),
				),
		) );
		register_rest_route( $this->namespace, "/$this->resource_refresh", array(
			array(
				'methods'   => 'GET, POST',
				'callback'  => array( $this, 'pb_oauth_refresh_token' ),
				'permission_callback' => false,
				'args'     => array(
					"refreshToken"     => $this->args_in(true,"Refresh token"),
					),
				),
		) );
		register_rest_route( $this->namespace, "/$this->resource_logout", array(
			array(
				'methods'   => 'GET, POST',
				'callback'  => array( $this, 'pb_oauth_logout' ),
				'permission_callback' => array( $this, 'pb_permissions_check' ),
			),
		) );
	}
	function pb_oauth_logout( $request ){
        $jwt_key = get_option( '_jwt_key', false );
		$auth = isset( $_SERVER[ 'HTTP_AUTHORIZATION' ] ) ? $_SERVER[ 'HTTP_AUTHORIZATION' ] : false;
        
        if ( !$auth ) {
            $auth = isset( $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ? $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] : false;
        }
        if ( !$auth ) {
            return null;
        }
		
        list( $token ) = sscanf( $auth, 'Bearer %s' );
        if (!$token) {
            return null;
        }

        $secret_key = $jwt_key ? $jwt_key : false;
        if ( !$secret_key ) {
            return null;
        }

        try {
			$token = JWT::decode( $token, $secret_key, array( 'HS256' ) ); 
			
            if ( !isset( $token->id ) ) { 
                return null;
			}
			if(isset($token->hash)){
				global $wpdb;
				$wpdb->query( "DELETE FROM `" . $wpdb->prefix . "an_jwt_token` WHERE `hash` = '" . $token->hash . "'" );
			}
			
        }catch(Exception $e){
			return null;
		}
	}	
	
	function pb_oauth_refresh_token( $request ){
		$rezult = AnJwt::Refresh();
		if( !isset($rezult->message) ){
			return $rezult;
		}else{
			return new WP_Error(
                $rezult->code,
                $rezult->message,
                array(
                    'status' => 403,
                )
            );
		}
	}	
	
	function oauth_token( $request ){
		$rezult = AnJwt::oAuth();
		if( !isset( $rezult->message ) ){
			return $rezult;
		}else{
			return new WP_Error(
                $rezult->code,
                $rezult->message,
                array(
                    'status' => $rezult->status,
                )
            );
		}
    }
    
	
	function pb_permissions_check( $request ) {
		if ( ! is_user_logged_in() )
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->error_status_code() ) ); 
		return true;
	}

	function error_status_code() {
		return is_user_logged_in() ? 403 : 401;
	}
	
	function args_in( $required = false, $description = "", $type = "string", $default = "", $enum = ""){
        $arg = array();
        $arg["required"] = $required;
        $arg["description"] = $description;
        $arg["type"] = $type;
        if($default != "") $arg["default"] = $default;
        if($enum != "") $arg["enum"] = $enum;
        return $arg;
    }
}
add_action( 'rest_api_init', function () {
    $controller = new AN_OAUTH();
	$controller->register_routes();
} );

$jwt_init  ;

function an_jwt_oauth_in( $rezult ){ 
	$Path=$_SERVER['REQUEST_URI'];
	$res = strpos ($Path, 'tokenRefresh'); // отменим проверку если в URI найдем строку
	$ros = strpos ($Path, 'token'); // отменим проверку если в URI найдем строку
	if($res or $ros){
		return $rezult; 
	}
	if( $rezult ){
		$rezult =  PB_JWT::ValidateTokenServer();
		if( isset($rezult->error) ) { 
			return $rezult->error;
		};
		return $rezult;
	}
	return $rezult; 
}
/**
 * Если используется JWT аутификация запускаем для нее REST API и включаем перехватчик
 */
$jwt_init = get_option( '_jwt_init', 0 );
if($jwt_init == 1) {
    add_filter( 'rest_authentication_errors', 'an_jwt_oauth_in', 100 );
}
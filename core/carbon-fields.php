<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field; 

if( !defined('AN_JWT_AUTH') || AN_JWT_AUTH!==true ) die();

add_action( 'after_setup_theme', 'an_jwt_auth_load_carbon_fields' );
function an_jwt_auth_load_carbon_fields() {
    \Carbon_Fields\Carbon_Fields::boot();
}

function an_jwt_auth_set_carbon_fields(){
        global $AnJwtAuth;
        Container::make( 'theme_options', __( 'JWT auth settings', 'an-jwt-auth' ) )
        ->set_page_parent( $AnJwtAuth->get_settings_page() ) 
        ->set_page_file( 'an-jwt-auth' )
        ->set_page_menu_title(  __( 'JWT AUTH', 'an-jwt-auth' ) )
        ->add_fields( array(
            Field::make( 'select', 'jwt_init', __( 'JWT switch?', 'an-jwt-auth' ) )
            ->set_options( array(
                '1' => __( 'Yes', 'an-jwt-auth' ),
                '0' => __( 'No', 'an-jwt-auth' )
            ) ),
			Field::make( 'text', 'jwt_nbf', __( 'Access token lifetime in seconds', 'an-jwt-auth' ) )
            ->set_attribute( 'type', 'number' )->set_attribute( 'step', '10' )
            ->set_default_value( 3600 )
            ->set_conditional_logic( array(
                'relation' => 'AND', 
                array(
                    'field' => 'jwt_init',
                    'value' => '1',
                    'compare' => '=', 
                )
            ) ),
			Field::make( 'text', 'jwt_nbf_session', __( 'The lifetime of the refresh token in seconds', 'an-jwt-auth' ) )
            ->set_attribute( 'type', 'number' )->set_attribute( 'step', '10' )
            ->set_default_value( 2678400 )
            ->set_conditional_logic( array(
                'relation' => 'AND', 
                array(
                    'field' => 'jwt_init',
                    'value' => '1',
                    'compare' => '=', 
                )
            ) ),
            Field::make( 'text', 'jwt_key', __( 'Secret JWT Key (Used to encrypt JWT)', 'an-jwt-auth' ) )
            ->set_default_value( 'USED_TO_ENCRYPT_JWTKEY' )
            ->set_conditional_logic( array(
                'relation' => 'AND', 
                array(
                    'field' => 'jwt_init',
                    'value' => '1',
                    'compare' => '=', 
                )
            ) ),
            Field::make( 'select', 'jwt_cors', __( 'Use CORs?', 'an-jwt-auth' ) )
            ->set_help_text( __( 'Cross-origin resource sharing (<a href="https://en.wikipedia.org/wiki/Cross-origin_resource_sharing"> CORS </a>; with persistent. - "sharing resources between different sources")-a technology of modern browsers that allows you to provide a web page access to resources of another domain.', 'an-jwt-auth' ) )
            ->set_options( array(
                '1' => __( 'Yes', 'an-jwt-auth' ),
                '0' => __( 'No', 'an-jwt-auth' )
            ) ) 
            ->set_conditional_logic( array(
                'relation' => 'AND', 
                array(
                    'field' => 'jwt_init',
                    'value' => '1',
                    'compare' => '=', 
                )
            ) ),
        ) );
}
add_action( 'carbon_fields_register_fields', 'an_jwt_auth_set_carbon_fields' );
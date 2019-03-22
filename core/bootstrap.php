<?php  if( !defined('AN_JWT_AUTH') || AN_JWT_AUTH!==true ) die();
/**
 * Логика плагина
 */
class AnJwtAuth {
    private $page;
    public function __construct(){
        $this->page = is_multisite() ? 'settings.php' : 'options-general.php';
        add_filter( 'plugin_action_links', array( $this, 'add_plugin_actions_links' ), 10, 2 );
        $this->includes();
    }
    public function add_plugin_actions_links( $links, $file ) { 
        if( basename( dirname( $file ) ) == 'an-jwt-auth' ) {
          $links[] = '<a href="' . esc_url( add_query_arg( array( 'page' => 'an-jwt-auth' ), $this->page ) ) . '">' . __( 'Settings', 'an-jwt-auth' ) . '</a>';
        }
        return $links;
    }
    public function get_settings_page() {
        return $this->page;
    }

    private function includes() {
        include 'carbon-fields.php';
    }
}
$GLOBALS[ 'AnJwtAuth' ] = new AnJwtAuth();


<?php
/*
Plugin Name: Encuestas-AL
Plugin URI: http://localhost
Description: Este plugin es de pruebas
Version: 0.0.1
*/

//Requieres
require_once dirname(__FILE__). '/clases/CodigoCorto.php';

//logica funcion activar
function Activar(){
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}enc_encuestas(
        `EncuestaID` INT NOT NULL AUTO_INCREMENT, 
        `Nombre` VARCHAR(45) NULL , 
        `Shortcode` VARCHAR(45) NULL , 
        PRIMARY KEY (`EncuestaID`)) 
        COMMENT = 'Tabla principal de encuestas del plugin Encuestas'
    ";  
    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}enc_encuestas_detalle(
        `DetalleID` INT NOT NULL AUTO_INCREMENT, 
        `EncuestaID` INT NULL , 
        `Pregunta` VARCHAR(150) NULL , 
        `Tipo` VARCHAR(45) NULL, 
        PRIMARY KEY (`DetalleID`)) 
        COMMENT = 'Tabla detalle de encuestas del plugin Encuestas'";

    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}enc_enc_det_respuestas(
        `RespuestaID` INT NOT NULL AUTO_INCREMENT, 
        `DetalleID` INT NULL , 
        `Respuesta` VARCHAR(150) NULL , 
        PRIMARY KEY (`RespuestaID`)) 
        COMMENT = 'Tabla respuestas de preguntas encuestas del plugin Encuestas'";

    $wpdb->query($sql);

}

//logica funcion desactivar
function Desactivar(){
    
}



//Registart la función de activar
register_activation_hook( __FILE__, 'Activar' );
//Registrar la funcion de desactivar
register_deactivation_hook( __FILE__, 'Desactivar' );

// hook para añadir el menu de administración del plugin
add_action( 'admin_menu','CrearMenu' );
function CrearMenu(){
    //Añadir la opción en el menu principal del dashboard
    add_menu_page( 
        "Super Encuentas", //Tituo de la pagina
        "Encuestas", // Titulo del Menu
        'manage_options', // Capability -Permisos
        plugin_dir_path( __FILE__ ). 'admin/lista_encuestas.php', // Puedes ser nombre de Slug solamente ejemplo: 'sp-menu'
        null , // funcion Callback. contenido de la pagina. si es null tienen en cuenta el slug
        plugin_dir_url( __FILE__ ). 'admin/img/icon.png', // Url icono
        '5' //position
    );

    add_submenu_page(
        plugin_dir_path( __FILE__ ). 'admin/lista_encuestas.php', //$parent_slug:string,
        'Ajustes', //$page_title:string,
        'Ajustes', //$menu_title:string,
        'manage_options', //$capability:string,
        'sp_menu_ajustes', //$menu_slug:string,
        'MostrarAdminAjustes' //$callback:callable,
    );
}

//Funcion callback para desplegar la pagina de administración del menu del plugin.
function MostrarAdminPrincipal(){
    echo '<h1>Contenido de la Página de Encuestas</h1>';
}

function MostrarAdminAjustes(){
    echo '<h1>Contenido de la Página de Ajustes</h1>';
}

//Encolar Bootstrap
function EncolarBootstrap($hook) {
    //echo "<script>console.log('$hook')</script>";
    //Validar que el hook solo se ejecute cuando se está en la pagiand e encuestas del plugin.
    if($hook!='test-wordpress-plugin/admin/lista_encuestas.php' &&
        $hook!='encuestas_page_sp_menu_ajustes'){
        return; 
    }
    // Encola el estilo de Bootstrap
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css');
    // Encola jQuery
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.4.1.slim.min.js', array(), null, false);

    // Encola Popper.js
    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js', array('jquery'), null, true);

    // Encola Bootstrap
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js', array('jquery', 'popper'), null, true);
}
add_action( 'admin_enqueue_scripts', 'EncolarBootstrap' );

//Encolar JS/CC Propios
function EncolarPropios($hook) {
    if($hook!='test-wordpress-plugin/admin/lista_encuestas.php' &&
        $hook!='encuestas_page_sp_menu_ajustes'){
        return; 
    }
    
    // JS Admin de encuestas
    wp_enqueue_script('adminencjs', plugins_url("admin/js/admin_encuestas.js", __FILE__), array('jquery'), null, true);

    //pasar datos de AJAX desde PHP a JavaScript
    wp_localize_script( 
        'adminencjs',//$handle:string, id del script que recibirá las variables
        'SolicitudesAjax',//$object_name:string, Nombre de la variable en el Script JS
        [
            'url' => admin_url( 'admin-ajax.php'), //Archivo punto de entrada para realizar solicitudes AJAX en el lado del administrador de WordPress
            'seguridad' => wp_create_nonce( "seg" ) //'seg' es el alias con que se crea el nonce.
        ]//$l10n:array de variables
    );
}
add_action( 'admin_enqueue_scripts', 'EncolarPropios' );


//AJAX

//funcion para eliminar una encuesta
function EliminarEnucesta(){
    $nonce = $_POST['nonce']; //recibimos el nonce del front.
    if(!wp_verify_nonce( $nonce, 'seg' )){ //verificamos que sea el mismo que se envió desde php wp_localize_script
        die('No tiene permisos para ejecutar esa petición AJAX');
    }

    $id = $_POST['id'];
    // obtenemos las tablas
    global $wpdb;
    $tabla_encuestas = "{$wpdb->prefix}enc_encuestas";
    $tabla_detalle = "{$wpdb->prefix}enc_encuestas_detalle";

    $wpdb->query('START TRANSACTION'); // Iniciar transacción
    try {
        // Realizar los deletes
        $wpdb->delete($tabla_detalle, array('EncuestaID' => $id));
        $wpdb->delete($tabla_encuestas, array('EncuestaID' => $id));

        $wpdb->query('COMMIT'); // Confirmar la transacción si todo va bien
        return true;
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK'); // Realizar rollback si ocurre una excepción
        die("Ha ocurrido un error, no se pudieron eliminar los datos. Error: " . $e->getMessage());
    }
}

add_action( 'wp_ajax_peticioneliminar', 'EliminarEnucesta');


//shortcode

function imprimirShortcode($atts){
    $_short = new CodigoCorto;
    $id = $atts['id'];

    $html = $_short->armarForm($id);
    return $html;
}

add_shortcode( "ENC", "imprimirShortcode" );
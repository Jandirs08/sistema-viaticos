<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_API_NAMESPACE = 'viaticos/v1';

// =============================================================================
// REGISTRO DE RUTAS
// =============================================================================

function viaticos_registrar_endpoints() {

    register_rest_route( VIATICOS_API_NAMESPACE, '/nueva-solicitud', array(
        'methods'             => WP_REST_Server::CREATABLE, // POST
        'callback'            => 'viaticos_callback_nueva_solicitud',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_solicitud(),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/nuevo-gasto', array(
        'methods'             => WP_REST_Server::CREATABLE, // POST
        'callback'            => 'viaticos_callback_nuevo_gasto',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_gasto(),
    ) );
}
add_action( 'rest_api_init', 'viaticos_registrar_endpoints' );


// =============================================================================
// PERMISSION CALLBACK COMPARTIDO
// =============================================================================

function viaticos_permission_logueado() {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Debes estar autenticado para realizar esta acción.', 'theme-administracion' ),
            array( 'status' => 401 )
        );
    }
    return true;
}


// =============================================================================
// ENDPOINT 1: POST /viaticos/v1/nueva-solicitud
// =============================================================================

/**
 * Esquema de validación y saneamiento de parámetros para el endpoint de solicitud.
 */
function viaticos_args_solicitud() {
    return array(
        'dni'    => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => static function( $value ) {
                return (bool) preg_match( '/^\d{8}$/', $value );
            },
        ),
        'monto'  => array(
            'required'          => true,
            'type'              => 'number',
            'minimum'           => 1,
            'sanitize_callback' => 'floatval',
        ),
        'fecha'  => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => static function( $value ) {
                // Espera formato Y-m-d.
                $d = DateTime::createFromFormat( 'Y-m-d', $value );
                return $d && $d->format( 'Y-m-d' ) === $value;
            },
        ),
        'motivo' => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ),
        'ceco'   => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
    );
}

function viaticos_callback_nueva_solicitud( WP_REST_Request $request ) {

    $titulo = sprintf(
        'Solicitud %s — %s',
        sanitize_text_field( $request->get_param( 'dni' ) ),
        current_time( 'd/m/Y H:i' )
    );

    $post_id = wp_insert_post( array(
        'post_type'   => 'solicitud_viatico',
        'post_status' => 'publish',
        'post_title'  => $titulo,
        'post_author' => get_current_user_id(),
    ), true ); // true → retorna WP_Error si falla.

    if ( is_wp_error( $post_id ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => $post_id->get_error_message() ),
            500
        );
    }

    update_field( 'dni_colaborador',  $request->get_param( 'dni' ),    $post_id );
    update_field( 'monto_solicitado', $request->get_param( 'monto' ),  $post_id );
    update_field( 'fecha_viaje',      $request->get_param( 'fecha' ),  $post_id );
    update_field( 'motivo_viaje',     $request->get_param( 'motivo' ), $post_id );
    update_field( 'centro_costo',     $request->get_param( 'ceco' ),   $post_id );
    update_field( 'estado_solicitud', 'pendiente',                      $post_id );

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Solicitud de viático creada correctamente.',
            'id'      => $post_id,
        ),
        201
    );
}


// =============================================================================
// ENDPOINT 2: POST /viaticos/v1/nuevo-gasto
// =============================================================================

/**
 * Esquema de validación y saneamiento de parámetros para el endpoint de gasto.
 */
function viaticos_args_gasto() {
    return array(
        'id_solicitud'    => array(
            'required'          => true,
            'type'              => 'integer',
            'minimum'           => 1,
            'sanitize_callback' => 'absint',
            'validate_callback' => static function( $value ) {
                // Verifica que la solicitud padre exista y sea del CPT correcto.
                return 'solicitud_viatico' === get_post_type( absint( $value ) );
            },
        ),
        'tipo'            => array(
            'required'          => false,
            'type'              => 'string',
            'enum'              => array( 'vale_caja', 'vale_movilidad', 'modelo_liquidacion' ),
            'sanitize_callback' => 'sanitize_text_field',
        ),
        'fecha'           => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => static function( $value ) {
                $d = DateTime::createFromFormat( 'Y-m-d', $value );
                return $d && $d->format( 'Y-m-d' ) === $value;
            },
        ),
        'importe'         => array(
            'required'          => true,
            'type'              => 'number',
            'minimum'           => 0.1,
            'sanitize_callback' => 'floatval',
        ),
        'ruc'             => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => static function( $value ) {
                return empty( $value ) || (bool) preg_match( '/^\d{1,11}$/', $value );
            },
        ),
        'razon_social'    => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
        'nro_comprobante' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
        'cuenta_contable' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
    );
}

function viaticos_callback_nuevo_gasto( WP_REST_Request $request ) {

    $id_solicitud = $request->get_param( 'id_solicitud' );

    $titulo = sprintf(
        'Gasto — Solicitud #%d — %s',
        $id_solicitud,
        current_time( 'd/m/Y H:i' )
    );

    $post_id = wp_insert_post( array(
        'post_type'   => 'gasto_rendicion',
        'post_status' => 'publish',
        'post_title'  => $titulo,
        'post_author' => get_current_user_id(),
    ), true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => $post_id->get_error_message() ),
            500
        );
    }

    update_field( 'id_solicitud_padre',  $id_solicitud,                                $post_id );
    update_field( 'tipo_plantilla',      $request->get_param( 'tipo' ),               $post_id );
    update_field( 'fecha_emision',       $request->get_param( 'fecha' ),              $post_id );
    update_field( 'importe_comprobante', $request->get_param( 'importe' ),            $post_id );
    update_field( 'ruc_proveedor',       $request->get_param( 'ruc' ),               $post_id );
    update_field( 'razon_social',        $request->get_param( 'razon_social' ),       $post_id );
    update_field( 'nro_comprobante',     $request->get_param( 'nro_comprobante' ),    $post_id );
    update_field( 'cuenta_contable',     $request->get_param( 'cuenta_contable' ),    $post_id );

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Gasto rendido registrado correctamente.',
            'id'      => $post_id,
        ),
        201
    );
}

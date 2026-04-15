<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_API_NAMESPACE = 'viaticos/v1';

// =============================================================================
// REGISTRO DE RUTAS
// =============================================================================

function viaticos_registrar_endpoints() {

    // ── GET: solicitudes del usuario autenticado ──────────────────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/mis-solicitudes', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_mis_solicitudes',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    // ── GET: gastos/rendiciones del usuario autenticado ───────────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/mis-rendiciones', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_mis_rendiciones',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    // ── POST: crear nueva solicitud ───────────────────────────────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/nueva-solicitud', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_nueva_solicitud',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_solicitud(),
    ) );

    // ── POST: registrar gasto rendido ─────────────────────────────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/nuevo-gasto', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_nuevo_gasto',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_gasto(),
    ) );

    // ── GET: todas las solicitudes (solo admins) ───────────────────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/todas-solicitudes', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_todas_solicitudes',
        'permission_callback' => 'viaticos_permission_admin',
    ) );

    // ── POST: actualizar estado de una solicitud (solo admins) ────────────────
    register_rest_route( VIATICOS_API_NAMESPACE, '/actualizar-estado', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_actualizar_estado',
        'permission_callback' => 'viaticos_permission_admin',
        'args'                => array(
            'id_solicitud' => array(
                'required'          => true,
                'type'              => 'integer',
                'minimum'           => 1,
                'sanitize_callback' => static function( $value ) { return absint( $value ); },
                'validate_callback' => static function( $value ) {
                    return 'solicitud_viatico' === get_post_type( absint( $value ) );
                },
            ),
            'nuevo_estado' => array(
                'required'          => true,
                'type'              => 'string',
                'enum'              => array( 'aprobada', 'observada', 'rechazada' ),
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
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

/**
 * viaticos_permission_admin()
 *
 * Valida que el usuario sea administrador del sistema o tenga el rol
 * personalizado 'admin_viaticos'. Usado en los endpoints de evaluación.
 *
 * @return bool|WP_Error
 */
function viaticos_permission_admin() {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Debes estar autenticado.', 'theme-administracion' ),
            array( 'status' => 401 )
        );
    }

    if ( current_user_can( 'administrator' ) || current_user_can( 'admin_viaticos' ) || current_user_can( 'edit_others_posts' ) ) {
        return true;
    }

    return new WP_Error(
        'rest_forbidden',
        __( 'No tienes permisos para realizar esta acción.', 'theme-administracion' ),
        array( 'status' => 403 )
    );
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
            'sanitize_callback' => static function( $value ) { return floatval( $value ); },
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

    // Usar field KEYS (no nombres) para que ACF no necesite resolver el key
    // en un post recién creado — práctica recomendada en la doc oficial de ACF.
    update_field( 'field_sol_dni_colaborador',  $request->get_param( 'dni' ),    $post_id );
    update_field( 'field_sol_monto_solicitado', $request->get_param( 'monto' ),  $post_id );
    update_field( 'field_sol_fecha_viaje',      $request->get_param( 'fecha' ),  $post_id );
    update_field( 'field_sol_motivo_viaje',     $request->get_param( 'motivo' ), $post_id );
    update_field( 'field_sol_centro_costo',     $request->get_param( 'ceco' ),   $post_id );
    update_field( 'field_sol_estado_solicitud', 'pendiente',                      $post_id );

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
            'sanitize_callback' => static function( $value ) { return absint( $value ); },
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
            'sanitize_callback' => static function( $value ) { return floatval( $value ); },
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

    // Usar field KEYS para que ACF no necesite resolver el key en tiempo de ejecución.
    update_field( 'field_gas_id_solicitud_padre',  $id_solicitud,                             $post_id );
    update_field( 'field_gas_tipo_plantilla',      $request->get_param( 'tipo' ),            $post_id );
    update_field( 'field_gas_fecha_emision',       $request->get_param( 'fecha' ),           $post_id );
    update_field( 'field_gas_importe_comprobante', $request->get_param( 'importe' ),         $post_id );
    update_field( 'field_gas_ruc_proveedor',       $request->get_param( 'ruc' ),            $post_id );
    update_field( 'field_gas_razon_social',        $request->get_param( 'razon_social' ),    $post_id );
    update_field( 'field_gas_nro_comprobante',     $request->get_param( 'nro_comprobante' ), $post_id );
    update_field( 'field_gas_cuenta_contable',     $request->get_param( 'cuenta_contable' ), $post_id );

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Gasto rendido registrado correctamente.',
            'id'      => $post_id,
        ),
        201
    );
}


// =============================================================================
// ENDPOINT GET: /viaticos/v1/mis-solicitudes
// Retorna las solicitudes del usuario autenticado con todos sus campos ACF.
// =============================================================================

/**
 * viaticos_callback_mis_solicitudes()
 *
 * Lee los posts del CPT 'solicitud_viatico' cuyo autor es el usuario actual
 * y retorna un array JSON con los campos ACF leídos mediante get_field().
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function viaticos_callback_mis_solicitudes( WP_REST_Request $request ) {

    $posts = get_posts( array(
        'post_type'      => 'solicitud_viatico',
        'post_status'    => 'publish',
        'author'         => get_current_user_id(),
        'posts_per_page' => 100,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    $data = array();

    foreach ( $posts as $post ) {
        $data[] = array(
            'id'     => $post->ID,
            'dni'    => get_field( 'dni_colaborador',  $post->ID ) ?: '',
            'monto'  => (float) get_field( 'monto_solicitado', $post->ID ),
            'fecha'  => get_field( 'fecha_viaje',      $post->ID ) ?: '',
            'motivo' => get_field( 'motivo_viaje',     $post->ID ) ?: '',
            'ceco'   => get_field( 'centro_costo',     $post->ID ) ?: '',
            'estado' => get_field( 'estado_solicitud', $post->ID ) ?: 'pendiente',
        );
    }

    return new WP_REST_Response( $data, 200 );
}


// =============================================================================
// ENDPOINT GET: /viaticos/v1/mis-rendiciones
// Retorna los gastos rendidos del usuario autenticado con todos sus campos ACF.
// =============================================================================

/**
 * viaticos_callback_mis_rendiciones()
 *
 * Lee los posts del CPT 'gasto_rendicion' cuyo autor es el usuario actual
 * y retorna un array JSON con los campos ACF leídos mediante get_field().
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function viaticos_callback_mis_rendiciones( WP_REST_Request $request ) {

    $posts = get_posts( array(
        'post_type'      => 'gasto_rendicion',
        'post_status'    => 'publish',
        'author'         => get_current_user_id(),
        'posts_per_page' => 100,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    $data = array();

    foreach ( $posts as $post ) {
        $data[] = array(
            'id'          => $post->ID,
            'id_solicitud'=> (int) get_field( 'id_solicitud_padre',  $post->ID ),
            'tipo'        => get_field( 'tipo_plantilla',      $post->ID ) ?: '',
            'fecha'       => get_field( 'fecha_emision',       $post->ID ) ?: '',
            'importe'     => (float) get_field( 'importe_comprobante', $post->ID ),
            'ruc'         => get_field( 'ruc_proveedor',       $post->ID ) ?: '',
            'razon'       => get_field( 'razon_social',        $post->ID ) ?: '',
            'nro'         => get_field( 'nro_comprobante',     $post->ID ) ?: '',
            'cuenta'      => get_field( 'cuenta_contable',     $post->ID ) ?: '',
        );
    }

    return new WP_REST_Response( $data, 200 );
}


// =============================================================================
// ENDPOINT GET: /viaticos/v1/todas-solicitudes  (solo admins)
// =============================================================================

/**
 * viaticos_callback_todas_solicitudes()
 *
 * Devuelve TODAS las solicitudes del sistema (de todos los colaboradores),
 * incluyendo el nombre del autor, para ser consumidas por el panel admin.
 *
 * @return WP_REST_Response
 */
function viaticos_callback_todas_solicitudes( WP_REST_Request $request ) {

    $posts = get_posts( array(
        'post_type'      => 'solicitud_viatico',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    $data = array();

    foreach ( $posts as $post ) {
        $data[] = array(
            'id'          => $post->ID,
            'colaborador' => get_the_author_meta( 'display_name', $post->post_author ),
            'dni'         => get_field( 'dni_colaborador',  $post->ID ) ?: '',
            'monto'       => (float) get_field( 'monto_solicitado', $post->ID ),
            'fecha'       => get_field( 'fecha_viaje',      $post->ID ) ?: '',
            'motivo'      => get_field( 'motivo_viaje',     $post->ID ) ?: '',
            'ceco'        => get_field( 'centro_costo',     $post->ID ) ?: '',
            'estado'      => get_field( 'estado_solicitud', $post->ID ) ?: 'pendiente',
            'fecha_creacion' => get_the_date( 'd/m/Y', $post->ID ),
        );
    }

    return new WP_REST_Response( $data, 200 );
}


// =============================================================================
// ENDPOINT POST: /viaticos/v1/actualizar-estado  (solo admins)
// =============================================================================

/**
 * viaticos_callback_actualizar_estado()
 *
 * Actualiza el campo ACF 'estado_solicitud' de una solicitud existente.
 * Solo accesible por administradores.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function viaticos_callback_actualizar_estado( WP_REST_Request $request ) {

    $post_id     = $request->get_param( 'id_solicitud' );
    $nuevo_estado = $request->get_param( 'nuevo_estado' );

    // Doble verificación: el post debe ser del tipo correcto.
    if ( 'solicitud_viatico' !== get_post_type( $post_id ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'ID de solicitud inválido.' ),
            400
        );
    }

    $resultado = update_field( 'field_sol_estado_solicitud', $nuevo_estado, $post_id );

    if ( false === $resultado ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'No se pudo actualizar el estado.' ),
            500
        );
    }

    return new WP_REST_Response(
        array(
            'success'      => true,
            'message'      => sprintf( 'Solicitud #%d actualizada a "%s".', $post_id, $nuevo_estado ),
            'id_solicitud' => $post_id,
            'nuevo_estado' => $nuevo_estado,
        ),
        200
    );
}

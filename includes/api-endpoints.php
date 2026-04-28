<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_API_NAMESPACE = 'viaticos/v1';

require_once __DIR__ . '/acf-keys.php';
require_once __DIR__ . '/api/permissions.php';
require_once __DIR__ . '/api/helpers.php';
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/solicitudes.php';
require_once __DIR__ . '/api/rendiciones.php';
require_once __DIR__ . '/api/adjuntos.php';
require_once __DIR__ . '/api/ocr.php';

// =============================================================================
// REGISTRO DE RUTAS
// =============================================================================

function viaticos_registrar_endpoints() {

    // Guard de dependencia: si ACF no está activo, los callbacks que usan
    // get_field/update_field generarían fatal. Registra un fallback 503 y
    // sale antes de declarar las rutas reales.
    if ( ! function_exists( 'viaticos_acf_active' ) || ! viaticos_acf_active() ) {
        register_rest_route( VIATICOS_API_NAMESPACE, '/(?P<rest_path>.*)', array(
            'methods'             => WP_REST_Server::ALLMETHODS,
            'permission_callback' => '__return_true',
            'callback'            => static function () {
                return new WP_REST_Response( array(
                    'success' => false,
                    'message' => 'Servicio no disponible: dependencia ACF inactiva.',
                ), 503 );
            },
        ) );
        return;
    }

    // ── Config (single source of truth: estados, schemas, mapping) ────────────

    register_rest_route( VIATICOS_API_NAMESPACE, '/config', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_config',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    // ── Colaborador ───────────────────────────────────────────────────────────

    register_rest_route( VIATICOS_API_NAMESPACE, '/mis-solicitudes', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_mis_solicitudes',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/mis-rendiciones', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_mis_rendiciones',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/nueva-solicitud', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_nueva_solicitud',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_solicitud(),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/nuevo-gasto', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_nuevo_gasto',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_gasto(),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/finalizar-rendicion', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_finalizar_rendicion',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => viaticos_args_finalizar_rendicion(),
    ) );

    // ── Admin ─────────────────────────────────────────────────────────────────

    register_rest_route( VIATICOS_API_NAMESPACE, '/todas-solicitudes', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_todas_solicitudes',
        'permission_callback' => 'viaticos_permission_admin',
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/detalle-rendicion-admin/(?P<id_solicitud>\d+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_detalle_rendicion_admin',
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
        ),
    ) );

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
                'enum'              => VIATICOS_DECISIONES,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'comentario' => array(
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
        ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/decidir-rendicion', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_decidir_rendicion',
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
            'decision' => array(
                'required'          => true,
                'type'              => 'string',
                'enum'              => VIATICOS_DECISIONES,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'comentario' => array(
                'required'          => false,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
        ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/editar-solicitud', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_editar_solicitud',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => array_merge( viaticos_args_solicitud(), array(
            'id_solicitud' => array(
                'required'          => true,
                'type'              => 'integer',
                'minimum'           => 1,
                'sanitize_callback' => static function( $value ) { return absint( $value ); },
                'validate_callback' => static function( $value ) {
                    return 'solicitud_viatico' === get_post_type( absint( $value ) );
                },
            ),
        ) ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/detalle-solicitud/(?P<id_solicitud>\d+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_detalle_solicitud',
        'permission_callback' => 'viaticos_permission_logueado',
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
        ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/reenviar-rendicion', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_reenviar_rendicion',
        'permission_callback' => 'viaticos_permission_logueado',
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
        ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/gasto/(?P<id_gasto>\\d+)', array(
        'methods'             => 'DELETE',
        'callback'            => 'viaticos_callback_eliminar_gasto',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => array(
            'id_gasto' => array(
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => static function( $v ) { return absint( $v ); },
            ),
        ),
    ) );

    // ── Adjuntos ──────────────────────────────────────────────────────────────

    register_rest_route( VIATICOS_API_NAMESPACE, '/gasto-adjuntos/(?P<id_gasto>\\d+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'viaticos_callback_listar_adjuntos',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => array(
            'id_gasto' => array(
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => static function( $v ) { return absint( $v ); },
            ),
        ),
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/gasto-adjunto', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_subir_adjunto',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/ocr-extract', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'viaticos_callback_ocr_extract',
        'permission_callback' => 'viaticos_permission_logueado',
    ) );

    register_rest_route( VIATICOS_API_NAMESPACE, '/gasto-adjunto/(?P<id_adjunto>\\d+)', array(
        'methods'             => 'DELETE',
        'callback'            => 'viaticos_callback_eliminar_adjunto',
        'permission_callback' => 'viaticos_permission_logueado',
        'args'                => array(
            'id_adjunto' => array(
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => static function( $v ) { return absint( $v ); },
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'viaticos_registrar_endpoints' );

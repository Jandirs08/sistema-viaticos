<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        'aprobador' => array(
            'required'          => false,
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
    ), true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => $post_id->get_error_message() ),
            500
        );
    }

    update_field( ACF_SOL_DNI,       $request->get_param( 'dni' ),    $post_id );
    update_field( ACF_SOL_MONTO,     $request->get_param( 'monto' ),  $post_id );
    update_field( ACF_SOL_FECHA,     $request->get_param( 'fecha' ),  $post_id );
    update_field( ACF_SOL_MOTIVO,    $request->get_param( 'motivo' ), $post_id );
    update_field( ACF_SOL_CECO,      $request->get_param( 'ceco' ),   $post_id );
    update_field( ACF_SOL_APROBADOR, $request->get_param( 'aprobador' ) ?: '', $post_id );
    update_field( ACF_SOL_ESTADO,    'pendiente',                      $post_id );
    registrarEventoSolicitud( $post_id, 'solicitud_creada', get_current_user_id() );

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Solicitud de viático creada correctamente.',
            'id'      => $post_id,
        ),
        201
    );
}

function viaticos_callback_mis_solicitudes( WP_REST_Request $request ) {

    $posts = get_posts( array(
        'post_type'      => 'solicitud_viatico',
        'post_status'    => 'publish',
        'author'         => get_current_user_id(),
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    $ids               = wp_list_pluck( $posts, 'ID' );
    $tiene_gastos_map  = viaticos_mapa_solicitudes_con_gastos( $ids );

    $data = array();

    foreach ( $posts as $post ) {
        $data[] = array(
            'id'                   => $post->ID,
            'dni'                  => get_field( ACF_SOL_DNI,    $post->ID ) ?: '',
            'monto'                => (float) get_field( ACF_SOL_MONTO, $post->ID ),
            'fecha'                => get_field( ACF_SOL_FECHA,  $post->ID ) ?: '',
            'motivo'               => wp_strip_all_tags( get_field( ACF_SOL_MOTIVO, $post->ID ) ?: '' ),
            'ceco'                 => get_field( ACF_SOL_CECO,   $post->ID ) ?: '',
            'estado'               => get_field( ACF_SOL_ESTADO, $post->ID ) ?: 'pendiente',
            'rendicion_finalizada' => viaticos_es_rendicion_finalizada( $post->ID ),
            'estado_rendicion'     => viaticos_get_estado_rendicion( $post->ID ),
            'fecha_creacion'       => get_the_date( 'd/m/Y', $post->ID ),
            'tiene_gastos'         => ! empty( $tiene_gastos_map[ $post->ID ] ),
        );
    }

    return new WP_REST_Response( $data, 200 );
}

function viaticos_callback_todas_solicitudes( WP_REST_Request $request ) {

    $posts = get_posts( array(
        'post_type'      => 'solicitud_viatico',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    $ids              = wp_list_pluck( $posts, 'ID' );
    $tiene_gastos_map = viaticos_mapa_solicitudes_con_gastos( $ids );

    $data = array();

    foreach ( $posts as $post ) {
        $estado               = get_field( ACF_SOL_ESTADO, $post->ID ) ?: 'pendiente';
        $rendicion_finalizada = viaticos_es_rendicion_finalizada( $post->ID );

        $data[] = array(
            'id'                   => $post->ID,
            'colaborador'          => get_the_author_meta( 'display_name', $post->post_author ),
            'dni'                  => get_field( ACF_SOL_DNI,    $post->ID ) ?: '',
            'monto'                => (float) get_field( ACF_SOL_MONTO, $post->ID ),
            'fecha'                => get_field( ACF_SOL_FECHA,  $post->ID ) ?: '',
            'motivo'               => wp_strip_all_tags( get_field( ACF_SOL_MOTIVO, $post->ID ) ?: '' ),
            'ceco'                 => get_field( ACF_SOL_CECO,   $post->ID ) ?: '',
            'estado'               => $estado,
            'rendicion_finalizada' => $rendicion_finalizada,
            'fecha_creacion'       => get_the_date( 'd/m/Y', $post->ID ),
            'estado_rendicion'     => viaticos_get_estado_rendicion( $post->ID ),
            'tiene_gastos'         => ! empty( $tiene_gastos_map[ $post->ID ] ),
        );
    }

    return new WP_REST_Response( $data, 200 );
}

function viaticos_callback_actualizar_estado( WP_REST_Request $request ) {

    $post_id      = $request->get_param( 'id_solicitud' );
    $nuevo_estado = $request->get_param( 'nuevo_estado' );
    $estado_actual = get_field( ACF_SOL_ESTADO, $post_id ) ?: 'pendiente';

    if ( 'solicitud_viatico' !== get_post_type( $post_id ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'ID de solicitud inválido.' ),
            400
        );
    }

    if ( 'pendiente' !== $estado_actual ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'Solo se pueden decidir solicitudes en estado pendiente.' ),
            400
        );
    }

    $comentario = sanitize_textarea_field( (string) ( $request->get_param( 'comentario' ) ?: '' ) );

    if ( 'observada' === $nuevo_estado && '' === trim( $comentario ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'Debes indicar el motivo de la observación.' ),
            422
        );
    }

    $resultado = update_field( ACF_SOL_ESTADO, $nuevo_estado, $post_id );

    if ( false === $resultado ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'No se pudo actualizar el estado.' ),
            500
        );
    }

    $eventos = array(
        'aprobada'  => 'solicitud_aprobada',
        'observada' => 'solicitud_observada',
        'rechazada' => 'solicitud_rechazada',
    );

    if ( $estado_actual !== $nuevo_estado && isset( $eventos[ $nuevo_estado ] ) ) {
        registrarEventoSolicitud( $post_id, $eventos[ $nuevo_estado ], get_current_user_id(), $comentario );
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

function viaticos_callback_editar_solicitud( WP_REST_Request $request ) {
    $id_solicitud = absint( $request->get_param( 'id_solicitud' ) );
    $solicitud    = get_post( $id_solicitud );

    if ( ! $solicitud || 'solicitud_viatico' !== $solicitud->post_type ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'Solicitud no encontrada.' ),
            404
        );
    }

    if ( (int) $solicitud->post_author !== get_current_user_id() ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'No tienes permisos para editar esta solicitud.' ),
            403
        );
    }

    $estado_actual = get_field( ACF_SOL_ESTADO, $id_solicitud ) ?: 'pendiente';

    if ( 'observada' !== $estado_actual ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'Solo puedes editar una solicitud observada.' ),
            409
        );
    }

    update_field( ACF_SOL_DNI,    $request->get_param( 'dni' ),    $id_solicitud );
    update_field( ACF_SOL_MONTO,  $request->get_param( 'monto' ),  $id_solicitud );
    update_field( ACF_SOL_FECHA,  $request->get_param( 'fecha' ),  $id_solicitud );
    update_field( ACF_SOL_MOTIVO, $request->get_param( 'motivo' ), $id_solicitud );
    update_field( ACF_SOL_CECO,   $request->get_param( 'ceco' ),   $id_solicitud );
    update_field( ACF_SOL_ESTADO, 'pendiente',                      $id_solicitud );

    registrarEventoSolicitud( $id_solicitud, 'solicitud_reenviada', get_current_user_id() );

    return new WP_REST_Response(
        array(
            'success'      => true,
            'message'      => 'Solicitud corregida y reenviada a revisión.',
            'id_solicitud' => $id_solicitud,
            'nuevo_estado' => 'pendiente',
        ),
        200
    );
}

/**
 * GET /viaticos/v1/detalle-solicitud/{id_solicitud}
 * Devuelve datos pesados (historial completo + total_rendido) on-demand.
 * Validación: autor de la solicitud o admin.
 */
function viaticos_callback_detalle_solicitud( WP_REST_Request $request ) {
    $id_solicitud = absint( $request->get_param( 'id_solicitud' ) );
    $solicitud    = get_post( $id_solicitud );

    if ( ! $solicitud || 'solicitud_viatico' !== $solicitud->post_type ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Solicitud no encontrada.' ), 404 );
    }

    $is_owner = (int) $solicitud->post_author === get_current_user_id();
    $is_admin = current_user_can( 'manage_viaticos' );

    if ( ! $is_owner && ! $is_admin ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Sin permisos para ver esta solicitud.' ), 403 );
    }

    $rendicion_finalizada = viaticos_es_rendicion_finalizada( $id_solicitud );

    return new WP_REST_Response( array(
        'success'        => true,
        'id'             => $id_solicitud,
        'historial'      => viaticos_preparar_historial_solicitud( $id_solicitud ),
        'total_rendido'  => $rendicion_finalizada ? viaticos_calcular_total_rendido_solicitud( $id_solicitud ) : 0,
    ), 200 );
}

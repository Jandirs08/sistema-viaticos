<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function viaticos_args_gasto() {
    return array(
        'id_solicitud'    => array(
            'required'          => true,
            'type'              => 'integer',
            'minimum'           => 1,
            'sanitize_callback' => static function( $value ) { return absint( $value ); },
            'validate_callback' => static function( $value ) {
                return 'solicitud_viatico' === get_post_type( absint( $value ) );
            },
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
        'id_categoria' => array(
            'required'          => false,
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ),
        'descripcion_concepto' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ),
        'motivo_movilidad' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ),
        'destino_movilidad' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
        'ceco_oi'         => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
    );
}

function viaticos_args_finalizar_rendicion() {
    return array(
        'id_solicitud' => array(
            'required'          => true,
            'type'              => 'integer',
            'minimum'           => 1,
            'sanitize_callback' => static function( $value ) { return absint( $value ); },
            'validate_callback' => static function( $value ) {
                return 'solicitud_viatico' === get_post_type( absint( $value ) );
            },
        ),
    );
}

function viaticos_callback_nuevo_gasto( WP_REST_Request $request ) {

    $id_solicitud = $request->get_param( 'id_solicitud' );
    $solicitud    = get_post( $id_solicitud );
    $tenia_gastos = viaticos_solicitud_tiene_gastos( $id_solicitud );

    if ( ! $solicitud || 'solicitud_viatico' !== $solicitud->post_type ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'La solicitud indicada no existe o no es válida.',
            ),
            400
        );
    }

    if ( (int) $solicitud->post_author !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'No tienes permisos para registrar gastos en esta solicitud.',
            ),
            403
        );
    }

    $estado_solicitud = get_field( 'estado_solicitud', $id_solicitud );

    if ( 'aprobada' !== $estado_solicitud ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'Solo puedes registrar gastos sobre solicitudes aprobadas.',
            ),
            400
        );
    }

    if ( viaticos_es_rendicion_finalizada( $id_solicitud ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'La rendición de esta solicitud ya fue finalizada y no admite más gastos.',
            ),
            409
        );
    }

    $id_categoria = absint( $request->get_param( 'id_categoria' ) );
    $cat_term     = $id_categoria ? get_term( $id_categoria, 'categoria_gasto' ) : null;

    if ( ! $cat_term || is_wp_error( $cat_term ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'Debes seleccionar una categoría de gasto válida.',
            ),
            400
        );
    }

    $clase_doc = get_field( 'clase_doc', 'categoria_gasto_' . $id_categoria ) ?: '';

    if ( '' === trim( (string) $clase_doc ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'La categoría seleccionada no tiene Clase de Documento configurada.',
            ),
            400
        );
    }

    $tipo = viaticos_clase_doc_to_tipo( $clase_doc );

    if ( 'movilidad' === $tipo ) {
        $required_movilidad = array(
            'motivo_movilidad'  => 'El motivo de movilidad es obligatorio.',
            'destino_movilidad' => 'El destino de movilidad es obligatorio.',
            'ceco_oi'           => 'El CECO / OI es obligatorio.',
        );

        foreach ( $required_movilidad as $field_name => $message ) {
            if ( '' === trim( (string) $request->get_param( $field_name ) ) ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => $message,
                    ),
                    400
                );
            }
        }
    } else {
        $required_documento = array(
            'ruc'             => 'El RUC del proveedor es obligatorio.',
            'razon_social'    => 'La razón social es obligatoria.',
            'nro_comprobante' => 'El número de comprobante es obligatorio.',
        );

        foreach ( $required_documento as $field_name => $message ) {
            if ( '' === trim( (string) $request->get_param( $field_name ) ) ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => $message,
                    ),
                    400
                );
            }
        }

        if ( ! preg_match( '/^\d{11}$/', (string) $request->get_param( 'ruc' ) ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => 'El RUC del proveedor debe tener 11 dígitos.',
                ),
                400
            );
        }
    }

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

    update_field( 'field_gas_id_solicitud_padre',  $id_solicitud,                    $post_id );
    update_field( 'field_gas_fecha_emision',       $request->get_param( 'fecha' ),   $post_id );
    update_field( 'field_gas_importe_comprobante', $request->get_param( 'importe' ), $post_id );
    wp_set_object_terms( $post_id, $id_categoria, 'categoria_gasto' );

    if ( 'movilidad' === $tipo ) {
        update_field( 'field_gas_motivo_movilidad',  $request->get_param( 'motivo_movilidad' ),  $post_id );
        update_field( 'field_gas_destino_movilidad', $request->get_param( 'destino_movilidad' ), $post_id );
        update_field( 'field_gas_ceco_oi',           $request->get_param( 'ceco_oi' ),           $post_id );
    } else {
        update_field( 'field_gas_ruc_proveedor',        $request->get_param( 'ruc' ),                  $post_id );
        update_field( 'field_gas_razon_social',         $request->get_param( 'razon_social' ),         $post_id );
        update_field( 'field_gas_nro_comprobante',      $request->get_param( 'nro_comprobante' ),      $post_id );
        update_field( 'field_gas_descripcion_concepto', $request->get_param( 'descripcion_concepto' ), $post_id );
    }

    if ( ! $tenia_gastos ) {
        registrarEventoSolicitud( $id_solicitud, 'rendicion_iniciada', get_current_user_id() );
    }

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Gasto rendido registrado correctamente.',
            'id'      => $post_id,
        ),
        201
    );
}

function viaticos_callback_finalizar_rendicion( WP_REST_Request $request ) {

    $id_solicitud = $request->get_param( 'id_solicitud' );
    $solicitud    = get_post( $id_solicitud );

    if ( ! $solicitud || 'solicitud_viatico' !== $solicitud->post_type ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'La solicitud indicada no existe o no es válida.',
            ),
            404
        );
    }

    if ( (int) $solicitud->post_author !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'No tienes permisos para finalizar la rendición de esta solicitud.',
            ),
            403
        );
    }

    if ( 'aprobada' !== get_field( 'estado_solicitud', $id_solicitud ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'Solo puedes finalizar la rendición de solicitudes aprobadas.',
            ),
            400
        );
    }

    if ( viaticos_es_rendicion_finalizada( $id_solicitud ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'La rendición de esta solicitud ya fue finalizada.',
            ),
            409
        );
    }

    if ( ! viaticos_solicitud_tiene_gastos( $id_solicitud ) ) {
        return new WP_REST_Response(
            array(
                'success' => false,
                'message' => 'Debes registrar al menos un gasto antes de finalizar la rendición.',
            ),
            400
        );
    }

    update_post_meta( $id_solicitud, 'rendicion_finalizada', '1' );

    if ( '' === viaticos_get_estado_rendicion( $id_solicitud ) ) {
        update_post_meta( $id_solicitud, 'estado_rendicion', 'finalizada' );
    }

    registrarEventoSolicitud( $id_solicitud, 'rendicion_finalizada', get_current_user_id() );

    return new WP_REST_Response(
        array(
            'success'              => true,
            'message'              => 'La rendición fue finalizada correctamente.',
            'id_solicitud'         => $id_solicitud,
            'rendicion_finalizada' => true,
            'estado_rendicion'     => 'finalizada',
        ),
        200
    );
}

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

    $data = array_map( 'viaticos_build_gasto_dto', $posts );

    return new WP_REST_Response( $data, 200 );
}

function viaticos_callback_detalle_rendicion_admin( WP_REST_Request $request ) {
    $id_solicitud = absint( $request->get_param( 'id_solicitud' ) );

    if ( 'solicitud_viatico' !== get_post_type( $id_solicitud ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'La solicitud indicada no existe.' ),
            404
        );
    }

    $estado = get_field( 'estado_solicitud', $id_solicitud ) ?: 'pendiente';

    if ( 'aprobada' !== $estado ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'Solo se puede revisar una rendición de solicitudes aprobadas.' ),
            400
        );
    }

    $gastos        = viaticos_obtener_gastos_solicitud( $id_solicitud );
    $usuario       = get_userdata( (int) get_post_field( 'post_author', $id_solicitud ) );
    $monto         = (float) get_field( 'monto_solicitado', $id_solicitud );
    $perfil        = $usuario ? viaticos_get_user_perfil( $usuario->ID ) : array( 'cargo' => '', 'area' => '' );
    $total_rendido = 0;

    foreach ( $gastos as $gasto ) {
        $total_rendido += (float) $gasto['importe'];
    }

    return new WP_REST_Response(
        array(
            'id'                   => $id_solicitud,
            'estado'               => $estado,
            'rendicion_finalizada' => viaticos_es_rendicion_finalizada( $id_solicitud ),
            'estado_rendicion'     => viaticos_get_estado_rendicion( $id_solicitud ),
            'fecha_creacion'       => get_the_date( 'd/m/Y', $id_solicitud ),
            'fecha_viaje'          => get_field( 'fecha_viaje', $id_solicitud ) ?: '',
            'motivo'               => wp_strip_all_tags( get_field( 'motivo_viaje', $id_solicitud ) ?: '' ),
            'ceco'                 => get_field( 'centro_costo', $id_solicitud ) ?: '',
            'dni'                  => get_field( 'dni_colaborador', $id_solicitud ) ?: '',
            'cargo'                => $perfil['cargo'],
            'area'                 => $perfil['area'],
            'monto'                => $monto,
            'total_rendido'        => $total_rendido,
            'saldo'                => $monto - $total_rendido,
            'colaborador'          => array(
                'id'           => $usuario ? $usuario->ID : 0,
                'display_name' => $usuario ? $usuario->display_name : '',
                'email'        => $usuario ? $usuario->user_email : '',
            ),
            'gastos'               => $gastos,
            'historial'            => viaticos_preparar_historial_solicitud( $id_solicitud ),
        ),
        200
    );
}

function viaticos_callback_decidir_rendicion( WP_REST_Request $request ) {

    $id_solicitud = $request->get_param( 'id_solicitud' );
    $decision     = $request->get_param( 'decision' );

    if ( 'solicitud_viatico' !== get_post_type( $id_solicitud ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'ID de solicitud inválido.' ),
            400
        );
    }

    if ( 'aprobada' !== get_field( 'estado_solicitud', $id_solicitud ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'La solicitud no está aprobada.' ),
            400
        );
    }

    if ( ! viaticos_es_rendicion_finalizada( $id_solicitud ) ) {
        return new WP_REST_Response(
            array( 'success' => false, 'message' => 'La rendición aún no fue finalizada por el colaborador.' ),
            400
        );
    }

    $estado_anterior = viaticos_get_estado_rendicion( $id_solicitud );
    update_post_meta( $id_solicitud, 'estado_rendicion', $decision );

    $eventos = array(
        'aprobada'  => 'rendicion_aprobada',
        'observada' => 'rendicion_observada',
        'rechazada' => 'rendicion_rechazada',
    );

    if ( $estado_anterior !== $decision && isset( $eventos[ $decision ] ) ) {
        registrarEventoSolicitud( $id_solicitud, $eventos[ $decision ], get_current_user_id() );
    }

    $labels = array(
        'aprobada'  => 'aprobada',
        'observada' => 'marcada como observada',
        'rechazada' => 'rechazada',
    );

    return new WP_REST_Response(
        array(
            'success'          => true,
            'message'          => sprintf( 'Rendición de solicitud #%d %s.', $id_solicitud, $labels[ $decision ] ?? $decision ),
            'id_solicitud'     => $id_solicitud,
            'estado_rendicion' => $decision,
        ),
        200
    );
}

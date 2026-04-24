<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function viaticos_es_rendicion_finalizada( $solicitud_id ) {
    return '1' === get_post_meta( $solicitud_id, 'rendicion_finalizada', true );
}

function viaticos_get_estado_rendicion( $solicitud_id ) {
    $valor = get_post_meta( $solicitud_id, 'estado_rendicion', true );
    return $valor ?: '';
}

function viaticos_solicitud_tiene_gastos( $solicitud_id ) {
    $gastos = get_posts( array(
        'post_type'      => 'gasto_rendicion',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
        'meta_query'     => array(
            array(
                'key'   => 'id_solicitud_padre',
                'value' => absint( $solicitud_id ),
            ),
        ),
    ) );

    return ! empty( $gastos );
}

/**
 * Mapea CLASE DOC textual a un tipo interno que el front usa para decidir
 * qué campos mostrar. No hay lista cerrada: cualquier valor distinto de
 * VALE MOVILIDAD / VALE DE CAJA se trata como 'documento' (form con PDF).
 */
function viaticos_clase_doc_to_tipo( $clase_doc ) {
    $n = strtoupper( trim( (string) $clase_doc ) );
    if ( 'VALE MOVILIDAD' === $n ) return 'movilidad';
    if ( 'VALE DE CAJA'   === $n ) return 'vale_caja';
    return 'documento';
}

/**
 * Build the canonical DTO for a single gasto_rendicion post.
 * Single source of truth for the shape of a gasto exposed via REST.
 */
function viaticos_build_gasto_dto( $post ) {
    $term_ids   = wp_get_object_terms( $post->ID, 'categoria_gasto', array( 'fields' => 'ids' ) );
    $cat_id     = ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ? (int) $term_ids[0] : 0;
    $cat_term   = $cat_id ? get_term( $cat_id, 'categoria_gasto' ) : null;
    $clase_doc  = $cat_id ? ( get_field( 'clase_doc',    'categoria_gasto_' . $cat_id ) ?: '' ) : '';
    $cta_cont   = $cat_id ? ( get_field( 'cta_contable', 'categoria_gasto_' . $cat_id ) ?: '' ) : '';

    return array(
        'id'                 => $post->ID,
        'id_solicitud'       => (int) get_field( 'id_solicitud_padre', $post->ID ),
        'tipo'               => viaticos_clase_doc_to_tipo( $clase_doc ),
        'fecha'              => get_field( 'fecha_emision', $post->ID ) ?: '',
        'importe'            => (float) get_field( 'importe_comprobante', $post->ID ),
        'ruc'                => get_field( 'ruc_proveedor', $post->ID ) ?: '',
        'razon'              => get_field( 'razon_social', $post->ID ) ?: '',
        'nro'                => get_field( 'nro_comprobante', $post->ID ) ?: '',
        'concepto'           => get_field( 'descripcion_concepto', $post->ID ) ?: '',
        'motivo_movilidad'   => get_field( 'motivo_movilidad', $post->ID ) ?: '',
        'destino_movilidad'  => get_field( 'destino_movilidad', $post->ID ) ?: '',
        'ceco_oi'            => get_field( 'ceco_oi', $post->ID ) ?: '',
        'categoria_id'       => $cat_id,
        'categoria_nombre'   => $cat_term ? $cat_term->name : '',
        'cta_contable'       => $cta_cont,
        'clase_doc'          => $clase_doc,
    );
}

function viaticos_obtener_gastos_solicitud( $solicitud_id ) {
    $posts = get_posts( array(
        'post_type'      => 'gasto_rendicion',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => array(
            array(
                'key'   => 'id_solicitud_padre',
                'value' => absint( $solicitud_id ),
            ),
        ),
    ) );

    return array_map( 'viaticos_build_gasto_dto', $posts );
}

/**
 * Resolve the cargo + area taxonomy terms attached to a user.
 *
 * @param int $user_id
 * @return array { cargo: string, area: string }
 */
function viaticos_get_user_perfil( $user_id ) {
    $user_id = (int) $user_id;
    if ( $user_id <= 0 ) {
        return array( 'cargo' => '', 'area' => '' );
    }
    $cargo_terms = wp_get_object_terms( $user_id, 'viaticos_cargo', array( 'fields' => 'names' ) );
    $area_terms  = wp_get_object_terms( $user_id, 'viaticos_area',  array( 'fields' => 'names' ) );
    return array(
        'cargo' => ! is_wp_error( $cargo_terms ) && ! empty( $cargo_terms ) ? (string) $cargo_terms[0] : '',
        'area'  => ! is_wp_error( $area_terms )  && ! empty( $area_terms )  ? (string) $area_terms[0]  : '',
    );
}

function viaticos_calcular_total_rendido_solicitud( $solicitud_id ) {
    $gastos = viaticos_obtener_gastos_solicitud( $solicitud_id );
    $total  = 0;

    foreach ( $gastos as $gasto ) {
        $total += (float) $gasto['importe'];
    }

    return $total;
}

function viaticos_get_historial_meta_key() {
    return 'viaticos_historial_solicitud';
}

function viaticos_get_eventos_historial_validos() {
    return array(
        'solicitud_creada',
        'solicitud_aprobada',
        'solicitud_observada',
        'solicitud_rechazada',
        'rendicion_iniciada',
        'rendicion_finalizada',
        'rendicion_aprobada',
        'rendicion_observada',
        'rendicion_rechazada',
    );
}

function registrarEventoSolicitud( $solicitud_id, $evento, $usuario_id = 0 ) {
    $solicitud_id = absint( $solicitud_id );
    $evento       = sanitize_key( $evento );
    $usuario_id   = absint( $usuario_id ?: get_current_user_id() );

    if ( ! $solicitud_id || 'solicitud_viatico' !== get_post_type( $solicitud_id ) ) {
        return false;
    }

    if ( ! in_array( $evento, viaticos_get_eventos_historial_validos(), true ) ) {
        return false;
    }

    $historial = get_post_meta( $solicitud_id, viaticos_get_historial_meta_key(), true );
    $historial = is_array( $historial ) ? $historial : array();
    $ultimo    = ! empty( $historial ) ? end( $historial ) : null;

    if (
        is_array( $ultimo )
        && ( $ultimo['evento'] ?? '' ) === $evento
        && absint( $ultimo['usuario_id'] ?? 0 ) === $usuario_id
    ) {
        return false;
    }

    $historial[] = array(
        'evento'     => $evento,
        'fecha'      => current_time( 'timestamp' ),
        'usuario_id' => $usuario_id,
    );

    return update_post_meta( $solicitud_id, viaticos_get_historial_meta_key(), $historial );
}

function viaticos_obtener_historial_solicitud( $solicitud_id ) {
    $historial = get_post_meta( absint( $solicitud_id ), viaticos_get_historial_meta_key(), true );
    $historial = is_array( $historial ) ? $historial : array();
    $eventos   = viaticos_get_eventos_historial_validos();
    $data      = array();

    foreach ( $historial as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }

        $evento = sanitize_key( $item['evento'] ?? '' );

        if ( ! in_array( $evento, $eventos, true ) ) {
            continue;
        }

        $data[] = array(
            'evento'     => $evento,
            'fecha'      => absint( $item['fecha'] ?? 0 ),
            'usuario_id' => absint( $item['usuario_id'] ?? 0 ),
        );
    }

    usort(
        $data,
        static function( $a, $b ) {
            return (int) $a['fecha'] <=> (int) $b['fecha'];
        }
    );

    return $data;
}

function viaticos_preparar_historial_solicitud( $solicitud_id ) {
    $historial = viaticos_obtener_historial_solicitud( $solicitud_id );
    $usuarios  = array();
    $data      = array();

    foreach ( $historial as $item ) {
        $usuario_id = absint( $item['usuario_id'] );

        if ( $usuario_id && ! array_key_exists( $usuario_id, $usuarios ) ) {
            $usuarios[ $usuario_id ] = get_userdata( $usuario_id );
        }

        $usuario = $usuario_id ? $usuarios[ $usuario_id ] : null;

        $data[] = array(
            'evento'         => $item['evento'],
            'fecha'          => $item['fecha'],
            'usuario_id'     => $usuario_id,
            'usuario_nombre' => $usuario ? $usuario->display_name : '',
        );
    }

    return $data;
}

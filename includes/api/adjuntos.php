<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Valida que el usuario actual puede operar sobre el gasto.
 * Retorna el objeto WP_Post del gasto o WP_REST_Response con error.
 */
function viaticos_check_acceso_gasto( $id_gasto ) {
    $gasto = get_post( absint( $id_gasto ) );
    if ( ! $gasto || 'gasto_rendicion' !== $gasto->post_type ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Gasto no encontrado.' ), 404 );
    }
    $is_owner = (int) $gasto->post_author === get_current_user_id();
    $is_admin = current_user_can( 'administrator' ) || current_user_can( 'admin_viaticos' ) || current_user_can( 'edit_others_posts' );
    if ( ! $is_owner && ! $is_admin ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Sin permisos para este gasto.' ), 403 );
    }
    return $gasto;
}

/**
 * GET /viaticos/v1/gasto-adjuntos/{id_gasto}
 */
function viaticos_callback_listar_adjuntos( WP_REST_Request $request ) {
    $id_gasto = absint( $request->get_param( 'id_gasto' ) );
    $result   = viaticos_check_acceso_gasto( $id_gasto );
    if ( $result instanceof WP_REST_Response ) return $result;

    $ids  = get_post_meta( $id_gasto, 'adjuntos_ids', true );
    $ids  = is_array( $ids ) ? array_filter( array_map( 'absint', $ids ) ) : array();
    $data = array();

    foreach ( $ids as $att_id ) {
        $url = wp_get_attachment_url( $att_id );
        if ( ! $url ) continue;
        $data[] = array(
            'id'   => $att_id,
            'url'  => $url,
            'name' => wp_basename( get_attached_file( $att_id ) ),
            'mime' => (string) get_post_mime_type( $att_id ),
        );
    }

    return new WP_REST_Response( array( 'success' => true, 'adjuntos' => $data ), 200 );
}

/**
 * POST /viaticos/v1/gasto-adjunto
 * Tipos permitidos: pdf, jpg, jpeg, png.
 */
function viaticos_callback_subir_adjunto( WP_REST_Request $request ) {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }

    $id_gasto = absint( isset( $_POST['id_gasto'] ) ? $_POST['id_gasto'] : 0 );
    $result   = viaticos_check_acceso_gasto( $id_gasto );
    if ( $result instanceof WP_REST_Response ) return $result;
    $gasto = $result;

    $id_solicitud = (int) get_field( 'id_solicitud_padre', $id_gasto );
    if ( $id_solicitud && viaticos_es_rendicion_finalizada( $id_solicitud ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'La rendición ya fue finalizada; no se pueden agregar adjuntos.' ), 409 );
    }

    if ( empty( $_FILES['archivo'] ) || empty( $_FILES['archivo']['name'] ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'No se recibió ningún archivo.' ), 400 );
    }

    $allowed = array(
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    );

    $uploaded = wp_handle_upload( $_FILES['archivo'], array(
        'test_form' => false,
        'mimes'     => $allowed,
    ) );

    if ( isset( $uploaded['error'] ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => $uploaded['error'] ), 400 );
    }

    $att_id = wp_insert_attachment( array(
        'post_mime_type' => $uploaded['type'],
        'post_title'     => sanitize_file_name( pathinfo( $uploaded['file'], PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_parent'    => $id_gasto,
    ), $uploaded['file'], $id_gasto );

    if ( is_wp_error( $att_id ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => $att_id->get_error_message() ), 500 );
    }

    wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $uploaded['file'] ) );

    $ids   = get_post_meta( $id_gasto, 'adjuntos_ids', true );
    $ids   = is_array( $ids ) ? $ids : array();
    $ids[] = $att_id;
    update_post_meta( $id_gasto, 'adjuntos_ids', array_values( $ids ) );

    return new WP_REST_Response( array(
        'success' => true,
        'message' => 'Archivo subido correctamente.',
        'adjunto' => array(
            'id'   => $att_id,
            'url'  => wp_get_attachment_url( $att_id ),
            'name' => wp_basename( $uploaded['file'] ),
            'mime' => $uploaded['type'],
        ),
    ), 201 );
}

/**
 * DELETE /viaticos/v1/gasto-adjunto/{id_adjunto}
 */
function viaticos_callback_eliminar_adjunto( WP_REST_Request $request ) {
    $id_adjunto = absint( $request->get_param( 'id_adjunto' ) );
    $attachment = get_post( $id_adjunto );

    if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Adjunto no encontrado.' ), 404 );
    }

    $id_gasto = (int) $attachment->post_parent;
    $result   = viaticos_check_acceso_gasto( $id_gasto );
    if ( $result instanceof WP_REST_Response ) return $result;

    $id_solicitud = (int) get_field( 'id_solicitud_padre', $id_gasto );
    if ( $id_solicitud && viaticos_es_rendicion_finalizada( $id_solicitud ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'La rendición ya fue finalizada.' ), 409 );
    }

    $ids = get_post_meta( $id_gasto, 'adjuntos_ids', true );
    if ( is_array( $ids ) ) {
        $ids = array_values( array_filter( $ids, static function( $id ) use ( $id_adjunto ) {
            return (int) $id !== $id_adjunto;
        } ) );
        update_post_meta( $id_gasto, 'adjuntos_ids', $ids );
    }

    wp_delete_attachment( $id_adjunto, true );

    return new WP_REST_Response( array( 'success' => true, 'message' => 'Adjunto eliminado correctamente.' ), 200 );
}

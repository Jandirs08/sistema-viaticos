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

    $user_id  = get_current_user_id();
    $is_admin = current_user_can( 'manage_viaticos' );

    if ( $is_admin ) {
        return $gasto;
    }

    $is_owner_gasto = (int) $gasto->post_author === $user_id;

    $id_solicitud      = (int) get_field( ACF_GAS_SOLICITUD, $gasto->ID );
    $solicitud         = $id_solicitud ? get_post( $id_solicitud ) : null;
    $is_owner_solicitud = $solicitud && (int) $solicitud->post_author === $user_id;

    if ( ! $is_owner_gasto && ! $is_owner_solicitud ) {
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

    $ids = array_filter( array_map( 'absint', get_post_meta( $id_gasto, 'adjunto_id', false ) ) );
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

    $id_gasto = absint( $request->get_param( 'id_gasto' ) );
    $result   = viaticos_check_acceso_gasto( $id_gasto );
    if ( $result instanceof WP_REST_Response ) return $result;
    $gasto = $result;

    $id_solicitud = (int) get_field( ACF_GAS_SOLICITUD, $id_gasto );
    if ( $id_solicitud && viaticos_es_rendicion_finalizada( $id_solicitud ) && 'observada' !== viaticos_get_estado_rendicion( $id_solicitud ) ) {
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

    $max_bytes  = 5 * 1024 * 1024;
    $max_count  = 10;
    $size_bytes = isset( $_FILES['archivo']['size'] ) ? (int) $_FILES['archivo']['size'] : 0;

    if ( $size_bytes <= 0 || $size_bytes > $max_bytes ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'El archivo supera el tamaño máximo permitido (5 MB).' ), 413 );
    }

    $existentes = array_filter( array_map( 'absint', get_post_meta( $id_gasto, 'adjunto_id', false ) ) );
    if ( count( $existentes ) >= $max_count ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Se alcanzó el máximo de 10 adjuntos por gasto.' ), 409 );
    }

    // HEIC/HEIF desde iPhone: convertir a JPEG antes de subir para que sea visible en WP Media.
    $ext_in = strtolower( pathinfo( (string) $_FILES['archivo']['name'], PATHINFO_EXTENSION ) );
    if ( in_array( $ext_in, array( 'heic', 'heif' ), true ) ) {
        $heic_result = viaticos_adjunto_convertir_heic( $_FILES['archivo'] );
        if ( ! $heic_result['ok'] ) {
            return new WP_REST_Response( array( 'success' => false, 'message' => $heic_result['error'] ), 422 );
        }
    }

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

    add_post_meta( $id_gasto, 'adjunto_id', $att_id );

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

    $id_solicitud = (int) get_field( ACF_GAS_SOLICITUD, $id_gasto );
    if ( $id_solicitud && viaticos_es_rendicion_finalizada( $id_solicitud ) && 'observada' !== viaticos_get_estado_rendicion( $id_solicitud ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'La rendición ya fue finalizada.' ), 409 );
    }

    delete_post_meta( $id_gasto, 'adjunto_id', $id_adjunto );

    wp_delete_attachment( $id_adjunto, true );

    return new WP_REST_Response( array( 'success' => true, 'message' => 'Adjunto eliminado correctamente.' ), 200 );
}

/**
 * Convierte un HEIC/HEIF (referencia $_FILES) a JPEG en el mismo tmp_name.
 * Reescribe name/type/size para que wp_handle_upload acepte el JPEG resultante.
 * Devuelve ['ok'=>true] o ['ok'=>false, 'error'=>...].
 */
function viaticos_adjunto_convertir_heic( &$file ) {
    if ( ! class_exists( 'Imagick' ) ) {
        return array( 'ok' => false, 'error' => 'El servidor no puede procesar HEIC. Sube la foto como JPG.' );
    }
    try {
        $im = new Imagick( $file['tmp_name'] );
        $im->setImageFormat( 'jpeg' );
        $im->setImageCompressionQuality( 85 );
        if ( $im->getImageWidth() > 2000 ) {
            $im->thumbnailImage( 2000, 0 );
        }
        $im->writeImage( $file['tmp_name'] );
        $im->clear();
        $im->destroy();

        $base_name        = pathinfo( (string) $file['name'], PATHINFO_FILENAME );
        $file['name']     = $base_name . '.jpg';
        $file['type']     = 'image/jpeg';
        $file['size']     = (int) filesize( $file['tmp_name'] );
        return array( 'ok' => true );
    } catch ( Exception $e ) {
        return array( 'ok' => false, 'error' => 'No se pudo procesar la imagen HEIC: ' . $e->getMessage() );
    }
}

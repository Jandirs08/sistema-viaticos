<?php
/**
 * REST endpoint OCR.
 *  POST /viaticos/v1/ocr-extract
 *  multipart/form-data:
 *    - archivo: file (PDF, JPG, PNG)
 *    - tipo:    string opcional ('documento' | 'vale_caja')
 *
 *  Permission: cualquier usuario logueado del SPA. La validación de tamaño,
 *  tipo y cap mensual ocurre acá.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function viaticos_callback_ocr_extract( WP_REST_Request $request ) {
    if ( ! viaticos_ocr_is_enabled() ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'OCR no está habilitado.' ), 503 );
    }

    if ( empty( $_FILES['archivo'] ) || empty( $_FILES['archivo']['tmp_name'] ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'No se recibió ningún archivo.' ), 400 );
    }

    $file = $_FILES['archivo'];

    if ( ! empty( $file['error'] ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Error al recibir el archivo.' ), 400 );
    }

    $size = (int) ( $file['size'] ?? 0 );
    if ( $size <= 0 || $size > VIATICOS_OCR_MAX_FILE_BYTES ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'El archivo supera el tamaño máximo permitido (10 MB).' ), 413 );
    }

    $check = wp_check_filetype( $file['name'] );
    $mime  = $check['type'] ?? '';
    $allowed = array( 'application/pdf', 'image/jpeg', 'image/png' );
    if ( ! in_array( $mime, $allowed, true ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Tipo de archivo no permitido. Usa PDF, JPG o PNG.' ), 415 );
    }

    $tipo = sanitize_key( (string) $request->get_param( 'tipo' ) );
    if ( ! in_array( $tipo, array( 'documento', 'vale_caja' ), true ) ) {
        $tipo = 'documento';
    }

    $cap = viaticos_ocr_check_cap();
    if ( ! $cap['allowed'] ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Se alcanzó el límite mensual de OCR.' ), 429 );
    }

    $result   = viaticos_ocr_extract_file( $file['tmp_name'], $mime, $tipo );
    $settings = viaticos_ocr_get_settings();

    $log_entry = array(
        'provider'    => $settings['provider'],
        'model'       => $settings['model'],
        'file_name'   => sanitize_file_name( (string) $file['name'] ),
        'file_size'   => $size,
        'duration_ms' => (int) ( $result['duration_ms'] ?? 0 ),
    );

    if ( ! empty( $result['ok'] ) ) {
        $usage = $result['usage'] ?? array();
        $log_entry['status']     = 'success';
        $log_entry['tokens_in']  = (int) ( $usage['tokens_in'] ?? 0 );
        $log_entry['tokens_out'] = (int) ( $usage['tokens_out'] ?? 0 );
        $log_entry['cost_usd']   = viaticos_ocr_estimate_cost( $settings['model'], $log_entry['tokens_in'], $log_entry['tokens_out'] );
        viaticos_ocr_log_insert( $log_entry );

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $result['data'],
            'usage'   => $log_entry,
        ), 200 );
    }

    $log_entry['status']    = $result['status'] ?? 'error';
    $log_entry['error_msg'] = isset( $result['error'] ) ? substr( (string) $result['error'], 0, 500 ) : '';
    viaticos_ocr_log_insert( $log_entry );

    $http = 'cap_reached' === $log_entry['status'] ? 429
        : ( 'disabled' === $log_entry['status'] ? 503
        : ( 'no_token' === $log_entry['status'] ? 503
        : ( 'provider_error' === $log_entry['status'] ? 502 : 500 ) ) );

    return new WP_REST_Response( array(
        'success' => false,
        'message' => $log_entry['error_msg'] ?: 'Error al procesar el OCR.',
        'status'  => $log_entry['status'],
    ), $http );
}

/**
 * Estimación de costo USD por llamada (input+output tokens).
 * Fuente: pricing público OpenAI 2025-Q1. Tabla acotada a los modelos que
 * exponemos hoy; resto cae a un default conservador.
 */
function viaticos_ocr_estimate_cost( $model, $tokens_in, $tokens_out ) {
    $pricing = array(
        'gpt-4o-mini' => array( 'in' => 0.15, 'out' => 0.60 ),
        'gpt-4o'      => array( 'in' => 2.50, 'out' => 10.00 ),
    );
    $p = $pricing[ $model ] ?? array( 'in' => 1.0, 'out' => 4.0 );
    return round(
        ( ( $tokens_in / 1_000_000 ) * $p['in'] ) + ( ( $tokens_out / 1_000_000 ) * $p['out'] ),
        5
    );
}

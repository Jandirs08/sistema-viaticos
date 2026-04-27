<?php
/**
 * OCR extractor: punto único que recibe un archivo, llama al proveedor
 * configurado y devuelve un payload normalizado para el frontend.
 *
 * Esquema de salida ('data'):
 *   ruc                  string|null
 *   razon_social         string|null
 *   nro_comprobante      string|null
 *   fecha_emision        string|null  (YYYY-MM-DD)
 *   importe_comprobante  float|null
 *   descripcion_concepto string|null
 *   confianza            float (0..1)
 *   notas                string|null
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_OCR_TIMEOUT_SEC = 25;

/**
 * @param string $file_path  Path absoluto al archivo en disco.
 * @param string $mime       MIME (application/pdf, image/jpeg, image/png).
 * @param string $tipo_gasto 'documento' | 'vale_caja' (sólo para hint del prompt).
 * @return array{ ok:bool, status:string, data?:array, error?:string, usage?:array, duration_ms?:int }
 */
function viaticos_ocr_extract_file( $file_path, $mime, $tipo_gasto = 'documento' ) {
    $started_at = microtime( true );

    if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
        return array( 'ok' => false, 'status' => 'error', 'error' => 'Archivo no legible.' );
    }

    if ( ! viaticos_ocr_is_enabled() ) {
        return array( 'ok' => false, 'status' => 'disabled', 'error' => 'OCR deshabilitado.' );
    }

    $cap = viaticos_ocr_check_cap();
    if ( ! $cap['allowed'] ) {
        return array( 'ok' => false, 'status' => 'cap_reached', 'error' => 'Límite mensual de OCR alcanzado.' );
    }

    $token = viaticos_ocr_get_token();
    if ( '' === $token ) {
        return array( 'ok' => false, 'status' => 'no_token', 'error' => 'No hay API key configurada.' );
    }

    $settings = viaticos_ocr_get_settings();
    $provider = $settings['provider'];
    $model    = $settings['model'];

    if ( 'openai' !== $provider ) {
        return array(
            'ok'     => false,
            'status' => 'unsupported_provider',
            'error'  => sprintf( 'Provider "%s" no implementado todavía. Usa "openai".', $provider ),
        );
    }

    $bytes = file_get_contents( $file_path );
    if ( false === $bytes ) {
        return array( 'ok' => false, 'status' => 'error', 'error' => 'No se pudo leer el archivo.' );
    }

    $result = viaticos_ocr_call_openai( $bytes, $mime, $tipo_gasto, $model, $token );
    $result['duration_ms'] = (int) round( ( microtime( true ) - $started_at ) * 1000 );
    return $result;
}

/**
 * Llama a OpenAI Chat Completions con la imagen/PDF en base64. Forza JSON.
 */
function viaticos_ocr_call_openai( $bytes, $mime, $tipo_gasto, $model, $token ) {
    $b64 = base64_encode( $bytes );

    $is_pdf = ( 'application/pdf' === $mime );

    if ( $is_pdf ) {
        $content_part = array(
            'type'      => 'file',
            'file'      => array(
                'filename'  => 'documento.pdf',
                'file_data' => 'data:application/pdf;base64,' . $b64,
            ),
        );
    } else {
        $content_part = array(
            'type'      => 'image_url',
            'image_url' => array(
                'url'    => 'data:' . $mime . ';base64,' . $b64,
                'detail' => 'high',
            ),
        );
    }

    $body = array(
        'model'           => $model,
        'response_format' => array( 'type' => 'json_object' ),
        'temperature'     => 0,
        'max_tokens'      => 800,
        'messages'        => array(
            array(
                'role'    => 'system',
                'content' => viaticos_ocr_build_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => array(
                    array(
                        'type' => 'text',
                        'text' => viaticos_ocr_build_user_prompt( $tipo_gasto ),
                    ),
                    $content_part,
                ),
            ),
        ),
    );

    $resp = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => VIATICOS_OCR_TIMEOUT_SEC,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $body ),
    ) );

    if ( is_wp_error( $resp ) ) {
        return array( 'ok' => false, 'status' => 'network_error', 'error' => $resp->get_error_message() );
    }

    $code = (int) wp_remote_retrieve_response_code( $resp );
    $raw  = wp_remote_retrieve_body( $resp );
    $json = json_decode( $raw, true );

    if ( $code < 200 || $code >= 300 ) {
        $err = is_array( $json ) && isset( $json['error']['message'] )
            ? (string) $json['error']['message']
            : sprintf( 'HTTP %d', $code );
        return array( 'ok' => false, 'status' => 'provider_error', 'error' => $err );
    }

    if ( ! is_array( $json ) || empty( $json['choices'][0]['message']['content'] ) ) {
        return array( 'ok' => false, 'status' => 'parse_error', 'error' => 'Respuesta vacía del proveedor.' );
    }

    $content = (string) $json['choices'][0]['message']['content'];
    $parsed  = json_decode( $content, true );

    if ( ! is_array( $parsed ) ) {
        return array( 'ok' => false, 'status' => 'parse_error', 'error' => 'El proveedor no devolvió JSON válido.' );
    }

    $data  = viaticos_ocr_normalize_extraction( $parsed );
    $usage = isset( $json['usage'] ) && is_array( $json['usage'] ) ? $json['usage'] : array();

    return array(
        'ok'     => true,
        'status' => 'success',
        'data'   => $data,
        'usage'  => array(
            'tokens_in'  => (int) ( $usage['prompt_tokens'] ?? 0 ),
            'tokens_out' => (int) ( $usage['completion_tokens'] ?? 0 ),
        ),
    );
}

function viaticos_ocr_build_system_prompt() {
    return implode( "\n", array(
        'Eres un extractor de datos para facturas, boletas y recibos por honorarios peruanos (SUNAT).',
        'Devuelves SIEMPRE un JSON válido con exactamente estas claves:',
        '  - ruc: string de 11 dígitos del proveedor o null si no se encuentra.',
        '  - razon_social: nombre legal del emisor o null.',
        '  - nro_comprobante: serie y correlativo (formato típico "F001-12345" o "B001-12345") o null.',
        '  - fecha_emision: en formato YYYY-MM-DD o null.',
        '  - importe_comprobante: número decimal con el TOTAL a pagar (no subtotal, no IGV) o null.',
        '  - descripcion_concepto: descripción libre del bien o servicio (máximo 200 caracteres) o null.',
        '  - confianza: número entre 0 y 1 con tu confianza global en la extracción.',
        '  - notas: comentarios opcionales si algo es ambiguo, o null.',
        'Reglas:',
        '  - No inventes datos. Si un campo no está claro, devuélvelo como null.',
        '  - El importe_comprobante es el monto FINAL pagado, no parciales.',
        '  - El RUC siempre tiene 11 dígitos numéricos.',
        '  - No incluyas comentarios, markdown ni texto fuera del objeto JSON.',
    ) );
}

function viaticos_ocr_build_user_prompt( $tipo_gasto ) {
    $hint = '';
    if ( 'vale_caja' === $tipo_gasto ) {
        $hint = ' Este documento corresponde a un vale de caja chica.';
    }
    return 'Extrae los datos del siguiente comprobante peruano y devuelve únicamente el objeto JSON descrito.' . $hint;
}

function viaticos_ocr_normalize_extraction( array $raw ) {
    $str = static function ( $v ) {
        if ( null === $v || '' === $v ) return null;
        return is_string( $v ) ? trim( $v ) : (string) $v;
    };
    $num = static function ( $v ) {
        if ( null === $v || '' === $v ) return null;
        if ( is_numeric( $v ) ) return (float) $v;
        // Limpia "S/. 1,234.56" o "1.234,56".
        $clean = preg_replace( '/[^\d,\.\-]/', '', (string) $v );
        if ( '' === $clean ) return null;
        // Si tiene coma como decimal (ej "1234,56"), conviértelo.
        if ( substr_count( $clean, ',' ) === 1 && substr_count( $clean, '.' ) === 0 ) {
            $clean = str_replace( ',', '.', $clean );
        } else {
            $clean = str_replace( ',', '', $clean );
        }
        return is_numeric( $clean ) ? (float) $clean : null;
    };

    $ruc = $str( $raw['ruc'] ?? null );
    if ( $ruc !== null ) {
        $digits = preg_replace( '/\D/', '', $ruc );
        $ruc = ( strlen( $digits ) === 11 ) ? $digits : null;
    }

    $fecha = $str( $raw['fecha_emision'] ?? null );
    if ( $fecha !== null ) {
        $d = DateTime::createFromFormat( 'Y-m-d', $fecha );
        if ( ! $d || $d->format( 'Y-m-d' ) !== $fecha ) {
            // Intentar otros formatos comunes.
            foreach ( array( 'd/m/Y', 'd-m-Y', 'Y/m/d' ) as $fmt ) {
                $d = DateTime::createFromFormat( $fmt, $fecha );
                if ( $d && $d->format( $fmt ) === $fecha ) {
                    $fecha = $d->format( 'Y-m-d' );
                    break;
                }
                $d = null;
            }
            if ( ! $d ) $fecha = null;
        }
    }

    $confianza = $raw['confianza'] ?? null;
    if ( $confianza !== null && is_numeric( $confianza ) ) {
        $confianza = max( 0.0, min( 1.0, (float) $confianza ) );
    } else {
        $confianza = 0.0;
    }

    $concepto = $str( $raw['descripcion_concepto'] ?? null );
    if ( $concepto !== null && strlen( $concepto ) > 200 ) {
        $concepto = substr( $concepto, 0, 200 );
    }

    return array(
        'ruc'                  => $ruc,
        'razon_social'         => $str( $raw['razon_social'] ?? null ),
        'nro_comprobante'      => $str( $raw['nro_comprobante'] ?? null ),
        'fecha_emision'        => $fecha,
        'importe_comprobante'  => $num( $raw['importe_comprobante'] ?? null ),
        'descripcion_concepto' => $concepto,
        'confianza'            => $confianza,
        'notas'                => $str( $raw['notas'] ?? null ),
    );
}

/**
 * Test ligero de credenciales: pide al modelo "ping" sin imagen.
 */
function viaticos_ocr_test_connection() {
    $token    = viaticos_ocr_get_token();
    $settings = viaticos_ocr_get_settings();
    if ( '' === $token ) {
        return array( 'ok' => false, 'error' => 'No hay API key guardada.' );
    }
    if ( 'openai' !== $settings['provider'] ) {
        return array( 'ok' => false, 'error' => 'Provider no soportado en este test.' );
    }

    $resp = wp_remote_get( 'https://api.openai.com/v1/models/' . rawurlencode( $settings['model'] ), array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
        ),
    ) );

    if ( is_wp_error( $resp ) ) {
        return array( 'ok' => false, 'error' => $resp->get_error_message() );
    }
    $code = (int) wp_remote_retrieve_response_code( $resp );
    if ( $code >= 200 && $code < 300 ) {
        return array( 'ok' => true, 'message' => sprintf( 'Modelo "%s" accesible.', $settings['model'] ) );
    }
    $raw  = wp_remote_retrieve_body( $resp );
    $json = json_decode( $raw, true );
    $err  = is_array( $json ) && isset( $json['error']['message'] ) ? $json['error']['message'] : sprintf( 'HTTP %d', $code );
    return array( 'ok' => false, 'error' => $err );
}

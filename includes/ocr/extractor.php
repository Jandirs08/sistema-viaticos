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

const VIATICOS_OCR_TIMEOUT_SEC      = 25;
const VIATICOS_OCR_PDF_DPI          = 150;
const VIATICOS_OCR_RASTER_MAX_WIDTH = 2000;
const VIATICOS_OCR_JPEG_QUALITY     = 85;
const VIATICOS_OCR_MAX_EXTRA_TEXT   = 8000; // chars del texto pág 2..N

/**
 * Normaliza el archivo de entrada: PDF y HEIC se rasterizan a JPEG.
 * Imágenes (JPG/PNG/WEBP) que excedan el ancho máximo se redimensionan
 * para reducir tokens consumidos por el provider; si Imagick no está,
 * passthrough silencioso.
 * Devuelve ['ok'=>true, 'path'=>..., 'mime'=>..., 'cleanup'=>bool]
 *      o  ['ok'=>false, 'error'=>...].
 */
function viaticos_ocr_normalize_input_file( $file_path, $mime ) {
    $passthrough_mimes = array( 'image/jpeg', 'image/png', 'image/webp' );

    if ( in_array( $mime, $passthrough_mimes, true ) ) {
        if ( class_exists( 'Imagick' ) ) {
            $resized = viaticos_ocr_resize_image_if_large( $file_path );
            if ( null !== $resized ) {
                return $resized;
            }
        }
        return array( 'ok' => true, 'path' => $file_path, 'mime' => $mime, 'cleanup' => false );
    }

    if ( ! class_exists( 'Imagick' ) ) {
        return array( 'ok' => false, 'error' => 'Conversión no disponible en el servidor (falta Imagick). Sube JPG, PNG o WEBP.' );
    }

    if ( 'application/pdf' === $mime ) {
        return viaticos_ocr_pdf_to_jpeg( $file_path );
    }
    if ( 'image/heic' === $mime || 'image/heif' === $mime ) {
        return viaticos_ocr_heic_to_jpeg( $file_path );
    }

    return array( 'ok' => false, 'error' => 'Tipo de archivo no soportado.' );
}

/**
 * Si la imagen excede el ancho máximo, la convierte a JPEG redimensionado.
 * Devuelve normalize-shape array si hizo trabajo, o null si la imagen es chica
 * (señal para que el caller haga passthrough sin tocar disk).
 */
function viaticos_ocr_resize_image_if_large( $file_path ) {
    try {
        $im = new Imagick( $file_path );
        if ( $im->getImageWidth() <= VIATICOS_OCR_RASTER_MAX_WIDTH ) {
            $im->clear();
            $im->destroy();
            return null;
        }
        viaticos_imagick_apply_jpeg( $im );
        $tmp = viaticos_imagick_write_tmp( $im, 'viaticos-ocr-img' );
        return array( 'ok' => true, 'path' => $tmp, 'mime' => 'image/jpeg', 'cleanup' => true );
    } catch ( Exception $e ) {
        return null; // fallback silencioso a passthrough
    }
}

/**
 * Aplica las settings JPEG comunes a un objeto Imagick: format jpeg,
 * compression quality, thumbnail si excede el ancho máximo. Mutates $im.
 */
function viaticos_imagick_apply_jpeg( $im ) {
    $im->setImageFormat( 'jpeg' );
    $im->setImageCompressionQuality( VIATICOS_OCR_JPEG_QUALITY );
    if ( $im->getImageWidth() > VIATICOS_OCR_RASTER_MAX_WIDTH ) {
        $im->thumbnailImage( VIATICOS_OCR_RASTER_MAX_WIDTH, 0 );
    }
}

/**
 * Escribe el Imagick a un tmp file (wp_tempnam con prefix), libera memoria.
 * Devuelve el path del nuevo archivo.
 */
function viaticos_imagick_write_tmp( $im, $prefix ) {
    $tmp = wp_tempnam( $prefix );
    $im->writeImage( $tmp );
    $im->clear();
    $im->destroy();
    return $tmp;
}

function viaticos_ocr_pdf_to_jpeg( $pdf_path ) {
    try {
        $count_im = new Imagick();
        $count_im->pingImage( $pdf_path );
        $pages = $count_im->getNumberImages();
        $count_im->clear();
        $count_im->destroy();

        $im = new Imagick();
        $im->setResolution( VIATICOS_OCR_PDF_DPI, VIATICOS_OCR_PDF_DPI );
        $im->readImage( $pdf_path . '[0]' ); // primera página solamente
        $im->setImageBackgroundColor( 'white' );
        $im = $im->flattenImages();

        viaticos_imagick_apply_jpeg( $im );
        $tmp = viaticos_imagick_write_tmp( $im, 'viaticos-ocr-pdf' );

        $extra_text = '';
        if ( $pages > 1 ) {
            $extra_text = viaticos_ocr_extract_pdf_text_from_page2( $pdf_path );
        }

        return array(
            'ok'         => true,
            'path'       => $tmp,
            'mime'       => 'image/jpeg',
            'cleanup'    => true,
            'pages'      => $pages,
            'extra_text' => $extra_text,
        );
    } catch ( Exception $e ) {
        return array( 'ok' => false, 'error' => 'No se pudo procesar el PDF: ' . $e->getMessage() );
    }
}

/**
 * Extrae texto plano de las páginas 2..N de un PDF usando Ghostscript via shell_exec.
 * Si shell_exec o gs no están disponibles, devuelve string vacío (fallback silencioso).
 */
function viaticos_ocr_extract_pdf_text_from_page2( $pdf_path ) {
    if ( ! function_exists( 'shell_exec' ) ) {
        return '';
    }
    $gs = viaticos_ocr_locate_ghostscript();
    if ( '' === $gs ) {
        return '';
    }

    $out_txt = wp_tempnam( 'viaticos-ocr-txt' );
    $has_timeout = viaticos_ocr_has_timeout_binary();
    $base = sprintf(
        '%s -dNOPAUSE -dBATCH -dQUIET -dSAFER -sDEVICE=txtwrite -dFirstPage=2 -sOutputFile=%s %s 2>&1',
        escapeshellcmd( $gs ),
        escapeshellarg( $out_txt ),
        escapeshellarg( $pdf_path )
    );
    $cmd = $has_timeout ? ( 'timeout 10s ' . $base ) : $base;
    @shell_exec( $cmd );

    $text = '';
    if ( file_exists( $out_txt ) ) {
        $raw = file_get_contents( $out_txt );
        @unlink( $out_txt );
        if ( false !== $raw ) {
            // Normaliza saltos de línea y espacios; corta si excede el límite.
            $text = preg_replace( '/[ \t]+/', ' ', (string) $raw );
            $text = preg_replace( '/\n{3,}/', "\n\n", trim( $text ) );
            if ( mb_strlen( $text, 'UTF-8' ) > VIATICOS_OCR_MAX_EXTRA_TEXT ) {
                $text = mb_substr( $text, 0, VIATICOS_OCR_MAX_EXTRA_TEXT, 'UTF-8' );
            }
        }
    }
    return $text;
}

function viaticos_ocr_locate_ghostscript() {
    static $cached = null;
    if ( null !== $cached ) {
        return $cached;
    }
    $candidates = array( 'gs', '/usr/bin/gs', '/usr/local/bin/gs' );
    foreach ( $candidates as $c ) {
        $check = @shell_exec( escapeshellcmd( $c ) . ' --version 2>&1' );
        if ( is_string( $check ) && preg_match( '/^\d+\.\d+/', trim( $check ) ) ) {
            $cached = $c;
            return $cached;
        }
    }
    $cached = '';
    return $cached;
}

function viaticos_ocr_has_timeout_binary() {
    static $cached = null;
    if ( null !== $cached ) {
        return $cached;
    }
    if ( ! function_exists( 'shell_exec' ) ) {
        $cached = false;
        return $cached;
    }
    $check  = @shell_exec( 'timeout --version 2>&1' );
    $cached = is_string( $check ) && false !== stripos( $check, 'timeout' );
    return $cached;
}

function viaticos_ocr_heic_to_jpeg( $heic_path ) {
    try {
        $im = new Imagick( $heic_path );
        viaticos_imagick_apply_jpeg( $im );
        $tmp = viaticos_imagick_write_tmp( $im, 'viaticos-ocr-heic' );
        return array( 'ok' => true, 'path' => $tmp, 'mime' => 'image/jpeg', 'cleanup' => true );
    } catch ( Exception $e ) {
        return array( 'ok' => false, 'error' => 'No se pudo procesar el HEIC: ' . $e->getMessage() );
    }
}

/**
 * @param string $file_path  Path absoluto al archivo en disco.
 * @param string $mime       MIME (application/pdf, image/jpeg, image/png, image/heic, image/heif).
 *                            PDF y HEIC se rasterizan a JPEG via Imagick antes de enviar al provider.
 * @param string $tipo_gasto 'documento' | 'vale_caja' (sólo para hint del prompt).
 * @return array{ ok:bool, status:string, data?:array, error?:string, usage?:array, duration_ms?:int }
 */
function viaticos_ocr_extract_file( $file_path, $mime, $tipo_gasto = 'documento' ) {
    $started_at = microtime( true );

    // Asume: caller (endpoint REST) ya validó is_enabled() y check_cap().
    // Estos dos guards se mantienen acá sólo para errores duros de configuración.
    if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
        return array( 'ok' => false, 'status' => 'error', 'error' => 'Archivo no legible.' );
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

    $normalized = viaticos_ocr_normalize_input_file( $file_path, $mime );
    if ( empty( $normalized['ok'] ) ) {
        return array( 'ok' => false, 'status' => 'normalize_error', 'error' => $normalized['error'] ?? 'No se pudo normalizar el archivo.' );
    }

    $bytes = file_get_contents( $normalized['path'] );
    if ( false === $bytes ) {
        if ( ! empty( $normalized['cleanup'] ) ) { @unlink( $normalized['path'] ); }
        return array( 'ok' => false, 'status' => 'error', 'error' => 'No se pudo leer el archivo.' );
    }

    $extra_text      = isset( $normalized['extra_text'] ) ? (string) $normalized['extra_text'] : '';
    $pages           = isset( $normalized['pages'] ) ? (int) $normalized['pages'] : 1;
    $extra_text_used = '' !== trim( $extra_text );

    $result = viaticos_ocr_call_openai( $bytes, $normalized['mime'], $tipo_gasto, $model, $token, $extra_text, $pages );

    if ( ! empty( $normalized['cleanup'] ) ) { @unlink( $normalized['path'] ); }

    $result['duration_ms']     = (int) round( ( microtime( true ) - $started_at ) * 1000 );
    $result['pdf_pages']       = $pages;
    $result['extra_text_used'] = $extra_text_used ? 1 : 0;
    return $result;
}

/**
 * Llama a OpenAI Chat Completions con la imagen/PDF en base64. Forza JSON.
 */
function viaticos_ocr_call_openai( $bytes, $mime, $tipo_gasto, $model, $token, $extra_text = '', $pages = 1 ) {
    $b64 = base64_encode( $bytes );

    // Tras normalize_input_file, $mime siempre es image/jpeg o image/png.
    $content_part = array(
        'type'      => 'image_url',
        'image_url' => array(
            'url'    => 'data:' . $mime . ';base64,' . $b64,
            'detail' => 'high',
        ),
    );

    $user_text_blocks = array(
        array( 'type' => 'text', 'text' => viaticos_ocr_build_user_prompt( $tipo_gasto, $pages ) ),
    );
    if ( '' !== trim( $extra_text ) ) {
        $user_text_blocks[] = array(
            'type' => 'text',
            'text' => "Texto plano extraído de las páginas 2 en adelante del PDF (puede contener detalle adicional, items, totales, observaciones). Úsalo SOLO si la imagen de la primera página no tiene los datos pedidos:\n---\n" . $extra_text . "\n---",
        );
    }

    $user_content = array_merge( $user_text_blocks, array( $content_part ) );

    $body = array(
        'model'           => $model,
        'response_format' => array( 'type' => 'json_object' ),
        'temperature'     => 0,
        'max_tokens'      => 1200,
        'messages'        => array(
            array(
                'role'    => 'system',
                'content' => viaticos_ocr_build_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => $user_content,
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

function viaticos_ocr_build_user_prompt( $tipo_gasto, $pages = 1 ) {
    $hint = '';
    if ( 'vale_caja' === $tipo_gasto ) {
        $hint = ' Este documento corresponde a un vale de caja chica.';
    }
    $pages_note = '';
    if ( (int) $pages > 1 ) {
        $pages_note = sprintf( ' El PDF tiene %d páginas; recibes la imagen de la página 1 y, en un bloque aparte, el texto plano de las páginas siguientes.', (int) $pages );
    }
    return 'Extrae los datos del siguiente comprobante peruano y devuelve únicamente el objeto JSON descrito.' . $hint . $pages_note;
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
    if ( $concepto !== null && mb_strlen( $concepto, 'UTF-8' ) > 200 ) {
        $concepto = mb_substr( $concepto, 0, 200, 'UTF-8' );
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
 * Test real de credenciales y soporte de visión: manda una imagen 1x1 en blanco
 * al modelo configurado y espera un JSON válido. Si el modelo no soporta visión,
 * el provider devuelve error 400/404 y lo reportamos. Costo aprox: <$0.0002.
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

    // 1x1 JPEG blanco precomputado (en base64, 161 bytes).
    $b64 = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AVN//2Q==';

    $body = array(
        'model'           => $settings['model'],
        'response_format' => array( 'type' => 'json_object' ),
        'temperature'     => 0,
        'max_tokens'      => 20,
        'messages'        => array(
            array(
                'role'    => 'user',
                'content' => array(
                    array( 'type' => 'text', 'text' => 'Devuelve {"ok":true}.' ),
                    array(
                        'type'      => 'image_url',
                        'image_url' => array(
                            'url'    => 'data:image/jpeg;base64,' . $b64,
                            'detail' => 'low',
                        ),
                    ),
                ),
            ),
        ),
    );

    $resp = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $body ),
    ) );

    if ( is_wp_error( $resp ) ) {
        return array( 'ok' => false, 'error' => $resp->get_error_message() );
    }

    $code = (int) wp_remote_retrieve_response_code( $resp );
    $raw  = wp_remote_retrieve_body( $resp );
    $json = json_decode( $raw, true );

    if ( $code >= 200 && $code < 300 ) {
        return array( 'ok' => true, 'message' => sprintf( 'Modelo "%s" responde y soporta visión.', $settings['model'] ) );
    }

    $err = is_array( $json ) && isset( $json['error']['message'] )
        ? (string) $json['error']['message']
        : sprintf( 'HTTP %d', $code );

    // Mensaje específico si el modelo no soporta visión.
    if ( false !== stripos( $err, 'image' ) || false !== stripos( $err, 'vision' ) ) {
        $err = sprintf( 'El modelo "%s" no soporta imágenes. Usa gpt-4o-mini o gpt-4o.', $settings['model'] );
    }
    return array( 'ok' => false, 'error' => $err );
}

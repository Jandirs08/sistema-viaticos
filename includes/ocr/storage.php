<?php
/**
 * OCR storage: cifrado del API key + tabla de logs.
 * El API key se guarda en wp_options cifrado con AES-256-CBC usando AUTH_KEY
 * de wp-config como deriving key. Si AUTH_KEY rota, el token se invalida y
 * hay que re-guardarlo (comportamiento seguro por defecto).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_OCR_OPTION_KEY      = 'viaticos_ocr_settings';
const VIATICOS_OCR_OPTION_TOKEN    = 'viaticos_ocr_api_token';
const VIATICOS_OCR_LOG_TABLE       = 'viaticos_ocr_log';
const VIATICOS_OCR_DB_VERSION      = '1.1.0';
const VIATICOS_OCR_DB_VERSION_OPT  = 'viaticos_ocr_db_version';

function viaticos_ocr_derive_key() {
    if ( ! defined( 'AUTH_KEY' ) || '' === AUTH_KEY ) {
        return false;
    }
    return hash( 'sha256', AUTH_KEY, true );
}

function viaticos_ocr_encrypt( $plain ) {
    $plain = (string) $plain;
    if ( '' === $plain ) {
        return '';
    }
    $key = viaticos_ocr_derive_key();
    if ( false === $key ) {
        return '';
    }
    $iv     = openssl_random_pseudo_bytes( 16 );
    $cipher = openssl_encrypt( $plain, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
    if ( false === $cipher ) {
        return '';
    }
    return base64_encode( $iv . $cipher );
}

function viaticos_ocr_decrypt( $payload ) {
    $payload = (string) $payload;
    if ( '' === $payload ) {
        return '';
    }
    $key = viaticos_ocr_derive_key();
    if ( false === $key ) {
        return '';
    }
    $raw = base64_decode( $payload, true );
    if ( false === $raw || strlen( $raw ) < 17 ) {
        return '';
    }
    $iv     = substr( $raw, 0, 16 );
    $cipher = substr( $raw, 16 );
    $plain  = openssl_decrypt( $cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
    return false === $plain ? '' : $plain;
}

function viaticos_ocr_save_token( $plain ) {
    $plain = trim( (string) $plain );
    if ( '' === $plain ) {
        delete_option( VIATICOS_OCR_OPTION_TOKEN );
        return true;
    }
    $payload = viaticos_ocr_encrypt( $plain );
    if ( '' === $payload ) {
        return false;
    }
    return update_option( VIATICOS_OCR_OPTION_TOKEN, $payload, false );
}

function viaticos_ocr_get_token() {
    $payload = get_option( VIATICOS_OCR_OPTION_TOKEN, '' );
    return viaticos_ocr_decrypt( $payload );
}

function viaticos_ocr_token_is_set() {
    return '' !== (string) get_option( VIATICOS_OCR_OPTION_TOKEN, '' );
}

/**
 * Crea o actualiza la tabla de logs si la versión cambió.
 * Se llama en init; cheap si la versión ya coincide.
 */
function viaticos_ocr_maybe_install_table() {
    if ( get_option( VIATICOS_OCR_DB_VERSION_OPT ) === VIATICOS_OCR_DB_VERSION ) {
        return;
    }

    global $wpdb;
    $table   = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        provider VARCHAR(32) NOT NULL DEFAULT '',
        model VARCHAR(64) NOT NULL DEFAULT '',
        file_name VARCHAR(255) NOT NULL DEFAULT '',
        file_size INT UNSIGNED NOT NULL DEFAULT 0,
        status VARCHAR(16) NOT NULL DEFAULT '',
        tokens_in INT UNSIGNED NOT NULL DEFAULT 0,
        tokens_out INT UNSIGNED NOT NULL DEFAULT 0,
        cost_usd DECIMAL(10,5) NOT NULL DEFAULT 0,
        duration_ms INT UNSIGNED NOT NULL DEFAULT 0,
        pdf_pages INT UNSIGNED NOT NULL DEFAULT 0,
        extra_text_used TINYINT(1) NOT NULL DEFAULT 0,
        error_msg TEXT NULL,
        PRIMARY KEY  (id),
        KEY idx_created (created_at),
        KEY idx_user (user_id),
        KEY idx_user_created (user_id, created_at)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option( VIATICOS_OCR_DB_VERSION_OPT, VIATICOS_OCR_DB_VERSION, false );
}
add_action( 'init', 'viaticos_ocr_maybe_install_table' );

/**
 * Inserta una entrada de log. $entry acepta cualquier subset de columnas.
 */
function viaticos_ocr_log_insert( array $entry ) {
    global $wpdb;
    $table   = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;
    $defaults = array(
        'user_id'         => get_current_user_id(),
        'created_at'      => current_time( 'mysql', true ), // GMT
        'provider'        => '',
        'model'           => '',
        'file_name'       => '',
        'file_size'       => 0,
        'status'          => '',
        'tokens_in'       => 0,
        'tokens_out'      => 0,
        'cost_usd'        => 0,
        'duration_ms'     => 0,
        'pdf_pages'       => 0,
        'extra_text_used' => 0,
        'error_msg'       => null,
    );
    $row = array_merge( $defaults, $entry );
    return $wpdb->insert( $table, $row );
}

const VIATICOS_OCR_CRON_CLEANUP   = 'viaticos_ocr_cleanup_logs';
const VIATICOS_OCR_LOG_RETENTION  = 12; // meses

/**
 * Programa el cron semanal de limpieza si no está agendado.
 */
function viaticos_ocr_schedule_cleanup() {
    if ( ! wp_next_scheduled( VIATICOS_OCR_CRON_CLEANUP ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', VIATICOS_OCR_CRON_CLEANUP );
    }
}
add_action( 'init', 'viaticos_ocr_schedule_cleanup' );

/**
 * Borra logs anteriores a la ventana de retención.
 */
function viaticos_ocr_cleanup_old_logs() {
    global $wpdb;
    $table  = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;
    $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-' . VIATICOS_OCR_LOG_RETENTION . ' months' ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $cutoff ) );
}
add_action( VIATICOS_OCR_CRON_CLEANUP, 'viaticos_ocr_cleanup_old_logs' );

/**
 * Cuenta llamadas exitosas del mes en curso (UTC).
 */
function viaticos_ocr_count_calls_this_month() {
    global $wpdb;
    $table = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;
    $start = gmdate( 'Y-m-01 00:00:00' );
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s AND status = %s",
        $start,
        'success'
    ) );
}

/**
 * Agrega métricas por usuario para un rango temporal dado.
 *
 * @param string $since  Fecha mínima (UTC, formato MySQL).
 * @param string $until  Fecha máxima exclusiva (UTC). Vacío = sin límite superior.
 * @param int    $limit  Top N usuarios.
 * @return array<int, array{user_id:int, calls:int, success:int, errors:int, tokens_in:int, tokens_out:int, cost_usd:float, last_call:string}>
 */
function viaticos_ocr_get_usage_by_user( $since, $until = '', $limit = 50 ) {
    global $wpdb;
    $table = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;

    if ( '' !== $until ) {
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT user_id,
                    COUNT(*) AS calls,
                    SUM( CASE WHEN status = 'success' THEN 1 ELSE 0 END ) AS success,
                    SUM( CASE WHEN status <> 'success' THEN 1 ELSE 0 END ) AS errors,
                    SUM(tokens_in)  AS tokens_in,
                    SUM(tokens_out) AS tokens_out,
                    SUM(cost_usd)   AS cost_usd,
                    MAX(created_at) AS last_call
             FROM {$table}
             WHERE created_at >= %s AND created_at < %s
             GROUP BY user_id
             ORDER BY calls DESC
             LIMIT %d",
            $since,
            $until,
            (int) $limit
        ), ARRAY_A );
    } else {
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT user_id,
                    COUNT(*) AS calls,
                    SUM( CASE WHEN status = 'success' THEN 1 ELSE 0 END ) AS success,
                    SUM( CASE WHEN status <> 'success' THEN 1 ELSE 0 END ) AS errors,
                    SUM(tokens_in)  AS tokens_in,
                    SUM(tokens_out) AS tokens_out,
                    SUM(cost_usd)   AS cost_usd,
                    MAX(created_at) AS last_call
             FROM {$table}
             WHERE created_at >= %s
             GROUP BY user_id
             ORDER BY calls DESC
             LIMIT %d",
            $since,
            (int) $limit
        ), ARRAY_A );
    }

    if ( ! is_array( $rows ) ) {
        return array();
    }
    return array_map( function ( $r ) {
        return array(
            'user_id'    => (int) $r['user_id'],
            'calls'      => (int) $r['calls'],
            'success'    => (int) $r['success'],
            'errors'     => (int) $r['errors'],
            'tokens_in'  => (int) $r['tokens_in'],
            'tokens_out' => (int) $r['tokens_out'],
            'cost_usd'   => (float) $r['cost_usd'],
            'last_call'  => (string) $r['last_call'],
        );
    }, $rows );
}

/**
 * Cuenta llamadas exitosas del usuario en el día UTC en curso.
 */
function viaticos_ocr_count_user_calls_today( $user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . VIATICOS_OCR_LOG_TABLE;
    $start = gmdate( 'Y-m-d 00:00:00' );
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND created_at >= %s AND status = %s",
        (int) $user_id,
        $start,
        'success'
    ) );
}

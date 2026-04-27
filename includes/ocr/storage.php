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
const VIATICOS_OCR_DB_VERSION      = '1.0.0';
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
        error_msg TEXT NULL,
        PRIMARY KEY  (id),
        KEY idx_created (created_at),
        KEY idx_user (user_id)
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
        'user_id'     => get_current_user_id(),
        'created_at'  => current_time( 'mysql', true ), // GMT
        'provider'    => '',
        'model'       => '',
        'file_name'   => '',
        'file_size'   => 0,
        'status'      => '',
        'tokens_in'   => 0,
        'tokens_out'  => 0,
        'cost_usd'    => 0,
        'duration_ms' => 0,
        'error_msg'   => null,
    );
    $row = array_merge( $defaults, $entry );
    return $wpdb->insert( $table, $row );
}

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

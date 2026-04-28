<?php
/**
 * OCR helpers — settings access + capacidad mensual.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_OCR_DEFAULT_PROVIDER  = 'openai';
const VIATICOS_OCR_DEFAULT_MODEL     = 'gpt-4o-mini';
const VIATICOS_OCR_DEFAULT_CAP       = 1000;
const VIATICOS_OCR_DEFAULT_USER_CAP  = 50; // 0 = sin límite por usuario/día
const VIATICOS_OCR_MAX_FILE_BYTES    = 10 * 1024 * 1024; // 10 MB

function viaticos_ocr_get_settings() {
    $stored = get_option( VIATICOS_OCR_OPTION_KEY, array() );
    if ( ! is_array( $stored ) ) {
        $stored = array();
    }
    return wp_parse_args( $stored, array(
        'enabled'         => false,
        'provider'        => VIATICOS_OCR_DEFAULT_PROVIDER,
        'model'           => VIATICOS_OCR_DEFAULT_MODEL,
        'monthly_cap'     => VIATICOS_OCR_DEFAULT_CAP,
        'daily_user_cap'  => VIATICOS_OCR_DEFAULT_USER_CAP,
    ) );
}

function viaticos_ocr_save_settings( array $settings ) {
    $provider  = sanitize_key( $settings['provider'] ?? VIATICOS_OCR_DEFAULT_PROVIDER );
    $supported = array( 'openai' );
    if ( ! in_array( $provider, $supported, true ) ) {
        $provider = VIATICOS_OCR_DEFAULT_PROVIDER;
    }
    $clean = array(
        'enabled'        => ! empty( $settings['enabled'] ),
        'provider'       => $provider,
        'model'          => sanitize_text_field( $settings['model'] ?? VIATICOS_OCR_DEFAULT_MODEL ),
        'monthly_cap'    => max( 0, absint( $settings['monthly_cap'] ?? VIATICOS_OCR_DEFAULT_CAP ) ),
        'daily_user_cap' => max( 0, absint( $settings['daily_user_cap'] ?? VIATICOS_OCR_DEFAULT_USER_CAP ) ),
    );
    return update_option( VIATICOS_OCR_OPTION_KEY, $clean, false );
}

function viaticos_ocr_is_enabled() {
    $s = viaticos_ocr_get_settings();
    return ! empty( $s['enabled'] ) && viaticos_ocr_token_is_set();
}

/**
 * @return array{ allowed:bool, used:int, cap:int, remaining:int }
 */
function viaticos_ocr_check_user_cap( $user_id ) {
    $s   = viaticos_ocr_get_settings();
    $cap = (int) $s['daily_user_cap'];
    if ( $cap <= 0 || $user_id <= 0 ) {
        return array( 'allowed' => true, 'used' => 0, 'cap' => 0, 'remaining' => PHP_INT_MAX );
    }
    $used = viaticos_ocr_count_user_calls_today( (int) $user_id );
    return array(
        'allowed'   => $used < $cap,
        'used'      => $used,
        'cap'       => $cap,
        'remaining' => max( 0, $cap - $used ),
    );
}

/**
 * @return array{ allowed:bool, used:int, cap:int, remaining:int }
 */
function viaticos_ocr_check_cap() {
    $s    = viaticos_ocr_get_settings();
    $cap  = (int) $s['monthly_cap'];
    $used = viaticos_ocr_count_calls_this_month();
    if ( $cap <= 0 ) {
        return array(
            'allowed'   => true,
            'used'      => $used,
            'cap'       => 0,
            'remaining' => PHP_INT_MAX,
        );
    }
    return array(
        'allowed'   => $used < $cap,
        'used'      => $used,
        'cap'       => $cap,
        'remaining' => max( 0, $cap - $used ),
    );
}

<?php
/**
 * Template Name: Dashboard App
 *
 * Single entry point for the Viáticos ERP dashboard.
 * Routes to the correct view (Admin or Colaborador) based on
 * the current user's WordPress role. Zero redundancy — all shared
 * HTML/CSS lives in app-layout-header.php; role views are isolated.
 *
 * URL: assign this template to a single WordPress page (e.g. /dashboard).
 *
 * @package ThemeAdministracion
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Security: must be logged in ───────────────────────────────────────────────
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// ── Admin guard: if URL is hit directly by a non-admin trying to escalate ─────
// (Role routing below handles the actual view; this just ensures valid session.)

// ── Shared data available to all template parts ───────────────────────────────
$current_user = wp_get_current_user();
$user_id = (int) $current_user->ID;
$user_name = esc_html($current_user->display_name);
$user_initials = strtoupper(
    mb_substr($current_user->first_name ?: $current_user->display_name, 0, 1) .
    mb_substr($current_user->last_name ?: '', 0, 1)
);
$logout_url = esc_url(wp_logout_url(home_url()));
$rest_nonce = wp_create_nonce('wp_rest');
$api_base = esc_js(rtrim(get_rest_url(null, 'viaticos/v1'), '/'));

// Role routing is intentionally binary: administrators see the admin app;
// every other authenticated role sees only its own portal.
$is_admin = in_array('administrator', (array) $current_user->roles, true);
$dashboard_role = $is_admin ? 'admin' : 'colaborador';
$user_acf_context = 'user_' . $user_id;
$user_dni = function_exists('get_field') ? (string) get_field('dni', $user_acf_context) : '';
$user_cargo_terms = wp_get_object_terms($user_id, 'viaticos_cargo', ['fields' => 'names']);
$user_area_terms = wp_get_object_terms($user_id, 'viaticos_area', ['fields' => 'names']);
$user_cargo = ! is_wp_error($user_cargo_terms) && ! empty($user_cargo_terms) ? (string) $user_cargo_terms[0] : '';
$user_area = ! is_wp_error($user_area_terms) && ! empty($user_area_terms) ? (string) $user_area_terms[0] : '';

$dashboard_args = [
    'user_name'      => $user_name,
    'user_initials'  => $user_initials,
    'logout_url'     => $logout_url,
    'rest_nonce'     => $rest_nonce,
    'api_base'       => $api_base,
    'dashboard_role' => $dashboard_role,
    'user_dni'       => sanitize_text_field($user_dni),
    'user_cargo'     => sanitize_text_field($user_cargo),
    'user_area'      => sanitize_text_field($user_area),
];

// ── Render ────────────────────────────────────────────────────────────────────
get_template_part('template-parts/dashboard/app-layout-header', null, $dashboard_args);

if ($is_admin) {
    get_template_part('template-parts/dashboard/view-admin', null, $dashboard_args);
} else {
    get_template_part('template-parts/dashboard/view-colaborador', null, $dashboard_args);
}

get_template_part('template-parts/dashboard/app-layout-footer');

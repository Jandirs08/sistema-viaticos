<?php
/**
 * functions.php — bootstrap del tema "theme-administracion".
 *
 * Carga módulos del dominio (CPTs, ACF, roles, REST) + render shell + auth
 * hooks. La lógica vive en /includes; este archivo solo orquesta y registra
 * los hooks de assets/setup propios del tema.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// 1. INCLUSIÓN DE MÓDULOS
// =============================================================================

// Constantes ACF — debe cargarse primero para que cualquier hook posterior las use.
require_once get_template_directory() . '/includes/acf-keys.php';

// Dominio.
require_once get_template_directory() . '/includes/cpt-setup.php';
require_once get_template_directory() . '/includes/acf-fields.php';
require_once get_template_directory() . '/includes/user-taxonomies.php';
require_once get_template_directory() . '/includes/roles-setup.php';
require_once get_template_directory() . '/includes/api-endpoints.php';

// Render shell + auth (orden importa: auth.php usa funciones de render-shell.php).
require_once get_template_directory() . '/includes/render-shell.php';
require_once get_template_directory() . '/includes/auth.php';

// OCR bootstrap (settings page + storage + helpers; endpoint REST en fase OCR-2).
require_once get_template_directory() . '/includes/ocr-bootstrap.php';

// =============================================================================
// 2. ASSETS Y CONFIGURACIÓN BÁSICA DEL TEMA
// =============================================================================

function viaticos_enqueue_assets()
{
    wp_enqueue_style(
        'viaticos-main-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'viaticos_enqueue_assets');

add_action('wp_head', function () {
    echo '<link rel="icon" type="image/x-icon" href="' . esc_url(get_template_directory_uri() . '/images/favicon.ico') . '">' . "\n";
}, 1);

function viaticos_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('wp-block-styles');
    load_theme_textdomain('theme-administracion', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'viaticos_theme_setup');

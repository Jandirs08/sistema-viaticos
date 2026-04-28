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
// 0. ACF — DEPENDENCIA REQUERIDA
// =============================================================================

/**
 * El tema asume que ACF (free o Pro) está activo. Si no lo está, el ERP
 * entero se desploma porque las 70+ llamadas a get_field/update_field
 * son fatal sin el plugin. Helper centralizado para guardar la dependencia
 * desde admin notice, frontend dashboard y REST API.
 */
function viaticos_acf_active()
{
    return function_exists('get_field')
        && function_exists('update_field')
        && function_exists('acf_add_local_field_group');
}

add_action('admin_notices', function () {
    if (viaticos_acf_active()) {
        return;
    }
    if (!current_user_can('activate_plugins')) {
        return;
    }
    echo '<div class="notice notice-error"><p><strong>ERP Viáticos:</strong> el plugin <em>Advanced Custom Fields</em> está desactivado. El sistema de viáticos no funcionará hasta que sea reactivado.</p></div>';
});

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

    if (!is_user_logged_in() || is_admin()) {
        return;
    }

    $base = get_template_directory_uri() . '/assets/js/';
    $path = get_template_directory() . '/assets/js/';

    $ver = static function ($rel) use ($path) {
        $abs = $path . $rel;
        return file_exists($abs) ? (string) filemtime($abs) : '1.1.0';
    };

    wp_enqueue_script('viaticos-utils',       $base . 'modules/utils.js',       array(),                                            $ver('modules/utils.js'),       true);
    wp_enqueue_script('viaticos-confirm',     $base . 'modules/confirm.js',     array(),                                            $ver('modules/confirm.js'),     true);
    wp_enqueue_script('viaticos-bootstrap',   $base . 'modules/bootstrap.js',   array(),                                            $ver('modules/bootstrap.js'),   true);
    wp_enqueue_script('viaticos-gasto-ui',    $base . 'modules/gasto-ui.js',    array('viaticos-utils'),                            $ver('modules/gasto-ui.js'),    true);
    wp_enqueue_script('viaticos-estado-ui',   $base . 'modules/estado-ui.js',   array('viaticos-bootstrap'),                        $ver('modules/estado-ui.js'),   true);
    wp_enqueue_script('viaticos-timeline-ui', $base . 'modules/timeline-ui.js', array(),                                            $ver('modules/timeline-ui.js'), true);
    wp_enqueue_script('viaticos-liquidacion', $base . 'modules/liquidacion.js', array(),                                            $ver('modules/liquidacion.js'), true);
    wp_enqueue_script('viaticos-router',      $base . 'modules/router.js',      array(),                                            $ver('modules/router.js'),      true);
    wp_enqueue_script('viaticos-detalle-ui',  $base . 'modules/detalle-ui.js',  array('viaticos-utils', 'viaticos-confirm', 'viaticos-gasto-ui', 'viaticos-estado-ui', 'viaticos-timeline-ui'), $ver('modules/detalle-ui.js'), true);
    wp_enqueue_script('viaticos-sidebar',     $base . 'modules/sidebar.js',     array(),                                            $ver('modules/sidebar.js'),     true);
    wp_enqueue_script('viaticos-worktray',    $base . 'modules/worktray.js',    array('viaticos-utils', 'viaticos-estado-ui'),      $ver('modules/worktray.js'),    true);

    $deps_app = array(
        'viaticos-utils', 'viaticos-confirm', 'viaticos-bootstrap', 'viaticos-gasto-ui', 'viaticos-estado-ui',
        'viaticos-timeline-ui', 'viaticos-liquidacion', 'viaticos-router',
        'viaticos-detalle-ui', 'viaticos-sidebar', 'viaticos-worktray',
    );

    $current_user = wp_get_current_user();
    $is_admin_role = in_array('administrator', (array) $current_user->roles, true)
        || current_user_can('manage_viaticos');

    if ($is_admin_role) {
        wp_enqueue_script('viaticos-admin', $base . 'admin.js', $deps_app, $ver('admin.js'), true);
    } else {
        wp_enqueue_script('viaticos-colaborador', $base . 'colaborador.js', $deps_app, $ver('colaborador.js'), true);
    }
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

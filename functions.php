<?php
/**
 * functions.php
 *
 * Archivo principal de funciones del tema "theme-administracion".
 * Responsable de:
 *  - Cargar hojas de estilo y scripts del tema.
 *  - Incluir los módulos de Custom Post Types y campos ACF.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

// Bloque de seguridad: impide el acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// 1. INCLUSIÓN DE MÓDULOS
// =============================================================================

/**
 * Incluye el módulo de registro de Custom Post Types.
 * Contiene la definición de 'solicitud_viatico' y 'gasto_rendicion'.
 */
require_once get_template_directory() . '/includes/cpt-setup.php';

/**
 * Incluye el módulo de campos ACF (Advanced Custom Fields).
 * Registra todos los field groups mediante acf_add_local_field_group().
 */
require_once get_template_directory() . '/includes/acf-fields.php';

/**
 * Incluye el módulo de taxonomías de usuario.
 */
require_once get_template_directory() . '/includes/user-taxonomies.php';

/**
 * Incluye el módulo de roles y seguridad.
 */
require_once get_template_directory() . '/includes/roles-setup.php';

/**
 * Incluye los endpoints de la API REST.
 */
require_once get_template_directory() . '/includes/api-endpoints.php';


// =============================================================================
// 2. CARGA DE ESTILOS Y SCRIPTS
// =============================================================================

/**
 * viaticos_enqueue_assets()
 *
 * Encola la hoja de estilos principal del tema y los scripts necesarios.
 * Se engancha en 'wp_enqueue_scripts' para cargarse en el front-end.
 *
 * @return void
 */
function viaticos_enqueue_assets()
{

    // --- Hoja de estilos principal del tema ---
    wp_enqueue_style(
        'viaticos-main-style',                      // Handle único.
        get_stylesheet_uri(),                        // Apunta a /style.css del tema.
        array(),                                     // Sin dependencias.
        wp_get_theme()->get('Version')             // Versión tomada del header del tema.
    );

    // --- Script principal del tema (cargado en el footer) ---
    // Descomenta y ajusta la ruta cuando necesites añadir JS propio.
    /*
    wp_enqueue_script(
        'viaticos-main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array( 'jquery' ),
        wp_get_theme()->get( 'Version' ),
        true  // true = carga en el footer para mejor rendimiento.
    );
    */
}
add_action('wp_enqueue_scripts', 'viaticos_enqueue_assets');


// =============================================================================
// 3. CONFIGURACIÓN BÁSICA DEL TEMA
// =============================================================================

/**
 * viaticos_theme_setup()
 *
 * Declara las características (features) que soporta el tema.
 * Se engancha en 'after_setup_theme' para ejecutarse temprano en el ciclo
 * de WordPress, antes de que se carguen los widgets y otras integraciones.
 *
 * @return void
 */
function viaticos_theme_setup()
{

    // Habilita soporte para el tag <title> generado dinámicamente por WordPress.
    add_theme_support('title-tag');

    // Habilita soporte para miniaturas de entradas (featured images).
    add_theme_support('post-thumbnails');

    // Compatibilidad con el editor en bloque (Gutenberg) mediante estilos del tema.
    add_theme_support('wp-block-styles');

    // Carga automática del archivo de traducción del tema.
    load_theme_textdomain('theme-administracion', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'viaticos_theme_setup');

<?php
/**
 * cpt-setup.php
 *
 * Registro de los Custom Post Types (CPT) del sistema de viáticos.
 *
 * CPTs registrados:
 *  - solicitud_viatico : Solicitud principal de viáticos por colaborador.
 *  - gasto_rendicion   : Gastos individuales rendidos contra una solicitud.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

// Bloque de seguridad: impide el acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// =============================================================================
// CPT 1: SOLICITUD DE VIÁTICOS
// =============================================================================

/**
 * viaticos_register_cpt_solicitud()
 *
 * Registra el Custom Post Type 'solicitud_viatico'.
 * Este CPT representa la entidad principal del sistema: la solicitud formal
 * de viáticos que emite un colaborador.
 *
 * @return void
 */
function viaticos_register_cpt_solicitud() {

    // Etiquetas visibles en el área de administración de WordPress.
    $labels = array(
        'name'                  => _x( 'Solicitudes de Viáticos', 'Post Type General Name', 'theme-administracion' ),
        'singular_name'         => _x( 'Solicitud de Viático',    'Post Type Singular Name', 'theme-administracion' ),
        'menu_name'             => __( 'Solicitudes de Viáticos', 'theme-administracion' ),
        'name_admin_bar'        => __( 'Solicitud de Viático',    'theme-administracion' ),
        'add_new'               => __( 'Añadir nueva',            'theme-administracion' ),
        'add_new_item'          => __( 'Añadir nueva solicitud',  'theme-administracion' ),
        'new_item'              => __( 'Nueva solicitud',         'theme-administracion' ),
        'edit_item'             => __( 'Editar solicitud',        'theme-administracion' ),
        'view_item'             => __( 'Ver solicitud',           'theme-administracion' ),
        'all_items'             => __( 'Todas las solicitudes',   'theme-administracion' ),
        'search_items'          => __( 'Buscar solicitudes',      'theme-administracion' ),
        'not_found'             => __( 'No se encontraron solicitudes.', 'theme-administracion' ),
        'not_found_in_trash'    => __( 'No hay solicitudes en la papelera.', 'theme-administracion' ),
    );

    // Argumentos de configuración del CPT.
    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Registro de solicitudes de viáticos del personal.', 'theme-administracion' ),

        // Visibilidad y acceso.
        'public'             => true,   // Visible en el front-end y en el admin.
        'show_ui'            => true,   // Muestra la interfaz de administración.
        'show_in_menu'       => true,   // Aparece como ítem de menú principal en el admin.
        'show_in_nav_menus'  => false,  // No aparece en los menús de navegación del sitio.
        'show_in_rest'       => true,   // Habilita la API REST (requerido para Gutenberg y apps externas).

        // Capacidades y jerarquía.
        'hierarchical'       => false,  // Comportamiento similar a "Entradas" (no páginas).
        'has_archive'        => true,   // Genera una página de archivo con todas las solicitudes.
        'rewrite'            => array(
            'slug'       => 'solicitudes-viaticos',
            'with_front' => false,
        ),
        'query_var'          => true,

        // Meta cuadros habilitados en el editor.
        'supports'           => array( 'title', 'author' ),

        // Icono del menú (Dashicons).
        'menu_icon'          => 'dashicons-money-alt',

        // Posición en el menú lateral del admin (después de "Comentarios" = 25).
        'menu_position'      => 25,
    );

    register_post_type( 'solicitud_viatico', $args );
}
add_action( 'init', 'viaticos_register_cpt_solicitud' );


// =============================================================================
// CPT 2: GASTO / RENDICIÓN
// =============================================================================

/**
 * viaticos_register_cpt_gasto()
 *
 * Registra el Custom Post Type 'gasto_rendicion'.
 * Representa cada comprobante o gasto individual que el colaborador rinde
 * contra una solicitud de viáticos existente.
 *
 * Este CPT se muestra como un sub‑menú dentro del menú de 'solicitud_viatico',
 * estableciendo visualmente la relación padre‑hijo entre ambas entidades.
 *
 * @return void
 */
function viaticos_register_cpt_gasto() {

    // Etiquetas visibles en el área de administración de WordPress.
    $labels = array(
        'name'                  => _x( 'Gastos Rendidos',       'Post Type General Name', 'theme-administracion' ),
        'singular_name'         => _x( 'Gasto Rendido',         'Post Type Singular Name', 'theme-administracion' ),
        'menu_name'             => __( 'Gastos Rendidos',        'theme-administracion' ),
        'name_admin_bar'        => __( 'Gasto Rendido',          'theme-administracion' ),
        'add_new'               => __( 'Añadir nuevo',           'theme-administracion' ),
        'add_new_item'          => __( 'Añadir nuevo gasto',     'theme-administracion' ),
        'new_item'              => __( 'Nuevo gasto',            'theme-administracion' ),
        'edit_item'             => __( 'Editar gasto',           'theme-administracion' ),
        'view_item'             => __( 'Ver gasto',              'theme-administracion' ),
        'all_items'             => __( 'Todos los gastos',       'theme-administracion' ),
        'search_items'          => __( 'Buscar gastos rendidos', 'theme-administracion' ),
        'not_found'             => __( 'No se encontraron gastos.', 'theme-administracion' ),
        'not_found_in_trash'    => __( 'No hay gastos en la papelera.', 'theme-administracion' ),
    );

    // Argumentos de configuración del CPT.
    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Comprobantes y gastos rendidos por solicitud de viáticos.', 'theme-administracion' ),

        // Visibilidad y acceso.
        'public'             => true,
        'show_ui'            => true,
        // Al pasar el slug del CPT padre, este CPT aparece como sub-menú de 'solicitud_viatico'.
        'show_in_menu'       => 'edit.php?post_type=solicitud_viatico',
        'show_in_nav_menus'  => false,
        'show_in_rest'       => true,   // Habilita la API REST.

        // Capacidades y jerarquía.
        'hierarchical'       => false,
        'has_archive'        => false,  // No necesita página de archivo propia.
        'rewrite'            => array(
            'slug'       => 'gastos-rendidos',
            'with_front' => false,
        ),
        'query_var'          => true,

        // Solo soporte para título (el resto de datos van en campos ACF).
        'supports'           => array( 'title' ),

        // Sin icono propio; hereda la apariencia del menú padre.
        'menu_icon'          => 'dashicons-clipboard',
    );

    register_post_type( 'gasto_rendicion', $args );
}
add_action( 'init', 'viaticos_register_cpt_gasto' );

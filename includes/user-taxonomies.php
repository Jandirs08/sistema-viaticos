<?php
/**
 * user-taxonomies.php
 *
 * Registra taxonomías administrativas para usuarios del ERP.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra las taxonomías de usuarios.
 *
 * @return void
 */
function viaticos_register_user_taxonomies() {
    register_taxonomy(
        'viaticos_cargo',
        array( 'user' ),
        array(
            'labels' => array(
                'name'              => 'Cargos',
                'singular_name'     => 'Cargo',
                'search_items'      => 'Buscar cargos',
                'all_items'         => 'Todos los cargos',
                'edit_item'         => 'Editar cargo',
                'update_item'       => 'Actualizar cargo',
                'add_new_item'      => 'Añadir cargo',
                'new_item_name'     => 'Nuevo cargo',
                'menu_name'         => 'Cargos',
            ),
            'public'                => false,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'show_in_nav_menus'     => false,
            'show_tagcloud'         => false,
            'hierarchical'          => true,
            'rewrite'               => false,
            'query_var'             => false,
            'capabilities'          => array(
                'manage_terms' => 'list_users',
                'edit_terms'   => 'list_users',
                'delete_terms' => 'list_users',
                'assign_terms' => 'edit_users',
            ),
            'update_count_callback' => '_update_generic_term_count',
        )
    );

    register_taxonomy(
        'viaticos_area',
        array( 'user' ),
        array(
            'labels' => array(
                'name'              => 'Áreas',
                'singular_name'     => 'Área',
                'search_items'      => 'Buscar áreas',
                'all_items'         => 'Todas las áreas',
                'edit_item'         => 'Editar área',
                'update_item'       => 'Actualizar área',
                'add_new_item'      => 'Añadir área',
                'new_item_name'     => 'Nueva área',
                'menu_name'         => 'Áreas',
            ),
            'public'                => false,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'show_in_nav_menus'     => false,
            'show_tagcloud'         => false,
            'hierarchical'          => true,
            'rewrite'               => false,
            'query_var'             => false,
            'capabilities'          => array(
                'manage_terms' => 'list_users',
                'edit_terms'   => 'list_users',
                'delete_terms' => 'list_users',
                'assign_terms' => 'edit_users',
            ),
            'update_count_callback' => '_update_generic_term_count',
        )
    );
}
add_action( 'init', 'viaticos_register_user_taxonomies' );

/**
 * Agrega accesos a las pantallas estándar de términos bajo Usuarios.
 *
 * @return void
 */
function viaticos_register_user_taxonomy_admin_pages() {
    add_submenu_page(
        'users.php',
        'Cargos',
        'Cargos',
        'list_users',
        'edit-tags.php?taxonomy=viaticos_cargo'
    );

    add_submenu_page(
        'users.php',
        'Áreas',
        'Áreas',
        'list_users',
        'edit-tags.php?taxonomy=viaticos_area'
    );
}
add_action( 'admin_menu', 'viaticos_register_user_taxonomy_admin_pages' );

/**
 * Mantiene la navegación activa bajo Usuarios al editar taxonomías de usuario.
 *
 * @param string $parent_file Archivo padre actual.
 * @return string
 */
function viaticos_set_user_taxonomy_parent_file( $parent_file ) {
    $screen = get_current_screen();
    if ( ! $screen || 'edit-tags' !== $screen->base ) {
        return $parent_file;
    }

    if ( in_array( $screen->taxonomy, array( 'viaticos_cargo', 'viaticos_area' ), true ) ) {
        return 'users.php';
    }

    return $parent_file;
}
add_filter( 'parent_file', 'viaticos_set_user_taxonomy_parent_file' );

/**
 * Ajusta el submenú activo para taxonomías de usuario.
 *
 * @param string $submenu_file Submenú actual.
 * @return string
 */
function viaticos_set_user_taxonomy_submenu_file( $submenu_file ) {
    $screen = get_current_screen();
    if ( ! $screen || 'edit-tags' !== $screen->base ) {
        return $submenu_file;
    }

    if ( 'viaticos_cargo' === $screen->taxonomy ) {
        return 'edit-tags.php?taxonomy=viaticos_cargo';
    }

    if ( 'viaticos_area' === $screen->taxonomy ) {
        return 'edit-tags.php?taxonomy=viaticos_area';
    }

    return $submenu_file;
}
add_filter( 'submenu_file', 'viaticos_set_user_taxonomy_submenu_file' );


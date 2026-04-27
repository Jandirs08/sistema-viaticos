<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function viaticos_permission_logueado() {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Debes estar autenticado para realizar esta acción.', 'theme-administracion' ),
            array( 'status' => 401 )
        );
    }
    return true;
}

function viaticos_permission_admin() {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_forbidden',
            __( 'Debes estar autenticado.', 'theme-administracion' ),
            array( 'status' => 401 )
        );
    }

    if ( current_user_can( 'manage_viaticos' ) ) {
        return true;
    }

    return new WP_Error(
        'rest_forbidden',
        __( 'No tienes permisos para realizar esta acción.', 'theme-administracion' ),
        array( 'status' => 403 )
    );
}

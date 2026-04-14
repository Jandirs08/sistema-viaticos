<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// ROLES
// =============================================================================

function viaticos_registrar_roles() {

    // Evita duplicar roles en cada carga si ya existen.
    if ( ! get_role( 'colaborador_viaticos' ) ) {
        add_role(
            'colaborador_viaticos',
            'Colaborador de Viáticos',
            array(
                'read'       => true,
                'edit_posts' => true,
            )
        );
    }

    if ( ! get_role( 'admin_viaticos' ) ) {
        // Copia las capacidades del rol nativo 'editor'.
        $editor = get_role( 'editor' );
        add_role(
            'admin_viaticos',
            'Administrador de Viáticos',
            $editor ? $editor->capabilities : array()
        );
    }
}
add_action( 'init', 'viaticos_registrar_roles' );


// =============================================================================
// SEGURIDAD: BLOQUEO DE EDICIÓN POR ESTADO
// =============================================================================

function viaticos_bloquear_edicion_segun_estado( $post_id, $post, $update ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'colaborador_viaticos' ) ) {
        return;
    }

    // Solo bloquear en actualizaciones; permitir la creación inicial.
    if ( ! $update ) {
        return;
    }

    $estado = get_field( 'estado_solicitud', $post_id );

    $estados_bloqueados = array( 'pendiente', 'aprobada', 'rechazada', 'rendida' );

    if ( in_array( $estado, $estados_bloqueados, true ) ) {
        wp_die(
            esc_html__( 'No puedes editar una solicitud en este estado.', 'theme-administracion' ),
            esc_html__( 'Acción no permitida', 'theme-administracion' ),
            array( 'response' => 403, 'back_link' => true )
        );
    }
}
add_action( 'save_post_solicitud_viatico', 'viaticos_bloquear_edicion_segun_estado', 10, 3 );

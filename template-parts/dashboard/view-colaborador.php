<?php
/**
 * Template Part: Dashboard — Vista Colaborador
 *
 * Orquesta las vistas y modales del colaborador.
 * Contenido dividido en:
 *   - view-colab-vistas.php   → secciones (inicio, solicitudes, detalle, rendiciones)
 *   - view-colab-modales.php  → todos los modales
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'user_name'      => '',
        'rest_nonce'     => '',
        'api_base'       => '',
        'user_dni'       => '',
        'user_cargo'     => '',
        'user_area'      => '',
        'user_aprobador' => '',
    ]
);

require get_template_directory() . '/template-parts/dashboard/view-colab-vistas.php';
require get_template_directory() . '/template-parts/dashboard/view-colab-modales.php';

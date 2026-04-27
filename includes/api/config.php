<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Single source of truth para enums, labels y schemas que el front necesita.
 * Inyectado inline en app-layout-header.php y expuesto vía GET /viaticos/v1/config
 * para clientes externos o pruebas.
 */
function viaticos_get_config() {
    return array(
        'estados' => array(
            'solicitud' => array(
                'pendiente'  => 'Anticipo Pendiente',
                'aprobada'   => 'Anticipo Aprobado',
                'observada'  => 'Anticipo Observado',
                'rechazada'  => 'Anticipo Rechazado',
            ),
            'rendicion' => array(
                'no_disponible' => 'No disponible',
                'no_iniciada'   => 'Por Rendir',
                'en_proceso'    => 'Rindiendo',
                'en_revision'   => 'Rendición en Revisión',
                'aprobada'      => 'Rendición Aprobada',
                'observada'     => 'Rendición Observada',
                'rechazada'     => 'Rendición Rechazada',
            ),
        ),
        'decisiones'      => VIATICOS_DECISIONES,
        'eventos_validos' => viaticos_get_eventos_historial_validos(),
        'clase_doc_map'   => array(
            'VALE MOVILIDAD' => 'movilidad',
            'VALE DE CAJA'   => 'vale_caja',
            // cualquier otro → 'documento' (fallback)
        ),
        'tipo_default'    => 'documento',
        'schemas_gasto'   => array(
            'movilidad' => array(
                'groups'   => array( 'rg-group-fecha', 'rg-group-motivo', 'rg-group-destino', 'rg-group-importe', 'rg-group-ceco-oi' ),
                'labels'   => array(
                    'lbl-rg-fecha'   => 'Día',
                    'lbl-rg-importe' => 'Monto Gastado S/.',
                ),
                'required' => array( 'rg-fecha', 'rg-motivo', 'rg-destino', 'rg-importe', 'rg-ceco-oi' ),
                'fields'   => array(
                    array( 'payload' => 'motivo_movilidad',  'el' => 'rg-motivo' ),
                    array( 'payload' => 'destino_movilidad', 'el' => 'rg-destino' ),
                    array( 'payload' => 'ceco_oi',           'el' => 'rg-ceco-oi' ),
                ),
            ),
            'vale_caja' => array(
                'groups'   => array( 'rg-group-fecha', 'rg-group-ruc', 'rg-group-razon', 'rg-group-concepto', 'rg-group-importe', 'rg-group-nro', 'rg-group-ceco', 'rg-group-oi' ),
                'labels'   => array(
                    'lbl-rg-fecha'    => 'Fecha Emisión Comprobante',
                    'lbl-rg-importe'  => 'Importe Total del Comprobante',
                    'lbl-rg-concepto' => 'Descripción del Servicio',
                ),
                'required' => array( 'rg-fecha', 'rg-ruc', 'rg-razon', 'rg-importe', 'rg-nro-comprobante' ),
                'fields'   => array(
                    array( 'payload' => 'ruc',                  'el' => 'rg-ruc' ),
                    array( 'payload' => 'razon_social',         'el' => 'rg-razon' ),
                    array( 'payload' => 'descripcion_concepto', 'el' => 'rg-concepto' ),
                    array( 'payload' => 'nro_comprobante',      'el' => 'rg-nro-comprobante' ),
                    array( 'payload' => 'ceco_oi',              'el' => 'rg-ceco', 'concat_with' => 'rg-oi', 'separator' => ' / ' ),
                ),
            ),
            'documento' => array(
                'groups'   => array( 'rg-group-fecha', 'rg-group-ruc', 'rg-group-razon', 'rg-group-concepto', 'rg-group-importe', 'rg-group-nro' ),
                'labels'   => array(
                    'lbl-rg-fecha'    => 'Fecha de Emisión',
                    'lbl-rg-importe'  => 'Importe',
                    'lbl-rg-concepto' => 'Descripción / Concepto',
                ),
                'required' => array( 'rg-fecha', 'rg-importe', 'rg-ruc', 'rg-razon', 'rg-nro-comprobante' ),
                'fields'   => array(
                    array( 'payload' => 'ruc',                  'el' => 'rg-ruc' ),
                    array( 'payload' => 'razon_social',         'el' => 'rg-razon' ),
                    array( 'payload' => 'nro_comprobante',      'el' => 'rg-nro-comprobante' ),
                    array( 'payload' => 'descripcion_concepto', 'el' => 'rg-concepto' ),
                ),
            ),
        ),
    );
}

function viaticos_callback_config( WP_REST_Request $request ) {
    return new WP_REST_Response( viaticos_get_config(), 200 );
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// ACF FIELD KEYS — source of truth for update_field() calls
// =============================================================================

// solicitud_viatico fields
define( 'ACF_SOL_DNI',       'field_sol_dni_colaborador' );
define( 'ACF_SOL_MONTO',     'field_sol_monto_solicitado' );
define( 'ACF_SOL_FECHA',     'field_sol_fecha_viaje' );
define( 'ACF_SOL_MOTIVO',    'field_sol_motivo_viaje' );
define( 'ACF_SOL_CECO',      'field_sol_centro_costo' );
define( 'ACF_SOL_APROBADOR', 'field_sol_nombre_aprobador' );
define( 'ACF_SOL_ESTADO',    'field_sol_estado_solicitud' );

// gasto_rendicion fields
define( 'ACF_GAS_SOLICITUD',  'field_gas_id_solicitud_padre' );
define( 'ACF_GAS_FECHA',      'field_gas_fecha_emision' );
define( 'ACF_GAS_IMPORTE',    'field_gas_importe_comprobante' );
define( 'ACF_GAS_MOTIVO_MOV', 'field_gas_motivo_movilidad' );
define( 'ACF_GAS_DESTINO',    'field_gas_destino_movilidad' );
define( 'ACF_GAS_CECO',       'field_gas_ceco_oi' );
define( 'ACF_GAS_RUC',        'field_gas_ruc_proveedor' );
define( 'ACF_GAS_RAZON',      'field_gas_razon_social' );
define( 'ACF_GAS_NRO',        'field_gas_nro_comprobante' );
define( 'ACF_GAS_CONCEPTO',   'field_gas_descripcion_concepto' );

// post_meta keys for rendicion state (not ACF fields — stored directly via update_post_meta)
define( 'META_RENDICION_FINALIZADA', 'rendicion_finalizada' );
define( 'META_ESTADO_RENDICION',     'estado_rendicion' );

// Valid decisions for actualizar-estado and decidir-rendicion
define( 'VIATICOS_DECISIONES', array( 'aprobada', 'observada', 'rechazada' ) );

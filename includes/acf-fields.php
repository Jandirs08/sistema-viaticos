<?php
/**
 * acf-fields.php
 *
 * Define y registra los grupos de campos de Advanced Custom Fields (ACF)
 * para los CPTs del sistema de viáticos, utilizando acf_add_local_field_group().
 *
 * Al registrar los campos por código (no desde la interfaz gráfica), estos
 * son portables entre entornos (desarrollo, staging, producción) y quedan
 * versionados en el repositorio de Git junto al resto del tema.
 *
 * Grupos registrados:
 *  - Grupo 1: Datos de la Solicitud de Viático  → CPT 'solicitud_viatico'
 *  - Grupo 2: Datos del Gasto / Rendición        → CPT 'gasto_rendicion'
 *  - Grupo 3: Datos ERP del Usuario              → Objeto 'user'
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

// Bloque de seguridad: impide el acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// =============================================================================
// REGISTRO DE CAMPOS — se ejecuta en el hook 'acf/init' de ACF
// =============================================================================

/**
 * viaticos_register_acf_fields()
 *
 * Función principal que llama a los registros de cada grupo de campos.
 * Se engancha en 'acf/init' para garantizar que ACF esté completamente
 * cargado antes de intentar registrar los grupos.
 *
 * @return void
 */
function viaticos_register_acf_fields() {

    // Verificación de seguridad: si ACF no está activo, no hacemos nada.
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    viaticos_acf_group_solicitud();
    viaticos_acf_group_gasto();
    viaticos_acf_group_usuario();
}
add_action( 'acf/init', 'viaticos_register_acf_fields' );

// =============================================================================
// GRUPO 1: CAMPOS PARA 'solicitud_viatico'
// =============================================================================

/**
 * viaticos_acf_group_solicitud()
 *
 * Registra el grupo de campos asociado al CPT 'solicitud_viatico'.
 *
 * Campos:
 *  - dni_colaborador    : Texto, máx. 8 caracteres, requerido.
 *  - monto_solicitado   : Número, mín. 1, requerido.
 *  - fecha_viaje        : Date Picker, requerido.
 *  - motivo_viaje       : Área de texto, requerido.
 *  - centro_costo       : Texto, requerido.
 *  - estado_solicitud   : Select (Pendiente / Aprobada / Observada / Rechazada). Default: Pendiente.
 *
 * @return void
 */
function viaticos_acf_group_solicitud() {

    acf_add_local_field_group( array(

        // ── Metadatos del grupo ───────────────────────────────────────────────
        'key'                   => 'group_solicitud_viatico',
        'title'                 => 'Datos de la Solicitud de Viático',
        'menu_order'            => 10,
        'position'              => 'normal',    // Posición en el editor: normal | side | acf_after_title.
        'style'                 => 'default',   // Estilo del meta-box: default | seamless.
        'label_placement'       => 'top',       // Etiqueta sobre el campo.
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => 'Información principal de la solicitud de viáticos.',

        // ── Regla de visualización: solo en 'solicitud_viatico' ───────────────
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'solicitud_viatico',
                ),
            ),
        ),

        // ── Definición de campos ──────────────────────────────────────────────
        'fields' => array(

            // ------------------------------------------------------------------
            // Campo 1: DNI del Colaborador
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_sol_dni_colaborador',
                'label'         => 'DNI del Colaborador',
                'name'          => 'dni_colaborador',
                'type'          => 'text',
                'instructions'  => 'Ingrese el número de DNI (exactamente 8 dígitos).',
                'required'      => 1,               // Campo obligatorio.
                'maxlength'     => 8,               // Máximo 8 caracteres.
                'placeholder'   => 'Ej: 12345678',
                'prepend'       => '',
                'append'        => '',
                'wrapper'       => array(
                    'width' => '50',                // Ocupa el 50 % del ancho disponible.
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 2: Monto Solicitado
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_sol_monto_solicitado',
                'label'         => 'Monto Solicitado (S/.)',
                'name'          => 'monto_solicitado',
                'type'          => 'number',
                'instructions'  => 'Ingrese el monto total solicitado en soles.',
                'required'      => 1,
                'min'           => 1,               // Valor mínimo aceptado.
                'max'           => '',              // Sin tope máximo.
                'step'          => 0.01,            // Permite decimales de 2 lugares.
                'placeholder'   => 'Ej: 250.00',
                'prepend'       => 'S/.',
                'append'        => '',
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 3: Fecha de Viaje
            // ------------------------------------------------------------------
            array(
                'key'               => 'field_sol_fecha_viaje',
                'label'             => 'Fecha de Viaje',
                'name'              => 'fecha_viaje',
                'type'              => 'date_picker',
                'instructions'      => 'Seleccione la fecha programada del viaje.',
                'required'          => 1,
                'display_format'    => 'd/m/Y',     // Formato de visualización en el admin.
                'return_format'     => 'Y-m-d',     // Formato de retorno al obtener el valor en PHP.
                'first_day'         => 1,           // Primer día de la semana: 1 = Lunes.
                'wrapper'           => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 4: Motivo del Viaje
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_sol_motivo_viaje',
                'label'         => 'Motivo del Viaje',
                'name'          => 'motivo_viaje',
                'type'          => 'textarea',
                'instructions'  => 'Describa el objetivo o motivo del viaje de forma clara.',
                'required'      => 1,
                'rows'          => 4,
                'placeholder'   => 'Ej: Capacitación en sede Lima sobre gestión de proyectos.',
                'new_lines'     => 'wpautop',       // Convierte saltos de línea a <p> automáticamente.
                'wrapper'       => array(
                    'width' => '100',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 5: Centro de Costo
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_sol_centro_costo',
                'label'         => 'Centro de Costo',
                'name'          => 'centro_costo',
                'type'          => 'text',
                'instructions'  => 'Ingrese el código o nombre del centro de costo responsable.',
                'required'      => 1,
                'maxlength'     => '',
                'placeholder'   => 'Ej: CC-001 / ADMINISTRACIÓN',
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 6: Estado de la Solicitud
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_sol_estado_solicitud',
                'label'         => 'Estado de la Solicitud',
                'name'          => 'estado_solicitud',
                'type'          => 'select',
                'instructions'  => 'Seleccione el estado actual de la solicitud.',
                'required'      => 0,               // No requerido: tiene valor por defecto.
                // Opciones en formato 'valor' => 'Etiqueta'.
                'choices'       => array(
                    'pendiente'  => 'Pendiente',
                    'aprobada'   => 'Aprobada',
                    'observada'  => 'Observada',
                    'rechazada'  => 'Rechazada',
                ),
                'default_value' => 'pendiente',     // Valor por defecto al crear una nueva solicitud.
                'allow_null'    => 0,               // No permite seleccionar una opción vacía.
                'multiple'      => 0,               // Selección simple.
                'ui'            => 1,               // Habilita la UI mejorada (select2).
                'return_format' => 'value',         // Retorna el valor (no la etiqueta).
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

        ), // fin fields

    ) ); // fin acf_add_local_field_group — grupo solicitud
}


// =============================================================================
// GRUPO 2: CAMPOS PARA 'gasto_rendicion'
// =============================================================================

/**
 * viaticos_acf_group_gasto()
 *
 * Registra el grupo de campos asociado al CPT 'gasto_rendicion'.
 *
 * Campos:
 *  - id_solicitud_padre    : Post Object (filtra por 'solicitud_viatico'), requerido.
 *  - tipo_plantilla        : Select (Vale de Caja / Vale de Movilidad / Modelo Liquidación).
 *  - fecha_emision         : Date Picker, requerido.
 *  - importe_comprobante   : Número, mín. 0.1, requerido.
 *  - ruc_proveedor         : Texto, máx. 11 caracteres.
 *  - razon_social          : Texto.
 *  - nro_comprobante       : Texto.
 *  - cuenta_contable       : Texto.
 *  - archivos_adjuntos     : File (URL, acepta pdf / jpg / xml).
 *
 * @return void
 */
function viaticos_acf_group_gasto() {

    acf_add_local_field_group( array(

        // ── Metadatos del grupo ───────────────────────────────────────────────
        'key'                   => 'group_gasto_rendicion',
        'title'                 => 'Datos del Gasto / Rendición',
        'menu_order'            => 10,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => 'Información del comprobante o gasto rendido.',

        // ── Regla de visualización: solo en 'gasto_rendicion' ─────────────────
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'gasto_rendicion',
                ),
            ),
        ),

        // ── Definición de campos ──────────────────────────────────────────────
        'fields' => array(

            // ------------------------------------------------------------------
            // Campo 1: Solicitud Padre (Post Object)
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_id_solicitud_padre',
                'label'         => 'Solicitud de Viático Relacionada',
                'name'          => 'id_solicitud_padre',
                'type'          => 'post_object',
                'instructions'  => 'Seleccione la solicitud de viático a la que pertenece este gasto.',
                'required'      => 1,
                // Filtra el selector para mostrar únicamente 'solicitud_viatico'.
                'post_type'     => array( 'solicitud_viatico' ),
                'taxonomy'      => array(),
                'allow_null'    => 0,
                'multiple'      => 0,               // Solo puede vincularse a una solicitud.
                'return_format' => 'id',            // Retorna el ID del post seleccionado.
                'ui'            => 1,               // UI mejorada con búsqueda en tiempo real.
                'wrapper'       => array(
                    'width' => '100',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 2: Tipo de Plantilla / Documento
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_tipo_plantilla',
                'label'         => 'Tipo de Plantilla',
                'name'          => 'tipo_plantilla',
                'type'          => 'select',
                'instructions'  => 'Seleccione el tipo de documento contable que sustenta el gasto.',
                'required'      => 0,
                'choices'       => array(
                    'movilidad' => 'Movilidad',
                    'vale_caja' => 'Vale de Caja',
                    'factura'   => 'Factura',
                    'boleta'    => 'Boleta',
                    'rxh'       => 'RxH',
                ),
                'default_value' => '',
                'allow_null'    => 1,               // Permite no seleccionar ninguna opción.
                'multiple'      => 0,
                'ui'            => 1,
                'return_format' => 'value',
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 3: Fecha de Emisión del Comprobante
            // ------------------------------------------------------------------
            array(
                'key'               => 'field_gas_fecha_emision',
                'label'             => 'Fecha de Emisión',
                'name'              => 'fecha_emision',
                'type'              => 'date_picker',
                'instructions'      => 'Seleccione la fecha de emisión del comprobante.',
                'required'          => 1,
                'display_format'    => 'd/m/Y',
                'return_format'     => 'Y-m-d',
                'first_day'         => 1,
                'wrapper'           => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 4: Importe del Comprobante
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_importe_comprobante',
                'label'         => 'Importe del Comprobante (S/.)',
                'name'          => 'importe_comprobante',
                'type'          => 'number',
                'instructions'  => 'Ingrese el monto del comprobante en soles.',
                'required'      => 1,
                'min'           => 0.1,             // Mínimo 0.10 soles.
                'max'           => '',
                'step'          => 0.01,
                'placeholder'   => 'Ej: 45.50',
                'prepend'       => 'S/.',
                'append'        => '',
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 5: RUC del Proveedor
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_ruc_proveedor',
                'label'         => 'RUC del Proveedor',
                'name'          => 'ruc_proveedor',
                'type'          => 'text',
                'instructions'  => 'Ingrese el número de RUC del proveedor (máximo 11 dígitos).',
                'required'      => 0,
                'maxlength'     => 11,
                'placeholder'   => 'Ej: 20123456789',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '!=',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 6: Razón Social del Proveedor
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_razon_social',
                'label'         => 'Razón Social del Proveedor',
                'name'          => 'razon_social',
                'type'          => 'text',
                'instructions'  => 'Ingrese la razón social o nombre del proveedor.',
                'required'      => 0,
                'maxlength'     => '',
                'placeholder'   => 'Ej: TRANSPORTES RÁPIDOS SAC',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '!=',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 7: Número de Comprobante
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_nro_comprobante',
                'label'         => 'Número de Comprobante',
                'name'          => 'nro_comprobante',
                'type'          => 'text',
                'instructions'  => 'Ingrese la serie y número del comprobante. Ej: F001-00123456.',
                'required'      => 0,
                'maxlength'     => '',
                'placeholder'   => 'Ej: F001-00123456',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '!=',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 8: Descripción / Concepto
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_descripcion_concepto',
                'label'         => 'Descripción / Concepto',
                'name'          => 'descripcion_concepto',
                'type'          => 'textarea',
                'instructions'  => 'Describa brevemente el concepto del gasto o comprobante.',
                'required'      => 0,
                'rows'          => 3,
                'new_lines'     => 'br',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '!=',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '100',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 9: Motivo de Movilidad
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_motivo_movilidad',
                'label'         => 'Motivo de Movilidad',
                'name'          => 'motivo_movilidad',
                'type'          => 'textarea',
                'instructions'  => 'Indique el motivo del traslado o movilidad.',
                'required'      => 0,
                'rows'          => 3,
                'new_lines'     => 'br',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '==',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 10: Destino de Movilidad
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_destino_movilidad',
                'label'         => 'Destino de Movilidad',
                'name'          => 'destino_movilidad',
                'type'          => 'text',
                'instructions'  => 'Indique el destino o ruta principal del traslado.',
                'required'      => 0,
                'placeholder'   => 'Ej: Oficina central / cliente / sede',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '==',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 11: CECO / OI
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_ceco_oi',
                'label'         => 'CECO / OI',
                'name'          => 'ceco_oi',
                'type'          => 'text',
                'instructions'  => 'Ingrese el CECO u orden interna asociada al gasto.',
                'required'      => 0,
                'placeholder'   => 'Ej: CC-001 / OI-123',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_gas_tipo_plantilla',
                            'operator' => '==',
                            'value'    => 'movilidad',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 12: Cuenta Contable
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_cuenta_contable',
                'label'         => 'Cuenta Contable',
                'name'          => 'cuenta_contable',
                'type'          => 'text',
                'instructions'  => 'Ingrese el código de cuenta contable del Plan de Cuentas.',
                'required'      => 0,
                'maxlength'     => '',
                'placeholder'   => 'Ej: 63.1.1',
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
            ),

            // ------------------------------------------------------------------
            // Campo 13: Archivos Adjuntos (File)
            // ------------------------------------------------------------------
            array(
                'key'           => 'field_gas_archivos_adjuntos',
                'label'         => 'Archivos Adjuntos',
                'name'          => 'archivos_adjuntos',
                'type'          => 'file',
                'instructions'  => 'Adjunte el comprobante escaneado. Formatos permitidos: PDF, JPG, XML.',
                'required'      => 0,
                'return_format' => 'url',           // Retorna la URL del archivo subido.
                // Tipos MIME permitidos (separados por coma, sin espacios).
                'mime_types'    => 'pdf,jpg,xml',
                'wrapper'       => array(
                    'width' => '100',
                    'class' => '',
                    'id'    => '',
                ),
            ),

        ), // fin fields

    ) ); // fin acf_add_local_field_group — grupo gasto
}


// =============================================================================
// GRUPO 3: CAMPOS PARA USUARIOS
// =============================================================================

/**
 * viaticos_acf_group_usuario()
 *
 * Registra los campos ERP asociados al objeto user.
 *
 * Campos:
 *  - dni                  : Texto, máximo 8 caracteres.
 *  - cargo                : Taxonomía de usuario administrable.
 *  - area_departamento    : Taxonomía de usuario administrable.
 *  - director_responsable : Relación a usuario.
 *
 * @return void
 */
function viaticos_acf_group_usuario() {

    acf_add_local_field_group( array(

        'key'                   => 'group_usuario_erp',
        'title'                 => 'Datos ERP del Usuario',
        'menu_order'            => 20,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => 'Información operativa del colaborador para el portal ERP.',

        'location' => array(
            array(
                array(
                    'param'    => 'user_form',
                    'operator' => '==',
                    'value'    => 'all',
                ),
            ),
        ),

        'fields' => array(
            array(
                'key'           => 'field_user_dni',
                'label'         => 'DNI',
                'name'          => 'dni',
                'type'          => 'text',
                'instructions'  => 'Ingrese el DNI del colaborador.',
                'required'      => 0,
                'maxlength'     => 8,
                'placeholder'   => '12345678',
                'wrapper'       => array(
                    'width' => '33',
                    'class' => '',
                    'id'    => '',
                ),
            ),
            array(
                'key'           => 'field_user_cargo',
                'label'         => 'Cargo',
                'name'          => 'cargo',
                'type'          => 'taxonomy',
                'instructions'  => 'Seleccione el cargo.',
                'required'      => 0,
                'taxonomy'      => 'viaticos_cargo',
                'field_type'    => 'select',
                'allow_null'    => 1,
                'multiple'      => 0,
                'ui'            => 1,
                'return_format' => 'id',
                'save_terms'    => 1,
                'load_terms'    => 1,
                'add_term'      => 0,
                'wrapper'       => array(
                    'width' => '33',
                    'class' => '',
                    'id'    => '',
                ),
            ),
            array(
                'key'           => 'field_user_area_departamento',
                'label'         => 'Área / Departamento',
                'name'          => 'area_departamento',
                'type'          => 'taxonomy',
                'instructions'  => 'Seleccione el área.',
                'required'      => 0,
                'taxonomy'      => 'viaticos_area',
                'field_type'    => 'select',
                'allow_null'    => 1,
                'multiple'      => 0,
                'ui'            => 1,
                'return_format' => 'id',
                'save_terms'    => 1,
                'load_terms'    => 1,
                'add_term'      => 0,
                'wrapper'       => array(
                    'width' => '33',
                    'class' => '',
                    'id'    => '',
                ),
            ),
            array(
                'key'           => 'field_user_director_responsable',
                'label'         => 'Director Responsable',
                'name'          => 'director_responsable',
                'type'          => 'user',
                'instructions'  => 'Seleccione el usuario que actúa como director responsable del colaborador.',
                'required'      => 0,
                'role'          => array(),
                'allow_null'    => 1,
                'multiple'      => 0,
                'return_format' => 'id',
                'wrapper'       => array(
                    'width' => '100',
                    'class' => '',
                    'id'    => '',
                ),
            ),
        ),

    ) );
}

<?php
/**
 * Template Part: Dashboard — Vista Colaborador
 *
 * Renders the full Colaborador SPA: sidebar nav links, three views
 * (Inicio / Mis Solicitudes / Mis Rendiciones), three modals
 * (Nueva Solicitud / Editar / Rendir Gasto) and the ViaticosApp JS module.
 *
 * Expected args (injected by page-dashboard.php):
 *   $args['user_name']   string  Escaped display name.
 *   $args['rest_nonce']  string  WP REST nonce.
 *   $args['api_base']    string  Base REST URL (no trailing slash).
 *   $args['user_dni']    string  DNI del usuario actual.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'user_name'         => '',
        'rest_nonce'        => '',
        'api_base'          => '',
        'user_dni'          => '',
    ]
);
?>


<!-- ============================================================
     VISTA: INICIO
     ============================================================ -->
<section id="view-inicio" class="erp-view active" aria-label="Inicio">

    <div class="welcome-banner">
        <div>
            <h2>¡Bienvenido, <?php echo esc_html( $args['user_name'] ); ?>!</h2>
            <p>Panel de gestión de viáticos — Fundación Romero</p>
        </div>
        <button class="btn btn-white" onclick="ViaticosApp.navigate('view-solicitudes')" id="btn-inicio-nueva">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nueva Solicitud
        </button>
    </div>

    <!-- KPIs -->
    <div class="stat-grid" id="inicio-stats">
        <div class="stat-card">
            <div class="stat-icon orange">
                <svg viewBox="0 0 24 24" fill="#da5b3e"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-total">—</div>
                <div class="stat-label">Total Solicitudes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" fill="#D97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-pendiente">—</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="#059669"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-aprobada">—</div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24" fill="#DC2626"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-rechazada">—</div>
                <div class="stat-label">Rechazadas</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">Actividad Reciente</div>
                <div class="card-header-subtitle">Últimas 5 solicitudes registradas</div>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="ViaticosApp.navigate('view-solicitudes')" id="btn-ver-todas">Ver todas</button>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Actividad reciente">
                <thead>
                    <tr>
                        <th>ID</th><th>Fecha Viaje</th><th>Monto</th><th>CECO/Proyecto</th><th>Estado solicitud</th><th>Estado rendición</th>
                    </tr>
                </thead>
                <tbody id="inicio-recent-tbody">
                    <tr><td colspan="6"><div class="table-loading"><div class="spinner"></div> Cargando...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-inicio -->


<!-- ============================================================
     VISTA: MIS SOLICITUDES
     ============================================================ -->
<section id="view-solicitudes" class="erp-view" aria-label="Mis Solicitudes">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Solicitudes</h1>
            <p>Gestión de solicitudes de viáticos</p>
        </div>
        <button class="btn btn-primary" id="btn-abrir-nueva-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nueva Solicitud
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">Listado de Solicitudes</div>
            <button class="btn btn-ghost btn-sm" id="btn-refrescar-solicitudes" title="Actualizar tabla">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Actualizar
            </button>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Mis solicitudes de viáticos">
                <thead>
                    <tr>
                        <th>ID</th><th>Fecha Viaje</th><th>Monto Solicitado</th>
                        <th>CECO / Proyecto</th><th>Estado solicitud</th><th>Estado rendición</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes-tbody">
                    <tr><td colspan="7"><div class="table-loading"><div class="spinner"></div> Cargando solicitudes...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-solicitudes -->


<!-- ============================================================
     VISTA: DETALLE DE SOLICITUD / RENDICION
     ============================================================ -->
<section id="view-detalle-solicitud" class="erp-view" aria-label="Detalle de Solicitud">

    <!-- Topbar liviana — los CTAs viven en el rail derecho -->
    <div class="rd-topbar">
        <button class="rd-back-btn" id="btn-volver-detalle-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver
        </button>
        <button class="btn btn-outline btn-sm" id="btn-detalle-view-liquidacion" style="display:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            Ver Liquidación
        </button>
    </div>

    <!-- Contenido dinámico generado por renderDetalleSolicitudContent() -->
    <div id="detalle-view-content"></div>
    <div id="detalle-view-error" class="erp-alert-error"></div>

</section><!-- /#view-detalle-solicitud -->


<!-- ============================================================
     VISTA: MIS RENDICIONES (Card list)
     ============================================================ -->
<section id="view-rendiciones" class="erp-view" aria-label="Mis Rendiciones">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Rendiciones</h1>
            <p>Resumen de expedientes por solicitud aprobada</p>
        </div>
    </div>

    <!-- El JS inyecta tarjetas aquí -->
    <div id="rendiciones-list-container">
        <div class="rd-list-loading"><div class="spinner"></div> Cargando rendiciones...</div>
    </div>

</section><!-- /#view-rendiciones -->

<!-- ============================================================
     VIEW: LIQUIDACIÓN (read-only document)
     ============================================================ -->
<section id="view-liquidacion" class="erp-view" aria-label="Liquidación de Rendición">
    <div class="liq-view-toolbar">
        <button class="liq-back-btn" id="btn-liq-volver" type="button">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver al detalle
        </button>
        <div class="liq-actions">
            <button type="button" class="btn btn-secondary btn-sm" id="btn-liq-exportar" title="Exportar PDF (próximamente)" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5z"/></svg>
                Exportar
            </button>
        </div>
    </div>
    <div id="liq-doc-container">
        <div class="liq-doc-empty"><div class="spinner"></div> Cargando liquidación…</div>
    </div>
</section><!-- /#view-liquidacion -->


<!-- ============================================================
     MODAL: NUEVA SOLICITUD
     ============================================================ -->
<div class="modal-overlay" id="modal-nueva-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-nueva-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-nueva-titulo">Nueva Solicitud de Viático</h2>
                <p>Complete todos los campos para enviar su solicitud.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-nueva" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-nueva-solicitud" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ns-dni">DNI del Colaborador <span class="required">*</span></label>
                        <input type="text" id="ns-dni" name="dni" class="form-control" placeholder="Ej: 12345678" maxlength="8" pattern="\d{8}" required inputmode="numeric" autocomplete="off">
                        <span class="form-error" id="err-ns-dni">El DNI debe tener exactamente 8 dígitos numéricos.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ns-monto" name="monto" class="form-control" placeholder="0.00" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" id="err-ns-monto">Ingrese un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input type="date" id="ns-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-ns-fecha">Seleccione una fecha válida.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ns-ceco" name="ceco" class="form-control" placeholder="Ej: CC-001 / ADMINISTRACIÓN" required autocomplete="off">
                        <span class="form-error" id="err-ns-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea id="ns-motivo" name="motivo" class="form-control" placeholder="Describa el objetivo o motivo del viaje..." required rows="4"></textarea>
                        <span class="form-error" id="err-ns-motivo">Describa el motivo del viaje.</span>
                    </div>
                </div>
                <div id="nueva-solicitud-error" class="erp-alert-error"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-nueva">Cancelar</button>
            <button type="submit" form="form-nueva-solicitud" class="btn btn-primary" id="btn-submit-nueva-solicitud">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Enviar Solicitud
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: EDITAR SOLICITUD (Observada)
     ============================================================ -->
<div class="modal-overlay" id="modal-editar-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-editar-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-editar-titulo">Editar Solicitud <span id="editar-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p>Esta solicitud fue observada. Corrija los datos y reenvíe.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-editar" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-editar-solicitud" novalidate>
                <input type="hidden" id="ed-post-id" name="post_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ed-dni">DNI del Colaborador <span class="required">*</span></label>
                        <input type="text" id="ed-dni" name="dni" class="form-control" maxlength="8" pattern="\d{8}" required inputmode="numeric">
                        <span class="form-error" id="err-ed-dni">El DNI debe tener exactamente 8 dígitos numéricos.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ed-monto" name="monto" class="form-control" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" id="err-ed-monto">Ingrese un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input type="date" id="ed-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-ed-fecha">Seleccione una fecha válida.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ed-ceco" name="ceco" class="form-control" required>
                        <span class="form-error" id="err-ed-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ed-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea id="ed-motivo" name="motivo" class="form-control" required rows="4"></textarea>
                        <span class="form-error" id="err-ed-motivo">Describa el motivo del viaje.</span>
                    </div>
                </div>
                <div id="editar-solicitud-error" class="erp-alert-error"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-editar">Cancelar</button>
            <button type="submit" form="form-editar-solicitud" class="btn btn-primary" id="btn-submit-editar-solicitud">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.11.0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1.0 2-.9 2-2V7l-4-4zm-5 16c-1.66.0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                Guardar Cambios
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: DETALLE DE SOLICITUD / RENDICIÓN
     ============================================================ -->
<div class="modal-overlay" id="modal-detalle-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-detalle-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-detalle-titulo">Solicitud <span id="detalle-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p id="detalle-sol-subtitulo">Revisa el detalle de la solicitud y gestiona su rendición.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-detalle" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="detalle-solicitud-content">
                <div class="table-loading"><div class="spinner"></div> Cargando detalle...</div>
            </div>
            <div id="detalle-solicitud-error" class="erp-alert-error"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-detalle">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btn-detalle-agregar-gasto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Agregar gasto
            </button>
            <button type="button" class="btn btn-success" id="btn-detalle-finalizar-rendicion">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Finalizar rendición
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: RENDIR GASTO
     ============================================================ -->
<div class="modal-overlay" id="modal-rendir-gasto" role="dialog" aria-modal="true" aria-labelledby="modal-rendir-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-rendir-titulo">Rendir Gasto</h2>
                <p>Registre un comprobante de gasto para la solicitud <strong id="rendir-sol-ref"></strong>.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-rendir" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-rendir-gasto" novalidate>
                <input type="hidden" id="rg-id-solicitud" name="id_solicitud">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="rg-tipo">Tipo de Documento <span class="required">*</span></label>
                        <select id="rg-tipo" name="tipo" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <option value="movilidad">Movilidad</option>
                            <option value="vale_caja">Vale de Caja</option>
                            <option value="factura">Factura</option>
                            <option value="boleta">Boleta</option>
                            <option value="rxh">RxH</option>
                        </select>
                        <span class="form-error" id="err-rg-tipo">Seleccione un tipo de rendición.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-fecha">Fecha de Emisión <span class="required">*</span></label>
                        <input type="date" id="rg-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-rg-fecha">Seleccione la fecha de emisión.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-importe">Importe <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="rg-importe" name="importe" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                        <span class="form-error" id="err-rg-importe">Ingrese un importe mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group" data-rendir-group="documento">
                        <label class="form-label" for="rg-ruc">RUC del Proveedor <span class="required">*</span></label>
                        <input type="text" id="rg-ruc" name="ruc" class="form-control" maxlength="11" placeholder="Ej: 20123456789" inputmode="numeric">
                        <span class="form-error" id="err-rg-ruc">Ingrese el RUC del proveedor.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-razon">Razón Social</label>
                        <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                        <span class="form-error" id="err-rg-razon">Ingrese la razón social.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-nro-comprobante">N° Comprobante</label>
                        <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                        <span class="form-error" id="err-rg-nro-comprobante">Ingrese el número de comprobante.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="rg-cuenta">Cuenta Contable</label>
                        <input type="text" id="rg-cuenta" name="cuenta_contable" class="form-control" placeholder="Ej: 63.1.1" required>
                        <span class="form-error" id="err-rg-cuenta">Ingrese la cuenta contable.</span>
                    </div>
                    <div class="form-group col-full" id="rg-group-concepto">
                        <label class="form-label" for="rg-concepto">Descripción / Concepto</label>
                        <textarea id="rg-concepto" name="descripcion_concepto" class="form-control" placeholder="Describa el concepto del gasto..." rows="3"></textarea>
                    </div>
                    <div class="form-group" id="rg-group-motivo">
                        <label class="form-label" for="rg-motivo">Motivo de Movilidad</label>
                        <textarea id="rg-motivo" name="motivo_movilidad" class="form-control" placeholder="Indique el motivo del traslado..." rows="3"></textarea>
                        <span class="form-error" id="err-rg-motivo">Ingrese el motivo de movilidad.</span>
                    </div>
                    <div class="form-group" id="rg-group-destino">
                        <label class="form-label" for="rg-destino">Destino de Movilidad</label>
                        <input type="text" id="rg-destino" name="destino_movilidad" class="form-control" placeholder="Ej: Oficina central / cliente / sede">
                        <span class="form-error" id="err-rg-destino">Ingrese el destino de movilidad.</span>
                    </div>
                    <div class="form-group col-full" id="rg-group-ceco-oi">
                        <label class="form-label" for="rg-ceco-oi">CECO / OI</label>
                        <input type="text" id="rg-ceco-oi" name="ceco_oi" class="form-control" placeholder="Ej: CC-001 / OI-123">
                        <span class="form-error" id="err-rg-ceco-oi">Ingrese el CECO / OI.</span>
                    </div>
                </div>
                <!-- Adjuntos al registrar el gasto -->
                <div class="rg-adj-section">
                    <div class="rg-adj-header">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V6H11v9.5a2.5 2.5 0 005 0V5c0-2.21-1.79-4-4-4S8 2.79 8 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-2.5z"/></svg>
                        Adjuntos <span style="font-weight:400;color:var(--text-muted);">(opcional &mdash; PDF, JPG, PNG)</span>
                    </div>
                    <div class="rg-adj-file-list" id="rg-adj-file-list"></div>
                    <label class="rg-adj-pick-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Agregar archivo
                        <input type="file" id="rg-adj-input" accept=".pdf,.jpg,.jpeg,.png" multiple style="display:none;">
                    </label>
                </div>
                <div id="rendir-gasto-error" class="erp-alert-error"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-rendir">Cancelar</button>
            <button type="submit" form="form-rendir-gasto" class="btn btn-primary" id="btn-submit-rendir-gasto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                Registrar Gasto
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     JAVASCRIPT — ViaticosApp
     ============================================================ -->
<div class="modal-overlay" id="modal-historial-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-historial-titulo">
    <div class="modal modal-lg solv-history-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-historial-titulo">Historial del expediente</h2>
                <p id="detalle-historial-subtitulo">Seguimiento completo de la solicitud y su rendicion.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-historial" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="solv-history-meta" id="detalle-historial-meta"></div>
        <div class="solv-history-body" id="detalle-historial-body">
            <div class="table-loading"><div class="spinner"></div> Cargando historial...</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-historial">Cerrar</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: CONFIRMAR FINALIZAR RENDICIÓN
     ============================================================ -->
<div class="modal-overlay" id="modal-confirmar-finalizar" role="dialog" aria-modal="true" aria-labelledby="modal-confirmar-titulo">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-confirmar-titulo">Finalizar rendición</h2>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-confirmar" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p>¿Confirmas que deseas enviar la rendición al administrador? Una vez finalizada no podrás agregar ni eliminar gastos.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-confirmar">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-confirmar-finalizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Sí, finalizar
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const CONFIG = {
        nonce:   '<?php echo esc_js( $args['rest_nonce'] ); ?>',
        apiBase: '<?php echo esc_js( $args['api_base'] ); ?>',
        profile: {
            name: '<?php echo esc_js( $args['user_name'] ); ?>',
            dni:  '<?php echo esc_js( $args['user_dni'] ); ?>',
        },
    };

    /* ── Utilities ────────────────────────────────────────── */
    const utils    = window.ViaticosUtils;
    const apiFetch = utils.createApiFetch(CONFIG.apiBase, CONFIG.nonce);

    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const renderEstadoBadge    = estadoUI.renderBadgeEstado;
    const renderEstadoGrupo    = estadoUI.renderEstadoGrupo;
    const getLabelEstado       = estadoUI.getLabelEstado;
    const getSolicitudEstado   = estadoUI.getSolicitudEstado;
    const renderSolicitudBadge = estadoUI.renderSolicitudBadge;

    function getRendicionEstado(sol, extra = {}) {
        return estadoUI.resolveEstadoRendicion({
            estadoSolicitud: sol && sol.estado,
            estadoRendicion: sol && sol.estado_rendicion,
            rendicionFinalizada: sol && sol.rendicion_finalizada,
            ...extra,
        });
    }

    const formatMonto = utils.fmtMonto;
    const formatFecha = utils.fmtFecha;

    function renderRendicionBadge(sol, extra = {}) {
        return renderEstadoBadge('rendicion', getRendicionEstado(sol, extra));
    }

    const escHtml          = utils.escapeHtml;
    const showToast        = utils.showToast.bind(utils);
    const setButtonLoading = utils.setButtonLoading;

    /* ── Modal Manager ────────────────────────────────────── */
    const ModalManager = utils.ModalManager;

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        ['modal-nueva-solicitud','modal-editar-solicitud','modal-rendir-gasto','modal-confirmar-finalizar','modal-historial-solicitud'].forEach(id => ModalManager.close(id));
    });

    /* ── Form validation ──────────────────────────────────── */
    function validateField(inputEl, errorEl, customValidator) {
        let isValid = inputEl.checkValidity();
        if (isValid && customValidator) isValid = customValidator(inputEl.value);
        if (!isValid) { errorEl.classList.add('visible'); inputEl.style.borderColor = '#FC8181'; }
        else          { errorEl.classList.remove('visible'); inputEl.style.borderColor = ''; }
        return isValid;
    }

    function resetFormErrors(formEl) {
        formEl.querySelectorAll('.form-error').forEach(el => el.classList.remove('visible'));
        formEl.querySelectorAll('.form-control').forEach(el => (el.style.borderColor = ''));
    }

    function getRendicionTipo() {
        return (document.getElementById('rg-tipo').value || '').trim();
    }

    function updateRendirTipoUI() {
        const tipo = getRendicionTipo();
        const isMovilidad = tipo === 'movilidad';
        const documentoIds = ['rg-ruc', 'rg-razon', 'rg-nro-comprobante', 'rg-group-concepto'];
        const movilidadIds = ['rg-group-motivo', 'rg-group-destino', 'rg-group-ceco-oi'];

        documentoIds.forEach(id => {
            const el = document.getElementById(id);
            const group = el ? (el.classList.contains('form-group') ? el : el.closest('.form-group')) : null;
            if (group) group.style.display = tipo && !isMovilidad ? '' : 'none';
        });

        movilidadIds.forEach(id => {
            const el = document.getElementById(id);
            const group = el ? (el.classList.contains('form-group') ? el : el.closest('.form-group')) : null;
            if (group) group.style.display = isMovilidad ? '' : 'none';
        });
    }

    function prefillNuevaSolicitudForm() {
        const dniEl = document.getElementById('ns-dni');

        if (dniEl && CONFIG.profile.dni) {
            dniEl.value = CONFIG.profile.dni;
        }
    }

    /* ── Data ─────────────────────────────────────────────── */
    let solicitudesCache = [];
    let gastosCache = [];
    let detalleSolicitudId = null;
    let currentRoute = { name: 'inicio', id: null };

    async function fetchSolicitudes() { return await apiFetch('/mis-solicitudes'); }
    async function fetchGastos()      { return await apiFetch('/mis-rendiciones'); }
    async function refreshSolicitudesCache() { solicitudesCache = await fetchSolicitudes(); return solicitudesCache; }
    async function refreshGastosCache() { gastosCache = await fetchGastos(); return gastosCache; }

    function getCurrentRoute() {
        return { ...currentRoute };
    }

    function setCurrentRoute(name, id = null) {
        currentRoute = { name, id: id == null ? null : parseInt(id, 10) };
    }

    function setSectionTitle(routeName) {
        const titleEl = document.getElementById('topbar-section-name');
        if (!titleEl) return;
        const labels = {
            inicio: 'Inicio',
            solicitudes: 'Mis Solicitudes',
            solicitud: 'Detalle de Solicitud',
            rendiciones: 'Mis Rendiciones',
            liquidacion: 'Liquidacion',
        };
        titleEl.textContent = labels[routeName] || 'Viaticos';
    }

    function showView(viewId, routeName = null) {
        document.querySelectorAll('.erp-view').forEach(view => {
            view.classList.toggle('active', view.id === viewId);
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            const target = link.dataset.view;
            const isActive = target === viewId || (
                target === 'view-solicitudes' && (viewId === 'view-detalle-solicitud' || viewId === 'view-liquidacion')
            );
            link.classList.toggle('active', isActive);
        });

        setSectionTitle(routeName || currentRoute.name);
    }

    function normalizeRoute(target) {
        if (typeof target === 'string') {
            const mapping = {
                'view-inicio': { name: 'inicio' },
                'view-solicitudes': { name: 'solicitudes' },
                'view-rendiciones': { name: 'rendiciones' },
                'view-detalle-solicitud': { name: 'solicitud', id: detalleSolicitudId },
                'view-liquidacion': { name: 'liquidacion', id: detalleSolicitudId },
            };
            return mapping[target] || { name: target };
        }
        return target || { name: 'inicio' };
    }

    function renderInicioStats(data) {
        const list = Array.isArray(data) ? data : [];
        const byEstado = list.reduce((acc, sol) => {
            const estado = getSolicitudEstado(sol);
            acc[estado] = (acc[estado] || 0) + 1;
            return acc;
        }, {});

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = String(value);
        };

        setText('kpi-total', list.length);
        setText('kpi-pendiente', byEstado.pendiente || 0);
        setText('kpi-aprobada', byEstado.aprobada || 0);
        setText('kpi-rechazada', byEstado.rechazada || 0);
    }

    function renderInicioRecent(data) {
        const tbody = document.getElementById('inicio-recent-tbody');
        if (!tbody) return;
        const recent = (Array.isArray(data) ? [...data] : [])
            .sort((a, b) => Number(b.id) - Number(a.id))
            .slice(0, 5);

        if (!recent.length) {
            renderTableEmpty(tbody, 6, 'Aun no tienes solicitudes registradas.');
            return;
        }

        tbody.innerHTML = recent.map(sol => `
            <tr>
                <td class="text-muted">#${sol.id}</td>
                <td>${formatFecha(sol.fecha)}</td>
                <td><strong>${formatMonto(sol.monto)}</strong></td>
                <td>${escHtml(sol.ceco || '-')}</td>
                <td>${renderSolicitudBadge(sol)}</td>
                <td>${renderRendicionBadge(sol, { gastos: getGastosBySolicitud(sol.id) })}</td>
            </tr>
        `).join('');
    }

    async function loadInicioView() {
        showView('view-inicio', 'inicio');
        const recentTbody = document.getElementById('inicio-recent-tbody');
        if (recentTbody) renderTableLoading(recentTbody, 6);
        try {
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            renderInicioStats(solicitudesCache);
            renderInicioRecent(solicitudesCache);
        } catch (err) {
            showToast('error', 'Error', err.message);
        }
    }

    async function loadSolicitudesView() {
        showView('view-solicitudes', 'solicitudes');
        const tbody = document.getElementById('solicitudes-tbody');
        if (tbody) renderTableLoading(tbody, 7);
        try {
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            renderSolicitudesTable(solicitudesCache);
        } catch (err) {
            showToast('error', 'Error', err.message);
        }
    }

    async function openDetalleSolicitudView(solicitudId) {
        detalleSolicitudId = parseInt(solicitudId, 10);
        setCurrentRoute('solicitud', detalleSolicitudId);
        showView('view-detalle-solicitud', 'solicitud');

        const contentEl = document.getElementById('detalle-view-content');
        const errorEl = document.getElementById('detalle-view-error');
        if (contentEl) contentEl.innerHTML = `<div class="table-loading"><div class="spinner"></div> Cargando detalle...</div>`;
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }

        try {
            if (!solicitudesCache.length) await refreshSolicitudesCache();
            if (!gastosCache.length) await refreshGastosCache();
            const sol = getSolicitudById(detalleSolicitudId);
            if (!sol) throw new Error('No se encontro la solicitud seleccionada.');
            renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
        } catch (err) {
            if (errorEl) {
                errorEl.textContent = err.message;
                errorEl.style.display = 'block';
            }
            showToast('error', 'Error', err.message);
        }
    }

    function renderLiquidacionView(sol, gastos) {
        const container = document.getElementById('liq-doc-container');
        if (!container || !window.ViaticosLiquidacion) return;

        const liqData = window.ViaticosLiquidacion.buildData(
            {
                id: sol.id,
                monto: sol.monto,
                fecha: sol.fecha,
                motivo: sol.motivo,
                ceco: sol.ceco,
                dni: sol.dni,
                estado_rendicion: getLabelEstado('rendicion', getRendicionEstado(sol, { gastos })),
                rendicion_finalizada: sol.rendicion_finalizada
            },
            gastos,
            {
                colaboradorNombre: CONFIG.profile.name || '',
                fechaRendicion: sol.fecha_creacion || ''
            }
        );

        const wrap = document.createElement('div');
        wrap.style.cssText = 'margin:20px;';
        wrap.innerHTML = window.ViaticosLiquidacion.renderDoc(liqData);
        container.innerHTML = '';
        container.appendChild(wrap);
    }

    async function openLiquidacionView(solicitudId = null) {
        const id = parseInt(solicitudId || detalleSolicitudId || getCurrentRoute().id, 10);
        if (!id) return;
        detalleSolicitudId = id;
        setCurrentRoute('liquidacion', id);
        showView('view-liquidacion', 'liquidacion');

        const container = document.getElementById('liq-doc-container');
        if (container) container.innerHTML = `<div class="liq-doc-empty"><div class="spinner"></div> Cargando liquidacion...</div>`;

        try {
            if (!solicitudesCache.length) await refreshSolicitudesCache();
            if (!gastosCache.length) await refreshGastosCache();
            const sol = getSolicitudById(id);
            if (!sol) throw new Error('No se encontro la solicitud para la liquidacion.');
            renderLiquidacionView(sol, getGastosBySolicitud(id));
        } catch (err) {
            if (container) container.innerHTML = `<div class="liq-doc-empty" style="color:#C53030;">${escHtml(err.message)}</div>`;
            showToast('error', 'Error', err.message);
        }
    }

    async function navigateTo(target) {
        const route = normalizeRoute(target);
        if (!route || !route.name) return;

        switch (route.name) {
            case 'inicio':
                setCurrentRoute('inicio');
                await loadInicioView();
                break;
            case 'solicitudes':
                setCurrentRoute('solicitudes');
                await loadSolicitudesView();
                break;
            case 'rendiciones':
                setCurrentRoute('rendiciones');
                showView('view-rendiciones', 'rendiciones');
                await loadRendicionesView();
                break;
            case 'solicitud':
                await openDetalleSolicitudView(route.id);
                break;
            case 'liquidacion':
                await openLiquidacionView(route.id);
                break;
            default:
                if (route.name.startsWith('view-')) {
                    showView(route.name);
                }
                break;
        }
    }

    function openEditarModal(sol) {
        if (!sol) return;
        const idEl = document.getElementById('editar-sol-id');
        const postIdEl = document.getElementById('ed-post-id');
        const dniEl = document.getElementById('ed-dni');
        const montoEl = document.getElementById('ed-monto');
        const fechaEl = document.getElementById('ed-fecha');
        const cecoEl = document.getElementById('ed-ceco');
        const motivoEl = document.getElementById('ed-motivo');

        if (idEl) idEl.textContent = `#${sol.id}`;
        if (postIdEl) postIdEl.value = sol.id || '';
        if (dniEl) dniEl.value = sol.dni || '';
        if (montoEl) montoEl.value = sol.monto || '';
        if (fechaEl) fechaEl.value = sol.fecha || '';
        if (cecoEl) cecoEl.value = sol.ceco || '';
        if (motivoEl) motivoEl.value = sol.motivo || '';

        const errEl = document.getElementById('editar-solicitud-error');
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
        ModalManager.open('modal-editar-solicitud');
    }

    async function handleNuevaSolicitudSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-nueva-solicitud');
        const errEl = document.getElementById('nueva-solicitud-error');
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }

        const payload = {
            dni: document.getElementById('ns-dni').value.trim(),
            monto: parseFloat(document.getElementById('ns-monto').value),
            fecha: document.getElementById('ns-fecha').value,
            ceco: document.getElementById('ns-ceco').value.trim(),
            motivo: document.getElementById('ns-motivo').value.trim(),
        };

        try {
            setButtonLoading(btn, true);
            await apiFetch('/nueva-solicitud', { method: 'POST', body: JSON.stringify(payload) });
            ModalManager.close('modal-nueva-solicitud');
            document.getElementById('form-nueva-solicitud').reset();
            prefillNuevaSolicitudForm();
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            renderInicioStats(solicitudesCache);
            renderInicioRecent(solicitudesCache);
            if (getCurrentRoute().name === 'solicitudes') renderSolicitudesTable(solicitudesCache);
            showToast('success', 'Solicitud registrada');
            await navigateTo({ name: 'solicitudes' });
        } catch (err) {
            if (errEl) {
                errEl.textContent = err.message;
                errEl.style.display = 'block';
            }
            showToast('error', 'Error', err.message);
        } finally {
            setButtonLoading(btn, false);
        }
    }

    function handleEditarSolicitudSubmit(e) {
        e.preventDefault();
        showToast('error', 'No disponible', 'La edicion de solicitudes observadas no esta conectada en esta version.');
    }

    /* ── Render helpers ───────────────────────────────────── */
    function renderTableEmpty(tbody, colSpan, message = 'No se encontraron registros.') {
        tbody.innerHTML = `<tr><td colspan="${colSpan}"><div class="table-empty">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            <p>${message}</p></div></td></tr>`;
    }

    function renderTableLoading(tbody, colSpan) {
        tbody.innerHTML = `<tr><td colspan="${colSpan}"><div class="table-loading"><div class="spinner"></div>Cargando datos...</div></td></tr>`;
    }

    /* ── Render: solicitudes table ────────────────────────── */
    function renderSolicitudesTable(data) {
        const tbody = document.getElementById('solicitudes-tbody');
        if (!data || !data.length) { renderTableEmpty(tbody, 7, 'Aún no tienes solicitudes registradas.'); return; }
        tbody.innerHTML = data.map(sol => {
            const gastosSolicitud = getGastosBySolicitud(sol.id);
            const acciones = buildAcciones(sol);
            return `<tr>
                <td class="text-muted">#${sol.id}</td>
                <td>${formatFecha(sol.fecha)}</td>
                <td><strong>${formatMonto(sol.monto)}</strong></td>
                <td>${escHtml(sol.ceco)}</td>
                <td>${renderSolicitudBadge(sol)}</td>
                <td>${renderRendicionBadge(sol, { gastos: gastosSolicitud })}</td>
                <td>${acciones}</td>
            </tr>`;
        }).join('');
        attachActionListeners(tbody, data);
    }

    function buildAcciones(sol) {
        const estado = getSolicitudEstado(sol);
        const estadoRend = getRendicionEstado(sol, { gastos: getGastosBySolicitud(sol.id) });
        let btns = '';

        if (estado === 'observada') {
            btns += `<button class="btn btn-secondary btn-sm action-editar" data-id="${sol.id}" title="Editar solicitud observada">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02.0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41.0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Editar</button>`;
        }

        if (estado === 'aprobada') {
            const verSolo = [ 'en_revision', 'aprobada', 'observada', 'rechazada' ].includes(estadoRend);
            if (verSolo) {
                btns += `<button class="btn btn-secondary btn-sm action-ver-rendir" data-id="${sol.id}" title="Ver detalle de la rendición">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76.0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66.0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    Ver</button>`;
            } else {
                btns += `<button class="btn btn-success btn-sm action-ver-rendir" data-id="${sol.id}" title="Rendir gastos de la solicitud">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                    Rendir</button>`;
            }
        }

        return btns || `<span style="color:var(--text-light);font-size:12px;">Sin acciones</span>`;
    }

    function attachActionListeners(tbody, data) {
        tbody.querySelectorAll('.action-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                const sol = data.find(s => s.id === parseInt(btn.dataset.id, 10));
                if (sol) openEditarModal(sol);
            });
        });
        tbody.querySelectorAll('.action-ver-rendir').forEach(btn => {
            btn.addEventListener('click', () => openDetalleSolicitudView(parseInt(btn.dataset.id, 10)));
        });
    }

    function getSolicitudById(id) {
        return solicitudesCache.find(sol => sol.id === parseInt(id, 10)) || null;
    }

    function getGastosBySolicitud(id) {
        return gastosCache
            .filter(gasto => parseInt(gasto.id_solicitud, 10) === parseInt(id, 10))
            .sort((a, b) => Number(b.id) - Number(a.id));
    }

    const gastoUI = window.ViaticosGastoUI;

    /* ── Adjuntos helpers ──────────────────────────────────────────── */
    const escA = utils.escapeHtml;

    const apiFetchForm = utils.createApiFetchForm(CONFIG.apiBase, CONFIG.nonce);

    function renderDetalleSolicitudContent(sol, gastos) {
        const contentEl       = document.getElementById('detalle-view-content');
        const estadoSolicitud = getSolicitudEstado(sol);
        const estadoRend      = getRendicionEstado(sol, { gastos });
        const canAdd          = estadoSolicitud === 'aprobada' && !sol.rendicion_finalizada && !['aprobada', 'rechazada'].includes(estadoRend);
        const canFinalize     = canAdd && gastos.length > 0;
        const canLiquidacion  = !!sol.rendicion_finalizada;

        const accionesHtml =
            '<button type="button" class="btn btn-primary solv-cta-full" id="detalle-sidebar-finalizar-rendicion"' + (canFinalize ? '' : ' style="display:none;"') + '><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Finalizar y enviar</button>' +
            '<button type="button" class="btn btn-secondary solv-cta-full" id="detalle-sidebar-agregar-gasto"' + (canAdd ? '' : ' style="display:none;"') + '><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>Agregar comprobante</button>' +
            '<button type="button" class="btn btn-secondary solv-cta-full" id="detalle-sidebar-ver-liquidacion"' + (canLiquidacion ? '' : ' style="display:none;"') + '><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>Ver liquidacion</button>';

        const { historialHtml } = window.ViaticosDetalleUI.render(contentEl, sol, gastos, { apiFetch, canDelete: true, accionesHtml });

        const historial           = Array.isArray(sol.historial) ? sol.historial : [];
        const historialBodyEl     = document.getElementById('detalle-historial-body');
        const historialMetaEl     = document.getElementById('detalle-historial-meta');
        const historialSubtitleEl = document.getElementById('detalle-historial-subtitulo');
        if (historialBodyEl) {
            historialBodyEl.innerHTML = historial.length
                ? historialHtml
                : '<div class="table-empty" style="padding:32px 20px;"><svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.28;"><path d="M13 3a9 9 0 1 0 8.95 10h-2.02A7 7 0 1 1 13 5v4l5-5-5-5v4z"/></svg><p>No hay movimientos registrados todavia.</p></div>';
        }
        if (historialMetaEl) {
            historialMetaEl.innerHTML =
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Expediente</span><strong>#' + sol.id + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Eventos</span><strong>' + historial.length + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Comprobantes</span><strong>' + gastos.length + '</strong></span>';
        }
        if (historialSubtitleEl) historialSubtitleEl.textContent = 'Seguimiento completo de la solicitud #' + sol.id + ' y su rendicion.';

        const btnAgregar     = contentEl.querySelector('#detalle-sidebar-agregar-gasto');
        const btnFinalizar   = contentEl.querySelector('#detalle-sidebar-finalizar-rendicion');
        const btnLiquidacion = contentEl.querySelector('#detalle-sidebar-ver-liquidacion');
        const actionStack    = contentEl.querySelector('.solv-cta-stack');

        if (btnAgregar)     btnAgregar.addEventListener('click',     () => { if (!sol.rendicion_finalizada) openRendirModal(sol.id); });
        if (btnFinalizar)   btnFinalizar.addEventListener('click',   () => { if (!sol.rendicion_finalizada && gastos.length) ModalManager.open('modal-confirmar-finalizar'); });
        if (btnLiquidacion) btnLiquidacion.addEventListener('click', () => openLiquidacionView(sol.id));
        if (actionStack) {
            const visible = [btnAgregar, btnFinalizar, btnLiquidacion].filter(b => b && b.style.display !== 'none').length;
            actionStack.style.display = visible ? 'flex' : 'none';
        }
        contentEl.querySelectorAll('[data-open-history="1"]').forEach(btn => btn.addEventListener('click', () => ModalManager.open('modal-historial-solicitud')));

        const btnLiqView = document.getElementById('btn-detalle-view-liquidacion');
        if (btnLiqView) btnLiqView.style.display = 'none';
    }

    async function handleFinalizarRendicion() {
        if (!detalleSolicitudId) return;
        try {
            await apiFetch('/finalizar-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: detalleSolicitudId }) });
            await refreshSolicitudesCache(); await refreshGastosCache();
            renderSolicitudesTable(solicitudesCache);
            const sol = getSolicitudById(detalleSolicitudId);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
            showToast('success', 'Finalizado', 'Rendición enviada a revisión.');
        } catch (err) { showToast('error', 'Error', err.message); }
    }

    function openRendirModal(solicitudId) {
        const form = document.getElementById('form-rendir-gasto');
        form.reset(); resetFormErrors(form);
        _adjFiles = []; renderAdjPickList();
        updateRendirTipoUI();
        const idInput = document.getElementById('rg-id-solicitud');
        const refEl = document.getElementById('rendir-sol-ref');
        if (idInput) idInput.value = solicitudId;
        if (refEl) refEl.textContent = `#${solicitudId}`;
        ModalManager.open('modal-rendir-gasto');
    }

    let _adjFiles = [];

    function renderAdjPickList() {
        const listEl = document.getElementById('rg-adj-file-list');
        if (!listEl) return;
        if (!_adjFiles.length) { listEl.innerHTML = ''; return; }
        listEl.innerHTML = _adjFiles.map((f, i) => `
            <div class="rg-adj-file-item">
                <span class="rg-adj-file-name">${escA(f.name)}</span>
                <button type="button" class="rg-adj-remove" data-idx="${i}">X</button>
            </div>`).join('');
        listEl.querySelectorAll('.rg-adj-remove').forEach(btn => {
            btn.addEventListener('click', () => { _adjFiles.splice(parseInt(btn.dataset.idx, 10), 1); renderAdjPickList(); });
        });
    }

    function bindAdjInput() {
        const input = document.getElementById('rg-adj-input');
        if (input) input.addEventListener('change', function() {
            Array.from(this.files).forEach(f => _adjFiles.push(f)); this.value = ''; renderAdjPickList();
        });
    }

    async function handleRendirGastoSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-rendir-gasto');
        setButtonLoading(btn, true);
        try {
            const payload = { id_solicitud: parseInt(document.getElementById('rg-id-solicitud').value, 10), tipo: getRendicionTipo(), fecha: document.getElementById('rg-fecha').value, importe: parseFloat(document.getElementById('rg-importe').value), cuenta_contable: document.getElementById('rg-cuenta').value };
            const res = await apiFetch('/nuevo-gasto', { method:'POST', body: JSON.stringify(payload) });
            ModalManager.close('modal-rendir-gasto');
            await refreshGastosCache();
            renderRendicionesResumen(gastosCache);
            showToast('success', 'Gasto registrado');
        } catch (err) { showToast('error', 'Error', err.message); }
        finally { setButtonLoading(btn, false); }
    }

    function renderRendicionesResumen(data) {
        const container = document.getElementById('rendiciones-list-container');
        if (!container) return;
        const grouped = (data || []).reduce((acc, g) => { (acc[g.id_solicitud] = acc[g.id_solicitud] || []).push(g); return acc; }, {});
        container.innerHTML = Object.keys(grouped).map(id => {
            const total = grouped[id].reduce((sum, g) => sum + parseFloat(g.importe || 0), 0);
            return `<div class="rd-card">Solicitud #${id} - Total: ${formatMonto(total)}</div>`;
        }).join('');
    }

    async function loadRendicionesView() {
        try { await refreshGastosCache(); renderRendicionesResumen(gastosCache); }
        catch (err) { showToast('error', 'Error', err.message); }
    }

    function bindEvents() {
        document.querySelectorAll('.nav-link[data-view]').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                await navigateTo(link.dataset.view);
            });
        });

        const btnAbrirNueva = document.getElementById('btn-abrir-nueva-solicitud');
        if (btnAbrirNueva) {
            btnAbrirNueva.addEventListener('click', () => {
                const form = document.getElementById('form-nueva-solicitud');
                if (form) {
                    form.reset();
                    resetFormErrors(form);
                }
                prefillNuevaSolicitudForm();
                ModalManager.open('modal-nueva-solicitud');
            });
        }

        document.getElementById('btn-cerrar-modal-nueva').addEventListener('click', () => ModalManager.close('modal-nueva-solicitud'));
        document.getElementById('btn-cancelar-modal-nueva').addEventListener('click', () => ModalManager.close('modal-nueva-solicitud'));
        document.getElementById('form-nueva-solicitud').addEventListener('submit', handleNuevaSolicitudSubmit);
        ModalManager.closeOnOverlayClick('modal-nueva-solicitud');

        document.getElementById('btn-cerrar-modal-editar').addEventListener('click', () => ModalManager.close('modal-editar-solicitud'));
        document.getElementById('btn-cancelar-modal-editar').addEventListener('click', () => ModalManager.close('modal-editar-solicitud'));
        document.getElementById('form-editar-solicitud').addEventListener('submit', handleEditarSolicitudSubmit);
        ModalManager.closeOnOverlayClick('modal-editar-solicitud');

        // Volver desde detalle
        document.getElementById('btn-volver-detalle-solicitud').addEventListener('click', () => navigateTo({ name: 'solicitudes' }));

        // Botón liquidación (topbar)
        const liqBtn = document.getElementById('btn-detalle-view-liquidacion');
        if (liqBtn) liqBtn.addEventListener('click', openLiquidacionView);
        const liqBack = document.getElementById('btn-liq-volver');
        if (liqBack) liqBack.addEventListener('click', () => navigateTo({ name: 'solicitud', id: getCurrentRoute().id }));

        // Modal confirmar finalizar rendición
        document.getElementById('btn-cerrar-modal-confirmar').addEventListener('click', () => ModalManager.close('modal-confirmar-finalizar'));
        document.getElementById('btn-cancelar-confirmar').addEventListener('click',     () => ModalManager.close('modal-confirmar-finalizar'));
        document.getElementById('btn-confirmar-finalizar').addEventListener('click', async () => {
            ModalManager.close('modal-confirmar-finalizar');
            await handleFinalizarRendicion();
        });
        ModalManager.closeOnOverlayClick('modal-confirmar-finalizar');

        // Modal rendir gasto
        bindAdjInput();
        document.getElementById('btn-cerrar-modal-rendir').addEventListener('click',   () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('btn-cancelar-modal-rendir').addEventListener('click', () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('form-rendir-gasto').addEventListener('submit', handleRendirGastoSubmit);
        document.getElementById('rg-tipo').addEventListener('change', updateRendirTipoUI);
        ModalManager.closeOnOverlayClick('modal-rendir-gasto');

        document.getElementById('btn-cerrar-modal-historial').addEventListener('click',  () => ModalManager.close('modal-historial-solicitud'));
        document.getElementById('btn-cancelar-modal-historial').addEventListener('click', () => ModalManager.close('modal-historial-solicitud'));
        ModalManager.closeOnOverlayClick('modal-historial-solicitud');

        document.getElementById('btn-refrescar-solicitudes').addEventListener('click', loadSolicitudesView);
    }

    /* ── Init ─────────────────────────────────────────────── */
    function init() { bindEvents(); loadInicioView(); }
    window.ViaticosApp = { navigate: navigateTo };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

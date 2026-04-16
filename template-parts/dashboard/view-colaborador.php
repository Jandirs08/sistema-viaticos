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

<!-- Inject sidebar nav items for the colaborador role -->
<script>
(function () {
    var nav = document.getElementById('sidebar-nav-items');
    if (nav) {
        nav.innerHTML = `
            <li>
                <a href="#" id="nav-inicio" class="nav-link active" data-view="view-inicio">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    Inicio
                </a>
            </li>
            <li>
                <a href="#" id="nav-solicitudes" class="nav-link" data-view="view-solicitudes">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    Mis Solicitudes
                </a>
            </li>`;
    }
    var bc = document.getElementById('topbar-section-name');
    if (bc) bc.textContent = 'Inicio';
})();
</script>


<!-- ============================================================
     VISTA: INICIO
     ============================================================ -->
<section id="view-inicio" class="erp-view active" aria-label="Inicio">

    <div class="welcome-banner">
        <div>
            <h2>¡Bienvenido, <?php echo $args['user_name']; ?>!</h2>
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

    <div class="page-header">
        <div class="page-header-left">
            <h1 id="detalle-view-title">Detalle de Solicitud</h1>
            <p id="detalle-view-subtitle">Revisión y gestión de rendición</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-secondary" id="btn-volver-detalle-solicitud">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                Volver
            </button>
            <button class="btn btn-outline" id="btn-detalle-view-liquidacion" style="display:none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                Ver Liquidación
            </button>
            <button class="btn btn-primary" id="btn-detalle-view-agregar-gasto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Agregar gasto
            </button>
            <button class="btn btn-success" id="btn-detalle-view-finalizar-rendicion">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Finalizar rendicion
            </button>
        </div>
    </div>

    <div id="detalle-view-content">
        <div class="card">
            <div class="modal-body">
                <div class="table-loading"><div class="spinner"></div> Cargando detalle...</div>
            </div>
        </div>
    </div>
    <div id="detalle-view-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>

</section><!-- /#view-detalle-solicitud -->


<!-- ============================================================
     VISTA: MIS RENDICIONES
     ============================================================ -->
<section id="view-rendiciones" class="erp-view" aria-label="Mis Rendiciones">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Rendiciones</h1>
            <p>Gastos registrados por solicitud</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">Gastos Rendidos</div>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Mis gastos rendidos">
                <thead>
                    <tr>
                        <th>ID Gasto</th><th>Solicitud Ref.</th><th>Tipo</th>
                        <th>Fecha Emisión</th><th>Importe</th><th>Proveedor / RUC</th>
                    </tr>
                </thead>
                <tbody id="rendiciones-tbody">
                    <tr><td colspan="6"><div class="table-loading"><div class="spinner"></div> Cargando rendiciones...</div></td></tr>
                </tbody>
            </table>
        </div>
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
                <div id="nueva-solicitud-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
                <div id="editar-solicitud-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
            <div id="detalle-solicitud-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
                        <span class="form-error" id="err-rg-tipo">Seleccione un tipo de rendiciÃ³n.</span>
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
                        <span class="form-error" id="err-rg-razon">Ingrese la razÃ³n social.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-nro-comprobante">N° Comprobante</label>
                        <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                        <span class="form-error" id="err-rg-nro-comprobante">Ingrese el nÃºmero de comprobante.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="rg-cuenta">Cuenta Contable</label>
                        <input type="text" id="rg-cuenta" name="cuenta_contable" class="form-control" placeholder="Ej: 63.1.1" required>
                        <span class="form-error" id="err-rg-cuenta">Ingrese la cuenta contable.</span>
                    </div>
                    <div class="form-group col-full" id="rg-group-concepto">
                        <label class="form-label" for="rg-concepto">DescripciÃ³n / Concepto</label>
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
                        Adjuntos <span style="font-weight:400;color:var(--text-muted);">(opcional &mdash; PDF, JPG, PNG, XML)</span>
                    </div>
                    <div class="rg-adj-file-list" id="rg-adj-file-list"></div>
                    <label class="rg-adj-pick-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Agregar archivo
                        <input type="file" id="rg-adj-input" accept=".pdf,.jpg,.jpeg,.png,.xml" multiple style="display:none;">
                    </label>
                </div>
                <div id="rendir-gasto-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
<script>
(function () {
    'use strict';

    const CONFIG = {
        nonce:   '<?php echo esc_js( $args['rest_nonce'] ); ?>',
        apiBase: '<?php echo esc_js( $args['api_base'] ); ?>',
        profile: {
            dni:  '<?php echo esc_js( $args['user_dni'] ); ?>',
        },
    };

    /* ── Utilities ────────────────────────────────────────── */
    async function apiFetch(endpoint, options = {}) {
        const defaults = { headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CONFIG.nonce } };
        const merged = Object.assign({}, defaults, options);
        if (options.headers) merged.headers = Object.assign({}, defaults.headers, options.headers);
        const response = await fetch(CONFIG.apiBase + endpoint, merged);
        const data     = await response.json();
        if (!response.ok) throw new Error(data.message || `Error ${response.status}`);
        return data;
    }

    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const renderEstadoBadge = estadoUI.renderBadgeEstado;
    const renderEstadoGrupo = estadoUI.renderEstadoGrupo;

    function getSolicitudEstado(sol) {
        return estadoUI.resolveEstadoSolicitud(sol && sol.estado);
    }

    function getRendicionEstado(sol, extra = {}) {
        return estadoUI.resolveEstadoRendicion({
            estadoSolicitud: sol && sol.estado,
            estadoRendicion: sol && sol.estado_rendicion,
            rendicionFinalizada: sol && sol.rendicion_finalizada,
            ...extra,
        });
    }


    function formatMonto(value) {
        const num = parseFloat(value);
        return isNaN(num) ? '—' : 'S/. ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatFecha(isoStr) {
        if (!isoStr) return '—';
        const parts = isoStr.split('-');
        return parts.length !== 3 ? isoStr : `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function renderSolicitudBadge(sol) {
        return renderEstadoBadge('solicitud', getSolicitudEstado(sol));
    }

    function renderRendicionBadge(sol, extra = {}) {
        return renderEstadoBadge('rendicion', getRendicionEstado(sol, extra));
    }


    function escHtml(str) {
        return String(str || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function showToast(type, title, message = '', duration = 4500) {
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41.0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
        };
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span class="toast-icon">${icons[type]}</span><div class="toast-body"><strong>${title}</strong>${message ? `<p>${message}</p>` : ''}</div>`;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; toast.style.transition = 'all .3s ease';
            setTimeout(() => toast.remove(), 320);
        }, duration);
    }

    function setButtonLoading(btn, isLoading) {
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.origText = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.origText || '';
        }
    }

    /* ── Modal Manager ────────────────────────────────────── */
    const ModalManager = {
        open(id)  { const o = document.getElementById(id); if (o) { o.classList.add('open'); document.body.style.overflow = 'hidden'; } },
        close(id) { const o = document.getElementById(id); if (o) { o.classList.remove('open'); document.body.style.overflow = ''; } },
        closeOnOverlayClick(id) {
            const o = document.getElementById(id);
            if (o) o.addEventListener('click', (e) => { if (e.target === o) this.close(id); });
        },
    };

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        ['modal-nueva-solicitud','modal-editar-solicitud','modal-rendir-gasto'].forEach(id => ModalManager.close(id));
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

    async function fetchSolicitudes() { return await apiFetch('/mis-solicitudes'); }
    async function fetchGastos()      { return await apiFetch('/mis-rendiciones'); }
    async function refreshSolicitudesCache() { solicitudesCache = await fetchSolicitudes(); return solicitudesCache; }
    async function refreshGastosCache() { gastosCache = await fetchGastos(); return gastosCache; }

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

        // Botón Editar — solo si la solicitud fue observada por el admin
        if (estado === 'observada') {
            btns += `<button class="btn btn-secondary btn-sm action-editar" data-id="${sol.id}" title="Editar solicitud observada">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02.0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41.0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Editar</button>`;
        }

        // Botón contextual para solicitudes aprobadas
        if (estado === 'aprobada') {
            // caso 1: rendición ya cerrada/evaluada → solo Ver
            const verSolo = [ 'en_revision', 'aprobada', 'observada', 'rechazada' ].includes(estadoRend);
            if (verSolo) {
                btns += `<button class="btn btn-secondary btn-sm action-ver-rendir" data-id="${sol.id}" title="Ver detalle de la rendición">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76.0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66.0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    Ver</button>`;
            } else {
                // caso 2: pendiente de rendir → Rendir
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

    // getRendicionBadgeHTML ya definida arriba (línea ~554) — no se duplica.

    // buildDetalleGasto replaced by shared ViaticosGastoUI.renderGastoItem
    const gastoUI = window.ViaticosGastoUI;

    /* ── Adjuntos helpers ──────────────────────────────────────────── */
    function adjIconClass(mime) {
        if (!mime) return 'file';
        if (mime.includes('pdf')) return 'pdf';
        if (mime.includes('xml')) return 'xml';
        if (mime.includes('image')) return 'img';
        return 'file';
    }
    function adjIconLabel(mime) {
        if (!mime) return 'FILE';
        if (mime.includes('pdf')) return 'PDF';
        if (mime.includes('xml')) return 'XML';
        if (mime.includes('image')) return mime.includes('png') ? 'PNG' : 'JPG';
        return 'FILE';
    }
    function escA(v) {
        return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderAdjuntosList(adjuntos, gastoId, canDelete) {
        if (!adjuntos.length) {
            return '<span style="font-size:12px;color:var(--text-muted);">Sin adjuntos.</span>';
        }
        return adjuntos.map(adj => `
            <div class="gasto-adj-item" data-adj-id="${escA(adj.id)}">
                <div class="gasto-adj-icon ${adjIconClass(adj.mime)}">${adjIconLabel(adj.mime)}</div>
                <span class="gasto-adj-name" title="${escA(adj.name)}">${escA(adj.name)}</span>
                <div class="gasto-adj-actions">
                    <a class="gasto-adj-btn" href="${escA(adj.url)}" target="_blank" rel="noopener">Ver</a>
                    ${canDelete ? `<button class="gasto-adj-btn del js-adj-delete" data-adj-id="${escA(adj.id)}" data-gasto-id="${escA(gastoId)}">Eliminar</button>` : ''}
                </div>
            </div>`).join('');
    }


    async function loadGastoAdjuntos(gastoId, itemEl) {
        const panel = itemEl.querySelector('.gasto-adj-panel[data-adj-gasto-id="' + gastoId + '"]');
        if (!panel || panel.dataset.adjLoaded === '1') return;
        panel.dataset.adjLoaded = '1';

        const listEl = panel.querySelector('.gasto-adj-list');
        listEl.innerHTML = '<span class="gasto-adj-loading">Cargando adjuntos\u2026</span>';
        try {
            const { adjuntos = [] } = await apiFetch('/gasto-adjuntos/' + gastoId);
            if (!adjuntos.length) {
                listEl.innerHTML = '<span class="gasto-adj-empty">Sin adjuntos.</span>';
                return;
            }
            listEl.innerHTML = renderAdjuntosList(adjuntos, gastoId, true);
            listEl.addEventListener('click', async function(e) {
                const btn = e.target.closest('.js-adj-delete');
                if (!btn || btn.disabled) return;
                btn.disabled = true; btn.textContent = '\u2026';
                try {
                    await apiFetch('/gasto-adjunto/' + btn.dataset.adjId, { method: 'DELETE' });
                    panel.dataset.adjLoaded = '0';
                    await loadGastoAdjuntos(gastoId, itemEl);
                } catch(err) {
                    btn.textContent = 'Error';
                    setTimeout(() => { btn.textContent = 'Eliminar'; btn.disabled = false; }, 2000);
                }
            }, { once: true });
        } catch (err) {
            listEl.innerHTML = '<span class="gasto-adj-empty" style="color:#C53030;">Error al cargar adjuntos.</span>';
        }
    }

    // apiFetchForm: multipart POST (no JSON header)
    async function apiFetchForm(endpoint, formData) {
        const url = CONFIG.apiBase.replace(/\/$/, '') + endpoint;
        const resp = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-WP-Nonce': CONFIG.nonce },
            body: formData,
        });
        const json = await resp.json();
        if (!resp.ok || !json.success) throw new Error(json.message || 'Error en la solicitud.');
        return json;
    }

    /* ── Liquidación (Fase 13 base) ─────────────────────────────────── */


    function renderDetalleSolicitudContent(sol, gastos) {
        const contentEl = document.getElementById('detalle-view-content');
        const totalSolicitado = parseFloat(sol.monto) || 0;
        const totalRendido = gastos.reduce((sum, gasto) => sum + (parseFloat(gasto.importe) || 0), 0);
        const saldo = totalSolicitado - totalRendido;
        const estadoSolicitud = getSolicitudEstado(sol);
        const estadoRend = getRendicionEstado(sol, { gastos });
        const saldoNegativo = saldo < 0;
        const historial = Array.isArray(sol.historial) ? sol.historial : [];
        const historialHtml = timelineUI.renderTimeline(historial);

        const gastosHtml = gastos.length
            ? `<div class="gasto-acc-list" id="colab-gastos-acc">${
                gastos.map((g, i) => gastoUI.renderGastoItem(g, `col-${sol.id}-${i}`)).join('')
              }</div>`
            : `<div class="table-empty" style="padding:32px 20px;">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.3;"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                <p>Aún no registraste gastos para esta solicitud.</p>
            </div>`;

        document.getElementById('detalle-view-title').textContent = `Solicitud #${sol.id}`;

        // Alerta de estado si aplica
        const alertaBanner = (() => {
            if (!['observada', 'rechazada', 'aprobada'].includes(estadoRend)) return '';
            const configs = {
                observada: {
                    clase: 'estado-alerta-observada',
                    icono: '<path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>',
                    color: '#D97706',
                    titulo: 'Rendición observada',
                    mensaje: 'El administrador revisó tu rendición y tiene observaciones. Espera instrucciones.'
                },
                rechazada: {
                    clase: 'estado-alerta-rechazada',
                    icono: '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>',
                    color: '#DC2626',
                    titulo: 'Rendición rechazada',
                    mensaje: 'El administrador rechazó tu rendición. Comunícate con el área de finanzas.'
                },
                aprobada: {
                    clase: 'estado-alerta-aprobada',
                    icono: '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>',
                    color: '#059669',
                    titulo: 'Rendición aprobada',
                    mensaje: 'Tu rendición fue revisada y aprobada correctamente.'
                }
            };
            const cfg = configs[estadoRend];
            return `<div class="estado-alerta ${cfg.clase}">
                <svg class="estado-alerta-icon" viewBox="0 0 24 24" fill="${cfg.color}">${cfg.icono}</svg>
                <div class="estado-alerta-content">
                    <strong>${cfg.titulo}</strong>
                    <p>${cfg.mensaje}</p>
                </div>
            </div>`;
        })();

        contentEl.innerHTML = `
            ${alertaBanner}

            <!-- SECCIÓN: Estados -->
            <div class="section-block">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        Estados
                    </div>
                </div>
                <div class="section-body">
                    <div class="estados-row">
                        <div class="estado-panel estado-panel-solicitud">
                            <div class="estado-panel-label">Estado de Solicitud</div>
                            <div class="estado-panel-badge">${renderSolicitudBadge(sol)}</div>
                        </div>
                        <div class="estado-panel estado-panel-rendicion">
                            <div class="estado-panel-label">Estado de Rendición</div>
                            <div class="estado-panel-badge">${renderRendicionBadge(sol, { gastos })}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Resumen Económico -->
            <div class="section-block">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        Resumen Económico
                    </div>
                </div>
                <div class="section-body">
                    <div class="resumen-economico">
                        <div class="resumen-card monto-solicitado">
                            <div class="resumen-card-label">Monto Solicitado</div>
                            <div class="resumen-card-value">${formatMonto(totalSolicitado)}</div>
                        </div>
                        <div class="resumen-card total-rendido">
                            <div class="resumen-card-label">Total Rendido</div>
                            <div class="resumen-card-value">${formatMonto(totalRendido)}</div>
                        </div>
                        <div class="resumen-card ${saldoNegativo ? 'saldo-negativo' : 'saldo'}">
                            <div class="resumen-card-label">Saldo</div>
                            <div class="resumen-card-value ${saldoNegativo ? 'saldo-negativo' : 'saldo'}">${formatMonto(saldo)}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Datos Generales -->
            <div class="section-block">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
                        Datos Generales
                    </div>
                </div>
                <div class="section-body">
                    <div class="datos-grid">
                        <div class="dato-item">
                            <div class="dato-label">Fecha de Viaje</div>
                            <div class="dato-value">${formatFecha(sol.fecha)}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">CECO / Proyecto</div>
                            <div class="dato-value">${escHtml(sol.ceco || '—')}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">DNI Colaborador</div>
                            <div class="dato-value">${escHtml(sol.dni || '—')}</div>
                        </div>
                        <div class="dato-motivo">
                            <div class="dato-label">Motivo del Viaje</div>
                            <div class="dato-value muted">${escHtml(sol.motivo || '—')}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Historial -->
            <div class="section-block">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 3a9 9 0 1 0 8.95 10H20a7 7 0 1 1-2.05-4.95L16 10h6V4l-2.64 2.64A8.96 8.96 0 0 0 13 3zm-1 5h2v5l4.25 2.52-1 1.68L12 14V8z"/></svg>
                        Historial
                    </div>
                    <div class="section-header-subtitle">${historial.length} evento(s)</div>
                </div>
                <div class="section-body">
                    ${historialHtml}
                </div>
            </div>

            <div class="section-block">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        Gastos Asociados
                    </div>
                    <div class="section-header-subtitle">${gastos.length} registro(s) · Total: ${formatMonto(totalRendido)}</div>
                </div>
                <div class="section-body" style="padding:16px 20px;">
                    ${gastosHtml}
                </div>
            </div>
        `;

        // Bind accordion for gastos + adjuntos lazy-load.
        const accContainer = contentEl.querySelector('#colab-gastos-acc');
        if (accContainer) {
            gastoUI.bindAccordionList(accContainer, {
                onOpen: function(itemEl, gastoId) {
                    if (gastoId) loadGastoAdjuntos(gastoId, itemEl);
                }
            });
        }



        
        const btnLiquidacion = document.getElementById('btn-detalle-view-liquidacion');
        if (btnLiquidacion) {
            btnLiquidacion.style.display = sol.rendicion_finalizada ? 'inline-flex' : 'none';
        }
        const btnAgregar = document.getElementById('btn-detalle-view-agregar-gasto');
        const btnFinalizar = document.getElementById('btn-detalle-view-finalizar-rendicion');
        btnAgregar.disabled = !!sol.rendicion_finalizada;
        btnFinalizar.disabled = !!sol.rendicion_finalizada || !gastos.length;

        if (sol.rendicion_finalizada) {
            btnAgregar.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg> Rendición cerrada';
            btnFinalizar.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> Rendición finalizada';
        } else {
            btnAgregar.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg> Agregar gasto';
            btnFinalizar.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> Finalizar rendición';
        }
    }

    
    function openLiquidacionView() {
        if (!detalleSolicitudId) return;
        const sol = getSolicitudById(detalleSolicitudId);
        const gastos = getGastosBySolicitud(detalleSolicitudId);
        if (!sol) return;

        const data = window.ViaticosLiquidacion.buildData(sol, gastos, {
            colaboradorNombre: sol.colaborador || '',
            area:  sol.area || '',
        });
        const docEl = document.getElementById('liq-doc-container');
        if (docEl) docEl.innerHTML = window.ViaticosLiquidacion.renderDoc(data);
        navigateTo('view-liquidacion');
    }

    async function openDetalleSolicitudView(solicitudId) {
        detalleSolicitudId = parseInt(solicitudId, 10);
        document.getElementById('detalle-view-error').style.display = 'none';
        document.getElementById('detalle-view-content').innerHTML = '<div class="card"><div class="modal-body"><div class="table-loading"><div class="spinner"></div> Cargando detalle...</div></div></div>';
        navigateTo('view-detalle-solicitud');

        try {
            let sol = getSolicitudById(detalleSolicitudId);
            if (!sol) {
                await refreshSolicitudesCache();
                sol = getSolicitudById(detalleSolicitudId);
            }

            if (!sol) throw new Error('No se encontró la solicitud seleccionada.');

            await refreshGastosCache();
            renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
        } catch (err) {
            const errEl = document.getElementById('detalle-view-error');
            errEl.textContent = err.message || 'No se pudo cargar el detalle de la solicitud.';
            errEl.style.display = 'block';
            document.getElementById('detalle-view-content').innerHTML = '';
        }
    }

    async function loadDetalleSolicitudView() {
        if (!detalleSolicitudId) {
            navigateTo('view-solicitudes');
            return;
        }

        document.getElementById('detalle-view-error').style.display = 'none';
        document.getElementById('detalle-view-content').innerHTML = '<div class="card"><div class="modal-body"><div class="table-loading"><div class="spinner"></div> Cargando detalle...</div></div></div>';

        try {
            await refreshSolicitudesCache();
            const sol = getSolicitudById(detalleSolicitudId);

            if (!sol) throw new Error('No se encontrÃ³ la solicitud seleccionada.');

            await refreshGastosCache();
            renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
        } catch (err) {
            const errEl = document.getElementById('detalle-view-error');
            errEl.textContent = err.message || 'No se pudo cargar el detalle de la solicitud.';
            errEl.style.display = 'block';
            document.getElementById('detalle-view-content').innerHTML = '';
        }
    }

    function closeDetalleSolicitudView() {
        detalleSolicitudId = null;
        navigateTo('view-solicitudes');
    }

    /* ── Render: inicio recent + KPIs ────────────────────── */
    function renderInicioRecent(data) {
        const tbody = document.getElementById('inicio-recent-tbody');
        const kpis  = { total: data.length, pendiente: 0, aprobada: 0, rechazada: 0 };
        data.forEach(s => { const e = getSolicitudEstado(s); if (e in kpis) kpis[e]++; });
        document.getElementById('kpi-total').textContent     = kpis.total;
        document.getElementById('kpi-pendiente').textContent = kpis.pendiente;
        document.getElementById('kpi-aprobada').textContent  = kpis.aprobada;
        document.getElementById('kpi-rechazada').textContent = kpis.rechazada;

        const recent = data.slice(0, 5);
        if (!recent.length) { renderTableEmpty(tbody, 6, 'Aún no tienes actividad registrada.'); return; }
        tbody.innerHTML = recent.map(sol => `<tr>
            <td class="text-muted">#${sol.id}</td>
            <td>${formatFecha(sol.fecha)}</td>
            <td><strong>${formatMonto(sol.monto)}</strong></td>
            <td>${escHtml(sol.ceco)}</td>
            <td>${renderSolicitudBadge(sol)}</td>
            <td>${renderRendicionBadge(sol, { gastos: getGastosBySolicitud(sol.id) })}</td>
        </tr>`).join('');
    }

    /* ── Render: rendiciones table ────────────────────────── */
    function renderRendicionesTable(data) {
        const tbody = document.getElementById('rendiciones-tbody');
        if (!data || !data.length) { renderTableEmpty(tbody, 6, 'Aún no tienes gastos rendidos registrados.'); return; }
        const tipoLabel = { movilidad:'Movilidad', vale_caja:'Vale de Caja', factura:'Factura', boleta:'Boleta', rxh:'RxH' };
        tbody.innerHTML = data.map(g => `<tr>
            <td class="text-muted">#${g.id}</td>
            <td>${g.id_solicitud ? `<span class="badge-ref">#${g.id_solicitud}</span>` : '—'}</td>
            <td>${tipoLabel[g.tipo] || g.tipo || '—'}</td>
            <td>${formatFecha(g.fecha)}</td>
            <td><strong>${formatMonto(g.importe)}</strong></td>
            <td>${escHtml(g.ruc)} ${g.razon && g.razon !== '—' ? `· ${escHtml(g.razon)}` : ''}</td>
        </tr>`).join('');
    }

    /* ── Navigation ───────────────────────────────────────── */
    function navigateTo(viewId) {
        document.querySelectorAll('.erp-view').forEach(v => v.classList.remove('active'));
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');
        document.querySelectorAll('.nav-link').forEach(a => a.classList.toggle('active', a.dataset.view === viewId));
        const names = { 'view-inicio':'Inicio', 'view-solicitudes':'Mis Solicitudes', 'view-detalle-solicitud':'Detalle de Solicitud', 'view-rendiciones':'Mis Rendiciones' };
        const nameEl = document.getElementById('topbar-section-name');
        if (nameEl) nameEl.textContent = names[viewId] || '';
        if (viewId === 'view-solicitudes') loadSolicitudesView();
        if (viewId === 'view-rendiciones') loadRendicionesView();
    }

    /* ── Load per view ────────────────────────────────────── */
    async function loadInicioView() {
        const tbody = document.getElementById('inicio-recent-tbody');
        renderTableLoading(tbody, 6);
        try { await Promise.all([ refreshSolicitudesCache(), refreshGastosCache() ]); renderInicioRecent(solicitudesCache); }
        catch (err) { console.error('[ViaticosApp]', err); renderTableEmpty(tbody, 6, 'Error al cargar datos.'); showToast('error', 'Error', err.message); }
    }

    async function loadSolicitudesView() {
        const tbody = document.getElementById('solicitudes-tbody');
        renderTableLoading(tbody, 7);
        try { await Promise.all([ refreshSolicitudesCache(), refreshGastosCache() ]); renderSolicitudesTable(solicitudesCache); }
        catch (err) { console.error('[ViaticosApp]', err); renderTableEmpty(tbody, 7, 'Error al cargar solicitudes.'); showToast('error', 'Error', err.message); }
    }

    function renderRendicionesResumen(data) {
        const tbody = document.getElementById('rendiciones-tbody');
        if (!data || !data.length) { renderTableEmpty(tbody, 6, 'Aún no tienes gastos rendidos registrados.'); return; }

        const grouped = data.reduce((acc, gasto) => {
            const key = gasto.id_solicitud || 0;
            if (!acc[key]) acc[key] = [];
            acc[key].push(gasto);
            return acc;
        }, {});

        const sortedIds = Object.keys(grouped).sort((a, b) => Number(b) - Number(a));

        tbody.innerHTML = sortedIds.map(id => {
            const gastos = grouped[id];
            const solicitud = getSolicitudById(id);
            const total = gastos.reduce((sum, g) => sum + (parseFloat(g.importe) || 0), 0);
            const accId = `rend-acc-sol-${escHtml(id)}`;
            const details = `<div class="gasto-acc-list" id="${accId}">${
                gastos.map((g, i) => gastoUI.renderGastoItem(g, `rend-${id}-${i}`)).join('')
            }</div>`;

            const estadosRow = solicitud ? `
                <div class="estados-row" style="padding:0 20px 16px;">
                    <div class="estado-panel estado-panel-solicitud">
                        <div class="estado-panel-label">Estado de Solicitud</div>
                        <div class="estado-panel-badge">${renderSolicitudBadge(solicitud)}</div>
                    </div>
                    <div class="estado-panel estado-panel-rendicion">
                        <div class="estado-panel-label">Estado de Rendición</div>
                        <div class="estado-panel-badge">${renderRendicionBadge(solicitud, { gastos })}</div>
                    </div>
                </div>
            ` : '';

            return `<tr>
                <td colspan="6" style="padding:12px 16px;">
                    <div class="section-block" style="margin:0;">
                        <div class="section-header">
                            <div class="section-header-title">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
                                Solicitud #${escHtml(id)}
                            </div>
                            <div class="section-header-subtitle">${formatMonto(total)} · ${gastos.length} gasto(s)</div>
                        </div>
                        ${estadosRow}
                        <div class="section-body" style="padding:16px 20px;">
                            ${details}
                        </div>
                    </div>
                </td>
            </tr>`;
        }).join('');

        sortedIds.forEach(id => {
            const el = document.getElementById(`rend-acc-sol-${id}`);
            if (el) gastoUI.bindAccordionList(el);
        });
    }

    async function loadRendicionesView() {
        const tbody = document.getElementById('rendiciones-tbody');
        renderTableLoading(tbody, 6);
        try { await Promise.all([ refreshSolicitudesCache(), refreshGastosCache() ]); renderRendicionesResumen(gastosCache); }
        catch (err) { console.error('[ViaticosApp]', err); renderTableEmpty(tbody, 6, 'Error al cargar rendiciones.'); showToast('error', 'Error', err.message); }
    }

    /* ── Modal: Nueva Solicitud ───────────────────────────── */
    function openNuevaSolicitudModal() {
        const form = document.getElementById('form-nueva-solicitud');
        form.reset(); resetFormErrors(form);
        prefillNuevaSolicitudForm();
        document.getElementById('nueva-solicitud-error').style.display = 'none';
        ModalManager.open('modal-nueva-solicitud');
        if (CONFIG.profile.dni) {
            document.getElementById('ns-monto').focus();
        } else {
            document.getElementById('ns-dni').focus();
        }
    }

    async function handleNuevaSolicitudSubmit(e) {
        e.preventDefault();
        const btn   = document.getElementById('btn-submit-nueva-solicitud');
        const errEl = document.getElementById('nueva-solicitud-error');
        const dniEl = document.getElementById('ns-dni'), montoEl = document.getElementById('ns-monto'),
              fechaEl = document.getElementById('ns-fecha'), cecoEl = document.getElementById('ns-ceco'),
              motivoEl = document.getElementById('ns-motivo');
        const v1 = validateField(dniEl,    document.getElementById('err-ns-dni'),    v => /^\d{8}$/.test(v));
        const v2 = validateField(montoEl,  document.getElementById('err-ns-monto'),  v => parseFloat(v) > 0);
        const v3 = validateField(fechaEl,  document.getElementById('err-ns-fecha'),  v => !!v);
        const v4 = validateField(cecoEl,   document.getElementById('err-ns-ceco'),   v => v.trim().length > 0);
        const v5 = validateField(motivoEl, document.getElementById('err-ns-motivo'), v => v.trim().length > 0);
        if (!v1||!v2||!v3||!v4||!v5) return;
        errEl.style.display = 'none';
        setButtonLoading(btn, true);
        try {
            await apiFetch('/nueva-solicitud', { method:'POST', body: JSON.stringify({
                dni: dniEl.value.trim(), monto: parseFloat(montoEl.value),
                fecha: fechaEl.value, ceco: cecoEl.value.trim(), motivo: motivoEl.value.trim(),
            }) });
            ModalManager.close('modal-nueva-solicitud');
            showToast('success', 'Solicitud enviada', 'Tu solicitud de viático fue registrada correctamente.');
            await loadSolicitudesView(); await loadInicioView();
        } catch (err) {
            errEl.textContent = err.message || 'Ocurrió un error. Intente de nuevo.'; errEl.style.display = 'block';
        } finally { setButtonLoading(btn, false); }
    }

    /* ── Modal: Editar Solicitud ──────────────────────────── */
    function openEditarModal(sol) {
        const form = document.getElementById('form-editar-solicitud');
        form.reset(); resetFormErrors(form);
        document.getElementById('editar-solicitud-error').style.display = 'none';
        document.getElementById('editar-sol-id').textContent = `#${sol.id}`;
        document.getElementById('ed-post-id').value = sol.id;
        document.getElementById('ed-dni').value   = sol.dni;
        document.getElementById('ed-monto').value = sol.monto;
        document.getElementById('ed-fecha').value = sol.fecha;
        document.getElementById('ed-ceco').value  = sol.ceco !== '—' ? sol.ceco : '';
        document.getElementById('ed-motivo').value = sol.motivo;
        ModalManager.open('modal-editar-solicitud');
        document.getElementById('ed-dni').focus();
    }

    async function handleEditarSolicitudSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-editar-solicitud');
        const errEl = document.getElementById('editar-solicitud-error');
        const postId = document.getElementById('ed-post-id').value;
        const dniEl = document.getElementById('ed-dni'), montoEl = document.getElementById('ed-monto'),
              fechaEl = document.getElementById('ed-fecha'), cecoEl = document.getElementById('ed-ceco'),
              motivoEl = document.getElementById('ed-motivo');
        const v1 = validateField(dniEl,    document.getElementById('err-ed-dni'),    v => /^\d{8}$/.test(v));
        const v2 = validateField(montoEl,  document.getElementById('err-ed-monto'),  v => parseFloat(v) > 0);
        const v3 = validateField(fechaEl,  document.getElementById('err-ed-fecha'),  v => !!v);
        const v4 = validateField(cecoEl,   document.getElementById('err-ed-ceco'),   v => v.trim().length > 0);
        const v5 = validateField(motivoEl, document.getElementById('err-ed-motivo'), v => v.trim().length > 0);
        if (!v1||!v2||!v3||!v4||!v5) return;
        errEl.style.display = 'none'; setButtonLoading(btn, true);
        try {
            const origin = (new URL(CONFIG.apiBase)).origin;
            const response = await fetch(`${origin}/wp-json/wp/v2/solicitud_viatico/${postId}`, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-WP-Nonce': CONFIG.nonce },
                credentials: 'include',
                body: JSON.stringify({ acf: {
                    dni_colaborador:  dniEl.value.trim(),
                    monto_solicitado: parseFloat(montoEl.value),
                    fecha_viaje:      fechaEl.value,
                    centro_costo:     cecoEl.value.trim(),
                    motivo_viaje:     motivoEl.value.trim(),
                    estado_solicitud: 'pendiente',
                } }),
            });
            if (!response.ok) { const err = await response.json(); throw new Error(err.message || `Error ${response.status}`); }
            ModalManager.close('modal-editar-solicitud');
            showToast('success', 'Solicitud actualizada', 'Los cambios fueron guardados y la solicitud está en revisión.');
            await loadSolicitudesView(); await loadInicioView();
        } catch (err) {
            errEl.textContent = err.message || 'No se pudo guardar. Intente de nuevo.'; errEl.style.display = 'block';
        } finally { setButtonLoading(btn, false); }
    }

    /* ── Modal: Rendir Gasto ──────────────────────────────── */
    async function handleFinalizarRendicion() {
        if (!detalleSolicitudId) return;

        const btn = document.getElementById('btn-detalle-view-finalizar-rendicion');
        const errEl = document.getElementById('detalle-view-error');
        let finalizacionAplicada = false;
        errEl.style.display = 'none';
        setButtonLoading(btn, true);

        try {
            await apiFetch('/finalizar-rendicion', {
                method: 'POST',
                body: JSON.stringify({ id_solicitud: detalleSolicitudId }),
            });

            await refreshSolicitudesCache();
            await refreshGastosCache();
            renderSolicitudesTable(solicitudesCache);
            renderInicioRecent(solicitudesCache);

            const sol = getSolicitudById(detalleSolicitudId);
            if (sol) {
                renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
                finalizacionAplicada = !!sol.rendicion_finalizada;
            }

            showToast('success', 'Rendición finalizada', `La solicitud #${detalleSolicitudId} fue enviada para revisión administrativa.`);
        } catch (err) {
            errEl.textContent = err.message || 'No se pudo finalizar la rendición.';
            errEl.style.display = 'block';
        } finally {
            if (!finalizacionAplicada) setButtonLoading(btn, false);
        }
    }

    // Accumulated files for the current modal session
    let _adjFiles = [];

    function openRendirModal(solicitudId) {
        const form = document.getElementById('form-rendir-gasto');
        form.reset(); resetFormErrors(form);
        document.getElementById('rendir-gasto-error').style.display = 'none';
        document.getElementById('rg-id-solicitud').value = solicitudId;
        document.getElementById('rendir-sol-ref').textContent = `#${solicitudId}`;
        _adjFiles = [];
        renderAdjPickList();
        updateRendirTipoUI();
        ModalManager.open('modal-rendir-gasto');
        document.getElementById('rg-tipo').focus();
    }



    function renderAdjPickList() {
        const listEl = document.getElementById('rg-adj-file-list');
        if (!listEl) return;
        if (!_adjFiles.length) { listEl.innerHTML = ''; return; }
        listEl.innerHTML = _adjFiles.map((f, i) => `
            <div class="rg-adj-file-item">
                <span class="rg-adj-file-icon ${adjIconClass(f.type)}">${adjIconLabel(f.type)}</span>
                <span class="rg-adj-file-name" title="${escA(f.name)}">${escA(f.name)}</span>
                <span class="rg-adj-file-size">${(f.size/1024).toFixed(0)} KB</span>
                <button type="button" class="rg-adj-remove" data-idx="${i}" aria-label="Quitar">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>`).join('');
        listEl.querySelectorAll('.rg-adj-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                _adjFiles.splice(parseInt(btn.dataset.idx, 10), 1);
                renderAdjPickList();
            });
        });
    }

    function bindAdjInput() {
        const input = document.getElementById('rg-adj-input');
        if (!input) return;
        input.addEventListener('change', function() {
            Array.from(this.files).forEach(f => _adjFiles.push(f));
            this.value = '';
            renderAdjPickList();
        });
    }

    async function handleRendirGastoSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-rendir-gasto'), errEl = document.getElementById('rendir-gasto-error');
        const idSolicitud = document.getElementById('rg-id-solicitud').value;
        const tipoEl = document.getElementById('rg-tipo');
        const fechaEl = document.getElementById('rg-fecha');
        const importeEl = document.getElementById('rg-importe');
        const cuentaEl = document.getElementById('rg-cuenta');
        const rucEl = document.getElementById('rg-ruc');
        const razonEl = document.getElementById('rg-razon');
        const nroEl = document.getElementById('rg-nro-comprobante');
        const conceptoEl = document.getElementById('rg-concepto');
        const motivoEl = document.getElementById('rg-motivo');
        const destinoEl = document.getElementById('rg-destino');
        const cecoOiEl = document.getElementById('rg-ceco-oi');
        const tipo = getRendicionTipo();
        const isMovilidad = tipo === 'movilidad';
        const validations = [
            validateField(tipoEl, document.getElementById('err-rg-tipo'), v => !!v),
            validateField(fechaEl, document.getElementById('err-rg-fecha'), v => !!v),
            validateField(importeEl, document.getElementById('err-rg-importe'), v => parseFloat(v) > 0),
            validateField(cuentaEl, document.getElementById('err-rg-cuenta'), v => v.trim().length > 0),
        ];

        if (isMovilidad) {
            validations.push(
                validateField(motivoEl, document.getElementById('err-rg-motivo'), v => v.trim().length > 0),
                validateField(destinoEl, document.getElementById('err-rg-destino'), v => v.trim().length > 0),
                validateField(cecoOiEl, document.getElementById('err-rg-ceco-oi'), v => v.trim().length > 0)
            );
        } else if (tipo) {
            validations.push(
                validateField(rucEl, document.getElementById('err-rg-ruc'), v => /^\d{11}$/.test(v.trim())),
                validateField(razonEl, document.getElementById('err-rg-razon'), v => v.trim().length > 0),
                validateField(nroEl, document.getElementById('err-rg-nro-comprobante'), v => v.trim().length > 0)
            );
        }

        if (validations.some(v => !v)) return;
        errEl.style.display = 'none'; setButtonLoading(btn, true);
        try {
            const payload = {
                id_solicitud:    parseInt(idSolicitud, 10),
                tipo:            tipo,
                fecha:           fechaEl.value,
                importe:         parseFloat(importeEl.value),
                cuenta_contable: cuentaEl.value.trim(),
                ruc:             !isMovilidad ? rucEl.value.trim() || undefined : undefined,
                razon_social:    !isMovilidad ? razonEl.value.trim() || undefined : undefined,
                nro_comprobante: !isMovilidad ? nroEl.value.trim() || undefined : undefined,
                descripcion_concepto: !isMovilidad ? conceptoEl.value.trim() || undefined : undefined,
                motivo_movilidad: isMovilidad ? motivoEl.value.trim() || undefined : undefined,
                destino_movilidad: isMovilidad ? destinoEl.value.trim() || undefined : undefined,
                ceco_oi: isMovilidad ? cecoOiEl.value.trim() || undefined : undefined,
            };
            Object.keys(payload).forEach(k => payload[k] === undefined && delete payload[k]);
            const res = await apiFetch('/nuevo-gasto', { method:'POST', body: JSON.stringify(payload) });
            const gastoId = res.id;
            if (gastoId && _adjFiles.length) {
                for (const file of [..._adjFiles]) {
                    try {
                        const fd = new FormData();
                        fd.append('id_gasto', gastoId);
                        fd.append('archivo', file);
                        await apiFetchForm('/gasto-adjunto', fd);
                    } catch(uploadErr) {
                        console.warn('[Adjunto] No se pudo subir:', file.name, uploadErr.message);
                    }
                }
                _adjFiles = [];
            }
            ModalManager.close('modal-rendir-gasto');
            await refreshSolicitudesCache();
            await refreshGastosCache();
            renderSolicitudesTable(solicitudesCache);
            renderInicioRecent(solicitudesCache);
            showToast('success', 'Gasto registrado', `El comprobante fue rendido correctamente contra la solicitud #${idSolicitud}.`);
            if (document.getElementById('view-detalle-solicitud').classList.contains('active') && detalleSolicitudId === parseInt(idSolicitud, 10)) {
                const sol = getSolicitudById(detalleSolicitudId);
                if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
            }
            if (document.getElementById('view-rendiciones').classList.contains('active')) {
                renderRendicionesResumen(gastosCache);
            }
        } catch (err) {
            errEl.textContent = err.message || 'No se pudo registrar el gasto. Intente de nuevo.'; errEl.style.display = 'block';
        } finally {
            setButtonLoading(btn, false);
        }
    }

    /* ── Event binding ────────────────────────────────────── */
    function bindEvents() {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => { e.preventDefault(); navigateTo(link.dataset.view); });
        });
        document.getElementById('btn-abrir-nueva-solicitud').addEventListener('click', openNuevaSolicitudModal);
        document.getElementById('btn-cerrar-modal-nueva').addEventListener('click', () => ModalManager.close('modal-nueva-solicitud'));
        document.getElementById('btn-cancelar-modal-nueva').addEventListener('click', () => ModalManager.close('modal-nueva-solicitud'));
        document.getElementById('form-nueva-solicitud').addEventListener('submit', handleNuevaSolicitudSubmit);
        ModalManager.closeOnOverlayClick('modal-nueva-solicitud');

        document.getElementById('btn-cerrar-modal-editar').addEventListener('click', () => ModalManager.close('modal-editar-solicitud'));
        document.getElementById('btn-cancelar-modal-editar').addEventListener('click', () => ModalManager.close('modal-editar-solicitud'));
        document.getElementById('form-editar-solicitud').addEventListener('submit', handleEditarSolicitudSubmit);
        ModalManager.closeOnOverlayClick('modal-editar-solicitud');

        document.getElementById('btn-volver-detalle-solicitud').addEventListener('click', closeDetalleSolicitudView);
                const liqBtn = document.getElementById('btn-detalle-view-liquidacion');
        if (liqBtn) liqBtn.addEventListener('click', openLiquidacionView);
        const liqBack = document.getElementById('btn-liq-volver');
        if (liqBack) liqBack.addEventListener('click', () => navigateTo('view-detalle-solicitud'));

        document.getElementById('btn-detalle-view-agregar-gasto').addEventListener('click', () => {
            if (!detalleSolicitudId) return;
            openRendirModal(detalleSolicitudId);
        });
        document.getElementById('btn-detalle-view-finalizar-rendicion').addEventListener('click', handleFinalizarRendicion);

        bindAdjInput();
        document.getElementById('btn-cerrar-modal-rendir').addEventListener('click', () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('btn-cancelar-modal-rendir').addEventListener('click', () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('form-rendir-gasto').addEventListener('submit', handleRendirGastoSubmit);
        document.getElementById('rg-tipo').addEventListener('change', updateRendirTipoUI);
        ModalManager.closeOnOverlayClick('modal-rendir-gasto');

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

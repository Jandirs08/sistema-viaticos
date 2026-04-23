<?php
/**
 * Template Part: Dashboard â€” Vista Colaborador
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
            <h2>Â¡Bienvenido, <?php echo $args['user_name']; ?>!</h2>
            <p>Panel de gestiÃ³n de viÃ¡ticos â€” FundaciÃ³n Romero</p>
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
                <div class="stat-num" id="kpi-total">â€”</div>
                <div class="stat-label">Total Solicitudes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" fill="#D97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-pendiente">â€”</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="#059669"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-aprobada">â€”</div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24" fill="#DC2626"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-num" id="kpi-rechazada">â€”</div>
                <div class="stat-label">Rechazadas</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">Actividad Reciente</div>
                <div class="card-header-subtitle">Ãšltimas 5 solicitudes registradas</div>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="ViaticosApp.navigate('view-solicitudes')" id="btn-ver-todas">Ver todas</button>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Actividad reciente">
                <thead>
                    <tr>
                        <th>ID</th><th>Fecha Viaje</th><th>Monto</th><th>CECO/Proyecto</th><th>Estado solicitud</th><th>Estado rendiciÃ³n</th>
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
            <p>GestiÃ³n de solicitudes de viÃ¡ticos</p>
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
            <table class="erp-table" aria-label="Mis solicitudes de viÃ¡ticos">
                <thead>
                    <tr>
                        <th>ID</th><th>Fecha Viaje</th><th>Monto Solicitado</th>
                        <th>CECO / Proyecto</th><th>Estado solicitud</th><th>Estado rendiciÃ³n</th><th>Acciones</th>
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

    <!-- Topbar liviana â€” los CTAs viven en el rail derecho -->
    <div class="rd-topbar">
        <button class="rd-back-btn" id="btn-volver-detalle-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver
        </button>
        <button class="btn btn-outline btn-sm" id="btn-detalle-view-liquidacion" style="display:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            Ver LiquidaciÃ³n
        </button>
    </div>

    <!-- Contenido dinÃ¡mico generado por renderDetalleSolicitudContent() -->
    <div id="detalle-view-content"></div>
    <div id="detalle-view-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>

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

    <!-- El JS inyecta tarjetas aquÃ­ -->
    <div id="rendiciones-list-container">
        <div class="rd-list-loading"><div class="spinner"></div> Cargando rendiciones...</div>
    </div>

</section><!-- /#view-rendiciones -->

<!-- ============================================================
     VIEW: LIQUIDACIÃ“N (read-only document)
     ============================================================ -->
<section id="view-liquidacion" class="erp-view" aria-label="LiquidaciÃ³n de RendiciÃ³n">
    <div class="liq-view-toolbar">
        <button class="liq-back-btn" id="btn-liq-volver" type="button">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver al detalle
        </button>
        <div class="liq-actions">
            <button type="button" class="btn btn-secondary btn-sm" id="btn-liq-exportar" title="Exportar PDF (prÃ³ximamente)" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5z"/></svg>
                Exportar
            </button>
        </div>
    </div>
    <div id="liq-doc-container">
        <div class="liq-doc-empty"><div class="spinner"></div> Cargando liquidaciÃ³nâ€¦</div>
    </div>
</section><!-- /#view-liquidacion -->


<!-- ============================================================
     MODAL: NUEVA SOLICITUD
     ============================================================ -->
<div class="modal-overlay" id="modal-nueva-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-nueva-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-nueva-titulo">Nueva Solicitud de ViÃ¡tico</h2>
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
                        <span class="form-error" id="err-ns-dni">El DNI debe tener exactamente 8 dÃ­gitos numÃ©ricos.</span>
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
                        <span class="form-error" id="err-ns-fecha">Seleccione una fecha vÃ¡lida.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ns-ceco" name="ceco" class="form-control" placeholder="Ej: CC-001 / ADMINISTRACIÃ“N" required autocomplete="off">
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
                <p>Esta solicitud fue observada. Corrija los datos y reenvÃ­e.</p>
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
                        <span class="form-error" id="err-ed-dni">El DNI debe tener exactamente 8 dÃ­gitos numÃ©ricos.</span>
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
                        <span class="form-error" id="err-ed-fecha">Seleccione una fecha vÃ¡lida.</span>
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
     MODAL: DETALLE DE SOLICITUD / RENDICIÃ“N
     ============================================================ -->
<div class="modal-overlay" id="modal-detalle-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-detalle-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-detalle-titulo">Solicitud <span id="detalle-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p id="detalle-sol-subtitulo">Revisa el detalle de la solicitud y gestiona su rendiciÃ³n.</p>
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
                Finalizar rendiciÃ³n
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
                            <option value="">â€” Seleccione â€”</option>
                            <option value="movilidad">Movilidad</option>
                            <option value="vale_caja">Vale de Caja</option>
                            <option value="factura">Factura</option>
                            <option value="boleta">Boleta</option>
                            <option value="rxh">RxH</option>
                        </select>
                        <span class="form-error" id="err-rg-tipo">Seleccione un tipo de rendiciÃƒÂ³n.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-fecha">Fecha de EmisiÃ³n <span class="required">*</span></label>
                        <input type="date" id="rg-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-rg-fecha">Seleccione la fecha de emisiÃ³n.</span>
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
                        <label class="form-label" for="rg-razon">RazÃ³n Social</label>
                        <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                        <span class="form-error" id="err-rg-razon">Ingrese la razÃƒÂ³n social.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-nro-comprobante">NÂ° Comprobante</label>
                        <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                        <span class="form-error" id="err-rg-nro-comprobante">Ingrese el nÃƒÂºmero de comprobante.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="rg-cuenta">Cuenta Contable</label>
                        <input type="text" id="rg-cuenta" name="cuenta_contable" class="form-control" placeholder="Ej: 63.1.1" required>
                        <span class="form-error" id="err-rg-cuenta">Ingrese la cuenta contable.</span>
                    </div>
                    <div class="form-group col-full" id="rg-group-concepto">
                        <label class="form-label" for="rg-concepto">DescripciÃƒÂ³n / Concepto</label>
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
     JAVASCRIPT â€” ViaticosApp
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

    /* â”€â”€ Utilities â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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
    const getLabelEstado = estadoUI.getLabelEstado;

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
        return isNaN(num) ? 'â€”' : 'S/. ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatFecha(isoStr) {
        if (!isoStr) return 'â€”';
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

    /* â”€â”€ Modal Manager â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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
        ['modal-nueva-solicitud','modal-editar-solicitud','modal-rendir-gasto','modal-confirmar-finalizar','modal-historial-solicitud'].forEach(id => ModalManager.close(id));
    });

    /* â”€â”€ Form validation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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

    /* â”€â”€ Data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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

    /* â”€â”€ Render helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    function renderTableEmpty(tbody, colSpan, message = 'No se encontraron registros.') {
        tbody.innerHTML = `<tr><td colspan="${colSpan}"><div class="table-empty">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            <p>${message}</p></div></td></tr>`;
    }

    function renderTableLoading(tbody, colSpan) {
        tbody.innerHTML = `<tr><td colspan="${colSpan}"><div class="table-loading"><div class="spinner"></div>Cargando datos...</div></td></tr>`;
    }

    /* â”€â”€ Render: solicitudes table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    function renderSolicitudesTable(data) {
        const tbody = document.getElementById('solicitudes-tbody');
        if (!data || !data.length) { renderTableEmpty(tbody, 7, 'AÃºn no tienes solicitudes registradas.'); return; }
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
                btns += `<button class="btn btn-secondary btn-sm action-ver-rendir" data-id="${sol.id}" title="Ver detalle de la rendiciÃ³n">
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

    /* â”€â”€ Adjuntos helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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

    function renderDetalleSolicitudContent(sol, gastos) {
        const contentEl = document.getElementById('detalle-view-content');
        const totalSolicitado = parseFloat(sol.monto) || 0;
        const totalRendido = gastos.reduce((sum, g) => sum + (parseFloat(g.importe) || 0), 0);
        const saldo = totalSolicitado - totalRendido;
        const estadoSolicitud = getSolicitudEstado(sol);
        const estadoRend = getRendicionEstado(sol, { gastos });
        const saldoNegativo = saldo < 0;
        const historial = Array.isArray(sol.historial) ? sol.historial : [];
        const historialHtml = timelineUI.renderTimeline(historial);
        const solicitudBadgeHtml = renderSolicitudBadge(sol);
        const rendicionBadgeHtml = renderRendicionBadge(sol, { gastos });
        const fechaViaje = formatFecha(sol.fecha);
        const cecoLabel = escHtml(sol.ceco || '-');
        const dniLabel = escHtml(sol.dni || '-');
        const motivoLabel = escHtml(sol.motivo || 'Sin detalle registrado.');
        const avancePct = totalSolicitado > 0 ? Math.max(0, Math.min(100, Math.round((totalRendido / totalSolicitado) * 100))) : (gastos.length ? 100 : 0);

        const accionContexto = (() => {
            if (estadoRend === 'aprobada') return 'Todo esta cerrado. Solo queda consultar la liquidacion o el historial.';
            if (estadoRend === 'rechazada') return 'Revisa el historial para entender el motivo del rechazo antes de continuar.';
            if (estadoRend === 'observada') return 'Hay ajustes pendientes. Corrige lo necesario y vuelve a enviarla.';
            if (sol.rendicion_finalizada) return 'La rendicion ya fue enviada. Por ahora solo queda esperar la revision.';
            if (estadoSolicitud === 'observada') return 'Primero corrige la solicitud observada antes de seguir con la rendicion.';
            if (estadoSolicitud === 'rechazada') return 'Esta solicitud no puede continuar. Necesitaras registrar una nueva.';
            if (estadoSolicitud !== 'aprobada') return 'Aun no puedes rendir gastos hasta que la solicitud sea aprobada.';
            if (gastos.length) return saldoNegativo ? 'Ya superaste el monto solicitado. Revisa los comprobantes antes de enviarla.' : 'Ya empezaste la rendicion. Puedes seguir cargando gastos o enviarla a revision.';
            return 'Aun no registras gastos. Empieza con el primer comprobante del viaje.';
        })();

        const estadoWorkspace = (() => {
            if (estadoRend === 'aprobada') return { tone: 'is-ok', pill: 'Aprobada', title: 'Tu rendicion fue aprobada', copy: 'No tienes nada pendiente. Puedes revisar la liquidacion cuando la necesites.', icon: 'check' };
            if (estadoRend === 'rechazada') return { tone: 'is-danger', pill: 'Rechazada', title: 'Tu rendicion fue rechazada', copy: 'Revisa el historial para ver la observacion y coordina el siguiente paso.', icon: 'alert' };
            if (estadoRend === 'observada') return { tone: 'is-warning', pill: 'Observada', title: 'Tu rendicion necesita ajustes', copy: 'Hay observaciones pendientes. Revisa el historial y completa lo necesario antes de enviarla otra vez.', icon: 'edit' };
            if (sol.rendicion_finalizada) return { tone: 'is-review', pill: 'En revision', title: 'Tu rendicion esta en revision', copy: 'Ya la enviaste. Por ahora no necesitas hacer nada mas.', icon: 'clock' };
            if (estadoSolicitud === 'observada') return { tone: 'is-warning', pill: 'Solicitud observada', title: 'Tu solicitud necesita correccion', copy: 'Corrige la solicitud observada antes de continuar con la rendicion.', icon: 'edit' };
            if (estadoSolicitud === 'rechazada') return { tone: 'is-danger', pill: 'Solicitud rechazada', title: 'Tu solicitud fue rechazada', copy: 'No podras rendir gastos con esta solicitud.', icon: 'alert' };
            if (estadoSolicitud !== 'aprobada') return { tone: 'is-idle', pill: 'Pendiente', title: 'Tu solicitud aun espera aprobacion', copy: 'Cuando la aprueben podras registrar los gastos del viaje.', icon: 'clock' };
            if (!gastos.length) return { tone: 'is-active', pill: 'Lista para rendir', title: 'Ya puedes registrar tus gastos', copy: 'Empieza con el primer comprobante del viaje.', icon: 'wallet' };
            return { tone: 'is-active', pill: 'En progreso', title: 'Completa tu rendicion', copy: saldoNegativo ? 'Ya superaste el monto solicitado. Revisa tus comprobantes antes de enviarla.' : (saldo > 0 ? `Tienes ${formatMonto(saldo)} pendientes por sustentar.` : 'Revisa tus comprobantes y enviala cuando todo este listo.'), icon: 'wallet' };
        })();

        const statusIconHtml = (() => {
            if (estadoWorkspace.icon === 'check') return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
            if (estadoWorkspace.icon === 'alert') return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>';
            if (estadoWorkspace.icon === 'edit') return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm14.71-9.04c.39-.39.39-1.02.0-1.41l-2.5-2.5a.9959.9959.0 0 0-1.41.0l-1.96 1.96 3.75 3.75 2.15-2.26z"/></svg>';
            if (estadoWorkspace.icon === 'wallet') return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 7H5C3.89 7 3 7.89 3 9v8c0 1.11.89 2 2 2h16c1.11.0 2-.89 2-2V9c0-1.11-.89-2-2-2zm0 10H5V9h16v8zm-3-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM5 6h13V4H5c-1.11.0-2 .89-2 2v1h2V6z"/></svg>';
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 11H11V7h2zm0 4H11v-2h2z"/></svg>';
        })();

        const flowSteps = [
            { state: 'is-done', label: 'Etapa 1', title: 'Solicitud', meta: `#${sol.id} registrada` },
            {
                state: estadoSolicitud === 'aprobada' ? 'is-done' : (['observada','rechazada'].includes(estadoSolicitud) ? 'is-warning' : 'is-current'),
                label: 'Etapa 2', title: 'Aprobacion',
                meta: estadoSolicitud === 'aprobada' ? 'Aprobada' : (estadoSolicitud === 'observada' ? 'Observada' : (estadoSolicitud === 'rechazada' ? 'Rechazada' : 'Pendiente'))
            },
            {
                state: sol.rendicion_finalizada || ['aprobada','rechazada','observada'].includes(estadoRend) ? 'is-done' : (estadoSolicitud === 'aprobada' ? 'is-current' : ''),
                label: 'Etapa 3', title: 'Gastos',
                meta: gastos.length ? `${gastos.length} registro(s)` : 'Sin registros'
            },
            {
                state: ['aprobada','rechazada','observada'].includes(estadoRend) ? (estadoRend === 'aprobada' ? 'is-done' : 'is-warning') : (sol.rendicion_finalizada ? 'is-current' : ''),
                label: 'Etapa 4', title: 'Revision',
                meta: estadoRend === 'aprobada' ? 'Aprobada' : (estadoRend === 'rechazada' ? 'Rechazada' : (estadoRend === 'observada' ? 'Observada' : (sol.rendicion_finalizada ? 'En revision' : 'Pendiente')))
            }
        ];
        const flowStepsHtml = flowSteps.map(s => `
            <div class="solv-stage-card ${s.state}">
                <span class="solv-stage-index">${escHtml(s.label.replace('Etapa ', ''))}</span>
                <div class="solv-stage-copy">
                    <div class="solv-stage-title">${s.title}</div>
                    <div class="solv-stage-meta">${escHtml(s.meta)}</div>
                </div>
            </div>`).join('');

        const gastosHtml = gastos.length
            ? `<div class="gasto-acc-list" id="colab-gastos-acc">${gastos.map((g, i) => gastoUI.renderGastoItem(g, `col-${sol.id}-${i}`)).join('')}</div>`
            : `<div class="table-empty" style="padding:36px 20px;"><svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.28;"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg><p>Aun no registraste gastos para esta solicitud.</p></div>`;

        const alertaBanner = (() => {
            if (!['observada', 'rechazada', 'aprobada'].includes(estadoRend)) return '';
            const m = { observada: ['estado-alerta-observada','Rendicion observada','El administrador devolvio observaciones.'], rechazada: ['estado-alerta-rechazada','Rendicion rechazada','Comunicate con el area de finanzas.'], aprobada: ['estado-alerta-aprobada','Rendicion aprobada','Tu rendicion fue aprobada correctamente.'] };
            const [cls, tit, msg] = m[estadoRend];
            return `<div class="estado-alerta ${cls}"><div class="estado-alerta-content"><strong>${tit}</strong><p>${msg}</p></div></div>`;
        })();

        contentEl.innerHTML = `
            ${alertaBanner}
            <div class="solv-shell">
                <section class="solv-hero ${estadoWorkspace.tone}">
                    <div class="solv-hero-main">
                        <div class="solv-hero-eyebrow">Expediente #${sol.id}</div>
                        <div class="solv-hero-state">
                            <span class="solv-state-icon">${statusIconHtml}</span>
                            <div class="solv-hero-intro">
                                <span class="solv-state-pill">${estadoWorkspace.pill}</span>
                                <h1 class="solv-hero-title">${estadoWorkspace.title}</h1>
                            </div>
                        </div>
                        <p class="solv-hero-copy">${estadoWorkspace.copy}</p>
                        <div class="solv-hero-badges">${solicitudBadgeHtml}${rendicionBadgeHtml}</div>
                    </div>
                    <div class="solv-hero-stats">
                        <div class="solv-hero-stat is-primary"><span class="solv-hero-stat-label">Monto solicitado</span><strong class="solv-hero-stat-value">${formatMonto(totalSolicitado)}</strong><span class="solv-hero-stat-note">Anticipo aprobado</span></div>
                        <div class="solv-hero-stat is-positive"><span class="solv-hero-stat-label">Total rendido</span><strong class="solv-hero-stat-value">${formatMonto(totalRendido)}</strong><span class="solv-hero-stat-note">${gastos.length} comprobante(s)</span></div>
                        <div class="solv-hero-stat ${saldoNegativo ? 'is-warning' : 'is-neutral'}"><span class="solv-hero-stat-label">Saldo</span><strong class="solv-hero-stat-value">${formatMonto(saldo)}</strong><span class="solv-hero-stat-note">${saldoNegativo ? 'Monto excedido' : 'Disponible por rendir'}</span></div>
                    </div>
                </section>
                <div class="solv-stage-strip">${flowStepsHtml}</div>
                <div class="solv-grid">
                    <div class="solv-main">
                        <section class="solv-panel solv-panel-primary">
                            <div class="solv-panel-head">
                                <div>
                                    <div class="solv-kicker">Rendicion</div>
                                    <h2 class="solv-panel-title">Comprobantes</h2>
                                    <p class="solv-panel-copy">Aqui registras y revisas los gastos del viaje.</p>
                                </div>
                                <div class="solv-toolbar">
                                    <div class="solv-chip-stat"><span class="solv-chip-stat-label">Registros</span><strong class="solv-chip-stat-value">${gastos.length}</strong></div>
                                    <div class="solv-chip-stat"><span class="solv-chip-stat-label">Avance</span><strong class="solv-chip-stat-value">${avancePct}%</strong></div>
                                </div>
                            </div>
                            <div class="solv-context-strip">
                                <div class="solv-context-item"><span class="solv-context-label">Fecha de viaje</span><strong class="solv-context-value">${fechaViaje}</strong></div>
                                <div class="solv-context-item"><span class="solv-context-label">Centro de costo</span><strong class="solv-context-value">${cecoLabel}</strong></div>
                                <div class="solv-context-item"><span class="solv-context-label">DNI</span><strong class="solv-context-value">${dniLabel}</strong></div>
                            </div>
                            <div class="solv-panel-body solv-panel-body-gastos">${gastosHtml}</div>
                        </section>
                        <section class="solv-panel">
                            <div class="solv-panel-head">
                                <div>
                                    <div class="solv-kicker">Detalle del viaje</div>
                                    <h2 class="solv-panel-title">Lo esencial de esta solicitud</h2>
                                </div>
                            </div>
                            <div class="solv-panel-body">
                                    <div class="solv-data-grid">
                                    <div class="solv-data-item"><span class="solv-data-label">Expediente</span><span class="solv-data-value">#${sol.id}</span></div>
                                    <div class="solv-data-item"><span class="solv-data-label">Historial</span><span class="solv-data-value">${historial.length} evento(s)</span></div>
                                    <div class="solv-data-item"><span class="solv-data-label">Estado solicitud</span><span class="solv-data-value">${escHtml(estadoSolicitud)}</span></div>
                                    <div class="solv-data-item"><span class="solv-data-label">Estado rendicion</span><span class="solv-data-value">${escHtml(estadoRend)}</span></div>
                                    <div class="solv-data-item is-wide"><span class="solv-data-label">Motivo del viaje</span><span class="solv-data-value is-muted">${motivoLabel}</span></div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <aside class="solv-rail">
                        <section class="solv-rail-card solv-status-card ${estadoWorkspace.tone}">
                            <div class="solv-status-top">
                                <span class="solv-state-icon is-rail">${statusIconHtml}</span>
                                <div class="solv-status-heading">
                                    <span class="solv-status-pill">${estadoWorkspace.pill}</span>
                                    <h3 class="solv-status-title">Que sigue ahora</h3>
                                </div>
                            </div>
                            <p class="solv-status-copy">${accionContexto}</p>
                            <div class="solv-balance-list">
                                <div class="solv-balance-row"><span>Saldo</span><strong>${formatMonto(saldo)}</strong></div>
                                <div class="solv-balance-row"><span>Comprobantes</span><strong>${gastos.length}</strong></div>
                            </div>
                            <button type="button" class="solv-history-link" data-open-history="1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13 3a9 9 0 1 0 8.95 10h-2.02A7 7 0 1 1 13 5v4l5-5-5-5v4z"/></svg>
                                Ver historial completo
                            </button>
                            <div class="solv-cta-stack">
                                <button type="button" class="btn btn-primary solv-cta-full" id="detalle-sidebar-finalizar-rendicion">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    Finalizar y enviar
                                </button>
                                <button type="button" class="btn btn-secondary solv-cta-full" id="detalle-sidebar-agregar-gasto">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                    Agregar comprobante
                                </button>
                                <button type="button" class="btn btn-secondary solv-cta-full" id="detalle-sidebar-ver-liquidacion" style="display:none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                    Ver liquidacion
                                </button>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        `;

        const historialBodyEl = document.getElementById('detalle-historial-body');
        const historialMetaEl = document.getElementById('detalle-historial-meta');
        const historialSubtitleEl = document.getElementById('detalle-historial-subtitulo');
        if (historialBodyEl) {
            historialBodyEl.innerHTML = historial.length
                ? historialHtml
                : `<div class="table-empty" style="padding:32px 20px;"><svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.28;"><path d="M13 3a9 9 0 1 0 8.95 10h-2.02A7 7 0 1 1 13 5v4l5-5-5-5v4z"/></svg><p>No hay movimientos registrados todavia.</p></div>`;
        }
        if (historialMetaEl) {
            historialMetaEl.innerHTML = `
                <span class="solv-history-chip"><span class="solv-history-chip-label">Expediente</span><strong>#${sol.id}</strong></span>
                <span class="solv-history-chip"><span class="solv-history-chip-label">Eventos</span><strong>${historial.length}</strong></span>
                <span class="solv-history-chip"><span class="solv-history-chip-label">Comprobantes</span><strong>${gastos.length}</strong></span>
            `;
        }
        if (historialSubtitleEl) {
            historialSubtitleEl.textContent = `Seguimiento completo de la solicitud #${sol.id} y su rendicion.`;
        }

        const accContainer = contentEl.querySelector('#colab-gastos-acc');
        if (accContainer) {
            gastoUI.bindAccordionList(accContainer, { onOpen: function(itemEl, gastoId) { if (gastoId) loadGastoAdjuntos(gastoId, itemEl); } });
        }

        const btnLiquidacion = document.getElementById('btn-detalle-view-liquidacion');
        if (btnLiquidacion) btnLiquidacion.style.display = 'none';

        const btnSidebarAgregar = contentEl.querySelector('#detalle-sidebar-agregar-gasto');
        const btnSidebarFinalizar = contentEl.querySelector('#detalle-sidebar-finalizar-rendicion');
        const btnSidebarLiquidacion = contentEl.querySelector('#detalle-sidebar-ver-liquidacion');
        const btnsOpenHistory = contentEl.querySelectorAll('[data-open-history="1"]');
        const actionStack = contentEl.querySelector('.solv-cta-stack');
        const canAdd = estadoSolicitud === 'aprobada' && !sol.rendicion_finalizada && !['aprobada', 'rechazada'].includes(estadoRend);
        const canFinalize = canAdd && gastos.length > 0;
        const canLiquidacion = !!sol.rendicion_finalizada;

        if (btnSidebarAgregar) {
            btnSidebarAgregar.style.display = canAdd ? 'inline-flex' : 'none';
            btnSidebarAgregar.addEventListener('click', () => { if (!sol.rendicion_finalizada) openRendirModal(sol.id); });
        }
        if (btnSidebarFinalizar) {
            btnSidebarFinalizar.style.display = canFinalize ? 'inline-flex' : 'none';
            btnSidebarFinalizar.addEventListener('click', () => { if (!sol.rendicion_finalizada && gastos.length) ModalManager.open('modal-confirmar-finalizar'); });
        }
        if (btnSidebarLiquidacion) {
            btnSidebarLiquidacion.style.display = canLiquidacion ? 'inline-flex' : 'none';
            btnSidebarLiquidacion.addEventListener('click', () => openLiquidacionView(sol.id));
        }
        if (actionStack) {
            const visibleActions = [btnSidebarAgregar, btnSidebarFinalizar, btnSidebarLiquidacion].filter(btn => btn && btn.style.display !== 'none').length;
            actionStack.style.display = visibleActions ? 'flex' : 'none';
        }
        if (btnsOpenHistory.length) {
            btnsOpenHistory.forEach(btn => btn.addEventListener('click', () => ModalManager.open('modal-historial-solicitud')));
        }
    }

    async function handleFinalizarRendicion() {
        if (!detalleSolicitudId) return;
        try {
            await apiFetch('/finalizar-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: detalleSolicitudId }) });
            await refreshSolicitudesCache(); await refreshGastosCache();
            renderSolicitudesTable(solicitudesCache);
            const sol = getSolicitudById(detalleSolicitudId);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(detalleSolicitudId));
            showToast('success', 'Finalizado', 'RendiciÃ³n enviada a revisiÃ³n.');
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

        // BotÃ³n liquidaciÃ³n (topbar)
        const liqBtn = document.getElementById('btn-detalle-view-liquidacion');
        if (liqBtn) liqBtn.addEventListener('click', openLiquidacionView);
        const liqBack = document.getElementById('btn-liq-volver');
        if (liqBack) liqBack.addEventListener('click', () => navigateTo({ name: 'solicitud', id: getCurrentRoute().id }));

        // Modal confirmar finalizar rendiciÃ³n
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

    /* â”€â”€ Init â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    function init() { bindEvents(); loadInicioView(); }
    window.ViaticosApp = { navigate: navigateTo };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

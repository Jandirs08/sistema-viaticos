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
            </li>
            <li>
                <a href="#" id="nav-rendiciones" class="nav-link" data-view="view-rendiciones">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                    Mis Rendiciones
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
                        <th>ID</th><th>Fecha Viaje</th><th>Monto</th><th>CECO/Proyecto</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody id="inicio-recent-tbody">
                    <tr><td colspan="5"><div class="table-loading"><div class="spinner"></div> Cargando...</div></td></tr>
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
            <p>Gestiona y da seguimiento a tus solicitudes de viáticos.</p>
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
                        <th>CECO / Proyecto</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes-tbody">
                    <tr><td colspan="6"><div class="table-loading"><div class="spinner"></div> Cargando solicitudes...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-solicitudes -->


<!-- ============================================================
     VISTA: MIS RENDICIONES
     ============================================================ -->
<section id="view-rendiciones" class="erp-view" aria-label="Mis Rendiciones">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Rendiciones</h1>
            <p>Revisa los gastos rendidos contra tus solicitudes aprobadas.</p>
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
                        <label class="form-label" for="rg-tipo">Tipo de Documento</label>
                        <select id="rg-tipo" name="tipo" class="form-control">
                            <option value="">— Seleccione —</option>
                            <option value="vale_caja">Vale de Caja</option>
                            <option value="vale_movilidad">Vale de Movilidad</option>
                            <option value="modelo_liquidacion">Modelo Liquidación</option>
                        </select>
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
                    <div class="form-group">
                        <label class="form-label" for="rg-ruc">RUC del Proveedor</label>
                        <input type="text" id="rg-ruc" name="ruc" class="form-control" maxlength="11" placeholder="Ej: 20123456789" inputmode="numeric">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-razon">Razón Social</label>
                        <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rg-nro-comprobante">N° Comprobante</label>
                        <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="rg-cuenta">Cuenta Contable</label>
                        <input type="text" id="rg-cuenta" name="cuenta_contable" class="form-control" placeholder="Ej: 63.1.1">
                    </div>
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

    function formatMonto(value) {
        const num = parseFloat(value);
        return isNaN(num) ? '—' : 'S/. ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatFecha(isoStr) {
        if (!isoStr) return '—';
        const parts = isoStr.split('-');
        return parts.length !== 3 ? isoStr : `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    const estadoLabel = { pendiente:'Pendiente', aprobada:'Aprobada', observada:'Observada', rechazada:'Rechazada' };

    function badgeHTML(estado) {
        const key = (estado || '').toLowerCase();
        return `<span class="badge badge-${key}">${estadoLabel[key] || estado}</span>`;
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

    function prefillNuevaSolicitudForm() {
        const dniEl = document.getElementById('ns-dni');

        if (dniEl && CONFIG.profile.dni) {
            dniEl.value = CONFIG.profile.dni;
        }
    }

    /* ── Data ─────────────────────────────────────────────── */
    let solicitudesCache = [];

    async function fetchSolicitudes() { return await apiFetch('/mis-solicitudes'); }
    async function fetchGastos()      { return await apiFetch('/mis-rendiciones'); }

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
        if (!data || !data.length) { renderTableEmpty(tbody, 6, 'Aún no tienes solicitudes registradas.'); return; }
        tbody.innerHTML = data.map(sol => {
            const estado   = (sol.estado || 'pendiente').toLowerCase();
            const acciones = buildAcciones(sol);
            return `<tr>
                <td class="text-muted">#${sol.id}</td>
                <td>${formatFecha(sol.fecha)}</td>
                <td><strong>${formatMonto(sol.monto)}</strong></td>
                <td>${escHtml(sol.ceco)}</td>
                <td>${badgeHTML(estado)}</td>
                <td>${acciones}</td>
            </tr>`;
        }).join('');
        attachActionListeners(tbody, data);
    }

    function buildAcciones(sol) {
        const estado = (sol.estado || '').toLowerCase();
        let btns = '';
        if (estado === 'observada') {
            btns += `<button class="btn btn-secondary btn-sm action-editar" data-id="${sol.id}" title="Editar solicitud observada">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02.0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41.0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Editar</button>`;
        }
        if (estado === 'aprobada') {
            btns += `<button class="btn btn-success btn-sm action-rendir" data-id="${sol.id}" title="Rendir gasto">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                Rendir Gasto</button>`;
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
        tbody.querySelectorAll('.action-rendir').forEach(btn => {
            btn.addEventListener('click', () => openRendirModal(parseInt(btn.dataset.id, 10)));
        });
    }

    /* ── Render: inicio recent + KPIs ────────────────────── */
    function renderInicioRecent(data) {
        const tbody = document.getElementById('inicio-recent-tbody');
        const kpis  = { total: data.length, pendiente: 0, aprobada: 0, rechazada: 0 };
        data.forEach(s => { const e = (s.estado||'').toLowerCase(); if (e in kpis) kpis[e]++; });
        document.getElementById('kpi-total').textContent     = kpis.total;
        document.getElementById('kpi-pendiente').textContent = kpis.pendiente;
        document.getElementById('kpi-aprobada').textContent  = kpis.aprobada;
        document.getElementById('kpi-rechazada').textContent = kpis.rechazada;

        const recent = data.slice(0, 5);
        if (!recent.length) { renderTableEmpty(tbody, 5, 'Aún no tienes actividad registrada.'); return; }
        tbody.innerHTML = recent.map(sol => `<tr>
            <td class="text-muted">#${sol.id}</td>
            <td>${formatFecha(sol.fecha)}</td>
            <td><strong>${formatMonto(sol.monto)}</strong></td>
            <td>${escHtml(sol.ceco)}</td>
            <td>${badgeHTML(sol.estado)}</td>
        </tr>`).join('');
    }

    /* ── Render: rendiciones table ────────────────────────── */
    function renderRendicionesTable(data) {
        const tbody = document.getElementById('rendiciones-tbody');
        if (!data || !data.length) { renderTableEmpty(tbody, 6, 'Aún no tienes gastos rendidos registrados.'); return; }
        const tipoLabel = { vale_caja:'Vale de Caja', vale_movilidad:'Vale de Movilidad', modelo_liquidacion:'Modelo Liquidación' };
        tbody.innerHTML = data.map(g => `<tr>
            <td class="text-muted">#${g.id}</td>
            <td>${g.id_solicitud ? `<span class="badge badge-aprobada">#${g.id_solicitud}</span>` : '—'}</td>
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
        const names = { 'view-inicio':'Inicio', 'view-solicitudes':'Mis Solicitudes', 'view-rendiciones':'Mis Rendiciones' };
        const nameEl = document.getElementById('topbar-section-name');
        if (nameEl) nameEl.textContent = names[viewId] || '';
        if (viewId === 'view-solicitudes') loadSolicitudesView();
        if (viewId === 'view-rendiciones') loadRendicionesView();
    }

    /* ── Load per view ────────────────────────────────────── */
    async function loadInicioView() {
        const tbody = document.getElementById('inicio-recent-tbody');
        renderTableLoading(tbody, 5);
        try { solicitudesCache = await fetchSolicitudes(); renderInicioRecent(solicitudesCache); }
        catch (err) { console.error('[ViaticosApp]', err); renderTableEmpty(tbody, 5, 'Error al cargar datos.'); showToast('error', 'Error', err.message); }
    }

    async function loadSolicitudesView() {
        const tbody = document.getElementById('solicitudes-tbody');
        renderTableLoading(tbody, 6);
        try { solicitudesCache = await fetchSolicitudes(); renderSolicitudesTable(solicitudesCache); }
        catch (err) { console.error('[ViaticosApp]', err); renderTableEmpty(tbody, 6, 'Error al cargar solicitudes.'); showToast('error', 'Error', err.message); }
    }

    async function loadRendicionesView() {
        const tbody = document.getElementById('rendiciones-tbody');
        renderTableLoading(tbody, 6);
        try { const gastos = await fetchGastos(); renderRendicionesTable(gastos); }
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
    function openRendirModal(solicitudId) {
        const form = document.getElementById('form-rendir-gasto');
        form.reset(); resetFormErrors(form);
        document.getElementById('rendir-gasto-error').style.display = 'none';
        document.getElementById('rg-id-solicitud').value = solicitudId;
        document.getElementById('rendir-sol-ref').textContent = `#${solicitudId}`;
        ModalManager.open('modal-rendir-gasto');
        document.getElementById('rg-fecha').focus();
    }

    async function handleRendirGastoSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-rendir-gasto'), errEl = document.getElementById('rendir-gasto-error');
        const idSolicitud = document.getElementById('rg-id-solicitud').value;
        const fechaEl = document.getElementById('rg-fecha'), importeEl = document.getElementById('rg-importe');
        const v1 = validateField(fechaEl,   document.getElementById('err-rg-fecha'),   v => !!v);
        const v2 = validateField(importeEl, document.getElementById('err-rg-importe'), v => parseFloat(v) > 0);
        if (!v1||!v2) return;
        errEl.style.display = 'none'; setButtonLoading(btn, true);
        try {
            const payload = {
                id_solicitud:    parseInt(idSolicitud, 10),
                tipo:            document.getElementById('rg-tipo').value || undefined,
                fecha:           fechaEl.value,
                importe:         parseFloat(importeEl.value),
                ruc:             document.getElementById('rg-ruc').value.trim() || undefined,
                razon_social:    document.getElementById('rg-razon').value.trim() || undefined,
                nro_comprobante: document.getElementById('rg-nro-comprobante').value.trim() || undefined,
                cuenta_contable: document.getElementById('rg-cuenta').value.trim() || undefined,
            };
            Object.keys(payload).forEach(k => payload[k] === undefined && delete payload[k]);
            await apiFetch('/nuevo-gasto', { method:'POST', body: JSON.stringify(payload) });
            ModalManager.close('modal-rendir-gasto');
            showToast('success', 'Gasto registrado', `El comprobante fue rendido correctamente contra la solicitud #${idSolicitud}.`);
            await loadRendicionesView();
        } catch (err) {
            errEl.textContent = err.message || 'No se pudo registrar el gasto. Intente de nuevo.'; errEl.style.display = 'block';
        } finally { setButtonLoading(btn, false); }
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

        document.getElementById('btn-cerrar-modal-rendir').addEventListener('click', () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('btn-cancelar-modal-rendir').addEventListener('click', () => ModalManager.close('modal-rendir-gasto'));
        document.getElementById('form-rendir-gasto').addEventListener('submit', handleRendirGastoSubmit);
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

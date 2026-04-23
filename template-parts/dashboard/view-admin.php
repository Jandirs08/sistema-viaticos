<?php
/**
 * Template Part: Dashboard - Vista Administrador
 *
 * Admin UX separado por etapas de negocio: anticipos y rendiciones.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'rest_nonce' => '',
        'api_base'   => '',
    ]
);
?>

<script>
(function () {
    var nav = document.getElementById('sidebar-nav-items');
    if (nav) {
        nav.innerHTML = `
            <li>
                <a href="?view=anticipos" class="nav-link active" data-view="view-anticipos" data-route="anticipos" id="nav-anticipos">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 12H7v-2h10v2zm0-4H7V9h10v2zm0-4H7V5h10v2z"/></svg>
                    Anticipos
                </a>
            </li>
            <li>
                <a href="?view=rendiciones" class="nav-link" data-view="view-rendiciones" data-route="rendiciones" id="nav-rendiciones">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6a9.77 9.77 0 0 1 8.82 6A9.77 9.77 0 0 1 12 18a9.77 9.77 0 0 1-8.82-6A9.77 9.77 0 0 1 12 6zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.2a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6z"/></svg>
                    Rendiciones
                </a>
            </li>`;
    }

    var bc = document.getElementById('topbar-section-name');
    if (bc) bc.textContent = 'Anticipos';
})();
</script>

<section id="view-anticipos" class="erp-view active">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Anticipos</h1>
            <p>Solicitudes en etapa de anticipo para evaluar y decidir.</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="search" id="search-anticipos" class="search-input" placeholder="Buscar por colaborador, ID o CECO..." autocomplete="off">
            <button class="btn btn-ghost btn-sm js-btn-refrescar" data-view="view-anticipos" title="Actualizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Actualizar
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">Bandeja de anticipos</div>
                <div class="card-header-subtitle">Solicitudes que aun requieren una decision sobre el anticipo.</div>
            </div>
            <div id="tbl-counter-anticipos" style="font-size:12px;color:var(--text-muted);"></div>
        </div>
        <div class="table-wrap">
            <table class="tbl" aria-label="Bandeja de anticipos">
                <thead>
                    <tr><th>ID</th><th>Solicitud</th><th>Fecha viaje</th><th>Monto</th><th>Estado solicitud</th><th>Estado rendicion</th><th>Accion</th></tr>
                </thead>
                <tbody id="anticipos-tbody">
                    <tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando anticipos...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="view-rendiciones" class="erp-view">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Rendiciones</h1>
            <p>Solicitudes aprobadas que ya se encuentran en la etapa de rendicion.</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="search" id="search-rendiciones" class="search-input" placeholder="Buscar por colaborador, ID o CECO..." autocomplete="off">
            <button class="btn btn-ghost btn-sm js-btn-refrescar" data-view="view-rendiciones" title="Actualizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Actualizar
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">Bandeja de rendiciones</div>
                <div class="card-header-subtitle">Seguimiento de solicitudes aprobadas y revision de rendiciones.</div>
            </div>
            <div id="tbl-counter-rendiciones" style="font-size:12px;color:var(--text-muted);"></div>
        </div>
        <div class="table-wrap">
            <table class="tbl" aria-label="Bandeja de rendiciones">
                <thead>
                    <tr><th>ID</th><th>Solicitud</th><th>Fecha viaje</th><th>Monto</th><th>Estado solicitud</th><th>Estado rendicion</th><th>Accion</th></tr>
                </thead>
                <tbody id="rendiciones-tbody">
                    <tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando rendiciones...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="view-solicitud-detalle" class="erp-view">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Detalle de solicitud</h1>
            <p>Revision completa de solicitud, historial y rendicion.</p>
        </div>
        <button class="btn btn-secondary btn-sm" id="btn-volver-lista">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.58-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span id="btn-volver-lista-texto">Volver a Anticipos</span>
        </button>
    </div>

    <div id="solicitud-detalle-content" class="card">
        <div style="padding:20px;"><div class="tbl-loading"><div class="spinner"></div>Cargando detalle...</div></div>
    </div>
</section>

<div class="overlay" id="modal-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-solicitud-titulo">
    <div class="modal" style="max-width:720px;">
        <div class="modal-header">
            <div>
                <h2 id="modal-solicitud-titulo">Detalle de solicitud</h2>
                <p id="modal-solicitud-subtitulo" style="margin-top:2px;"></p>
            </div>
            <button class="modal-close" id="btn-cerrar-solicitud-modal" aria-label="Cerrar"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
        </div>

        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="di-label">Monto solicitado</div><div class="di-value" id="modal-det-monto">-</div></div>
                <div class="detail-item"><div class="di-label">Fecha del viaje</div><div class="di-value" id="modal-det-fecha">-</div></div>
                <div class="detail-item"><div class="di-label">Colaborador</div><div class="di-value" id="modal-det-colaborador">-</div></div>
                <div class="detail-item"><div class="di-label">DNI</div><div class="di-value" id="modal-det-dni">-</div></div>
                <div class="detail-item"><div class="di-label">CECO / Proyecto</div><div class="di-value" id="modal-det-ceco">-</div></div>
                <div class="detail-item"><div class="di-label">Estado solicitud</div><div class="di-value" id="modal-det-estado-solicitud">-</div></div>
                <div class="detail-item"><div class="di-label">Estado rendicion</div><div class="di-value" id="modal-det-estado-rendicion">-</div></div>
                <div class="detail-item col-full"><div class="di-label">Motivo del viaje</div><div class="motivo-box" id="modal-det-motivo">-</div></div>
            </div>
            <div style="margin-top:20px;"><div class="di-label" style="margin-bottom:10px;">Historial</div><div id="modal-det-historial"></div></div>
            <div id="modal-solicitud-error" style="display:none; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px; margin-top:16px;"></div>
        </div>

        <div class="modal-footer">
            <span class="modal-decision-label" id="modal-solicitud-label" style="margin-right:auto;">Decision:</span>
            <button class="btn btn-secondary" id="btn-cancelar-solicitud-modal">Cerrar</button>
            <button class="btn btn-warning" id="btn-modal-observar" data-estado="observada"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>Observar</button>
            <button class="btn btn-danger" id="btn-modal-rechazar" data-estado="rechazada"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Rechazar</button>
            <button class="btn btn-success" id="btn-modal-aprobar" data-estado="aprobada"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Aprobar</button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const CFG = { nonce: '<?php echo esc_js( $args['rest_nonce'] ); ?>', apiBase: '<?php echo esc_js( $args['api_base'] ); ?>' };
    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const gastoUI = window.ViaticosGastoUI;
    const LIST_VIEWS = ['view-anticipos', 'view-rendiciones'];
    const ROUTE_CONFIG = {
        anticipos: { viewId: 'view-anticipos', breadcrumb: 'Anticipos' },
        rendiciones: { viewId: 'view-rendiciones', breadcrumb: 'Rendiciones' },
        solicitud: { viewId: 'view-solicitud-detalle', breadcrumb: 'Detalle de solicitud', requiresId: true }
    };
    const VIEW_TO_ROUTE = Object.keys(ROUTE_CONFIG).reduce((acc, routeName) => {
        acc[ROUTE_CONFIG[routeName].viewId] = routeName;
        return acc;
    }, {});
    const VIEW_CONFIG = {
        'view-anticipos': {
            breadcrumb: 'Anticipos',
            tbodyId: 'anticipos-tbody',
            searchId: 'search-anticipos',
            counterId: 'tbl-counter-anticipos',
            emptyText: 'No hay anticipos en esta etapa.',
            loadingText: 'Cargando anticipos...',
            filter: function (sol) {
                return getSolicitudEstado(sol) !== 'aprobada';
            }
        },
        'view-rendiciones': {
            breadcrumb: 'Rendiciones',
            tbodyId: 'rendiciones-tbody',
            searchId: 'search-rendiciones',
            counterId: 'tbl-counter-rendiciones',
            emptyText: 'No hay rendiciones registradas.',
            loadingText: 'Cargando rendiciones...',
            filter: function (sol) {
                return getSolicitudEstado(sol) === 'aprobada';
            }
        }
    };

    let cache = [];
    let modalSolId = null;

    async function apiFetch(endpoint, options = {}) {
        const merged = Object.assign({ headers: {} }, options);
        merged.headers = Object.assign({ 'Content-Type': 'application/json', 'X-WP-Nonce': CFG.nonce }, options.headers || {});
        const response = await fetch(CFG.apiBase + endpoint, merged);
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || `Error ${response.status}`);
        return data;
    }

    function escHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmt(num) {
        const n = parseFloat(num);
        return isNaN(n) ? '-' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtFecha(iso) {
        if (!iso) return '-';
        const p = String(iso).split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    function getSolicitudEstado(sol) {
        return estadoUI.resolveEstadoSolicitud(sol && sol.estado);
    }

    function tieneEvento(sol, evento) {
        return Array.isArray(sol && sol.historial) && sol.historial.some(item => item && item.evento === evento);
    }

    function getRendicionEstado(sol) {
        return estadoUI.resolveEstadoRendicion({
            estadoSolicitud: sol && sol.estado,
            estadoRendicion: sol && sol.estado_rendicion,
            rendicionFinalizada: sol && sol.rendicion_finalizada,
            totalRendido: sol && sol.total_rendido,
            tieneGastos: tieneEvento(sol, 'rendicion_iniciada')
        });
    }

    function renderSolicitudBadge(sol) {
        return estadoUI.renderBadgeEstado('solicitud', getSolicitudEstado(sol));
    }

    function renderRendicionBadge(sol) {
        return estadoUI.renderBadgeEstado('rendicion', getRendicionEstado(sol));
    }

    function normalizeRouteName(value) {
        return ROUTE_CONFIG[value] ? value : 'anticipos';
    }

    function normalizeListRouteName(value) {
        return value === 'rendiciones' ? 'rendiciones' : 'anticipos';
    }

    function normalizeSolicitudId(value) {
        const parsed = parseInt(value, 10);
        return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
    }

    function getCurrentRoute() {
        const params = new URLSearchParams(window.location.search);
        const routeName = normalizeRouteName(params.get('view'));
        const fromRoute = normalizeListRouteName(params.get('from'));
        const routeConfig = ROUTE_CONFIG[routeName];
        const solicitudId = normalizeSolicitudId(params.get('id'));

        if (routeConfig.requiresId && !solicitudId) {
            return {
                name: fromRoute,
                viewId: ROUTE_CONFIG[fromRoute].viewId,
                breadcrumb: ROUTE_CONFIG[fromRoute].breadcrumb,
                id: null,
                from: null,
            };
        }

        return {
            name: routeName,
            viewId: routeConfig.viewId,
            breadcrumb: routeConfig.breadcrumb,
            id: solicitudId,
            from: routeName === 'solicitud' ? fromRoute : null,
        };
    }

    function buildRouteUrl(route) {
        const routeName = normalizeRouteName(route.name);
        const routeConfig = ROUTE_CONFIG[routeName];
        const solicitudId = normalizeSolicitudId(route.id);
        const fromRoute = normalizeListRouteName(route.from);
        const url = new URL(window.location.href);

        url.searchParams.set('view', routeName);

        if (routeConfig.requiresId && solicitudId) {
            url.searchParams.set('id', String(solicitudId));
            url.searchParams.set('from', fromRoute);
        } else {
            url.searchParams.delete('id');
            url.searchParams.delete('from');
        }

        return `${url.pathname}${url.search}`;
    }

    function updateHistory(route, historyMode) {
        const url = buildRouteUrl(route);
        if (historyMode === 'replace') {
            window.history.replaceState(route, '', url);
            return;
        }
        if (historyMode === 'push') {
            window.history.pushState(route, '', url);
        }
    }

    function updateRouteLinks() {
        document.querySelectorAll('[data-route]').forEach(element => {
            element.setAttribute('href', buildRouteUrl({
                name: element.dataset.route,
                id: element.dataset.routeId || null,
                from: element.dataset.routeFrom || null,
            }));
        });
    }

    function showToast(type, title, message = '', duration = 4500) {
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`
        };
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span class="toast-icon">${icons[type]}</span><div class="toast-body"><strong>${escHtml(title)}</strong>${message ? `<p>${escHtml(message)}</p>` : ''}</div>`;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => {
            toast.style.cssText = 'opacity:0;transform:translateX(20px);transition:all .3s ease;';
            setTimeout(() => toast.remove(), 320);
        }, duration);
    }

    function setButtonLoading(btn, on) {
        if (on) {
            btn.disabled = true;
            btn.dataset.orig = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
            return;
        }
        btn.disabled = false;
        btn.innerHTML = btn.dataset.orig || '';
    }

    function getAnticipoActionConfig(sol) {
        const solicitudEstado = getSolicitudEstado(sol);
        if (solicitudEstado === 'pendiente') {
            return { type: 'button', label: 'Evaluar solicitud', action: 'evaluate', highlight: 'pending' };
        }
        if (solicitudEstado === 'observada') {
            return { type: 'text', label: estadoUI.getLabelEstado('solicitud', solicitudEstado), tone: 'warning' };
        }
        if (solicitudEstado === 'rechazada') {
            return { type: 'text', label: estadoUI.getLabelEstado('solicitud', solicitudEstado), tone: 'danger' };
        }
        return { type: 'text', label: estadoUI.getLabelEstado('solicitud', solicitudEstado), tone: 'muted' };
    }

    function getRendicionActionConfig(sol) {
        const rendicionEstado = getRendicionEstado(sol);
        if (rendicionEstado === 'en_revision') {
            return { type: 'button', label: 'Revisar rendicion', action: 'review', highlight: 'review' };
        }
        if (rendicionEstado === 'no_iniciada') {
            return { type: 'text', label: 'Esperando rendicion', tone: 'muted' };
        }
        if (rendicionEstado === 'en_proceso') {
            return { type: 'text', label: 'En proceso', tone: 'progress' };
        }
        if (rendicionEstado === 'observada') {
            return { type: 'text', label: 'Observada', tone: 'warning' };
        }
        if (rendicionEstado === 'aprobada') {
            return { type: 'text', label: 'Completado', tone: 'success' };
        }
        if (rendicionEstado === 'rechazada') {
            return { type: 'text', label: 'Rechazada', tone: 'danger' };
        }
        return { type: 'text', label: estadoUI.getLabelEstado('rendicion', rendicionEstado), tone: 'muted' };
    }

    function getActionConfig(sol, viewId) {
        return viewId === 'view-rendiciones' ? getRendicionActionConfig(sol) : getAnticipoActionConfig(sol);
    }

    function renderActionCell(sol, viewId) {
        const action = getActionConfig(sol, viewId);
        if (action.type === 'button') {
            const icon = action.action === 'review'
                ? '<path d="M12 6a9.77 9.77 0 0 1 8.82 6A9.77 9.77 0 0 1 12 18a9.77 9.77 0 0 1-8.82-6A9.77 9.77 0 0 1 12 6zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.2a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6z"/>'
                : '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>';
            return `<button class="btn btn-primary btn-sm worktray-primary js-row-action" data-id="${sol.id}" data-view="${viewId}" data-action="${action.action}"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">${icon}</svg>${escHtml(action.label)}</button>`;
        }
        return `<span class="worktray-note ${action.tone || ''}">${escHtml(action.label)}</span>`;
    }

    function updateBackButton(route = getCurrentRoute()) {
        const text = document.getElementById('btn-volver-lista-texto');
        if (!text) return;
        const originRoute = route.name === 'solicitud'
            ? normalizeListRouteName(route.from)
            : normalizeListRouteName(route.name);
        text.textContent = originRoute === 'rendiciones' ? 'Volver a Rendiciones' : 'Volver a Anticipos';
    }

    function setActiveView(viewId) {
        document.querySelectorAll('.erp-view').forEach(view => view.classList.remove('active'));
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');
        const activeRoute = VIEW_TO_ROUTE[viewId];
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.toggle('active', activeRoute && link.dataset.route === activeRoute);
        });
        const breadcrumb = document.getElementById('topbar-section-name');
        if (breadcrumb) {
            breadcrumb.textContent = activeRoute && ROUTE_CONFIG[activeRoute]
                ? ROUTE_CONFIG[activeRoute].breadcrumb
                : 'Detalle de solicitud';
        }
    }

    function openRow(sol, viewId) {
        if (viewId === 'view-rendiciones') {
            openSolicitudDetail(sol.id, viewId);
            return;
        }
        openSolicitudModal(sol);
    }

    function getSearchValue(viewId) {
        const cfg = VIEW_CONFIG[viewId];
        const input = cfg ? document.getElementById(cfg.searchId) : null;
        return input ? input.value : '';
    }

    function setTableLoading(viewId) {
        const cfg = VIEW_CONFIG[viewId];
        const tbody = cfg ? document.getElementById(cfg.tbodyId) : null;
        if (!tbody) return;
        tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>${cfg.loadingText}</div></td></tr>`;
    }

    function renderTable(viewId, data, filter = '') {
        const cfg = VIEW_CONFIG[viewId];
        if (!cfg) return;
        const tbody = document.getElementById(cfg.tbodyId);
        const counter = document.getElementById(cfg.counterId);
        const q = filter.toLowerCase().trim();
        let rows = data.filter(cfg.filter);
        if (q) {
            rows = rows.filter(sol => (
                String(sol.id).includes(q) ||
                (sol.colaborador || '').toLowerCase().includes(q) ||
                (sol.ceco || '').toLowerCase().includes(q) ||
                (sol.motivo || '').toLowerCase().includes(q)
            ));
        }
        if (counter) counter.textContent = `${rows.length} registro(s)`;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>${q ? 'No se encontraron resultados.' : cfg.emptyText}</p></div></td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map(sol => {
            const action = getActionConfig(sol, viewId);
            const classes = ['worktray-row'];
            if (action.highlight === 'pending') classes.push('is-needs-action');
            if (action.highlight === 'review') classes.push('is-needs-action', 'is-review-action');
            return `
                <tr class="${classes.join(' ')}" data-id="${sol.id}" data-view="${viewId}" tabindex="0">
                    <td class="muted">#${sol.id}</td>
                    <td><div class="worktray-person"><strong>${escHtml(sol.colaborador || 'Sin nombre')}</strong><span>${escHtml(sol.ceco || 'Sin CECO')}</span></div></td>
                    <td>${fmtFecha(sol.fecha)}</td>
                    <td><strong>${fmt(sol.monto)}</strong></td>
                    <td>${renderSolicitudBadge(sol)}</td>
                    <td>${renderRendicionBadge(sol)}</td>
                    <td>${renderActionCell(sol, viewId)}</td>
                </tr>`;
        }).join('');

        tbody.querySelectorAll('.worktray-row').forEach(row => {
            const id = parseInt(row.dataset.id, 10);
            const rowView = row.dataset.view;
            const sol = rows.find(item => item.id === id);
            if (!sol) return;
            const open = event => {
                if (event && event.target && event.target.closest('button, a')) return;
                openRow(sol, rowView);
            };
            row.addEventListener('click', open);
            row.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openRow(sol, rowView);
                }
            });
        });

        tbody.querySelectorAll('.js-row-action').forEach(btn => {
            btn.addEventListener('click', event => {
                event.stopPropagation();
                const id = parseInt(btn.dataset.id, 10);
                const rowView = btn.dataset.view;
                const sol = rows.find(item => item.id === id);
                if (!sol) return;
                if (btn.dataset.action === 'review') {
                    openSolicitudDetail(id, rowView);
                    return;
                }
                openSolicitudModal(sol);
            });
        });
    }

    function renderAllTables() {
        LIST_VIEWS.forEach(viewId => renderTable(viewId, cache, getSearchValue(viewId)));
    }

    async function loadSolicitudDetailView(route = getCurrentRoute()) {
        if (!route.id) {
            await navigateTo({ name: normalizeListRouteName(route.from) }, { historyMode: 'replace' });
            return;
        }

        const container = document.getElementById('solicitud-detalle-content');
        container.innerHTML = `<div style="padding:20px;"><div class="tbl-loading"><div class="spinner"></div>Cargando detalle...</div></div>`;
        try {
            const detalle = await apiFetch(`/detalle-rendicion-admin/${route.id}`);
            renderDetalle(detalle);
        } catch (error) {
            container.innerHTML = `<div style="padding:20px;"><div class="tbl-empty"><p>Error: ${escHtml(error.message)}</p></div></div>`;
            showToast('error', 'No se pudo abrir el detalle', error.message);
        }
    }

    async function loadSolicitudes() {
        LIST_VIEWS.forEach(setTableLoading);
        try {
            cache = await apiFetch('/todas-solicitudes');
            renderAllTables();
        } catch (error) {
            LIST_VIEWS.forEach(viewId => {
                const cfg = VIEW_CONFIG[viewId];
                const tbody = document.getElementById(cfg.tbodyId);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>Error: ${escHtml(error.message)}</p></div></td></tr>`;
                }
            });
            showToast('error', 'Error al cargar solicitudes', error.message);
        }
    }

    function closeSolicitudModal() {
        document.getElementById('modal-solicitud').classList.remove('open');
        document.body.style.overflow = '';
        modalSolId = null;
        document.getElementById('modal-solicitud-error').style.display = 'none';
    }

    function toggleModalDecision(canEvaluate) {
        const label = document.getElementById('modal-solicitud-label');
        const actionIds = ['btn-modal-aprobar', 'btn-modal-observar', 'btn-modal-rechazar'];
        label.style.display = canEvaluate ? '' : 'none';
        actionIds.forEach(id => {
            const btn = document.getElementById(id);
            btn.style.display = canEvaluate ? '' : 'none';
            btn.disabled = !canEvaluate;
        });
    }

    function openSolicitudModal(sol) {
        modalSolId = sol.id;
        document.getElementById('modal-solicitud-titulo').textContent = `Solicitud #${sol.id}`;
        document.getElementById('modal-solicitud-subtitulo').textContent = sol.colaborador || '';
        document.getElementById('modal-det-monto').textContent = fmt(sol.monto);
        document.getElementById('modal-det-fecha').textContent = fmtFecha(sol.fecha);
        document.getElementById('modal-det-colaborador').textContent = sol.colaborador || '-';
        document.getElementById('modal-det-dni').textContent = sol.dni || '-';
        document.getElementById('modal-det-ceco').textContent = sol.ceco || '-';
        document.getElementById('modal-det-estado-solicitud').innerHTML = renderSolicitudBadge(sol);
        document.getElementById('modal-det-estado-rendicion').innerHTML = renderRendicionBadge(sol);
        document.getElementById('modal-det-motivo').textContent = sol.motivo || '-';
        document.getElementById('modal-det-historial').innerHTML = timelineUI.renderTimeline(sol.historial);
        document.getElementById('modal-solicitud-error').style.display = 'none';
        toggleModalDecision(getSolicitudEstado(sol) === 'pendiente');
        document.getElementById('modal-solicitud').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    async function handleSolicitudDecision(nuevoEstado) {
        if (!modalSolId) return;
        const btnMap = { aprobada: 'btn-modal-aprobar', observada: 'btn-modal-observar', rechazada: 'btn-modal-rechazar' };
        const btn = document.getElementById(btnMap[nuevoEstado]);
        const errEl = document.getElementById('modal-solicitud-error');
        errEl.style.display = 'none';
        setButtonLoading(btn, true);
        ['btn-modal-aprobar', 'btn-modal-observar', 'btn-modal-rechazar'].forEach(id => {
            if (id !== btnMap[nuevoEstado]) document.getElementById(id).disabled = true;
        });
        try {
            await apiFetch('/actualizar-estado', { method: 'POST', body: JSON.stringify({ id_solicitud: modalSolId, nuevo_estado: nuevoEstado }) });
            closeSolicitudModal();
            showToast('success', 'Estado actualizado', `Solicitud #${modalSolId} ${nuevoEstado}.`);
            await loadSolicitudes();
        } catch (error) {
            errEl.textContent = error.message || 'No se pudo actualizar la solicitud.';
            errEl.style.display = 'block';
            setButtonLoading(btn, false);
            toggleModalDecision(true);
        }
    }

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

    async function loadGastoAdjuntosAdmin(gastoId, itemEl) {
        const panel = itemEl.querySelector('.gasto-adj-panel[data-adj-gasto-id="' + gastoId + '"]');
        if (!panel || panel.dataset.adjLoaded === '1') return;
        panel.dataset.adjLoaded = '1';
        const listEl = panel.querySelector('.gasto-adj-list');
        listEl.innerHTML = '<span class="gasto-adj-loading">Cargando adjuntos...</span>';
        try {
            const res = await apiFetch('/gasto-adjuntos/' + gastoId);
            const adjuntos = res.adjuntos || [];
            if (!adjuntos.length) {
                listEl.innerHTML = '<span class="gasto-adj-empty">Sin adjuntos registrados para este gasto.</span>';
                return;
            }
            listEl.innerHTML = adjuntos.map(adj => `
                <div class="gasto-adj-item">
                    <div class="gasto-adj-icon ${adjIconClass(adj.mime)}">${adjIconLabel(adj.mime)}</div>
                    <span class="gasto-adj-name" title="${escHtml(adj.name)}">${escHtml(adj.name)}</span>
                    <div class="gasto-adj-actions"><a class="gasto-adj-btn" href="${escHtml(adj.url)}" target="_blank" rel="noopener">Ver / Descargar</a></div>
                </div>`).join('');
        } catch (error) {
            listEl.innerHTML = '<span class="gasto-adj-empty" style="color:#C53030;">Error al cargar adjuntos.</span>';
        }
    }

    function buildDetalleDecisionArea(detalle) {
        const rendicionEstado = getRendicionEstado(detalle);
        if (rendicionEstado !== 'en_revision') {
            return `<div id="rendicion-decision-area" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:16px 20px; border-top:1px solid #E5E7EB; background:#FAFBFC;"><span style="font-size:12px;color:var(--text-muted);">Estado actual de rendicion: ${escHtml(estadoUI.getLabelEstado('rendicion', rendicionEstado))}</span></div>`;
        }
        return `
            <div id="rendicion-decision-area" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:16px 20px; border-top:1px solid #E5E7EB; background:#FAFBFC;">
                <span style="font-size:12px; font-weight:600; color:var(--text-muted); margin-right:4px;">Decision:</span>
                <button class="btn btn-success btn-sm js-decidir-rendicion" data-decision="aprobada"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Aprobar</button>
                <button class="btn btn-warning btn-sm js-decidir-rendicion" data-decision="observada"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>Observar</button>
                <button class="btn btn-danger btn-sm js-decidir-rendicion" data-decision="rechazada"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Rechazar</button>
            </div>`;
    }

    function renderDetalle(detalle) {
        const container = document.getElementById('solicitud-detalle-content');
        if (!container) return;
        const gastos = Array.isArray(detalle.gastos) ? detalle.gastos : [];
        const colaborador = detalle.colaborador || {};
        const saldoNegativo = parseFloat(detalle.saldo) < 0;
        const historial = Array.isArray(detalle.historial) ? detalle.historial : [];
        const gastosHtml = gastos.length
            ? `<div class="gasto-acc-list" id="admin-gastos-acc">${gastos.map((g, i) => gastoUI.renderGastoItem(g, `adm-${detalle.id}-${i}`)).join('')}</div>`
            : `<div class="table-empty" style="padding:32px 20px;"><p>No hay gastos asociados a esta solicitud.</p></div>`;
        container.innerHTML = `
            <div class="section-block"><div class="section-header"><div class="section-header-title"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>Estados</div></div><div class="section-body"><div class="estados-row"><div class="estado-panel estado-panel-solicitud"><div class="estado-panel-label">Solicitud</div><div class="estado-panel-badge">${renderSolicitudBadge(detalle)}</div></div><div class="estado-panel estado-panel-rendicion"><div class="estado-panel-label">Rendicion</div><div class="estado-panel-badge" id="rendicion-estado-badge">${renderRendicionBadge(detalle)}</div></div></div></div></div>
            <div class="section-block"><div class="section-header"><div class="section-header-title"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .97-.79 2.03-2.5 2.03-2.08 0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>Resumen economico</div></div><div class="section-body"><div class="resumen-economico"><div class="resumen-card monto-solicitado"><div class="resumen-card-label">Monto solicitado</div><div class="resumen-card-value">${fmt(detalle.monto)}</div></div><div class="resumen-card total-rendido"><div class="resumen-card-label">Total rendido</div><div class="resumen-card-value">${fmt(detalle.total_rendido)}</div></div><div class="resumen-card ${saldoNegativo ? 'saldo-negativo' : 'saldo'}"><div class="resumen-card-label">Saldo</div><div class="resumen-card-value ${saldoNegativo ? 'saldo-negativo' : 'saldo'}">${fmt(detalle.saldo)}</div></div></div></div></div>
            <div class="section-block"><div class="section-header"><div class="section-header-title"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>Datos generales</div></div><div class="section-body"><div class="datos-grid"><div class="dato-item"><div class="dato-label">Colaborador</div><div class="dato-value">${escHtml(colaborador.display_name || '-')}</div></div><div class="dato-item"><div class="dato-label">Email</div><div class="dato-value">${escHtml(colaborador.email || '-')}</div></div><div class="dato-item"><div class="dato-label">DNI</div><div class="dato-value">${escHtml(detalle.dni || '-')}</div></div><div class="dato-item"><div class="dato-label">Fecha de viaje</div><div class="dato-value">${fmtFecha(detalle.fecha_viaje)}</div></div><div class="dato-item"><div class="dato-label">CECO / Proyecto</div><div class="dato-value">${escHtml(detalle.ceco || '-')}</div></div><div class="dato-motivo"><div class="dato-label">Motivo del viaje</div><div class="dato-value muted">${escHtml(detalle.motivo || '-')}</div></div></div></div></div>
            <div class="section-block"><div class="section-header"><div class="section-header-title"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 3a9 9 0 1 0 8.95 10H20a7 7 0 1 1-2.05-4.95L16 10h6V4l-2.64 2.64A8.96 8.96 0 0 0 13 3zm-1 5h2v5l4.25 2.52-1 1.68L12 14V8z"/></svg>Historial</div><div class="section-header-subtitle">${historial.length} evento(s)</div></div><div class="section-body">${timelineUI.renderTimeline(historial)}</div></div>
            ${buildDetalleDecisionArea(detalle)}
            <div id="rendicion-decision-error" style="display:none; margin:0 20px 12px; padding:10px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
            <div class="section-block" style="margin-top:20px;"><div class="section-header"><div class="section-header-title"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>Gastos asociados</div><div class="section-header-subtitle">${gastos.length} registro(s) - Total: ${fmt(detalle.total_rendido)}</div></div><div class="section-body" style="padding:16px 20px;">${gastosHtml}</div></div>`;
        const accContainer = container.querySelector('#admin-gastos-acc');
        if (accContainer) {
            gastoUI.bindAccordionList(accContainer, {
                onOpen: function (itemEl, gastoId) {
                    if (gastoId) loadGastoAdjuntosAdmin(gastoId, itemEl);
                }
            });
        }
        if (detalle.rendicion_finalizada) {
            const liqData = window.ViaticosLiquidacion.buildData(
                {
                    id: detalle.id,
                    monto: detalle.monto,
                    fecha: detalle.fecha_viaje,
                    motivo: detalle.motivo,
                    ceco: detalle.ceco,
                    dni: detalle.dni,
                    estado_rendicion: detalle.estado_rendicion,
                    rendicion_finalizada: detalle.rendicion_finalizada
                },
                gastos,
                {
                    colaboradorNombre: colaborador.display_name || '',
                    fechaRendicion: detalle.fecha_creacion || ''
                }
            );
            const wrap = document.createElement('div');
            wrap.style.cssText = 'margin:20px;';
            wrap.innerHTML = window.ViaticosLiquidacion.renderDoc(liqData);
            container.appendChild(wrap);
        }
        container.querySelectorAll('.js-decidir-rendicion').forEach(btn => {
            btn.addEventListener('click', () => handleDecisionRendicion(detalle.id, btn.dataset.decision));
        });
    }

    async function openSolicitudDetail(idSolicitud, fromView) {
        await navigateTo({
            name: 'solicitud',
            id: idSolicitud,
            from: VIEW_TO_ROUTE[fromView] || fromView,
        });
    }

    async function handleDecisionRendicion(idSolicitud, decision) {
        const errEl = document.getElementById('rendicion-decision-error');
        if (errEl) errEl.style.display = 'none';
        const buttons = document.querySelectorAll('.js-decidir-rendicion');
        buttons.forEach(btn => { btn.disabled = true; });
        const activeBtn = [...buttons].find(btn => btn.dataset.decision === decision);
        if (activeBtn) {
            activeBtn.dataset.orig = activeBtn.innerHTML;
            activeBtn.innerHTML = `<div class="spinner" style="width:13px;height:13px;border-width:2px;"></div> Procesando...`;
        }
        try {
            await apiFetch('/decidir-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: parseInt(idSolicitud, 10), decision }) });
            const detalleActualizado = await apiFetch(`/detalle-rendicion-admin/${idSolicitud}`);
            renderDetalle(detalleActualizado);
            await loadSolicitudes();
            showToast('success', 'Decision registrada', `Rendicion de solicitud #${idSolicitud} ${decision}.`);
        } catch (error) {
            if (errEl) {
                errEl.textContent = error.message || 'No se pudo registrar la decision.';
                errEl.style.display = 'block';
            }
            buttons.forEach(btn => {
                btn.disabled = false;
                if (btn === activeBtn && btn.dataset.orig) btn.innerHTML = btn.dataset.orig;
            });
        }
    }

    async function renderRoute(route) {
        setActiveView(route.viewId);
        updateBackButton(route);

        if (route.name === 'solicitud') {
            await loadSolicitudDetailView(route);
            return;
        }

        if (VIEW_CONFIG[route.viewId]) {
            renderTable(route.viewId, cache, getSearchValue(route.viewId));
        }
    }

    async function navigateTo(target, options = {}) {
        const routeName = typeof target === 'string'
            ? (VIEW_TO_ROUTE[target] || normalizeRouteName(target))
            : normalizeRouteName(target.name || VIEW_TO_ROUTE[target.viewId]);
        const route = {
            name: routeName,
            id: typeof target === 'object' ? target.id : options.id,
            from: typeof target === 'object' ? target.from : options.from,
        };

        updateHistory(route, options.historyMode || 'push');
        await renderRoute(getCurrentRoute());
    }

    function bindEvents() {
        document.querySelectorAll('[data-route]').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                navigateTo({
                    name: link.dataset.route,
                    id: link.dataset.routeId || null,
                    from: link.dataset.routeFrom || null,
                });
            });
        });

        document.querySelectorAll('.js-btn-refrescar').forEach(btn => {
            btn.addEventListener('click', loadSolicitudes);
        });

        LIST_VIEWS.forEach(viewId => {
            const cfg = VIEW_CONFIG[viewId];
            const input = document.getElementById(cfg.searchId);
            if (!input) return;
            input.addEventListener('input', event => {
                renderTable(viewId, cache, event.target.value);
            });
        });

        document.getElementById('btn-volver-lista').addEventListener('click', () => {
            const route = getCurrentRoute();
            navigateTo({ name: normalizeListRouteName(route.from || route.name) });
        });
        document.getElementById('btn-cerrar-solicitud-modal').addEventListener('click', closeSolicitudModal);
        document.getElementById('btn-cancelar-solicitud-modal').addEventListener('click', closeSolicitudModal);
        document.getElementById('modal-solicitud').addEventListener('click', event => {
            if (event.target === document.getElementById('modal-solicitud')) closeSolicitudModal();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeSolicitudModal();
        });
        document.getElementById('btn-modal-aprobar').addEventListener('click', () => handleSolicitudDecision('aprobada'));
        document.getElementById('btn-modal-observar').addEventListener('click', () => handleSolicitudDecision('observada'));
        document.getElementById('btn-modal-rechazar').addEventListener('click', () => handleSolicitudDecision('rechazada'));
        window.addEventListener('popstate', () => {
            renderRoute(getCurrentRoute());
        });
    }

    async function init() {
        bindEvents();
        updateRouteLinks();
        const route = getCurrentRoute();
        updateHistory(route, 'replace');
        await loadSolicitudes();
        await renderRoute(route);
    }

    window.AdminApp = {
        navigate: navigateTo,
        loadSolicitudes,
        refreshRows: loadSolicitudes,
        showToast,
        openSolicitudDetail
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

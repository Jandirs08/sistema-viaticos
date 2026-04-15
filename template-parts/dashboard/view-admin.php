<?php
/**
 * Template Part: Dashboard — Vista Administrador
 *
 * Renders the full Admin SPA: sidebar nav links, view-resumen,
 * view-solicitudes, the evaluation modal, and the AdminApp JS module.
 *
 * Expected args (injected by page-dashboard.php):
 *   $args['rest_nonce']  string  WP REST nonce.
 *   $args['api_base']    string  Base REST URL (no trailing slash).
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

<!-- Inject sidebar nav items for the admin role -->
<script>
(function () {
    var nav = document.getElementById('sidebar-nav-items');
    if (nav) {
        nav.innerHTML = `
            <li>
                <a href="#" class="nav-link active" data-view="view-resumen" id="nav-resumen">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    Resumen
                </a>
            </li>
            <li>
                <a href="#" class="nav-link" data-view="view-solicitudes" id="nav-solicitudes">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    Solicitudes Equipo
                </a>
            </li>`;
    }
    // Set initial breadcrumb
    var bc = document.getElementById('topbar-section-name');
    if (bc) bc.textContent = 'Resumen';
})();
</script>

<div class="page-header" style="margin-bottom:20px;">
    <div class="page-header-left">
        <h1>Panel de Administración</h1>
        <p>Gestiona solicitudes, usuarios y operación del portal de viáticos.</p>
    </div>
    <a
        href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"
        class="btn btn-secondary"
        target="_blank"
        rel="noopener noreferrer"
    >
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Gestionar Usuarios
    </a>
</div>

<!-- ================================================
     VISTA: RESUMEN
     ================================================ -->
<section id="view-resumen" class="erp-view active">

    <div class="welcome-banner">
        <div>
            <h2>Panel de Administración</h2>
            <p>Gestión centralizada de viáticos — Fundación Romero</p>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon yellow">
                <svg viewBox="0 0 24 24" fill="#D97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
            <div>
                <div class="kpi-num" id="kpi-pendientes">—</div>
                <div class="kpi-label">Solicitudes Pendientes</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green">
                <svg viewBox="0 0 24 24" fill="#059669"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </div>
            <div>
                <div class="kpi-num" id="kpi-monto-aprobado">—</div>
                <div class="kpi-label">Total Aprobado (S/.)</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon orange">
                <svg viewBox="0 0 24 24" fill="#EA580C"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
            </div>
            <div>
                <div class="kpi-num" id="kpi-observadas">—</div>
                <div class="kpi-label">Observadas</div>
            </div>
        </div>
    </div>

    <!-- Recent activity table -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">Solicitudes Recientes</div>
                <div class="card-header-subtitle">Últimas 8 del equipo</div>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="AdminApp.navigate('view-solicitudes')" id="btn-ver-todas">Ver todas</button>
        </div>
        <div class="table-wrap">
            <table class="tbl" aria-label="Solicitudes recientes">
                <thead>
                    <tr>
                        <th>ID</th><th>Colaborador</th><th>Fecha Viaje</th>
                        <th>Monto</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody id="resumen-tbody">
                    <tr><td colspan="5"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-resumen -->


<!-- ================================================
     VISTA: SOLICITUDES EQUIPO
     ================================================ -->
<section id="view-solicitudes" class="erp-view">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Solicitudes del Equipo</h1>
            <p>Revisa, filtra y evalúa todas las solicitudes de viáticos.</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input
                type="search"
                id="search-solicitudes"
                class="search-input"
                placeholder="Buscar colaborador, CECO…"
                autocomplete="off"
            >
            <button class="btn btn-ghost btn-sm" id="btn-refrescar" title="Actualizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Actualizar
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">Todas las Solicitudes</div>
            <div id="tbl-counter" style="font-size:12px;color:var(--text-muted);"></div>
        </div>
        <div class="table-wrap">
            <table class="tbl" aria-label="Todas las solicitudes de viáticos">
                <thead>
                    <tr>
                        <th>ID</th><th>Colaborador</th><th>Fecha Viaje</th>
                        <th>Monto</th><th>CECO / Proyecto</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes-tbody">
                    <tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando solicitudes...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-solicitudes -->


<!-- ================================================
     MODAL: EVALUAR SOLICITUD
     ================================================ -->
<div class="overlay" id="modal-evaluar" role="dialog" aria-modal="true" aria-labelledby="modal-evaluar-titulo">
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <div>
                <h2 id="modal-evaluar-titulo">Evaluar Solicitud <span id="evaluar-sol-id" style="font-weight:400;color:var(--text-muted);"></span></h2>
                <p id="evaluar-sol-colaborador" style="margin-top:2px;"></p>
            </div>
            <button class="modal-close" id="btn-cerrar-evaluar" aria-label="Cerrar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="di-label">Monto Solicitado</div>
                    <div class="di-value" id="det-monto">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">Fecha del Viaje</div>
                    <div class="di-value" id="det-fecha">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">CECO / Proyecto</div>
                    <div class="di-value" id="det-ceco">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">DNI Colaborador</div>
                    <div class="di-value" id="det-dni">—</div>
                </div>
                <div class="detail-item col-full">
                    <div class="di-label">Estado Actual</div>
                    <div class="di-value" id="det-estado">—</div>
                </div>
                <div class="detail-item col-full">
                    <div class="di-label">Motivo del Viaje</div>
                    <div class="motivo-box" id="det-motivo">—</div>
                </div>
            </div>
            <div id="evaluar-error" style="display:none; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px; margin-top:4px;"></div>
        </div>

        <div class="modal-footer">
            <span class="modal-decision-label" style="margin-right:auto;">Decisión:</span>
            <button class="btn btn-secondary" id="btn-cancelar-evaluar">Cancelar</button>
            <button class="btn btn-warning" id="btn-observar" data-estado="observada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                Observar
            </button>
            <button class="btn btn-danger" id="btn-rechazar" data-estado="rechazada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                Rechazar
            </button>
            <button class="btn btn-success" id="btn-aprobar" data-estado="aprobada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Aprobar
            </button>
        </div>
    </div>
</div>


<!-- ================================================
     JAVASCRIPT — AdminApp
     ================================================ -->
<script>
(function () {
    'use strict';

    /* ── Config (PHP-injected) ────────────────────────────── */
    const CFG = {
        nonce:   '<?php echo esc_js( $args['rest_nonce'] ); ?>',
        apiBase: '<?php echo esc_js( $args['api_base'] ); ?>',
    };

    /* ── Utilities ────────────────────────────────────────── */
    async function apiFetch(endpoint, options = {}) {
        const merged = Object.assign({ headers: {} }, options);
        merged.headers = Object.assign({
            'Content-Type': 'application/json',
            'X-WP-Nonce':   CFG.nonce,
        }, options.headers || {});
        const res  = await fetch(CFG.apiBase + endpoint, merged);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `Error ${res.status}`);
        return data;
    }

    function fmt(num) {
        const n = parseFloat(num);
        return isNaN(n) ? '—' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtFecha(iso) {
        if (!iso) return '—';
        const p = iso.split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    const estadoLabel = { pendiente:'Pendiente', aprobada:'Aprobada', observada:'Observada', rechazada:'Rechazada', rendida:'Rendida' };

    function badgeHTML(estado) {
        const k = (estado || '').toLowerCase();
        return `<span class="badge badge-${k}">${estadoLabel[k] || estado}</span>`;
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function showToast(type, title, msg = '', duration = 4500) {
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41.0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
        };
        const t = document.createElement('div');
        t.className = `toast toast-${type}`;
        t.innerHTML = `<span class="toast-icon">${icons[type]}</span><div class="toast-body"><strong>${escHtml(title)}</strong>${msg ? `<p>${escHtml(msg)}</p>` : ''}</div>`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => {
            t.style.cssText = 'opacity:0;transform:translateX(20px);transition:all .3s ease;';
            setTimeout(() => t.remove(), 320);
        }, duration);
    }

    function setLoading(btn, on) {
        if (on) {
            btn.disabled = true;
            btn.dataset.orig = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.orig || '';
        }
    }

    /* ── Data ─────────────────────────────────────────────── */
    let cache = [];

    async function fetchTodas() {
        return await apiFetch('/todas-solicitudes');
    }

    /* ── Table: Solicitudes ───────────────────────────────── */
    function renderSolicitudesTable(data, filter = '') {
        const tbody = document.getElementById('solicitudes-tbody');
        const q = filter.toLowerCase().trim();
        let rows = data;
        if (q) {
            rows = data.filter(s =>
                (s.colaborador || '').toLowerCase().includes(q) ||
                (s.ceco        || '').toLowerCase().includes(q) ||
                (s.motivo      || '').toLowerCase().includes(q) ||
                String(s.id).includes(q)
            );
        }
        document.getElementById('tbl-counter').textContent = `${rows.length} de ${data.length} registros`;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                <p>${q ? 'Sin resultados para "' + escHtml(q) + '".' : 'No hay solicitudes registradas.'}</p>
            </div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(s => {
            const estado   = (s.estado || 'pendiente').toLowerCase();
            const evaluable = estado === 'pendiente' || estado === 'rendida';
            const accion = evaluable
                ? `<button class="btn btn-primary btn-sm action-evaluar" data-id="${s.id}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        Evaluar
                   </button>`
                : `<span style="color:var(--text-light);font-size:12px;">Sin acciones</span>`;
            return `<tr>
                <td class="muted">#${s.id}</td>
                <td><strong>${escHtml(s.colaborador)}</strong></td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${escHtml(s.ceco)}</td>
                <td>${badgeHTML(estado)}</td>
                <td>${accion}</td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('.action-evaluar').forEach(btn => {
            btn.addEventListener('click', () => {
                const sol = data.find(s => s.id === parseInt(btn.dataset.id, 10));
                if (sol) openEvaluarModal(sol);
            });
        });
    }

    /* ── Table: Resumen (last 8 + KPIs) ──────────────────── */
    function renderResumenTable(data) {
        const tbody = document.getElementById('resumen-tbody');

        let pendientes = 0, montoAprobado = 0, observadas = 0;
        data.forEach(s => {
            const e = (s.estado || '').toLowerCase();
            if (e === 'pendiente') pendientes++;
            if (e === 'aprobada')  montoAprobado += parseFloat(s.monto) || 0;
            if (e === 'observada') observadas++;
        });
        document.getElementById('kpi-pendientes').textContent     = pendientes;
        document.getElementById('kpi-monto-aprobado').textContent = fmt(montoAprobado);
        document.getElementById('kpi-observadas').textContent     = observadas;

        const recent = data.slice(0, 8);
        if (!recent.length) {
            tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-empty"><p>No hay solicitudes registradas.</p></div></td></tr>`;
            return;
        }
        tbody.innerHTML = recent.map(s => `
            <tr>
                <td class="muted">#${s.id}</td>
                <td>${escHtml(s.colaborador)}</td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${badgeHTML(s.estado)}</td>
            </tr>`).join('');
    }

    /* ── Load per view ────────────────────────────────────── */
    async function loadResumen() {
        const tbody = document.getElementById('resumen-tbody');
        tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            renderResumenTable(cache);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
            showToast('error', 'Error al cargar datos', err.message);
        }
    }

    async function loadSolicitudes() {
        const tbody = document.getElementById('solicitudes-tbody');
        tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            const q = document.getElementById('search-solicitudes').value;
            renderSolicitudesTable(cache, q);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
            showToast('error', 'Error al cargar solicitudes', err.message);
        }
    }

    /* ── Modal: Evaluar ───────────────────────────────────── */
    let modalSolId = null;

    function openEvaluarModal(sol) {
        modalSolId = sol.id;
        document.getElementById('evaluar-sol-id').textContent         = `#${sol.id}`;
        document.getElementById('evaluar-sol-colaborador').textContent = sol.colaborador || '';
        document.getElementById('det-monto').textContent  = fmt(sol.monto);
        document.getElementById('det-fecha').textContent  = fmtFecha(sol.fecha);
        document.getElementById('det-ceco').textContent   = sol.ceco  || '—';
        document.getElementById('det-dni').textContent    = sol.dni   || '—';
        document.getElementById('det-motivo').textContent = sol.motivo || '—';
        document.getElementById('det-estado').innerHTML   = badgeHTML(sol.estado);
        document.getElementById('evaluar-error').style.display = 'none';
        ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => setLoading(document.getElementById(id), false));
        document.getElementById('modal-evaluar').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEvaluarModal() {
        document.getElementById('modal-evaluar').classList.remove('open');
        document.body.style.overflow = '';
        modalSolId = null;
    }

    async function handleDecision(nuevoEstado) {
        if (!modalSolId) return;
        const btnMap = { aprobada: 'btn-aprobar', observada: 'btn-observar', rechazada: 'btn-rechazar' };
        const btn    = document.getElementById(btnMap[nuevoEstado]);
        const errEl  = document.getElementById('evaluar-error');

        errEl.style.display = 'none';
        setLoading(btn, true);
        ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => {
            if (id !== btnMap[nuevoEstado]) document.getElementById(id).disabled = true;
        });

        try {
            await apiFetch('/actualizar-estado', {
                method: 'POST',
                body:   JSON.stringify({ id_solicitud: modalSolId, nuevo_estado: nuevoEstado }),
            });
            closeEvaluarModal();
            const labels = { aprobada: 'aprobada ✓', observada: 'marcada como observada', rechazada: 'rechazada' };
            showToast('success', 'Estado actualizado', `Solicitud #${modalSolId} ${labels[nuevoEstado]}.`);
            await loadResumen();
            renderSolicitudesTable(cache, document.getElementById('search-solicitudes').value);
        } catch (err) {
            errEl.textContent   = err.message || 'No se pudo actualizar. Intente de nuevo.';
            errEl.style.display = 'block';
            setLoading(btn, false);
            ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => {
                document.getElementById(id).disabled = false;
            });
        }
    }

    /* ── Navigation ───────────────────────────────────────── */
    function navigate(viewId) {
        document.querySelectorAll('.erp-view').forEach(v => v.classList.remove('active'));
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');
        document.querySelectorAll('.nav-link').forEach(a =>
            a.classList.toggle('active', a.dataset.view === viewId)
        );
        const names = { 'view-resumen': 'Resumen', 'view-solicitudes': 'Solicitudes Equipo' };
        document.getElementById('topbar-section-name').textContent = names[viewId] || '';
        if (viewId === 'view-solicitudes') loadSolicitudes();
    }

    /* ── Event binding ────────────────────────────────────── */
    function bindEvents() {
        document.querySelectorAll('.nav-link').forEach(a => {
            a.addEventListener('click', e => { e.preventDefault(); navigate(a.dataset.view); });
        });
        document.getElementById('btn-cerrar-evaluar').addEventListener('click', closeEvaluarModal);
        document.getElementById('btn-cancelar-evaluar').addEventListener('click', closeEvaluarModal);
        document.getElementById('modal-evaluar').addEventListener('click', e => {
            if (e.target === document.getElementById('modal-evaluar')) closeEvaluarModal();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEvaluarModal(); });
        document.getElementById('btn-aprobar').addEventListener('click',  () => handleDecision('aprobada'));
        document.getElementById('btn-observar').addEventListener('click', () => handleDecision('observada'));
        document.getElementById('btn-rechazar').addEventListener('click', () => handleDecision('rechazada'));
        document.getElementById('btn-refrescar').addEventListener('click', loadSolicitudes);
        document.getElementById('search-solicitudes').addEventListener('input', e => {
            renderSolicitudesTable(cache, e.target.value);
        });
    }

    /* ── Init ─────────────────────────────────────────────── */
    function init() { bindEvents(); loadResumen(); }
    window.AdminApp = { navigate };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

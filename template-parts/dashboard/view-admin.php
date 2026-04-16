<?php
/**
 * Template Part: Dashboard — Vista Administrador
 *
 * Renders the full Admin SPA: sidebar nav links, main views,
 * the evaluation modal, and the AdminApp JS module.
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
            </li>
            <li>
                <a href="#" class="nav-link" data-view="view-rendiciones" id="nav-rendiciones">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 14H7v-2h4v2zm6-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    Rendiciones
                </a>
            </li>`;
    }
    // Set initial breadcrumb
    var bc = document.getElementById('topbar-section-name');
    if (bc) bc.textContent = 'Resumen';
})();
</script>
<script>
(function () {
    'use strict';

    const CFG = {
        nonce: '<?php echo esc_js( $args['rest_nonce'] ); ?>',
        apiBase: '<?php echo esc_js( $args['api_base'] ); ?>',
    };

    const previousNavigate = window.AdminApp && typeof window.AdminApp.navigate === 'function'
        ? window.AdminApp.navigate.bind(window.AdminApp)
        : null;

    async function apiFetch(endpoint, options = {}) {
        const merged = Object.assign({ headers: {} }, options);
        merged.headers = Object.assign({
            'Content-Type': 'application/json',
            'X-WP-Nonce': CFG.nonce,
        }, options.headers || {});

        const res = await fetch(CFG.apiBase + endpoint, merged);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `Error ${res.status}`);
        return data;
    }

    function escHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmt(num) {
        const n = parseFloat(num);
        return isNaN(n) ? '—' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtFecha(iso) {
        if (!iso) return '—';
        const p = String(iso).split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    const estadoUI = window.ViaticosEstadoUI;

    // ── Badge helpers (single source — duplicates removed from AdminApp block below) ──
    function badgeHTML(estado) {
        return estadoUI.renderBadgeEstado('solicitud', estadoUI.resolveEstadoSolicitud(estado));
    }

    function estadoRendicionBadge(source, extra = {}) {
        return estadoUI.renderBadgeEstado('rendicion', estadoUI.resolveEstadoRendicion({
            estadoSolicitud: source && source.estado,
            estadoRendicion: source && source.estado_rendicion,
            rendicionFinalizada: source && source.rendicion_finalizada,
            totalRendido: source && source.total_rendido,
            ...extra,
        }));
    }

    // ── Business logic helpers ──────────────────────────────────
    function puedeDecidirRendicion(estadoRend) {
        return estadoRend === 'finalizada' || estadoRend === '';
    }

    function setAdminView(viewId) {
        document.querySelectorAll('.erp-view').forEach(view => view.classList.remove('active'));
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');

        const activeNav = viewId === 'view-rendicion-detalle' ? 'view-rendiciones' : viewId;
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.toggle('active', link.dataset.view === activeNav);
        });

        const breadcrumb = document.getElementById('topbar-section-name');
        if (breadcrumb) {
            const names = {
                'view-rendiciones': 'Rendiciones',
                'view-rendicion-detalle': 'Detalle de Rendicion',
            };
            if (names[viewId]) breadcrumb.textContent = names[viewId];
        }
    }

    async function fetchRendicionesFinalizadas() {
        const solicitudes = await apiFetch('/todas-solicitudes');
        return solicitudes.filter(item =>
            String(item.estado || '').toLowerCase() === 'aprobada' &&
            !!item.rendicion_finalizada
        );
    }

    function renderRendicionesTable(rows) {
        const tbody = document.getElementById('rendiciones-tbody');
        const counter = document.getElementById('tbl-rendiciones-counter');
        if (!tbody || !counter) return;

        counter.textContent = `${rows.length} registros`;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>No hay rendiciones finalizadas para revisar.</p></div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr>
                <td class="muted">#${row.id}</td>
                <td><strong>${escHtml(row.colaborador)}</strong></td>
                <td><strong>${fmt(row.monto)}</strong></td>
                <td><strong>${fmt(row.total_rendido)}</strong></td>
                <td>${badgeHTML(row.estado)}</td>
                <td>${estadoRendicionBadge(row)}</td>
                <td>
                    <button class="btn btn-primary btn-sm js-ver-rendicion" data-id="${row.id}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6a9.77 9.77 0 0 1 8.82 6A9.77 9.77 0 0 1 12 18a9.77 9.77 0 0 1-8.82-6A9.77 9.77 0 0 1 12 6zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.2a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6z"/></svg>
                        Ver rendicion
                    </button>
                </td>
            </tr>
        `).join('');

        tbody.querySelectorAll('.js-ver-rendicion').forEach(btn => {
            btn.addEventListener('click', () => openDetalleRendicion(btn.dataset.id));
        });
    }

    const gastoUI = window.ViaticosGastoUI;

    /* ── Adjuntos helpers (admin, read-only) ───────────────────── */
    function adjIconClassA(mime) {
        if (!mime) return 'file';
        if (mime.includes('pdf')) return 'pdf';
        if (mime.includes('xml')) return 'xml';
        if (mime.includes('image')) return 'img';
        return 'file';
    }
    function adjIconLabelA(mime) {
        if (!mime) return 'FILE';
        if (mime.includes('pdf')) return 'PDF';
        if (mime.includes('xml')) return 'XML';
        if (mime.includes('image')) return mime.includes('png') ? 'PNG' : 'JPG';
        return 'FILE';
    }
    function escAdj(v) {
        return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    async function loadGastoAdjuntosAdmin(gastoId, itemEl) {
        const panel = itemEl.querySelector('.gasto-adj-panel[data-adj-gasto-id="' + gastoId + '"]');
        if (!panel || panel.dataset.adjLoaded === '1') return;
        panel.dataset.adjLoaded = '1';
        const listEl = panel.querySelector('.gasto-adj-list');
        listEl.innerHTML = '<span class="gasto-adj-loading">Cargando adjuntos…</span>';
        try {
            const res     = await apiFetch('/gasto-adjuntos/' + gastoId);
            const adjuntos = res.adjuntos || [];
            if (!adjuntos.length) {
                listEl.innerHTML = '<span class="gasto-adj-empty">Sin adjuntos registrados para este gasto.</span>';
                return;
            }
            listEl.innerHTML = adjuntos.map(adj => `
                <div class="gasto-adj-item">
                    <div class="gasto-adj-icon ${adjIconClassA(adj.mime)}">${adjIconLabelA(adj.mime)}</div>
                    <span class="gasto-adj-name" title="${escAdj(adj.name)}">${escAdj(adj.name)}</span>
                    <div class="gasto-adj-actions">
                        <a class="gasto-adj-btn" href="${escAdj(adj.url)}" target="_blank" rel="noopener">Ver / Descargar</a>
                    </div>
                </div>`).join('');
        } catch(err) {
            listEl.innerHTML = '<span class="gasto-adj-empty" style="color:#C53030;">Error al cargar adjuntos.</span>';
        }
    }

    function renderDetalle(detalle) {
        const container = document.getElementById('rendicion-detalle-content');
        if (!container) return;

        // Guardar id para usarlo en las decisiones.
        container.dataset.idSolicitud = detalle.id;

        const gastos = Array.isArray(detalle.gastos) ? detalle.gastos : [];
        const colaborador = detalle.colaborador || {};
        const saldoNegativo = parseFloat(detalle.saldo) < 0;

        const gastosHtml = gastos.length
            ? `<div class="gasto-acc-list" id="admin-gastos-acc">${
                gastos.map((g, i) => gastoUI.renderGastoItem(g, `adm-${detalle.id}-${i}`)).join('')
              }</div>`
            : `<div class="table-empty" style="padding:32px 20px;">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.3;"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                <p>No hay gastos asociados a esta solicitud.</p>
            </div>`;

        const estadoRend = detalle.estado_rendicion || '';
        const puedeDecidir = puedeDecidirRendicion(estadoRend);
        const decisionBtns = `
            <div id="rendicion-decision-area" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:16px 20px; border-top:1px solid #E5E7EB; background:#FAFBFC;">
                <span style="font-size:12px; font-weight:600; color:var(--text-muted); margin-right:4px;">Decisión:</span>
                <button class="btn btn-success btn-sm js-decidir-rendicion" data-decision="aprobada" ${!puedeDecidir ? 'disabled' : ''}>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    Aprobar
                </button>
                <button class="btn btn-warning btn-sm js-decidir-rendicion" data-decision="observada" ${!puedeDecidir ? 'disabled' : ''}>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                    Observar
                </button>
                <button class="btn btn-danger btn-sm js-decidir-rendicion" data-decision="rechazada" ${!puedeDecidir ? 'disabled' : ''}>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    Rechazar
                </button>
                ${!puedeDecidir ? `<span style="font-size:12px;color:var(--text-muted); margin-left:auto;">Decisión ya registrada</span>` : ''}
            </div>
        `;

        container.innerHTML = `
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
                            <div class="estado-panel-badge">${badgeHTML(detalle.estado)}</div>
                        </div>
                        <div class="estado-panel estado-panel-rendicion">
                            <div class="estado-panel-label">Estado de Rendición</div>
                            <div class="estado-panel-badge" id="rendicion-estado-badge">${estadoRendicionBadge(detalle)}</div>
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
                            <div class="resumen-card-value">${fmt(detalle.monto)}</div>
                        </div>
                        <div class="resumen-card total-rendido">
                            <div class="resumen-card-label">Total Rendido</div>
                            <div class="resumen-card-value">${fmt(detalle.total_rendido)}</div>
                        </div>
                        <div class="resumen-card ${saldoNegativo ? 'saldo-negativo' : 'saldo'}">
                            <div class="resumen-card-label">Saldo</div>
                            <div class="resumen-card-value ${saldoNegativo ? 'saldo-negativo' : 'saldo'}">${fmt(detalle.saldo)}</div>
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
                            <div class="dato-label">Colaborador</div>
                            <div class="dato-value">${escHtml(colaborador.display_name || '—')}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">Email</div>
                            <div class="dato-value">${escHtml(colaborador.email || '—')}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">DNI</div>
                            <div class="dato-value">${escHtml(detalle.dni || '—')}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">Fecha de Viaje</div>
                            <div class="dato-value">${fmtFecha(detalle.fecha_viaje)}</div>
                        </div>
                        <div class="dato-item">
                            <div class="dato-label">CECO / Proyecto</div>
                            <div class="dato-value">${escHtml(detalle.ceco || '—')}</div>
                        </div>
                        <div class="dato-motivo">
                            <div class="dato-label">Motivo del Viaje</div>
                            <div class="dato-value muted">${escHtml(detalle.motivo || '—')}</div>
                        </div>
                    </div>
                </div>
            </div>

            ${decisionBtns}
            <div id="rendicion-decision-error" style="display:none; margin:0 20px 12px; padding:10px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>

            <!-- SECCIÓN: Gastos -->
            <div class="section-block" style="margin-top:20px;">
                <div class="section-header">
                    <div class="section-header-title">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        Gastos Asociados
                    </div>
                    <div class="section-header-subtitle">${gastos.length} registro(s) · Total: ${fmt(detalle.total_rendido)}</div>
                </div>
                <div class="section-body" style="padding:16px 20px;">
                    ${gastosHtml}
                </div>
            </div>
        `;

        // Bind accordion for gastos + adjuntos lazy-load (read-only).
        const accContainer = container.querySelector('#admin-gastos-acc');
        if (accContainer) {
            gastoUI.bindAccordionList(accContainer, {
                onOpen: function(itemEl, gastoId) {
                    if (gastoId) loadGastoAdjuntosAdmin(gastoId, itemEl);
                }
            });
        }

        // Liquidación — shared formal document
        if (detalle.rendicion_finalizada) {
            const _liqData = window.ViaticosLiquidacion.buildData(
                {
                    id: detalle.id, monto: detalle.monto, fecha: detalle.fecha_viaje,
                    motivo: detalle.motivo, ceco: detalle.ceco, dni: detalle.dni,
                    estado_rendicion: detalle.estado_rendicion,
                    rendicion_finalizada: detalle.rendicion_finalizada,
                },
                Array.isArray(detalle.gastos) ? detalle.gastos : [],
                {
                    colaboradorNombre: detalle.colaborador ? detalle.colaborador.display_name : '',
                    fechaRendicion: detalle.fecha_creacion || '',
                }
            );
            const _liqWrap = document.createElement('div');
            _liqWrap.style.cssText = 'margin:20px;';
            _liqWrap.innerHTML = window.ViaticosLiquidacion.renderDoc(_liqData);
            container.appendChild(_liqWrap);
        }

        // Bind decision buttons.
        container.querySelectorAll('.js-decidir-rendicion').forEach(btn => {
            btn.addEventListener('click', () => handleDecisionRendicion(detalle.id, btn.dataset.decision));
        });
    }

    async function loadRendiciones() {
        const tbody = document.getElementById('rendiciones-tbody');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando rendiciones...</div></td></tr>`;

        try {
            const rows = await fetchRendicionesFinalizadas();
            renderRendicionesTable(rows);
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>Error: ${escHtml(error.message)}</p></div></td></tr>`;
        }
    }

    async function openDetalleRendicion(idSolicitud) {
        const container = document.getElementById('rendicion-detalle-content');
        if (!container) return;

        setAdminView('view-rendicion-detalle');
        container.innerHTML = `<div style="padding:20px;"><div class="tbl-loading"><div class="spinner"></div>Cargando detalle...</div></div>`;

        try {
            const detalle = await apiFetch(`/detalle-rendicion-admin/${idSolicitud}`);
            renderDetalle(detalle);
        } catch (error) {
            container.innerHTML = `<div style="padding:20px;"><div class="tbl-empty"><p>Error: ${escHtml(error.message)}</p></div></div>`;
        }
    }

    async function handleDecisionRendicion(idSolicitud, decision) {
        const errEl = document.getElementById('rendicion-decision-error');
        if (errEl) errEl.style.display = 'none';

        const allBtns = document.querySelectorAll('.js-decidir-rendicion');
        allBtns.forEach(b => { b.disabled = true; });

        const activeBtn = [...allBtns].find(b => b.dataset.decision === decision);
        if (activeBtn) {
            activeBtn.dataset.orig = activeBtn.innerHTML;
            activeBtn.innerHTML = `<div class="spinner" style="width:13px;height:13px;border-width:2px;"></div> Procesando...`;
        }

        try {
            await apiFetch('/decidir-rendicion', {
                method: 'POST',
                body: JSON.stringify({ id_solicitud: parseInt(idSolicitud, 10), decision }),
            });

            const labels = { aprobada: 'aprobada ✓', observada: 'marcada como observada', rechazada: 'rechazada' };

            // Actualizar badge en la vista, sin recargar todo.
            const badgeEl = document.getElementById('rendicion-estado-badge');
            if (badgeEl) {
                badgeEl.innerHTML = estadoRendicionBadge({
                    estado: 'aprobada',
                    estado_rendicion: decision,
                    rendicion_finalizada: true,
                });
            }

            // Deshabilitar todos los botones de decisión.
            allBtns.forEach(b => {
                b.disabled = true;
                if (b === activeBtn) b.innerHTML = activeBtn.dataset.orig || b.innerHTML;
            });

            const notaEl = document.getElementById('rendicion-decision-area');
            if (notaEl) {
                const nota = document.createElement('span');
                nota.style.cssText = 'font-size:12px;color:var(--text-muted);margin-left:auto;';
                nota.textContent = 'Decisión registrada.';
                notaEl.appendChild(nota);
            }

            // Refrescar tabla de rendiciones en background.
            loadRendiciones();

            showToast('success', 'Decisión registrada', `Rendición de solicitud #${idSolicitud} ${labels[decision]}.`);
        } catch (err) {
            if (errEl) { errEl.textContent = err.message || 'No se pudo registrar la decisión.'; errEl.style.display = 'block'; }
            allBtns.forEach(b => {
                b.disabled = false;
                if (b === activeBtn && b.dataset.orig) b.innerHTML = b.dataset.orig;
            });
        }
    }

    function navigate(viewId) {
        if (viewId === 'view-rendiciones') {
            setAdminView(viewId);
            loadRendiciones();
            return;
        }

        if (viewId === 'view-rendicion-detalle') {
            setAdminView(viewId);
            return;
        }

        const baseNavigate = window.AdminBaseNavigate || previousNavigate;
        if (baseNavigate) baseNavigate(viewId);
    }

    function bindExtraEvents() {
        const navRendiciones = document.getElementById('nav-rendiciones');
        const refreshRendiciones = document.getElementById('btn-refrescar-rendiciones');
        const volverRendiciones = document.getElementById('btn-volver-rendiciones');

        if (navRendiciones) {
            navRendiciones.addEventListener('click', event => {
                event.preventDefault();
                navigate('view-rendiciones');
            });
        }

        if (refreshRendiciones) {
            refreshRendiciones.addEventListener('click', event => {
                event.preventDefault();
                loadRendiciones();
            });
        }

        if (volverRendiciones) {
            volverRendiciones.addEventListener('click', event => {
                event.preventDefault();
                navigate('view-rendiciones');
            });
        }
    }

    function initExtra() {
        bindExtraEvents();
        window.AdminRendicionesExt = {
            navigate,
            openDetalleRendicion,
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExtra);
    } else {
        initExtra();
    }
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
                        <th>Monto</th><th>Estado solicitud</th><th>Estado rendición</th>
                    </tr>
                </thead>
                <tbody id="resumen-tbody">
                    <tr><td colspan="6"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>
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
            <p>Evaluación de solicitudes de viáticos</p>
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
                        <th>Monto</th><th>CECO / Proyecto</th><th>Estado solicitud</th><th>Estado rendición</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes-tbody">
                    <tr><td colspan="8"><div class="tbl-loading"><div class="spinner"></div>Cargando solicitudes...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-solicitudes -->


<!-- ================================================
     VISTA: RENDICIONES FINALIZADAS
     ================================================ -->
<section id="view-rendiciones" class="erp-view">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Rendiciones Finalizadas</h1>
            <p>Solicitudes aprobadas en revisión</p>
        </div>
        <button class="btn btn-ghost btn-sm" id="btn-refrescar-rendiciones" title="Actualizar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
            Actualizar
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">Solicitudes con rendici&oacute;n finalizada</div>
            <div id="tbl-rendiciones-counter" style="font-size:12px;color:var(--text-muted);"></div>
        </div>
        <div class="table-wrap">
            <table class="tbl" aria-label="Rendiciones finalizadas">
                <thead>
                    <tr>
                        <th>ID Solicitud</th><th>Colaborador</th><th>Monto Solicitado</th>
                        <th>Total Rendido</th><th>Estado solicitud</th><th>Estado rendición</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="rendiciones-tbody">
                    <tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando rendiciones...</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section><!-- /#view-rendiciones -->


<!-- ================================================
     VISTA: DETALLE RENDICION
     ================================================ -->
<section id="view-rendicion-detalle" class="erp-view">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Detalle de Rendición</h1>
            <p>Revisión de solicitud y gastos</p>
        </div>
        <button class="btn btn-secondary btn-sm" id="btn-volver-rendiciones">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.58-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver
        </button>
    </div>

    <div id="rendicion-detalle-content" class="card">
        <div style="padding:20px;">
            <div class="tbl-loading"><div class="spinner"></div>Cargando detalle...</div>
        </div>
    </div>

</section><!-- /#view-rendicion-detalle -->


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

    // ── Shared badge & logic helpers (same logic as AdminRendicionesExt; separate IIFE scope) ──
    const estadoUI = window.ViaticosEstadoUI;

    function badgeHTML(estado) {
        return estadoUI.renderBadgeEstado('solicitud', estadoUI.resolveEstadoSolicitud(estado));
    }

    function estadoRendicionBadge(source) {
        return estadoUI.renderBadgeEstado('rendicion', estadoUI.resolveEstadoRendicion({
            estadoSolicitud: source && source.estado,
            estadoRendicion: source && source.estado_rendicion,
            rendicionFinalizada: source && source.rendicion_finalizada,
            totalRendido: source && source.total_rendido,
        }));
    }

    function buildAccionAdmin(sol) {
        const estado = estadoUI.resolveEstadoSolicitud(sol.estado);
        if (estado === 'pendiente') {
            return `<button class="btn btn-primary btn-sm action-evaluar" data-id="${sol.id}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Evaluar
            </button>`;
        }
        return `<span style="color:var(--text-light);font-size:12px;">Sin acciones</span>`;
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
            tbody.innerHTML = `<tr><td colspan="8"><div class="tbl-empty">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                <p>${q ? 'Sin resultados para "' + escHtml(q) + '".' : 'No hay solicitudes registradas.'}</p>
            </div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(s => {
            return `<tr>
                <td class="muted">#${s.id}</td>
                <td><strong>${escHtml(s.colaborador)}</strong></td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${escHtml(s.ceco)}</td>
                <td>${badgeHTML(s.estado)}</td>
                <td>${estadoRendicionBadge(s)}</td>
                <td>${buildAccionAdmin(s)}</td>
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
            const e = estadoUI.resolveEstadoSolicitud(s.estado);
            if (e === 'pendiente') pendientes++;
            if (e === 'aprobada')  montoAprobado += parseFloat(s.monto) || 0;
            if (e === 'observada') observadas++;
        });
        document.getElementById('kpi-pendientes').textContent     = pendientes;
        document.getElementById('kpi-monto-aprobado').textContent = fmt(montoAprobado);
        document.getElementById('kpi-observadas').textContent     = observadas;

        const recent = data.slice(0, 8);
        if (!recent.length) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="tbl-empty"><p>No hay solicitudes registradas.</p></div></td></tr>`;
            return;
        }
        tbody.innerHTML = recent.map(s => `
            <tr>
                <td class="muted">#${s.id}</td>
                <td>${escHtml(s.colaborador)}</td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${badgeHTML(s.estado)}</td>
                <td>${estadoRendicionBadge(s)}</td>
            </tr>`).join('');
    }

    /* ── Load per view ────────────────────────────────────── */
    async function loadResumen() {
        const tbody = document.getElementById('resumen-tbody');
        tbody.innerHTML = `<tr><td colspan="6"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            renderResumenTable(cache);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
            showToast('error', 'Error al cargar datos', err.message);
        }
    }

    async function loadSolicitudes() {
        const tbody = document.getElementById('solicitudes-tbody');
        tbody.innerHTML = `<tr><td colspan="8"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            const q = document.getElementById('search-solicitudes').value;
            renderSolicitudesTable(cache, q);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
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
<script>
(function () {
    function mergeAdminRendicionesFinal() {
        if (!window.AdminRendicionesExt) return;
        if (!window.AdminBaseNavigate && window.AdminApp && typeof window.AdminApp.navigate === 'function') {
            window.AdminBaseNavigate = window.AdminApp.navigate;
        }
        window.AdminApp = Object.assign({}, window.AdminApp || {}, window.AdminRendicionesExt);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mergeAdminRendicionesFinal);
    } else {
        mergeAdminRendicionesFinal();
    }
})();
</script>

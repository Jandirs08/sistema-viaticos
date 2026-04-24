(function () {
    'use strict';

    const CFG = window.ViaticosConfig;
    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const gastoUI = window.ViaticosGastoUI;
    const LIST_VIEWS = ['view-anticipos', 'view-rendiciones'];
    const ROUTE_CONFIG = {
        anticipos: { viewId: 'view-anticipos', breadcrumb: 'Anticipos' },
        rendiciones: { viewId: 'view-rendiciones', breadcrumb: 'Rendiciones' },
        solicitud: { viewId: 'view-solicitud-detalle', breadcrumb: 'Detalle de Solicitud', requiresId: true, validFrom: ['anticipos', 'rendiciones'] }
    };
    const VIEW_TO_ROUTE = Object.keys(ROUTE_CONFIG).reduce((acc, routeName) => {
        acc[ROUTE_CONFIG[routeName].viewId] = routeName;
        return acc;
    }, {});

    const router = window.ViaticosRouter.create({
        routes:       ROUTE_CONFIG,
        defaultRoute: 'anticipos',
        onNavigate:   async function (route) { await renderRoute(route); },
    });
    const VIEW_CONFIG = {
        'view-anticipos': {
            breadcrumb: 'Anticipos',
            tbodyId: 'anticipos-tbody',
            searchId: 'search-anticipos',
            counterId: 'tbl-counter-anticipos',
            paginationId: 'tbl-pag-anticipos',
            chipGroupId: 'chips-anticipos',
            pageSizeId: 'page-size-anticipos',
            fechaChipId: 'fecha-chip-anticipos',
            datesStripId: 'dates-strip-anticipos',
            dateFromId: 'fecha-desde-anticipos',
            dateToId: 'fecha-hasta-anticipos',
            clearBtnId: 'clear-anticipos',
            emptyText: 'No hay anticipos por revisar.',
            emptySearchText: 'No se encontraron resultados.',
            filter: function (sol) { return getSolicitudEstado(sol) !== 'aprobada'; },
            getChipEstado: function (sol) { return getSolicitudEstado(sol); }
        },
        'view-rendiciones': {
            breadcrumb: 'Rendiciones',
            tbodyId: 'rendiciones-tbody',
            searchId: 'search-rendiciones',
            counterId: 'tbl-counter-rendiciones',
            paginationId: 'tbl-pag-rendiciones',
            chipGroupId: 'chips-rendiciones',
            pageSizeId: 'page-size-rendiciones',
            fechaChipId: 'fecha-chip-rendiciones',
            datesStripId: 'dates-strip-rendiciones',
            dateFromId: 'fecha-desde-rendiciones',
            dateToId: 'fecha-hasta-rendiciones',
            clearBtnId: 'clear-rendiciones',
            emptyText: 'No hay rendiciones registradas.',
            emptySearchText: 'No se encontraron resultados.',
            filter: function (sol) { return getSolicitudEstado(sol) === 'aprobada'; },
            getChipEstado: function (sol) { return getRendicionEstado(sol); }
        }
    };

    const sortState = {};
    const pageState = {};
    const filterState = {};
    const pageSizeState = {};

    function getPageSize(viewId) { return parseInt(pageSizeState[viewId] || 10, 10); }

    let cache = [];
    let modalSolId = null;

    const utils        = window.ViaticosUtils;
    const apiFetch     = utils.createApiFetch(CFG.apiBase, CFG.nonce);
    const escHtml      = utils.escapeHtml;
    const fmt          = utils.fmtMonto;
    const fmtFecha     = utils.fmtFecha;
    const ModalManager = utils.ModalManager;

    const getSolicitudEstado   = estadoUI.getSolicitudEstado;
    const renderSolicitudBadge = estadoUI.renderSolicitudBadge;

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

    function renderRendicionBadge(sol) {
        return estadoUI.renderBadgeEstado('rendicion', getRendicionEstado(sol));
    }

    const showToast        = utils.showToast.bind(utils);
    const setButtonLoading = utils.setButtonLoading;

    function getAnticipoActionConfig(sol) {
        const solicitudEstado = getSolicitudEstado(sol);
        if (solicitudEstado === 'pendiente') {
            return { type: 'button', label: 'Revisar anticipo', action: 'evaluate', highlight: 'pending' };
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
            return { type: 'button', label: 'Revisar rendición', action: 'review', highlight: 'review' };
        }
        if (rendicionEstado === 'no_iniciada') {
            return { type: 'text', label: 'Esperando rendición', tone: 'muted' };
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

    function updateBackButton(route) {
        route = route || router.getCurrentRoute();
        const text = document.getElementById('btn-volver-lista-texto');
        if (!text) return;
        const originRoute = route.name === 'solicitud' ? (route.from || 'anticipos') : route.name;
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
                : 'Detalle de Solicitud';
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
        utils.renderTableSkeleton(tbody, 6);
        const pagEl = document.getElementById(cfg.paginationId);
        if (pagEl) pagEl.innerHTML = '';
    }

    function applySortToRows(rows, viewId) {
        const s = sortState[viewId];
        if (!s || !s.key) return rows;
        const { key, dir, type } = s;
        return [...rows].sort((a, b) => {
            let av = a[key], bv = b[key];
            if (type === 'num') { av = parseFloat(av) || 0; bv = parseFloat(bv) || 0; }
            else { av = String(av || '').toLowerCase(); bv = String(bv || '').toLowerCase(); }
            if (av < bv) return dir === 'asc' ? -1 : 1;
            if (av > bv) return dir === 'asc' ? 1 : -1;
            return 0;
        });
    }

    function renderPagination(cfg, viewId, total) {
        const el = document.getElementById(cfg.paginationId);
        if (!el) return;
        const page = pageState[viewId] || 1;
        const ps = getPageSize(viewId);
        const totalPages = Math.ceil(total / ps);
        if (totalPages <= 1) { el.innerHTML = ''; return; }
        const start = (page - 1) * ps;
        const end = Math.min(page * ps, total);
        el.innerHTML = `
            <span class="tbl-pag-info">${start + 1}–${end} de ${total}</span>
            <div class="tbl-pag-btns">
                <button class="btn btn-ghost btn-sm js-pag-prev" ${page <= 1 ? 'disabled' : ''}>← Anterior</button>
                <button class="btn btn-ghost btn-sm js-pag-next" ${page >= totalPages ? 'disabled' : ''}>Siguiente →</button>
            </div>`;
        el.querySelector('.js-pag-prev').addEventListener('click', () => {
            pageState[viewId] = Math.max(1, (pageState[viewId] || 1) - 1);
            renderTable(viewId, cache, getSearchValue(viewId));
        });
        el.querySelector('.js-pag-next').addEventListener('click', () => {
            pageState[viewId] = Math.min(totalPages, (pageState[viewId] || 1) + 1);
            renderTable(viewId, cache, getSearchValue(viewId));
        });
    }

    function applyDateFilter(rows, viewId) {
        const cfg = VIEW_CONFIG[viewId];
        const desdeEl = document.getElementById(cfg.dateFromId);
        const hastaEl = document.getElementById(cfg.dateToId);
        const from = desdeEl ? desdeEl.value : '';
        const to = hastaEl ? hastaEl.value : '';
        if (!from && !to) return rows;
        return rows.filter(function(sol) {
            var f = sol.fecha || '';
            if (from && f < from) return false;
            if (to && f > to) return false;
            return true;
        });
    }

    function hasActiveFilters(viewId) {
        const cfg = VIEW_CONFIG[viewId];
        if (filterState[viewId]) return true;
        const searchEl = document.getElementById(cfg.searchId);
        if (searchEl && searchEl.value.trim()) return true;
        const desdeEl = document.getElementById(cfg.dateFromId);
        const hastaEl = document.getElementById(cfg.dateToId);
        if (desdeEl && desdeEl.value) return true;
        if (hastaEl && hastaEl.value) return true;
        return false;
    }

    function updateClearButton(viewId) {
        const cfg = VIEW_CONFIG[viewId];
        const btn = document.getElementById(cfg.clearBtnId);
        if (btn) btn.style.display = hasActiveFilters(viewId) ? '' : 'none';
    }

    function clearAllFilters(viewId) {
        const cfg = VIEW_CONFIG[viewId];
        filterState[viewId] = '';
        pageState[viewId] = 1;
        const group = document.getElementById(cfg.chipGroupId);
        if (group) {
            group.querySelectorAll('.tbl-chip').forEach(function(c) { c.classList.remove('is-active'); });
            const first = group.querySelector('.tbl-chip[data-filter=""]');
            if (first) first.classList.add('is-active');
        }
        const searchEl = document.getElementById(cfg.searchId);
        if (searchEl) searchEl.value = '';
        const desdeEl = document.getElementById(cfg.dateFromId);
        const hastaEl = document.getElementById(cfg.dateToId);
        if (desdeEl) desdeEl.value = '';
        if (hastaEl) hastaEl.value = '';
        const strip = document.getElementById(cfg.datesStripId);
        if (strip) strip.classList.remove('is-open');
        const fechaChip = document.getElementById(cfg.fechaChipId);
        if (fechaChip) fechaChip.classList.remove('is-active');
        renderTable(viewId, cache, '');
    }

    function updateAllChipCounts() {
        LIST_VIEWS.forEach(function(viewId) {
            const cfg = VIEW_CONFIG[viewId];
            const baseRows = cache.filter(cfg.filter);
            const group = document.getElementById(cfg.chipGroupId);
            if (!group) return;
            group.querySelectorAll('.tbl-chip[data-filter]').forEach(function(chip) {
                const filter = chip.dataset.filter;
                const countEl = chip.querySelector('.tbl-chip-count');
                if (!countEl) return;
                const count = filter
                    ? baseRows.filter(function(sol) { return cfg.getChipEstado(sol) === filter; }).length
                    : baseRows.length;
                countEl.textContent = count;
            });
        });
    }

    function renderTable(viewId, data, filter) {
        if (filter === undefined) filter = '';
        const cfg = VIEW_CONFIG[viewId];
        if (!cfg) return;
        updateClearButton(viewId);
        const tbody = document.getElementById(cfg.tbodyId);
        const counter = document.getElementById(cfg.counterId);
        const q = filter.toLowerCase().trim();
        const chipFilter = filterState[viewId] || '';
        let rows = data.filter(cfg.filter);
        if (chipFilter) {
            rows = rows.filter(sol => cfg.getChipEstado(sol) === chipFilter);
        }
        if (q) {
            rows = rows.filter(sol => (
                String(sol.id).includes(q) ||
                (sol.colaborador || '').toLowerCase().includes(q) ||
                (sol.ceco || '').toLowerCase().includes(q) ||
                (sol.motivo || '').toLowerCase().includes(q)
            ));
        }
        rows = applyDateFilter(rows, viewId);
        rows = applySortToRows(rows, viewId);
        if (counter) counter.textContent = `${rows.length} resultado${rows.length !== 1 ? 's' : ''}`;
        if (!rows.length) {
            const emptyIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>';
            tbody.innerHTML = `<tr><td colspan="6"><div class="table-empty">${emptyIcon}<p>${(q || chipFilter) ? cfg.emptySearchText : cfg.emptyText}</p></div></td></tr>`;
            const pagEl = document.getElementById(cfg.paginationId);
            if (pagEl) pagEl.innerHTML = '';
            return;
        }
        const page = pageState[viewId] || 1;
        const ps = getPageSize(viewId);
        const start = (page - 1) * ps;
        const pageRows = rows.slice(start, start + ps);
        tbody.innerHTML = pageRows.map(sol => {
            const action = getActionConfig(sol, viewId);
            const classes = ['worktray-row'];
            if (action.highlight === 'pending') classes.push('is-needs-action');
            if (action.highlight === 'review') classes.push('is-needs-action', 'is-review-action');
            return `
                <tr class="${classes.join(' ')}" data-id="${sol.id}" data-view="${viewId}" tabindex="0">
                    <td class="muted">#${sol.id}</td>
                    <td class="worktray-person-cell" data-fecha="${fmtFecha(sol.fecha)}"><div class="worktray-person"><strong>${escHtml(sol.colaborador || 'Sin nombre')}</strong><span>${escHtml(sol.ceco || 'Sin CECO')}</span></div></td>
                    <td>${fmtFecha(sol.fecha)}</td>
                    <td><strong>${fmt(sol.monto)}</strong></td>
                    <td>${viewId === 'view-rendiciones' ? renderRendicionBadge(sol) : renderSolicitudBadge(sol)}</td>
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

        renderPagination(cfg, viewId, rows.length);
    }

    function renderAllTables() {
        LIST_VIEWS.forEach(viewId => renderTable(viewId, cache, getSearchValue(viewId)));
    }

    async function loadSolicitudDetailView(route) {
        route = route || router.getCurrentRoute();
        if (!route.id) {
            await navigateTo({ name: route.from || 'anticipos' }, { historyMode: 'replace' });
            return;
        }

        const container = document.getElementById('solicitud-detalle-content');
        container.innerHTML = `<div style="padding:20px;"><div class="table-loading"><div class="spinner"></div>Cargando detalle…</div></div>`;
        try {
            const detalle = await apiFetch(`/detalle-rendicion-admin/${route.id}`);
            renderDetalle(detalle);
        } catch (error) {
            container.innerHTML = `<div style="padding:20px;"><div class="table-empty"><p>Error: ${escHtml(error.message)}</p></div></div>`;
            showToast('error', 'No se pudo abrir el detalle', error.message);
        }
    }

    async function loadSolicitudes() {
        LIST_VIEWS.forEach(setTableLoading);
        try {
            cache = await apiFetch('/todas-solicitudes');
            updateAllChipCounts();
            renderAllTables();
        } catch (error) {
            LIST_VIEWS.forEach(viewId => {
                const cfg = VIEW_CONFIG[viewId];
                const tbody = document.getElementById(cfg.tbodyId);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="6"><div class="table-empty"><p>Error: ${escHtml(error.message)}</p></div></td></tr>`;
                }
            });
            showToast('error', 'Error al cargar solicitudes', error.message);
        }
    }

    function closeSolicitudModal() {
        ModalManager.close('modal-solicitud');
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
        ModalManager.open('modal-solicitud');
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
            showToast('success', 'Estado actualizado', `Solicitud #${modalSolId}: ${estadoUI.getLabelEstado('solicitud', nuevoEstado)}.`);
            await loadSolicitudes();
        } catch (error) {
            errEl.textContent = error.message || 'No se pudo actualizar la solicitud.';
            errEl.style.display = 'block';
            setButtonLoading(btn, false);
            toggleModalDecision(true);
        }
    }

    const ICON = {
        check:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
        warn:     '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        close:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
        doc:      '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
        timeline: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="5" r="2"/><circle cx="5" cy="12" r="2"/><circle cx="5" cy="19" r="2"/><rect x="4.25" y="6.5" width="1.5" height="4"/><rect x="4.25" y="13.5" width="1.5" height="4"/><rect x="9" y="4" width="11" height="2" rx="1"/><rect x="9" y="11" width="8" height="2" rx="1"/><rect x="9" y="18" width="10" height="2" rx="1"/></svg>',
        ban:      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>',
        msg:      '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>'
    };

    function buildAdminAcciones(detalle, estadoRend) {
        const errDiv = '<div id="rendicion-decision-error" class="erp-alert-error" style="display:none;margin-bottom:8px;width:100%;"></div>';
        const buttons = [];
        if (detalle.rendicion_finalizada) {
            buttons.push('<button type="button" class="btn btn-secondary js-view-liquidacion">' + ICON.doc + 'Ver liquidación</button>');
        }
        buttons.push('<button type="button" class="btn btn-ghost js-view-historial">' + ICON.timeline + 'Historial</button>');
        if (estadoRend === 'en_revision') {
            buttons.push('<span style="flex:1;"></span>');
            buttons.push('<button type="button" class="btn-decide-rechazar js-decidir-rendicion" data-decision="rechazada">' + ICON.ban + 'Rechazar</button>');
            buttons.push('<span class="decision-sep"></span>');
            buttons.push('<button type="button" class="btn-decide-observar js-decidir-rendicion" data-decision="observada">' + ICON.msg + 'Observar</button>');
            buttons.push('<button type="button" class="btn-decide-aprobar js-decidir-rendicion" data-decision="aprobada">' + ICON.check + 'Aprobar</button>');
        }
        return errDiv + buttons.join('');
    }

    function openHistorialModal(detalle) {
        const body = document.getElementById('admin-historial-body');
        const meta = document.getElementById('admin-historial-meta');
        const sub  = document.getElementById('admin-historial-subtitulo');
        const gastos = Array.isArray(detalle.gastos) ? detalle.gastos : [];
        const historial = Array.isArray(detalle.historial) ? detalle.historial : [];
        if (body) {
            body.innerHTML = historial.length
                ? timelineUI.renderTimeline(historial)
                : '<div class="table-empty" style="padding:32px 20px;"><p>No hay movimientos registrados.</p></div>';
        }
        if (meta) {
            meta.innerHTML =
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Expediente</span><strong>#' + detalle.id + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Eventos</span><strong>' + historial.length + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Gastos</span><strong>' + gastos.length + '</strong></span>';
        }
        if (sub) sub.textContent = 'Seguimiento del expediente #' + detalle.id + '.';
        ModalManager.open('modal-admin-historial');
    }

    let currentLiqData = null;

    function openLiquidacionModal(detalle) {
        const container = document.getElementById('admin-liq-container');
        if (!container) return;
        const gastos = Array.isArray(detalle.gastos) ? detalle.gastos : [];
        const liqOpts = {
            colaboradorNombre:  (detalle.colaborador || {}).display_name || '',
            codigoEmpleado:     detalle.dni  || '',
            area:               detalle.area || '',
            cargo:              detalle.cargo || '',
            fechaRendicion:     detalle.fecha_creacion || '',
            estadoRendicionKey: detalle.estado_rendicion || '',
        };
        currentLiqData = window.ViaticosLiquidacion.buildData(detalle, gastos, liqOpts);
        container.innerHTML = window.ViaticosLiquidacion.renderDoc(currentLiqData);
        ModalManager.open('modal-admin-liquidacion');
    }

    let currentDetalle = null;

    function renderDetalle(detalle) {
        const container = document.getElementById('solicitud-detalle-content');
        if (!container) return;
        currentDetalle = detalle;
        const gastos = Array.isArray(detalle.gastos) ? detalle.gastos : [];
        const sol    = Object.assign({}, detalle, { fecha: detalle.fecha_viaje });

        window.ViaticosDetalleUI.render(container, sol, gastos, {
            apiFetch,
            canDelete: false,
            accionesHtml: buildAdminAcciones(detalle, getRendicionEstado(detalle)),
        });

        container.querySelectorAll('.js-decidir-rendicion').forEach(btn => {
            btn.addEventListener('click', () => handleDecisionRendicion(detalle.id, btn.dataset.decision));
        });
        container.querySelectorAll('.js-view-historial').forEach(btn => {
            btn.addEventListener('click', () => openHistorialModal(currentDetalle || detalle));
        });
        container.querySelectorAll('.js-view-liquidacion').forEach(btn => {
            btn.addEventListener('click', () => openLiquidacionModal(currentDetalle || detalle));
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
            activeBtn.innerHTML = `<div class="spinner" style="width:13px;height:13px;border-width:2px;"></div> Procesando…`;
        }
        try {
            await apiFetch('/decidir-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: parseInt(idSolicitud, 10), decision }) });
            const detalleActualizado = await apiFetch(`/detalle-rendicion-admin/${idSolicitud}`);
            renderDetalle(detalleActualizado);
            await loadSolicitudes();
            showToast('success', 'Decisión registrada', `Rendición de solicitud #${idSolicitud}: ${estadoUI.getLabelEstado('rendicion', decision)}.`);
        } catch (error) {
            if (errEl) {
                errEl.textContent = error.message || 'No se pudo registrar la decisión.';
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
            ? (VIEW_TO_ROUTE[target] || target)
            : (target.name || VIEW_TO_ROUTE[target.viewId] || 'anticipos');
        const id   = typeof target === 'object' ? target.id   : (options.id   || null);
        const from = typeof target === 'object' ? target.from : (options.from || null);
        await router.navigateTo(routeName, { id, from, historyMode: options.historyMode || 'push' });
    }

    function initFilterChips() {
        LIST_VIEWS.forEach(viewId => {
            const cfg = VIEW_CONFIG[viewId];
            const group = document.getElementById(cfg.chipGroupId);
            if (!group) return;
            group.querySelectorAll('.tbl-chip:not(.tbl-chip-fecha)').forEach(chip => {
                chip.addEventListener('click', () => {
                    group.querySelectorAll('.tbl-chip:not(.tbl-chip-fecha)').forEach(c => c.classList.remove('is-active'));
                    chip.classList.add('is-active');
                    filterState[viewId] = chip.dataset.filter || '';
                    pageState[viewId] = 1;
                    renderTable(viewId, cache, getSearchValue(viewId));
                });
            });

            const fechaChip = document.getElementById(cfg.fechaChipId);
            const strip = document.getElementById(cfg.datesStripId);
            if (fechaChip && strip) {
                fechaChip.addEventListener('click', () => {
                    const opening = !strip.classList.contains('is-open');
                    strip.classList.toggle('is-open');
                    if (!opening) {
                        const desdeEl = document.getElementById(cfg.dateFromId);
                        const hastaEl = document.getElementById(cfg.dateToId);
                        const hasDate = (desdeEl && desdeEl.value) || (hastaEl && hastaEl.value);
                        fechaChip.classList.toggle('is-active', !!hasDate);
                    } else {
                        fechaChip.classList.add('is-active');
                        const desde = document.getElementById(cfg.dateFromId);
                        if (desde) desde.focus();
                    }
                });
            }

            [cfg.dateFromId, cfg.dateToId].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.addEventListener('change', () => {
                    const desdeEl = document.getElementById(cfg.dateFromId);
                    const hastaEl = document.getElementById(cfg.dateToId);
                    const hasDate = (desdeEl && desdeEl.value) || (hastaEl && hastaEl.value);
                    const fc = document.getElementById(cfg.fechaChipId);
                    if (fc) fc.classList.toggle('is-active', !!hasDate || strip.classList.contains('is-open'));
                    pageState[viewId] = 1;
                    renderTable(viewId, cache, getSearchValue(viewId));
                });
            });

            const clearBtn = document.getElementById(cfg.clearBtnId);
            if (clearBtn) {
                clearBtn.addEventListener('click', () => clearAllFilters(viewId));
            }
        });
    }

    function initSortHeaders() {
        LIST_VIEWS.forEach(viewId => {
            const section = document.getElementById(viewId);
            if (!section) return;
            section.querySelectorAll('thead th[data-sort-key]').forEach(th => {
                th.addEventListener('click', () => {
                    const key = th.dataset.sortKey;
                    const type = th.dataset.sortType || 'str';
                    const current = sortState[viewId] || {};
                    const newDir = current.key === key && current.dir === 'asc' ? 'desc' : 'asc';
                    sortState[viewId] = { key, dir: newDir, type };
                    th.closest('thead').querySelectorAll('th').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                    th.classList.add(newDir === 'asc' ? 'sort-asc' : 'sort-desc');
                    pageState[viewId] = 1;
                    renderTable(viewId, cache, getSearchValue(viewId));
                });
            });
        });
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
                pageState[viewId] = 1;
                renderTable(viewId, cache, event.target.value);
            });
        });

        initSortHeaders();
        initFilterChips();

        LIST_VIEWS.forEach(viewId => {
            const cfg = VIEW_CONFIG[viewId];
            const select = document.getElementById(cfg.pageSizeId);
            if (!select) return;
            select.addEventListener('change', () => {
                pageSizeState[viewId] = parseInt(select.value, 10);
                pageState[viewId] = 1;
                renderTable(viewId, cache, getSearchValue(viewId));
            });
        });

        document.getElementById('btn-volver-lista').addEventListener('click', () => {
            const route = router.getCurrentRoute();
            navigateTo({ name: route.from || 'anticipos' });
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
        ['btn-cerrar-admin-historial','btn-cancelar-admin-historial'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', () => ModalManager.close('modal-admin-historial'));
        });
        ['btn-cerrar-admin-liq','btn-cancelar-admin-liq'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', () => ModalManager.close('modal-admin-liquidacion'));
        });
        document.getElementById('btn-imprimir-admin-liq').addEventListener('click', () => window.ViaticosLiquidacion.print('admin-liq-container'));
        document.getElementById('btn-excel-admin-liq').addEventListener('click', async function () {
            if (!currentLiqData) return;
            const btn = this;
            setButtonLoading(btn, true);
            try {
                await window.ViaticosLiquidacion.exportXlsx(currentLiqData, undefined, CFG.logoUrl);
            } catch (err) {
                showToast('error', 'Exportación fallida', err.message || 'No se pudo generar el archivo.');
            } finally {
                setButtonLoading(btn, false);
            }
        });
        ['modal-admin-historial','modal-admin-liquidacion'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', event => {
                if (event.target === el) ModalManager.close(id);
            });
        });
    }

    async function init() {
        bindEvents();
        await loadSolicitudes();
        await router.init();
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

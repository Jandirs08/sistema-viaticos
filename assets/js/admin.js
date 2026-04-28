(function () {
    'use strict';

    const CFG = window.ViaticosConfig;
    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const gastoUI = window.ViaticosGastoUI;
    const LIST_VIEWS = ['view-anticipos', 'view-rendiciones'];
    const ROUTE_CONFIG = {
        anticipos:   { viewId: 'view-anticipos',        breadcrumb: 'Anticipos' },
        rendiciones: { viewId: 'view-rendiciones',       breadcrumb: 'Rendiciones' },
        solicitud:   { viewId: 'view-solicitud-detalle', breadcrumb: 'Detalle de Solicitud', requiresId: true, validFrom: ['anticipos', 'rendiciones'] }
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
    const getRendicionEstado   = estadoUI.getRendicionEstado;
    const renderRendicionBadge = estadoUI.renderRendicionBadge;

    const showToast        = utils.showToast.bind(utils);
    const showApiError     = utils.showApiError.bind(utils);
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

    function renderAnticipoRow(sol) {
        const action  = getAnticipoActionConfig(sol);
        const classes = ['worktray-row'];
        if (action.highlight === 'pending') classes.push('is-needs-action');
        return `
                <tr class="${classes.join(' ')}" data-id="${sol.id}" data-view="view-anticipos" tabindex="0">
                    <td class="muted">#${sol.id}</td>
                    <td class="worktray-person-cell" data-fecha="${fmtFecha(sol.fecha)}"><div class="worktray-person"><strong>${escHtml(sol.colaborador || 'Sin nombre')}</strong><span>${escHtml(sol.ceco || 'Sin CECO')}</span></div></td>
                    <td>${fmtFecha(sol.fecha)}</td>
                    <td><strong>${fmt(sol.monto)}</strong></td>
                    <td>${renderSolicitudBadge(sol)}</td>
                    <td>${renderActionCell(sol, 'view-anticipos')}</td>
                </tr>`;
    }

    function renderRendicionRow(sol) {
        const action  = getRendicionActionConfig(sol);
        const classes = ['worktray-row'];
        if (action.highlight === 'review') classes.push('is-needs-action', 'is-review-action');
        return `
                <tr class="${classes.join(' ')}" data-id="${sol.id}" data-view="view-rendiciones" tabindex="0">
                    <td class="muted">#${sol.id}</td>
                    <td class="worktray-person-cell" data-fecha="${fmtFecha(sol.fecha)}"><div class="worktray-person"><strong>${escHtml(sol.colaborador || 'Sin nombre')}</strong><span>${escHtml(sol.ceco || 'Sin CECO')}</span></div></td>
                    <td>${fmtFecha(sol.fecha)}</td>
                    <td><strong>${fmt(sol.monto)}</strong></td>
                    <td>${renderRendicionBadge(sol)}</td>
                    <td>${renderActionCell(sol, 'view-rendiciones')}</td>
                </tr>`;
    }

    function attachAdminRowListeners(tbody, pageRows, allRows) {
        tbody.querySelectorAll('.worktray-row').forEach(row => {
            const id     = parseInt(row.dataset.id, 10);
            const rowView = row.dataset.view;
            const sol    = allRows.find(item => item.id === id);
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
                const id     = parseInt(btn.dataset.id, 10);
                const rowView = btn.dataset.view;
                const sol    = allRows.find(item => item.id === id);
                if (!sol) return;
                if (btn.dataset.action === 'review') {
                    openSolicitudDetail(id, rowView);
                    return;
                }
                openSolicitudModal(sol);
            });
        });
    }

    const trays = {
        'view-anticipos': window.ViaticosWorktray.create({
            tbodyId:        'anticipos-tbody',
            searchId:       'search-anticipos',
            counterId:      'tbl-counter-anticipos',
            paginationId:   'tbl-pag-anticipos',
            chipGroupId:    'chips-anticipos',
            pageSizeId:     'page-size-anticipos',
            fechaChipId:    'fecha-chip-anticipos',
            datesStripId:   'dates-strip-anticipos',
            dateFromId:     'fecha-desde-anticipos',
            dateToId:       'fecha-hasta-anticipos',
            clearBtnId:     'clear-anticipos',
            sortSectionId:  'view-anticipos',
            colspan:         6,
            emptyText:       'No hay anticipos por revisar.',
            emptySearchText: 'No se encontraron resultados.',
            filter:          sol => getSolicitudEstado(sol) !== 'aprobada',
            getChipEstado:   getSolicitudEstado,
            renderRow:       renderAnticipoRow,
            onAfterRender:   attachAdminRowListeners,
        }),
        'view-rendiciones': window.ViaticosWorktray.create({
            tbodyId:        'rendiciones-tbody',
            searchId:       'search-rendiciones',
            counterId:      'tbl-counter-rendiciones',
            paginationId:   'tbl-pag-rendiciones',
            chipGroupId:    'chips-rendiciones',
            pageSizeId:     'page-size-rendiciones',
            fechaChipId:    'fecha-chip-rendiciones',
            datesStripId:   'dates-strip-rendiciones',
            dateFromId:     'fecha-desde-rendiciones',
            dateToId:       'fecha-hasta-rendiciones',
            clearBtnId:     'clear-rendiciones',
            sortSectionId:  'view-rendiciones',
            colspan:         6,
            emptyText:       'No hay rendiciones registradas.',
            emptySearchText: 'No se encontraron resultados.',
            filter:          sol => getSolicitudEstado(sol) === 'aprobada',
            getChipEstado:   getRendicionEstado,
            renderRow:       renderRendicionRow,
            onAfterRender:   attachAdminRowListeners,
        }),
    };

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
            showApiError(error, 'No se pudo abrir el detalle');
        }
    }

    async function loadSolicitudes() {
        LIST_VIEWS.forEach(id => trays[id].setLoading());
        try {
            cache = await apiFetch('/todas-solicitudes');
            LIST_VIEWS.forEach(id => trays[id].updateChipCounts(cache));
            LIST_VIEWS.forEach(id => trays[id].render(cache));
        } catch (error) {
            LIST_VIEWS.forEach(id => trays[id].setError(error.message));
            showApiError(error, 'Error al cargar solicitudes');
        }
    }

    function closeSolicitudModal() {
        ModalManager.close('modal-solicitud');
        modalSolId = null;
        document.getElementById('modal-solicitud-error').style.display = 'none';
        const obsSection = document.getElementById('modal-obs-section');
        if (obsSection) obsSection.style.display = 'none';
    }

    function toggleModalDecision(canEvaluate) {
        const label     = document.getElementById('modal-solicitud-label');
        const actionIds = ['btn-modal-aprobar', 'btn-modal-observar', 'btn-modal-rechazar'];
        label.style.display = canEvaluate ? '' : 'none';
        actionIds.forEach(id => {
            const btn    = document.getElementById(id);
            btn.style.display = canEvaluate ? '' : 'none';
            btn.disabled      = !canEvaluate;
        });
    }

    async function openSolicitudModal(sol) {
        modalSolId = sol.id;
        document.getElementById('modal-solicitud-titulo').textContent    = `Solicitud #${sol.id}`;
        document.getElementById('modal-solicitud-subtitulo').textContent = sol.colaborador || '';
        document.getElementById('modal-det-monto').textContent           = fmt(sol.monto);
        document.getElementById('modal-det-fecha').textContent           = fmtFecha(sol.fecha);
        document.getElementById('modal-det-colaborador').textContent     = sol.colaborador || '-';
        document.getElementById('modal-det-dni').textContent             = sol.dni  || '-';
        document.getElementById('modal-det-ceco').textContent            = sol.ceco || '-';
        document.getElementById('modal-det-estado-solicitud').innerHTML  = renderSolicitudBadge(sol);
        document.getElementById('modal-det-estado-rendicion').innerHTML  = renderRendicionBadge(sol);
        document.getElementById('modal-det-motivo').textContent          = sol.motivo || '-';
        document.getElementById('modal-det-historial').innerHTML         = '<div class="timeline-empty"><div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Cargando historial…</div>';
        document.getElementById('modal-solicitud-error').style.display  = 'none';
        const _obsSection = document.getElementById('modal-obs-section');
        if (_obsSection) _obsSection.style.display = 'none';
        toggleModalDecision(getSolicitudEstado(sol) === 'pendiente');
        ModalManager.open('modal-solicitud');

        try {
            const detalle = await apiFetch('/detalle-solicitud/' + sol.id, { method: 'GET' });
            if (modalSolId !== sol.id) return;
            const historialEl = document.getElementById('modal-det-historial');
            if (historialEl) historialEl.innerHTML = timelineUI.renderTimeline(detalle.historial || []);
        } catch (err) {
            const historialEl = document.getElementById('modal-det-historial');
            if (historialEl) historialEl.innerHTML = '<div class="timeline-empty">No se pudo cargar el historial.</div>';
        }
    }

    function showModalObservarSection() {
        const section = document.getElementById('modal-obs-section');
        const ta      = document.getElementById('modal-obs-ta');
        const btnConf = document.getElementById('btn-modal-obs-confirmar');
        const btnCanc = document.getElementById('btn-modal-obs-cancelar');
        if (!section) return;

        section.style.display = '';
        ta.value = '';
        btnConf.disabled = true;

        ['btn-modal-aprobar', 'btn-modal-observar', 'btn-modal-rechazar', 'modal-solicitud-label'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        function onInput() { btnConf.disabled = !ta.value.trim(); }
        ta.removeEventListener('input', ta._obsOnInput);
        ta._obsOnInput = onInput;
        ta.addEventListener('input', onInput);

        btnCanc.onclick = function () {
            section.style.display = 'none';
            ta.value = '';
            toggleModalDecision(true);
        };

        btnConf.onclick = async function () {
            const comentario = ta.value.trim();
            if (!comentario) return;
            await handleSolicitudDecision('observada', comentario);
        };

        ta.focus();
    }

    async function handleSolicitudDecision(nuevoEstado, comentario) {
        if (!modalSolId) return;
        comentario = comentario || '';
        const btnMap = { aprobada: 'btn-modal-aprobar', observada: 'btn-modal-obs-confirmar', rechazada: 'btn-modal-rechazar' };
        const btn    = document.getElementById(btnMap[nuevoEstado]) || document.getElementById('btn-modal-observar');
        const errEl  = document.getElementById('modal-solicitud-error');
        errEl.style.display = 'none';
        setButtonLoading(btn, true);
        ['btn-modal-aprobar', 'btn-modal-rechazar', 'btn-modal-obs-confirmar', 'btn-modal-obs-cancelar'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el !== btn) el.disabled = true;
        });
        try {
            const body = { id_solicitud: modalSolId, nuevo_estado: nuevoEstado };
            if (comentario) body.comentario = comentario;
            await apiFetch('/actualizar-estado', { method: 'POST', body: JSON.stringify(body) });
            closeSolicitudModal();
            showToast('success', 'Estado actualizado', `Solicitud #${modalSolId}: ${estadoUI.getLabelEstado('solicitud', nuevoEstado)}.`);
            await loadSolicitudes();
        } catch (error) {
            errEl.textContent    = error.message || 'No se pudo actualizar la solicitud.';
            errEl.style.display  = 'block';
            setButtonLoading(btn, false);
            if (nuevoEstado === 'observada') {
                ['btn-modal-obs-confirmar', 'btn-modal-obs-cancelar'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.disabled = false;
                });
            } else {
                toggleModalDecision(true);
            }
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
        const errDiv  = '<div id="rendicion-decision-error" class="erp-alert-error" style="display:none;margin-bottom:8px;width:100%;"></div>';
        const buttons = [];
        if (detalle.rendicion_finalizada) {
            buttons.push('<button type="button" class="btn btn-secondary js-view-liquidacion">' + ICON.doc + 'Ver liquidación</button>');
        }
        buttons.push('<button type="button" class="btn btn-ghost js-view-historial">' + ICON.timeline + 'Historial</button>');
        if (estadoRend === 'en_revision') {
            buttons.push('<span style="flex:1;"></span>');
            buttons.push('<button type="button" class="btn-decide-rechazar js-decidir-rendicion" data-decision="rechazada">' + ICON.ban  + 'Rechazar</button>');
            buttons.push('<span class="decision-sep"></span>');
            buttons.push('<button type="button" class="btn-decide-observar js-decidir-rendicion" data-decision="observada">' + ICON.msg  + 'Observar</button>');
            buttons.push('<button type="button" class="btn-decide-aprobar  js-decidir-rendicion" data-decision="aprobada">'  + ICON.check + 'Aprobar</button>');
        }
        return errDiv + buttons.join('');
    }

    function openHistorialModal(detalle) {
        const body    = document.getElementById('admin-historial-body');
        const meta    = document.getElementById('admin-historial-meta');
        const sub     = document.getElementById('admin-historial-subtitulo');
        const gastos   = Array.isArray(detalle.gastos)   ? detalle.gastos   : [];
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
        const gastos  = Array.isArray(detalle.gastos) ? detalle.gastos : [];
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
            canDelete:    false,
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
            id:   idSolicitud,
            from: VIEW_TO_ROUTE[fromView] || fromView,
        });
    }

    function showDecisionCommentForm(idSolicitud, decision) {
        const actionsEl = document.querySelector('.solv-exp-actions');
        if (!actionsEl) return;
        const isRequired = decision === 'observada';
        const btnClass   = decision === 'observada' ? 'btn-decide-observar' : 'btn-decide-rechazar';
        const btnLabel   = decision === 'observada' ? 'Confirmar observación' : 'Confirmar rechazo';
        const placeholder = isRequired
            ? 'Indica qué debe corregir el colaborador…'
            : 'Indica el motivo del rechazo…';

        actionsEl.innerHTML =
            '<div id="rendicion-decision-error" class="erp-alert-error" style="display:none;margin-bottom:8px;width:100%;"></div>' +
            '<div class="decision-prompt">' +
            '<textarea class="decision-prompt-ta" placeholder="' + escHtml(placeholder) + '" maxlength="500" rows="3" aria-label="Motivo de la decisión"></textarea>' +
            '<div class="decision-prompt-row">' +
            '<button type="button" class="btn btn-ghost btn-sm" id="btn-dp-cancelar">Cancelar</button>' +
            '<button type="button" class="' + btnClass + ' btn-sm" id="btn-dp-confirmar"' + (isRequired ? ' disabled' : '') + '>' + escHtml(btnLabel) + '</button>' +
            '</div>' +
            '</div>';

        const ta       = actionsEl.querySelector('.decision-prompt-ta');
        const btnConf  = document.getElementById('btn-dp-confirmar');
        const btnCanc  = document.getElementById('btn-dp-cancelar');

        if (isRequired) {
            ta.addEventListener('input', function () {
                btnConf.disabled = !this.value.trim();
            });
        }

        btnCanc.addEventListener('click', function () {
            if (currentDetalle) renderDetalle(currentDetalle);
        });

        btnConf.addEventListener('click', function () {
            const comentario = ta.value.trim();
            if (isRequired && !comentario) return;
            submitDecisionRendicion(idSolicitud, decision, comentario);
        });

        ta.focus();
    }

    async function submitDecisionRendicion(idSolicitud, decision, comentario) {
        const errEl   = document.getElementById('rendicion-decision-error');
        if (errEl) errEl.style.display = 'none';
        const btnConf = document.getElementById('btn-dp-confirmar');
        const btnCanc = document.getElementById('btn-dp-cancelar');
        if (btnConf) {
            btnConf.disabled  = true;
            btnConf.innerHTML = '<div class="spinner" style="width:13px;height:13px;border-width:2px;"></div> Procesando…';
        }
        if (btnCanc) btnCanc.disabled = true;
        try {
            const body = { id_solicitud: parseInt(idSolicitud, 10), decision };
            if (comentario) body.comentario = comentario;
            await apiFetch('/decidir-rendicion', { method: 'POST', body: JSON.stringify(body) });
            const detalleActualizado = await apiFetch(`/detalle-rendicion-admin/${idSolicitud}`);
            renderDetalle(detalleActualizado);
            await loadSolicitudes();
            showToast('success', 'Decisión registrada', `Rendición de solicitud #${idSolicitud}: ${estadoUI.getLabelEstado('rendicion', decision)}.`);
        } catch (error) {
            if (errEl) {
                errEl.textContent   = error.message || 'No se pudo registrar la decisión.';
                errEl.style.display = 'block';
            }
            if (btnConf) {
                const btnLabel = decision === 'observada' ? 'Confirmar observación' : 'Confirmar rechazo';
                btnConf.disabled  = false;
                btnConf.innerHTML = escHtml(btnLabel);
            }
            if (btnCanc) btnCanc.disabled = false;
        }
    }

    async function handleDecisionRendicion(idSolicitud, decision) {
        if (decision === 'observada' || decision === 'rechazada') {
            showDecisionCommentForm(idSolicitud, decision);
            return;
        }
        const errEl   = document.getElementById('rendicion-decision-error');
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
                errEl.textContent   = error.message || 'No se pudo registrar la decisión.';
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

        if (trays[route.viewId]) {
            trays[route.viewId].render(cache);
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

    function bindEvents() {
        document.querySelectorAll('[data-route]').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                navigateTo({
                    name: link.dataset.route,
                    id:   link.dataset.routeId   || null,
                    from: link.dataset.routeFrom || null,
                });
            });
        });

        document.querySelectorAll('.js-btn-refrescar').forEach(btn => {
            btn.addEventListener('click', loadSolicitudes);
        });

        LIST_VIEWS.forEach(viewId => trays[viewId].initInteractions(() => cache));

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
        document.getElementById('btn-modal-aprobar').addEventListener('click', () => handleSolicitudDecision('aprobada', ''));
        document.getElementById('btn-modal-observar').addEventListener('click', () => showModalObservarSection());
        document.getElementById('btn-modal-rechazar').addEventListener('click', () => handleSolicitudDecision('rechazada', ''));
        ['btn-cerrar-admin-historial', 'btn-cancelar-admin-historial'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', () => ModalManager.close('modal-admin-historial'));
        });
        ['btn-cerrar-admin-liq', 'btn-cancelar-admin-liq'].forEach(id => {
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
                showApiError(err.message ? err : 'No se pudo generar el archivo.', 'Exportación fallida');
            } finally {
                setButtonLoading(btn, false);
            }
        });
        ['modal-admin-historial', 'modal-admin-liquidacion'].forEach(id => {
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
        navigate:        navigateTo,
        loadSolicitudes,
        refreshRows:     loadSolicitudes,
        showToast,
        openSolicitudDetail
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

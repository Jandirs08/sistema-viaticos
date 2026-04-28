(function () {
    'use strict';

    const CONFIG = window.ViaticosConfig;

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
    const getRendicionEstado   = estadoUI.getRendicionEstado;
    const renderRendicionBadge = estadoUI.renderRendicionBadge;

    const formatMonto = utils.fmtMonto;
    const formatFecha = utils.fmtFecha;

    const escHtml          = utils.escapeHtml;
    const showToast        = utils.showToast.bind(utils);
    const showApiError     = utils.showApiError.bind(utils);
    const setButtonLoading = utils.setButtonLoading;

    /* ── Modal Manager ────────────────────────────────────── */
    const ModalManager = utils.ModalManager;

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (_ocrInFlight) return; // bloqueado durante OCR
        ['modal-nueva-solicitud','modal-editar-solicitud','modal-rendir-gasto','modal-confirmar-finalizar','modal-historial-solicitud','modal-ocr-picker'].forEach(id => ModalManager.close(id));
    });

    /* ── Form validation ──────────────────────────────────── */
    const Forms = window.ViaticosForms;

    /**
     * Wrapper sobre Forms.validateInput. Forza required:true porque la lista de
     * campos a validar viene de schemas_gasto.required (config.php).
     */
    function validateField(inputEl, errorEl, customValidator) {
        return Forms.validateInput(inputEl, errorEl, {
            required: true,
            validator: customValidator,
        });
    }

    /**
     * Devuelve el número de step (1, 2) al que pertenece un elemento del wizard, o null.
     */
    function getWizardStepOf(el) {
        if (!el) return null;
        const section = el.closest && el.closest('.wizard-panel[data-step]');
        return section ? parseInt(section.dataset.step, 10) : null;
    }

    function getRendicionTipo() {
        return (document.getElementById('rg-tipo') ? document.getElementById('rg-tipo').value : '').trim();
    }

    // Schemas vienen desde window.ViaticosConfigData.schemas_gasto (single
    // source of truth definida en includes/api/config.php). Cada schema declara
    // groups visibles, labels y los fields a mapear en el payload (con soporte
    // opcional para concatenar dos elementos vía concat_with + separator).
    const SCHEMAS_RAW   = (window.ViaticosConfigData && window.ViaticosConfigData.schemas_gasto) || {};
    const TIPO_DEFAULT  = (window.ViaticosConfigData && window.ViaticosConfigData.tipo_default) || 'documento';

    function buildPayloadFromSchema(schema, base) {
        const out = Object.assign({}, base);
        (schema.fields || []).forEach(function (f) {
            const elA = document.getElementById(f.el);
            const valA = elA ? (elA.value || '').trim() : '';
            if (f.concat_with) {
                const elB = document.getElementById(f.concat_with);
                const valB = elB ? (elB.value || '').trim() : '';
                out[f.payload] = valA + (valB ? (f.separator || ' ') + valB : '');
            } else {
                out[f.payload] = elA ? elA.value : '';
            }
        });
        out.id_categoria = parseInt(document.getElementById('rg-categoria').value) || 0;
        return out;
    }

    const RG_SCHEMAS = {};
    Object.keys(SCHEMAS_RAW).forEach(function (key) {
        const s = SCHEMAS_RAW[key];
        RG_SCHEMAS[key] = {
            groups:       s.groups || [],
            labels:       s.labels || {},
            required:     s.required || [],
            buildPayload: function (base) { return buildPayloadFromSchema(s, base); },
        };
    });

    const RG_SCHEMA_DEFAULT = RG_SCHEMAS[TIPO_DEFAULT] || null;

    const ALL_RG_GROUPS = [
        'rg-group-fecha','rg-group-ruc','rg-group-razon','rg-group-concepto','rg-group-motivo',
        'rg-group-destino','rg-group-importe','rg-group-nro','rg-group-ceco-oi',
        'rg-group-ceco','rg-group-oi',
    ];

    function getActiveSchema() {
        const tipo = getRendicionTipo();
        if (!tipo) return null;
        return RG_SCHEMAS[tipo] || RG_SCHEMA_DEFAULT;
    }

    function updateRendirTipoUI() {
        const schema = getActiveSchema();
        const activeGroups = schema ? schema.groups : [];

        ALL_RG_GROUPS.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = activeGroups.includes(id) ? '' : 'none';
        });

        if (schema) {
            Object.entries(schema.labels).forEach(([id, text]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = text;
            });
        }
    }

    function prefillNuevaSolicitudForm() {
        const dniHidden = document.getElementById('ns-dni');
        if (dniHidden) dniHidden.value = CONFIG.profile.dni || '';

        const set = (id, val) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = val || '';
            el.classList.toggle('is-empty', !val);
            if (!val) el.textContent = 'No registrado';
        };
        set('ns-display-nombre',    CONFIG.profile.name);
        set('ns-display-dni',       CONFIG.profile.dni);
        set('ns-display-cargo',     CONFIG.profile.cargo);
        set('ns-display-area',      CONFIG.profile.area);
        set('ns-display-aprobador', CONFIG.profile.aprobador);

        const aprobadorHidden = document.getElementById('ns-aprobador');
        if (aprobadorHidden) aprobadorHidden.value = CONFIG.profile.aprobador || '';
    }

    /* ── Data ─────────────────────────────────────────────── */
    function renderSolicitudRow(sol) {
        const gastos   = getGastosBySolicitud(sol.id);
        const acciones = buildAcciones(sol);
        return `<tr class="row-clickable" data-id="${sol.id}" tabindex="0" role="button" aria-label="Abrir detalle de solicitud ${sol.id}">
            <td class="text-muted">#${sol.id}</td>
            <td>${formatFecha(sol.fecha_creacion)}</td>
            <td>${formatFecha(sol.fecha)}</td>
            <td class="td-truncate" title="${escHtml(sol.motivo)}">${escHtml(sol.motivo)}</td>
            <td><strong>${formatMonto(sol.monto)}</strong></td>
            <td>${escHtml(sol.ceco)}</td>
            <td>${estadoUI.renderNarrativeBadge(sol, { gastos })}</td>
            <td>${acciones}</td>
        </tr>`;
    }

    const solTray = window.ViaticosWorktray.create({
        tbodyId:         'solicitudes-tbody',
        paginationId:    'tbl-pag-solicitudes',
        chipGroupId:     'chips-solicitudes',
        searchId:        'search-solicitudes',
        pageSizeId:      'page-size-solicitudes',
        fechaChipId:     'fecha-chip-solicitudes',
        datesStripId:    'dates-strip-solicitudes',
        dateFromId:      'fecha-desde-solicitudes',
        dateToId:        'fecha-hasta-solicitudes',
        clearBtnId:      'clear-solicitudes',
        sortSectionId:   'view-solicitudes',
        colspan:         8,
        defaultSort:     { key: 'fecha_creacion', dir: 'desc', type: 'date' },
        emptyText:       'Aún no tienes solicitudes registradas.',
        emptySearchText: 'No se encontraron resultados.',
        getChipEstado:   getSolicitudEstado,
        renderRow:       renderSolicitudRow,
        onAfterRender:   function (tbody, pageRows) { attachActionListeners(tbody, pageRows); },
    });

    let solicitudesCache = [];
    let gastosCache = [];

    async function fetchSolicitudes() { return await apiFetch('/mis-solicitudes'); }
    async function fetchGastos()      { return await apiFetch('/mis-rendiciones'); }
    async function refreshSolicitudesCache() { solicitudesCache = await fetchSolicitudes(); return solicitudesCache; }
    async function refreshGastosCache() { gastosCache = await fetchGastos(); return gastosCache; }

    const ROUTE_CONFIG = {
        inicio:      { viewId: 'view-inicio',            breadcrumb: 'Inicio' },
        solicitudes: { viewId: 'view-solicitudes',       breadcrumb: 'Mis Solicitudes' },
        rendiciones: { viewId: 'view-rendiciones',       breadcrumb: 'Mis Rendiciones' },
        solicitud:   { viewId: 'view-detalle-solicitud', breadcrumb: 'Detalle de Solicitud', requiresId: true },
    };

    const router = window.ViaticosRouter.create({
        routes:       ROUTE_CONFIG,
        defaultRoute: 'inicio',
        onNavigate:   function (route) { return renderRoute(route); },
    });

    function setSectionTitle(routeName) {
        const titleEl = document.getElementById('topbar-section-name');
        if (!titleEl) return;
        const labels = {
            inicio: 'Inicio',
            solicitudes: 'Mis Solicitudes',
            solicitud: 'Detalle de Solicitud',
            rendiciones: 'Mis Rendiciones',
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
                target === 'view-solicitudes' && viewId === 'view-detalle-solicitud'
            );
            link.classList.toggle('active', isActive);
        });

        setSectionTitle(routeName);
    }

    function normalizeTarget(target) {
        if (typeof target === 'string') {
            const byView = {
                'view-inicio':            'inicio',
                'view-solicitudes':       'solicitudes',
                'view-rendiciones':       'rendiciones',
                'view-detalle-solicitud': 'solicitud',
            };
            return { name: byView[target] || target, id: null, from: null };
        }
        return { name: (target && target.name) || 'inicio', id: (target && target.id) || null, from: (target && target.from) || null };
    }

    async function renderRoute(route) {
        switch (route.name) {
            case 'inicio':      await loadInicioView(); break;
            case 'solicitudes': await loadSolicitudesView(); break;
            case 'rendiciones':
                showView('view-rendiciones', 'rendiciones');
                await loadRendicionesView();
                break;
            case 'solicitud':   await loadDetalleSolicitudContent(route.id); break;
        }
    }

    function renderInicioEyebrow() {
        const el = document.getElementById('inicio-eyebrow');
        if (!el) return;
        const d = new Date();
        const fecha = new Intl.DateTimeFormat('es-PE', {
            weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
        }).format(d);
        el.textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
    }

    function classifyInicio(sol) {
        const estadoSol  = getSolicitudEstado(sol);
        const estadoRend = getRendicionEstado(sol, { gastos: getGastosBySolicitud(sol.id) });
        return { estadoSol, estadoRend };
    }

    function renderInicioStats(data) {
        renderInicioEyebrow();
        const list = Array.isArray(data) ? data : [];
        let porRendir = 0, enRevision = 0, observadas = 0;

        list.forEach(sol => {
            const { estadoSol, estadoRend } = classifyInicio(sol);
            if (estadoSol === 'aprobada' && (estadoRend === 'no_iniciada' || estadoRend === 'en_proceso')) porRendir++;
            if (estadoRend === 'en_revision') enRevision++;
            if (estadoSol === 'observada' || estadoRend === 'observada') observadas++;
        });

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = String(value);
        };
        setText('kpi-por-rendir',  porRendir);
        setText('kpi-en-revision', enRevision);
        setText('kpi-observadas',  observadas);
        setText('kpi-total',       list.length);

        const summary = document.getElementById('inicio-summary');
        if (summary) {
            const toggle = (metric, active) => {
                const el = summary.querySelector('[data-metric="' + metric + '"]');
                if (el) el.classList.toggle('is-active', active);
            };
            toggle('por-rendir',  porRendir > 0);
            toggle('en-revision', enRevision > 0);
            toggle('observadas',  observadas > 0);
        }

        const byFilter = {
            'por-rendir': porRendir,
            'en-revision': enRevision,
            'observadas': observadas,
            'total': list.length,
        };
        Object.keys(byFilter).forEach(k => {
            const el = document.getElementById('metric-' + k);
            if (el) el.disabled = byFilter[k] === 0 && k !== 'total';
        });
    }

    function inicioBandejaReason(sol, estadoSol, estadoRend) {
        if (estadoSol === 'observada')  return { tag: 'observada-anticipo',  label: 'Anticipo observado',  hint: 'Corrige y reenvía.', tone: 'warn' };
        if (estadoSol === 'rechazada')  return { tag: 'rechazada-anticipo',  label: 'Anticipo rechazado',  hint: 'Consulta el historial.', tone: 'danger' };
        if (estadoRend === 'observada') return { tag: 'observada-rendicion', label: 'Rendición observada', hint: 'Revisa y reenvía.',   tone: 'warn' };
        if (estadoRend === 'rechazada') return { tag: 'rechazada-rendicion', label: 'Rendición rechazada', hint: 'Consulta el historial.', tone: 'danger' };
        if (estadoSol === 'aprobada' && estadoRend === 'no_iniciada') return { tag: 'por-rendir',    label: 'Listo para rendir',    hint: 'Agrega tus primeros gastos.', tone: 'accent' };
        if (estadoSol === 'aprobada' && estadoRend === 'en_proceso')  return { tag: 'en-proceso',    label: 'Rendición en curso',   hint: 'Termina y envía a revisión.', tone: 'accent' };
        return null;
    }

    function renderInicioBandeja(data) {
        const list = document.getElementById('inicio-bandeja');
        const countEl = document.getElementById('inicio-bandeja-count');
        const subEl = document.getElementById('inicio-bandeja-sub');
        if (!list) return;

        const items = (Array.isArray(data) ? data : [])
            .map(sol => {
                const { estadoSol, estadoRend } = classifyInicio(sol);
                const reason = inicioBandejaReason(sol, estadoSol, estadoRend);
                return reason ? { sol, estadoSol, estadoRend, reason } : null;
            })
            .filter(Boolean)
            .sort((a, b) => {
                const order = { danger: 0, warn: 1, accent: 2 };
                return (order[a.reason.tone] - order[b.reason.tone]) || (Number(b.sol.id) - Number(a.sol.id));
            });

        if (countEl) countEl.textContent = String(items.length);

        if (!items.length) {
            if (subEl) subEl.textContent = 'Todo en orden. Nada pendiente por ahora.';
            list.innerHTML = `
                <li class="inicio-bandeja-empty">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    <span>Sin pendientes. Cuando un anticipo o rendición requiera tu acción aparecerá aquí.</span>
                </li>`;
            return;
        }

        if (subEl) subEl.textContent = `${items.length} ${items.length === 1 ? 'solicitud' : 'solicitudes'} esperan una acción tuya.`;

        list.innerHTML = items.map(({ sol, reason }, idx) => {
            const ceco = escHtml(sol.ceco || 'Sin CECO');
            const motivo = escHtml(sol.motivo || 'Sin motivo');
            const fecha = formatFecha(sol.fecha);
            const monto = formatMonto(sol.monto);
            const num   = String(idx + 1).padStart(2, '0');
            return `
                <li class="inicio-bandeja-item inicio-bandeja-${reason.tone}"
                    tabindex="0" role="button"
                    data-route="solicitud" data-route-id="${sol.id}" data-route-from="inicio"
                    onclick="ViaticosApp.navigate({ name: 'solicitud', id: ${sol.id}, from: 'inicio' })"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();ViaticosApp.navigate({ name: 'solicitud', id: ${sol.id}, from: 'inicio' });}">
                    <span class="inicio-bandeja-num" aria-hidden="true">${num}</span>
                    <div class="inicio-bandeja-body">
                        <div class="inicio-bandeja-row">
                            <span class="inicio-bandeja-tag">${escHtml(reason.label)}</span>
                            <span class="inicio-bandeja-sep">·</span>
                            <span class="inicio-bandeja-id">Sol. #${sol.id}</span>
                        </div>
                        <p class="inicio-bandeja-motivo">${motivo}</p>
                        <div class="inicio-bandeja-meta">
                            <span>${fecha}</span>
                            <span class="inicio-bandeja-dot"></span>
                            <span>${ceco}</span>
                        </div>
                    </div>
                    <div class="inicio-bandeja-end">
                        <span class="inicio-bandeja-monto">${monto}</span>
                        <span class="inicio-bandeja-cta" aria-hidden="true">
                            ${reason.tone === 'accent' ? 'Rendir' : 'Revisar'}
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                        </span>
                    </div>
                </li>`;
        }).join('');
    }

    function renderInicioRecent(data) {
        const list = document.getElementById('inicio-recent-list');
        if (!list) return;
        const recent = (Array.isArray(data) ? [...data] : [])
            .sort((a, b) => Number(b.id) - Number(a.id))
            .slice(0, 5);

        if (!recent.length) {
            list.innerHTML = `
                <li class="inicio-recent-empty">
                    Aún no tienes solicitudes registradas.
                    <button type="button" class="inicio-linkbtn" onclick="document.getElementById('btn-inicio-nueva').click();">Crear la primera →</button>
                </li>`;
            return;
        }

        list.innerHTML = recent.map(sol => {
            const badge = estadoUI.renderNarrativeBadge(sol, { gastos: getGastosBySolicitud(sol.id) });
            const ceco = escHtml(sol.ceco || '—');
            return `
                <li class="inicio-recent-item"
                    tabindex="0" role="button"
                    onclick="ViaticosApp.navigate({ name: 'solicitud', id: ${sol.id}, from: 'inicio' })"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();ViaticosApp.navigate({ name: 'solicitud', id: ${sol.id}, from: 'inicio' });}">
                    <span class="inicio-recent-id">#${sol.id}</span>
                    <span class="inicio-recent-fecha">${formatFecha(sol.fecha)}</span>
                    <span class="inicio-recent-ceco">${ceco}</span>
                    <span class="inicio-recent-monto">${formatMonto(sol.monto)}</span>
                    <span class="inicio-recent-estado">${badge}</span>
                </li>`;
        }).join('');
    }

    function renderInicioLoading() {
        const bandeja = document.getElementById('inicio-bandeja');
        const recent  = document.getElementById('inicio-recent-list');
        const sk = n => Array.from({length:n}, () => '<li class="inicio-skel"></li>').join('');
        if (bandeja) bandeja.innerHTML = sk(2);
        if (recent)  recent.innerHTML  = sk(3);
    }

    async function loadInicioView() {
        showView('view-inicio', 'inicio');
        renderInicioEyebrow();
        renderInicioLoading();
        try {
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            solTray.updateChipCounts(solicitudesCache);
            renderInicioStats(solicitudesCache);
            renderInicioBandeja(solicitudesCache);
            renderInicioRecent(solicitudesCache);
        } catch (err) {
            showApiError(err);
        }
    }

    async function loadSolicitudesView() {
        showView('view-solicitudes', 'solicitudes');
        solTray.setLoading();
        try {
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            solTray.updateChipCounts(solicitudesCache);
            solTray.render(solicitudesCache);
        } catch (err) {
            showApiError(err);
        }
    }

    async function openDetalleSolicitudView(solicitudId) {
        await router.navigateTo('solicitud', { id: parseInt(solicitudId, 10), from: 'solicitudes' });
    }

    async function loadDetalleSolicitudContent(id) {
        showView('view-detalle-solicitud', 'solicitud');
        const contentEl = document.getElementById('detalle-view-content');
        const errorEl = document.getElementById('detalle-view-error');
        if (contentEl) contentEl.innerHTML = `<div class="table-loading"><div class="spinner"></div> Cargando detalle…</div>`;
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        try {
            if (!solicitudesCache.length) await refreshSolicitudesCache();
            if (!gastosCache.length) await refreshGastosCache();
            const sol = getSolicitudById(id);
            if (!sol) throw new Error('No se encontró la solicitud seleccionada.');
            const detalle = await apiFetch('/detalle-solicitud/' + id, { method: 'GET' });
            sol.historial     = Array.isArray(detalle.historial) ? detalle.historial : [];
            sol.total_rendido = Number(detalle.total_rendido || 0);
            renderDetalleSolicitudContent(sol, getGastosBySolicitud(id));
        } catch (err) {
            if (errorEl) { errorEl.textContent = err.message; errorEl.style.display = 'block'; }
            showApiError(err);
        }
    }

    let currentLiqData = null;

    async function openLiquidacionModal(solicitudId = null) {
        const id = parseInt(solicitudId || router.getCurrentRoute().id, 10);
        if (!id) return;
        const container = document.getElementById('colab-liq-container');
        if (!container) return;
        currentLiqData = null;
        container.innerHTML = '<div class="liq-doc-empty"><div class="spinner"></div> Cargando liquidación…</div>';
        ModalManager.open('modal-colab-liquidacion');
        try {
            if (!solicitudesCache.length) await refreshSolicitudesCache();
            if (!gastosCache.length) await refreshGastosCache();
            const sol = getSolicitudById(id);
            if (!sol) throw new Error('No se encontró la solicitud para la liquidación.');
            const gastos = getGastosBySolicitud(id);
            const liqOpts = {
                colaboradorNombre:  CONFIG.profile.name || '',
                codigoEmpleado:     CONFIG.profile.dni  || '',
                area:               CONFIG.profile.area || '',
                cargo:              CONFIG.profile.cargo || '',
                fechaRendicion:     formatFecha(sol.fecha_creacion) || '',
                estadoRendicionKey: getRendicionEstado(sol, { gastos }),
            };
            currentLiqData = window.ViaticosLiquidacion.buildData(sol, gastos, liqOpts);
            container.innerHTML = window.ViaticosLiquidacion.renderDoc(currentLiqData);
        } catch (err) {
            container.innerHTML = `<div class="liq-doc-empty" style="color:#C53030;">${escHtml(err.message)}</div>`;
            showApiError(err);
        }
    }

    async function navigateTo(target) {
        const route = normalizeTarget(target);
        await router.navigateTo(route.name, { id: route.id, from: route.from });
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
        Forms.clearFormErrors(document.getElementById('form-editar-solicitud'));
        ModalManager.open('modal-editar-solicitud');
    }

    async function handleNuevaSolicitudSubmit(e) {
        e.preventDefault();
        const form  = document.getElementById('form-nueva-solicitud');
        const btn   = document.getElementById('btn-submit-nueva-solicitud');
        const errEl = document.getElementById('nueva-solicitud-error');
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
        Forms.clearFormErrors(form);

        const fields = [
            { id: 'ns-monto',  validator: v => parseFloat(v) >= 1 },
            { id: 'ns-fecha' },
            { id: 'ns-ceco' },
            { id: 'ns-motivo' },
        ];
        let isValid = true;
        fields.forEach(f => {
            const ok = Forms.validateInput(
                document.getElementById(f.id),
                document.getElementById('err-' + f.id),
                { required: true, validator: f.validator }
            );
            if (!ok) isValid = false;
        });

        const aprobador = document.getElementById('ns-aprobador').value.trim();
        if (!aprobador) {
            if (errEl) { errEl.style.display = 'block'; errEl.textContent = 'No tienes aprobador asignado en tu perfil. Contacta al administrador.'; }
            isValid = false;
        }

        if (!isValid) {
            const errCount = form.querySelectorAll('.form-control.is-invalid').length;
            Forms.focusFirstInvalid(form);
            if (errCount > 0) {
                showToast('error', 'Faltan datos', errCount > 1
                    ? `Completa los ${errCount} campos marcados.`
                    : 'Completa el campo marcado.');
            }
            return;
        }

        const payload = {
            dni:       document.getElementById('ns-dni').value.trim(),
            monto:     parseFloat(document.getElementById('ns-monto').value),
            fecha:     document.getElementById('ns-fecha').value,
            ceco:      document.getElementById('ns-ceco').value.trim(),
            aprobador: aprobador,
            motivo:    document.getElementById('ns-motivo').value.trim(),
        };

        try {
            setButtonLoading(btn, true);
            await apiFetch('/nueva-solicitud', { method: 'POST', body: JSON.stringify(payload) });
            ModalManager.close('modal-nueva-solicitud');
            form.reset();
            prefillNuevaSolicitudForm();
            await Promise.all([refreshSolicitudesCache(), refreshGastosCache()]);
            renderInicioStats(solicitudesCache);
            renderInicioBandeja(solicitudesCache);
            renderInicioRecent(solicitudesCache);
            if (router.getCurrentRoute().name === 'solicitudes') solTray.render(solicitudesCache);
            showToast('success', 'Solicitud registrada');
            await navigateTo({ name: 'solicitudes' });
        } catch (err) {
            const msg = err && err.message ? String(err.message) : 'Error inesperado';
            const handled = Forms.handleServerError(form, msg);
            if (handled.handled) {
                const tit = 'invalid' === handled.kind ? 'Datos inválidos' : 'Datos incompletos';
                const verb = 'invalid' === handled.kind ? 'Revisa' : 'Faltan';
                showToast('error', tit, `${verb} campos: ${handled.fields.join(', ')}.`);
            } else {
                if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
                showApiError(msg);
            }
        } finally {
            setButtonLoading(btn, false);
        }
    }

    async function handleEditarSolicitudSubmit(e) {
        e.preventDefault();
        const form  = document.getElementById('form-editar-solicitud');
        const btn   = document.getElementById('btn-submit-editar-solicitud');
        const errEl = document.getElementById('editar-solicitud-error');
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
        Forms.clearFormErrors(form);

        const id_solicitud = parseInt(document.getElementById('ed-post-id').value, 10);
        if (!id_solicitud) return;

        const fields = [
            { id: 'ed-dni',    validator: v => /^\d{8}$/.test(v) },
            { id: 'ed-monto',  validator: v => parseFloat(v) >= 1 },
            { id: 'ed-fecha' },
            { id: 'ed-ceco' },
            { id: 'ed-motivo' },
        ];
        let isValid = true;
        fields.forEach(f => {
            const ok = Forms.validateInput(
                document.getElementById(f.id),
                document.getElementById('err-' + f.id),
                { required: true, validator: f.validator }
            );
            if (!ok) isValid = false;
        });

        if (!isValid) {
            const errCount = form.querySelectorAll('.form-control.is-invalid').length;
            Forms.focusFirstInvalid(form);
            showToast('error', 'Faltan datos', errCount > 1
                ? `Completa los ${errCount} campos marcados.`
                : 'Completa el campo marcado.');
            return;
        }

        setButtonLoading(btn, true);
        try {
            const payload = {
                id_solicitud,
                dni:    document.getElementById('ed-dni').value,
                monto:  parseFloat(document.getElementById('ed-monto').value),
                fecha:  document.getElementById('ed-fecha').value,
                ceco:   document.getElementById('ed-ceco').value,
                motivo: document.getElementById('ed-motivo').value,
            };
            await apiFetch('/editar-solicitud', { method: 'POST', body: JSON.stringify(payload) });
            ModalManager.close('modal-editar-solicitud');
            await refreshSolicitudesCache();
            await refreshGastosCache();
            solTray.render(solicitudesCache);
            const sol = getSolicitudById(id_solicitud);
            if (sol) {
                renderDetalleSolicitudContent(sol, getGastosBySolicitud(id_solicitud));
                await navigateTo({ name: 'solicitud', id: id_solicitud, from: 'solicitudes' });
            }
            showToast('success', 'Solicitud corregida y reenviada a revisión.');
        } catch (err) {
            const msg = err && err.message ? String(err.message) : 'Error inesperado';
            const handled = Forms.handleServerError(form, msg);
            if (handled.handled) {
                const tit = 'invalid' === handled.kind ? 'Datos inválidos' : 'Datos incompletos';
                const verb = 'invalid' === handled.kind ? 'Revisa' : 'Faltan';
                showToast('error', tit, `${verb} campos: ${handled.fields.join(', ')}.`);
            } else {
                if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
                showApiError(msg);
            }
        } finally {
            setButtonLoading(btn, false);
        }
    }

    async function handleDeleteGasto(solicitudId, gastoId) {
        try {
            await apiFetch('/gasto/' + gastoId, { method: 'DELETE' });
            await refreshGastosCache();
            const sol = getSolicitudById(solicitudId);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(solicitudId));
            showToast('success', 'Gasto eliminado.');
        } catch (err) {
            showApiError(err);
        }
    }

    async function handleReenviarRendicion(solicitudId) {
        if (!confirm('¿Reenviar la rendición a revisión? El administrador podrá revisarla nuevamente.')) return;
        try {
            await apiFetch('/reenviar-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: solicitudId }) });
            await refreshSolicitudesCache();
            await refreshGastosCache();
            solTray.render(solicitudesCache);
            const sol = getSolicitudById(solicitudId);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(solicitudId));
            showToast('success', 'Rendición reenviada a revisión.');
        } catch (err) {
            showApiError(err);
        }
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
                btns += `<button class="btn btn-primary btn-sm action-ver-rendir" data-id="${sol.id}" title="Rendir gastos de la solicitud">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                    Rendir</button>`;
            }
        }

        return btns || `<span style="color:var(--text-light);font-size:12px;">Sin acciones</span>`;
    }

    function attachActionListeners(tbody, data) {
        utils.bindRowAction(tbody, {
            onActivate: (id) => openDetalleSolicitudView(id),
        });

        tbody.querySelectorAll('.action-editar').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const sol = data.find(s => s.id === parseInt(btn.dataset.id, 10));
                if (sol) openEditarModal(sol);
            });
        });
        tbody.querySelectorAll('.action-ver-rendir').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openDetalleSolicitudView(parseInt(btn.dataset.id, 10));
            });
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

    const apiFetchForm = utils.createApiFetchForm(CONFIG.apiBase, CONFIG.nonce);

    function renderDetalleSolicitudContent(sol, gastos) {
        const contentEl       = document.getElementById('detalle-view-content');
        const estadoSolicitud = getSolicitudEstado(sol);
        const estadoRend      = getRendicionEstado(sol, { gastos });
        const canAdd          = estadoSolicitud === 'aprobada' && (!sol.rendicion_finalizada || estadoRend === 'observada') && !['aprobada', 'rechazada', 'en_revision'].includes(estadoRend);
        const canFinalize     = estadoSolicitud === 'aprobada' && !sol.rendicion_finalizada && gastos.length > 0;
        const canLiquidacion  = !!sol.rendicion_finalizada;
        const canEditSolicitud = estadoSolicitud === 'observada';
        const canReenviar     = estadoRend === 'observada';

        const editIcon     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02.0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41.0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>';
        const checkIcon    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
        const plusIcon     = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
        const docIcon      = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
        const timelineIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="5" r="2"/><circle cx="5" cy="12" r="2"/><circle cx="5" cy="19" r="2"/><rect x="4.25" y="6.5" width="1.5" height="4"/><rect x="4.25" y="13.5" width="1.5" height="4"/><rect x="9" y="4" width="11" height="2" rx="1"/><rect x="9" y="11" width="8" height="2" rx="1"/><rect x="9" y="18" width="10" height="2" rx="1"/></svg>';

        const accionesHtml =
            '<button type="button" class="btn btn-primary" id="detalle-hero-editar-solicitud"' + (canEditSolicitud ? '' : ' style="display:none;"') + '>' + editIcon + 'Corregir solicitud</button>' +
            '<button type="button" class="btn btn-primary" id="detalle-hero-reenviar-rendicion"' + (canReenviar ? '' : ' style="display:none;"') + '>' + checkIcon + 'Reenviar rendición</button>' +
            '<button type="button" class="btn btn-primary" id="detalle-hero-finalizar-rendicion"' + (canFinalize ? '' : ' style="display:none;"') + '>' + checkIcon + 'Finalizar y enviar rendición</button>' +
            '<button type="button" class="btn btn-secondary" id="detalle-hero-agregar-gasto"' + (canAdd ? '' : ' style="display:none;"') + '>' + plusIcon + 'Agregar gasto</button>' +
            '<button type="button" class="btn btn-secondary" id="detalle-hero-ver-liquidacion"' + (canLiquidacion ? '' : ' style="display:none;"') + '>' + docIcon + 'Ver liquidación</button>' +
            '<button type="button" class="btn btn-ghost" data-open-history="1">' + timelineIcon + 'Historial</button>';

        const { historialHtml } = window.ViaticosDetalleUI.render(contentEl, sol, gastos, {
            apiFetch,
            canDelete: true,
            accionesHtml,
            canDeleteGasto: canReenviar,
            onDeleteGasto: (gastoId) => handleDeleteGasto(sol.id, gastoId),
        });

        const historial           = Array.isArray(sol.historial) ? sol.historial : [];
        const historialBodyEl     = document.getElementById('detalle-historial-body');
        const historialMetaEl     = document.getElementById('detalle-historial-meta');
        const historialSubtitleEl = document.getElementById('detalle-historial-subtitulo');
        if (historialBodyEl) {
            historialBodyEl.innerHTML = historial.length
                ? historialHtml
                : '<div class="table-empty" style="padding:32px 20px;"><svg viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;opacity:.28;"><path d="M13 3a9 9 0 1 0 8.95 10h-2.02A7 7 0 1 1 13 5v4l5-5-5-5v4z"/></svg><p>No hay movimientos registrados todavía.</p></div>';
        }
        if (historialMetaEl) {
            historialMetaEl.innerHTML =
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Expediente</span><strong>#' + sol.id + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Eventos</span><strong>' + historial.length + '</strong></span>' +
                '<span class="solv-history-chip"><span class="solv-history-chip-label">Gastos</span><strong>' + gastos.length + '</strong></span>';
        }
        if (historialSubtitleEl) historialSubtitleEl.textContent = 'Seguimiento completo de la solicitud #' + sol.id + ' y su rendición.';

        const btnAgregar     = contentEl.querySelector('#detalle-hero-agregar-gasto');
        const btnFinalizar   = contentEl.querySelector('#detalle-hero-finalizar-rendicion');
        const btnLiquidacion = contentEl.querySelector('#detalle-hero-ver-liquidacion');
        const btnEditar      = contentEl.querySelector('#detalle-hero-editar-solicitud');
        const btnReenviar    = contentEl.querySelector('#detalle-hero-reenviar-rendicion');

        if (btnAgregar)     btnAgregar.addEventListener('click',     () => openRendirModal(sol.id));
        if (btnFinalizar)   btnFinalizar.addEventListener('click',   () => { if (!sol.rendicion_finalizada && gastos.length) ModalManager.open('modal-confirmar-finalizar'); });
        if (btnLiquidacion) btnLiquidacion.addEventListener('click', () => openLiquidacionModal(sol.id));
        if (btnEditar)      btnEditar.addEventListener('click',      () => openEditarModal(sol));
        if (btnReenviar)    btnReenviar.addEventListener('click',    () => handleReenviarRendicion(sol.id));
        contentEl.querySelectorAll('[data-open-history="1"]').forEach(btn => btn.addEventListener('click', () => ModalManager.open('modal-historial-solicitud')));

    }

    async function handleFinalizarRendicion() {
        const currentId = router.getCurrentRoute().id;
        if (!currentId) return;
        try {
            await apiFetch('/finalizar-rendicion', { method: 'POST', body: JSON.stringify({ id_solicitud: currentId }) });
            await refreshSolicitudesCache(); await refreshGastosCache();
            solTray.render(solicitudesCache);
            const sol = getSolicitudById(currentId);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(currentId));
            showToast('success', 'Finalizado', 'Rendición enviada a revisión.');
        } catch (err) { showApiError(err); }
    }

    function populateCategoriasSelect() {
        const sel = document.getElementById('rg-categoria');
        if (!sel || sel.options.length > 1) return;
        (window.ViaticosCategoriasGasto || []).forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre;
            sel.appendChild(opt);
        });
    }

    function updateAdjuntosBadge(tipo) {
        const badge = document.getElementById('rg-adj-badge');
        const dz    = document.getElementById('rg-dropzone');
        if (!badge || !dz) return;
        const requerido = tipo && tipo !== 'movilidad' && tipo !== 'vale_caja';
        badge.textContent = requerido ? 'Obligatorio' : 'Opcional';
        badge.classList.toggle('is-required', requerido);
        badge.classList.toggle('is-optional', !requerido);
        dz.classList.toggle('is-required', requerido);
    }

    function onCategoriaChange() {
        const id  = parseInt(document.getElementById('rg-categoria').value);
        const cat = (window.ViaticosCategoriasGasto || []).find(c => c.id === id);
        const infoEl = document.getElementById('rg-cat-info');
        const tipoEl = document.getElementById('rg-tipo');
        if (cat && cat.tipo) {
            tipoEl.value = cat.tipo;
            document.getElementById('rg-cat-cta').textContent   = cat.cta_contable || '—';
            document.getElementById('rg-cat-clase').textContent = cat.clase_doc    || '—';
            infoEl.classList.add('is-visible');
        } else {
            tipoEl.value = '';
            infoEl.classList.remove('is-visible');
        }
        updateAdjuntosBadge(tipoEl.value);
        updateRendirTipoUI();
        updateOcrButtonVisibility();
    }

    document.addEventListener('DOMContentLoaded', function () {
        populateCategoriasSelect();
        document.getElementById('rg-categoria').addEventListener('change', onCategoriaChange);
    });

    function openRendirModal(solicitudId) {
        const form = document.getElementById('form-rendir-gasto');
        form.reset(); Forms.clearFormErrors(form);
        document.getElementById('rg-cat-info').classList.remove('is-visible');
        updateAdjuntosBadge('');
        _adjFiles = []; renderAdjPickList();
        updateRendirTipoUI();
        updateOcrButtonVisibility();
        hideOcrBanner();
        _forceGoToStep(1);
        const idInput = document.getElementById('rg-id-solicitud');
        const refEl = document.getElementById('rendir-sol-ref');
        if (idInput) idInput.value = solicitudId;
        if (refEl) refEl.textContent = `#${solicitudId}`;
        ModalManager.open('modal-rendir-gasto');
    }

    let _adjFiles = [];
    let _wizardStep = 1;

    function _fileExtClass(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf') return 'pdf';
        if (['jpg', 'jpeg', 'png'].indexOf(ext) !== -1) return 'img';
        return 'file';
    }
    function _fileExtLabel(name) {
        const ext = (name.split('.').pop() || '').toUpperCase();
        return ext.slice(0, 3) || 'FILE';
    }
    function _fileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function renderAdjPickList() {
        const listEl  = document.getElementById('rg-adj-file-list');
        const emptyEl = document.getElementById('dz-empty');
        const addEl   = document.getElementById('dz-add-more');
        const dz      = document.getElementById('rg-dropzone');
        if (!listEl) return;
        if (!_adjFiles.length) {
            listEl.innerHTML = '';
            if (emptyEl) emptyEl.style.display = '';
            if (addEl) addEl.style.display = 'none';
            if (dz) dz.classList.remove('has-files');
            return;
        }
        listEl.innerHTML = _adjFiles.map((f, i) => `
            <div class="dropzone-file">
                <span class="dropzone-file__icon ${_fileExtClass(f.name)}">${_fileExtLabel(f.name)}</span>
                <span class="dropzone-file__name" title="${escHtml(f.name)}">${escHtml(f.name)}</span>
                <span class="dropzone-file__size">${_fileSize(f.size)}</span>
                <button type="button" class="dropzone-file__remove" data-idx="${i}" aria-label="Quitar archivo">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>`).join('');
        listEl.querySelectorAll('.dropzone-file__remove').forEach(btn => {
            btn.addEventListener('click', () => {
                _adjFiles.splice(parseInt(btn.dataset.idx, 10), 1);
                renderAdjPickList();
                updateOcrButtonVisibility();
            });
        });
        if (emptyEl) emptyEl.style.display = 'none';
        if (addEl) addEl.style.display = '';
        if (dz) dz.classList.add('has-files');
        updateOcrButtonVisibility();
    }

    const ADJ_CONFIG = (window.ViaticosConfigData && window.ViaticosConfigData.adjuntos) || { max_file_bytes: 5 * 1024 * 1024, max_count: 10 };
    const ADJ_ALLOWED_EXT_RE = /\.(pdf|jpg|jpeg|png|heic|heif)$/i;

    function filterAndAddFiles(rawFiles) {
        const maxBytes = ADJ_CONFIG.max_file_bytes || 5 * 1024 * 1024;
        const maxCount = ADJ_CONFIG.max_count || 10;
        const errors   = [];
        for (const f of rawFiles) {
            if (!ADJ_ALLOWED_EXT_RE.test(f.name)) {
                errors.push(`${f.name}: formato no permitido.`);
                continue;
            }
            if (f.size > maxBytes) {
                const mb = (maxBytes / (1024 * 1024)).toFixed(0);
                errors.push(`${f.name}: supera ${mb} MB.`);
                continue;
            }
            if (_adjFiles.length >= maxCount) {
                errors.push(`Máximo ${maxCount} archivos por gasto.`);
                break;
            }
            _adjFiles.push(f);
        }
        if (errors.length) showToast('error', 'Archivo no agregado', errors.join(' '));
    }

    function bindDropzone() {
        const dz       = document.getElementById('rg-dropzone');
        const input    = document.getElementById('rg-adj-input');
        const emptyBtn = document.getElementById('dz-empty');
        const addBtn   = document.getElementById('dz-add-more');
        if (!dz || !input) return;
        if (emptyBtn) emptyBtn.addEventListener('click', () => input.click());
        if (addBtn)   addBtn.addEventListener('click',   () => input.click());
        input.addEventListener('change', function () {
            filterAndAddFiles(Array.from(this.files));
            this.value = '';
            renderAdjPickList();
            updateOcrButtonVisibility();
        });
        ['dragenter', 'dragover'].forEach(evt => {
            dz.addEventListener(evt, e => { e.preventDefault(); dz.classList.add('is-dragover'); });
        });
        dz.addEventListener('dragleave', e => {
            e.preventDefault();
            if (!dz.contains(e.relatedTarget)) dz.classList.remove('is-dragover');
        });
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('is-dragover');
            filterAndAddFiles(Array.from(e.dataTransfer.files));
            renderAdjPickList();
            updateOcrButtonVisibility();
        });
    }

    /* ── OCR auto-llenado ────────────────────────────────── */
    const OCR_CONFIG = (window.ViaticosConfigData && window.ViaticosConfigData.ocr) || { enabled: false, timeout_ms: 35000 };
    const OCR_TIPOS_SOPORTADOS = (OCR_CONFIG.tipos_soportados && OCR_CONFIG.tipos_soportados.length)
        ? OCR_CONFIG.tipos_soportados
        : ['documento', 'vale_caja'];
    const OCR_ALLOWED_EXTS     = (OCR_CONFIG.allowed_exts && OCR_CONFIG.allowed_exts.length)
        ? OCR_CONFIG.allowed_exts
        : ['pdf', 'jpg', 'jpeg', 'png', 'heic', 'heif'];
    const OCR_CONFIANZA_BAJA   = 0.5;
    const OCR_LOADER_STEPS     = [
        { delay: 0,    text: 'Subiendo archivo' },
        { delay: 1200, text: 'Procesando con IA' },
        { delay: 5000, text: 'Extrayendo datos del comprobante' },
        { delay: 12000, text: 'Casi listo, validando información' },
    ];
    let   _ocrInFlight   = false;
    let   _ocrStepTimers = [];

    function shouldRunOcrOnNext() {
        if (!OCR_CONFIG.enabled) return false;
        if (OCR_TIPOS_SOPORTADOS.indexOf(getRendicionTipo()) === -1) return false;
        return _adjFiles.length > 0;
    }

    function updateOcrButtonVisibility() {
        const hint = document.getElementById('rg-ocr-hint');
        if (!hint) return;
        hint.hidden = !shouldRunOcrOnNext();
    }

    function showOcrLoader() {
        const loader = document.getElementById('rg-ocr-loader');
        const step   = document.getElementById('rg-ocr-loader-step');
        if (!loader) return;
        loader.hidden = false;
        // Oculta el contenido del paso 1 mientras se procesa.
        document.querySelectorAll('#modal-rendir-gasto .wizard-panel[data-step="1"] > .form-group').forEach(el => el.style.display = 'none');
        const hint = document.getElementById('rg-ocr-hint');
        if (hint) hint.hidden = true;
        _ocrStepTimers.forEach(t => clearTimeout(t));
        _ocrStepTimers = OCR_LOADER_STEPS.map(s => setTimeout(() => {
            if (step) step.textContent = s.text + '…';
        }, s.delay));
    }

    function hideOcrLoader() {
        const loader = document.getElementById('rg-ocr-loader');
        if (loader) loader.hidden = true;
        document.querySelectorAll('#modal-rendir-gasto .wizard-panel[data-step="1"] > .form-group').forEach(el => el.style.display = '');
        _ocrStepTimers.forEach(t => clearTimeout(t));
        _ocrStepTimers = [];
        updateOcrButtonVisibility();
    }

    /**
     * Muestra un banner sutil dentro del paso 2 con el resultado del OCR.
     * @param {'success'|'warning'|'error'} kind
     * @param {string} text
     */
    function showOcrBanner(kind, text) {
        const banner = document.getElementById('rg-ocr-banner');
        const txtEl  = document.getElementById('rg-ocr-banner-text');
        if (!banner || !txtEl) return;
        banner.className = 'rg-ocr-banner is-' + kind;
        txtEl.textContent = text;
        banner.hidden = false;
    }

    function hideOcrBanner() {
        const banner = document.getElementById('rg-ocr-banner');
        if (banner) banner.hidden = true;
    }

    function setOcrAutofilled(elId, value) {
        const el = document.getElementById(elId);
        if (!el || value === null || value === undefined || value === '') return false;
        el.value = value;
        el.classList.add('is-autofilled');
        el.dispatchEvent(new Event('input', { bubbles: true }));
        const clear = () => el.classList.remove('is-autofilled');
        el.addEventListener('focus', clear, { once: true });
        el.addEventListener('change', clear, { once: true });
        return true;
    }

    function applyOcrData(data) {
        const result = { filled: 0, skipped: 0, available: 0 };
        if (!data) return result;
        const map = [
            ['fecha_emision',         'rg-fecha'],
            ['importe_comprobante',   'rg-importe'],
            ['ruc',                   'rg-ruc'],
            ['razon_social',          'rg-razon'],
            ['nro_comprobante',       'rg-nro-comprobante'],
            ['descripcion_concepto',  'rg-concepto'],
        ];
        map.forEach(([key, elId]) => {
            const v = data[key];
            if (v === null || v === undefined || v === '') return;
            result.available++;
            const el = document.getElementById(elId);
            if (!el) return;
            const current = (el.value || '').trim();
            if (current !== '') { result.skipped++; return; }
            if (setOcrAutofilled(elId, v)) result.filled++;
        });
        return result;
    }

    function formatFileSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function ocrIconForExt(ext) {
        if (ext === 'pdf') {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-2 16H8v-2h4v2zm4-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
        }
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>';
    }

    function openOcrPicker(files, onPick, onCancel) {
        const list      = document.getElementById('ocr-picker-list');
        const okBtn     = document.getElementById('btn-confirmar-ocr-picker');
        const closeBtn  = document.getElementById('btn-cerrar-modal-ocr-picker');
        const cancelBtn = document.getElementById('btn-cancelar-ocr-picker');
        if (!list || !okBtn) return;

        let selectedIdx = -1;
        let resolved    = false;

        list.innerHTML = files.map((f, i) => {
            const ext = (f.name.split('.').pop() || '').toLowerCase();
            return `<li class="ocr-picker-item" data-idx="${i}" role="option" tabindex="0" aria-selected="false">
                <span class="ocr-picker-item__icon">${ocrIconForExt(ext)}</span>
                <span class="ocr-picker-item__name" title="${escHtml(f.name)}">${escHtml(f.name)}</span>
                <span class="ocr-picker-item__size">${formatFileSize(f.size)}</span>
            </li>`;
        }).join('');

        okBtn.disabled = true;

        const handleSelect = (idx) => {
            selectedIdx = idx;
            list.querySelectorAll('.ocr-picker-item').forEach((el, i) => {
                const sel = i === idx;
                el.classList.toggle('is-selected', sel);
                el.setAttribute('aria-selected', sel ? 'true' : 'false');
            });
            okBtn.disabled = false;
        };

        list.querySelectorAll('.ocr-picker-item').forEach(el => {
            const idx = parseInt(el.dataset.idx, 10);
            el.addEventListener('click', () => handleSelect(idx));
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handleSelect(idx); }
            });
        });

        const overlay = document.getElementById('modal-ocr-picker');
        let observer  = null;

        const cleanup = () => {
            okBtn.removeEventListener('click', onConfirm);
            if (closeBtn)  closeBtn.removeEventListener('click', onCancelInternal);
            if (cancelBtn) cancelBtn.removeEventListener('click', onCancelInternal);
            if (observer)  observer.disconnect();
        };
        const onConfirm = () => {
            if (selectedIdx < 0 || resolved) return;
            resolved = true;
            const picked = files[selectedIdx];
            ModalManager.close('modal-ocr-picker');
            cleanup();
            if (typeof onPick === 'function') onPick(picked);
        };
        const onCancelInternal = () => {
            if (resolved) return;
            resolved = true;
            cleanup();
            if (typeof onCancel === 'function') onCancel();
        };
        okBtn.addEventListener('click', onConfirm);
        if (closeBtn)  closeBtn.addEventListener('click', onCancelInternal);
        if (cancelBtn) cancelBtn.addEventListener('click', onCancelInternal);

        // Cierre por overlay click o ESC: detectamos vía clase 'open'.
        if (overlay && typeof MutationObserver !== 'undefined') {
            observer = new MutationObserver(() => {
                if (!overlay.classList.contains('open') && !resolved) onCancelInternal();
            });
            observer.observe(overlay, { attributes: true, attributeFilter: ['class'] });
        }

        ModalManager.open('modal-ocr-picker');
    }

    /**
     * Devuelve una promesa que resuelve cuando el usuario pickea (o cancela) un archivo.
     * Si _adjFiles.length === 1 resuelve directo con ese archivo.
     */
    function selectFileForOcr() {
        return new Promise((resolve) => {
            if (_adjFiles.length === 0) { resolve(null); return; }
            if (_adjFiles.length === 1) { resolve(_adjFiles[0]); return; }
            openOcrPicker(_adjFiles.slice(), (picked) => resolve(picked), () => resolve(null));
        });
    }

    /**
     * Corre OCR sobre el archivo seleccionado mostrando el loader.
     * Siempre resuelve (success o failure) para que el wizard pueda continuar.
     */
    async function runOcrOnFile(file) {
        if (_ocrInFlight || !file) return { ok: false };

        const ext = (file.name.split('.').pop() || '').toLowerCase();
        if (OCR_ALLOWED_EXTS.indexOf(ext) === -1) {
            showOcrBanner('error', 'Formato no soportado para OCR. Usa PDF, JPG, PNG o HEIC.');
            return { ok: false };
        }
        if (file.size > (OCR_CONFIG.max_file_bytes || 10 * 1024 * 1024)) {
            showOcrBanner('error', 'Archivo supera 10 MB. Llena los datos manualmente.');
            return { ok: false };
        }

        const tipo = getRendicionTipo();
        const fd = new FormData();
        fd.append('archivo', file, file.name);
        fd.append('tipo', tipo);

        _ocrInFlight = true;
        showOcrLoader();

        const ctrl = new AbortController();
        const timeoutMs = OCR_CONFIG.timeout_ms || 35000;
        const timer = setTimeout(() => ctrl.abort(), timeoutMs);

        try {
            const url = CONFIG.apiBase.replace(/\/$/, '') + '/ocr-extract';
            const resp = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': CONFIG.nonce },
                body: fd,
                signal: ctrl.signal,
            });
            const json = await resp.json().catch(() => ({}));

            if (!resp.ok || !json.success) {
                let msg = json.message || `Error ${resp.status}`;
                if (resp.status === 503) msg = 'OCR no está habilitado.';
                if (resp.status === 429) msg = 'Se alcanzó el límite de OCR.';
                if (resp.status === 502) msg = 'El proveedor OCR rechazó la solicitud.';
                if (resp.status === 422) msg = msg || 'No se pudo procesar el archivo.';
                showOcrBanner('error', `${msg} Llena los datos manualmente.`);
                return { ok: false };
            }

            const data    = json.data || {};
            const result  = applyOcrData(data);
            const conf    = typeof data.confianza === 'number' ? data.confianza : null;
            const confPct = conf !== null ? Math.round(conf * 100) : null;
            const isLow   = conf !== null && conf < OCR_CONFIANZA_BAJA;

            if (result.filled === 0 && result.skipped === 0) {
                showOcrBanner('warning', 'No se pudieron extraer datos del documento. Complétalos manualmente.');
                return { ok: true, empty: true };
            }

            const partes = [];
            if (result.filled > 0)  partes.push(`${result.filled} pre-llenado${result.filled === 1 ? '' : 's'}`);
            if (result.skipped > 0) partes.push(`${result.skipped} respetado${result.skipped === 1 ? '' : 's'}`);
            const confMsg = confPct !== null ? ` · confianza ${confPct}%` : '';

            if (isLow) {
                showOcrBanner('warning', `${partes.join(', ')}${confMsg}. Confianza baja, revisa con atención.`);
            } else {
                showOcrBanner('success', `${partes.join(', ')}${confMsg}. Revísalos antes de guardar.`);
            }
            return { ok: true };
        } catch (err) {
            const isAbort = err && err.name === 'AbortError';
            const msg = isAbort
                ? `El OCR tardó demasiado (más de ${Math.round(timeoutMs / 1000)}s).`
                : 'No se pudo conectar al OCR. Verifica tu conexión.';
            showOcrBanner('error', `${msg} Llena los datos manualmente.`);
            return { ok: false };
        } finally {
            clearTimeout(timer);
            hideOcrLoader();
            _ocrInFlight = false;
        }
    }

    /**
     * Orquesta "Siguiente" del paso 1: valida, corre OCR si aplica, y avanza.
     */
    async function handleWizardNext() {
        if (_ocrInFlight) return;
        if (!validateStep1()) return;
        if (shouldRunOcrOnNext()) {
            const file = await selectFileForOcr();
            if (!file) return; // user canceló picker
            const nextBtn = document.getElementById('btn-wizard-next');
            if (nextBtn) setButtonLoading(nextBtn, true);
            try {
                await runOcrOnFile(file);
            } finally {
                if (nextBtn) setButtonLoading(nextBtn, false);
            }
        }
        goToStep(2);
    }

    function validateStep1() {
        const catEl  = document.getElementById('rg-categoria');
        const catErr = document.getElementById('err-rg-categoria');
        if (!validateField(catEl, catErr)) return false;
        const tipo = getRendicionTipo();
        const needsPdf = tipo && tipo !== 'movilidad' && tipo !== 'vale_caja';
        if (needsPdf && _adjFiles.length === 0) {
            showToast('error', 'Comprobante requerido', 'Este tipo de gasto necesita al menos un archivo adjunto.');
            const dz = document.getElementById('rg-dropzone');
            if (dz) {
                dz.classList.add('has-error');
                setTimeout(() => dz.classList.remove('has-error'), 1800);
            }
            return false;
        }
        return true;
    }

    function renderWizardSummary() {
        const catId = parseInt(document.getElementById('rg-categoria').value, 10);
        const cat   = (window.ViaticosCategoriasGasto || []).find(c => c.id === catId);
        const n     = _adjFiles.length;
        document.getElementById('wz-summary-cat').textContent   = cat ? cat.nombre : '—';
        document.getElementById('wz-summary-clase').textContent = cat ? (cat.clase_doc || '—') : '—';
        document.getElementById('wz-summary-files').textContent = n === 0 ? '' : ` · ${n} archivo${n === 1 ? '' : 's'}`;
    }

    function _applyStepToDom(n) {
        _wizardStep = n;
        const modal = document.querySelector('#modal-rendir-gasto .modal-wizard');
        if (!modal) return;
        modal.dataset.wizardStep = String(n);
        const stepper = modal.querySelector('.wizard-topbar');
        if (stepper) stepper.dataset.current = String(n);
        modal.querySelectorAll('.wizard-step').forEach(el => {
            const s = parseInt(el.dataset.step, 10);
            el.classList.toggle('is-active', s === n);
            el.classList.toggle('is-complete', s < n);
            el.disabled = s > n;
        });
        modal.querySelectorAll('.wizard-panel').forEach(p => {
            p.classList.toggle('is-active', parseInt(p.dataset.step, 10) === n);
        });
    }

    function goToStep(n) {
        if (n === _wizardStep) return;
        if (n === 2 && !validateStep1()) return;
        _applyStepToDom(n);
        if (n === 2) renderWizardSummary();
        setTimeout(() => {
            const panel = document.querySelector(`#modal-rendir-gasto .wizard-panel[data-step="${n}"].is-active`);
            if (!panel) return;
            const first = panel.querySelector('select, input:not([type="hidden"]):not([hidden]), textarea');
            if (first && typeof first.focus === 'function') first.focus();
        }, 220);
    }

    function _forceGoToStep(n) { _applyStepToDom(n); }

    async function handleRendirGastoSubmit(e) {
        e.preventDefault();
        const form   = document.getElementById('form-rendir-gasto');
        const btn    = document.getElementById('btn-submit-rendir-gasto');
        const schema = getActiveSchema();

        let isValid = true;
        isValid &= validateField(document.getElementById('rg-categoria'), document.getElementById('err-rg-categoria'));
        if (schema) {
            const rucValidator = (v) => /^\d{11}$/.test(v);
            schema.required.forEach(id => {
                isValid &= validateField(
                    document.getElementById(id),
                    document.getElementById('err-' + id),
                    id === 'rg-ruc' ? rucValidator : null
                );
            });
        }

        if (!isValid) {
            const invalids  = form.querySelectorAll('.form-control.is-invalid');
            const errCount  = invalids.length;
            const firstStep = invalids[0] ? getWizardStepOf(invalids[0]) : null;

            if (firstStep && firstStep !== _wizardStep) {
                goToStep(firstStep);
                setTimeout(() => Forms.focusFirstInvalid(form), 280);
            } else {
                Forms.focusFirstInvalid(form);
            }

            const msg = errCount > 1
                ? `Completa los ${errCount} campos marcados antes de guardar.`
                : 'Completa el campo marcado antes de guardar.';
            showToast('error', 'Faltan datos', msg);
            return;
        }

        const tipo = getRendicionTipo();
        const requierePdf = tipo && tipo !== 'movilidad' && tipo !== 'vale_caja';
        if (requierePdf && _adjFiles.length === 0) {
            if (_wizardStep !== 1) goToStep(1);
            const dz = document.getElementById('rg-dropzone');
            if (dz) {
                dz.classList.add('has-error');
                setTimeout(() => dz.classList.remove('has-error'), 2200);
                setTimeout(() => dz.scrollIntoView({ behavior: 'smooth', block: 'center' }), 280);
            }
            showToast('error', 'Comprobante requerido', 'Adjunta al menos un archivo para este tipo de gasto.');
            return;
        }

        setButtonLoading(btn, true);
        try {
            const base = {
                id_solicitud: parseInt(document.getElementById('rg-id-solicitud').value, 10),
                fecha:        document.getElementById('rg-fecha').value,
                importe:      parseFloat(document.getElementById('rg-importe').value),
            };
            const payload = schema ? schema.buildPayload(base) : base;

            const res = await apiFetch('/nuevo-gasto', { method:'POST', body: JSON.stringify(payload) });
            const newGastoId = res && res.id ? parseInt(res.id, 10) : 0;

            let fallidos = 0;
            if (newGastoId && _adjFiles.length > 0) {
                for (const file of _adjFiles) {
                    const fd = new FormData();
                    fd.append('id_gasto', String(newGastoId));
                    fd.append('archivo',  file);
                    try { await apiFetchForm('/gasto-adjunto', fd); }
                    catch (e) { fallidos++; }
                }
            }

            ModalManager.close('modal-rendir-gasto');
            await refreshGastosCache();
            renderRendicionesResumen(gastosCache);

            const sol = getSolicitudById(payload.id_solicitud);
            if (sol) renderDetalleSolicitudContent(sol, getGastosBySolicitud(payload.id_solicitud));

            if (fallidos > 0) {
                showToast('warning', 'Gasto registrado', `${fallidos} adjunto(s) fallaron al subir.`);
            } else {
                showToast('success', 'Gasto registrado');
            }
        } catch (err) {
            const msg = err && err.message ? String(err.message) : 'Error inesperado';
            const handled = Forms.handleServerError(form, msg);
            if (handled.handled) {
                const firstInvalid = form.querySelector('.form-control.is-invalid');
                const stepOf = firstInvalid ? getWizardStepOf(firstInvalid) : null;
                if (stepOf && stepOf !== _wizardStep) {
                    goToStep(stepOf);
                    setTimeout(() => Forms.focusFirstInvalid(form), 280);
                }
                showToast('error', 'Datos incompletos', `Faltan campos: ${handled.fields.join(', ')}.`);
            } else {
                showApiError(msg);
            }
        } finally { setButtonLoading(btn, false); }
    }

    function renderRendicionesResumen(data) {
        const container = document.getElementById('rendiciones-list-container');
        if (!container) return;
        const grouped = (data || []).reduce((acc, g) => { (acc[g.id_solicitud] = acc[g.id_solicitud] || []).push(g); return acc; }, {});
        container.innerHTML = Object.keys(grouped).map(id => {
            const total = grouped[id].reduce((sum, g) => sum + parseFloat(g.importe || 0), 0);
            return `<div class="rd-card">Solicitud #${id} · Total: ${formatMonto(total)}</div>`;
        }).join('');
    }

    async function loadRendicionesView() {
        try { await refreshGastosCache(); renderRendicionesResumen(gastosCache); }
        catch (err) { showApiError(err); }
    }

    function bindEvents() {
        document.querySelectorAll('.nav-link[data-view]').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                await navigateTo(link.dataset.view);
            });
        });

        function abrirNuevaSolicitud() {
            const form = document.getElementById('form-nueva-solicitud');
            if (form) {
                form.reset();
                Forms.clearFormErrors(form);
            }
            prefillNuevaSolicitudForm();
            ModalManager.open('modal-nueva-solicitud');
        }

        const btnAbrirNueva = document.getElementById('btn-abrir-nueva-solicitud');
        if (btnAbrirNueva) btnAbrirNueva.addEventListener('click', abrirNuevaSolicitud);
        const btnInicioNueva = document.getElementById('btn-inicio-nueva');
        if (btnInicioNueva) btnInicioNueva.addEventListener('click', abrirNuevaSolicitud);

        async function goToSolicitudesFiltered(filter) {
            await navigateTo({ name: 'solicitudes' });
            const chip = document.querySelector('#chips-solicitudes .tbl-chip[data-filter="' + (filter || '') + '"]');
            if (chip && !chip.classList.contains('is-active')) chip.click();
        }

        async function handleMetricClick(kind) {
            if (kind === 'por-rendir' || kind === 'en-revision') {
                const bandejaEl = document.getElementById('inicio-bandeja');
                if (bandejaEl) bandejaEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (kind === 'observadas') {
                const observadas = solicitudesCache.filter(s => {
                    const estadoSol  = getSolicitudEstado(s);
                    const estadoRend = getRendicionEstado(s, { gastos: getGastosBySolicitud(s.id) });
                    return estadoSol === 'observada' || estadoRend === 'observada';
                });
                if (observadas.length === 1) {
                    await navigateTo({ name: 'solicitud', id: observadas[0].id, from: 'inicio' });
                    return;
                }
                if (observadas.length > 1) {
                    await goToSolicitudesFiltered('observada');
                    return;
                }
                return;
            }
            if (kind === 'total') {
                await goToSolicitudesFiltered('');
            }
        }

        document.querySelectorAll('.inicio-metric[data-metric]').forEach(btn => {
            btn.addEventListener('click', () => handleMetricClick(btn.dataset.metric));
        });
        const btnVerTodas = document.getElementById('inicio-ver-todas');
        if (btnVerTodas) btnVerTodas.addEventListener('click', () => navigateTo({ name: 'solicitudes' }));

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

        ['btn-cerrar-colab-liq', 'btn-cancelar-colab-liq'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', () => ModalManager.close('modal-colab-liquidacion'));
        });
        document.getElementById('btn-imprimir-colab-liq').addEventListener('click', () => window.ViaticosLiquidacion.print('colab-liq-container'));
        document.getElementById('btn-excel-colab-liq').addEventListener('click', async function () {
            if (!currentLiqData) return;
            const btn = this;
            setButtonLoading(btn, true);
            try {
                await window.ViaticosLiquidacion.exportXlsx(currentLiqData, undefined, CONFIG.logoUrl);
            } catch (err) {
                showApiError(err.message ? err : 'No se pudo generar el archivo.', 'Exportación fallida');
            } finally {
                setButtonLoading(btn, false);
            }
        });
        ModalManager.closeOnOverlayClick('modal-colab-liquidacion');

        // Modal confirmar finalizar rendición
        document.getElementById('btn-cerrar-modal-confirmar').addEventListener('click', () => ModalManager.close('modal-confirmar-finalizar'));
        document.getElementById('btn-cancelar-confirmar').addEventListener('click',     () => ModalManager.close('modal-confirmar-finalizar'));
        document.getElementById('btn-confirmar-finalizar').addEventListener('click', async () => {
            ModalManager.close('modal-confirmar-finalizar');
            await handleFinalizarRendicion();
        });
        ModalManager.closeOnOverlayClick('modal-confirmar-finalizar');

        // Modal rendir gasto (wizard 2 pasos)
        bindDropzone();

        // Modal OCR picker (selector de archivo cuando hay >1 adjunto)
        const ocrPickerCloseBtn = document.getElementById('btn-cerrar-modal-ocr-picker');
        const ocrPickerCancelBtn = document.getElementById('btn-cancelar-ocr-picker');
        if (ocrPickerCloseBtn) ocrPickerCloseBtn.addEventListener('click', () => ModalManager.close('modal-ocr-picker'));
        if (ocrPickerCancelBtn) ocrPickerCancelBtn.addEventListener('click', () => ModalManager.close('modal-ocr-picker'));
        ModalManager.closeOnOverlayClick('modal-ocr-picker');

        // Banner OCR (paso 2)
        const ocrBannerCloseBtn = document.getElementById('rg-ocr-banner-close');
        if (ocrBannerCloseBtn) ocrBannerCloseBtn.addEventListener('click', hideOcrBanner);
        const closeRendirIfAllowed = () => { if (!_ocrInFlight) ModalManager.close('modal-rendir-gasto'); };
        document.getElementById('btn-cerrar-modal-rendir').addEventListener('click',   closeRendirIfAllowed);
        document.getElementById('btn-cancelar-modal-rendir').addEventListener('click', closeRendirIfAllowed);
        document.getElementById('form-rendir-gasto').addEventListener('submit', handleRendirGastoSubmit);
        document.getElementById('btn-wizard-next').addEventListener('click', handleWizardNext);
        document.getElementById('btn-wizard-back').addEventListener('click', () => goToStep(1));
        document.getElementById('wz-summary-back').addEventListener('click', () => goToStep(1));
        document.querySelectorAll('#modal-rendir-gasto .wizard-step').forEach(el => {
            el.addEventListener('click', () => {
                const s = parseInt(el.dataset.step, 10);
                if (s < _wizardStep) goToStep(s);
            });
        });
        ModalManager.closeOnOverlayClick('modal-rendir-gasto');

        document.getElementById('btn-cerrar-modal-historial').addEventListener('click',  () => ModalManager.close('modal-historial-solicitud'));
        document.getElementById('btn-cancelar-modal-historial').addEventListener('click', () => ModalManager.close('modal-historial-solicitud'));
        ModalManager.closeOnOverlayClick('modal-historial-solicitud');

        document.getElementById('btn-refrescar-solicitudes').addEventListener('click', () => {
            solTray.setPage(1);
            loadSolicitudesView();
        });

        solTray.initInteractions(() => solicitudesCache);
    }

    /* ── Init ─────────────────────────────────────────────── */
    async function init() { bindEvents(); await router.init(); }
    window.ViaticosApp = { navigate: navigateTo };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

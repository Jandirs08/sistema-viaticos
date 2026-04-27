window.ViaticosDetalleUI = (function () {
    'use strict';

    const gastoUI = window.ViaticosGastoUI;
    const estadoUI = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const utils = window.ViaticosUtils;
    const esc = utils.escapeHtml;
    const fmtMonto = utils.fmtMonto;
    const fmtFecha = utils.fmtFecha;

    function buildStepperHtml(sol, estadoSolicitud, estadoRend) {
        const s2 = estadoSolicitud === 'aprobada' ? 'is-done' : (['observada', 'rechazada'].includes(estadoSolicitud) ? 'is-warning' : 'is-current');
        const s3 = sol.rendicion_finalizada || ['aprobada', 'rechazada', 'observada'].includes(estadoRend) ? 'is-done' : (estadoSolicitud === 'aprobada' ? 'is-current' : '');
        const s4 = estadoRend === 'aprobada' ? 'is-done' : (['rechazada', 'observada'].includes(estadoRend) ? 'is-warning' : (sol.rendicion_finalizada ? 'is-current' : ''));
        const steps = [
            { n: 1, label: 'Solicitud', state: 'is-done' },
            { n: 2, label: 'Aprobación', state: s2 },
            { n: 3, label: 'Gastos', state: s3 },
            { n: 4, label: 'Revisión', state: s4 },
        ];
        return steps.map(function (s) {
            return '<div class="solv-step ' + s.state + '"><span class="solv-step-dot">' + s.n + '</span><span class="solv-step-label">' + s.label + '</span></div>';
        }).join('');
    }

    function getUltimoComentario(historial, eventoFiltro) {
        if (!Array.isArray(historial)) return '';
        for (var i = historial.length - 1; i >= 0; i--) {
            var item = historial[i];
            if (item && item.comentario && (!eventoFiltro || item.evento === eventoFiltro)) {
                return String(item.comentario);
            }
        }
        return '';
    }

    function buildBannerHtml(estadoSolicitud, estadoRend, historial) {
        var comentario, extra;
        if (estadoRend === 'rechazada') {
            comentario = getUltimoComentario(historial, 'rendicion_rechazada');
            extra = comentario ? '<p class="solv-banner-comment">' + esc(comentario) + '</p>' : '';
            return '<div class="solv-banner is-danger"><strong>Rendición rechazada.</strong> Revisa el historial.' + extra + '</div>';
        }
        if (estadoRend === 'observada') {
            comentario = getUltimoComentario(historial, 'rendicion_observada');
            extra = comentario ? '<p class="solv-banner-comment">' + esc(comentario) + '</p>' : '';
            return '<div class="solv-banner is-warn"><strong>Rendición observada.</strong> Corrige y reenvía.' + extra + '</div>';
        }
        if (estadoRend === 'aprobada') return '<div class="solv-banner is-ok"><strong>Rendición aprobada.</strong></div>';
        if (estadoSolicitud === 'rechazada') {
            comentario = getUltimoComentario(historial, 'solicitud_rechazada');
            extra = comentario ? '<p class="solv-banner-comment">' + esc(comentario) + '</p>' : '';
            return '<div class="solv-banner is-danger"><strong>Solicitud rechazada.</strong>' + extra + '</div>';
        }
        if (estadoSolicitud === 'observada') {
            comentario = getUltimoComentario(historial, 'solicitud_observada');
            extra = comentario ? '<p class="solv-banner-comment">' + esc(comentario) + '</p>' : '';
            return '<div class="solv-banner is-warn"><strong>Solicitud observada.</strong> Corrige y reenvía.' + extra + '</div>';
        }
        return '';
    }

    /**
     * render(containerEl, sol, gastos, opts)
     *   opts.apiFetch      fn   – API fetch (required for accordion lazy-load)
     *   opts.canDelete     bool – adjuntos deletable (colaborador only)
     *   opts.accionesHtml  str  – HTML injected into header actions slot
     * Returns { historialHtml, estadoRend, estadoSolicitud }
     */
    function render(containerEl, sol, gastos, opts) {
        opts = opts || {};
        const apiFetch = opts.apiFetch;
        const canDelete = !!opts.canDelete;
        const canDeleteGasto = !!opts.canDeleteGasto;
        const onDeleteGasto = typeof opts.onDeleteGasto === 'function' ? opts.onDeleteGasto : null;
        const accionesHtml = opts.accionesHtml || '';

        const totalSolicitado = parseFloat(sol.monto) || 0;
        const totalRendido = gastos.reduce(function (s, g) { return s + (parseFloat(g.importe) || 0); }, 0);
        const saldo = totalSolicitado - totalRendido;
        const saldoNegativo = saldo < 0;
        const estadoSolicitud = estadoUI.getSolicitudEstado(sol);
        const estadoRend = estadoUI.resolveEstadoRendicion({
            estadoSolicitud: sol.estado,
            estadoRendicion: sol.estado_rendicion,
            rendicionFinalizada: sol.rendicion_finalizada,
            gastos: gastos,
        });
        const historial = Array.isArray(sol.historial) ? sol.historial : [];
        const historialHtml = timelineUI.renderTimeline(historial);
        const accId = 'solv-gastos-acc-' + sol.id;

        const gastosBodyHtml = gastos.length
            ? (function () {
                var chevron = '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>';
                var rows = gastos.map(function (g, i) {
                    var itemId = 'solv-' + sol.id + '-' + i;
                    var tipoLabel = gastoUI.TIPO_LABEL[g.tipo] || g.tipo || 'Gasto';
                    var summary = gastoUI.summaryText(g);
                    var fields = gastoUI.buildFields(g);
                    var gastoIdAttr = g.id ? String(g.id) : '';
                    var deleteBtn = (canDeleteGasto && gastoIdAttr)
                        ? '<button type="button" class="btn-gasto-eliminar" data-delete-gasto-id="' + gastoIdAttr + '"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>Eliminar gasto</button>'
                        : '';
                    var adjuntosHtml = gastoIdAttr
                        ? '<div class="gasto-adj-panel" data-adj-gasto-id="' + gastoIdAttr + '"><div class="gasto-adj-title"><span style="display:flex;align-items:center;gap:6px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="color:#4A5568;"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V6H11v9.5a2.5 2.5 0 005 0V5c0-2.21-1.79-4-4-4S8 2.79 8 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-2.5z"/></svg>Adjuntos</span></div><div class="gasto-adj-list"><span class="gasto-adj-loading">Cargando adjuntos…</span></div></div>'
                        : '';
                    return (
                        '<tr class="solv-gtbl-row" data-acc-id="' + itemId + '" data-gasto-id="' + gastoIdAttr + '" tabindex="0" role="button" aria-expanded="false">' +
                        '<td class="solv-gtbl-chev">' + chevron + '</td>' +
                        '<td><span class="solv-gtbl-tipo">' + gastoUI.escHtml(tipoLabel) + '</span></td>' +
                        '<td class="solv-gtbl-concept">' + gastoUI.escHtml(summary) + '</td>' +
                        '<td class="solv-gtbl-fecha">' + gastoUI.escHtml(gastoUI.fmtFecha(g.fecha)) + '</td>' +
                        '<td class="solv-gtbl-amount">' + gastoUI.escHtml(gastoUI.fmtMonto(g.importe)) + '</td>' +
                        '</tr>' +
                        '<tr class="solv-gtbl-detail" data-detail-for="' + itemId + '" hidden>' +
                        '<td colspan="5">' +
                        '<div class="solv-gtbl-fields">' + fields + '</div>' +
                        adjuntosHtml +
                        deleteBtn +
                        '</td>' +
                        '</tr>'
                    );
                }).join('');
                return (
                    '<table class="solv-gtbl" id="' + accId + '">' +
                    '<thead><tr>' +
                    '<th style="width:36px;"></th>' +
                    '<th style="width:120px;">Tipo</th>' +
                    '<th>Concepto</th>' +
                    '<th style="width:110px;">Fecha</th>' +
                    '<th style="width:140px;" class="num">Importe</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                    '</table>'
                );
            })()
            : '<div class="solv-empty"><p>Sin gastos registrados.</p></div>';

        const stepperHtml = buildStepperHtml(sol, estadoSolicitud, estadoRend);
        const bannerHtml = buildBannerHtml(estadoSolicitud, estadoRend, historial);

        containerEl.innerHTML =
            '<div class="solv-shell">' +
            '<header class="solv-exp-head">' +
            '<div class="solv-exp-id">' +
            '<span class="solv-exp-num">#' + sol.id + '</span>' +
            '<span class="solv-exp-sep">·</span>' +
            '<span class="solv-exp-date">' + esc(fmtFecha(sol.fecha)) + '</span>' +
            '</div>' +
            '<nav class="solv-exp-stepper" aria-label="Flujo">' + stepperHtml + '</nav>' +
            '<div class="solv-exp-actions">' + accionesHtml + '</div>' +
            '</header>' +
            bannerHtml +
            '<div class="solv-fin">' +
            '<div class="solv-fin-cell is-anticipo"><span class="solv-fin-k">Anticipo</span><strong class="solv-fin-v">' + fmtMonto(totalSolicitado) + '</strong></div>' +
            '<div class="solv-fin-cell is-rendido"><span class="solv-fin-k">Rendido</span><strong class="solv-fin-v is-ok">' + fmtMonto(totalRendido) + '</strong></div>' +
            '<div class="solv-fin-cell' + (saldoNegativo ? ' is-warn' : ' is-saldo') + '"><span class="solv-fin-k">Saldo</span><strong class="solv-fin-v' + (saldoNegativo ? ' is-warn' : '') + '">' + fmtMonto(saldo) + '</strong></div>' +
            '</div>' +
            '<section class="solv-section">' +
            '<header class="solv-section-head"><h2 class="solv-section-title">Expediente</h2></header>' +
            '<table class="solv-meta">' +
            '<tbody>' +
            '<tr><th scope="row">Fecha viaje</th><td>' + esc(fmtFecha(sol.fecha)) + '</td></tr>' +
            '<tr><th scope="row">CECO</th><td>' + esc(sol.ceco || '—') + '</td></tr>' +
            '<tr><th scope="row">DNI</th><td>' + esc(sol.dni || '—') + '</td></tr>' +
            '<tr><th scope="row">Motivo</th><td>' + esc(sol.motivo || '—') + '</td></tr>' +
            '</tbody>' +
            '</table>' +
            '</section>' +
            '<section class="solv-section">' +
            '<header class="solv-section-head">' +
            '<h2 class="solv-section-title">Gastos</h2>' +
            '<span class="solv-section-count">' + gastos.length + '</span>' +
            '</header>' +
            '<div class="solv-section-body">' + gastosBodyHtml + '</div>' +
            '</section>' +
            '</div>';

        const tableEl = containerEl.querySelector('#' + accId);
        if (tableEl && apiFetch) {
            tableEl.addEventListener('click', function (e) {
                var delBtn = e.target.closest('.btn-gasto-eliminar');
                if (delBtn && onDeleteGasto) {
                    e.stopPropagation();
                    if (confirm('¿Eliminar este gasto? Esta acción no se puede deshacer.')) {
                        onDeleteGasto(parseInt(delBtn.dataset.deleteGastoId, 10));
                    }
                    return;
                }
                var row = e.target.closest('.solv-gtbl-row');
                if (!row || !tableEl.contains(row)) return;
                var id = row.dataset.accId;
                var gastoId = row.dataset.gastoId || null;
                var detailRow = tableEl.querySelector('.solv-gtbl-detail[data-detail-for="' + id + '"]');
                var wasOpen = row.classList.contains('is-open');
                tableEl.querySelectorAll('.solv-gtbl-row.is-open').forEach(function (r) {
                    r.classList.remove('is-open');
                    r.setAttribute('aria-expanded', 'false');
                });
                tableEl.querySelectorAll('.solv-gtbl-detail').forEach(function (d) { d.hidden = true; });
                if (!wasOpen) {
                    row.classList.add('is-open');
                    row.setAttribute('aria-expanded', 'true');
                    if (detailRow) {
                        detailRow.hidden = false;
                        if (gastoId) {
                            gastoUI.loadAdjuntos(gastoId, detailRow, { apiFetch: apiFetch, canDelete: canDelete });
                        }
                    }
                }
            });
            tableEl.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var row = e.target.closest('.solv-gtbl-row');
                if (row) { e.preventDefault(); row.click(); }
            });
        }

        return { historialHtml: historialHtml, estadoRend: estadoRend, estadoSolicitud: estadoSolicitud };
    }

    return { render: render };
})();

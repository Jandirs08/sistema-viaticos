window.ViaticosEstadoUI = (function () {
    const FALLBACK_LABELS = {
        solicitud: {
            pendiente: 'Anticipo Pendiente',
            aprobada: 'Anticipo Aprobado',
            observada: 'Anticipo Observado',
            rechazada: 'Anticipo Rechazado',
        },
        rendicion: {
            no_disponible: 'No disponible',
            no_iniciada: 'Por Rendir',
            en_proceso: 'Rindiendo',
            en_revision: 'Rendición en Revisión',
            aprobada: 'Rendición Aprobada',
            observada: 'Rendición Observada',
            rechazada: 'Rendición Rechazada',
        },
    };

    const cfg = (window.ViaticosConfigData && window.ViaticosConfigData.estados) || {};
    const labels = {
        solicitud: Object.assign({}, FALLBACK_LABELS.solicitud, cfg.solicitud || {}),
        rendicion: Object.assign({}, FALLBACK_LABELS.rendicion, cfg.rendicion || {}),
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function isTruthy(value) {
        return value === true || value === 1 || value === '1';
    }

    function resolveEstadoSolicitud(estado) {
        const raw = String(estado || '').toLowerCase();
        if (raw === 'rendida') return 'aprobada';
        return labels.solicitud[raw] ? raw : 'pendiente';
    }

    function resolveEstadoRendicion(options = {}) {
        const estadoSolicitud = resolveEstadoSolicitud(options.estadoSolicitud || options.estado);
        const estadoRendicion = String(options.estadoRendicion || '').toLowerCase();
        const rendicionFinalizada = isTruthy(options.rendicionFinalizada);
        const tieneGastos = Array.isArray(options.gastos)
            ? options.gastos.length > 0
            : !!options.tieneGastos || Number(options.cantidadGastos || 0) > 0 || Number(options.totalRendido || 0) > 0;

        if (estadoSolicitud !== 'aprobada') {
            return 'no_disponible';
        }

        if (!rendicionFinalizada) {
            return tieneGastos ? 'en_proceso' : 'no_iniciada';
        }

        if (estadoRendicion === 'aprobada' || estadoRendicion === 'observada' || estadoRendicion === 'rechazada') {
            return estadoRendicion;
        }

        return 'en_revision';
    }

    function renderBadgeEstado(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const allowed = labels[normalizedTipo];
        const fallback = normalizedTipo === 'rendicion' ? 'no_disponible' : 'pendiente';
        const key = allowed[String(estado || '').toLowerCase()] ? String(estado || '').toLowerCase() : fallback;
        return `<span class="badge badge-${normalizedTipo}-${key}">${escapeHtml(allowed[key])}</span>`;
    }

    function getLabelEstado(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const allowed = labels[normalizedTipo];
        const fallback = normalizedTipo === 'rendicion' ? 'no_disponible' : 'pendiente';
        const key = allowed[String(estado || '').toLowerCase()] ? String(estado || '').toLowerCase() : fallback;
        return allowed[key];
    }

    function renderEstadoGrupo(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const title = normalizedTipo === 'rendicion' ? 'Rendición' : 'Solicitud';
        return `
    <div class="estado-group estado-group-${normalizedTipo}">
        <div class="estado-group-label">${title}</div>
        <div>${renderBadgeEstado(normalizedTipo, estado)}</div>
    </div>
`;
    }

    function getSolicitudEstado(sol) {
        return resolveEstadoSolicitud(sol && sol.estado);
    }

    function renderSolicitudBadge(sol) {
        return renderBadgeEstado('solicitud', getSolicitudEstado(sol));
    }

    function renderNarrativeBadge(sol, extra) {
        const estadoSolicitud = resolveEstadoSolicitud(sol && sol.estado);
        if (estadoSolicitud !== 'aprobada') {
            return renderBadgeEstado('solicitud', estadoSolicitud);
        }
        const opts = Object.assign({
            estadoSolicitud: sol.estado,
            estadoRendicion: sol.estado_rendicion,
            rendicionFinalizada: sol.rendicion_finalizada,
        }, extra || {});
        return renderBadgeEstado('rendicion', resolveEstadoRendicion(opts));
    }

    function getRendicionEstado(sol, extra) {
        return resolveEstadoRendicion(Object.assign({
            estadoSolicitud:     sol && sol.estado,
            estadoRendicion:     sol && sol.estado_rendicion,
            rendicionFinalizada: sol && sol.rendicion_finalizada,
            tieneGastos:         !!(sol && sol.tiene_gastos),
        }, extra || {}));
    }

    function renderRendicionBadge(sol, extra) {
        return renderBadgeEstado('rendicion', getRendicionEstado(sol, extra));
    }

    return {
        resolveEstadoSolicitud,
        resolveEstadoRendicion,
        getLabelEstado,
        renderBadgeEstado,
        renderEstadoGrupo,
        getSolicitudEstado,
        renderSolicitudBadge,
        renderNarrativeBadge,
        getRendicionEstado,
        renderRendicionBadge,
    };
})();
window.resolveEstadoSolicitud = window.ViaticosEstadoUI.resolveEstadoSolicitud;
window.resolveEstadoRendicion = window.ViaticosEstadoUI.resolveEstadoRendicion;
window.getLabelEstado = window.ViaticosEstadoUI.getLabelEstado;
window.renderBadgeEstado = window.ViaticosEstadoUI.renderBadgeEstado;
window.renderEstadoGrupo = window.ViaticosEstadoUI.renderEstadoGrupo;

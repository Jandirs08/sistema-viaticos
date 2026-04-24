window.ViaticosTimelineUI = (function () {
    'use strict';

    const labels = {
        solicitud_creada: 'Solicitud creada',
        solicitud_aprobada: 'Solicitud aprobada',
        solicitud_observada: 'Solicitud observada',
        solicitud_rechazada: 'Solicitud rechazada',
        rendicion_iniciada: 'Rendición iniciada',
        rendicion_finalizada: 'Rendición finalizada',
        rendicion_aprobada: 'Rendición aprobada',
        rendicion_observada: 'Rendición observada',
        rendicion_rechazada: 'Rendición rechazada',
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getLabel(evento) {
        const key = String(evento || '').toLowerCase();
        return labels[key] || 'Evento registrado';
    }

    function formatDateTime(timestamp) {
        const value = Number(timestamp || 0);
        if (!value) return 'Sin fecha';

        const date = new Date(value * 1000);
        if (Number.isNaN(date.getTime())) return 'Sin fecha';

        return new Intl.DateTimeFormat('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function renderTimeline(historial) {
        const items = Array.isArray(historial) ? [...historial] : [];

        items.sort((a, b) => Number(a && a.fecha || 0) - Number(b && b.fecha || 0));

        if (!items.length) {
            return '<div class="timeline-empty">No hay eventos registrados todavía.</div>';
        }

        return `<div class="timeline-list">${items.map(item => {
            const usuario = item && item.usuario_nombre
                ? `Por ${escapeHtml(item.usuario_nombre)}`
                : item && item.usuario_id
                    ? `Usuario #${escapeHtml(item.usuario_id)}`
                    : '';
            const meta = [formatDateTime(item && item.fecha), usuario].filter(Boolean).join(' · ');

            return `
        <div class="timeline-item">
            <div class="timeline-marker"><span class="timeline-dot"></span></div>
            <div class="timeline-content">
                <div class="timeline-title">${escapeHtml(getLabel(item && item.evento))}</div>
                <div class="timeline-meta">${meta}</div>
            </div>
        </div>
    `;
        }).join('')}</div>`;
    }

    return {
        getLabel,
        formatDateTime,
        renderTimeline,
    };
})();

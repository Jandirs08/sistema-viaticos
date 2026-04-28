window.ViaticosTimelineUI = (function () {
    'use strict';

    const labels = {
        solicitud_creada: 'Solicitud creada',
        solicitud_aprobada: 'Solicitud aprobada',
        solicitud_observada: 'Solicitud observada',
        solicitud_rechazada: 'Solicitud rechazada',
        solicitud_reenviada: 'Solicitud reenviada a revisión',
        rendicion_iniciada: 'Rendición iniciada',
        rendicion_finalizada: 'Rendición finalizada',
        rendicion_aprobada: 'Rendición aprobada',
        rendicion_observada: 'Rendición observada',
        rendicion_rechazada: 'Rendición rechazada',
        rendicion_reenviada: 'Rendición reenviada a revisión',
    };

    const ADMIN_EVENTS = new Set([
        'solicitud_aprobada',
        'solicitud_observada',
        'solicitud_rechazada',
        'rendicion_aprobada',
        'rendicion_observada',
        'rendicion_rechazada',
    ]);

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

    function getEventoRol(evento) {
        return ADMIN_EVENTS.has(String(evento || '').toLowerCase()) ? 'admin' : 'colab';
    }

    function getInitials(nombre) {
        const parts = String(nombre || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '·';
        if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
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

    function formatRelativeTime(timestamp) {
        const value = Number(timestamp || 0);
        if (!value) return '';
        const nowSec = Math.floor(Date.now() / 1000);
        const diff = nowSec - value;
        if (diff < 60) return 'hace un momento';
        if (diff < 3600) {
            const m = Math.floor(diff / 60);
            return 'hace ' + m + ' min';
        }
        if (diff < 86400) {
            const h = Math.floor(diff / 3600);
            return 'hace ' + h + ' h';
        }
        if (diff < 86400 * 2) return 'ayer';
        return null;
    }

    function formatSmartTime(timestamp) {
        const rel = formatRelativeTime(timestamp);
        return rel || formatDateTime(timestamp);
    }

    function renderAuthorChip(item) {
        const nombre = item && item.usuario_nombre ? String(item.usuario_nombre) : '';
        if (!nombre && !(item && item.usuario_id)) return '';
        const rol = getEventoRol(item && item.evento);
        const rolLabel = rol === 'admin' ? 'Admin' : 'Colaborador';
        const display = nombre || ('Usuario #' + (item.usuario_id || ''));
        const initials = getInitials(display);
        return (
            '<span class="timeline-author is-' + rol + '">' +
                '<span class="timeline-author-avatar" aria-hidden="true">' + escapeHtml(initials) + '</span>' +
                '<span class="timeline-author-name">' + escapeHtml(display) + '</span>' +
                '<span class="timeline-author-rol">' + rolLabel + '</span>' +
            '</span>'
        );
    }

    function renderTimeline(historial) {
        const items = Array.isArray(historial) ? [...historial] : [];

        items.sort((a, b) => Number(a && a.fecha || 0) - Number(b && b.fecha || 0));

        if (!items.length) {
            return '<div class="timeline-empty">No hay eventos registrados todavía.</div>';
        }

        return `<div class="timeline-list">${items.map((item, idx) => {
            const evento = String(item && item.evento || '').toLowerCase();
            const rol = getEventoRol(evento);
            const comentario = item && item.comentario ? String(item.comentario) : '';
            const authorChip = renderAuthorChip(item);
            const smart = formatSmartTime(item && item.fecha);
            const absolute = formatDateTime(item && item.fecha);
            const relative = formatRelativeTime(item && item.fecha);
            const tStamp = relative
                ? `<time class="timeline-time" datetime="${Number(item.fecha) || ''}" title="${escapeHtml(absolute)}">${escapeHtml(smart)}</time>`
                : `<time class="timeline-time" datetime="${Number(item.fecha) || ''}">${escapeHtml(smart)}</time>`;

            const comentarioHtml = comentario
                ? `<blockquote class="timeline-comment is-${rol}">${escapeHtml(comentario)}</blockquote>`
                : '';

            return `
        <div class="timeline-item is-${rol}" data-evento="${escapeHtml(evento)}" data-event-idx="${idx}">
            <div class="timeline-marker"><span class="timeline-dot"></span></div>
            <div class="timeline-content">
                <div class="timeline-head">
                    <span class="timeline-title">${escapeHtml(getLabel(evento))}</span>
                    ${tStamp}
                </div>
                ${authorChip}
                ${comentarioHtml}
            </div>
        </div>
    `;
        }).join('')}</div>`;
    }

    return {
        getLabel,
        getEventoRol,
        formatDateTime,
        formatRelativeTime,
        formatSmartTime,
        renderTimeline,
    };
})();

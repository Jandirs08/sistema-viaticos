/**
 * ViaticosUtils — Shared utility functions for all dashboard views.
 * Exposes: escapeHtml, fmtMonto, fmtFecha, showToast, setButtonLoading,
 *          createApiFetch, createApiFetchForm, ModalManager
 */
window.ViaticosUtils = (function () {
    'use strict';

    function escapeHtml(v) {
        return String(v || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function fmtMonto(v) {
        const n = parseFloat(v);
        return isNaN(n) ? '—' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtFecha(iso) {
        if (!iso) return '—';
        const p = String(iso).split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    function showToast(type, title, message, duration) {
        if (message === undefined) message = '';
        if (duration === undefined) duration = 4500;
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
        };
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span class="toast-icon">${icons[type] || ''}</span><div class="toast-body"><strong>${escapeHtml(title)}</strong>${message ? `<p>${escapeHtml(message)}</p>` : ''}</div>`;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all .3s ease';
            setTimeout(function () { toast.remove(); }, 320);
        }, duration);
    }

    function setButtonLoading(btn, on) {
        if (on) {
            btn.disabled = true;
            btn.dataset.origText = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando…`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.origText || '';
        }
    }

    let nonceExpiredHandled = false;
    function handleNonceExpired() {
        if (nonceExpiredHandled) return;
        nonceExpiredHandled = true;
        try {
            showToast('info', 'Sesión expirada', 'Recargando para renovar tu sesión…', 2500);
        } catch (_) { /* toast container may not exist */ }
        setTimeout(function () { window.location.reload(); }, 1600);
    }

    function isNonceError(status, data) {
        if (status !== 401 && status !== 403) return false;
        const code = data && (data.code || (data.data && data.data.code));
        return code === 'rest_cookie_invalid_nonce';
    }

    function createApiFetch(apiBase, nonce) {
        return async function apiFetch(endpoint, options) {
            options = options || {};
            const merged = Object.assign({ headers: {} }, options);
            merged.headers = Object.assign({ 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, options.headers || {});
            const response = await fetch(apiBase + endpoint, merged);
            const data = await response.json();
            if (!response.ok) {
                if (isNonceError(response.status, data)) handleNonceExpired();
                throw new Error(data.message || `Error ${response.status}`);
            }
            return data;
        };
    }

    function createApiFetchForm(apiBase, nonce) {
        return async function apiFetchForm(endpoint, formData) {
            const url = apiBase.replace(/\/$/, '') + endpoint;
            const resp = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': nonce },
                body: formData,
            });
            const json = await resp.json();
            if (!resp.ok || !json.success) {
                if (isNonceError(resp.status, json)) handleNonceExpired();
                throw new Error(json.message || 'Error en la solicitud.');
            }
            return json;
        };
    }

    const ModalManager = {
        _triggers: {},
        _focusable: function (container) {
            return Array.from(container.querySelectorAll(
                'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )).filter(function (el) { return el.offsetParent !== null; });
        },
        open: function (id) {
            const o = document.getElementById(id);
            if (!o) return;
            this._triggers[id] = document.activeElement;
            o.classList.add('open');
            document.body.style.overflow = 'hidden';
            const self = this;
            setTimeout(function () {
                const els = self._focusable(o);
                if (els.length) els[0].focus();
            }, 60);
            o._trapFocus = function (e) {
                if (e.key !== 'Tab') return;
                const els = self._focusable(o);
                if (!els.length) return;
                const first = els[0], last = els[els.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) { e.preventDefault(); last.focus(); }
                } else {
                    if (document.activeElement === last) { e.preventDefault(); first.focus(); }
                }
            };
            o.addEventListener('keydown', o._trapFocus);
        },
        close: function (id) {
            const o = document.getElementById(id);
            if (!o) return;
            o.classList.remove('open');
            document.body.style.overflow = '';
            if (o._trapFocus) { o.removeEventListener('keydown', o._trapFocus); delete o._trapFocus; }
            const trigger = this._triggers[id];
            if (trigger && typeof trigger.focus === 'function') trigger.focus();
            delete this._triggers[id];
        },
        closeOnOverlayClick: function (id) {
            const self = this;
            const o = document.getElementById(id);
            if (o) o.addEventListener('click', function (e) { if (e.target === o) self.close(id); });
        },
    };

    function renderTableSkeleton(tbody, colCount, rowCount) {
        if (rowCount === undefined) rowCount = 5;
        var widths = ['38px', '180px', '90px', '110px', '100px', '72px'];
        tbody.innerHTML = Array.from({ length: rowCount }, function () {
            return '<tr class="table-skeleton-row">' +
                Array.from({ length: colCount }, function (_, i) {
                    return '<td><div class="skel-cell" style="width:' + (widths[i] || '80px') + '"></div></td>';
                }).join('') +
                '</tr>';
        }).join('');
    }

    return { escapeHtml, fmtMonto, fmtFecha, showToast, setButtonLoading, createApiFetch, createApiFetchForm, ModalManager, renderTableSkeleton };
})();

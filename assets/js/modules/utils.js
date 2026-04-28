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
            warning: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`,
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

    /**
     * Helper para errores de API/red. Acepta un Error, string, o {message}.
     * Muestra toast tipo 'error' con título consistente.
     * Uso: showApiError(err)  /  showApiError(err, 'Título custom')
     */
    function showApiError(err, title) {
        let msg;
        if (err && typeof err.message === 'string') msg = err.message;
        else if (typeof err === 'string')           msg = err;
        else                                        msg = 'Error inesperado';
        showToast('error', title || 'Error', msg);
    }

    /**
     * Hace filas de tabla clickeables + keyboard-accessible (Enter/Space).
     * Convenciones del HTML (rendererSolicitudRow / renderAdminRow):
     *   <tr class="row-clickable" tabindex="0" role="button" data-id="X">
     *
     * @param {HTMLElement} tbody       <tbody> que contiene las filas.
     * @param {Object}      opts
     *   - onActivate:   function(id:number, row:HTMLElement, event:Event) → void  (requerido)
     *   - rowSelector:  string (default 'tr.row-clickable')
     *   - idAttr:       string (default 'data-id'). Su valor se castea a int.
     *   - actionGuard:  string (default 'button, a'). Si el target del click matchea, NO dispara onActivate.
     */
    function bindRowAction(tbody, opts) {
        if (!tbody || !opts || typeof opts.onActivate !== 'function') return;
        const rowSelector = opts.rowSelector || 'tr.row-clickable';
        const idAttr      = opts.idAttr      || 'data-id';
        const guard       = opts.actionGuard || 'button, a';

        tbody.querySelectorAll(rowSelector).forEach(function (row) {
            const id = parseInt(row.getAttribute(idAttr), 10);
            if (!id) return;

            const activate = function (e) {
                if (e && e.target && e.target.closest(guard)) return;
                opts.onActivate(id, row, e);
            };

            row.addEventListener('click', activate);
            row.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                if (e.target && e.target.closest(guard)) return;
                e.preventDefault();
                opts.onActivate(id, row, e);
            });
        });
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
            if (!o) return;
            // Cierre solo si AMBOS mousedown y mouseup ocurrieron en el overlay
            // (no en el modal). Evita cierre accidental al arrastrar selección de
            // texto dentro de un input y soltar el mouse fuera del modal.
            let mdTarget = null;
            o.addEventListener('mousedown', function (e) { mdTarget = e.target; });
            o.addEventListener('mouseup', function (e) {
                const md = mdTarget;
                mdTarget = null;
                if (md === o && e.target === o) self.close(id);
            });
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

    return { escapeHtml, fmtMonto, fmtFecha, showToast, showApiError, setButtonLoading, bindRowAction, createApiFetch, createApiFetchForm, ModalManager, renderTableSkeleton };
})();

/**
 * ViaticosForms — validación y feedback visual reutilizable para todos los formularios del SPA.
 * Convenciones:
 *   - El input tiene .form-control.
 *   - Cerca del input vive un <span class="form-error"> que se hace visible con la clase 'visible'.
 *   - Estado inválido: clase 'is-invalid' en el input + 'visible' en el .form-error.
 *   - Cuando el usuario edita el campo, el estado inválido se borra automáticamente.
 *
 * Métodos públicos:
 *   validateInput(input, errEl, opts)         → bool
 *   clearInvalid(input, errEl)                → void
 *   clearFormErrors(formEl)                   → void
 *   focusFirstInvalid(formEl)                 → HTMLElement|null
 *   markServerError(formEl, fieldName, msg)   → bool
 *   parseServerMissingFields(message)         → string[]
 *   handleServerError(formEl, message)        → { handled:bool, fields:string[] }
 */
window.ViaticosForms = (function () {
    'use strict';

    function findErrorElFor(input) {
        if (!input) return null;
        const group = input.closest && input.closest('.form-group');
        return group ? group.querySelector('.form-error') : null;
    }

    function setInvalid(input, errEl, message) {
        if (!input) return;
        if (!errEl) errEl = findErrorElFor(input);
        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');
        if (errEl) {
            // Cachea el mensaje default del HTML para poder restaurarlo después.
            if (errEl.dataset.defaultMsg === undefined) {
                errEl.dataset.defaultMsg = errEl.textContent || '';
            }
            errEl.textContent = message || errEl.dataset.defaultMsg || '';
            errEl.classList.add('visible');
        }
        bindAutoClear(input, errEl);
    }

    function bindAutoClear(input, errEl) {
        if (input.dataset.invalidBound === '1') return;
        input.dataset.invalidBound = '1';
        const handler = function () {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
            if (errEl) errEl.classList.remove('visible');
        };
        input.addEventListener('input', handler);
        input.addEventListener('change', handler);
    }

    function clearInvalid(input, errEl) {
        if (!input) return;
        if (!errEl) errEl = findErrorElFor(input);
        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');
        if (errEl) errEl.classList.remove('visible');
    }

    /**
     * Valida un input.
     * @param {HTMLElement} input
     * @param {HTMLElement|null} errEl
     * @param {Object} [opts]
     *   - required:  bool (default: input.required)
     *   - validator: function(value) → bool
     *   - message:   string (override del .form-error)
     */
    function validateInput(input, errEl, opts) {
        if (!input) return false;
        opts = opts || {};
        if (!errEl) errEl = findErrorElFor(input);
        const value = (input.value == null ? '' : String(input.value)).trim();
        const required = opts.required !== undefined ? opts.required : input.required;

        let valid = true;
        if (required && '' === value) valid = false;
        if (valid && typeof opts.validator === 'function') valid = !!opts.validator(value);
        if (valid && typeof input.checkValidity === 'function' && !input.checkValidity()) valid = false;

        if (valid) clearInvalid(input, errEl);
        else       setInvalid(input, errEl, opts.message);
        return valid;
    }

    function clearFormErrors(formEl) {
        if (!formEl) return;
        formEl.querySelectorAll('.form-control.is-invalid').forEach(function (el) { clearInvalid(el); });
        formEl.querySelectorAll('.form-error.visible').forEach(function (el) { el.classList.remove('visible'); });
    }

    function focusFirstInvalid(formEl) {
        if (!formEl) return null;
        const candidates = formEl.querySelectorAll('.form-control.is-invalid:not([type="hidden"])');
        let target = null;
        for (let i = 0; i < candidates.length; i++) {
            const el = candidates[i];
            // Salta inputs cuyo contenedor está display:none (offsetParent === null).
            if (el.offsetParent !== null || el === document.activeElement) {
                target = el;
                break;
            }
        }
        if (!target) return null;
        try { target.focus({ preventScroll: true }); } catch (_) { target.focus(); }
        if (typeof target.scrollIntoView === 'function') {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return target;
    }

    /**
     * Marca un campo como inválido por respuesta del servidor.
     * Busca por [name="X"] o por id "rg-X".
     */
    function markServerError(formEl, fieldName, message) {
        if (!formEl || !fieldName) return false;
        let input = formEl.querySelector('[name="' + fieldName + '"]');
        if (!input) input = formEl.querySelector('#rg-' + fieldName);
        if (!input) return false;
        setInvalid(input, findErrorElFor(input), message);
        return true;
    }

    /**
     * Parsea mensajes típicos de WP REST API. Devuelve { kind, fields }.
     *   "Missing parameter(s): foo, bar" → kind 'missing'
     *   "Invalid parameter(s): foo, bar" → kind 'invalid'
     *   sino                              → kind ''
     */
    function parseServerFieldErrors(message) {
        const re = /(Missing|Invalid) parameter\(s\):\s*(.+?)(?:\.|$)/i;
        const m  = re.exec(message || '');
        if (!m) return { kind: '', fields: [] };
        const kind   = m[1].toLowerCase() === 'invalid' ? 'invalid' : 'missing';
        const fields = m[2].split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        return { kind: kind, fields: fields };
    }

    /** @deprecated Usa parseServerFieldErrors. */
    function parseServerMissingFields(message) {
        return parseServerFieldErrors(message).fields;
    }

    /**
     * Si el mensaje del server lista campos con error, los marca en el form.
     * Devuelve { handled, fields, kind }.
     */
    function handleServerError(formEl, message) {
        const parsed = parseServerFieldErrors(message);
        if (!parsed.fields.length) return { handled: false, fields: [], kind: '' };
        const fieldMsg = 'invalid' === parsed.kind ? 'Valor inválido' : 'Campo requerido';
        let any = false;
        parsed.fields.forEach(function (f) {
            if (markServerError(formEl, f, fieldMsg)) any = true;
        });
        if (any) focusFirstInvalid(formEl);
        return { handled: any, fields: parsed.fields, kind: parsed.kind };
    }

    return {
        validateInput: validateInput,
        clearInvalid: clearInvalid,
        clearFormErrors: clearFormErrors,
        focusFirstInvalid: focusFirstInvalid,
        markServerError: markServerError,
        parseServerFieldErrors: parseServerFieldErrors,
        parseServerMissingFields: parseServerMissingFields,
        handleServerError: handleServerError,
    };
})();

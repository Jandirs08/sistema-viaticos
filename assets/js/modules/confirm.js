/**
 * ViaticosConfirm — Promise-based confirmation dialog.
 * Replaces native window.confirm with a brand-aligned modal.
 *
 *   ViaticosConfirm.show({
 *     title: 'Eliminar gasto',
 *     message: 'No podrás recuperarlo.',
 *     variant: 'danger',          // 'default' | 'danger' | 'warning'
 *     confirmText: 'Eliminar',    // default 'Confirmar'
 *     cancelText:  'Cancelar',    // default 'Cancelar'
 *     icon: 'trash' | 'warning' | 'info' (optional, inferred from variant)
 *   }).then(ok => { if (ok) ... });
 *
 * Esc cancela. Enter confirma. Trap de foco. Solo una instancia en DOM.
 */
window.ViaticosConfirm = (function () {
    'use strict';

    const DOM_ID = 'viaticos-confirm-overlay';

    const ICONS = {
        trash: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>',
        question: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>',
    };

    function escHtml(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function ensureDom() {
        let el = document.getElementById(DOM_ID);
        if (el) return el;
        el = document.createElement('div');
        el.id = DOM_ID;
        el.className = 'modal-overlay vc-overlay';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-modal', 'true');
        el.setAttribute('aria-labelledby', DOM_ID + '-title');
        el.innerHTML = `
            <div class="modal modal-sm vc-modal" role="document">
                <div class="vc-head">
                    <span class="vc-icon" aria-hidden="true"></span>
                    <div class="vc-headtxt">
                        <h2 class="vc-title" id="${DOM_ID}-title"></h2>
                        <p class="vc-msg"></p>
                    </div>
                </div>
                <div class="vc-foot">
                    <button type="button" class="btn btn-secondary btn-sm vc-cancel"></button>
                    <button type="button" class="btn btn-sm vc-confirm"></button>
                </div>
            </div>`;
        document.body.appendChild(el);
        return el;
    }

    function show(opts) {
        opts = opts || {};
        const variant = ['danger', 'warning', 'default'].indexOf(opts.variant) >= 0 ? opts.variant : 'default';
        const title = opts.title || '¿Continuar?';
        const message = opts.message || '';
        const confirmText = opts.confirmText || (variant === 'danger' ? 'Eliminar' : 'Confirmar');
        const cancelText = opts.cancelText || 'Cancelar';
        const iconKey = opts.icon || (variant === 'danger' ? 'trash' : variant === 'warning' ? 'warning' : 'question');

        const overlay = ensureDom();
        overlay.dataset.variant = variant;

        const titleEl = overlay.querySelector('.vc-title');
        const msgEl = overlay.querySelector('.vc-msg');
        const iconEl = overlay.querySelector('.vc-icon');
        const cancelBtn = overlay.querySelector('.vc-cancel');
        const confirmBtn = overlay.querySelector('.vc-confirm');

        titleEl.textContent = title;
        if (message) {
            msgEl.innerHTML = escHtml(message).replace(/\n/g, '<br>');
            msgEl.style.display = '';
        } else {
            msgEl.textContent = '';
            msgEl.style.display = 'none';
        }
        iconEl.innerHTML = ICONS[iconKey] || ICONS.question;
        cancelBtn.textContent = cancelText;
        confirmBtn.textContent = confirmText;

        confirmBtn.classList.remove('btn-primary', 'btn-danger', 'btn-warning');
        confirmBtn.classList.add(variant === 'danger' ? 'btn-danger' : variant === 'warning' ? 'btn-warning' : 'btn-primary');

        const previouslyFocused = document.activeElement;

        return new Promise(function (resolve) {
            let settled = false;
            let armedAt = 0;
            let mousedownTarget = null;

            const finish = (value) => {
                if (settled) return;
                settled = true;
                cleanup();
                close();
                resolve(value);
            };

            const onCancel = () => finish(false);
            const onConfirm = () => finish(true);

            const isWithinKeyGuard = () => (Date.now() - armedAt) < 280;

            const onMousedown = (e) => { mousedownTarget = e.target; };
            const onMouseup = (e) => {
                const md = mousedownTarget;
                mousedownTarget = null;
                if (md === overlay && e.target === overlay) onCancel();
            };

            const onCancelClick = (e) => { if (isWithinKeyGuard()) { e.preventDefault(); return; } onCancel(); };
            const onConfirmClick = (e) => { if (isWithinKeyGuard()) { e.preventDefault(); return; } onConfirm(); };

            const onKey = (e) => {
                if (e.key === 'Escape') { e.preventDefault(); onCancel(); return; }
                if (e.key === 'Tab') {
                    const focusable = [cancelBtn, confirmBtn];
                    const idx = focusable.indexOf(document.activeElement);
                    if (e.shiftKey) {
                        if (idx <= 0) { e.preventDefault(); confirmBtn.focus(); }
                    } else {
                        if (idx === focusable.length - 1) { e.preventDefault(); cancelBtn.focus(); }
                    }
                    return;
                }
                if ((e.key === 'Enter' || e.key === ' ') && isWithinKeyGuard()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            };

            const cleanup = () => {
                cancelBtn.removeEventListener('click', onCancelClick);
                confirmBtn.removeEventListener('click', onConfirmClick);
                overlay.removeEventListener('mousedown', onMousedown);
                overlay.removeEventListener('mouseup', onMouseup);
                overlay.removeEventListener('keydown', onKey, true);
            };

            const close = () => {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
                if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
                    setTimeout(() => { try { previouslyFocused.focus(); } catch (_) {} }, 0);
                }
            };

            cancelBtn.addEventListener('click', onCancelClick);
            confirmBtn.addEventListener('click', onConfirmClick);
            overlay.addEventListener('mousedown', onMousedown);
            overlay.addEventListener('mouseup', onMouseup);
            overlay.addEventListener('keydown', onKey, true);

            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';

            const initialFocusTarget = variant === 'danger' ? cancelBtn : confirmBtn;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    armedAt = Date.now();
                    try { initialFocusTarget.focus({ preventScroll: true }); } catch (_) { initialFocusTarget.focus(); }
                });
            });
        });
    }

    return { show: show };
})();

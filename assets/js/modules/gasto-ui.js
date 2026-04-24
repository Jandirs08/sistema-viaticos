/**
 * ViaticosGastoUI — Shared expandable gasto accordion module.
 * Exposes:
 *   renderGastoItem(gasto, idPrefix)  → HTML string for one accordion item
 *   bindAccordionList(containerEl)    → attach single-open click handlers
 */
window.ViaticosGastoUI = (function () {
    'use strict';

    const TIPO_LABEL = {
        movilidad: 'Movilidad',
        vale_caja: 'Vale de Caja',
        factura: 'Factura',
        boleta: 'Boleta',
        rxh: 'RxH',
    };

    function esc(v) {
        return String(v || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function fmtFecha(iso) {
        if (!iso) return '—';
        const p = String(iso).split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    function fmtMonto(v) {
        const n = parseFloat(v);
        return isNaN(n) ? '—' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function field(label, value) {
        if (!value && value !== 0) return '';
        return `<div class="gasto-acc-field">
    <span class="gaf-label">${esc(label)}</span>
    <span class="gaf-value">${esc(String(value))}</span>
</div>`;
    }

    function buildFields(gasto) {
        const tipo = String(gasto.tipo || '');
        const parts = [];
        parts.push(field('Fecha emisión', fmtFecha(gasto.fecha)));
        parts.push(field('Importe', fmtMonto(gasto.importe)));
        parts.push(field('Categoría', gasto.categoria_nombre));
        parts.push(field('Cta. Contable', gasto.cta_contable));
        if (tipo === 'movilidad') {
            parts.push(field('Motivo', gasto.motivo_movilidad));
            parts.push(field('Destino', gasto.destino_movilidad));
            parts.push(field('CECO / OI', gasto.ceco_oi));
        } else {
            parts.push(field('RUC proveedor', gasto.ruc));
            parts.push(field('Razón social', gasto.razon));
            parts.push(field('N° comprobante', gasto.nro));
            parts.push(field('Concepto', gasto.concepto));
        }
        return parts.filter(Boolean).join('');
    }

    function summaryText(gasto) {
        const tipo = String(gasto.tipo || '');
        if (tipo === 'movilidad') {
            return [gasto.destino_movilidad, gasto.motivo_movilidad].filter(Boolean).join(' · ') || 'Movilidad registrada';
        }
        return [gasto.razon, gasto.nro].filter(Boolean).join(' · ') || gasto.concepto || 'Sin detalle';
    }

    /**
     * @param {Object} gasto   - gasto object from API
     * @param {string} itemId  - unique HTML id for this item
     * @returns {string} HTML
     */
    function renderGastoItem(gasto, itemId) {
        const tipoLabel = TIPO_LABEL[gasto.tipo] || esc(gasto.tipo) || 'Gasto';
        const summary = esc(summaryText(gasto));
        const fecha = fmtFecha(gasto.fecha);
        const importe = fmtMonto(gasto.importe);
        const fields = buildFields(gasto);
        const chevron = `<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>`;
        const gastoId = gasto.id ? esc(String(gasto.id)) : '';

        return `
<div class="gasto-acc-item" data-acc-id="${esc(String(itemId))}" data-gasto-id="${gastoId}">
    <div class="gasto-acc-header" role="button" tabindex="0"
         aria-expanded="false" data-acc-toggle="${esc(String(itemId))}">
        <span class="gasto-acc-chevron">${chevron}</span>
        <span class="gasto-acc-tipo">${esc(tipoLabel)}</span>
        <div class="gasto-acc-summary">
            <div class="gas-label">${summary}</div>
            <div class="gas-sub">${esc(fecha)}</div>
        </div>
        <span class="gasto-acc-importe">${esc(importe)}</span>
    </div>
    <div class="gasto-acc-body">
        <div class="gasto-acc-fields">${fields}</div>
        ${gastoId ? `<div class="gasto-adj-panel" data-adj-gasto-id="${gastoId}">
            <div class="gasto-adj-title">
                <span style="display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="color:#4A5568;"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V6H11v9.5a2.5 2.5 0 005 0V5c0-2.21-1.79-4-4-4S8 2.79 8 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-2.5z"/></svg>
                    Adjuntos
                </span>
            </div>
            <div class="gasto-adj-list"><span class="gasto-adj-loading">Cargando adjuntos…</span></div>
        </div>` : ''}
    </div>
</div>`;
    }

    /**
     * Bind single-open accordion logic to all .gasto-acc-header elements
     * inside containerEl. Safe to call multiple times (uses event delegation).
     * @param {Element} containerEl
     * @param {Object}  [opts]
     * @param {Function} [opts.onOpen]  Called with (itemEl, gastoId) when an item opens.
     */
    function bindAccordionList(containerEl, opts) {
        if (!containerEl) return;
        const onOpen = (opts && typeof opts.onOpen === 'function') ? opts.onOpen : null;
        containerEl.addEventListener('click', function (e) {
            const header = e.target.closest('[data-acc-toggle]');
            if (!header) return;
            const id = header.dataset.accToggle;
            const items = containerEl.querySelectorAll('.gasto-acc-item');
            items.forEach(function (item) {
                const isTarget = item.dataset.accId === id;
                const wasOpen = item.classList.contains('is-open');
                if (isTarget) {
                    const nowOpen = !wasOpen;
                    item.classList.toggle('is-open', nowOpen);
                    header.setAttribute('aria-expanded', String(nowOpen));
                    if (nowOpen && onOpen) {
                        onOpen(item, item.dataset.gastoId || null);
                    }
                } else {
                    item.classList.remove('is-open');
                    const h = item.querySelector('[data-acc-toggle]');
                    if (h) h.setAttribute('aria-expanded', 'false');
                }
            });
        });
        // Keyboard: Enter/Space triggers click on focused header
        containerEl.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const header = e.target.closest('[data-acc-toggle]');
            if (header) { e.preventDefault(); header.click(); }
        });
    }

    function adjIconClass(mime) {
        if (!mime) return 'file';
        if (mime.includes('pdf')) return 'pdf';
        if (mime.includes('xml')) return 'xml';
        if (mime.includes('image')) return 'img';
        return 'file';
    }

    function adjIconLabel(mime) {
        if (!mime) return 'FILE';
        if (mime.includes('pdf')) return 'PDF';
        if (mime.includes('xml')) return 'XML';
        if (mime.includes('image')) return mime.includes('png') ? 'PNG' : 'JPG';
        return 'FILE';
    }

    function renderAdjuntosList(adjuntos, gastoId, canDelete) {
        if (!adjuntos.length) {
            return '<span class="gasto-adj-empty">Sin adjuntos.</span>';
        }
        return adjuntos.map(function (adj) {
            return `
<div class="gasto-adj-item" data-adj-id="${esc(String(adj.id))}">
    <div class="gasto-adj-icon ${adjIconClass(adj.mime)}">${adjIconLabel(adj.mime)}</div>
    <span class="gasto-adj-name" title="${esc(adj.name)}">${esc(adj.name)}</span>
    <div class="gasto-adj-actions">
        <a class="gasto-adj-btn" href="${esc(adj.url)}" target="_blank" rel="noopener">Ver</a>
        ${canDelete ? `<button class="gasto-adj-btn del js-adj-delete" data-adj-id="${esc(String(adj.id))}" data-gasto-id="${esc(String(gastoId))}">Eliminar</button>` : ''}
    </div>
</div>`;
        }).join('');
    }

    async function loadAdjuntos(gastoId, itemEl, opts) {
        const canDelete = !!(opts && opts.canDelete);
        const apiFetch = opts && opts.apiFetch;
        const panel = itemEl.querySelector('.gasto-adj-panel[data-adj-gasto-id="' + gastoId + '"]');
        if (!panel || panel.dataset.adjLoaded === '1') return;
        panel.dataset.adjLoaded = '1';
        const listEl = panel.querySelector('.gasto-adj-list');
        listEl.innerHTML = '<span class="gasto-adj-loading">Cargando adjuntos…</span>';
        try {
            const res = await apiFetch('/gasto-adjuntos/' + gastoId);
            const adjuntos = res.adjuntos || [];
            listEl.innerHTML = renderAdjuntosList(adjuntos, gastoId, canDelete);
            if (canDelete && adjuntos.length) {
                listEl.addEventListener('click', async function (e) {
                    const btn = e.target.closest('.js-adj-delete');
                    if (!btn || btn.disabled) return;
                    btn.disabled = true; btn.textContent = '…';
                    try {
                        await apiFetch('/gasto-adjunto/' + btn.dataset.adjId, { method: 'DELETE' });
                        panel.dataset.adjLoaded = '0';
                        await loadAdjuntos(gastoId, itemEl, opts);
                    } catch (err) {
                        btn.textContent = 'Error';
                        setTimeout(function () { btn.textContent = 'Eliminar'; btn.disabled = false; }, 2000);
                    }
                }, { once: true });
            }
        } catch (err) {
            listEl.innerHTML = '<span class="gasto-adj-empty" style="color:#C53030;">Error al cargar adjuntos.</span>';
        }
    }

    return { renderGastoItem, bindAccordionList, adjIconClass, adjIconLabel, renderAdjuntosList, loadAdjuntos, buildFields, summaryText, TIPO_LABEL, escHtml: esc, fmtFecha, fmtMonto };
})();

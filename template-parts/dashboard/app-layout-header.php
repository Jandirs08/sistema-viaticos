<?php
/**
 * Template Part: Dashboard App Layout — Header
 *
 * Shared shell: DOCTYPE, <head> (tokens + CSS), toast container, sidebar,
 * topbar and the opening <main id="erp-content"> tag.
 * Closed by app-layout-footer.php.
 *
 * Expected args (set by the router page-dashboard.php):
 *   $args['user_name']      string  Escaped display name.
 *   $args['user_initials']  string  Upper-cased initials (1–2 chars).
 *   $args['logout_url']     string  Escaped logout URL.
 *   $args['dashboard_role'] string  'admin' | 'colaborador'
 *   $args['user_dni']       string  DNI del usuario actual.
 *   $args['user_cargo']     string  Cargo del usuario actual.
 *   $args['user_area']      string  Área del usuario actual.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'user_name'      => '',
        'user_initials'  => '',
        'logout_url'     => '',
        'dashboard_role' => 'colaborador',
        'user_dni'       => '',
        'user_cargo'     => '',
        'user_area'      => '',
    ]
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $args['dashboard_role'] === 'admin' ? 'Panel Administrador' : 'Dashboard Colaborador'; ?> — Sistema de Gestión de Viáticos</title>
    <meta name="description" content="Panel de gestión de viáticos — Fundación Romero.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <?php wp_head(); ?>

<script>
/**
 * ViaticosGastoUI — Shared expandable gasto accordion module.
 * Exposes:
 *   renderGastoItem(gasto, idPrefix)  → HTML string for one accordion item
 *   bindAccordionList(containerEl)    → attach single-open click handlers
 */
window.ViaticosGastoUI = (function () {
    'use strict';

    const TIPO_LABEL = {
        movilidad:  'Movilidad',
        vale_caja:  'Vale de Caja',
        factura:    'Factura',
        boleta:     'Boleta',
        rxh:        'RxH',
    };

    function esc(v) {
        return String(v || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
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
        parts.push(field('Cuenta contable', gasto.cuenta));
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
        const summary   = esc(summaryText(gasto));
        const fecha     = fmtFecha(gasto.fecha);
        const importe   = fmtMonto(gasto.importe);
        const fields    = buildFields(gasto);
        const chevron   = `<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>`;
        const gastoId   = gasto.id ? esc(String(gasto.id)) : '';

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
                const wasOpen  = item.classList.contains('is-open');
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
        return adjuntos.map(function (adj) { return `
            <div class="gasto-adj-item" data-adj-id="${esc(String(adj.id))}">
                <div class="gasto-adj-icon ${adjIconClass(adj.mime)}">${adjIconLabel(adj.mime)}</div>
                <span class="gasto-adj-name" title="${esc(adj.name)}">${esc(adj.name)}</span>
                <div class="gasto-adj-actions">
                    <a class="gasto-adj-btn" href="${esc(adj.url)}" target="_blank" rel="noopener">Ver</a>
                    ${canDelete ? `<button class="gasto-adj-btn del js-adj-delete" data-adj-id="${esc(String(adj.id))}" data-gasto-id="${esc(String(gastoId))}">Eliminar</button>` : ''}
                </div>
            </div>`; }).join('');
    }

    async function loadAdjuntos(gastoId, itemEl, opts) {
        const canDelete = !!(opts && opts.canDelete);
        const apiFetch  = opts && opts.apiFetch;
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
</script>
<script>
window.ViaticosEstadoUI = (function () {
    const labels = {
        solicitud: {
            pendiente: 'Pendiente',
            aprobada: 'Aprobada',
            observada: 'Observada',
            rechazada: 'Rechazada',
        },
        rendicion: {
            no_disponible: 'No disponible',
            no_iniciada: 'No iniciada',
            en_proceso: 'En proceso',
            en_revision: 'En revisión',
            aprobada: 'Aprobada',
            observada: 'Observada',
            rechazada: 'Rechazada',
        },
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

    return {
        resolveEstadoSolicitud,
        resolveEstadoRendicion,
        getLabelEstado,
        renderBadgeEstado,
        renderEstadoGrupo,
        getSolicitudEstado,
        renderSolicitudBadge,
    };
})();
window.resolveEstadoSolicitud = window.ViaticosEstadoUI.resolveEstadoSolicitud;
window.resolveEstadoRendicion = window.ViaticosEstadoUI.resolveEstadoRendicion;
window.getLabelEstado = window.ViaticosEstadoUI.getLabelEstado;
window.renderBadgeEstado = window.ViaticosEstadoUI.renderBadgeEstado;
window.renderEstadoGrupo = window.ViaticosEstadoUI.renderEstadoGrupo;
</script>
<script>
window.ViaticosTimelineUI = (function () {
    'use strict';

    const labels = {
        solicitud_creada: 'Solicitud creada',
        solicitud_aprobada: 'Solicitud aprobada',
        solicitud_observada: 'Solicitud observada',
        solicitud_rechazada: 'Solicitud rechazada',
        rendicion_iniciada: 'Rendicion iniciada',
        rendicion_finalizada: 'Rendicion finalizada',
        rendicion_aprobada: 'Rendicion aprobada',
        rendicion_observada: 'Rendicion observada',
        rendicion_rechazada: 'Rendicion rechazada',
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
            return '<div class="timeline-empty">No hay eventos registrados todavia.</div>';
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
</script>
<script>
/**
 * ViaticosLiquidacion — Shared formal liquidation document renderer.
 * Exposes:
 *   buildData(sol, gastos, opts?)  → normalized data object
 *   renderDoc(data)                → HTML string (document)
 */
window.ViaticosLiquidacion = (function () {
    'use strict';

    const TIPO_LABEL = {
        movilidad: 'Movilidad', vale_caja: 'Vale de Caja',
        factura: 'Factura', boleta: 'Boleta', rxh: 'RxH',
    };
    const CLASE_DOC = {
        movilidad: 'Vale Movilidad', vale_caja: 'Vale de Caja',
        factura: 'Factura', boleta: 'Boleta', rxh: 'Recibo x Hon.',
    };

    function esc(v) {
        return String(v || '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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

    /**
     * buildData — normalise all values into a plain object.
     * @param {Object} sol    Solicitud record from the cache
     * @param {Array}  gastos Array of gasto records
     * @param {Object} opts   Optional overrides { colaboradorNombre, area, fechaRendicion }
     */
    function buildData(sol, gastos, opts) {
        opts = opts || {};
        const gastosArr = Array.isArray(gastos) ? gastos : [];
        const totalRendido = gastosArr.reduce((s, g) => s + (parseFloat(g.importe) || 0), 0);
        const montoSolicitado = parseFloat(sol.monto) || 0;
        const saldo = montoSolicitado - totalRendido;
        return {
            id:                sol.id,
            colaborador:       opts.colaboradorNombre || sol.colaborador || '—',
            dni:               sol.dni || '—',
            area:              opts.area || sol.area || '—',
            cargo:             opts.cargo || sol.cargo || '—',
            motivo:            sol.motivo || '—',
            fechaViaje:        sol.fecha || sol.fecha_viaje || '',
            fechaRendicion:    opts.fechaRendicion || sol.fecha_creacion || '—',
            montoSolicitado,
            totalRendido,
            saldo,
            moneda:            'SOLES',
            ceco:              sol.ceco || '—',
            estadoRendicion:   sol.estado_rendicion || 'finalizada',
            gastos:            gastosArr,
        };
    }

    /**
     * renderDoc — build the full document HTML from normalised data.
     * No DOM side-effects; returns a string.
     */
    function renderDoc(data) {
        const today = new Date().toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long', year: 'numeric',
        });

        // Rows
        const rows = data.gastos.map((g, i) => {
            const tipo = String(g.tipo || '');
            const concepto = tipo === 'movilidad'
                ? [g.destino_movilidad, g.motivo_movilidad].filter(Boolean).join(' — ') || g.concepto || '—'
                : g.concepto || g.razon || '—';
            const ruc = tipo === 'movilidad' ? '—' : (g.ruc || '—');
            return `
            <tr>
                <td class="muted">${i + 1}</td>
                <td>${esc(TIPO_LABEL[tipo] || tipo || '—')}</td>
                <td>${esc(CLASE_DOC[tipo] || '—')}</td>
                <td>${esc(fmtFecha(g.fecha))}</td>
                <td>SOLES</td>
                <td>${esc(g.nro || '—')}</td>
                <td>${esc(concepto)}</td>
                <td class="muted">${esc(ruc)}</td>
                <td class="muted">${esc(g.cuenta || '—')}</td>
                <td class="muted">${esc(g.ceco_oi || '—')}</td>
                <td class="num"><strong>${esc(fmtMonto(g.importe))}</strong></td>
            </tr>`;
        }).join('');

        const emptyRow = `<tr><td colspan="11" style="text-align:center;padding:28px;color:#A0AEC0;font-style:italic;">Sin gastos registrados.</td></tr>`;

        const saldoClass = data.saldo >= 0 ? 'amber' : 'red';

        return `
<div class="liq-doc" id="liq-documento">
    <div class="liq-doc-header">
        <div>
            <div class="liq-doc-header-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:-3px;margin-right:6px;"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                Liquidación de Rendición de Viáticos
            </div>
            <div class="liq-doc-header-sub">Solicitud N.° ${esc(data.id)} &nbsp;&bull;&nbsp; Moneda: ${esc(data.moneda)}</div>
        </div>
        <div class="liq-doc-header-meta">
            <strong>Viáticos ERP</strong>
            Generado: ${esc(today)}
        </div>
    </div>

    <div class="liq-doc-info">
        <div class="liq-info-cell">
            <div class="liq-info-label">Colaborador</div>
            <div class="liq-info-value">${esc(data.colaborador)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">DNI / Código</div>
            <div class="liq-info-value">${esc(data.dni)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Área</div>
            <div class="liq-info-value muted">${esc(data.area)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">CECO / Proyecto</div>
            <div class="liq-info-value muted">${esc(data.ceco)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Fecha de Viaje</div>
            <div class="liq-info-value">${esc(fmtFecha(data.fechaViaje))}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Fecha de Rendición</div>
            <div class="liq-info-value muted">${esc(data.fechaRendicion)}</div>
        </div>
        <div class="liq-info-cell" style="grid-column:1/-1;border-top:1px solid #E2E8F0;">
            <div class="liq-info-label">Motivo del Viaje</div>
            <div class="liq-info-value muted">${esc(data.motivo)}</div>
        </div>
    </div>

    <div class="liq-doc-table-wrap">
        <table class="liq-doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría</th>
                    <th>Clase Doc.</th>
                    <th>Fecha</th>
                    <th>Moneda</th>
                    <th>N° Documento</th>
                    <th>Concepto</th>
                    <th>RUC</th>
                    <th>Cuenta Cont.</th>
                    <th>CECO</th>
                    <th class="num">Importe</th>
                </tr>
            </thead>
            <tbody>${rows || emptyRow}</tbody>
        </table>
    </div>

    <div class="liq-doc-totals">
        <div class="liq-total-cell">
            <div class="liq-total-label">Monto Solicitado</div>
            <div class="liq-total-value blue">${esc(fmtMonto(data.montoSolicitado))}</div>
        </div>
        <div class="liq-total-cell">
            <div class="liq-total-label">Total Rendido</div>
            <div class="liq-total-value green">${esc(fmtMonto(data.totalRendido))}</div>
        </div>
        <div class="liq-total-cell">
            <div class="liq-total-label">Saldo</div>
            <div class="liq-total-value ${saldoClass}">${esc(fmtMonto(data.saldo))}</div>
        </div>
    </div>

    <div class="liq-doc-footer">
        <span>Solicitud #${esc(data.id)} &mdash; Estado rendición: <strong>${esc(data.estadoRendicion)}</strong></span>
        <span>Viáticos ERP &mdash; Documento de solo lectura</span>
    </div>
</div>`;
    }

    function renderTo(containerEl, sol, gastos, opts) {
        containerEl.innerHTML = renderDoc(buildData(sol, gastos, opts));
    }

    function print(containerId) {
        const el = document.getElementById(containerId);
        if (!el) return;
        const root = document.createElement('div');
        root.className = 'liq-print-root';
        root.innerHTML = el.innerHTML;
        document.body.appendChild(root);
        document.body.classList.add('liq-printing');
        window.print();
        document.body.classList.remove('liq-printing');
        document.body.removeChild(root);
    }

    return { buildData, renderDoc, renderTo, print };
})();
</script>
<script>
/**
 * ViaticosUtils — Shared utility functions for all dashboard views.
 * Exposes: escapeHtml, fmtMonto, fmtFecha, showToast, setButtonLoading,
 *          createApiFetch, createApiFetchForm, ModalManager
 */
window.ViaticosUtils = (function () {
    'use strict';

    function escapeHtml(v) {
        return String(v || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
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
            error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
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
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.origText || '';
        }
    }

    function createApiFetch(apiBase, nonce) {
        return async function apiFetch(endpoint, options) {
            options = options || {};
            const merged = Object.assign({ headers: {} }, options);
            merged.headers = Object.assign({ 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, options.headers || {});
            const response = await fetch(apiBase + endpoint, merged);
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || `Error ${response.status}`);
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
            if (!resp.ok || !json.success) throw new Error(json.message || 'Error en la solicitud.');
            return json;
        };
    }

    const ModalManager = {
        open: function (id) {
            const o = document.getElementById(id);
            if (o) { o.classList.add('open'); document.body.style.overflow = 'hidden'; }
        },
        close: function (id) {
            const o = document.getElementById(id);
            if (o) { o.classList.remove('open'); document.body.style.overflow = ''; }
        },
        closeOnOverlayClick: function (id) {
            const self = this;
            const o = document.getElementById(id);
            if (o) o.addEventListener('click', function (e) { if (e.target === o) self.close(id); });
        },
    };

    return { escapeHtml, fmtMonto, fmtFecha, showToast, setButtonLoading, createApiFetch, createApiFetchForm, ModalManager };
})();
</script>
<script>
window.ViaticosDetalleUI = (function () {
    'use strict';

    const gastoUI    = window.ViaticosGastoUI;
    const estadoUI   = window.ViaticosEstadoUI;
    const timelineUI = window.ViaticosTimelineUI;
    const utils      = window.ViaticosUtils;
    const esc        = utils.escapeHtml;
    const fmtMonto   = utils.fmtMonto;
    const fmtFecha   = utils.fmtFecha;

    function buildStepperHtml(sol, estadoSolicitud, estadoRend) {
        const s2 = estadoSolicitud === 'aprobada' ? 'is-done' : (['observada','rechazada'].includes(estadoSolicitud) ? 'is-warning' : 'is-current');
        const s3 = sol.rendicion_finalizada || ['aprobada','rechazada','observada'].includes(estadoRend) ? 'is-done' : (estadoSolicitud === 'aprobada' ? 'is-current' : '');
        const s4 = estadoRend === 'aprobada' ? 'is-done' : (['rechazada','observada'].includes(estadoRend) ? 'is-warning' : (sol.rendicion_finalizada ? 'is-current' : ''));
        const steps = [
            { n: 1, label: 'Solicitud',  state: 'is-done' },
            { n: 2, label: 'Aprobación', state: s2 },
            { n: 3, label: 'Gastos',     state: s3 },
            { n: 4, label: 'Revisión',   state: s4 },
        ];
        return steps.map(function (s) {
            return '<div class="solv-step ' + s.state + '"><span class="solv-step-dot">' + s.n + '</span><span class="solv-step-label">' + s.label + '</span></div>';
        }).join('');
    }

    function buildBannerHtml(estadoSolicitud, estadoRend) {
        if (estadoRend === 'rechazada')      return '<div class="solv-banner is-danger"><strong>Rendición rechazada.</strong> Revisa el historial.</div>';
        if (estadoRend === 'observada')      return '<div class="solv-banner is-warn"><strong>Rendición observada.</strong> Corrige y reenvía.</div>';
        if (estadoRend === 'aprobada')       return '<div class="solv-banner is-ok"><strong>Rendición aprobada.</strong></div>';
        if (estadoSolicitud === 'rechazada') return '<div class="solv-banner is-danger"><strong>Solicitud rechazada.</strong></div>';
        if (estadoSolicitud === 'observada') return '<div class="solv-banner is-warn"><strong>Solicitud observada.</strong> Corrige y reenvía.</div>';
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
        const apiFetch     = opts.apiFetch;
        const canDelete    = !!opts.canDelete;
        const accionesHtml = opts.accionesHtml || '';

        const totalSolicitado = parseFloat(sol.monto) || 0;
        const totalRendido    = gastos.reduce(function (s, g) { return s + (parseFloat(g.importe) || 0); }, 0);
        const saldo           = totalSolicitado - totalRendido;
        const saldoNegativo   = saldo < 0;
        const estadoSolicitud = estadoUI.getSolicitudEstado(sol);
        const estadoRend      = estadoUI.resolveEstadoRendicion({
            estadoSolicitud:     sol.estado,
            estadoRendicion:     sol.estado_rendicion,
            rendicionFinalizada: sol.rendicion_finalizada,
            gastos:              gastos,
        });
        const historial     = Array.isArray(sol.historial) ? sol.historial : [];
        const historialHtml = timelineUI.renderTimeline(historial);
        const accId         = 'solv-gastos-acc-' + sol.id;

        const gastosBodyHtml = gastos.length
            ? (function () {
                var chevron = '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>';
                var rows = gastos.map(function (g, i) {
                    var itemId = 'solv-' + sol.id + '-' + i;
                    var tipoLabel = gastoUI.TIPO_LABEL[g.tipo] || g.tipo || 'Gasto';
                    var summary = gastoUI.summaryText(g);
                    var fields = gastoUI.buildFields(g);
                    var gastoIdAttr = g.id ? String(g.id) : '';
                    var adjuntosHtml = gastoIdAttr
                        ? '<div class="gasto-adj-panel" data-adj-gasto-id="' + gastoIdAttr + '"><div class="gasto-adj-title"><span style="display:flex;align-items:center;gap:6px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="color:#4A5568;"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V6H11v9.5a2.5 2.5 0 005 0V5c0-2.21-1.79-4-4-4S8 2.79 8 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-2.5z"/></svg>Adjuntos</span></div><div class="gasto-adj-list"><span class="gasto-adj-loading">Cargando adjuntos...</span></div></div>'
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
            : '<div class="solv-empty"><p>Sin comprobantes registrados.</p></div>';

        const stepperHtml = buildStepperHtml(sol, estadoSolicitud, estadoRend);
        const bannerHtml  = buildBannerHtml(estadoSolicitud, estadoRend);

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
                        '<h2 class="solv-section-title">Comprobantes</h2>' +
                        '<span class="solv-section-count">' + gastos.length + '</span>' +
                    '</header>' +
                    '<div class="solv-section-body">' + gastosBodyHtml + '</div>' +
                '</section>' +
            '</div>';

        const tableEl = containerEl.querySelector('#' + accId);
        if (tableEl && apiFetch) {
            tableEl.addEventListener('click', function (e) {
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
</script>
</head>

<body>

<!-- Toast notifications -->
<div id="toast-container" role="alert" aria-live="polite"></div>

<!-- ERP Shell -->
<div id="erp-shell">

    <!-- ══ SIDEBAR ══════════════════════════════════════════════ -->
    <aside id="erp-sidebar" role="navigation" aria-label="Menú principal">

        <div class="sidebar-logo">
            <a href="#" class="sidebar-logo-mark">
                <div class="logo-icon">
                    <?php if ( $args['dashboard_role'] === 'admin' ) : ?>
                        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                    <?php else : ?>
                        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h16v12z"/><path d="M6 10h2v2H6zm0 4h8v2H6zm4-4h8v2h-8z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="logo-text">
                    <strong>Viáticos ERP</strong>
                    <span><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador' : 'Fundación Romero'; ?></span>
                </div>
            </a>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-section-label">Menú</p>
            <ul class="sidebar-nav" id="sidebar-nav-items">
                <?php if ( $args['dashboard_role'] === 'admin' ) : ?>
                    <li>
                        <a href="?view=anticipos" class="nav-link active" data-view="view-anticipos" data-route="anticipos" id="nav-anticipos">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 12H7v-2h10v2zm0-4H7V9h10v2zm0-4H7V5h10v2z"/></svg>
                            Anticipos
                        </a>
                    </li>
                    <li>
                        <a href="?view=rendiciones" class="nav-link" data-view="view-rendiciones" data-route="rendiciones" id="nav-rendiciones">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6a9.77 9.77 0 0 1 8.82 6A9.77 9.77 0 0 1 12 18a9.77 9.77 0 0 1-8.82-6A9.77 9.77 0 0 1 12 6zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.2a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6z"/></svg>
                            Rendiciones
                        </a>
                    </li>
                <?php else : ?>
                    <li>
                        <a href="#" id="nav-inicio" class="nav-link active" data-view="view-inicio">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <a href="#" id="nav-solicitudes" class="nav-link" data-view="view-solicitudes">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                            Mis Solicitudes
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar" aria-hidden="true"><?php echo esc_html( $args['user_initials'] ?: 'U' ); ?></div>
                <div class="user-info">
                    <strong class="u-name"><?php echo esc_html( $args['user_name'] ); ?></strong>
                    <span class="u-role"><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador' : 'Colaborador'; ?></span>
                </div>
            </div>
        </div>
    </aside><!-- /#erp-sidebar -->

    <!-- ══ MAIN AREA ════════════════════════════════════════════ -->
    <div id="erp-main">

        <!-- TOPBAR -->
        <header id="erp-topbar">
            <nav class="topbar-breadcrumb" aria-label="Ruta de navegación">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity:.5"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                &rsaquo;
                <span id="topbar-section-name"><?php echo $args['dashboard_role'] === 'admin' ? 'Anticipos' : 'Inicio'; ?></span>
            </nav>
            <div class="topbar-actions">
                <div class="topbar-user-info" aria-label="Usuario autenticado">
                    <strong class="t-name"><?php echo esc_html( $args['user_name'] ); ?></strong>
                    <span class="t-role"><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador de Viáticos' : 'Colaborador'; ?></span>
                    <?php if ( $args['user_dni'] || $args['user_cargo'] || $args['user_area'] ) : ?>
                        <div class="topbar-user-meta">
                            <?php if ( $args['user_dni'] ) : ?>
                                <span class="user-meta-chip"><strong>DNI</strong> <?php echo esc_html( $args['user_dni'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $args['user_cargo'] ) : ?>
                                <span class="user-meta-chip"><strong>Cargo</strong> <?php echo esc_html( $args['user_cargo'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $args['user_area'] ) : ?>
                                <span class="user-meta-chip"><strong>Área</strong> <?php echo esc_html( $args['user_area'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="<?php echo $args['logout_url']; ?>" class="btn-logout" id="btn-logout" title="Cerrar sesión">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    Salir
                </a>
            </div>
        </header><!-- /#erp-topbar -->

        <!-- CONTENT AREA — views are injected here -->
        <main id="erp-content">
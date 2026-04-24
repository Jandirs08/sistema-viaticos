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
    const TIPO_AFECTO_IGV = ['factura', 'boleta'];
    const IGV_RATE = 0.18;

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
    function fmtMontoSigned(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return '—';
        const sign = n < 0 ? '-' : '';
        const abs = Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return sign + 'S/. ' + abs;
    }
    function fmtDocAnticipo(id) {
        const n = parseInt(id, 10);
        return Number.isInteger(n) && n > 0 ? 'ANT-' + String(n).padStart(6, '0') : '—';
    }

    /**
     * calcImporteIgv — split an importe into base + IGV + total.
     * Convention: `importe` is gross (con IGV) for tipos afectos. For tipos no
     * afectos (movilidad / vale_caja / rxh) base = importe and IGV = 0.
     */
    function calcImporteIgv(tipo, importe) {
        const total = parseFloat(importe) || 0;
        if (TIPO_AFECTO_IGV.indexOf(String(tipo)) === -1) {
            return { base: total, igv: 0, total: total };
        }
        const base = total / (1 + IGV_RATE);
        const igv = total - base;
        return { base: base, igv: igv, total: total };
    }

    function resolveSaldoTipo(saldo) {
        if (saldo > 0.005) return 'favor';   // anticipo > rendido → empresa tiene saldo a favor
        if (saldo < -0.005) return 'contra';  // anticipo < rendido → reintegro al colaborador
        return 'neutro';
    }

    function saldoLabelText(tipo) {
        if (tipo === 'favor') return 'SALDO A FAVOR (Empresa)';
        if (tipo === 'contra') return 'SALDO EN CONTRA (Reintegro al colaborador)';
        return 'SALDO';
    }

    /**
     * buildData — normalise all values into a plain object.
     * @param {Object} sol    Solicitud record from the cache
     * @param {Array}  gastos Array of gasto records
     * @param {Object} opts   Optional overrides:
     *   { colaboradorNombre, area, cargo, fechaRendicion, codigoEmpleado, tipoGasto }
     */
    function buildData(sol, gastos, opts) {
        opts = opts || {};
        const gastosArr = Array.isArray(gastos) ? gastos : [];

        const gastosEnriched = gastosArr.map(function (g) {
            const calc = calcImporteIgv(g.tipo, g.importe);
            return Object.assign({}, g, {
                montoBase: calc.base,
                igvMonto: calc.igv,
                importeTotal: calc.total,
            });
        });

        const totalBase = gastosEnriched.reduce(function (s, g) { return s + g.montoBase; }, 0);
        const totalIgv = gastosEnriched.reduce(function (s, g) { return s + g.igvMonto; }, 0);
        const subTotal = gastosEnriched.reduce(function (s, g) { return s + g.importeTotal; }, 0);

        const montoSolicitado = parseFloat(sol.monto) || 0;
        const saldo = montoSolicitado - subTotal;
        const saldoTipo = resolveSaldoTipo(saldo);

        const estadoKey = String(opts.estadoRendicionKey || sol.estado_rendicion || '').toLowerCase();
        const estadoLabel = (window.ViaticosEstadoUI && window.ViaticosEstadoUI.getLabelEstado)
            ? (window.ViaticosEstadoUI.getLabelEstado('rendicion', estadoKey) || estadoKey || '—')
            : (estadoKey || '—');

        return {
            id: sol.id,
            docAnticipo: fmtDocAnticipo(sol.id),
            colaborador: opts.colaboradorNombre || sol.colaborador || '—',
            codigoEmpleado: opts.codigoEmpleado || sol.codigo_empleado || sol.dni || '—',
            dni: sol.dni || '—',
            area: opts.area || sol.area || '—',
            cargo: opts.cargo || sol.cargo || '—',
            tipoGasto: opts.tipoGasto || 'Anticipo',
            motivo: sol.motivo || '—',
            fechaViaje: sol.fecha || sol.fecha_viaje || '',
            fechaRendicion: opts.fechaRendicion || sol.fecha_creacion || '—',
            moneda: 'SOLES',
            ceco: sol.ceco || '—',
            estadoRendicion: estadoKey,
            estadoRendicionLabel: estadoLabel,

            montoSolicitado: montoSolicitado,
            totalBase: totalBase,
            totalIgv: totalIgv,
            subTotal: subTotal,
            totalRendido: subTotal,         // backward-compat alias
            saldo: saldo,            // anticipo − subTotal
            saldoTipo: saldoTipo,        // 'favor' | 'contra' | 'neutro'
            saldoLabel: saldoLabelText(saldoTipo),

            gastos: gastosEnriched,
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

        const rows = data.gastos.map(function (g, i) {
            const tipo = String(g.tipo || '');
            const concepto = tipo === 'movilidad'
                ? [g.destino_movilidad, g.motivo_movilidad].filter(Boolean).join(' · ') || g.concepto || '—'
                : g.concepto || g.razon || '—';
            const ruc = tipo === 'movilidad' ? '—' : (g.ruc || '—');
            const igvCell = (g.igvMonto > 0.005)
                ? esc(fmtMonto(g.igvMonto))
                : '<span class="muted">—</span>';
            return `
    <tr>
        <td class="muted">${i + 1}</td>
        <td>${esc(g.categoria_nombre || TIPO_LABEL[tipo] || tipo || '—')}</td>
        <td>${esc(g.clase_doc || '—')}</td>
        <td>${esc(fmtFecha(g.fecha))}</td>
        <td class="muted">SOLES</td>
        <td>${esc(g.nro || '—')}</td>
        <td>${esc(concepto)}</td>
        <td class="muted">${esc(ruc)}</td>
        <td class="muted">${esc(g.cta_contable || '—')}</td>
        <td class="muted">${esc(g.ceco_oi || '—')}</td>
        <td class="num">${esc(fmtMonto(g.montoBase))}</td>
        <td class="num">${igvCell}</td>
        <td class="num">${esc(fmtMonto(g.importeTotal))}</td>
    </tr>`;
        }).join('');

        const emptyRow = `<tr><td colspan="13" style="text-align:center;padding:32px;color:var(--text-light,#98a2b3);font-style:italic;">Sin gastos registrados.</td></tr>`;

        const saldoCellClass = 'is-saldo-' + (data.saldoTipo || 'neutro');

        const stampKey = (data.estadoRendicion || '').replace(/[^a-z_]/g, '') || 'default';
        const stampClass = 'is-' + stampKey;

        const logoSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';

        return `
<div class="liq-doc" id="liq-documento">
    <div class="liq-doc-header">
        <div class="liq-doc-header-left">
            <div class="liq-doc-header-title">
                <span class="liq-doc-header-title-icon">${logoSvg}</span>
                Liquidación de Rendición de Viáticos
            </div>
            <div class="liq-doc-header-sub">${esc(data.docAnticipo)} &nbsp;·&nbsp; ${esc(data.moneda)} &nbsp;·&nbsp; Emitido ${esc(today)}</div>
        </div>
        <div class="liq-doc-header-meta">
            <strong>Viáticos ERP</strong>
            <span class="liq-doc-stamp ${stampClass}">${esc(data.estadoRendicionLabel || '—')}</span>
        </div>
    </div>

    <div class="liq-doc-info">
        <div class="liq-info-cell">
            <div class="liq-info-label">Nombre</div>
            <div class="liq-info-value">${esc(data.colaborador)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Tipo de Gasto</div>
            <div class="liq-info-value">${esc(data.tipoGasto)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Código Empleado</div>
            <div class="liq-info-value mono">${esc(data.codigoEmpleado)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Área</div>
            <div class="liq-info-value muted">${esc(data.area)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Doc. Anticipo</div>
            <div class="liq-info-value mono">${esc(data.docAnticipo)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Moneda</div>
            <div class="liq-info-value">${esc(data.moneda)}</div>
        </div>
        <div class="liq-info-cell liq-info-motivo">
            <div class="liq-info-label">Motivo</div>
            <div class="liq-info-value muted">${esc(data.motivo)}</div>
        </div>
    </div>

    <div class="liq-doc-table-wrap">
        <table class="liq-doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría del Gasto</th>
                    <th>Clase Doc.</th>
                    <th>Fecha de Documento</th>
                    <th>Moneda</th>
                    <th>N° de Documento</th>
                    <th>Concepto</th>
                    <th>RUC</th>
                    <th>Cta. Contable</th>
                    <th>Ce. Co. / Orden Interna</th>
                    <th class="num">Monto Base</th>
                    <th class="num">IGV</th>
                    <th class="num">Importe Total</th>
                </tr>
            </thead>
            <tbody>${rows || emptyRow}</tbody>
        </table>
    </div>

    <div class="liq-doc-totals">
        <div class="liq-totals-rail">
            <div class="liq-total-row">
                <span class="liq-total-label">Sub Total</span>
                <span class="liq-total-value">${esc(fmtMonto(data.subTotal))}</span>
            </div>
            <div class="liq-total-row ${saldoCellClass}">
                <span class="liq-total-label">${esc(data.saldoLabel)}</span>
                <span class="liq-total-value">${esc(fmtMontoSigned(data.saldo))}</span>
            </div>
            <div class="liq-total-row is-total">
                <span class="liq-total-label">Total (Anticipo)</span>
                <span class="liq-total-value">${esc(fmtMonto(data.montoSolicitado))}</span>
            </div>
        </div>
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

    /* ── XLSX export (ExcelJS, lazy-loaded from CDN) ──────────── */

    const EXCELJS_CDN = 'https://unpkg.com/exceljs@4.4.0/dist/exceljs.min.js';
    let _excelJsPromise = null;
    function loadExcelJS() {
        if (window.ExcelJS) return Promise.resolve(window.ExcelJS);
        if (_excelJsPromise) return _excelJsPromise;
        _excelJsPromise = new Promise(function (resolve, reject) {
            const s = document.createElement('script');
            s.src = EXCELJS_CDN;
            s.async = true;
            s.onload = function () { resolve(window.ExcelJS); };
            s.onerror = function () {
                _excelJsPromise = null;
                reject(new Error('No se pudo cargar ExcelJS. Revisa tu conexión.'));
            };
            document.head.appendChild(s);
        });
        return _excelJsPromise;
    }

    // Palette used in XLSX (ARGB, no alpha → FF prefix)
    const XLSX_COLOR = {
        navy: 'FF17202B',
        paper: 'FFF3F6F9',
        border: 'FFE4E7EC',
        ink: 'FF101828',
        muted: 'FF667085',
        white: 'FFFFFFFF',
        terracotta: 'FFDA5B3E',
        terraInk: 'FF7F2D1D',
        terraMist: 'FFFDE9E3',
        saldoFavorBg: 'FFE8F2EC',
        saldoFavorInk: 'FF1F5736',
        saldoContraBg: 'FFFBE7E7',
        saldoContraInk: 'FF6A1F1F',
        saldoNeutBg: 'FFFCEFD2',
        saldoNeutInk: 'FF6F4B13',
    };

    const THIN = { style: 'thin', color: { argb: XLSX_COLOR.border } };
    const BORDER_ALL = { top: THIN, left: THIN, right: THIN, bottom: THIN };

    const XLSX_COLUMNS = [
        { header: '#', width: 5, num: false },
        { header: 'Categoría del Gasto', width: 24, num: false },
        { header: 'Clase Doc.', width: 14, num: false },
        { header: 'Fecha de Documento', width: 14, num: false },
        { header: 'Moneda', width: 10, num: false },
        { header: 'N° de Documento', width: 16, num: false },
        { header: 'Concepto', width: 36, num: false },
        { header: 'RUC', width: 14, num: false },
        { header: 'Cta. Contable', width: 14, num: false },
        { header: 'Ce. Co. / Orden Interna', width: 18, num: false },
        { header: 'Monto Base', width: 13, num: true },
        { header: 'IGV', width: 11, num: true },
        { header: 'Importe Total', width: 15, num: true },
    ];
    const COL_COUNT = XLSX_COLUMNS.length;       // 13
    const IMPORTE_COL = COL_COUNT;                 // M
    const TOTALS_LABEL_START_COL = 10;                // J
    const TOTALS_LABEL_END_COL = COL_COUNT - 1;     // L (merge J:L)

    function colLetter(n) {
        let s = '';
        while (n > 0) { const r = (n - 1) % 26; s = String.fromCharCode(65 + r) + s; n = Math.floor((n - 1) / 26); }
        return s;
    }

    function saldoXlsxFills(tipo) {
        if (tipo === 'favor') return { bg: XLSX_COLOR.saldoFavorBg, ink: XLSX_COLOR.saldoFavorInk };
        if (tipo === 'contra') return { bg: XLSX_COLOR.saldoContraBg, ink: XLSX_COLOR.saldoContraInk };
        return { bg: XLSX_COLOR.saldoNeutBg, ink: XLSX_COLOR.saldoNeutInk };
    }

    function writeXlsxCell(cell, value, style) {
        cell.value = value;
        if (style) cell.style = style;
    }

    /**
     * Build a factura-style XLSX workbook from a liquidación `data` object.
     */
    async function buildXlsxWorkbook(data, logoUrl) {
        const ExcelJS = await loadExcelJS();
        const today = new Date().toLocaleDateString('es-PE', {
            day: '2-digit', month: 'long', year: 'numeric',
        });

        const wb = new ExcelJS.Workbook();
        wb.creator = 'Viáticos ERP';
        wb.created = new Date();
        const ws = wb.addWorksheet('Liquidación', {
            views: [{ showGridLines: false }],
            pageSetup: { orientation: 'landscape', fitToPage: true, fitToWidth: 1, fitToHeight: 0 },
        });

        // Column widths
        XLSX_COLUMNS.forEach(function (c, i) { ws.getColumn(i + 1).width = c.width; });

        const lastCol = colLetter(COL_COUNT);
        let r = 1;

        // ── Title band (row 1) ────────────────────────────────
        // Logo occupies last 4 cols (J-M) with light bg; title takes A-I
        const LOGO_COL_START = COL_COUNT - 3; // col index 10 (J), 0-based = 9
        const titleEndCol = colLetter(LOGO_COL_START - 1); // I
        const logoStartCol = colLetter(LOGO_COL_START);     // J

        ws.mergeCells(`A${r}:${titleEndCol}${r}`);
        writeXlsxCell(ws.getCell(`A${r}`), 'Liquidación de Rendición de Viáticos', {
            font: { name: 'Calibri', size: 18, bold: true, color: { argb: XLSX_COLOR.white } },
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.navy } },
            alignment: { vertical: 'middle', horizontal: 'left', indent: 1 },
        });

        // Logo area: light smoke background so logo is readable on any theme
        ws.mergeCells(`${logoStartCol}${r}:${lastCol}${r}`);
        writeXlsxCell(ws.getCell(`${logoStartCol}${r}`), '', {
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF5F6F8' } },
            alignment: { vertical: 'middle', horizontal: 'center' },
        });

        ws.getRow(r).height = 50;

        // Embed logo — use ext (px) for correct aspect ratio
        if (logoUrl) {
            try {
                const resp = await fetch(logoUrl);
                const buf = await resp.arrayBuffer();
                const imgExt = logoUrl.toLowerCase().endsWith('.png') ? 'png' : 'jpeg';
                const imgId = wb.addImage({ buffer: buf, extension: imgExt });
                ws.addImage(imgId, {
                    tl: { col: LOGO_COL_START - 1 + 0.15, row: 0.12 },
                    ext: { width: 220, height: 50 },
                    editAs: 'oneCell',
                });
            } catch (_) { /* logo fetch failed — still export */ }
        }

        r++;

        // ── Sub-title band (row 2) ────────────────────────────
        ws.mergeCells(`A${r}:${lastCol}${r}`);
        const subLine = `${data.docAnticipo}  ·  ${data.moneda}  ·  Emitido ${today}  ·  ${data.estadoRendicionLabel || '—'}`;
        writeXlsxCell(ws.getCell(`A${r}`), subLine, {
            font: { name: 'Calibri', size: 10, color: { argb: 'FFC9CED6' } },
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.navy } },
            alignment: { vertical: 'middle', horizontal: 'left', indent: 1 },
            border: { bottom: { style: 'medium', color: { argb: XLSX_COLOR.terracotta } } },
        });
        ws.getRow(r).height = 20;
        r++;

        // Spacer
        ws.getRow(r).height = 8; r++;

        // ── Info block (rows) ─────────────────────────────────
        const infoRows = [
            ['Nombre', data.colaborador],
            ['Tipo de Gasto', data.tipoGasto],
            ['Código Empleado', data.codigoEmpleado],
            ['Área', data.area],
            ['Doc. Anticipo', data.docAnticipo],
            ['Moneda', data.moneda],
            ['Motivo', data.motivo],
        ];
        const infoLabelStyle = {
            font: { name: 'Calibri', size: 9, bold: true, color: { argb: XLSX_COLOR.muted } },
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.paper } },
            alignment: { vertical: 'middle', horizontal: 'left', indent: 1 },
            border: BORDER_ALL,
        };
        const infoValueStyle = {
            font: { name: 'Calibri', size: 11, color: { argb: XLSX_COLOR.ink } },
            alignment: { vertical: 'middle', horizontal: 'left', indent: 1, wrapText: true },
            border: BORDER_ALL,
        };
        infoRows.forEach(function (row) {
            ws.mergeCells(`A${r}:C${r}`);
            ws.mergeCells(`D${r}:${lastCol}${r}`);
            writeXlsxCell(ws.getCell(`A${r}`), row[0], infoLabelStyle);
            writeXlsxCell(ws.getCell(`D${r}`), row[1] || '—', infoValueStyle);
            ws.getRow(r).height = 20;
            r++;
        });

        // Spacer
        ws.getRow(r).height = 10; r++;

        // ── Gastos table header (terracotta) ──────────────────
        const headerRow = ws.getRow(r);
        const headerStyleBase = {
            font: { name: 'Calibri', size: 10, bold: true, color: { argb: XLSX_COLOR.white } },
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.terracotta } },
            alignment: { vertical: 'middle', wrapText: true },
            border: BORDER_ALL,
        };
        XLSX_COLUMNS.forEach(function (c, i) {
            const cell = headerRow.getCell(i + 1);
            cell.value = c.header;
            cell.style = Object.assign({}, headerStyleBase, {
                alignment: Object.assign({}, headerStyleBase.alignment, {
                    horizontal: c.num ? 'right' : 'left',
                    indent: c.num ? 0 : 1,
                }),
            });
        });
        headerRow.height = 32;
        r++;

        // ── Gastos rows ──────────────────────────────────────
        const bodyBaseStyle = {
            font: { name: 'Calibri', size: 10, color: { argb: XLSX_COLOR.ink } },
            alignment: { vertical: 'middle', wrapText: true },
            border: BORDER_ALL,
        };
        const importeTotalStyle = {
            font: { name: 'Calibri', size: 10, bold: true, color: { argb: XLSX_COLOR.terraInk } },
            fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.terraMist } },
            alignment: { vertical: 'middle', horizontal: 'right' },
            border: BORDER_ALL,
            numFmt: '#,##0.00',
        };

        if (!data.gastos.length) {
            ws.mergeCells(`A${r}:${lastCol}${r}`);
            writeXlsxCell(ws.getCell(`A${r}`), 'Sin gastos registrados.', {
                font: { name: 'Calibri', size: 10, italic: true, color: { argb: 'FF98A2B3' } },
                alignment: { vertical: 'middle', horizontal: 'center' },
                border: BORDER_ALL,
            });
            ws.getRow(r).height = 24;
            r++;
        } else {
            data.gastos.forEach(function (g, idx) {
                const tipo = String(g.tipo || '');
                const concepto = tipo === 'movilidad'
                    ? [g.destino_movilidad, g.motivo_movilidad].filter(Boolean).join(' · ') || g.concepto || ''
                    : g.concepto || g.razon || '';
                const ruc = tipo === 'movilidad' ? '' : (g.ruc || '');

                const row = ws.getRow(r);
                row.getCell(1).value = idx + 1;
                row.getCell(2).value = g.categoria_nombre || TIPO_LABEL[tipo] || tipo || '';
                row.getCell(3).value = g.clase_doc || '';
                row.getCell(4).value = g.fecha || '';
                row.getCell(5).value = 'SOLES';
                row.getCell(6).value = g.nro || '';
                row.getCell(7).value = concepto;
                row.getCell(8).value = ruc;
                row.getCell(9).value = g.cta_contable || '';
                row.getCell(10).value = g.ceco_oi || '';
                row.getCell(11).value = Number((g.montoBase || 0).toFixed(2));
                row.getCell(12).value = Number((g.igvMonto || 0).toFixed(2));
                row.getCell(13).value = Number((g.importeTotal || 0).toFixed(2));

                for (let c = 1; c <= COL_COUNT; c++) {
                    const cell = row.getCell(c);
                    const isNum = XLSX_COLUMNS[c - 1].num;
                    if (c === IMPORTE_COL) {
                        cell.style = importeTotalStyle;
                    } else {
                        cell.style = Object.assign({}, bodyBaseStyle, {
                            alignment: Object.assign({}, bodyBaseStyle.alignment, {
                                horizontal: isNum ? 'right' : 'left',
                                indent: isNum ? 0 : 1,
                            }),
                            numFmt: isNum ? '#,##0.00' : undefined,
                        });
                    }
                }
                row.height = 22;
                r++;
            });
        }

        // Spacer
        ws.getRow(r).height = 8; r++;

        // ── Totals — right-aligned rail (cols J:L label, M value) ──
        const totalsStartColLetter = colLetter(TOTALS_LABEL_START_COL);
        const totalsEndColLetter = colLetter(TOTALS_LABEL_END_COL);
        const valueColLetter = lastCol;

        const totalLabelBase = {
            font: { name: 'Calibri', size: 10, bold: true, color: { argb: XLSX_COLOR.muted } },
            alignment: { vertical: 'middle', horizontal: 'right', indent: 1 },
            border: BORDER_ALL,
        };
        const totalValueBase = {
            font: { name: 'Calibri', size: 12, bold: true, color: { argb: XLSX_COLOR.ink } },
            alignment: { vertical: 'middle', horizontal: 'right' },
            border: BORDER_ALL,
            numFmt: '#,##0.00',
        };

        const saldoFills = saldoXlsxFills(data.saldoTipo);

        const totalsSpec = [
            {
                label: 'Sub Total',
                value: data.subTotal,
                labelStyle: totalLabelBase,
                valueStyle: totalValueBase,
            },
            {
                label: data.saldoLabel,
                value: data.saldo,
                labelStyle: Object.assign({}, totalLabelBase, {
                    fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: saldoFills.bg } },
                    font: { name: 'Calibri', size: 10, bold: true, color: { argb: saldoFills.ink } },
                }),
                valueStyle: Object.assign({}, totalValueBase, {
                    fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: saldoFills.bg } },
                    font: { name: 'Calibri', size: 12, bold: true, color: { argb: saldoFills.ink } },
                }),
            },
            {
                label: 'Total (Anticipo)',
                value: data.montoSolicitado,
                labelStyle: Object.assign({}, totalLabelBase, {
                    fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.navy } },
                    font: { name: 'Calibri', size: 11, bold: true, color: { argb: XLSX_COLOR.white } },
                }),
                valueStyle: Object.assign({}, totalValueBase, {
                    fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: XLSX_COLOR.navy } },
                    font: { name: 'Calibri', size: 14, bold: true, color: { argb: XLSX_COLOR.white } },
                }),
            },
        ];

        totalsSpec.forEach(function (t) {
            ws.mergeCells(`${totalsStartColLetter}${r}:${totalsEndColLetter}${r}`);
            writeXlsxCell(ws.getCell(`${totalsStartColLetter}${r}`), t.label, t.labelStyle);
            writeXlsxCell(ws.getCell(`${valueColLetter}${r}`),
                Number((parseFloat(t.value) || 0).toFixed(2)),
                t.valueStyle);
            ws.getRow(r).height = 24;
            r++;
        });

        return wb;
    }

    async function exportXlsx(data, filename, logoUrl) {
        const wb = await buildXlsxWorkbook(data, logoUrl);
        const buf = await wb.xlsx.writeBuffer();
        const blob = new Blob([buf], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        const fname = filename || ('liquidacion-' + (data.docAnticipo || data.id || 'doc') + '.xlsx');
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = fname;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    }

    async function exportXlsxFrom(sol, gastos, opts) {
        return exportXlsx(buildData(sol, gastos, opts));
    }

    return { buildData, renderDoc, renderTo, print, exportXlsx, exportXlsxFrom };
})();

/**
 * ViaticosBootstrap — lee los <script type="application/json"> que el header
 * inyecta y expone los globals que el resto de los módulos esperan. Permite
 * mantener una CSP estricta (script-src 'self') sin perder la inyección PHP.
 */
(function () {
    'use strict';

    function readJson(id, fallback) {
        const el = document.getElementById(id);
        if (!el) return fallback;
        try {
            return JSON.parse(el.textContent || el.innerText || '');
        } catch (e) {
            return fallback;
        }
    }

    window.ViaticosCategoriasGasto = readJson('viaticos-categorias-data', []);
    window.ViaticosConfigData      = readJson('viaticos-config-data', {});
    window.ViaticosConfig          = readJson('viaticos-runtime-config', {});
})();

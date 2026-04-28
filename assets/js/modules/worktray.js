window.ViaticosWorktray = (function () {
    'use strict';

    var EMPTY_ICON = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>';

    function gel(id) { return id ? document.getElementById(id) : null; }

    function create(config) {
        // sortState: si config.defaultSort = {key, dir, type}, arranca con esos valores.
        // Cuando el user clickea otro th, se sobrescribe; cuando vuelve a la misma key,
        // alterna asc/desc respetando el default.
        var sortState  = config.defaultSort
            ? { key: config.defaultSort.key, dir: config.defaultSort.dir || 'desc', type: config.defaultSort.type || 'str' }
            : null;
        var page       = 1;
        var chipFilter = '';
        var search     = '';
        var pageSize   = config.defaultPageSize || 10;
        var _cache     = [];

        var colspan         = config.colspan         || 6;
        var emptyText       = config.emptyText       || 'No se encontraron registros.';
        var emptySearchText = config.emptySearchText || 'No se encontraron resultados.';

        function applySort(rows) {
            if (!sortState || !sortState.key) return rows;
            var key = sortState.key, dir = sortState.dir, type = sortState.type;
            return rows.slice().sort(function (a, b) {
                var av = a[key], bv = b[key];
                if (type === 'num') { av = parseFloat(av) || 0; bv = parseFloat(bv) || 0; }
                else { av = String(av || '').toLowerCase(); bv = String(bv || '').toLowerCase(); }
                if (av < bv) return dir === 'asc' ? -1 : 1;
                if (av > bv) return dir === 'asc' ? 1 : -1;
                return 0;
            });
        }

        function applyDateFilter(rows) {
            var fromEl = gel(config.dateFromId), toEl = gel(config.dateToId);
            var from = fromEl ? fromEl.value : '';
            var to   = toEl   ? toEl.value   : '';
            if (!from && !to) return rows;
            return rows.filter(function (row) {
                var f = row.fecha || '';
                if (from && f < from) return false;
                if (to   && f > to  ) return false;
                return true;
            });
        }

        function hasActiveFilters() {
            if (chipFilter || search.trim()) return true;
            var fromEl = gel(config.dateFromId), toEl = gel(config.dateToId);
            return !!(fromEl && fromEl.value) || !!(toEl && toEl.value);
        }

        function updateClearButton() {
            var btn = gel(config.clearBtnId);
            if (btn) btn.style.display = hasActiveFilters() ? '' : 'none';
        }

        function renderPagination(total) {
            var pag = gel(config.paginationId);
            if (!pag) return;
            var totalPages = Math.ceil(total / pageSize);
            if (totalPages <= 1) { pag.innerHTML = ''; return; }
            var start = (page - 1) * pageSize;
            var end   = Math.min(page * pageSize, total);
            pag.innerHTML =
                '<span class="tbl-pag-info">' + (start + 1) + '–' + end + ' de ' + total + '</span>' +
                '<div class="tbl-pag-btns">' +
                '<button class="btn btn-ghost btn-sm js-wt-prev"' + (page <= 1 ? ' disabled' : '') + '>← Anterior</button>' +
                '<button class="btn btn-ghost btn-sm js-wt-next"' + (page >= totalPages ? ' disabled' : '') + '>Siguiente →</button>' +
                '</div>';
            pag.querySelector('.js-wt-prev').addEventListener('click', function () {
                page = Math.max(1, page - 1);
                render(_cache);
            });
            pag.querySelector('.js-wt-next').addEventListener('click', function () {
                page = Math.min(totalPages, page + 1);
                render(_cache);
            });
        }

        function render(data) {
            _cache = data || [];
            updateClearButton();
            var tbody   = gel(config.tbodyId);
            var counter = gel(config.counterId);
            if (!tbody) return;

            var rows = config.filter ? _cache.filter(config.filter) : _cache.slice();

            if (chipFilter) {
                rows = rows.filter(function (row) {
                    return config.getChipEstado ? config.getChipEstado(row) === chipFilter : false;
                });
            }
            if (search) {
                var q = search.toLowerCase();
                if (config.searchFilter) {
                    rows = rows.filter(function (row) { return config.searchFilter(row, q); });
                } else {
                    rows = rows.filter(function (row) {
                        return String(row.id).includes(q) ||
                            (row.colaborador || '').toLowerCase().includes(q) ||
                            (row.ceco        || '').toLowerCase().includes(q) ||
                            (row.motivo      || '').toLowerCase().includes(q);
                    });
                }
            }
            rows = applyDateFilter(rows);
            rows = applySort(rows);

            if (counter) counter.textContent = rows.length + ' resultado' + (rows.length !== 1 ? 's' : '');

            if (!rows.length) {
                tbody.innerHTML =
                    '<tr><td colspan="' + colspan + '"><div class="table-empty">' +
                    EMPTY_ICON + '<p>' + (hasActiveFilters() ? emptySearchText : emptyText) + '</p>' +
                    '</div></td></tr>';
                var pagEl = gel(config.paginationId);
                if (pagEl) pagEl.innerHTML = '';
                return;
            }

            var start    = (page - 1) * pageSize;
            var pageRows = rows.slice(start, start + pageSize);
            tbody.innerHTML = pageRows.map(config.renderRow).join('');

            if (config.onAfterRender) config.onAfterRender(tbody, pageRows, rows);
            renderPagination(rows.length);
        }

        function setLoading() {
            var tbody = gel(config.tbodyId);
            if (tbody && window.ViaticosUtils) {
                window.ViaticosUtils.renderTableSkeleton(tbody, colspan);
            }
            var pagEl = gel(config.paginationId);
            if (pagEl) pagEl.innerHTML = '';
        }

        function setError(message) {
            var safeMsg = window.ViaticosUtils ? window.ViaticosUtils.escapeHtml(message) : message;
            var tbody = gel(config.tbodyId);
            if (tbody) {
                tbody.innerHTML =
                    '<tr><td colspan="' + colspan + '"><div class="table-empty"><p>Error: ' + safeMsg + '</p></div></td></tr>';
            }
            var pagEl = gel(config.paginationId);
            if (pagEl) pagEl.innerHTML = '';
        }

        function updateChipCounts(data) {
            var rows  = config.filter ? (data || []).filter(config.filter) : (data || []);
            var group = gel(config.chipGroupId);
            if (!group) return;
            group.querySelectorAll('.tbl-chip[data-filter]').forEach(function (chip) {
                var f       = chip.dataset.filter;
                var countEl = chip.querySelector('.tbl-chip-count');
                if (!countEl) return;
                countEl.textContent = f
                    ? rows.filter(function (row) { return config.getChipEstado && config.getChipEstado(row) === f; }).length
                    : rows.length;
            });
        }

        function clearFilters() {
            chipFilter = ''; search = ''; page = 1;
            var group = gel(config.chipGroupId);
            if (group) {
                group.querySelectorAll('.tbl-chip').forEach(function (c) { c.classList.remove('is-active'); });
                var first = group.querySelector('.tbl-chip[data-filter=""]');
                if (first) first.classList.add('is-active');
            }
            var searchEl = gel(config.searchId);    if (searchEl) searchEl.value = '';
            var fromEl   = gel(config.dateFromId);  if (fromEl)   fromEl.value   = '';
            var toEl     = gel(config.dateToId);    if (toEl)     toEl.value     = '';
            var strip    = gel(config.datesStripId); if (strip) strip.classList.remove('is-open');
            var fchip    = gel(config.fechaChipId);  if (fchip) fchip.classList.remove('is-active');
        }

        function setPage(n) { page = n; }
        function getSearch() { return search; }

        function initInteractions(getCache) {
            var searchEl = gel(config.searchId);
            if (searchEl) {
                searchEl.addEventListener('input', function () {
                    search = searchEl.value;
                    page = 1;
                    render(getCache());
                });
            }

            var group = gel(config.chipGroupId);
            if (group) {
                group.querySelectorAll('.tbl-chip:not(.tbl-chip-fecha)').forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        group.querySelectorAll('.tbl-chip:not(.tbl-chip-fecha)').forEach(function (c) { c.classList.remove('is-active'); });
                        chip.classList.add('is-active');
                        chipFilter = chip.dataset.filter || '';
                        page = 1;
                        render(getCache());
                    });
                });
            }

            var fchip = gel(config.fechaChipId);
            var strip = gel(config.datesStripId);
            if (fchip && strip) {
                fchip.addEventListener('click', function () {
                    var opening = !strip.classList.contains('is-open');
                    strip.classList.toggle('is-open');
                    if (opening) {
                        fchip.classList.add('is-active');
                        var desde = gel(config.dateFromId);
                        if (desde) desde.focus();
                    } else {
                        var fromEl = gel(config.dateFromId), toEl = gel(config.dateToId);
                        fchip.classList.toggle('is-active', !!(fromEl && fromEl.value) || !!(toEl && toEl.value));
                    }
                });
            }

            [config.dateFromId, config.dateToId].forEach(function (dateId) {
                var dateEl = gel(dateId);
                if (!dateEl) return;
                dateEl.addEventListener('change', function () {
                    var fromEl = gel(config.dateFromId), toEl = gel(config.dateToId);
                    var hasDate = !!(fromEl && fromEl.value) || !!(toEl && toEl.value);
                    if (fchip) fchip.classList.toggle('is-active', !!(hasDate || (strip && strip.classList.contains('is-open'))));
                    page = 1;
                    render(getCache());
                });
            });

            var clearBtn = gel(config.clearBtnId);
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    clearFilters();
                    render(getCache());
                });
            }

            var pageSizeEl = gel(config.pageSizeId);
            if (pageSizeEl) {
                pageSizeEl.addEventListener('change', function () {
                    pageSize = parseInt(pageSizeEl.value, 10);
                    page = 1;
                    render(getCache());
                });
            }

            var sectionEl = config.sortSectionId ? document.getElementById(config.sortSectionId) : null;
            if (sectionEl) {
                // Marca visualmente el sort default si existe.
                if (sortState && sortState.key) {
                    var defTh = sectionEl.querySelector('thead th[data-sort-key="' + sortState.key + '"]');
                    if (defTh) {
                        defTh.classList.add(sortState.dir === 'asc' ? 'sort-asc' : 'sort-desc');
                        defTh.setAttribute('aria-sort', sortState.dir === 'asc' ? 'ascending' : 'descending');
                    }
                }
                sectionEl.querySelectorAll('thead th[data-sort-key]').forEach(function (th) {
                    th.addEventListener('click', function () {
                        var key  = th.dataset.sortKey;
                        var type = th.dataset.sortType || 'str';
                        var newDir = (sortState && sortState.key === key && sortState.dir === 'asc') ? 'desc' : 'asc';
                        sortState = { key: key, dir: newDir, type: type };
                        sectionEl.querySelectorAll('thead th').forEach(function (h) { h.classList.remove('sort-asc', 'sort-desc'); });
                        th.classList.add(newDir === 'asc' ? 'sort-asc' : 'sort-desc');
                        sectionEl.querySelectorAll('thead th[aria-sort]').forEach(function (h) { h.setAttribute('aria-sort', 'none'); });
                        th.setAttribute('aria-sort', newDir === 'asc' ? 'ascending' : 'descending');
                        page = 1;
                        render(getCache());
                    });
                });
            }
        }

        return {
            render:           render,
            setLoading:       setLoading,
            setError:         setError,
            clearFilters:     clearFilters,
            setPage:          setPage,
            getSearch:        getSearch,
            updateChipCounts: updateChipCounts,
            initInteractions: initInteractions,
        };
    }

    return { create: create };
})();

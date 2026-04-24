/**
 * ViaticosRouter — URL-based SPA router shared by all dashboard views.
 * Usage: ViaticosRouter.create({ routes, defaultRoute, onNavigate })
 * Returns: { getCurrentRoute, buildUrl, updateHistory, updateLinks, navigateTo, init }
 */
window.ViaticosRouter = (function () {
    'use strict';

    function create(config) {
        var routes = config.routes;
        var defaultRoute = config.defaultRoute;
        var onNavigate = config.onNavigate;

        function normalizeName(name) {
            return routes[name] ? name : defaultRoute;
        }

        function normalizeId(val) {
            var n = parseInt(val, 10);
            return Number.isInteger(n) && n > 0 ? n : null;
        }

        function buildRouteObj(name, id, from) {
            name = normalizeName(name);
            var cfg = routes[name];
            return { name: name, viewId: cfg.viewId, breadcrumb: cfg.breadcrumb, id: id || null, from: from || null };
        }

        function getCurrentRoute() {
            var params = new URLSearchParams(window.location.search);
            var name = normalizeName(params.get('view'));
            var cfg = routes[name];
            var id = normalizeId(params.get('id'));
            var fromParam = params.get('from');
            var from;
            if (fromParam && routes[fromParam]) {
                from = (!cfg.validFrom || cfg.validFrom.indexOf(fromParam) !== -1) ? fromParam : defaultRoute;
            } else {
                from = defaultRoute;
            }

            if (cfg.requiresId && !id) {
                return buildRouteObj(from, null, null);
            }

            return buildRouteObj(name, id, cfg.requiresId ? from : null);
        }

        function buildUrl(route) {
            var url = new URL(window.location.href);
            var name = normalizeName(route.name);
            url.searchParams.set('view', name);
            if (routes[name].requiresId && route.id) {
                url.searchParams.set('id', String(route.id));
                if (route.from) { url.searchParams.set('from', String(route.from)); }
                else { url.searchParams.delete('from'); }
            } else {
                url.searchParams.delete('id');
                url.searchParams.delete('from');
            }
            return url.pathname + url.search;
        }

        function updateHistory(route, mode) {
            var url = buildUrl(route);
            if (mode === 'replace') { history.replaceState(route, '', url); }
            else if (mode === 'push') { history.pushState(route, '', url); }
        }

        function updateLinks() {
            document.querySelectorAll('[data-route]').forEach(function (el) {
                el.setAttribute('href', buildUrl({
                    name: el.dataset.route,
                    id: el.dataset.routeId || null,
                    from: el.dataset.routeFrom || null,
                }));
            });
        }

        async function navigateTo(name, opts) {
            opts = opts || {};
            var routeName = normalizeName(name);
            updateHistory({ name: routeName, id: opts.id || null, from: opts.from || null }, opts.historyMode || 'push');
            await onNavigate(getCurrentRoute());
        }

        async function init() {
            window.addEventListener('popstate', function () { onNavigate(getCurrentRoute()); });
            updateLinks();
            var route = getCurrentRoute();
            updateHistory(route, 'replace');
            await onNavigate(route);
        }

        return { getCurrentRoute: getCurrentRoute, buildUrl: buildUrl, updateHistory: updateHistory, updateLinks: updateLinks, navigateTo: navigateTo, init: init };
    }

    return { create: create };
})();

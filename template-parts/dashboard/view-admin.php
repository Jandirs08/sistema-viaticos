<?php
/**
 * Template Part: Dashboard - Vista Administrador
 *
 * Admin UX separado por etapas de negocio: anticipos y rendiciones.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'rest_nonce' => '',
        'api_base'   => '',
    ]
);
?>

<section id="view-anticipos" class="erp-view active">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Anticipos</h1>
        </div>
        <button class="btn btn-ghost btn-sm js-btn-refrescar" data-view="view-anticipos" title="Actualizar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
            Actualizar
        </button>
    </div>

    <div class="card">
        <div class="tbl-toolbar">
            <div class="tbl-toolbar-search">
                <svg class="tbl-toolbar-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="search" id="search-anticipos" class="search-input" placeholder="Buscar..." autocomplete="off">
            </div>
            <div class="tbl-chips" id="chips-anticipos">
                <button class="tbl-chip is-active" data-filter="">Todos <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="pendiente">Pendiente <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="observada">Observada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="rechazada">Rechazada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip tbl-chip-fecha" id="fecha-chip-anticipos" title="Filtrar por fecha de viaje">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    Fecha
                </button>
            </div>
            <div class="tbl-toolbar-right">
                <span class="tbl-count-label" id="tbl-counter-anticipos"></span>
                <button class="tbl-clear-btn" id="clear-anticipos" style="display:none;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    Limpiar
                </button>
                <div class="tbl-page-size-wrap">
                    Mostrar
                    <select class="tbl-page-size" id="page-size-anticipos">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="tbl-toolbar-dates" id="dates-strip-anticipos">
                <span class="tbl-dates-label">Viaje entre</span>
                <input type="date" class="tbl-date-input" id="fecha-desde-anticipos">
                <span class="tbl-dates-sep">y</span>
                <input type="date" class="tbl-date-input" id="fecha-hasta-anticipos">
            </div>
        </div>
        <div class="table-wrap">
            <table class="erp-table" aria-label="Bandeja de anticipos">
                <thead>
                    <tr>
                        <th data-sort-key="id" data-sort-type="num" class="sortable"><span class="th-inner">ID<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>Solicitud</th>
                        <th data-sort-key="fecha" data-sort-type="date" class="sortable"><span class="th-inner">Fecha viaje<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th data-sort-key="monto" data-sort-type="num" class="sortable"><span class="th-inner">Monto<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>Estado</th>
                        <th>AcciÃƒÂ³n</th>
                    </tr>
                </thead>
                <tbody id="anticipos-tbody">
                </tbody>
            </table>
        </div>
        <div class="tbl-pagination" id="tbl-pag-anticipos"></div>
    </div>
</section>

<section id="view-rendiciones" class="erp-view">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Rendiciones</h1>
        </div>
        <button class="btn btn-ghost btn-sm js-btn-refrescar" data-view="view-rendiciones" title="Actualizar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
            Actualizar
        </button>
    </div>

    <div class="card">
        <div class="tbl-toolbar">
            <div class="tbl-toolbar-search">
                <svg class="tbl-toolbar-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="search" id="search-rendiciones" class="search-input" placeholder="Buscar..." autocomplete="off">
            </div>
            <div class="tbl-chips" id="chips-rendiciones">
                <button class="tbl-chip is-active" data-filter="">Todos <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="no_iniciada">Sin iniciar <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="en_proceso">En proceso <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="en_revision">En revisiÃƒÂ³n <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="observada">Observada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="aprobada">Aprobada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="rechazada">Rechazada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip tbl-chip-fecha" id="fecha-chip-rendiciones" title="Filtrar por fecha de viaje">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    Fecha
                </button>
            </div>
            <div class="tbl-toolbar-right">
                <span class="tbl-count-label" id="tbl-counter-rendiciones"></span>
                <button class="tbl-clear-btn" id="clear-rendiciones" style="display:none;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    Limpiar
                </button>
                <div class="tbl-page-size-wrap">
                    Mostrar
                    <select class="tbl-page-size" id="page-size-rendiciones">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="tbl-toolbar-dates" id="dates-strip-rendiciones">
                <span class="tbl-dates-label">Viaje entre</span>
                <input type="date" class="tbl-date-input" id="fecha-desde-rendiciones">
                <span class="tbl-dates-sep">y</span>
                <input type="date" class="tbl-date-input" id="fecha-hasta-rendiciones">
            </div>
        </div>
        <div class="table-wrap">
            <table class="erp-table" aria-label="Bandeja de rendiciones">
                <thead>
                    <tr>
                        <th data-sort-key="id" data-sort-type="num" class="sortable"><span class="th-inner">ID<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>Solicitud</th>
                        <th data-sort-key="fecha" data-sort-type="date" class="sortable"><span class="th-inner">Fecha viaje<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th data-sort-key="monto" data-sort-type="num" class="sortable"><span class="th-inner">Monto<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>Estado</th>
                        <th>AcciÃƒÂ³n</th>
                    </tr>
                </thead>
                <tbody id="rendiciones-tbody">
                </tbody>
            </table>
        </div>
        <div class="tbl-pagination" id="tbl-pag-rendiciones"></div>
    </div>
</section>

<section id="view-solicitud-detalle" class="erp-view">
    <div class="rd-topbar">
        <button class="rd-back-btn" id="btn-volver-lista">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            <span id="btn-volver-lista-texto">Volver a Anticipos</span>
        </button>
    </div>

    <div id="solicitud-detalle-content">
        <div class="table-loading" style="padding:40px;"><div class="spinner"></div>Cargando detalleÃ¢â‚¬Â¦</div>
    </div>
</section>

<!-- MODAL: historial admin -->
<div class="modal-overlay" id="modal-admin-historial" role="dialog" aria-modal="true" aria-labelledby="modal-admin-historial-titulo">
    <div class="modal modal-lg solv-history-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-admin-historial-titulo">Historial del expediente</h2>
                <p id="admin-historial-subtitulo">Seguimiento del expediente.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-admin-historial" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="solv-history-meta" id="admin-historial-meta"></div>
        <div class="solv-history-body" id="admin-historial-body">
            <div class="table-loading"><div class="spinner"></div> Cargando historialÃ¢â‚¬Â¦</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-admin-historial">Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL: liquidaciÃƒÂ³n admin -->
<div class="modal-overlay" id="modal-admin-liquidacion" role="dialog" aria-modal="true" aria-labelledby="modal-admin-liq-titulo">
    <div class="modal modal-xl liq-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-admin-liq-titulo">LiquidaciÃƒÂ³n de RendiciÃƒÂ³n</h2>
                <p>Documento de solo lectura</p>
            </div>
            <button class="modal-close" id="btn-cerrar-admin-liq" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body" id="admin-liq-container"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="btn-excel-admin-liq">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zM8 16l2.5-3.5L13 16h-5zm8 0h-2l-2.5-3.5L14 9h2l-2.5 3.5L16 16z"/></svg>
                Exportar Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="btn-imprimir-admin-liq">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                Imprimir
            </button>
            <button type="button" class="btn btn-secondary" id="btn-cancelar-admin-liq">Cerrar</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-solicitud-titulo">
    <div class="modal" style="max-width:720px;">
        <div class="modal-header">
            <div>
                <h2 id="modal-solicitud-titulo">Detalle de Solicitud</h2>
                <p id="modal-solicitud-subtitulo" style="margin-top:2px;"></p>
            </div>
            <button class="modal-close" id="btn-cerrar-solicitud-modal" aria-label="Cerrar"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
        </div>

        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="di-label">Monto solicitado</div><div class="di-value" id="modal-det-monto">-</div></div>
                <div class="detail-item"><div class="di-label">Fecha del viaje</div><div class="di-value" id="modal-det-fecha">-</div></div>
                <div class="detail-item"><div class="di-label">Colaborador</div><div class="di-value" id="modal-det-colaborador">-</div></div>
                <div class="detail-item"><div class="di-label">DNI</div><div class="di-value" id="modal-det-dni">-</div></div>
                <div class="detail-item"><div class="di-label">CECO / Proyecto</div><div class="di-value" id="modal-det-ceco">-</div></div>
                <div class="detail-item"><div class="di-label">Estado solicitud</div><div class="di-value" id="modal-det-estado-solicitud">-</div></div>
                <div class="detail-item"><div class="di-label">Estado rendiciÃƒÂ³n</div><div class="di-value" id="modal-det-estado-rendicion">-</div></div>
                <div class="detail-item col-full"><div class="di-label">Motivo del viaje</div><div class="motivo-box" id="modal-det-motivo">-</div></div>
            </div>
            <div style="margin-top:20px;"><div class="di-label" style="margin-bottom:10px;">Historial</div><div id="modal-det-historial"></div></div>
            <div id="modal-solicitud-error" class="erp-alert-error"></div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" id="btn-cancelar-solicitud-modal" style="margin-right:auto;">Cerrar</button>
            <span id="modal-solicitud-label" style="display:none;"></span>
            <button class="btn-decide-rechazar" id="btn-modal-rechazar" data-estado="rechazada"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>Rechazar</button>
            <span class="decision-sep"></span>
            <button class="btn-decide-observar" id="btn-modal-observar" data-estado="observada"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>Observar</button>
            <button class="btn-decide-aprobar" id="btn-modal-aprobar" data-estado="aprobada"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Aprobar</button>
        </div>
    </div>
</div>
<script>window.ViaticosConfig = { nonce: '<?php echo esc_js( $args['rest_nonce'] ); ?>', apiBase: '<?php echo esc_js( $args['api_base'] ); ?>', logoUrl: '<?php echo esc_js( $args['logo_url'] ?? '' ); ?>' };</script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/admin.js"></script>

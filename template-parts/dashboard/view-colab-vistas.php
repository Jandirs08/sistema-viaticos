<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$_first_name = explode( ' ', trim( (string) $args['user_name'] ) );
$_first_name = $_first_name[0] ?: 'Colaborador';
?>

<!-- ============================================================
     VISTA: INICIO
     ============================================================ -->
<section id="view-inicio" class="erp-view active" aria-label="Inicio">

    <header class="inicio-head">
        <div class="inicio-head-main">
            <p class="inicio-eyebrow" id="inicio-eyebrow"></p>
            <h1 class="inicio-title">Hola, <?php echo esc_html( $_first_name ); ?>.</h1>
            <p class="inicio-sub" id="inicio-sub">Tus viáticos, en una mirada.</p>
        </div>
        <button type="button" class="btn btn-primary inicio-cta" id="btn-inicio-nueva">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nueva solicitud
        </button>
    </header>

    <div class="inicio-summary" id="inicio-summary" role="group" aria-label="Resumen rápido">
        <button type="button" class="inicio-metric" data-metric="por-rendir" id="metric-por-rendir">
            <span class="inicio-metric-num" id="kpi-por-rendir">—</span>
            <span class="inicio-metric-lbl">Por rendir</span>
            <span class="inicio-metric-hint">Ir a la bandeja</span>
        </button>
        <button type="button" class="inicio-metric" data-metric="en-revision" id="metric-en-revision">
            <span class="inicio-metric-num" id="kpi-en-revision">—</span>
            <span class="inicio-metric-lbl">En revisión</span>
            <span class="inicio-metric-hint">Ver estado</span>
        </button>
        <button type="button" class="inicio-metric" data-metric="observadas" id="metric-observadas">
            <span class="inicio-metric-num" id="kpi-observadas">—</span>
            <span class="inicio-metric-lbl">Observadas</span>
            <span class="inicio-metric-hint">Ir al detalle</span>
        </button>
        <button type="button" class="inicio-metric inicio-metric-total" data-metric="total" id="metric-total">
            <span class="inicio-metric-num" id="kpi-total">—</span>
            <span class="inicio-metric-lbl">Solicitudes totales</span>
            <span class="inicio-metric-hint">Ver todas</span>
        </button>
    </div>

    <div class="inicio-grid">
        <section class="inicio-block inicio-block-bandeja">
            <header class="inicio-block-head">
                <div>
                    <h2 class="inicio-block-title">Requieren tu atención</h2>
                    <p class="inicio-block-sub" id="inicio-bandeja-sub">Anticipos y rendiciones que esperan una acción tuya.</p>
                </div>
                <span class="inicio-pill" id="inicio-bandeja-count" aria-live="polite">0</span>
            </header>
            <ol class="inicio-bandeja-list" id="inicio-bandeja"></ol>
        </section>

        <aside class="inicio-block inicio-block-recent">
            <header class="inicio-block-head">
                <div>
                    <h2 class="inicio-block-title">Actividad reciente</h2>
                    <p class="inicio-block-sub">Tus últimos 5 movimientos.</p>
                </div>
                <button type="button" class="inicio-linkbtn" id="inicio-ver-todas">
                    Ver todas
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </button>
            </header>
            <ol class="inicio-recent-list" id="inicio-recent-list" aria-label="Actividad reciente"></ol>
        </aside>
    </div>

</section><!-- /#view-inicio -->


<!-- ============================================================
     VISTA: MIS SOLICITUDES
     ============================================================ -->
<section id="view-solicitudes" class="erp-view" aria-label="Mis Solicitudes">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Solicitudes</h1>
        </div>
        <button class="btn btn-primary" id="btn-abrir-nueva-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nueva Solicitud
        </button>
    </div>

    <div class="card">
        <div class="tbl-toolbar">
            <div class="tbl-toolbar-search">
                <svg class="tbl-toolbar-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="search" id="search-solicitudes" class="search-input" placeholder="Buscar..." autocomplete="off" aria-label="Buscar en solicitudes">
            </div>
            <div class="tbl-chips" id="chips-solicitudes">
                <button class="tbl-chip is-active" data-filter="">Todas <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="pendiente">Pendiente <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="aprobada">Aprobada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="observada">Observada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip" data-filter="rechazada">Rechazada <span class="tbl-chip-count"></span></button>
                <button class="tbl-chip tbl-chip-fecha" id="fecha-chip-solicitudes" title="Filtrar por fecha de viaje">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    Fecha
                </button>
            </div>
            <div class="tbl-toolbar-right">
                <button class="btn btn-ghost btn-sm" id="btn-refrescar-solicitudes" title="Actualizar tabla">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                    Actualizar
                </button>
                <button class="tbl-clear-btn" id="clear-solicitudes" style="display:none;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    Limpiar
                </button>
                <div class="tbl-page-size-wrap">
                    Mostrar
                    <select class="tbl-page-size" id="page-size-solicitudes">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="tbl-toolbar-dates" id="dates-strip-solicitudes">
                <span class="tbl-dates-label">Viaje entre</span>
                <input type="date" class="tbl-date-input" id="fecha-desde-solicitudes" aria-label="Fecha desde">
                <span class="tbl-dates-sep">y</span>
                <input type="date" class="tbl-date-input" id="fecha-hasta-solicitudes" aria-label="Fecha hasta">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Mis solicitudes de viáticos">
                <thead>
                    <tr>
                        <th scope="col" aria-sort="none" data-sort-key="id" data-sort-type="num" class="sortable"><span class="th-inner">ID<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th scope="col" aria-sort="none" data-sort-key="fecha_creacion" data-sort-type="date" class="sortable"><span class="th-inner">Solicitada<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th scope="col" aria-sort="none" data-sort-key="fecha" data-sort-type="date" class="sortable"><span class="th-inner">Viaje<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th scope="col">Motivo</th>
                        <th scope="col" aria-sort="none" data-sort-key="monto" data-sort-type="num" class="sortable"><span class="th-inner">Monto<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th scope="col">CECO / Proyecto</th><th scope="col">Estado</th><th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudes-tbody">
                </tbody>
            </table>
        </div>
        <div class="tbl-pagination" id="tbl-pag-solicitudes"></div>
    </div>

</section><!-- /#view-solicitudes -->


<!-- ============================================================
     VISTA: DETALLE DE SOLICITUD / RENDICION
     ============================================================ -->
<section id="view-detalle-solicitud" class="erp-view" aria-label="Detalle de Solicitud">

    <div class="rd-topbar">
        <button class="rd-back-btn" id="btn-volver-detalle-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver
        </button>
    </div>

    <div id="detalle-view-content"></div>
    <div id="detalle-view-error" class="erp-alert-error"></div>

</section><!-- /#view-detalle-solicitud -->


<!-- ============================================================
     VISTA: MIS RENDICIONES (Card list)
     ============================================================ -->
<section id="view-rendiciones" class="erp-view" aria-label="Mis Rendiciones">

    <div class="page-header">
        <div class="page-header-left">
            <h1>Mis Rendiciones</h1>
            <p>Resumen del expediente por cada solicitud aprobada.</p>
        </div>
    </div>

    <div id="rendiciones-list-container">
        <div class="rd-list-loading"><div class="spinner"></div> Cargando rendiciones…</div>
    </div>

</section><!-- /#view-rendiciones -->

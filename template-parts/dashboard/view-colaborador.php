<?php
/**
 * Template Part: Dashboard â€” Vista Colaborador
 *
 * Renders the full Colaborador SPA: sidebar nav links, three views
 * (Inicio / Mis Solicitudes / Mis Rendiciones), three modals
 * (Nueva Solicitud / Editar / Rendir Gasto) and the ViaticosApp JS module.
 *
 * Expected args (injected by page-dashboard.php):
 *   $args['user_name']   string  Escaped display name.
 *   $args['rest_nonce']  string  WP REST nonce.
 *   $args['api_base']    string  Base REST URL (no trailing slash).
 *   $args['user_dni']    string  DNI del usuario actual.
 *
 * @package ThemeAdministracion
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = wp_parse_args(
    $args,
    [
        'user_name'         => '',
        'rest_nonce'        => '',
        'api_base'          => '',
        'user_dni'          => '',
        'user_cargo'        => '',
        'user_area'         => '',
        'user_aprobador'    => '',
    ]
);
?>


<!-- ============================================================
     VISTA: INICIO
     ============================================================ -->
<?php
$_first_name = explode( ' ', trim( (string) $args['user_name'] ) );
$_first_name = $_first_name[0] ?: 'Colaborador';
?>
<section id="view-inicio" class="erp-view active" aria-label="Inicio">

    <header class="inicio-head">
        <div class="inicio-head-main">
            <p class="inicio-eyebrow" id="inicio-eyebrow"></p>
            <h1 class="inicio-title">Hola, <?php echo esc_html( $_first_name ); ?>.</h1>
            <p class="inicio-sub" id="inicio-sub">Tus viÃ¡ticos, en una mirada.</p>
        </div>
        <button type="button" class="btn btn-primary inicio-cta" id="btn-inicio-nueva">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nueva solicitud
        </button>
    </header>

    <div class="inicio-summary" id="inicio-summary" role="group" aria-label="Resumen rÃ¡pido">
        <button type="button" class="inicio-metric" data-metric="por-rendir" id="metric-por-rendir">
            <span class="inicio-metric-num" id="kpi-por-rendir">â€”</span>
            <span class="inicio-metric-lbl">Por rendir</span>
            <span class="inicio-metric-hint">Ir a la bandeja</span>
        </button>
        <button type="button" class="inicio-metric" data-metric="en-revision" id="metric-en-revision">
            <span class="inicio-metric-num" id="kpi-en-revision">â€”</span>
            <span class="inicio-metric-lbl">En revisiÃ³n</span>
            <span class="inicio-metric-hint">Ver estado</span>
        </button>
        <button type="button" class="inicio-metric" data-metric="observadas" id="metric-observadas">
            <span class="inicio-metric-num" id="kpi-observadas">â€”</span>
            <span class="inicio-metric-lbl">Observadas</span>
            <span class="inicio-metric-hint">Ir al detalle</span>
        </button>
        <button type="button" class="inicio-metric inicio-metric-total" data-metric="total" id="metric-total">
            <span class="inicio-metric-num" id="kpi-total">â€”</span>
            <span class="inicio-metric-lbl">Solicitudes totales</span>
            <span class="inicio-metric-hint">Ver todas</span>
        </button>
    </div>

    <div class="inicio-grid">
        <section class="inicio-block inicio-block-bandeja">
            <header class="inicio-block-head">
                <div>
                    <h2 class="inicio-block-title">Requieren tu atenciÃ³n</h2>
                    <p class="inicio-block-sub" id="inicio-bandeja-sub">Anticipos y rendiciones que esperan una acciÃ³n tuya.</p>
                </div>
                <span class="inicio-pill" id="inicio-bandeja-count" aria-live="polite">0</span>
            </header>
            <ol class="inicio-bandeja-list" id="inicio-bandeja"></ol>
        </section>

        <aside class="inicio-block inicio-block-recent">
            <header class="inicio-block-head">
                <div>
                    <h2 class="inicio-block-title">Actividad reciente</h2>
                    <p class="inicio-block-sub">Tus Ãºltimos 5 movimientos.</p>
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
                <input type="search" id="search-solicitudes" class="search-input" placeholder="Buscar..." autocomplete="off">
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
                <input type="date" class="tbl-date-input" id="fecha-desde-solicitudes">
                <span class="tbl-dates-sep">y</span>
                <input type="date" class="tbl-date-input" id="fecha-hasta-solicitudes">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="erp-table" aria-label="Mis solicitudes de viÃ¡ticos">
                <thead>
                    <tr>
                        <th data-sort-key="id" data-sort-type="num" class="sortable"><span class="th-inner">ID<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th data-sort-key="fecha" data-sort-type="date" class="sortable"><span class="th-inner">Fecha Viaje<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>Motivo</th>
                        <th data-sort-key="monto" data-sort-type="num" class="sortable"><span class="th-inner">Monto Solicitado<span class="sort-arrows"><svg class="arrow-asc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 5L4 0L8 5Z"/></svg><svg class="arrow-desc" width="8" height="5" viewBox="0 0 8 5" fill="currentColor"><path d="M0 0L4 5L8 0Z"/></svg></span></span></th>
                        <th>CECO / Proyecto</th><th>Estado</th><th>Acciones</th>
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

    <!-- Topbar liviana â€” los CTAs viven en el rail derecho -->
    <div class="rd-topbar">
        <button class="rd-back-btn" id="btn-volver-detalle-solicitud">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Volver
        </button>
    </div>

    <!-- Contenido dinÃ¡mico generado por renderDetalleSolicitudContent() -->
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

    <!-- El JS inyecta tarjetas aquÃ­ -->
    <div id="rendiciones-list-container">
        <div class="rd-list-loading"><div class="spinner"></div> Cargando rendicionesâ€¦</div>
    </div>

</section><!-- /#view-rendiciones -->

<!-- MODAL: liquidaciÃ³n colaborador -->
<div class="modal-overlay" id="modal-colab-liquidacion" role="dialog" aria-modal="true" aria-labelledby="modal-colab-liq-titulo">
    <div class="modal modal-xl liq-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-colab-liq-titulo">LiquidaciÃ³n de RendiciÃ³n</h2>
                <p>Documento de solo lectura</p>
            </div>
            <button class="modal-close" id="btn-cerrar-colab-liq" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body" id="colab-liq-container"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="btn-excel-colab-liq">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zM8 16l2.5-3.5L13 16h-5zm8 0h-2l-2.5-3.5L14 9h2l-2.5 3.5L16 16z"/></svg>
                Exportar Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="btn-imprimir-colab-liq">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                Imprimir
            </button>
            <button type="button" class="btn btn-secondary" id="btn-cancelar-colab-liq">Cerrar</button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: NUEVA SOLICITUD
     ============================================================ -->
<div class="modal-overlay" id="modal-nueva-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-nueva-titulo">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modal-nueva-titulo">Nueva Solicitud de ViÃ¡tico</h2>
            <button class="modal-close" id="btn-cerrar-modal-nueva" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-nueva-solicitud" novalidate>
                <input type="hidden" id="ns-dni" name="dni">
                <input type="hidden" id="ns-aprobador" name="aprobador">
                <div class="sol-profile-section">
                    <div class="sol-profile-field">
                        <span class="sol-profile-label">Solicitante</span>
                        <span class="sol-profile-value" id="ns-display-nombre"></span>
                    </div>
                    <div class="sol-profile-field">
                        <span class="sol-profile-label">DNI</span>
                        <span class="sol-profile-value" id="ns-display-dni"></span>
                    </div>
                    <div class="sol-profile-field">
                        <span class="sol-profile-label">Cargo</span>
                        <span class="sol-profile-value" id="ns-display-cargo"></span>
                    </div>
                    <div class="sol-profile-field">
                        <span class="sol-profile-label">Ãrea</span>
                        <span class="sol-profile-value" id="ns-display-area"></span>
                    </div>
                    <div class="sol-profile-field col-full">
                        <span class="sol-profile-label">Aprobador</span>
                        <span class="sol-profile-value" id="ns-display-aprobador"></span>
                    </div>
                </div>
                <p class="form-section-label">Datos del viaje</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ns-monto">Monto solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ns-monto" name="monto" class="form-control" placeholder="0.00" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" id="err-ns-monto">Ingresa un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-fecha">Fecha del viaje <span class="required">*</span></label>
                        <input type="date" id="ns-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-ns-fecha">Selecciona una fecha vÃ¡lida.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ns-ceco" name="ceco" class="form-control" placeholder="Ej: CC-001 / ADMINISTRACIÃ“N" required autocomplete="off">
                        <span class="form-error" id="err-ns-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-motivo">Motivo del viaje <span class="required">*</span></label>
                        <textarea id="ns-motivo" name="motivo" class="form-control" placeholder="Describe el objetivo o motivo del viajeâ€¦" required rows="3"></textarea>
                        <span class="form-error" id="err-ns-motivo">Describe el motivo del viaje.</span>
                    </div>
                </div>
                <div id="nueva-solicitud-error" class="erp-alert-error"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-nueva">Cancelar</button>
            <button type="submit" form="form-nueva-solicitud" class="btn btn-primary" id="btn-submit-nueva-solicitud">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Enviar Solicitud
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: EDITAR SOLICITUD (Observada)
     ============================================================ -->
<div class="modal-overlay" id="modal-editar-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-editar-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-editar-titulo">Editar Solicitud <span id="editar-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p>Esta solicitud fue observada. Corrige los datos y reenvÃ­ala.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-editar" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-editar-solicitud" novalidate>
                <input type="hidden" id="ed-post-id" name="post_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ed-dni">DNI del Colaborador <span class="required">*</span></label>
                        <input type="text" id="ed-dni" name="dni" class="form-control" maxlength="8" pattern="\d{8}" required inputmode="numeric">
                        <span class="form-error" id="err-ed-dni">El DNI debe tener exactamente 8 dÃ­gitos numÃ©ricos.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ed-monto" name="monto" class="form-control" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" id="err-ed-monto">Ingresa un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input type="date" id="ed-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-ed-fecha">Selecciona una fecha vÃ¡lida.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ed-ceco" name="ceco" class="form-control" required>
                        <span class="form-error" id="err-ed-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ed-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea id="ed-motivo" name="motivo" class="form-control" required rows="4"></textarea>
                        <span class="form-error" id="err-ed-motivo">Describe el motivo del viaje.</span>
                    </div>
                </div>
                <div id="editar-solicitud-error" class="erp-alert-error"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-editar">Cancelar</button>
            <button type="submit" form="form-editar-solicitud" class="btn btn-primary" id="btn-submit-editar-solicitud">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.11.0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1.0 2-.9 2-2V7l-4-4zm-5 16c-1.66.0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                Guardar Cambios
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: DETALLE DE SOLICITUD / RENDICIÃ“N
     ============================================================ -->
<div class="modal-overlay" id="modal-detalle-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-detalle-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-detalle-titulo">Solicitud <span id="detalle-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p id="detalle-sol-subtitulo">Revisa el expediente de la solicitud y gestiona su rendiciÃ³n.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-detalle" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="detalle-solicitud-content">
                <div class="table-loading"><div class="spinner"></div> Cargando detalleâ€¦</div>
            </div>
            <div id="detalle-solicitud-error" class="erp-alert-error"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-detalle">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btn-detalle-agregar-gasto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Agregar gasto
            </button>
            <button type="button" class="btn btn-success" id="btn-detalle-finalizar-rendicion">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Finalizar y enviar rendiciÃ³n
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: RENDIR GASTO (WIZARD 2 PASOS)
     ============================================================ -->
<div class="modal-overlay" id="modal-rendir-gasto" role="dialog" aria-modal="true" aria-label="Rendir gasto" aria-labelledby="modal-rendir-titulo">
    <div class="modal modal-wizard" data-wizard-step="1">
        <!-- Topbar: stepper + ref + cerrar (chrome compacto) -->
        <nav class="wizard-topbar" aria-label="Progreso" data-current="1">
            <div class="wizard-topbar__progress">
                <button type="button" class="wizard-step is-active" data-step="1">
                    <span class="wizard-step__node">
                        <span class="wizard-step__num">1</span>
                        <svg class="wizard-step__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span class="wizard-step__label">CategorÃ­a</span>
                </button>
                <span class="wizard-step__line" aria-hidden="true"></span>
                <button type="button" class="wizard-step" data-step="2" disabled>
                    <span class="wizard-step__node">
                        <span class="wizard-step__num">2</span>
                    </span>
                    <span class="wizard-step__label">Detalles</span>
                </button>
            </div>
            <span class="wizard-topbar__ref" id="modal-rendir-titulo">
                Solicitud <strong id="rendir-sol-ref"></strong>
            </span>
            <button class="modal-close" id="btn-cerrar-modal-rendir" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </nav>

        <!-- Body -->
        <div class="modal-body wizard-body">
            <form id="form-rendir-gasto" novalidate>
                <input type="hidden" id="rg-id-solicitud" name="id_solicitud">
                <input type="hidden" id="rg-tipo" name="tipo">

                <!-- â•â•â• STEP 1 â•â•â• -->
                <section class="wizard-panel is-active" data-step="1" aria-labelledby="wz-step1-title">
                    <h3 id="wz-step1-title" class="wizard-panel__title">Â¿QuÃ© tipo de gasto vas a rendir?</h3>
                    <p class="wizard-panel__subtitle">Elige la categorÃ­a y adjunta el comprobante si corresponde.</p>

                    <div class="form-group">
                        <label class="form-label" for="rg-categoria">CategorÃ­a del gasto <span class="required">*</span></label>
                        <select id="rg-categoria" name="id_categoria" class="form-control" required>
                            <option value="">â€” Seleccionar categorÃ­a â€”</option>
                        </select>
                        <span class="form-error" id="err-rg-categoria">Selecciona una categorÃ­a de gasto.</span>
                        <div class="rg-cat-panel" id="rg-cat-info" aria-live="polite">
                            <div class="rg-cat-panel__field">
                                <span class="rg-cat-panel__label">Clase de documento</span>
                                <span class="rg-cat-panel__value" id="rg-cat-clase"></span>
                            </div>
                            <div class="rg-cat-panel__field">
                                <span class="rg-cat-panel__label">Cuenta contable</span>
                                <span class="rg-cat-panel__value rg-cat-panel__value--mono" id="rg-cat-cta"></span>
                            </div>
                            <span class="rg-cat-panel__lock" aria-hidden="true" title="Auto-completado por la categorÃ­a">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Dropzone -->
                    <div class="form-group">
                        <div class="dropzone-label">
                            <span>Comprobante</span>
                            <span class="dropzone-badge is-optional" id="rg-adj-badge">Opcional</span>
                        </div>
                        <div class="dropzone" id="rg-dropzone">
                            <input type="file" id="rg-adj-input" accept=".pdf,.jpg,.jpeg,.png" multiple hidden>
                            <button type="button" class="dropzone-empty" id="dz-empty">
                                <span class="dropzone-icon" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </span>
                                <span class="dropzone-title">Arrastra el archivo aquÃ­</span>
                                <span class="dropzone-sub">o haz clic para buscar Â· PDF, JPG, PNG</span>
                            </button>
                            <div class="dropzone-files" id="rg-adj-file-list"></div>
                            <button type="button" class="dropzone-add" id="dz-add-more">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                Agregar otro archivo
                            </button>
                        </div>
                    </div>
                </section>

                <!-- â•â•â• STEP 2 â•â•â• -->
                <section class="wizard-panel wizard-panel--dense" data-step="2" aria-labelledby="wz-step2-title">
                    <h3 id="wz-step2-title" class="wizard-panel__title">Detalles del gasto</h3>

                    <!-- Summary de step 1 -->
                    <div class="wizard-summary" id="wz-summary">
                        <div class="wizard-summary__line">
                            <span class="wizard-summary__cat" id="wz-summary-cat"></span>
                            <span class="wizard-summary__sep">Â·</span>
                            <span class="wizard-summary__clase" id="wz-summary-clase"></span>
                            <span class="wizard-summary__files" id="wz-summary-files"></span>
                        </div>
                        <button type="button" class="wizard-summary__back" id="wz-summary-back">Editar</button>
                    </div>

                    <div class="form-grid">
                        <div class="form-group" id="rg-group-fecha">
                            <label class="form-label" for="rg-fecha"><span id="lbl-rg-fecha">Fecha de EmisiÃ³n</span> <span class="required">*</span></label>
                            <input type="date" id="rg-fecha" name="fecha" class="form-control" required>
                            <span class="form-error" id="err-rg-fecha">Selecciona la fecha de emisiÃ³n.</span>
                        </div>
                        <div class="form-group" id="rg-group-importe">
                            <label class="form-label" for="rg-importe"><span id="lbl-rg-importe">Importe</span> <span class="required">*</span></label>
                            <div class="input-prefix-wrap">
                                <span class="input-prefix">S/.</span>
                                <input type="number" id="rg-importe" name="importe" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                            </div>
                            <span class="form-error" id="err-rg-importe">Ingresa un importe mayor a S/. 0.00.</span>
                        </div>
                        <div class="form-group" id="rg-group-ruc" style="display:none">
                            <label class="form-label" for="rg-ruc">RUC <span class="required">*</span></label>
                            <input type="text" id="rg-ruc" name="ruc" class="form-control" maxlength="11" placeholder="Ej: 20123456789" inputmode="numeric">
                            <span class="form-error" id="err-rg-ruc">Ingresa el RUC (11 dÃ­gitos).</span>
                        </div>
                        <div class="form-group" id="rg-group-razon" style="display:none">
                            <label class="form-label" for="rg-razon">RazÃ³n Social <span class="required">*</span></label>
                            <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                            <span class="form-error" id="err-rg-razon">Ingresa la razÃ³n social.</span>
                        </div>
                        <div class="form-group" id="rg-group-nro" style="display:none">
                            <label class="form-label" for="rg-nro-comprobante">NÂ° Comprobante <span class="required">*</span></label>
                            <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                            <span class="form-error" id="err-rg-nro-comprobante">Ingresa el nÃºmero de comprobante.</span>
                        </div>
                        <div class="form-group" id="rg-group-ceco" style="display:none">
                            <label class="form-label" for="rg-ceco">CECO</label>
                            <input type="text" id="rg-ceco" name="ceco" class="form-control" placeholder="Ej: CC-001">
                        </div>
                        <div class="form-group" id="rg-group-oi" style="display:none">
                            <label class="form-label" for="rg-oi">OI</label>
                            <input type="text" id="rg-oi" name="oi" class="form-control" placeholder="Ej: OI-123">
                        </div>
                        <div class="form-group col-full" id="rg-group-concepto" style="display:none">
                            <label class="form-label" for="rg-concepto"><span id="lbl-rg-concepto">DescripciÃ³n / Concepto</span></label>
                            <textarea id="rg-concepto" name="descripcion_concepto" class="form-control" placeholder="Describe el concepto del gastoâ€¦" rows="2"></textarea>
                        </div>
                        <div class="form-group col-full" id="rg-group-motivo" style="display:none">
                            <label class="form-label" for="rg-motivo">Motivo <span class="required">*</span></label>
                            <textarea id="rg-motivo" name="motivo_movilidad" class="form-control" placeholder="Indica el motivo del trasladoâ€¦" rows="2"></textarea>
                            <span class="form-error" id="err-rg-motivo">Ingresa el motivo de movilidad.</span>
                        </div>
                        <div class="form-group" id="rg-group-destino" style="display:none">
                            <label class="form-label" for="rg-destino">Destino <span class="required">*</span></label>
                            <input type="text" id="rg-destino" name="destino_movilidad" class="form-control" placeholder="Ej: Oficina central / cliente / sede">
                            <span class="form-error" id="err-rg-destino">Ingresa el destino de movilidad.</span>
                        </div>
                        <div class="form-group col-full" id="rg-group-ceco-oi" style="display:none">
                            <label class="form-label" for="rg-ceco-oi">CECO / OI <span class="required">*</span></label>
                            <input type="text" id="rg-ceco-oi" name="ceco_oi" class="form-control" placeholder="Ej: CC-001 / OI-123">
                            <span class="form-error" id="err-rg-ceco-oi">Ingresa el CECO / OI.</span>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        <!-- Footer dinÃ¡mico por paso -->
        <div class="modal-footer wizard-footer">
            <!-- Step 1 actions -->
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-rendir" data-show-step="1">Cancelar</button>
            <button type="button" class="btn btn-primary wizard-next" id="btn-wizard-next" data-show-step="1">
                Siguiente
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <!-- Step 2 actions -->
            <button type="button" class="btn btn-tertiary wizard-back" id="btn-wizard-back" data-show-step="2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                AtrÃ¡s
            </button>
            <button type="submit" form="form-rendir-gasto" class="btn btn-primary" id="btn-submit-rendir-gasto" data-show-step="2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                Registrar Gasto
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     JAVASCRIPT â€” ViaticosApp
     ============================================================ -->
<div class="modal-overlay" id="modal-historial-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-historial-titulo">
    <div class="modal modal-lg solv-history-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-historial-titulo">Historial del expediente</h2>
                <p id="detalle-historial-subtitulo">Seguimiento completo de la solicitud y su rendiciÃ³n.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-historial" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="solv-history-meta" id="detalle-historial-meta"></div>
        <div class="solv-history-body" id="detalle-historial-body">
            <div class="table-loading"><div class="spinner"></div> Cargando historialâ€¦</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-historial">Cerrar</button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: CONFIRMAR FINALIZAR RENDICIÃ“N
     ============================================================ -->
<div class="modal-overlay" id="modal-confirmar-finalizar" role="dialog" aria-modal="true" aria-labelledby="modal-confirmar-titulo">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-confirmar-titulo">Finalizar rendiciÃ³n</h2>
                <p>Esta acciÃ³n no se puede deshacer.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-confirmar" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p>Â¿Confirmas que deseas enviar la rendiciÃ³n al administrador? Una vez finalizada no podrÃ¡s agregar ni eliminar gastos.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-confirmar">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-confirmar-finalizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                SÃ­, finalizar
            </button>
        </div>
    </div>
</div>

<script>window.ViaticosConfig = { nonce: '<?php echo esc_js( $args['rest_nonce'] ); ?>', apiBase: '<?php echo esc_js( $args['api_base'] ); ?>', profile: { name: '<?php echo esc_js( $args['user_name'] ); ?>', dni: '<?php echo esc_js( $args['user_dni'] ); ?>', cargo: '<?php echo esc_js( $args['user_cargo'] ?? '' ); ?>', area: '<?php echo esc_js( $args['user_area'] ?? '' ); ?>', aprobador: '<?php echo esc_js( $args['user_aprobador'] ?? '' ); ?>' }, logoUrl: '<?php echo esc_js( $args['logo_url'] ?? '' ); ?>' };</script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/colaborador.js"></script>

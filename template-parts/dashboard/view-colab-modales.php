<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<!-- MODAL: liquidación colaborador -->
<div class="modal-overlay" id="modal-colab-liquidacion" role="dialog" aria-modal="true" aria-labelledby="modal-colab-liq-titulo">
    <div class="modal modal-xl liq-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-colab-liq-titulo">Liquidación de Rendición</h2>
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
            <h2 id="modal-nueva-titulo">Nueva Solicitud de Viático</h2>
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
                        <span class="sol-profile-label">Área</span>
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
                        <span class="form-error" aria-live="assertive" id="err-ns-monto">Ingresa un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ns-fecha">Fecha del viaje <span class="required">*</span></label>
                        <input type="date" id="ns-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" aria-live="assertive" id="err-ns-fecha">Selecciona una fecha válida.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ns-ceco" name="ceco" class="form-control" placeholder="Ej: CC-001 / ADMINISTRACIÓN" required autocomplete="off">
                        <span class="form-error" aria-live="assertive" id="err-ns-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-motivo">Motivo del viaje <span class="required">*</span></label>
                        <textarea id="ns-motivo" name="motivo" class="form-control" placeholder="Describe el objetivo o motivo del viaje…" required rows="3"></textarea>
                        <span class="form-error" aria-live="assertive" id="err-ns-motivo">Describe el motivo del viaje.</span>
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
                <p>Esta solicitud fue observada. Corrige los datos y reenvíala.</p>
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
                        <span class="form-error" aria-live="assertive" id="err-ed-dni">El DNI debe tener exactamente 8 dígitos numéricos.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ed-monto" name="monto" class="form-control" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" aria-live="assertive" id="err-ed-monto">Ingresa un monto mayor a S/. 0.00.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input type="date" id="ed-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" aria-live="assertive" id="err-ed-fecha">Selecciona una fecha válida.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ed-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ed-ceco" name="ceco" class="form-control" required>
                        <span class="form-error" aria-live="assertive" id="err-ed-ceco">Este campo es obligatorio.</span>
                    </div>
                    <div class="form-group col-full">
                        <label class="form-label" for="ed-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea id="ed-motivo" name="motivo" class="form-control" required rows="4"></textarea>
                        <span class="form-error" aria-live="assertive" id="err-ed-motivo">Describe el motivo del viaje.</span>
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
     MODAL: DETALLE DE SOLICITUD / RENDICIÓN
     ============================================================ -->
<div class="modal-overlay" id="modal-detalle-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-detalle-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-detalle-titulo">Solicitud <span id="detalle-sol-id" style="color:var(--text-muted); font-weight:400;"></span></h2>
                <p id="detalle-sol-subtitulo">Revisa el expediente de la solicitud y gestiona su rendición.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-detalle" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="detalle-solicitud-content">
                <div class="table-loading"><div class="spinner"></div> Cargando detalle…</div>
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
                Finalizar y enviar rendición
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: RENDIR GASTO (WIZARD 2 PASOS)
     ============================================================ -->
<div class="modal-overlay" id="modal-rendir-gasto" role="dialog" aria-modal="true" aria-label="Rendir gasto" aria-labelledby="modal-rendir-titulo">
    <div class="modal modal-wizard" data-wizard-step="1">
        <nav class="wizard-topbar" aria-label="Progreso" data-current="1">
            <div class="wizard-topbar__progress">
                <button type="button" class="wizard-step is-active" data-step="1" aria-current="step">
                    <span class="wizard-step__node">
                        <span class="wizard-step__num">1</span>
                        <svg class="wizard-step__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span class="wizard-step__label">Categoría</span>
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

        <div class="modal-body wizard-body">
            <form id="form-rendir-gasto" novalidate>
                <input type="hidden" id="rg-id-solicitud" name="id_solicitud">
                <input type="hidden" id="rg-tipo" name="tipo">

                <!-- STEP 1 -->
                <section class="wizard-panel is-active" data-step="1" aria-labelledby="wz-step1-title">
                    <h3 id="wz-step1-title" class="wizard-panel__title">¿Qué tipo de gasto vas a rendir?</h3>
                    <p class="wizard-panel__subtitle">Elige la categoría y adjunta el comprobante si corresponde.</p>

                    <div class="form-group">
                        <label class="form-label" for="rg-categoria">Categoría del gasto <span class="required">*</span></label>
                        <select id="rg-categoria" name="id_categoria" class="form-control" required>
                            <option value="">— Seleccionar categoría —</option>
                        </select>
                        <span class="form-error" aria-live="assertive" id="err-rg-categoria">Selecciona una categoría de gasto.</span>
                        <div class="rg-cat-panel" id="rg-cat-info" aria-live="polite">
                            <div class="rg-cat-panel__field">
                                <span class="rg-cat-panel__label">Clase de documento</span>
                                <span class="rg-cat-panel__value" id="rg-cat-clase"></span>
                            </div>
                            <div class="rg-cat-panel__field">
                                <span class="rg-cat-panel__label">Cuenta contable</span>
                                <span class="rg-cat-panel__value rg-cat-panel__value--mono" id="rg-cat-cta"></span>
                            </div>
                            <span class="rg-cat-panel__lock" aria-hidden="true" title="Auto-completado por la categoría">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
                            </span>
                        </div>
                    </div>

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
                                <span class="dropzone-title">Arrastra el archivo aquí</span>
                                <span class="dropzone-sub">o haz clic para buscar · PDF, JPG, PNG</span>
                            </button>
                            <div class="dropzone-files" id="rg-adj-file-list"></div>
                            <button type="button" class="dropzone-add" id="dz-add-more">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                Agregar otro archivo
                            </button>
                        </div>
                        <div class="rg-ocr-block" id="rg-ocr-block" hidden>
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-rg-ocr">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                                Auto-llenar desde documento
                            </button>
                            <span class="rg-ocr-status" id="rg-ocr-status" aria-live="polite"></span>
                        </div>
                    </div>
                </section>

                <!-- STEP 2 -->
                <section class="wizard-panel wizard-panel--dense" data-step="2" aria-labelledby="wz-step2-title">
                    <h3 id="wz-step2-title" class="wizard-panel__title">Detalles del gasto</h3>

                    <div class="wizard-summary" id="wz-summary">
                        <div class="wizard-summary__line">
                            <span class="wizard-summary__cat" id="wz-summary-cat"></span>
                            <span class="wizard-summary__sep">·</span>
                            <span class="wizard-summary__clase" id="wz-summary-clase"></span>
                            <span class="wizard-summary__files" id="wz-summary-files"></span>
                        </div>
                        <button type="button" class="wizard-summary__back" id="wz-summary-back">Editar</button>
                    </div>

                    <div class="form-grid">
                        <div class="form-group" id="rg-group-fecha">
                            <label class="form-label" for="rg-fecha"><span id="lbl-rg-fecha">Fecha de Emisión</span> <span class="required">*</span></label>
                            <input type="date" id="rg-fecha" name="fecha" class="form-control" required>
                            <span class="form-error" aria-live="assertive" id="err-rg-fecha">Selecciona la fecha de emisión.</span>
                        </div>
                        <div class="form-group" id="rg-group-importe">
                            <label class="form-label" for="rg-importe"><span id="lbl-rg-importe">Importe</span> <span class="required">*</span></label>
                            <div class="input-prefix-wrap">
                                <span class="input-prefix">S/.</span>
                                <input type="number" id="rg-importe" name="importe" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                            </div>
                            <span class="form-error" aria-live="assertive" id="err-rg-importe">Ingresa un importe mayor a S/. 0.00.</span>
                        </div>
                        <div class="form-group" id="rg-group-ruc" style="display:none">
                            <label class="form-label" for="rg-ruc">RUC <span class="required">*</span></label>
                            <input type="text" id="rg-ruc" name="ruc" class="form-control" maxlength="11" placeholder="Ej: 20123456789" inputmode="numeric">
                            <span class="form-error" aria-live="assertive" id="err-rg-ruc">Ingresa el RUC (11 dígitos).</span>
                        </div>
                        <div class="form-group" id="rg-group-razon" style="display:none">
                            <label class="form-label" for="rg-razon">Razón Social <span class="required">*</span></label>
                            <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                            <span class="form-error" aria-live="assertive" id="err-rg-razon">Ingresa la razón social.</span>
                        </div>
                        <div class="form-group" id="rg-group-nro" style="display:none">
                            <label class="form-label" for="rg-nro-comprobante">N° Comprobante <span class="required">*</span></label>
                            <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                            <span class="form-error" aria-live="assertive" id="err-rg-nro-comprobante">Ingresa el número de comprobante.</span>
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
                            <label class="form-label" for="rg-concepto"><span id="lbl-rg-concepto">Descripción / Concepto</span></label>
                            <textarea id="rg-concepto" name="descripcion_concepto" class="form-control" placeholder="Describe el concepto del gasto…" rows="2"></textarea>
                        </div>
                        <div class="form-group col-full" id="rg-group-motivo" style="display:none">
                            <label class="form-label" for="rg-motivo">Motivo <span class="required">*</span></label>
                            <textarea id="rg-motivo" name="motivo_movilidad" class="form-control" placeholder="Indica el motivo del traslado…" rows="2"></textarea>
                            <span class="form-error" aria-live="assertive" id="err-rg-motivo">Ingresa el motivo de movilidad.</span>
                        </div>
                        <div class="form-group" id="rg-group-destino" style="display:none">
                            <label class="form-label" for="rg-destino">Destino <span class="required">*</span></label>
                            <input type="text" id="rg-destino" name="destino_movilidad" class="form-control" placeholder="Ej: Oficina central / cliente / sede">
                            <span class="form-error" aria-live="assertive" id="err-rg-destino">Ingresa el destino de movilidad.</span>
                        </div>
                        <div class="form-group col-full" id="rg-group-ceco-oi" style="display:none">
                            <label class="form-label" for="rg-ceco-oi">CECO / OI <span class="required">*</span></label>
                            <input type="text" id="rg-ceco-oi" name="ceco_oi" class="form-control" placeholder="Ej: CC-001 / OI-123">
                            <span class="form-error" aria-live="assertive" id="err-rg-ceco-oi">Ingresa el CECO / OI.</span>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        <div class="modal-footer wizard-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-rendir" data-show-step="1">Cancelar</button>
            <button type="button" class="btn btn-primary wizard-next" id="btn-wizard-next" data-show-step="1">
                Siguiente
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <button type="button" class="btn btn-tertiary wizard-back" id="btn-wizard-back" data-show-step="2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Atrás
            </button>
            <button type="submit" form="form-rendir-gasto" class="btn btn-primary" id="btn-submit-rendir-gasto" data-show-step="2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                Registrar Gasto
            </button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: HISTORIAL
     ============================================================ -->
<div class="modal-overlay" id="modal-historial-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-historial-titulo">
    <div class="modal modal-lg solv-history-modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-historial-titulo">Historial del expediente</h2>
                <p id="detalle-historial-subtitulo">Seguimiento completo de la solicitud y su rendición.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-historial" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="solv-history-meta" id="detalle-historial-meta"></div>
        <div class="solv-history-body" id="detalle-historial-body">
            <div class="table-loading"><div class="spinner"></div> Cargando historial…</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-historial">Cerrar</button>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: CONFIRMAR FINALIZAR RENDICIÓN
     ============================================================ -->
<div class="modal-overlay" id="modal-confirmar-finalizar" role="dialog" aria-modal="true" aria-labelledby="modal-confirmar-titulo">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-confirmar-titulo">Finalizar rendición</h2>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-confirmar" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p>¿Confirmas que deseas enviar la rendición al administrador? Una vez finalizada no podrás agregar ni eliminar gastos.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-confirmar">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-confirmar-finalizar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Sí, finalizar
            </button>
        </div>
    </div>
</div>

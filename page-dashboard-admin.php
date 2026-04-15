<?php
/**
 * Template Name: Dashboard Administrador
 *
 * Panel ERP para el rol "admin_viaticos" / "administrator".
 * Permite visualizar todas las solicitudes del equipo y cambiar su estado.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Seguridad: solo administradores ───────────────────────────────────────────
if ( ! is_user_logged_in() ||
     ( ! current_user_can( 'administrator' ) &&
       ! current_user_can( 'admin_viaticos' ) &&
       ! current_user_can( 'edit_others_posts' ) ) ) {
    wp_redirect( home_url() );
    exit;
}

$current_user  = wp_get_current_user();
$user_name     = esc_html( $current_user->display_name );
$user_initials = strtoupper(
    mb_substr( $current_user->first_name ?: $current_user->display_name, 0, 1 ) .
    mb_substr( $current_user->last_name  ?: '', 0, 1 )
);
$logout_url  = esc_url( wp_logout_url( home_url() ) );
$rest_nonce  = wp_create_nonce( 'wp_rest' );
$api_base    = esc_js( rtrim( get_rest_url( null, 'viaticos/v1' ), '/' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel Administrador — Sistema de Gestión de Viáticos</title>
    <meta name="description" content="Panel de administración de viáticos — Fundación Romero.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ============================================================
   DESIGN TOKENS
   ============================================================ */
:root {
    --primary:          #da5b3e;
    --primary-dark:     #bf4c32;
    --primary-light:    #f0ddd9;
    --bg:               #F5F7F9;
    --surface:          #FFFFFF;
    --border:           #E2E8F0;
    --border-light:     #EDF2F7;
    --text:             #333333;
    --text-muted:       #718096;
    --text-light:       #A0AEC0;
    --sidebar-bg:       #1E2433;
    --sidebar-hover:    #2D3448;

    --badge-pendiente-bg:   #FEF3C7; --badge-pendiente-text: #92400E;
    --badge-aprobada-bg:    #D1FAE5; --badge-aprobada-text:  #065F46;
    --badge-observada-bg:   #FFEDD5; --badge-observada-text: #9A3412;
    --badge-rechazada-bg:   #FEE2E2; --badge-rechazada-text: #991B1B;
    --badge-rendida-bg:     #EDE9FE; --badge-rendida-text:   #5B21B6;

    --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,.10);
    --shadow-lg: 0 10px 30px rgba(0,0,0,.15);
    --radius-sm: 6px; --radius-md: 10px; --radius-lg: 16px;
    --sidebar-w: 240px; --topbar-h: 60px;
    --ease: .18s ease;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%; font-family: 'Inter', sans-serif; font-size: 14px;
    color: var(--text); background: var(--bg); line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

/* ============================================================
   LAYOUT
   ============================================================ */
#shell { display: flex; height: 100vh; overflow: hidden; }

/* ── Sidebar ─────────────────────────────────────────────── */
#sidebar {
    width: var(--sidebar-w); min-width: var(--sidebar-w);
    background: var(--sidebar-bg); display: flex; flex-direction: column;
    height: 100vh; overflow-y: auto; z-index: 100;
}
.sidebar-logo {
    padding: 20px 18px 16px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.sidebar-logo-mark { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.logo-icon {
    width: 36px; height: 36px; background: var(--primary);
    border-radius: var(--radius-sm); display: flex; align-items: center;
    justify-content: center; flex-shrink: 0;
}
.logo-icon svg { fill: #fff; }
.logo-text strong { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.2; display: block; }
.logo-text span   { font-size: 10.5px; color: rgba(255,255,255,.45); }

.sidebar-section   { padding: 16px 0 8px; }
.sidebar-label     { padding: 0 16px 8px; font-size: 10px; font-weight: 600;
                     letter-spacing: .08em; text-transform: uppercase;
                     color: rgba(255,255,255,.30); }
.sidebar-nav       { list-style: none; padding: 0 8px; }
.sidebar-nav li    { margin-bottom: 2px; }
.sidebar-nav a {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px;
    border-radius: var(--radius-sm); color: rgba(255,255,255,.62);
    text-decoration: none; font-size: 13.5px; font-weight: 500;
    transition: background var(--ease), color var(--ease); cursor: pointer;
}
.sidebar-nav a .nav-icon { width: 18px; height: 18px; flex-shrink: 0; opacity: .7; }
.sidebar-nav a:hover { background: var(--sidebar-hover); color: #fff; }
.sidebar-nav a:hover .nav-icon { opacity: 1; }
.sidebar-nav a.active { background: rgba(218,91,62,.18); color: #f4a58f; }
.sidebar-nav a.active .nav-icon { opacity: 1; }

.sidebar-footer {
    margin-top: auto; padding: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.sidebar-user { display: flex; align-items: center; gap: 10px; }
.user-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.u-name { display: block; font-size: 12.5px; color: #fff; font-weight: 600;
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.u-role { font-size: 10.5px; color: rgba(255,255,255,.40); }

/* ── Main ────────────────────────────────────────────────── */
#main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── Topbar ──────────────────────────────────────────────── */
#topbar {
    height: var(--topbar-h); min-height: var(--topbar-h);
    background: var(--surface); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; gap: 16px; box-shadow: var(--shadow-sm); z-index: 50;
}
.topbar-bc { display: flex; align-items: center; gap: 6px;
             font-size: 12.5px; color: var(--text-muted); }
.topbar-bc span { color: var(--text); font-weight: 600; }
.topbar-actions { display: flex; align-items: center; gap: 16px; }
.topbar-user .t-name { display: block; font-size: 13px; font-weight: 600;
                        color: var(--text); text-align: right; }
.topbar-user .t-role { display: block; font-size: 11px; color: var(--text-muted);
                        text-align: right; }
.btn-logout {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px;
    background: transparent; border: 1px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text-muted);
    font-size: 12.5px; font-weight: 500; cursor: pointer;
    text-decoration: none; transition: all var(--ease);
}
.btn-logout:hover { background: #FEF2F2; border-color: #FECACA; color: #DC2626; }

/* ── Content ─────────────────────────────────────────────── */
#content { flex: 1; overflow-y: auto; padding: 28px; }

/* ============================================================
   VISTAS
   ============================================================ */
.view { display: none; animation: fadeIn .18s ease; }
.view.active { display: block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

/* ── Page header ─────────────────────────────────────────── */
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
}
.page-header h1 { font-size: 20px; font-weight: 700; }
.page-header p  { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

/* ── KPI Grid ────────────────────────────────────────────── */
.kpi-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px; margin-bottom: 28px;
}
.kpi-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm); transition: box-shadow var(--ease), transform var(--ease);
}
.kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.kpi-icon {
    width: 46px; height: 46px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icon.yellow { background: #FEF3C7; }
.kpi-icon.green  { background: #D1FAE5; }
.kpi-icon.orange { background: #FFEDD5; }
.kpi-icon svg    { width: 24px; height: 24px; }
.kpi-num  { font-size: 28px; font-weight: 700; line-height: 1; }
.kpi-label{ font-size: 12px; color: var(--text-muted); margin-top: 3px; }

/* ── Card ────────────────────────────────────────────────── */
.card { background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border-light);
    gap: 12px; flex-wrap: wrap;
}
.card-title    { font-size: 14.5px; font-weight: 600; }
.card-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 1px; }

/* ── Tabla ───────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; }
table.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl thead th {
    padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted);
    background: #F8FAFC; white-space: nowrap; border-bottom: 1px solid var(--border);
}
.tbl tbody tr { border-bottom: 1px solid var(--border-light); transition: background var(--ease); }
.tbl tbody tr:last-child { border-bottom: none; }
.tbl tbody tr:hover { background: #FAFBFC; }
.tbl td { padding: 12px 16px; vertical-align: middle; }
.tbl td.muted { color: var(--text-muted); }

.tbl-empty { text-align: center; padding: 48px 20px; color: var(--text-muted); }
.tbl-empty svg { width: 40px; height: 40px; margin: 0 auto 12px; display: block; opacity: .3; }
.tbl-loading {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 40px; color: var(--text-muted); font-size: 13px;
}

/* ── Filtro búsqueda ─────────────────────────────────────── */
.search-input {
    padding: 8px 12px; border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: 13px; color: var(--text); font-family: inherit;
    outline: none; transition: border-color var(--ease), box-shadow var(--ease);
    width: 220px;
}
.search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(218,91,62,.15); }

/* ── Badges ──────────────────────────────────────────────── */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600; white-space: nowrap;
}
.badge::before {
    content: ''; display: inline-block; width: 6px; height: 6px;
    border-radius: 50%; background: currentColor;
}
.badge-pendiente { background: var(--badge-pendiente-bg); color: var(--badge-pendiente-text); }
.badge-aprobada  { background: var(--badge-aprobada-bg);  color: var(--badge-aprobada-text);  }
.badge-observada { background: var(--badge-observada-bg); color: var(--badge-observada-text); }
.badge-rechazada { background: var(--badge-rechazada-bg); color: var(--badge-rechazada-text); }
.badge-rendida   { background: var(--badge-rendida-bg);   color: var(--badge-rendida-text);   }

/* ── Botones ─────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 600; cursor: pointer;
    border: 1px solid transparent; text-decoration: none;
    transition: all var(--ease); white-space: nowrap; font-family: inherit;
}
.btn:disabled { opacity: .55; cursor: not-allowed; }
.btn-primary  { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn-primary:hover:not(:disabled) { background: var(--primary-dark); border-color: var(--primary-dark); box-shadow: 0 2px 8px rgba(218,91,62,.35); }
.btn-secondary{ background: var(--surface); color: var(--text); border-color: var(--border); }
.btn-secondary:hover:not(:disabled){ background: var(--bg); border-color: #CBD5E0; }
.btn-ghost    { background: transparent; color: var(--text-muted); border-color: transparent; padding: 6px 10px; }
.btn-ghost:hover:not(:disabled){ background: var(--bg); color: var(--text); }
.btn-sm       { padding: 5px 11px; font-size: 12px; }
.btn-success  { background: #F0FDF4; color: #15803D; border-color: #BBF7D0; }
.btn-success:hover:not(:disabled){ background: #DCFCE7; box-shadow: 0 2px 8px rgba(21,128,61,.2); }
.btn-warning  { background: #FFF7ED; color: #9A3412; border-color: #FED7AA; }
.btn-warning:hover:not(:disabled){ background: #FFEDD5; }
.btn-danger   { background: #FEF2F2; color: #DC2626; border-color: #FECACA; }
.btn-danger:hover:not(:disabled){ background: #FEE2E2; box-shadow: 0 2px 8px rgba(220,38,38,.2); }

/* ── Spinner ─────────────────────────────────────────────── */
.spinner {
    width: 20px; height: 20px; border: 2px solid var(--border);
    border-top-color: var(--primary); border-radius: 50%;
    animation: spin .7s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ============================================================
   MODAL
   ============================================================ */
.overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.55); backdrop-filter: blur(3px);
    z-index: 1000; align-items: center; justify-content: center; padding: 20px;
}
.overlay.open { display: flex; animation: overlayIn .18s ease; }
@keyframes overlayIn { from { opacity:0; } to { opacity:1; } }

.modal {
    background: var(--surface); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg); width: 100%; max-width: 620px;
    max-height: 92vh; overflow-y: auto; animation: modalIn .2s ease;
}
@keyframes modalIn { from { opacity:0; transform:translateY(-16px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 18px; border-bottom: 1px solid var(--border);
}
.modal-header h2 { font-size: 16px; font-weight: 700; }
.modal-header p  { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }
.modal-close {
    width: 32px; height: 32px; border: none; background: var(--bg);
    border-radius: var(--radius-sm); display: flex; align-items: center;
    justify-content: center; cursor: pointer; color: var(--text-muted);
    transition: background var(--ease), color var(--ease); flex-shrink: 0;
}
.modal-close:hover { background: #FEE2E2; color: #DC2626; }
.modal-body   { padding: 24px; }
.modal-footer {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 10px; padding: 16px 24px; border-top: 1px solid var(--border);
    background: #FAFBFD; border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    flex-wrap: wrap;
}

/* ── Detalle de solicitud en modal ───────────────────────── */
.detail-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    margin-bottom: 24px;
}
.detail-item {}
.detail-item .di-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted); margin-bottom: 4px;
}
.detail-item .di-value {
    font-size: 14px; font-weight: 500; color: var(--text);
}
.detail-item.col-full { grid-column: 1 / -1; }

.motivo-box {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 12px 14px;
    font-size: 13px; color: var(--text); line-height: 1.6;
    white-space: pre-wrap; word-break: break-word;
}

.modal-decision-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted);
    margin-bottom: 10px; display: block;
}

/* ============================================================
   TOAST
   ============================================================ */
#toast-container {
    position: fixed; top: 20px; right: 20px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; max-width: 360px;
}
.toast {
    display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px;
    border-radius: var(--radius-md); box-shadow: var(--shadow-md);
    font-size: 13px; font-weight: 500; animation: toastIn .25s ease;
    border-left: 4px solid transparent;
}
@keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
.toast-success { background: #F0FDF4; border-color: #22C55E; color: #15803D; }
.toast-error   { background: #FEF2F2; border-color: #EF4444; color: #DC2626; }
.toast-info    { background: #EFF6FF; border-color: #3B82F6; color: #1D4ED8; }
.toast-icon    { flex-shrink: 0; margin-top: 1px; }
.toast-body strong { display: block; font-weight: 700; }
.toast-body p  { font-weight: 400; margin-top: 2px; opacity: .85; }

/* ── Welcome banner ──────────────────────────────────────── */
.welcome-banner {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-md); padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
}
.welcome-banner h2 { font-size: 20px; font-weight: 700; color: #fff; }
.welcome-banner p  { font-size: 13.5px; color: rgba(255,255,255,.80); margin-top: 4px; }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    .detail-grid { grid-template-columns: 1fr; }
    .detail-item.col-full { grid-column: 1; }
    #content { padding: 16px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; }
    .search-input { width: 100%; }
}
</style>
</head>

<body>
<div id="toast-container" role="alert" aria-live="polite"></div>

<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  SHELL                                               ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div id="shell">

    <!-- SIDEBAR -->
    <aside id="sidebar" role="navigation" aria-label="Menú administrador">
        <div class="sidebar-logo">
            <a href="#" class="sidebar-logo-mark">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
                <div class="logo-text">
                    <strong>Viáticos ERP</strong>
                    <span>Administrador</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-label">Menú</p>
            <ul class="sidebar-nav">
                <li>
                    <a href="#" class="nav-link active" data-view="view-resumen" id="nav-resumen">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                        Resumen
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link" data-view="view-solicitudes" id="nav-solicitudes">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        Solicitudes Equipo
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar"><?php echo esc_html( $user_initials ?: 'A' ); ?></div>
                <div style="flex:1; overflow:hidden;">
                    <strong class="u-name"><?php echo $user_name; ?></strong>
                    <span class="u-role">Administrador</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <div id="main">

        <!-- TOPBAR -->
        <header id="topbar">
            <nav class="topbar-bc">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity:.5"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                &rsaquo;
                <span id="topbar-section">Resumen</span>
            </nav>
            <div class="topbar-actions">
                <div class="topbar-user">
                    <strong class="t-name"><?php echo $user_name; ?></strong>
                    <span class="t-role">Administrador de Viáticos</span>
                </div>
                <a href="<?php echo $logout_url; ?>" class="btn-logout" id="btn-logout">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    Salir
                </a>
            </div>
        </header>

        <!-- CONTENT -->
        <main id="content">

            <!-- ================================================
                 VISTA: RESUMEN
                 ================================================ -->
            <section id="view-resumen" class="view active">

                <div class="welcome-banner">
                    <div>
                        <h2>Panel de Administración</h2>
                        <p>Gestión centralizada de viáticos — Fundación Romero</p>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon yellow">
                            <svg viewBox="0 0 24 24" fill="#D97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                        <div>
                            <div class="kpi-num" id="kpi-pendientes">—</div>
                            <div class="kpi-label">Solicitudes Pendientes</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon green">
                            <svg viewBox="0 0 24 24" fill="#059669"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        </div>
                        <div>
                            <div class="kpi-num" id="kpi-monto-aprobado">—</div>
                            <div class="kpi-label">Total Aprobado (S/.)</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon orange">
                            <svg viewBox="0 0 24 24" fill="#EA580C"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                        </div>
                        <div>
                            <div class="kpi-num" id="kpi-observadas">—</div>
                            <div class="kpi-label">Observadas</div>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Solicitudes Recientes</div>
                            <div class="card-subtitle">Últimas 8 del equipo</div>
                        </div>
                        <button class="btn btn-secondary btn-sm" onclick="AdminApp.navigate('view-solicitudes')" id="btn-ver-todas">
                            Ver todas
                        </button>
                    </div>
                    <div class="table-wrap">
                        <table class="tbl" aria-label="Solicitudes recientes">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Colaborador</th><th>Fecha Viaje</th>
                                    <th>Monto</th><th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="resumen-tbody">
                                <tr><td colspan="5"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section><!-- /#view-resumen -->


            <!-- ================================================
                 VISTA: SOLICITUDES EQUIPO
                 ================================================ -->
            <section id="view-solicitudes" class="view">

                <div class="page-header">
                    <div>
                        <h1>Solicitudes del Equipo</h1>
                        <p>Revisa, filtra y evalúa todas las solicitudes de viáticos.</p>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <input
                            type="search"
                            id="search-solicitudes"
                            class="search-input"
                            placeholder="Buscar colaborador, CECO…"
                            autocomplete="off"
                        >
                        <button class="btn btn-ghost btn-sm" id="btn-refrescar" title="Actualizar">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                            Actualizar
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Todas las Solicitudes</div>
                        <div id="tbl-counter" style="font-size:12px;color:var(--text-muted);"></div>
                    </div>
                    <div class="table-wrap">
                        <table class="tbl" aria-label="Todas las solicitudes de viáticos">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Colaborador</th>
                                    <th>Fecha Viaje</th>
                                    <th>Monto</th>
                                    <th>CECO / Proyecto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="solicitudes-tbody">
                                <tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando solicitudes...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section><!-- /#view-solicitudes -->

        </main>
    </div><!-- /#main -->
</div><!-- /#shell -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL: EVALUAR SOLICITUD                            ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="overlay" id="modal-evaluar" role="dialog" aria-modal="true" aria-labelledby="modal-evaluar-titulo">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2 id="modal-evaluar-titulo">Evaluar Solicitud <span id="evaluar-sol-id" style="font-weight:400;color:var(--text-muted);"></span></h2>
                <p id="evaluar-sol-colaborador" style="margin-top:2px;"></p>
            </div>
            <button class="modal-close" id="btn-cerrar-evaluar" aria-label="Cerrar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <!-- Detalles en solo lectura -->
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="di-label">Monto Solicitado</div>
                    <div class="di-value" id="det-monto">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">Fecha del Viaje</div>
                    <div class="di-value" id="det-fecha">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">CECO / Proyecto</div>
                    <div class="di-value" id="det-ceco">—</div>
                </div>
                <div class="detail-item">
                    <div class="di-label">DNI Colaborador</div>
                    <div class="di-value" id="det-dni">—</div>
                </div>
                <div class="detail-item col-full">
                    <div class="di-label">Estado Actual</div>
                    <div class="di-value" id="det-estado">—</div>
                </div>
                <div class="detail-item col-full">
                    <div class="di-label">Motivo del Viaje</div>
                    <div class="motivo-box" id="det-motivo">—</div>
                </div>
            </div>

            <!-- Error inline -->
            <div id="evaluar-error" style="display:none; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px; margin-top:4px;"></div>
        </div>

        <div class="modal-footer">
            <span class="modal-decision-label" style="margin-right:auto;">Decisión:</span>
            <button class="btn btn-secondary" id="btn-cancelar-evaluar">Cancelar</button>
            <button class="btn btn-warning"  id="btn-observar"  data-estado="observada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                Observar
            </button>
            <button class="btn btn-danger"   id="btn-rechazar"  data-estado="rechazada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                Rechazar
            </button>
            <button class="btn btn-success"  id="btn-aprobar"   data-estado="aprobada">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                Aprobar
            </button>
        </div>
    </div>
</div>


<!-- ╔══════════════════════════════════════════════════════════════╗ -->
<!-- ║  JAVASCRIPT — AdminApp (Vanilla JS, sin dependencias)        ║ -->
<!-- ╚══════════════════════════════════════════════════════════════╝ -->
<script>
(function () {
    'use strict';

    /* ── Configuración inyectada desde PHP ────────────────── */
    const CFG = {
        nonce:   '<?php echo esc_js( $rest_nonce ); ?>',
        apiBase: '<?php echo $api_base; ?>',
    };

    /* ================================================================
       UTILIDADES
       ================================================================ */

    async function apiFetch(endpoint, options = {}) {
        const merged = Object.assign({ headers: {} }, options);
        merged.headers = Object.assign({
            'Content-Type': 'application/json',
            'X-WP-Nonce':   CFG.nonce,
        }, options.headers || {});

        const res  = await fetch(CFG.apiBase + endpoint, merged);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `Error ${res.status}`);
        return data;
    }

    function fmt(num) {
        const n = parseFloat(num);
        return isNaN(n) ? '—' : 'S/. ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function fmtFecha(iso) {
        if (!iso) return '—';
        const p = iso.split('-');
        return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso;
    }

    const estadoLabel = { pendiente:'Pendiente', aprobada:'Aprobada', observada:'Observada', rechazada:'Rechazada', rendida:'Rendida' };

    function badgeHTML(estado) {
        const k = (estado || '').toLowerCase();
        return `<span class="badge badge-${k}">${estadoLabel[k] || estado}</span>`;
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function showToast(type, title, msg = '', duration = 4500) {
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41.0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
        };
        const t = document.createElement('div');
        t.className = `toast toast-${type}`;
        t.innerHTML = `<span class="toast-icon">${icons[type]}</span><div class="toast-body"><strong>${escHtml(title)}</strong>${msg ? `<p>${escHtml(msg)}</p>` : ''}</div>`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => {
            t.style.cssText = 'opacity:0;transform:translateX(20px);transition:all .3s ease;';
            setTimeout(() => t.remove(), 320);
        }, duration);
    }

    function setLoading(btn, on) {
        if (on) {
            btn.disabled = true;
            btn.dataset.orig = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.orig || '';
        }
    }

    /* ================================================================
       DATOS
       ================================================================ */
    let cache = [];

    async function fetchTodas() {
        return await apiFetch('/todas-solicitudes');
    }

    /* ================================================================
       TABLA
       ================================================================ */
    function renderSolicitudesTable(data, filter = '') {
        const tbody = document.getElementById('solicitudes-tbody');
        const q = filter.toLowerCase().trim();

        let rows = data;
        if (q) {
            rows = data.filter(s =>
                (s.colaborador || '').toLowerCase().includes(q) ||
                (s.ceco        || '').toLowerCase().includes(q) ||
                (s.motivo      || '').toLowerCase().includes(q) ||
                String(s.id).includes(q)
            );
        }

        document.getElementById('tbl-counter').textContent =
            `${rows.length} de ${data.length} registros`;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                <p>${q ? 'Sin resultados para "' + escHtml(q) + '".' : 'No hay solicitudes registradas.'}</p>
            </div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(s => {
            const estado   = (s.estado || 'pendiente').toLowerCase();
            const evaluable = estado === 'pendiente' || estado === 'rendida';
            const accion = evaluable
                ? `<button class="btn btn-primary btn-sm action-evaluar" data-id="${s.id}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        Evaluar
                   </button>`
                : `<span style="color:var(--text-light);font-size:12px;">Sin acciones</span>`;

            return `<tr>
                <td class="muted">#${s.id}</td>
                <td><strong>${escHtml(s.colaborador)}</strong></td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${escHtml(s.ceco)}</td>
                <td>${badgeHTML(estado)}</td>
                <td>${accion}</td>
            </tr>`;
        }).join('');

        // Listeners en botones Evaluar
        tbody.querySelectorAll('.action-evaluar').forEach(btn => {
            btn.addEventListener('click', () => {
                const sol = data.find(s => s.id === parseInt(btn.dataset.id, 10));
                if (sol) openEvaluarModal(sol);
            });
        });
    }

    /* ================================================================
       TABLA RESUMEN (últimas 8)
       ================================================================ */
    function renderResumenTable(data) {
        const tbody = document.getElementById('resumen-tbody');

        // KPIs
        let pendientes = 0, montoAprobado = 0, observadas = 0;
        data.forEach(s => {
            const e = (s.estado || '').toLowerCase();
            if (e === 'pendiente') pendientes++;
            if (e === 'aprobada')  montoAprobado += parseFloat(s.monto) || 0;
            if (e === 'observada') observadas++;
        });

        document.getElementById('kpi-pendientes').textContent     = pendientes;
        document.getElementById('kpi-monto-aprobado').textContent = fmt(montoAprobado);
        document.getElementById('kpi-observadas').textContent     = observadas;

        const recent = data.slice(0, 8);
        if (!recent.length) {
            tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-empty"><p>No hay solicitudes registradas.</p></div></td></tr>`;
            return;
        }

        tbody.innerHTML = recent.map(s => `
            <tr>
                <td class="muted">#${s.id}</td>
                <td>${escHtml(s.colaborador)}</td>
                <td>${fmtFecha(s.fecha)}</td>
                <td><strong>${fmt(s.monto)}</strong></td>
                <td>${badgeHTML(s.estado)}</td>
            </tr>
        `).join('');
    }

    /* ================================================================
       CARGA DE DATOS POR VISTA
       ================================================================ */
    async function loadResumen() {
        const tbody = document.getElementById('resumen-tbody');
        tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            renderResumenTable(cache);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
            showToast('error', 'Error al cargar datos', err.message);
        }
    }

    async function loadSolicitudes() {
        const tbody = document.getElementById('solicitudes-tbody');
        tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-loading"><div class="spinner"></div>Cargando...</div></td></tr>`;
        try {
            cache = await fetchTodas();
            const q = document.getElementById('search-solicitudes').value;
            renderSolicitudesTable(cache, q);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="tbl-empty"><p>Error: ${escHtml(err.message)}</p></div></td></tr>`;
            showToast('error', 'Error al cargar solicitudes', err.message);
        }
    }

    /* ================================================================
       MODAL EVALUAR
       ================================================================ */
    let modalSolId = null;

    function openEvaluarModal(sol) {
        modalSolId = sol.id;

        document.getElementById('evaluar-sol-id').textContent          = `#${sol.id}`;
        document.getElementById('evaluar-sol-colaborador').textContent  = sol.colaborador || '';
        document.getElementById('det-monto').textContent  = fmt(sol.monto);
        document.getElementById('det-fecha').textContent  = fmtFecha(sol.fecha);
        document.getElementById('det-ceco').textContent   = sol.ceco  || '—';
        document.getElementById('det-dni').textContent    = sol.dni   || '—';
        document.getElementById('det-motivo').textContent = sol.motivo || '—';
        document.getElementById('det-estado').innerHTML   = badgeHTML(sol.estado);
        document.getElementById('evaluar-error').style.display = 'none';

        // Resetear botones
        ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => {
            setLoading(document.getElementById(id), false);
        });

        document.getElementById('modal-evaluar').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEvaluarModal() {
        document.getElementById('modal-evaluar').classList.remove('open');
        document.body.style.overflow = '';
        modalSolId = null;
    }

    async function handleDecision(nuevoEstado) {
        if (!modalSolId) return;

        const btnMap = { aprobada: 'btn-aprobar', observada: 'btn-observar', rechazada: 'btn-rechazar' };
        const btn    = document.getElementById(btnMap[nuevoEstado]);
        const errEl  = document.getElementById('evaluar-error');

        errEl.style.display = 'none';
        setLoading(btn, true);

        // Deshabilitar los otros botones
        ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => {
            if (id !== btnMap[nuevoEstado]) document.getElementById(id).disabled = true;
        });

        try {
            await apiFetch('/actualizar-estado', {
                method: 'POST',
                body:   JSON.stringify({ id_solicitud: modalSolId, nuevo_estado: nuevoEstado }),
            });

            closeEvaluarModal();

            const labels = { aprobada: 'aprobada ✓', observada: 'marcada como observada', rechazada: 'rechazada' };
            showToast('success', 'Estado actualizado', `Solicitud #${modalSolId} ${labels[nuevoEstado]}.`);

            // Refrescar ambas vistas
            await loadResumen();
            renderSolicitudesTable(cache, document.getElementById('search-solicitudes').value);

        } catch (err) {
            errEl.textContent   = err.message || 'No se pudo actualizar. Intente de nuevo.';
            errEl.style.display = 'block';
            setLoading(btn, false);
            ['btn-aprobar','btn-observar','btn-rechazar'].forEach(id => {
                document.getElementById(id).disabled = false;
            });
        }
    }

    /* ================================================================
       NAVEGACIÓN SPA
       ================================================================ */
    function navigate(viewId) {
        document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');

        document.querySelectorAll('.nav-link').forEach(a =>
            a.classList.toggle('active', a.dataset.view === viewId)
        );

        const names = { 'view-resumen': 'Resumen', 'view-solicitudes': 'Solicitudes Equipo' };
        document.getElementById('topbar-section').textContent = names[viewId] || '';

        if (viewId === 'view-solicitudes') loadSolicitudes();
    }

    /* ================================================================
       BIND DE EVENTOS
       ================================================================ */
    function bindEvents() {
        // Navegación
        document.querySelectorAll('.nav-link').forEach(a => {
            a.addEventListener('click', e => { e.preventDefault(); navigate(a.dataset.view); });
        });

        // Modal evaluar — cerrar
        document.getElementById('btn-cerrar-evaluar').addEventListener('click', closeEvaluarModal);
        document.getElementById('btn-cancelar-evaluar').addEventListener('click', closeEvaluarModal);

        // Cerrar al click en overlay
        document.getElementById('modal-evaluar').addEventListener('click', e => {
            if (e.target === document.getElementById('modal-evaluar')) closeEvaluarModal();
        });

        // Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeEvaluarModal();
        });

        // Botones de decisión
        document.getElementById('btn-aprobar').addEventListener('click',  () => handleDecision('aprobada'));
        document.getElementById('btn-observar').addEventListener('click', () => handleDecision('observada'));
        document.getElementById('btn-rechazar').addEventListener('click', () => handleDecision('rechazada'));

        // Actualizar tabla
        document.getElementById('btn-refrescar').addEventListener('click', loadSolicitudes);

        // Búsqueda en tiempo real
        document.getElementById('search-solicitudes').addEventListener('input', e => {
            renderSolicitudesTable(cache, e.target.value);
        });
    }

    /* ================================================================
       INIT
       ================================================================ */
    function init() {
        bindEvents();
        loadResumen();
    }

    // API pública
    window.AdminApp = { navigate };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
</script>

</body>
</html>

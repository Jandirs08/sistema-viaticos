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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ============================================================
   DESIGN TOKENS — Sistema de Viáticos · Fundación Romero
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

    /* Badge colors */
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

/* ============================================================
   RESET & BASE
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 14px; color: var(--text); background: var(--bg);
    line-height: 1.5; -webkit-font-smoothing: antialiased;
}

/* ============================================================
   LAYOUT — ERP Shell
   ============================================================ */
#erp-shell { display: flex; height: 100vh; overflow: hidden; }

/* ── Sidebar ──────────────────────────────────────────────── */
#erp-sidebar {
    width: var(--sidebar-w); min-width: var(--sidebar-w);
    background: var(--sidebar-bg); display: flex; flex-direction: column;
    height: 100vh; overflow-y: auto; z-index: 100;
    transition: width var(--ease);
}

.sidebar-logo { padding: 20px 18px 16px; border-bottom: 1px solid rgba(255,255,255,.06); }
.sidebar-logo-mark { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.logo-icon {
    width: 36px; height: 36px; background: var(--primary);
    border-radius: var(--radius-sm); display: flex; align-items: center;
    justify-content: center; flex-shrink: 0;
}
.logo-icon svg { fill: #fff; }
.logo-text { display: flex; flex-direction: column; }
.logo-text strong { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.2; }
.logo-text span   { font-size: 10.5px; color: rgba(255,255,255,.45); font-weight: 400; letter-spacing: .02em; }

.sidebar-section       { padding: 16px 0 8px; }
.sidebar-section-label { padding: 0 16px 8px; font-size: 10px; font-weight: 600;
                         letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.30); }
.sidebar-nav           { list-style: none; padding: 0 8px; }
.sidebar-nav li        { margin-bottom: 2px; }
.sidebar-nav a {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px;
    border-radius: var(--radius-sm); color: rgba(255,255,255,.62);
    text-decoration: none; font-size: 13.5px; font-weight: 500;
    transition: background var(--ease), color var(--ease); cursor: pointer;
}
.sidebar-nav a .nav-icon { width: 18px; height: 18px; flex-shrink: 0; opacity: .7; transition: opacity var(--ease); }
.sidebar-nav a:hover { background: var(--sidebar-hover); color: #fff; }
.sidebar-nav a:hover .nav-icon { opacity: 1; }
.sidebar-nav a.active { background: rgba(218,91,62,.18); color: #f4a58f; }
.sidebar-nav a.active .nav-icon { opacity: 1; }

.sidebar-footer {
    margin-top: auto; padding: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.sidebar-user  { display: flex; align-items: center; gap: 10px; }
.user-avatar {
    width: 32px; height: 32px; border-radius: 50%; background: var(--primary);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.user-info { flex: 1; overflow: hidden; }
.user-info .u-name { display: block; font-size: 12.5px; color: #fff; font-weight: 600;
                     white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-info .u-role { display: block; font-size: 10.5px; color: rgba(255,255,255,.40); }

/* ── Main area ──────────────────────────────────────────────── */
#erp-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── Topbar ─────────────────────────────────────────────────── */
#erp-topbar {
    height: var(--topbar-h); min-height: var(--topbar-h);
    background: var(--surface); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; gap: 16px; box-shadow: var(--shadow-sm); z-index: 50;
}
.topbar-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-muted); }
.topbar-breadcrumb span { color: var(--text); font-weight: 600; }
.topbar-actions { display: flex; align-items: center; gap: 16px; }
.topbar-user-info { text-align: right; }
.topbar-user-info .t-name { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
.topbar-user-info .t-role { display: block; font-size: 11px; color: var(--text-muted); }
.topbar-user-meta {
    display: flex; align-items: center; justify-content: flex-end; gap: 8px;
    flex-wrap: wrap; margin-top: 6px;
}
.user-meta-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 9px; border-radius: 999px; background: var(--bg);
    border: 1px solid var(--border-light); color: var(--text-muted);
    font-size: 11px; line-height: 1;
}
.user-meta-chip strong { color: var(--text); font-weight: 600; }
.btn-logout {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px;
    background: transparent; border: 1px solid var(--border); border-radius: var(--radius-sm);
    color: var(--text-muted); font-size: 12.5px; font-weight: 500; cursor: pointer;
    text-decoration: none; transition: all var(--ease);
}
.btn-logout:hover { background: #FEF2F2; border-color: #FECACA; color: #DC2626; }

/* ── Content area ───────────────────────────────────────────── */
#erp-content { flex: 1; overflow-y: auto; padding: 28px; }

/* ============================================================
   VIEWS
   ============================================================ */
.erp-view { display: none; animation: fadeIn .18s ease; }
.erp-view.active { display: block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

/* ── Page header ─────────────────────────────────────────── */
.page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
}
.page-header-left h1 { font-size: 20px; font-weight: 700; color: var(--text); line-height: 1.3; }
.page-header-left p  { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

/* ── KPI / Stat cards ────────────────────────────────────── */
.kpi-grid, .stat-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px; margin-bottom: 28px;
}
.kpi-card, .stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-sm); transition: box-shadow var(--ease), transform var(--ease);
}
.kpi-card:hover, .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.kpi-icon, .stat-icon {
    width: 46px; height: 46px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icon.yellow, .stat-icon.yellow { background: #FEF3C7; }
.kpi-icon.green,  .stat-icon.green  { background: #D1FAE5; }
.kpi-icon.orange, .stat-icon.orange { background: #FFEDD5; }
.kpi-icon.red,    .stat-icon.red    { background: #FEE2E2; }
.kpi-icon svg, .stat-icon svg       { width: 24px; height: 24px; }
.kpi-num, .stat-num   { font-size: 28px; font-weight: 700; line-height: 1; }
.kpi-label, .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 3px; }

/* ── Card ────────────────────────────────────────────────── */
.card { background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border-light);
    gap: 12px; flex-wrap: wrap;
}
.card-header-title, .card-title { font-size: 14.5px; font-weight: 600; color: var(--text); }
.card-header-subtitle, .card-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 1px; }

/* ── Tables ──────────────────────────────────────────────── */
.table-wrapper, .table-wrap { overflow-x: auto; }
table.erp-table, table.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.erp-table thead th, .tbl thead th {
    padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted);
    background: #F8FAFC; white-space: nowrap; border-bottom: 1px solid var(--border);
}
.erp-table tbody tr, .tbl tbody tr { border-bottom: 1px solid var(--border-light); transition: background var(--ease); }
.erp-table tbody tr:last-child, .tbl tbody tr:last-child { border-bottom: none; }
.erp-table tbody tr:hover, .tbl tbody tr:hover { background: #FAFBFC; }
.erp-table td, .tbl td { padding: 12px 16px; color: var(--text); vertical-align: middle; }
.erp-table td.text-muted, .tbl td.muted { color: var(--text-muted); }

.table-empty, .tbl-empty { text-align: center; padding: 48px 20px; color: var(--text-muted); }
.table-empty svg, .tbl-empty svg { width: 40px; height: 40px; margin: 0 auto 12px; display: block; opacity: .3; }
.table-empty p { font-size: 13.5px; }
.table-loading, .tbl-loading {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 40px; color: var(--text-muted); font-size: 13px;
}

/* ── Search input ─────────────────────────────────────────── */
.search-input {
    padding: 8px 12px; border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: 13px; color: var(--text); font-family: inherit;
    outline: none; transition: border-color var(--ease), box-shadow var(--ease); width: 220px;
}
.search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(218,91,62,.15); }

/* ── Badges ──────────────────────────────────────────────── */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; white-space: nowrap;
}
.badge::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-pendiente { background: var(--badge-pendiente-bg); color: var(--badge-pendiente-text); }
.badge-aprobada  { background: var(--badge-aprobada-bg);  color: var(--badge-aprobada-text);  }
.badge-observada { background: var(--badge-observada-bg); color: var(--badge-observada-text); }
.badge-rechazada { background: var(--badge-rechazada-bg); color: var(--badge-rechazada-text); }
.badge-rendida   { background: var(--badge-rendida-bg);   color: var(--badge-rendida-text);   }

/* ── Buttons ─────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; cursor: pointer;
    border: 1px solid transparent; text-decoration: none;
    transition: all var(--ease); white-space: nowrap; font-family: inherit;
}
.btn:disabled { opacity: .55; cursor: not-allowed; }
.btn-primary   { background: var(--primary); color: #fff; border-color: var(--primary); }
.btn-primary:hover:not(:disabled) { background: var(--primary-dark); border-color: var(--primary-dark); box-shadow: 0 2px 8px rgba(218,91,62,.35); }
.btn-secondary { background: var(--surface); color: var(--text); border-color: var(--border); }
.btn-secondary:hover:not(:disabled) { background: var(--bg); border-color: #CBD5E0; }
.btn-ghost     { background: transparent; color: var(--text-muted); border-color: transparent; padding: 6px 10px; }
.btn-ghost:hover:not(:disabled) { background: var(--bg); color: var(--text); }
.btn-sm        { padding: 5px 11px; font-size: 12px; }
.btn-success   { background: #F0FDF4; color: #15803D; border-color: #BBF7D0; }
.btn-success:hover:not(:disabled) { background: #DCFCE7; box-shadow: 0 2px 8px rgba(21,128,61,.2); }
.btn-warning   { background: #FFF7ED; color: #9A3412; border-color: #FED7AA; }
.btn-warning:hover:not(:disabled) { background: #FFEDD5; }
.btn-danger    { background: #FEF2F2; color: #DC2626; border-color: #FECACA; }
.btn-danger:hover:not(:disabled)  { background: #FEE2E2; box-shadow: 0 2px 8px rgba(220,38,38,.2); }
.btn-white { background: rgba(255,255,255,.18); color: #fff; border-color: rgba(255,255,255,.35); backdrop-filter: blur(4px); }
.btn-white:hover { background: rgba(255,255,255,.28); }

/* ── Spinner ─────────────────────────────────────────────── */
.spinner {
    width: 20px; height: 20px; border: 2px solid var(--border);
    border-top-color: var(--primary); border-radius: 50%;
    animation: spin .7s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Welcome banner ──────────────────────────────────────── */
.welcome-banner {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-md); padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
}
.welcome-banner h2 { font-size: 20px; font-weight: 700; color: #fff; }
.welcome-banner p  { font-size: 13.5px; color: rgba(255,255,255,.80); margin-top: 4px; }

/* ── Modals ──────────────────────────────────────────────── */
.modal-overlay, .overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.55); backdrop-filter: blur(3px);
    z-index: 1000; align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open, .overlay.open { display: flex; animation: overlayIn .18s ease; }
@keyframes overlayIn { from { opacity:0; } to { opacity:1; } }
.modal {
    background: var(--surface); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg); width: 100%; max-width: 580px;
    max-height: 92vh; overflow-y: auto; animation: modalIn .2s ease;
}
.modal-lg { max-width: 720px; }
@keyframes modalIn { from { opacity:0; transform:translateY(-16px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 18px; border-bottom: 1px solid var(--border);
}
.modal-header h2, .modal-header-info h2 { font-size: 16px; font-weight: 700; color: var(--text); }
.modal-header p,  .modal-header-info p  { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }
.modal-close {
    width: 32px; height: 32px; border: none; background: var(--bg);
    border-radius: var(--radius-sm); display: flex; align-items: center;
    justify-content: center; cursor: pointer; color: var(--text-muted);
    transition: background var(--ease), color var(--ease); flex-shrink: 0;
}
.modal-close:hover { background: #FEE2E2; color: #DC2626; }
.modal-body { padding: 24px; }
.modal-footer {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 10px; padding: 16px 24px; border-top: 1px solid var(--border);
    background: #FAFBFD; border-radius: 0 0 var(--radius-lg) var(--radius-lg); flex-wrap: wrap;
}

/* ── Forms ───────────────────────────────────────────────── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-grid .col-full { grid-column: 1 / -1; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 12.5px; font-weight: 600; color: var(--text); }
.form-label .required { color: var(--primary); margin-left: 2px; }
.form-control {
    width: 100%; padding: 9px 12px; border: 1px solid #CBD5E0;
    border-radius: var(--radius-sm); font-size: 13.5px; color: var(--text);
    background: var(--surface); font-family: inherit;
    transition: border-color var(--ease), box-shadow var(--ease); outline: none;
}
.form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(218,91,62,.15); }
.form-control:invalid:not(:placeholder-shown) { border-color: #FC8181; }
textarea.form-control { resize: vertical; min-height: 88px; }
.form-hint  { font-size: 11.5px; color: var(--text-muted); }
.form-error { font-size: 11.5px; color: #DC2626; display: none; }
.form-error.visible { display: block; }
.input-prefix-wrap { display: flex; }
.input-prefix {
    padding: 9px 10px; background: #F1F5F9; border: 1px solid #CBD5E0;
    border-right: none; border-radius: var(--radius-sm) 0 0 var(--radius-sm);
    font-size: 13px; color: var(--text-muted); font-weight: 500; white-space: nowrap;
}
.input-prefix-wrap .form-control { border-radius: 0 var(--radius-sm) var(--radius-sm) 0; }

/* ── Toast ───────────────────────────────────────────────── */
#toast-container {
    position: fixed; top: 20px; right: 20px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; max-width: 360px; width: 100%;
}
.toast {
    display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px;
    border-radius: var(--radius-md); box-shadow: var(--shadow-md);
    font-size: 13px; font-weight: 500; animation: toastIn .25s ease; border-left: 4px solid transparent;
}
@keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
.toast-success { background: #F0FDF4; border-color: #22C55E; color: #15803D; }
.toast-error   { background: #FEF2F2; border-color: #EF4444; color: #DC2626; }
.toast-info    { background: #EFF6FF; border-color: #3B82F6; color: #1D4ED8; }
.toast-icon    { flex-shrink: 0; margin-top: 1px; }
.toast-body    { flex: 1; }
.toast-body strong { display: block; font-weight: 700; }
.toast-body p      { font-weight: 400; margin-top: 2px; opacity: .85; }

/* ── Admin-specific: detail modal grid ──────────────────── */
.detail-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
.detail-item .di-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted); margin-bottom: 4px;
}
.detail-item .di-value { font-size: 14px; font-weight: 500; color: var(--text); }
.detail-item.col-full  { grid-column: 1 / -1; }
.motivo-box {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 12px 14px;
    font-size: 13px; color: var(--text); line-height: 1.6;
    white-space: pre-wrap; word-break: break-word;
}
.modal-decision-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted); margin-bottom: 10px; display: block;
}

/* ── Colaborador-specific: gasto items ───────────────────── */
.gastos-list { margin-top: 20px; }
.gastos-list h4 {
    font-size: 13px; font-weight: 600; color: var(--text-muted);
    margin-bottom: 10px; text-transform: uppercase; letter-spacing: .05em;
}
.gasto-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); margin-bottom: 6px; font-size: 13px;
}
.gasto-item .gi-meta   { color: var(--text-muted); font-size: 12px; }
.gasto-item .gi-amount { font-weight: 700; color: var(--text); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    #erp-sidebar { position: fixed; left: -100%; transition: left .25s ease; }
    #erp-sidebar.open { left: 0; }
    .form-grid { grid-template-columns: 1fr; }
    .form-grid .col-full { grid-column: 1; }
    .detail-grid { grid-template-columns: 1fr; }
    .detail-item.col-full { grid-column: 1; }
    #erp-content { padding: 16px; }
    .kpi-grid, .stat-grid { grid-template-columns: 1fr 1fr; }
    .search-input { width: 100%; }
}
</style>
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

        <!-- Nav items injected by each view part -->
        <div class="sidebar-section">
            <p class="sidebar-section-label">Menú</p>
            <ul class="sidebar-nav" id="sidebar-nav-items">
                <!-- populated by view-admin.php or view-colaborador.php via inline script -->
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar" aria-hidden="true"><?php echo esc_html( $args['user_initials'] ?: 'U' ); ?></div>
                <div class="user-info">
                    <strong class="u-name"><?php echo $args['user_name']; ?></strong>
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
                <span id="topbar-section-name"></span>
            </nav>
            <div class="topbar-actions">
                <div class="topbar-user-info" aria-label="Usuario autenticado">
                    <strong class="t-name"><?php echo $args['user_name']; ?></strong>
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

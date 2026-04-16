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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lexend:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

    /* Badge colors: solicitud */
    --badge-solicitud-pendiente-bg:  #FEF3C7; --badge-solicitud-pendiente-text:  #92400E; --badge-solicitud-pendiente-border:  #FCD34D;
    --badge-solicitud-aprobada-bg:   #D1FAE5; --badge-solicitud-aprobada-text:   #065F46; --badge-solicitud-aprobada-border:   #6EE7B7;
    --badge-solicitud-observada-bg:  #FFEDD5; --badge-solicitud-observada-text:  #9A3412; --badge-solicitud-observada-border:  #FDBA74;
    --badge-solicitud-rechazada-bg:  #FEE2E2; --badge-solicitud-rechazada-text:  #991B1B; --badge-solicitud-rechazada-border:  #FCA5A5;

    /* Badge colors: rendicion */
    --badge-rendicion-no-disponible-bg: #E5E7EB; --badge-rendicion-no-disponible-text: #6B7280; --badge-rendicion-no-disponible-border: #D1D5DB;
    --badge-rendicion-no-iniciada-bg:   #E0F2FE; --badge-rendicion-no-iniciada-text:   #075985; --badge-rendicion-no-iniciada-border:   #7DD3FC;
    --badge-rendicion-en-proceso-bg:    #DBEAFE; --badge-rendicion-en-proceso-text:    #1D4ED8; --badge-rendicion-en-proceso-border:    #93C5FD;
    --badge-rendicion-en-revision-bg:   #E0E7FF; --badge-rendicion-en-revision-text:   #4338CA; --badge-rendicion-en-revision-border:   #A5B4FC;
    --badge-rendicion-aprobada-bg:      #DCFCE7; --badge-rendicion-aprobada-text:      #166534; --badge-rendicion-aprobada-border:      #86EFAC;
    --badge-rendicion-observada-bg:     #FEF3C7; --badge-rendicion-observada-text:     #B45309; --badge-rendicion-observada-border:     #FCD34D;
    --badge-rendicion-rechazada-bg:     #FEE2E2; --badge-rendicion-rechazada-text:     #B91C1C; --badge-rendicion-rechazada-border:     #FCA5A5;

    --estado-solicitud-panel-bg: linear-gradient(180deg, #FFF7ED 0%, #FFFFFF 100%);
    --estado-solicitud-panel-border: #FED7AA;
    --estado-rendicion-panel-bg: linear-gradient(180deg, #EFF6FF 0%, #FFFFFF 100%);
    --estado-rendicion-panel-border: #BFDBFE;

    --shadow-sm: 0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
    --shadow-md: 0 4px 12px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.04);
    --shadow-lg: 0 10px 30px rgba(0,0,0,.12), 0 4px 8px rgba(0,0,0,.06);
    --radius-sm: 8px; --radius-md: 12px; --radius-lg: 18px;
    --sidebar-w: 240px; --topbar-h: 60px;
    --ease: .2s ease;
    --font-display: 'Lexend', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ============================================================
   RESET & BASE
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    font-family: var(--font-body);
    font-size: 14px; color: var(--text); background: var(--bg);
    line-height: 1.55; -webkit-font-smoothing: antialiased;
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
.page-header-left h1 { font-family: var(--font-display); font-size: 21px; font-weight: 700; color: var(--text); line-height: 1.3; letter-spacing: -.01em; }
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
.kpi-num, .stat-num   { font-family: var(--font-display); font-size: 28px; font-weight: 700; line-height: 1; }
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
.tbl td .badge, .erp-table td .badge { font-weight: 700; }

.worktray-row { cursor: pointer; }
.worktray-row td { transition: background var(--ease), border-color var(--ease); }
.worktray-row:hover td { background: #F8FAFC; }
.worktray-row:focus { outline: none; }
.worktray-row.is-needs-action td:first-child {
    border-left: 3px solid #DA5B3E;
    padding-left: 13px;
}
.worktray-row.is-needs-action td {
    background: linear-gradient(90deg, rgba(218,91,62,.06) 0%, rgba(218,91,62,.02) 26%, transparent 100%);
}
.worktray-row.is-review-action td:first-child {
    border-left-color: #2563EB;
}
.worktray-row.is-review-action td {
    background: linear-gradient(90deg, rgba(37,99,235,.08) 0%, rgba(37,99,235,.03) 26%, transparent 100%);
}
.worktray-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.worktray-note {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.worktray-note.progress { color: #1D4ED8; }
.worktray-note.warning { color: #B45309; }
.worktray-note.success { color: #166534; }
.worktray-note.danger { color: #B91C1C; }
.worktray-person {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.worktray-person strong {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}
.worktray-person span {
    font-size: 12px;
    color: var(--text-muted);
}

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
    padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; white-space: nowrap;
    border: 1px solid transparent;
}
.badge::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-solicitud-pendiente { background: var(--badge-solicitud-pendiente-bg); color: var(--badge-solicitud-pendiente-text); border-color: var(--badge-solicitud-pendiente-border); }
.badge-solicitud-aprobada  { background: var(--badge-solicitud-aprobada-bg);  color: var(--badge-solicitud-aprobada-text);  border-color: var(--badge-solicitud-aprobada-border); }
.badge-solicitud-observada { background: var(--badge-solicitud-observada-bg); color: var(--badge-solicitud-observada-text); border-color: var(--badge-solicitud-observada-border); }
.badge-solicitud-rechazada { background: var(--badge-solicitud-rechazada-bg); color: var(--badge-solicitud-rechazada-text); border-color: var(--badge-solicitud-rechazada-border); }
.badge-rendicion-no_disponible { background: var(--badge-rendicion-no-disponible-bg); color: var(--badge-rendicion-no-disponible-text); border-color: var(--badge-rendicion-no-disponible-border); }
.badge-rendicion-no_iniciada   { background: var(--badge-rendicion-no-iniciada-bg);   color: var(--badge-rendicion-no-iniciada-text);   border-color: var(--badge-rendicion-no-iniciada-border); }
.badge-rendicion-en_proceso    { background: var(--badge-rendicion-en-proceso-bg);    color: var(--badge-rendicion-en-proceso-text);    border-color: var(--badge-rendicion-en-proceso-border); }
.badge-rendicion-en_revision   { background: var(--badge-rendicion-en-revision-bg);   color: var(--badge-rendicion-en-revision-text);   border-color: var(--badge-rendicion-en-revision-border); }
.badge-rendicion-aprobada      { background: var(--badge-rendicion-aprobada-bg);      color: var(--badge-rendicion-aprobada-text);      border-color: var(--badge-rendicion-aprobada-border); }
.badge-rendicion-observada     { background: var(--badge-rendicion-observada-bg);     color: var(--badge-rendicion-observada-text);     border-color: var(--badge-rendicion-observada-border); }
.badge-rendicion-rechazada     { background: var(--badge-rendicion-rechazada-bg);     color: var(--badge-rendicion-rechazada-text);     border-color: var(--badge-rendicion-rechazada-border); }

.estado-group {
    display: flex; flex-direction: column; gap: 8px;
    padding: 14px 16px; border-radius: 14px; border: 1px solid var(--border);
    min-height: 94px;
}
.estado-group-solicitud { background: var(--estado-solicitud-panel-bg); border-color: var(--estado-solicitud-panel-border); }
.estado-group-rendicion { background: var(--estado-rendicion-panel-bg); border-color: var(--estado-rendicion-panel-border); }
.estado-group-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em;
}
.estado-group-solicitud .estado-group-label { color: #9A3412; }
.estado-group-rendicion .estado-group-label { color: #1D4ED8; }
.estado-group-note { font-size: 12px; color: var(--text-muted); }

.badge-ref {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 44px; padding: 4px 10px; border-radius: 999px;
    background: #F8FAFC; border: 1px solid var(--border); color: var(--text);
    font-size: 11.5px; font-weight: 700;
}

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
.modal-header h2, .modal-header-info h2 { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text); }
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

/* ── Gasto accordion ─────────────────────────────────────── */
.gasto-acc-list { display: flex; flex-direction: column; gap: 6px; }
.gasto-acc-item {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    background: var(--surface); overflow: hidden;
    transition: box-shadow var(--ease);
}
.gasto-acc-item.is-open { box-shadow: var(--shadow-sm); border-color: #CBD5E0; }
.gasto-acc-header {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 16px; cursor: pointer; user-select: none;
    background: var(--bg); transition: background var(--ease);
}
.gasto-acc-header:hover { background: #EDF2F7; }
.gasto-acc-item.is-open .gasto-acc-header { background: #EDF2F7; border-bottom: 1px solid var(--border); }
.gasto-acc-chevron {
    width: 16px; height: 16px; flex-shrink: 0; color: var(--text-muted);
    transition: transform .2s ease; display: flex; align-items: center;
}
.gasto-acc-item.is-open .gasto-acc-chevron { transform: rotate(90deg); }
.gasto-acc-tipo {
    font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 999px;
    background: var(--primary-light); color: var(--primary-dark); white-space: nowrap;
}
.gasto-acc-summary { flex: 1; min-width: 0; }
.gasto-acc-summary .gas-label {
    font-size: 12.5px; font-weight: 600; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gasto-acc-summary .gas-sub {
    font-size: 11.5px; color: var(--text-muted); margin-top: 1px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gasto-acc-importe { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; }
.gasto-acc-body {
    max-height: 0; overflow: hidden;
    transition: max-height .25s ease, padding .2s ease;
    padding: 0 16px;
}
.gasto-acc-item.is-open .gasto-acc-body { max-height: 500px; padding: 14px 16px; }
.gasto-acc-fields {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}
.gasto-acc-field { display: flex; flex-direction: column; gap: 3px; }
.gaf-label {
    font-size: 10.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted);
}
.gaf-value { font-size: 13px; color: var(--text); word-break: break-word; }

/* ── Adjuntos panel (inside gasto accordion body) ──────────── */
.gasto-adj-panel {
    margin-top: 16px;
    padding: 14px 16px 12px;
    background: #F8FAFC;
    border: 1px solid #CBD5E0;
    border-radius: 10px;
}
.gasto-adj-title {
    font-size: 11.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #4A5568;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 10px; gap: 8px;
}
.gasto-adj-title svg { flex-shrink: 0; }
.gasto-adj-list { display: flex; flex-direction: column; gap: 6px; }
.gasto-adj-empty {
    font-size: 12.5px; color: var(--text-muted);
    padding: 8px 0; font-style: italic;
}
.gasto-adj-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; background: #fff;
    border: 1px solid #E2E8F0; border-radius: 8px;
    font-size: 12.5px; transition: box-shadow var(--ease);
}
.gasto-adj-item:hover { box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.gasto-adj-icon {
    width: 30px; height: 30px; border-radius: 6px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 9.5px; font-weight: 800; color: #fff; letter-spacing: .03em;
}
.gasto-adj-icon.pdf  { background: #E53E3E; }
.gasto-adj-icon.img  { background: #38A169; }
.gasto-adj-icon.xml  { background: #D69E2E; }
.gasto-adj-icon.file { background: #718096; }
.gasto-adj-name {
    flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis;
    white-space: nowrap; color: var(--text); font-weight: 500;
}
.gasto-adj-actions { display: flex; gap: 5px; flex-shrink: 0; }
.gasto-adj-btn {
    font-size: 11.5px; padding: 4px 10px;
    border: 1px solid #CBD5E0; border-radius: 6px;
    background: #fff; cursor: pointer;
    color: #4A5568; transition: all var(--ease); font-weight: 500;
}
.gasto-adj-btn:hover { background: #EDF2F7; color: var(--text); border-color: #A0AEC0; }
.gasto-adj-btn.del { color: #C53030; border-color: #FEB2B2; background: #FFF5F5; }
.gasto-adj-btn.del:hover { background: #FED7D7; border-color: #FC8181; }
/* Upload area (accordion - unused, removed) */
.gasto-adj-loading { font-size: 12px; color: var(--text-muted); font-style: italic; }

/* ── Modal: adjuntos section inside "Registrar Gasto" form ── */
.rg-adj-section {
    margin-top: 18px;
    padding: 14px 16px;
    background: #F8FAFC;
    border: 1px solid #CBD5E0;
    border-radius: 10px;
}
.rg-adj-header {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: #4A5568; margin-bottom: 10px;
}
.rg-adj-file-list { display: flex; flex-direction: column; gap: 5px; margin-bottom: 8px; }
.rg-adj-file-item {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 10px; background: #fff;
    border: 1px solid #E2E8F0; border-radius: 7px;
    font-size: 12px;
}
.rg-adj-file-icon {
    width: 26px; height: 26px; border-radius: 5px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 800; color: #fff;
}
.rg-adj-file-icon.pdf  { background: #E53E3E; }
.rg-adj-file-icon.img  { background: #38A169; }
.rg-adj-file-icon.xml  { background: #D69E2E; }
.rg-adj-file-icon.file { background: #718096; }
.rg-adj-file-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rg-adj-file-size { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
.rg-adj-remove {
    background: none; border: none; cursor: pointer;
    color: #A0AEC0; padding: 2px; display: flex; align-items: center;
    border-radius: 4px; transition: color var(--ease);
}
.rg-adj-remove:hover { color: #E53E3E; }
.rg-adj-pick-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: #EBF8FF; border: 1px solid #90CDF4;
    border-radius: 7px; cursor: pointer;
    font-size: 12px; font-weight: 600; color: #2B6CB0;
    transition: all var(--ease);
}
.rg-adj-pick-btn:hover { background: #BEE3F8; border-color: #63B3ED; }


/* ── Liquidación — vista de documento formal ─────────────────────────── */
.liq-view-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap;
    margin-bottom: 20px;
}
.liq-view-toolbar .liq-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 500; color: var(--text-muted);
    background: none; border: none; cursor: pointer;
    padding: 6px 0; transition: color var(--ease);
}
.liq-view-toolbar .liq-back-btn:hover { color: var(--primary); }
.liq-view-toolbar .liq-actions { display: flex; gap: 8px; }
.liq-doc {
    background: #fff;
    border: 1px solid #CBD5E0;
    border-radius: 12px;
    overflow: hidden;
    font-family: inherit;
    max-width: 900px;
    margin: 0 auto;
}
/* Document header */
.liq-doc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 24px 32px; background: #1A365D; color: #fff; gap: 16px;
}
.liq-doc-header-title { font-family: var(--font-display); font-size: 18px; font-weight: 800; letter-spacing: .01em; }
.liq-doc-header-sub { font-size: 12px; opacity: .75; margin-top: 2px; }
.liq-doc-header-meta { text-align: right; font-size: 12px; opacity: .8; }
.liq-doc-header-meta strong { display: block; font-size: 15px; opacity: 1; font-weight: 700; }
/* Info grid */
.liq-doc-info {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 0; border-bottom: 1px solid #E2E8F0;
}
.liq-info-cell {
    padding: 16px 20px; border-right: 1px solid #E2E8F0;
}
.liq-info-cell:last-child { border-right: none; }
.liq-info-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #718096; margin-bottom: 3px;
}
.liq-info-value { font-size: 14px; font-weight: 600; color: #1A202C; }
.liq-info-value.muted { font-weight: 400; color: #4A5568; }
/* Table */
.liq-doc-table-wrap {
    overflow-x: auto; border-bottom: 1px solid #E2E8F0;
}
.liq-doc-table {
    width: 100%; border-collapse: collapse; font-size: 12.5px;
}
.liq-doc-table thead tr {
    background: #EDF2F7;
}
.liq-doc-table th {
    padding: 10px 12px; text-align: left;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #4A5568; white-space: nowrap;
    border-bottom: 2px solid #CBD5E0;
}
.liq-doc-table th.num, .liq-doc-table td.num { text-align: right; }
.liq-doc-table tbody tr {
    border-bottom: 1px solid #EDF2F7;
    transition: background var(--ease);
}
.liq-doc-table tbody tr:last-child { border-bottom: none; }
.liq-doc-table tbody tr:hover { background: #F7FAFC; }
.liq-doc-table td { padding: 9px 12px; vertical-align: middle; color: #2D3748; }
.liq-doc-table td.muted { color: #718096; font-size: 11.5px; }
/* Totals */
.liq-doc-totals {
    display: flex; align-items: stretch; justify-content: flex-end;
    gap: 0; border-top: 2px solid #CBD5E0;
}
.liq-total-cell {
    padding: 14px 20px; min-width: 160px; text-align: center;
    border-right: 1px solid #E2E8F0;
}
.liq-total-cell:last-child { border-right: none; }
.liq-total-label {
    font-size: 10.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #718096; margin-bottom: 4px;
}
.liq-total-value { font-size: 17px; font-weight: 800; }
.liq-total-value.blue { color: #2B6CB0; }
.liq-total-value.green { color: #276749; }
.liq-total-value.amber { color: #744210; }
.liq-total-value.red { color: #9B2C2C; }
/* Footer */
.liq-doc-footer {
    padding: 12px 24px; background: #F7FAFC;
    border-top: 1px solid #E2E8F0;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 11px; color: #A0AEC0;
}
/* Empty/loading state */
.liq-doc-empty { padding: 40px; text-align: center; color: var(--text-muted); font-size: 13px; }

/* ============================================================
   SECTION BLOCKS — Enterprise Soft-UI Components
   ============================================================ */
.section-block {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.02);
    margin-bottom: 20px;
    overflow: hidden;
    transition: box-shadow .2s ease;
}
.section-block:last-child { margin-bottom: 0; }
.section-block:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.06), 0 1px 3px rgba(0,0,0,.04);
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-light);
    background: linear-gradient(180deg, #FAFBFC 0%, var(--surface) 100%);
    gap: 12px;
    flex-wrap: wrap;
}
.section-header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: .01em;
}
.section-header-title svg {
    width: 16px;
    height: 16px;
    color: var(--primary);
    flex-shrink: 0;
}
.section-header-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}
.section-body {
    padding: 18px 20px;
}

/* ── Estados row ─────────────────────────────────────────── */
.estados-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.estado-panel {
    padding: 16px 18px;
    border-radius: 10px;
    border: 1px solid var(--border);
    transition: box-shadow .2s ease;
}
.estado-panel:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,.05);
}
.estado-panel-solicitud {
    background: var(--estado-solicitud-panel-bg);
    border-color: var(--estado-solicitud-panel-border);
}
.estado-panel-rendicion {
    background: var(--estado-rendicion-panel-bg);
    border-color: var(--estado-rendicion-panel-border);
}
.estado-panel-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #718096;
    margin-bottom: 8px;
}
.estado-panel-solicitud .estado-panel-label { color: #9A3412; }
.estado-panel-rendicion .estado-panel-label { color: #1D4ED8; }
.estado-panel-badge {
    display: flex;
    align-items: center;
}

/* ── Resumen Económico ───────────────────────────────────── */
.resumen-economico {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
.resumen-card {
    padding: 18px 16px;
    border-radius: 10px;
    border: 1px solid var(--border-light);
    background: var(--bg);
    text-align: center;
    position: relative;
    transition: box-shadow .2s ease, transform .2s ease;
}
.resumen-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transform: translateY(-1px);
}
.resumen-card-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.resumen-card-value {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
}
/* Card color variants */
.resumen-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 12px 12px 0 0;
}
.resumen-card.monto-solicitado::before { background: #3B82F6; }
.resumen-card.total-rendido::before { background: #10B981; }
.resumen-card.saldo::before { background: #F59E0B; }
.resumen-card.saldo-negativo::before { background: #EF4444; }
.resumen-card.monto-solicitado {
    background: linear-gradient(180deg, #EFF6FF 0%, #F8FAFC 100%);
    border-color: #BFDBFE;
}
.resumen-card.monto-solicitado .resumen-card-value { color: #1D4ED8; }
.resumen-card.total-rendido {
    background: linear-gradient(180deg, #ECFDF5 0%, #F8FAFC 100%);
    border-color: #A7F3D0;
}
.resumen-card.total-rendido .resumen-card-value { color: #065F46; }
.resumen-card.saldo {
    background: linear-gradient(180deg, #FFFBEB 0%, #F8FAFC 100%);
    border-color: #FDE68A;
}
.resumen-card.saldo .resumen-card-value { color: #92400E; }
.resumen-card.saldo-negativo {
    background: linear-gradient(180deg, #FEF2F2 0%, #F8FAFC 100%);
    border-color: #FECACA;
}
.resumen-card.saldo-negativo .resumen-card-value,
.resumen-card-value.saldo-negativo { color: #991B1B; }

/* ── Datos Generales grid ────────────────────────────────── */
.datos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}
.dato-item {
    padding: 10px 0;
}
.dato-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.dato-value {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
}
.dato-value.muted {
    font-weight: 400;
    color: #4A5568;
    line-height: 1.6;
}
.dato-motivo {
    grid-column: 1 / -1;
    padding: 12px 0 0;
    border-top: 1px solid var(--border-light);
    margin-top: 4px;
}

/* ── Estado Alerta banners ───────────────────────────────── */
.timeline-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.timeline-item {
    display: grid;
    grid-template-columns: 18px 1fr;
    gap: 14px;
    align-items: flex-start;
}
.timeline-marker {
    position: relative;
    display: flex;
    justify-content: center;
    min-height: 48px;
}
.timeline-marker::after {
    content: '';
    position: absolute;
    top: 14px;
    bottom: -14px;
    left: 50%;
    width: 2px;
    transform: translateX(-50%);
    background: linear-gradient(180deg, #CBD5E0 0%, #E2E8F0 100%);
}
.timeline-item:last-child .timeline-marker::after {
    display: none;
}
.timeline-dot {
    width: 10px;
    height: 10px;
    margin-top: 4px;
    border-radius: 999px;
    background: linear-gradient(135deg, #2563EB 0%, #F97316 100%);
    box-shadow: 0 0 0 3px #FFFFFF, 0 0 0 4px #E2E8F0;
}
.timeline-content {
    padding-bottom: 16px;
}
.timeline-item:last-child .timeline-content {
    padding-bottom: 0;
}
.timeline-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
}
.timeline-meta {
    margin-top: 4px;
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.5;
}
.timeline-empty {
    font-size: 13px;
    color: var(--text-muted);
}

.estado-alerta {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 16px;
    border: 1px solid transparent;
}
.estado-alerta-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    margin-top: 1px;
}
.estado-alerta-content strong {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
}
.estado-alerta-content p {
    margin: 4px 0 0;
    font-size: 12.5px;
    opacity: .85;
}
.estado-alerta-observada {
    background: #FFFBEB;
    border-color: #FDE68A;
}
.estado-alerta-observada strong { color: #92400E; }
.estado-alerta-observada p { color: #78350F; }
.estado-alerta-rechazada {
    background: #FEF2F2;
    border-color: #FECACA;
}
.estado-alerta-rechazada strong { color: #991B1B; }
.estado-alerta-rechazada p { color: #7F1D1D; }
.estado-alerta-aprobada {
    background: #ECFDF5;
    border-color: #A7F3D0;
}
.estado-alerta-aprobada strong { color: #065F46; }
.estado-alerta-aprobada p { color: #064E3B; }

/* ── Button: outline variant ─────────────────────────────── */
.btn-outline {
    background: var(--surface);
    color: var(--primary);
    border-color: var(--primary);
}
.btn-outline:hover:not(:disabled) {
    background: rgba(218,91,62,.06);
    box-shadow: 0 2px 6px rgba(218,91,62,.18);
}

/* ── Gastos Section Header ───────────────────────────────── */
.gastos-section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 1px solid var(--border-light);
}
.gastos-section-title {
    font-size: 14px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 8px;
}
.gastos-section-count {
    font-size: 12px; color: var(--text-muted); font-weight: 500;
}


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
    /* Enterprise components */
    .estados-row { grid-template-columns: 1fr; }
    .resumen-economico { grid-template-columns: 1fr; }
    .datos-grid { grid-template-columns: 1fr; }
    .liq-doc-info { grid-template-columns: repeat(2, 1fr); }
    .liq-doc-totals { flex-direction: column; }
    .liq-total-cell { border-right: none; border-bottom: 1px solid #E2E8F0; }
    .liq-total-cell:last-child { border-bottom: none; }
}
</style>
<script>
/**
 * ViaticosGastoUI — Shared expandable gasto accordion module.
 * Exposes:
 *   renderGastoItem(gasto, idPrefix)  → HTML string for one accordion item
 *   bindAccordionList(containerEl)    → attach single-open click handlers
 */
window.ViaticosGastoUI = (function () {
    'use strict';

    const TIPO_LABEL = {
        movilidad:  'Movilidad',
        vale_caja:  'Vale de Caja',
        factura:    'Factura',
        boleta:     'Boleta',
        rxh:        'RxH',
    };

    function esc(v) {
        return String(v || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
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

    function field(label, value) {
        if (!value && value !== 0) return '';
        return `<div class="gasto-acc-field">
            <span class="gaf-label">${esc(label)}</span>
            <span class="gaf-value">${esc(String(value))}</span>
        </div>`;
    }

    function buildFields(gasto) {
        const tipo = String(gasto.tipo || '');
        const parts = [];
        parts.push(field('Fecha emisión', fmtFecha(gasto.fecha)));
        parts.push(field('Importe', fmtMonto(gasto.importe)));
        parts.push(field('Cuenta contable', gasto.cuenta));
        if (tipo === 'movilidad') {
            parts.push(field('Motivo', gasto.motivo_movilidad));
            parts.push(field('Destino', gasto.destino_movilidad));
            parts.push(field('CECO / OI', gasto.ceco_oi));
        } else {
            parts.push(field('RUC proveedor', gasto.ruc));
            parts.push(field('Razón social', gasto.razon));
            parts.push(field('N° comprobante', gasto.nro));
            parts.push(field('Concepto', gasto.concepto));
        }
        return parts.filter(Boolean).join('');
    }

    function summaryText(gasto) {
        const tipo = String(gasto.tipo || '');
        if (tipo === 'movilidad') {
            return [gasto.destino_movilidad, gasto.motivo_movilidad].filter(Boolean).join(' · ') || 'Movilidad registrada';
        }
        return [gasto.razon, gasto.nro].filter(Boolean).join(' · ') || gasto.concepto || 'Sin detalle';
    }

    /**
     * @param {Object} gasto   - gasto object from API
     * @param {string} itemId  - unique HTML id for this item
     * @returns {string} HTML
     */
    function renderGastoItem(gasto, itemId) {
        const tipoLabel = TIPO_LABEL[gasto.tipo] || esc(gasto.tipo) || 'Gasto';
        const summary   = esc(summaryText(gasto));
        const fecha     = fmtFecha(gasto.fecha);
        const importe   = fmtMonto(gasto.importe);
        const fields    = buildFields(gasto);
        const chevron   = `<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>`;
        const gastoId   = gasto.id ? esc(String(gasto.id)) : '';

        return `
        <div class="gasto-acc-item" data-acc-id="${esc(String(itemId))}" data-gasto-id="${gastoId}">
            <div class="gasto-acc-header" role="button" tabindex="0"
                 aria-expanded="false" data-acc-toggle="${esc(String(itemId))}">
                <span class="gasto-acc-chevron">${chevron}</span>
                <span class="gasto-acc-tipo">${esc(tipoLabel)}</span>
                <div class="gasto-acc-summary">
                    <div class="gas-label">${summary}</div>
                    <div class="gas-sub">${esc(fecha)}</div>
                </div>
                <span class="gasto-acc-importe">${esc(importe)}</span>
            </div>
            <div class="gasto-acc-body">
                <div class="gasto-acc-fields">${fields}</div>
                ${gastoId ? `<div class="gasto-adj-panel" data-adj-gasto-id="${gastoId}">
                    <div class="gasto-adj-title">
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="color:#4A5568;"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 015 0v10.5c0 .28-.22.5-.5.5s-.5-.22-.5-.5V6H11v9.5a2.5 2.5 0 005 0V5c0-2.21-1.79-4-4-4S8 2.79 8 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-2.5z"/></svg>
                            Adjuntos
                        </span>
                    </div>
                    <div class="gasto-adj-list"><span class="gasto-adj-loading">Cargando adjuntos…</span></div>
                </div>` : ''}
            </div>
        </div>`;
    }

    /**
     * Bind single-open accordion logic to all .gasto-acc-header elements
     * inside containerEl. Safe to call multiple times (uses event delegation).
     * @param {Element} containerEl
     * @param {Object}  [opts]
     * @param {Function} [opts.onOpen]  Called with (itemEl, gastoId) when an item opens.
     */
    function bindAccordionList(containerEl, opts) {
        if (!containerEl) return;
        const onOpen = (opts && typeof opts.onOpen === 'function') ? opts.onOpen : null;
        containerEl.addEventListener('click', function (e) {
            const header = e.target.closest('[data-acc-toggle]');
            if (!header) return;
            const id = header.dataset.accToggle;
            const items = containerEl.querySelectorAll('.gasto-acc-item');
            items.forEach(function (item) {
                const isTarget = item.dataset.accId === id;
                const wasOpen  = item.classList.contains('is-open');
                if (isTarget) {
                    const nowOpen = !wasOpen;
                    item.classList.toggle('is-open', nowOpen);
                    header.setAttribute('aria-expanded', String(nowOpen));
                    if (nowOpen && onOpen) {
                        onOpen(item, item.dataset.gastoId || null);
                    }
                } else {
                    item.classList.remove('is-open');
                    const h = item.querySelector('[data-acc-toggle]');
                    if (h) h.setAttribute('aria-expanded', 'false');
                }
            });
        });
        // Keyboard: Enter/Space triggers click on focused header
        containerEl.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const header = e.target.closest('[data-acc-toggle]');
            if (header) { e.preventDefault(); header.click(); }
        });
    }

    return { renderGastoItem, bindAccordionList };
})();
</script>
<script>
window.ViaticosEstadoUI = (function () {
    const labels = {
        solicitud: {
            pendiente: 'Pendiente',
            aprobada: 'Aprobada',
            observada: 'Observada',
            rechazada: 'Rechazada',
        },
        rendicion: {
            no_disponible: 'No disponible',
            no_iniciada: 'No iniciada',
            en_proceso: 'En proceso',
            en_revision: 'En revisión',
            aprobada: 'Aprobada',
            observada: 'Observada',
            rechazada: 'Rechazada',
        },
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function isTruthy(value) {
        return value === true || value === 1 || value === '1';
    }

    function resolveEstadoSolicitud(estado) {
        const raw = String(estado || '').toLowerCase();
        if (raw === 'rendida') return 'aprobada';
        return labels.solicitud[raw] ? raw : 'pendiente';
    }

    function resolveEstadoRendicion(options = {}) {
        const estadoSolicitud = resolveEstadoSolicitud(options.estadoSolicitud || options.estado);
        const estadoRendicion = String(options.estadoRendicion || '').toLowerCase();
        const rendicionFinalizada = isTruthy(options.rendicionFinalizada);
        const tieneGastos = Array.isArray(options.gastos)
            ? options.gastos.length > 0
            : !!options.tieneGastos || Number(options.cantidadGastos || 0) > 0 || Number(options.totalRendido || 0) > 0;

        if (estadoSolicitud !== 'aprobada') {
            return 'no_disponible';
        }

        if (!rendicionFinalizada) {
            return tieneGastos ? 'en_proceso' : 'no_iniciada';
        }

        if (estadoRendicion === 'aprobada' || estadoRendicion === 'observada' || estadoRendicion === 'rechazada') {
            return estadoRendicion;
        }

        return 'en_revision';
    }

    function renderBadgeEstado(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const allowed = labels[normalizedTipo];
        const fallback = normalizedTipo === 'rendicion' ? 'no_disponible' : 'pendiente';
        const key = allowed[String(estado || '').toLowerCase()] ? String(estado || '').toLowerCase() : fallback;
        return `<span class="badge badge-${normalizedTipo}-${key}">${escapeHtml(allowed[key])}</span>`;
    }

    function getLabelEstado(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const allowed = labels[normalizedTipo];
        const fallback = normalizedTipo === 'rendicion' ? 'no_disponible' : 'pendiente';
        const key = allowed[String(estado || '').toLowerCase()] ? String(estado || '').toLowerCase() : fallback;
        return allowed[key];
    }

    function renderEstadoGrupo(tipo, estado) {
        const normalizedTipo = tipo === 'rendicion' ? 'rendicion' : 'solicitud';
        const title = normalizedTipo === 'rendicion' ? 'Rendición' : 'Solicitud';
        return `
            <div class="estado-group estado-group-${normalizedTipo}">
                <div class="estado-group-label">${title}</div>
                <div>${renderBadgeEstado(normalizedTipo, estado)}</div>
            </div>
        `;
    }

    return {
        resolveEstadoSolicitud,
        resolveEstadoRendicion,
        getLabelEstado,
        renderBadgeEstado,
        renderEstadoGrupo,
    };
})();
window.resolveEstadoSolicitud = window.ViaticosEstadoUI.resolveEstadoSolicitud;
window.resolveEstadoRendicion = window.ViaticosEstadoUI.resolveEstadoRendicion;
window.getLabelEstado = window.ViaticosEstadoUI.getLabelEstado;
window.renderBadgeEstado = window.ViaticosEstadoUI.renderBadgeEstado;
window.renderEstadoGrupo = window.ViaticosEstadoUI.renderEstadoGrupo;
</script>
<script>
window.ViaticosTimelineUI = (function () {
    'use strict';

    const labels = {
        solicitud_creada: 'Solicitud creada',
        solicitud_aprobada: 'Solicitud aprobada',
        solicitud_observada: 'Solicitud observada',
        solicitud_rechazada: 'Solicitud rechazada',
        rendicion_iniciada: 'Rendicion iniciada',
        rendicion_finalizada: 'Rendicion finalizada',
        rendicion_aprobada: 'Rendicion aprobada',
        rendicion_observada: 'Rendicion observada',
        rendicion_rechazada: 'Rendicion rechazada',
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getLabel(evento) {
        const key = String(evento || '').toLowerCase();
        return labels[key] || 'Evento registrado';
    }

    function formatDateTime(timestamp) {
        const value = Number(timestamp || 0);
        if (!value) return 'Sin fecha';

        const date = new Date(value * 1000);
        if (Number.isNaN(date.getTime())) return 'Sin fecha';

        return new Intl.DateTimeFormat('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function renderTimeline(historial) {
        const items = Array.isArray(historial) ? [...historial] : [];

        items.sort((a, b) => Number(a && a.fecha || 0) - Number(b && b.fecha || 0));

        if (!items.length) {
            return '<div class="timeline-empty">No hay eventos registrados todavia.</div>';
        }

        return `<div class="timeline-list">${items.map(item => {
            const usuario = item && item.usuario_nombre
                ? `Por ${escapeHtml(item.usuario_nombre)}`
                : item && item.usuario_id
                    ? `Usuario #${escapeHtml(item.usuario_id)}`
                    : '';
            const meta = [formatDateTime(item && item.fecha), usuario].filter(Boolean).join(' · ');

            return `
                <div class="timeline-item">
                    <div class="timeline-marker"><span class="timeline-dot"></span></div>
                    <div class="timeline-content">
                        <div class="timeline-title">${escapeHtml(getLabel(item && item.evento))}</div>
                        <div class="timeline-meta">${meta}</div>
                    </div>
                </div>
            `;
        }).join('')}</div>`;
    }

    return {
        getLabel,
        formatDateTime,
        renderTimeline,
    };
})();
</script>
<script>
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
    const CLASE_DOC = {
        movilidad: 'Vale Movilidad', vale_caja: 'Vale de Caja',
        factura: 'Factura', boleta: 'Boleta', rxh: 'Recibo x Hon.',
    };

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

    /**
     * buildData — normalise all values into a plain object.
     * @param {Object} sol    Solicitud record from the cache
     * @param {Array}  gastos Array of gasto records
     * @param {Object} opts   Optional overrides { colaboradorNombre, area, fechaRendicion }
     */
    function buildData(sol, gastos, opts) {
        opts = opts || {};
        const gastosArr = Array.isArray(gastos) ? gastos : [];
        const totalRendido = gastosArr.reduce((s, g) => s + (parseFloat(g.importe) || 0), 0);
        const montoSolicitado = parseFloat(sol.monto) || 0;
        const saldo = montoSolicitado - totalRendido;
        return {
            id:                sol.id,
            colaborador:       opts.colaboradorNombre || sol.colaborador || '—',
            dni:               sol.dni || '—',
            area:              opts.area || sol.area || '—',
            cargo:             opts.cargo || sol.cargo || '—',
            motivo:            sol.motivo || '—',
            fechaViaje:        sol.fecha || sol.fecha_viaje || '',
            fechaRendicion:    opts.fechaRendicion || sol.fecha_creacion || '—',
            montoSolicitado,
            totalRendido,
            saldo,
            moneda:            'SOLES',
            ceco:              sol.ceco || '—',
            estadoRendicion:   sol.estado_rendicion || 'finalizada',
            gastos:            gastosArr,
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

        // Rows
        const rows = data.gastos.map((g, i) => {
            const tipo = String(g.tipo || '');
            const concepto = tipo === 'movilidad'
                ? [g.destino_movilidad, g.motivo_movilidad].filter(Boolean).join(' — ') || g.concepto || '—'
                : g.concepto || g.razon || '—';
            const ruc = tipo === 'movilidad' ? '—' : (g.ruc || '—');
            return `
            <tr>
                <td class="muted">${i + 1}</td>
                <td>${esc(TIPO_LABEL[tipo] || tipo || '—')}</td>
                <td>${esc(CLASE_DOC[tipo] || '—')}</td>
                <td>${esc(fmtFecha(g.fecha))}</td>
                <td>SOLES</td>
                <td>${esc(g.nro || '—')}</td>
                <td>${esc(concepto)}</td>
                <td class="muted">${esc(ruc)}</td>
                <td class="muted">${esc(g.cuenta || '—')}</td>
                <td class="muted">${esc(g.ceco_oi || '—')}</td>
                <td class="num"><strong>${esc(fmtMonto(g.importe))}</strong></td>
            </tr>`;
        }).join('');

        const emptyRow = `<tr><td colspan="11" style="text-align:center;padding:28px;color:#A0AEC0;font-style:italic;">Sin gastos registrados.</td></tr>`;

        const saldoClass = data.saldo >= 0 ? 'amber' : 'red';

        return `
<div class="liq-doc" id="liq-documento">
    <div class="liq-doc-header">
        <div>
            <div class="liq-doc-header-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:-3px;margin-right:6px;"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                Liquidación de Rendición de Viáticos
            </div>
            <div class="liq-doc-header-sub">Solicitud N.° ${esc(data.id)} &nbsp;&bull;&nbsp; Moneda: ${esc(data.moneda)}</div>
        </div>
        <div class="liq-doc-header-meta">
            <strong>Viáticos ERP</strong>
            Generado: ${esc(today)}
        </div>
    </div>

    <div class="liq-doc-info">
        <div class="liq-info-cell">
            <div class="liq-info-label">Colaborador</div>
            <div class="liq-info-value">${esc(data.colaborador)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">DNI / Código</div>
            <div class="liq-info-value">${esc(data.dni)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Área</div>
            <div class="liq-info-value muted">${esc(data.area)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">CECO / Proyecto</div>
            <div class="liq-info-value muted">${esc(data.ceco)}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Fecha de Viaje</div>
            <div class="liq-info-value">${esc(fmtFecha(data.fechaViaje))}</div>
        </div>
        <div class="liq-info-cell">
            <div class="liq-info-label">Fecha de Rendición</div>
            <div class="liq-info-value muted">${esc(data.fechaRendicion)}</div>
        </div>
        <div class="liq-info-cell" style="grid-column:1/-1;border-top:1px solid #E2E8F0;">
            <div class="liq-info-label">Motivo del Viaje</div>
            <div class="liq-info-value muted">${esc(data.motivo)}</div>
        </div>
    </div>

    <div class="liq-doc-table-wrap">
        <table class="liq-doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría</th>
                    <th>Clase Doc.</th>
                    <th>Fecha</th>
                    <th>Moneda</th>
                    <th>N° Documento</th>
                    <th>Concepto</th>
                    <th>RUC</th>
                    <th>Cuenta Cont.</th>
                    <th>CECO</th>
                    <th class="num">Importe</th>
                </tr>
            </thead>
            <tbody>${rows || emptyRow}</tbody>
        </table>
    </div>

    <div class="liq-doc-totals">
        <div class="liq-total-cell">
            <div class="liq-total-label">Monto Solicitado</div>
            <div class="liq-total-value blue">${esc(fmtMonto(data.montoSolicitado))}</div>
        </div>
        <div class="liq-total-cell">
            <div class="liq-total-label">Total Rendido</div>
            <div class="liq-total-value green">${esc(fmtMonto(data.totalRendido))}</div>
        </div>
        <div class="liq-total-cell">
            <div class="liq-total-label">Saldo</div>
            <div class="liq-total-value ${saldoClass}">${esc(fmtMonto(data.saldo))}</div>
        </div>
    </div>

    <div class="liq-doc-footer">
        <span>Solicitud #${esc(data.id)} &mdash; Estado rendición: <strong>${esc(data.estadoRendicion)}</strong></span>
        <span>Viáticos ERP &mdash; Documento de solo lectura</span>
    </div>
</div>`;
    }

    return { buildData, renderDoc };
})();
</script>
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

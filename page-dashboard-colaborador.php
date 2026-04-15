<?php
/**
 * Template Name: Dashboard Colaborador
 *
 * Panel ERP para el rol "colaborador". Permite al usuario:
 *  - Ver sus solicitudes de viáticos (Mis Solicitudes).
 *  - Crear una nueva solicitud mediante un modal.
 *  - Editar una solicitud observada.
 *  - Rendir gastos contra una solicitud aprobada.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

// ── Seguridad: bloquear acceso directo ────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Autenticación: redirigir si no hay sesión ─────────────────────────────────
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

// ── Datos del usuario actual ───────────────────────────────────────────────────
$current_user    = wp_get_current_user();
$user_name       = esc_html( $current_user->display_name );
$user_initials   = strtoupper( mb_substr( $current_user->first_name ?: $current_user->display_name, 0, 1 ) . mb_substr( $current_user->last_name ?: '', 0, 1 ) );
$logout_url      = esc_url( wp_logout_url( home_url() ) );
$rest_nonce      = wp_create_nonce( 'wp_rest' );
$api_base        = esc_url( get_rest_url( null, 'viaticos/v1' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard Colaborador — Sistema de Gestión de Viáticos</title>
    <meta name="description" content="Panel de gestión de viáticos para colaboradores de Fundación Romero.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ===================================================================
           DESIGN TOKENS — Sistema de Viáticos · Fundación Romero
           =================================================================== */
        :root {
            --color-primary:        #da5b3e;
            --color-primary-dark:   #bf4c32;
            --color-primary-light:  #f0ddd9;
            --color-bg:             #F5F7F9;
            --color-surface:        #FFFFFF;
            --color-border:         #E2E8F0;
            --color-border-light:   #EDF2F7;
            --color-text:           #333333;
            --color-text-muted:     #718096;
            --color-text-light:     #A0AEC0;
            --color-sidebar-bg:     #1E2433;
            --color-sidebar-hover:  #2D3448;
            --color-sidebar-active: #da5b3e;

            /* Badges de estado */
            --badge-pendiente-bg:   #FEF3C7;
            --badge-pendiente-text: #92400E;
            --badge-aprobada-bg:    #D1FAE5;
            --badge-aprobada-text:  #065F46;
            --badge-observada-bg:   #FFEDD5;
            --badge-observada-text: #9A3412;
            --badge-rechazada-bg:   #FEE2E2;
            --badge-rechazada-text: #991B1B;

            --shadow-sm:   0 1px 3px rgba(0,0,0,.08);
            --shadow-md:   0 4px 12px rgba(0,0,0,.10);
            --shadow-lg:   0 10px 30px rgba(0,0,0,.15);
            --radius-sm:   6px;
            --radius-md:   10px;
            --radius-lg:   16px;
            --sidebar-w:   240px;
            --topbar-h:    60px;
            --transition:  0.2s ease;
        }

        /* ===================================================================
           RESET & BASE
           =================================================================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: var(--color-text);
            background: var(--color-bg);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ===================================================================
           LAYOUT PRINCIPAL — Shell ERP
           =================================================================== */
        #erp-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ===================================================================
           SIDEBAR
           =================================================================== */
        #erp-sidebar {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            background: var(--color-sidebar-bg);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto;
            transition: width var(--transition);
            z-index: 100;
        }

        .sidebar-logo {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .sidebar-logo-mark {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--color-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-icon svg { fill: #fff; }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-text strong {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .logo-text span {
            font-size: 10.5px;
            color: rgba(255,255,255,.45);
            font-weight: 400;
            letter-spacing: .02em;
        }

        .sidebar-section {
            padding: 16px 0 8px;
        }

        .sidebar-section-label {
            padding: 0 16px 8px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.30);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 8px;
        }

        .sidebar-nav li { margin-bottom: 2px; }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,.62);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background var(--transition), color var(--transition);
            cursor: pointer;
        }

        .sidebar-nav a .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: .7;
            transition: opacity var(--transition);
        }

        .sidebar-nav a:hover {
            background: var(--color-sidebar-hover);
            color: #fff;
        }

        .sidebar-nav a:hover .nav-icon { opacity: 1; }

        .sidebar-nav a.active {
            background: rgba(218,91,62,.18);
            color: #f4a58f;
        }

        .sidebar-nav a.active .nav-icon { opacity: 1; }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--color-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info { flex: 1; overflow: hidden; }

        .user-info .u-name {
            display: block;
            font-size: 12.5px;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-info .u-role {
            display: block;
            font-size: 10.5px;
            color: rgba(255,255,255,.40);
        }

        /* ===================================================================
           ÁREA PRINCIPAL
           =================================================================== */
        #erp-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── TOPBAR ──────────────────────────────────────────────────────── */
        #erp-topbar {
            height: var(--topbar-h);
            min-height: var(--topbar-h);
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            z-index: 50;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--color-text-muted);
        }

        .topbar-breadcrumb span { color: var(--color-text); font-weight: 600; }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-user-info {
            text-align: right;
        }

        .topbar-user-info .t-name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
        }

        .topbar-user-info .t-role {
            display: block;
            font-size: 11px;
            color: var(--color-text-muted);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            color: var(--color-text-muted);
            font-size: 12.5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background var(--transition), border-color var(--transition), color var(--transition);
        }

        .btn-logout:hover {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #DC2626;
        }

        /* ── CONTENT AREA ────────────────────────────────────────────────── */
        #erp-content {
            flex: 1;
            overflow-y: auto;
            padding: 28px;
        }

        /* ===================================================================
           VISTAS (MODULES)
           =================================================================== */
        .erp-view { display: none; animation: fadeIn .18s ease; }
        .erp-view.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Page Header ─────────────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .page-header-left h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-text);
            line-height: 1.3;
        }

        .page-header-left p {
            font-size: 13px;
            color: var(--color-text-muted);
            margin-top: 2px;
        }

        /* ===================================================================
           STAT CARDS (Inicio)
           =================================================================== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow var(--transition), transform var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon.orange { background: var(--color-primary-light); }
        .stat-icon.green  { background: #D1FAE5; }
        .stat-icon.yellow { background: #FEF3C7; }
        .stat-icon.red    { background: #FEE2E2; }
        .stat-icon svg    { width: 22px; height: 22px; }

        .stat-body {}
        .stat-body .stat-num {
            font-size: 26px;
            font-weight: 700;
            color: var(--color-text);
            line-height: 1;
        }

        .stat-body .stat-label {
            font-size: 12px;
            color: var(--color-text-muted);
            margin-top: 3px;
        }

        /* ===================================================================
           CARD CONTAINER
           =================================================================== */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--color-border-light);
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-header-title {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--color-text);
        }

        .card-header-subtitle {
            font-size: 12px;
            color: var(--color-text-muted);
            margin-top: 1px;
        }

        /* ===================================================================
           TABLA DE DATOS
           =================================================================== */
        .table-wrapper {
            overflow-x: auto;
        }

        table.erp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .erp-table thead th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--color-text-muted);
            background: #F8FAFC;
            white-space: nowrap;
            border-bottom: 1px solid var(--color-border);
        }

        .erp-table tbody tr {
            border-bottom: 1px solid var(--color-border-light);
            transition: background var(--transition);
        }

        .erp-table tbody tr:last-child { border-bottom: none; }
        .erp-table tbody tr:hover { background: #FAFBFC; }

        .erp-table td {
            padding: 12px 16px;
            color: var(--color-text);
            vertical-align: middle;
        }

        .erp-table td.text-muted { color: var(--color-text-muted); }

        .table-empty {
            text-align: center;
            padding: 48px 20px;
            color: var(--color-text-muted);
        }

        .table-empty svg {
            width: 40px;
            height: 40px;
            margin: 0 auto 12px;
            display: block;
            opacity: .3;
        }

        .table-empty p { font-size: 13.5px; }

        /* ── Estado / loading ─────────────────────────────────────────────── */
        .table-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 40px 20px;
            color: var(--color-text-muted);
            font-size: 13px;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===================================================================
           BADGES DE ESTADO
           =================================================================== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .badge-pendiente  { background: var(--badge-pendiente-bg);  color: var(--badge-pendiente-text); }
        .badge-aprobada   { background: var(--badge-aprobada-bg);   color: var(--badge-aprobada-text); }
        .badge-observada  { background: var(--badge-observada-bg);  color: var(--badge-observada-text); }
        .badge-rechazada  { background: var(--badge-rechazada-bg);  color: var(--badge-rechazada-text); }

        /* ===================================================================
           BOTONES
           =================================================================== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all var(--transition);
            white-space: nowrap;
        }

        .btn:disabled { opacity: .55; cursor: not-allowed; }

        .btn-primary {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--color-primary-dark);
            border-color: var(--color-primary-dark);
            box-shadow: 0 2px 8px rgba(218,91,62,.35);
        }

        .btn-secondary {
            background: var(--color-surface);
            color: var(--color-text);
            border-color: var(--color-border);
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--color-bg);
            border-color: #CBD5E0;
        }

        .btn-ghost {
            background: transparent;
            color: var(--color-text-muted);
            border-color: transparent;
            padding: 6px 10px;
        }

        .btn-ghost:hover:not(:disabled) {
            background: var(--color-bg);
            color: var(--color-text);
        }

        .btn-sm {
            padding: 5px 11px;
            font-size: 12px;
        }

        .btn-danger {
            background: #FEF2F2;
            color: #DC2626;
            border-color: #FECACA;
        }

        .btn-danger:hover:not(:disabled) {
            background: #FEE2E2;
        }

        .btn-success {
            background: #F0FDF4;
            color: #15803D;
            border-color: #BBF7D0;
        }

        .btn-success:hover:not(:disabled) {
            background: #DCFCE7;
        }

        /* ===================================================================
           MODALES
           =================================================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,.55);
            backdrop-filter: blur(3px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.open { display: flex; animation: overlayIn .18s ease; }

        @keyframes overlayIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .modal {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 580px;
            max-height: 92vh;
            overflow-y: auto;
            animation: modalIn .2s ease;
        }

        .modal-lg { max-width: 720px; }

        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-16px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px 18px;
            border-bottom: 1px solid var(--color-border);
        }

        .modal-header-info h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--color-text);
        }

        .modal-header-info p {
            font-size: 12.5px;
            color: var(--color-text-muted);
            margin-top: 2px;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: var(--color-bg);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--color-text-muted);
            transition: background var(--transition), color var(--transition);
            flex-shrink: 0;
        }

        .modal-close:hover { background: #FEE2E2; color: #DC2626; }

        .modal-body { padding: 24px; }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--color-border);
            background: #FAFBFD;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        }

        /* ===================================================================
           FORMULARIOS
           =================================================================== */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid .col-full { grid-column: 1 / -1; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }

        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--color-text);
        }

        .form-label .required { color: var(--color-primary); margin-left: 2px; }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #CBD5E0;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            color: var(--color-text);
            background: var(--color-surface);
            font-family: inherit;
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(218,91,62,.15);
        }

        .form-control:invalid:not(:placeholder-shown) {
            border-color: #FC8181;
        }

        textarea.form-control { resize: vertical; min-height: 88px; }

        .form-hint {
            font-size: 11.5px;
            color: var(--color-text-muted);
        }

        .form-error {
            font-size: 11.5px;
            color: #DC2626;
            display: none;
        }

        .form-error.visible { display: block; }

        .input-prefix-wrap {
            display: flex;
        }

        .input-prefix {
            padding: 9px 10px;
            background: #F1F5F9;
            border: 1px solid #CBD5E0;
            border-right: none;
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
            font-size: 13px;
            color: var(--color-text-muted);
            font-weight: 500;
            white-space: nowrap;
        }

        .input-prefix-wrap .form-control {
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        /* ===================================================================
           ALERTAS / TOAST
           =================================================================== */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 360px;
            width: 100%;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            font-size: 13px;
            font-weight: 500;
            animation: toastIn .25s ease;
            border-left: 4px solid transparent;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .toast-success { background: #F0FDF4; border-color: #22C55E; color: #15803D; }
        .toast-error   { background: #FEF2F2; border-color: #EF4444; color: #DC2626; }
        .toast-info    { background: #EFF6FF; border-color: #3B82F6; color: #1D4ED8; }

        .toast-icon { flex-shrink: 0; margin-top: 1px; }

        .toast-body { flex: 1; }
        .toast-body strong { display: block; font-weight: 700; }
        .toast-body p { font-weight: 400; margin-top: 2px; opacity: .85; }

        /* ===================================================================
           VISTA INICIO — panel de bienvenida
           =================================================================== */
        .welcome-banner {
            background: linear-gradient(135deg, var(--color-primary) 0%, #bf4c32 100%);
            border-radius: var(--radius-md);
            padding: 28px 32px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .welcome-banner h2 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }

        .welcome-banner p {
            font-size: 13.5px;
            color: rgba(255,255,255,.80);
            margin-top: 4px;
        }

        .welcome-banner .btn-white {
            background: rgba(255,255,255,.18);
            color: #fff;
            border-color: rgba(255,255,255,.35);
            backdrop-filter: blur(4px);
        }

        .welcome-banner .btn-white:hover {
            background: rgba(255,255,255,.28);
            box-shadow: none;
        }

        /* ===================================================================
           TABLA GASTOS dentro del modal Rendir Gasto
           =================================================================== */
        .gastos-list {
            margin-top: 20px;
        }

        .gastos-list h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .gasto-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            margin-bottom: 6px;
            font-size: 13px;
        }

        .gasto-item .gi-meta { color: var(--color-text-muted); font-size: 12px; }
        .gasto-item .gi-amount { font-weight: 700; color: var(--color-text); }

        /* ===================================================================
           RESPONSIVE
           =================================================================== */
        @media (max-width: 768px) {
            #erp-sidebar {
                position: fixed;
                left: -100%;
                transition: left .25s ease;
            }

            #erp-sidebar.open { left: 0; }

            .form-grid { grid-template-columns: 1fr; }
            .form-grid .col-full { grid-column: 1; }

            #erp-content { padding: 16px; }

            .stat-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>

<body>
<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  TOAST CONTAINER                                     ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div id="toast-container" role="alert" aria-live="polite"></div>

<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  ERP SHELL                                           ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div id="erp-shell">

    <!-- ── SIDEBAR ──────────────────────────────────────────────────────── -->
    <aside id="erp-sidebar" role="navigation" aria-label="Menú principal">

        <!-- Logo -->
        <div class="sidebar-logo">
            <a href="#" class="sidebar-logo-mark">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h16v12z"/><path d="M6 10h2v2H6zm0 4h8v2H6zm4-4h8v2h-8z"/></svg>
                </div>
                <div class="logo-text">
                    <strong>Viáticos ERP</strong>
                    <span>Fundación Romero</span>
                </div>
            </a>
        </div>

        <!-- Navegación -->
        <div class="sidebar-section">
            <p class="sidebar-section-label">Menú</p>
            <ul class="sidebar-nav">
                <li>
                    <a href="#" id="nav-inicio" class="nav-link active" data-view="view-inicio">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                        Inicio
                    </a>
                </li>
                <li>
                    <a href="#" id="nav-solicitudes" class="nav-link" data-view="view-solicitudes">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                        Mis Solicitudes
                    </a>
                </li>
                <li>
                    <a href="#" id="nav-rendiciones" class="nav-link" data-view="view-rendiciones">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        Mis Rendiciones
                    </a>
                </li>
            </ul>
        </div>

        <!-- Footer del sidebar -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar" aria-hidden="true"><?php echo esc_html( $user_initials ?: 'U' ); ?></div>
                <div class="user-info">
                    <strong class="u-name"><?php echo $user_name; ?></strong>
                    <span class="u-role">Colaborador</span>
                </div>
            </div>
        </div>
    </aside><!-- /#erp-sidebar -->

    <!-- ── ÁREA PRINCIPAL ────────────────────────────────────────────────── -->
    <div id="erp-main">

        <!-- TOP BAR -->
        <header id="erp-topbar">
            <nav class="topbar-breadcrumb" aria-label="Ruta de navegación">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity:.5"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                &rsaquo;
                <span id="topbar-section-name">Inicio</span>
            </nav>
            <div class="topbar-actions">
                <div class="topbar-user-info" aria-label="Usuario autenticado">
                    <strong class="t-name"><?php echo $user_name; ?></strong>
                    <span class="t-role">Colaborador</span>
                </div>
                <a href="<?php echo $logout_url; ?>" class="btn-logout" id="btn-logout" title="Cerrar sesión">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    Salir
                </a>
            </div>
        </header><!-- /#erp-topbar -->

        <!-- CONTENT AREA -->
        <main id="erp-content">

            <!-- ============================================================
                 VISTA: INICIO
                 ============================================================ -->
            <section id="view-inicio" class="erp-view active" aria-label="Inicio">

                <div class="welcome-banner">
                    <div>
                        <h2>¡Bienvenido, <?php echo $user_name; ?>!</h2>
                        <p>Panel de gestión de viáticos — Fundación Romero</p>
                    </div>
                    <button class="btn btn-white" onclick="ViaticosApp.navigate('view-solicitudes')" id="btn-inicio-nueva">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Nueva Solicitud
                    </button>
                </div>

                <!-- KPIs -->
                <div class="stat-grid" id="inicio-stats">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <svg viewBox="0 0 24 24" fill="#da5b3e"><path d="M14 2H6c-1.1.0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1.0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                        </div>
                        <div class="stat-body">
                            <div class="stat-num" id="kpi-total">—</div>
                            <div class="stat-label">Total Solicitudes</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon yellow">
                            <svg viewBox="0 0 24 24" fill="#D97706"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                        <div class="stat-body">
                            <div class="stat-num" id="kpi-pendiente">—</div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <svg viewBox="0 0 24 24" fill="#059669"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        </div>
                        <div class="stat-body">
                            <div class="stat-num" id="kpi-aprobada">—</div>
                            <div class="stat-label">Aprobadas</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <svg viewBox="0 0 24 24" fill="#DC2626"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                        </div>
                        <div class="stat-body">
                            <div class="stat-num" id="kpi-rechazada">—</div>
                            <div class="stat-label">Rechazadas</div>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-header-title">Actividad Reciente</div>
                            <div class="card-header-subtitle">Últimas 5 solicitudes registradas</div>
                        </div>
                        <button class="btn btn-secondary btn-sm" onclick="ViaticosApp.navigate('view-solicitudes')" id="btn-ver-todas">
                            Ver todas
                        </button>
                    </div>
                    <div class="table-wrapper">
                        <table class="erp-table" aria-label="Actividad reciente">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Viaje</th>
                                    <th>Monto</th>
                                    <th>CECO/Proyecto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="inicio-recent-tbody">
                                <tr><td colspan="5"><div class="table-loading"><div class="spinner"></div> Cargando...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section><!-- /#view-inicio -->


            <!-- ============================================================
                 VISTA: MIS SOLICITUDES
                 ============================================================ -->
            <section id="view-solicitudes" class="erp-view" aria-label="Mis Solicitudes">

                <div class="page-header">
                    <div class="page-header-left">
                        <h1>Mis Solicitudes</h1>
                        <p>Gestiona y da seguimiento a tus solicitudes de viáticos.</p>
                    </div>
                    <button class="btn btn-primary" id="btn-abrir-nueva-solicitud">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Nueva Solicitud
                    </button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-header-title">Listado de Solicitudes</div>
                        </div>
                        <button class="btn btn-ghost btn-sm" id="btn-refrescar-solicitudes" title="Actualizar tabla">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42.0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73.0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31.0-6-2.69-6-6s2.69-6 6-6c1.66.0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                            Actualizar
                        </button>
                    </div>
                    <div class="table-wrapper">
                        <table class="erp-table" aria-label="Mis solicitudes de viáticos">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Viaje</th>
                                    <th>Monto Solicitado</th>
                                    <th>CECO / Proyecto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="solicitudes-tbody">
                                <tr><td colspan="6"><div class="table-loading"><div class="spinner"></div> Cargando solicitudes...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section><!-- /#view-solicitudes -->


            <!-- ============================================================
                 VISTA: MIS RENDICIONES
                 ============================================================ -->
            <section id="view-rendiciones" class="erp-view" aria-label="Mis Rendiciones">

                <div class="page-header">
                    <div class="page-header-left">
                        <h1>Mis Rendiciones</h1>
                        <p>Revisa los gastos rendidos contra tus solicitudes aprobadas.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-title">Gastos Rendidos</div>
                    </div>
                    <div class="table-wrapper">
                        <table class="erp-table" aria-label="Mis gastos rendidos">
                            <thead>
                                <tr>
                                    <th>ID Gasto</th>
                                    <th>Solicitud Ref.</th>
                                    <th>Tipo</th>
                                    <th>Fecha Emisión</th>
                                    <th>Importe</th>
                                    <th>Proveedor / RUC</th>
                                </tr>
                            </thead>
                            <tbody id="rendiciones-tbody">
                                <tr><td colspan="6"><div class="table-loading"><div class="spinner"></div> Cargando rendiciones...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section><!-- /#view-rendiciones -->

        </main><!-- /#erp-content -->
    </div><!-- /#erp-main -->
</div><!-- /#erp-shell -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL: NUEVA SOLICITUD                              ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="modal-overlay" id="modal-nueva-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-nueva-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-nueva-titulo">Nueva Solicitud de Viático</h2>
                <p>Complete todos los campos para enviar su solicitud.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-nueva" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-nueva-solicitud" novalidate>
                <div class="form-grid">

                    <!-- DNI -->
                    <div class="form-group">
                        <label class="form-label" for="ns-dni">DNI del Colaborador <span class="required">*</span></label>
                        <input
                            type="text"
                            id="ns-dni"
                            name="dni"
                            class="form-control"
                            placeholder="Ej: 12345678"
                            maxlength="8"
                            pattern="\d{8}"
                            required
                            inputmode="numeric"
                            autocomplete="off"
                        >
                        <span class="form-error" id="err-ns-dni">El DNI debe tener exactamente 8 dígitos numéricos.</span>
                    </div>

                    <!-- Monto -->
                    <div class="form-group">
                        <label class="form-label" for="ns-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input
                                type="number"
                                id="ns-monto"
                                name="monto"
                                class="form-control"
                                placeholder="0.00"
                                min="1"
                                step="0.01"
                                required
                            >
                        </div>
                        <span class="form-error" id="err-ns-monto">Ingrese un monto mayor a S/. 0.00.</span>
                    </div>

                    <!-- Fecha del Viaje -->
                    <div class="form-group">
                        <label class="form-label" for="ns-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input
                            type="date"
                            id="ns-fecha"
                            name="fecha"
                            class="form-control"
                            required
                        >
                        <span class="form-error" id="err-ns-fecha">Seleccione una fecha válida.</span>
                    </div>

                    <!-- CECO / Proyecto -->
                    <div class="form-group">
                        <label class="form-label" for="ns-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input
                            type="text"
                            id="ns-ceco"
                            name="ceco"
                            class="form-control"
                            placeholder="Ej: CC-001 / ADMINISTRACIÓN"
                            required
                            autocomplete="off"
                        >
                        <span class="form-error" id="err-ns-ceco">Este campo es obligatorio.</span>
                    </div>

                    <!-- Motivo -->
                    <div class="form-group col-full">
                        <label class="form-label" for="ns-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea
                            id="ns-motivo"
                            name="motivo"
                            class="form-control"
                            placeholder="Describa el objetivo o motivo del viaje..."
                            required
                            rows="4"
                        ></textarea>
                        <span class="form-error" id="err-ns-motivo">Describa el motivo del viaje.</span>
                    </div>

                </div><!-- /.form-grid -->

                <!-- Error global del formulario -->
                <div id="nueva-solicitud-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
</div><!-- /#modal-nueva-solicitud -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL: EDITAR SOLICITUD (Observada)                 ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="modal-overlay" id="modal-editar-solicitud" role="dialog" aria-modal="true" aria-labelledby="modal-editar-titulo">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-editar-titulo">Editar Solicitud <span id="editar-sol-id" style="color:var(--color-text-muted); font-weight:400;"></span></h2>
                <p>Esta solicitud fue observada. Corrija los datos y reenvíe.</p>
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
                        <span class="form-error" id="err-ed-dni">El DNI debe tener exactamente 8 dígitos numéricos.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ed-monto">Monto Solicitado <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="ed-monto" name="monto" class="form-control" min="1" step="0.01" required>
                        </div>
                        <span class="form-error" id="err-ed-monto">Ingrese un monto mayor a S/. 0.00.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ed-fecha">Fecha del Viaje <span class="required">*</span></label>
                        <input type="date" id="ed-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-ed-fecha">Seleccione una fecha válida.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ed-ceco">CECO / Proyecto <span class="required">*</span></label>
                        <input type="text" id="ed-ceco" name="ceco" class="form-control" required>
                        <span class="form-error" id="err-ed-ceco">Este campo es obligatorio.</span>
                    </div>

                    <div class="form-group col-full">
                        <label class="form-label" for="ed-motivo">Motivo del Viaje <span class="required">*</span></label>
                        <textarea id="ed-motivo" name="motivo" class="form-control" required rows="4"></textarea>
                        <span class="form-error" id="err-ed-motivo">Describa el motivo del viaje.</span>
                    </div>
                </div>
                <div id="editar-solicitud-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
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
</div><!-- /#modal-editar-solicitud -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL: RENDIR GASTO                                 ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="modal-overlay" id="modal-rendir-gasto" role="dialog" aria-modal="true" aria-labelledby="modal-rendir-titulo">
    <div class="modal modal-lg">
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-rendir-titulo">Rendir Gasto</h2>
                <p>Registre un comprobante de gasto para la solicitud <strong id="rendir-sol-ref"></strong>.</p>
            </div>
            <button class="modal-close" id="btn-cerrar-modal-rendir" aria-label="Cerrar modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-rendir-gasto" novalidate>
                <input type="hidden" id="rg-id-solicitud" name="id_solicitud">
                <div class="form-grid">

                    <!-- Tipo de Plantilla -->
                    <div class="form-group">
                        <label class="form-label" for="rg-tipo">Tipo de Documento</label>
                        <select id="rg-tipo" name="tipo" class="form-control">
                            <option value="">— Seleccione —</option>
                            <option value="vale_caja">Vale de Caja</option>
                            <option value="vale_movilidad">Vale de Movilidad</option>
                            <option value="modelo_liquidacion">Modelo Liquidación</option>
                        </select>
                    </div>

                    <!-- Fecha de Emisión -->
                    <div class="form-group">
                        <label class="form-label" for="rg-fecha">Fecha de Emisión <span class="required">*</span></label>
                        <input type="date" id="rg-fecha" name="fecha" class="form-control" required>
                        <span class="form-error" id="err-rg-fecha">Seleccione la fecha de emisión.</span>
                    </div>

                    <!-- Importe -->
                    <div class="form-group">
                        <label class="form-label" for="rg-importe">Importe <span class="required">*</span></label>
                        <div class="input-prefix-wrap">
                            <span class="input-prefix">S/.</span>
                            <input type="number" id="rg-importe" name="importe" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                        <span class="form-error" id="err-rg-importe">Ingrese un importe mayor a S/. 0.00.</span>
                    </div>

                    <!-- RUC -->
                    <div class="form-group">
                        <label class="form-label" for="rg-ruc">RUC del Proveedor</label>
                        <input type="text" id="rg-ruc" name="ruc" class="form-control" maxlength="11" placeholder="Ej: 20123456789" inputmode="numeric">
                    </div>

                    <!-- Razón Social -->
                    <div class="form-group">
                        <label class="form-label" for="rg-razon">Razón Social</label>
                        <input type="text" id="rg-razon" name="razon_social" class="form-control" placeholder="Ej: EMPRESA S.A.C.">
                    </div>

                    <!-- Nro Comprobante -->
                    <div class="form-group">
                        <label class="form-label" for="rg-nro-comprobante">N° Comprobante</label>
                        <input type="text" id="rg-nro-comprobante" name="nro_comprobante" class="form-control" placeholder="Ej: F001-00123456">
                    </div>

                    <!-- Cuenta Contable -->
                    <div class="form-group col-full">
                        <label class="form-label" for="rg-cuenta">Cuenta Contable</label>
                        <input type="text" id="rg-cuenta" name="cuenta_contable" class="form-control" placeholder="Ej: 63.1.1">
                    </div>
                </div>

                <div id="rendir-gasto-error" style="display:none; margin-top:16px; padding:12px 14px; background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--radius-sm); color:#DC2626; font-size:13px;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btn-cancelar-modal-rendir">Cancelar</button>
            <button type="submit" form="form-rendir-gasto" class="btn btn-primary" id="btn-submit-rendir-gasto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                Registrar Gasto
            </button>
        </div>
    </div>
</div><!-- /#modal-rendir-gasto -->


<!-- ╔══════════════════════════════════════════════════════════════════╗ -->
<!-- ║  JAVASCRIPT — Módulo principal ViaticosApp (Vanilla JS)         ║ -->
<!-- ╚══════════════════════════════════════════════════════════════════╝ -->
<script>
(function () {
    'use strict';

    /* ----------------------------------------------------------------
       CONFIG — Inyectada desde PHP
       ---------------------------------------------------------------- */
    const CONFIG = {
        nonce:   '<?php echo esc_js( $rest_nonce ); ?>',
        apiBase: '<?php echo esc_js( rtrim( $api_base, '/' ) ); ?>',
    };

    /* ================================================================
       UTILITIES
       ================================================================ */

    /**
     * Realiza un fetch con los headers de WordPress REST API.
     */
    async function apiFetch(endpoint, options = {}) {
        const defaults = {
            headers: {
                'Content-Type':  'application/json',
                'X-WP-Nonce':    CONFIG.nonce,
            },
        };

        const merged = Object.assign({}, defaults, options);
        if (options.headers) {
            merged.headers = Object.assign({}, defaults.headers, options.headers);
        }

        const response = await fetch(CONFIG.apiBase + endpoint, merged);
        const data     = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `Error ${response.status}`);
        }

        return data;
    }

    /**
     * Formato de moneda peruana.
     */
    function formatMonto(value) {
        const num = parseFloat(value);
        if (isNaN(num)) return '—';
        return 'S/. ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Formatea una fecha ISO (Y-m-d) a dd/mm/yyyy.
     */
    function formatFecha(isoStr) {
        if (!isoStr) return '—';
        const parts = isoStr.split('-');
        if (parts.length !== 3) return isoStr;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    /**
     * Genera el HTML de un badge de estado.
     */
    const estadoLabel = {
        pendiente: 'Pendiente',
        aprobada:  'Aprobada',
        observada: 'Observada',
        rechazada: 'Rechazada',
    };

    function badgeHTML(estado) {
        const key   = (estado || '').toLowerCase();
        const label = estadoLabel[key] || estado;
        return `<span class="badge badge-${key}">${label}</span>`;
    }

    /**
     * Muestra un toast de notificación.
     * @param {'success'|'error'|'info'} type
     * @param {string} title
     * @param {string} [message]
     * @param {number} [duration=4500]
     */
    function showToast(type, title, message = '', duration = 4500) {
        const icons = {
            success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
            error:   `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2z"/></svg>`,
            info:    `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41.0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>`,
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type]}</span>
            <div class="toast-body">
                <strong>${title}</strong>
                ${message ? `<p>${message}</p>` : ''}
            </div>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all .3s ease';
            setTimeout(() => toast.remove(), 320);
        }, duration);
    }

    /**
     * Muestra/oculta el spinner en un botón.
     */
    function setButtonLoading(btn, isLoading, originalText) {
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.origText = btn.innerHTML;
            btn.innerHTML = `<div class="spinner" style="width:14px;height:14px;border-width:2px;"></div> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.origText || originalText;
        }
    }

    /* ================================================================
       MODAL MANAGER
       ================================================================ */
    const ModalManager = {
        open(modalId) {
            const overlay = document.getElementById(modalId);
            if (!overlay) return;
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        },
        close(modalId) {
            const overlay = document.getElementById(modalId);
            if (!overlay) return;
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        },
        closeOnOverlayClick(modalId) {
            const overlay = document.getElementById(modalId);
            if (!overlay) return;
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) this.close(modalId);
            });
        },
    };

    // Cerrar modales al pulsar tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        ['modal-nueva-solicitud', 'modal-editar-solicitud', 'modal-rendir-gasto'].forEach(id => {
            ModalManager.close(id);
        });
    });

    /* ================================================================
       VALIDACIÓN DE FORMULARIOS
       ================================================================ */

    /**
     * Valida un campo y muestra/oculta el mensaje de error.
     * @returns {boolean} true si válido
     */
    function validateField(inputEl, errorEl, customValidator) {
        let isValid = inputEl.checkValidity();

        // Validación adicional custom
        if (isValid && customValidator) {
            isValid = customValidator(inputEl.value);
        }

        if (!isValid) {
            errorEl.classList.add('visible');
            inputEl.style.borderColor = '#FC8181';
        } else {
            errorEl.classList.remove('visible');
            inputEl.style.borderColor = '';
        }

        return isValid;
    }

    /**
     * Limpia estilos y errores de un formulario.
     */
    function resetFormErrors(formEl) {
        formEl.querySelectorAll('.form-error').forEach(el => el.classList.remove('visible'));
        formEl.querySelectorAll('.form-control').forEach(el => (el.style.borderColor = ''));
    }

    /* ================================================================
       DATOS: SOLICITUDES
       ================================================================ */
    let solicitudesCache = [];

    /**
     * Obtiene las solicitudes del usuario actual desde el endpoint propio
     * /viaticos/v1/mis-solicitudes que usa get_field() internamente.
     */
    async function fetchSolicitudes() {
        const data = await apiFetch( '/mis-solicitudes' );
        return data; // Ya viene con la estructura correcta desde el servidor.
    }

    /**
     * Obtiene gastos rendidos (gasto_rendicion) del usuario
     * desde el endpoint propio /viaticos/v1/mis-rendiciones.
     */
    async function fetchGastos() {
        const data = await apiFetch( '/mis-rendiciones' );
        return data;
    }

    /* ================================================================
       RENDER: TABLA SOLICITUDES
       ================================================================ */

    function renderTableEmpty(tbody, colSpan, message = 'No se encontraron registros.') {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colSpan}">
                    <div class="table-empty">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5v14H5V5h14m0-2H5c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1.0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/><path d="M14 17H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <p>${message}</p>
                    </div>
                </td>
            </tr>
        `;
    }

    function renderTableLoading(tbody, colSpan) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colSpan}">
                    <div class="table-loading">
                        <div class="spinner"></div>
                        Cargando datos...
                    </div>
                </td>
            </tr>
        `;
    }

    function renderSolicitudesTable(data) {
        const tbody = document.getElementById('solicitudes-tbody');
        if (!data || data.length === 0) {
            renderTableEmpty(tbody, 6, 'Aún no tienes solicitudes registradas.');
            return;
        }

        tbody.innerHTML = data.map(sol => {
            const estado  = (sol.estado || 'pendiente').toLowerCase();
            const acciones = buildAcciones(sol);

            return `
                <tr>
                    <td class="text-muted">#${sol.id}</td>
                    <td>${formatFecha(sol.fecha)}</td>
                    <td><strong>${formatMonto(sol.monto)}</strong></td>
                    <td>${escHtml(sol.ceco)}</td>
                    <td>${badgeHTML(estado)}</td>
                    <td>${acciones}</td>
                </tr>
            `;
        }).join('');

        // Adjuntar listeners a los botones de acción
        attachActionListeners(tbody, data);
    }

    function buildAcciones(sol) {
        const estado = (sol.estado || '').toLowerCase();
        let btns = '';

        if (estado === 'observada') {
            btns += `
                <button
                    class="btn btn-secondary btn-sm action-editar"
                    data-id="${sol.id}"
                    title="Editar solicitud observada"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02.0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41.0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    Editar
                </button>
            `;
        }

        if (estado === 'aprobada') {
            btns += `
                <button
                    class="btn btn-success btn-sm action-rendir"
                    data-id="${sol.id}"
                    title="Rendir gasto contra esta solicitud"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78.0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61.0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41.0.97-.79 2.03-2.5 2.03-2.08.0-2.98-.93-3.1-2.1H7.3c.13 2.15 1.73 3.56 3.7 3.97V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55.0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                    Rendir Gasto
                </button>
            `;
        }

        return btns || `<span style="color:var(--color-text-light); font-size:12px;">Sin acciones</span>`;
    }

    function attachActionListeners(tbody, data) {
        // Botones Editar
        tbody.querySelectorAll('.action-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                const id  = parseInt(btn.dataset.id, 10);
                const sol = data.find(s => s.id === id);
                if (!sol) return;
                openEditarModal(sol);
            });
        });

        // Botones Rendir Gasto
        tbody.querySelectorAll('.action-rendir').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.id, 10);
                openRendirModal(id);
            });
        });
    }

    /* ================================================================
       RENDER: TABLA INICIO (actividad reciente)
       ================================================================ */
    function renderInicioRecent(data) {
        const tbody = document.getElementById('inicio-recent-tbody');

        // KPIs
        const kpis = { total: data.length, pendiente: 0, aprobada: 0, rechazada: 0 };
        data.forEach(s => {
            const est = (s.estado || '').toLowerCase();
            if (est in kpis) kpis[est]++;
        });

        document.getElementById('kpi-total').textContent     = kpis.total;
        document.getElementById('kpi-pendiente').textContent = kpis.pendiente;
        document.getElementById('kpi-aprobada').textContent  = kpis.aprobada;
        document.getElementById('kpi-rechazada').textContent = kpis.rechazada;

        // Tabla — últimas 5
        const recent = data.slice(0, 5);
        if (!recent.length) {
            renderTableEmpty(tbody, 5, 'Aún no tienes actividad registrada.');
            return;
        }

        tbody.innerHTML = recent.map(sol => `
            <tr>
                <td class="text-muted">#${sol.id}</td>
                <td>${formatFecha(sol.fecha)}</td>
                <td><strong>${formatMonto(sol.monto)}</strong></td>
                <td>${escHtml(sol.ceco)}</td>
                <td>${badgeHTML(sol.estado)}</td>
            </tr>
        `).join('');
    }

    /* ================================================================
       RENDER: TABLA RENDICIONES
       ================================================================ */
    function renderRendicionesTable(data) {
        const tbody = document.getElementById('rendiciones-tbody');

        if (!data || data.length === 0) {
            renderTableEmpty(tbody, 6, 'Aún no tienes gastos rendidos registrados.');
            return;
        }

        const tipoLabel = {
            vale_caja:          'Vale de Caja',
            vale_movilidad:     'Vale de Movilidad',
            modelo_liquidacion: 'Modelo Liquidación',
        };

        tbody.innerHTML = data.map(g => `
            <tr>
                <td class="text-muted">#${g.id}</td>
                <td>${g.id_solicitud ? `<span class="badge badge-aprobada">#${g.id_solicitud}</span>` : '—'}</td>
                <td>${tipoLabel[g.tipo] || g.tipo || '—'}</td>
                <td>${formatFecha(g.fecha)}</td>
                <td><strong>${formatMonto(g.importe)}</strong></td>
                <td>${escHtml(g.ruc)} ${g.razon && g.razon !== '—' ? `· ${escHtml(g.razon)}` : ''}</td>
            </tr>
        `).join('');
    }

    /* ================================================================
       SEGURIDAD: escape HTML básico
       ================================================================ */
    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ================================================================
       NAVEGACIÓN SPA
       ================================================================ */
    function navigateTo(viewId) {
        // Ocultar todas las vistas
        document.querySelectorAll('.erp-view').forEach(v => v.classList.remove('active'));

        // Mostrar la vista objetivo
        const target = document.getElementById(viewId);
        if (target) target.classList.add('active');

        // Actualizar nav activo
        document.querySelectorAll('.nav-link').forEach(a => {
            a.classList.toggle('active', a.dataset.view === viewId);
        });

        // Actualizar breadcrumb
        const sectionNames = {
            'view-inicio':        'Inicio',
            'view-solicitudes':   'Mis Solicitudes',
            'view-rendiciones':   'Mis Rendiciones',
        };
        const nameEl = document.getElementById('topbar-section-name');
        if (nameEl) nameEl.textContent = sectionNames[viewId] || '';

        // Cargar datos según la vista
        if (viewId === 'view-solicitudes') loadSolicitudesView();
        if (viewId === 'view-rendiciones') loadRendicionesView();
    }

    /* ================================================================
       CARGA DE DATOS POR VISTA
       ================================================================ */

    async function loadInicioView() {
        const tbody = document.getElementById('inicio-recent-tbody');
        renderTableLoading(tbody, 5);

        try {
            solicitudesCache = await fetchSolicitudes();
            renderInicioRecent(solicitudesCache);
        } catch (err) {
            console.error('[ViaticosApp]', err);
            renderTableEmpty(tbody, 5, 'Error al cargar datos. Intente de nuevo.');
            showToast('error', 'Error', err.message);
        }
    }

    async function loadSolicitudesView() {
        const tbody = document.getElementById('solicitudes-tbody');
        renderTableLoading(tbody, 6);

        try {
            solicitudesCache = await fetchSolicitudes();
            renderSolicitudesTable(solicitudesCache);
        } catch (err) {
            console.error('[ViaticosApp]', err);
            renderTableEmpty(tbody, 6, 'Error al cargar solicitudes.');
            showToast('error', 'Error', err.message);
        }
    }

    async function loadRendicionesView() {
        const tbody = document.getElementById('rendiciones-tbody');
        renderTableLoading(tbody, 6);

        try {
            const gastos = await fetchGastos();
            renderRendicionesTable(gastos);
        } catch (err) {
            console.error('[ViaticosApp]', err);
            renderTableEmpty(tbody, 6, 'Error al cargar rendiciones.');
            showToast('error', 'Error', err.message);
        }
    }

    /* ================================================================
       MODAL: NUEVA SOLICITUD
       ================================================================ */
    function openNuevaSolicitudModal() {
        const form = document.getElementById('form-nueva-solicitud');
        form.reset();
        resetFormErrors(form);
        document.getElementById('nueva-solicitud-error').style.display = 'none';
        ModalManager.open('modal-nueva-solicitud');
        document.getElementById('ns-dni').focus();
    }

    function closeNuevaSolicitudModal() {
        ModalManager.close('modal-nueva-solicitud');
    }

    async function handleNuevaSolicitudSubmit(e) {
        e.preventDefault();

        const form  = document.getElementById('form-nueva-solicitud');
        const btn   = document.getElementById('btn-submit-nueva-solicitud');
        const errEl = document.getElementById('nueva-solicitud-error');

        // Validación de campos
        const dniEl    = document.getElementById('ns-dni');
        const montoEl  = document.getElementById('ns-monto');
        const fechaEl  = document.getElementById('ns-fecha');
        const cecoEl   = document.getElementById('ns-ceco');
        const motivoEl = document.getElementById('ns-motivo');

        const v1 = validateField(dniEl,    document.getElementById('err-ns-dni'),    v => /^\d{8}$/.test(v));
        const v2 = validateField(montoEl,  document.getElementById('err-ns-monto'),  v => parseFloat(v) > 0);
        const v3 = validateField(fechaEl,  document.getElementById('err-ns-fecha'),  v => !!v);
        const v4 = validateField(cecoEl,   document.getElementById('err-ns-ceco'),   v => v.trim().length > 0);
        const v5 = validateField(motivoEl, document.getElementById('err-ns-motivo'), v => v.trim().length > 0);

        if (!v1 || !v2 || !v3 || !v4 || !v5) return;

        errEl.style.display = 'none';
        setButtonLoading(btn, true);

        try {
            const payload = {
                dni:    dniEl.value.trim(),
                monto:  parseFloat(montoEl.value),
                fecha:  fechaEl.value,
                ceco:   cecoEl.value.trim(),
                motivo: motivoEl.value.trim(),
            };

            await apiFetch('/nueva-solicitud', {
                method: 'POST',
                body:   JSON.stringify(payload),
            });

            closeNuevaSolicitudModal();
            showToast('success', 'Solicitud enviada', 'Tu solicitud de viático fue registrada correctamente.');
            await loadSolicitudesView();
            await loadInicioView();

        } catch (err) {
            console.error('[ViaticosApp] nueva-solicitud:', err);
            errEl.textContent   = err.message || 'Ocurrió un error. Intente de nuevo.';
            errEl.style.display = 'block';
        } finally {
            setButtonLoading(btn, false);
        }
    }

    /* ================================================================
       MODAL: EDITAR SOLICITUD
       ================================================================ */
    function openEditarModal(sol) {
        const form = document.getElementById('form-editar-solicitud');
        form.reset();
        resetFormErrors(form);
        document.getElementById('editar-solicitud-error').style.display = 'none';
        document.getElementById('editar-sol-id').textContent = `#${sol.id}`;

        // Rellenar campos
        document.getElementById('ed-post-id').value = sol.id;
        document.getElementById('ed-dni').value      = sol.dni;
        document.getElementById('ed-monto').value    = sol.monto;
        document.getElementById('ed-fecha').value    = sol.fecha;
        document.getElementById('ed-ceco').value     = sol.ceco !== '—' ? sol.ceco : '';
        document.getElementById('ed-motivo').value   = sol.motivo;

        ModalManager.open('modal-editar-solicitud');
        document.getElementById('ed-dni').focus();
    }

    function closeEditarModal() {
        ModalManager.close('modal-editar-solicitud');
    }

    async function handleEditarSolicitudSubmit(e) {
        e.preventDefault();

        const btn    = document.getElementById('btn-submit-editar-solicitud');
        const errEl  = document.getElementById('editar-solicitud-error');
        const postId = document.getElementById('ed-post-id').value;

        const dniEl    = document.getElementById('ed-dni');
        const montoEl  = document.getElementById('ed-monto');
        const fechaEl  = document.getElementById('ed-fecha');
        const cecoEl   = document.getElementById('ed-ceco');
        const motivoEl = document.getElementById('ed-motivo');

        const v1 = validateField(dniEl,    document.getElementById('err-ed-dni'),    v => /^\d{8}$/.test(v));
        const v2 = validateField(montoEl,  document.getElementById('err-ed-monto'),  v => parseFloat(v) > 0);
        const v3 = validateField(fechaEl,  document.getElementById('err-ed-fecha'),  v => !!v);
        const v4 = validateField(cecoEl,   document.getElementById('err-ed-ceco'),   v => v.trim().length > 0);
        const v5 = validateField(motivoEl, document.getElementById('err-ed-motivo'), v => v.trim().length > 0);

        if (!v1 || !v2 || !v3 || !v4 || !v5) return;

        errEl.style.display = 'none';
        setButtonLoading(btn, true);

        try {
            const origin = (new URL(CONFIG.apiBase)).origin;

            // Actualizar metadatos ACF vía WP REST API
            const aclPayload = {
                acf: {
                    dni_colaborador:  dniEl.value.trim(),
                    monto_solicitado: parseFloat(montoEl.value),
                    fecha_viaje:      fechaEl.value,
                    centro_costo:     cecoEl.value.trim(),
                    motivo_viaje:     motivoEl.value.trim(),
                    estado_solicitud: 'pendiente', // Resetear a pendiente al re-enviar
                },
            };

            const response = await fetch(
                `${origin}/wp-json/wp/v2/solicitud_viatico/${postId}`,
                {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce':   CONFIG.nonce,
                    },
                    credentials: 'include',
                    body: JSON.stringify(aclPayload),
                }
            );

            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.message || `Error ${response.status}`);
            }

            closeEditarModal();
            showToast('success', 'Solicitud actualizada', 'Los cambios fueron guardados y la solicitud está en revisión.');
            await loadSolicitudesView();
            await loadInicioView();

        } catch (err) {
            console.error('[ViaticosApp] editar-solicitud:', err);
            errEl.textContent   = err.message || 'No se pudo guardar. Intente de nuevo.';
            errEl.style.display = 'block';
        } finally {
            setButtonLoading(btn, false);
        }
    }

    /* ================================================================
       MODAL: RENDIR GASTO
       ================================================================ */
    function openRendirModal(solicitudId) {
        const form = document.getElementById('form-rendir-gasto');
        form.reset();
        resetFormErrors(form);
        document.getElementById('rendir-gasto-error').style.display = 'none';
        document.getElementById('rg-id-solicitud').value = solicitudId;
        document.getElementById('rendir-sol-ref').textContent = `#${solicitudId}`;

        ModalManager.open('modal-rendir-gasto');
        document.getElementById('rg-fecha').focus();
    }

    function closeRendirModal() {
        ModalManager.close('modal-rendir-gasto');
    }

    async function handleRendirGastoSubmit(e) {
        e.preventDefault();

        const btn         = document.getElementById('btn-submit-rendir-gasto');
        const errEl       = document.getElementById('rendir-gasto-error');
        const idSolicitud = document.getElementById('rg-id-solicitud').value;

        const fechaEl    = document.getElementById('rg-fecha');
        const importeEl  = document.getElementById('rg-importe');

        const v1 = validateField(fechaEl,   document.getElementById('err-rg-fecha'),   v => !!v);
        const v2 = validateField(importeEl, document.getElementById('err-rg-importe'), v => parseFloat(v) > 0);

        if (!v1 || !v2) return;

        errEl.style.display = 'none';
        setButtonLoading(btn, true);

        try {
            const payload = {
                id_solicitud:    parseInt(idSolicitud, 10),
                tipo:            document.getElementById('rg-tipo').value || undefined,
                fecha:           fechaEl.value,
                importe:         parseFloat(importeEl.value),
                ruc:             document.getElementById('rg-ruc').value.trim() || undefined,
                razon_social:    document.getElementById('rg-razon').value.trim() || undefined,
                nro_comprobante: document.getElementById('rg-nro-comprobante').value.trim() || undefined,
                cuenta_contable: document.getElementById('rg-cuenta').value.trim() || undefined,
            };

            // Limpiar undefined
            Object.keys(payload).forEach(k => payload[k] === undefined && delete payload[k]);

            await apiFetch('/nuevo-gasto', {
                method: 'POST',
                body:   JSON.stringify(payload),
            });

            closeRendirModal();
            showToast('success', 'Gasto registrado', `El comprobante fue rendido correctamente contra la solicitud #${idSolicitud}.`);
            await loadRendicionesView();

        } catch (err) {
            console.error('[ViaticosApp] rendir-gasto:', err);
            errEl.textContent   = err.message || 'No se pudo registrar el gasto. Intente de nuevo.';
            errEl.style.display = 'block';
        } finally {
            setButtonLoading(btn, false);
        }
    }

    /* ================================================================
       BIND DE EVENTOS
       ================================================================ */
    function bindEvents() {

        // ── Navegación sidebar ────────────────────────────────────────
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                navigateTo(link.dataset.view);
            });
        });

        // ── Modal Nueva Solicitud ─────────────────────────────────────
        document.getElementById('btn-abrir-nueva-solicitud')
            .addEventListener('click', openNuevaSolicitudModal);

        document.getElementById('btn-cerrar-modal-nueva')
            .addEventListener('click', closeNuevaSolicitudModal);

        document.getElementById('btn-cancelar-modal-nueva')
            .addEventListener('click', closeNuevaSolicitudModal);

        document.getElementById('form-nueva-solicitud')
            .addEventListener('submit', handleNuevaSolicitudSubmit);

        ModalManager.closeOnOverlayClick('modal-nueva-solicitud');

        // ── Modal Editar Solicitud ────────────────────────────────────
        document.getElementById('btn-cerrar-modal-editar')
            .addEventListener('click', closeEditarModal);

        document.getElementById('btn-cancelar-modal-editar')
            .addEventListener('click', closeEditarModal);

        document.getElementById('form-editar-solicitud')
            .addEventListener('submit', handleEditarSolicitudSubmit);

        ModalManager.closeOnOverlayClick('modal-editar-solicitud');

        // ── Modal Rendir Gasto ────────────────────────────────────────
        document.getElementById('btn-cerrar-modal-rendir')
            .addEventListener('click', closeRendirModal);

        document.getElementById('btn-cancelar-modal-rendir')
            .addEventListener('click', closeRendirModal);

        document.getElementById('form-rendir-gasto')
            .addEventListener('submit', handleRendirGastoSubmit);

        ModalManager.closeOnOverlayClick('modal-rendir-gasto');

        // ── Botón Actualizar tabla solicitudes ────────────────────────
        document.getElementById('btn-refrescar-solicitudes')
            .addEventListener('click', loadSolicitudesView);
    }

    /* ================================================================
       INIT
       ================================================================ */
    function init() {
        bindEvents();
        loadInicioView();
    }

    // API pública (para uso desde atributos onclick inline)
    window.ViaticosApp = {
        navigate: navigateTo,
    };

    // Arrancar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
</script>

</body>
</html>

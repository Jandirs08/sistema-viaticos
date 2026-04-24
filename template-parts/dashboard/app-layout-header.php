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

if (!defined('ABSPATH')) {
    exit;
}

$args = wp_parse_args(
    $args,
    [
        'user_name' => '',
        'user_initials' => '',
        'logout_url' => '',
        'dashboard_role' => 'colaborador',
        'user_dni' => '',
        'user_cargo' => '',
        'user_area' => '',
        'user_aprobador' => '',
        'logo_url' => '',
    ]
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $args['dashboard_role'] === 'admin' ? 'Panel Administrador' : 'Dashboard Colaborador'; ?> —
        Sistema de Gestión de Viáticos</title>
    <meta name="description" content="Panel de gestión de viáticos — Fundación Romero.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <?php wp_head(); ?>

    <?php
    $_cat_terms = get_terms(array('taxonomy' => 'categoria_gasto', 'hide_empty' => false));
    $_cats_js = array();
    if (!is_wp_error($_cat_terms)) {
        foreach ($_cat_terms as $_ct) {
            $_cd = get_field('clase_doc', 'categoria_gasto_' . $_ct->term_id) ?: '';
            $_cats_js[] = array(
                'id' => $_ct->term_id,
                'nombre' => $_ct->name,
                'cta_contable' => get_field('cta_contable', 'categoria_gasto_' . $_ct->term_id) ?: '',
                'clase_doc' => $_cd,
                'tipo' => viaticos_clase_doc_to_tipo($_cd),
            );
        }
    }
    ?>
    <script>window.ViaticosCategoriasGasto = <?php echo wp_json_encode($_cats_js); ?>;</script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/gasto-ui.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/estado-ui.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/timeline-ui.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/liquidacion.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/utils.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/router.js"></script>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/modules/detalle-ui.js"></script>
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
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/fr-logo-mark.png'); ?>"
                            alt="FR" width="28" height="28">
                    </div>
                    <div class="logo-text">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/fr-logo2.png'); ?>"
                            alt="Fundación Romero" class="sidebar-logo-full">
                        <span><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador' : 'Colaborador'; ?></span>
                    </div>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Menú</p>
                <ul class="sidebar-nav" id="sidebar-nav-items">
                    <?php if ($args['dashboard_role'] === 'admin'): ?>
                        <li>
                            <a href="?view=anticipos" class="nav-link active" data-view="view-anticipos"
                                data-route="anticipos" id="nav-anticipos">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 12H7v-2h10v2zm0-4H7V9h10v2zm0-4H7V5h10v2z" />
                                </svg>
                                Anticipos
                            </a>
                        </li>
                        <li>
                            <a href="?view=rendiciones" class="nav-link" data-view="view-rendiciones"
                                data-route="rendiciones" id="nav-rendiciones">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 6a9.77 9.77 0 0 1 8.82 6A9.77 9.77 0 0 1 12 18a9.77 9.77 0 0 1-8.82-6A9.77 9.77 0 0 1 12 6zm0 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2.2a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6z" />
                                </svg>
                                Rendiciones
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="#" id="nav-inicio" class="nav-link active" data-view="view-inicio">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                </svg>
                                Inicio
                            </a>
                        </li>
                        <li>
                            <a href="#" id="nav-solicitudes" class="nav-link" data-view="view-solicitudes">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                </svg>
                                Mis Solicitudes
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar" aria-hidden="true"><?php echo esc_html($args['user_initials'] ?: 'U'); ?>
                    </div>
                    <div class="user-info">
                        <strong class="u-name"><?php echo esc_html($args['user_name']); ?></strong>
                        <span
                            class="u-role"><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador' : 'Colaborador'; ?></span>
                    </div>
                </div>
            </div>
        </aside><!-- /#erp-sidebar -->
        <div id="sidebar-overlay"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('btn-hamburger');
            var sidebar = document.getElementById('erp-sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            if (!btn || !sidebar || !overlay) return;
            function setSidebar(open) {
                sidebar.classList.toggle('open', open);
                overlay.classList.toggle('open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
            btn.addEventListener('click', function () { setSidebar(true); });
            overlay.addEventListener('click', function () { setSidebar(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setSidebar(false);
            });
        });
        </script>

        <!-- ══ MAIN AREA ════════════════════════════════════════════ -->
        <div id="erp-main">

            <!-- TOPBAR -->
            <header id="erp-topbar">
                <button class="btn-hamburger" id="btn-hamburger" aria-label="Abrir menú" aria-expanded="false">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                </button>
                <nav class="topbar-breadcrumb" aria-label="Ruta de navegación">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity:.5">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    &rsaquo;
                    <span
                        id="topbar-section-name"><?php echo $args['dashboard_role'] === 'admin' ? 'Anticipos' : 'Inicio'; ?></span>
                </nav>
                <div class="topbar-actions">
                    <div class="topbar-user-info" aria-label="Usuario autenticado">
                        <strong class="t-name"><?php echo esc_html($args['user_name']); ?></strong>
                        <span
                            class="t-role"><?php echo $args['dashboard_role'] === 'admin' ? 'Administrador de Viáticos' : 'Colaborador'; ?></span>
                        <?php if ($args['user_dni'] || $args['user_cargo'] || $args['user_area']): ?>
                            <div class="topbar-user-meta">
                                <?php if ($args['user_dni']): ?>
                                    <span class="user-meta-chip"><strong>DNI</strong>
                                        <?php echo esc_html($args['user_dni']); ?></span>
                                <?php endif; ?>
                                <?php if ($args['user_cargo']): ?>
                                    <span class="user-meta-chip"><strong>Cargo</strong>
                                        <?php echo esc_html($args['user_cargo']); ?></span>
                                <?php endif; ?>
                                <?php if ($args['user_area']): ?>
                                    <span class="user-meta-chip"><strong>Área</strong>
                                        <?php echo esc_html($args['user_area']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo $args['logout_url']; ?>" class="btn-logout" id="btn-logout"
                        title="Cerrar sesión">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1.0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                        </svg>
                        Salir
                    </a>
                </div>
            </header><!-- /#erp-topbar -->

            <!-- CONTENT AREA — views are injected here -->
            <main id="erp-content">

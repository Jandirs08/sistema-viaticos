<?php
/**
 * functions.php
 *
 * Archivo principal de funciones del tema "theme-administracion".
 * Responsable de:
 *  - Cargar hojas de estilo y scripts del tema.
 *  - Incluir los módulos de Custom Post Types y campos ACF.
 *
 * @package ThemeAdministracion
 * @version 1.0.0
 */

// Bloque de seguridad: impide el acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// 1. INCLUSIÓN DE MÓDULOS
// =============================================================================

/**
 * Incluye el módulo de registro de Custom Post Types.
 * Contiene la definición de 'solicitud_viatico' y 'gasto_rendicion'.
 */
require_once get_template_directory() . '/includes/cpt-setup.php';

/**
 * Incluye el módulo de campos ACF (Advanced Custom Fields).
 * Registra todos los field groups mediante acf_add_local_field_group().
 */
require_once get_template_directory() . '/includes/acf-fields.php';

/**
 * Incluye el módulo de taxonomías de usuario.
 */
require_once get_template_directory() . '/includes/user-taxonomies.php';

/**
 * Incluye el módulo de roles y seguridad.
 */
require_once get_template_directory() . '/includes/roles-setup.php';

/**
 * Incluye los endpoints de la API REST.
 */
require_once get_template_directory() . '/includes/api-endpoints.php';


// =============================================================================
// 2. CARGA DE ESTILOS Y SCRIPTS
// =============================================================================

/**
 * viaticos_enqueue_assets()
 *
 * Encola la hoja de estilos principal del tema y los scripts necesarios.
 * Se engancha en 'wp_enqueue_scripts' para cargarse en el front-end.
 *
 * @return void
 */
function viaticos_enqueue_assets()
{

    // --- Hoja de estilos principal del tema ---
    wp_enqueue_style(
        'viaticos-main-style',                      // Handle único.
        get_stylesheet_uri(),                        // Apunta a /style.css del tema.
        array(),                                     // Sin dependencias.
        wp_get_theme()->get('Version')             // Versión tomada del header del tema.
    );

    // --- Script principal del tema (cargado en el footer) ---
    // Descomenta y ajusta la ruta cuando necesites añadir JS propio.
    /*
    wp_enqueue_script(
        'viaticos-main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array( 'jquery' ),
        wp_get_theme()->get( 'Version' ),
        true  // true = carga en el footer para mejor rendimiento.
    );
    */
}
add_action('wp_enqueue_scripts', 'viaticos_enqueue_assets');


// =============================================================================
// 3. CONFIGURACIÓN BÁSICA DEL TEMA
// =============================================================================

/**
 * viaticos_theme_setup()
 *
 * Declara las características (features) que soporta el tema.
 * Se engancha en 'after_setup_theme' para ejecutarse temprano en el ciclo
 * de WordPress, antes de que se carguen los widgets y otras integraciones.
 *
 * @return void
 */
function viaticos_theme_setup()
{

    // Habilita soporte para el tag <title> generado dinámicamente por WordPress.
    add_theme_support('title-tag');

    // Habilita soporte para miniaturas de entradas (featured images).
    add_theme_support('post-thumbnails');

    // Compatibilidad con el editor en bloque (Gutenberg) mediante estilos del tema.
    add_theme_support('wp-block-styles');

    // Carga automática del archivo de traducción del tema.
    load_theme_textdomain('theme-administracion', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'viaticos_theme_setup');

// =============================================================================
// 4. FRONT DOOR: HOME, LOGIN AND LOGOUT
// =============================================================================

/**
 * Build the shared dashboard args used by the existing template parts.
 *
 * @return array<string, string>
 */
function theme_administracion_get_dashboard_args()
{
    $current_user = wp_get_current_user();
    $user_id = (int) $current_user->ID;
    $first_name = (string) $current_user->first_name;
    $last_name = (string) $current_user->last_name;
    $display_name = (string) $current_user->display_name;

    if (function_exists('mb_substr')) {
        $user_initials = strtoupper(
            mb_substr($first_name ?: $display_name, 0, 1) .
            mb_substr($last_name ?: '', 0, 1)
        );
    } else {
        $user_initials = strtoupper(
            substr($first_name ?: $display_name, 0, 1) .
            substr($last_name ?: '', 0, 1)
        );
    }

    $is_admin = in_array('administrator', (array) $current_user->roles, true);
    $user_acf_context = 'user_' . $user_id;
    $user_dni = function_exists('get_field') ? (string) get_field('dni', $user_acf_context) : '';
    $user_cargo_terms = wp_get_object_terms($user_id, 'viaticos_cargo', ['fields' => 'names']);
    $user_area_terms = wp_get_object_terms($user_id, 'viaticos_area', ['fields' => 'names']);
    $user_cargo = !is_wp_error($user_cargo_terms) && !empty($user_cargo_terms) ? (string) $user_cargo_terms[0] : '';
    $user_area = !is_wp_error($user_area_terms) && !empty($user_area_terms) ? (string) $user_area_terms[0] : '';

    return [
        'user_name' => esc_html($display_name),
        'user_initials' => $user_initials,
        'logout_url' => esc_url(wp_logout_url(home_url('/'))),
        'rest_nonce' => wp_create_nonce('wp_rest'),
        'api_base' => esc_js(rtrim(get_rest_url(null, 'viaticos/v1'), '/')),
        'dashboard_role' => $is_admin ? 'admin' : 'colaborador',
        'user_dni' => sanitize_text_field($user_dni),
        'user_cargo' => sanitize_text_field($user_cargo),
        'user_area' => sanitize_text_field($user_area),
    ];
}

/**
 * Detect whether the current login submission belongs to the front door form.
 *
 * @return bool
 */
function theme_administracion_is_front_login_submission()
{
    if ('POST' !== strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET')) {
        return false;
    }

    $redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw(wp_unslash($_REQUEST['redirect_to'])) : '';

    if (!$redirect_to) {
        return false;
    }

    return untrailingslashit($redirect_to) === untrailingslashit(home_url('/'));
}

/**
 * Render the public app entrypoint.
 *
 * Guests see the native WordPress login form. Authenticated users see the
 * existing dashboard shell and views.
 *
 * @return void
 */
function theme_administracion_render_front_app()
{
    if (!is_user_logged_in()) {
        theme_administracion_render_login_screen();
        return;
    }

    // The dashboard shell still defines its own <title>, so avoid rendering
    // WordPress' title tag a second time for authenticated app views.
    remove_action('wp_head', '_wp_render_title_tag', 1);

    $dashboard_args = theme_administracion_get_dashboard_args();
    $is_admin = 'admin' === $dashboard_args['dashboard_role'];

    get_template_part('template-parts/dashboard/app-layout-header', null, $dashboard_args);

    if ($is_admin) {
        get_template_part('template-parts/dashboard/view-admin', null, $dashboard_args);
    } else {
        get_template_part('template-parts/dashboard/view-colaborador', null, $dashboard_args);
    }

    get_template_part('template-parts/dashboard/app-layout-footer');
}

/**
 * Render the login page on "/".
 *
 * @return void
 */
function theme_administracion_render_login_screen()
{
    $login_state = isset($_GET['login']) ? sanitize_key(wp_unslash($_GET['login'])) : '';
    $message_text = '';
    $message_class = '';
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');

    if ('failed' === $login_state) {
        $message_text = 'Usuario o contrasena incorrectos.';
        $message_class = 'is-error';
    } elseif ('empty' === $login_state) {
        $message_text = 'Ingresa usuario y contrasena.';
        $message_class = 'is-error';
    } elseif ('loggedout' === $login_state) {
        $message_text = 'Sesion cerrada correctamente.';
        $message_class = 'is-success';
    }

    $login_form = wp_login_form([
        'echo' => false,
        'redirect' => home_url('/'),
        'remember' => true,
        'label_username' => 'Usuario o correo',
        'label_password' => 'Contrasena',
        'label_remember' => 'Recordarme',
        'label_log_in' => 'Ingresar',
    ]);
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <?php wp_head(); ?>
    <style>
        :root {
            --primary: #da5b3e;
            --primary-dark: #bf4c32;
            --bg: #f4f6f8;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --error-text: #b91c1c;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #047857;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body.theme-administracion-login {
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(218, 91, 62, .18), transparent 38%),
                linear-gradient(180deg, #fffaf8 0%, var(--bg) 100%);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-shell {
            width: min(100%, 420px);
        }

        .login-card {
            background: var(--card);
            border: 1px solid rgba(229, 231, 235, .9);
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .login-card__head {
            padding: 32px 32px 20px;
        }

        .login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(218, 91, 62, .12);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .login-card h1 {
            margin: 16px 0 8px;
            font-size: 28px;
            line-height: 1.1;
        }

        .login-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .login-card__body {
            padding: 0 32px 32px;
        }

        .login-message {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            line-height: 1.5;
        }

        .login-message.is-error {
            background: var(--error-bg);
            border-color: var(--error-border);
            color: var(--error-text);
        }

        .login-message.is-success {
            background: var(--success-bg);
            border-color: var(--success-border);
            color: var(--success-text);
        }

        .login-card form {
            display: grid;
            gap: 16px;
        }

        .login-card label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .login-card input[type="text"],
        .login-card input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .login-card input[type="text"]:focus,
        .login-card input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(218, 91, 62, .14);
        }

        .login-card .login-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
        }

        .login-card .login-remember label {
            margin: 0;
            font-weight: 500;
            color: var(--muted);
        }

        .login-card .button {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 13px 16px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s ease, transform .2s ease;
        }

        .login-card .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .login-card__links {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .login-card__links a {
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .login-card__links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-card__head,
            .login-card__body {
                padding-left: 22px;
                padding-right: 22px;
            }

            .login-card__links {
                flex-direction: column;
            }
        }
    </style>
</head>
<body <?php body_class('theme-administracion-login'); ?>>
<?php wp_body_open(); ?>
<div class="login-shell">
    <div class="login-card">
        <div class="login-card__head">
            <span class="login-eyebrow"><?php echo esc_html($site_name); ?></span>
            <h1>Acceso al sistema</h1>
            <p>
                <?php
                echo esc_html(
                    $site_description
                        ? $site_description
                        : 'Ingresa con tu cuenta para continuar.'
                );
                ?>
            </p>
        </div>
        <div class="login-card__body">
            <?php if ($message_text) : ?>
                <div class="login-message <?php echo esc_attr($message_class); ?>">
                    <?php echo esc_html($message_text); ?>
                </div>
            <?php endif; ?>

            <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="login-card__links">
                <a href="<?php echo esc_url(wp_lostpassword_url(home_url('/'))); ?>">Recuperar contrasena</a>
                <?php if (get_option('users_can_register')) : ?>
                    <a href="<?php echo esc_url(wp_registration_url()); ?>">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
    <?php
}

/**
 * Keep successful front login on the home URL.
 *
 * @param string           $redirect_to           Safe redirect destination.
 * @param string           $requested_redirect_to Requested destination.
 * @param WP_User|WP_Error $user                  Auth result.
 * @return string
 */
function theme_administracion_login_redirect($redirect_to, $requested_redirect_to, $user)
{
    if ($user instanceof WP_User && theme_administracion_is_front_login_submission()) {
        return home_url('/');
    }

    return $redirect_to;
}
add_filter('login_redirect', 'theme_administracion_login_redirect', 10, 3);

/**
 * Send front-login failures back to "/".
 *
 * @return void
 */
function theme_administracion_redirect_failed_login($username)
{
    if (!theme_administracion_is_front_login_submission()) {
        return;
    }

    wp_safe_redirect(add_query_arg('login', 'failed', home_url('/')));
    exit;
}
add_action('wp_login_failed', 'theme_administracion_redirect_failed_login');

/**
 * Handle empty credentials submitted from the front login form.
 *
 * @param WP_User|WP_Error|null $user     Auth result.
 * @param string                $username Username submitted.
 * @param string                $password Password submitted.
 * @return WP_User|WP_Error|null
 */
function theme_administracion_handle_empty_front_login($user, $username, $password)
{
    if (!theme_administracion_is_front_login_submission()) {
        return $user;
    }

    if ('' !== trim((string) $username) && '' !== trim((string) $password)) {
        return $user;
    }

    wp_safe_redirect(add_query_arg('login', 'empty', home_url('/')));
    exit;
}
add_filter('authenticate', 'theme_administracion_handle_empty_front_login', 30, 3);

/**
 * Always return logout to the home entrypoint.
 *
 * @return string
 */
function theme_administracion_logout_redirect($redirect_to, $requested_redirect_to, $user)
{
    return add_query_arg('login', 'loggedout', home_url('/'));
}
add_filter('logout_redirect', 'theme_administracion_logout_redirect', 10, 3);

/**
 * Redirect any direct front-end page access back to "/".
 *
 * @return void
 */
function theme_administracion_redirect_non_home_requests()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    if ((defined('REST_REQUEST') && REST_REQUEST) || is_feed() || is_preview() || is_trackback() || is_robots()) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

    if ($request_path === $home_path) {
        return;
    }

    if (is_page() || is_home() || is_single() || is_singular() || is_archive() || is_search() || is_404()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}
add_action('template_redirect', 'theme_administracion_redirect_non_home_requests');

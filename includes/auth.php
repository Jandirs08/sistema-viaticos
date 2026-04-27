<?php
/**
 * Hooks de autenticación: redirige login/logout/errores al entrypoint público.
 * Depende de theme_administracion_is_front_login_submission() definida en
 * includes/render-shell.php — cargar este archivo después.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
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

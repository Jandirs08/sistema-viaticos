<?php
/**
 * Template Name: Dashboard App
 *
 * Legacy route kept only to send the user back to "/".
 *
 * @package ThemeAdministracion
 */

if (!defined('ABSPATH')) {
    exit;
}

wp_safe_redirect(home_url('/'));
exit;

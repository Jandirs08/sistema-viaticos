<?php
/**
 * Página de configuración OCR en wp-admin (Ajustes → Viáticos OCR).
 * Requiere capability 'manage_options' (admins WP).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const VIATICOS_OCR_SETTINGS_SLUG  = 'viaticos-ocr';
const VIATICOS_OCR_SETTINGS_NONCE = 'viaticos_ocr_save';

function viaticos_ocr_register_menu() {
    add_options_page(
        'Viáticos OCR',
        'Viáticos OCR',
        'manage_options',
        VIATICOS_OCR_SETTINGS_SLUG,
        'viaticos_ocr_render_settings_page'
    );
}
add_action( 'admin_menu', 'viaticos_ocr_register_menu' );

function viaticos_ocr_handle_save() {
    if ( empty( $_POST['viaticos_ocr_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['viaticos_ocr_nonce'] ) ), VIATICOS_OCR_SETTINGS_NONCE ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings = array(
        'enabled'        => ! empty( $_POST['viaticos_ocr_enabled'] ),
        'provider'       => isset( $_POST['viaticos_ocr_provider'] ) ? sanitize_key( wp_unslash( $_POST['viaticos_ocr_provider'] ) ) : VIATICOS_OCR_DEFAULT_PROVIDER,
        'model'          => isset( $_POST['viaticos_ocr_model'] ) ? sanitize_text_field( wp_unslash( $_POST['viaticos_ocr_model'] ) ) : VIATICOS_OCR_DEFAULT_MODEL,
        'monthly_cap'    => isset( $_POST['viaticos_ocr_cap'] ) ? absint( wp_unslash( $_POST['viaticos_ocr_cap'] ) ) : VIATICOS_OCR_DEFAULT_CAP,
        'daily_user_cap' => isset( $_POST['viaticos_ocr_user_cap'] ) ? absint( wp_unslash( $_POST['viaticos_ocr_user_cap'] ) ) : VIATICOS_OCR_DEFAULT_USER_CAP,
    );
    viaticos_ocr_save_settings( $settings );

    if ( isset( $_POST['viaticos_ocr_token'] ) ) {
        $raw = trim( (string) wp_unslash( $_POST['viaticos_ocr_token'] ) );
        // Mantener token existente si el usuario deja el campo vacío y NO marcó eliminar.
        if ( '' !== $raw ) {
            viaticos_ocr_save_token( $raw );
        } elseif ( ! empty( $_POST['viaticos_ocr_clear_token'] ) ) {
            viaticos_ocr_save_token( '' );
        }
    }

    if ( ! empty( $_POST['viaticos_ocr_test'] ) ) {
        $test = viaticos_ocr_test_connection();
        if ( ! empty( $test['ok'] ) ) {
            add_settings_error( 'viaticos_ocr', 'viaticos_ocr_test_ok', $test['message'], 'success' );
        } else {
            add_settings_error( 'viaticos_ocr', 'viaticos_ocr_test_err', 'Test falló: ' . $test['error'], 'error' );
        }
    } else {
        add_settings_error(
            'viaticos_ocr',
            'viaticos_ocr_saved',
            'Configuración guardada.',
            'updated'
        );
    }
}
add_action( 'admin_init', 'viaticos_ocr_handle_save' );

function viaticos_ocr_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $tab           = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'config';
    $valid_tabs    = array( 'config', 'uso' );
    if ( ! in_array( $tab, $valid_tabs, true ) ) {
        $tab = 'config';
    }
    $base_url     = admin_url( 'options-general.php?page=' . VIATICOS_OCR_SETTINGS_SLUG );
    $tab_url      = function( $t ) use ( $base_url ) {
        return esc_url( add_query_arg( 'tab', $t, $base_url ) );
    };
    ?>
    <div class="wrap">
        <h1>Viáticos OCR</h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo $tab_url( 'config' ); ?>" class="nav-tab <?php echo 'config' === $tab ? 'nav-tab-active' : ''; ?>">Configuración</a>
            <a href="<?php echo $tab_url( 'uso' ); ?>" class="nav-tab <?php echo 'uso' === $tab ? 'nav-tab-active' : ''; ?>">Uso por usuario</a>
        </h2>

        <?php settings_errors( 'viaticos_ocr' ); ?>

        <?php if ( 'uso' === $tab ) :
            viaticos_ocr_render_usage_tab();
            echo '</div>';
            return;
        endif; ?>

        <?php
        $s        = viaticos_ocr_get_settings();
        $has_tok  = viaticos_ocr_token_is_set();
        $usage    = viaticos_ocr_check_cap();
        $form_url = $base_url;
        ?>
        <p>Configura el extractor automático de datos para boletas y facturas adjuntas durante la rendición.</p>

        <div class="notice notice-info inline">
            <p>
                <strong>Uso este mes:</strong>
                <?php echo (int) $usage['used']; ?>
                <?php if ( $usage['cap'] > 0 ) : ?>
                    de <?php echo (int) $usage['cap']; ?> llamadas (<?php echo (int) $usage['remaining']; ?> disponibles).
                <?php else : ?>
                    (cap mensual desactivado).
                <?php endif; ?>
            </p>
        </div>

        <form method="post" action="<?php echo esc_url( $form_url ); ?>">
            <?php wp_nonce_field( VIATICOS_OCR_SETTINGS_NONCE, 'viaticos_ocr_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="viaticos_ocr_enabled">Activar OCR</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="viaticos_ocr_enabled" id="viaticos_ocr_enabled" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?>>
                            Habilitado
                        </label>
                        <p class="description">Si está apagado, el endpoint OCR responde 503 y el botón en el wizard no aparece.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_provider">Proveedor</label></th>
                    <td>
                        <select name="viaticos_ocr_provider" id="viaticos_ocr_provider">
                            <option value="openai" <?php selected( 'openai', $s['provider'] ); ?>>OpenAI</option>
                            <option value="anthropic" disabled>Anthropic (próximamente)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_model">Modelo</label></th>
                    <td>
                        <input type="text" name="viaticos_ocr_model" id="viaticos_ocr_model" class="regular-text" value="<?php echo esc_attr( $s['model'] ); ?>">
                        <p class="description">Por defecto: <code>gpt-4o-mini</code>. Otros modelos OpenAI con visión también funcionan (ej. <code>gpt-4o</code>).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_cap">Límite mensual (global)</label></th>
                    <td>
                        <input type="number" name="viaticos_ocr_cap" id="viaticos_ocr_cap" min="0" value="<?php echo (int) $s['monthly_cap']; ?>" class="small-text">
                        <p class="description">Llamadas máximas por mes calendario (UTC) sumando a todos los usuarios. Pon <code>0</code> para desactivar.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_user_cap">Límite diario por usuario</label></th>
                    <td>
                        <input type="number" name="viaticos_ocr_user_cap" id="viaticos_ocr_user_cap" min="0" value="<?php echo (int) $s['daily_user_cap']; ?>" class="small-text">
                        <p class="description">Tope individual por día UTC para evitar abuso. Pon <code>0</code> para desactivar.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_token">API Token</label></th>
                    <td>
                        <input type="password" name="viaticos_ocr_token" id="viaticos_ocr_token" class="regular-text" autocomplete="new-password" value="" placeholder="<?php echo $has_tok ? '•••••••• (token actual conservado)' : 'sk-...'; ?>">
                        <p class="description">
                            <?php if ( $has_tok ) : ?>
                                Hay un token guardado y cifrado. Deja vacío para conservarlo, o pega uno nuevo para reemplazarlo.
                            <?php else : ?>
                                Sin token guardado. Pega tu API key del proveedor.
                            <?php endif; ?>
                        </p>
                        <?php if ( $has_tok ) : ?>
                            <label style="display:block;margin-top:6px;">
                                <input type="checkbox" name="viaticos_ocr_clear_token" value="1">
                                Eliminar el token guardado
                            </label>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <?php submit_button( 'Guardar cambios', 'primary', 'submit', false ); ?>
                <?php submit_button( 'Probar conexión', 'secondary', 'viaticos_ocr_test', false ); ?>
            </p>
        </form>

        <hr>
        <h2>Notas</h2>
        <ul>
            <li>El token se cifra con AES-256-CBC usando <code>AUTH_KEY</code> de <code>wp-config.php</code>. Si rotas <code>AUTH_KEY</code>, el token deja de funcionar y debes guardarlo de nuevo.</li>
            <li>Endpoint REST activo: <code>POST /viaticos/v1/ocr-extract</code>. Lo invoca el wizard de rendición cuando hay un comprobante adjunto.</li>
            <li>Los logs viven en la tabla <code><?php echo esc_html( $GLOBALS['wpdb']->prefix . VIATICOS_OCR_LOG_TABLE ); ?></code>.</li>
            <li>Para PDFs de varias páginas y fotos HEIC desde iPhone, el servidor necesita la extensión <code>Imagick</code> (PDF + HEIC) y el binario <code>Ghostscript</code> (texto de páginas 2+). Kinsta los incluye por default.</li>
        </ul>
    </div>
    <?php
}

/**
 * Renderiza la tab "Uso por usuario".
 * La query a la tabla de logs sólo se ejecuta cuando el admin entra a la tab.
 */
function viaticos_ocr_render_usage_tab() {
    $range_param   = isset( $_GET['rango'] ) ? sanitize_key( wp_unslash( $_GET['rango'] ) ) : 'mes_actual';
    $valid_rangos  = array( 'mes_actual', 'mes_anterior', 'ultimos_7d', 'ultimos_30d', 'todo' );
    if ( ! in_array( $range_param, $valid_rangos, true ) ) {
        $range_param = 'mes_actual';
    }

    switch ( $range_param ) {
        case 'mes_anterior':
            $since      = gmdate( 'Y-m-01 00:00:00', strtotime( 'first day of last month' ) );
            $until      = gmdate( 'Y-m-01 00:00:00' );
            $label      = 'Mes anterior';
            break;
        case 'ultimos_7d':
            $since      = gmdate( 'Y-m-d 00:00:00', strtotime( '-6 days' ) );
            $until      = '';
            $label      = 'Últimos 7 días';
            break;
        case 'ultimos_30d':
            $since      = gmdate( 'Y-m-d 00:00:00', strtotime( '-29 days' ) );
            $until      = '';
            $label      = 'Últimos 30 días';
            break;
        case 'todo':
            $since      = '1970-01-01 00:00:00';
            $until      = '';
            $label      = 'Todo el historial';
            break;
        case 'mes_actual':
        default:
            $since      = gmdate( 'Y-m-01 00:00:00' );
            $until      = '';
            $label      = 'Mes actual';
            break;
    }

    $rows = viaticos_ocr_get_usage_by_user( $since, $until, 50 );

    $total_calls   = array_sum( array_column( $rows, 'calls' ) );
    $total_success = array_sum( array_column( $rows, 'success' ) );
    $total_cost    = array_sum( array_column( $rows, 'cost_usd' ) );

    $base_url = admin_url( 'options-general.php?page=' . VIATICOS_OCR_SETTINGS_SLUG . '&tab=uso' );
    ?>
    <p>Top usuarios por consumo de OCR. Datos calculados sólo al entrar a esta pestaña.</p>

    <form method="get" style="margin-bottom:16px;">
        <input type="hidden" name="page" value="<?php echo esc_attr( VIATICOS_OCR_SETTINGS_SLUG ); ?>">
        <input type="hidden" name="tab" value="uso">
        <label>
            Rango:
            <select name="rango" onchange="this.form.submit()">
                <option value="mes_actual"   <?php selected( 'mes_actual',   $range_param ); ?>>Mes actual</option>
                <option value="mes_anterior" <?php selected( 'mes_anterior', $range_param ); ?>>Mes anterior</option>
                <option value="ultimos_7d"   <?php selected( 'ultimos_7d',   $range_param ); ?>>Últimos 7 días</option>
                <option value="ultimos_30d"  <?php selected( 'ultimos_30d',  $range_param ); ?>>Últimos 30 días</option>
                <option value="todo"         <?php selected( 'todo',         $range_param ); ?>>Todo</option>
            </select>
        </label>
        <noscript><button class="button">Aplicar</button></noscript>
    </form>

    <div class="notice notice-info inline">
        <p>
            <strong><?php echo esc_html( $label ); ?>:</strong>
            <?php echo (int) $total_calls; ?> llamadas
            (<?php echo (int) $total_success; ?> exitosas)
            · costo estimado <strong>$<?php echo number_format_i18n( $total_cost, 4 ); ?></strong> USD
        </p>
    </div>

    <?php if ( empty( $rows ) ) : ?>
        <p><em>Sin actividad en este rango.</em></p>
    <?php else : ?>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th style="text-align:right;">Llamadas</th>
                    <th style="text-align:right;">Éxito</th>
                    <th style="text-align:right;">Errores</th>
                    <th style="text-align:right;">Tokens (in/out)</th>
                    <th style="text-align:right;">Costo USD</th>
                    <th>Última llamada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) :
                    $user        = $row['user_id'] ? get_userdata( $row['user_id'] ) : null;
                    $name        = $user ? $user->display_name : '— sin usuario —';
                    $email       = $user ? $user->user_email : '';
                    $error_pct   = $row['calls'] > 0 ? round( ( $row['errors'] / $row['calls'] ) * 100 ) : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $name ); ?></strong>
                            <?php if ( $email ) : ?><br><small><?php echo esc_html( $email ); ?></small><?php endif; ?>
                        </td>
                        <td style="text-align:right;"><?php echo (int) $row['calls']; ?></td>
                        <td style="text-align:right;"><?php echo (int) $row['success']; ?></td>
                        <td style="text-align:right;">
                            <?php echo (int) $row['errors']; ?>
                            <?php if ( $row['errors'] > 0 ) : ?>
                                <small>(<?php echo (int) $error_pct; ?>%)</small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo number_format_i18n( $row['tokens_in'] ); ?> /
                            <?php echo number_format_i18n( $row['tokens_out'] ); ?>
                        </td>
                        <td style="text-align:right;">$<?php echo number_format_i18n( $row['cost_usd'], 4 ); ?></td>
                        <td>
                            <?php
                            $ts = strtotime( $row['last_call'] . ' UTC' );
                            echo $ts ? esc_html( wp_date( 'Y-m-d H:i', $ts ) ) : esc_html( $row['last_call'] );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="description" style="margin-top:8px;">Top 50 usuarios. Costos calculados con los precios públicos de OpenAI registrados al momento de cada llamada.</p>
    <?php endif; ?>
    <?php
}

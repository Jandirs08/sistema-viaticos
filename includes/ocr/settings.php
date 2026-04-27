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
        'enabled'     => ! empty( $_POST['viaticos_ocr_enabled'] ),
        'provider'    => isset( $_POST['viaticos_ocr_provider'] ) ? sanitize_key( wp_unslash( $_POST['viaticos_ocr_provider'] ) ) : VIATICOS_OCR_DEFAULT_PROVIDER,
        'model'       => isset( $_POST['viaticos_ocr_model'] ) ? sanitize_text_field( wp_unslash( $_POST['viaticos_ocr_model'] ) ) : VIATICOS_OCR_DEFAULT_MODEL,
        'monthly_cap' => isset( $_POST['viaticos_ocr_cap'] ) ? absint( wp_unslash( $_POST['viaticos_ocr_cap'] ) ) : VIATICOS_OCR_DEFAULT_CAP,
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
    $s        = viaticos_ocr_get_settings();
    $has_tok  = viaticos_ocr_token_is_set();
    $usage    = viaticos_ocr_check_cap();
    $form_url = admin_url( 'options-general.php?page=' . VIATICOS_OCR_SETTINGS_SLUG );
    ?>
    <div class="wrap">
        <h1>Viáticos OCR</h1>
        <p>Configura el extractor automático de datos para boletas y facturas adjuntas durante la rendición.</p>

        <?php settings_errors( 'viaticos_ocr' ); ?>

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
                            <option value="anthropic" <?php selected( 'anthropic', $s['provider'] ); ?>>Anthropic</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_model">Modelo</label></th>
                    <td>
                        <input type="text" name="viaticos_ocr_model" id="viaticos_ocr_model" class="regular-text" value="<?php echo esc_attr( $s['model'] ); ?>">
                        <p class="description">Por defecto: <code>gpt-4o-mini</code> (OpenAI) o <code>claude-sonnet-4-6</code> (Anthropic).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="viaticos_ocr_cap">Límite mensual</label></th>
                    <td>
                        <input type="number" name="viaticos_ocr_cap" id="viaticos_ocr_cap" min="0" value="<?php echo (int) $s['monthly_cap']; ?>" class="small-text">
                        <p class="description">Llamadas máximas por mes calendario (UTC). Pon <code>0</code> para desactivar el límite.</p>
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
            <li>El endpoint REST <code>POST /viaticos/v1/ocr-extract</code> se activará en la siguiente fase del proyecto.</li>
            <li>Los logs viven en la tabla <code><?php echo esc_html( $GLOBALS['wpdb']->prefix . VIATICOS_OCR_LOG_TABLE ); ?></code>.</li>
        </ul>
    </div>
    <?php
}

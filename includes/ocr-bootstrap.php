<?php
/**
 * OCR bootstrap: carga storage, helpers y página de settings.
 * Punto de entrada único — functions.php solo hace require de este archivo.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ocr/storage.php';
require_once __DIR__ . '/ocr/helpers.php';
require_once __DIR__ . '/ocr/extractor.php';

if ( is_admin() ) {
    require_once __DIR__ . '/ocr/settings.php';
}

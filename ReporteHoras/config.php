<?php
/**
 * config.php
 * Piezas compartidas entre los demás archivos PHP: las columnas del CSV
 * y cómo se decide el nombre del archivo de reporte de cada consultor.
 * No se llama directamente desde el navegador, solo lo usan los otros scripts.
 */

define('COLUMNAS', ['fecha', 'cliente', 'proyecto', 'actividad', 'tiempo']);
define('RUTA_CONSULTORES', __DIR__ . '/consultores.txt');

// Convierte el nombre del consultor en algo seguro para usar como nombre de
// archivo, ej. "Juan Pérez" -> "juan_perez".
function nombreDeArchivo($consultor) {
    $texto = mb_strtolower(trim($consultor), 'UTF-8');
    $sinAcentos = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', 'ü' => 'u',
    ]);
    $limpio = preg_replace('/[^a-z0-9]+/', '_', $sinAcentos);
    $limpio = trim($limpio, '_');
    return $limpio !== '' ? $limpio : 'sin_nombre';
}

// Cada consultor tiene su propio archivo: Reporte_juan_perez.csv,
// Reporte_maria_lopez.csv, etc., todos en esta misma carpeta.
function rutaReporte($consultor) {
    return __DIR__ . '/Reporte_' . nombreDeArchivo($consultor) . '.csv';
}

<?php
/**
 * descargar.php
 * Entrega el reporte del consultor indicado (?consultor=Nombre) como
 * descarga, con el nombre correcto, sin importar cómo esté configurado
 * el servidor para servir archivos .csv.
 */

require __DIR__ . '/config.php';

$consultor = isset($_GET['consultor']) ? trim($_GET['consultor']) : '';
if ($consultor === '') {
    http_response_code(400);
    echo 'Falta indicar el consultor.';
    exit;
}

$rutaCsv = rutaReporte($consultor);

if (!file_exists($rutaCsv)) {
    http_response_code(404);
    echo 'Todavía no hay registros guardados para este consultor.';
    exit;
}

$nombreDescarga = 'Reporte_' . nombreDeArchivo($consultor) . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
header('Content-Length: ' . filesize($rutaCsv));
readfile($rutaCsv);

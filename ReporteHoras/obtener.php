<?php
/**
 * obtener.php
 * Lee el reporte del consultor indicado (?consultor=Nombre) y regresa todos
 * sus registros en JSON, para que la página los muestre al abrirla.
 * Solo lee, nunca modifica el archivo.
 */

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$consultor = isset($_GET['consultor']) ? trim($_GET['consultor']) : '';
if ($consultor === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Falta el consultor.']);
    exit;
}

$rutaCsv = rutaReporte($consultor);
$registros = [];

if (file_exists($rutaCsv)) {
    $archivo = fopen($rutaCsv, 'r');

    // Si el archivo empieza con el BOM UTF-8 (3 bytes invisibles), lo saltamos;
    // si no lo tiene (archivos viejos), regresamos al inicio para no perder nada.
    $primerosBytes = fread($archivo, 3);
    if ($primerosBytes !== "\xEF\xBB\xBF") {
        rewind($archivo);
    }

    $encabezado = fgetcsv($archivo); // primera fila: nombres de columnas

    if ($encabezado) {
        while (($fila = fgetcsv($archivo)) !== false) {
            // Si una fila viene incompleta o corrupta, la saltamos en vez de tronar.
            if (count($fila) !== count($encabezado)) {
                continue;
            }
            $registros[] = array_combine($encabezado, $fila);
        }
    }

    fclose($archivo);
}

echo json_encode(['ok' => true, 'registros' => $registros]);

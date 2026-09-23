<?php
/**
 * borrar.php
 * Reinicia el reporte del consultor indicado, dejándolo solo con su
 * encabezado, para empezar una nueva serie de registros. No toca los
 * reportes de los demás consultores. La página solo llama a esto después
 * de que el usuario confirma explícitamente que quiere borrar todo.
 */

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$datos = json_decode(file_get_contents('php://input'), true);
$consultor = trim((string)($datos['consultor'] ?? ''));

if ($consultor === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Falta el consultor.']);
    exit;
}

$rutaCsv = rutaReporte($consultor);

$archivo = fopen($rutaCsv, 'w'); // 'w' = crear vacío / sobrescribir desde cero
if ($archivo === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo reiniciar el archivo del reporte.']);
    exit;
}

// BOM UTF-8: son 3 bytes invisibles que le indican a Excel que el archivo
// está en UTF-8, para que muestre bien las tildes y la "ñ" al abrirlo.
fwrite($archivo, "\xEF\xBB\xBF");
fputcsv($archivo, COLUMNAS);
fclose($archivo);

echo json_encode(['ok' => true]);

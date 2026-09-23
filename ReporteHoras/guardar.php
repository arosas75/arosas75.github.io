<?php
/**
 * guardar.php
 * Recibe un registro (consultor, fecha, cliente, proyecto, actividad, tiempo)
 * como JSON y lo agrega al final del reporte de ESE consultor
 * (Reporte_<consultor>.csv, ver config.php), en esta misma carpeta.
 *
 * Reglas que sigue, tal como se pidieron:
 * - Cada consultor tiene su propio archivo, separado de los demás.
 * - Si el archivo del consultor no existe, se crea aquí mismo con su encabezado.
 * - Nunca se borra ni se reescribe lo que ya había, solo se agrega al final.
 * - No se revisan duplicados: si mandas el mismo registro dos veces, se guardan las dos.
 */

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// Leemos el JSON que mandó la página.
$cuerpo = file_get_contents('php://input');
$datos = json_decode($cuerpo, true);

if (!is_array($datos) || trim((string)($datos['consultor'] ?? '')) === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Falta el consultor.']);
    exit;
}

if (empty($datos['actividad'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Falta la actividad del registro.']);
    exit;
}

$rutaCsv = rutaReporte($datos['consultor']);

// ¿El archivo ya existía con contenido? Si no, hay que escribir el encabezado primero.
$archivoYaTeniaContenido = file_exists($rutaCsv) && filesize($rutaCsv) > 0;

$archivo = fopen($rutaCsv, 'a'); // 'a' = abrir para agregar al final (append)
if ($archivo === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo abrir el archivo del reporte.']);
    exit;
}

if (!$archivoYaTeniaContenido) {
    // BOM UTF-8: son 3 bytes invisibles que le indican a Excel que el archivo
    // está en UTF-8, para que muestre bien las tildes y la "ñ" al abrirlo.
    fwrite($archivo, "\xEF\xBB\xBF");
    fputcsv($archivo, COLUMNAS);
}

// Armamos la fila en el mismo orden que las columnas, tomando lo que llegó.
$fila = [];
foreach (COLUMNAS as $columna) {
    $fila[] = isset($datos[$columna]) ? $datos[$columna] : '';
}
fputcsv($archivo, $fila);

fclose($archivo);

echo json_encode(['ok' => true]);

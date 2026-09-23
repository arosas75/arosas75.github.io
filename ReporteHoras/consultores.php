<?php
/**
 * consultores.php
 * Maneja la lista de consultores que capturan horas, cada uno con su propio
 * archivo de reporte (ver config.php).
 *
 * GET  -> regresa {"consultores": [...]}
 * POST -> agrega un consultor nuevo. Cuerpo JSON: {"valor": "Juan Pérez"}
 *         Si ya existe uno igual (sin importar mayúsculas/espacios), no se duplica.
 *
 * Los nombres se guardan uno por línea en consultores.txt, en esta misma carpeta.
 */

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function leerConsultores() {
    if (!file_exists(RUTA_CONSULTORES)) return [];
    $lineas = file(RUTA_CONSULTORES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $lineas ? array_values($lineas) : [];
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    echo json_encode(['ok' => true, 'consultores' => leerConsultores()]);
    exit;
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $valor = trim(isset($datos['valor']) ? $datos['valor'] : '');

    if ($valor === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Falta el nombre del consultor.']);
        exit;
    }

    $lista = leerConsultores();
    $normalizadoNuevo = mb_strtolower(preg_replace('/\s+/', '', $valor), 'UTF-8');
    $yaExiste = false;
    foreach ($lista as $existente) {
        if (mb_strtolower(preg_replace('/\s+/', '', $existente), 'UTF-8') === $normalizadoNuevo) {
            $yaExiste = true;
            break;
        }
    }

    if (!$yaExiste) {
        $lista[] = $valor;
        sort($lista, SORT_FLAG_CASE | SORT_STRING);
        file_put_contents(RUTA_CONSULTORES, implode("\n", $lista) . "\n");
    }

    echo json_encode(['ok' => true, 'consultores' => $lista]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);

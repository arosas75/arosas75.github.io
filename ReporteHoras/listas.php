<?php
/**
 * listas.php
 * Maneja las listas de clientes y proyectos ya usados, para poder elegir de
 * una lista existente y también agregar nombres nuevos.
 *
 * GET  -> regresa {"clientes": [...], "proyectos": [...]}
 * POST -> agrega un valor nuevo. Cuerpo JSON: {"tipo":"cliente"|"proyecto","valor":"Alsuper"}
 *         Si el valor ya existe (sin importar mayúsculas o espacios), no lo duplica.
 *
 * Los nombres se guardan uno por línea en clientes.txt y proyectos.txt,
 * en esta misma carpeta.
 */

header('Content-Type: application/json; charset=utf-8');

define('RUTA_CLIENTES', __DIR__ . '/clientes.txt');
define('RUTA_PROYECTOS', __DIR__ . '/proyectos.txt');

function leerLista($ruta) {
    if (!file_exists($ruta)) return [];
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $lineas ? array_values($lineas) : [];
}

// Quita espacios y acentos y pasa a minúsculas, para comparar sin importar
// cómo se haya escrito ("Alsuper" y "al super" deben verse como lo mismo).
function normalizar($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = preg_replace('/\s+/', '', $texto);
    return $texto;
}

function agregarALista($ruta, $valor) {
    $valor = trim($valor);
    if ($valor === '') return leerLista($ruta);

    $lista = leerLista($ruta);
    $normalizado = normalizar($valor);

    foreach ($lista as $existente) {
        if (normalizar($existente) === $normalizado) {
            return $lista; // ya existía uno igual, no se duplica
        }
    }

    $lista[] = $valor;
    sort($lista, SORT_FLAG_CASE | SORT_STRING);
    file_put_contents($ruta, implode("\n", $lista) . "\n");
    return $lista;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    echo json_encode([
        'ok' => true,
        'clientes' => leerLista(RUTA_CLIENTES),
        'proyectos' => leerLista(RUTA_PROYECTOS),
    ]);
    exit;
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $tipo = isset($datos['tipo']) ? $datos['tipo'] : '';
    $valor = isset($datos['valor']) ? $datos['valor'] : '';

    if (!in_array($tipo, ['cliente', 'proyecto']) || trim($valor) === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Faltan datos (tipo o valor).']);
        exit;
    }

    $ruta = ($tipo === 'cliente') ? RUTA_CLIENTES : RUTA_PROYECTOS;
    $lista = agregarALista($ruta, $valor);

    echo json_encode(['ok' => true, 'lista' => $lista]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);

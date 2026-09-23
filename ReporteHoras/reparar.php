<?php
/**
 * reparar.php
 * Herramienta de un solo uso. Revisa TODOS los reportes que encuentre en esta
 * carpeta (de cualquier consultor, y también el viejo Reporte.csv si existe
 * de antes de que hubiera consultores) y le agrega el BOM UTF-8 al que le
 * falte, sin tocar ni borrar ninguna fila del historial.
 *
 * Cómo usarlo: entra una sola vez desde el navegador, por ejemplo
 * http://tudominio.com/bitacora/reparar.php — y ya. Después puedes borrar
 * este archivo del servidor si quieres, no hace falta dejarlo instalado.
 */

$archivos = glob(__DIR__ . '/Reporte*.csv');

if (empty($archivos)) {
    echo 'Todavía no hay ningún archivo de reporte, no hay nada que reparar.';
    exit;
}

foreach ($archivos as $ruta) {
    $nombre = basename($ruta);
    $contenido = file_get_contents($ruta);
    $yaTeniaBom = substr($contenido, 0, 3) === "\xEF\xBB\xBF";

    if ($yaTeniaBom) {
        echo "$nombre: ya tenía el BOM UTF-8, no hacía falta reparar.<br>";
        continue;
    }

    file_put_contents($ruta, "\xEF\xBB\xBF" . $contenido);
    echo "$nombre: listo, se le agregó el BOM UTF-8 sin tocar ninguna fila.<br>";
}

echo '<br>Ábrelos de nuevo en Excel: los acentos deberían verse bien.';

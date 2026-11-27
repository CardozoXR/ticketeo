<?php
date_default_timezone_set('America/Montevideo');

/*
 * PRUEBA ESC/POS NATIVO - TICKeteo
 * Impresora compartida: caja1
 * Ancho de impresión: 58 mm
 */

$raw = "";

// Comandos básicos
$ESC = "\x1B";
$GS = "\x1D";

function comandoTexto($texto)
{
    // Conversión para impresoras configuradas con PC850
    return iconv("UTF-8", "CP850//TRANSLIT", $texto);
}

function alinear($posicion)
{
    // 0 izquierda, 1 centro, 2 derecha
    return "\x1B\x61" . chr($posicion);
}

function negrita($activar)
{
    return "\x1B\x45" . chr($activar ? 1 : 0);
}

function tamano($valor)
{
    return "\x1D\x21" . chr($valor);
}

function imagenESCPos($ruta, $anchoMaximo = 280)
{

    if (!extension_loaded('gd')) {
        throw new Exception("La extensión GD no está habilitada.");
    }

    if (!file_exists($ruta)) {
        throw new Exception("No se encontró el logo: " . $ruta);
    }

    $imagen = imagecreatefrompng($ruta);

    if (!$imagen) {
        throw new Exception("No se pudo cargar el logo.");
    }

    $anchoOriginal = imagesx($imagen);
    $altoOriginal = imagesy($imagen);

    $nuevoAncho = min($anchoOriginal, $anchoMaximo);
    $nuevoAlto = (int) round(
        $altoOriginal * ($nuevoAncho / $anchoOriginal)
    );

    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

    // Fondo blanco para transparencias
    $blanco = imagecolorallocate($destino, 255, 255, 255);
    imagefill($destino, 0, 0, $blanco);

    imagecopyresampled(
        $destino,
        $imagen,
        0,
        0,
        0,
        0,
        $nuevoAncho,
        $nuevoAlto,
        $anchoOriginal,
        $altoOriginal
    );

    // Ancho en bytes (8 píxeles por byte)
    $anchoBytes = (int) ceil($nuevoAncho / 8);

    $datos = "";

    for ($y = 0; $y < $nuevoAlto; $y++) {

        for ($xByte = 0; $xByte < $anchoBytes; $xByte++) {

            $byte = 0;

            for ($bit = 0; $bit < 8; $bit++) {

                $x = ($xByte * 8) + $bit;

                if ($x >= $nuevoAncho) {
                    continue;
                }

                $rgb = imagecolorat($destino, $x, $y);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $gris = (int) (
                    0.299 * $r +
                    0.587 * $g +
                    0.114 * $b
                );

                // Negro si el píxel es oscuro
                if ($gris < 160) {
                    $byte |= (128 >> $bit);
                }
            }

            $datos .= chr($byte);
        }
    }

    imagedestroy($imagen);
    imagedestroy($destino);

    // GS v 0 - Raster Bit Image
    return "\x1D\x76\x30\x00" .
        chr($anchoBytes & 0xFF) .
        chr(($anchoBytes >> 8) & 0xFF) .
        chr($nuevoAlto & 0xFF) .
        chr(($nuevoAlto >> 8) & 0xFF) .
        $datos . "\n";
}

function imagenFooterESCPos($anchoMaximo = 280)
{
    $ruta = __DIR__ . "/imgs/footer-ticketeo.png";

    if (!extension_loaded('gd')) {
        throw new Exception("La extensión GD no está habilitada.");
    }

    if (!file_exists($ruta)) {
        throw new Exception("No se encontró el footer: " . $ruta);
    }

    $imagen = imagecreatefrompng($ruta);

    if (!$imagen) {
        throw new Exception("No se pudo cargar el footer.");
    }

    $anchoOriginal = imagesx($imagen);
    $altoOriginal = imagesy($imagen);

    $nuevoAncho = min($anchoOriginal, $anchoMaximo);

    $nuevoAlto = (int) round(
        $altoOriginal * ($nuevoAncho / $anchoOriginal)
    );

    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

    // Fondo blanco
    $blanco = imagecolorallocate($destino, 255, 255, 255);
    imagefill($destino, 0, 0, $blanco);

    imagecopyresampled(
        $destino,
        $imagen,
        0,
        0,
        0,
        0,
        $nuevoAncho,
        $nuevoAlto,
        $anchoOriginal,
        $altoOriginal
    );

    // Conversión a ESC/POS
    $anchoBytes = (int) ceil($nuevoAncho / 8);

    $datos = "";

    for ($y = 0; $y < $nuevoAlto; $y++) {

        for ($xByte = 0; $xByte < $anchoBytes; $xByte++) {

            $byte = 0;

            for ($bit = 0; $bit < 8; $bit++) {

                $x = ($xByte * 8) + $bit;

                if ($x >= $nuevoAncho) {
                    continue;
                }

                $rgb = imagecolorat($destino, $x, $y);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $gris = (int) (
                    0.299 * $r +
                    0.587 * $g +
                    0.114 * $b
                );

                if ($gris < 160) {
                    $byte |= (128 >> $bit);
                }
            }

            $datos .= chr($byte);
        }
    }

    imagedestroy($imagen);
    imagedestroy($destino);

    return "\x1D\x76\x30\x00" .
        chr($anchoBytes & 0xFF) .
        chr(($anchoBytes >> 8) & 0xFF) .
        chr($nuevoAlto & 0xFF) .
        chr(($nuevoAlto >> 8) & 0xFF) .
        $datos . "\n";
}

function codigoQR($contenido, $tamano = 6, $correccion = 48)
{

    $GS = "\x1D";

    // Seleccionar modelo QR
    $comando = $GS . "(k" .
        "\x04\x00" .
        "\x31\x41\x32\x00";

    // Tamaño del módulo
    $comando .= $GS . "(k" .
        "\x03\x00" .
        "\x31\x43" . chr($tamano);

    // Corrección de errores
    // 48 = nivel L
    $comando .= $GS . "(k" .
        "\x03\x00" .
        "\x31\x45" . chr($correccion);

    // Guardar contenido del QR
    $longitud = strlen($contenido) + 3;

    $comando .= $GS . "(k" .
        chr($longitud & 0xFF) .
        chr(($longitud >> 8) & 0xFF) .
        "\x31\x50\x30" .
        $contenido;

    // Imprimir QR
    $comando .= $GS . "(k" .
        "\x03\x00" .
        "\x31\x51\x30";

    return $comando;
}

function franjaCodigoESCPos($texto)
{
    if (!extension_loaded('gd')) {
        throw new Exception("La extensión GD no está habilitada.");
    }

    $fuente = "C:/Windows/Fonts/arialbd.ttf";

    if (!file_exists($fuente)) {
        throw new Exception("No se encontró la fuente Arial Bold.");
    }

    // Ancho imprimible de una impresora térmica de 58 mm
    $ancho = 384;

    // Altura de la franja
    $alto = 25;

    $imagen = imagecreatetruecolor($ancho, $alto);

    $negro = imagecolorallocate($imagen, 0, 0, 0);
    $blanco = imagecolorallocate($imagen, 255, 255, 255);

    // Fondo negro
    imagefilledrectangle(
        $imagen,
        0,
        0,
        $ancho - 1,
        $alto - 1,
        $negro
    );

    // Tamaño de letra normal
    $tamanoFuente = 19;

    // Calcular ancho del texto
    $caja = imagettfbbox($tamanoFuente, 0, $fuente, $texto);

    $anchoTexto = $caja[2] - $caja[0];

    // Reducir si no entra
    while ($anchoTexto > ($ancho - 20) && $tamanoFuente > 10) {

        $tamanoFuente--;

        $caja = imagettfbbox(
            $tamanoFuente,
            0,
            $fuente,
            $texto
        );

        $anchoTexto = $caja[2] - $caja[0];
    }

    // Centrar horizontalmente
    $x = (int) (($ancho - $anchoTexto) / 2);

    // Centrar verticalmente
    $altoTexto = $caja[1] - $caja[7];

    $y = (int) (($alto - $altoTexto) / 2) - $caja[7];

    // Dibujar texto blanco
    imagettftext(
        $imagen,
        $tamanoFuente,
        0,
        $x,
        $y,
        $blanco,
        $fuente,
        $texto
    );

    // Convertir imagen a bytes ESC/POS
    $anchoBytes = (int) ceil($ancho / 8);

    $datos = "";

    for ($fila = 0; $fila < $alto; $fila++) {

        for ($byte = 0; $byte < $anchoBytes; $byte++) {

            $valor = 0;

            for ($bit = 0; $bit < 8; $bit++) {

                $px = ($byte * 8) + $bit;

                $rgb = imagecolorat($imagen, $px, $fila);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $gris = (int) (
                    0.299 * $r +
                    0.587 * $g +
                    0.114 * $b
                );

                // Negro = punto impreso
                if ($gris < 128) {
                    $valor |= (128 >> $bit);
                }
            }

            $datos .= chr($valor);
        }
    }

    imagedestroy($imagen);

    // ESC/POS GS v 0
    return "\x1D\x76\x30\x00" .
        chr($anchoBytes & 0xFF) .
        chr(($anchoBytes >> 8) & 0xFF) .
        chr($alto & 0xFF) .
        chr(($alto >> 8) & 0xFF) .
        $datos .
        "\n";
}


function imprimirTicket($producto, $precio, $codigo, $nombreEvento, $fechaEvento, $impresora, $logo)
{
    $archivoTemporal = __DIR__ . "/impresion_ticket.bin";

    if ($precio != 0) {
        $precio = "$ " . $precio;
    } else {
        $precio = "CORTESÍA";
    }

    $raw = "";

    try {

        // Inicializar impresora
        $raw .= "\x1B\x40";

        // Seleccionar página de códigos
        $raw .= "\x1B\x74\x02";

        // FRANJA NEGRA CON CODIGO BLANCO
        $raw .= alinear(1);
        $raw .= franjaCodigoESCPos($codigo);
        $raw .= "\n";

        // LOGO
        $raw .= alinear(1);
        $raw .= imagenESCPos($logo);

        $raw .= "\n";

        // ENCABEZADO
        $raw .= tamano(0x01);
        $raw .= negrita(true);
        $raw .= comandoTexto("$nombreEvento $fechaEvento\n");
        $raw .= negrita(false);
        $raw .= tamano(0);

        $raw .= comandoTexto("----------------------------\n");

        // PRODUCTO
        $raw .= tamano(0x11);
        $raw .= negrita(true);

        $textoProducto = wordwrap("1x " . $producto, 16, "\n", false);

        $raw .= comandoTexto($textoProducto . "\n");

        $raw .= "\n";

        // PRECIO
        $raw .= "\x1D\x21\x22";
        $raw .= comandoTexto($precio . "\n");
        $raw .= tamano(0);
        $raw .= negrita(false);

        $raw .= "\n";

        // QR
        $raw .= codigoQR($codigo, 7, 48);

        // CODIGO
        $raw .= alinear(1);
        $raw .= tamano(0x00);
        $raw .= negrita(true);
        $raw .= comandoTexto("$codigo\n");
        $raw .= negrita(false);

        $raw .= comandoTexto("----------------------------\n");

        $raw .= "\n\n";

        // Cortar papel
        $raw .= "\x1D\x56\x00";

        // Guardar archivo binario
        if (file_put_contents($archivoTemporal, $raw) === false) {
            throw new Exception("No se pudo guardar el archivo de impresión.");
        }

        $archivoTemporalReal = realpath($archivoTemporal);

        if ($archivoTemporalReal === false) {
            throw new Exception("No se pudo obtener la ruta del archivo temporal.");
        }

        // Enviar RAW a la impresora compartida
        $comando = 'cmd.exe /c copy /b "' .
            $archivoTemporalReal . '" "' .
            $impresora . '"';

        exec($comando . " 2>&1", $salida, $resultado);

        if ($resultado !== 0) {
            throw new Exception(
                "Error enviando a impresora: " .
                implode("\n", $salida)
            );
        }

        return [
            "status" => "ok",
            "message" => "Trabajo enviado a la impresora correctamente."
        ];

    } catch (Throwable $e) {

        return [
            "status" => "error",
            "message" => $e->getMessage()
        ];
    }
}

function imprimirResumen($nombreImpresora, array $resumen, $logo = null)
{
    date_default_timezone_set('America/Montevideo');

    $archivoTemporal = __DIR__ . "/impresion_resumen.bin";

    if (empty($nombreImpresora)) {
        $nombreImpresora = "caja1";
    }

    if (!preg_match('/^[a-zA-Z0-9_\-\\\\. ]+$/', $nombreImpresora)) {
        return ["status" => "error", "message" => "Nombre de impresora inválido."];
    }

    $impresora = str_starts_with($nombreImpresora, '\\\\')
        ? $nombreImpresora
        : '\\\\localhost\\' . $nombreImpresora;

    $ancho = 28;
    $sep = str_repeat('-', $ancho) . "\n";
    $sepD = str_repeat('=', $ancho) . "\n";

    $par = function (string $izq, string $der) use ($ancho): string {
        $esp = max(1, $ancho - mb_strlen($izq) - mb_strlen($der));
        return $izq . str_repeat(' ', $esp) . $der . "\n";
    };
    $dinero = fn($v) => '$ ' . number_format((float) $v, 0, ',', '.');
    $num = fn($v) => number_format((int) $v, 0, ',', '.');
    $up = fn($s) => mb_strtoupper((string) $s, 'UTF-8');

    // Bloque de ventas + tickets
    $bloque = function (string $titV, array $v, string $titT, array $t) use ($par, $dinero, $num, $sep): string {

        $o = alinear(1);

        // TITULO VENTAS
        $o .= tamano(0x01);
        $o .= negrita(true);
        $o .= comandoTexto($titV . "\n");
        $o .= negrita(false);
        $o .= tamano(0x00);

        $o .= alinear(0);

        // DATOS VENTAS
        $o .= negrita(true) . comandoTexto($par("TOTAL:", $dinero($v['total'] ?? 0))) . negrita(false);
        $o .= "\n";
        $o .= comandoTexto($par("EFECTIVO:", $dinero($v['efectivo'] ?? 0)));
        $o .= comandoTexto($par("TARJETA:", $dinero($v['tarjeta'] ?? 0)));

        $o .= alinear(1) . comandoTexto($sep);

        // TITULO TICKETS
        $o .= tamano(0x01);
        $o .= negrita(true);
        $o .= comandoTexto($titT . "\n");
        $o .= negrita(false);
        $o .= tamano(0x00);

        $o .= alinear(0);

        // DATOS TICKETS
        $o .= negrita(true) . comandoTexto($par("TOTAL EMITIDOS:", $num($t['total_emitidos'] ?? 0))) . negrita(false);
        $o .= "\n";
        $o .= comandoTexto($par("ORIGINAL:", $num($t['original'] ?? 0)));
        $o .= comandoTexto($par("REIMPRESION:", $num($t['reimpresion'] ?? 0)));

        return $o;
    };

    $raw = "";

    try {
        // Inicializar + página de códigos (igual que el ticket)
        $raw .= "\x1B\x40";
        $raw .= "\x1B\x74\x02";

        // FRANJA NEGRA CON TÍTULO BLANCO
        $raw .= alinear(1);
        $raw .= franjaCodigoESCPos($up($resumen['titulo_head'] ?? 'RESUMEN'));
        $raw .= "\n";

        // LOGO
        if ($logo && is_file($logo)) {
            $raw .= alinear(1);
            $raw .= imagenESCPos($logo);
            $raw .= "\n";
        }

        // EVENTO
        $raw .= alinear(1);
        $raw .= tamano(0x01);
        $raw .= negrita(true);
        $raw .= comandoTexto(wordwrap($up($resumen['evento_titulo'] ?? ''), 24, "\n", false) . "\n");
        $raw .= negrita(false);
        $raw .= tamano(0);

        $raw .= comandoTexto($sep);

        // INFO DEL EVENTO
        $raw .= tamano(0);
        $raw .= negrita(true);
        foreach (($resumen['info_evento'] ?? []) as $linea) {
            if ($linea !== '') {
                $raw .= comandoTexto($up($linea) . "\n");
            }
        }
        $raw .= negrita(false);
        $raw .= tamano(0);

        $raw .= comandoTexto($sepD);

        // CUERPO
        if (!empty($resumen['es_general']) && !empty($resumen['desglose_cajas'])) {
            foreach ($resumen['desglose_cajas'] as $idx => $c) {
                if ($idx > 0) {
                    $raw .= alinear(1) . comandoTexto($sep);
                }
                $n = (int) $c['caja_num'];
                $raw .= $bloque("VENTAS CAJA $n", $c['ventas'] ?? [], "TICKETS CAJA $n", $c['tickets'] ?? []);
            }
            $raw .= alinear(1) . comandoTexto($sepD);
            $raw .= $bloque("GENERAL VENTAS", $resumen['ventas'] ?? [], "GENERAL TICKETS", $resumen['tickets'] ?? []);
        } else {
            $raw .= $bloque("RESUMEN VENTAS", $resumen['ventas'] ?? [], "RESUMEN TICKETS", $resumen['tickets'] ?? []);
        }

        // PIE
        $raw .= alinear(1) . comandoTexto($sepD);
        $raw .= alinear(0);
        $raw .= comandoTexto("Evento: " . ($resumen['evento_nombre'] ?? '') . " (" . ($resumen['codigo_evento'] ?? '') . ")\n");
        $raw .= comandoTexto("Fecha: " . ($resumen['fecha_emision'] ?? date('d/m/Y H:i')) . "\n");
        $raw .= comandoTexto("Caja: " . ($resumen['caja_texto'] ?? '') . "\n");

        // FOOTER TICKeteo
        $raw .= "\n";
        $raw .= alinear(1);
        $raw .= imagenFooterESCPos(380);
        $raw .= "\n";

        $raw .= alinear(1) . comandoTexto($sep);

        $raw .= "\n\n";

        // Cortar papel
        $raw .= "\x1D\x56\x00";

        // Guardar y enviar RAW
        if (file_put_contents($archivoTemporal, $raw) === false) {
            throw new Exception("No se pudo guardar el archivo de impresión.");
        }

        $archivoTemporalReal = realpath($archivoTemporal);
        if ($archivoTemporalReal === false) {
            throw new Exception("No se pudo obtener la ruta del archivo temporal.");
        }

        $comando = 'cmd.exe /c copy /b "' . $archivoTemporalReal . '" "' . $impresora . '"';
        $salida = [];
        exec($comando . " 2>&1", $salida, $resultado);

        if ($resultado !== 0) {
            throw new Exception("Error enviando a impresora: " . implode("\n", $salida));
        }

        return ["status" => "ok", "message" => "Resumen enviado a la impresora correctamente."];

    } catch (Throwable $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    }
}
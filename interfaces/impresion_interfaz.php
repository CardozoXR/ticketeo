<?php
date_default_timezone_set('America/Montevideo');

class FuncionesImpresion
{
    // 1. true = vista previa en pantalla | false = imprime en impresora real
    public const MODO_PRUEBA = true;

    // 2. true = impresora común vía driver Windows | false = térmica 58mm ESC/POS directo
    public const IMPRESORA_COMUN = false;

    public static function imprimirTicketsVenta(?string $nombreImpresora, array $datosVenta): array
    {
        date_default_timezone_set('America/Montevideo');

        if (empty($datosVenta['tickets'])) {
            return ["ok" => false, "error" => "No hay tickets para imprimir."];
        }

        if (self::MODO_PRUEBA) {
            return ["ok" => true, "modo_prueba" => true];
        }

        if (empty($nombreImpresora) || !preg_match('/^[a-zA-Z0-9_\-\\\\. ]+$/', $nombreImpresora)) {
            return ["ok" => false, "error" => "Nombre de impresora inválido o no asignado."];
        }

        $fechaHora    = date('d/m/Y H:i');
        $eventoNombre = self::limpiarTexto(strtoupper($datosVenta['evento_nombre'] ?? 'AFTER BANCO'));
        $rawTotal     = "";

        if (self::IMPRESORA_COMUN) {
            foreach ($datosVenta['tickets'] as $ticket) {
                $esCortesia = ((float)$ticket['precio'] === 0.0);
                $precioTxt  = $esCortesia
                    ? "CORTESIA"
                    : "$ " . number_format((float)$ticket['precio'], 2, '.', '');

                $etiqueta = self::limpiarTexto(strtoupper($ticket['etiqueta']));
                $cajaTxt  = self::limpiarTexto($datosVenta['caja_nombre']);

                $rawTotal .= "================================\r\n";
                $rawTotal .= "  CODIGO: " . $ticket['codigo_qr'] . "\r\n";
                $rawTotal .= "  EVENTO: " . $eventoNombre . "\r\n";
                $rawTotal .= "--------------------------------\r\n";
                $rawTotal .= "  1x " . $etiqueta . "\r\n";
                $rawTotal .= "  " . $precioTxt . "\r\n";
                $rawTotal .= "  TICKET: " . $ticket['numero_ticket'] . "\r\n";
                $rawTotal .= "--------------------------------\r\n";
                $rawTotal .= "  Op #" . $datosVenta['numero_operacion'] . " - " . $cajaTxt . "\r\n";
                $rawTotal .= "  Cajero: " . $cajTxt . " | " . $fechaHora . "\r\n";
                $rawTotal .= "================================\r\n\r\n";
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'tck_') . '.txt';
            file_put_contents($tmpFile, $rawTotal);

            $nombreBuscado   = ltrim(str_replace('\\\\localhost\\', '', $nombreImpresora), '\\');
            $nombreEscapado  = str_replace("'", "''", $nombreBuscado);
            $archivoEscapado = str_replace("'", "''", $tmpFile);

            $psScript = "\$p = Get-Printer | Where-Object { \$_.ShareName -eq '{$nombreEscapado}' -or \$_.Name -eq '{$nombreEscapado}' } | Select-Object -First 1; "
                      . "if (-not \$p) { Write-Error 'No se encontro impresora {$nombreEscapado}'; exit 1 }; "
                      . "Get-Content -Path '{$archivoEscapado}' -Encoding UTF8 | Out-Printer -Name \$p.Name";

            $cmd = 'powershell -NoProfile -NonInteractive -Command "' . $psScript . '" 2>&1';

            $salida = [];
            $codigo = 0;
            exec($cmd, $salida, $codigo);
            @unlink($tmpFile);

            if ($codigo !== 0) {
                $detalleError = trim(implode(" ", $salida));
                return [
                    "ok"    => false,
                    "error" => "Error driver Windows ({$nombreBuscado}): " . ($detalleError !== '' ? $detalleError : "código {$codigo}")
                ];
            }

            return ["ok" => true, "modo_prueba" => false];
        }

        // Impresión térmica directa 58mm ESC/POS
        $rutaImpresora = str_starts_with($nombreImpresora, '\\\\')
            ? $nombreImpresora
            : '\\\\localhost\\' . $nombreImpresora;

        $ESC = "\x1B";
        $GS  = "\x1D";

        $INIT        = $ESC . "@";
        $CENTER      = $ESC . "a\x01";
        $BOLD_ON     = $ESC . "E\x01";
        $BOLD_OFF    = $ESC . "E\x00";
        $INVERT_ON   = $GS  . "B\x01";
        $INVERT_OFF  = $GS  . "B\x00";
        $DOUBLE_SIZE = $GS  . "!\x11";
        $NORMAL_SIZE = $GS  . "!\x00";
        $CUT         = $GS  . "V\x41\x03";

        foreach ($datosVenta['tickets'] as $ticket) {
            $esCortesia = ((float)$ticket['precio'] === 0.0);
            $precioTxt  = $esCortesia
                ? "CORTESIA"
                : "$ " . number_format((float)$ticket['precio'], 2, '.', '');

            $etiqueta = self::limpiarTexto(strtoupper($ticket['etiqueta']));
            $qrData   = $ticket['codigo_qr'];

            $qrLen  = strlen($qrData) + 3;
            $lenBin = pack('v', $qrLen);
            $qrCmd  = $GS . "(k\x04\x00\x31\x41\x32\x00"
                    . $GS . "(k\x03\x00\x31\x43\x05"
                    . $GS . "(k\x03\x00\x31\x45\x31"
                    . $GS . "(k" . $lenBin . "\x31\x50\x30" . $qrData
                    . $GS . "(k\x03\x00\x31\x51\x30";

            $rawTotal .= $INIT . $CENTER;
            $rawTotal .= $INVERT_ON . $BOLD_ON . "   " . $qrData . "   \n" . $BOLD_OFF . $INVERT_OFF . "\n";
            $rawTotal .= $BOLD_ON . $eventoNombre . "\n" . $BOLD_OFF;
            $rawTotal .= "- - - - - - - - - - - -\n";
            $rawTotal .= $DOUBLE_SIZE . $BOLD_ON . "1x " . $etiqueta . "\n";
            $rawTotal .= $precioTxt . "\n" . $NORMAL_SIZE . $BOLD_OFF . "\n";
            $rawTotal .= $qrCmd . "\n";
            $rawTotal .= $BOLD_ON . $qrData . "\n" . $BOLD_OFF;
            $rawTotal .= "- - - - - - - - - - - -\n\n\n";
            $rawTotal .= $CUT;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'tck_');
        file_put_contents($tmpFile, $rawTotal);

        $enviado = @copy($tmpFile, $rutaImpresora);
        @unlink($tmpFile);

        if (!$enviado) {
            return [
                "ok"    => false,
                "error" => "No se pudo comunicar con la impresora ({$rutaImpresora})."
            ];
        }

        return ["ok" => true, "modo_prueba" => false];
    }

    public static function imprimirResumen(?string $nombreImpresora, array $resumen): array
    {
        date_default_timezone_set('America/Montevideo');

        if (self::MODO_PRUEBA) {
            return ["ok" => true, "modo_prueba" => true];
        }

        if (empty($nombreImpresora) || !preg_match('/^[a-zA-Z0-9_\-\\\\. ]+$/', $nombreImpresora)) {
            return ["ok" => false, "error" => "Nombre de impresora inválido o no asignado."];
        }

        $rutaImpresora = str_starts_with($nombreImpresora, '\\\\')
            ? $nombreImpresora
            : '\\\\localhost\\' . $nombreImpresora;

        $ESC         = "\x1B";
        $GS          = "\x1D";
        $INIT        = $ESC . "@";
        $CENTER      = $ESC . "a\x01";
        $LEFT        = $ESC . "a\x00";
        $BOLD_ON     = $ESC . "E\x01";
        $BOLD_OFF    = $ESC . "E\x00";
        $INVERT_ON   = $GS  . "B\x01";
        $INVERT_OFF  = $GS  . "B\x00";
        $DOUBLE_SIZE = $GS  . "!\x11";
        $NORMAL_SIZE = $GS  . "!\x00";
        $CUT         = $GS  . "V\x41\x03";

        $lineaPar = function (string $izq, string $der): string {
            $ancho = 32;
            $espacios = max(1, $ancho - strlen($izq) - strlen($der));
            return $izq . str_repeat(' ', $espacios) . $der . "\n";
        };

        $renderBloqueEscPos = function (string $titVentas, array $ventas, string $titTickets, array $tickets) use ($CENTER, $LEFT, $BOLD_ON, $BOLD_OFF, $lineaPar): string {
            $out  = $CENTER . $BOLD_ON . $titVentas . "\n\n" . $BOLD_OFF;
            $out .= $LEFT . $BOLD_ON;
            $out .= $lineaPar("TOTAL:", "$" . number_format((float)($ventas['total'] ?? 0), 0, ',', '.')) . "\n";
            $out .= $lineaPar("EFECTIVO:", "$" . number_format((float)($ventas['efectivo'] ?? 0), 0, ',', '.'));
            $out .= $lineaPar("TARJETA:", "$" . number_format((float)($ventas['tarjeta'] ?? 0), 0, ',', '.'));
            $out .= $BOLD_OFF . $CENTER;

            $out .= "------------------\n";
            $out .= $BOLD_ON . $titTickets . "\n\n" . $BOLD_OFF;
            $out .= $LEFT . $BOLD_ON;
            $out .= $lineaPar("TOTAL EMITIDOS:", number_format((int)($tickets['total_emitidos'] ?? 0), 0, ',', '.')) . "\n";
            $out .= $lineaPar("ORIGINAL:", number_format((int)($tickets['original'] ?? 0), 0, ',', '.'));
            $out .= $lineaPar("REIMPRESION:", number_format((int)($tickets['reimpresion'] ?? 0), 0, ',', '.'));
            $out .= $BOLD_OFF . $CENTER;

            return $out;
        };

        $tituloHead = self::limpiarTexto(strtoupper($resumen['titulo_head']));
        $eventoTit  = self::limpiarTexto(strtoupper($resumen['evento_titulo']));

        $raw  = $INIT . $CENTER;
        $raw .= $INVERT_ON . $BOLD_ON . "   " . $tituloHead . "   \n" . $BOLD_OFF . $INVERT_OFF . "\n";
        $raw .= $DOUBLE_SIZE . $BOLD_ON . $eventoTit . "\n" . $NORMAL_SIZE . $BOLD_OFF;
        $raw .= "------------------\n";

        foreach ($resumen['info_evento'] as $lineaInfo) {
            if ($lineaInfo !== '') {
                $raw .= $BOLD_ON . self::limpiarTexto(strtoupper($lineaInfo)) . "\n" . $BOLD_OFF;
            }
        }

        $raw .= "------------------\n";

        if (!empty($resumen['es_general']) && !empty($resumen['desglose_cajas'])) {
            foreach ($resumen['desglose_cajas'] as $idx => $cajaData) {
                if ($idx > 0) {
                    $raw .= "------------------\n";
                }
                $numCaja = (int)$cajaData['caja_num'];
                $raw .= $renderBloqueEscPos(
                    "VENTAS CAJA {$numCaja}",
                    $cajaData['ventas'],
                    "TICKETS CAJA {$numCaja}",
                    $cajaData['tickets']
                );
            }
            $raw .= "===================\n";
            $raw .= $renderBloqueEscPos(
                "GENERAL VENTAS",
                $resumen['ventas'],
                "GENERAL TICKETS",
                $resumen['tickets']
            );
        } else {
            $raw .= $renderBloqueEscPos(
                "RESUMEN VENTAS",
                $resumen['ventas'],
                "RESUMEN TICKETS",
                $resumen['tickets']
            );
        }

        $raw .= "------------------\n";
        $raw .= $LEFT;
        $raw .= "Evento: " . self::limpiarTexto($resumen['evento_nombre']) . " (" . $resumen['codigo_evento'] . ")\n";
        $raw .= "Fecha: " . $resumen['fecha_emision'] . "\n";
        $raw .= "Caja: " . self::limpiarTexto($resumen['caja_texto']) . "\n";
        $raw .= $CENTER . "------------------\n\n\n";
        $raw .= $CUT;

        $tmpFile = tempnam(sys_get_temp_dir(), 'res_');
        file_put_contents($tmpFile, $raw);
        $enviado = @copy($tmpFile, $rutaImpresora);
        @unlink($tmpFile);

        if (!$enviado) {
            return ["ok" => false, "error" => "No se pudo imprimir el resumen en ({$rutaImpresora})."];
        }

        return ["ok" => true, "modo_prueba" => false];
    }

    private static function limpiarTexto(string $texto): string
    {
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            '·' => '-'
        ]);
    }
}
?>
<?php
class FuncionesImpresion {
    // 1. true = vista previa en pantalla | false = imprime en impresora real
    public const MODO_PRUEBA = true;

    // 2. true = impresora común vía driver Windows | false = térmica 58mm ESC/POS directo
    public const IMPRESORA_COMUN = true;

    public static function imprimirTicketsVenta($nombreImpresora, $datosVenta) {
        if (empty($datosVenta['tickets'])) {
            return ["ok" => false, "error" => "No hay tickets para imprimir."];
        }

        if (self::MODO_PRUEBA) {
            return [
                "ok" => true,
                "modo_prueba" => true
            ];
        }

        if (empty($nombreImpresora)) {
            return ["ok" => false, "error" => "Caja sin impresora asignada."];
        }

        $fechaHora = date('d/m/Y H:i');
        $rawTotal  = "";

        if (self::IMPRESORA_COMUN) {
            foreach ($datosVenta['tickets'] as $ticket) {
                $esCortesia = ((float)$ticket['precio'] === 0.0);
                $precioTxt  = $esCortesia
                    ? "CORTESIA"
                    : "$" . number_format((float)$ticket['precio'], 0, ',', '.');

                $etiqueta = self::limpiarTexto(strtoupper($ticket['etiqueta']));
                $cajaTxt  = self::limpiarTexto($datosVenta['caja_nombre']);
                $cajTxt   = self::limpiarTexto($datosVenta['cajero_nombre']);

                $rawTotal .= "================================\r\n";
                $rawTotal .= "       AFTER BANCO 10.12        \r\n";
                $rawTotal .= "--------------------------------\r\n";
                $rawTotal .= "  PRODUCTO: " . $etiqueta . "\r\n";
                $rawTotal .= "  VALOR:    " . $precioTxt . "\r\n";
                $rawTotal .= "  TICKET:   " . $ticket['numero_ticket'] . "\r\n";
                $rawTotal .= "  QR CODE:  " . $ticket['codigo_qr'] . "\r\n";
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
                      . "if (-not \$p) { Write-Error 'No se encontro impresora con nombre o recurso compartido {$nombreEscapado}'; exit 1 }; "
                      . "Get-Content -Path '{$archivoEscapado}' -Encoding UTF8 | Out-Printer -Name \$p.Name";

            $cmd = 'powershell -NoProfile -NonInteractive -Command "' . $psScript . '" 2>&1';

            $salida = [];
            $codigo = 0;
            exec($cmd, $salida, $codigo);
            @unlink($tmpFile);

            if ($codigo !== 0) {
                $detalleError = trim(implode(" ", $salida));
                return [
                    "ok" => false,
                    "error" => "Error driver Windows ({$nombreBuscado}): " . ($detalleError !== '' ? $detalleError : "código {$codigo}")
                ];
            }

            return ["ok" => true, "modo_prueba" => false];
        }

        // Formato binario ESC/POS nativo para impresoras térmicas de 58mm
        $rutaImpresora = str_starts_with($nombreImpresora, '\\\\')
            ? $nombreImpresora
            : '\\\\localhost\\' . $nombreImpresora;

        $ESC = "\x1B";
        $GS  = "\x1D";

        $INIT        = $ESC . "@";
        $CENTER      = $ESC . "a\x01";
        $BOLD_ON     = $ESC . "E\x01";
        $BOLD_OFF    = $ESC . "E\x00";
        $DOUBLE_SIZE = $GS  . "!\x11";
        $NORMAL_SIZE = $GS  . "!\x00";
        $CUT         = $GS  . "V\x41\x03";

        foreach ($datosVenta['tickets'] as $ticket) {
            $esCortesia = ((float)$ticket['precio'] === 0.0);
            $precioTxt  = $esCortesia
                ? "CORTESIA"
                : "$" . number_format((float)$ticket['precio'], 0, ',', '.');

            $etiqueta = self::limpiarTexto(strtoupper($ticket['etiqueta']));
            $cajaTxt  = self::limpiarTexto($datosVenta['caja_nombre']);
            $cajTxt   = self::limpiarTexto($datosVenta['cajero_nombre']);
            $qrData   = $ticket['codigo_qr'];

            $qrLen  = strlen($qrData) + 3;
            $lenBin = pack('v', $qrLen);
            $qrCmd  = $GS . "(k\x04\x00\x31\x41\x32\x00"
                    . $GS . "(k\x03\x00\x31\x43\x05"
                    . $GS . "(k\x03\x00\x31\x45\x31"
                    . $GS . "(k" . $lenBin . "\x31\x50\x30" . $qrData
                    . $GS . "(k\x03\x00\x31\x51\x30";

            $rawTotal .= $INIT . $CENTER;
            $rawTotal .= $BOLD_ON . "AFTER BANCO 10.12\n" . $BOLD_OFF;
            $rawTotal .= "--------------------------------\n";
            $rawTotal .= $DOUBLE_SIZE . $BOLD_ON . $etiqueta . "\n" . $NORMAL_SIZE . $BOLD_OFF;
            $rawTotal .= $BOLD_ON . $precioTxt . "\n\n" . $BOLD_OFF;
            $rawTotal .= $qrCmd . "\n";
            $rawTotal .= $ticket['numero_ticket'] . "\n";
            $rawTotal .= "--------------------------------\n";
            $rawTotal .= "Op #" . $datosVenta['numero_operacion'] . " - " . $cajaTxt . "\n";
            $rawTotal .= "Cajero: " . $cajTxt . "\n";
            $rawTotal .= $fechaHora . "\n\n\n";
            $rawTotal .= $CUT;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'tck_');
        file_put_contents($tmpFile, $rawTotal);

        $enviado = @copy($tmpFile, $rutaImpresora);
        @unlink($tmpFile);

        if (!$enviado) {
            return [
                "ok" => false,
                "error" => "No se pudo comunicar con la impresora ({$rutaImpresora})."
            ];
        }

        return ["ok" => true, "modo_prueba" => false];
    }

    private static function limpiarTexto($texto) {
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            '·' => '-'
        ]);
    }
}
?>